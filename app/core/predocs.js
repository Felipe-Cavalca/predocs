class predocsHelper {
    _criarComponentesIniciais() {
        Promise.all([
            this.criarComponente(
                "carregando",
                "html",
                "append",
                ["/components/css/carregando.css"],
                ["/components/js/carregando.js"]
            ),
            this.criarComponente(
                "offline",
                "html",
                "append",
                ["/components/css/offline.css"],
                ["/components/js/offline.js"]
            ),
        ]);
    }

    _PWA() {
        const link = document.createElement("link");
        this.adicionarAtributos(link, {
            rel: "manifest",
            href: this.getUrl("/config/manifest.json"),
        });
        document.querySelector("head").prepend(link);

        if ("serviceWorker" in navigator) {
            navigator.serviceWorker
                .register(this.getUrl("/sw.js"))
                .catch((err) => {
                    console.error("Erro ao registrar o Service Worker: ", err);
                });
            window.addEventListener("beforeinstallprompt", (e) => {
                this.deferredPrompt = e;
            });
        }
    }

    _criarMetaDados() {
        const metadados = [
            { tipo: "meta", name: "viewport", content: "width=device-width, initial-scale=1.0" },
            { tipo: "meta", httpEquiv: "X-UA-Compatible", content: "IE=edge" },
            { tipo: "meta", charset: "utf-8" },
            {
                tipo: "link",
                rel: "shortcut icon",
                href: this.getUrl("/midia/global/favicon.ico"),
                type: "image/x-icon",
            },
            {
                tipo: "link",
                rel: "apple-touch-icon",
                href: this.getConfig("iconApp"),
            },
            {
                tipo: "meta",
                name: "theme-color",
                content: this.getConfig("corApp"),
            },
        ];

        metadados.forEach((md) => {
            const elemento = document.createElement(md.tipo);
            this.adicionarAtributos(elemento, md);
            document.querySelector("head").prepend(elemento);
        });

        document.querySelector("html").setAttribute("lang", "pt-br");
    }

    _incluirDependenciasGlobais() {
        const includes = JSON.parse(this.get("/config/includes.json"));

        const styles = [
            ...includes.stylesGlobais,
            ...this.recurso.styles,
        ].filter((style) => !this.recurso.stylesBloqueados.includes(style));
        const scripts = [
            ...includes.scriptsGlobais,
            ...this.recurso.scripts,
        ].filter((script) => !this.recurso.scriptsBloqueados.includes(script));

        this.incluirRecurso("link", styles);
        this.incluirRecurso("script", scripts);

        Promise.all(
            includes.componentsGlobal.map((c) => {
                if (!this.recurso.componentesBloqueados.includes(c.component)) {
                    this.criarComponente(
                        c.component,
                        c.element,
                        c.local,
                        c.css,
                        c.js
                    );
                }
            })
        );
    }

    _onloadBody(after) {
        document.querySelector("body").onload = () => {
            this.carregando = new Carregando(this);
            this.offline = new Offline(this);

            if (typeof after === "function") {
                after();
            }

            this.carregando.hide();
        };
    }

    _desabilitarAutocomplete() {
        Array.from(
            document.querySelectorAll("input:not([autocomplete])")
        ).forEach((element) => {
            this.adicionarAtributos(element, {
                autocomplete: "off",
            });
        });
    }
}

class Predocs extends predocsHelper {
    config = {};
    carregando; // Classe do component de carregamento
    offline; //Classe do component offline

    recurso = {
        scripts: [],
        styles: [],
        scriptsBloqueados: [],
        stylesBloqueados: [],
        componentesBloqueados: [],
    };

    deferredPrompt;

    constructor(params = {}) {
        super();

        this.recurso.scripts = params.scripts || [];
        this.recurso.styles = params.styles || [];
        this.recurso.scriptsBloqueados = params.scriptsBloqueados || [];
        this.recurso.stylesBloqueados = params.stylesBloqueados || [];
        this.recurso.componentesBloqueados = params.componentesBloqueados || [];
    }

    init(before, after) {
        if (typeof before === "function") {
            before();
        }

        this._criarComponentesIniciais();
        this._PWA();
        this._criarMetaDados();
        this._incluirDependenciasGlobais();
        this._onloadBody(after);
        this._desabilitarAutocomplete();
    }

    getModoExecucao() {
        const suportaModoStandalone = window.matchMedia(
            "(display-mode: standalone)"
        ).matches;
        if (document.referrer.startsWith("android-app://")) {
            return "PWA";
        } else if (navigator.standalone || suportaModoStandalone) {
            return "Standalone";
        }
        return "Navegador";
    }

    get(url, params = {}, cache = true) {
        const fullUrl = this.addParamsToUrl(this.getUrl(url), params);

        if (!navigator.onLine) {
            if (cache) {
                return localStorage.getItem(`GET-${fullUrl}`);
            }
            return undefined;
        }

        try {
            const xhr = new XMLHttpRequest();
            xhr.open("GET", fullUrl, false);
            xhr.send();

            if (xhr.status === 200) {
                if (cache) {
                    localStorage.setItem(`GET-${fullUrl}`, xhr.responseText);
                }
                return xhr.responseText;
            }
        } catch (e) {
            console.error(e);
        }

        return undefined;
    }

    post(url, dadosPost, paramsUrl = {}, json = true, assincrona = false) {
        const fullUrl = this.addParamsToUrl(this.getUrl(url), paramsUrl);
        try {
            const xhttp = new XMLHttpRequest();
            xhttp.open("POST", fullUrl, assincrona);
            xhttp.send(json ? JSON.stringify(dadosPost) : dadosPost);
            return xhttp.responseText;
        } catch (e) {
            console.error(e);
        }

        return undefined;
    }

    form(element, before, after) {
        document.querySelector(element).addEventListener("submit", (event) => {
            event.preventDefault();
            if (before() !== false) {
                let resp;
                const data = new FormData(event.target);
                if (document.querySelector(element).method == "post") {
                    resp = JSON.parse(
                        this.post(
                            "/server" + document.querySelector(element).action.replace(location.origin, ""),
                            data,
                            {},
                            false
                        )
                    );
                }
                after(resp);
            }
        });
        return true;
    }

    montaSelect = (element, options) => {
        options.forEach(({ value, text }) => {
            const option = document.createElement("option");
            option.value = value || text;
            option.textContent = text;
            document.querySelector(element).appendChild(option);
        });
        return true;
    };

    replaceTextInView(elemSelector, replacements) {
        let html = document.querySelector(elemSelector).innerHTML;
        Object.keys(replacements).forEach((key) => {
            html = html.replace(
                new RegExp(`{{${key}}}`, "g"),
                replacements[key]
            );
        });
        document.querySelector(elemSelector).innerHTML = html;
        return true;
    }

    criarComponente(component, element, local, css = [], js = []) {
        let componentElement;

        switch (component) {
            case "carregando":
            case "offline":
                componentElement = document.createElement(component);
                break;
            default:
                componentElement = document.createElement("section");
                componentElement.setAttribute(
                    "class",
                    `component-${component}`
                );
        }

        switch (local) {
            case "append":
                document.querySelector(element).append(componentElement);
                break;
            case "prepend":
            default:
                document.querySelector(element).prepend(componentElement);
        }

        componentElement.innerHTML = this.get(
            `/components/html/${component}.html`
        );
        this.incluirRecurso("script", js);
        this.incluirRecurso("link", css);

        return true;
    }

    getUrl(url) {
        if (url.startsWith("/server")) {
            return this.getConfig("servidor") + url.replace("/server/", "");
        } else {
            return url.startsWith("/")
                ? `${document
                      .querySelector("#coreJs")
                      .src.replace("/core/predocs.js", "")}${url}`
                : url;
        }
    }

    getConfig(tipo) {
        if (!this.config[tipo]) {
            switch (tipo) {
                case "app":
                    this.config[tipo] = JSON.parse(
                        this.get("/config/app.json")
                    );
                    break;
                case "servidor":
                    this.config[tipo] = JSON.parse(
                        this.get("/config/app.json")
                    ).server;
                    break;
                case "iconApp":
                    this.config[tipo] = JSON.parse(
                        this.get("/config/manifest.json")
                    ).icons[0].src;
                    break;
                case "corApp":
                    this.config[tipo] = JSON.parse(
                        this.get("/config/manifest.json")
                    ).theme_color;
                    break;
                default:
                    break;
            }
        }
        return this.config[tipo];
    }

    adicionarAtributos(elemento, atributos) {
        Object.entries(atributos).forEach(([chave, valor]) => {
            if (chave !== "tipo") {
                elemento.setAttribute(chave, valor);
            }
        });
    }

    incluirRecurso(tipo = "script", links = []) {
        links.forEach((link) => {
            const recurso = document.createElement(tipo);
            if (tipo === "script") {
                recurso.setAttribute("src", this.getUrl(link));
                recurso.onload = () => {};
            } else if (tipo === "link") {
                recurso.setAttribute("rel", "stylesheet");
                recurso.setAttribute("href", this.getUrl(link));
            }
            document
                .querySelector(tipo === "script" ? "body" : "head")
                .appendChild(recurso);
        });
    }

    addParamsToUrl(url, params) {
        let queryParams = Object.entries(params)
            .map(([key, value]) => `${key}=${value}`)
            .join("&");

        return `${url}?${queryParams}`;
    }

    async installApp(aceitou = () => {}, recusou = () => {}) {
        try {
            await this.deferredPrompt.prompt();
            const choice = await this.deferredPrompt.userChoice;

            if (choice.outcome === "accepted") {
                aceitou();
            } else {
                recusou();
            }
        } catch (error) {
            console.error(error);
        } finally {
            this.deferredPrompt = null;
        }
    }
}

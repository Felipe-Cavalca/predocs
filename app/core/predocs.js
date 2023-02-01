var deferredPrompt;
class Predocs {
    config = {};
    carregando; // Classe do component de carregamento
    offline; //Classe do component offline
    scripts;
    styles;
    scriptsBloqueados;
    stylesBloqueados;
    componentesBloqueados;

    constructor(params = {}) {
        this.scripts = params.scripts || [];
        this.styles = params.styles || [];
        this.scriptsBloqueados = params.scriptsBloqueados || [];
        this.stylesBloqueados = params.stylesBloqueados || [];
        this.componentesBloqueados = params.componentesBloqueados || [];
    }

    async init(before, after) {
        if (typeof before === "function") {
            await before();
        }

        await Promise.all([
            this.createComponent(
                "carregando",
                "html",
                "append",
                ["/components/css/carregando.css"],
                ["/components/js/carregando.js"]
            ),
            this.createComponent(
                "offline",
                "html",
                "append",
                ["/components/css/offline.css"],
                ["/components/js/offline.js"]
            ),
        ]);

        this.PWA();
        this.criarMetaDados();

        const includes = JSON.parse(this.get("/config/includes.json"));
        this.incluirRecurso("link", [
            ...includes.stylesGlobais,
            ...(this.styles || []),
        ]);
        this.incluirRecurso("script", [
            ...includes.scriptsGlobais,
            ...(this.scripts || []),
        ]);

        await Promise.all(
            includes.componentsGlobal.map((c) =>
                this.createComponent(
                    c.component,
                    c.element,
                    c.local,
                    c.css,
                    c.js
                )
            )
        );

        document.querySelector("body").onload = () => {
            this.carregando = new Carregando(this);
            this.offline = new Offline(this);

            if (typeof after === "function") {
                after();
            }

            this.carregando.hide();
        };

        Array.from(
            document.querySelectorAll("input:not([autocomplete])")
        ).forEach((element) => {
            element.setAttribute("autocomplete", "off");
        });

        document.querySelector("html").onerror = () => {
            window.location.href = this.getUrl("/error");
        };
    }

    mostrarBotaoInstalacao() {
        document.querySelector("html").addEventListener("click", () => {
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then((escolha) => {
                if (escolha.outcome === "accepted") {
                    console.log("Usuário aceitou instalar o app");
                } else {
                    console.log("Usuário recusou instalar");
                }
                document
                    .querySelector("html")
                    .removeEventListener("click", this.mostrarBotaoInstalacao);
                localStorage.setItem("exibeMsgInstall", false);
                deferredPrompt = null;
            });
        });
        return undefined;
    }

    determinaModoExecucao() {
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

        this.offlineShow();
        return undefined;
    }

    post(url, dadosPost, paramsUrl = {}) {
        const fullUrl = this.addParamsToUrl(this.getUrl(url), paramsUrl);
        try {
            const xhttp = new XMLHttpRequest();
            xhttp.open("POST", fullUrl, false);
            xhttp.setRequestHeader("Content-Type", "application/json");
            xhttp.send(JSON.stringify(dadosPost));
            return xhttp.responseText;
        } catch (e) {}

        this.offlineShow();
        return undefined;
    }

    form(element, before, after) {
        document.querySelector(element).addEventListener("submit", (event) => {
            event.preventDefault();
            if (before() !== false) {
                const form = event.target;
                const data = new FormData(form);
                const resp =
                    form.method === "post"
                        ? this.post(
                              `/server${form.action.replace(
                                  location.origin,
                                  ""
                              )}`,
                              data,
                              false
                          )
                        : null;
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

    createComponent(component, element, local, css = [], js = []) {
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

    criarMetaDados() {
        const metadados = [
            { tipo: "meta", name: "viewport", content: "" },
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

    PWA() {
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
                deferredPrompt = e;
                if (localStorage.getItem("exibeMsgInstall") !== "false") {
                    this.mostrarBotaoInstalacao();
                }
            });
        }
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
}

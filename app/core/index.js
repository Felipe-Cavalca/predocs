try {
    //funções ==================================================

    /**
     * Adiciona as tags de script e link a tela
     * @version 2.0.1
     * @access public
     * @param {array} links - Array de urls que serão importadas
     * @param {string} tipo - tipo de arquivo que será adicionado na tela (css, js)
     * @return {void} - Função não retorna dados
     */
    async function incluiScript(links, tipo) {
        switch (tipo) {
            case "js":
                for (var i = 0; i < links.length; i++) {
                    let scriptsNegados = Lis.Nscripts ?? [];
                    if (!scriptsNegados.includes(links[i])) {
                        const script = document.createElement("script");
                        script.setAttribute("src", Lis.getUrl(links[i]));
                        document.querySelector("body").appendChild(script);
                        //Aguarda o carregamento do script
                        await new Promise((res) => {
                            script.onload = () => {
                                res(0);
                            };
                        });
                    }
                }
                break;
            case "css":
                links.forEach((url) => {
                    let stylesNegados = Lis.Nstyles ?? [];
                    if (!stylesNegados.includes(url)) {
                        const style = document.createElement("link");
                        style.setAttribute("rel", "stylesheet");
                        style.setAttribute("href", Lis.getUrl(url));
                        document.querySelector("head").appendChild(style);
                    }
                });
                break;
        }
    }

    /**
     * Função para adicionar os metadados ao arquivo
     * @version 1
     * @access public
     * @return {void} - Função não retorna dados
     */
    function createMeta() {
        let meta;
        let link;

        meta = document.createElement("meta");
        meta.setAttribute("name", "viewport");
        meta.setAttribute("content", "width=device-width, initial-scale=1.0");
        document.querySelector("head").prepend(meta);

        meta = document.createElement("meta");
        meta.setAttribute("http-equiv", "X-UA-Compatible");
        meta.setAttribute("content", "IE=edge");
        document.querySelector("head").prepend(meta);

        meta = document.createElement("meta");
        meta.setAttribute("charset", "utf-8");
        document.querySelector("head").prepend(meta);

        const html = document.querySelector("html");
        html.setAttribute("lang", "pt-br");

        link = document.createElement("link");
        link.setAttribute("rel", "shortcut icon");
        link.setAttribute("href", Lis.getUrl("/midia/global/favicon.ico"));
        link.setAttribute("type", "image/x-icon");
        document.querySelector("head").prepend(link);

        // necessario para o funcionamento em sistemas IOS
        link = document.createElement("link");
        link.setAttribute("rel", "apple-touch-icon");
        link.setAttribute("href", Lis.getConfig("iconApp"));
        document.querySelector("head").prepend(link);

        meta = document.createElement("meta");
        meta.setAttribute("name", "theme-color");
        meta.setAttribute("content", Lis.getConfig("corApp"));
        document.querySelector("head").prepend(meta);
    }

    /**
     * Função para adicionar o PWA a pagina
     * @version 1
     * @access public
     * @return {void}
     */
    function PWA() {
        const link = document.createElement("link");
        link.setAttribute("rel", "manifest");
        link.setAttribute("href", Lis.getUrl("/config/manifest.json"));
        document.querySelector("head").prepend(link); // Adiciona o elemento com o link do manifest a tela

        if ("serviceWorker" in navigator) {
            navigator.serviceWorker.register(Lis.getUrl("/sw.js"));
            var deferredPrompt; // Inicialize o deferredPrompt para posteriormente mostrar o prompt de instalação do navegador.
            window.addEventListener("beforeinstallprompt", (e) => {
                deferredPrompt = e;
                if (
                    localStorage.getItem("exibeMsgInstall") == null ||
                    localStorage.getItem("exibeMsgInstall") == "true"
                ) {
                    document
                        .querySelector("html")
                        .addEventListener("click", chamaInstallApp);
                }
            });
        }

        /**
         * Função para exibir a tela de instação do app
         * OBS: Essa função só pode ser executada através de um gesto do usuario
         * @version 1
         * @access public
         * @return {void}
         */
        Lis.installApp = () => {
            deferredPrompt.prompt();
            // Aguarda a resposta do usuario
            deferredPrompt.userChoice.then((escolha) => {
                if (escolha.outcome === "accepted") {
                    // Usuário aceitou instalar o app
                } else {
                    // Usuário recusou instalar
                }

                //zera as variaveis após exibição do botão para instalar
                document
                    .querySelector("html")
                    .removeEventListener("click", chamaInstallApp);
                localStorage.setItem("exibeMsgInstall", false);
                deferredPrompt = null;
            });
        };

        /**
         * Função a que será adicionada ao evento de click
         * @version 1
         * @access public
         * @return {void}
         */
        function chamaInstallApp() {
            Lis.installApp();
        }
    }

    /**
     * Função chamada para iniciar o framework
     * @version 2
     * @access public
     * @return {void} - Função não tem retorno
     */
    async function init() {
        //cria o elemento de carregando //component sendo carregado antes para que se consiga exibir o carregamento
        await Lis.createComponent(
            "carregando",
            "html",
            "append",
            ["/components/css/carregando.css"],
            ["/components/js/carregando.js"]
        );

        //cria o component offline
        await Lis.createComponent(
            "offline",
            "html",
            "append",
            ["/components/css/offline.css"],
            ["/components/js/offline.js"]
        );

        //Adiciona os Scripts para o pwa
        PWA();

        //cria os meta dados
        createMeta();

        const includes = JSON.parse(Lis.get("/config/includes.json"));

        //inclui os scripts e styles globais
        incluiScript(includes.stylesGlobais, "css");
        await incluiScript(includes.scriptsGlobais, "js");

        //incluindo arquivos css de cada pagina
        if (Lis.styles) {
            incluiScript(Lis.styles, "css");
        }
        //incluindo scripts js de cada pagina
        if (Lis.scripts) {
            await incluiScript(Lis.scripts, "js");
        }

        //inclui os components na tela
        includes.componentsGlobal.forEach(async (c) => {
            Lis.createComponent(c.component, c.element, c.local, c.css, c.js);
        });

        if (Lis) {
            //aguarda o carregamento das paginas e executa o init
            document.querySelector("body").onload = async () => {
                if (typeof Lis.init === "function") {
                    await Lis.init();
                }
                Lis.carregandoHide();
            };
        }

        document.querySelectorAll("input:not([autocomplete])").forEach((element) => {
            element.setAttribute("autocomplete", "off");
        });

        document.querySelector("html").onerror = (erro) => {
            window.location.href = Lis.getUrl("/error");
        };
    }

    //incluindo funções na Lis ====================================

    /**
     * Função para pegar um objeto de configurações
     * @version 1
     * @access public
     * @param {string} tipo - Tipo de config que deseja receber
     * app - Configurações do app
     * servidor - Url do servidor
     * @return {json, string} - Json com os dados ou uma string
     */
    Lis.getConfig = (tipo) => {
        switch (tipo) {
            case "app":
                return JSON.parse(Lis.get("/config/app.json"));
            case "servidor":
                return JSON.parse(Lis.get("/config/app.json")).server;
            case "iconApp":
                return JSON.parse(Lis.get("/config/manifest.json"))["icons"][0][
                    "src"
                ];
            case "corApp":
                return JSON.parse(Lis.get("/config/manifest.json"))[
                    "theme_color"
                ];
        }
    };

    /**
     * Função para retornar a url completa do sistema
     * @version 1.1.0
     * @access public
     * @param {string} url- url que será montada
     * @returns {string} - url montada
     */
    Lis.getUrl = (url) => {
        if (url.substr(0, 1) == "/") {
            if (url.substr(0, 7) == "/server") {
                return Lis.getConfig("servidor") + url.replace("/server/", "");
            } else {
                let inicioUrl = document.querySelector("#coreJs").src;
                inicioUrl = inicioUrl.replaceAll("/core/index.js", "");
                inicioUrl = inicioUrl.replaceAll(window.location.origin, "");
                return inicioUrl + url;
            }
        } else {
            return url;
        }
    };

    /**
     * Função para fazer um requisição GET
     * @version 3.1.0
     * @access public
     * @author felipe <flpsnocvla@gmail.com>
     * @author Gabriel <gabrielvitorlaurindo@gmail.com>
     * @param {string} url - Url destino da solicitação
     * @param {boolean} assincrona - função assincrona ? - padrão false
     */
    Lis.get = (url, assincrona = false) => {
        if (!navigator.onLine) return localStorage.getItem("GET-" + Lis.getUrl(url));

        try {
            const xhttp = new XMLHttpRequest();
            xhttp.open("GET", Lis.getUrl(url), assincrona);
            xhttp.send();

            if (Lis.getUrl(url).substr(0, 4) != "http") {
                localStorage.setItem(
                    "GET-" + Lis.getUrl(url),
                    xhttp.responseText
                );
            }
            return xhttp.responseText;
        } catch (e) {}

        Lis.offlineShow();
        return undefined;
    };

    /**
     * Função para realizar uma requisição POST
     * @version 2
     * @access public
     * @param {string} url - link para a requisição post
     * @param {obj} dados - dados a serem enviados para o servidor
     * @param {boolean} json - indica se os dados são json ou FormData
     * @param {boolean} assincrona - Função assincrona ?
     * @return {string} resposta do post
     */
    Lis.post = (url, dados, json = true, assincrona = false) => {
        try {
            const xhttp = new XMLHttpRequest();
            xhttp.open("POST", Lis.getUrl(url), assincrona);
            xhttp.send(json ? JSON.stringify(dados) : dados);
            return xhttp.responseText;
        } catch (e) {}

        Lis.offlineShow();
        return undefined;
    };

    /**
     * @version 1.1.1
     * @access public
     * @param {string} component Nome do component a ser colocado
     * @param {string} element local onde o elemento será criado
     * @param {string} local - como será criado o elemento (append, prepend)
     * @param {array} css - array de strings com os nomes dos css dos componentes
     * @param {array} js - array de strings com os nomes dos js dos componentes
     *
     * @return {void} - Função não tem retorno
     */
    Lis.createComponent = (component, element, local, css = [], js = []) => {
        if (Lis.Ncomponents && Lis.Ncomponents.includes(component)) {
            return false;
        }

        switch (component) {
            case "carregando":
            case "offline":
                var elemento = document.createElement(component);
                break;
            default:
                var elemento = document.createElement("section");
                elemento.setAttribute("class", "component-" + component);
        }

        switch (local) {
            case "append":
                document.querySelector(element).append(elemento);
                break;
            case "prepend":
            default:
                document.querySelector(element).prepend(elemento);
        }

        elemento.innerHTML = Lis.get("/components/html/" + component + ".html");
        incluiScript(js, "js");
        incluiScript(css, "css");
        return true;
    };

    /**
     * Função a ser aplicada nos forms, para que consiga realizar o envio de dados
     * @version 1
     * @access public
     * @param {string} element seletor do elemento do form
     * @param {function} before Função a ser executada antes do envio
     * @param {function} after Função a ser executada apos o envio
     */
    Lis.form = (element, before, after) => {
        document.querySelector(element).addEventListener(
            "submit",
            (event) => {
                event.preventDefault();
                if (before() != false) {
                    const data = new FormData(event.target);
                    if (document.querySelector(element).method == "post") {
                        resp = JSON.parse(
                            Lis.post(
                                "/server" +
                                    document
                                        .querySelector(element)
                                        .action.replace(location.origin, ""),
                                data,
                                false
                            )
                        );
                    }
                    after(resp);
                }
            },
            true
        );
    };

    /**
     * Função para adicionar valores ao select
     * @version 1
     * @access public
     * @param {string} element - seletor até o select
     * @param {array} array - array de objetos contendo o value e o texto
     */
    Lis.montaSelect = (element, array) => {
        array.forEach((opt) => {
            let option = document.createElement("option");
            option.setAttribute("value", opt.value ? opt.value : opt.text);
            option.innerHTML = opt.text;
            document.querySelector(element).appendChild(option);
        });
    };

    /**
     * Verifica onde a aplicação está sendo executada
     * @version 1
     * @access public
     * @version 1
     * @access public
     * @return {string} diz onde o aplicativo está rodando
     */
    Lis.rodando = () => {
        const isStandalone = window.matchMedia(
            "(display-mode: standalone)"
        ).matches;
        if (document.referrer.startsWith("android-app://")) {
            return "pwa";
        } else if (navigator.standalone || isStandalone) {
            return "standalone";
        }
        return "browser";
    };

    //iniciando a pagina ===========================================
    init();
} catch (e) {
    console.log(e);
}

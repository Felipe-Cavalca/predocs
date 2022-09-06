try {
    //links a serem incluidos na pagina
    const scriptsGlobais = [
        "/framework/jquery-3.6.0.js", //jquery version 3.6.0
        "/framework/bootstrap-5.1.3-dist/js/bootstrap.js", //bootstrap version 5.1.3
        "/framework/vue.global.js", //vue version 3
        "/js/global/funcoes.js", //funcoes
    ];

    const stylesGlobais = [
        "/css/global/variaveis.css", //arquivo de variaveis css
        "/core/index.css", //arquivo do core css
        "/framework/bootstrap-5.1.3-dist/css/bootstrap.css", //bootstrap version 5.1.3
        "/css/global/variaveisBootstrap.css", //arquivo para substituir variaveis do bootstrap
        "https://fonts.googleapis.com/icon?family=Material+Icons", // google icons
    ];

    const componentsGlobal = [{
        component: "nav",
        element: "body",
        local: "prepend",
        css: ["/components/css/nav.css"],
        js: ["/components/js/nav.js"],
    }];

    //funções ==================================================

    /**
     * Adiciona as tags de script e link a tela
     *
     * @param {array} links - Array de urls que serão importadas
     * @param {string} tipo - tipo de arquivo que será adicionado na tela (css, js)
     * @return {void} - Função não retorna dados
     */
    async function incluiScript(links, tipo) {
        switch (tipo) {
            case "js":
                for (var i = 0; i < links.length; i++) {
                    var script = document.createElement("script");
                    script.setAttribute("src", Lis.getUrl(links[i]));
                    document.querySelector("body").appendChild(script);
                    await new Promise((res) => {
                        script.onload = () => {
                            res(0);
                        }
                    });
                }
                break;
            case "css":
                links.forEach((url) => {
                    var style = document.createElement("link");
                    style.setAttribute("rel", "stylesheet");
                    style.setAttribute("href", Lis.getUrl(url));
                    document.querySelector("head").appendChild(style);
                });
                break;
        }
    }

    /**
     * Função para adicionar os metadados do arquivo
     *
     * @return {void} - Função não tem retorno
     */
    function createMeta() {
        //os elementos meta
        var meta = document.createElement("meta");
        meta.setAttribute("name", "viewport");
        meta.setAttribute("content", "width=device-width, initial-scale=1.0");
        document.querySelector("head").prepend(meta);

        var meta = document.createElement("meta");
        meta.setAttribute("http-equiv", "X-UA-Compatible");
        meta.setAttribute("content", "IE=edge");
        document.querySelector("head").prepend(meta);

        var meta = document.createElement("meta");
        meta.setAttribute("charset", "utf-8");
        document.querySelector("head").prepend(meta);

        var html = document.querySelector("html");
        html.setAttribute("lang", "pt-br");
    }

    /**
     * Função para transformar a pagina web em pagina PWA
     * @return {void}
     */
    function PWA() {
        var link = document.createElement("link");
        link.setAttribute("rel", "manifest");
        link.setAttribute("href", "data:json;base64," + btoa(Lis.getConfig("manifest")));
        document.querySelector("head").prepend(link);

        if ("serviceWorker" in navigator) {
            navigator.serviceWorker.register(Lis.getUrl("/sw.js"));
            var deferredPrompt; // Inicialize o deferredPrompt para posteriormente mostrar o prompt de instalação do navegador.
            window.addEventListener("beforeinstallprompt", e => {
                deferredPrompt = e;
                if (localStorage.getItem("exibeMsgInstall") == null || localStorage.getItem("exibeMsgInstall") == "true") {
                    document.querySelector("html").addEventListener("click", chamaInstallApp);
                }
            });
        }

        /**
         * Função para exibir a tela de instação do app
         * OBS: Essa função só pode ser executada através de um gesto do usuario
         * @return {void}
        */
        Lis.installApp = () => {
            deferredPrompt.prompt();
            // Aguarda a resposta do usuario
            deferredPrompt.userChoice.then(escolha => {
                if (escolha.outcome === 'accepted') {
                    // Usuário aceitou instalar o app
                } else {
                    // Usuário recusou instalar
                }

                //zera as variaveis após exibição do botão para instalar
                document.querySelector("html").removeEventListener("click", chamaInstallApp);
                localStorage.setItem("exibeMsgInstall", false);
                deferredPrompt = null;
            });
        }

        /**
         * Função para abrir a tela de instação do app fornecida pelo navegador
         */
        function chamaInstallApp() {
            Lis.installApp();
        }
    }

    /**
     * Função chamada para iniciar o framework
     *
     * @return {void} - Função não tem retorno
     */
    async function init() {

        //cria o elemento de carregando //component sendo carregado antes para que se consiga exibir o carregamento
        await Lis.createComponent("carregando", "html", "append", ["/components/css/carregando.css"], ["/components/js/carregando.js"]);

        //Adiciona os Scripts para o pwa
        PWA();

        //cria os meta dados
        createMeta();

        //inclui os scripts e styles globais
        incluiScript(stylesGlobais, "css");
        await incluiScript(scriptsGlobais, "js");

        //incluindo arquivos css do usuario
        if (Lis.styles) {
            incluiScript(Lis.styles, "css");
        }
        //incluindo scripts dos usuarios
        if (Lis.scripts) {
            await incluiScript(Lis.scripts, "js");
        }

        //inclui os components na tela
        componentsGlobal.forEach(async c => {
            Lis.createComponent(c.component, c.element, c.local, c.css, c.js);
        });

        if (Lis) {
            //aguarda o carregamento das paginas e executa o init
            document.querySelector("body").onload = async function () {
                if (typeof Lis.init === "function") {
                    await Lis.init();
                }
                Lis.carregandoHide();
            };
        }

        document.querySelector("html").onerror = function (erro) {
            window.location.href = Lis.getUrl("/error");
        };
    }

    //incluindo funções na Lis ====================================

    /**
     * Função para pegar um objeto de configurações
     * @param {string} tipo - Tipo de config que deseja receber
     * app - Configurações do app
     * servidor - Url do servidor
     * manifest - arquivo de manifesto para o app
     * @return {json, string} - Json com os dados
     */
    Lis.getConfig = (tipo) => {
        switch (tipo) {
            case "app":
                return JSON.parse(Lis.get("/config/app.json"));
            case "servidor":
                return JSON.parse(Lis.get("/config/app.json")).server;
            case "manifest":
                let manifest = JSON.parse(Lis.get("/model/manifest.json"));
                let config = JSON.parse(Lis.get("/config/app.json"));
                let imagens = JSON.parse(Lis.get("/config/imagens.json"));
                manifest.name = config.nome;
                manifest.short_name = config.nome_cuto;
                manifest.description = config.descricao;
                manifest.display = config.display;
                manifest.start_url = config.server;
                manifest.shortcuts[0].url = config.server;
                manifest.icons = imagens.icons;
                manifest.screenshots = imagens.icons;
                return JSON.stringify(manifest);
        }
    }

    /**
     * Funçãom para retornar a url completa do sistema
     *
     * @param {string} url- url que será montada
     * @returns {string} - url montada
     */
    Lis.getUrl = (url) => {
        if (url.substr(0, 1) == "/") {
            if (url.substr(0, 7) == "/server") {
                return Lis.getConfig("servidor") + url;
            } else {
                return document.querySelector("#coreJs").src.replaceAll("/core/index.js", "") + url;
            }
        } else {
            return url;
        }
    };

    /**
     * Função para fazer um requisição GET
     *
     * @param {string} url - Url destino da solicitação
     * @param {boolean} assincrona - função assincrona ? - padrão false
     */
    Lis.get = function (url, assincrona = false) {
        var xhttp = new XMLHttpRequest();
        xhttp.open("GET", Lis.getUrl(url), assincrona);
        xhttp.send();
        return xhttp.responseText;
    };

    /**
     * Função para realizar um get
     * @param {string} url - link para a requisição post
     * @param {obj} dados - dados a serem enviados para o servidor
     * @param {obj} header - cabeçalho da requisição
     * @return resposta do post
     */
    Lis.post = async function (url, dados, headers = {}) {
        const data = await fetch(Lis.getUrl(url), {
            body: dados,
            method: "POST",
            headers: headers,
            data: dados,
        });

        var texto = await data.text();

        return texto;
    };

    /**
     *
     * @param {string} component Nome do component a ser colocado
     * @param {string} element local onde o elemento será criado
     * @param {string} local - como será criado o elemento (append, prepend)
     * @param {array} css - array de strings com os nomes dos css dos componentes
     * @param {array} js - array de strings com os nomes dos js dos componentes
     *
     * @return {void} - Função não tem retorno
     */
    Lis.createComponent = function (component, element, local, css = [], js = []) {

        if (Lis.Ncomponents && Lis.Ncomponents.includes(component)) {
            return false;
        }

        if (component == "carregando") { //elemento carregando recebe classes e ids para que de o efeito certo
            var elemento = document.createElement(component);
        } else {
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

        elemento.innerHTML = Lis.get("/components/html/" + component + ".html", false);
        incluiScript(js, "js");
        incluiScript(css, "css");
        return true;
    };

    /**
     * Função a ser aplicada nos forms, para que consiga
     * @param {string} element seletor do elemento do form
     * @param {function} before Função a ser executada antes do envio
     * @param {function} after Função a ser executada apos o envio
     */
    Lis.form = (element, before, after) => {
        document.querySelector(element).addEventListener(
            "submit",
            async function (event) {
                event.preventDefault();
                if (await before() != false) {
                    const data = new FormData(event.target);
                    var url = Lis.getUrl(Lis.getConfig("server") + "/server/" + document.querySelector(element).action.replace(location.origin, "").replace("/", ""));
                    var resp = {};
                    if (document.querySelector(element).method == "post") {
                        resp = JSON.parse(await Lis.post(url, data));
                    }
                    after(resp);
                }
            },
            true
        );
    };

    /**
     * Função para adicionar valores ao select
     * @param {string} element - seletor até o select
     * @param {array} array - array de objetos contendo o value e o texto
     */
    Lis.montaSelect = (element, array) => {
        array.forEach(opt => {
            let option = document.createElement('option');
            option.setAttribute('value', opt.value ? opt.value : opt.text);
            option.innerHTML = opt.text;
            document.querySelector(element).appendChild(option);
        });

    };

    //iniciando a pagina ===========================================
    init();
} catch (e) {
    console.log(e);
}

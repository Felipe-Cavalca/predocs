try {
    //define as variaveis globais
    var VarsGlobal = {
        url: 'http://localhost'
    };

    //links a serem incluidos na pagina
    const scriptsGlobais = [
        "/framework/jquery-3.6.0.js", //jquery version 3.6.0
        "/framework/bootstrap-5.1.3-dist/js/bootstrap.js", //bootstrap version 5.1.3
        "/framework/vue.global.js", //vue version 3
        "/js/global/funcoes.js", //funcoes
    ];

    const stylesGlobais = [
        "/css/global/variaveis.css", //arquivo de variaveis css
        "/coreCss", //arquivo do core css
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
                    script.setAttribute("src", validaUrl(links[i]));
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
                    style.setAttribute("href", validaUrl(url));
                    document.querySelector("head").appendChild(style);
                });
                break;
        }
    }

    /**
     * Função para pegar a url correta
     *
     * @param {string} url - recebe a url
     * @return {string} - url a ser usada
     */
    function validaUrl(url) {
        if (url.substr(0, 1) == "/") {
            return VarsGlobal.url + url;
        }

        return url;
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
     * Função chamada para iniciar o framework
     *
     * @return {void} - Função não tem retorno
     */
    async function init() {
        //seta as variavies globais
        VarsGlobal = await JSON.parse(Lis.get(Lis.validaUrl(document.querySelector("#coreJs").src.replaceAll("coreJs", "varsApp")), false));

        //cria o elemento de carregando //component sendo carregado antes para que se consiga exibir o carregamento
        await Lis.createComponent("carregando", "html", "append", ["/components/css/carregando.css"], ["/components/js/carregando.js"]);

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
            document.querySelector("body").onload = async function() {
                if (typeof Lis.init === "function") {
                    await Lis.init();
                }
                Lis.carregandoHide();
            };
        }

        document.querySelector("html").onerror = function(erro) {
            window.location.href = VarsGlobal["url"] + "/error";
        };
    }

    //incluindo funções na Lis ====================================

    /**
     * Funçãom para retornar a url completa do sistema
     *
     * @param {string} url- url que será montada
     * @returns {string} - url mon
     */
    Lis.validaUrl = (url) => {
        return validaUrl(url);
    };

    /**
     * Função para fazer um requisição GET
     *
     * @param {string} url - Url destino da solicitação
     * @param {boolean} assincrona - função assincrona ? - padrão false
     */
    Lis.get = function(url, assincrona = false) {
        var xhttp = new XMLHttpRequest();
        xhttp.open("GET", url, assincrona);
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
    Lis.post = async function(url, dados, headers = {}) {
        const data = await fetch(url, {
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
    Lis.createComponent = function(component, element, local, css = [], js = []) {

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

        elemento.innerHTML = Lis.get(validaUrl("/components/html/" + component + ".html"), false);
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
            async function(event) {
                event.preventDefault();
                if (await before() != false) {
                    const data = new FormData(event.target);
                    var url = validaUrl("/server/" + document.querySelector(element).action.replace(location.origin, "").replace("/", ""));
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
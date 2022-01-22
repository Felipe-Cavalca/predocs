//dominio da aplicação
const dominio = "http://localhost/lis/";

//caminhos para as pastas da aplicação
const URLS = {
    'dominio' : dominio,
    "dominioServer" : dominio + "server/",
    "dominioWeb" : dominio + "web/",
    "dominioCore" : dominio + "web/core/",
    "dominioCss" : dominio + "web/css/",
    "dominioFramework" : dominio + "web/core/frameworks/",
    "dominioJs" : dominio + "web/js/",
    "dominioPages" : dominio + "pages/",
    "dominioJsGlobal" : dominio + "web/js/global/",
}

//links a serem incluidos na pagina
export const linksFramework = [
    URLS.dominioFramework + "jquery-3.6.0.js",
    URLS.dominioFramework + "materialize/js/materialize.js",
    URLS.dominioFramework + "vue.global.js",
];


//funções ==================================================

/**
 *
 * @param {arr} urls - array das urls
 * Adiciona os scripts js a pagina
 */
function incluiScript(links) {

    //incluindo links dos frameworks
    if(links){
        links.forEach(url => {
            var script = document.createElement("script");
            script.setAttribute('src', url);
            document.querySelector("body").appendChild(script);
        });
    }

    //incluindo scripts dos usuarios
    if(Lis.scripts){
        Lis.scripts.forEach(url => {
            var script = document.createElement("script");
            script.setAttribute('src', substituiCaminho(url));
            document.querySelector("body").appendChild(script);
        });
    }

    //incluindo arquivos css do usuario
    if(Lis.styles){
        Lis.styles.forEach(url => {
            var style = document.createElement("link");
            style.setAttribute('rel', "stylesheet");
            style.setAttribute('href', substituiCaminho(url));
            document.querySelector("head").appendChild(style);
        });
    }

    if (Lis && typeof Lis.init === 'function') {
        //aguarda o carregamento das paginas e executa o init
        //necessario alterar para uma função que detecte o carregamento
        setTimeout(function () {
            Lis.init();
        }, 1000);
    }

}

/**
 *
 * @param {string} url String contendo a url para completar
 * @returns url completa
 */
function substituiCaminho(url){
    url = url.replace("{{js}}", URLS.dominioJs);
    url = url.replace("{{css}}", URLS.dominioCss);
    url = url.replace("{{server}}", URLS.dominioServer);
    return url;
}

//incluindo variaveis na Lis ==================================
Lis.URLS = URLS;

//incluindo funções na Lis ====================================

/**
 *
 * @param {string} url Url destino da solicitação
 * @param {boolean} assincrona função assincrona ? - padrão false
 */
Lis.get = function (url, assincrona = false){
    var xhttp = new XMLHttpRequest();
    xhttp.open("GET", substituiCaminho(url), assincrona);
    xhttp.send();
    return xhttp.responseText;
}

Lis.post = function (url, dados, assincrona = false){
    var xhttp = new XMLHttpRequest();
    xhttp.open("POST", substituiCaminho(url), assincrona);
    xhttp.setRequestHeader('Content-Type', 'application/json');
    xhttp.send(JSON.stringify(dados));
    return xhttp.responseText;
}

//iniciando a pagina ===========================================
incluiScript(linksFramework, URLS);
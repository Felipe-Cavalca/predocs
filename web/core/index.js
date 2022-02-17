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
    "dominioImg" : dominio + "web/img/",
    "dominioComponents" : dominio + "web/components/",
    "dominioErros" : dominio + "web/error/"
}

//links a serem incluidos na pagina
const scriptsGlobais = [
    URLS.dominioFramework + "jquery-3.6.0.js",
    URLS.dominioFramework + "materialize/js/materialize.js",
    URLS.dominioFramework + "vue.global.js",
    URLS.dominioJs + "global/variaveis.js",
    URLS.dominioJs + "global/funcoes.js",
];

const stylesGlobais = [
    URLS.dominioFramework + "materialize/css/materialize.css",
    "https://fonts.googleapis.com/icon?family=Material+Icons"
]


//funções ==================================================

/**
 *
 * @param {arr} urls - array das urls
 * Adiciona as tags de script e link a tela
 */
function incluiScript(links, tipo) {

    if(tipo == 'css'){
        links.forEach(url => {
            var style = document.createElement("link");
            style.setAttribute('rel', "stylesheet");
            style.setAttribute('href', substituiCaminho(url));
            document.querySelector("head").appendChild(style);
        });
    }else{
        links.forEach(url => {
            var script = document.createElement("script");
            script.setAttribute('src', url);
            document.querySelector("body").appendChild(script);
        });
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
    url = url.replace("{{img}}", URLS.dominioImg);
    return url;
}

/**
 * Função para criar o elemento "carregando" na tela
 */
function criarCarregando(){
    //cria o elemento de carregando
    var carregando = document.createElement("carregando");
    carregando.setAttribute('class', 'scale-transition scale-in');
    document.querySelector("html").appendChild(carregando);

    //insere a imagem no mesmo
    var img = document.createElement("img");
    img.setAttribute('class', 'materialboxed');
    img.setAttribute('src', substituiCaminho("{{img}}carregando.gif"));
    //coloca a imagem dentro do carregando
    document.querySelector("carregando").appendChild(img);

    //some com o body
    var body = document.querySelector('body');
    body.setAttribute('class', 'scale-transition scale-out');
    body.setAttribute('style', 'display: none;');
}

function createMeta(){
    //os elementos meta
    var meta = document.createElement("meta");
    meta.setAttribute('name', 'viewport');
    meta.setAttribute('content', 'width=device-width, initial-scale=1.0');
    document.querySelector("head").prepend(meta);

    var meta = document.createElement("meta");
    meta.setAttribute('http-equiv', 'X-UA-Compatible');
    meta.setAttribute('content', 'IE=edge');
    document.querySelector("head").prepend(meta);

    var meta = document.createElement("meta");
    meta.setAttribute('charset', 'utf-8');
    document.querySelector("head").prepend(meta);

    var html = document.querySelector('html');
    html.setAttribute('lang', 'pt-br');
}

function init(){
    criarCarregando();

    incluiScript([URLS.dominioCore+"index.css"], 'css');

    document.querySelector('carregando img').onload = function () {
        if(document.querySelector("nav") == null && Lis.nav != false){
            Lis.createComponent('nav', "body");
        }

        createMeta();

        incluiScript(stylesGlobais, 'css');
        incluiScript(scriptsGlobais, 'js');

        //incluindo arquivos css do usuario
        if(Lis.styles){
            incluiScript(stylesGlobais, 'css');
        }
        //incluindo scripts dos usuarios
        if(Lis.scripts){
            incluiScript(scriptsGlobais, 'js');
        }

        if (Lis && typeof Lis.init === 'function') {
            //aguarda o carregamento das paginas e executa o init
            document.querySelector('body').onload = function () {
                Lis.init();

                if(Lis.nav != false){
                    //valida se o vue existe
                    if(typeof window.Vue !== 'undefined'){
                        window.initVueDefault(document.querySelector('nav'));
                    }
                }

                setTimeout(function () {
                    //apos o carregamento some a tela de carregamento
                    Lis.carregandoHide();
                }, 300);
            }
        }
    }

    document.querySelector('body').onbeforeunload = function (a) {
        alert('onbeforeunload');
        alert(a);
    }

    document.querySelector('body').onunload = function (a) {
        alert('onunload');
        alert(a);
    }

    document.querySelector('body').onerror = function (erro) {
        if(window.location.href != URLS.dominioErros+"700.html"){
            window.location.href = URLS.dominioErros+"700.html";
        }
    }
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

/**
 *
 * @param {string} url link para a requisição post
 * @param {obj} dados dados a serem enviados para o servidor
 * @param {boolean} assincrona função assincrona ?
 * @returns resposta do post
 */
Lis.post = function (url, dados, assincrona = false){
    var xhttp = new XMLHttpRequest();
    xhttp.open("POST", substituiCaminho(url), assincrona);
    xhttp.setRequestHeader('Content-Type', 'application/json');
    xhttp.send(JSON.stringify(dados));
    return xhttp.responseText;
}

/**
 * Esconde a tela de carregando
 */
Lis.carregandoHide = function (){
    const carregando = document.querySelector('carregando');
    const pagina = document.querySelector('body')

    carregando.classList.remove("scale-in");
    carregando.classList.add("scale-out");
    setTimeout(function () {
        carregando.style.display = "none";
        pagina.style.display = null;
        setTimeout(function () {
            pagina.classList.remove("scale-out");
            pagina.classList.add("scale-in");
        }, 200);
    }, 500);
}

/**
 * Exibe a tela de carregando
 */
Lis.carregandoShow = function (){
    const carregando = document.querySelector('carregando');
    const pagina = document.querySelector('body');

    pagina.classList.remove("scale-in");
    pagina.classList.add("scale-out");
    setTimeout(function () {
        pagina.style.display = "none";
        carregando.style.display = null;
        setTimeout(function () {
            carregando.classList.remove("scale-out");
            carregando.classList.add("scale-in");
        }, 200);
    }, 500);
}

/**
 *
 * @param {string} component Nome do component a ser colocado
 * @param {string} element local onde o elemento será criado
 */
Lis.createComponent = function (component, element){
    var elemento = document.createElement(component);
    document.querySelector(element).prepend(elemento);
    elemento.innerHTML = Lis.get(URLS.dominioComponents + component + '.html', false);
}

//iniciando a pagina ===========================================
init();
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
    'dominioComponents' : dominio + "web/components/"
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
    URLS.dominioCore+"index.css",
    URLS.dominioFramework + "materialize/css/materialize.css",
    "https://fonts.googleapis.com/icon?family=Material+Icons"
]


//funções ==================================================

/**
 *
 * @param {arr} urls - array das urls
 * Adiciona as tags de script e link a tela
 */
function incluiScript(scripts, styles) {

    if(styles){
        styles.forEach(url => {
            var style = document.createElement("link");
            style.setAttribute('rel', "stylesheet");
            style.setAttribute('href', substituiCaminho(url));
            document.querySelector("head").appendChild(style);
        });
    }

    //incluindo links dos frameworks
    if(scripts){
        scripts.forEach(url => {
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
            setTimeout(function () {
                //apos o carregamento some a tela de carregamento
                Lis.carregandoHide();
            }, 300);
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
    const pagina = document.querySelector('body')

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
criarCarregando();
Lis.createComponent('nav', "body");
incluiScript(scriptsGlobais, stylesGlobais);
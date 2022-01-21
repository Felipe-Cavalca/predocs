const dominio = "http://localhost/lis/"; //dominio da aplicação

//links a serem incluidos na pagina
export const linksFramework = [
    URLS.dominioFramework + "jquery-3.6.0.js",
    URLS.dominioFramework + "materialize/js/materialize.js",
    URLS.dominioFramework + "vue.global.js",
    URLS.dominioJsGlobal + "variaveis.js",
    URLS.dominioJsGlobal + "funcoes.js",
];

export const URLS = {
    'dominio' : dominio,
    "dominioWeb" : dominio + "web/",
    "dominioCore" : dominio + "web/core/",
    "dominioCss" : dominio + "web/css/",
    "dominioFramework" : dominio + "web/core/frameworks/",
    "dominioJs" : dominio + "web/js/",
    "dominioPages" : dominio + "pages/",
    "dominioJsGlobal" : dominio + "web/js/global/",
}

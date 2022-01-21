/**
 *
 * @param {arr} urls - array das urls
 * Adiciona os scripts js a pagina
 */
export function incluiScript(links, URLS) {
    //incluindo links dos frameworks
    links.forEach(url => {
        var script = document.createElement("script");
        script.setAttribute('src', url);
        document.querySelector("body").appendChild(script);
    });

    //incluindo scripts dos usuarios
    Lis.scripts.forEach(url => {
        url = url.replace("{{js}}", URLS.dominioJs);
        var script = document.createElement("script");
        script.setAttribute('src', url);
        document.querySelector("body").appendChild(script);
    });

    //incluindo arquivos css do usuario
    Lis.styles.forEach(url => {
        url = url.replace("{{css}}", URLS.dominioCss);
        var style = document.createElement("link");
        style.setAttribute('rel', "stylesheet");
        style.setAttribute('href', url);
        document.querySelector("head").appendChild(style);
    });

    //aguarda o carregamento das paginas e executa o init
    //necessario alterar para uma função que detexte o carregamento
    setTimeout(function () {
        Lis.init();
    }, 1000);
}

//incluindo funções na Lis
Lis.get = function (url, sincrono = false){
    var xhttp = new XMLHttpRequest();
    xhttp.open("GET", url, sincrono);
    xhttp.send();//A execução do script pára aqui até a requisição retornar do servidor

    console.log(xhttp.responseText);
}
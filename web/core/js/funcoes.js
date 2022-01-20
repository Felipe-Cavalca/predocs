/**
 *
 * @param {arr} urls - array das urls
 * Adiciona os scripts js a pagina
 */
export function incluiScript(links, URLS) {
    //incluindo links dos frameworks
    links.forEach(url => {
        var script = document.createElement("script")
        script.setAttribute('src', url)
        document.querySelector("body").appendChild(script)
    });

    //incluindo scripts dos usuarios
    Lis.scripts.forEach(url => {
        url = url.replace("{{js}}", URLS.dominioJs);
        var script = document.createElement("script")
        script.setAttribute('src', url)
        document.querySelector("body").appendChild(script)
    });

    //aguarda o carregamento das paginas e executa o init
    //necessario alterar para uma função que detexte o carregamento
    setTimeout(function () {
        Lis.init();
    }, 1000);
}
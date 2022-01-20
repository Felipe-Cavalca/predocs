/**
 *
 * @param {arr} urls - array das urls
 * Adiciona os scripts js a pagina
 */
export function incluiScript(urls, domain) {
    //incluindo urls dos frameworks
    urls.forEach(url => {
        var script = document.createElement("script")
        script.setAttribute('src', url)
        document.querySelector("body").appendChild(script)
    });

    //incluindo scripts dos usuarios
    Lis.scripts.forEach(url => {
        url = url.replace("{{domain}}", domain + "web/");
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
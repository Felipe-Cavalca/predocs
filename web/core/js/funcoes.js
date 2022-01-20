/**
 *
 * @param {arr} urls - array das urls
 * Adiciona os scripts js a pagina
 */
export function incluiScript(urls, domain) {
    urls.forEach(url => {
        var script = document.createElement("script")
        script.setAttribute('src', url)
        document.querySelector("body").appendChild(script)
    });

    Scripts.forEach(url => {
        url = url.replace("{{domain}}", domain);
        var script = document.createElement("script")
        script.setAttribute('src', url)
        document.querySelector("body").appendChild(script)
    });

    setTimeout(function() {
        init();
    }, 1000);
}
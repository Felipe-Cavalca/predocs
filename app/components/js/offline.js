//some com o body
var body = document.querySelector("body");
body.setAttribute("class", "scale-transition scale-out");
body.setAttribute("style", "display: none;");

var offline = document.querySelector("offline");
offline.setAttribute("class", "scale-transition scale-in");

/**
 * Esconde a tela de offline
 *
 * @return {void} - Função não tem retorno
 */
Lis.offlineHide = function () {
    Lis.carregandoShow();

    const offline = document.querySelector("offline");
    const pagina = document.querySelector("body");

    offline.classList.remove("scale-in");
    offline.classList.add("scale-out");
    setTimeout(function () {
        offline.style.display = "none";
        pagina.style.display = null;
        setTimeout(function () {
            pagina.classList.remove("scale-out");
            pagina.classList.add("scale-in");
        }, 200);
    }, 500);

    Lis.carregandoHide();
};

/**
 * Exibe a tela de offline
 *
 * @return {void} - Função não tem retorno
 */
Lis.offlineShow = function () {
    Lis.carregandoHide();

    const offline = document.querySelector("offline");
    const pagina = document.querySelector("body");

    pagina.classList.remove("scale-in");
    pagina.classList.add("scale-out");
    setTimeout(function () {
        pagina.style.display = "none";
        offline.style.display = null;
        setTimeout(function () {
            offline.classList.remove("scale-out");
            offline.classList.add("scale-in");
        }, 200);
    }, 500);
};

Lis.offlineHide();

//some com o body
var body = document.querySelector("body");
body.setAttribute("class", "scale-transition scale-out");
body.setAttribute("style", "display: none;");

var carregando = document.querySelector("carregando");
carregando.setAttribute("class", "scale-transition scale-in");

//Caso o component tenha uma imagem, setar o caminho abaixo
var img = document.querySelector("carregando img");
var urlImg = Lis.getUrl("/midia/global/gif_carregando.gif");
img.setAttribute("src", urlImg);

/**
 * Esconde a tela de carregando
 *
 * @return {void} - Função não tem retorno
 */
Lis.carregandoHide = function () {
    const carregando = document.querySelector("carregando");
    const pagina = document.querySelector("body");

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
};

/**
 * Exibe a tela de carregando
 *
 * @return {void} - Função não tem retorno
 */
Lis.carregandoShow = function () {
    const carregando = document.querySelector("carregando");
    const pagina = document.querySelector("body");

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
};

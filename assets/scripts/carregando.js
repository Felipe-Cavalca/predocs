/**
 * Função para sumir com a tela de carregando
 */
function carregandoHide() {
    const carregando = document.querySelector("carregando");
    const pagina = document.querySelector("#body");

    carregando.classList.remove("scale-in");
    carregando.classList.add("scale-out");
    setTimeout(function () {
        carregando.style.display = "none";
        pagina.style.display = null;
        setTimeout(function () {
            pagina.classList.remove("scale-out");
            pagina.classList.add("scale-in");
            return true;
        }, 200);
    }, 500);
}

/**
 * Função para exibir a tela de carregando
 */
function carregandoShow() {
    const carregando = document.querySelector("carregando");
    const pagina = document.querySelector("#body");

    pagina.classList.remove("scale-in");
    pagina.classList.add("scale-out");
    setTimeout(function () {
        pagina.style.display = "none";
        carregando.style.display = null;
        setTimeout(function () {
            carregando.classList.remove("scale-out");
            carregando.classList.add("scale-in");
            return true;
        }, 200);
    }, 500);
}

//seta a variavel do tempo do intervalo
var idIntervalAddLoad = 0;

//executa o 1 addLoad
addLoad();

function addLoad() {
    document.querySelector("iframe").contentWindow.addEventListener("load", function (event) {
        clearInterval(idIntervalAddLoad);
        idIntervalAddLoad = 0;
        setTimeout(function () {
            carregandoHide();
            addUnload();
        }, 2000);
    });
}

function addUnload() {
    document.querySelector("iframe").contentWindow.addEventListener("beforeunload", function (event) {
        carregandoShow();
        if (idIntervalAddLoad == 0) {
            idIntervalAddLoad = setInterval(addLoad, 1);
        }
    });
}

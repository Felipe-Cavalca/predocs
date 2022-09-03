if ("serviceWorker" in navigator) {
    navigator.serviceWorker.register(Lis.getUrl("/sw.js"))
        .then(function () {
            // service worker criado
        })
        .catch(function () {
            // erro ao gerar o service worker
        });

    // Inicialize o deferredPrompt para posteriormente mostrar o prompt de instalação do navegador.
    var deferredPrompt;

    window.addEventListener("beforeinstallprompt", (e) => {
        // Impede que o mini-infobar apareça em mobile
        // e.preventDefault();
        // Guarda evento para que possa ser disparado depois.
        deferredPrompt = e;
        // Opcionalmente, enviar eventos de analytics que promo de instalação PWA foi mostrado.
        console.log(`'beforeinstallprompt' event was fired.`);

        btnInstall = document.querySelector(".BtnInstallPWA");
        if (btnInstall) {
            btnInstall.addEventListener('click', async () => {
                console.log("click");
                // Mostra prompt de instalação
                deferredPrompt.prompt();
                // Usamos o prompt e não podemos usar de novo; jogue fora
                deferredPrompt = null;
            });
        }
    });

}

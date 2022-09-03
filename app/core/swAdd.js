if ("serviceWorker" in navigator) {
    navigator.serviceWorker.register(Lis.getUrl("/sw.js"))
        .then(function () { })
        .catch(function () { });

    // Inicialize o deferredPrompt para posteriormente mostrar o prompt de instalação do navegador.
    var deferredPrompt;

    window.addEventListener("beforeinstallprompt", (e) => {
        // Guarda evento para que possa ser disparado depois.
        deferredPrompt = e;
    });

    function installApp() {
        var elem = document.getElementById("msg");
        elem.innerHTML = "Foi";

        // Show the prompt
        deferredPrompt.prompt();
        // Wait for the user to respond to the prompt
        deferredPrompt.userChoice
            .then((choiceResult) => {
                // if (choiceResult.outcome === 'accepted') {
                    // console.log('PWA setup accepted');
                    // hide our user interface that shows our A2HS button
                // } else {
                    // console.log('PWA setup rejected');
                // }
                deferredPrompt = null;
            });
    }

}

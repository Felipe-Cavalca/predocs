class Offline extends Predocs {
    constructor() {
        super();

        this.offline = document.querySelector("offline");
        this.pagina = document.querySelector("body");

        this._showBody();
        this._hideOffline();
    }

    _hideBody() {
        this.pagina.setAttribute("class", "scale-transition scale-out");
        this.offline.style.display = "none";
    }

    _showBody() {
        this.pagina.setAttribute("class", "scale-transition scale-in");
        this.offline.style.display = null;
    }

    _hideOffline() {
        this.offline.setAttribute("class", "scale-transition scale-out");
        this.offline.style.display = "none";
    }

    _showOffline() {
        this.offline.setAttribute("class", "scale-transition scale-in");
        this.offline.style.display = null;
    }

    hide() {
        this._hideOffline();
        setTimeout(() => {
            this._showBody();
        }, 500);
    }

    show() {
        this._hideBody();
        setTimeout(() => {
            this._showOffline();
        }, 500);
    }
}

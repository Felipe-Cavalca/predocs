class Carregando extends Predocs {
    constructor() {
        super();

        this.body = document.querySelector("body");
        this.carregando = document.querySelector("carregando");
        this.img = document.querySelector("carregando img");

        this._initBody();
        this._initCarregando();
        this._setImage();
    }

    _initBody() {
        this.body.classList.add("scale-transition");
        this.body.style.display = "none";
    }

    _initCarregando() {
        this.carregando.classList.add("scale-transition");
    }

    _setImage() {
        this.img.src =
            this.getUrl("/midia/global/gif_carregando.gif") ||
            "/midia/global/gif_carregando.gif";
    }

    hide() {
        this.carregando.classList.remove("scale-in");
        this.carregando.classList.add("scale-out");

        setTimeout(() => {
            this.carregando.style.display = "none";
            this.body.style.display = null;
            setTimeout(() => {
                this.body.classList.remove("scale-out");
                this.body.classList.add("scale-in");
            }, 200);
        }, 500);
    }

    show() {
        this.body.classList.remove("scale-in");
        this.body.classList.add("scale-out");

        setTimeout(() => {
            this.body.style.display = "none";
            this.carregando.style.display = null;
            setTimeout(() => {
                this.carregando.classList.remove("scale-out");
                this.carregando.classList.add("scale-in");
            }, 200);
        }, 200);
    }
}

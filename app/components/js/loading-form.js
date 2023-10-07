class LoadingForm {

    formElement;
    loadingElement;
    successElement;
    errorElement;
    btnVoltar;

    constructor(formSelector) {
        this.formElement = document.querySelector(formSelector);
        this.loadingElement = document.querySelector(formSelector + " + .component-loading-form .loading");
        this.successElement = document.querySelector(formSelector + " + .component-loading-form .sucesso");
        this.errorElement = document.querySelector(formSelector + " + .component-loading-form .erro");
        this.btnVoltar = document.querySelector(formSelector + " + .component-loading-form .btnVoltar");

        this.btnVoltar.addEventListener("click", this.onClickBtnVoltar.bind(this));
    }

    onClickBtnVoltar() {
        this.showForm();
    }

    showLoading() {
        this.formElement.style.display = "none";
        this.loadingElement.style.display = "block";
        this.btnVoltar.style.display = "none";
        return true;
    }

    showSuccess(resposta = []) {
        this.successElement.style.display = "block";
        this.loadingElement.style.display = "none";
        this.btnVoltar.style.display = "block";
        if (resposta[0]) {
            this.successElement.textContent = resposta[0];
        }
        if (resposta[1]) {
            this.successElement.style.backgroundColor = resposta[1] ?? "#d1e7dd";
        }
    }

    showError() {
        this.errorElement.style.display = "block";
        this.loadingElement.style.display = "none";
        this.btnVoltar.style.display = "block";
    }

    showForm() {
        this.formElement.style.display = "block";
        this.loadingElement.style.display = "none";
        this.successElement.style.display = "none";
        this.errorElement.style.display = "none";
        this.btnVoltar.style.display = "none";
    }
}
class __nameComponent__ extends HTMLElement {
    connectedCallback() {
        this.attachShadow({ mode: 'open' });
        this.shadowRoot.innerHTML = `__html__`;
    }
}

customElements.define("__nameElement__", __nameComponent__);

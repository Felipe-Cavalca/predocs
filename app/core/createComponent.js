class __nameComponent__ extends HTMLElement {
    connectedCallback() {
        this.attachShadow({ mode: 'open' });
        this.shadowRoot.innerHTML = `
        <style>
        __css__
        </style>
        __html__
        `;
        __script__
    }
}

customElements.define("__nameElement__", __nameComponent__);

class __nameComponent__ extends HTMLElement {
    connectedCallback() {
        this.attachShadow({ mode: 'open' });
        this.shadowRoot.innerHTML = `__html__`;

        let script = document.createElement('script');
        script.textContent = `__script__`;
        this.shadowRoot.appendChild(script);
    }
}

customElements.define("__nameElement__", __nameComponent__);

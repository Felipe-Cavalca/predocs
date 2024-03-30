// Classe pra menipulação de elementos do dom
class DomPredocs {

    get head() {
        return document.head;
    }

    get body() {
        return document.body;
    }

    set meta(valor) {
        let elem = this.createElement("meta", valor);
        this.insertBefore(elem, this.head);
    }

    set link(valor) {
        let elem = this.createElement("link", valor);
        this.insertBefore(elem, this.head);
    }

    getAttribute(elem, attr) {
        return elem.getAttribute(attr);
    }

    getElement(selector) {
        let elements = document.querySelectorAll(selector);
        if (elements.length === 1) {
            return elements[0];
        }
        return elements;
    }

    setHtml(elem, html) {
        elem.innerHTML = html;
    }

    disableAutoComplete() {
        Array.from(
            document.querySelectorAll("input:not([autocomplete])")
        ).forEach((element) => {
            element = this.addAtributes(element, {
                autocomplete: "off",
            });
        });
    }

    insertBefore(newNode, referenceNode) {
        referenceNode.insertBefore(newNode, referenceNode.firstChild);
    }

    insertChild(newNode, parentNode) {
        parentNode.appendChild(newNode);
    }

    createElement(tagName, attributes) {
        let element = document.createElement(tagName);
        element = this.addAtributes(element, attributes);
        return element;
    }

    addAtributes(element, attributes) {
        for (let key in attributes) {
            element.setAttribute(key, attributes[key]);
        }
        return element;
    }

    addEvent(element, event, callback) {
        element.addEventListener(event, callback);
    }

    registerServiceWorker(sw) {
        navigator.serviceWorker.register(sw)
            .then(registration => {
                console.log("Service Worker registrado com sucesso:", registration);
            })
            .catch(err => {
                console.warn("Registro do Service Worker falhou:", err);
            });
    }

    existComponent(componentName) {
        let component = document.querySelector("c-" + componentName);
        return component !== null;
    }
};

// Classe principal do framework
class Predocs {

    // Classe para manipulação de elementos do dom
    #dom = new DomPredocs();

    // Configurações do app
    #configApp = null;

    // Arquivo de configuração
    #jsonIncludes = null;

    // Prompt para instalação do PWA
    deferredPrompt

    constructor(before, after) {

        if (typeof before === "function") {
            before(this);
        }

        this.#includeDefaultCss();
        this.#includeDefaultJs();
        this.#includeComponents();
        this.#includeMetaDataAndLink();
        this.#applyPWA();

        if (typeof after === "function") {
            after(this);
        }
    }

    get configApp() {
        if (this.#configApp === null) {
            this.#configApp = JSON.parse(this.requestGet("/config/app.json"));
        }
        return this.#configApp;
    }

    get listComponents() {
        return JSON.parse(this.requestGet("/config/components.json"));
    }

    get #includes() {
        if (this.#jsonIncludes === null) {
            this.#jsonIncludes = JSON.parse(this.requestGet("/config/includes.json"));
        }
        return this.#jsonIncludes;
    }

    #includeDefaultCss() {
        let $this = this;
        let head = this.#dom.head;
        let cssFiles = this.#includes.cssFiles;

        cssFiles.forEach(function (cssFile) {
            let attrs = {
                rel: "stylesheet",
                type: "text/css",
                href: $this.#getUrl(cssFile)
            };
            let elem = $this.#dom.createElement("link", attrs);
            $this.#dom.insertBefore(elem, head);
        });
    }

    #includeDefaultJs() {
        let $this = this;
        let body = this.#dom.body;
        let jsFiles = this.#includes.jsFiles;

        jsFiles.forEach(function (jsFile) {
            let attrs = {
                type: "text/javascript",
                src: $this.#getUrl(jsFile)
            };
            let elem = $this.#dom.createElement("script", attrs);
            $this.#dom.insertChild(elem, body);
        });
    }

    #includeComponents() {
        const components = this.listComponents;
        const pathComponent = this.#getUrl("/components");
        const strConstrutorComponent = this.requestGet(this.#getUrl("/core") + "/createComponent.js");

        for (let name in components) {
            if (!this.#dom.existComponent(name)) {
                continue;
            }

            let componentData = components[name];

            let htmlComponent = this.requestGet(`${pathComponent}/${componentData.html}.html`);
            let nameComponent = name + "ComponentHtml";
            let nameElement = "c-" + name;
            let jsComponent = "";
            let cssComponent = "";

            if (componentData.js) {
                jsComponent = this.requestGet(`${pathComponent}/${componentData.js}.js`);
            }

            if (componentData.css) {
                cssComponent = this.requestGet(`${pathComponent}/${componentData.css}.css`);
            }

            let strClassComponent = strConstrutorComponent;
            strClassComponent = strClassComponent.replaceAll(/[\r\n]+/g, ' ');
            strClassComponent = strClassComponent.replaceAll("__nameComponent__", nameComponent);
            strClassComponent = strClassComponent.replaceAll("__html__", htmlComponent);
            strClassComponent = strClassComponent.replaceAll("__nameElement__", nameElement);
            strClassComponent = strClassComponent.replaceAll("__script__", jsComponent);
            strClassComponent = strClassComponent.replaceAll("__css__", cssComponent);
            eval(strClassComponent);
        }
    }

    #includeMetaDataAndLink() {
        let $this = this;
        let metaData = this.#includes.metaData;
        let html = this.#dom.getElement("html");

        this.#dom.addAtributes(html, {
            lang: "pt-br"
        });

        metaData.meta.forEach(function (meta) {
            $this.#dom.meta = meta;
        });

        metaData.link.forEach(function (link) {
            $this.#dom.link = link;
        });
    }

    #applyPWA() {
        let manifest = {
            rel: "manifest",
            href: this.#getUrl("/config/manifest.json")
        };
        this.#dom.link = manifest;

        if ("serviceWorker" in navigator) {
            this.#dom.registerServiceWorker(this.#getUrl("/sw.js"));
        }
    }

    #getUrl(url) {
        if (url.startsWith("/")) {
            let elem = document.querySelector("[src$='predocs.js']");
            let src = elem.src;
            let appPath = src.substring(0, src.lastIndexOf("/"));
            let parentPath = appPath.substring(0, appPath.lastIndexOf("/"));
            return parentPath + url;
        } else {
            return url;
        }
    }

    getParamUrl($param) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get($param);
    }

    #addParamsToUrl(url, params) {
        if (params === undefined) {
            return url;
        }

        let urlParams = new URLSearchParams(params);
        return url + "?" + urlParams.toString();
    }

    requestGet(url, params) {
        return this.request(url, {}, params, "GET");
    }

    requestPost(url, data, params, awaitResponse = true) {
        return this.request(url, data, params, "POST", awaitResponse);
    }

    request(url, data, params, method, awaitResponse = true) {
        const fullUrl = this.#addParamsToUrl(this.#getUrl(url), params);
        try {
            const xhttp = new XMLHttpRequest();
            xhttp.open(method, fullUrl, !awaitResponse); // false = aguarda retorno
            xhttp.send(JSON.stringify(data));
            return xhttp.responseText;
        } catch (e) {
            console.error(e);
        }

        return undefined;
    }

    form(formSelector, beforeSubmit, afterSubmit) {
        const form = this.#dom.getElement(formSelector);
        this.#dom.addEvent(form, "submit", async (event) => {
            event.preventDefault();
            let beforeResult = true;

            if (typeof beforeSubmit === 'function') {
                beforeResult = beforeSubmit();
            }

            if (beforeResult !== false) {
                const action = "/api" + form.action.replace(location.origin, "");
                const method = this.#dom.getAttribute(form, "method");
                const formData = new FormData(form);
                const formDataObject = Object.fromEntries(formData);
                const response = await this.request(action, formDataObject, {}, method, true);

                if (typeof afterSubmit === 'function') {
                    afterSubmit(response);
                }
            }
        });
    }

    createOptionsInSelect(elem, options) {
        options.forEach(attrOption => {
            console.log(attrOption);
            let option = this.#dom.createElement("option", attrOption);
            option.textContent = attrOption.textContent;
            this.#dom.insertChild(option, elem);
        });
    }

    replaceTextInElement(elemSelector, replacements) {
        let elem = this.#dom.getElement(elemSelector);
        let html = elem.innerHTML;
        Object.keys(replacements).forEach((key) => {
            html = html.replace(
                new RegExp(`{{${key}}}`, "g"),
                replacements[key]
            );
        });
        this.#dom.setHtml(elem, html);
        return true;
    }
}

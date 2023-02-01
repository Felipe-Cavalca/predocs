class ComponentNav extends Predocs {
    seletor = "nav";

    constructor() {
        super();
        this.replaceTextInView(this.seletor, this.getConfig("app"));
    }
}

new ComponentNav();

/**
 * Função para criação de uma pagina basica do vue js 3
 * @param {domElement} e elemento dom
 */
function initVueDefault(e) {

    if (typeof Vue == "undefined") {
        throw "Vue js não existe!";
    }

    const pagina = {
        data() {
            return {
                VarsApp: Lis.getConfig("app"),
            };
        },
    };

    Vue.createApp(pagina).mount(e);
}

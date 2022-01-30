/**
 * Função para criação de uma pagina basica do vue js 3
 * @param {domElement} e elemento dom
 */
function initVueDefault(e){
    const pagina = {
        data() {
            return {
                VarsGlobal: VarsGlobal
            }
        }
    }

    Vue.createApp(pagina).mount(e);
}
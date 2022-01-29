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
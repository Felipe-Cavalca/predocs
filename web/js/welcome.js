function iniciaPagina() {
    const app = {
        data() {
            return {
                NomeApp: NomeApp
            }
        }
    }

    Vue.createApp(app).mount('#app')
}
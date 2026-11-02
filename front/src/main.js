import Vue from 'vue'
import App from './App.vue'
import router from './router'
import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap/dist/js/bootstrap.bundle.min.js'
import './assets/styles.css'

Vue.config.productionTip = false


Vue.prototype.$env = {
  appName: process.env.VUE_APP_NAME || 'Inmobiliaria',
  apiUrl: process.env.VUE_APP_API_URL || 'http://localhost:8000/api',
  whatsappPhone: process.env.VUE_APP_WHATSAPP_PHONE || ''
}

new Vue({
  router,
  render: h => h(App)
}).$mount('#app')

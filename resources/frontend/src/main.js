import 'core-js/es'

import Vue from 'vue'
import App from './App.vue'
import router from './router'
import store from './store'
import axios from './axios'
import moment from 'moment'
import VueAxios from 'vue-axios'
import VueSweetalert2 from 'vue-sweetalert2'
import EditField from '@/components/general/EditField'
import ProgressLinear from '@/components/general/ProgressLinear'
import Vuetify from 'vuetify'
import chartist from 'vue-chartist'
import 'vuetify/dist/vuetify.min.css'
import Vue2TouchEvents from 'vue2-touch-events'
import VueSync from 'vue-sync'

Vue.config.productionTip = false
Vue.use(VueAxios, axios)
Vue.use(VueSweetalert2)
Vue.use(chartist)
Vue.use(Vue2TouchEvents)
Vue.use(VueSync)

moment.locale('de-ch')
Vue.prototype.$moment = moment
Vue.component('edit-field', EditField)
Vue.component('progress-linear', ProgressLinear)
Vue.router = router
Vue.store = store

Vue.use(require('@websanova/vue-auth'), {
  auth: require('@websanova/vue-auth/drivers/auth/bearer.js'),
  http: require('@websanova/vue-auth/drivers/http/axios.1.x.js'),
  router: require('@websanova/vue-auth/drivers/router/vue-router.2.x.js'),
  parseUserData: function(body) {
    let user = body.data
    if (user) {
      user.hasPermission = function(types, roles = []) {
        if (!Array.isArray(types)) types = [types]
        if (types.includes(this.type.name)) return true

        return this.role && !!this.role.authorization_rules.find(r => roles.includes(r.name))
      }
    }
    return user
  }
})

const vuetifyOpts = {
  theme: {
    light: true,
    themes: {
      light: {
        primary: '#26a69a',
        secondary: '#26a69a',
        accent: '#26a69a'
      }
    }
  }
}
Vue.use(Vuetify)

App.router = Vue.router
App.store = Vue.store

new Vue({
  vuetify: new Vuetify(vuetifyOpts),
  render: h => h(App)
}).$mount('#app')

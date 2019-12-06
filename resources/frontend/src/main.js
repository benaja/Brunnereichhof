import Vue from 'vue'
import App from './App.vue'
import router from './router'
import store from './store'
import axios from 'axios'
import moment from 'moment'
import VueAxios from 'vue-axios'
import VueSweetalert2 from 'vue-sweetalert2'
import VueTouch from 'vue-touch'
import EditField from '@/components/general/EditField'
import Vuetify from 'vuetify'
import chartist from 'vue-chartist'
import 'vuetify/dist/vuetify.min.css'

Vue.config.productionTip = false
Vue.use(VueAxios, axios)
Vue.use(VueSweetalert2)
Vue.use(VueTouch)
Vue.use(chartist)

Vue.prototype.$moment = moment
Vue.component('edit-field', EditField)
Vue.router = router
Vue.store = store
axios.defaults.baseURL = process.env.VUE_APP_API_URL
axios.interceptors.response.use(
  response => {
    return response
  },
  error => {
    error.includes = (string, key = null) => {
      if (!error.response) return false
      if (
        key &&
        error.response.data &&
        error.response.data.errors &&
        error.response.data.errors[key] &&
        error.response.data.errors[key].includes(string)
      ) {
        return true
      } else {
        for (let err in error.response.data.errors) {
          if (error.response.data.errors[err].includes(string)) return true
        }
      }
      if (error.response.data.error && error.response.data.error.includes(string)) return true
      if (error.response.data.message && error.response.data.message.includes(string)) return true
      if (error.response.data && typeof error.response.data !== 'object' && error.response.data.includes(string)) return true
      return false
    }

    error.status = status => {
      return error.response && error.response.status === status
    }

    if (error.includes('token_not_provided')) {
      store.commit('isLoading', false)
      router.push('/login')
    } else if (error.status(401) || error.status(429)) {
      store.commit('isLoading', false)
      router.push('/login')
    } else if (error.includes('token_invalid') || error.includes('token_expired')) {
      localStorage.removeItem('default_auth_token')
      store.commit('isLoading', false)
      router.push('/login')
    } else if (error.status(403) && error.includes('Your account has been deactivated')) {
      Vue.swal(
        'Benuter deaktiviert',
        'Dein Benutzer wurde deaktiviert. Bitte kontaktiere Steffan Brunner um deinen Benutzer wieder zu aktivieren.',
        'error'
      )
      store.commit('isLoading', false)
    } else if (error.status(403)) {
      Vue.swal('Nicht berechtigt', 'Du bist für diese Aktion nicht berechtigt', 'error')
      store.commit('isLoading', false)
    }

    return Promise.reject(error)
  }
)

Vue.use(require('@websanova/vue-auth'), {
  auth: require('@websanova/vue-auth/drivers/auth/bearer.js'),
  http: require('@websanova/vue-auth/drivers/http/axios.1.x.js'),
  router: require('@websanova/vue-auth/drivers/router/vue-router.2.x.js'),
  parseUserData: function(body) {
    let user = body.data
    user.hasPermission = function(types, roles = []) {
      if (!Array.isArray(types)) types = [types]
      if (types.includes(this.type.name)) return true

      return this.role && !!this.role.authorization_rules.find(r => roles.includes(r.name))
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

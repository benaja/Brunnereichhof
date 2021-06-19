import 'core-js/es'

import Vue from 'vue'
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
import vueAuthAxios from '@websanova/vue-auth/drivers/http/axios.1.x'
import vueAuthRouter from '@websanova/vue-auth/drivers/router/vue-router.2.x'
import auth from '@websanova/vue-auth'
import VueLodash from 'vue-lodash'
import lodash from 'lodash'
import NavigationBar from '@/components/NavigationBar'
import Fragment from 'vue-fragment'
import i18n from '@/plugins/i18n'
import { COLORS } from './constants'
import axios from './axios'
import store from './store'
import router from './router'
import App from './App'
import './filters'

Vue.config.productionTip = false
Vue.use(VueAxios, axios)
Vue.use(VueSweetalert2, {
  confirmButtonColor: COLORS.PRIMARY
})
Vue.use(chartist)
Vue.use(Vue2TouchEvents)
Vue.use(VueSync)
Vue.use(VueLodash, { name: '$lodash', lodash })
Vue.use(Fragment.Plugin)

moment.locale('de-ch')
Vue.prototype.$moment = moment
Vue.component('edit-field', EditField)
Vue.component('navigation-bar', NavigationBar)
Vue.component('progress-linear', ProgressLinear)
Vue.router = router
Vue.store = store

Vue.use(auth, {
  auth: {
    request (req, token) {
      this.http.setHeaders.call(this, req, {
        Authorization: `Bearer ${token}`
      })
    },
    response (res) {
      return res.data.access_token
    }
  },
  http: vueAuthAxios,
  router: vueAuthRouter,
  parseUserData(body) {
    console.log(body)
    const user = body.data
    if (user) {
      user.hasPermission = function(types, roles = []) {
        if (!Array.isArray(types)) types = [types]
        if (types.includes(this.type.name)) return true

        return this.role && !!this.role.authorization_rules.find(r => roles.includes(r.name))
      }
    }
    return user
  },
  refreshData: { enabled: false, interval: 0 }
})

const vuetifyOpts = {
  theme: {
    light: true,
    themes: {
      light: {
        primary: COLORS.PRIMARY,
        secondary: COLORS.SECONDARY,
        accent: COLORS.ACCENT
      }
    }
  }
}
Vue.use(Vuetify)

App.router = Vue.router
App.store = Vue.store

new Vue({
  vuetify: new Vuetify(vuetifyOpts),
  render: h => h(App),
  i18n
}).$mount('#app')

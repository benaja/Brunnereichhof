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
import auth from './plugins/vue-auth'

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

auth.register()

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

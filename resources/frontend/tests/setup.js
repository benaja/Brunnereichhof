import Vue from 'vue'
import Vuetify from 'vuetify'

Vue.use(Vuetify)

beforeEach(() => {
  const app = document.createElement('div')
  app.setAttribute('data-app', true)
  document.body.append(app)
})

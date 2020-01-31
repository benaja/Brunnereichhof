import axios from 'axios'
import router from './router'
import store from './store'
import Vue from 'vue'

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

export default axios

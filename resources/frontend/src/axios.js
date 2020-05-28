import axios from 'axios'
import Vue from 'vue'
import router from './router'

axios.defaults.baseURL = process.env.VUE_APP_API_URL
axios.interceptors.response.use(
  response => response,
  error => {
    error.includes = (string, key = null) => {
      if (!error.response) return false
      if (
        key
        && error.response.data
        && error.response.data.errors
        && error.response.data.errors[key]
        && error.response.data.errors[key].includes(string)
      ) {
        return true
      }
      if (!key) {
        for (const err in error.response.data.errors) {
          if (error.response.data.errors[err].includes(string)) return true
        }
      }

      if (error.response.data.error && error.response.data.error.includes(string)) return true
      if (error.response.data.message && error.response.data.message.includes(string)) return true
      if (error.response.data && typeof error.response.data !== 'object' && error.response.data.includes(string)) return true
      return false
    }

    error.status = status => error.response && error.response.status === status

    if (error.includes('token_not_provided')) {
      router.push('/login')
    } else if (error.status(401) || error.status(429)) {
      router.push('/login')
    } else if (error.includes('token_invalid') || error.includes('token_expired')) {
      localStorage.removeItem('default_auth_token')
      router.push('/login')
    } else if (error.status(403) && error.includes('Your account has been deactivated')) {
      Vue.swal(
        'Benuter deaktiviert',
        'Dein Benutzer wurde deaktiviert. Bitte kontaktiere Steffan Brunner um deinen Benutzer wieder zu aktivieren.',
        'error',
      )
    } else if (error.status(403)) {
      Vue.swal('Nicht berechtigt', 'Du bist für diese Aktion nicht berechtigt', 'error')
    }

    return Promise.reject(error)
  },
)

export default axios

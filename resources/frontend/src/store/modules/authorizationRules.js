import axios from 'axios'

export default {
  state: {
    authorizationRules: []
  },
  getters: {
    authorizationRules: state => state.authorizationRules
  },
  mutations: {
    setAuthorizationRules(state, authorizationRules) {
      state.authorizationRules = authorizationRules
    }
  },
  actions: {
    fetchAuthorizationRules({ commit, getters, dispatch }) {
      return new Promise((resolve, reject) => {
        commit('loading', {
          authorizationRules: true
        })
        axios
          .get('/authorizationRules')
          .then(response => {
            commit('setAuthorizationRules', response.data)
            resolve(getters.authorizationRules)
          })
          .catch(error => {
            dispatch('error', 'Fehler beim Laden der Mitarbeiter.')
            reject(error)
          })
          .finally(() => {
            commit('loading', {
              authorizationRules: false
            })
          })
      })
    }
  }
}

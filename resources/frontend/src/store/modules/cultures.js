import axios from 'axios'
import moment from 'moment'

export default {
  state: {
    cultures: []
  },
  getters: {
    cultures: state => state.cultures.filter(c => !c.deleted_at),
    allCultures: state => state.cultures,
    deletedCultures: state => state.cultures.filter(c => c.deleted_at)
  },
  mutations: {
    setCultures(state, cultures) {
      state.cultures = cultures
    },
    addCulture(state, culture) {
      state.cultures.push(culture)
    },
    updateCulture(state, updatedCulture) {
      const culture = state.cultures.find(c => c.id === updatedCulture.id)
      const index = state.cultures.indexOf(culture)
      state.cultures[index] = updatedCulture
    },
    deleteCulture(state, cultureId) {
      const culture = state.cultures.find(e => e.id === cultureId)
      const index = state.cultures.indexOf(culture)
      culture.deleted_at = moment().format('YYYY-MM-DD HH:mm:ss')
      state.cultures[index] = culture
    }
  },
  actions: {
    fetchCultures({ commit, getters, dispatch }) {
      return new Promise((resolve, reject) => {
        commit('loading', {
          cultures: true
        })
        axios
          .get('/cultures')
          .then(response => {
            commit('setCultures', response.data)
            resolve(getters.cultures)
          })
          .catch(error => {
            dispatch('error', 'Fehler beim Laden der Mitarbeiter.')
            reject(error)
          })
          .finally(() => {
            commit('loading', {
              cultures: false
            })
          })
      })
    },
    updateCulture({ commit }, culture) {
      return new Promise((resolve, reject) => {
        axios
          .put(`cultures/${culture.id}`, culture)
          .then(() => {
            commit('updateCulture', culture)
            resolve(culture)
          })
          .catch(error => reject(error))
      })
    },
    deleteCulture({ commit }, cultureId) {
      return new Promise((resolve, reject) => {
        axios
          .delete(`cultures/${cultureId}`)
          .then(() => {
            commit('deleteCulture', cultureId)
            resolve()
          })
          .catch(error => reject(error))
      })
    }
  }
}

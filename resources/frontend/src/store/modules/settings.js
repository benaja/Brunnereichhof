import axios from 'axios'
import Vue from 'vue'
import moment from 'moment'

export default {
  state: {
    timerecordSettings: {},
    settings: {}
  },
  getters: {
    timerecordSettings: state => state.timerecordSettings
  },
  mutations: {
    setTimerecordSettings(state, settings) {
      state.timerecordSettings = settings
    },
    setSettings(state, settings) {
      state.settings = {
        ...settings,
        welcomeText: settings.welcomeText.replace('{name}', `${Vue.auth.user().firstname} ${Vue.auth.user().lastname}`),
        hourrecordValid: settings.hourrecordValid.replace('{datum}', moment(settings.hourrecordEndDate).format('DD.MM.YYYY'))
      }
    }
  },
  actions: {
    fetchTimerecordSettings({ commit, getters, dispatch }, urlParams) {
      return new Promise((resolve, reject) => {
        commit('loading', { settings: true })
        axios
          .get(`settings/time${urlParams}`)
          .then(response => {
            commit('setTimerecordSettings', response.data)
            resolve(getters.worktypes)
          })
          .catch(error => {
            dispatch('error', 'Fehler beim Laden der Einstellungen.')
            reject(error)
          })
          .finally(() => {
            commit('loading', { settings: false })
          })
      })
    }
  }
}

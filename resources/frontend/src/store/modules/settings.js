import axios from 'axios'
import Vue from 'vue'
import moment from 'moment'

export default {
  state: {
    timerecordSettings: {},
    settings: {},
    hourrecordSettings: {}
  },
  getters: {
    timerecordSettings: state => state.timerecordSettings,
    settings: state => state.settings,
    hourrecordSettings: state => state.hourrecordSettings,
    isEditTime: state => {
      const startDate = moment(state.hourrecordSettings.hourrecordStartDate)
      const endDate = moment(state.hourrecordSettings.hourrecordEndDate)

      return moment().isBetween(startDate, endDate, 'day', '[]')
    }
  },
  mutations: {
    setTimerecordSettings(state, settings) {
      state.timerecordSettings = settings
    },
    setSettings(state, settings) {
      state.settings = settings
    },
    setHourreocrdSettings(state, settings) {
      state.hourrecordSettings = {
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
            resolve(getters.timerecordSettings)
          })
          .catch(error => {
            dispatch('error', 'Fehler beim Laden der Einstellungen.')
            reject(error)
          })
          .finally(() => {
            commit('loading', { settings: false })
          })
      })
    },
    fetchSettings({ commit, getters, dispatch }) {
      return new Promise((resolve, reject) => {
        commit('loading', { settings: true })
        axios.get('settings').then(response => {
          commit('setSettings', response.data)
          resolve(getters.settings)
        }).catch(error => {
          dispatch('error', 'Fehler beim Laden der Einstellungen')
          reject(error)
        }).finally(() => {
          commit('loading', { settings: false })
        })
      })
    },
    fetchHourrecordSettings({ commit, getters, dispatch }) {
      return new Promise((resolve, reject) => {
        commit('loading', { settings: true })
        axios.get('settings/hourrecords').then(response => {
          commit('setHourreocrdSettings', response.data)
          resolve(getters.hourrecordSettings)
        }).catch(error => {
          dispatch('error', 'Fehler beim Laden der Einstellungen')
          reject(error)
        }).finally(() => {
          commit('loading', { settings: false })
        })
      })
    }
  }
}

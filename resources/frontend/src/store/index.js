import Vue from 'vue'
import Vuex from 'vuex'
import roles from './modules/roles'
import rooms from './modules/rooms'
import loading from './modules/loading'
import beds from './modules/beds'
import employees from './modules/employees'
import customers from './modules/customers'
import workers from './modules/workers'
import cultures from './modules/cultures'
import authorizationRules from './modules/authorizationRules'
import timerecords from './modules/timerecords'
import settings from './modules/settings'

Vue.use(Vuex)

export default new Vuex.Store({
  modules: {
    roles,
    rooms,
    loading,
    beds,
    employees,
    customers,
    workers,
    cultures,
    authorizationRules,
    timerecords,
    settings
  },
  state: {
    isMobile: false,
    openPopups: true,
    dayShortNames: ['Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa', 'So'],
    dayNames: ['Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag'],
    recordWeeks: [],
    hourRecords: [],
    saveState: {
      isSaving: false,
      saves: 0
    },
    alerts: []
  },
  mutations: {
    isMobile(state, isMobile) {
      state.isMobile = isMobile
    },
    openPopups(state, openPopups) {
      state.openPopups = openPopups
    },
    recordWeeks(state, recordWeeks) {
      state.recordWeeks = recordWeeks
    },
    hourRecords(state, hourRecords) {
      state.hourRecords = hourRecords
    },
    isSaving(state, isSaving) {
      if (isSaving) {
        state.saveState.saves++
        state.saveState.isSaving = true
      } else {
        state.saveState.saves--
        if (state.saveState.saves === 0) {
          state.saveState.isSaving = false
        }
      }
    },
    addAlert(state, alert) {
      state.alerts.push(alert)
    },
    removeAlert(state) {
      state.alerts.shift()
    }
  },
  getters: {
    isMobile: state => state.isMobile,
    openPopups: state => state.openPopups,
    dayShortNames: state => state.dayShortNames,
    dayNames: state => state.dayNames,
    recordWeeks: state => state.recordWeeks,
    hourRecords: state => state.hourRecords,
    saveState: state => state.saveState,
    isSaving: state => state.saveState.isSaving,
    isEditTime: state => {
      let startdate = new Date(state.settings.hourrecordStartDate)
      let endDate = new Date(state.settings.hourrecordEndDate)

      if (startdate instanceof Date && !isNaN(startdate)) {
        let today = new Date()
        // make shure that the time is everywhere the same
        today = new Date(today.toDateString())
        endDate = new Date(endDate.toDateString())
        startdate = new Date(startdate.toDateString())
        return startdate <= today && endDate >= today
      }
      return true
    },
    alerts: state => state.alerts
  },
  actions: {
    closeAllPopups({ commit }) {
      commit('openPupups', false)
      setTimeout(() => {
        commit('openPopups', true)
      }, 10)
    },
    alert({ commit }, alert) {
      commit('addAlert', {
        ...alert,
        visible: true,
        key: Math.random()
      })
      setTimeout(() => {
        commit('removeAlert')
      }, (alert.duration || 3) * 1000)
    },
    error({ dispatch }, text) {
      dispatch('alert', { text, type: 'error', duration: 4 })
    }
  }
})

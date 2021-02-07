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
import transactionTypes from './modules/transactionTypes'
import transactions from './modules/transactions'
import tools from './modules/tools'
import cars from './modules/cars'

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
    settings,
    transactionTypes,
    transactions,
    tools,
    cars
  },
  state: {
    isMobile: false,
    openPopups: true,
    recordWeeks: [],
    hourRecords: [],
    saveState: {
      isSaving: false,
      saves: 0,
      saved: false
    },
    alerts: [],
    navigationBarModel: false,
    preventFormSubmit: false
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
          state.saveState.saved = true
          setTimeout(() => {
            state.saveState.saved = false
          }, 2000)
        }
      }
    },
    addAlert(state, alert) {
      state.alerts.push(alert)
    },
    removeAlert(state) {
      state.alerts.shift()
    },
    navigationBarModel(state, value) {
      state.navigationBarModel = value
    },
    preventFormSubmit(state, value) {
      state.preventFormSubmit = value
    }
  },
  getters: {
    isMobile: state => state.isMobile,
    openPopups: state => state.openPopups,
    recordWeeks: state => state.recordWeeks,
    hourRecords: state => state.hourRecords,
    saveState: state => state.saveState,
    isSaving: state => state.saveState.isSaving,
    saved: state => state.saveState.saved,
    alerts: state => state.alerts,
    navigationBarModel: state => state.navigationBarModel,
    preventFormSubmit: state => state.preventFormSubmit
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

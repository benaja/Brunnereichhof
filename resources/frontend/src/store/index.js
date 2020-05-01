import Vue from 'vue'
import Vuex from 'vuex'
import axios from 'axios'
import moment from 'moment'
import roles from './modules/roles'
import rooms from './modules/rooms'
import loading from './modules/loading'
import beds from './modules/beds'
import employees from './modules/employees'

Vue.use(Vuex)

const resolveContent = (context, getter, url, properties) => new Promise((resolve, reject) => {
  const hasDeletedAttributes = !Array.isArray(context.getters[getter])
  let urlParams = ''
  let value
  let getterValues
  if (hasDeletedAttributes) {
    if (properties.deleted) {
      value = 'deleted'
      urlParams = `?deleted=true${properties.urlParams ? `&${properties.urlParams}` : ''}`
    } else if (properties.all) {
      value = 'all'
      urlParams = `?all=true${properties.urlParams ? `&${properties.urlParams}` : ''}`
    } else {
      value = 'items'
      urlParams = properties.urlParams ? `?${properties.urlParams}` : ''
    }
    getterValues = context.getters[getter][value]
  } else {
    getterValues = context.getters[getter]
  }
  if (getterValues.length === 0 || properties.update) {
    const fullUrl = `/${url}${urlParams}`
    axios
      .get(fullUrl)
      .then(response => {
        context.commit('dynamicMutate', {
          getter,
          value,
          items: response.data
        })
        if (hasDeletedAttributes) resolve(context.getters[getter][value])
        else resolve(context.getters[getter])
      })
      .catch(error => reject(error))
  } else if (hasDeletedAttributes) resolve(context.getters[getter][value])
  else resolve(context.getters[getter])
})

const resetContent = (context, getter) => {
  const values = ['deleted', 'all', 'items']
  for (let i = 0; i < values.length; i++) {
    context.commit('dynamicMutate', {
      getter,
      value: values[i],
      items: []
    })
  }
}

const getPeopleWithFullName = people => {
  for (const person of people) {
    person.name = `${person.lastname} ${person.firstname}`
  }
  return people
}

const defaultResolveProperties = {
  update: false,
  deleted: false
}

const defaultValuesArray = {
  items: [],
  deleted: [],
  all: []
}

export default new Vuex.Store({
  modules: {
    roles,
    rooms,
    loading,
    beds,
    employees
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
    settings: {},
    customers: {
      ...defaultValuesArray
    },
    cultures: {
      ...defaultValuesArray
    },
    workers: {
      ...defaultValuesArray
    },
    authorizationRules: [],
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
        state.saveState.isSaving = isSaving
      } else {
        state.saveState.saves--
        if (state.saveState.saves === 0) {
          state.saveState.isSaving = false
        }
      }
    },
    settings(state, settings) {
      state.settings = {
        ...settings,
        welcomeText: settings.welcomeText.replace('{name}', `${Vue.auth.user().firstname} ${Vue.auth.user().lastname}`),
        hourrecordValid: settings.hourrecordValid.replace('{datum}', moment(settings.hourrecordEndDate).format('DD.MM.YYYY'))
      }
    },
    customers(state, customers) {
      state.customers.items = getPeopleWithFullName(customers)
    },
    cultures(state, cultures) {
      state.cultures.itmes = cultures
    },
    authorizationRules(state, authorizationRules) {
      state.authorizationRules = authorizationRules
    },
    dynamicMutate(state, properties) {
      if (properties.items[0] && properties.items[0].firstname) {
        properties.items = getPeopleWithFullName(properties.items)
      }
      if (Array.isArray(state[properties.getter])) {
        state[properties.getter] = properties.items
      } else {
        state[properties.getter][properties.value] = properties.items
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
    settings: state => state.settings,
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
    customers: state => state.customers,
    cultures: state => state.cultures,
    workers: state => state.workers,
    authorizationRules: state => state.authorizationRules,
    alerts: state => state.alerts
  },
  actions: {
    closeAllPopups({ commit }) {
      commit('openPupups', false)
      setTimeout(() => {
        commit('openPopups', true)
      }, 10)
    },
    customers(context, properties = defaultResolveProperties) {
      return resolveContent(context, 'customers', 'customer', properties)
    },
    workers(context, properties = defaultResolveProperties) {
      return resolveContent(context, 'workers', 'worker', properties)
    },
    cultures(context, properties = defaultResolveProperties) {
      return resolveContent(context, 'cultures', 'culture', properties)
    },
    authorizationRules(context, properties = defaultResolveProperties) {
      return resolveContent(context, 'authorizationRules', 'rules', properties)
    },
    resetCustomers(context) {
      resetContent(context, 'customers')
    },
    resetCultures(context) {
      resetContent(context, 'cultures')
    },
    resetWorkers(context) {
      resetContent(context, 'workers')
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
      dispatch('alert', { text, type: 'error' })
    }
  }
})

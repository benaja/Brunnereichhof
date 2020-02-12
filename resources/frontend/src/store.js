import Vue from 'vue'
import Vuex from 'vuex'
import axios from 'axios'

Vue.use(Vuex)

const resolveContent = (context, getter, url, properties) => {
  return new Promise((resolve, reject) => {
    let hasDeletedAttributes = !Array.isArray(context.getters[getter])
    let urlParams = ''
    let value
    let getterValues
    if (hasDeletedAttributes) {
      if (properties.deleted) {
        value = 'deleted'
        urlParams = '?deleted=true' + (properties.urlParams ? `&${properties.urlParams}` : '')
      } else if (properties.all) {
        value = 'all'
        urlParams = '?all=true' + (properties.urlParams ? `&${properties.urlParams}` : '')
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
    } else {
      if (hasDeletedAttributes) resolve(context.getters[getter][value])
      else resolve(context.getters[getter])
    }
  })
}

const resetContent = (context, getter) => {
  let values = ['deleted', 'all', 'items']
  for (let i = 0; i < values.length; i++) {
    context.commit('dynamicMutate', {
      getter,
      value: values[i],
      items: []
    })
  }
}

const getPeopleWithFullName = people => {
  for (let person of people) {
    person.name = person.lastname + ' ' + person.firstname
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
  state: {
    isMobile: false,
    openPopups: true,
    dayShortNames: ['Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa', 'So'],
    dayNames: ['Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag'],
    recordWeeks: [],
    hourRecords: [],
    isLoading: false,
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
    employees: {
      ...defaultValuesArray
    },
    workers: {
      ...defaultValuesArray
    },
    guests: {
      ...defaultValuesArray
    },
    employeesWithGuests: {
      ...defaultValuesArray
    },
    rooms: {
      ...defaultValuesArray
    },
    allEmployees: [],
    employeesAndGuests: [],
    roles: [],
    authorizationRules: []
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
    isLoading(state, isLoading) {
      state.isLoading = isLoading
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
      state.settings = settings
    },
    customers(state, customers) {
      state.customers.items = getPeopleWithFullName(customers)
    },
    cultures(state, cultures) {
      state.cultures.itmes = cultures
    },
    employees(state, employees) {
      state.employees.items = getPeopleWithFullName(employees)
    },
    rooms(state, rooms) {
      state.rooms.items = rooms
    },
    roles(state, roles) {
      state.roles = roles
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
    }
  },
  getters: {
    isMobile: state => state.isMobile,
    openPopups: state => state.openPopups,
    dayShortNames: state => state.dayShortNames,
    dayNames: state => state.dayNames,
    recordWeeks: state => state.recordWeeks,
    hourRecords: state => state.hourRecords,
    isLoading: state => state.isLoading,
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
      } else {
        return true
      }
    },
    customers: state => state.customers,
    cultures: state => state.cultures,
    employees: state => state.employees,
    workers: state => state.workers,
    rooms: state => state.rooms,
    employeesWithGuests: state => state.employeesWithGuests,
    guests: state => state.guests,
    roles: state => state.roles,
    authorizationRules: state => state.authorizationRules
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
    employees(context, properties = defaultResolveProperties) {
      return resolveContent(context, 'employees', 'employee', properties)
    },
    guests(context, properties = defaultResolveProperties) {
      return resolveContent(context, 'guests', 'guest', properties)
    },
    rooms(context, properties = defaultResolveProperties) {
      return resolveContent(context, 'rooms', 'rooms', properties)
    },
    employeesWithGuests(context, properties = defaultResolveProperties) {
      return resolveContent(context, 'employeesWithGuests', 'employeeswithguests', properties)
    },
    cultures(context, properties = defaultResolveProperties) {
      return resolveContent(context, 'cultures', 'culture', properties)
    },
    roles(context, properties = defaultResolveProperties) {
      return resolveContent(context, 'roles', 'roles', properties)
    },
    authorizationRules(context, properties = defaultResolveProperties) {
      return resolveContent(context, 'authorizationRules', 'rules', properties)
    },
    resetCustomers(context) {
      resetContent(context, 'customers')
    },
    resetEmployees(context) {
      resetContent(context, 'employees')
    },
    resetCultures(context) {
      resetContent(context, 'cultures')
    },
    resetWorkers(context) {
      resetContent(context, 'workers')
    },
    resetGuests(context) {
      resetContent(context, 'guests')
    },
    resetRooms(context) {
      resetContent(context, 'rooms')
    }
  }
})

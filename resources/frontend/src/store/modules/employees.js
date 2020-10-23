import axios from 'axios'
import moment from 'moment'

export default {
  state: {
    employees: [],
    employeesMeta: {}
  },
  getters: {
    employees: state => state.employees.filter(e => !e.deleted_at && !e.isGuest),
    allEmployees: state => state.employees.filter(e => !e.isGuest),
    deletedEmployees: state => state.employees.filter(e => e.deleted_at && !e.isGuest),
    guests: state => state.employees.filter(e => !e.deleted_at && e.isGuest),
    allGuests: state => state.employees.filter(e => e.isGuest),
    deletedGuests: state => state.employees.filter(e => e.deleted_at && e.isGuest),
    employeesWithGuests: state => state.employees.filter(e => !e.deleted_at),
    allEmployeesWithGuests: state => state.employees,
    activeEmployees: state => state.employees
      .filter(e => !e.deleted_at && !e.isGuest && e.isActive),
    employeesMeta: state => state.employeesMeta
  },
  mutations: {
    setEmployees(state, employees) {
      state.employees = employees.map(e => ({
        ...e,
        name: `${e.lastname} ${e.firstname}`,
        nameWithCallName: `${e.lastname} ${e.firstname} ${e.callname || ''}`
      }))
    },
    addEmployee(state, employee) {
      state.employees.push(employee)
    },
    updateEmployee(state, updatedEmployee) {
      const employee = state.employees.find(b => b.id === updatedEmployee.id)
      const index = state.employees.indexOf(employee)
      state.employees[index] = updatedEmployee
    },
    deleteEmployee(state, employeeId) {
      const employee = state.employees.find(e => e.id === employeeId)
      const index = state.employees.indexOf(employee)
      employee.deleted_at = moment().format('YYYY-MM-DD HH:mm:ss')
      state.employees[index] = employee
    },
    setEmployeesMeta(state, value) {
      state.employeesMeta = value || {}
    }
  },
  actions: {
    loadEmployees({ commit, getters, dispatch }, { withGuests, withInactive }) {
      return new Promise((resolve, reject) => {
        commit('loading', { employees: true })
        axios
          .get('/employees', {
            params: {
              withGuests,
              withInactive,
              paginate: true
            }
          })
          .then(response => {
            commit('setEmployees', response.data.data)
            commit('setEmployeesMeta', response.data.meta)
            resolve(getters.allEmployeesWithGuests)
          })
          .catch(error => {
            dispatch('error', 'Fehler beim Laden der Mitarbeiter.')
            reject(error)
          })
          .finally(() => {
            commit('loading', { employees: false })
          })
      })
    },
    loadMoreEmployees({ commit, getters, dispatch }, { withGuests, withInactive }) {
      return new Promise((resolve, reject) => {
        commit('loading', { employees: true })
        axios
          .get('/employees', {
            params: {
              withGuests,
              withInactive,
              paginate: true,
              page: getters.employeesMeta.current_page + 1
            }
          })
          .then(response => {
            commit('setEmployees', [...getters.allEmployeesWithGuests, ...response.data.data])
            commit('setEmployeesMeta', response.data.meta)
            resolve(getters.allEmployeesWithGuests)
          })
          .catch(error => {
            dispatch('error', 'Fehler beim Laden der Mitarbeiter.')
            reject(error)
          })
          .finally(() => {
            commit('loading', { employees: false })
          })
      })
    },
    fetchEmployees({ commit, getters, dispatch }) {
      return new Promise((resolve, reject) => {
        commit('loading', { employees: true })
        axios
          .get('/employees?all=true')
          .then(response => {
            commit('setEmployees', response.data)
            resolve(getters.employees)
          })
          .catch(error => {
            dispatch('error', 'Fehler beim Laden der Mitarbeiter.')
            reject(error)
          })
          .finally(() => {
            commit('loading', { employees: false })
          })
      })
    },
    updateEmployee({ commit }, employee) {
      return new Promise((resolve, reject) => {
        axios
          .put(`employees/${employee.id}`, employee)
          .then(() => {
            commit('updateEmployee', employee)
            resolve(employee)
          })
          .catch(error => reject(error))
      })
    },
    deleteEmployee({ commit }, employeeId) {
      return new Promise((resolve, reject) => {
        axios
          .delete(`employees/${employeeId}`)
          .then(() => {
            commit('deleteEmployee', employeeId)
            resolve()
          })
          .catch(error => reject(error))
      })
    }
  }
}

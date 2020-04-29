import axios from 'axios'
import moment from 'moment'

export default {
  state: {
    employees: []
  },
  getters: {
    employees: state => state.employees.filter(e => !e.deleted_at && !e.isGuest),
    allEmployees: state => state.employees,
    deletedEmployees: state => state.employees.filter(e => e.deleted_at),
    guests: state => state.employees.filter(e => !e.deleted_at && e.isGuest)
  },
  mutations: {
    setEmployees(state, employees) {
      state.employees = employees.map(e => ({
        ...e,
        name: `${e.lastname} ${e.firstname}`
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
    }
  },
  actions: {
    fetchEmployees({ commit, getters, dispatch }) {
      return new Promise((resolve, reject) => {
        commit('loadingEmployees', true)
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
            commit('loadingEmployees', false)
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

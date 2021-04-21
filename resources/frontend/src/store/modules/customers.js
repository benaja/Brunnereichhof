import axios from 'axios'
import moment from 'moment'

export default {
  state: {
    customers: []
  },
  getters: {
    customers: state => state.customers.filter(c => !c.deleted_at),
    allCustomers: state => state.customers,
    deletedCustomers: state => state.customers.filter(c => c.deleted_at)
  },
  mutations: {
    setCustomers(state, customers) {
      state.customers = customers.map(c => ({
        ...c,
        name: `${c.lastname} ${c.firstname}`
      }))
    },
    addCustomer(state, customer) {
      state.customers.push(customer)
    },
    updateCustomer(state, updatedCustomer) {
      const customer = state.customers.find(c => c.id === updatedCustomer.id)
      const index = state.customers.indexOf(customer)
      state.customers[index] = updatedCustomer
    },
    deleteCustomer(state, customerId) {
      const customer = state.customers.find(e => e.id === customerId)
      const index = state.customers.indexOf(customer)
      customer.deleted_at = moment().format('YYYY-MM-DD HH:mm:ss')
      state.customers[index] = customer
    }
  },
  actions: {
    fetchCustomers({ commit, getters, dispatch }, params = {}) {
      return new Promise((resolve, reject) => {
        commit('loading', { customers: true })
        axios
          .get('/customers', {
            params: {
              all: true,
              ...params
            }
          })
          .then(response => {
            commit('setCustomers', response.data)
            resolve(getters.customers)
          })
          .catch(error => {
            dispatch('error', 'Fehler beim Laden der Mitarbeiter.')
            reject(error)
          })
          .finally(() => {
            commit('loading', { customers: false })
          })
      })
    },
    updateCustomer({ commit }, customer) {
      return new Promise((resolve, reject) => {
        axios
          .put(`customers/${customer.id}`, customer)
          .then(() => {
            commit('updateCustomer', customer)
            resolve(customer)
          })
          .catch(error => reject(error))
      })
    },
    deleteCustomer({ commit }, customerId) {
      return new Promise((resolve, reject) => {
        axios
          .delete(`customers/${customerId}`)
          .then(() => {
            commit('deleteCustomer', customerId)
            resolve()
          })
          .catch(error => reject(error))
      })
    }
  }
}

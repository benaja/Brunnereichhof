import axios from 'axios'

export default {
  state: {
    transactions: [],
    transactionsMeta: {}
  },
  getters: {
    transactions: state => state.transactions,
    transactionsMeta: state => state.transactionsMeta
  },
  mutations: {
    setTransactions(state, value) {
      state.transactions = value
    },
    setTransactionsMeta(state, value) {
      state.transactionsMeta = value
    }
  },
  actions: {
    fetchTransactions({ commit, getters, dispatch }, pagination) {
      return new Promise((resolve, reject) => {
        commit('loading', { transactions: true })
        axios.get('transactions', {
          params: {
            page: pagination.page,
            per_page: pagination.itemsPerPage,
            sort_by: pagination.sortBy,
            sort_desc: pagination.sortDesc
          }
        }).then(response => {
          commit('setTransactions', response.data.data)
          commit('setTransactionsMeta', response.data.meta || {})
          resolve(getters.transactions)
        }).catch(error => {
          dispatch('error', 'Fehler beim Laden der Vorschüsse.')
          reject(error)
        }).finally(() => {
          commit('loading', { transactions: false })
        })
      })
    }
  }
}

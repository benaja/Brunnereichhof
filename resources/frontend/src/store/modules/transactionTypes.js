import axios from 'axios'

export default {
  state: {
    transactionTypes: []
  },
  getters: {
    transactionTypes: state => state.transactionTypes.filter(t => !t.deleted_at),
    allTransactionTypes: state => state.transactionTypes,
    deletedTransactionTypes: state => state.transactionTypes.filter(t => t.deleted_at)
  },
  mutations: {
    setTransactionTypes(state, transactionTypes) {
      state.transactionTypes = transactionTypes
    }
  },
  actions: {
    fetchTransactionTypes({ commit, getters, dispatch }) {
      return new Promise((resolve, reject) => {
        commit('loading', { transactionTypes: true })
        axios
          .get('/transaction-types?all=true')
          .then(response => {
            commit('setTransactionTypes', response.data.data)
            resolve(getters.transactionTypes)
          })
          .catch(error => {
            dispatch('error', 'Fehler beim Laden der Betten.')
            reject(error)
          })
          .finally(() => {
            commit('loading', { transactionTypes: false })
          })
      })
    }
  }
}

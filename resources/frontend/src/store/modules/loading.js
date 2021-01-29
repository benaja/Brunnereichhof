export default {
  state: {
    isLoading: {
      roles: false,
      rooms: false,
      beds: false,
      employees: false,
      customers: false,
      workers: false,
      cultures: false,
      authorizationRules: false,
      dashboard: false,
      evaluation: false,
      worktypes: false,
      settings: false,
      transactionTypes: false,
      transactions: false,
      tools: false
    }
  },
  getters: {
    isLoading: state => state.isLoading
  },
  mutations: {
    loading: (state, isLoading) => {
      for (const key in isLoading) {
        state.isLoading[key] = isLoading[key]
      }
    }
  }
}

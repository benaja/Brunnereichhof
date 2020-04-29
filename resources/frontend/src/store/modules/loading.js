export default {
  state: {
    isLoading: {
      rooms: false,
      beds: false,
      employees: false
    }
  },
  getters: {
    isLoading: state => state.isLoading
  },
  mutations: {
    loadingRooms(state, isLoading) {
      state.isLoading.rooms = isLoading
    },
    loadingBeds(state, isLoading) {
      state.isLoading.beds = isLoading
    },
    loadingEmployees(state, isLoading) {
      state.isLoading.employees = isLoading
    }
  }
}

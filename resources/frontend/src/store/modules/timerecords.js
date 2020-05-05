import axios from 'axios'

export default {
  state: {
    worktypes: []
  },
  getters: {
    worktypes: state => state.worktypes
  },
  mutations: {
    setWorktypes(state, worktypes) {
      state.worktypes = worktypes
    }
  },
  actions: {
    fetchWorktypes({ commit, getters, dispatch }) {
      return new Promise((resolve, reject) => {
        commit('loading', { worktypes: true })
        axios
          .get('worktypes')
          .then(response => {
            commit('setWorktypes', response.data)
            resolve(getters.worktypes)
          })
          .catch(error => {
            dispatch('error', 'Fehler beim Laden der Leistungsarten.')
            reject(error)
          })
          .finally(() => {
            commit('loading', { worktypes: false })
          })
      })
    }
  }
}

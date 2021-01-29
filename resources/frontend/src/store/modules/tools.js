import axios from 'axios'

export default {
  state: {
    tools: []
  },
  getters: {
    tools: state => state.tools.filter(r => !r.deleted_at)
  },
  mutations: {
    setTools(state, tools) {
      state.tools = tools
    },
    addTool(state, tool) {
      state.tools.push(tool)
    },
    updateTool(state, value) {
      const tool = state.tools.find(r => r.id === value.id)
      const index = state.tools.indexOf(tool)
      state.tools[index] = tool
    },
    removeTool(state, toolId) {
      const tool = state.tools.find(t => t.id === toolId)
      const index = state.tools.indexOf(tool)
      state.tools.splice(index, 1)
    }
  },
  actions: {
    fetchTools({ commit, getters, dispatch }) {
      return new Promise((resolve, reject) => {
        commit('loading', { tools: true })
        axios
          .get('/tools')
          .then(({ data }) => {
            commit('setTools', data.data)
            resolve(getters.tools)
          })
          .catch(error => {
            dispatch('alert', { text: 'Fehler beim Laden der Werkzeuge.', type: 'error' })
            reject(error)
          })
          .finally(() => {
            commit('loading', { tools: false })
          })
      })
    },
    updateTool({ commit }, tool) {
      return new Promise((resolve, reject) => {
        axios
          .put(`tools/${tool.id}`, tool)
          .then(() => {
            commit('updateTool', tool)
            resolve(tool)
          })
          .catch(error => reject(error))
      })
    },
    deleteTool({ commit }, toolId) {
      return new Promise((resolve, reject) => {
        axios
          .delete(`tools/${toolId}`)
          .then(() => {
            commit('removeTool', toolId)
            resolve()
          })
          .catch(error => reject(error))
      })
    }
  }
}

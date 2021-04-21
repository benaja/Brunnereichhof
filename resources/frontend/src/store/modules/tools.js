import axios from 'axios'
import i18n from '@/plugins/i18n'

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
      state.tools[index] = value
      state.tools = [...state.tools]
    },
    removeTool(state, toolId) {
      const tool = state.tools.find(t => t.id === toolId)
      const index = state.tools.indexOf(tool)
      state.tools.splice(index, 1)
    }
  },
  actions: {
    async fetchTools({ commit, getters, dispatch }, params) {
      commit('loading', { tools: true })

      const { data } = await axios.get('/tools', {
        params
      }).catch(error => {
        dispatch('alert', { text: i18n.t('fehler-beim-laden-der-werkzeuge'), type: 'error' })
        throw error
      }).finally(() => {
        commit('loading', { tools: false })
      })

      commit('setTools', data.data)
      return getters.tools
    },
    async updateTool({ commit }, tool) {
      const formData = new FormData()
      for (const key of Object.keys(tool)) {
        formData.append(key, tool[key] || '')
      }

      const { data } = await axios.post(`tools/${tool.id}`, formData)
      commit('updateTool', data.data)
      return data.data
    },
    async createTool({ dispatch }, tool) {
      const formData = new FormData()
      for (const key of Object.keys(tool)) {
        formData.append(key, tool[key] || '')
      }

      const { data } = await axios.post('tools', formData)

      dispatch('fetchTools')
      return data.data
    },
    async deleteTool({ commit }, toolId) {
      await axios.delete(`tools/${toolId}`)
      commit('removeTool', toolId)
    }
  }
}

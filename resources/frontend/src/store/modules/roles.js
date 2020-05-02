import axios from 'axios'

export default {
  state: {
    roles: []
  },
  getters: {
    roles: state => state.roles
  },
  mutations: {
    setRoles(state, roles) {
      state.roles = roles
    },
    updateRole(state, updatedRole) {
      const role = state.roles.find(r => r.id === updatedRole.id)
      const index = state.roles.indexOf(role)
      state.roles[index] = updatedRole
    },
    removeRole(state, roleId) {
      const role = state.roles.find(r => r.id === roleId)
      const index = state.roles.indexOf(role)
      state.roles.splice(index, 1)
    },
    addRole(state, role) {
      state.roles.push(role)
    }
  },
  actions: {
    fetchRoles({ commit, getters }) {
      return new Promise((resolve, reject) => {
        if (getters.roles.length === 0) {
          commit('loading', { roles: true })
          axios
            .get('/roles')
            .then(response => {
              commit('setRoles', response.data)
              resolve(getters.roles)
            })
            .catch(error => {
              reject(error)
            }).finally(() => {
              commit('loading', { roles: false })
            })
        } else {
          resolve(getters.roles)
        }
      })
    },
    updateRole({ commit }, role) {
      return new Promise((resolve, reject) => {
        axios
          .put(`roles/${role.id}`, role)
          .then(() => {
            commit('updateRole', role)
            resolve(role)
          })
          .catch(error => reject(error))
      })
    },
    deleteRole({ commit }, roleId) {
      return new Promise((resolve, reject) => {
        axios
          .delete(`roles/${roleId}`)
          .then(() => {
            commit('removeRole', roleId)
            resolve()
          })
          .catch(error => reject(error))
      })
    }
  }
}

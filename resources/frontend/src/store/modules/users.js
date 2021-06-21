import axios from 'axios'
import { UserType } from '@/utils'

export default {
  state: {
    users: []
  },
  getters: {
    users: state => state.users,
    activeUsers: state => state.users.filter(u => u.isActive),
    employeesAndWorkers: state => state.users.filter(
      u => u.type_id === UserType.Worker || u.type_id === UserType.Employee
    )
  },
  mutations: {
    setUsers(state, users) {
      state.users = users
    }
  },
  actions: {
    fetchUsers({ commit, getters, dispatch }, params) {
      return new Promise((resolve, reject) => {
        commit('loading', { users: true })
        axios
          .$get('/users', {
            params: {
              ...params
            }
          })
          .then(data => {
            commit('setUsers', data)
            resolve(getters.users)
          })
          .catch(error => {
            dispatch('error', 'Fehler beim Laden der Mitarbeiter.')
            reject(error)
          })
          .finally(() => {
            commit('loading', { users: false })
          })
      })
    }
  }
}

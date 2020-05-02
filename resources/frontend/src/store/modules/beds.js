import axios from 'axios'
import moment from 'moment'

export default {
  state: {
    beds: []
  },
  getters: {
    beds: state => state.beds.filter(b => !b.deleted_at),
    allBeds: state => state.beds,
    deletedBeds: state => state.beds.filter(b => b.deleted_at)
  },
  mutations: {
    setBeds(state, beds) {
      state.beds = beds
    },
    addBed(state, bed) {
      state.beds.push(bed)
    },
    updateBed(state, updatedBed) {
      const bed = state.beds.find(b => b.id === updatedBed.id)
      const index = state.beds.indexOf(bed)
      state.beds[index] = updatedBed
    },
    deleteBed(state, bedId) {
      const bed = state.beds.find(b => b.id === bedId)
      const index = state.beds.indexOf(bed)
      bed.deleted_at = moment().format('YYYY-MM-DD HH:mm:ss')
      state.beds[index] = bed
    }
  },
  actions: {
    fetchBeds({ commit, getters, dispatch }) {
      return new Promise((resolve, reject) => {
        commit('loading', { beds: true })
        axios
          .get('/beds?all=true')
          .then(response => {
            commit('setBeds', response.data)
            resolve(getters.beds)
          })
          .catch(error => {
            dispatch('error', 'Fehler beim Laden der Betten.')
            reject(error)
          })
          .finally(() => {
            commit('loading', { beds: false })
          })
      })
    },
    updateBed({ commit }, bed) {
      return new Promise((resolve, reject) => {
        axios
          .put(`beds/${bed.id}`, bed)
          .then(() => {
            commit('updateBed', bed)
            resolve(bed)
          })
          .catch(error => reject(error))
      })
    },
    deleteBed({ commit }, bedId) {
      return new Promise((resolve, reject) => {
        axios
          .delete(`beds/${bedId}`)
          .then(() => {
            commit('deleteBed', bedId)
            resolve()
          })
          .catch(error => reject(error))
      })
    }
  }
}

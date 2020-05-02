import axios from 'axios'
import moment from 'moment'

export default {
  state: {
    workers: []
  },
  getters: {
    workers: state => state.workers.filter(w => !w.deleted_at),
    allWorkers: state => state.workers,
    deletedWorkers: state => state.workers.filter(w => w.deleted_at)
  },
  mutations: {
    setWorkers(state, workers) {
      state.workers = workers.map(w => ({
        ...w,
        name: `${w.lastname} ${w.firstname}`
      }))
    },
    addWorker(state, worker) {
      state.workers.push(worker)
    },
    updateWorker(state, updatedWorker) {
      const worker = state.workers.find(w => w.id === updatedWorker.id)
      const index = state.workers.indexOf(worker)
      state.workers[index] = updatedWorker
    },
    deleteWorker(state, workerId) {
      const worker = state.workers.find(e => e.id === workerId)
      const index = state.workers.indexOf(worker)
      worker.deleted_at = moment().format('YYYY-MM-DD HH:mm:ss')
      state.workers[index] = worker
    }
  },
  actions: {
    fetchWorkers({ commit, getters, dispatch }) {
      return new Promise((resolve, reject) => {
        commit('loading', { workers: true })
        axios
          .get('/workers?all=true')
          .then(response => {
            commit('setWorkers', response.data)
            resolve(getters.workers)
          })
          .catch(error => {
            dispatch('error', 'Fehler beim Laden der Mitarbeiter.')
            reject(error)
          })
          .finally(() => {
            commit('loading', { workers: false })
          })
      })
    },
    updateWorker({ commit }, worker) {
      return new Promise((resolve, reject) => {
        axios
          .put(`workers/${worker.id}`, worker)
          .then(() => {
            commit('updateWorker', worker)
            resolve(worker)
          })
          .catch(error => reject(error))
      })
    },
    deleteWorker({ commit }, workerId) {
      return new Promise((resolve, reject) => {
        axios
          .delete(`workers/${workerId}`)
          .then(() => {
            commit('deleteWorker', workerId)
            resolve()
          })
          .catch(error => reject(error))
      })
    }
  }
}

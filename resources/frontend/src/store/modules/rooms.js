import axios from 'axios'
import moment from 'moment'

export default {
  state: {
    rooms: []
  },
  getters: {
    rooms: state => state.rooms.filter(r => !r.deleted_at),
    allRooms: state => state.rooms,
    deletedRooms: state => state.rooms.filter(r => r.deleted_at)
  },
  mutations: {
    setRooms(state, rooms) {
      rooms.sort((a, b) => a.number - b.number)
      state.rooms = rooms
    },
    adddRoom(state, room) {
      state.rooms.push(room)
    },
    updateRoom(state, updatedRoom) {
      const room = state.rooms.find(r => r.id === updatedRoom.id)
      const index = state.rooms.indexOf(room)
      state.rooms[index] = updatedRoom
    },
    removeRoom(state, roomId) {
      const room = state.rooms.find(r => r.id === roomId)
      const index = state.rooms.indexOf(room)
      state.rooms.splice(index, 1)
    },
    deleteRoom(state, roomId) {
      const room = state.rooms.find(r => r.id === roomId)
      const index = state.rooms.indexOf(room)
      room.deleted_at = moment().format('YYYY-MM-DD HH:mm:ss')
      state.rooms[index] = room
    }
  },
  actions: {
    fetchRooms({ commit, getters, dispatch }) {
      return new Promise((resolve, reject) => {
        commit('loading', { rooms: true })
        axios
          .get('/rooms?all=true')
          .then(response => {
            commit('setRooms', response.data)
            resolve(getters.rooms)
          })
          .catch(error => {
            dispatch('alert', { text: 'Fehler beim Laden der Räume.', type: 'error' })
            reject(error)
          })
          .finally(() => {
            commit('loading', { rooms: false })
          })
      })
    },
    updateRoom({ commit }, room) {
      return new Promise((resolve, reject) => {
        axios
          .put(`rooms/${room.id}`, room)
          .then(() => {
            commit('updateRoom', room)
            resolve(room)
          })
          .catch(error => reject(error))
      })
    },
    deleteRoom({ commit }, roomId) {
      return new Promise((resolve, reject) => {
        axios
          .delete(`rooms/${roomId}`)
          .then(() => {
            commit('deleteRoom', roomId)
            resolve()
          })
          .catch(error => reject(error))
      })
    }
  }
}

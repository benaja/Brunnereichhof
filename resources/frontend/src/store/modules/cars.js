import axios from 'axios'
import i18n from '@/plugins/i18n'

export default {
  state: {
    cars: []
  },
  getters: {
    cars: state => state.cars.filter(r => !r.deleted_at)
  },
  mutations: {
    setCars(state, cars) {
      state.cars = cars
    },
    addCar(state, car) {
      state.cars.push(car)
    },
    updateCar(state, value) {
      const car = state.cars.find(c => c.id === value.id)
      const index = state.cars.indexOf(car)
      state.cars[index] = value
      state.cars = [...state.cars]
    },
    removeCar(state, carId) {
      const car = state.cars.find(c => c.id === carId)
      const index = state.cars.indexOf(car)
      state.cars.splice(index, 1)
    }
  },
  actions: {
    async fetchCars({ commit, getters, dispatch }, params) {
      commit('loading', { cars: true })

      const { data } = await axios.get('cars', {
        params
      }).catch(error => {
        dispatch('alert', { text: i18n.t('Fehler beim Laden der Autos'), type: 'error' })
        throw error
      }).finally(() => {
        commit('loading', { cars: false })
      })

      commit('setCars', data.data)
      return getters.cars
    },
    async updateCar({ commit }, car) {
      const formData = new FormData()
      for (const key of Object.keys(car)) {
        formData.append(key, car[key] || '')
      }

      const { data } = await axios.post(`cars/${car.id}`, formData)
      commit('updateCar', data.data)
      return data.data
    },
    async createCar({ dispatch }, car) {
      const formData = new FormData()
      for (const key of Object.keys(car)) {
        formData.append(key, car[key] || '')
      }

      const { data } = await axios.post('cars', formData)

      dispatch('fetchCars')
      return data.data
    },
    async deleteCar({ commit }, carId) {
      await axios.delete(`cars/${carId}`)
      commit('removeCar', carId)
    }
  }
}

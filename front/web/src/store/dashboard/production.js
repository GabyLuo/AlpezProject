import api from '../../commons/api.js'

const module = {
  namespaced: true,
  state: {
    gpiOne: [],
    dayProductionCharts: [],
    weekProductionCharts: [],
    monthProductionCharts: []
  },
  mutations: {
    gpiOne: (state, payload) => { state.gpiOne = payload },
    dayProductionCharts: (state, payload) => { state.dayProductionCharts = payload },
    weekProductionCharts: (state, payload) => { state.weekProductionCharts = payload },
    monthProductionCharts: (state, payload) => { state.monthProductionCharts = payload }
  },
  actions: {
    gpiOne: ({ commit }) => {
      return api.get('dashboard/getGpiOne').then(response => {
        commit('gpiOne', response.data)
        return response
      }).catch(error => error)
    },
    dayProductionCharts: ({ commit }) => {
      return api.get('dashboard/dayProductionCharts').then(response => {
        commit('dayProductionCharts', response.data)
        return response
      }).catch(error => error)
    },
    weekProductionCharts: ({ commit }) => {
      return api.get('dashboard/weekProductionCharts').then(response => {
        commit('weekProductionCharts', response.data)
        return response
      }).catch(error => error)
    },
    monthProductionCharts: ({ commit }) => {
      return api.get('dashboard/monthProductionCharts').then(response => {
        commit('monthProductionCharts', response.data)
        return response
      }).catch(error => error)
    }
  },
  getters: {
    gpiOne: state => state.gpiOne,
    dayProductionCharts: state => state.dayProductionCharts,
    weekProductionCharts: state => state.weekProductionCharts,
    monthProductionCharts: state => state.monthProductionCharts
  }
}
export default module

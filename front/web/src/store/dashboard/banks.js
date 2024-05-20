import api from '../../commons/api.js'

const module = {
  namespaced: true,
  state: {
    gpiOne: [],
    gpiTwo: [],
    chartOne: [],
    chartTwo: [],
    chartThree: [],
    chartTwoPayment: []
  },
  mutations: {
    gpiOne: (state, payload) => { state.gpiOne = payload },
    gpiTwo: (state, payload) => { state.gpiTwo = payload },
    chartOne: (state, payload) => { state.chartOne = payload },
    chartTwo: (state, payload) => { state.chartTwo = payload },
    chartThree: (state, payload) => { state.chartThree = payload },
    chartTwoPayment: (state, payload) => { state.chartTwoPayment = payload }
  },
  actions: {
    gpiOne: ({ commit }) => {
      return api.get('dashboard-backs/getGpiOne').then(response => {
        commit('gpiOne', response.data)
        return response
      }).catch(error => error)
    },
    gpiTwo: ({ commit }) => {
      return api.get('dashboard-backs/getGpiTwo').then(response => {
        commit('gpiTwo', response.data)
        return response
      }).catch(error => error)
    },
    chartOne: ({ commit }) => {
      return api.get('dashboard-backs/getChartOne').then(response => {
        commit('chartOne', response.data)
        return response
      }).catch(error => error)
    },
    chartTwo: ({ commit }) => {
      return api.get('dashboard-backs/getChartTwo').then(response => {
        commit('chartTwo', response.data)
        return response
      }).catch(error => error)
    },
    chartTwoPayment: ({ commit }) => {
      return api.get('dashboard-backs/getChartTwoByPayment').then(response => {
        commit('chartTwo', response.data)
        return response
      }).catch(error => error)
    },
    chartThree: ({ commit }) => {
      return api.get('dashboard-backs/getChartThree').then(response => {
        commit('chartThree', response.data)
        return response
      }).catch(error => error)
    }
  },
  getters: {
    gpiOne: state => state.gpiOne,
    gpiTwo: state => state.gpiTwo,
    chartOne: state => state.chartOne,
    chartTwo: state => state.chartTwo,
    chartTwoPayment: state => state.chartTwo,
    chartThree: state => state.chartThree
  }
}
export default module

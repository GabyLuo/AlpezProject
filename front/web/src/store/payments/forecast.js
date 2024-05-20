import api from '../../commons/api'

const module = {
  namespaced: true,
  state: {
    dataC: []
  },
  mutations: {
    dataC: (state, payload) => { state.dataC = payload }
  },
  actions: {
    dataC: ({ commit }) => {
      return api.get('invoices/getDataCalendar').then(response => {
        commit('dataC', response.data.dataC)
        return response
      }).catch(error => error)
    }
  },
  getters: {
    dataC: state => state.dataC
  }
}
export default module

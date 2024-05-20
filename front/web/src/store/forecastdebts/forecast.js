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
    dataC: ({ commit }, payload) => {
      return api.get(`forecast-debts/getDataCalendar/${payload.branch_id}`).then(response => {
        commit('dataC', response.data)
        return response
      }).catch(error => error)
    }
  },
  getters: {
    dataC: state => state.dataC
  }
}
export default module

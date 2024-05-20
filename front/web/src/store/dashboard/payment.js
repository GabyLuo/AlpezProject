import api from '../../commons/api.js'

const module = {
  namespaced: true,
  state: {
    daySale: [],
    weekSales: [],
    monthSales: [],
    dayPays: [],
    weekPays: [],
    monthPays: [],
    yearPays: [],
    daySalesCharts: [],
    weekSalesCharts: [],
    yearSalesCharts: [],
    weekSellerSalesCharts: [],
    monthSellerSalesCharts: [],
    monthSellerBoxesCharts: [],
    top10BestCustomers: []
  },
  mutations: {
    daySale: (state, payload) => { state.daySale = payload },
    weekSales: (state, payload) => { state.weekSales = payload },
    monthSales: (state, payload) => { state.monthSales = payload },
    dayPays: (state, payload) => { state.dayPays = payload },
    weekPays: (state, payload) => { state.weekPays = payload },
    monthPays: (state, payload) => { state.monthPays = payload },
    yearPays: (state, payload) => { state.yearPays = payload },
    daySalesCharts: (state, payload) => { state.daySalesCharts = payload },
    weekSalesCharts: (state, payload) => { state.weekSalesCharts = payload },
    yearSalesCharts: (state, payload) => { state.yearSalesCharts = payload },
    weekSellerSalesCharts: (state, payload) => { state.weekSellerSalesCharts = payload },
    monthSellerSalesCharts: (state, payload) => { state.monthSellerSalesCharts = payload },
    monthSellerBoxesCharts: (state, payload) => { state.monthSellerBoxesCharts = payload },
    top10BestCustomers: (state, payload) => { state.top10BestCustomers = payload }
  },
  actions: {
    daySale: ({ commit }) => {
      return api.get('dashboard-sales/daySales').then(response => {
        commit('daySale', response.data.daySalas)
        return response
      }).catch(error => error)
    },
    weekSales: ({ commit }) => {
      return api.get('dashboard-sales/weekSales').then(response => {
        commit('weekSales', response.data.weekSales)
        return response
      }).catch(error => error)
    },
    monthSales: ({ commit }) => {
      return api.get('dashboard-sales/monthSales').then(response => {
        commit('monthSales', response.data.monthSales)
        return response
      }).catch(error => error)
    },
    dayPays: ({ commit }) => {
      return api.get('dashboard-sales/dayPays').then(response => {
        commit('dayPays', response.data.dayPays)
        return response
      }).catch(error => error)
    },
    weekPays: ({ commit }) => {
      return api.get('dashboard-sales/weekPays').then(response => {
        commit('weekPays', response.data.weekPays)
        return response
      }).catch(error => error)
    },
    monthPays: ({ commit }) => {
      return api.get('dashboard-sales/monthPays').then(response => {
        commit('monthPays', response.data.monthPays)
        return response
      }).catch(error => error)
    },
    yearPays: ({ commit }) => {
      return api.get('dashboard-sales/yearPays').then(response => {
        commit('yearPays', response.data.yearPays)
        return response
      }).catch(error => error)
    },
    daySalesCharts: ({ commit }) => {
      return api.get('dashboard-sales/daySalesCharts').then(response => {
        commit('daySalesCharts', response.data.daySalesCharts)
        return response
      }).catch(error => error)
    },
    weekSalesCharts: ({ commit }) => {
      return api.get('dashboard-sales/weekSalesCharts').then(response => {
        commit('weekSalesCharts', response.data.weekSalesCharts)
        return response
      }).catch(error => error)
    },
    yearSalesCharts: ({ commit }) => {
      return api.get('dashboard-sales/yearSalesCharts').then(response => {
        commit('yearSalesCharts', response.data.yearSalesCharts)
        return response
      }).catch(error => error)
    },
    weekSellerSalesCharts: ({ commit }) => {
      return api.get('dashboard-sales/weekSellerSalesCharts').then(response => {
        commit('weekSellerSalesCharts', response.data.weekSellerSalesCharts)
        return response
      }).catch(error => error)
    },
    monthSellerSalesCharts: ({ commit }) => {
      return api.get('dashboard-sales/monthSellerSalesCharts').then(response => {
        commit('monthSellerSalesCharts', response.data.monthSellerSalesCharts)
        return response
      }).catch(error => error)
    },
    monthSellerBoxesCharts: ({ commit }) => {
      return api.get('dashboard-sales/monthSellerBoxesCharts').then(response => {
        commit('monthSellerBoxesCharts', response.data.monthSellerBoxesCharts)
        return response
      }).catch(error => error)
    },
    top10BestCustomers: ({ commit }) => {
      return api.get('dashboard-sales/top10BestCustomers').then(response => {
        commit('top10BestCustomers', response.data.top10BestCustomers)
        return response
      }).catch(error => error)
    }
  },
  getters: {
    daySale: state => state.daySale,
    weekSales: state => state.weekSales,
    monthSales: state => state.monthSales,
    dayPays: state => state.dayPays,
    weekPays: state => state.weekPays,
    monthPays: state => state.monthPays,
    yearPays: state => state.yearPays,
    daySalesCharts: state => state.daySalesCharts,
    weekSalesCharts: state => state.weekSalesCharts,
    yearSalesCharts: state => state.yearSalesCharts,
    weekSellerSalesCharts: state => state.weekSellerSalesCharts,
    monthSellerSalesCharts: state => state.monthSellerSalesCharts,
    monthSellerBoxesCharts: state => state.monthSellerBoxesCharts,
    top10BestCustomers: state => state.top10BestCustomers
  }
}
export default module

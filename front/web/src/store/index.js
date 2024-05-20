import Vue from 'vue'
import Vuex from 'vuex'

import users from './users'
import dashboard from './dashboard'
import payments from './payments'
import debts from './forecastdebts'

Vue.use(Vuex)

/*
 * If not building with SSR mode, you can
 * directly export the Store instantiation
 */

export default new Vuex.Store({
  state: {},
  mutations: {},
  actions: {},
  getters: {},
  modules: {
    users,
    dashboard,
    payments,
    debts
  }
})

import api from '../commons/api.js'

export default {
  namespaced: true,
  state: {
    user: [],
    id: null,
    nickname: null,
    roles: null,
    wasLoaded: false,
    repositories: null,
    rol: null,
    branch: null
  },
  mutations: {
    setId (state, id) {
      state.id = id
    },
    setNickname (state, nickname) {
      state.nickname = nickname
    },
    setRoles (state, roles) {
      state.roles = roles
    },
    setRol (state, rol) {
      state.rol = rol
    },
    setBranch (state, branch) {
      state.branch = branch
    },
    setWasLoaded (state, wasLoaded) {
      state.wasLoaded = wasLoaded
    },
    setRepositories (state, repositories) {
      state.repositories = repositories
    }
  },
  actions: {
    getProfile: async (context) => {
      const response = await api.get('users/profile')
      if (response.data.result) {
        context.commit('setId', response.data.user.id)
        context.commit('setNickname', response.data.user.nickname)
        context.commit('setRoles', response.data.user.roles)
        context.commit('setRol', response.data.user.rol)
        context.commit('setBranch', response.data.user.branch)
        context.commit('setRepositories', response.data.user.repositories)
        context.commit('setWasLoaded', true)
      }
      return response
    }
  },
  getters: {
    user: state => state.user,
    id: state => state.id,
    nickname: state => state.nickname,
    roles: state => state.roles,
    rol: state => state.rol,
    branch: state => state.branch,
    wasLoaded: state => state.wasLoaded,
    repositories: state => state.repositories,
    isSuperAdmin: state => {
      return 1 * state.user.role_id === 1
    }
  }
}

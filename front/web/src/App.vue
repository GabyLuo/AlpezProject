<template>
  <div id="q-app">
    <template v-if="canRender">
      <router-view />
    </template>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'App',
  data () {
    return {
      canRender: false
    }
  },
  // computed: {},
  // beforeCreate () {},
  created () {
    const JWT = localStorage.getItem('JWT')

    if (JWT !== null && JWT !== '') {
      axios.defaults.headers.common.Authorization = `Bearer ${JWT}`

      this.$store.dispatch('users/getProfile').then(() => {
        this.canRender = true
        const userWasLoaded = this.$store.getters['users/wasLoaded']

        if (!userWasLoaded) {
          this.$router.push('/login')
        }
      }).catch(error => {
        localStorage.removeItem('JWT')
        window.location.reload()
        console.error(error)
      })
    } else {
      this.canRender = true
      this.$router.push('/login')
    }
  }
  // mounted () {}
}
</script>

<style>
</style>

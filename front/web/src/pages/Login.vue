<template>
  <q-layout>
    <q-page-container style="background-color: #0e0f1f;">
      <q-page>
        <div class="row justify-center" style="padding-top: 5%;">
          <img src="../assets/logo.png" alt="Iniciar Sesión" width="300" @dblclick="magic">
        </div>
        <div class="row justify-center">
          <div class="col-sm-4 q-pa-md">
            <div class="row q-mb-sm">
              <div class="col-sm-1 offset-11 pull-right">
                <q-btn color="primary" label="New" style="visibility: hidden;" />
              </div>
            </div>

            <div class="row q-col-gutter-xs">
              <div class="col-xs-12 col-sm-12">
                <q-input dark v-model="email" filled label="Correo electrónico" type="email" v-on:keyup.enter="logIn()" />
              </div>
              <div class="col-xs-12 col-sm-12">
                <q-input dark v-model="password" filled label="Contraseña" :type="isPwd ? 'password' : 'text'" v-on:keyup.enter="logIn()">
                  <template v-slot:append>
                    <q-icon
                      :name="isPwd ? 'visibility_off' : 'visibility'"
                      class="cursor-pointer"
                      @click="isPwd = !isPwd"
                    />
                  </template>
                </q-input>
              </div>
              <div class="col-xs-12 col-sm-12 q-mt-sm pull-right">
                <q-btn color="secondary" text-color="white" label="Iniciar sesión" :loading="loading" @click="logIn()" />
              </div>
            </div>

          </div>
        </div>
      </q-page>
    </q-page-container>
  </q-layout>
</template>

<script>
import api from '../commons/api.js' // El api pasa los params por: 'qs.stringify'

export default {
  name: 'Login',
  data () {
    return {
      email: null,
      password: null,
      isPwd: true,
      loading: false
    }
  },
  // computed: {},
  // beforeCreate () {},
  created () {
    const JWT = localStorage.getItem('JWT')
    if (JWT !== null && JWT !== '') {
      this.$axios.defaults.headers.common.Authorization = `Bearer ${JWT}`

      this.$store.dispatch('users/getProfile').then(() => {
        const userWasLoaded = this.$store.getters['users/wasLoaded']
        if (userWasLoaded) {
          this.$router.push('/dashboard')
        } else {
          // Se queda en LOGIN
        }
      }).catch(error => {
        localStorage.removeItem('JWT')
        window.location.reload()
        console.error(error)
      })
    } else {
      // Se queda en LOGIN
    }
  },
  // mounted () {},
  methods: {
    magic () {
      this.email = 'sa@wasp.mx'
      this.password = '4NT12345'
    },
    logIn () {
      console.log(api)
      const params = {
        email: this.email,
        password: this.password
      }
      this.loading = true
      api.post('/auth/login', params).then(({ data }) => {
        this.loading = false
        if (data.result) {
          localStorage.setItem('JWT', data.jwt)
          this.$axios.defaults.headers.common.Authorization = `Bearer ${data.jwt}`

          this.$store.dispatch('users/getProfile').then(({ data }) => {
            this.$router.push(data.result ? '/' : '/login')
          }).catch(error => {
            localStorage.removeItem('JWT')
            window.location.reload()
            console.error(error)
          })
        } else {
          this.$q.dialog({
            title: data.message.title,
            message: data.message.content,
            persistent: true
          })
        }
      }).catch(error => {
        this.loading = false
        console.error(error)
      })
    },
    nope () {
      // NOPE
    }
  }
}
</script>

<style>
</style>

<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-9">
          <span class="q-ml-md grey-8 fs28 page-title">Perfil {{ user.fields.email }}</span>
        </div>
        <div class="col-sm-3">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="right">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Usuarios" to="/users" />
              <q-breadcrumbs-el label="Editar" />
            </q-breadcrumbs>
          </div>
        </div>
      </div>
    </div>

    <div class="q-pa-md bg-grey-3">
      <div class="row bg-white border-panel">
        <div class="col q-pa-md">
          <div class="row q-mb-sm" style="visibility: hidden;">
            <div class="col-sm-1 offset-11 pull-right">
              <q-btn color="primary" label="Editar" />
            </div>
          </div>

          <div class="row q-col-gutter-xs">
            <div class="col-xs-12 col-sm-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="user.fields.email"
                :error="$v.user.fields.email.$error"
                label="Dirección de correo electrónico"
                :rules="emailRules"
                @input="v => { user.fields.email = v.toLowerCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-at" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-4">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="user.fields.nickname"
                :error="$v.user.fields.nickname.$error"
                label="Nickname"
                :rules="nicknameRules"
                @input="v => { user.fields.nickname = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-signature" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-2">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="user.fields.password"
                :error="$v.user.fields.password.$error"
                label="Contraseña"
                :type="isPwd ? 'password' : 'text'"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-key" />
                </template>
                <template v-slot:append>
                  <q-icon
                    :name="isPwd ? 'visibility_off' : 'visibility'"
                    class="cursor-pointer"
                    @click="isPwd = !isPwd"
                  />
                </template>
              </q-input>
            </div>
          </div>

          <div class="row q-mb-sm q-mt-md">
            <div class="col-xs-12 col-sm-2 offset-sm-10 pull-right">
              <q-btn color="positive" icon="save" label="Guardar" @click="editUser()" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </q-page>
</template>

<script>
import api from '../../commons/api.js'
const { required, maxLength, email } = require('vuelidate/lib/validators')

export default {
  name: 'Profile',
  validations: {
    user: {
      fields: {
        email: { required, maxLength: maxLength(100), email },
        nickname: { required, maxLength: maxLength(100) },
        password: { }
      }
    }
  },
  data () {
    return {
      user: {
        fields: {
          id: null,
          email: null,
          nickname: null,
          password: null
        }
      },
      isPwd: true
    }
  },
  computed: {
    emailRules (val) {
      return [
        val => (this.$v.user.fields.email.required) || 'El campo Dirección de correo electrónico es requerido.',
        val => (this.$v.user.fields.email.maxLength) || 'El campo Dirección de correo electrónico no debe exceder los 100 dígitos.',
        val => (this.$v.user.fields.email.email) || 'El campo Dirección de correo electrónico debe contener una dirección de correo electrónico válida.'
      ]
    },
    nicknameRules (val) {
      return [
        val => (this.$v.user.fields.nickname.required) || 'El campo Nickname es requerido.',
        val => (this.$v.user.fields.nickname.maxLength) || 'El campo Nickname no debe exceder los 100 dígitos.'
      ]
    }
  },
  created () {
    this.fetchFromServer()
  },
  methods: {
    fetchFromServer () {
      api.get('/users/profile').then(({ data }) => {
        if (!data.user) {
          this.$router.push('/')
        } else {
          this.user.fields = data.user
          this.$q.loading.hide()
        }
      })
    },
    editUser () {
      this.$v.user.fields.$reset()
      this.$v.user.fields.$touch()
      if (this.$v.user.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = { ...this.user.fields }
      api.put('/users/profile', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: data.result ? 'positive' : 'warning'
        })
        if (data.result) {
          this.$q.loading.hide()
          window.location.reload()
        } else {
          this.$q.loading.hide()
          if (data.errors && data.errors.message === 'Expired token') {
            localStorage.removeItem('JWT')
            window.location.reload()
          }
        }
      })
    }
  }
}
</script>

<style>
</style>

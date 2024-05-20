<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-9">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Usuarios" to="/users" />
              <q-breadcrumbs-el label="Nuevo Usuario" />
            </q-breadcrumbs>
          </div>
        </div>
      </div>
    </div>

    <div class="q-pa-md bg-grey-3">
      <div class="row bg-white border-panel">
        <div class="col q-pa-md">
          <div class="row q-mb-sm">

          </div>

          <div class="row q-col-gutter-xs">
            <div class="col-xs-12 col-sm-4">
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
            <div class="col-xs-12 col-sm-4">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="user.fields.password"
                :error="$v.user.fields.password.$error"
                label="Contraseña"
                :rules="passwordRules"
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
              <q-btn color="positive" icon="save" label="Guardar" @click="createUser()" />
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
  name: 'NewUser',
  validations: {
    user: {
      fields: {
        email: { required, maxLength: maxLength(100), email },
        nickname: { required, maxLength: maxLength(100) },
        password: { required }
      }
    }
  },
  data () {
    return {
      user: {
        fields: {
          email: null,
          nickname: null,
          password: null
        }
      },
      isPwd: true
    }
  },
  beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7)) {
      this.$router.push('/')
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
    },
    passwordRules (val) {
      return [
        val => (this.$v.user.fields.password.required) || 'El campo Contraseña es requerido.'
      ]
    }
  },
  methods: {
    createUser () {
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
      api.post('/users', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.$router.push(`/users/${data.userId}`)
        } else {
          this.$q.loading.hide()
        }
      })
    }
  }
}
</script>

<style>
</style>

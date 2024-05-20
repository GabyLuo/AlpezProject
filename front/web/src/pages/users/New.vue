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
          <div class="row q-col-gutter-xs">
            <div class="col-xs-12 col-sm-3 text-center" v-if="user.fields.role !== 26">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="user.fields.branch_id"
                :options="branchesList"
                label="Estaciones"
                :error="$v.user.fields.branch_id.$error"
                :rules="branchOfficeRules"
                emit-value
                map-options>
                  <template v-slot:prepend>
                    <q-icon name="business"></q-icon>
                  </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-3 text-center" v-else>
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="user.fields.cluster_id"
                :options="clusterOptions"
                label="Cluster"
                emit-value
                map-options>
                  <template v-slot:prepend>
                    <q-icon name="business"></q-icon>
                  </template>
              </q-select>
            </div>
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
            <div class="col-xs-12 col-sm-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="user.fields.nickname"
                :error="$v.user.fields.nickname.$error"
                label="Nombre Completo"
                :rules="nicknameRules"
                @input="v => { user.fields.nickname = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-signature" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-3">
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
            <div class="col-xs-12 col-sm-3">
              <q-select class="uppcase" color="dark" borderless bg-color="secondary" filled v-model="user.fields.role" label="Rol" :options="selectRoles"  :error="$v.user.fields.role.$error" emit-value map-options></q-select>
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
        password: { required },
        branch_id: { required },
        role: { required }
      }
    }
  },
  data () {
    return {
      user: {
        fields: {
          email: null,
          nickname: null,
          password: null,
          branch_id: null,
          role: null,
          cluster_id: null
        }
      },
      isPwd: true,
      branchesList: [],
      selectRoles: [],
      clusterOptions: []
    }
  },
  /* beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7)) {
      this.$router.push('/')
    }
  }, */
  beforeRouteEnter (to, from, next) {
    next(vm => {
      const propiedades = vm.$store.getters['users/rol']
      console.log(propiedades)
      if (propiedades === 1 || propiedades === 3 || propiedades === 7) {
        next()
      } else {
        next('/')
      }
    })
  },
  created () {
    this.getRoles()
    this.getBranchesList()
    this.getClusterList()
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
    },
    branchOfficeRules (val) {
      return [
        val => (this.$v.user.fields.branch_id.required) || 'El campo Cluster es requerido.'
      ]
    }
  },
  methods: {
    createUser () {
      if (this.user.fields.role === 26) {
        this.user.fields.branch_id = 0
      }
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
    },
    getBranchesList () {
      api.get('branch-offices/options').then(data => {
        this.branchesList = data.data.options
      })
    },
    getClusterList () {
      api.get('supercluster/getOptions').then(data => {
        this.clusterOptions = data.data.options
      })
    },
    getRoles () {
      this.selectRoles = []
      api.get('/roles').then(({ data }) => {
        if (data.roles.length > 0) {
          data.roles.forEach(rol => {
            this.selectRoles.push({ label: rol.name, value: rol.id })
          })
        }
      }).catch(error => error)
    }
  }
}
</script>

<style>
</style>

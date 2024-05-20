<template>
  <q-page class="bg-grey-3">

      <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-6">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left">
              <q-breadcrumbs-el class="fs20" label="" icon="home" to="/" />
              <q-breadcrumbs-el class="fs20" label="Roles" to="/roles" />
              <q-breadcrumbs-el class="fs20" label="Editar" />
            </q-breadcrumbs>
          </div>
        </div>
      </div>
    </div>

    <div class="q-pa-md bg-grey-3">
      <div class="row bg-white border-panel">
        <div class="col q-pa-md">

          <div class="row q-col-gutter-xs">
            <div class="col-xs-12 col-sm-4">
              <q-input class="uppcase" color="white" bg-color="secondary" filled dark v-model="rol.fields.name" :error="$v.rol.fields.name.$error" label="ROL">
                <template v-slot:prepend>
                  <q-icon name="fas fa-user" />
                </template>
              </q-input>
            </div>
          </div>
          <div class="row q-mb-sm">
            <div class="col-xs-12 col-sm-2 offset-sm-10 pull-right">
              <q-btn color="positive" icon="save" label="Guardar" @click="editRol()" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </q-page>
</template>

<script>
import api from '../../commons/api.js'
const { required, maxLength } = require('vuelidate/lib/validators')

export default {
  name: 'EditUser',
  validations: {
    rol: {
      fields: {
        name: { required, maxLength: maxLength(50) }
      }
    }
  },
  /* beforeRouteEnter (to, from, next) {
    next(vm => {
      const propiedades = vm.$store.getters['users/user']
      if (propiedades.role_id === 1) {
        next()
      } else {
        next('/')
      }
    })
  }, */
  data () {
    return {
      rol: {
        fields: {
          id: null,
          name: null
        }
      }
    }
  },
  computed: {
  },
  created () {
    this.fetchFromServer()
  },
  methods: {
    async loadAll () {
    },
    fetchFromServer () {
      this.$q.loading.show()
      const id = this.$route.params.id
      api.get(`/roles/${id}`).then(({ data }) => {
        if (!data.roles) {
          this.$router.push('/roles')
        } else {
          this.rol.fields.id = data.roles.id
          this.rol.fields.name = data.roles.name
          this.$q.loading.hide()
        }
      })
    },
    editRol () {
      this.$v.rol.fields.$reset()
      this.$v.rol.fields.$touch()
      if (this.$v.rol.fields.$error) {
        this.$q.notify({
          message: 'Por favor revise los campos.',
          color: 'red',
          position: 'top'
        })
        return false
      }
      this.showLoading()
      const params = { ...this.rol.fields }
      api.put(`/roles/${params.id}`, params).then(({ data }) => {
        if (data.result) {
          if (data.result) {
            this.$router.push('/roles')
          }
        }
        this.$q.notify({
          message: data.message.content,
          color: data.result ? 'positive' : 'red',
          position: 'top'
        })
        this.beforeDestroy()
      })
    },
    showLoading () {
      this.$q.loading.show({
        spinnerColor: 'primary',
        spinnerSize: 140,
        backgroundColor: 'white',
        message: 'Cargando..',
        messageColor: 'black'
      })
    },
    beforeDestroy () {
      this.$q.loading.hide()
    }
  }
}
</script>

<style>
.uppcase{
  text-transform: uppercase
}
</style>

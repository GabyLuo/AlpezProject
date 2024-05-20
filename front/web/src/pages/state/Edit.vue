<template>
  <q-page>
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-9">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Estado" to="/state" />
              <q-breadcrumbs-el label="Editar estado" v-text= "state.fields.nombre"/>
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
            <div class="col-xs-12 col-sm-12">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="state.fields.nombre"
                :error="$v.state.fields.nombre.$error"
                label="Nombre"
                :rules="nameRules"
                @input="v => { state.fields.nombre = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon nombre="fas fa-signature" />
                </template>
              </q-input>
            </div>
          </div>

          <div class="row q-mb-sm q-mt-md">
            <div class="col-xs-12 col-sm-2 offset-sm-10 pull-right">
              <q-btn color="positive" icon="save" label="Actualizar" @click="editState()" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </q-page>
</template>

<script>
import api from '../../commons/api.js'
const { required } = require('vuelidate/lib/validators')

export default {
  name: 'EditState',
  validations: {
    state: {
      fields: {
        nombre: { required }
      }
    }
  },
  data () {
    return {
      state: {
        fields: {
          nombre: null
        }
      }
    }
  },
  computed: {
    nameRules (val) {
      return [
        val => (this.$v.state.fields.nombre.required) || 'El campo Nombre es requerido.'
      ]
    }
  },
  beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(8)) {
      this.$router.push('/')
    }
  },
  created () {
    this.fetchFromServer()
  },
  methods: {
    fetchFromServer () {
      this.$q.loading.show()
      const id = this.$route.params.id
      api.get(`/state/${id}`).then(({ data }) => {
        if (!data.state) {
          this.$router.push('/state')
        } else {
          this.state.fields = data.state
          this.$q.loading.hide()
        }
      })
    },
    editState () {
      this.$v.state.fields.$reset()
      this.$v.state.fields.$touch()
      if (this.$v.state.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = { ...this.state.fields }
      api.put(`/state/${params.id}`, params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.$router.push('/state')
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

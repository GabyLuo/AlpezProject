<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-9">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Incidencias" to="/incidencia_type" />
              <q-breadcrumbs-el label="Nueva Incidencia" />
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
            <div class="col-xs-12 col-sm-6">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="incidencia.fields.code"
                :error="$v.incidencia.fields.code.$error"
                label="Código"
                :rules="codeRules"
                @input="v => { incidencia.fields.code = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="folder" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-6">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="incidencia.fields.name"
                :error="$v.incidencia.fields.name.$error"
                label="Nombre"
                :rules="nameRules"
                @input="v => { incidencia.fields.name = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-signature" />
                </template>
              </q-input>
            </div>
          </div>

          <div class="row q-mb-sm q-mt-md">
            <div class="col-xs-12 col-sm-2 offset-sm-10 pull-right">
              <q-btn color="positive" icon="save" label="Guardar" @click="createIncidencia()" />
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
  name: 'Newincidencia',
  validations: {
    incidencia: {
      fields: {
        name: { required, maxLength: maxLength(100) },
        code: { required, maxLength: maxLength(20) }
      }
    }
  },
  data () {
    return {
      incidencia: {
        fields: {
          name: null,
          code: null
        }
      }
    }
  },
  computed: {
    nameRules (val) {
      return [
        val => (this.$v.incidencia.fields.name.required) || 'El campo Nombre es requerido.',
        val => (this.$v.incidencia.fields.name.maxLength) || 'El campo Nombre no debe exceder los 100 dígitos.'
      ]
    },
    codeRules (val) {
      return [
        val => (this.$v.incidencia.fields.code.required) || 'El campo Código es requerido.',
        val => (this.$v.incidencia.fields.code.maxLength) || 'El campo Código no debe exceder los 30 dígitos.'
      ]
    }
  },
  beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(4) && !this.$store.getters['users/roles'].includes(6)) {
      this.$router.push('/')
    }
  },
  methods: {
    createIncidencia () {
      this.$v.incidencia.fields.$reset()
      this.$v.incidencia.fields.$touch()
      if (this.$v.incidencia.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = { ...this.incidencia.fields }
      api.post('/incidencias', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.$router.push('/incidencia_type')
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

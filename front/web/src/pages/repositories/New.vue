<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-9">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Dirección" to="/repositories" />
              <q-breadcrumbs-el label="Nueva Dirección" />
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
                v-model="info.fields.name"
                :error="$v.info.fields.name.$error"
                label="Nombre"
                :rules="nameRules"
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
                v-model="info.fields.icon"
                :error="$v.info.fields.icon.$error"
                label="Icono"
                :rules="iconRules"
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
                v-model="info.fields.sequence"
                :error="$v.info.fields.sequence.$error"
                label="Orden"
                :rules="sequenceRules"
                @input="v => { info.fields.sequence = v.replace(/[^0-9\\.]/g, '') }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-signature" />
                </template>
              </q-input>
            </div>
          </div>

          <div class="row q-mb-sm q-mt-md">
            <div class="col-xs-12 col-sm-2 offset-sm-10 pull-right">
              <q-btn color="positive" icon="save" label="Guardar" @click="create()" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </q-page>
</template>

<script>
import api from '../../commons/api.js'
const { required, decimal, maxLength } = require('vuelidate/lib/validators')

export default {
  name: 'New',
  validations: {
    info: {
      fields: {
        name: { required, maxLength: maxLength(50) },
        icon: { required, maxLength: maxLength(50) },
        sequence: { decimal, required, maxLength: maxLength(50) }
      }
    }
  },
  data () {
    return {
      info: {
        fields: {
          name: null,
          icon: null,
          sequence: null
        }
      }
    }
  },
  computed: {
    nameRules (val) {
      return [
        val => (this.$v.info.fields.name.required) || 'El campo Nombre es requerido.',
        val => (this.$v.info.fields.name.maxLength) || 'El campo Nombre no debe exceder los 50 dígitos.'
      ]
    },
    iconRules (val) {
      return [
        val => (this.$v.info.fields.name.required) || 'El campo Icono es requerido.',
        val => (this.$v.info.fields.name.maxLength) || 'El campo Icono no debe exceder los 50 dígitos.'
      ]
    },
    sequenceRules (val) {
      return [
        val => (this.$v.info.fields.name.required) || 'El campo Orden es requerido.',
        val => (this.$v.info.fields.name.maxLength) || 'El campo Orden no debe exceder los 50 dígitos.'
      ]
    }
  },
  beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1)) {
      this.$router.push('/')
    }
  },
  methods: {
    create () {
      this.$v.info.fields.$reset()
      this.$v.info.fields.$touch()
      if (this.$v.info.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = { ...this.info.fields }
      api.post('/repositories', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.$router.push('/repositories')
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

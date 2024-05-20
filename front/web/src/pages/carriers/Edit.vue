<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-9">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Paqueterías" to="/carriers" />
              <q-breadcrumbs-el label="Editar Paquetería" v-text= "carrier.fields.name"/>
            </q-breadcrumbs>
          </div>
        </div>
        <!-- <div class="col-sm-3">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="right">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Categorías" to="/categories" />
              <q-breadcrumbs-el label="Editar" />
            </q-breadcrumbs>
          </div>
        </div> -->
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
            <div class="col-xs-12 col-sm-2">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="carrier.fields.code"
                :error="$v.carrier.fields.code.$error"
                label="Código"
                :rules="codeRules"
                @input="v => { carrier.fields.code = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fingerprint" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-4">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="carrier.fields.name"
                :error="$v.carrier.fields.name.$error"
                label="Nombre"
                :rules="nameRules"
                @input="v => { carrier.fields.name = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-signature" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-6">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="carrier.fields.url"
                label="Nombre"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-signature" />
                </template>
              </q-input>
            </div>
          </div>

          <div class="row q-mb-sm q-mt-md">
            <div class="col-xs-12 col-sm-2 offset-sm-10 pull-right">
              <q-btn color="positive" icon="save" label="Actualizar" @click="editCarrier()" />
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
  name: 'EditCarrier',
  validations: {
    carrier: {
      fields: {
        name: { required, maxLength: maxLength(50) },
        code: { required, maxLength: maxLength(3) }
      }
    }
  },
  data () {
    return {
      carrier: {
        fields: {
          id: null,
          name: null,
          code: null,
          url: null
        }
      }
    }
  },
  computed: {
    nameRules (val) {
      return [
        val => (this.$v.carrier.fields.name.required) || 'El campo Nombre es requerido.',
        val => (this.$v.carrier.fields.name.maxLength) || 'El campo Nombre no debe exceder los 50 dígitos.'
      ]
    },
    codeRules (val) {
      return [
        val => (this.$v.carrier.fields.code.required) || 'El campo Código es requerido.',
        val => (this.$v.carrier.fields.code.maxLength) || 'El campo Código no debe exceder los 3 dígitos.'
      ]
    }
  },
  beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(2) && !this.$store.getters['users/roles'].includes(22)) {
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
      api.get(`/carriers/${id}`).then(({ data }) => {
        if (!data.carrier) {
          this.$router.push('/carriers')
        } else {
          this.carrier.fields = data.carrier
          this.$q.loading.hide()
        }
      })
    },
    updateCarrierFields () {
      this.$v.carrier.fields.$reset()
      this.$v.carrier.fields.$touch()
    },
    editCarrier () {
      this.$v.carrier.fields.$reset()
      this.$v.carrier.fields.$touch()
      if (this.$v.carrier.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = { ...this.carrier.fields }
      api.put(`/carriers/${params.id}`, params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.$router.push('/carriers')
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

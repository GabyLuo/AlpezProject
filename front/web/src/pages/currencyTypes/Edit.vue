<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-9">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Tipo de Moneda" to="/currencies" />
              <q-breadcrumbs-el label="Editar" v-text= "currency.fields.name"/>
            </q-breadcrumbs>
          </div>
        </div>
        <!-- <div class="col-sm-3">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="right">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Categorías" to="/currencies" />
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
            <div class="col-xs-12 col-sm-6">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="currency.fields.code"
                :error="$v.currency.fields.code.$error"
                label="Código"
                :rules="codeRules"
                @input="v => { currency.fields.code = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fingerprint" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-6">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="currency.fields.name"
                :error="$v.currency.fields.name.$error"
                label="Nombre"
                :rules="nameRules"
                @input="v => { currency.fields.name = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-signature" />
                </template>
              </q-input>
            </div>
          </div>

          <div class="row q-mb-sm q-mt-md">
            <div class="col-xs-12 col-sm-2 offset-sm-10 pull-right">
              <q-btn color="positive" icon="save" label="Actualizar" @click="editCurrency()" />
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
  name: 'EditCategory',
  validations: {
    currency: {
      fields: {
        name: { required, maxLength: maxLength(20) },
        code: { required, maxLength: maxLength(5) }
      }
    }
  },
  data () {
    return {
      currency: {
        fields: {
          id: null,
          name: null,
          code: null
        }
      }
    }
  },
  computed: {
    nameRules (val) {
      return [
        val => (this.$v.currency.fields.name.required) || 'El campo Nombre es requerido.',
        val => (this.$v.currency.fields.name.maxLength) || 'El campo Nombre no debe exceder los 20 dígitos.'
      ]
    },
    codeRules (val) {
      return [
        val => (this.$v.currency.fields.code.required) || 'El campo Código es requerido.',
        val => (this.$v.currency.fields.code.maxLength) || 'El campo Código no debe exceder los 5 dígitos.'
      ]
    }
  },
  beforeRouteEnter (to, from, next) {
    next(vm => {
      const propiedades = vm.$store.getters['users/rol']
      console.log(propiedades)
      if (propiedades === 1 || propiedades === 3 || propiedades === 7 || propiedades === 2 || propiedades === 20 || propiedades === 4 || propiedades === 27) {
        next()
      } else {
        next('/')
      }
    })
  },
  /* beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(10)) {
      this.$router.push('/')
    }
  }, */
  created () {
    this.fetchFromServer()
  },
  methods: {
    fetchFromServer () {
      this.$q.loading.show()
      const id = this.$route.params.id
      api.get(`/currencies/${id}`).then(({ data }) => {
        if (!data.currency) {
          this.$router.push('/currencies')
        } else {
          this.currency.fields = data.currency
          this.$q.loading.hide()
        }
      })
    },
    updateCategoryFields () {
      this.$v.currency.fields.$reset()
      this.$v.currency.fields.$touch()
    },
    editCurrency () {
      this.$v.currency.fields.$reset()
      this.$v.currency.fields.$touch()
      if (this.$v.currency.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = { ...this.currency.fields }
      api.put(`/currencies/${params.id}`, params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.$router.push('/currencies')
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

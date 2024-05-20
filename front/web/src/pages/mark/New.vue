<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-9">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Marcas" to="/marks" />
              <q-breadcrumbs-el label="Nueva Marca" />
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
                v-model="mark.fields.code"
                :error="$v.mark.fields.code.$error"
                label="Código"
                :rules="codeRules"
                @input="v => { mark.fields.code = v.toUpperCase() }"
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
                v-model="mark.fields.name"
                :error="$v.mark.fields.name.$error"
                label="Nombre"
                :rules="nameRules"
                @input="v => { mark.fields.name = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-signature" />
                </template>
              </q-input>
            </div>
          </div>

          <div class="row q-mb-sm q-mt-md">
            <div class="col-xs-12 col-sm-2 offset-sm-10 pull-right">
              <q-btn color="positive" icon="save" label="Guardar" @click="createMark()" />
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
  name: 'NewLine',
  validations: {
    mark: {
      fields: {
        name: { required, maxLength: maxLength(50) },
        code: { required, maxLength: maxLength(5) }
      }
    }
  },
  data () {
    return {
      mark: {
        fields: {
          name: null,
          code: null
        }
      },
      categoryOptions: []
    }
  },
  computed: {
    nameRules (val) {
      return [
        val => (this.$v.mark.fields.name.required) || 'El campo Nombre es requerido.',
        val => (this.$v.mark.fields.name.maxLength) || 'El campo Nombre no debe exceder los 50 dígitos.'
      ]
    },
    codeRules (val) {
      return [
        val => (this.$v.mark.fields.code.required) || 'El campo Código es requerido.',
        val => (this.$v.mark.fields.code.maxLength) || 'El campo Código no debe exceder los 5 dígitos.'
      ]
    },
    categoryRules (val) {
      return [
        val => this.$v.mark.fields.category.required || 'El campo Categoría es requerido.'
      ]
    }
  },
  beforeRouteEnter (to, from, next) {
    next(vm => {
      const propiedades = vm.$store.getters['users/rol']
      console.log(propiedades)
      if (propiedades === 1 || propiedades === 3 || propiedades === 7 || propiedades === 2 || propiedades === 20 || propiedades === 4 || propiedades === 27 || propiedades === 22) {
        next()
      } else {
        next('/')
      }
    })
  },
  /* beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(2) && !this.$store.getters['users/roles'].includes(22)) {
      this.$router.push('/')
    }
  }, */
  created () {
    this.fetchFromServer()
  },
  mounted () {},
  methods: {
    fetchFromServer () {
      // this.$q.loading.show()
    },
    createMark () {
      this.$v.mark.fields.$reset()
      this.$v.mark.fields.$touch()
      if (this.$v.mark.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = []
      params.code = { ...this.mark.fields }.code
      params.name = { ...this.mark.fields }.name
      api.post('/marks', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.$router.push('/marks')
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

<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-9">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Subcategorías" to="/lines" />
              <q-breadcrumbs-el label="Nueva Subcategoría" />
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
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="line.fields.category"
                :options="categoryOptions"
                label="Categorías"
                :rules="categoryRules"
              >
                <template v-slot:prepend>
                  <q-icon name="category" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-4">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="line.fields.code"
                :error="$v.line.fields.code.$error"
                label="Código"
                :rules="codeRules"
                @input="v => { line.fields.code = v.toUpperCase() }"
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
                v-model="line.fields.name"
                :error="$v.line.fields.name.$error"
                label="Nombre"
                :rules="nameRules"
                @input="v => { line.fields.name = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-signature" />
                </template>
              </q-input>
            </div>
          </div>

          <div class="row q-mb-sm q-mt-md">
            <div class="col-xs-12 col-sm-2 offset-sm-10 pull-right">
              <q-btn color="positive" icon="save" label="Guardar" @click="createLine()" />
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
    line: {
      fields: {
        name: { required, maxLength: maxLength(50) },
        code: { required, maxLength: maxLength(5) },
        category: { required }
      }
    }
  },
  data () {
    return {
      line: {
        fields: {
          name: null,
          code: null,
          category: null
        }
      },
      categoryOptions: []
    }
  },
  computed: {
    nameRules (val) {
      return [
        val => (this.$v.line.fields.name.required) || 'El campo Nombre es requerido.',
        val => (this.$v.line.fields.name.maxLength) || 'El campo Nombre no debe exceder los 50 dígitos.'
      ]
    },
    codeRules (val) {
      return [
        val => (this.$v.line.fields.code.required) || 'El campo Código es requerido.',
        val => (this.$v.line.fields.code.maxLength) || 'El campo Código no debe exceder los 5 dígitos.'
      ]
    },
    categoryRules (val) {
      return [
        val => this.$v.line.fields.category.required || 'El campo Categoría es requerido.'
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
      this.$q.loading.show()
      api.get('/categories/options').then(({ data }) => {
        this.categoryOptions = data.options
        this.$q.loading.hide()
      })
    },
    createLine () {
      this.$v.line.fields.$reset()
      this.$v.line.fields.$touch()
      if (this.$v.line.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = []
      params.code = { ...this.line.fields }.code
      params.name = { ...this.line.fields }.name
      params.category_id = { ...this.line.fields }.category.value
      api.post('/lines', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.$router.push('/lines')
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

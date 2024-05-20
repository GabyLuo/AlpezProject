<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-9">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Categorías" to="/categories" />
              <q-breadcrumbs-el label="Nueva Categoría" />
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
                v-model="category.fields.code"
                :error="$v.category.fields.code.$error"
                label="Código"
                :rules="codeRules"
                @input="v => { category.fields.code = v.toUpperCase() }"
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
                v-model="category.fields.name"
                :error="$v.category.fields.name.$error"
                label="Nombre"
                :rules="nameRules"
                @input="v => { category.fields.name = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-signature" />
                </template>
              </q-input>
            </div>
          </div>

          <div class="row q-mb-sm q-mt-md">
            <div class="col-xs-12 col-sm-2 offset-sm-10 pull-right">
              <q-btn color="positive" icon="save" label="Guardar" @click="createCategory()" />
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
  name: 'NewCategory',
  validations: {
    category: {
      fields: {
        name: { required, maxLength: maxLength(50) },
        code: { required, maxLength: maxLength(5) }
      }
    }
  },
  data () {
    return {
      category: {
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
        val => (this.$v.category.fields.name.required) || 'El campo Nombre es requerido.',
        val => (this.$v.category.fields.name.maxLength) || 'El campo Nombre no debe exceder los 50 dígitos.'
      ]
    },
    codeRules (val) {
      return [
        val => (this.$v.category.fields.code.required) || 'El campo Código es requerido.',
        val => (this.$v.category.fields.code.maxLength) || 'El campo Código no debe exceder los 5 dígitos.'
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
  methods: {
    createCategory () {
      this.$v.category.fields.$reset()
      this.$v.category.fields.$touch()
      if (this.$v.category.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = { ...this.category.fields }
      api.post('/categories', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.$router.push('/categories')
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

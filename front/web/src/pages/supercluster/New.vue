<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-9">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Supercluster" to="/supercluster" />
              <q-breadcrumbs-el label="Nuevo" />
            </q-breadcrumbs>
          </div>
        </div>
      </div>
    </div>

    <div class="q-pa-md bg-grey-3">
      <div class="row bg-white border-panel">
        <div class="col q-pa-md">
          <div class="row q-col-gutter-xs">
            <div class="col-xs-12 col-sm-2">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="supercluster.fields.code"
                :error="$v.supercluster.fields.code.$error"
                label="Código"
                :rules="codeRules"
                @input="v => { supercluster.fields.code = v.toUpperCase() }">
                <template v-slot:prepend>
                  <q-icon name="fingerprint" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="supercluster.fields.name"
                :error="$v.supercluster.fields.name.$error"
                label="Nombre"
                :rules="nameRules"
                @input="v => { supercluster.fields.name = v.toUpperCase() }"
                @keyup.enter="create()">
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
const { required, maxLength } = require('vuelidate/lib/validators')

export default {
  name: 'NewSupercluster',
  validations: {
    supercluster: {
      fields: {
        name: { required, maxLength: maxLength(100) },
        code: { required, maxLength: maxLength(5) }
      }
    }
  },
  data () {
    return {
      supercluster: {
        fields: {
          name: null,
          code: null
        }
      },
      branchOfficeOptions: []
    }
  },
  computed: {
    nameRules (val) {
      return [
        val => (this.$v.supercluster.fields.name.required) || 'Requerido.',
        val => (this.$v.supercluster.fields.name.maxLength) || 'El campo Nombre no debe exceder los 100 dígitos.'
      ]
    },
    codeRules (val) {
      return [
        val => (this.$v.supercluster.fields.code.required) || 'Requerido.',
        val => (this.$v.supercluster.fields.code.maxLength) || 'El campo Código no debe exceder los 5 dígitos.'
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
  created () {},
  methods: {
    create () {
      this.$v.supercluster.fields.$reset()
      this.$v.supercluster.fields.$touch()
      if (this.$v.supercluster.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = { ...this.supercluster.fields }
      api.post('/supercluster/create', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          // this.$router.push('/supercluster')
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

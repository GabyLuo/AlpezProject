<template>
  <q-page>
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-9">
          <span class="q-ml-md grey-8 fs28 page-title">Editar Operador {{ $route.params.id }}</span>
        </div>
        <div class="col-sm-3">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="right">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Operadores" to="/operators" />
              <q-breadcrumbs-el label="Editar" />
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
            <div class="col-xs-12 col-sm-6">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="operator.fields.name"
                :error="$v.operator.fields.name.$error"
                label="Nombre"
                :rules="nameRules"
                @input="v => { operator.fields.name = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-signature" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-6 pull-right">
              <q-btn color="positive" icon="save" label="Guardar" @click="updateOperator()" style="height: 70%;" />
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
  name: 'EditOperator',
  validations: {
    operator: {
      fields: {
        name: { required, maxLength: maxLength(100) }
      }
    }
  },
  data () {
    return {
      operator: {
        fields: {
          name: null
        }
      }
    }
  },
  computed: {
    nameRules (val) {
      return [
        val => (this.$v.operator.fields.name.required) || 'El campo Nombre es requerido.',
        val => (this.$v.operator.fields.name.maxLength) || 'El campo Nombre no debe exceder los 100 dígitos.'
      ]
    }
  },
  beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(10)) {
      this.$router.push('/')
    }
  },
  created () {
    this.fetchFromServer()
  },
  methods: {
    fetchFromServer () {
      this.$q.loading.show()
      api.get(`/operators/${this.$route.params.id}`).then(({ data }) => {
        if (!data.operator) {
          this.$router.push('/operators')
        } else {
          this.operator.fields = data.operator
          this.$q.loading.hide()
        }
      })
    },
    updateOperator () {
      this.$v.operator.fields.$reset()
      this.$v.operator.fields.$touch()
      if (this.$v.operator.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = { ...this.operator.fields }
      api.put(`/operators/${this.$route.params.id}`, params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.$router.push('/operators')
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

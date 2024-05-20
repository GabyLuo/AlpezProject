<template>
  <q-page>
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-9">
          <span class="q-ml-md grey-8 fs28 page-title">Proceso: {{ productionProcess.fields.name }}</span>
        </div>
        <div class="col-sm-3">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="right">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Procesos" to="/production-processes" />
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
            <div class="col-xs-12 col-sm-10">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="productionProcess.fields.name"
                :error="$v.productionProcess.fields.name.$error"
                label="Nombre"
                :rules="nameRules"
                @input="v => { productionProcess.fields.name = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-signature" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-2 pull-right">
              <q-btn color="positive" icon="save" label="Guardar" @click="editProductionProcess()" />
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
  name: 'EditProductionProcess',
  validations: {
    productionProcess: {
      fields: {
        name: { required, maxLength: maxLength(100) }
      }
    },
    measurementPoint: {
      fields: {
        name: { required, maxLength: maxLength(20) }
      }
    }
  },
  data () {
    return {
      productionProcess: {
        fields: {
          id: null,
          name: null
        }
      },
      measurementPoint: {
        fields: {
          id: null,
          name: null
        }
      }
    }
  },
  computed: {
    nameRules (val) {
      return [
        val => (this.$v.productionProcess.fields.name.required) || 'El campo Nombre es requerido.',
        val => (this.$v.productionProcess.fields.name.maxLength) || 'El campo Nombre no debe exceder de 100 caracteres.'
      ]
    }
  },
  beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1)) {
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
      api.get(`/production-processes/${id}`).then(({ data }) => {
        if (!data.productionProcess) {
          this.$router.push('/production-processes')
        } else {
          this.productionProcess.fields = data.productionProcess
          this.$q.loading.hide()
        }
      })
    },
    updateProductionProcessFields () {
      this.$v.productionProcess.fields.$reset()
      this.$v.productionProcess.fields.$touch()
    },
    editProductionProcess () {
      this.$v.productionProcess.fields.$reset()
      this.$v.productionProcess.fields.$touch()
      if (this.$v.productionProcess.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = { ...this.productionProcess.fields }
      api.put(`/production-processes/${params.id}`, params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.$router.push('/production-processes')
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

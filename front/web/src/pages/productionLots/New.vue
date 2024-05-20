<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-12">
          <q-btn color="primary" icon="keyboard_backspace" label="Regresar" @click="backToProductionOrder()" />
          <span class="q-ml-md grey-8 fs28 page-title">Nuevo lote</span>
        </div>
      </div>
    </div>

    <div class="q-pa-md bg-grey-3">
      <div class="row bg-white border-panel">
        <div class="col q-pa-md">
          <div class="row q-col-gutter-xs">
            <div class="col-sm-4 col-xs-12">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="lot.fields.product"
                :options="productOptions"
                label="Producto"
                :rules="lotProductRules"
              >
                <template v-slot:prepend>
                  <q-icon name="emoji_objects" />
                </template>
              </q-select>
            </div>
            <div class="col-sm-4 col-xs-12 text-center">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="lot.fields.weight"
                label="Peso"
                type="number"
                :rules="lotWeightRules"
                suffix="Kg."
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-weight-hanging" />
                </template>
              </q-input>
            </div>
            <div class="col-sm-4 col-xs-12 text-center">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="lot.fields.status"
                label="Estatus"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="battery_full" />
                </template>
              </q-input>
            </div>
          </div>

          <div class="row q-mb-sm q-mt-md">
            <div class="col-xs-12 col-sm-2 offset-sm-10 pull-right">
              <q-btn color="positive" icon="save" label="Guardar" @click="createLot()" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </q-page>
</template>

<script>
import api from '../../commons/api.js'
const { required, numeric } = require('vuelidate/lib/validators')

export default {
  name: 'NewLot',
  validations: {
    lot: {
      fields: {
        start_date: { required },
        product: { required },
        weight: { required, numeric }
      }
    }
  },
  data () {
    return {
      lot: {
        fields: {
          start_date: null,
          product: null,
          weight: null,
          status: 'NUEVO'
        }
      },
      productOptions: []
    }
  },
  computed: {
    lotStartDateRules (val) {
      return [
        val => (this.$v.lot.fields.start_date.required) || 'El campo Fecha de inicio es requerido.'
      ]
    },
    lotProductRules (val) {
      return [
        val => (this.$v.lot.fields.product.required) || 'El campo Producto es requerido.'
      ]
    },
    lotWeightRules (val) {
      return [
        val => (this.$v.lot.fields.weight.required) || 'El campo Kilogramos es requerido.',
        val => (this.$v.lot.fields.weight.numeric) || 'El campo Kilogramos debe ser numérico.'
      ]
    }
  },
  beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(4) && !this.$store.getters['users/roles'].includes(5) && !this.$store.getters['users/roles'].includes(6) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(8) && !this.$store.getters['users/roles'].includes(9) && !this.$store.getters['users/roles'].includes(10)) {
      this.$router.push('/')
    }
  },
  created () {
    this.fetchFromServer()
  },
  methods: {
    fetchFromServer () {
      this.$q.loading.show()
      api.get('/products/options').then(({ data }) => {
        this.productOptions = data.options
        this.$q.loading.hide()
      })
    },
    backToProductionOrder () {
      this.$router.push(`/production-orders/${this.$route.params.id}`)
    },
    createLot () {
      this.$v.lot.fields.$reset()
      this.$v.lot.fields.$touch()
      if (this.$v.lot.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      if (this.lot.fields.start_date == null) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, seleccione la fecha de recepción.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = { ...this.lot.fields }
      params.order_id = this.$route.params.id
      params.product_id = params.product.value
      api.post('/production-lots', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.$router.push(`/lots/${data.lot.id}`)
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

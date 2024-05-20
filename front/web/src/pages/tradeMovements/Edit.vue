<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-9">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Movimientos" to="/trade-movements" />
              <q-breadcrumbs-el label="Editar Movimiento" />
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
            <div class="col-xs-12 col-sm-3">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                emit-value
                map-options
                :options="accountOptions"
                v-model="movement.fields.account_id"
                label="Cuenta"
                :rules="accountRules"
                :error="$v.movement.fields.account_id.$error"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-building" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                emit-value
                map-options
                :options="outputOptions"
                v-model="movement.fields.output_id"
                label="Rubro"
                :rules="outputRules"
                :error="$v.movement.fields.output_id.$error"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-building" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-2">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                emit-value
                map-options
                :options="movementOptions"
                v-model="movement.fields.movement_type"
                label="Movimiento"
                :rules="movementRules"
                :error="$v.movement.fields.movement_type.$error"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-building" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-2">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="movement.fields.amount"
                label="Importe"
                :rules="amountRules"
                :error="$v.movement.fields.amount.$error"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-signature" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-2 text-center">
                <q-select color="dark"
                bg-color="secondary"
                filled
                v-model="movement.fields.date"
                :rules="dateRules"
                :error="$v.movement.fields.date.$error"
                mask="date"
                label="Fecha">
                <template v-slot:prepend>
                    <q-icon name="event"></q-icon>
                </template>
                <q-popup-proxy
                ref="date_ref"
                transition-show="scale"
                transition-hide="scale">
                    <div class="col-sm-12">
                        <q-date
                        color="secondary"
                        text-color="white"
                        mask="DD/MM/YYYY"
                        v-model="movement.fields.date"
                        @input="() => $refs.date_ref.hide()"
                        today-btn>
                        </q-date>
                    </div>
                </q-popup-proxy>
                </q-select>
            </div>
            <div class="col-xs-12 col-sm-12">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="movement.fields.description"
                label="Descripción"
                @input="v => { movement.fields.description = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-signature" />
                </template>
              </q-input>
            </div>
          </div>

          <div class="row q-mb-sm q-mt-md">
            <div class="col-xs-12 col-sm-2 offset-sm-10 pull-right">
              <q-btn color="positive" icon="save" label="Guardar" @click="editMovement()" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </q-page>
</template>

<script>
import api from '../../commons/api.js'
const { required, decimal } = require('vuelidate/lib/validators')

export default {
  name: 'NewCategory',
  validations: {
    movement: {
      fields: {
        account_id: { required },
        output_id: { required },
        movement_type: { required },
        amount: { required, decimal },
        date: { required }
      }
    }
  },
  data () {
    return {
      movement: {
        fields: {
          account_id: null,
          output_id: null,
          movement_type: null,
          amount: null,
          date: null,
          description: null
        }
      },
      accountOptions: [],
      outputOptions: [],
      movementOptions: [
        { value: 'CARGO', label: 'CARGO' },
        { value: 'ABONO', label: 'ABONO' }
      ]
    }
  },
  computed: {
    accountRules (val) {
      return [
        val => (this.$v.movement.fields.account_id.required) || 'El campo Cuenta es requerido.'
      ]
    },
    outputRules (val) {
      return [
        val => (this.$v.movement.fields.output_id.required) || 'El campo Rubro es requerido.'
      ]
    },
    movementRules (val) {
      return [
        val => (this.$v.movement.fields.movement_type.required) || 'El campo Movimiento es requerido.'
      ]
    },
    amountRules (val) {
      return [
        val => (this.$v.movement.fields.amount.required) || 'El campo Importe es requerido.',
        val => (this.$v.movement.fields.amount.decimal) || 'El campo Importe es numerico.'
      ]
    },
    dateRules (val) {
      return [
        val => (this.$v.movement.fields.date.required) || 'El campo Fecha es requerido.'
      ]
    },
    descriptionRules (val) {
      return [
        val => (this.$v.movement.fields.description.required) || 'El campo Descripción es requerido.'
      ]
    }
  },
  beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(2) && !this.$store.getters['users/roles'].includes(24)) {
      this.$router.push('/')
    }
  },
  mounted () {
    this.$q.loading.show()
    this.fetchFromServer()
    this.getAccount()
    this.getOutputs()
    this.$q.loading.hide()
  },
  methods: {
    fetchFromServer () {
      this.$q.loading.show()
      const id = this.$route.params.id
      api.get(`/movement-trade/${id}`).then(({ data }) => {
        if (!data.movement) {
          this.$router.push('/trade-movements')
        } else {
          this.movement.fields.account_id = { value: data.movement.account_id, label: data.movement.account_name }
          this.movement.fields.output_id = { value: data.movement.output_id, label: data.movement.output_name }
          this.movement.fields.movement_type = { value: data.movement.movement, label: data.movement.movement }
          this.movement.fields.amount = data.movement.amount
          this.movement.fields.date = data.movement.date
          this.movement.fields.description = data.movement.description
          this.$q.loading.hide()
        }
      })
    },
    updateMovementFields () {
      this.$v.movement.fields.$reset()
      this.$v.movement.fields.$touch()
    },
    editMovement () {
      this.$v.movement.fields.$reset()
      this.$v.movement.fields.$touch()
      const id = this.$route.params.id
      if (this.$v.movement.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = { ...this.movement.fields }
      if (this.movement.fields.account_id.value) {
        params.account_id = this.movement.fields.account_id.value
      }
      if (this.movement.fields.output_id.value) {
        params.output_id = this.movement.fields.output_id.value
      }
      if (this.movement.fields.movement_type.value) {
        params.movement_type = this.movement.fields.movement_type.value
      }
      params.date = this.movement.fields.date.substr(6, 4) + '-' + this.movement.fields.date.substr(3, 2) + '-' + this.movement.fields.date.substr(0, 2)
      api.put(`/movement-trade/${id}`, params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.$router.push('/trade-movements')
        } else {
          this.$q.loading.hide()
        }
      })
    },
    getAccount () {
      this.$q.loading.show()
      api.get('/account-trade/options').then(({ data }) => {
        this.accountOptions = data.options
        this.$q.loading.hide()
      })
    },
    getOutputs () {
      this.$q.loading.show()
      api.get('/output_type/options').then(({ data }) => {
        this.outputOptions = data.options
        this.$q.loading.hide()
      })
    }
  }
}
</script>

<style>
</style>

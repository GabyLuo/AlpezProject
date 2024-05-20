<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-12 row">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="right" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el :label="this.$route.params.type === '1' ? 'Ordenes de compra' : 'Recepciones'" :to="this.$route.params.type === '1' ? `/purchase-orders/${this.$route.params.id}` : '/raw-material-shipments'" />
              <q-breadcrumbs-el label="Nueva recepción" />
            </q-breadcrumbs>
          </div>
        </div>
      </div>
    </div>

    <div class="q-pa-md bg-grey-3">
      <div class="row bg-white border-panel">
        <div class="col q-pa-md">
          <!-- <div class="row q-mb-sm">

          </div> -->
          <div class="row q-col-gutter-xs">
            <div class="col-xs-12 col-sm-3">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="shipment.fields.receive_date"
                mask="date"
                label="Fecha de recepción"
                :rules="shipmentReceiveDateRules"
              >
                <template v-slot:prepend>
                  <q-icon name="event" />
                </template>
                <q-popup-proxy
                  ref="shipmentFieldsReceiveDateRef"
                  transition-show="scale"
                  transition-hide="scale"
                >
                  <div class="col-sm-12">
                    <q-date
                      mask="DD/MM/YYYY"
                      v-model="shipment.fields.receive_date"
                      @input="() => $refs.shipmentFieldsReceiveDateRef.hide()"
                      today-btn
                    />
                  </div>
                </q-popup-proxy>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-3 text-center">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="shipment.fields.receive_time"
                mask="time"
                label="Hora de recepción"
                :rules="shipmentReceiveTimeRules"
              >
                <template v-slot:prepend>
                  <q-icon name="access_time" />
                </template>
                <q-popup-proxy
                  ref="shipmentFieldsReceiveTimeRef"
                  transition-show="scale"
                  transition-hide="scale"
                >
                  <div class="col-sm-12">
                    <q-time
                      v-model="shipment.fields.receive_time"
                      @input="() => $refs.shipmentFieldsReceiveTimeRef.hide()"
                      now-btn
                    />
                  </div>
                </q-popup-proxy>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-input
                color="dark"
                bg-color="blue"
                dark
                filled
                disable
                v-model="shipment.fields.status"
                :error="$v.shipment.fields.status.$error"
                label="Estatus"
                :rules="shipmentStatusRules"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-battery-empty" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-2 offset-xs-10 pull-right">
              <q-btn color="positive" icon="save" label="Guardar" @click="createShipment()" />
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
  name: 'NewShipment',
  validations: {
    shipment: {
      fields: {
        status: { required, maxLength: maxLength(10) },
        receive_date: { required },
        receive_time: { required }
      }
    }
  },
  beforeCreate () {
    if (!this.$store.getters['users/rol'] === 22 && !this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(2)) {
      this.$router.push('/')
    }
  },
  created () {
    console.log(this.$route.params)
  },
  data () {
    return {
      shipment: {
        fields: {
          status: 'NUEVO',
          receive_date: `${(new Date().getDate()).toString().padStart(2, '0')}/${(new Date().getMonth() + 1).toString().padStart(2, '0')}/${new Date().getFullYear()}`,
          receive_time: `${(new Date().getHours()).toString().padStart(2, '0')}:${(new Date().getMinutes()).toString().padStart(2, '0')}`
        }
      }
    }
  },
  computed: {
    shipmentReceiveDateRules (val) {
      return [
        val => (this.$v.shipment.fields.receive_date.required) || 'El campo Fecha de recepción es requerido.'
      ]
    },
    shipmentReceiveTimeRules (val) {
      return [
        val => (this.$v.shipment.fields.receive_time.required) || 'El campo Hora de recepción es requerido.'
      ]
    },
    shipmentStatusRules (val) {
      return [
        val => (this.$v.shipment.fields.status.required) || 'El campo Estatus es requerido.',
        val => (this.$v.shipment.fields.status.maxLength) || 'El campo Estatus no debe exceder los 10 dígitos.'
      ]
    }
  },
  methods: {
    backToPurchaseOrder () {
      this.$router.push(`/purchase-orders/${this.$route.params.id}`)
    },
    invertDate (date) {
      if (date !== null) {
        var info = date.split('/').reverse().join('-')
      }
      return info
    },
    createShipment () {
      this.$v.shipment.fields.$reset()
      this.$v.shipment.fields.$touch()
      if (this.$v.shipment.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      if (this.shipment.fields.receive_date == null) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, seleccione la fecha de recepción.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = { ...this.shipment.fields }
      params.receive_date = this.invertDate(this.shipment.fields.receive_date)
      params.order_id = this.$route.params.id
      api.post('/shipments', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.$router.push(`/shipments/${data.shipment.id}/${this.$route.params.type}`)
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

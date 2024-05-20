<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-9">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Embarques" to="/trips" />
              <q-breadcrumbs-el label="Nuevo embarque" />
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
            <div class="col-xs-12 col-sm-2 col-md-2 col-lg-2">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="trip.fields.invoice_id"
                :error="$v.trip.fields.invoice_id.$error"
                label="Remision / Factura"
                :rules="invoiceRules"
                :options="invoicesOptionsFilter"
                use-input
                hide-selected
                fill-input
                input-debounce="0"
                @filter="filterInvoices"
                emit-value
                map-options
                >
                <template v-slot:prepend>
                  <q-icon name="fact_check" />
                </template>
                <template v-slot:no-option>
                  <q-item>
                    <q-item-section class="text-grey">
                      No hay Resultados
                    </q-item-section>
                  </q-item>
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-2 text-center">
                <q-select color="dark"
                bg-color="secondary"
                filled
                :rules="dateRules"
                v-model="trip.fields.date"
                :error="$v.trip.fields.date.$error"
                mask="DD/MM/YYYY HH:mm:ss"
                label="Fecha">
                <template v-slot:prepend>
                    <q-icon name="event"></q-icon>
                </template>
                <q-popup-proxy
                ref="date_ref"
                transition-show="scale"
                transition-hide="scale">
                    <div class="row">
                      <div class="col-sm-6">
                        <q-date
                          color="secondary"
                          text-color="white"
                          v-model="trip.fields.date"
                          mask="DD/MM/YYYY HH:mm:ss"
                        />
                      </div>
                      <div class="col-sm-6">
                        <q-time
                          color="secondary"
                          text-color="white"
                          v-model="trip.fields.date"
                          mask="DD/MM/YYYY HH:mm:ss"
                          @input="() => $refs.date_ref.hide()"
                          now-btn
                        />
                      </div>
                    </div>
                </q-popup-proxy>
                </q-select>
            </div>
            <!-- <div class="col-xs-12 col-sm-4">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="trip.fields.destiny"
                :error="$v.trip.fields.destiny.$error"
                label="Destino"
                :rules="destinyRules"
                :options="destinysOptionsFilter"
                use-input
                hide-selected
                fill-input
                input-debounce="0"
                @filter="filterDestinys"
                hint="Basic autocomplete"
                emit-value
                map-options
                >
                <template v-slot:prepend>
                  <q-icon name="fmd_good" />
                </template>
                <template v-slot:no-option>
                  <q-item>
                    <q-item-section class="text-grey">
                      No hay Resultados
                    </q-item-section>
                  </q-item>
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-5">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="trip.fields.driver"
                :error="$v.trip.fields.driver.$error"
                label="Chofer"
                :rules="driverRules"
                :options="driversOptionsFilter"
                use-input
                hide-selected
                fill-input
                input-debounce="0"
                @filter="filterDrivers"
                hint="Basic autocomplete"
                emit-value
                map-options
                >
                <template v-slot:prepend>
                  <q-icon name="drive_eta" />
                </template>
                <template v-slot:no-option>
                  <q-item>
                    <q-item-section class="text-grey">
                      No hay Resultados
                    </q-item-section>
                  </q-item>
                </template>
              </q-select>
            </div> -->
            <div class="col-xs-12 col-sm-3">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="trip.fields.vehicle_id"
                :error="$v.trip.fields.vehicle_id.$error"
                label="Número de unidad"
                :rules="vehicle_idRules"
                :options="economicNumberOptions"
                @input="getVehicleData()"
                emit-value
                map-options
                >
                <template v-slot:prepend>
                  <q-icon name="tag" />
                </template>
                <template v-slot:no-option>
                  <q-item>
                    <q-item-section class="text-grey">
                      No hay Resultados
                    </q-item-section>
                  </q-item>
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                disable
                v-model="vehicle.fields.type"
                label="Tipo de vehículo"
                emit-value
                map-options
                >
                <template v-slot:prepend>
                  <q-icon name="format_list_numbered" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-2">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                disable
                v-model="vehicle.fields.brand"
                label="Marca de vehículo"
                emit-value
                map-options
                >
                <template v-slot:prepend>
                  <q-icon name="book" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-2">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                disable
                v-model="vehicle.fields.model"
                label="Modelo de vehiculo"
                emit-value
                map-options
                >
                <template v-slot:prepend>
                  <q-icon name="format_list_bulleted" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-2">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                disable
                v-model="vehicle.fields.plate"
                label="Placas"
                emit-value
                map-options
                >
                <template v-slot:prepend>
                  <q-icon name="format_list_bulleted" />
                </template>
              </q-input>
            </div>
          </div>

          <div class="row q-mb-sm q-mt-md">
            <div class="col-xs-12 col-sm-2 offset-sm-10 pull-right">
              <q-btn color="positive" icon="save" label="Guardar" @click="createTrip()" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </q-page>
</template>

<script>
import api from '../../commons/api.js'
const { required } = require('vuelidate/lib/validators')

export default {
  name: 'NewTrip',
  validations: {
    trip: {
      fields: {
        date: { required },
        vehicle_id: { required },
        invoice_id: { required }
      }
    }
  },
  data () {
    return {
      trip: {
        fields: {
          date: null,
          vehicle_id: null,
          invoice_id: null
        }
      },
      vehicle: {
        fields: {
          type: null,
          brand: null,
          model: null
        }
      },
      driversOptions: [],
      destinysOptions: [],
      economicNumberOptions: [],
      driversOptionsFilter: [],
      destinysOptionsFilter: [],
      invoicesOptions: [],
      invoicesOptionsFilter: []
    }
  },
  mounted () {
    this.$q.loading.show()
    // this.getDrivers()
    // this.getDestinys()
    this.getEconomicNumber()
    this.getInvoices()
    this.$q.loading.hide()
  },
  computed: {
    invoiceRules (val) {
      return [
        val => (this.$v.trip.fields.invoice_id.required) || 'El campo Remision / Factura es requerido.'
      ]
    },
    driverRules (val) {
      return [
        val => (this.$v.trip.fields.driver.required) || 'El campo Chofer es requerido.'
      ]
    },
    destinyRules (val) {
      return [
        val => (this.$v.trip.fields.destiny.required) || 'El campo Destino es requerido.'
      ]
    },
    dateRules (val) {
      return [
        val => (this.$v.trip.fields.date.required) || 'El campo Fecha es requerido.'
      ]
    },
    vehicle_idRules (val) {
      return [
        val => (this.$v.trip.fields.vehicle_id.required) || 'El campo Número de unidad es requerido.'
      ]
    }
  },
  beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(8)) {
      this.$router.push('/')
    }
  },
  methods: {
    createTrip () {
      this.$v.trip.fields.$reset()
      this.$v.trip.fields.$touch()
      if (this.$v.trip.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = { ...this.trip.fields }
      params.date = this.trip.fields.date.split(' ')[0].split('/').reverse().join('-') + ' ' + this.trip.fields.date.split(' ')[1]
      api.post('/trips', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.$router.push(`/trips/${Number(data.id)}`)
        } else {
          this.$q.loading.hide()
        }
      })
    },
    getDrivers () {
      api.get('drivers/options').then(data => {
        this.driversOptions = data.data.options
      })
    },
    filterDrivers (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.driversOptionsFilter = this.driversOptions.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    getDestinys () {
      api.get('ranges/options').then(data => {
        this.destinysOptions = data.data.options
      })
    },
    filterDestinys (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.destinysOptionsFilter = this.destinysOptions.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    getEconomicNumber () {
      api.get('vehicle/options').then(data => {
        this.economicNumberOptions = data.data.options
      })
    },
    getVehicleData () {
      const id = this.trip.fields.vehicle_id
      api.get(`vehicle/vehicle-data/${id}`).then(data => {
        console.log(data)
        this.vehicle.fields = data.data.vehicle
      })
    },
    getInvoices () {
      api.get('invoices/invoicesTripOptions').then(data => {
        this.invoicesOptions = data.data.options
      })
    },
    filterInvoices (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.invoicesOptionsFilter = this.invoicesOptions.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    }
  }
}
</script>

<style>
</style>

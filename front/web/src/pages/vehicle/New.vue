<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-9">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Vehículos" to="/vehicle" />
              <q-breadcrumbs-el label="Nuevo Vehículo" />
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
            <div class="col-xs-12 col-sm-2">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="vehicle.fields.economic_number"
                :error="$v.vehicle.fields.economic_number.$error"
                label="Número de unidad"
                :rules="economic_numberRules"
              >
                <template v-slot:prepend>
                  <q-icon name="tag" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="vehicle.fields.type_id"
                :error="$v.vehicle.fields.type_id.$error"
                :options="vehicleTypeFilter"
                label="Tipo de vehículo"
                :rules="type_idRules"
                @filter="filterVehicle"
                use-input
                emit-value
                map-options
              >
                <template v-slot:prepend>
                  <q-icon name="format_list_numbered" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-2">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="vehicle.fields.vehicle_brand"
                :error="$v.vehicle.fields.vehicle_brand.$error"
                label="Marca de vehículo"
                :rules="vehicle_brandRules"
                @input="v => { vehicle.fields.vehicle_brand = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="book" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="vehicle.fields.vehicle_model"
                :error="$v.vehicle.fields.vehicle_model.$error"
                label="Modelo de vehículo"
                :rules="vehicle_modelRules"
                @input="v => { vehicle.fields.vehicle_model = v.toUpperCase() }"
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
                v-model="vehicle.fields.year"
                :error="$v.vehicle.fields.year.$error"
                label="Año"
                :rules="yearRules"
              >
                <template v-slot:prepend>
                  <q-icon name="event" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="vehicle.fields.license_plate"
                :error="$v.vehicle.fields.license_plate.$error"
                label="Placas"
                :rules="license_plateRules"
                @input="v => { vehicle.fields.license_plate = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="credit_score" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                :error="$v.vehicle.fields.perm_sct.$error"
                :rules="perm_sctRules"
                v-model="vehicle.fields.perm_sct"
                label="Permiso SCT"
                @input="v => { vehicle.fields.perm_sct = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="insert_drive_file" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="vehicle.fields.perm_num_sct"
                :error="$v.vehicle.fields.perm_num_sct.$error"
                :rules="perm_num_sctRules"
                label="Número de permiso SCT"
                @input="v => { vehicle.fields.perm_num_sct = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="format_list_bulleted" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="vehicle.fields.vehicle_config"
                :error="$v.vehicle.fields.vehicle_config.$error"
                :options="vehicleConfigFilter"
                label="Configuracion vehicular"
                :rules="vehicle_configRules"
                @filter="filterConfig"
                use-input
                emit-value
                map-options
              >
                <template v-slot:prepend>
                  <q-icon name="format_list_numbered" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="vehicle.fields.insurance_civil_resp"
                :error="$v.vehicle.fields.insurance_civil_resp.$error"
                :rules="insurance_civil_respRules"
                label="Aseguradora Resp. Civil"
                @input="v => { vehicle.fields.insurance_civil_resp = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="insert_drive_file" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="vehicle.fields.resp_civil_policy"
                :error="$v.vehicle.fields.resp_civil_policy.$error"
                :rules="resp_civil_policyRules"
                label="Poliza Aseg. Resp. Civil"
                @input="v => { vehicle.fields.resp_civil_policy = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="format_list_bulleted" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="vehicle.fields.ambience_insurance"
                label="Aseguradora Med. Ambiente"
                @input="v => { vehicle.fields.ambience_insurance = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="insert_drive_file" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="vehicle.fields.ambience_insurance_policy"
                label="Poliza Aseg. Med. Ambiente"
                @input="v => { vehicle.fields.ambience_insurance_policy = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="format_list_bulleted" />
                </template>
              </q-input>
            </div>
          </div>

          <div class="row q-mb-lg">
          </div>

          <div class="row q-col-gutter-xs">
            <div class="col-xs-1 col-sm-1">
              <div class="q-gutter-sm">
                <q-checkbox indeterminate-value="maybe" v-model="vehicle.fields.has_towing" label="Remolque" />
              </div>
            </div>
            <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3" v-if="vehicle.fields.has_towing">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="vehicle.fields.towing_type_id"
                :error="$v.vehicle.fields.towing_type_id.$error"
                :rules="towing_type_idRules"
                :options="towingTypeFilter"
                label="SubTipo remolque"
                @filter="filterTowing"
                use-input
                emit-value
                map-options
              >
                <template v-slot:prepend>
                  <q-icon name="format_list_numbered" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-2" v-if="vehicle.fields.has_towing">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="vehicle.fields.towing_plate"
                :error="$v.vehicle.fields.towing_plate.$error"
                :rules="towing_plateRules"
                label="Placas remolque"
                @input="v => { vehicle.fields.towing_plate = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="credit_score" />
                </template>
              </q-input>
            </div>
          </div>

          <div class="row q-mb-sm q-mt-md">
            <div class="col-xs-12 col-sm-2 offset-sm-10 pull-right">
              <q-btn color="positive" icon="save" label="Guardar" @click="createVehicle()" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </q-page>
</template>

<script>
import api from '../../commons/api.js'
const { required, requiredIf, numeric } = require('vuelidate/lib/validators')

export default {
  name: 'NewVehicle',
  validations: {
    vehicle: {
      fields: {
        economic_number: { required, numeric },
        type_id: { required },
        vehicle_brand: { required },
        vehicle_model: { required },
        year: { required, numeric },
        license_plate: { required },
        perm_sct: { required },
        perm_num_sct: { required },
        vehicle_config: { required },
        insurance_civil_resp: { required },
        resp_civil_policy: { required },
        towing_type_id: { required: requiredIf(function () { return this.vehicle.fields.has_towing }) },
        towing_plate: { required: requiredIf(function () { return this.vehicle.fields.has_towing }) }
      }
    }
  },
  data () {
    return {
      vehicle: {
        fields: {
          economic_number: null,
          type_id: null,
          vehicle_brand: null,
          vehicle_model: null,
          year: null,
          license_plate: null,
          perm_sct: null,
          perm_num_sct: null,
          vehicle_config: null,
          insurance_civil_resp: null,
          resp_civil_policy: null,
          ambience_insurance: null,
          ambience_insurance_policy: null,
          has_towing: null,
          towing_type_id: null,
          towing_plate: null
        }
      },
      vehicleType: [],
      towingType: [],
      vehicleTypeFilter: [],
      towingTypeFilter: [],
      vehicleConfig: [],
      vehicleConfigFilter: []
    }
  },
  mounted () {
    this.getVehicleType()
    this.getTowingType()
    this.getConfig()
  },
  computed: {
    economic_numberRules (val) {
      return [
        val => (this.$v.vehicle.fields.economic_number.required) || 'El campo Número de unidad es requerido.',
        val => (this.$v.vehicle.fields.economic_number.numeric) || 'El campo Número de unidad es numerico.'
      ]
    },
    type_idRules (val) {
      return [
        val => (this.$v.vehicle.fields.type_id.required) || 'El campo Tipo de vehiculo es requerido.'
      ]
    },
    vehicle_brandRules (val) {
      return [
        val => (this.$v.vehicle.fields.vehicle_brand.required) || 'El campo Marca de vehículo es requerido.'
      ]
    },
    vehicle_modelRules (val) {
      return [
        val => (this.$v.vehicle.fields.vehicle_model.required) || 'El campo Modelo de vehículo es requerido.'
      ]
    },
    yearRules (val) {
      return [
        val => (this.$v.vehicle.fields.year.required) || 'El campo Año es requerido.',
        val => (this.$v.vehicle.fields.year.numeric) || 'El campo Año es numerico.'
      ]
    },
    vinRules (val) {
      return [
        val => (this.$v.vehicle.fields.vin.required) || 'El campo VIN es requerido.'
      ]
    },
    license_plateRules (val) {
      return [
        val => (this.$v.vehicle.fields.license_plate.required) || 'El campo Placas es requerido.'
      ]
    },
    perm_sctRules (val) {
      return [
        val => (this.$v.vehicle.fields.perm_sct.required) || 'El campo Permiso SCT es requerido.'
      ]
    },
    perm_num_sctRules (val) {
      return [
        val => (this.$v.vehicle.fields.perm_num_sct.required) || 'El campo Número de permiso SCT es requerido.'
      ]
    },
    vehicle_configRules (val) {
      return [
        val => (this.$v.vehicle.fields.vehicle_config.required) || 'El campo Configuracion vehicular es requerido.'
      ]
    },
    insurance_civil_respRules (val) {
      return [
        val => (this.$v.vehicle.fields.insurance_civil_resp.required) || 'El campo Aseguradora Resp. Civil es requerido.'
      ]
    },
    resp_civil_policyRules (val) {
      return [
        val => (this.$v.vehicle.fields.resp_civil_policy.required) || 'El campo Poliza Aseg. Resp. Civil es requerido.'
      ]
    },
    towing_type_idRules (val) {
      return [
        val => (this.$v.vehicle.fields.towing_type_id.required) || 'El campo SubTipo remolque es requerido.'
      ]
    },
    towing_plateRules (val) {
      return [
        val => (this.$v.vehicle.fields.towing_plate.required) || 'El campo Placas remolque es requerido.'
      ]
    }
  },
  beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(8)) {
      this.$router.push('/')
    }
  },
  methods: {
    createVehicle () {
      this.$v.vehicle.fields.$reset()
      this.$v.vehicle.fields.$touch()
      if (this.$v.vehicle.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = { ...this.vehicle.fields }
      api.post('/vehicle', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.$router.push('/vehicle')
        } else {
          this.$q.loading.hide()
        }
      })
    },
    getVehicleType () {
      api.get('vehicleType/options').then(data => {
        this.vehicleType = data.data.options
        this.towingTypeFilter = data.data.options
      })
    },
    getTowingType () {
      api.get('vehicleType/optionstowing').then(data => {
        this.towingType = data.data.options
      })
    },
    getConfig () {
      api.get('vehicleType/optionsconfig').then(data => {
        this.vehicleConfig = data.data.options
      })
    },
    filterVehicle (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.vehicleTypeFilter = this.vehicleType.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    filterTowing (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.towingTypeFilter = this.towingType.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    filterConfig (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.vehicleConfigFilter = this.vehicleConfig.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    }
  }
}
</script>

<style>
</style>

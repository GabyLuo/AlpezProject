<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-3">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Choferes" to="/drivers" />
              <q-breadcrumbs-el label="Nuevo Chofer" />
            </q-breadcrumbs>
          </div>
        </div>
      </div>
    </div>

    <div class="q-pa-md bg-grey-3">
      <div class="row bg-white border-panel">
        <div class="col q-pa-md">
          <div class="row" style="padding-bottom: 15px;">
            Información General
          </div>

          <div class="row q-col-gutter-xs">
            <div class="col-xs-12 col-sm-5 col-md-5 col-lg-5">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="driver.fields.name"
                :error="$v.driver.fields.name.$error"
                label="Nombre"
                :rules="nameRules"
                @input="v => { driver.fields.name = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-signature" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="driver.fields.rfc"
                :error="$v.driver.fields.rfc.$error"
                label="RFC"
                :rules="rfcRules"
                @input="v => { driver.fields.rfc = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="insert_drive_file" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="driver.fields.license"
                :error="$v.driver.fields.license.$error"
                label="Licencia"
                :rules="licenseRules"
                @input="v => { driver.fields.license = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-id-card" />
                </template>
              </q-input>
            </div>
          </div>
          <div class="col-xs-12 col-sm-6 pull-right">
              <q-btn color="positive" icon="save" label="Guardar" @click="createDriver()" />
            </div>
        </div>
      </div>
      <!-- <br>

      <div class="row bg-white border-panel">
        <div class="col q-pa-md">
          <div class="row" style="padding-bottom: 15px;">
            Domicilio
          </div>

          <div class="row q-col-gutter-xs">
                <div class="col-xs-2 col-sm-4 col-md-4 col-lg-4">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="driver.fields.street"
                    :error="$v.driver.fields.street.$error"
                    label="Calle"
                    :rules="streetRules"
                    @input="v => { driver.fields.street = v.toUpperCase() }"
                  >
                    <template v-slot:prepend>
                      <q-icon name="fas fa-road" />
                    </template>
                  </q-input>
                </div>
                <div class="col-xs-2 col-sm-2 col-md-2 col-ld-2">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="driver.fields.outdoor_number"
                    :error="$v.driver.fields.outdoor_number.$error"
                    label="Número exterior"
                    :rules="outdoor_numberRules"
                    @input="v => { driver.fields.outdoor_number = v.toUpperCase() }"
                  >
                    <template v-slot:prepend>
                      <q-icon name="fas fa-hashtag" />
                    </template>
                  </q-input>
                </div>
                <div class="col-xs-2 col-sm-2 col-md-2 col-ld-2">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="driver.fields.indoor_number"
                    label="Número interior"
                    @input="v => { driver.fields.indoor_number = v.toUpperCase() }"
                  >
                    <template v-slot:prepend>
                      <q-icon name="fas fa-hashtag" />
                    </template>
                  </q-input>
                </div>
                <div class="col-xs-2 col-sm-4 col-md-4 col-lg-4">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="driver.fields.between_street"
                    :error="$v.driver.fields.between_street.$error"
                    label="Entre calles"
                    :rules="between_streetRules"
                    @input="v => { driver.fields.between_street = v.toUpperCase() }"
                  >
                    <template v-slot:prepend>
                      <q-icon name="fas fa-road" />
                    </template>
                  </q-input>
                </div>
                <div class="col-xs-12 col-sm-2 col-md-2 col-lg-2">
                  <q-select
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="driver.fields.postal_code_id"
                    :rules="cpRules"
                    :error="$v.driver.fields.postal_code_id.$error"
                    :options="postal_codes"
                    label="Codigo Postal"
                    use-input
                    emit-value
                    @filter="filterPostalCodes"
                    @input="getSuburbsByPostalCode(driver.fields.postal_code_id)"
                    map-options
                  >
                    <template v-slot:prepend>
                      <q-icon name="fas fa-mail-bulk" />
                    </template>
                  </q-select>
                </div>
                <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
                  <q-select
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="driver.fields.suburb_id"
                    :options="suburbs_options"
                    :rules="suburbRules"
                    :error="$v.driver.fields.suburb_id.$error"
                    label="Colonia"
                    use-input
                    emit-value
                    @filter="filterSuburbs"
                    map-options
                  >
                    <template v-slot:prepend>
                      <q-icon name="fas fa-city" />
                    </template>
                  </q-select>
                </div>
                <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
                  <q-select
                  filled
                  class="uppcase"
                  color="dark"
                  bg-color="secondary"
                  v-model="driver.fields.municipio_id"
                  label="Municipio"
                  :options="MunicipioOptions"
                  use-input
                  hide-selected
                  fill-input
                  input-debounce="0"
                  emit-value
                  @filter="filterMunicipio"
                  @input="getPostalCodes()"
                  map-options>
                  <template v-slot:no-option>
                    <q-item>
                      <q-item-section class="text-grey">
                        No hay Resultados
                      </q-item-section>
                    </q-item>
                  </template>
                </q-select>
                </div>
                <div class="col-xs-2 col-sm-2">
                  <q-select
                  filled
                  class="uppcase"
                  color="dark"
                  bg-color="secondary"
                  v-model="driver.fields.city_id"
                  label="Ciudad"
                  :options="cityOptions"
                  use-input
                  hide-selected
                  fill-input
                  @filter="filterCity"
                  input-debounce="0"
                  emit-value
                  :disable="true"
                  map-options>
                  <template v-slot:no-option>
                    <q-item>
                      <q-item-section class="text-grey">
                        No hay Resultados
                      </q-item-section>
                    </q-item>
                  </template>
                </q-select>
                </div>
                <div class="col-xs-3 col-sm-2">
                  <q-select
                  filled
                  class="uppcase"
                  color="dark"
                  bg-color="secondary"
                  v-model="driver.fields.estado_id"
                  label="ESTADO"
                  :options="estadoOptions"
                  @input="getMunicipiosbyEstado(driver.fields.estado_id, true)"
                  use-input
                  hide-selected
                  fill-input
                  input-debounce="0"
                  emit-value
                  @filter="filterEstado"
                  map-options>
                    <template v-slot:no-option>
                      <q-item>
                        <q-item-section class="text-grey">
                          No hay Resultados
                        </q-item-section>
                      </q-item>
                    </template>
                  </q-select>
                </div>

              </div>
        </div>
      </div> -->

    </div>
  </q-page>
</template>

<script>
import api from '../../commons/api.js'
const { required, maxLength } = require('vuelidate/lib/validators')

export default {
  name: 'NewDriver',
  validations: {
    driver: {
      fields: {
        name: { required, maxLength: maxLength(100) },
        rfc: { required, maxLength: maxLength(15) },
        license: { required, maxLength: maxLength(20) }
        // street: { required, maxLength: maxLength(100) },
        // postal_code_id: { required },
        // suburb_id: { required },
        // indoor_number: { },
        // outdoor_number: { required },
        // between_street: { required }
      }
    }
  },
  data () {
    return {
      driver: {
        fields: {
          name: null,
          rfc: null,
          license: null,
          street: null,
          estado_id: null,
          municipio_id: null,
          postal_code_id: null,
          suburb_id: null,
          indoor_number: null,
          outdoor_number: null,
          between_street: null,
          city_id: null
        }
      },
      postal_codes: [],
      postal_codes_options: [],
      suburbs: [],
      suburbs_options: [],
      cities: [],
      cityOptions: [],
      selectMunicipio: [],
      MunicipioOptions: [],
      selectEstados: [],
      estadoOptions: []
    }
  },
  computed: {
    nameRules (val) {
      return [
        val => (this.$v.driver.fields.name.required) || 'El campo Nombre es requerido.',
        val => (this.$v.driver.fields.name.maxLength) || 'El campo Nombre no debe exceder los 100 dígitos.'
      ]
    },
    rfcRules (val) {
      return [
        val => (this.$v.driver.fields.rfc.required) || 'El campo RFC es requerido.',
        val => (this.$v.driver.fields.rfc.maxLength) || 'El campo RFC no debe exceder los 15 dígitos.'
      ]
    },
    licenseRules (val) {
      return [
        val => (this.$v.driver.fields.license.required) || 'El campo Licencia es requerido.',
        val => (this.$v.driver.fields.license.maxLength) || 'El campo Licencia no debe exceder los 20 dígitos.'
      ]
    },
    streetRules (val) {
      return [
        val => (this.$v.driver.fields.street.required) || 'El campo Calle es requerido.',
        val => (this.$v.driver.fields.street.maxLength) || 'El campo Calle no debe exceder los 100 dígitos.'
      ]
    },
    cpRules (val) {
      return [
        val => (this.$v.driver.fields.postal_code_id.required) || 'El campo código postal es requerido.'
      ]
    },
    outdoor_numberRules (val) {
      return [
        val => (this.$v.driver.fields.outdoor_number.required) || 'El campo Número exterior es requerido.'
      ]
    },
    suburbRules (val) {
      return [
        val => (this.$v.driver.fields.suburb_id.required) || 'El campo Colonia es requerido.'
      ]
    },
    between_streetRules (val) {
      return [
        val => (this.$v.driver.fields.between_street.required) || 'El campo Entre calles es requerido.'
      ]
    }
  },
  beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(2) && !this.$store.getters['users/roles'].includes(8)) {
      this.$router.push('/')
    }
  },
  created () {
    this.getEstados()
  },
  methods: {
    createDriver () {
      this.$v.driver.fields.$reset()
      this.$v.driver.fields.$touch()
      if (this.$v.driver.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = { ...this.driver.fields }
      api.post('/drivers', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.$router.push(`/drivers/${data.id}`)
        } else {
          this.$q.loading.hide()
        }
      })
    },
    getEstados () {
      this.selectEstados = []
      api.get('/branch-offices/states').then(({ data }) => {
        this.selectEstados = data.options
        // console.log(data)
      }).catch(error => error)
    },
    async getPostalCodes (code = '') {
      code = code === '' ? '0' : code
      await api.get(`/branch-offices/postal_codes/${code}/${this.driver.fields.municipio_id}`).then(({ data }) => {
        this.postal_codes = data.options
      }).catch(error => error)
    },
    async getMunicipiosbyEstado (id, postalReset = false) {
      this.driver.fields.postal_code_id = postalReset ? null : this.driver.fields.postal_code_id
      this.driver.fields.municipio_id = null
      this.driver.fields.city_id = null
      this.selectMunicipio = []
      this.getcities(id)
      await api.get(`/branch-offices/municipalities/${id}/${this.driver.fields.postal_code_id}`).then(({ data }) => {
        this.selectMunicipio = data.options
      }).catch(error => error)
    },
    async getcities (id) {
      this.cities = []
      await api.get(`/branch-offices/cities/${id}/${this.driver.fields.postal_code_id}`).then(({ data }) => {
        this.cities = data.options
      }).catch(error => error)
    },
    async getSuburbsByPostalCode (id) {
      this.suburbs = []
      this.driver.fields.suburb_id = null
      this.driver.fields.municipio_id = null
      this.driver.fields.estado_id = null
      this.driver.fields.city_id = null
      this.selectMunicipio = []
      if (id !== null) {
        this.driver.fields.estado_id = this.postal_codes.filter(v => v.value === id)[0].state_id
        this.estadoOptions = this.selectEstados.filter(v => v.value === this.driver.fields.estado_id)
        await this.getMunicipiosbyEstado(this.driver.fields.estado_id)
        this.driver.fields.municipio_id = this.postal_codes.filter(v => v.value === id)[0].municipality_id
        this.MunicipioOptions = this.selectMunicipio.filter(v => v.value === this.driver.fields.municipio_id)
        await this.getcities(this.driver.fields.estado_id)
        this.driver.fields.city_id = this.postal_codes.filter(v => v.value === id)[0].city_id
        this.cityOptions = this.cities.filter(v => v.value === this.driver.fields.city_id)

        await api.get(`/branch-offices/suburbs/${id}`).then(({ data }) => {
          this.suburbs = data.options
          this.suburbs_options = data.options
        }).catch(error => error)
      }
    },
    filterMunicipio (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.MunicipioOptions = this.selectMunicipio.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    filterEstado (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.estadoOptions = this.selectEstados.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    filterPostalCodes (val, update, abort) {
      update(async () => {
        const needle = val.toLowerCase()
        await this.getPostalCodes(needle)
      })
    },
    filterSuburbs (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.suburbs_options = this.suburbs.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    filterCity (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.cityOptions = this.cities.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    }
  }
}
</script>

<style>
</style>

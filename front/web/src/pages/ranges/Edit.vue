<template>
  <q-page>
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-9">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Destinos" to="/ranges" />
              <q-breadcrumbs-el label="Editar Destino" v-text= "range.fields.name"/>
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
            <div class="col-xs-12 col-sm-4">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="range.fields.name"
                :error="$v.range.fields.name.$error"
                label="Nombre"
                :rules="nameRules"
                @input="v => { range.fields.name = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-signature" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="range.fields.state"
                :error="$v.range.fields.state.$error"
                label="Estado"
                :rules="stateRules"
                :options="stateOptionsFilter"
                @filter="filterState"
                @input="clearMunicipality()"
                use-input
                hide-selected
                fill-input
                input-debounce="0"
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
            <div class="col-xs-12 col-sm-3">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="range.fields.municipality"
                :error="$v.range.fields.municipality.$error"
                label="Municipio"
                :rules="municipalityRules"
                :options="municipalityOptionsFilter"
                @filter="filterMunicipality"
                use-input
                hide-selected
                fill-input
                input-debounce="0"
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
            <div class="col-xs-12 col-sm-2">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="range.fields.distance"
                :error="$v.range.fields.distance.$error"
                label="Distancia"
                :rules="distanceRules"
                @input="v => { range.fields.distance = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="social_distance" />
                </template>
              </q-input>
            </div>
          </div>

          <div class="row q-mb-sm q-mt-md">
            <div class="col-xs-12 col-sm-2 offset-sm-10 pull-right">
              <q-btn color="positive" icon="save" label="Actualizar" @click="editRange()" />
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
  name: 'EditRange',
  validations: {
    range: {
      fields: {
        name: { required },
        distance: { required, decimal },
        state: { required },
        municipality: { required }
      }
    }
  },
  data () {
    return {
      range: {
        fields: {
          name: null,
          distance: null,
          state: null,
          municipality: null
        }
      },
      stateOptions: [],
      stateOptionsFilter: [],
      municipalityOptions: [],
      municipalityOptionsFilter: []
    }
  },
  computed: {
    nameRules (val) {
      return [
        val => (this.$v.range.fields.name.required) || 'El campo Nombre es requerido.'
      ]
    },
    distanceRules (val) {
      return [
        val => (this.$v.range.fields.distance.required) || 'El campo Distancia es requerido.',
        val => (this.$v.range.fields.distance.decimal) || 'El campo Distancia es numerico.'
      ]
    },
    stateRules (val) {
      return [
        val => (this.$v.range.fields.state.required) || 'El campo Estado es requerido.'
      ]
    },
    municipalityRules (val) {
      return [
        val => (this.$v.range.fields.municipality.required) || 'El campo Minicipio es requerido.'
      ]
    },
    filteredMunicipalityOptions () {
      if (this.range.fields.state != null) {
        let stateId = this.range.fields.state
        if (this.range.fields.state.value) {
          stateId = this.range.fields.state.value
        }
        return this.municipalityOptions.filter(m => Number(m.state_id) === Number(stateId))
      }
      return this.municipalityOptions
    }
  },
  beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(8)) {
      this.$router.push('/')
    }
  },
  created () {
    this.fetchFromServer()
    this.getState()
    this.getMunicipality()
  },
  methods: {
    fetchFromServer () {
      this.$q.loading.show()
      const id = this.$route.params.id
      api.get(`/ranges/${id}`).then(({ data }) => {
        if (!data.range) {
          this.$router.push('/ranges')
        } else {
          this.range.fields = data.range
          this.range.fields.state = { value: data.range.state_id, label: data.range.state_name }
          this.range.fields.municipality = { value: data.range.municipality_id, label: data.range.municipality_name }
          this.$q.loading.hide()
        }
      })
    },
    editRange () {
      this.$v.range.fields.$reset()
      this.$v.range.fields.$touch()
      if (this.$v.range.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      if (this.range.fields.municipality == null) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, seleccióne un municipio.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = { ...this.range.fields }
      if (this.range.fields.state.value) {
        params.state = this.range.fields.state.value
      }
      if (this.range.fields.municipality.value) {
        params.municipality = this.range.fields.municipality.value
      }
      api.put(`/ranges/${params.id}`, params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.$router.push('/ranges')
        } else {
          this.$q.loading.hide()
        }
      })
    },
    getState () {
      api.get('ranges/getStateOptions').then(data => {
        this.stateOptions = data.data.options
      })
    },
    filterState (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.stateOptionsFilter = this.stateOptions.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    getMunicipality () {
      api.get('ranges/getMunicipalityOptions').then(data => {
        this.municipalityOptions = data.data.options
      })
    },
    filterMunicipality (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.municipalityOptionsFilter = this.filteredMunicipalityOptions.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    clearMunicipality () {
      this.range.fields.municipality = null
      this.municipalityOptions = null
      this.municipalityOptionsFilter = null
      this.getMunicipality()
    }
  }
}
</script>

<style>
</style>

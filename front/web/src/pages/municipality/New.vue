<template>
  <q-page>
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-9">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Municipios" to="/municipality" />
              <q-breadcrumbs-el label="Nuevo municipio" />
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
                v-model="municipality.fields.state_id"
                :error="$v.municipality.fields.state_id.$error"
                label="Estado"
                :rules="stateRules"
                :options="stateOptionsFilter"
                @filter="filterState"
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
            <div class="col-xs-12 col-sm-9">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="municipality.fields.name"
                :error="$v.municipality.fields.name.$error"
                label="Nombre"
                :rules="nameRules"
                @input="v => { municipality.fields.name = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-signature" />
                </template>
              </q-input>
            </div>
          </div>

          <div class="row q-mb-sm q-mt-md">
            <div class="col-xs-12 col-sm-2 offset-sm-10 pull-right">
              <q-btn color="positive" icon="save" label="Guardar" @click="createState()" />
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
  name: 'NewState',
  validations: {
    municipality: {
      fields: {
        name: { required },
        state_id: { required }
      }
    }
  },
  data () {
    return {
      municipality: {
        fields: {
          name: null,
          state_id: null
        }
      },
      stateOptions: [],
      stateOptionsFilter: []
    }
  },
  mounted () {
    this.getState()
  },
  computed: {
    nameRules (val) {
      return [
        val => (this.$v.municipality.fields.name.required) || 'El campo Nombre es requerido.'
      ]
    },
    stateRules (val) {
      return [
        val => (this.$v.municipality.fields.state_id.required) || 'El campo Estado es requerido.'
      ]
    }
  },
  beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(8)) {
      this.$router.push('/')
    }
  },
  methods: {
    createState () {
      this.$v.municipality.fields.$reset()
      this.$v.municipality.fields.$touch()
      if (this.$v.municipality.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = { ...this.municipality.fields }
      api.post('/municipality', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.$router.push('/municipality')
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
    }
  }
}
</script>

<style>
</style>

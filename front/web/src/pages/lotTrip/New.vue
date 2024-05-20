<template>
  <q-page>
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-9">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Embarque" :to='idUrlBack' />
              <q-breadcrumbs-el label="Nuevo envio" />
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
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                disable
                v-model="lot.fields.folio"
                label="Folio"
              >
                <template v-slot:prepend>
                  <q-icon name="fingerprint" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-3 text-center">
                <q-select color="dark"
                bg-color="secondary"
                filled
                :rules="dateRules"
                v-model="lot.fields.date"
                :error="$v.lot.fields.date.$error"
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
                        v-model="lot.fields.date"
                        @input="() => $refs.date_ref.hide()"
                        today-btn>
                        </q-date>
                    </div>
                </q-popup-proxy>
                </q-select>
            </div>
            <div class="col-xs-12 col-sm-6">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="lot.fields.client"
                :error="$v.lot.fields.client.$error"
                label="Cliente"
                :rules="clientRules"
                @filter="filterClient"
                :options="clientOptionsFilter"
                use-input
                hide-selected
                fill-input
                input-debounce="0"
                hint="Basic autocomplete"
                emit-value
                map-options
                >
                <template v-slot:prepend>
                  <q-icon name="person" />
                </template>
                <template v-slot:no-option>
                  <q-item>
                    <q-item-section class="groups">
                      No hay Resultados
                    </q-item-section>
                  </q-item>
                </template>
              </q-select>
            </div>
          </div>

          <div class="row q-mb-sm q-mt-md">
            <div class="col-xs-12 col-sm-2 offset-sm-10 pull-right">
              <q-btn color="positive" icon="add" label="Crear" @click="createLotTrip()" />
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
  name: 'NewShipping',
  validations: {
    lot: {
      fields: {
        date: { required },
        client: { required }
      }
    }
  },
  data () {
    return {
      lot: {
        fields: {
          folio: null,
          date: null,
          client: null,
          trip_id: Number(this.$route.params.id)
        }
      },
      idUrlBack: '/trips/' + this.$route.params.id,
      id: Number(this.$route.params.id),
      clientOptions: [],
      clientOptionsFilter: []
    }
  },
  mounted () {
    this.getClient()
    this.getFolioTrip()
  },
  computed: {
    dateRules (val) {
      return [
        val => (this.$v.lot.fields.date.required) || 'El campo Fecha es requerido.'
      ]
    },
    clientRules (val) {
      return [
        val => (this.$v.lot.fields.client.required) || 'El campo Cliente es requerido.'
      ]
    }
  },
  beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(10)) {
      this.$router.push('/')
    }
  },
  methods: {
    createLotTrip () {
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
      this.$q.loading.show()
      const params = { ...this.lot.fields }
      params.date = this.lot.fields.date.substr(6, 10) + '-' + this.lot.fields.date.substr(3, 2) + '-' + this.lot.fields.date.substr(0, 2)
      api.post('/shippings', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.$router.push(`/lotTrip/${data.id}`)
        } else {
          this.$q.loading.hide()
        }
      })
    },
    getClient () {
      api.get('customers/options').then(data => {
        this.clientOptions = data.data.options
      })
    },
    filterClient (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.clientOptionsFilter = this.clientOptions.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    getFolioTrip () {
      api.get(`shippings/folio/${this.id}`).then(data => {
        const folioTrip = data.data.folio.folio
        api.get(`shippings/folioShipping/${this.id}`).then(data => {
          const number = data.data.folioShipping.number
          this.lot.fields.folio = folioTrip + '-' + number
        })
      })
    }
  }
}
</script>

<style>
</style>

<template>
  <q-page>
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-9">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Tipo de Cambio" to="/exchanges" />
              <q-breadcrumbs-el label="Nuevo Tipo de Cambio" />
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
                v-model="exchange.fields.currency"
                :options="currencyOptions"
                label="Tipo de Moneda"
                :rules="currencyRules"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-dollar-sign" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-3 text-center">
                <q-select color="dark"
                bg-color="secondary"
                filled
                :rules="dateRules"
                v-model="exchange.fields.date"
                mask="date"
                label="Fecha">
                <template v-slot:prepend>
                    <q-icon name="event"></q-icon>
                </template>
                <q-popup-proxy ref="date" transition-show="scale" transition-hide="scale">
                    <div class="col-sm-12">
                        <q-date
                        color="secondary"
                        :locale="myLocale"
                        text-color="white"
                        mask="DD/MM/YYYY"
                        v-model="exchange.fields.date"
                        today-btn>
                        </q-date>
                    </div>
                </q-popup-proxy>
                </q-select>
            </div>
            <div class="col-xs-12 col-sm-6">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                type="number"
                v-model="exchange.fields.current_value"
                :error="$v.exchange.fields.current_value.$error"
                label="Valor"
                :rules="currentValueRules"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-signature" />
                </template>
              </q-input>
            </div>
          </div>

          <div class="row q-mb-sm q-mt-md">
            <div class="col-xs-12 col-sm-2 offset-sm-10 pull-right">
              <q-btn color="positive" icon="save" label="Actualizar" @click="editExchange()" />
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
  name: 'NewExchange',
  validations: {
    exchange: {
      fields: {
        current_value: { required },
        date: { required },
        currency: { required }
      }
    }
  },
  data () {
    return {
      myLocale: {
        /* starting with Sunday */
        days: 'Domingo_Lunes_Martes_Miércoles_Jueves_Viernes_Sábado'.split('_'),
        daysShort: 'Dom_Lun_Mar_Mié_Jue_Vie_Sáb'.split('_'),
        months: 'Enero_Febrero_Marzo_Abril_Mayo_Junio_Julio_Agosto_Septiembre_Octubre_Noviembre_Diciembre'.split('_'),
        monthsShort: 'Ene_Feb_Mar_Abr_May_Jun_Jul_Ago_Sep_Oct_Nov_Dic'.split('_'),
        firstDayOfWeek: 1
      },
      exchange: {
        fields: {
          current_value: null,
          date: null,
          currency: null
        }
      },
      currencyOptions: []
    }
  },
  computed: {
    dateRules (val) {
      return [
        val => (this.$v.exchange.fields.date.required) || 'El campo Fecha es requerido.'
      ]
    },
    currentValueRules (val) {
      return [
        val => (this.$v.exchange.fields.current_value.required) || 'El campo valor es requerido.'
      ]
    },
    currencyRules (val) {
      return [
        val => this.$v.exchange.fields.currency.required || 'El campo Tipo de Moneda es requerido.'
      ]
    }
  },
  beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(10)) {
      this.$router.push('/')
    }
  },
  created () {
    this.getCurrencies()
    this.fetchFromServer()
  },
  mounted () {},
  methods: {
    editExchange () {
      this.$v.exchange.fields.$reset()
      this.$v.exchange.fields.$touch()
      if (this.$v.exchange.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      var params = []
      params.id = Number(this.exchange.fields.id)
      params.date = this.invertDate(this.exchange.fields.date)
      params.current_value = Number({ ...this.exchange.fields }.current_value)
      params.currency_id = { ...this.exchange.fields }.currency.value
      api.put(`/exchanges/${params.id}`, params).then(({ data }) => {
        console.log(data)
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.$router.push('/exchanges')
        } else {
          this.$q.loading.hide()
        }
      })
    },
    invertDate (date) {
      if (date !== null) {
        var info = date.split('/').reverse().join('-')
      }
      return info
    },
    fetchFromServer () {
      this.$q.loading.show()
      const id = this.$route.params.id
      api.get(`/exchanges/${id}`).then(({ data }) => {
        if (!data.exchange) {
          this.$q.loading.hide()
          this.$router.push('/exchanges')
        } else {
          console.log(data.exchange)
          this.exchange.fields = data.exchange
          this.exchange.fields.currency = { label: data.exchange.label, value: data.exchange.value }
        }
        this.$q.loading.hide()
        console.log(this.exchange.fields)
      })
    },
    getCurrencies () {
      api.get('/currencies/options').then(({ data }) => {
        this.currencyOptions = data.options
        this.$q.loading.hide()
      })
    }
  }
}
</script>

<style>
</style>

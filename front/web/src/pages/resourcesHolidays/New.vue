<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-9">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Días festivos" to="/holidays" />
              <q-breadcrumbs-el label="Nuevo Día festivo" />
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
            <div class="col-xs-12 col-sm-2 text-center">
                <q-select color="dark"
                bg-color="secondary"
                filled
                v-model="holiday.fields.date"
                mask="date"
                label="Fecha">
                <template v-slot:prepend>
                    <q-icon name="event"></q-icon>
                </template>
                <q-popup-proxy
                ref="date"
                transition-show="scale"
                transition-hide="scale">
                    <div class="col-sm-12">
                        <q-date
                        color="secondary"
                        text-color="white"
                        mask="DD/MM/YYYY"
                        v-model="holiday.fields.date"
                        @input="() => $refs.date_ref.hide()"
                        today-btn>
                        </q-date>
                    </div>
                </q-popup-proxy>
                </q-select>
            </div>
            <div class="col-xs-12 col-sm-10">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="holiday.fields.note"
                :error="$v.holiday.fields.note.$error"
                label="Nota"
                :rules="noteRules"
                @input="v => { holiday.fields.note = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-signature" />
                </template>
              </q-input>
            </div>
          </div>

          <div class="row q-mb-sm q-mt-md">
            <div class="col-xs-12 col-sm-2 offset-sm-10 pull-right">
              <q-btn color="positive" icon="save" label="Guardar" @click="createHoliday()" />
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
  date: 'Newholiday',
  validations: {
    holiday: {
      fields: {
        date: { required },
        note: { required }
      }
    }
  },
  data () {
    return {
      holiday: {
        fields: {
          date: null,
          note: null
        }
      }
    }
  },
  computed: {
    dateRules (val) {
      return [
        val => (this.$v.holiday.fields.date.required) || 'El campo Fecha es requerido.'
      ]
    },
    noteRules (val) {
      return [
        val => (this.$v.holiday.fields.date.required) || 'El campo Nota es requerido.'
      ]
    }
  },
  beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(4)) {
      this.$router.push('/')
    }
  },
  methods: {
    createHoliday () {
      this.$v.holiday.fields.$reset()
      this.$v.holiday.fields.$touch()
      if (this.$v.holiday.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = { ...this.holiday.fields }
      api.post('/holidays', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.$router.push('/holidays')
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

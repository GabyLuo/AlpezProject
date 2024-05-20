<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-9">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Horarios" to="/timetables" />
              <q-breadcrumbs-el label="Editar" v-text="timetable.fields.name"/>
            </q-breadcrumbs>
          </div>
        </div>
      </div>
    </div>
    <div class="q-pa-md bg-grey-3">
      <div class="row bg-white border-panel">
        <div class="col q-pa-md">
          <div class="row q-col-gutter-xs">
            <div class="col-xs-12 col-sm-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="timetable.fields.name"
                :error="$v.timetable.fields.name.$error"
                label="Nombre"
                :rules="nameRules"
                @input="v => { timetable.fields.name = v.toUpperCase() }"
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
                v-model="timetable.fields.shift"
                :options="shiftOptions"
                label="Turno"
                :rules="shifttRules"
              >
                <template v-slot:prepend>
                  <q-icon name="brightness_6" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="timetable.fields.time_entry"
                label="Hora Entrada"
                :rules="entryRules">
                <template v-slot:append>
                  <q-icon name="access_time" class="cursor-pointer">
                    <q-popup-proxy transition-show="scale" transition-hide="scale">
                      <q-time v-model="timetable.fields.time_entry" color="secondary" bg-color="secondary">
                        <div class="row items-center justify-end">
                          <q-btn v-close-popup label="Cerrar" color="primary" flat></q-btn>
                        </div>
                      </q-time>
                    </q-popup-proxy>
                  </q-icon>
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="timetable.fields.time_departure"
                label="Hora Salida"
                :rules="departureRules">
                <template v-slot:append>
                  <q-icon name="access_time" class="cursor-pointer">
                    <q-popup-proxy transition-show="scale" transition-hide="scale">
                      <q-time v-model="timetable.fields.time_departure" color="secondary" bg-color="secondary">
                        <div class="row items-center justify-end">
                          <q-btn v-close-popup label="Cerrar" color="primary" flat></q-btn>
                        </div>
                      </q-time>
                    </q-popup-proxy>
                  </q-icon>
                </template>
              </q-input>
            </div>
          </div>
          <div class="row q-mb-sm q-mt-md">
            <div class="col-xs-12 col-sm-2 offset-sm-10 pull-right">
              <q-btn color="positive" icon="save" label="Actualizar" @click="updateTimetable()" />
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
  name: 'EditTimetable',
  validations: {
    timetable: {
      fields: {
        shift: { required },
        time_entry: { required },
        time_departure: { required },
        name: { required }
      }
    }
  },
  data () {
    return {
      timetable: {
        fields: {
          id: null,
          shift: null,
          time_entry: null,
          time_departure: null,
          name: null
        }
      },
      shiftOptions: []
    }
  },
  computed: {
    shifttRules (val) {
      return [
        val => this.$v.timetable.fields.shift.required || 'El campo Turno es requerido.'
      ]
    },
    nameRules (val) {
      return [
        val => this.$v.timetable.fields.name.required || 'El campo Nombre es requerido.'
      ]
    },
    entryRules (val) {
      return [
        val => this.$v.timetable.fields.time_entry.required || 'El campo Hora Entrada es requerido.'
      ]
    },
    departureRules (val) {
      return [
        val => this.$v.timetable.fields.time_departure.required || 'El campo Hora Salida es requerido.'
      ]
    }
  },
  beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(4)) {
      this.$router.push('/')
    }
  },
  mounted () {
    this.fetchFromServer()
    this.getShifts()
  },
  methods: {
    fetchFromServer () {
      this.$q.loading.show()
      const id = this.$route.params.id
      api.get(`/timetables/${id}`).then(({ data }) => {
        if (!data.timetable) {
          this.$router.push('/timetables')
        } else {
          console.log(data)
          this.timetable.fields.id = id
          this.timetable.fields.shift = { value: data.timetable.job_title_id, label: data.timetable.shift_name }
          this.timetable.fields.time_entry = data.timetable.check_in_time
          this.timetable.fields.time_departure = data.timetable.check_out_time
          this.timetable.fields.name = data.timetable.name
          this.$q.loading.hide()
          console.log(this.timetable.fields)
        }
      })
      this.$q.loading.hide()
    },
    getShifts () {
      api.get('/shifts/options').then(({ data }) => {
        this.shiftOptions = data.options
      })
    },
    updateTimetable () {
      this.$v.timetable.fields.$reset()
      this.$v.timetable.fields.$touch()
      if (this.$v.timetable.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = []
      params.id = Number(this.timetable.fields.id)
      params.shift_id = Number(this.timetable.fields.shift.value)
      params.time_entry = this.timetable.fields.time_entry
      params.time_departure = this.timetable.fields.time_departure
      params.name = this.timetable.fields.name
      api.put(`/timetables/${params.id}`, params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.$router.push('/timetables')
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

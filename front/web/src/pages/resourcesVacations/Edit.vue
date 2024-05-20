<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-9">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Derecho a vacaciones" to="/vacation-grants" />
              <q-breadcrumbs-el label="Editar derecho a vacaciones" v-text= "vacation.fields.year"/>
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
            <div class="col-xs-12 col-sm-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="vacation.fields.year"
                :error="$v.vacation.fields.year.$error"
                label="Años"
                :rules="yearsRules"
                @input="v => { vacation.fields.year = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-signature" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="vacation.fields.day"
                :error="$v.vacation.fields.day.$error"
                label="Días"
                :rules="daysRules"
                @input="v => { vacation.fields.day = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-signature" />
                </template>
              </q-input>
            </div>
          </div>

          <div class="row q-mb-sm q-mt-md">
            <div class="col-xs-12 col-sm-2 offset-sm-10 pull-right">
              <q-btn color="positive" icon="save" label="Actualizar" @click="editVacations()" />
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
  date: 'Newvacation',
  validations: {
    vacation: {
      fields: {
        year: { required },
        day: { required }
      }
    }
  },
  data () {
    return {
      vacation: {
        fields: {
          id: null,
          year: null,
          day: null
        }
      }
    }
  },
  computed: {
    yearsRules (val) {
      return [
        val => (this.$v.vacation.fields.year.required) || 'El campo Año es requerido.'
      ]
    },
    daysRules (val) {
      return [
        val => (this.$v.vacation.fields.day.required) || 'El campo Dias es requerido.'
      ]
    }
  },
  beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(4) && !this.$store.getters['users/roles'].includes(5)) {
      this.$router.push('/')
    }
  },
  created () {
    this.fetchFromServer()
  },
  methods: {
    fetchFromServer () {
      this.$q.loading.show()
      const id = this.$route.params.id
      api.get(`/vacations/${id}`).then(({ data }) => {
        if (!data.vacation) {
          this.$router.push('/vacation-grants')
        } else {
          this.vacation.fields = data.vacation
          this.$q.loading.hide()
        }
      })
    },
    updateVacationsFields () {
      this.$v.vacation.fields.$reset()
      this.$v.vacation.fields.$touch()
    },
    editVacations () {
      this.$v.vacation.fields.$reset()
      this.$v.vacation.fields.$touch()
      if (this.$v.vacation.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = { ...this.vacation.fields }
      api.put(`/vacations/${params.id}`, params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.$router.push('/vacation-grants')
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

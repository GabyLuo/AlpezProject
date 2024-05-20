<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-9">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Derecho a vacaciones" to="/vacation-grants" />
              <q-breadcrumbs-el label="Nuevo derecho a vaciones" />
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
                v-model="vacation_grant.fields.years"
                :error="$v.vacation_grant.fields.years.$error"
                label="Años"
                :rules="yearsRules"
                @input="v => { vacation_grant.fields.years = v.toUpperCase() }"
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
                v-model="vacation_grant.fields.days"
                :error="$v.vacation_grant.fields.days.$error"
                label="Días"
                :rules="daysRules"
                @input="v => { vacation_grant.fields.days = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-signature" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-12">
              <div class="col-xs-12 col-sm-2 offset-sm-10 pull-right">
                <q-btn color="positive" icon="save" label="Guardar" @click="createvacation_grant()" />
              </div>
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
  date: 'Newvacation_grant',
  validations: {
    vacation_grant: {
      fields: {
        years: { required },
        days: { required }
      }
    }
  },
  data () {
    return {
      vacation_grant: {
        fields: {
          years: null,
          days: null
        }
      }
    }
  },
  computed: {
    yearsRules (val) {
      return [
        val => (this.$v.vacation_grant.fields.years.required) || 'El campo Años es requerido.'
      ]
    },
    daysRules (val) {
      return [
        val => (this.$v.vacation_grant.fields.days.required) || 'El campo Dias es requerido.'
      ]
    }
  },
  beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(4) && !this.$store.getters['users/roles'].includes(5)) {
      this.$router.push('/')
    }
  },
  methods: {
    createvacation_grant () {
      this.$v.vacation_grant.fields.$reset()
      this.$v.vacation_grant.fields.$touch()
      if (this.$v.vacation_grant.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = { ...this.vacation_grant.fields }
      api.post('/vacations', params).then(({ data }) => {
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

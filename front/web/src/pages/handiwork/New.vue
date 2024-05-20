<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-9">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Mano de Obra" to="/works" />
              <q-breadcrumbs-el label="Nueva Mano de Obra" />
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
            <div class="col-xs-12 col-sm-4">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="work.fields.name"
                :error="$v.work.fields.name.$error"
                label="Nombre"
                :rules="nameRules"
                @input="v => { work.fields.name = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-signature" />
                </template>
              </q-input>
            </div>
            <!--<div class="col-xs-12 col-sm-4">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="work.fields.time_job"
                :error="$v.work.fields.time_job.$error"
                label="Tiempo/Minuto"
                :rules="timeRules"
                @input="v => { work.fields.time_job = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-clock" />
                </template>
              </q-input>
            </div>-->
            <div class="col-xs-12 col-sm-4">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="work.fields.price"
                :error="$v.work.fields.price.$error"
                label="Costo por hora"
                :rules="priceRueles"
                @input="v => { work.fields.price = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-dollar-sign" />
                </template>
              </q-input>
            </div>
          </div>

          <div class="row q-mb-sm q-mt-md">
            <div class="col-xs-12 col-sm-2 offset-sm-10 pull-right">
              <q-btn color="positive" icon="save" label="Guardar" @click="createWork()" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </q-page>
</template>

<script>
import api from '../../commons/api.js'
const { required, maxLength, decimal } = require('vuelidate/lib/validators')
// const num = helpers.regex('num', /[0-9]+$/)

export default {
  name: 'NewLine',
  validations: {
    work: {
      fields: {
        name: { required, maxLength: maxLength(50) },
        price: { required, decimal },
        time_job: { }
      }
    }
  },
  data () {
    return {
      work: {
        fields: {
          name: null,
          price: null,
          time_job: 1
        }
      }
    }
  },
  computed: {
    nameRules (val) {
      return [
        val => (this.$v.work.fields.name.required) || 'El campo Nombre es requerido.',
        val => (this.$v.work.fields.name.maxLength) || 'El campo Nombre no debe exceder los 50 dígitos.'
      ]
    },
    priceRueles (val) {
      return [
        val => (this.$v.work.fields.price.required) || 'El campo Costo es requerido.'
      ]
    },
    timeRules () {
      return [
        val => (this.$v.work.fields.time_job.required) || 'El campo Tiempo/Minuto es requerido.'
      ]
    }
  },
  beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7)) {
      this.$router.push('/')
    }
  },
  created () {
    this.fetchFromServer()
  },
  mounted () {},
  methods: {
    fetchFromServer () {
      this.$q.loading.show()
      api.get('/works/options').then(({ data }) => {
        this.categoryOptions = data.options
        this.$q.loading.hide()
      })
    },
    createWork () {
      this.$v.work.fields.$reset()
      this.$v.work.fields.$touch()
      if (this.$v.work.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = { ...this.work.fields }
      api.post('/works', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'red')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.$router.push('/works')
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

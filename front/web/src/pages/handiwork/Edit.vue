<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-9"  style="font-size: 20px">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Mano de Obra" to="/works" />
              <q-breadcrumbs-el label="Editar Mano de Obra" v-text="work.fields.name" />
            </q-breadcrumbs>
          </div>
        </div>
        <div class="col-sm-3">
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
                label="Tiempo/Minutos"
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
              <q-btn color="positive" icon="save" label="Actualizar" @click="editWork()" />
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
  name: 'EditLine',
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
          id: null,
          name: null,
          price: null,
          time_job: 1
        }
      },
      categoryhandiwork: []
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
        val => (this.$v.work.fields.time_job.required) || 'El campo Tiempo/Minutos es requerido.'
      ]
    }
  },
  beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7)) {
      this.$router.push('/')
    }
  },
  created () {
    this.getCategoriesList()
    this.fetchFromServer()
  },
  methods: {
    getCategoriesList () {
      api.get('/works/options').then(({ data }) => {
        this.categoryhandiwork = data.options
        this.$q.loading.hide()
      })
    },
    fetchFromServer () {
      this.$q.loading.show()
      const id = this.$route.params.id
      api.get(`/works/${id}`).then(({ data }) => {
        if (!data.gethandiwork) {
          this.$q.loading.hide()
          this.$router.push('/works')
        } else {
          console.log(data.gethandiwork)
          this.work.fields = data.gethandiwork
        }
        this.$q.loading.hide()
        console.log(this.work.fields)
      })
    },
    updateWork () {
      this.$v.work.fields.$reset()
      this.$v.work.fields.$touch()
    },
    editWork () {
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
      api.put(`/works/${params.id}`, params).then(({ data }) => {
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

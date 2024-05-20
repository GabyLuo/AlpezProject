<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-9">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Areas" to="/area" />
              <q-breadcrumbs-el label="Nueva Area" />
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
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="area.fields.department"
                :options="departmentOptions"
                label="Departamentos"
                :rules="departmentRules"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-building" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-8">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="area.fields.name"
                :error="$v.area.fields.name.$error"
                label="Nombre"
                :rules="nameRules"
                @input="v => { area.fields.name = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-signature" />
                </template>
              </q-input>
            </div>
          </div>

          <div class="row q-mb-sm q-mt-md">
            <div class="col-xs-12 col-sm-2 offset-sm-10 pull-right">
              <q-btn color="positive" icon="save" label="Guardar" @click="createArea()" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </q-page>
</template>

<script>
import api from '../../commons/api.js'
const { required, maxLength } = require('vuelidate/lib/validators')

export default {
  name: 'NewArea',
  validations: {
    area: {
      fields: {
        name: { required, maxLength: maxLength(20) },
        department: { required }
      }
    }
  },
  data () {
    return {
      area: {
        fields: {
          name: null,
          department: null
        }
      },
      departmentOptions: []
    }
  },
  computed: {
    nameRules (val) {
      return [
        val => (this.$v.area.fields.name.required) || 'El campo Nombre es requerido.',
        val => (this.$v.area.fields.name.maxLength) || 'El campo Nombre no debe exceder los 20 dígitos.'
      ]
    },
    departmentRules (val) {
      return [
        val => this.$v.area.fields.department.required || 'El campo Departamento es requerido.'
      ]
    }
  },
  beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(4)) {
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
      api.get('/departments/options').then(({ data }) => {
        this.departmentOptions = data.options
        this.$q.loading.hide()
      })
    },
    createArea () {
      this.$v.area.fields.$reset()
      this.$v.area.fields.$touch()
      if (this.$v.area.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = []
      params.name = { ...this.area.fields }.name
      params.department_id = { ...this.area.fields }.department.value
      api.post('/areas', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.$router.push('/areas')
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

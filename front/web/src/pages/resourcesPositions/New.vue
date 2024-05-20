<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-9">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Puestos" to="/positions" />
              <q-breadcrumbs-el label="Nuevo Puesto" />
            </q-breadcrumbs>
          </div>
        </div>
      </div>
    </div>

    <div class="q-pa-md bg-grey-3">
      <div class="row bg-white border-panel">
        <div class="col q-pa-md">
          <div class="row q-col-gutter-xs">
            <div class="col-xs-12 col-sm-6">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="position.fields.department"
                :options="departmentOptions"
                label="Departamentos"
                :rules="departmentRules"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-building" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-6">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="position.fields.area"
                :options="filteredAreaOptions"
                label="Areas"
                :rules="areaRules"
              >
                <template v-slot:prepend>
                  <q-icon name="list" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-12">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="position.fields.name"
                :error="$v.position.fields.name.$error"
                label="Nombre"
                :rules="nameRules"
                @input="v => { position.fields.name = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-signature" />
                </template>
              </q-input>
            </div>
          </div>
          <div class="row q-mb-sm q-mt-md">
            <div class="col-xs-12 col-sm-2 offset-sm-10 pull-right">
              <q-btn color="positive" icon="save" label="Guardar" @click="createPosition()" />
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
  name: 'NewPosition',
  validations: {
    position: {
      fields: {
        name: { required, maxLength: maxLength(100) },
        department: { required },
        area: { required }
      }
    }
  },
  data () {
    return {
      position: {
        fields: {
          name: null,
          department: null,
          area: null
        }
      },
      departmentOptions: [],
      areaOptions: []
    }
  },
  computed: {
    nameRules (val) {
      return [
        val => (this.$v.position.fields.name.required) || 'El campo Nombre es requerido.',
        val => (this.$v.position.fields.name.maxLength) || 'El campo Nombre no debe exceder los 100 dígitos.'
      ]
    },
    departmentRules (val) {
      return [
        val => this.$v.position.fields.department.required || 'El campo Departamento es requerido.'
      ]
    },
    areaRules (val) {
      return [
        val => this.$v.position.fields.area.required || 'El campo Area es requerido.'
      ]
    },
    filteredAreaOptions () {
      if (this.position.fields.department != null && this.position.fields.department.value != null) {
        return this.areaOptions.filter(area => area.department === this.position.fields.department.value)
      }
      return []
    }
  },
  beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(4)) {
      this.$router.push('/')
    }
  },
  mounted () {
    this.fetchFromServer()
  },
  methods: {
    fetchFromServer () {
      this.$q.loading.show()
      this.getDepartments()
      this.getAreas()
      this.$q.loading.hide()
    },
    getDepartments () {
      api.get('/departments/options').then(({ data }) => {
        this.departmentOptions = data.options
      })
    },
    getAreas () {
      api.get('/areas/options').then(({ data }) => {
        this.areaOptions = data.options
      })
    },
    createPosition () {
      this.$v.position.fields.$reset()
      this.$v.position.fields.$touch()
      if (this.$v.position.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = []
      params.name = this.position.fields.name
      params.area_id = Number(this.position.fields.area.value)
      api.post('/positions', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.$router.push('/positions')
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

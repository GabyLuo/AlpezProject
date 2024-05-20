<template>
  <q-page>
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-8">
          <q-btn color="primary" icon="keyboard_backspace" label="Regresar" @click="backToGrid()" />
          <span class="q-ml-md grey-8 fs28 page-title">Nueva apertura de paca</span>
        </div>
        <div class="col-sm-4">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="right">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Apertura de pacas" to="/bale-opening" />
              <q-breadcrumbs-el label="Nuevo" />
            </q-breadcrumbs>
          </div>
        </div>
      </div>
    </div>

    <div class="q-pa-md bg-grey-3">
      <div class="row bg-white border-panel">
        <div class="col q-pa-md">
          <div class="row q-col-gutter-xs">
            <div class="col-xs-12 col-sm-4 text-center">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="baleOpening.fields.branchOffice"
                :options="branchOfficeOptions"
                label="Sucursal"
                @input="updateBranchOffice()"
                :rules="branchOfficeRules"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-store-alt" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-4 text-center">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="baleOpening.fields.baleStorage"
                :options="storageOptions"
                label="Almacén de pacas"
                :rules="baleStorageRules"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-warehouse" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-4 text-center">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="baleOpening.fields.inBulkStorage"
                :options="storageOptions"
                label="Almacén fibra abierta"
                :rules="inBulkStorageRules"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-warehouse" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-6 text-center">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="baleOpening.fields.operator"
                :options="operatorOptions"
                label="Operador"
                :rules="operatorRules"
              >
                <template v-slot:prepend>
                  <q-icon name="fa fa-user" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-6 text-center">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="baleOpening.fields.openedBy"
                :error="$v.baleOpening.fields.openedBy.$error"
                label="Responsable"
                :rules="openedByRules"
                @input="v => { baleOpening.fields.openedBy = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fa fa-user" />
                </template>
              </q-input>
            </div>
          </div>

          <div class="row q-mb-sm q-mt-md">
            <div class="col-xs-12 col-sm-2 offset-sm-10 pull-right">
              <q-btn color="positive" icon="save" label="Guardar" @click="createSale()" />
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
  name: 'NewBaleOpening',
  validations: {
    baleOpening: {
      fields: {
        branchOffice: { required },
        baleStorage: { required },
        inBulkStorage: { required },
        operator: { required },
        openedBy: { required, maxLength: maxLength(30) }
      }
    }
  },
  data () {
    return {
      baleOpening: {
        fields: {
          saleDate: `${new Date().getFullYear()}/${(new Date().getMonth() + 1).toString().padStart(2, '0')}/${(new Date().getDate()).toString().padStart(2, '0')}`,
          baleStorage: null,
          inBulkStorage: null,
          openedBy: null
        }
      },
      branchOfficeOptions: [],
      storageOptions: [],
      operatorOptions: []
    }
  },
  computed: {
    branchOfficeRules (val) {
      return [
        val => this.$v.baleOpening.fields.branchOffice.required || 'El campo Sucursal es requerido.'
      ]
    },
    baleStorageRules (val) {
      return [
        val => this.$v.baleOpening.fields.baleStorage.required || 'El campo Almacén de pacas es requerido.'
      ]
    },
    inBulkStorageRules (val) {
      return [
        val => this.$v.baleOpening.fields.inBulkStorage.required || 'El campo Almacén fibra abierta es requerido.'
      ]
    },
    operatorRules (val) {
      return [
        val => this.$v.baleOpening.fields.operator.required || 'El campo Operador es requerido.'
      ]
    },
    openedByRules (val) {
      return [
        val => this.$v.baleOpening.fields.openedBy.required || 'El campo Responsable es requerido.',
        val => this.$v.baleOpening.fields.openedBy.maxLength || 'El campo Responsable no debe exceder los 30 dígitos.'
      ]
    }
  },
  beforeCreate () {
    if (!(this.$store.getters['users/roles'].includes(1) || this.$store.getters['users/roles'].includes(3) || this.$store.getters['users/roles'].includes(7) || this.$store.getters['users/roles'].includes(2) || this.$store.getters['users/roles'].includes(3) || this.$store.getters['users/roles'].includes(4) || this.$store.getters['users/roles'].includes(5) || this.$store.getters['users/roles'].includes(13))) {
      this.$router.push('/')
    }
  },
  created () {
    this.fetchFromServer()
  },
  methods: {
    fetchFromServer () {
      this.$q.loading.show()
      api.get('/branch-offices/options').then(({ data }) => {
        this.branchOfficeOptions = data.options
        api.get('/storages/options').then(({ data }) => {
          this.storageOptions = data.options
          api.get('/operators/options').then(({ data }) => {
            this.operatorOptions = data.options
            this.$q.loading.hide()
          })
        })
      })
    },
    backToGrid () {
      this.$router.push('/bale-opening')
    },
    updateBranchOffice () {
      if (this.baleOpening.fields.branchOffice == null || this.baleOpening.fields.branchOffice.value == null) {
        this.baleOpening.fields.baleStorage = null
        this.baleOpening.fields.inBulkStorage = null
      } else {
        this.baleOpening.fields.baleStorage = this.storageOptions.filter(so => Number(so.branchOffice) === Number(this.baleOpening.fields.branchOffice.value) && Number(so.storageType) === 1)[0]
        this.baleOpening.fields.inBulkStorage = this.storageOptions.filter(so => Number(so.branchOffice) === Number(this.baleOpening.fields.branchOffice.value) && Number(so.storageType) === 2)[0]
      }
    },
    createSale () {
      this.$v.baleOpening.fields.$reset()
      this.$v.baleOpening.fields.$touch()
      if (this.$v.baleOpening.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = []
      params.branchOffice = Number({ ...this.baleOpening.fields }.branchOffice.value)
      params.operator = Number({ ...this.baleOpening.fields }.operator.value)
      params.openedBy = { ...this.baleOpening.fields }.openedBy
      api.post('/bale-openings', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.$router.push(`/bale-opening/${data.baleOpening.id}`)
        }
        this.$q.loading.hide()
      })
    }
  }
}
</script>

<style>
</style>

<template>
  <q-page>
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-8">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Traspasos Sucursales" to="/branch-transfers" />
              <q-breadcrumbs-el label="Nuevo Traspaso Sucursales" />
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
          <div class="row q-col-gutter-xs" style="margin-bottom: 20px">
            <div class="col-sm-12 col-md-2">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="branchTrasfer.fields.statusStr"
                label="Estatus"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="battery_full" />
                </template>
              </q-input>
            </div>
          </div>
          <div class="row q-col-gutter-xs">
            <div class="col-sm-12 col-md-6">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="branchTrasfer.fields.originBranchOffice"
                :options="branchOfficeOptions"
                label="Sucursal origen"
                emit-value map-options
                :rules="originBranchOfficeRules"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-store-alt" />
                </template>
              </q-select>
            </div>
            <div class="col-sm-12 col-md-4">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="branchTrasfer.fields.originStorage"
                :options="filteredOriginStorageOptions"
                label="Almacén origen"
                :rules="originStorageRules"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-warehouse" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-2 text-center">
              <q-input
                  color="dark"
                  bg-color="secondary"
                  filled
                  readonly
                  v-model="branchTrasfer.fields.folio_1"
                  label="Folio">
                <template v-slot:prepend>
                    <q-icon name="style"></q-icon>
                </template>
              </q-input>
            </div>
            <div class="col-sm-12 col-md-6">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="branchTrasfer.fields.destinationBranchOffice"
                :options="branchOfficeOptions2"
                label="Sucursal destino"
                :rules="destinationBranchOfficeRules"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-store-alt" />
                </template>
              </q-select>
            </div>
            <div class="col-sm-12 col-md-4">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="branchTrasfer.fields.destinationStorage"
                :options="filteredDestinationStorage"
                label="Almacén destino"
                :rules="destinationStorageRules"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-warehouse" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-2 text-center">
              <q-input
                  color="dark"
                  bg-color="secondary"
                  filled
                  readonly
                  v-model="branchTrasfer.fields.folio_1"
                  label="Folio">
                <template v-slot:prepend>
                    <q-icon name="style"></q-icon>
                </template>
              </q-input>
            </div>
            <!-- <div class="col-sm-12 col-md-2">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="branchTrasfer.fields.operator"
                :options="operatorOptions"
                label="Chofer"
                :rules="operatorRules"
              >
                <template v-slot:prepend>
                  <q-icon name="fa fa-user" />
                </template>
              </q-select>
            </div> -->
            <div class="col-sm-12 col-md-12 pull-right">
              <q-btn color="positive" icon="save" label="Guardar" @click="createBranchOfficeTransfer()" />
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
  name: 'NewBranchTransfer',
  validations: {
    branchTrasfer: {
      fields: {
        originBranchOffice: { required },
        originStorage: { required },
        destinationBranchOffice: { required },
        destinationStorage: { required },
        folio_1: { required },
        folio_2: { required }
      }
    }
  },
  data () {
    return {
      branchTrasfer: {
        fields: {
          id: null,
          originBranchOffice: null,
          originStorage: null,
          destinationBranchOffice: null,
          destinationStorage: null,
          status: null,
          statusStr: 'NUEVO',
          transactionId: null,
          folio_1: null,
          folio_2: null
        }
      },
      branchOfficeOptions: [],
      branchOfficeOptions2: [],
      storageOptions: [],
      storageOptions2: []
    }
  },
  computed: {
    originBranchOfficeRules (val) {
      return [
        val => (this.$v.branchTrasfer.fields.originBranchOffice.required) || 'El campo Sucursal origen es requerido.'
      ]
    },
    originStorageRules (val) {
      return [
        val => (this.$v.branchTrasfer.fields.originStorage.required) || 'El campo Almacén origen es requerido.'
      ]
    },
    destinationBranchOfficeRules (val) {
      return [
        val => (this.$v.branchTrasfer.fields.destinationBranchOffice.required) || 'El campo Sucursal destino es requerido.'
      ]
    },
    destinationStorageRules (val) {
      return [
        val => (this.$v.branchTrasfer.fields.destinationStorage.required) || 'El campo Almacén destino es requerido.'
      ]
    },
    filteredOriginStorageOptions () {
      if (this.branchTrasfer.fields.originBranchOffice != null && !isNaN(this.branchTrasfer.fields.originBranchOffice)) {
        return this.storageOptions.filter(op => Number(op.branchOffice) === Number(this.branchTrasfer.fields.originBranchOffice))
      }
      return []
    },
    filteredDestinationStorage () {
      console.log(this.branchTrasfer.fields.originStorage)
      if (this.branchTrasfer.fields.destinationBranchOffice != null && !isNaN(this.branchTrasfer.fields.destinationBranchOffice.value)) {
        return this.storageOptions2.filter(op => Number(op.branchOffice) === Number(this.branchTrasfer.fields.destinationBranchOffice.value) && Number(this.branchTrasfer.fields.originStorage.value) !== Number(op.value))
      }
      return []
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
        this.branchOfficeOptions2 = data.options
        api.get('/storages/options').then(({ data }) => {
          this.storageOptions = data.options
          this.storageOptions2 = data.options
          this.$q.loading.hide()
        })
      })
    },
    createBranchOfficeTransfer () {
      this.$v.branchTrasfer.fields.$reset()
      this.$v.branchTrasfer.fields.$touch()
      if (this.$v.branchTrasfer.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = []
      // params.operator = Number({ ...this.branchTrasfer.fields }.operator.value)
      params.originStorage = Number({ ...this.branchTrasfer.fields }.originStorage.value)
      params.destinationStorage = Number({ ...this.branchTrasfer.fields }.destinationStorage.value)
      api.post('/branch-transfers', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.$router.push(`/branch-transfers/${data.branchTransfer.id}`)
        } else {
          this.$q.loading.hide()
        }
      })
    },
    prueba () {
      this.branchTrasfer.fields.originStorage = null // Almacen de origen
      const id = this.branchTrasfer.fields.originBranchOffice // Sucursal origen
      this.branchOfficeOptions2 = []
      this.branchOfficeOptions.forEach(element => {
        if (id !== element.value) {
          this.branchOfficeOptions2.push({ label: element.label, value: element.value })
        }
      })
    },
    test () {
      const id = this.branchTrasfer.fields.originStorage.value // Sucursal origen
      // const idSto = this.branchTrasfer.fields.originBranchOffice // Sucursal origen
      // const id2 = this.branchTransfer.fields.originBranchOffice
      this.storageOptions2 = [] // Almacenes a mostrar en la segunda opcion
      this.storageOptions.forEach(element => {
        if (id !== element.value) {
          this.storageOptions2.push({ label: element.label, value: element.value })
        }
      })
    }
  }
}
</script>

<style>
</style>

<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-8">
          <!-- <q-btn color="amber-6" icon="keyboard_backspace" label="Regresar" @click="backToGrid()" /> -->
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Recepciones" to="/raw-material-shipments" />
              <q-breadcrumbs-el label="Nueva Recepción" />
            </q-breadcrumbs>
          </div>
        </div>
      </div>
    </div>

    <div class="q-pa-md bg-grey-3">
      <div class="row bg-white border-panel">
        <div class="col q-pa-md">
          <div class="row q-col-gutter-xs">
            <div class="col-xs-12 col-sm-4">
              <q-select
                color="white"
                bg-color="amber-6"
                filled
                dark
                v-model="rawMaterialShipment.fields.supplier"
                :error="$v.rawMaterialShipment.fields.supplier.$error"
                :options="supplierOptions"
                label="Proveedor"
                :rules="supplierRules"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-building" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-4">
              <q-select
                color="white"
                bg-color="amber-6"
                filled
                dark
                v-model="rawMaterialShipment.fields.branchOffice"
                :error="$v.rawMaterialShipment.fields.branchOffice.$error"
                :options="branchOfficeOptions"
                label="Sucursal"
                :rules="branchOfficeRules"
                @input="branchOfficeChanged"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-store-alt" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-4">
              <q-select
                color="white"
                bg-color="amber-6"
                filled
                dark
                v-model="rawMaterialShipment.fields.storage"
                :error="$v.rawMaterialShipment.fields.storage.$error"
                :options="storageOptions"
                label="Almacén"
                :rules="storageRules"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-warehouse" />
                </template>
              </q-select>
            </div>
          </div>

          <div class="row q-mb-sm">
            <div class="col-xs-12 col-sm-2 offset-sm-10 pull-right">
              <q-btn color="positive" icon="save" label="Guardar" @click="createShipment()" />
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
  name: 'NewRawMaterialShipment',
  validations: {
    rawMaterialShipment: {
      fields: {
        supplier: { required },
        branchOffice: { required },
        storage: { required }
      }
    }
  },
  data () {
    return {
      rawMaterialShipment: {
        fields: {
          supplier: null,
          branchOffice: null,
          storage: null
        }
      },
      supplierOptions: [],
      branchOfficeOptions: [],
      storageOptions: []
    }
  },
  computed: {
    supplierRules (val) {
      return [
        val => (this.$v.rawMaterialShipment.fields.supplier.required) || 'El campo Proveedor es requerido.'
      ]
    },
    branchOfficeRules (val) {
      return [
        val => (this.$v.rawMaterialShipment.fields.branchOffice.required) || 'El campo Sucursal es requerido.'
      ]
    },
    storageRules (val) {
      return [
        val => (this.$v.rawMaterialShipment.fields.storage.required) || 'El campo Almacén es requerido.'
      ]
    }
  },
  /* beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(2)) {
      this.$router.push('/')
    }
  }, */
  beforeRouteEnter (to, from, next) {
    next(vm => {
      const propiedades = vm.$store.getters['users/rol']
      console.log(propiedades)
      if (propiedades === 1 || propiedades === 3 || propiedades === 7 || propiedades === 2 || propiedades === 20 || propiedades === 4 || propiedades === 27 || propiedades === 22) {
        next()
      } else {
        next('/')
      }
    })
  },
  created () {
    this.fetchFromServer()
  },
  methods: {
    fetchFromServer () {
      this.$q.loading.show()
      api.get('/suppliers/options').then(({ data }) => {
        this.supplierOptions = data.options
        api.get('/branch-offices/options').then(({ data }) => {
          this.branchOfficeOptions = data.options
          api.get('/storages/options').then(({ data }) => {
            this.storageOptions = data.options
            this.$q.loading.hide()
          })
        })
      })
    },
    backToGrid () {
      this.$router.push('/raw-material-shipments')
    },
    branchOfficeChanged () {
      this.rawMaterialShipment.fields.storage = null
      if (this.rawMaterialShipment.fields.branchOffice != null && this.rawMaterialShipment.fields.branchOffice.value != null) {
        this.rawMaterialShipment.fields.storage = this.storageOptions.filter(so => Number(this.rawMaterialShipment.fields.branchOffice.value) === Number(so.branchOffice) && Number(so.storageType) === 9)[0]
      }
    },
    createShipment () {
      this.$v.rawMaterialShipment.fields.$reset()
      this.$v.rawMaterialShipment.fields.$touch()
      if (this.$v.rawMaterialShipment.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = []
      params.supplierId = Number({ ...this.rawMaterialShipment.fields.supplier }.value)
      params.branchOfficeId = Number({ ...this.rawMaterialShipment.fields.branchOffice }.value)
      api.post('/raw-material-shipments', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.$router.push(`/raw-material-shipments/${data.shipment.id}`)
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

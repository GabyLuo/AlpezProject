<template>
  <q-page>
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-8">
          <q-btn color="primary" icon="keyboard_backspace" label="Regresar" @click="backToGrid()" />
          <span class="q-ml-md grey-8 fs28 page-title">Nueva venta de fibra</span>
        </div>
        <div class="col-sm-4">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="right">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Salidas de almacén" to="/storage-exits" />
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
            <div class="col-xs-12 col-sm-6">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="invoice.fields.saleDate"
                mask="date"
                label="Fecha de venta"
                :rules="saleDateRules"
              >
                <template v-slot:prepend>
                  <q-icon name="event" />
                </template>
                <q-popup-proxy
                  ref="invoiceFieldsSaleDate"
                  transition-show="scale"
                  transition-hide="scale"
                >
                  <div class="col-sm-12">
                    <q-date
                      v-model="invoice.fields.saleDate"
                      @input="() => $refs.invoiceFieldsSaleDate.hide()"
                      today-btn
                    />
                  </div>
                </q-popup-proxy>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-6 text-center">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="invoice.fields.branchOffice"
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
                v-model="invoice.fields.baleStorage"
                :options="storageOptions"
                label="Almacén de pacas"
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
                v-model="invoice.fields.inBulkStorage"
                :options="storageOptions"
                label="Almacén fibra abierta"
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
                v-model="invoice.fields.laminateStorage"
                :options="storageOptions"
                label="Almacén de laminado"
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
                v-model="invoice.fields.customer"
                :options="filteredCustomerOptions"
                label="Cliente"
                @input="() => {invoice.fields.customerBranchOffice = null}"
                :rules="customerRules"
                @filter="filtrarClientes"
                use-input
                input-debounce="0"
                behavior="menu"
              >
                <template v-slot:no-option> <q-item> <q-item-section class="text-grey"> No results </q-item-section> </q-item> </template>
                <template v-slot:prepend>
                  <q-icon name="fas fa-shopping-cart" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-4 text-center">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="invoice.fields.customerBranchOffice"
                :options="filteredCustomerBranchOfficeOptions"
                label="Sucursal de cliente"
                :rules="customerBranchOfficeRules"
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
                v-model="invoice.fields.driver"
                :options="driverOptions"
                label="Chofer"
                :rules="driverRules"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-truck" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 text-center">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                autogrow
                v-model="invoice.fields.comments"
                label="Comentarios"
                type="textarea"
              />
            </div>
          </div>

          <div class="row q-mb-sm q-mt-md">
            <div class="col-xs-12 col-md-2 offset-md-10 pull-right">
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
const { required } = require('vuelidate/lib/validators')

export default {
  name: 'NewStorageExit',
  validations: {
    invoice: {
      fields: {
        saleDate: { required },
        branchOffice: { required },
        customer: { required },
        customerBranchOffice: { required },
        driver: { required }
      }
    }
  },
  data () {
    return {
      invoice: {
        fields: {
          saleDate: `${new Date().getFullYear()}/${(new Date().getMonth() + 1).toString().padStart(2, '0')}/${(new Date().getDate()).toString().padStart(2, '0')}`,
          branchOffice: null,
          baleStorage: null,
          inBulkStorage: null,
          laminateStorage: null,
          customer: null,
          customerBranchOffice: null,
          driver: null,
          comments: null
        }
      },
      branchOfficeOptions: [],
      storageOptions: [],
      customerOptions: [],
      customerBranchOfficeOptions: [],
      driverOptions: [],
      filteredCustomerOptions: []
    }
  },
  computed: {
    roleId () {
      const user = this.$store.getters['users/rol']
      return parseInt(user)
    },
    saleDateRules (val) {
      return [
        val => (this.$v.invoice.fields.saleDate.required) || 'El campo Fecha de venta es requerido.'
      ]
    },
    branchOfficeRules (val) {
      return [
        val => this.$v.invoice.fields.branchOffice.required || 'El campo Sucursal es requerido.'
      ]
    },
    customerRules (val) {
      return [
        val => (this.$v.invoice.fields.customer.required) || 'El campo Cliente es requerido.'
      ]
    },
    customerBranchOfficeRules (val) {
      return [
        val => (this.$v.invoice.fields.customerBranchOffice.required) || 'El campo Sucursal de cliente es requerido.'
      ]
    },
    driverRules (val) {
      return [
        val => (this.$v.invoice.fields.driver.required) || 'El campo Chofer es requerido.'
      ]
    },
    filteredCustomerBranchOfficeOptions () {
      let options = []
      if (this.invoice.fields.customer != null && this.invoice.fields.customer.value != null) {
        options = this.customerBranchOfficeOptions.filter(cboo => (Number(cboo.customer) === Number(this.invoice.fields.customer.value)))
      }
      return options
    }
  },
  beforeRouteEnter (to, from, next) {
    next(vm => {
      const propiedades = vm.$store.getters['users/rol']
      console.log(propiedades)
      if (propiedades === 1 || propiedades === 3 || propiedades === 7 || propiedades === 2 || propiedades === 20 || propiedades === 4 || propiedades === 27 || propiedades === 17 || propiedades === 22 || propiedades === 28) {
        next()
      } else {
        next('/')
      }
    })
  },
  /* beforeCreate () {
    if (!(this.$store.getters['users/roles'].includes(1) || this.$store.getters['users/roles'].includes(3) || this.$store.getters['users/roles'].includes(7) || this.$store.getters['users/roles'].includes(12))) {
      this.$router.push('/')
    }
  }, */
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
          api.get('/customers/options').then(({ data }) => {
            this.customerOptions = data.options
            api.get('/customer-branch-offices/options').then(({ data }) => {
              this.customerBranchOfficeOptions = data.options
              api.get('/drivers/options').then(({ data }) => {
                this.driverOptions = data.options
                this.$q.loading.hide()
              })
            })
          })
        })
      })
    },
    backToGrid () {
      this.$router.push('/storage-exits')
    },
    updateBranchOffice () {
      if (this.invoice.fields.branchOffice == null || this.invoice.fields.branchOffice.value == null) {
        this.invoice.fields.baleStorage = null
        this.invoice.fields.inBulkStorage = null
        this.invoice.fields.laminateStorage = null
      } else {
        this.invoice.fields.baleStorage = this.storageOptions.filter(so => Number(so.branchOffice) === Number(this.invoice.fields.branchOffice.value) && Number(so.storageType) === 1)[0]
        this.invoice.fields.inBulkStorage = this.storageOptions.filter(so => Number(so.branchOffice) === Number(this.invoice.fields.branchOffice.value) && Number(so.storageType) === 2)[0]
        this.invoice.fields.laminateStorage = this.storageOptions.filter(so => Number(so.branchOffice) === Number(this.invoice.fields.branchOffice.value) && Number(so.storageType) === 8)[0]
      }
    },
    createSale () {
      this.$v.invoice.fields.$reset()
      this.$v.invoice.fields.$touch()
      if (this.$v.invoice.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = []
      params.branch_office_id = Number({ ...this.invoice.fields }.branchOffice.value)
      params.sale_date = { ...this.invoice.fields }.saleDate
      params.customer_branch_office_id = Number({ ...this.invoice.fields }.customerBranchOffice.value)
      params.driver_id = Number({ ...this.invoice.fields }.driver.value)
      params.comments = { ...this.invoice.fields }.comments
      api.post('/invoices', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.$router.push(`/storage-exits/${data.invoice.id}`)
        } else {
          this.$q.loading.hide()
        }
      })
    },
    filtrarClientes (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.filteredCustomerOptions = this.customerOptions.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    }
  }
}
</script>

<style>
</style>

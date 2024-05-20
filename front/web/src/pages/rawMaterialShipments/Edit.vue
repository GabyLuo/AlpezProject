<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-8">
          <q-btn color="primary" icon="keyboard_backspace" label="Regresar" @click="backToGrid()" />
          <span class="q-ml-md grey-8 fs28 page-title">Recepción MP {{ this.$route.params.id }}</span>
        </div>
        <div class="col-sm-4">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="right">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Recepciones" to="/raw-material-shipments" />
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
            <div class="col-xs-12 col-sm-3">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="rawMaterialShipment.fields.supplier"
                :options="supplierOptions"
                label="Proveedor"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-building" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="rawMaterialShipment.fields.branchOffice"
                :options="branchOfficeOptions"
                label="Sucursal"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-store-alt" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-4">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="rawMaterialShipment.fields.storage"
                :options="storageOptions"
                label="Almacén"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-warehouse" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-2">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="rawMaterialShipment.fields.status"
                label="Estatus"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="battery_full" />
                </template>
              </q-input>
            </div>
          </div>

          <div class="row q-mb-sm">
            <div class="col-xs-12 col-sm-2 offset-sm-10 pull-right" v-if="rawMaterialShipment.fields.status == 'NUEVO'">
              <q-btn color="warning" icon="fas fa-bolt" label="Ejecutar" @click="execute()" style="margin-right: 5px;" />
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="bg-white border-panel">
        <q-expansion-item
          label="Detalles"
          expand-separator
          default-opened
          style="font-weight: bold; font-size: large;"
        >
          <div style="font-weight: normal;">
            <div class="row q-col-gutter-xs" style="padding-right: 2%;" v-if="rawMaterialShipment.fields.status == 'NUEVO'">
              <div class="col-xs-12 col-sm-4 offset-sm-8 pull-right">
                <q-btn color="positive" icon="save" label="Actualizar" @click="updateDetail()" style="margin-right: 5px;" v-show="detail.fields.id != null" />
                <q-btn color="negative" icon="cancel" label="Cancelar" @click="cancelEditDetail()" style="margin-right: 5px;" v-show="detail.fields.id != null" />
                <q-btn color="primary" icon="add" label="Agregar" @click="addDetail()" style="margin-right: 5px;" v-show="detail.fields.id == null" />
              </div>
            </div>
            <div class="row q-col-gutter-xs" style="padding: 2%;" v-if="rawMaterialShipment.fields.status == 'NUEVO'">
              <div class="col-xs-12 col-sm-6">
                <q-select
                  color="dark"
                  bg-color="secondary"
                  filled
                  ref="detailFieldsProductRef"
                  v-model="detail.fields.product"
                  :options="rawMaterialProductOptions"
                  label="Producto"
                  :rules="productRules"
                >
                  <template v-slot:prepend>
                    <q-icon name="emoji_objects" />
                  </template>
                </q-select>
              </div>
              <div class="col-xs-12 col-sm-3">
                <q-input
                  color="dark"
                  bg-color="secondary"
                  filled
                  ref="detailFieldsQtyRef"
                  v-model="detail.fields.qty"
                  :error="$v.detail.fields.qty.$error"
                  label="Cantidad"
                  :rules="qtyRules"
                  @input="v => { detail.fields.qty = v.replace(/[^0-9\\.]/g, '') }"
                  min="1"
                  suffix="KG."
                >
                  <template v-slot:prepend>
                    <q-icon name="fas fa-weight-hanging" />
                  </template>
                </q-input>
              </div>
              <div class="col-xs-12 col-sm-3">
                <q-input
                  color="dark"
                  bg-color="secondary"
                  filled
                  ref="detailFieldsQtyRef"
                  v-model="detail.fields.price"
                  :error="$v.detail.fields.price.$error"
                  label="Precio por kilogramo"
                  :rules="priceRules"
                  type="number"
                  min="1"
                >
                  <template v-slot:prepend>
                    <q-icon name="attach_money" />
                  </template>
                </q-input>
              </div>
            </div>
            <div class="q-col-gutter-xs" style="padding: 2%;">
              <q-table
                flat
                bordered
                :data="details"
                :columns="detailsColumns"
                row-key="name"
                :pagination.sync="pagination"
              >
                <template v-slot:body="props">
                  <q-tr :props="props">
                    <q-td key="product" style="text-align: left; width: 45%;" :props="props">{{ props.row.product }}</q-td>
                    <q-td key="qty" style="text-align: right; width: 15%;" :props="props">{{ formatPrice(props.row.qty) }} KG.</q-td>
                    <q-td key="unitPrice" style="text-align: right; width: 15%;" :props="props">{{ `${currencyFormatter.format(props.row.unit_price)}` }}</q-td>
                    <q-td key="totalPrice" style="text-align: right; width: 15%;" :props="props">{{ `${currencyFormatter.format(props.row.total_price)}` }}</q-td>
                    <q-td key="actions" style="width: 10%;" :props="props" v-if="rawMaterialShipment.fields.status == 'NUEVO'">
                      <q-btn color="primary" icon="fas fa-edit" flat @click.native="editDetail(props.row)" size="10px">
                        <q-tooltip content-class="bg-primary">Editar</q-tooltip>
                      </q-btn>
                      <q-btn color="primary" icon="fas fa-trash-alt" flat @click.native="removeDetail(props.row.id)" size="10px">
                        <q-tooltip content-class="bg-red">Eliminar</q-tooltip>
                      </q-btn>
                    </q-td>
                    <q-td key="actions" style="width: 10%;" :props="props" v-else>
                      -
                    </q-td>
                  </q-tr>
                </template>
              </q-table>
            </div>
          </div>
        </q-expansion-item>
      </div>
  </q-page>
</template>

<script>
import api from '../../commons/api.js'
const { required, decimal } = require('vuelidate/lib/validators')

export default {
  name: 'EditRawMaterialShipment',
  validations: {
    detail: {
      fields: {
        product: { required },
        qty: { required, decimal },
        price: { required, decimal }
      }
    }
  },
  data () {
    return {
      formatter: new Intl.NumberFormat('en-US'),
      currencyFormatter: new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
      }),
      rawMaterialShipment: {
        fields: {
          supplier: null,
          branchOffice: null,
          storage: null,
          status: null
        }
      },
      supplierOptions: [],
      branchOfficeOptions: [],
      storageOptions: [],
      detail: {
        fields: {
          id: null,
          product: null,
          qty: null,
          price: null
        }
      },
      detailIdToEdit: null,
      rawMaterialProductOptions: [],
      details: [],
      detailsColumns: [
        { name: 'product', align: 'center', label: 'Producto', field: 'product', style: 'width: 45%', sortable: true },
        { name: 'qty', align: 'center', label: 'Cantidad', field: 'qty', style: 'width: 15%', sortable: true },
        { name: 'unitPrice', align: 'center', label: 'Precio unitario', field: 'unitPrice', style: 'width: 15%', sortable: true },
        { name: 'totalPrice', align: 'center', label: 'Importe', field: 'totalPrice', style: 'width: 15%', sortable: true },
        { name: 'actions', align: 'center', label: 'Acciones', field: 'actions', style: 'width: 10%', sortable: false }
      ],
      pagination: {
        rowsPerPage: 50
      }
    }
  },
  computed: {
    productRules (val) {
      return [
        val => (this.$v.detail.fields.product.required) || 'El campo Producto es requerido.'
      ]
    },
    qtyRules (val) {
      return [
        val => (this.$v.detail.fields.qty.required) || 'El campo Cantidad es requerido.',
        val => (this.$v.detail.fields.qty.decimal) || 'El campo Cantidad debe ser numérico.'
      ]
    },
    priceRules (val) {
      return [
        val => (this.$v.detail.fields.price.required) || 'El campo Precio por kilogramo es requerido.',
        val => (this.$v.detail.fields.price.decimal) || 'El campo Precio por kilogramo debe ser numérico.'
      ]
    }
  },
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
  /* beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(2)) {
      this.$router.push('/')
    }
  }, */
  created () {
    this.fetchFromServer()
  },
  methods: {
    formatPrice (value) {
      const val = (value / 1).toFixed(1).replace('.', ',')
      return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')
    },
    fetchFromServer () {
      api.get(`/raw-material-shipments/${this.$route.params.id}`).then(({ data }) => {
        if (!data.shipment) {
          this.$router.push('/')
        } else {
          this.$q.loading.show()
          this.rawMaterialShipment.fields.supplier = { value: data.shipment.supplier_id, label: data.shipment.supplier }
          this.rawMaterialShipment.fields.branchOffice = { value: data.shipment.branch_office_id, label: data.shipment.branch_office }
          this.rawMaterialShipment.fields.storage = { value: data.shipment.storage_id, label: data.shipment.storage }
          this.rawMaterialShipment.fields.status = data.shipment.status
          api.get('/suppliers/options').then(({ data }) => {
            this.supplierOptions = data.options
            api.get('/branch-offices/options').then(({ data }) => {
              this.branchOfficeOptions = data.options
              api.get('/storages/options').then(({ data }) => {
                this.storageOptions = data.options
                api.get('/products/options/category/14').then(({ data }) => {
                  this.rawMaterialProductOptions = data.options
                  api.get(`/raw-material-shipments-details/raw-material-shipment/${this.$route.params.id}`).then(({ data }) => {
                    this.details = data.details
                    this.$q.loading.hide()
                  })
                })
              })
            })
          })
        }
      })
    },
    backToGrid () {
      this.$router.push('/raw-material-shipments')
    },
    execute () {
      this.$q.loading.show()
      api.put(`/raw-material-shipments/${this.$route.params.id}/execute`, []).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.fetchFromServer()
        } else {
          this.$q.loading.hide()
        }
      })
    },
    updateDetail () {
      this.$v.detail.fields.$reset()
      this.$v.detail.fields.$touch()
      if (this.$v.detail.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = []
      params.id = Number({ ...this.detail.fields }.id)
      params.rawMaterialShipmentId = Number(this.$route.params.id)
      params.productId = Number({ ...this.detail.fields }.product.value)
      params.qty = Number({ ...this.detail.fields }.qty)
      params.unitPrice = Number({ ...this.detail.fields }.price)
      this.detail.fields.id = null
      this.detail.fields.product = null
      this.detail.fields.qty = null
      this.detail.fields.price = null
      api.put(`/raw-material-shipments-details/${params.id}`, params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          api.get(`/raw-material-shipments-details/raw-material-shipment/${this.$route.params.id}`).then(({ data }) => {
            this.details = data.details
            this.$q.loading.hide()
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    cancelEditDetail () {
      this.detail.fields.id = null
      this.detail.fields.product = null
      this.detail.fields.qty = null
      this.detail.fields.price = null
    },
    addDetail () {
      this.$v.detail.fields.$reset()
      this.$v.detail.fields.$touch()
      if (this.$v.detail.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = []
      params.rawMaterialShipmentId = Number(this.$route.params.id)
      params.productId = Number({ ...this.detail.fields }.product.value)
      params.qty = Number({ ...this.detail.fields }.qty)
      params.unitPrice = Number({ ...this.detail.fields }.price)
      this.detail.fields.product = null
      this.detail.fields.qty = null
      this.detail.fields.price = null
      api.post('/raw-material-shipments-details', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          api.get(`/raw-material-shipments-details/raw-material-shipment/${this.$route.params.id}`).then(({ data }) => {
            this.details = data.details
            this.$q.loading.hide()
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    editDetail (detail) {
      this.detail.fields.id = detail.id
      this.detail.fields.product = { value: detail.product_id, label: detail.product }
      this.detail.fields.qty = detail.qty
      this.detail.fields.price = detail.unit_price
    },
    removeDetail (id) {
      this.$q.loading.show()
      api.delete(`/raw-material-shipments-details/${id}`).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          api.get(`/raw-material-shipments-details/raw-material-shipment/${this.$route.params.id}`).then(({ data }) => {
            this.details = data.details
            this.$q.loading.hide()
          })
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

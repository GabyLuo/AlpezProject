<template>
  <q-page>

    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-8">
          <span class="q-ml-md grey-8 fs28 page-title">Pedidos por producir</span>
        </div>
        <div class="col-sm-4 pull-right">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="right">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Pedidos por producir" />
            </q-breadcrumbs>
          </div>
        </div>
      </div>
    </div>
<!--    <q-tabs v-model="currentTab" dense class="text-grey" active-color="primary" indicator-color="primary" align="justify" narrow-indicator @input="changeModel">-->
<!--      <q-tab name="baleOrders" label="Pacas" />-->
<!--      <q-tab name="inBulkOrders" label="Fibra abierta" />-->
<!--      <q-tab name="laminateOrders" label="Laminado" />-->
<!--    </q-tabs>-->

    <div class="q-pa-md bg-grey-3">
      <div class="row bg-white border-panel">
        <div class="col q-pa-md">
          <q-table
            flat
            bordered
            :data="products"
            :columns="columns"
            row-key="category"
            :pagination.sync="pagination"
          >
            <template v-slot:body="props">
              <q-tr :props="props">
                <q-td key="category" :props="props">{{ props.row.category_name }}</q-td>
                <q-td key="line" :props="props">{{ props.row.line_name }}</q-td>
                <q-td key="product" :props="props">{{ props.row.product_name }}</q-td>
                <q-td key="qty" :props="props">{{ props.row.qty }} Kg.</q-td>
                <q-td key="actions" :props="props">
                  <q-btn color="primary" icon="remove_red_eye" flat @click.native="openDetailModal(props.row)" size="10px">
                    <q-tooltip content-class="bg-primary">Ver</q-tooltip>
                  </q-btn>
                </q-td>
              </q-tr>
            </template>
          </q-table>
        </div>
      </div>
    </div>

    <q-dialog v-model="detailModal" persistent>
      <q-card style="min-width: 400px;">
        <q-card-section>
          <div class="text-h6">Detalles respecto a producto {{ selectedProduct != null ? `${selectedProduct.category_code}-${selectedProduct.line_code}-${selectedProduct.product_name}` : '' }}</div>
        </q-card-section>
        <q-card-section>
          <q-table
            flat
            bordered
            :data="productDetails"
            :columns="productDetailsColumns"
            row-key="order"
            :pagination.sync="pagination"
          >
            <template v-slot:body="props">
              <q-tr :props="props">
                <q-td key="order" :props="props">{{ props.row.shopping_cart_id }}</q-td>
                <q-td key="qty" :props="props">{{ props.row.qty }} Kg.</q-td>
              </q-tr>
            </template>
          </q-table>
        </q-card-section>
        <q-card-actions align="right" class="text-primary">
          <q-btn flat label="Cancelar" @click="closeDetailModal()" v-close-popup />
          <q-btn flat label="Generar OP" @click="openGenerateProductionOrderModal(selectedProduct)" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <q-dialog v-model="generateProductionOrdenModal" persistent>
      <q-card style="min-width: 400px;">
        <q-card-section>
          <div class="text-h6">Generar orden de producción del producto {{ productionOrder.fields.productName }}</div>
        </q-card-section>
        <q-card-section>
          <div class="col-xs-12">
            <q-select
              color="white"
              bg-color="primary"
              filled
              dark
              v-model="productionOrder.fields.productionDate"
              mask="date"
              label="Fecha programada"
              :rules="creationDateRules"
            >
              <template v-slot:prepend>
                <q-icon name="event" />
              </template>
              <q-popup-proxy
                ref="productionOrderFieldsCreationDateRef"
                transition-show="scale"
                transition-hide="scale"
              >
                <div class="col-sm-12">
                  <q-date
                    v-model="productionOrder.fields.productionDate"
                    @input="() => $refs.productionOrderFieldsCreationDateRef.hide()"
                    today-btn
                  />
                </div>
              </q-popup-proxy>
            </q-select>
          </div>
          <div class="col-xs-12">
            <q-input
              color="white"
              bg-color="primary"
              filled
              dark
              v-model="productionOrder.fields.productName"
              label="Producto"
              disable
            >
              <template v-slot:prepend>
                <q-icon name="fas fa-building" />
              </template>
            </q-input>
          </div>
          <div class="col-xs-12">
            <q-input
              color="white"
              bg-color="primary"
              filled
              dark
              v-model="productionOrder.fields.qty"
              :error="$v.productionOrder.fields.qty.$error"
              label="Peso solicitado"
              :rules="qtyRules"
              @input="v => { productionOrder.fields.qty = v.replace(/[^0-9\\.]/g, '') }"
              suffix="Kg."
              min="1"
            >
              <template v-slot:prepend>
                <q-icon name="fas fa-weight-hanging" />
              </template>
            </q-input>
          </div>
          <div class="col-xs-12">
            <q-select
              color="white"
              bg-color="primary"
              filled
              dark
              v-model="productionOrder.fields.lotsQty"
              :options="lotOptions"
              label="Lotes"
              :rules="lotsQtyRules"
            >
              <template v-slot:prepend>
                <q-icon name="fas fa-box-open" />
              </template>
            </q-select>
          </div>
        </q-card-section>
        <q-card-actions align="right" class="text-primary">
          <q-btn flat label="Cancelar" @click="closeGenerateProductionOrdenModal()" v-close-popup />
          <q-btn flat label="Generar" @click="generateProductionOrder()" />
        </q-card-actions>
      </q-card>
    </q-dialog>

  </q-page>
</template>

<script>
import api from '../../commons/api.js'
const { required, decimal } = require('vuelidate/lib/validators')

export default {
  name: 'IndexOrdersToBeProduced',
  validations: {
    productionOrder: {
      fields: {
        productionDate: { required },
        qty: { required, decimal },
        lotsQty: { required }
      }
    }
  },
  data () {
    return {
      currentTab: 'baleOrders',
      pagination: {
        sortBy: 'category',
        descending: false,
        rowsPerPage: 50
      },
      columns: [
        { name: 'category', align: 'left', label: 'Categoría', field: 'category', sortable: true },
        { name: 'line', align: 'left', label: 'Línea', field: 'line', sortable: true },
        { name: 'product', align: 'left', label: 'Producto', field: 'product', sortable: true },
        { name: 'qty', align: 'right', label: 'Cantidad', field: 'qty', sortable: true },
        { name: 'actions', align: 'center', label: 'Acciones', field: 'actions', sortable: false }
      ],
      productDetailsColumns: [
        { name: 'order', align: 'left', label: '# Pedido', field: 'order', sortable: true },
        { name: 'qty', align: 'right', label: 'Cantidad', field: 'qty', sortable: true }
      ],
      productionOrder: {
        fields: {
          productionDate: null,
          productName: null,
          productId: null,
          qty: null,
          lotsQty: null,
          minQty: null
        }
      },
      lotOptions: [
        { value: 1, label: 1 },
        { value: 2, label: 2 },
        { value: 3, label: 3 },
        { value: 4, label: 4 },
        { value: 5, label: 5 },
        { value: 6, label: 6 },
        { value: 7, label: 7 },
        { value: 8, label: 8 },
        { value: 9, label: 9 },
        { value: 10, label: 10 }
      ],
      products: [],
      baleDetails: [],
      inBulkDetails: [],
      laminateDetails: [],
      productDetails: [],
      selectedProduct: null,
      detailModal: false,
      generateProductionOrdenModal: false
    }
  },
  beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(17)) {
      this.$router.push('/')
    }
  },
  mounted () {
    this.fetchFromServer()
  },
  computed: {
    creationDateRules (val) {
      return [
        val => (this.$v.productionOrder.fields.productionDate.required) || 'El campo Fecha de creación es requerido.'
      ]
    },
    qtyRules (val) {
      return [
        val => (this.$v.productionOrder.fields.qty.required) || 'El campo Cantidad es requerido.',
        val => (this.$v.productionOrder.fields.qty.decimal) || 'El campo Cantidad debe ser numérico.'
      ]
    },
    lotsQtyRules (val) {
      return [
        val => (this.$v.productionOrder.fields.lotsQty.required) || 'El campo Lotes es requerido.'
      ]
    }
  },
  methods: {
    fetchFromServer () {
      this.$q.loading.show()
      api.get('/shopping-cart-bale-details/without-stock').then(({ data }) => {
        this.baleDetails = data.details
        api.get('/shopping-cart-in-bulk-details/without-stock').then(({ data }) => {
          this.inBulkDetails = data.details
          api.get('/shopping-cart-laminate-details/without-stock').then(({ data }) => {
            this.laminateDetails = data.details
            this.products = []
            const productsIds = []
            this.baleDetails.forEach(baleDetail => {
              if (productsIds.includes(Number(baleDetail.product_id))) {
                for (let i = 0; i < this.products.length; i++) {
                  if (Number(baleDetail.product_id) === Number(this.products[i].product_id)) {
                    this.products[i].qty += Number(baleDetail.qty)
                  }
                }
              } else {
                productsIds.push(Number(baleDetail.product_id))
                this.products.push({ category_id: baleDetail.category_id, category_code: baleDetail.category_code, category_name: baleDetail.category_name, line_id: baleDetail.line_id, line_code: baleDetail.line_code, line_name: baleDetail.line_name, product_id: baleDetail.product_id, product_code: baleDetail.product_code, product_name: baleDetail.product_name, qty: Number(baleDetail.qty) })
              }
            })
            this.inBulkDetails.forEach(inBulkDetail => {
              if (productsIds.includes(Number(inBulkDetail.product_id))) {
                for (let i = 0; i < this.products.length; i++) {
                  if (Number(inBulkDetail.product_id) === Number(this.products[i].product_id)) {
                    this.products[i].qty += Number(inBulkDetail.qty)
                  }
                }
              } else {
                productsIds.push(Number(inBulkDetail.product_id))
                this.products.push({ category_id: inBulkDetail.category_id, category_code: inBulkDetail.category_code, category_name: inBulkDetail.category_name, line_id: inBulkDetail.line_id, line_code: inBulkDetail.line_code, line_name: inBulkDetail.line_name, product_id: inBulkDetail.product_id, product_code: inBulkDetail.product_code, product_name: inBulkDetail.product_name, qty: Number(inBulkDetail.qty) })
              }
            })
            this.laminateDetails.forEach(laminateDetail => {
              if (productsIds.includes(Number(laminateDetail.product_id))) {
                for (let i = 0; i < this.products.length; i++) {
                  if (Number(laminateDetail.product_id) === Number(this.products[i].product_id)) {
                    this.products[i].qty += Number(laminateDetail.qty)
                  }
                }
              } else {
                productsIds.push(Number(laminateDetail.product_id))
                this.products.push({ category_id: laminateDetail.category_id, category_code: laminateDetail.category_code, category_name: laminateDetail.category_name, line_id: laminateDetail.line_id, line_code: laminateDetail.line_code, line_name: laminateDetail.line_name, product_id: laminateDetail.product_id, product_code: laminateDetail.product_code, product_name: laminateDetail.product_name, qty: Number(laminateDetail.qty) })
              }
            })
            this.$q.loading.hide()
          })
        })
      })
    },
    openDetailModal (product) {
      this.selectedProduct = product
      switch (Number(product.category_id)) {
        case 5:
          this.productDetails = this.laminateDetails.filter(detail => Number(detail.product_id) === Number(product.product_id))
          break
        case 6:
          this.productDetails = this.baleDetails.filter(detail => Number(detail.product_id) === Number(product.product_id))
          break
        case 13:
          this.productDetails = this.inBulkDetails.filter(detail => Number(detail.product_id) === Number(product.product_id))
          break
        default:
          break
      }
      this.detailModal = true
    },
    closeDetailModal () {
      this.detailModal = false
      this.selectedProduct = null
    },
    openGenerateProductionOrderModal (product) {
      if (Number(product.category_id) === 6) {
        if (this.detailModal) {
          this.detailModal = false
        }
        this.generateProductionOrdenModal = true
        this.productionOrder.fields.productId = Number(product.product_id)
        this.productionOrder.fields.productName = `${product.category_code}-${product.line_code}-${product.product_name}`
        this.productionOrder.fields.qty = Number(product.qty)
        this.productionOrder.fields.minQty = Number(product.qty)
      } else {
        this.$q.notify({
          message: 'El producto seleccionado no es fibra.',
          position: 'top',
          color: 'warning'
        })
      }
    },
    closeGenerateProductionOrdenModal () {
      this.productionOrder.fields.productionDate = null
      this.productionOrder.fields.productName = null
      this.productionOrder.fields.productId = null
      this.productionOrder.fields.qty = null
      this.productionOrder.fields.lotsQty = null
      this.productionOrder.fields.minQty = null
    },
    generateProductionOrder () {
      this.$v.productionOrder.fields.$reset()
      this.$v.productionOrder.fields.$touch()
      if (this.$v.productionOrder.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      if (Number(this.productionOrder.fields.qty) < Number(this.productionOrder.fields.minQty)) {
        this.$q.dialog({
          title: 'Error',
          message: `Por favor, ingrese un peso mayor o igual al mínimo necesario que es de ${this.productionOrder.fields.minQty}.`,
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = []
      params.productionDate = this.productionOrder.fields.productionDate
      params.productId = this.productionOrder.fields.productId
      params.qty = this.productionOrder.fields.qty
      params.lotsQty = this.productionOrder.fields.lotsQty.value
      api.post('/shopping-cart-bale-details/generate-production-order', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.closeGenerateProductionOrdenModal()
          this.$q.loading.hide()
          this.fetchFromServer()
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
// C

<template>
  <q-page>
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-6">
          <span class="q-ml-md grey-8 fs28 page-title">Pedido autorizado {{ $route.params.id }}</span>
        </div>
        <div class="col-sm-6">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="right">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Pedidos autorizados" to="/approved-orders" />
              <q-breadcrumbs-el label="Detalles" />
            </q-breadcrumbs>
          </div>
        </div>
      </div>
    </div>

    <div class="q-pa-md bg-grey-3">
      <div class="row bg-white border-panel">
        <div class="col q-pa-md">
          <div class="row q-col-gutter-xs">
            <div class="col-xs-12 col-md-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="shoppingCart.fields.customer_name"
                label="Cliente"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-shopping-cart" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-md-2">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="shoppingCart.fields.user_name"
                label="Nombre usuario"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-user" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-md-2">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="shoppingCart.fields.status"
                label="Estatus"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-battery-full" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-md-2">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="shoppingCart.fields.price_list"
                label="Precio lista"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-list" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-md-2">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="totalPrice"
                label="Importe total"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-dollar-sign" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-md-1 pull-right">
              <q-btn color="positive" icon="fas fa-shopping-cart" :disabled="canGenerateInvoice" label="Remisionar" @click="openGenerateInvoiceModal()" />
            </div>
            <div class="col-xs-12">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="shoppingCart.fields.comments"
                label="Comentarios"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="chat" />
                </template>
              </q-input>
            </div>
          </div>
            <div class="q-pa-md">
            <q-table
              title="Fibra"
              dense
              hide-bottom
              :data="shoppingCartBaleDetails"
              :columns="baleColumns"
              row-key="product_name"
              v-if="shoppingCartBaleDetails.length > 0"
            >
              <template v-slot:body="props">
                <q-tr :props="props">
                  <q-td key="product_name" :props="props" style="width:20%;">{{ props.row.product_name }}</q-td>
                  <q-td key="line_name" :props="props" style="width:10%;">{{ props.row.line_name }}</q-td>
                  <q-td key="category_name" :props="props" style="width:10%;">{{ props.row.category_name }}</q-td>
                  <q-td key="qty" :props="props" style="width:15%;">{{ `${formatter.format(props.row.qty)} KG.` }}</q-td>
                  <q-td key="unit_price" :props="props" style="width:10%;">{{ `${currencyFormatter.format(props.row.unit_price)} ` }}</q-td>
                  <q-td key="amount" :props="props" style="width:15%;">{{ `${currencyFormatter.format(props.row.amount)} ` }}</q-td>
                  <q-td key="bale" :props="props" v-if="props.row.bale_id && props.row.bale_qty" style="width:15%;">
                    <q-chip square dense color="positive" text-color="white">
                      {{ `PACA ${props.row.bale_id} (${formatter.format(props.row.bale_qty)} KG.)` }}
                    </q-chip>
                  </q-td>
                  <q-td key="bale" :props="props" v-else-if="props.row.bales_ids && props.row.bales_qtys" style="width:15%;">
                    <q-chip square dense color="positive" text-color="white">
                      {{ `PACAS ${props.row.bales_ids} (${formatter.format(props.row.bales_qty)} KG.)` }}
                    </q-chip>
                  </q-td>
                  <q-td key="bale" :props="props" v-else-if="props.row.status == 'SURTIDO'" style="width:15%;">
                    <q-chip square dense color="positive" text-color="white">
                      SURTIDO
                    </q-chip>
                  </q-td>
                  <q-td key="bale" :props="props" style="width:15%;" v-else>
                    <q-chip square dense color="negative" text-color="white">
                      SIN PACA
                    </q-chip>
                  </q-td>
                  <q-td key="status" :props="props" style="width:5%;">
                    <q-chip square dense :color="props.row.status == 'REMISIONADO' ? 'positive' : (props.row.status == 'AUTORIZADO' ? 'warning' : 'primary')" text-color="white">
                      {{ props.row.status }}
                    </q-chip>
                  </q-td>
                </q-tr>
              </template>
            </q-table>
            <br v-if="shoppingCartInBulkDetails.length > 0">
            <q-table
              title="Fibra abierta"
              dense
              hide-bottom
              :data="shoppingCartInBulkDetails"
              :columns="inBulkColumns"
              row-key="product_name"
              v-if="shoppingCartInBulkDetails.length > 0"
            >
              <template v-slot:body="props">
                <q-tr :props="props">
                  <q-td key="product_name" :props="props" style="width:20%;">{{ props.row.product_name }}</q-td>
                  <q-td key="line_name" :props="props" style="width:10%;">{{ props.row.line_name }}</q-td>
                  <q-td key="category_name" :props="props" style="width:10%;">{{ props.row.category_name }}</q-td>
                  <q-td key="qty" :props="props" style="width:15%;">{{ `${formatter.format(props.row.qty)} KG.` }}</q-td>
                  <q-td key="unit_price" :props="props" style="width:10%;">{{ `${currencyFormatter.format(props.row.unit_price)} ` }}</q-td>
                  <q-td key="amount" :props="props" style="width:15%;">{{ `${currencyFormatter.format(props.row.amount)} ` }}</q-td>
                  <q-td key="stock" :props="props" v-if="props.row.stock" style="width:15%;">
                    <q-chip square dense color="positive" text-color="white">
                      STOCK: {{ props.row.stock}} KG
                    </q-chip>
                  </q-td>
                  <q-td key="stock" :props="props" style="width:15%;" v-else>
                    <q-chip square dense color="negative" text-color="white">
                      SIN STOCK SUFICIENTE
                    </q-chip>
                  </q-td>
                  <q-td key="status" :props="props" style="width:5%;">
                    <q-chip square dense :color="props.row.status == 'REMISIONADO' ? 'positive' : (props.row.status == 'AUTORIZADO' ? 'warning' : 'primary')" text-color="white">
                      {{ props.row.status }}
                    </q-chip>
                  </q-td>
                </q-tr>
              </template>
            </q-table>
            <br v-if="shoppingCartLaminateDetails.length > 0">
            <q-table
              title="Laminado"
              dense
              hide-bottom
              :data="shoppingCartLaminateDetails"
              :columns="laminateColumns"
              row-key="product_name"
              v-if="shoppingCartLaminateDetails.length > 0"
            >
              <template v-slot:body="props">
                <q-tr :props="props">
                  <q-td key="product_name" :props="props" style="width:20%;">{{ props.row.product_name }}</q-td>
                  <q-td key="line_name" :props="props" style="width:10%;">{{ props.row.line_name }}</q-td>
                  <q-td key="category_name" :props="props" style="width:10%;">{{ props.row.category_name }}</q-td>
                  <q-td key="qty" :props="props" style="width:15%;">{{ `${formatter.format(props.row.qty)} KG.` }}</q-td>
                  <q-td key="unit_price" :props="props" style="width:10%;">{{ `${currencyFormatter.format(props.row.unit_price)} ` }}</q-td>
                  <q-td key="amount" :props="props" style="width:15%;">{{ `${currencyFormatter.format(props.row.amount)} ` }}</q-td>
                  <q-td key="stock" :props="props" v-if="props.row.stock" style="width:15%;">
                    <q-chip square dense color="positive" text-color="white">
                      STOCK: {{ props.row.stock}} KG
                    </q-chip>
                  </q-td>
                  <q-td key="stock" :props="props" style="width:15%;" v-else>
                    <q-chip square dense color="negative" text-color="white">
                      SIN STOCK SUFICIENTE
                    </q-chip>
                  </q-td>
                  <q-td key="status" :props="props" style="width:5%;">
                    <q-chip square dense :color="props.row.status == 'REMISIONADO' ? 'positive' : (props.row.status == 'AUTORIZADO' ? 'warning' : 'primary')" text-color="white">
                      {{ props.row.status }}
                    </q-chip>
                  </q-td>
                </q-tr>
              </template>
            </q-table>
          </div>
        </div>
      </div>
    </div>

    <q-dialog v-model="generateInvoiceModal" persistent>
      <q-card style="min-width: 400px;">
        <q-card-section>
          <div class="text-h6">Surtir pedido</div>
        </q-card-section>
        <q-card-section>
          <div class="row">
            <div class="col-xs-12">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="saleDate"
                mask="date"
                label="Fecha de venta"
                :rules="saleDateRules"
              >
                <template v-slot:prepend>
                  <q-icon name="event" />
                </template>
                <q-popup-proxy
                  ref="invoiceSaleDate"
                  transition-show="scale"
                  transition-hide="scale"
                >
                  <div class="col-sm-12">
                    <q-date
                      v-model="saleDate"
                      @input="() => $refs.invoiceSaleDate.hide()"
                      today-btn
                    />
                  </div>
                </q-popup-proxy>
              </q-select>
            </div>
            <div class="col-xs-12">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                autogrow
                v-model="shoppingCart.fields.customer_name"
                :rules="customerBranchOfficeRules"
                label="Cliente"
                disable
              />
            </div>
            <div class="col-xs-12">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="customerBranchOffice"
                :options="customerBranchOfficeOptions"
                label="Sucursal de cliente"
                :rules="customerBranchOfficeRules"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-store-alt" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="driver"
                :options="driverOptions"
                label="Chofer"
                :rules="driverRules"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-truck" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                autogrow
                v-model="comments"
                label="Comentarios"
                type="textarea"
                @input="v => { comments = v.toUpperCase() }"
              />
            </div>
          </div>
        </q-card-section>
        <q-card-actions align="right" class="text-primary">
          <q-btn flat label="Cancelar" @click="closeGenerateInvoiceModal()" v-close-popup />
          <q-btn flat label="Generar" @click="generateInvoice()" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import api from '../../commons/api.js'
const { required } = require('vuelidate/lib/validators')

export default {
  name: 'EditRequestedOrder',
  validations: {
    saleDate: { required },
    customerBranchOffice: { required },
    driver: { required }
  },
  data () {
    return {
      shoppingCart: {
        fields: {
          id: null,
          customer_id: null,
          customer_name: null,
          price_list: null,
          user_id: null,
          user_name: null,
          user_email: null,
          status: null,
          comments: null
        }
      },
      serverUrl: process.env.API,
      formatter: new Intl.NumberFormat('en-US'),
      currencyFormatter: new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
      }),
      baleColumns: [
        { name: 'product_name', align: 'left', label: 'Producto', field: 'product_name', sortable: true },
        { name: 'line_name', align: 'left', label: 'Línea', field: 'line_name', sortable: true },
        { name: 'category_name', align: 'left', label: 'Categoría', field: 'category_name', sortable: true },
        { name: 'qty', align: 'right', label: 'Cantidad', field: 'qty', sortable: true },
        { name: 'unit_price', align: 'right', label: 'Precio kg.', field: 'unit_price', sortable: true },
        { name: 'amount', align: 'right', label: 'Importe', field: 'amount', sortable: true },
        { name: 'bale', align: 'center', label: 'Paca', field: 'bale', sortable: false },
        { name: 'status', align: 'center', label: 'Estatus', field: 'status', sortable: false }
      ],
      inBulkColumns: [
        { name: 'product_name', align: 'left', label: 'Producto', field: 'product_name', sortable: true },
        { name: 'line_name', align: 'left', label: 'Línea', field: 'line_name', sortable: true },
        { name: 'category_name', align: 'left', label: 'Categoría', field: 'category_name', sortable: true },
        { name: 'qty', align: 'right', label: 'Cantidad', field: 'qty', sortable: true },
        { name: 'unit_price', align: 'right', label: 'Precio kg.', field: 'unit_price', sortable: true },
        { name: 'amount', align: 'right', label: 'Importe', field: 'amount', sortable: true },
        { name: 'stock', align: 'center', label: 'Stock', field: 'stock', sortable: true },
        { name: 'status', align: 'center', label: 'Estatus', field: 'status', sortable: false }
      ],
      laminateColumns: [
        { name: 'product_name', align: 'left', label: 'Producto', field: 'product_name', sortable: true },
        { name: 'line_name', align: 'left', label: 'Línea', field: 'line_name', sortable: true },
        { name: 'category_name', align: 'left', label: 'Categoría', field: 'category_name', sortable: true },
        { name: 'qty', align: 'right', label: 'Cantidad', field: 'qty', sortable: true },
        { name: 'unit_price', align: 'right', label: 'Precio kg.', field: 'unit_price', sortable: true },
        { name: 'amount', align: 'right', label: 'Importe', field: 'amount', sortable: true },
        { name: 'stock', align: 'center', label: 'Stock', field: 'stock', sortable: true },
        { name: 'status', align: 'center', label: 'Estatus', field: 'status', sortable: false }
      ],
      shoppingCartBaleDetails: [],
      shoppingCartInBulkDetails: [],
      shoppingCartLaminateDetails: [],
      customerBranchOfficeOptions: [],
      driverOptions: [],
      generateInvoiceModal: false,
      saleDate: null,
      customerBranchOffice: null,
      driver: null,
      comments: null
    }
  },
  beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(2)) {
      this.$router.push('/')
    }
  },
  created () {
    this.fetchFromServer()
  },
  computed: {
    totalPrice () {
      let price = 0
      this.shoppingCartBaleDetails.forEach(detail => {
        price += Number(detail.amount)
      })
      this.shoppingCartInBulkDetails.forEach(detail => {
        price += Number(detail.amount)
      })
      this.shoppingCartLaminateDetails.forEach(detail => {
        price += Number(detail.amount)
      })
      return this.currencyFormatter.format(price)
    },
    canGenerateInvoice () {
      let canGenerate = false
      const baleDetailsWithStock = this.shoppingCartBaleDetails.filter(detail => ((detail.bale_id != null && detail.bale_qty != null) || (detail.bales_ids != null && detail.bales_ids.length > 0 && detail.bales_qtys != null && detail.bales_qtys.length > 0 && detail.bales_qty != null)) && detail.status === 'REMISIONADO')
      const inBulkDetailsWithStock = this.shoppingCartInBulkDetails.filter(detail => detail.stock && detail.status === 'REMISIONADO')
      const laminateDetailsWithStock = this.shoppingCartLaminateDetails.filter(detail => detail.stock && detail.status === 'REMISIONADO')
      // let laminateDetailsWithStock = this.shoppingCartLaminateDetails.filter(detail => detail.stock && detail.status === 'AUTORIZADO')
      // let inBulkDetailsWithStock = this.shoppingCartInBulkDetails.filter(detail => detail.stock && detail.status === 'AUTORIZADO')

      if (baleDetailsWithStock.length > 0 && inBulkDetailsWithStock.length > 0 && laminateDetailsWithStock.length > 0) {
        canGenerate = true
      }
      return canGenerate
    },
    saleDateRules (val) {
      return [
        val => (this.$v.saleDate.required) || 'El campo Fecha de venta es requerido.'
      ]
    },
    customerBranchOfficeRules (val) {
      return [
        val => (this.$v.customerBranchOffice.required) || 'El campo Sucursal de cliente es requerido.'
      ]
    },
    driverRules (val) {
      return [
        val => (this.$v.driver.required) || 'El campo Chofer es requerido.'
      ]
    }
  },
  methods: {
    fetchFromServer () {
      this.$q.loading.show()
      const id = this.$route.params.id
      api.get(`/shopping-carts/${id}`).then(({ data }) => {
        if (!data.shoppingCart) {
          this.$router.push('/approved-orders')
        } else {
          this.shoppingCart.fields = data.shoppingCart
          api.get(`/shopping-cart-bale-details/shopping-cart/${id}`).then(({ data }) => {
            this.shoppingCartBaleDetails = data.details
            api.get(`/shopping-cart-in-bulk-details/shopping-cart/${id}`).then(({ data }) => {
              this.shoppingCartInBulkDetails = data.details
              api.get(`/shopping-cart-laminate-details/shopping-cart/${id}`).then(({ data }) => {
                this.shoppingCartLaminateDetails = data.details
                api.get('/customer-branch-offices/options').then(({ data }) => {
                  this.customerBranchOfficeOptions = data.options.filter(option => (Number(option.customer) === Number(this.shoppingCart.fields.customer_id)))
                  api.get('/drivers/options').then(({ data }) => {
                    this.driverOptions = data.options
                    this.$q.loading.hide()
                  })
                })
              })
            })
          })
        }
      })
    },
    openGenerateInvoiceModal () {
      this.generateInvoiceModal = true
    },
    closeGenerateInvoiceModal () {
      this.generateInvoiceModal = false
      this.saleDate = null
      this.customerBranchOffice = null
      this.driver = null
      this.comments = null
    },
    generateInvoice () {
      this.$v.saleDate.$reset()
      this.$v.saleDate.$touch()
      this.$v.customerBranchOffice.$reset()
      this.$v.customerBranchOffice.$touch()
      this.$v.driver.$reset()
      this.$v.driver.$touch()
      if (this.$v.saleDate.$error || this.$v.customerBranchOffice.$error || this.$v.driver.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = []
      params.saleDate = this.saleDate
      params.customerBranchOfficeId = this.customerBranchOffice.value
      params.driverId = this.driver.value
      params.comments = this.comments
      api.put(`/shopping-carts/${this.$route.params.id}/generate-invoice`, params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.closeGenerateInvoiceModal()
          this.$q.loading.hide()
          this.fetchFromServer()
        } else {
          this.closeGenerateInvoiceModal()
          this.$q.loading.hide()
        }
      })
    }
  }
}
</script>

<style>
</style>

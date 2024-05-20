<template>
  <q-page>
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-8">
          <span class="q-ml-md grey-8 fs28 page-title">Detalles del pedido</span>
        </div>
        <div class="col-sm-4 pull-right">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="right">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Detalles del pedido" />
            </q-breadcrumbs>
          </div>
        </div>
      </div>
    </div>

    <div class="q-pa-md bg-grey-3">
      <div class="row bg-white border-panel">
        <div class="col q-pa-md">
          <div class="row">
            <div class="col-xs-12 pull-right">
              <q-btn color="primary" icon="check" label="Solicitar" @click="request" style="margin-right: 15px;" />
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
                  <q-td key="product_name" :props="props">{{ props.row.product_name }}</q-td>
                  <q-td key="line_name" :props="props">{{ props.row.line_name }}</q-td>
                  <q-td key="category_name" :props="props">{{ props.row.category_name }}</q-td>
                  <q-td key="qty" :props="props">{{ `${formatter.format(props.row.qty)} KG.` }}</q-td>
                  <q-td key="unit_price" :props="props">{{ `${currencyFormatter.format(props.row.unit_price)} ` }}</q-td>
                  <q-td key="amount" :props="props">{{ `${currencyFormatter.format(props.row.amount)}` }}</q-td>
                  <q-td key="product_photo" :props="props">
                    <q-img :src="serverUrl + 'assets/images/products/' + props.row.product_photo" style="height: 100px; width: 100px;" spinner-color="white" :ratio="1">
                      <template v-slot:error>
                        <div class="absolute-full flex flex-center bg-gray text-white">
                          SIN IMAGEN
                        </div>
                      </template>
                    </q-img>
                  </q-td>
                  <q-td key="actions" :props="props">
                    <q-btn color="negative" icon="fas fa-trash-alt" flat @click.native="removeBaleDetail(props.row.id)" size="10px">
                      <q-tooltip content-class="bg-negative">Eliminar</q-tooltip>
                    </q-btn>
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
                  <q-td key="product_name" :props="props">{{ props.row.product_name }}</q-td>
                  <q-td key="line_name" :props="props">{{ props.row.line_name }}</q-td>
                  <q-td key="category_name" :props="props">{{ props.row.category_name }}</q-td>
                  <q-td key="qty" :props="props">{{ `${formatter.format(props.row.qty)} KG.` }}</q-td>
                  <q-td key="unit_price" :props="props">{{ `${currencyFormatter.format(props.row.unit_price)} ` }}</q-td>
                  <q-td key="amount" :props="props">{{ `${currencyFormatter.format(props.row.amount)} ` }}</q-td>
                  <q-td key="product_photo" :props="props">
                    <q-img :src="serverUrl + 'assets/images/products/' + props.row.product_photo" style="height: 100px; width: 100px;" spinner-color="white" :ratio="1">
                      <template v-slot:error>
                        <div class="absolute-full flex flex-center bg-gray text-white">
                          SIN IMAGEN
                        </div>
                      </template>
                    </q-img>
                  </q-td>
                  <q-td key="actions" :props="props">
                    <q-btn color="negative" icon="fas fa-trash-alt" flat @click.native="removeInBulkDetail(props.row.id)" size="10px">
                      <q-tooltip content-class="bg-negative">Eliminar</q-tooltip>
                    </q-btn>
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
                  <q-td key="product_name" :props="props">{{ props.row.product_name }}</q-td>
                  <q-td key="line_name" :props="props">{{ props.row.line_name }}</q-td>
                  <q-td key="category_name" :props="props">{{ props.row.category_name }}</q-td>
                  <q-td key="qty" :props="props">{{ `${formatter.format(props.row.qty)} KG.` }}</q-td>
                  <q-td key="unit_price" :props="props">{{ `${currencyFormatter.format(props.row.unit_price)}` }}</q-td>
                  <q-td key="amount" :props="props">{{ `${currencyFormatter.format(props.row.amount)}` }}</q-td>
                  <q-td key="product_photo" :props="props">
                    <q-img :src="serverUrl + 'assets/images/products/' + props.row.product_photo" style="height: 100px; width: 100px;" spinner-color="white" :ratio="1">
                      <template v-slot:error>
                        <div class="absolute-full flex flex-center bg-gray text-white">
                          SIN IMAGEN
                        </div>
                      </template>
                    </q-img>
                  </q-td>
                  <q-td key="actions" :props="props">
                    <q-btn color="negative" icon="fas fa-trash-alt" flat @click.native="removeLaminateDetail(props.row.id)" size="10px">
                      <q-tooltip content-class="bg-negative">Eliminar</q-tooltip>
                    </q-btn>
                  </q-td>
                </q-tr>
              </template>
            </q-table>
          </div>
          <div class="q-pa-md pull-right">
            <h4>IMPORTE TOTAL: {{ `${currencyFormatter.format(totalPrice)} MXN` }}</h4>
          </div>
        </div>
      </div>
    </div>

  </q-page>
</template>

<script>
import api from '../commons/api.js'

export default {
  name: 'ShoppingCart',
  data () {
    return {
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
        { name: 'product_photo', align: 'center', label: 'Foto', field: 'product_photo', sortable: false },
        { name: 'actions', align: 'center', label: 'Acciones', field: 'actions', sortable: false }
      ],
      inBulkColumns: [
        { name: 'product_name', align: 'left', label: 'Producto', field: 'product_name', sortable: true },
        { name: 'line_name', align: 'left', label: 'Línea', field: 'line_name', sortable: true },
        { name: 'category_name', align: 'left', label: 'Categoría', field: 'category_name', sortable: true },
        { name: 'qty', align: 'right', label: 'Cantidad', field: 'qty', sortable: true },
        { name: 'unit_price', align: 'right', label: 'Precio kg.', field: 'unit_price', sortable: true },
        { name: 'amount', align: 'right', label: 'Importe', field: 'amount', sortable: true },
        { name: 'product_photo', align: 'center', label: 'Foto', field: 'product_photo', sortable: false },
        { name: 'actions', align: 'center', label: 'Acciones', field: 'actions', sortable: false }
      ],
      laminateColumns: [
        { name: 'product_name', align: 'left', label: 'Producto', field: 'product_name', sortable: true },
        { name: 'line_name', align: 'left', label: 'Línea', field: 'line_name', sortable: true },
        { name: 'category_name', align: 'left', label: 'Categoría', field: 'category_name', sortable: true },
        { name: 'qty', align: 'right', label: 'Cantidad', field: 'qty', sortable: true },
        { name: 'unit_price', align: 'right', label: 'Precio kg.', field: 'unit_price', sortable: true },
        { name: 'amount', align: 'right', label: 'Importe', field: 'amount', sortable: true },
        { name: 'product_photo', align: 'center', label: 'Foto', field: 'product_photo', sortable: false },
        { name: 'actions', align: 'center', label: 'Acciones', field: 'actions', sortable: false }
      ],
      shoppingCartBaleDetails: [],
      shoppingCartInBulkDetails: [],
      shoppingCartLaminateDetails: []
    }
  },
  mounted () {
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
      return price
    }
  },
  methods: {
    fetchFromServer () {
      this.$q.loading.show()
      api.get('/shopping-cart-bale-details').then(({ data }) => {
        this.shoppingCartBaleDetails = data.details
        api.get('/shopping-cart-in-bulk-details').then(({ data }) => {
          this.shoppingCartInBulkDetails = data.details
          api.get('/shopping-cart-laminate-details').then(({ data }) => {
            this.shoppingCartLaminateDetails = data.details
            this.$q.loading.hide()
          })
        })
      })
    },
    request () {
      this.$q.loading.show()
      api.put('/shopping-carts/request').then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$router.push('/')
          this.$q.loading.hide()
        } else {
          this.$q.loading.hide()
        }
      })
    },
    removeBaleDetail (id) {
      this.$q.loading.show()
      api.delete(`/shopping-cart-bale-details/${id}`).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          api.get('/shopping-cart-bale-details').then(({ data }) => {
            this.shoppingCartBaleDetails = data.details
            this.$q.loading.hide()
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    removeInBulkDetail (id) {
      this.$q.loading.show()
      api.delete(`/shopping-cart-in-bulk-details/${id}`).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          api.get('/shopping-cart-in-bulk-details').then(({ data }) => {
            this.shoppingCartInBulkDetails = data.details
            this.$q.loading.hide()
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    removeLaminateDetail (id) {
      this.$q.loading.show()
      api.delete(`/shopping-cart-laminate-details/${id}`).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          api.get('/shopping-cart-laminate-details').then(({ data }) => {
            this.shoppingCartLaminateDetails = data.details
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

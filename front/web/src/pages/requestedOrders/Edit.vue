<template>
  <q-page>
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-9">
          <span class="q-ml-md grey-8 fs28 page-title">Pedido solicitado #{{ $route.params.id }}</span>
        </div>
        <div class="col-sm-3">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="right">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Pedidos solicitados" to="/requested-orders" />
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
            <div class="col-xs-12 col-sm-3">
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
            <div class="col-xs-12 col-sm-2">
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
            <div class="col-xs-12 col-sm-2">
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
            <div class="col-xs-12 col-sm-2">
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
            <div class="col-xs-12 col-sm-2">
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
            <div class="col-xs-12 col-sm-1">
              <q-btn color="positive" icon="check" label="Aprobar" @click="approve()" />
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
          </div><div class="q-pa-md">
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
                  <q-td key="line_name" :props="props" style="width:15%;">{{ props.row.line_name }}</q-td>
                  <q-td key="category_name" :props="props" style="width:10%;">{{ props.row.category_name }}</q-td>
                  <q-td key="qty" :props="props" style="width:20%;">{{ `${formatter.format(props.row.qty)} KG.` }}</q-td>
                  <q-td key="unit_price" :props="props" style="width:20%;">{{ `${currencyFormatter.format(props.row.unit_price)} ` }}</q-td>
                  <q-td key="amount" :props="props" style="width:15%;">{{ `${currencyFormatter.format(props.row.amount)} ` }}</q-td>
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
                  <q-td key="line_name" :props="props" style="width:15%;">{{ props.row.line_name }}</q-td>
                  <q-td key="category_name" :props="props" style="width:10%;">{{ props.row.category_name }}</q-td>
                  <q-td key="qty" :props="props" style="width:20%;">{{ `${formatter.format(props.row.qty)} KG.` }}</q-td>
                  <q-td key="unit_price" :props="props" style="width:20%;">{{ `${currencyFormatter.format(props.row.unit_price)} ` }}</q-td>
                  <q-td key="amount" :props="props" style="width:15%;">{{ `${currencyFormatter.format(props.row.amount)} ` }}</q-td>
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
                  <q-td key="line_name" :props="props" style="width:15%;">{{ props.row.line_name }}</q-td>
                  <q-td key="category_name" :props="props" style="width:10%;">{{ props.row.category_name }}</q-td>
                  <q-td key="qty" :props="props" style="width:20%;">{{ `${formatter.format(props.row.qty)} KG.` }}</q-td>
                  <q-td key="unit_price" :props="props" style="width:20%;">{{ `${currencyFormatter.format(props.row.unit_price)} ` }}</q-td>
                  <q-td key="amount" :props="props" style="width:15%;">{{ `${currencyFormatter.format(props.row.amount)} ` }}</q-td>
                </q-tr>
              </template>
            </q-table>
          </div>
        </div>
      </div>
    </div>
  </q-page>
</template>

<script>
import api from '../../commons/api.js'

export default {
  name: 'EditRequestedOrder',
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
        { name: 'amount', align: 'right', label: 'Importe', field: 'amount', sortable: true }
      ],
      inBulkColumns: [
        { name: 'product_name', align: 'left', label: 'Producto', field: 'product_name', sortable: true },
        { name: 'line_name', align: 'left', label: 'Línea', field: 'line_name', sortable: true },
        { name: 'category_name', align: 'left', label: 'Categoría', field: 'category_name', sortable: true },
        { name: 'qty', align: 'right', label: 'Cantidad', field: 'qty', sortable: true },
        { name: 'unit_price', align: 'right', label: 'Precio kg.', field: 'unit_price', sortable: true },
        { name: 'amount', align: 'right', label: 'Importe', field: 'amount', sortable: true }
      ],
      laminateColumns: [
        { name: 'product_name', align: 'left', label: 'Producto', field: 'product_name', sortable: true },
        { name: 'line_name', align: 'left', label: 'Línea', field: 'line_name', sortable: true },
        { name: 'category_name', align: 'left', label: 'Categoría', field: 'category_name', sortable: true },
        { name: 'qty', align: 'right', label: 'Cantidad', field: 'qty', sortable: true },
        { name: 'unit_price', align: 'right', label: 'Precio kg.', field: 'unit_price', sortable: true },
        { name: 'amount', align: 'right', label: 'Importe', field: 'amount', sortable: true }
      ],
      shoppingCartBaleDetails: [],
      shoppingCartInBulkDetails: [],
      shoppingCartLaminateDetails: []
    }
  },
  beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(17)) {
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
    }
  },
  methods: {
    fetchFromServer () {
      this.$q.loading.show()
      const id = this.$route.params.id
      api.get(`/shopping-carts/${id}`).then(({ data }) => {
        if (!data.shoppingCart) {
          this.$router.push('/requested-orders')
        } else {
          this.shoppingCart.fields = data.shoppingCart
          api.get(`/shopping-cart-bale-details/shopping-cart/${id}`).then(({ data }) => {
            this.shoppingCartBaleDetails = data.details
            api.get(`/shopping-cart-in-bulk-details/shopping-cart/${id}`).then(({ data }) => {
              this.shoppingCartInBulkDetails = data.details
              api.get(`/shopping-cart-laminate-details/shopping-cart/${id}`).then(({ data }) => {
                this.shoppingCartLaminateDetails = data.details
                this.$q.loading.hide()
              })
            })
          })
        }
      })
    },
    approve () {
      this.$q.loading.show()
      api.put(`/shopping-carts/${this.$route.params.id}/approve`).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$router.push('/requested-orders')
          this.$q.loading.hide()
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

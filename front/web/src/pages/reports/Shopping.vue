<template>
  <q-page class="bg-grey-3">
      <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-6">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Compras proveedor" />
            </q-breadcrumbs>
          </div>
        </div>
        <div class="col-xs-12 col-md-6  pull-right">
          <div class="q-pa-sm q-gutter-sm">
            <q-btn color="green" style="margin-left: 10px;" icon="fas fa-file-excel" @click="generateCSV()">
                <q-tooltip>GENERAR CSV</q-tooltip>
              </q-btn>
            <q-btn color="red" style="margin-left: 10px;" icon="fas fa-file-pdf" @click="generatePDF()">
                <q-tooltip>GENERAR PDF</q-tooltip>
              </q-btn>
            <!-- <q-btn class="bg-primary" style="color: white" icon="add" label="Nuevo" @click.native="$router.push('/compras/new')" /> -->
          </div>
        </div>
      </div>
    </div>
    <div class="q-pa-md bg-grey-3">
        <div class="row bg-white border-panel">
            <div class="col q-pa-md">
               <div class="row bg-white q-pb-md q-col-gutter-xs q-col-gutter-md">
                 <div class="col-sm-2">
          <q-select
            color="dark"
            bg-color="secondary"
            filled
            v-model="saleDatev1"
            mask="dd/mm/Y"
            label="Desde"
          >
            <q-popup-proxy
              ref="date"
              transition-show="scale"
              transition-hide="scale"
            >
              <div class="col-sm-12">
                <q-date
                  v-model="saleDatev1"
                  @input="fetchFromServer()"
                  today-btn
                />
              </div>
            </q-popup-proxy>
          </q-select>
          </div>
          <div class="col-md-2">
          <q-select
            color="dark"
            bg-color="secondary"
            filled
            v-model="saleDatev2"
            mask="date"
            label="Hasta"
          >
            <q-popup-proxy
              ref="date"
              transition-show="scale"
              transition-hide="scale"
            >
              <div class="col-sm-12">
                <q-date
                  v-model="saleDatev2"
                  @input="fetchFromServer()"
                  today-btn
                />
              </div>
            </q-popup-proxy>
          </q-select>
        </div>
        <div class="col-md-2">
          <q-select  color="dark" bg-color="secondary" filled
                    v-model="product"
                    :options="filteredProductOptions"
                    use-input
                    hide-selected
                    fill-input
                    input-debounce="0"
                    @filter="filtrarProduct"
                    @input="fetchFromServer()"
                    label="Producto"
                    emit-value map-options>
          </q-select>
        </div>
        <div class="col-md-2">
          <q-select  color="dark" bg-color="secondary" filled
                    v-model="supplier"
                    :options="filteredSupplierOptions"
                    use-input
                    hide-selected
                    fill-input
                    input-debounce="0"
                    @filter="filtrarSupplier"
                    @input="fetchFromServer()"
                    label="Proveedor"
                    emit-value map-options>
          </q-select>
        </div>
               </div>
                <div class="row q-col-gutter-xs">
                <div class="col-xs-12 col-sm-12">
                    <q-table
                    flat
                    bordered
                    :data="dataShopping"
                    :columns="columns"
                    row-key="serial"
                    :pagination.sync="pagination"
                    :filter="filter"
                    @request="qTableRequest"
                    >
                        <template v-slot:top>
                            <div style="width: 100%;">
                                <q-input dense debounce="300" v-model="filter" placeholder="Buscar" @input="v => { filter = v.toUpperCase() }">
                                <template v-slot:append>
                                    <q-icon name="search" />
                                </template>
                                </q-input>
                            </div>
                        </template>
                        <template v-slot:body="props">
                            <q-tr :props="props">
                              <q-td key="serial" style="text-align: center; width: 10%;" :props="props">{{ props.row.serial }}</q-td>
                                <q-td key="supplier" style="text-align: left; width: 20%;" :props="props">{{ props.row.supplier }}</q-td>
                                <q-td key="product" style="text-align: left; width: 20%;" :props="props">{{ props.row.product }}</q-td>
                                <q-td key="description" style="text-align: left; width: 75%;" :props="props">{{ props.row.description }}</q-td>
                                <q-td key="receive_date" style="text-align: center; width: 10%;" :props="props">{{ props.row.receive_date }}</q-td>
                                <q-td key="qty" style="text-align: right; width: 5%;" :props="props">{{ props.row.qty }}</q-td>
                                <q-td key="price_unit" style="text-align: right; width: 5%;" :props="props">{{ props.row.price_unit }}</q-td>
                                <q-td key="total" style="text-align: right; width: 5%;" :props="props">{{ props.row.total }}</q-td>
                            </q-tr>
                        </template>
                    </q-table>
                    </div>
                        </div>
            </div>
        </div>
    </div>
  </q-page>
</template>
<script>
import api from '../../commons/api.js'
export default {
  name: 'CrmIndex',
  data () {
    return {
      supplier: 'TODOS',
      filteredSupplierOptions: [],
      supplierOptions: [],
      saleDatev2: null,
      saleDatev1: null,
      dataShopping: [],
      product: 'TODOS',
      filteredProductOptions: [],
      productsOptions: [],
      columns: [
        // style: 'width: 10%'
        { name: 'serial', align: 'center', label: 'ORDEN COMPRA', field: 'serial', sortable: false, style: 'width: 10%' },
        { name: 'supplier', align: 'center', label: 'PROVEEDOR', field: 'supplier', sortable: false, style: 'width: 20%' },
        { name: 'product', align: 'center', label: 'PRODUCTO', field: 'product', sortable: false, style: 'width: 20%' },
        { name: 'description', align: 'center', label: 'DESCRIPCIÓN', field: 'description', sortable: false, style: 'width: 25%' },
        { name: 'receive_date', align: 'center', label: 'FECHA ENTRADA', field: 'receive_date', sortable: false, style: 'width: 10%' },
        { name: 'qty', align: 'center', label: 'CANTIDAD', field: 'qty', sortable: false, style: 'width: 5%' },
        { name: 'price_unit', align: 'center', label: 'PRECIO UNITARIO', field: 'price_unit', sortable: false, style: 'width: 5%' },
        { name: 'total', align: 'center', label: 'TOTAL', field: 'total', sortable: false, style: 'width: 5%' }
      ],
      filter: '',
      pagination: {
        page: 1,
        rowsPerPage: 25
      }
    }
  },
  mounted () {
    this.getSuppliers()
    this.getProduct()
    this.fetchFromServer()
  },
  methods: {
    filtrarProduct (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.filteredProductOptions = this.productsOptions.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    async getProduct () {
      await api.get('purchase-order-details/getProducts').then(({ data }) => {
        if (data.result) {
          this.productsOptions = data.products
          this.productsOptions.unshift({ label: 'TODOS', value: 'TODOS' })
        }
      })
    },
    generateCSV () {
      const dataini = this.saleDatev1 !== null ? this.$formatDate(this.saleDatev1) : null
      const datafin = this.saleDatev2 !== null ? this.$formatDate(this.saleDatev2) : null
      const supplier = this.supplier
      const product = this.product
      const uri = process.env.API + `purchase-order-details/getReportShoppingToCSV/${dataini}/${datafin}/${supplier}/${product}`
      window.open(uri, '_blank')
    },
    generatePDF () {
      const dataini = this.saleDatev1 !== null ? this.$formatDate(this.saleDatev1) : null
      const datafin = this.saleDatev2 !== null ? this.$formatDate(this.saleDatev2) : null
      const supplier = this.supplier
      const product = this.product
      const uri = process.env.API + `purchase-order-details/getReportShoppingToPDF/${dataini}/${datafin}/${supplier}/${product}`
      window.open(uri, '_blank')
    },
    async getSuppliers () {
      await api.get('/suppliers/getSuppliersToReportSales').then(({ data }) => {
        if (data.result) {
          this.supplierOptions = data.suppliers
          this.supplierOptions.unshift({ label: 'TODOS', value: 'TODOS' })
        }
      })
    },
    filtrarSupplier (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.filteredSupplierOptions = this.supplierOptions.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    async qTableRequest (props) {
      this.$q.loading.show()
      const params = []
      params.pagination = props.pagination
      params.filter = props.filter
      params.dataini = this.saleDatev1
      params.datafin = this.saleDatev2
      params.supplier = this.supplier
      params.product = this.product
      await api.post('/purchase-order-details/getReportShopping', params).then(({ data }) => {
        if (data.result) {
          this.dataShopping = data.shopping
        }
      })
      this.$q.loading.hide()
    },
    async fetchFromServer () {
      this.qTableRequest({
        pagination: this.pagination,
        filter: this.filter
      })
    },
    editSelectedRow (idFunnel) {
      const id = idFunnel
      this.$router.push(`/compras/${id}`)
    },
    deleteSelectedRow (id) {
      this.$q.dialog({
        title: 'Confirmación',
        message: '¿Desea eliminar este COMPRAS?',
        persistent: true,
        ok: {
          label: 'Aceptar',
          color: 'green'
        },
        cancel: {
          label: 'Cancelar',
          color: 'red'
        }
      }).onOk(async () => {
        this.$q.loading.show()
        await api.delete(`/compras/${id}`).then(({ data }) => {
          this.$q.notify({
            message: data.message.content,
            position: 'top',
            color: (data.result ? 'positive' : 'warning')
          })
          if (data.result) {
            this.fetchFromServer()
            this.$q.loading.hide()
          }
        })
      }).onCancel(() => {})
      this.$q.loading.hide()
    }
  }
}
</script>
<style>
</style>

<template>
  <q-page padding>
    <div class="row q-col-gutter-xs">
          <div class="col-xs-12 pull-right">
            <div>
              <q-btn color="green" style="margin-left: 10px;" icon="fas fa-file-excel" @click="generateCSV()">
                <q-tooltip>GENERAR CSV</q-tooltip>
              </q-btn>
              <q-btn color="red" style="margin-left: 10px;" icon="fas fa-file-pdf" @click="generatePDF()">
                <q-tooltip>GENERAR PDF</q-tooltip>
              </q-btn>
            </div>
          </div>
        </div>
    <div class="row q-col-gutter-xs" style="margin-top: 20px">
      <div class="col-md-3">
       <q-select color="white" bg-color="secondary" filled v-model="clientes.fields.DateOf" mask="date" label="Fecha de inicio">
        <q-popup-proxy ref="date1" transition-show="scale" transition-hide="scale">
          <div class="col-sm-12">
            <q-date v-model="clientes.fields.DateOf" @input="fetchFromServer()" today-btn/>
          </div>
        </q-popup-proxy>
      </q-select>
    </div>
    <div class="col-md-3">
      <q-select color="white" bg-color="secondary" filled v-model="clientes.fields.DateUntil" mask="date" label="Fecha de fin">
        <q-popup-proxy ref="date2" transition-show="scale" transition-hide="scale">
          <div class="col-sm-12">
            <q-date v-model="clientes.fields.DateUntil" @input="fetchFromServer()" today-btn/>
          </div>
        </q-popup-proxy>
      </q-select>
    </div>
    <div class="col-md-3">
      <q-select color="white" bg-color="secondary" filled map-options v-model="clientes.fields.sucursal" label="Estación" :options="branchesList" @input="fetchFromServer()"> </q-select>
    </div>
    <div class="col-md-3">
      <q-select
      filled
    color="dark"
    bg-color="secondary"
    v-model="clientes.fields.marca"
    label="Marca"
    :options="options"
    use-input
    hide-selected
    fill-input
    input-debounce="0"
    @filter="filterMarcas"
    @input="fetchFromServer()"
    map-options>
        <template v-slot:prepend>
          <q-icon name="fas fa-grip-lines-vertical" />
        </template>
        <template v-slot:no-option>
          <q-item>
            <q-item-section class="text-grey">
              No hay Resultados
            </q-item-section>
          </q-item>
        </template>
      </q-select>
    </div>
  <div class="col-md-3">
    <q-select
      filled
    color="dark"
    bg-color="secondary"
    v-model="clientes.fields.linea"
    label="Subcategoría"
    :options="options3"
    use-input
    hide-selected
    fill-input
    input-debounce="0"
    @filter="filterLines"
    @input="fetchFromServer()"
    map-options> </q-select>
  </div>
  <div class="col-md-3">
    <q-select
    filled
    color="dark"
    bg-color="secondary"
    v-model="clientes.fields.product"
    label="Producto"
    :options="options4"
    use-input
    hide-selected
    fill-input
    input-debounce="0"
    @filter="filterProduct"
    @input="fetchFromServer()"
    map-options
    >
    <template v-slot:prepend>
      <q-icon name="fas fa-grip-lines-vertical" />
    </template>
    <template v-slot:no-option>
      <q-item>
        <q-item-section class="text-grey">
          No hay Resultados
        </q-item-section>
      </q-item>
    </template>
  </q-select>
</div>
  <div class="col-md-6">
    <q-select
    filled
    color="dark"
    bg-color="secondary"
    v-model="clientes.fields.cliente"
    label="Cliente"
    :options="options2"
    use-input
    hide-selected
    fill-input
    input-debounce="0"
    @filter="filtrarClientes"
    @input="fetchFromServer()"
    map-options>
      <template v-slot:prepend>
        <q-icon name="fas fa-grip-lines-vertical" />
      </template>
      <template v-slot:no-option>
        <q-item>
          <q-item-section class="text-grey">
            No hay Resultados
          </q-item-section>
        </q-item>
      </template>
    </q-select>
  </div>
</div>
<div class="row bg-white" style="margin-top: 20px">
  <div class="col q-pa-md">
    <q-table
    flat
    bordered
    :data="data"
    :columns="columns"
    row-key="sale_date"
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
        <q-td key="factura" style="text-align: center;" :props="props"><label @click="openActions(props.row.factura)" class="text-primary" style="text-decoration: underline; cursor: pointer;">{{ props.row.factura }}</label></q-td>
        <q-td key="sale_date" :props="props">{{ props.row.sale_date }}</q-td>
        <q-td key="sucursal" :props="props">{{ props.row.sucursal }}</q-td>
        <!-- <q-td key="categoria" :props="props">{{ props.row.categoria }}</q-td>-->
        <q-td key="line" :props="props">{{ props.row.line }}</q-td>
        <q-td key="cliente" :props="props">{{ props.row.cliente }}</q-td>
        <q-td key="producto" :props="props">{{ props.row.producto }}</q-td>
        <q-td key="qty" :props="props">{{ props.row.qty }}</q-td>
        <q-td key="qty_price" :props="props">{{ props.row.qty_price }}</q-td>
        <q-td key="qty_iva" :props="props">{{ props.row.qty_iva }}</q-td>
        <q-td key="total" :props="props">{{ props.row.total }}</q-td>
                <!--<q-td key="actions" style="text-align: left" :props="props">
                </q-td>-->
              </q-tr>
            </template>
          </q-table>
        </div>
      </div>
    </q-page>
  </template>

<script>
import api from '../../../commons/api.js'
// import Vue from 'vue'

export default {
  name: 'clientes',
  clientes: {
    fields: {
      DateOf: { },
      DateUntil: { },
      sucursal: { },
      marca: { },
      categoria: { },
      Linea: { },
      cliente: { },
      product: { }
    }
  },
  /* beforeCreate () {
    if (this.$store.getters['users/user'].role_id !== 1) {
      this.$router.push('/')
    }
  }, */
  beforeRouteEnter (to, from, next) {
    next(vm => {
      const propiedades = vm.$store.getters['users/user']
      if (propiedades === 1 || propiedades === 3 || propiedades === 7 || propiedades === 2 || propiedades === 20 || propiedades === 4 || propiedades === 27 || propiedades === 17 || propiedades === 22 || propiedades === 28 || propiedades === 29 || propiedades === 17) {
        next()
      } else {
        next('/')
      }
    })
  },
  data () {
    return {
      clientes: {
        fields: {
          DateOf: null,
          DateUntil: null,
          sucursal: null,
          marca: null,
          categoria: null,
          linea: null,
          cliente: null,
          product: null
        }
      },
      branchesList: [],
      categoryOptions: [],
      lineOptions: [],
      markOptions: [],
      familyOptions: [],
      unitOptions: [],
      claveProdOptions: [],
      productOptions: [],
      options: this.markOptions,
      options2: this.customerOptions,
      options3: this.lineOptions,
      options4: this.productOptions,
      customerOptions: [],
      filteredCustomerOptions: [],
      pagination: {
        sortBy: 'id',
        descending: true,
        page: 1,
        rowsNumber: 0,
        rowsPerPage: 25
      },
      columns: [
        { name: 'factura', align: 'center', label: 'REMISION', field: 'factura', sortable: true },
        { name: 'sale_date', align: 'center', label: 'VENTA', field: 'sale_date', sortable: true },
        { name: 'sucursal', align: 'center', label: 'ESTACIÓN', field: 'sucursal', sortable: true },
        // { name: 'categoria', align: 'center', label: 'CATEGORIA', field: 'categoria', sortable: true },
        { name: 'line', align: 'left', label: 'SUBCATEGORÍA', field: 'line', sortable: true },
        { name: 'cliente', align: 'left', label: 'CLIENTE', field: 'cliente', sortable: true },
        { name: 'producto', align: 'left', label: 'PRODUCTO', field: 'producto', sortable: true },
        { name: 'qty', align: 'right', label: 'CANTIDAD', field: 'qty', sortable: true },
        { name: 'qty_price', align: 'right', label: 'IMPORTE', field: 'qty_price', sortable: true },
        { name: 'qty_iva', align: 'right', label: 'IVA', field: 'qty_iva', sortable: true },
        { name: 'total', align: 'right', label: 'TOTAL', field: 'total', sortable: true }
        // { name: 'actions', align: 'center', label: 'ACCIONES', field: 'actions', sortable: false }
      ],
      data: [],
      filter: ''
    }
  },
  computed: {},
  created () {
    this.loadExpenses()
    this.getBranchesList()
    this.getMarks()
    this.getCategories()
    this.getLines()
    this.getClients()
    this.getAllProducts()
    const branch = this.$store.getters['users/branch']
    const user = this.$store.getters['users/rol']
    if (user !== 1) {
      if (branch === 9) {
        this.clientes.fields.sucursal = { value: branch, label: 'EMPRESA' }
      }
      if (branch === 12) {
        this.clientes.fields.sucursal = { value: branch, label: 'LOPEZ DE LARA TINAJERO GUILLERMO' }
      }
      if (branch === 13) {
        this.clientes.fields.sucursal = { value: branch, label: 'EMPRESA SA DE CV' }
      }
      if (branch === 14) {
        this.clientes.fields.sucursal = { value: branch, label: 'REBASA RODAMIENTOS Y MANGUERAS - RODAMIENTOS' }
      }
    }
    this.fetchFromServer()
  },
  mounted () {
  },
  methods: {
    fetchFromServer () {
      // this.$q.loading.show()
      this.qTableRequest({
        pagination: this.pagination,
        filter: this.filter
      })
    },
    async qTableRequest (props) {
      this.pagination = props.pagination
      this.filter = props.filter
      const params = []
      params.DateOf = this.clientes.fields.DateOf
      params.DateUntil = this.clientes.fields.DateUntil
      params.sucursal = this.clientes.fields.sucursal
      params.marca = this.clientes.fields.marca
      params.product = this.clientes.fields.product
      params.linea = this.clientes.fields.linea
      params.cliente = this.clientes.fields.cliente
      params.pagination = this.pagination
      params.filter = this.filter
      // console.log(params)
      api.post('/reports/pagbyclients', params).then(({ data }) => {
        // this.$q.loading.hide()
        this.data = data.info
        // console.log(data.invoices)
        this.pagination.rowsNumber = data.infoCount
      }).catch(error => error)
    },
    async loadExpenses () {
    },
    async loadExpensesFilter () {
    },
    filtrarClientes (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.options2 = this.customerOptions.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    filterMarcas (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.options = this.markOptions.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    filterProduct (val, update, abort) {
      const listproducts = []
      this.productOptions.forEach(product => {
        listproducts.push({ label: product.label, value: product.id })
      })
      update(() => {
        const needle = val.toLowerCase()
        this.options4 = listproducts.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    filterLines (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.options3 = this.lineOptions.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    getCategories () {
      api.get('/categories/options').then(({ data }) => {
        this.categoryOptions = data.options
        this.categoryOptions.push({ label: 'TODAS', value: 'TODAS' })
      })
    },
    getLines () {
      api.get('/lines/options').then(({ data }) => {
        this.lineOptions = data.options
        this.lineOptions.push({ label: 'TODAS', value: 'TODAS' })
      })
    },
    getMarks () {
      api.get('/marks/options').then(({ data }) => {
        this.markOptions = data.options
        this.markOptions.push({ label: 'TODAS', value: 'TODAS' })
      })
    },
    getBranchesList () {
      api.get('branch-offices/options').then(data => {
        this.branchesList = data.data.options
        this.branchesList.push({ label: 'TODAS', value: 'TODAS' })
      })
    },
    getClients () {
      api.get('/customers/options').then(({ data }) => {
        this.customerOptions = data.options
        this.customerOptions.push({ label: 'TODOS', value: 'TODOS' })
      })
    },
    getAllProducts () {
      api.get('/products/category2').then(({ data }) => {
        this.productOptions = data.products
        this.productOptions.push({ label: 'TODOS', value: 'TODOS' })
      })
    },
    openActions (id) {
      this.$router.push(`/storage-exits/${id}`)
    },
    generateCSV () {
      const params = []
      if (this.clientes.fields.DateOf !== null) {
        params.DateOf = this.$formatDate(this.clientes.fields.DateOf)
      } else {
        params.DateOf = null
      }
      if (this.clientes.fields.DateUntil) {
        params.DateUntil = this.$formatDate(this.clientes.fields.DateUntil)
      } else {
        params.DateUntil = null
      }
      if (this.clientes.fields.sucursal != null) {
        params.sucursal = this.clientes.fields.sucursal.value
      } else {
        params.sucursal = null
      }
      if (this.clientes.fields.narca != null) {
        params.marca = this.clientes.fields.marca.value
      } else {
        params.marca = null
      }
      if (this.clientes.fields.product != null) {
        params.product = this.clientes.fields.product.value
      } else {
        params.product = null
      }
      if (this.clientes.fields.linea != null) {
        params.linea = this.clientes.fields.linea.value
      } else {
        params.linea = null
      }
      if (this.clientes.fields.cliente != null) {
        params.cliente = this.clientes.fields.cliente.value
      } else {
        params.cliente = null
      }
      // console.log(params.cliente)
      const uri = process.env.API + `reports/getCsvpagbyclients/${params.DateOf}/${params.DateUntil}/${params.sucursal}/${params.marca}/${params.product}/${params.linea}/${params.cliente}`
      // window.open(uri, '_blank')
      this.$q.loading.show()
      api.fileDownload(uri).then(({ data }) => {
        const url = window.URL.createObjectURL(new Blob([data], { type: 'application/csv' }))
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', 'ReporteClientes.csv')
        document.body.appendChild(link)
        this.$q.loading.hide()
        link.click()
      })
    },
    generatePDF () {
      const params = []
      if (this.clientes.fields.DateOf !== null) {
        params.DateOf = this.$formatDate(this.clientes.fields.DateOf)
      } else {
        params.DateOf = null
      }
      if (this.clientes.fields.DateUntil) {
        params.DateUntil = this.$formatDate(this.clientes.fields.DateUntil)
      } else {
        params.DateUntil = null
      }
      if (this.clientes.fields.sucursal != null) {
        params.sucursal = this.clientes.fields.sucursal.value
      } else {
        params.sucursal = null
      }
      if (this.clientes.fields.narca != null) {
        params.marca = this.clientes.fields.marca.value
      } else {
        params.marca = null
      }
      if (this.clientes.fields.product != null) {
        params.product = this.clientes.fields.product.value
      } else {
        params.product = null
      }
      if (this.clientes.fields.linea != null) {
        params.linea = this.clientes.fields.linea.value
      } else {
        params.linea = null
      }
      if (this.clientes.fields.cliente != null) {
        params.cliente = this.clientes.fields.cliente.value
      } else {
        params.cliente = null
      }
      // console.log(params.cliente)
      const uri = process.env.API + `reports/getPdfpagbyclients/${params.DateOf}/${params.DateUntil}/${params.sucursal}/${params.marca}/${params.product}/${params.linea}/${params.cliente}`
      // window.open(uri, '_blank')
      this.$q.loading.show()
      api.fileDownload(uri).then(({ data }) => {
        const url = window.URL.createObjectURL(new Blob([data], { type: 'application/pdf' }))
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', 'ReporteClientes.pdf')
        document.body.appendChild(link)
        this.$q.loading.hide()
        link.click()
      })
    }
  }
}

</script>

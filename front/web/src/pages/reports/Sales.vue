<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row q-col-gutter-xs q-col-gutter-md">
        <div class="col-sm-4" style="font-size: 20px">
          <div class="q-pa-md q-gutter-sm">
              <q-breadcrumbs>
                  <q-breadcrumbs-el label="" icon="home" to="/"/>
                  <q-breadcrumbs-el label="Reporte de Ventas"/>
              </q-breadcrumbs>
          </div>
        </div>
        <div class="col-sm-8">
          <div class="col-xs-12 col-md-4 offset-md-10 pull-right">
            <div >
<!--              <q-btn color="purple" icon="mail" @click.native="sendMail()">-->
<!--                <q-tooltip>ENVIAR CORREO</q-tooltip>-->
<!--              </q-btn>-->
              <q-btn v-if="roleId === 1 || roleId === 3 || roleId === 7 || roleId === 2 || roleId === 17 || roleId === 20" color="green" label="Generar CSV" style="margin-left: 10px;" icon="fas fa-file-excel" @click="generateCSV()">
              </q-btn>
              <q-btn v-if="roleId === 1 || roleId === 3 || roleId === 7 || roleId === 2 || roleId === 17 || roleId === 20" color="red" label="Generar PDF" style="margin-left: 10px;" icon="fas fa-file-pdf" @click="generatePDF()">
              </q-btn>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="q-pa-md bg-grey-3">
      <div class="row bg-white border-panel">
        <div class="col q-pa-md">
          <div class="row q-col-gutter-xs q-col-gutter-md">
            <div class="col-sm-2"></div>
            <div class="col-md-2">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="saleDatev1"
                mask="date"
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
                      @input="filterGrid()"
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
                      @input="filterGrid()"
                      today-btn
                    />
                  </div>
                </q-popup-proxy>
              </q-select>
            </div>
            <div class="col-sm-2">
              <q-select  color="dark" bg-color="secondary" filled
                        v-model="branchG"
                        :options="branchOptionsG"
                        emit-value map-options
                        label="Sucursal"
                        @input="filterGrid()"
              >
              </q-select>
            </div>
            <div class="col-sm-2">
              <q-select  color="dark" bg-color="secondary" filled
                        v-model="customerG"
                        :options="filteredCustomerOptions2"
                        use-input
                        hide-selected
                        fill-input
                        input-debounce="0"
                        @filter="filtrarClientes2"
                        @input="filterGrid()"
                        label="Cliente"
                        emit-value map-options>
              </q-select>
            </div>
            <div class="col-sm-2">
              <q-select  color="dark" bg-color="secondary" filled
                        v-model="sellerG"
                        :options="sellerOptionsG"
                        emit-value map-options
                        label="Vendedor"
                        @input="filterGrid()"
              >
              </q-select>
            </div>
          </div>
          <div style="padding-top: 20px;">
            <q-table
              flat
              bordered
              :data="data"
              :columns="columns"
              row-key="code"
              :pagination.sync="pagination"
              :filter="filter"
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
                  <q-td key="date" :props="props" style="width: 10%;">{{ props.row.date }}</q-td>
                  <q-td key="invoice" :props="props" style="width: 10%;">{{ props.row.invoice }}</q-td>
                  <q-td key="branchofficeorigin" :props="props" style="width: 10%;">{{ props.row.branchofficeorigin }}</q-td>
                  <q-td key="customer" :props="props"  style="width: 20%;">{{ props.row.customer_name }}</q-td>
                  <q-td key="product" :props="props"  style="width: 20%;">{{ props.row.product }}</q-td>
                  <q-td key="user_name" :props="props" style="width: 10%;">{{ props.row.user_name }}</q-td>
                  <q-td key="total" :props="props" style="width: 10%; text-align: right;">{{ `${currencyFormatter.format( Number(props.row.total))}` }}</q-td>
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
const { required } = require('vuelidate/lib/validators')

export default {
  name: 'IndexShoppingCarts',
  validations: {
    customer: { required },
    branch: { required },
    seller: { required },
    clientofficeDestiny: { required }
  },
  data () {
    return {
      currencyFormatter: new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
      }),
      formatter: new Intl.NumberFormat('en-US'),
      role: null,
      modalPedido: false,
      modalClient: false,
      customer: null,
      saleDatev1: null,
      saleDatev2: null,
      clientofficeDestiny: null,
      customerG: null,
      seller: null,
      sellerG: 'TODOS',
      branch: null,
      branchG: 'TODOS',
      status: 'TODOS',
      customerOptions: [],
      customerOptionsG: [],
      officeClientsOptions: [],
      sellerOptions: [],
      sellerOptionsG: [],
      branchOffices: [],
      branchOptions: [],
      branchOptionsG: [],
      filteredCustomerOptions: [],
      filteredCustomerOptions2: [],
      dataClient: {
        fields: {
          id: null,
          client: null,
          address: null,
          phone: null,
          term: null
        }
      },
      pagination: {
        sortBy: 'id',
        descending: true,
        rowsPerPage: 25
      },
      columns: [
        { name: 'date', align: 'center', label: 'FECHA', field: 'date', style: 'width: 10%', sortable: true },
        { name: 'invoice', align: 'center', label: 'N. REMISIÓN', field: 'invoice', style: 'width: 10%', sortable: true },
        { name: 'branchofficeorigin', align: 'center', label: 'SUCURSAL DE VENTA', field: 'branchofficeorigin', style: 'width: 10%', sortable: true },
        { name: 'customer', align: 'left', label: 'CLIENTE', field: 'customer', style: 'width: 20%', sortable: true },
        { name: 'product', align: 'left', label: 'PRODUCTO', field: 'product', style: 'width: 20%', sortable: true },
        { name: 'user_name', align: 'left', label: 'VENDEDOR', field: 'user_name', style: 'width: 10%', sortable: true },
        { name: 'total', align: 'center', label: 'MONTO (NETO)', field: 'total', style: 'width: 10%', sortable: true }
      ],
      data: [],
      filter: ''
    }
  },
  computed: {
    roleId () {
      const user = this.$store.getters['users/rol']
      return parseInt(user)
    },
    haspermissionv1 () {
      let permission = false
      if (this.$store.getters['users/roles'].includes(1) || this.$store.getters['users/roles'].includes(3) || this.$store.getters['users/roles'].includes(7) || this.$store.getters['users/roles'].includes(12) || this.$store.getters['users/roles'].includes(17)) {
        permission = true
      }
      return permission
    },
    haspermissionv2 () {
      let permission = false
      if (this.$store.getters['users/roles'].includes(1) || this.$store.getters['users/roles'].includes(3) || this.$store.getters['users/roles'].includes(20) || this.$store.getters['users/roles'].includes(12)) {
        permission = true
      }
      return permission
    }
  },
  /* beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(2) && !this.$store.getters['users/roles'].includes(12) && !this.$store.getters['users/roles'].includes(20) && !this.$store.getters['users/roles'].includes(17)) {
      this.$router.push('/')
    }
  }, */
  beforeRouteEnter (to, from, next) {
    next(vm => {
      const propiedades = vm.$store.getters['users/rol']
      console.log(propiedades)
      if (propiedades === 1 || propiedades === 2 || propiedades === 12 || propiedades === 17 || propiedades === 3 || propiedades === 20 || propiedades === 3) {
        next()
      } else {
        next('/')
      }
    })
  },
  mounted () {
    this.getClients()
    this.getSellers()
    this.fetchFromServer()
    this.getBranches()
  },
  methods: {
    getClients () {
      api.get('/customers/options').then(({ data }) => {
        this.customerOptions = data.options
        this.customerOptionsG = data.options
        this.customerOptionsG.push({ label: 'TODOS', value: 'TODOS' })
      })
    },
    getSellers () {
      api.get('/users/getSeller').then(({ data }) => {
        this.sellerOptionsG = data.options
        this.sellerOptionsG.push({ label: 'TODOS', value: 'TODOS' })
        this.$q.loading.hide()
      })
    },
    getBranches () {
      api.get('/branch-offices/options').then(({ data }) => {
        this.branchOptionsG = data.options
        this.branchOptionsG.push({ label: 'TODOS', value: 'TODOS' })
        this.$q.loading.hide()
      })
    },
    fetchFromServer () {
      this.role = this.$store.getters['users/roles'][0]
      console.log(this.$store.getters)
      this.$q.loading.show()
      this.data = []
      const params = []
      params.customer = this.customerG
      params.branch = this.branchG
      params.seller = this.sellerG
      params.sellerId = this.$store.getters['users/id']
      params.saleDatev1 = this.saleDatev1
      params.saleDatev2 = this.saleDatev2
      params.sellerId = this.$store.getters['users/id']
      console.log(params)
      api.post('/sales-reports/getGridReport', params).then(({ data }) => {
        this.data = data.shoppingCarts
        this.$q.loading.hide()
      })
    },
    filtrarClientes2 (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.filteredCustomerOptions2 = this.customerOptionsG.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    filterGrid () {
      this.data = []
      const params = []
      params.customer = this.customerG
      params.branch = this.branchG
      params.seller = this.sellerG
      params.sellerId = this.$store.getters['users/id']
      params.saleDatev1 = this.saleDatev1
      params.saleDatev2 = this.saleDatev2
      console.log(params)
      api.post('/sales-reports/getGridReport', params).then(({ data }) => {
        if (data.result) {
          this.data = data.shoppingCarts
        }
      })
    },
    generatePDF () {
      const params = []
      params.customer = this.customerG
      params.branch = this.branchG
      params.seller = this.sellerG
      if (this.saleDatev1) {
        params.saleDatev1 = this.saleDatev1
        while (params.saleDatev1.includes('/')) {
          params.saleDatev1 = params.saleDatev1.replace('/', '-')
        }
      } else {
        params.saleDatev1 = null
      }
      if (this.saleDatev2) {
        params.saleDatev2 = this.saleDatev2
        while (params.saleDatev2.includes('/')) {
          params.saleDatev2 = params.saleDatev2.replace('/', '-')
        }
      } else {
        params.saleDatev2 = null
      }
      const uri = process.env.API + `sales-reports/getPdfFromShoppingCarts/${params.customer}/${params.branch}/${params.seller}/${params.saleDatev1}/${params.saleDatev2}`
      window.open(uri, '_blank')
    },
    generateCSV () {
      const params = []
      params.customer = this.customerG
      params.status = this.status
      params.seller = this.sellerG
      if (this.saleDatev1) {
        params.saleDatev1 = this.saleDatev1
        while (params.saleDatev1.includes('/')) {
          params.saleDatev1 = params.saleDatev1.replace('/', '-')
        }
      } else {
        params.saleDatev1 = null
      }
      if (this.saleDatev2) {
        params.saleDatev2 = this.saleDatev2
        while (params.saleDatev2.includes('/')) {
          params.saleDatev2 = params.saleDatev2.replace('/', '-')
        }
      } else {
        params.saleDatev2 = null
      }
      const uri = process.env.API + `sales-reports/getCSVFromShoppingCarts/${params.customer}/${params.status}/${params.seller}/${params.saleDatev1}/${params.saleDatev2}`
      window.open(uri, '_blank')
    }
  }
}
</script>

<style>
</style>

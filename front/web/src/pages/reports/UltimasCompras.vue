<template>
  <q-page>
    <div class="q-pa-md panel-header">
      <div class="row">
        <div class="col-sm-8">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Últimas compras" />
            </q-breadcrumbs>
          </div>
        </div>
        <div class="col-sm-4 q-pa-md q-gutter-sm pull-right">
          <div class="col-xs-12 col-md-4 offset-md-10 pull-right">
            <div>
              <!--<q-btn color="purple" icon="mail" @click.native="sendMail()">
                <q-tooltip>ENVIAR CORREO</q-tooltip>
              </q-btn>
              <q-dialog v-model="promptEmailPagos" persistent>
                <q-card style="min-width: 350px">
                  <q-card-section>
                    <q-select color="dark" bg-color="secondary" filled
                              v-model="emailReport.fields.customer_select"
                              :options="filteredCustomerOptionsv2"
                              use-input
                              hide-selected
                              fill-input
                              input-debounce="0"
                              @filter="filtrarClientesv2"
                              @input="filterMail()"
                              label="Cliente"
                              emit-value map-options>
                    </q-select>
                  </q-card-section>
                  <q-card-section class="q-pt-none">
                    <q-input
                      color="white"
                      bg-color="primary"
                      filled
                      dark
                      v-model="emailReport.fields.email_cliente"
                      :error="$v.emailReport.fields.email_cliente.$error"
                      label="Email"
                      :rules="emailRule"
                      @keyup.enter="promptEmail = false"
                    />
                  </q-card-section>

                  <q-card-actions align="right" class="text-primary">
                    <q-btn flat label="Cancelar" @click="cleanDialog()" />
                    <q-btn flat label="Enviar" @click="sendEmailsReport()" :loading="loadingSendingMailBtn" />
                  </q-card-actions>
                </q-card>
              </q-dialog>-->
              <!--<q-btn v-if="haspermissionv1" color="green" style="margin-left: 10px;" icon="fas fa-file-excel" @click="generateCSV()">
                <q-tooltip>GENERAR CSV</q-tooltip>
              </q-btn>
              <q-btn v-if="haspermissionv1" color="red" style="margin-left: 10px;" icon="fas fa-file-pdf" @click="generatePDF()">
                <q-tooltip>GENERAR PDF</q-tooltip>
              </q-btn>-->
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="q-pa-md bg-grey-3">
      <div class="row bg-white">
        <div class="col-md-3 q-pa-md">
          <q-select color="dark" bg-color="secondary" filled
            v-model="filter.product"
            :options="productsOptions"
            use-input
            hide-selected
            fill-input
            input-debounce="0"
            @filter="fSelectProducts"
            @input="filterGrid()"
            label="Productos"
            clearable
            emit-value map-options>
          </q-select>
        </div>
      </div>
      <div class="row bg-white" >
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
                <q-input dense debounce="300" v-model="filter.searchbar" placeholder="Buscar por códigos o nombre de producto">
                  <template v-slot:append>
                    <q-icon name="search" />
                  </template>
                </q-input>
              </div>
            </template>
            <template v-slot:body="props">
              <q-tr :props="props">
                <q-td key="code" style="text-align: center;" :props="props">{{ props.row.code === null ? '-' : props.row.code }}</q-td>
                <q-td key="old_code" style="text-align: center;" :props="props">{{ props.row.old_code === null ? '-' : props.row.old_code }}</q-td>
                <q-td key="product" style="text-align: left;" :props="props">{{ props.row.product === null ? '-' : props.row.product }}</q-td>
                <q-td key="requested_date" style="text-align: center;" :props="props">{{ props.row.requested_date === null ? '-' : props.row.requested_date }}</q-td>
                <q-td key="price" style="text-align: center;" :props="props">{{ props.row.price === null ? '-' : `${currencyFormatter.format(props.row.price)}` }}</q-td>
                <q-td key="supplier" style="text-align: left;" :props="props">{{ props.row.supplier === null ? '-' : props.row.supplier }}</q-td>
              </q-tr>
            </template>
          </q-table>
        </div>
      </div>
    </div>
  </q-page>
</template>

<script>
import api from '../../commons/api.js'
const { required, email } = require('vuelidate/lib/validators')

export default {
  name: 'IndexStorageExits',
  validations: {
    paymentMethod: { required },
    paymentDate: { required },
    qty: { required },
    emailReport: {
      fields: {
        email_cliente: { required, email },
        customer_select: { required }
      }
    }
  },
  data () {
    return {
      currencyFormatter: new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
      }),
      formatter: new Intl.NumberFormat('en-US'),
      shipmentDetailsModal: false,
      role: null,
      promptEmailPagos: false,
      tableWithInvoice: false,
      tableWithoutInvoice: true,
      interval: null,
      customer: 'TODOS',
      disableBtnAddPayment: true,
      disableTblPayments: true,
      qty: null,
      paymentDate: null,
      paymentMethod: null,
      status: [],
      emailReport: {
        fields: {
          email_cliente: '',
          customer_select: null
        }
      },
      loadingSendingMailBtn: false,
      reference: null,
      total_invoice: null,
      id_invoice: null,
      paymentsModal: false,
      saleDatev1: null,
      saleDatev2: null,
      customerOptions: [],
      productsOptions: [],
      filteredProductsOptions: [],
      formaPagoOptions: [],
      pagination: {
        sortBy: 'id',
        descending: true,
        page: 1,
        rowsNumber: 0,
        rowsPerPage: 25
      },
      columns: [
        { name: 'code', align: 'center', label: 'CÓDIGO', field: 'code', sortable: true },
        { name: 'old_code', align: 'center', label: 'CÓDIGO VIEJO', field: 'old_code', sortable: true },
        { name: 'product', align: 'center', label: 'PRODUCTO', field: 'product', sortable: true },
        { name: 'requested_date', align: 'center', label: 'ULTIMA FECHA', field: 'requested_date', sortable: true },
        { name: 'price', align: 'center', label: 'ULTIMOS COSTO', field: 'price', sortable: true },
        { name: 'supplier', align: 'center', label: 'PROVEEDOR', field: 'proveedor', sortable: true }
      ],
      data: [],
      details: [],
      filter: {
        product: null,
        searchbar: ''
      },
      statusShipment: ['PENDIENTE', 'AUTORIZADO'],
      colorStatus: { PENDIENTE: 'warning', AUTORIZADO: 'green-6' }
    }
  },
  computed: {
    roleId () {
      const user = this.$store.getters['users/rol']
      return parseInt(user)
    },
    fiberSaleDocumentFileUrl () {
      return `${process.env.API}invoices/${this.fiberSaleDocumentFile.fields.fiberSaleId}/document-file`
    },
    totalAmounfromPayments () {
      let price = 0
      this.dataPayments.forEach(detail => {
        price += Number(detail.amount)
      })
      return price
    },
    haspermissionv1 () {
      let permission = false
      if (this.$store.getters['users/roles'].includes(1) || this.$store.getters['users/roles'].includes(3) || this.$store.getters['users/roles'].includes(7) || this.$store.getters['users/roles'].includes(12) || this.$store.getters['users/roles'].includes(17)) {
        permission = true
      }
      return permission
    },
    emailRule (val) {
      return [
        val => (this.$v.emailReport.fields.email_cliente.email) || 'El campo debe contener un correo válido.'
      ]
    }
  },
  beforeRouteEnter (to, from, next) {
    next(vm => {
      const propiedades = vm.$store.getters['users/rol']
      console.log(propiedades)
      if (propiedades === 1 || propiedades === 2 || propiedades === 12 || propiedades === 17) {
        next()
      } else {
        next('/')
      }
    })
  },
  /* beforeCreate () {
    if (!(this.$store.getters['users/roles'].includes(1) || this.$store.getters['users/roles'].includes(3) || this.$store.getters['users/roles'].includes(7) || this.$store.getters['users/roles'].includes(2) || this.$store.getters['users/roles'].includes(12) || this.$store.getters['users/roles'].includes(17))) {
      this.$router.push('/')
    }
  }, */
  mounted () {
    this.fetchFromServer()
  },
  methods: {
    fSelectProducts (val, update) {
      if (val === '') {
        update(() => {
          this.filteredProductsOptions = this.productOptions
        })
        return
      }
      update(() => {
        const needle = val.toLowerCase()
        this.filteredProductsOptions = this.productsOptions.filter(v => v.toLowerCase().indexOf(needle) > -1)
      })
    },
    // NOTA: el estatus de pago del invoices es 0: Pendiente, 1: Abonado, 2: Pagado
    // Obtencion de datos
    fetchFromServer () {
      this.$q.loading.show()
      this.qTableRequest({
        pagination: this.pagination,
        filter: this.filter
      })
      /* api.get('/invoices').then(({ data }) => {
        this.data = data.invoices
        this.$q.loading.hide()
      }) */
    },
    async qTableRequest (props) {
      this.pagination = props.pagination
      this.filter = props.filter
      this.data = []
      const params = []
      params.pagination = this.pagination
      params.filter = this.filter
      await api.get('/products/options/category/0').then(({ data }) => {
        this.productsOptions = data.options
        this.productsOptions.push({ label: 'TODOS', value: 'TODOS' })
      })
      await api.post('/products/report', params).then(({ data }) => {
        this.$q.loading.hide()
        this.data = data.products
        this.pagination.rowsNumber = data.productsCount
      }).catch(error => error)
    },
    filterGrid () {
      this.qTableRequest({
        pagination: this.pagination,
        filter: this.filter
      })
    },
    generatePDF () {
      const params = []
      // eslint-disable-next-line camelcase
      let var_status = []
      if (this.status.length === 0) {
        // eslint-disable-next-line camelcase
        var_status = [99]
      } else {
        // eslint-disable-next-line camelcase
        var_status = this.status
      }
      params.customer = this.customer
      // eslint-disable-next-line camelcase
      params.status = var_status
      params.type = 1
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
      const uri = process.env.API + `/invoices/getPdfFromPaymentsDetails/${params.type}/${params.customer}/${params.status}/${params.saleDatev1}/${params.saleDatev2}`
      window.open(uri, '_blank')
    },
    generateCSV () {
      const params = []
      params.customer = this.customer
      params.status = this.status
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
      const uri = process.env.API + `/invoices/getCSVFromPaymentsDetails/${params.customer}/${params.status}/${params.saleDatev1}/${params.saleDatev2}`
      window.open(uri, '_blank')
    }
  }
}
</script>

<style>
.changeDocumentsReturnedByDriverNotify {
  width: 100px;
}
</style>
// C

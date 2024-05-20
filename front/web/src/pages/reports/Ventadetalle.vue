<template>
  <q-page>
    <div class="q-pa-md panel-header">
      <div class="row">
        <div class="col-sm-8">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Cobranza" />
            </q-breadcrumbs>
          </div>
        </div>
        <div class="col-sm-4 pull-right">
          <div class="col-xs-12 col-md-4 offset-md-10 pull-right">
            <div>
              <q-btn color="purple" icon="mail" @click.native="sendMail()">
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
              </q-dialog>
              <q-btn v-if="haspermissionv1" color="green" style="margin-left: 10px;" icon="fas fa-file-excel" @click="generateCSV()">
                <q-tooltip>GENERAR CSV</q-tooltip>
              </q-btn>
              <q-btn v-if="haspermissionv1" color="red" style="margin-left: 10px;" icon="fas fa-file-pdf" @click="generatePDF()">
                <q-tooltip>GENERAR PDF</q-tooltip>
              </q-btn>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="q-pa-md bg-grey-3">
      <div class="row bg-white q-col-gutter-md">
        <div class="col-md-4"></div>
        <div class="col-md-2">
          <q-select
            color="white"
            bg-color="primary"
            filled
            dark
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
            color="white"
            bg-color="primary"
            filled
            dark
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
        <div class="col-md-2">
          <q-select color="dark" bg-color="secondary" filled
                    v-model="status"
                    :options="[
                      {label: 'PENDIENTE', value: 0},
                      {label: 'ABONADO', value: 1},
                      {label: 'PAGADO', value: 2}
                    ]"
                    emit-value map-options multiple
                    @input="filterGrid()"
                    label="Estatus"
          >
            <template v-slot:option="{ itemProps, itemEvents, opt, selected, toggleOption }">
              <q-item
                v-bind="itemProps"
                v-on="itemEvents"
              >
                <q-item-section>
                  <q-item-label v-html="opt.label" ></q-item-label>
                </q-item-section>
                <q-item-section side>
                  <q-toggle :value="selected" @input="toggleOption(opt)" />
                </q-item-section>
              </q-item>
            </template>
          </q-select>
        </div>
        <div class="col-md-2">
          <q-select color="dark" bg-color="secondary" filled
                    v-model="customer"
                    :options="filteredCustomerOptions"
                    use-input
                    hide-selected
                    fill-input
                    input-debounce="0"
                    @filter="filtrarClientes"
                    @input="filterGrid()"
                    label="Cliente"
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
                <q-input dense debounce="300" v-model="filter" placeholder="Buscar">
                  <template v-slot:append>
                    <q-icon name="search" />
                  </template>
                </q-input>
              </div>
            </template>
            <template v-slot:body="props">
              <q-tr :props="props">
                <q-td key="id" style="text-align: right;" :props="props"><label @click="openActions(props.row.id)" style="text-decoration: underline black; cursor: pointer;">{{ props.row.id }}</label></q-td>
                <q-td key="status_payment" :props="props"><q-chip square dense :color="colorPayment[props.row.status_payment]" text-color="white">{{ statusPayment[props.row.status_payment] }}</q-chip></q-td>
                <q-td key="sale_date" :props="props">{{ props.row.sale_date }}</q-td>
                <q-td key="shopping_cart_id" style="text-align: right;" :props="props"><label @click="openActionsv2(props.row.shopping_cart_id)" style="text-decoration: underline black; cursor: pointer;">{{ props.row.shopping_cart_id }}</label></q-td>
                <q-td key="customer" style="text-align: left;" :props="props">{{ props.row.customer }}</q-td>
                <q-td key="total" style="text-align: right;" :props="props">{{ `${currencyFormatter.format(props.row.cantidad_total) }` }}</q-td>
                <q-td key="abonado" style="text-align: right;" :props="props">{{ `${currencyFormatter.format(props.row.abonado)}` }}</q-td>
                <q-td key="restante" style="text-align: right;" :props="props">{{ `${currencyFormatter.format(props.row.cantidad_restante)}` }}</q-td>
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
      filteredCustomerOptions: [],
      filteredCustomerOptionsv2: [],
      formaPagoOptions: [],
      pagination: {
        sortBy: 'id',
        descending: true,
        page: 1,
        rowsNumber: 0,
        rowsPerPage: 50
      },
      columns: [
        { name: 'id', align: 'center', label: '# Remision', field: 'id', sortable: true },
        { name: 'status_payment', align: 'center', label: 'Estatus de Pago', field: 'status_payment', sortable: true },
        { name: 'sale_date', align: 'center', label: 'Fecha de venta', field: 'sale_date', sortable: true },
        { name: 'shopping_cart_id', align: 'center', label: '# Pedido', field: 'shopping_cart_id', sortable: true },
        { name: 'customer', align: 'center', label: 'Cliente', field: 'customer', sortable: true },
        { name: 'total', align: 'center', label: 'Monto Total', field: 'total', sortable: true },
        { name: 'abonado', align: 'center', label: 'Abonado', field: 'abonado', sortable: true },
        { name: 'restante', align: 'center', label: 'Restante', field: 'restante', sortable: true }
      ],
      data: [],
      filter: '',
      statusPayment: ['PENDIENTE DE PAGO', 'ABONADO', 'PAGADO'],
      colorPayment: ['blue-6', 'warning', 'green-6']
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
  /* beforeCreate () {
    if (!(this.$store.getters['users/roles'].includes(1) || this.$store.getters['users/roles'].includes(3) || this.$store.getters['users/roles'].includes(7) || this.$store.getters['users/roles'].includes(2) || this.$store.getters['users/roles'].includes(12) || this.$store.getters['users/roles'].includes(17))) {
      this.$router.push('/')
    }
  }, */
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
  mounted () {
    this.fetchFromServer()
    this.getClients()
  },
  methods: {
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
      params.customer = this.customer
      params.status = this.status
      params.saleDatev1 = this.saleDatev1
      params.saleDatev2 = this.saleDatev2
      params.type = 1
      params.pagination = this.pagination
      params.filter = this.filter
      await api.post('/invoices/pag_payments', params).then(({ data }) => {
        this.$q.loading.hide()
        this.data = data.invoices
        this.pagination.rowsNumber = data.invoicesCount
      }).catch(error => error)
    },
    getClients () {
      api.get('/customers/options').then(({ data }) => {
        this.customerOptions = data.options
        this.customerOptionsv2 = data.options
        this.customerOptions.push({ label: 'TODOS', value: 'TODOS' })
      })
    },
    getPayments (id) {
      const params = []
      params.id = this.id_invoice
      api.get('/invoices/formaPagoOptions').then(({ data }) => {
        this.formaPagoOptions = data.options
        api.post('/invoices/dataFromInvoice', params).then(({ data }) => {
          if (data.result) {
            this.total_invoice = parseFloat(data.total_invoice)
            this.dataPayments = data.payments
            if (this.dataPayments.length > 0) {
              api.get('/invoices/keepCheckingPayments/' + this.id_invoice).then(({ data }) => {
                if (!data.result) {
                  clearInterval(this.interval)
                  this.interval = null
                } else if (this.interval === null) {
                  this.interval = setInterval(() => {
                    this.revisarPagos(this.id_invoice)
                  }, 10000)
                }
                this.disableTblPayments = true
                const amounts = parseFloat(this.totalAmounfromPayments)
                const amountfromInvoice = parseFloat(this.total_invoice.toFixed(2))
                if (amountfromInvoice > amounts) {
                  this.disableBtnAddPayment = false
                  this.fetchFromServer()
                }
              })
            } else {
              this.disableTblPayments = false
            }
          }
        })
      })
    },
    // Filtrado de datos
    filterGrid () {
      this.data = []
      const params = []
      params.customer = this.customer
      params.status = this.status
      params.saleDatev1 = this.saleDatev1
      params.saleDatev2 = this.saleDatev2
      params.type = 1
      params.pagination = this.pagination
      params.filter = this.filter
      api.post('/invoices/getGridPayments', params).then(({ data }) => {
        if (data.result) {
          this.$q.loading.hide()
          this.data = data.invoices
          this.pagination.rowsNumber = data.invoicesCount
        }
      })
    },
    filtrarClientes (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.filteredCustomerOptions = this.customerOptions.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    filtrarClientesv2 (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.filteredCustomerOptionsv2 = this.customerOptionsv2.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    // Acciones
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
    },
    // Enviar Correos Siuuuu!
    sendMail () {
      this.promptEmailPagos = true
    },
    filterMail () {
      const params = []
      params.id = this.emailReport.fields.customer_select
      api.post('/customers/getDataClient', params).then(({ data }) => {
        console.log(data)
        this.emailReport.fields.email_cliente = data.data[0].email
      })
    },
    cleanDialog () {
      this.emailReport.fields.email_cliente = ''
      this.emailReport.fields.customer_select = null
      this.promptEmailPagos = false
    },
    sendEmailsReport () {
      this.$v.emailReport.fields.$reset()
      this.$v.emailReport.fields.$reset()
      this.$v.emailReport.fields.$touch()
      if (this.$v.emailReport.fields.$error) {
        return false
      }
      this.loadingSendingMailBtn = true
      const params = []
      params.customer = this.emailReport.fields.customer_select
      params.email = { ...this.emailReport.fields }.email_cliente
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
      params.status = this.status
      api.post('/invoices/sendEmailsReport', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.promptEmailPagos = false
          this.emailReport.fields.email_cliente = ''
          this.emailReport.fields.customer_select = null
          this.loadingSendingMailBtn = false
        } else {
          this.loadingSendingMailBtn = false
        }
      })
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

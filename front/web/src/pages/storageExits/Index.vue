<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-8">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Remisiones" />
            </q-breadcrumbs>
          </div>
        </div>
        <div class="col-sm-4 pull-right">
          <div class="col-xs-12 col-md-4 offset-md-10 pull-right">
            <div>
              <!--              <q-btn color="purple" icon="mail" @click.native="sendMail()">-->
              <!--                <q-tooltip>ENVIAR CORREO</q-tooltip>-->
              <!--              </q-btn>-->
              <!-- <q-btn v-if="haspermissionv1" color="red" style="margin-left: 10px;" icon="fas fa-file-pdf" @click="closeSales()">
                <q-tooltip>CORTE DE CAJA</q-tooltip>
              </q-btn> -->
              <q-btn v-if="haspermissionv1" color="green" style="margin-left: 10px;" icon="fas fa-file-excel" @click="generateCSV()">
                <q-tooltip>GENERAR CSV</q-tooltip>
              </q-btn>
              <q-btn v-if="haspermissionv1" color="red" style="margin-left: 10px;" icon="fas fa-file-pdf" @click="generatePDF()">
                <q-tooltip>GENERAR PDF</q-tooltip>
              </q-btn>
            </div>
          </div>
        </div>
        <!--        <div class="col-sm-4 pull-right">-->
<!--          <div class="q-pa-md q-gutter-sm">-->
<!--            <q-breadcrumbs align="right">-->
<!--              <q-breadcrumbs-el label="" icon="home" to="/" />-->
<!--              <q-breadcrumbs-el label="Salidas de almacén" />-->
<!--            </q-breadcrumbs>-->
<!--          </div>-->
<!--        </div>-->
      </div>
    </div>
    <div class="q-pa-md bg-grey-3">
      <div class="row bg-white border-panel">
        <div class="col q-pa-md">
      <div class="row bg-white q-col-gutter-xs q-col-gutter-md">
        <!-- <div class="col-sm-2"></div> -->
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
          <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="remision"
                label="Remision"
                v-on:keyup.enter="filterGrid()"
              >
                <template v-slot:prepend>
                  <q-icon name="tag" />
                </template>
              </q-input>
        </div>
        <!-- <div class="col-sm-2">
          <q-select  color="dark" bg-color="secondary" filled
                    v-model="remision"
                    :options="optionsRemision"
                    use-input
                    hide-selected
                    fill-input
                    input-debounce="0"
                    @filter="filtrarRemision"
                    @input="filterGrid()"
                    label="Remisión"
                    emit-value map-options>
          </q-select>
        </div> -->
        <div class="col-sm-2">
          <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="factura"
                label="Factura"
                v-on:keyup.enter="filterGrid()"
              >
                <template v-slot:prepend>
                  <q-icon name="description" />
                </template>
              </q-input>
          <!-- <q-select  color="dark" bg-color="secondary" filled
                    v-model="customer"
                    :options="filteredCustomerOptions"
                    use-input
                    hide-selected
                    fill-input
                    input-debounce="0"
                    @filter="filtrarClientes"
                    @input="filterGrid()"
                    label="Facutura"
                    emit-value map-options>
          </q-select> -->
        </div>
        <div class="col-md-2">
          <q-select  color="dark" bg-color="secondary" filled
                    v-model="status"
                    :options="[
                      {label: 'TODOS', value: 'TODOS'},
                      {label: 'PAGADO', value: 'PAGADO'},
                      {label: 'ENVIADO', value: 'ENVIADO'},
                      {label: 'REMISIONADO', value: 'REMISIONADO'}
                    ]"
                    emit-value map-options
                    @input="filterGrid()"
                    label="Estatus"
          >
          </q-select>
        </div>
        <div class="col-sm-2">
          <q-select  color="dark" bg-color="secondary" filled
                    v-model="statusT"
                    :options="[
                      {label: 'TODOS', value: 'TODOS'},
                      {label: 'NUEVO', value: '0'},
                      {label: 'TIMBRADO', value: '1'},
                      {label: 'CANCELADO', value: '2'},
                      {label: 'TIMBRANDO', value: '4'},
                      {label: 'CANCELANDO', value: '5'},
                      {label: 'ERROR', value: '6'},
                      {label: 'ERROR DE CANCELACION', value: '7'}
                    ]"
                    emit-value map-options
                    @input="filterGrid()"
                    label="Estatus de timbrado"
          >
          </q-select>
        </div>
        <div class="col-sm-2">
          <q-select  color="dark" bg-color="secondary" filled
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
          <!--          <div class="row q-mb-sm">-->
          <!--            <div class="col-xs-12 col-md-2 offset-md-10 pull-right">-->
          <!--              <q-btn color="primary" icon="add" label="Nuevo" @click.native="$router.push('/storage-exits/new')" />-->
          <!--            </div>-->
          <!--          </div>-->
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
                <q-td key="id" style="text-align: right;" :props="props"><label @click="openActions(props.row.id)" class="text-primary" style="text-decoration: underline; cursor: pointer;">{{ props.row.id }}</label></q-td>
                <q-td key="status" style="text-align: center;" :props="props" >
                  <q-chip square dense :color="props.row.status == 'REMISIONADO' ? 'primary' : (props.row.status == 'ENVIADO' ? 'warning' : (props.row.status == 'ENTREGADO' ? 'positive' : (props.row.status == 'PAGADO' ? 'positive' : (props.row.status == 'FACTURADO' ? 'purple' : (props.row.status == 'CANCELADO' ? 'negative' : 'black')))))" text-color="white">
                    {{ props.row.status }}
                  </q-chip>
                  <div style="display: inline;">
                  </div>
                </q-td>
                <q-td key="status_timbrado" :props="props"><q-chip square dense :color="colorTimbrado[props.row.status_timbrado]" text-color="white">{{ statusTimbrado[props.row.status_timbrado] }}</q-chip></q-td>
                <q-td key="sale_date" :props="props">{{ props.row.sale_date }}</q-td>
                <q-td key="shopping_cart_id" style="text-align: center;" :props="props"><label @click="openActionsv2(props.row.shopping_cart_id)" style="text-decoration: underline black; cursor: pointer;">{{ props.row.shopping_cart_id }}</label></q-td>
                <q-td key="factura" style="text-align: left;" :props="props">{{ props.row.factura }}</q-td>
                <q-td key="customer" style="text-align: left;" :props="props">{{ props.row.customer }}</q-td>
                <!-- <q-td key="customer_branch_office" style="text-align: left;" :props="props">{{ props.row.customer_branch_office }}</q-td> -->
                <q-td key="execution_date" :props="props">{{ props.row.bale_movement_date ? props.row.bale_movement_date : '-' }}</q-td>
                <q-td key="exit_branch_office" style="text-align: left;" :props="props">{{ props.row.exit_branch_office }}</q-td>
                <q-td key="total" :props="props">
                  {{ `${currencyFormatter.format(props.row.total) }` }}
                </q-td>
                <q-td key="saldo_insoluto" :props="props">
                  {{ `${currencyFormatter.format(props.row.saldo_insoluto) }` }}
                </q-td>
                <q-td key="actions" style="text-align: left" :props="props">
                  <q-checkbox v-model="props.row.documents_returned_by_driver" @click.native="changeDocumentsReturnedByDriver(props.row.id)" />
                  <!--                  <q-btn color="primary" icon="remove_red_eye" flat @click.native="editSelectedRow(props.row.id)" size="10px" v-if="props.row.status == 'DOCUMENTADO' || props.row.status == 'ENTREGADO'">-->
                  <!--                    <q-tooltip content-class="bg-primary">Ver</q-tooltip>-->
                  <!--                  </q-btn>-->
                  <q-btn color="brown" icon="fas fa-file-pdf" flat @click.native="getPdfInvoice(props.row.id_request, props.row.pdf)" size="10px" v-if="props.row.status_timbrado === 1">
                    <q-tooltip content-class="bg-primary">Ver PDF de factura</q-tooltip>
                  </q-btn>
                  <q-btn color="green" icon="fas fa-file-excel" flat @click.native="getCFDIInvoice(props.row.id_request)" size="10px" v-if="props.row.status_timbrado === 1">
                    <q-tooltip content-class="bg-primary">Ver CFDI de factura</q-tooltip>
                  </q-btn>
                  <q-btn color="primary" icon="fas fa-file-pdf" flat @click.native="saleReferral(props.row.id)" size="10px" v-if="props.row.status == 'ENVIADO' || props.row.status == 'ENTREGADO' || props.row.status == 'FACTURADO'">
                    <q-tooltip content-class="bg-primary">Ver reporte</q-tooltip>
                  </q-btn>
                  <q-btn :color="props.row.document_file ? 'positive' : 'negative'" icon="cloud_upload" flat @click.native="openUploadFiberSaleDocumentFileModal(props.row)" size="10px">
                    <q-tooltip :content-class="props.row.document_file ? 'bg-positive' : 'bg-negative'">Subir documento</q-tooltip>
                  </q-btn>
                  <q-btn color="primary" icon="remove_red_eye" flat @click.native="showFiberSaleDocumentFile(props.row)" size="10px" v-if="props.row.document_file">
                    <q-tooltip content-class="bg-primary">Ver documento</q-tooltip>
                  </q-btn>
                  <q-btn color="primary" icon="cloud_download" flat @click.native="downloadFiberSaleDocumentFile(props.row)" size="10px" v-if="props.row.document_file">
                    <q-tooltip content-class="bg-primary">Descargar documento</q-tooltip>
                  </q-btn>
                  <!--                  <q-btn color="primary" icon="fas fa-edit" flat @click.native="editSelectedRow(props.row.id)" size="10px" v-if="props.row.status == 'NUEVO'">-->
                  <!--                    <q-tooltip content-class="bg-primary">Editar</q-tooltip>-->
                  <!--                  </q-btn>-->
                  <!--                  <q-btn color="primary" icon="fas fa-trash-alt" flat @click.native="deleteSelectedRow(props.row.id)" size="10px" v-if="props.row.status == 'NUEVO'">-->
                  <!--                    <q-tooltip content-class="bg-red">Eliminar</q-tooltip>-->
                  <!--                  </q-btn>-->
                </q-td>

              </q-tr>
            </template>
          </q-table>
        </div>
      </div>
        </div>
      </div>
    </div>
    <q-dialog v-model="fiberSaleDocumentFileModal" persistent>
      <q-card>
        <q-card-section class="row">
          <div class="col-xs-12 col-sm-10 text-h6">Documento: Venta fibra #{{ fiberSaleDocumentFile.fields.fiberSaleId }}</div>
          <q-btn class="col-xs-12 col-sm-2 pull-right" icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-card-section>
          <q-uploader
            :url="fiberSaleDocumentFileUrl"
            method="POST"
            ref="fiberSaleDocumentFileRef"
            hide-upload-btn
            @uploaded="afterUploadFiberSaleDocumentFile"
          />
        </q-card-section>
        <q-card-actions align="right" class="text-primary">
          <q-btn flat label="Subir archivo" @click="uploadFiberSaleDocumentFile()" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import api from '../../commons/api.js'

export default {
  name: 'IndexStorageExits',
  data () {
    return {
      currencyFormatter: new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
      }),
      customer: null,
      saleDatev1: null,
      saleDatev2: null,
      status: 'TODOS',
      statusT: 'TODOS',
      customerOptions: [],
      filteredCustomerOptions: [],
      statusTimbrado: ['NUEVO', 'TIMBRADO', 'CANCELADO', 'CANCELANDO', 'TIMBRANDO', 'CANCELANDO', 'ERROR', 'ERROR AL CANCELAR'],
      colorTimbrado: ['blue-6', 'green-6', 'purple-6', 'warning', 'warning', 'warning', 'red-6', 'red-6'],
      pagination: {
        sortBy: 'id',
        descending: true,
        page: 1,
        rowsNumber: 0,
        rowsPerPage: 25
      },
      columns: [
        { name: 'id', align: 'center', label: '# REMISIÓN', field: 'id', sortable: true },
        { name: 'status', align: 'center', label: 'ESTATUS', field: 'status', sortable: true },
        { name: 'status_timbrado', align: 'center', label: 'ESTATUS DE TIMBRADO', field: 'status_timbrado', sortable: true },
        { name: 'sale_date', align: 'center', label: 'FECHA DE VENTA', field: 'sale_date', sortable: true },
        { name: 'shopping_cart_id', align: 'center', label: '# PEDIDO', field: 'shopping_cart_id', sortable: true },
        { name: 'factura', align: 'center', label: 'FACTURA', field: 'factura', sortable: true },
        { name: 'customer', align: 'center', label: 'CLIENTE', field: 'customer', sortable: true },
        // { name: 'customer_branch_office', align: 'center', label: 'SUCURSAL DE CLIENTE', field: 'customer_branch_office', sortable: true },
        { name: 'execution_date', align: 'center', label: 'FECHA SALIDA', field: 'execution_date', sortable: true },
        { name: 'exit_branch_office', align: 'center', label: 'SALIDA ESTACIÓN', field: 'exit_branch_office', sortable: true },
        // { name: 'deliver_status_by', align: 'center', label: 'RECIBIDO POR', field: 'deliver_status_by', sortable: true },
        // { name: 'deliver_status_at', align: 'center', label: 'RECIBIDO EN', field: 'deliver_status_at', sortable: true },
        { name: 'total', align: 'center', label: 'TOTAL', field: 'total', sortable: true },
        { name: 'saldo_insoluto', align: 'center', label: 'PENDIENTE', field: 'saldo_insoluto', sortable: true },
        { name: 'actions', align: 'center', label: 'ACCIONES', field: 'actions', sortable: false }
      ],
      data: [],
      filter: '',
      fiberSaleDocumentFile: {
        fields: {
          fiberSaleId: null
        }
      },
      fiberSaleDocumentFileModal: false,
      serverUrl: process.env.API,
      optionsRemision: [],
      optionsRemisionId: [],
      remision: null,
      factura: null
    }
  },
  computed: {
    fiberSaleDocumentFileUrl () {
      return `${process.env.API}invoices/${this.fiberSaleDocumentFile.fields.fiberSaleId}/document-file`
    },
    haspermissionv1 () {
      let permission = false
      const propiedades = this.$store.getters['users/rol']
      if (propiedades === 1 || propiedades === 3 || propiedades === 7 || propiedades === 2 || propiedades === 20 || propiedades === 4 || propiedades === 27 || propiedades === 22 || propiedades === 29 || propiedades === 28 || propiedades === 17) {
        permission = true
      }
      return permission
    }
  },
  beforeRouteEnter (to, from, next) {
    next(vm => {
      const propiedades = vm.$store.getters['users/rol']
      // console.log(propiedades)
      if (propiedades === 1 || propiedades === 3 || propiedades === 7 || propiedades === 2 || propiedades === 20 || propiedades === 4 || propiedades === 27 || propiedades === 22 || propiedades === 29 || propiedades === 28 || propiedades === 17) {
        next()
      } else {
        next('/')
      }
    })
  },
  /* beforeCreate () {
    if (!(this.$store.getters['users/roles'].includes(1) || this.$store.getters['users/roles'].includes(3) || this.$store.getters['users/roles'].includes(4) || this.$store.getters['users/roles'].includes(2) || this.$store.getters['users/roles'].includes(18) || this.$store.getters['users/roles'].includes(20))) {
      this.$router.push('/')
    }
  }, */
  mounted () {
    this.fetchFromServer()
    this.getClients()
    // this.idRemmision()
  },
  methods: {
    filtrarRemision (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.optionsRemision = this.optionsRemisionId.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    async idRemmision () {
      await api.get('/invoices/idRemmision').then(({ data }) => {
        if (data.result) {
          this.optionsRemisionId = data.idremision
          console.log(this.optionsRemisionId)
        }
      })
    },
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
      const params = []
      params.customer = this.customer
      params.saleDatev1 = this.saleDatev1
      params.saleDatev2 = this.saleDatev2
      params.status = this.status
      params.statusT = this.statusT
      params.pagination = this.pagination
      params.remision = this.remision
      params.factura = this.factura
      params.filter = this.filter
      // console.log(params)
      api.post('/invoices/pag', params).then(({ data }) => {
        this.$q.loading.hide()
        this.data = data.invoices
        // console.log(data.invoices)
        this.pagination.rowsNumber = data.invoicesCount
      }).catch(error => error)
    },
    saleReferral (id) {
      const uri = process.env.API + `invoices/pdf/${id}`
      window.open(uri, '_blank')
    },
    changeDocumentsReturnedByDriver (id) {
      this.$q.loading.show()
      api.put(`/invoices/${id}/documents-returned`, []).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning'),
          classes: 'changeDocumentsReturnedByDriverNotify'
        })
        if (data.result) {
          this.$q.loading.hide()
          this.fetchFromServer()
        } else {
          this.$q.loading.hide()
        }
      })
    },
    openUploadFiberSaleDocumentFileModal (fiberSale) {
      this.fiberSaleDocumentFile.fields.fiberSaleId = fiberSale.id
      this.fiberSaleDocumentFileModal = true
    },
    afterUploadFiberSaleDocumentFile (response) {
      this.$q.loading.show()
      const data = JSON.parse(response.xhr.response)
      this.$q.notify({
        message: data.message.content,
        position: 'top',
        color: (data.result ? 'positive' : 'warning')
      })
      if (data.result) {
        this.$q.loading.hide()
        this.fetchFromServer()
        this.fiberSaleDocumentFileModal = false
      } else {
        this.$q.loading.hide()
      }
    },
    uploadFiberSaleDocumentFile () {
      this.$refs.fiberSaleDocumentFileRef.upload()
    },
    showFiberSaleDocumentFile (fiberSale) {
      if (fiberSale.document_file) {
        window.open(`${this.serverUrl}assets/invoices/documents/${fiberSale.document_file}`, '_blank')
      } else {
        this.$q.notify({
          message: 'La venta de fibra no cuenta con un documento subido',
          position: 'top',
          color: 'warning'
        })
      }
    },
    downloadFiberSaleDocumentFile (fiberSale) {
      if (fiberSale.document_file) {
        window.open(`${process.env.API}invoices/${fiberSale.id}/document-file/download`, '_blank')
      } else {
        this.$q.notify({
          message: 'La venta de fibra no cuenta con un documento subido',
          position: 'top',
          color: 'warning'
        })
      }
    },
    editSelectedRow (id) {
      this.$router.push(`/storage-exits/${id}`)
    },
    deleteSelectedRow (id) {
      this.$q.loading.show()
      api.delete(`/invoices/${id}`).then(({ data }) => {
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
    openActions (id) {
      this.$router.push(`/storage-exits/${id}`)
    },
    openActionsv2 (id) {
      this.$router.push(`/shopping-carts/orders/${id}`)
    },
    getClients () {
      api.get('/customers/options').then(({ data }) => {
        this.customerOptions = data.options
        this.customerOptions.push({ label: 'TODOS', value: 'TODOS' })
      })
    },
    filterGrid () {
      this.data = []
      const params = []
      params.customer = this.customer
      params.saleDatev1 = this.saleDatev1
      params.saleDatev2 = this.saleDatev2
      params.status = this.status
      params.statusT = this.statusT
      params.pagination = this.pagination
      params.remision = this.remision
      params.factura = this.factura
      params.filter = this.filter
      api.post('/invoices/getGrid', params).then(({ data }) => {
        if (data.result) {
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
    getCFDIInvoice (idRequest) {
      var url = process.env.API === 'https://api_alpez.wasp.mx/' ? 'https://batuta.wasp.mx' : 'http://batuta.beta.antfarm.mx'
      window.open(url + '/api/download_xml/' + idRequest, '_blank')
    },
    getPdfInvoice (idRequest, name) {
      this.$q.loading.show()
      api.fileDownload(`/invoices/pdfi/${idRequest}`).then(({ data }) => {
        const url = window.URL.createObjectURL(new Blob([data], { type: 'application/pdf' }))
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', name)
        document.body.appendChild(link)
        this.$q.loading.hide()
        link.click()
      })
      // var url = process.env.API === 'http://api_alpez.wasp.mx/' ? 'batuta.antfarm.mx' : 'batuta.beta.antfarm.mx'
      // window.open('http://' + url + '/api/get_pdf/' + idRequest + '/0', '_blank')
    },
    closeSales () {
      const uri = process.env.API + 'close-sale/closeSales'
      window.open(uri, '_blank')
    },
    generatePDF () {
      const params = []
      params.customer = this.customer
      params.status = this.status
      params.statusT = this.statusT
      params.remision = this.remision
      params.factura = this.factura
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
      const uri = process.env.API + `invoices/createPdfFromRemission/${params.customer}/${params.status}/${params.saleDatev1}/${params.saleDatev2}/${params.statusT}/${params.remision}/${params.factura}`
      this.$q.loading.show()
      api.fileDownload(uri).then(({ data }) => {
        const url = window.URL.createObjectURL(new Blob([data], { type: 'application/pdf' }))
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', 'Reporte_Remisiones.pdf')
        document.body.appendChild(link)
        this.$q.loading.hide()
        link.click()
      })
    },
    generateCSV () {
      const params = []
      params.customer = this.customer
      params.status = this.status
      params.statusT = this.statusT
      params.remision = this.remision
      params.factura = this.factura
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
      const uri = process.env.API + `invoices/getCSVFromRemission/${params.customer}/${params.status}/${params.saleDatev1}/${params.saleDatev2}/${params.statusT}/${params.remision}/${params.factura}`
      // window.open(uri, '_blank')
      this.$q.loading.show()
      api.fileDownload(uri).then(({ data }) => {
        const url = window.URL.createObjectURL(new Blob([data], { type: 'application/csv' }))
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', 'Reporte_Remisiones.csv')
        document.body.appendChild(link)
        this.$q.loading.hide()
        link.click()
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

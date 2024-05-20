<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-8">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Ordenes de compra" />
            </q-breadcrumbs>
          </div>
        </div>
        <div class="col-xs-6 col-md-4 pull-right" v-if="roleId === 1 ||  roleId === 22 || roleId === 20 || roleId === 28">
          <div class="q-pa-sm q-gutter-sm">
            <q-btn color="primary" icon="add" label="Nuevo" @click.native="$router.push('/purchase-orders/new')" />
          </div>
        </div>
      </div>
    </div>

    <div class="q-pa-md bg-grey-3">
      <div class="row bg-white border-panel">
        <div class="col q-pa-md">

          <q-table
            flat
            bordered
            :data="data"
            :columns="columns"
            row-key="serial"
            :pagination.sync="pagination"
          >
            <template v-slot:body="props">
              <q-tr :props="props">
                <q-td key="serial" style="text-align: center; width: 10%;" :props="props">{{ props.row.serial }}</q-td>
                <q-td key="supplier" style="text-align: left; width: 35%;" :props="props">{{ props.row.supplier }}</q-td>
                <q-td key="order_date" style="width: 15%;" :props="props">{{ props.row.order_date }}</q-td>
                <q-td key="requested_date" style="width: 15%;" :props="props">{{ props.row.requested_date }}</q-td>
                <q-td key="status" style="width: 10%; text-align: center" :props="props">
                  <q-chip square dense :color="props.row.status == 'NUEVO' ? 'secondary' : (props.row.status == 'COTIZADO' ? 'warning' : (props.row.status == 'PEDIDO' ? 'orange' : (props.row.status == 'EMBARCADO' ? 'purple-6' : (props.row.status == 'ARRIBO' ? 'accent' : (props.row.status == 'RECEPCION' ? 'light-green' : (props.row.status == 'RECIBIDO' ? 'green' : (props.row.status == 'PARCIAL' ? 'red-4' : 'red-6')))))))" text-color="white">
                    {{ props.row.status }}
                  </q-chip>
                </q-td>
                <q-td key="actions" style="width: 10%;  text-align: left" :props="props" v-if="props.row.status != 'Cerrado'">
                  <q-btn color="primary" :icon="props.row.hayrestante || props.row.shipment_id === null ? 'add' : 'edit'" flat @click.native="props.row.hayrestante || props.row.shipment_id === null ? newShipment(props.row.id) : editShipment(props.row.shipment_id)" size="10px">
                    <q-tooltip v-if="props.row.hayrestante || props.row.shipment_id === null" content-class="bg-primary">Nueva Recepción</q-tooltip>
                    <q-tooltip v-else content-class="bg-primary">Editar Recepción</q-tooltip>
                  </q-btn>
                  <q-btn color="primary" icon="fas fa-eye" flat @click.native="shipmentsModal(props.row)" size="10px">
                    <q-tooltip content-class="bg-primary">Ver Recepciones</q-tooltip>
                  </q-btn>
                  <!-- <q-btn color="negative" v-if="($store.getters['users/roles'].includes(1) || $store.getters['users/roles'].includes(5) || $store.getters['users/roles'].includes(10) || $store.getters['users/roles'].includes(19)) && props.row.status == 'NUEVO'" icon="fas fa-trash-alt" flat @click.native="deleteSelectedRow(props.row.id)" size="10px">
                    <q-tooltip content-class="bg-red">Eliminar</q-tooltip>
                  </q-btn> -->
                </q-td>
                <q-td key="actions" style="width: 10%;" :props="props" v-else>
                  <q-btn color="primary" icon="remove_red_eye" flat @click.native="editSelectedRow(props.row.id)" size="10px">
                    <q-tooltip content-class="bg-primary">Ver detalles</q-tooltip>
                  </q-btn>
                </q-td>
              </q-tr>
            </template>
          </q-table>
        </div>
      </div>
    </div>
    <q-dialog v-model="showShipments" persistent>
      <q-card style="min-width: 900px;" class="q-pt-md">
        <q-card-section>
          <div class="row">
            <div class="col-md-12 text-h5 text-center">
              <label for="">Recepciones Orden de Compra {{foliooc}}</label>
            </div>
          </div>
        </q-card-section>
        <q-card-section class="q-pt-none">
          <div class="row">
            <div class="col-md-12 col-xs-12 q-pa-xs">
            <q-table
              flat
              bordered
              row-key="name"
              :data="shipments"
              :columns="shipmentColumns"
              :pagination.sync="pagination"
              hide-pagination
            >
              <template v-slot:body="props">
                <q-tr :props="props">
                  <q-td key="serial" style="text-align: center; width: 10%;" :props="props">{{ props.row.serial }}</q-td>
                  <q-td key="receive_date" style="width: 30%;" :props="props">{{ props.row.receive_date }}</q-td>
                  <q-td key="status" style="width: 30%;" :props="props">
                    <q-chip square dense :color="props.row.status == 'NUEVO' ? 'secondary' : (props.row.status == 'MUESTREADO' ? 'warning' : (props.row.status == 'RECIBIDO' ? 'orange' : (props.row.status == 'ANALIZADO' ? 'positive' : (props.row.status == 'RECHAZADO' ? 'negative' : 'blue'))))" text-color="white">
                      {{ props.row.status }}
                    </q-chip>
                  </q-td>
                      <q-td key="actions" style="width: 10%;" :props="props" v-if="props.row.status != 'RECIBIDO'">
                        <q-btn :color="props.row.invoice ? 'positive' : 'negative'" icon="cloud_upload" flat @click.native="openUploadShipmentInvoiceModal(props.row)" size="10px">
                          <q-tooltip :content-class="props.row.invoice ? 'bg-positive' : 'bg-negative'">Subir archivo</q-tooltip>
                        </q-btn>
                        <q-btn color="primary" icon="remove_red_eye" flat @click.native="showShipmentInvoiceFile(props.row)" size="10px" v-if="props.row.invoice">
                          <q-tooltip content-class="bg-primary">Ver factura</q-tooltip>
                        </q-btn>
                        <q-btn color="primary" icon="cloud_download" flat @click.native="downloadShipmentInvoiceFile(props.row)" size="10px" v-if="props.row.invoice">
                          <q-tooltip content-class="bg-primary">Descargar factura</q-tooltip>
                        </q-btn>
                        <q-btn color="primary" icon="fas fa-edit" flat @click.native="editShipment(props.row.id)" size="10px">
                          <q-tooltip content-class="bg-primary">Editar</q-tooltip>
                        </q-btn>
                        <q-btn color="primary" icon="fas fa-trash-alt" flat @click.native="removeShipment(props.row)" size="10px">
                          <q-tooltip content-class="bg-red">Eliminar</q-tooltip>
                        </q-btn>
                      </q-td>
                      <q-td key="actions" style="width: 10%;" :props="props" v-else>
                        <q-btn :color="props.row.invoice ? 'positive' : 'negative'" icon="cloud_upload" flat @click.native="openUploadShipmentInvoiceModal(props.row)" size="10px">
                          <q-tooltip :content-class="props.row.invoice ? 'bg-positive' : 'bg-negative'">Subir factura</q-tooltip>
                        </q-btn>
                        <q-btn color="primary" icon="remove_red_eye" flat @click.native="showShipmentInvoiceFile(props.row)" size="10px" v-if="props.row.invoice">
                          <q-tooltip content-class="bg-primary">Ver factura</q-tooltip>
                        </q-btn>
                        <q-btn color="primary" icon="cloud_download" flat @click.native="downloadShipmentInvoiceFile(props.row)" size="10px" v-if="props.row.invoice">
                          <q-tooltip content-class="bg-primary">Descargar factura</q-tooltip>
                        </q-btn>
                        <q-btn color="primary" icon="fas fa-file-pdf" flat @click.native="generateShipmentPdf(props.row.id)" size="10px" v-if="props.row.status == 'RECIBIDO'">
                          <q-tooltip content-class="bg-primary">Ver ticket de entrada</q-tooltip>
                        </q-btn>
                        <q-btn color="primary" icon="remove_red_eye" flat @click.native="editShipment(props.row.id)" size="10px">
                          <q-tooltip content-class="bg-primary">Ver detalles</q-tooltip>
                        </q-btn>
                      </q-td>
                </q-tr>
              </template>
            </q-table>
            </div>
          </div>
        </q-card-section>
        <q-card-actions align="right" class="text-primary">
          <q-btn  label="Cerrar" color="red" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>
    <q-dialog v-model="shipmentInvoiceModal" persistent>
      <q-card>
        <q-card-section class="row">
          <div class="col-xs-12 col-sm-10 text-h6">Factura: Recepción {{ shipmentInvoice.fields.shipmentSerial }}</div>
          <q-btn class="col-xs-12 col-sm-2 pull-right" icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-card-section>
          <q-uploader
            :url="fileShipmentInvoiceUrl"
            method="POST"
            ref="fileShipmentInvoiceRef"
            hide-upload-btn
            @uploaded="afterUploadShipmentInvoiceFile"
          />
        </q-card-section>
        <q-card-actions align="right" class="text-primary">
          <q-btn flat label="Subir archivo" @click="uploadShipmentInvoiceFile()" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import api from '../../commons/api.js'

export default {
  name: 'IndexPurchaseOrders',
  data () {
    return {
      shipmentInvoiceModal: false,
      shipments: [],
      foliooc: null,
      showShipments: false,
      pagination: {
        sortBy: 'serial',
        descending: true,
        rowsPerPage: 25
      },
      shipmentInvoice: {
        fields: {
          shipmentId: null,
          shipmentSerial: null
        }
      },
      columns: [
        { name: 'serial', align: 'center', label: 'FOLIO', field: 'serial', style: 'width: 10%', sortable: true },
        { name: 'supplier', align: 'center', label: 'PROVEEDOR', field: 'supplier', style: 'width: 35%', sortable: true },
        { name: 'order_date', align: 'center', label: 'FECHA DE PEDIDO', field: 'order_date', style: 'width: 15%', sortable: true },
        { name: 'requested_date', align: 'center', label: 'FECHA DE ARRIBO', field: 'requested_date', style: 'width: 15%', sortable: true },
        { name: 'status', align: 'center', label: 'ESTATUS', field: 'status', style: 'width: 10%', sortable: true },
        { name: 'actions', align: 'center', label: 'ACCIONES', field: 'actions', style: 'width: 10%', sortable: false }
      ],
      shipmentColumns: [
        { name: 'serial', align: 'center', label: 'FOLIO', field: 'serial', style: 'width: 10%', sortable: true },
        { name: 'receive_date', align: 'center', label: 'FECHA DE RECEPCION', field: 'receive_date', style: 'width: 30%', sortable: true },
        { name: 'status', align: 'center', label: 'ESTATUS', field: 'status', style: 'width: 30%', sortable: true },
        { name: 'actions', align: 'center', label: 'ACCIONES', field: 'actions', style: 'width: 10%', sortable: false }
      ],
      data: []
    }
  },
  computed: {
    roleId () {
      const user = this.$store.getters['users/rol']
      return parseInt(user)
    },
    fileShipmentInvoiceUrl () {
      return `${process.env.API}shipments/${this.shipmentInvoice.fields.shipmentId}/invoice-file`
    }
  },
  beforeRouteEnter (to, from, next) {
    next(vm => {
      const propiedades = vm.$store.getters['users/rol']
      console.log(propiedades)
      if (propiedades === 1 || propiedades === 3 || propiedades === 7 || propiedades === 2 || propiedades === 20 || propiedades === 4 || propiedades === 27 || propiedades === 20 || propiedades === 22 | propiedades === 28) {
        next()
      } else {
        next('/')
      }
    })
  },
  /* beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(2)) {
      this.$router.push('/')
    }
  }, */
  mounted () {
    this.fetchFromServer()
  },
  methods: {
    generateShipmentPdf (id) {
      const uri = process.env.API + `shipments/pdf/${id}`
      window.open(uri, '_blank')
    },
    afterUploadShipmentInvoiceFile (response) {
      const data = JSON.parse(response.xhr.response)
      this.$q.notify({
        message: data.message.content,
        position: 'top',
        color: (data.result ? 'positive' : 'warning')
      })
      if (data.result) {
        this.fetchFromServer()
        this.shipmentInvoiceModal = false
      }
    },
    openUploadShipmentInvoiceModal (shipment) {
      this.shipmentInvoice.fields.shipmentId = shipment.id
      this.shipmentInvoice.fields.shipmentSerial = shipment.serial
      this.shipmentInvoiceModal = true
    },
    downloadShipmentInvoiceFile (shipment) {
      if (shipment.invoice) {
        window.open(`${process.env.API}shipments/${shipment.id}/invoice-file/download`, '_blank')
      } else {
        this.$q.notify({
          message: 'La recepción no cuenta con una factura subida',
          position: 'top',
          color: 'warning'
        })
      }
    },
    showShipmentInvoiceFile (shipment) {
      if (shipment.invoice) {
        window.open(`${this.serverUrl}assets/shipments/invoices/${shipment.invoice}`, '_blank')
      } else {
        this.$q.notify({
          message: 'La recepción no cuenta con una factura subida',
          position: 'top',
          color: 'warning'
        })
      }
    },
    editShipment (shipmentElement) {
      this.$router.push(`/shipments/${shipmentElement}/${1}`)
    },
    removeShipment (shipment) {
      this.$q.loading.show()
      api.delete(`/shipments/${shipment.id}`).then(({ data }) => {
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
    },
    shipmentsModal (props) {
      api.get(`/shipments/order/${props.id}`).then(({ data }) => {
        console.log(data)
        this.shipments = data.shipments
      })
      this.foliooc = props.serial
      this.showShipments = true
    },
    newShipment (id) {
      const orderid = Number(id)
      this.$router.push(`/shipments/purcharse-order/${orderid}/${1}`)
    },
    fetchFromServer () {
      this.$q.loading.show()
      api.get('/purchase-orders/all').then(({ data }) => {
        console.log(data)
        if (data.result) {
          this.showShipments = false
          this.data = data.orders
        } else {
          this.$q.notify({
            message: data.message.content,
            position: 'top',
            color: 'warning',
            icon: 'close'
          })
        }
        this.$q.loading.hide()
      })
    },
    editSelectedRow (id) {
      this.$router.push(`/purchase-orders/${id}`)
    },
    deleteSelectedRow (id) {
      this.$q.dialog({
        title: 'Confirmación',
        message: '¿Desea eliminar esta Orden de Compra?',
        persistent: true,
        ok: 'Eliminar',
        cancel: 'Cancelar'
      }).onOk(() => {
        api.delete(`/purchase-orders/${id}`).then(({ data }) => {
          this.$q.notify({
            message: data.message.content,
            position: 'top',
            color: (data.result ? 'positive' : 'warning'),
            icon: (data.result ? 'thumb_up' : 'close')
          })
          if (data.result) {
            this.fetchFromServer()
          }
        })
      }).onCancel(() => {})
    }
  }
}
</script>

<style>
</style>

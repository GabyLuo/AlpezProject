<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-8 row">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="right" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el :label="this.$route.params.type === '1' ? 'Ordenes de compra' : 'Recepciones'" :to="this.$route.params.type === '1' ? `/purchase-orders/${shipment.fields.order_id}` : '/raw-material-shipments'" />
              <q-breadcrumbs-el :label="'Recepción ' + shipment.fields.serial " />
            </q-breadcrumbs>
          </div>
        </div>
        <div class="col-xs-12 col-sm-4 pull-right q-pa-sm q-gutter-sm" v-if="shipment.fields.status == 'NUEVO'">
          <q-btn color="positive" icon="arrow_right_alt" label="Entrada" @click="saveStorageEntry()" v-show="canExecute" />
        </div>
            <div class="col-xs-12 col-sm-4 pull-right q-pa-sm q-gutter-sm" v-if="shipment.fields.status == 'RECIBIDO'">
              <q-btn color="primary" icon="mail" label="Enviar a proveedor" @click="sendShipmentPdfToSupplier()" :loading="loadingSendingMailBtn">
                <template v-slot:loading>
                  <q-spinner class="on-left" />
                  Enviando correo...
                </template>
              </q-btn>
            </div>
      </div>
    </div>

    <div class="q-pa-md bg-grey-3">
      <div class="row bg-white border-panel">
        <div class="col q-pa-md">
          <div class="row q-col-gutter-xs">
            <div class="col-xs-12 col-sm-3">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                ref="shipmentFieldsReceiveDateSelectRef"
                v-model="shipment.fields.receive_date"
                mask="date"
                label="Fecha de recepción"
                :rules="shipmentReceiveDateRules"
                :disable="shipment.fields.status != 'NUEVO'"
              >
                <template v-slot:prepend>
                  <q-icon name="event" />
                </template>
                <q-popup-proxy
                  ref="shipmentFieldsReceiveDateRef"
                  transition-show="scale"
                  transition-hide="scale"
                >
                  <div class="col-sm-12">
                    <q-date
                    :locale="myLocale"
                      mask="DD/MM/YYYY"
                      v-model="shipment.fields.receive_date"
                      @input="() => $refs.shipmentFieldsReceiveDateRef.hide()"
                      today-btn
                    />
                  </div>
                </q-popup-proxy>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                ref="shipmentFieldsReceiveTimeSelectRef"
                v-model="shipment.fields.receive_time"
                mask="time"
                label="Hora de recepción"
                :rules="shipmentReceiveTimeRules"
                :disable="shipment.fields.status != 'NUEVO'"
              >
                <template v-slot:prepend>
                  <q-icon name="access_time" />
                </template>
                <q-popup-proxy
                  ref="shipmentFieldsReceiveTimeRef"
                  transition-show="scale"
                  transition-hide="scale"
                >
                  <div class="col-sm-12">
                    <q-time
                      v-model="shipment.fields.receive_time"
                      @input="() => $refs.shipmentFieldsReceiveTimeRef.hide()"
                      now-btn
                    />
                  </div>
                </q-popup-proxy>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-input
                color="dark"
                bg-color="blue"
                filled
                disable
                v-model="shipment.fields.status"
                :error="$v.shipment.fields.status.$error"
                label="Estatus"
                :rules="shipmentStatusRules"
              >
                <template v-slot:prepend>
                  <q-icon name="dvr" />
                </template>
              </q-input>
            </div>
          </div>
          <div class="col-xs-12 col-sm-2 offset-sm-10 pull-right">
            <q-btn color="positive" icon="save" label="Actualizar" @click="updateShipment()" style="margin-right:2%;"/>
          </div>
        </div>
      </div>
      <br>

      <div class="bg-white border-panel">
        <div style="font-weight: normal;">
          <div class="col q-pa-md">
            <div class="row q-col-gutter-xs" style="padding: 2%;">
              <div class="col-xs-12 col-sm-6">
                <q-select
                  :readonly="shipment.fields.status !== 'NUEVO'"
                  color="dark"
                  bg-color="secondary"
                  filled
                  v-model="detail.fields.product"
                  :options="productShipmentDetailOptions"
                  label="Producto"
                  :rules="detailProductRules"
                  @input="onSelectProduct()"
                >
                  <template v-slot:prepend>
                    <q-icon name="settings" />
                  </template>
                </q-select>
              </div>
              <div class="col-xs-12 col-sm-2 text-center">
                <q-input
                  color="dark"
                  bg-color="secondary"
                  filled
                  :readonly="detail.fields.qty === null"
                  v-model="detail.fields.qty"
                  :error="$v.detail.fields.qty.$error"
                  label="Cantidad"
                  :rules="detailQtyRules"
                >
                  <template v-slot:prepend>
                    <q-icon name="scatter_plot" />
                  </template>
                </q-input>
              </div>
              <div class="col-xs-12 col-sm-4 text-center">
                <q-input
                  color="dark"
                  bg-color="secondary"
                  filled
                  v-model="detail.fields.observation"
                  label="Observaciones"
                >
                  <template v-slot:prepend>
                    <q-icon name="fas fa-eye" />
                  </template>
                </q-input>
              </div>
              <div class="col-xs-12 col-sm-12 pull-right">
                <!--<q-btn color="primary" style="margin-right: 5px;" icon="fas fa-qrcode" label="Imprimir QR" @click="generateQRCode()" v-if="details.length > 0" />-->
                  <q-btn v-if="shipment.fields.status == 'NUEVO'" color="blue" style="margin-right: 5px;" icon="add" label="Agregar todo" @click="addDetailAll()"/>
                <q-btn v-if="shipment.fields.status == 'NUEVO'" color="positive" style="margin-right: 5px;" icon="add" label="Agregar" @click="addDetail()"/>
            </div>
            </div>
            <div class="q-col-gutter-xs" style="padding: 2%;">
              <q-table
                flat
                bordered
                :data="details"
                :columns="detailsColumns"
                row-key="name"
                :pagination.sync="pagination"
              >
                <template v-slot:body="props">
                  <q-tr :props="props">
                    <q-td key="code" style="text-align: center; width: 10%;" :props="props">{{ props.row.code }} </q-td>
                    <q-td key="product" style="text-align: left; width: 25%;" :props="props">{{ props.row.product }}</q-td>
                    <q-td key="qty" style="text-align: center; width: 10%;" :props="props">{{ $formatNumberThree(props.row.product_shipment_number) }}</q-td>
                    <q-td key="receive_date" style="width: 10%;" :props="props">{{ props.row.receive_date }}</q-td>
                    <q-td key="receive_time" style="width: 10%;" :props="props">{{ props.row.receive_time }}</q-td>
                    <q-td key="observation" style="text-align: left; width: 50%;" :props="props">{{ props.row.observation }}</q-td>
                    <q-td key="actions" style="width: 10%;" :props="props">
                      <q-btn v-if="shipment.fields.status == 'NUEVO'" color="negative" icon="fas fa-trash-alt" flat @click.native="removeDetail(props.row.id)" size="10px">
                        <q-tooltip content-class="bg-red">Eliminar</q-tooltip>
                      </q-btn>
                    </q-td>
                  </q-tr>
                </template>
              </q-table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <q-dialog v-model="shipmentDetailsModal">
      <q-card>
        <q-card-section>
          <q-toolbar>
            <q-toolbar-title>Saco {{ detailToEdit.fields.id }}</q-toolbar-title>
            <q-btn flat v-close-popup round dense icon="close" />
          </q-toolbar>
        </q-card-section>

        <q-card-section>
          <div class="row bg-white border-panel">
            <div class="col q-pa-md">
              <div class="row q-col-gutter-xs">
                <div class="col-sm-6">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="detailToEdit.fields.product"
                    label="Producto"
                    disable
                  >
                    <template v-slot:prepend>
                      <q-icon name="emoji_objects" />
                    </template>
                  </q-input>
                </div>
                <div class="col-sm-6">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="detailToEdit.fields.qty"
                    :error="$v.detailToEdit.fields.qty.$error"
                    label="Peso"
                    suffix="Kg."
                    :rules="detailToEditQtyRules"
                    @input="v => { detailToEdit.fields.qty = v.replace(/[^0-9\\.]/g, '') }"
                    :disable="shipment.fields.status !== 'ANALIZADO'"
                  >
                    <template v-slot:prepend>
                      <q-icon name="fas fa-weight-hanging" />
                    </template>
                  </q-input>
                </div>
              </div>
            </div>
          </div>
        </q-card-section>
        <q-card-actions align="right" class="text-primary" v-if="shipment.fields.status === 'ANALIZADO'">
          <q-btn flat label="Cancelar" v-close-popup />
          <q-btn flat label="Registrar peso" @click="updateDetail()" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import api from '../../commons/api.js'
const { required, decimal, maxLength, minValue } = require('vuelidate/lib/validators')

export default {
  name: 'EditShipment',
  validations: {
    shipment: {
      fields: {
        status: { required, maxLength: maxLength(10) },
        receive_date: { required },
        receive_time: { required }
      }
    },
    detail: {
      fields: {
        product: { required },
        qty: { required }
      }
    },
    detailToEdit: {
      fields: {
        product: { required },
        qty: { required, decimal, minValue: minValue(1) }
      }
    },
    sampling: {
      fields: {
        status: { required, maxLength: maxLength(10) },
        product: { required },
        humidity: { decimal },
        dirty: { decimal },
        metals: { decimal },
        recicled: { decimal },
        pvc: { decimal },
        sifting: { decimal }
      }
    }
  },
  data () {
    return {
      prueba: null,
      myLocale: {
        /* starting with Sunday */
        days: 'Domingo_Lunes_Martes_Miércoles_Jueves_Viernes_Sábado'.split('_'),
        daysShort: 'Dom_Lun_Mar_Mié_Jue_Vie_Sáb'.split('_'),
        months: 'Enero_Febrero_Marzo_Abril_Mayo_Junio_Julio_Agosto_Septiembre_Octubre_Noviembre_Diciembre'.split('_'),
        monthsShort: 'Ene_Feb_Mar_Abr_May_Jun_Jul_Ago_Sep_Oct_Nov_Dic'.split('_'),
        firstDayOfWeek: 1
      },
      formatter: new Intl.NumberFormat('en-US'),
      currentTab: null,
      shipment: {
        fields: {
          id: null,
          status: null,
          receive_date: null,
          receive_time: null,
          order_id: null,
          serial: null
        }
      },
      detail: {
        fields: {
          product: null,
          qty: null
        }
      },
      detailToEdit: {
        fields: {
          id: null,
          product: null,
          qty: null
        }
      },
      sampling: {
        fields: {
          status: 'NUEVO',
          product: { value: null, label: 'Seleccione el producto' },
          humidity: null,
          dirty: null,
          metals: null,
          recicled: null,
          pvc: null,
          recommendations: null,
          sifting: null
        }
      },
      pagination: {
        descending: false,
        rowsPerPage: 25
      },
      detailsColumns: [
        { name: 'code', align: 'center', label: 'CÓDIGO', field: 'code', style: 'width: 10%', sortable: true },
        { name: 'product', align: 'left', label: 'PRODUCTO', field: 'product', style: 'width: 25%', sortable: true },
        { name: 'qty', align: 'center', label: 'CANTIDAD', field: 'qty', style: 'width: 10%', sortable: true },
        { name: 'receive_date', align: 'left', label: 'FECHA', field: 'receive_date', style: 'width: 10%', sortable: true },
        { name: 'receive_time', align: 'left', label: 'HORA', field: 'receive_time', style: 'width: 10%', sortable: true },
        { name: 'observation', align: 'center', label: 'OBSERVACIONES', field: 'observation', style: 'width: 25%', sortable: true },
        { name: 'actions', align: 'center', label: 'ACCIONES', field: 'actions', style: 'width: 10%', sortable: false }
      ],
      samplingsColumns: [
        { name: 'product', align: 'center', label: 'PRODUCTO', field: 'product', style: 'width: 20%', sortable: true },
        { name: 'humidity', align: 'center', label: 'Humedad (%)', field: 'humidity', style: 'width: 10%', sortable: true },
        { name: 'metals', align: 'center', label: 'Metales (PPM)', field: 'metals', style: 'width: 10%', sortable: true },
        { name: 'recicled', align: 'center', label: 'Degradado (PPM)', field: 'recicled', style: 'width: 10%', sortable: true },
        { name: 'pvc', align: 'center', label: 'PVC (PPM)', field: 'pvc', style: 'width: 10%', sortable: true },
        { name: 'dirty', align: 'center', label: 'Otros (PPM)', field: 'dirty', style: 'width: 10%', sortable: true },
        { name: 'sifting', align: 'center', label: 'Tamizado (%)', field: 'sifting', style: 'width: 10%', sortable: true },
        { name: 'status', align: 'center', label: 'Estatus', field: 'status', style: 'width: 10%', sortable: true },
        { name: 'actions', align: 'center', label: 'Acciones', field: 'actions', style: 'width: 10%', sortable: false }
      ],
      details: [],
      samplings: [],
      productShipmentDetailOptions: [],
      unitOptions: [{ value: 1, label: 'KILOGRAMO' }],
      productSamplingOptions: [],
      detailIdToEdit: null,
      samplingIdToEdit: null,
      shipmentDetailsModal: false,
      loadingSendingMailBtn: false,
      totalQty: 0
    }
  },
  computed: {
    canExecute () {
      if (this.details.length > 0 && this.shipment.fields.status === 'NUEVO') {
        return true
      }
      return false
    },
    shipmentReceiveDateRules (val) {
      return [
        val => (this.$v.shipment.fields.receive_date.required) || 'El campo Fecha de recepción es requerido.'
      ]
    },
    shipmentReceiveTimeRules (val) {
      return [
        val => (this.$v.shipment.fields.receive_time.required) || 'El campo Hora de recepción es requerido.'
      ]
    },
    shipmentStatusRules (val) {
      return [
        val => (this.$v.shipment.fields.status.required) || 'El campo Estatus es requerido.',
        val => (this.$v.shipment.fields.status.maxLength) || 'El campo Estatus no debe exceder los 10 dígitos.'
      ]
    },
    detailProductRules (val) {
      return [
        val => this.$v.detail.fields.product.required || 'El campo Producto es requerido.'
      ]
    },
    detailQtyRules (val) {
      return [
        val => (this.$v.detail.fields.qty.required) || 'El campo Cantidad es requerido.'
      ]
    },
    detailToEditQtyRules (val) {
      return [
        val => (this.$v.detailToEdit.fields.qty.required) || 'El campo Peso es requerido.',
        val => (this.$v.detailToEdit.fields.qty.decimal) || 'El campo Peso debe ser numérico.',
        val => (this.$v.detailToEdit.fields.qty.minValue) || 'El campo Peso debe ser positivo.'
      ]
    },
    samplingProductRules (val) {
      return [
        val => this.$v.sampling.fields.product.required || 'El campo Producto es requerido.'
      ]
    },
    samplingHumidityRules (val) {
      return [
        val => (this.$v.sampling.fields.humidity.decimal) || 'El campo Humedad debe ser numérico.'
      ]
    },
    samplingMetalsRules (val) {
      return [
        val => (this.$v.sampling.fields.metals.decimal) || 'El campo Metales debe ser numérico.'
      ]
    },
    samplingRecicledRules (val) {
      return [
        val => (this.$v.sampling.fields.recicled.decimal) || 'El campo Degradado debe ser numérico.'
      ]
    },
    samplingPvcRules (val) {
      return [
        val => (this.$v.sampling.fields.pvc.decimal) || 'El campo PVC debe ser numérico.'
      ]
    },
    samplingDirtyRules (val) {
      return [
        val => (this.$v.sampling.fields.dirty.decimal) || 'El campo Otros debe ser numérico.'
      ]
    },
    samplingSiftingRules (val) {
      return [
        val => (this.$v.sampling.fields.sifting.decimal) || 'El campo Tamizado debe ser numérico.'
      ]
    },
    currentWeight () {
      let weight = 0
      for (let i = 0; i < this.details.length; i++) {
        if (Number(this.details[i].id) === Number(this.detailToEdit.fields.id)) {
          weight += Number(this.detailToEdit.fields.qty)
        } else {
          weight += Number(this.details[i].qty)
        }
      }
      return weight
    }
  },
  beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(2) && !this.$store.getters['users/rol'] === 22) {
      this.$router.push('/')
    }
  },
  mounted () {
    this.fetchFromServer()
    /* this.getDetails()
    this.getProducts() */
  },
  methods: {
    getDetails () {
      const id = this.$route.params.id
      api.get(`/shipment-details/shipment/${id}`).then(({ data }) => {
        console.log('Entre')
        console.log(data)
        this.details = data.shipmentDetails
        console.log('No pude entrar')
        this.currentTab = 'jumbos'
        this.$q.loading.hide()
      })
    },
    getProducts () {
      api.get(`/products/options/purchase-order/${this.$route.params.id}`).then(({ data }) => {
        console.log('Mis products')
        console.log(data)
        // this.productSamplingOptions = data.options
        this.productShipmentDetailOptions = data.options
        if (this.$route.params.detailId) {
          const detail = this.details.filter(d => Number(d.id) === Number(this.$route.params.detailId))
          if (detail.length > 0) {
            this.editDetail(detail[0])
          }
        }
      })
    },
    formatPrice (value) {
      const val = (value / 1).toFixed(1).replace('.', ',')
      return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')
    },
    fetchFromServer () {
      this.$q.loading.show()
      const id = this.$route.params.id
      console.log(this.$route.params)
      api.get(`/shipments/${id}`).then(({ data }) => {
        if (!data.shipment) {
          this.$router.push('/purchase-orders')
        } else {
          if (data.shipment.status !== 'ANALIZADO' && !this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(2) && !this.$store.getters['users/rol'] === 22) {
            this.$router.push('/')
          }
          this.shipment.fields = data.shipment
          api.get(`/products/options/purchase-order/${this.shipment.fields.order_id}`).then(({ data }) => {
            this.productSamplingOptions = this.productShipmentDetailOptions = data.options
            if (this.$route.params.detailId) {
              const detail = this.details.filter(d => Number(d.id) === Number(this.$route.params.detailId))
              if (detail.length > 0) {
                this.editDetail(detail[0])
              }
            }
            api.get(`/shipment-details/shipment/${this.shipment.fields.id}`).then(({ data }) => {
              this.details = data.shipmentDetails
              this.currentTab = 'jumbos'
              this.$q.loading.hide()
            })
          })
        }
      })
    },
    updateShipment () {
      this.$v.shipment.fields.$reset()
      this.$v.shipment.fields.$touch()
      if (this.$v.shipment.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      if (this.shipment.fields.receive_date == null) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, seleccione la fecha de recepción.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = { ...this.shipment.fields }
      params.receive_date = this.invertDate(this.shipment.fields.receive_date)
      api.put(`/shipments/${params.id}`, params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          const id = this.$route.params.id
          api.get(`/shipments/${id}`).then(({ data }) => {
            this.shipment.fields = data.shipment
            this.shipment.fields.receive_date = this.shipment.fields.receive_date.split('-').join('/')
            this.$q.loading.hide()
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    invertDate (date) {
      if (date !== null) {
        var info = date.split('/').reverse().join('-')
      }
      return info
    },
    sendShipmentPdfToSupplier () {
      this.loadingSendingMailBtn = true
      api.get(`/shipments/${this.$route.params.id}/send-mail`).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        this.loadingSendingMailBtn = false
      })
    },
    updateDetail () {
      this.$v.detailToEdit.fields.$reset()
      this.$v.detailToEdit.fields.$touch()
      if (this.$v.detailToEdit.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      if (this.detailToEdit.fields.qty == null || isNaN(this.detailToEdit.fields.qty) || this.detailToEdit.fields.qty < 1) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, ingrese un peso válido.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = []
      params.qty = Number(this.detailToEdit.fields.qty)
      api.put(`/shipment-details/${this.detailToEdit.fields.id}`, params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          api.get(`/shipment-details/shipment/${this.shipment.fields.id}`).then(({ data }) => {
            this.details = data.shipmentDetails
            api.get(`/products/options/shipment/${this.shipment.fields.id}`).then(({ data }) => {
              this.productSamplingOptions = data.options
              this.shipmentDetailsModal = false
              this.$q.loading.hide()
            })
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    addDetailAll () {
      console.log(this.productSamplingOptions)
      this.$q.loading.show()
      const params = []
      // params.products = this.productSamplingOptions
      params.shipment_id = Number(this.shipment.fields.id)
      params.shipment = this.shipment.fields.order_id
      console.log(params)
      api.post('/shipment-details/addAll', params).then(({ data }) => {
        console.log(data)
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.detail.fields.product = { value: null, label: 'Seleccione el producto' }
          this.detail.fields.qty = null
          this.detail.fields.observation = null
          api.get(`/products/options/purchase-order/${this.shipment.fields.order_id}`).then(({ data }) => {
            this.productSamplingOptions = this.productShipmentDetailOptions = data.options
            if (this.$route.params.detailId) {
              const detail = this.details.filter(d => Number(d.id) === Number(this.$route.params.detailId))
              if (detail.length > 0) {
                this.editDetail(detail[0])
              }
            }
            api.get(`/shipment-details/shipment/${this.shipment.fields.id}`).then(({ data }) => {
              this.details = data.shipmentDetails
              api.get(`/products/options/shipment/${this.shipment.fields.id}`).then(({ data }) => {
                this.productSamplingOptions = data.options
                this.$q.loading.hide()
              })
            })
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    addDetail () {
      this.$v.detail.fields.$reset()
      this.$v.detail.fields.$touch()
      if (this.$v.detail.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      if (this.detail.fields.product.value == null) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, seleccione el producto.',
          persistent: true
        })
        return false
      }
      console.log(this.detail.fields.qty + ' <= ' + '0' + ' || ' + this.detail.fields.qty + ' > ' + this.totalQty)
      if (Number(this.detail.fields.qty) <= 0 || Number(this.detail.fields.qty) > Number(this.totalQty)) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, ingrese una cantidad válida.',
          persistent: true
        })
        this.detail.fields.qty = this.totalQty
        return false
      }
      if (this.detail.fields.product.value == null) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, seleccione la unidad.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = []
      params.product_id = Number({ ...this.detail.fields }.product.value)
      params.shipment_id = Number(this.shipment.fields.id)
      params.qty = Number({ ...this.detail.fields }.qty)
      params.observation = { ...this.detail.fields }.observation
      console.log(params)
      api.post('/shipment-details', params).then(({ data }) => {
        console.log(data)
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.detail.fields.product = { value: null, label: 'Seleccione el producto' }
          this.detail.fields.qty = null
          this.detail.fields.observation = null
          api.get(`/products/options/purchase-order/${this.shipment.fields.order_id}`).then(({ data }) => {
            this.productSamplingOptions = this.productShipmentDetailOptions = data.options
            if (this.$route.params.detailId) {
              const detail = this.details.filter(d => Number(d.id) === Number(this.$route.params.detailId))
              if (detail.length > 0) {
                this.editDetail(detail[0])
              }
            }
            api.get(`/shipment-details/shipment/${this.shipment.fields.id}`).then(({ data }) => {
              this.details = data.shipmentDetails
              api.get(`/products/options/shipment/${this.shipment.fields.id}`).then(({ data }) => {
                this.productSamplingOptions = data.options
                this.$q.loading.hide()
              })
            })
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    editDetail (detail) {
      this.detailToEdit.fields.id = detail.id
      this.detailToEdit.fields.product = detail.product
      this.detailToEdit.fields.qty = detail.qty
      this.shipmentDetailsModal = true
    },
    removeDetail (id) {
      this.$q.loading.show()
      api.delete(`/shipment-details/${id}`).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.detail.fields.product = { value: null, label: 'Seleccione el producto' }
          this.detail.fields.qty = null
          this.detail.fields.observation = null
          api.get(`/products/options/purchase-order/${this.shipment.fields.order_id}`).then(({ data }) => {
            this.productSamplingOptions = this.productShipmentDetailOptions = data.options
            if (this.$route.params.detailId) {
              const detail = this.details.filter(d => Number(d.id) === Number(this.$route.params.detailId))
              if (detail.length > 0) {
                this.editDetail(detail[0])
              }
            }
            api.get(`/shipment-details/shipment/${this.shipment.fields.id}`).then(({ data }) => {
              this.details = data.shipmentDetails
              this.currentTab = 'jumbos'
              this.$q.loading.hide()
            })
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    analyzed () {
      this.$q.loading.show()
      const id = this.$route.params.id
      api.put(`/shipments/analyzed/${id}`).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          const id = this.$route.params.id
          api.get(`/shipments/${id}`).then(({ data }) => {
            this.shipment.fields = data.shipment
            this.shipment.fields.receive_date = this.shipment.fields.receive_date.split('-').join('/')
            this.$q.loading.hide()
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    reject () {
      this.$q.loading.show()
      const id = this.$route.params.id
      api.put(`/shipments/reject/${id}`).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          const id = this.$route.params.id
          api.get(`/shipments/${id}`).then(({ data }) => {
            this.shipment.fields = data.shipment
            this.shipment.fields.receive_date = this.shipment.fields.receive_date.split('-').join('/')
            this.$q.loading.hide()
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    updateSampling () {
      this.$v.sampling.fields.$reset()
      this.$v.sampling.fields.$touch()
      if (this.$v.sampling.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      if (this.sampling.fields.product.value == null) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, seleccione el producto.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      let samplingErrorsMessage = ''
      if (Number(this.sampling.fields.humidity) > 1) {
        samplingErrorsMessage += 'Exceso de Humedad (Límite 1%)'
      }
      if (Number(this.sampling.fields.metals) > 20) {
        samplingErrorsMessage += (samplingErrorsMessage.length > 0) ? ', Exceso de Metales (Límite 20 PPM)' : 'Exceso de Metales (Límite 20 PPM)'
      }
      if (Number(this.sampling.fields.recicled) > 5000) {
        samplingErrorsMessage += (samplingErrorsMessage.length > 0) ? ', Exceso de Degradado (Límite 5000 PPM)' : 'Exceso de Degradado (Límite 5000 PPM)'
      }
      if (Number(this.sampling.fields.pvc) > 100) {
        samplingErrorsMessage += (samplingErrorsMessage.length > 0) ? ', Exceso de PVC (Límite 100 PPM)' : 'Exceso de PVC (Límite 100 PPM)'
      }
      if (Number(this.sampling.fields.dirty) > 200) {
        samplingErrorsMessage += (samplingErrorsMessage.length > 0) ? ', Exceso de Otros (Límite 200 PPM)' : 'Exceso de Otros (Límite 200 PPM)'
      }
      if (Number(this.sampling.fields.sifting) > 2) {
        samplingErrorsMessage += (samplingErrorsMessage.length > 0) ? ', Exceso de Tamizado (Límite 2%)' : 'Exceso de Tamizado (Límite 2%)'
      }
      this.$q.loading.hide()
      if (samplingErrorsMessage.length > 0) {
        samplingErrorsMessage += '.'
        this.$q.dialog({
          title: 'Advertencia',
          message: samplingErrorsMessage,
          cancel: true,
          persistent: true
        }).onOk(data => {
          this.$q.loading.show()
          const params = { ...this.sampling.fields }
          params.product_id = Number(params.product.value)
          params.shipment_id = Number(this.shipment.fields.id)
          api.put(`/samplings/${this.samplingIdToEdit}`, params).then(({ data }) => {
            this.$q.notify({
              message: data.message.content,
              position: 'top',
              color: (data.result ? 'positive' : 'warning')
            })
            if (data.result) {
              this.cancelEditSampling()
              api.get(`/samplings/shipment/${this.shipment.fields.id}`).then(({ data }) => {
                this.samplings = data.samplings
                this.$q.loading.hide()
              })
            } else {
              this.$q.loading.hide()
            }
          })
        }).onCancel(() => {
          return false
        })
      } else {
        this.$q.loading.show()
        const params = { ...this.sampling.fields }
        params.product_id = Number(params.product.value)
        params.shipment_id = Number(this.shipment.fields.id)
        api.put(`/samplings/${this.samplingIdToEdit}`, params).then(({ data }) => {
          this.$q.notify({
            message: data.message.content,
            position: 'top',
            color: (data.result ? 'positive' : 'warning')
          })
          if (data.result) {
            this.cancelEditSampling()
            api.get(`/samplings/shipment/${this.shipment.fields.id}`).then(({ data }) => {
              this.samplings = data.samplings
              this.$q.loading.hide()
            })
          } else {
            this.$q.loading.hide()
          }
        })
      }
    },
    cancelEditSampling () {
      this.samplingIdToEdit = null
      this.sampling.fields.status = 'NUEVO'
      this.sampling.fields.product = { value: null, label: 'Seleccione el producto' }
      this.sampling.fields.humidity = null
      this.sampling.fields.dirty = null
      this.sampling.fields.metals = null
      this.sampling.fields.recicled = null
      this.sampling.fields.pvc = null
      this.sampling.fields.recommendations = null
      this.sampling.fields.sifting = null
    },
    addSampling () {
      this.$v.sampling.fields.$reset()
      this.$v.sampling.fields.$touch()
      if (this.$v.sampling.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      if (this.sampling.fields.product.value == null) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, seleccione el producto.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      let samplingErrorsMessage = ''
      if (Number(this.sampling.fields.humidity) > 1) {
        samplingErrorsMessage += 'Exceso de Humedad (Límite 1%)'
      }
      if (Number(this.sampling.fields.metals) > 20) {
        samplingErrorsMessage += (samplingErrorsMessage.length > 0) ? ', Exceso de Metales (Límite 20 PPM)' : 'Exceso de Metales (Límite 20 PPM)'
      }
      if (Number(this.sampling.fields.recicled) > 5000) {
        samplingErrorsMessage += (samplingErrorsMessage.length > 0) ? ', Exceso de Degradado (Límite 5000 PPM)' : 'Exceso de Degradado (Límite 5000 PPM)'
      }
      if (Number(this.sampling.fields.pvc) > 100) {
        samplingErrorsMessage += (samplingErrorsMessage.length > 0) ? ', Exceso de PVC (Límite 100 PPM)' : 'Exceso de PVC (Límite 100 PPM)'
      }
      if (Number(this.sampling.fields.dirty) > 200) {
        samplingErrorsMessage += (samplingErrorsMessage.length > 0) ? ', Exceso de Otros (Límite 200 PPM)' : 'Exceso de Otros (Límite 200 PPM)'
      }
      if (Number(this.sampling.fields.sifting) > 2) {
        samplingErrorsMessage += (samplingErrorsMessage.length > 0) ? ', Exceso de Tamizado (Límite 2%)' : 'Exceso de Tamizado (Límite 2%)'
      }
      this.$q.loading.hide()
      if (samplingErrorsMessage.length > 0) {
        samplingErrorsMessage += '.'
        this.$q.dialog({
          title: 'Advertencia',
          message: samplingErrorsMessage,
          cancel: true,
          persistent: true
        }).onOk(data => {
          this.$q.loading.show()
          const params = { ...this.sampling.fields }
          params.product_id = Number(params.product.value)
          params.shipment_id = Number(this.shipment.fields.id)
          api.post('/samplings', params).then(({ data }) => {
            this.$q.notify({
              message: data.message.content,
              position: 'top',
              color: (data.result ? 'positive' : 'warning')
            })
            if (data.result) {
              api.get(`/shipments/${this.$route.params.id}`).then(({ data }) => {
                this.shipment.fields = data.shipment
                this.shipment.fields.receive_date = this.shipment.fields.receive_date.split('-').join('/')
                this.sampling.fields.status = 'NUEVO'
                this.sampling.fields.product = { value: null, label: 'Seleccione el producto' }
                this.sampling.fields.humidity = null
                this.sampling.fields.dirty = null
                this.sampling.fields.metals = null
                this.sampling.fields.recicled = null
                this.sampling.fields.pvc = null
                this.sampling.fields.recommendations = null
                this.sampling.fields.sifting = null
                api.get(`/samplings/shipment/${this.shipment.fields.id}`).then(({ data }) => {
                  this.samplings = data.samplings
                  this.$q.loading.hide()
                })
              })
            } else {
              this.$q.loading.hide()
            }
          })
        }).onCancel(() => {
          return false
        })
      } else {
        this.$q.loading.show()
        const params = { ...this.sampling.fields }
        params.product_id = Number(params.product.value)
        params.shipment_id = Number(this.shipment.fields.id)
        api.post('/samplings', params).then(({ data }) => {
          this.$q.notify({
            message: data.message.content,
            position: 'top',
            color: (data.result ? 'positive' : 'warning')
          })
          if (data.result) {
            api.get(`/shipments/${this.$route.params.id}`).then(({ data }) => {
              this.shipment.fields = data.shipment
              this.shipment.fields.receive_date = this.shipment.fields.receive_date.split('-').join('/')
              this.sampling.fields.status = 'NUEVO'
              this.sampling.fields.product = { value: null, label: 'Seleccione el producto' }
              this.sampling.fields.humidity = null
              this.sampling.fields.dirty = null
              this.sampling.fields.metals = null
              this.sampling.fields.recicled = null
              this.sampling.fields.pvc = null
              this.sampling.fields.recommendations = null
              this.sampling.fields.sifting = null
              api.get(`/samplings/shipment/${this.shipment.fields.id}`).then(({ data }) => {
                this.samplings = data.samplings
                this.$q.loading.hide()
              })
            })
          } else {
            this.$q.loading.hide()
          }
        })
      }
    },
    validateHumity () {
      if (parseFloat(this.sampling.fields.humidity) < 0) {
        this.sampling.fields.humidity = null
      }
    },
    validateMetals () {
      if (parseFloat(this.sampling.fields.metals) < 0) {
        this.sampling.fields.metals = null
      }
    },
    validateRecicled () {
      if (parseFloat(this.sampling.fields.recicled) < 0) {
        this.sampling.fields.recicled = null
      }
    },
    validatePvc () {
      if (parseFloat(this.sampling.fields.pvc) < 0) {
        this.sampling.fields.pvc = null
      }
    },
    validateDirty () {
      if (parseFloat(this.sampling.fields.dirty) < 0) {
        this.sampling.fields.dirty = null
      }
    },
    validateSifting () {
      if (parseFloat(this.sampling.fields.sifting) < 0) {
        this.sampling.fields.sifting = null
      }
    },
    editSampling (sampling) {
      this.samplingIdToEdit = sampling.id
      this.sampling.fields.status = sampling.status
      this.sampling.fields.product = { value: sampling.product_id, label: sampling.product }
      this.sampling.fields.humidity = sampling.humidity
      this.sampling.fields.dirty = sampling.dirty
      this.sampling.fields.metals = sampling.metals
      this.sampling.fields.recicled = sampling.recicled
      this.sampling.fields.pvc = sampling.pvc
      this.sampling.fields.recommendations = sampling.recommendations
      this.sampling.fields.sifting = sampling.sifting
    },
    removeSampling (sampling) {
      this.$q.loading.show()
      api.delete(`/samplings/${sampling.id}`).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          api.get(`/samplings/shipment/${this.shipment.fields.id}`).then(({ data }) => {
            this.samplings = data.samplings
            this.$q.loading.hide()
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    onSelectProduct () {
      api.get(`/shipment-details/qtyProducts/${this.shipment.fields.order_id}/${this.detail.fields.product.value}`).then(({ data }) => {
        this.detail.fields.qty = (this.detail.fields.product.qty - (data.qty !== false ? data.qty.total : 0)).toFixed(2)
        this.totalQty = this.detail.fields.qty
      })
    },
    generateQRCode () {
      const shipmentId = Number(this.shipment.fields.id)
      const uri = process.env.API + `shipments/pdf-qr/${shipmentId}`
      window.open(uri, '_blank')
    },
    generateDetailQRCode (detail) {
      const shipmentDetailId = Number(detail.id)
      const uri = process.env.API + `shipment-details/pdf-qr/${shipmentDetailId}`
      window.open(uri, '_blank')
    },
    saveStorageEntry () {
      this.$q.loading.show()
      api.put(`/shipments/${this.$route.params.id}/entry`, []).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          const id = this.$route.params.id
          api.get(`/shipments/${id}`).then(({ data }) => {
            this.shipment.fields = data.shipment
            this.shipment.fields.receive_date = this.shipment.fields.receive_date.split('-').join('/')
            this.$q.loading.hide()
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    generateSamplingPdf (sampling) {
      const uri = process.env.API + `samplings/pdf/${sampling.id}`
      window.open(uri, '_blank')
    }
  }
}
</script>

<style>
</style>

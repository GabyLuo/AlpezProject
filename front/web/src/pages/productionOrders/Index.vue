<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-6" style="font-size: 20px">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Ordenes de producción " />
            </q-breadcrumbs>
          </div>
        </div>
        <div class="col-xs-6 col-md-6 pull-right">
          <div class="q-pa-sm q-gutter-sm">
                <q-btn class="bg-primary" style="color: white" icon="add" label="Nuevo" @click.native="$router.push('/production-orders/new')" />
          </div>
        </div>
      </div>
    </div>
    <div class="q-pa-md bg-grey-3">
      <div class="row bg-white border-panel">
        <div class="col q-pa-md">
          <div class="row q-mb-sm">
          </div>
          <q-table
            flat
            bordered
            :data="data"
            :columns="columns"
            row-key="order_number"
            :pagination.sync="pagination"
            :filter = "filter"
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
                <q-td key="order_number" style="width: 10%;" :props="props">{{ props.row.order_number }}</q-td>
                <q-td key="created" style="width: 10%;" :props="props">{{ props.row.created }}</q-td>
                <q-td key="production_date" style="width: 10%;" :props="props">{{ props.row.production_date }}</q-td>
                <q-td key="shipmentProgress" style="width: 10%;" :props="props">
                  <q-linear-progress stripe size="33px" :value="Number(props.row.lot_completed / props.row.lot_all)"  color="positive">
                    <q-chip dense square color="primary" text-color="white">
                      {{ shipmentsProgressLabel(Number(props.row.lot_completed / props.row.lot_all)) }}
                    </q-chip>
                  </q-linear-progress>
                </q-td>
                <q-td key="status" style="width: 10%;" :props="props">
                      <q-btn square dense :color="props.row.status === 'NUEVO' ? 'primary' : (props.row.status === 'INICIADO' ? 'blue' : (props.row.status === 'FINALIZADO' ? 'green' : (props.row.status === 'CANCELADO' ? 'negative' : (props.row.status === 'ASIGNADO' ? 'orange' : negative))))" text-color="white" size="10px" >
                        {{ props.row.status }}
                      </q-btn>
                    </q-td>
                <!--<q-td key="status" style="width: 10%;" :props="props" v-if="props.row.mostAdvancedLot">
                  <q-chip square dense :color="props.row.status == 'NUEVO' ? 'primary' : (props.row.status == 'FORMULADO' ? 'light-blue-10' : (props.row.status == 'SURTIDO' ? 'cyan-9' : (props.row.status == 'PRODUCIENDO' ? 'cyan' : (props.row.status == 'SECADO' ? 'purple' : (props.row.status == 'EXTRUDER' ? 'deep-orange' : (props.row.status == 'ESTIRADO' ? 'amber' : (props.row.status == 'RIZADO' ? 'blue-grey' : (props.row.status == 'HORNO' ? 'teal' : (props.row.status == 'EMPAQUE' ? 'warning' : (props.row.status == 'TERMINADO' ? 'positive' : (props.row.status == 'CANCELADO' ? 'negative' : 'blue')))))))))))" text-color="white" clickable @click.native="$router.push(`/lots/${props.row.mostAdvancedLot}`)">
                    {{ props.row.status }}
                  </q-chip>
                </q-td>-->
                <!--<q-td key="status" style="width: 10%;" :props="props" v-else>
                  <q-chip square dense :color="props.row.status == 'NUEVO' ? 'primary' : (props.row.status == 'FORMULADO' ? 'light-blue-10' : (props.row.status == 'SURTIDO' ? 'cyan-9' : (props.row.status == 'PRODUCIENDO' ? 'cyan' : (props.row.status == 'SECADO' ? 'purple' : (props.row.status == 'EXTRUDER' ? 'deep-orange' : (props.row.status == 'ESTIRADO' ? 'amber' : (props.row.status == 'RIZADO' ? 'blue-grey' : (props.row.status == 'HORNO' ? 'teal' : (props.row.status == 'EMPAQUE' ? 'warning' : (props.row.status == 'TERMINADO' ? 'positive' : (props.row.status == 'CANCELADO' ? 'negative' : 'blue')))))))))))" text-color="white">
                    {{ props.row.status }}
                  </q-chip>
                </q-td>-->
                <q-td key="actions" style="width: 8%;" :props="props" class="pull-left">
                  <q-btn color="primary" icon="fas fa-edit" flat @click.native="editSelectedRow(props.row.id)" size="10px">
                    <q-tooltip content-class="bg-primary">Editar</q-tooltip>
                  </q-btn>
                  <q-btn color="primary" icon="fas fa-file-pdf" flat @click.native="packingList(props.row.id)" size="10px" v-if="props.row.status == 'FINALIZADO'">
                    <q-tooltip content-class="bg-primary">Packing List</q-tooltip>
                  </q-btn>
                  <q-btn color="positive" icon="fas fa-file-pdf" flat @click.native="handiWork(props.row.id)" size="10px" v-if="props.row.status == 'FINALIZADO'">
                    <q-tooltip content-class="bg-primary">Mano de Obra</q-tooltip>
                  </q-btn>
                  <q-btn color="purple" icon="fas fa-file-pdf" flat @click.native="cost(props.row.id)" size="10px" v-if="props.row.status == 'FINALIZADO'">
                    <q-tooltip content-class="bg-primary">Costo Produción</q-tooltip>
                  </q-btn>
                  <q-btn color="negative" icon="fas fa-ban" flat @click.native="cancelSelectedRow(props.row.id)" size="10px" v-if="props.row.status == 'NUEVO'">
                    <q-tooltip content-class="bg-red">Cancelar</q-tooltip>
                  </q-btn>
                </q-td>
              </q-tr>
            </template>
          </q-table>
        </div>
      </div>
    </div>

    <q-dialog persistent>
      <q-card>
        <q-card-section class="row">
          <div class="col-xs-12 col-sm-10 text-h6">Orden de producción</div>
          <q-btn class="col-xs-12 col-sm-2 pull-right" icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-card-section>
          <q-uploader
            accept=".pdf"
            method="POST"
            hide-upload-btn
          />
        </q-card-section>
        <q-card-actions align="right" class="text-primary">
          <q-btn flat label="Subir documento" />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <q-dialog v-model="modalOrders" persistent>
                <q-card style="min-width: 80%">
                  <q-card-section>
                    <div class="text-h6">Ordenes de producción</div>
                  </q-card-section>
                  <q-card-section>
                    <div class="row q-col-gutter-xs">
                      <div class="col-xs-12 col-sm-3">
            <q-select
              color="dark"
              bg-color="secondary"
              filled
              v-model="ordera.fields.fecha_a"
              mask="date"
              label="Fecha programada"
              disable
            >
              <template v-slot:prepend>
                <q-icon name="event" />
              </template>
              <q-popup-proxy
                ref="orderFieldsCreationDateRef"
                transition-show="scale"
                transition-hide="scale"
              >
                <div class="col-sm-12">
                  <q-date
                    v-model="ordera.fields.fecha_a"
                    @input="() => $refs.orderFieldsCreationDateRef.hide()"
                    today-btn
                  />
                </div>
              </q-popup-proxy>
            </q-select>
          </div>
                       <div class="col-xs-12 col-sm-3">
                  <q-select
                  filled
                  dark
                  color="white"
                  bg-color="secondary"
                  v-model="ordera.fields.a"
                  :error="$v.ordera.fields.a.$error"
                  label="Producto"
                  :options="options2"
                  use-input
                  hide-selected
                  fill-input
                  input-debounce="0"
                  @input="getUnit(ordera.fields.a, 'a')"
                  @filter="filterProduct"
                  hint="Basic autocomplete"
                  emit-value
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
              <div class="col-xs-12 col-sm-3">
                  <q-select
                  color="dark"
                  bg-color="secondary"
                  filled
                  v-model="ordera.fields.unit"
                  label="Unidad"
                  disable
                  >
                </q-select>
              </div>
              <div class="col-xs-12 col-sm-3">
                <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="ordera.fields.ca"
                :error="$v.ordera.fields.ca.$error"
                label="Cantidad"
                >
                <template v-slot:prepend>
                  <q-icon name="fas fa-hashtag" />
                </template>
              </q-input>
            </div>
            </div>
                <div class="row q-col-gutter-xs">
                  <div class="col-xs-12 col-sm-3">
            <q-select
              color="dark"
              bg-color="secondary"
              filled
              v-model="orderb.fields.fecha_b"
              mask="date"
              label="Fecha programada"
              disable
            >
              <template v-slot:prepend>
                <q-icon name="event" />
              </template>
              <q-popup-proxy
                ref="orderFieldsCreationDateRef"
                transition-show="scale"
                transition-hide="scale"
              >
                <div class="col-sm-12">
                  <q-date
                    v-model="orderb.fields.fecha_b"
                    @input="() => $refs.orderFieldsCreationDateRef.hide()"
                    today-btn
                  />
                </div>
              </q-popup-proxy>
            </q-select>
          </div>
                       <div class="col-xs-12 col-sm-3">
                  <q-select
                  filled
                  dark
                  color="white"
                  bg-color="secondary"
                  v-model="orderb.fields.b"
                  :error="$v.orderb.fields.b.$error"
                  label="Producto"
                  :options="options2"
                  use-input
                  hide-selected
                  fill-input
                  input-debounce="0"
                  @input="getUnit(orderb.fields.b,'b')"
                  @filter="filterProduct"
                  hint="Basic autocomplete"
                  emit-value
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
              <div class="col-xs-12 col-sm-3">
                  <q-select
                  color="dark"
                  bg-color="secondary"
                  filled
                  v-model="orderb.fields.unit"
                  label="Unidad"
                  disable
                  >
                </q-select>
              </div>
              <div class="col-xs-12 col-sm-3">
                <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="orderb.fields.cb"
                :error="$v.orderb.fields.cb.$error"
                label="Cantidad"
                >
                <template v-slot:prepend>
                  <q-icon name="fas fa-hashtag" />
                </template>
              </q-input>
            </div>
            </div>
                <div class="row q-col-gutter-xs">
                  <div class="col-xs-12 col-sm-3">
            <q-select
              color="dark"
              bg-color="secondary"
              filled
              v-model="orderc.fields.fecha_c"
              mask="date"
              label="Fecha programada"
              disable
            >
              <template v-slot:prepend>
                <q-icon name="event" />
              </template>
              <q-popup-proxy
                ref="orderFieldsCreationDateRef"
                transition-show="scale"
                transition-hide="scale"
              >
                <div class="col-sm-12">
                  <q-date
                    v-model="orderc.fields.fecha_c"
                    @input="() => $refs.orderFieldsCreationDateRef.hide()"
                    today-btn
                  />
                </div>
              </q-popup-proxy>
            </q-select>
          </div>
                       <div class="col-xs-12 col-sm-3">
                  <q-select
                  filled
                  dark
                  color="white"
                  bg-color="secondary"
                  v-model="orderc.fields.c"
                  :error="$v.orderc.fields.c.$error"
                  label="Producto"
                  :options="options2"
                  use-input
                  hide-selected
                  fill-input
                  input-debounce="0"
                  @input="getUnit(orderc.fields.c,'c')"
                  @filter="filterProduct"
                  hint="Basic autocomplete"
                  emit-value
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
              <div class="col-xs-12 col-sm-3">
                  <q-select
                  color="dark"
                  bg-color="secondary"
                  filled
                  v-model="orderc.fields.unit"
                  label="Unidad"
                  disable
                  >
                </q-select>
              </div>
              <div class="col-xs-12 col-sm-3">
                <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="orderc.fields.cc"
                :error="$v.orderc.fields.cc.$error"
                label="Cantidad"
                >
                <template v-slot:prepend>
                  <q-icon name="fas fa-hashtag" />
                </template>
              </q-input>
            </div>
            </div>
          </q-card-section>
          <q-card-actions align="right" class="text-primary">
            <q-btn label="Cancelar" color="red" v-close-popup @click.native="closeModal()"/>
            <q-btn label="Guardar" color="green" @click.native="save()" />
          </q-card-actions>
        </q-card>
        </q-dialog>

  </q-page>
</template>

<script>
import api from '../../commons/api.js'
import { date } from 'quasar'
// const { } = require('vuelidate/lib/validators')

export default {
  name: 'IndexProductionOrders',
  validations: {
    ordera: {
      fields: {
        a: { },
        ca: { },
        fecha_a: { },
        unit: { }
      }
    },
    orderb: {
      fields: {
        b: { },
        cb: { },
        fecha_b: { },
        unit: { }
      }
    },
    orderc: {
      fields: {
        c: { },
        cc: { },
        fecha_c: { },
        unit: { }
      }
    }
  },
  data () {
    return {
      modalOrders: false,
      options2: this.ProductsOptionsbyLines,
      formatter: new Intl.NumberFormat('en-US'),
      pagination: {
        sortBy: 'order_number',
        descending: true,
        rowsPerPage: 25
      },
      filter: '',
      columns: [
        { name: 'order_number', align: 'center', label: 'NÚMERO DE ORDEN', field: 'order_number', style: 'width: 10%', sortable: true },
        { name: 'created', align: 'center', label: 'FECHA DE CAPTURA', field: 'created', style: 'width: 10%', sortable: true },
        { name: 'production_date', align: 'center', label: 'FECHA PROGRAMADA', field: 'production_date', style: 'width: 10%', sortable: true },
        { name: 'shipmentProgress', align: 'center', label: 'AVANCE', field: 'shipmentProgress', style: 'width: 10%', sortable: true },
        { name: 'status', align: 'center', label: 'ESTATUS', field: 'status', style: 'width: 10%', sortable: true },
        { name: 'actions', align: 'center', label: 'ACCIONES', field: 'actions', style: 'width: 8%', sortable: false }
      ],
      data: [],
      ordera: {
        fields: {
          a: null,
          ca: null,
          fecha_a: null,
          unit: null
        }
      },
      orderb: {
        fields: {
          b: null,
          cb: null,
          fecha_b: null,
          unit: null
        }
      },
      orderc: {
        fields: {
          c: null,
          cc: null,
          fecha_c: null,
          unit: null
        }
      },
      ProductsOptionsbyLines: []
    }
  },
  computed: {
  },
  beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(5) && !this.$store.getters['users/roles'].includes(6) && !this.$store.getters['users/roles'].includes(7)) {
      this.$router.push('/')
    }
  },
  mounted () {
    this.fetchFromServer()
    this.getLinesByCategories()
    const timeStamp = Date.now()
    this.ordera.fields.fecha_a = date.formatDate(timeStamp, 'DD/MM/YYYY')
    this.orderb.fields.fecha_b = date.formatDate(timeStamp, 'DD/MM/YYYY')
    this.orderc.fields.fecha_c = date.formatDate(timeStamp, 'DD/MM/YYYY')
  },
  methods: {
    formatPrice (value) {
      const val = (value / 1).toFixed(1).replace('.', ',')
      return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')
    },
    fetchFromServer () {
      this.$q.loading.show()
      api.get('/production-orders').then(({ data }) => {
        this.data = data.orders
        this.$q.loading.hide()
      })
    },
    editSelectedRow (id) {
      this.$router.push(`/production-orders/${id}`)
    },
    cancelSelectedRow (id) {
      this.$q.loading.show()
      api.put(`/production-orders/${id}/cancel`).then(({ data }) => {
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
    shipmentsProgress (row) {
      console.log(row.lot_completed / row.lot_all)
      return Number(row.lot_completed / row.lot_al)
    },
    shipmentsProgressLabel (shipmentsProgress) {
      return (shipmentsProgress * 100).toFixed(2) + '%'
    },
    getLinesByCategories () {
      this.ProductsOptionsbyLines = []
      api.get('/products/getProductsBy').then(({ data }) => {
        this.ProductsOptionsbyLines = data.products
        this.ProductsOptionsbyLines.unshift({ value: null, label: 'NINGUNO' })
      })
    },
    filterProduct (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.options2 = this.ProductsOptionsbyLines.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    save () {
      this.$q.loading.show()
      this.$v.ordera.fields.$reset()
      this.$v.ordera.fields.$touch()
      this.$v.orderb.fields.$reset()
      this.$v.orderb.fields.$touch()
      this.$v.orderc.fields.$reset()
      this.$v.orderc.fields.$touch()
      const params = []
      const products = []
      if (this.ordera.fields.a != null && this.ordera.fields.ca) {
        products.push({ product: this.ordera.fields.a, cantidad: Number(this.ordera.fields.ca), fecha: this.ordera.fields.fecha_a, unit: this.ordera.fields.unit })
      }
      if (this.orderb.fields.b != null && this.orderb.fields.cb) {
        products.push({ product: this.orderb.fields.b, cantidad: Number(this.orderb.fields.cb), fecha: this.orderb.fields.fecha_b, unit: this.orderb.fields.unit })
      }
      if (this.orderc.fields.c != null && this.orderc.fields.cc) {
        products.push({ product: this.orderc.fields.c, cantidad: Number(this.orderc.fields.cc), fecha: this.orderc.fields.fecha_c, unit: this.orderc.fields.unit })
      }
      params.products = products
      this.modalOrders = false
      api.post('/production-orders/createorders', params).then(({ data }) => {
        if (data.result) {
          this.$q.notify({
            message: 'Ordenes de produccion creadas',
            position: 'top',
            color: 'positive'
          })
          this.closeModal()
        }
        this.fetchFromServer()
        this.$q.loading.hide()
      })
      this.modalOrders = false
    },
    getUnit (idaux, aux) {
      const id = idaux.id
      api.get(`products/${id}`).then(({ data }) => {
        if (aux === 'a') {
          this.ordera.fields.unit = { value: data.product.unit_id, label: data.product.unit }
        }
        if (aux === 'b') {
          this.orderb.fields.unit = { value: data.product.unit_id, label: data.product.unit }
        }
        if (aux === 'c') {
          this.orderc.fields.unit = { value: data.product.unit_id, label: data.product.unit }
        }
      })
    },
    closeModal () {
      this.ordera.fields.a = null
      this.orderb.fields.b = null
      this.orderc.fields.c = null
      this.ordera.fields.ca = null
      this.orderb.fields.cb = null
      this.orderc.fields.cc = null
      this.ordera.fields.unit = null
      this.orderb.fields.unit = null
      this.orderc.fields.unit = null
    },
    packingList (id) {
      const uri = process.env.API + `production-orders/packing-list/pdf/${id}`
      window.open(uri, '_blank')
    },
    handiWork (id) {
      const uri = process.env.API + `production-orders/handi-work/pdf/${id}`
      window.open(uri, '_blank')
    },
    cost (id) {
      const uri = process.env.API + `production-orders/cost/pdf/${id}`
      window.open(uri, '_blank')
    }
  }
}
</script>

<style>
</style>

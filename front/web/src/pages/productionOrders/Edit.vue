<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-6">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Ordenes de producción" to="/production-orders" />
              <q-breadcrumbs-el label="Editar" v-text="order.fields.order_number"/>
            </q-breadcrumbs>
          </div>
        </div>
        <div style="margin-top: 10px;" class="col-sm-6 pull-right" v-if="order.fields.status === 'FINALIZADO'">
          <q-btn style="margin-right: 10px" color="primary" icon="fas fa-file-pdf" label="Packing List" @click="packingList()" />
          <q-btn style="margin-right: 10px" color="positive" icon="fas fa-file-pdf" label="Mano de obra" @click="handiWork()" />
        <q-btn style="margin-right: 10px" color="purple" icon="fas fa-dollar-sign" label="Costo de producción" @click="cost()" />
        </div>
        <div style="margin-top: 10px" class="col-sm-6 pull-right" v-if="order.fields.status !== 'FINALIZADO'">
          <!--<q-btn color="primary" @click="production()" v-if="order.fields.status === 'NUEVO'" icon="save" label="PRODUCCIÓN" style="margin-right:10px " />-->
          <!-- <q-btn color="positive" @click="finalizar()" v-if="this.finalizeOrder === true && (order.fields.status === 'NUEVO' || order.fields.status == 'INICIADO')" icon="bolt" label="FINALIZAR" style="margin-right:10px " /> -->
        </div>
      </div>
    </div>

    <div class="q-pa-md bg-grey-3">
      <div class="row bg-white border-panel">
        <div class="col q-pa-md">
          <div class="row q-col-gutter-xs">
            <div class="col-xs-12 col-sm-4">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                disable
                v-model="order.fields.order_number"
                label="Número de orden"
              >
                <template v-slot:prepend>
                  <q-icon name="code" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-4">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="order.fields.production_date"
                mask="date"
                label="Fecha programada"
                :rules="creationDateRules"
                :disable="!canModifyOrder"
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
                      v-model="order.fields.production_date"
                      @input="() => $refs.orderFieldsCreationDateRef.hide()"
                      today-btn
                    />
                  </div>
                </q-popup-proxy>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-4">
              <q-input
                color="white"
                :bg-color="order.fields.status === 'NUEVO' ? 'primary' : (order.fields.status === 'INICIADO' ? 'blue' : (order.fields.status === 'FINALIZADO' ? 'green' : (order.fields.status === 'ASIGNADO' ? 'orange' : 'red')))"
                filled
                dark
                v-model="order.fields.status"
                label="Estatus"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="battery_full" />
                </template>
              </q-input>
            </div>
            <!-- <div class="col-xs-12 col-sm-4">
                        <q-select
                        color="dark"
                        bg-color="secondary"
                        filled
                        v-model="order.fields.family"
                        :error="$v.order.fields.family.$error"
                        label="Familias"
                        :rules="bomcategoryRules"
                        @input="getLinesByCategories()"
                        :options="options1"
                        use-input
                        hide-selected
                        fill-input
                        input-debounce="0"
                        @filter="filterCategory"
                        hint="Basic autocomplete"
                        emit-value
                        map-options
                        >
                        <template v-slot:prepend>
                          <q-icon name="fas fa-cubes" />
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
                  <div class="col-xs-12 col-sm-4">
                    <q-select
                    filled
                    color="dark"
                    bg-color="secondary"
                    v-model="order.fields.product"
                    :error="$v.order.fields.product.$error"
                    label="Producto"
                    :options="options2"
                    use-input
                    hide-selected
                    fill-input
                    input-debounce="0"
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
            <div class="col-xs-12 col-sm-4 text-center">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="order.fields.qty"
                :error="$v.order.fields.qty.$error"
                label="Cantidad a producir"
                :rules="qtyRules"
                @input="v => { order.fields.qty = v.replace(/[^0-9\\.]/g, '') }"
                suffix="U."
                :disable="!canModifyOrder"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-weight-hanging" />
                </template>
              </q-input>
            </div> -->
            <div class="col-xs-12 text-center">
              <q-linear-progress stripe size="33px" :value="shipmentsProgress" color="positive">
                <q-chip dense square color="secondary" text-color="white">
                  {{ shipmentsProgressLabel }}
                </q-chip>
              </q-linear-progress>
            </div>
          </div>

          <div class="row q-mb-sm q-mt-md">
            <div class="col-xs-12 col-sm-2 pull-left">
              <q-btn color="negative" icon="clear" label="Cancelar" @click="cancelOrder()" v-if="canModifyOrder" />
            </div>
            <div class="col-xs-12 col-sm-4 offset-sm-6 pull-right">
                <q-btn class="bg-primary" style="margin-right : 10px; color: white;" icon="add" label="Agregar"  @click="modalOrders = true" v-if="canModifyLots" />
                <q-btn color="positive" icon="save" label="Guardar" @click="updateOrder()" v-if="canModifyOrder" />
            </div>
          </div>
        </div>
      </div>

      <br>
      <div class="bg-white border-panel">
          <div>
            <div class="q-col-gutter-xs">
              <q-table
                flat
                bordered
                :data="lots"
                :columns="lotColumns"
                row-key="start_date"
                :pagination.sync="lotsPagination"
              >
                <template v-slot:body="props">
                  <q-tr :props="props">
                    <q-td key="lot_number" style="width: 10%;" :props="props">{{ props.row.lot_number }}</q-td>
                    <q-td key="scheduled_start_date" style="width: 15%;" :props="props">{{ props.row.scheduled_start_date }}</q-td>
                    <q-td key="product" style="text-align: left; width: 25%;" :props="props">{{ props.row.product }}</q-td>
                    <q-td key="shift" style="text-align: left; width: 15%;" :props="props">{{ props.row.shift }}</q-td>
                    <q-td key="weight" style="text-align: right; width: 5%;" :props="props">{{ props.row.weight }}</q-td>
                    <q-td key="qty_real" style="text-align: right; width: 5%;" :props="props">{{ props.row.qty_real }}</q-td>
                    <q-td key="status" style="width: 10%;" :props="props">
                      <q-btn square dense :color="props.row.status === 'NUEVO' ? 'primary' : (props.row.status === 'INICIADO' ? 'blue' : (props.row.status === 'FINALIZADO' ? 'green' : (props.row.status === 'ASIGNADO' ? 'orange' : 'red')))" text-color="white" size="10px" >
                        {{ props.row.status }}
                      </q-btn>
                    </q-td>
                    <q-td key="actions" style="width: 10%;" :props="props" v-if="order.fields.status != 'CANCELADO'" class="pull-left">
                      <q-btn color="primary" icon="remove_red_eye" flat @click.native="editLot(props.row)" size="10px" v-if="props.row.status == 'CANCELADO' || props.row.status == 'TERMINADO'">
                        <q-tooltip content-class="bg-primary">Ver</q-tooltip>
                      </q-btn>
                      <q-btn color="primary" icon="fas fa-edit" flat @click.native="editLot(props.row)" size="10px" v-else>
                        <q-tooltip content-class="bg-primary">Editar</q-tooltip>
                      </q-btn>
                      <q-btn color="negative" icon="fas fa-ban" flat @click.native="cancelLot(props.row.id)" size="10px" v-if="props.row.status == 'NUEVO'">
                        <q-tooltip content-class="bg-red">Cancelar</q-tooltip>
                      </q-btn>
                    </q-td>
                    <q-td key="actions" style="width: 10%;" :props="props" v-else>
                      <q-btn color="primary" icon="remove_red_eye" flat @click.native="editLot(props.row)" size="10px">
                        <q-tooltip content-class="bg-primary">Ver detalles</q-tooltip>
                      </q-btn>
                    </q-td>
                  </q-tr>
                </template>
              </q-table>
            </div>
          </div>
      </div>
    </div>

    <q-dialog v-model="modalOrders" persistent>
                <q-card style="min-width: 80%">
                  <q-card-section>
                    <div class="text-h6">Agregar Lote</div>
                  </q-card-section>
      <q-card-section>
        <div class="row q-col-gutter-xs">
          <div class="col-xs-12 col-sm-4">
                        <q-select
                        color="dark"
                        bg-color="secondary"
                        filled
                        v-model="order.fields.family"
                        :error="$v.order.fields.family.$error"
                        label="Categorías"
                        :rules="bomcategoryRules"
                        @input="getLinesByCategories()"
                        :options="options1"
                        use-input
                        hide-selected
                        fill-input
                        input-debounce="0"
                        @filter="filterCategory"
                        emit-value
                        map-options
                        >
                        <template v-slot:prepend>
                          <q-icon name="fas fa-cubes" />
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
                <div class="col-xs-12 col-sm-4">
                  <q-select
                  filled
                  color="dark"
                  bg-color="secondary"
                  v-model="lot.fields.product_id"
                  :error="$v.lot.fields.product_id.$error"
                  label="Producto"
                  :options="options2"
                  use-input
                  hide-selected
                  fill-input
                  input-debounce="0"
                  @input="getUnit(lot.fields.product_id)"
                  @filter="filterProduct"
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
              <div class="col-xs-12 col-sm-2">
                  <q-select
                  color="dark"
                  bg-color="secondary"
                  filled
                  v-model="lot.fields.unit"
                  label="Unidad"
                  disable
                  >
                </q-select>
              </div>
              <div class="col-xs-12 col-sm-4">
                <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="lot.fields.qty"
                :error="$v.lot.fields.qty.$error"
                label="Cantidad"
                >
                <template v-slot:prepend>
                  <q-icon name="fas fa-hashtag" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-4">
              <q-select
                emit-value
                map-options
                color="dark"
                bg-color="secondary"
                filled
                v-model="lot.fields.shift"
                :error="$v.lot.fields.shift.$error"
                :options="shiftOptions"
                label="Turno"
                :rules="shifttRules"
              >
                <template v-slot:prepend>
                  <q-icon name="brightness_6" />
                </template>
              </q-select>
            </div>
            </div>
          </q-card-section>
          <q-card-actions align="right" class="text-primary">
            <q-btn label="Cancelar" color="red" v-close-popup @click.native="closeModal()"/>
            <q-btn label="Agregar" color="green" @click.native="addLot()" />
          </q-card-actions>
        </q-card>
        </q-dialog>
  </q-page>
</template>

<script>
import api from '../../commons/api.js'
const { required, decimal } = require('vuelidate/lib/validators')

export default {
  name: 'EditProductionOrder',
  validations: {
    order: {
      fields: {
        order_number: { required },
        production_date: { required },
        product: { required },
        family: { required },
        qty: { required, decimal },
        status: { required },
        category_id: {}
      }
    },
    lot: {
      fields: {
        qty: { required, decimal },
        unit: {},
        product_id: { required },
        shift: { required }
      }
    },
    quality: {
      fields: {
        volume: { decimal },
        strength: { decimal },
        rebound: { decimal },
        spring: { decimal }
      }
    },
    bom: {
      fields: {
        category: { },
        product: { required },
        cantidad: { required }
      }
    }
  },
  data () {
    return {
      modalOrders: false,
      finalizeOrder: false,
      porcentaje: 0,
      tab: 'bom',
      options1: this.categoryOptions,
      options2: this.ProductsOptionsbyLines,
      formatter: new Intl.NumberFormat('en-US'),
      order: {
        fields: {
          id: null,
          order_number: null,
          production_date: null,
          product: null,
          qty: null,
          status: null,
          family: 1,
          category_id: null
        }
      },
      lot: {
        fields: {
          qty: null,
          unit: null,
          product_id: null,
          shift: null
        }
      },
      shiftOptions: [],
      bom: {
        fields: {
          idedit: null,
          category: null,
          product: null,
          cantidad: null
        }
      },
      columns: [
        { name: 'code_product', align: 'center', label: 'CÓDIGO PRODUCTO', field: 'code_product', sortable: false },
        { name: 'name_category', align: 'center', label: 'CATEGORÍA', field: 'name_category', sortable: false },
        { name: 'amount', align: 'center', label: 'CANTIDAD POR PIEZA', field: 'amount', sortable: false },
        { name: 'qty', align: 'center', label: 'CANTIDAD A PRODUCIR', field: 'qty', sortable: false },
        { name: 'stock', align: 'center', label: 'EXISTENCIA', field: 'stok', sortable: false },
        { name: 'total', align: 'center', label: 'TOTAL REQUERIDO', field: 'total', sortable: false }
      ],
      pagination: {
        sortBy: 'id',
        descending: false,
        rowsPerPage: 25
      },
      lots: [],
      lotColumns: [
        { name: 'lot_number', align: 'center', label: 'NÚMERO DE LOTE', field: 'lot_number', style: 'width: 10%', sortable: true },
        { name: 'scheduled_start_date', align: 'center', label: 'FECHA PROGRAMADA', field: 'scheduled_start_date', style: 'width: 15%', sortable: true },
        { name: 'product', align: 'center', label: 'PRODUCTO', field: 'product', style: 'width: 25%', sortable: true },
        { name: 'shift', align: 'center', label: 'TURNO', field: 'shift', style: 'width: 15%', sortable: true },
        { name: 'weight', align: 'center', label: 'A PRODUCIR', field: 'weight', style: 'width: 5%', sortable: true },
        { name: 'qty_real', align: 'center', label: 'PRODUCIDO', field: 'qty_real', style: 'width: 5%', sortable: true },
        { name: 'status', align: 'center', label: 'ESTATUS', field: 'status', style: 'width: 10%', sortable: true },
        { name: 'actions', align: 'center', label: 'ACCIONES', field: 'actions', style: 'width: 10%', sortable: false }
      ],
      lotsPagination: {
        rowsPerPage: 50
      },
      productFibersOptions: [],
      categoryOptions: [],
      ProductsOptionsbyLines: [],
      dataBom: [],
      qualityModal: false,
      quality: {
        fields: {
          id: null,
          volume: null,
          strength: null,
          rebound: null,
          spring: null,
          line: null,
          lot_number: null
        }
      }
    }
  },
  computed: {
    canModifyOrder () {
      if (this.order.fields.status === 'NUEVO') {
        if (this.lots.filter(l => (l.status !== 'NUEVO')).length === 0) {
          return true
        }
      }
      return false
    },
    canModifyLots () {
      if (this.order.fields.status === 'NUEVO') {
        return true
      }
      return false
    },
    shifttRules (val) {
      return [
        val => this.$v.lot.fields.shift.required || 'El campo Turno es requerido.'
      ]
    },
    creationDateRules (val) {
      return [
        val => (this.$v.order.fields.production_date.required) || 'El campo Fecha de creación es requerido.'
      ]
    },
    productRules (val) {
      return [
        val => (this.$v.order.fields.product.required) || 'El campo Producto es requerido.'
      ]
    },
    qtyRules (val) {
      return [
        val => (this.$v.order.fields.qty.required) || 'El campo Cantidad es requerido.',
        val => (this.$v.order.fields.qty.decimal) || 'El campo Cantidad debe ser numérico.'
      ]
    },
    shipmentsProgress () {
      // return Number(this.order.fields.status === 'NUEVO' ? 0 : (this.order.fields.status === 'EJECUTADO' ? 0.5 : (this.order.fields.status === 'FINALIZADO' ? 1 : 0)))
      return Number(this.porcentaje)
    },
    shipmentsProgressLabel () {
      return 'Avance: ' + (this.shipmentsProgress * 100).toFixed(2) + '%'
    },
    qualityVolumeRules (val) {
      return [
        val => (this.$v.quality.fields.volume.decimal) || 'El campo Volumen debe ser numérico.'
      ]
    },
    qualityStrengthRules (val) {
      return [
        val => (this.$v.quality.fields.strength.decimal) || 'El campo Resistencia debe ser numérico.'
      ]
    },
    qualityReboundRules (val) {
      return [
        val => (this.$v.quality.fields.rebound.decimal) || 'El campo Rebote debe ser numérico.'
      ]
    },
    qualitySpringRules (val) {
      return [
        val => (this.$v.quality.fields.spring.decimal) || 'El campo Resorte debe ser numérico.'
      ]
    },
    bomcategoryRules (val) {
      return [
        val => this.$v.order.fields.family.required || 'El campo Categorías es requerido.'
      ]
    },
    bomproductsRules (val) {
      return [
        val => this.$v.order.fields.product.required || 'El campo Productos es requerido.'
      ]
    }
  },
  beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(5) && !this.$store.getters['users/roles'].includes(6) && !this.$store.getters['users/roles'].includes(7)) {
      this.$router.push('/')
    }
  },
  created () {
    this.fetchFromServer()
    this.getCategories()
    this.fetchFromServerProductBom()
    this.getShifts()
  },
  methods: {
    formatPrice (value) {
      const val = (value / 1).toFixed(1).replace('.', ',')
      return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')
    },
    fetchFromServer () {
      const id = this.$route.params.id
      api.get(`/production-orders/${id}`).then(async ({ data }) => {
        if (!data.order) {
          this.$router.push('/production-orders')
        } else {
          this.order.fields.id = data.order.id
          this.order.fields.order_number = data.order.order_number
          this.order.fields.production_date = data.order.production_date
          this.order.fields.qty = data.order.qty
          this.order.fields.status = data.order.status
          if (data.order.production_date) {
            this.order.fields.production_date = data.order.production_date.split('-').join('/')
          }
          this.order.fields.family = { value: data.order.category_id, label: data.order.category_name }
          this.order.fields.product = { value: data.order.product_id, label: data.order.product }
          await api.get(`/production-lots/order/${id}`).then(({ data }) => {
            var aux = 0
            for (var i = 0; i < data.lots.length; i++) {
              if (data.lots[i].status === 'FINALIZADO') {
                aux = aux + 1
              }
            }
            if (aux === data.lots[0].tot) {
              if (this.order.fields.status !== 'FINALIZADO') {
                this.finalizar()
              }
            }
            this.porcentaje = aux / data.lots[0].tot
            this.lots = data.lots
          })
          this.getLinesByCategoriesEdit()
        }
      })
    },
    fetchFromServerProductBom () {
      const id = this.$route.params.id
      api.get(`/bom/productBom/${id}`).then(({ data }) => {
        this.dataBom = data.bom
      })
    },
    backToGrid () {
      this.$router.push('/production-orders')
    },
    cancelOrder () {
      this.$q.loading.show()
      const id = this.$route.params.id
      api.put(`/production-orders/${id}/cancel`, []).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.fetchFromServer()
        }
      })
    },
    updateOrder () {
      this.$v.order.fields.$reset()
      this.$v.order.fields.$touch()
      if (this.$v.order.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = { ...this.order.fields }
      params.unit_id = 5
      api.put(`/production-orders/${params.id}`, params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'red')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.fetchFromServer()
          this.fetchFromServerProductBom()
        } else {
          this.$q.loading.hide()
        }
      })
    },
    packingList () {
      const uri = process.env.API + `production-orders/packing-list/pdf/${this.$route.params.id}`
      window.open(uri, '_blank')
    },
    handiWork () {
      const uri = process.env.API + `production-orders/handi-work/pdf/${this.$route.params.id}`
      window.open(uri, '_blank')
    },
    cost () {
      const uri = process.env.API + `production-orders/cost/pdf/${this.$route.params.id}`
      window.open(uri, '_blank')
    },
    editLot (lotElement) {
      // alert(lotElement.id)
      this.$router.push(`/lots/${lotElement.id}`)
    },
    lotPackingList (id) {
      const uri = process.env.API + `production-lots/packing-list/pdf/${id}`
      window.open(uri, '_blank')
    },
    cancelLot (lotElementId) {
      this.$q.dialog({
        title: 'Cancelar lote',
        message: '¿Realmente desea cancelar el lote?',
        cancel: true,
        persistent: true
      }).onOk(() => {
        this.$q.loading.show()
        api.put(`/production-lots/${lotElementId}/cancel`).then(({ data }) => {
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
      }).onCancel(() => {
        return false
      })
    },
    getCategories () {
      api.get('/categories/options').then(({ data }) => {
        this.categoryOptions = data.options
      })
    },
    getLinesByCategories () {
      this.$q.loading.show()
      this.ProductsOptionsbyLines = []
      this.ProductsOptionsbyLines.unshift({ value: null, label: 'NINGUNO' })
      api.get(`/products/category1/${this.order.fields.family}`).then(({ data }) => {
        if (data.products.length > 0) {
          data.products.forEach(branch => {
            this.ProductsOptionsbyLines.push({ label: branch.label, value: branch.id })
          })
          this.$q.loading.hide()
        }
      })
    },
    getLinesByCategoriesEdit () {
      this.ProductsOptionsbyLines = []
      this.ProductsOptionsbyLines.unshift({ value: null, label: 'NINGUNO' })
      api.get(`/products/category1/${this.order.fields.family.value}`).then(({ data }) => {
        if (data.products.length > 0) {
          data.products.forEach(branch => {
            this.ProductsOptionsbyLines.push({ label: branch.label, value: branch.id })
          })
        }
      })
    },
    filterCategory (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.options1 = this.categoryOptions.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    filterProduct (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.options2 = this.ProductsOptionsbyLines.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    production () {
      this.$q.dialog({
        title: 'Confirmación',
        message: '¿Comenzar producción?',
        persistent: true,
        ok: {
          label: 'Aceptar',
          color: 'green'
        },
        cancel: {
          label: 'Cancelar',
          color: 'red'
        }
      }).onOk(() => {
        this.$q.loading.show()
        const params = { ...this.order.fields }
        api.post(`/production-orders/getByOrder/${params.id}`, params).then(({ data }) => {
          this.$q.notify({
            message: data.message.content,
            position: 'top',
            color: (data.result ? 'positive' : 'red')
          })
          this.fetchFromServer()
          this.fetchFromServerProductBom()
          this.$q.loading.hide()
        })
        this.$q.loading.hide()
      }).onCancel(() => {
        return false
      })
    },
    async finalizar () {
      this.showLoading()
      const params = { ...this.order.fields }

      api.post(`/production-orders/finalizeOrder/${params.id}`, params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'red')
        })
        this.fetchFromServer()
        this.fetchFromServerProductBom()
        this.beforeDestroy()
      })
    },
    showLoading () {
      this.$q.loading.show({
        spinnerColor: 'primary',
        spinnerSize: 140,
        backgroundColor: 'white',
        message: 'Cargando..',
        messageColor: 'black'
      })
    },
    beforeDestroy () {
      this.$q.loading.hide()
    },
    getUnit (idaux) {
      const id = idaux
      api.get(`products/${id}`).then(({ data }) => {
        this.lot.fields.unit = { value: data.product.unit_id, label: data.product.unit }
      })
    },
    closeModal () {
      this.lot.fields.product_id = null
      this.lot.fields.unit = null
      this.lot.fields.qty = null
      this.lot.fields.shift = null
    },
    addLot () {
      this.$v.lot.fields.$reset()
      this.$v.lot.fields.$touch()
      if (this.$v.lot.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.showLoading()
      this.lot.fields.order_id = this.$route.params.id
      const params = { ...this.lot.fields }
      api.post('/production-lots/createLotByOrder', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'red')
        })
        this.modalOrders = false
        this.closeModal()
        this.fetchFromServer()
        this.beforeDestroy()
      })
    },
    getShifts () {
      api.get('/shifts/options').then(({ data }) => {
        this.shiftOptions = data.options
      })
    }
  }
}
</script>

<style>
</style>

<template>
<q-page class="bg-grey-3">
  <div class="q-pa-sm panel-header">
    <div class="row">
      <div class="col-sm-12" style="font-size: 20px">
        <div class="q-pa-md q-gutter-sm">
          <q-breadcrumbs align="left">
            <q-breadcrumbs-el label="" icon="home" to="/" />
            <q-breadcrumbs-el label="Ordenes de producción" to="/production-orders" />
            <q-breadcrumbs-el label="Nuevo" />
          </q-breadcrumbs>
        </div>
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
              v-model="order.fields.production_date"
              mask="date"
              label="Fecha programada"
              :rules="creationDateRules"
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
      <!-- <div class="col-xs-12 col-sm-3">
            <q-select
              color="dark"
              bg-color="secondary"
              filled
              v-model="order.fields.category"
              :error="$v.order.fields.category.$error"
              label="Familias"
              :rules="bomcategoryRules"
              :options="options1"
              use-input
              hide-selected
              fill-input
              @filter="filterCategory"
              input-debounce="0"
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
               <div class="col-xs-12 col-sm-3">
                  <q-select
                  filled
                  dark
                  color="white"
                  bg-color="secondary"
                  v-model="order.fields.product"
                  :error="$v.order.fields.product.$error"
                  label="Producto"
                  :options="options2"
                  use-input
                  hide-selected
                  fill-input
                  input-debounce="0"
                  @input="getUnit()"
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
                <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="order.fields.cantidad"
                :error="$v.order.fields.cantidad.$error"
                label="Cantidad"
                :rules="CantidadRules"
                >
                <template v-slot:prepend>
                  <q-icon name="fas fa-hashtag" />
                </template>
              </q-input>
            </div>-->
          <!-- <div class="col-xs-12 col-sm-3">
            <q-input
              color="dark"
              bg-color="secondary"
              filled
              v-model="order.fields.status"
              label="Estatus"
              disable
            >
              <template v-slot:prepend>
                <q-icon name="battery_full" />
              </template>
            </q-input>
          </div> -->
        </div>

        <div class="row q-mb-sm q-mt-md">
          <div class="col-xs-12 col-sm-2 offset-sm-10 pull-right">
            <q-btn color="positive" icon="save" label="Guardar" @click="createOrder()" />
          </div>
        </div>
      </div>
    </div>
  </div>
</q-page>
</template>

<script>
import api from '../../commons/api.js'
const { required } = require('vuelidate/lib/validators')
// const cantidad = helpers.regex('cantidad', /[0-9]+$/)
export default {
  name: 'NewProductionOrder',
  validations: {
    order: {
      fields: {
        production_date: { required },
        status: { required },
        category: { },
        product: { },
        cantidad: { },
        unit_id: { }
      }
    }
  },
  data () {
    return {
      options1: this.categoryOptions,
      options2: this.ProductsOptionsbyLines,
      order: {
        fields: {
          production_date: null,
          product: null,
          qty: null,
          status: 'NUEVO',
          category: null,
          cantidad: null,
          unit_id: null
        }
      },
      productOptions: [],
      lotOptions: [
        { value: 1, label: 1 },
        { value: 2, label: 2 },
        { value: 3, label: 3 },
        { value: 4, label: 4 },
        { value: 5, label: 5 },
        { value: 6, label: 6 },
        { value: 7, label: 7 },
        { value: 8, label: 8 },
        { value: 9, label: 9 },
        { value: 10, label: 10 }
      ],
      categoryOptions: [],
      ProductsOptionsbyLines: []
    }
  },
  computed: {
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
    lotsQtyRules (val) {
      return [
        val => (this.$v.order.fields.lotsQty.required) || 'El campo Lotes es requerido.'
      ]
    },
    bomcategoryRules (val) {
      return [
        val => this.$v.order.fields.category || 'El campo Categorías es requerido.'
      ]
    },
    bomproductsRules (val) {
      return [
        val => this.$v.order.fields.product.required || 'El campo Productos es requerido.'
      ]
    },
    CantidadRules (val) {
      return [
        val => this.$v.order.fields.cantidad.required || 'El campo Cantidad es requerido',
        val => this.$v.order.fields.cantidad.cantidad || 'El campo Cantidad debe ser númerico'
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
  },
  mounted () {
    this.getCategories()
  },
  methods: {
    fetchFromServer () {
      this.$q.loading.show()
      this.$q.loading.hide()
    },
    backToGrid () {
      this.$router.push('/production-orders')
    },
    getUnit () {
      const id = this.order.fields.product.id
      // console.log(this.order.fields.product)
      api.get(`products/${id}`).then(({ data }) => {
        this.order.fields.unit = { value: data.product.unit_id, label: data.product.unit }
      })
    },
    createOrder () {
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
      const params = []
      params.production_date = this.order.fields.production_date
      api.post('/production-orders', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.$router.push(`/production-orders/${data.order.id}`)
        } else {
          this.$q.loading.hide()
        }
      })
    },
    getCategories () {
      api.get('/categories/options').then(({ data }) => {
        this.categoryOptions = data.options
      })
    },
    getLinesByCategories () {
      this.ProductsOptionsbyLines = []
      api.get(`/products/category1/${this.order.fields.category}`).then(({ data }) => {
        this.ProductsOptionsbyLines = data.products
        this.ProductsOptionsbyLines.unshift({ value: null, label: 'NINGUNO' })
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
    }
  }
}
</script>

<style>
</style>

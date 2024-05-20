<template>
  <q-page>
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-8">
          <span class="q-ml-md grey-8 fs28 page-title">Tracking de Pacas</span>
        </div>
        <div class="col-sm-4 pull-right">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="right">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Tracking de Pacas" />
            </q-breadcrumbs>
          </div>
        </div>
      </div>
    </div>

    <div class="q-pa-md bg-grey-3">
      <div class="row bg-white border-panel">
        <div class="col q-pa-md">
          <div class="row q-mb-sm">
            <div class="col-xs-12 col-md-3" style="padding: 3px;">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="category"
                :options="categoryOptions"
                label="Categoría"
                @input="() => {line = { value: null, label: 'Seleccione la línea' };product = { value: null, label: 'Seleccione el producto' };bale = { value: null, label: 'Seleccione la paca' }}"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-cubes" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-md-3" style="padding: 3px;">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="line"
                :options="filteredLineOptions"
                label="Línea"
                @input="() => {product = { value: null, label: 'Seleccione el producto' };bale = { value: null, label: 'Seleccione la paca' }}"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-grip-lines-vertical" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-md-3" style="padding: 3px;">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="product"
                :options="filteredProductOptions"
                label="Producto"
                @input="() => {this.bale = { value: null, label: 'Seleccione la paca' }}"
              >
                <template v-slot:prepend>
                  <q-icon name="emoji_objects" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-md-3 text-center" style="padding: 3px;">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="bale"
                :options="filteredBaleOptions"
                label="Paca"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-th-large" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-md-3 text-center" style="padding: 3px;">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="baleNumber"
                label="Número de Paca"
                @keyup="updateFieldsv2($event,'cantidad')"
                type="number"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-th-large" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-md-3 offset-md-6 text-center pull-right">
              <q-btn color="primary" icon="fas fa-search" label="Buscar" @click="generateTracking()" style="margin: 3px; height: 95%;" />
            </div>
          </div>

          <br>

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
                <q-td key="date" style="width: 10%;" :props="props">{{ props.row.date }}</q-td>
                <q-td key="bale" style="text-align: right; width: 10%;" :props="props">{{ props.row.bale_id == 0 ? '-' : props.row.bale_id }}</q-td>
                <q-td key="product" style="text-align: left; width: 20%;" :props="props">{{ `${props.row.category_code}-${props.row.line_code}-${props.row.product_name}` }}</q-td>
                <q-td key="branchOffice" style="text-align: left; width: 20%;" :props="props">{{ props.row.branch_office_name }}</q-td>
                <q-td key="storage" style="text-align: left; width: 20%;" :props="props">{{ props.row.storage_name }}</q-td>
                <q-td key="movement_type" style="width: 10%;" :props="props">
                  <q-chip square dense :color="props.row.movement_type == 1 ? 'green' : (props.row.movement_type == 2 ? 'orange' : 'blue')" text-color="white">
                    {{ (props.row.movement_type == 1 ? 'Entrada' : (props.row.movement_type == 2 ? 'Salida' : 'Otro')) }}
                  </q-chip>
                </q-td>
                <q-td key="qty" style="text-align: right; width: 10%;" :props="props">{{ formatPrice(props.row.qty) }} KG</q-td>
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

export default {
  name: 'Tracking',
  data () {
    return {
      formatter: new Intl.NumberFormat('en-US'),
      category: { value: null, label: 'Seleccione la categoría' },
      line: { value: null, label: 'Seleccione la línea' },
      product: { value: null, label: 'Seleccione el producto' },
      bale: { value: null, label: 'Seleccione la paca' },
      baleNumber: null,
      baleOptions: [],
      productOptions: [],
      lineOptions: [],
      categoryOptions: [],
      pagination: {
        rowsPerPage: 50
      },
      columns: [
        { name: 'date', align: 'center', label: 'Fecha', field: 'date', style: 'width: 10%', sortable: true },
        { name: 'bale', align: 'center', label: 'Número de paca', field: 'bale', style: 'width: 10%', sortable: true, sort: (a, b) => Number(a, 10) - Number(b, 10) },
        { name: 'product', align: 'center', label: 'Producto', field: 'product', style: 'width: 20%', sortable: true },
        { name: 'branchOffice', align: 'center', label: 'Sucursal', field: 'branchOffice', style: 'width: 20%', sortable: true },
        { name: 'storage', align: 'center', label: 'Almacén', field: 'storage', style: 'width: 20%', sortable: true },
        { name: 'movement_type', align: 'center', label: 'Tipo de movimiento', field: 'movement_type', style: 'width: 10%', sortable: true },
        { name: 'qty', align: 'center', label: 'Cantidad', field: 'qty', style: 'width: 10%', sortable: true, sort: (a, b) => Number(a, 10) - Number(b, 10) }
      ],
      data: []
    }
  },
  computed: {
    filteredLineOptions () {
      const filteredLines = (this.category != null && this.category.value != null) ? this.lineOptions.filter(lo => Number(lo.category) === Number(this.category.value)) : []
      filteredLines.unshift({ value: null, label: 'Seleccione la línea' })
      return filteredLines
    },
    filteredProductOptions () {
      const filteredProducts = (this.line != null && this.line.value != null) ? this.productOptions.filter(lo => Number(lo.line) === Number(this.line.value)) : []
      filteredProducts.unshift({ value: null, label: 'Seleccione el producto' })
      return filteredProducts
    },
    filteredBaleOptions () {
      const filteredBales = (this.product != null && this.product.value != null) ? this.baleOptions.filter(bo => (Number(bo.product_id) === Number(this.product.value))) : []
      filteredBales.unshift({ value: null, label: 'Seleccione la paca' })
      return filteredBales
    }
  },
  beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(2) && !this.$store.getters['users/roles'].includes(5)) {
      this.$router.push('/')
    }
  },
  created () {
    this.fetchFromServer()
  },
  methods: {
    formatPrice (value) {
      const val = (value / 1).toFixed(1).replace('.', ',')
      return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')
    },
    fetchFromServer () {
      this.$q.loading.show()
      api.get('/bales/options').then(({ data }) => {
        this.baleOptions = data.options
        const productsWithBale = []
        this.baleOptions.forEach(function (bale) {
          if (!productsWithBale.includes(Number(bale.product_id))) {
            productsWithBale.push(Number(bale.product_id))
          }
        })
        api.get('/categories/options').then(({ data }) => {
          this.categoryOptions = data.options
          this.categoryOptions.unshift({ value: null, label: 'Seleccione la categoría' })
          api.get('/lines/options').then(({ data }) => {
            this.lineOptions = data.options
            this.lineOptions.unshift({ value: null, label: 'Seleccione la línea' })
            api.get('/products/options').then(({ data }) => {
              const options = []
              data.options.forEach(function (po) {
                if (productsWithBale.includes(Number(po.value))) {
                  options.push(po)
                }
              })
              this.productOptions = options
            })
            this.$q.loading.hide()
          })
        })
      })
    },
    updateFields () {
      if (isNaN(this.baleNumber)) {
        this.category = { value: null, label: 'Seleccione la categoría' }
        this.line = { value: null, label: 'Seleccione la línea' }
        this.product = { value: null, label: 'Seleccione el producto' }
        this.bale = { value: null, label: 'Seleccione la paca' }
      } else {
        const baleEntered = this.baleOptions.filter(bo => Number(bo.value) === Number(this.baleNumber))
        if (baleEntered.length > 0 && baleEntered[0] != null) {
          this.bale = baleEntered[0]
          this.product = this.productOptions.filter(po => Number(po.value) === Number(this.bale.product_id))[0]
          this.line = this.lineOptions.filter(lo => Number(lo.value) === Number(this.product.line))[0]
          this.category = this.categoryOptions.filter(co => Number(co.value) === Number(this.line.category))[0]
        } else {
          this.category = { value: null, label: 'Seleccione la categoría' }
          this.line = { value: null, label: 'Seleccione la línea' }
          this.product = { value: null, label: 'Seleccione el producto' }
          this.bale = { value: null, label: 'Seleccione la paca' }
        }
      }
    },
    updateFieldsv2 (evt, input) {
      switch (input) {
        case 'cantidad':
          this.baleNumber = this.baleNumber.replace(/[^0-9.]/g, '')
          if (this.baleNumber > 0) {
            const baleEntered = this.baleOptions.filter(bo => Number(bo.value) === Number(this.baleNumber))
            if (baleEntered.length > 0 && baleEntered[0] != null) {
              this.bale = baleEntered[0]
              this.product = this.productOptions.filter(po => Number(po.value) === Number(this.bale.product_id))[0]
              this.line = this.lineOptions.filter(lo => Number(lo.value) === Number(this.product.line))[0]
              this.category = this.categoryOptions.filter(co => Number(co.value) === Number(this.line.category))[0]
            }
          } else {
            this.category = { value: null, label: 'Seleccione la categoría' }
            this.line = { value: null, label: 'Seleccione la línea' }
            this.product = { value: null, label: 'Seleccione el producto' }
            this.bale = { value: null, label: 'Seleccione la paca' }
          }
          this.$v.bagNumber.$touch()
          break
        default:
          break
      }
    },
    generateTracking () {
      if (this.bale == null || this.bale.value == null) {
        this.$q.notify({
          message: 'Por favor, seleccione la paca',
          position: 'top',
          color: 'warning'
        })
        return false
      }
      this.$q.loading.show()
      api.get(`/movements/kardex/null/null/null/null/null/null/${this.bale.value}`).then(({ data }) => {
        if (data.result) {
          this.data = data.kardex
          this.$q.loading.hide()
        }
      })
    }
  }
}
</script>

<style>
input[type=number]::-webkit-inner-spin-button,
input[type=number]::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

input[type=number] { -moz-appearance:textfield; }
</style>

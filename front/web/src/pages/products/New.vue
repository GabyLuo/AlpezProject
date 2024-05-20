<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-9">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Artículos" to="/products" />
              <q-breadcrumbs-el label="Nuevo Producto" />
            </q-breadcrumbs>
          </div>
        </div>
      </div>
    </div>

    <div class="q-pa-md bg-grey-3">
      <div class="row bg-white border-panel">
        <div class="col q-pa-md">
          <div class="row q-mb-sm">

          </div>

          <div class="row q-col-gutter-xs">
            <div class="col-xs-12 col-sm-3">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="product.fields.category"
                :options="categoryOptions"
                label="Categorías"
                :rules="categoryRules"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-cubes" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="product.fields.line"
                :options="filteredLineOptions"
                label="Subcategoría"
                :rules="lineRules"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-grip-lines-vertical" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="product.fields.unit"
                :options="unitOptions"
                label="Unidad"
                :rules="unitRules"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-grip-lines-vertical" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-select
                          filled
                          color="dark"
                          bg-color="secondary"
                          v-model="product.fields.mark"
                          label="Marca"
                          :options="options"
                          use-input
                          hide-selected
                          fill-input
                          input-debounce="0"
                          @filter="filterMarcas"
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
            <!-- <div class="col-xs-12 col-sm-2">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="product.fields.code"
                :error="$v.product.fields.code.$error"
                label="Código"
                :rules="codeRules"
                @input="v => { product.fields.code = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fingerprint" />
                </template>
              </q-input>
            </div> -->
            <!--<div class="col-xs-12 col-sm-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                :error="$v.product.fields.code.$error"
                v-model="product.fields.code"
                label="Código"
                @input="v => { product.fields.code = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fingerprint" />
                </template>
              </q-input>
            </div>-->
            <div class="col-xs-12 col-sm-6">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="product.fields.name"
                :error="$v.product.fields.name.$error"
                label="Nombre"
                :rules="nameRules"
                @input="v => { product.fields.name = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-signature" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                autogrow
                :rules="weightRules"
                :error="$v.product.fields.weight.$error"
                v-model="product.fields.weight"
                label="Peso KG"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-weight-hanging" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="product.fields.rebasa_code"
                :error="$v.product.fields.rebasa_code.$error"
                label="Código Interno"
                :rules="codeRebasa"
                @input="v => { product.fields.rebasa_code = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-signature" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-4">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                :rules="codeSuplier"
                v-model="product.fields.supplier_code"
                :error="$v.product.fields.supplier_code.$error"
                label="Código de proveedor"
                @input="v => { product.fields.supplier_code = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-box-open" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-4">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                use-input
                input-debounce="0"
                emit-value
                label="Clave producto servicio"
                v-model="product.fields.clave_producto_id"
                :options="claveProdOptions"
                @filter="searchClave"
                clear
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-key" />
                </template>
                <template v-slot:no-option>
                  <q-item>
                    <q-item-section class="text-grey">
                      No results
                    </q-item-section>
                  </q-item>
                </template>
                <template v-slot:append>
                  <q-icon
                    v-if="product.fields.clave_producto_id !== null"
                    class="cursor-pointer"
                    name="clear"
                    @click.stop="product.fields.clave_producto_id = null"
                  />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-4">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                autogrow
                :rules="barcodeRules"
                :error="$v.product.fields.barcode.$error"
                v-model="product.fields.barcode"
                label="Código de barras"
              >
                <template v-slot:prepend>
                  <q-icon name="view_week" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-7">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                autogrow
                v-model="product.fields.description"
                label="Descripción"
                @input="v => { product.fields.description = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-signature" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-5">
              <q-input
              color="dark"
              type="textarea"
              bg-color="secondary"
              filled
              autogrow
              v-model="product.fields.additional_information"
              label="Información adicional"
              @input="v => { product.fields.additional_information = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-signature" />
                </template>
              </q-input>
            </div>
            <!-- <div class="col-xs-12 col-sm-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                type="number"
                suffix="MXN"
                v-model="product.fields.cost_a"
                label="Precio a"
                :error="$v.product.fields.cost_a.$error"
                :rules="priceRules"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-dollar-sign" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                type=number
                suffix="MXN"
                v-model="product.fields.cost_b"
                label="Precio b"
                :error="$v.product.fields.cost_a.$error"
                :rules="priceRules"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-dollar-sign" />
                </template>
              </q-input>
            </div> -->
          </div>
          <div class="row q-mb-sm q-mt-md">
            <div class="col-xs-12 col-sm-2 offset-sm-10 pull-right">
              <q-btn color="positive" icon="save" label="Guardar" @click="createProduct()" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </q-page>
</template>

<script>
import api from '../../commons/api.js'
const { required, maxLength, integer, decimal } = require('vuelidate/lib/validators')

export default {
  name: 'NewProduct',
  validations: {
    product: {
      fields: {
        name: { required, maxLength: maxLength(100) },
        old_code: { maxLength: maxLength(40) },
        family: { required },
        rebasa_code: { maxLength: maxLength(40) },
        supplier_code: { maxLength: maxLength(40) },
        category: { required },
        line: { required },
        unit: { required },
        barcode: { maxLength: maxLength(20), integer },
        mark: { },
        code: { },
        weight: { required, decimal }
      }
    }
  },
  data () {
    return {
      product: {
        fields: {
          name: null,
          family: { value: null, label: 'NINGUNO' },
          category: null,
          line: null,
          old_code: null,
          unit: null,
          cost_a: null,
          cost_b: null,
          rebasa_code: null,
          supplier_code: null,
          description: null,
          barcode: null,
          mark: null,
          weight: null,
          additional_information: null
        }
      },
      categoryOptions: [],
      lineOptions: [],
      markOptions: [],
      familyOptions: [],
      unitOptions: [],
      claveProdOptions: [],
      options: this.markOptions
    }
  },
  computed: {
    roleId () {
      const user = this.$store.getters['users/rol']
      return parseInt(user)
    },
    priceRules (val) {
      return [
        val => (this.$v.product.fields.cost_a.required) || 'El campo Precio es requerido.'
      ]
    },
    nameRules (val) {
      return [
        val => (this.$v.product.fields.name.required) || 'El campo Nombre es requerido.',
        val => (this.$v.product.fields.name.maxLength) || 'El campo Nombre no debe exceder los 100 dígitos.'
      ]
    },
    codeRules (val) {
      return [
        val => (this.$v.product.fields.code.required) || 'El campo Código es requerido.',
        val => (this.$v.product.fields.code.maxLength) || 'El campo Código no debe exceder los 10 dígitos.'
      ]
    },
    categoryRules (val) {
      return [
        val => this.$v.product.fields.category.required || 'El campo Categorías es requerido.'
      ]
    },
    lineRules (val) {
      return [
        val => this.$v.product.fields.line.required || 'El campo Líneas es requerido.'
      ]
    },
    unitRules (val) {
      return [
        val => this.$v.product.fields.unit.required || 'El campo Unidad es requerido.'
      ]
    },
    codeRebasa (val) {
      return [
        val => (this.$v.product.fields.rebasa_code.maxLength) || 'El campo Código no debe exceder los 40 dígitos.'
      ]
    },
    codeSuplier (val) {
      return [
        val => (this.$v.product.fields.supplier_code.maxLength) || 'El campo Código no debe exceder los 40 dígitos.'
      ]
    },
    barcodeRules (val) {
      return [
        val => this.$v.product.fields.barcode.maxLength || 'El campo Código de barras no debe exceder 20 dígitos.',
        val => this.$v.product.fields.barcode.integer || 'El campo Código de barras es numérico.'
      ]
    },
    filteredLineOptions () {
      if (this.product.fields.category != null && this.product.fields.category.value != null) {
        return this.lineOptions.filter(line => line.category === this.product.fields.category.value)
      }
      return []
    },
    weightRules (val) {
      return [
        val => this.$v.product.fields.weight.decimal || 'El campo Peso es numérico.'
      ]
    }
  },
  beforeRouteEnter (to, from, next) {
    next(vm => {
      const propiedades = vm.$store.getters['users/rol']
      console.log(propiedades)
      if (propiedades === 1 || propiedades === 3 || propiedades === 7 || propiedades === 2 || propiedades === 20 || propiedades === 4 || propiedades === 27 || propiedades === 22) {
        next()
      } else {
        next('/')
      }
    })
  },
  /* beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(2) && !this.$store.getters['users/roles'].includes(22) && !this.$store.getters['users/roles'].includes(4)) {
      this.$router.push('/')
    }
  }, */
  mounted () {
    this.fetchFromServer()
  },
  methods: {
    fetchFromServer () {
      this.$q.loading.show()
      this.getCategories()
      this.getLines()
      this.getMarks()
      this.getProducts()
      this.$q.loading.hide()
    },
    filterMarcas (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.options = this.markOptions.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    getCategories () {
      api.get('/categories/options').then(({ data }) => {
        this.categoryOptions = data.options
      })
    },
    getLines () {
      api.get('/lines/options').then(({ data }) => {
        this.lineOptions = data.options
      })
    },
    getMarks () {
      api.get('/marks/options').then(({ data }) => {
        this.markOptions = data.options
      })
    },
    getProducts () {
      const id = this.$route.params.id
      api.get('/products/options').then(({ data }) => {
        this.familyOptions = data.options.filter(product => product.family == null && product.value !== id)
        this.familyOptions.unshift({ value: null, label: 'NINGUNO' })
        api.get('/units/options').then(({ data }) => {
          this.unitOptions = data.options
        })
      })
    },
    createProduct () {
      this.$v.product.fields.$reset()
      this.$v.product.fields.$touch()
      if (this.$v.product.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = []
      params.name = this.product.fields.name
      params.description = this.product.fields.description
      params.line_id = this.product.fields.line.value
      params.unit_id = this.product.fields.unit.value
      params.clave_producto_id = this.product.fields.clave_producto_id
      params.old_code = this.product.fields.old_code
      params.barcode = this.product.fields.barcode
      params.rebasa_code = this.product.fields.rebasa_code
      params.supplier_code = this.product.fields.supplier_code
      params.mark = this.product.fields.mark
      params.code = this.product.fields.code
      params.weight = this.product.fields.weight
      params.additional_information = this.product.fields.additional_information
      if (this.product.fields.family != null && this.product.fields.family.value != null) {
        params.family_id = this.product.fields.family.value
      }
      api.post('/products', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.$router.push(`/products/${data.id}`)
        } else {
          this.$q.loading.hide()
        }
      })
    },
    searchClave (val, update) {
      if (val.length < 3) {
        return false
      }
      update(() => {
        api.get(`/products/searchClave/${val}`).then(({ data }) => {
          this.claveProdOptions = data.claves
        })
      })
    }
  }
}
</script>

<style>
</style>

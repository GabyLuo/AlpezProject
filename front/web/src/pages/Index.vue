<template>
  <q-page class="q-pa-md" v-if="this.$store.getters['users/roles'].includes(14) || this.$store.getters['users/roles'].includes(16)">
    <div class="q-col-gutter-md row items-start">
      <div class="row col-sm-12 col-md-2">
        <div class="row bg-white border-panel" style="width: 100%;">
          <div class="col q-pa-md">
            <div class="row q-col-gutter-xs">
              <div class="col-xs-12">
                CATEGORÍA
                <q-option-group
                  :options="categoryOptions"
                  label="Categoría"
                  type="checkbox"
                  v-model="categories"
                />
              </div>
              <q-separator />
              <div class="col-xs-12">
                DISPONIBILIDAD
                <q-checkbox v-model="includeNotAvailable" label="Incluir no disponibles" />
              </div>
              <q-separator />
              <div class="col-xs-12">
                PRECIO
                <q-range
                  v-model="price"
                  color="primary"
                  :min="minPrice"
                  :max="maxPrice"
                />
                <q-badge color="secondary">
                  ${{ price.min }} a ${{ price.max }}
                </q-badge>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="row col-sm-12 col-md-8">
        <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 col-xl-2" v-for="product in filteredProducts" v-bind:key="product.id">
          <q-card style="margin-bottom: 10px; margin-left: 5px; margin-right: 5px;">
            <q-img :src="product.photo ? serverUrl + 'assets/images/products/' + product.photo : serverUrl + 'assets/images/logo.png'" style="height: 250px; width: 100%;">
              <template v-slot:error>
                <div class="absolute-full flex flex-center bg-gray text-white">
                  SIN IMAGEN
                </div>
              </template>
              <div class="row absolute-bottom text-subtitle1 text-center">
                <q-btn class="col-xs-3" color="negative" icon="favorite_border" flat @click.native="addToFavorites(product)" size="10px">
                  <q-tooltip content-class="bg-negative">   Agregar a favoritos</q-tooltip>
                </q-btn>
                <q-btn class="col-xs-3" color="primary" icon="compare_arrows" flat @click.native="openCompareModal(product)" size="10px">
                  <q-tooltip content-class="bg-primary">Comparar</q-tooltip>
                </q-btn>
                <q-btn class="col-xs-3" color="warning" icon="search" flat @click.native="openDetailsModal(product)" size="10px">
                  <q-tooltip content-class="bg-warning">Ver detalles</q-tooltip>
                </q-btn>
                <q-btn class="col-xs-3" color="positive" icon="add_shopping_cart" flat @click.native="openAddDetailModal(product)" size="10px">
                  <q-tooltip content-class="bg-positive">Agregar a carrito</q-tooltip>
                </q-btn>
              </div>
            </q-img>

            <q-card-section>
              <div class="row no-wrap items-center">
                <div class="col text-h6 ellipsis text-primary">
                  {{ product.name }}
                </div>
              </div>
            </q-card-section>

            <q-card-section class="q-pt-none">
              {{ currencyFormatter.format(product.price) }} / KG.
              <q-separator />
              <div class="text-subtitle1">
                Código: {{ product.code }}
                <br>
                Línea: {{ product.line }}
                <br>
                Categoría: {{ product.category }}
              </div>
            </q-card-section>
          </q-card>
        </div>
      </div>
      <div class="row col-sm-12 col-md-2">
        <div class="row bg-white border-panel" style="width: 100%;">
          <div class="col q-pa-md">
            <div class="row q-col-gutter-xs">
              <div class="col-xs-12 pull-right">
                <div class="row">
                  <div class="col-xs-12">
                    <q-btn color="positive" stack icon="check" label="Checkout" @click.native="$router.push('/shopping-cart')" style="width: 100%;" :disable="shoppingCartBaleDetails.length == 0 && shoppingCartInBulkDetails.length == 0 && shoppingCartLaminateDetails.length == 0">
                      <strong>{{ currencyFormatter.format(totalPrice) }}</strong>
                    </q-btn>
                  </div>
                </div>
              </div>
              <div class="col-xs-12">
                <div v-for="detail in shoppingCartBaleDetails" v-bind:key="detail.id" style="margin-top: 10px; margin-bottom: 10px;">
                  <q-card>
                    <q-card-section class="row">
                      <div class="col-xs-12 pull-right">
                        <q-btn color="negative" icon="delete" flat @click.native="removeBaleDetail(detail.id)" size="10px">
                          <q-tooltip content-class="bg-negative">Eliminar</q-tooltip>
                        </q-btn>
                      </div>
                      <div class="row col-xs-12">
                        <div class="col-xs-5 col-md-12 col-lg-5 col-xl-2">
                          <q-img :src="serverUrl + 'assets/images/products/' + detail.product_photo" style="width: 100%; height: 100px;">
                            <template v-slot:error>
                              <div class="absolute-full flex flex-center bg-gray text-white">
                                SIN IMAGEN
                              </div>
                            </template>
                          </q-img>
                        </div>
                        <q-card-section class="col-xs-7 col-md-12 col-lg-7 col-xl-10 text-primary">
                          {{ detail.product_name }} <strong>({{ detail.product_code }})</strong>
                          <br>
                          {{ detail.line_name }} <strong>({{ detail.category_name }})</strong>
                        </q-card-section>
                      </div>
                      <div class="col-xs-12 pull-right">
                        <strong>{{ currencyFormatter.format(detail.unit_price) }} × {{ formatter.format(detail.qty) }} = {{ currencyFormatter.format(detail.amount) }}</strong>
                      </div>
                    </q-card-section>
                  </q-card>
                </div>
                <div v-for="detail in shoppingCartInBulkDetails" v-bind:key="detail.id" style="margin-top: 10px; margin-bottom: 10px;">
                  <q-card>
                    <q-card-section class="row">
                      <div class="col-xs-12 pull-right">
                        <q-btn color="negative" icon="delete" flat @click.native="removeInBulkDetail(detail.id)" size="10px">
                          <q-tooltip content-class="bg-negative">Eliminar</q-tooltip>
                        </q-btn>
                      </div>
                      <div class="row col-xs-12">
                        <div class="col-xs-5 col-md-12 col-lg-5 col-xl-2">
                          <q-img :src="serverUrl + 'assets/images/products/' + detail.product_photo" style="width: 100%; height: 100px;">
                            <template v-slot:error>
                              <div class="absolute-full flex flex-center bg-gray text-white">
                                SIN IMAGEN
                              </div>
                            </template>
                          </q-img>
                        </div>
                        <q-card-section class="col-xs-7 col-md-12 col-lg-7 col-xl-10 text-primary">
                          {{ detail.product_name }} <strong>({{ detail.product_code }})</strong>
                          <br>
                          {{ detail.line_name }} <strong>({{ detail.category_name }})</strong>
                        </q-card-section>
                      </div>
                      <div class="col-xs-12 pull-right">
                        <strong>{{ currencyFormatter.format(detail.unit_price) }} × {{ formatter.format(detail.qty) }} = {{ currencyFormatter.format(detail.amount) }}</strong>
                      </div>
                    </q-card-section>
                  </q-card>
                </div>
                <div v-for="detail in shoppingCartLaminateDetails" v-bind:key="detail.id" style="margin-top: 10px; margin-bottom: 10px;">
                  <q-card>
                    <q-card-section class="row">
                      <div class="col-xs-12 pull-right">
                        <q-btn color="negative" icon="delete" flat @click.native="removeLaminateDetail(detail.id)" size="10px">
                          <q-tooltip content-class="bg-negative">Eliminar</q-tooltip>
                        </q-btn>
                      </div>
                      <div class="row col-xs-12">
                        <div class="col-xs-5 col-md-12 col-lg-5 col-xl-2">
                          <q-img :src="serverUrl + 'assets/images/products/' + detail.product_photo" style="width: 100%; height: 100px;">
                            <template v-slot:error>
                              <div class="absolute-full flex flex-center bg-gray text-white">
                                SIN IMAGEN
                              </div>
                            </template>
                          </q-img>
                        </div>
                        <q-card-section class="col-xs-7 col-md-12 col-lg-7 col-xl-10 text-primary">
                          {{ detail.product_name }} <strong>({{ detail.product_code }})</strong>
                          <br>
                          {{ detail.line_name }} <strong>({{ detail.category_name }})</strong>
                        </q-card-section>
                      </div>
                      <div class="col-xs-12 pull-right">
                        <strong>{{ currencyFormatter.format(detail.unit_price) }} × {{ formatter.format(detail.qty) }} = {{ currencyFormatter.format(detail.amount) }}</strong>
                      </div>
                    </q-card-section>
                  </q-card>
                </div>
                <div class="text-center" v-show="shoppingCartBaleDetails.length > 0 || shoppingCartInBulkDetails.length > 0 || shoppingCartLaminateDetails.length > 0">
                  <q-btn color="negative" icon="delete" label="Vaciar" @click.native="removeShoppingCart()" />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <q-dialog v-model="addFiberDetailModal" persistent>
      <q-card style="min-width: 400px;">
        <q-card-section>
          <div class="text-h6">Agregar fibra a carrito de compras</div>
        </q-card-section>
        <q-card-section>
          <div class="row">
            <div class="col-xs-12">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="fiberDetailQty"
                label="Cantidad"
                :rules="fiberDetailQtyRules"
                :suffix="`/${formatter.format(fiberDetailAvailableQty)} Kg.`"
              >
                <template v-slot:prepend>
                  <q-icon name="emoji_objects" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-6" style="padding-right: 10px;">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="unitPrice"
                label="Precio kg."
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-dollar-sign" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-6" style="padding-left: 10px;">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="fiberPrice"
                label="Importe"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-dollar-sign" />
                </template>
              </q-input>
            </div>
          </div>
        </q-card-section>
        <q-card-actions align="right" class="text-primary">
          <q-btn flat label="Cancelar" @click="closeAddFiberDetailModal()" v-close-popup />
          <q-btn flat label="Agregar" @click="addFiberDetail()" />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <q-dialog v-model="addLaminateDetailModal" persistent>
      <q-card style="min-width: 400px;">
        <q-card-section>
          <div class="text-h6">Agregar laminado a carrito de compras</div>
        </q-card-section>
        <q-card-section>
          <div class="row">
            <div class="col-xs-12">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="laminateDetailQty"
                label="Cantidad"
                :rules="laminateDetailQtyRules"
                :suffix="`/${formatter.format(laminateDetailAvailableQty)} Kg.`"
              >
                <template v-slot:prepend>
                  <q-icon name="emoji_objects" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-6" style="padding-right: 10px;">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="unitPrice"
                label="Precio kg."
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-dollar-sign" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-6" style="padding-left: 10px;">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="laminatePrice"
                label="Importe"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-dollar-sign" />
                </template>
              </q-input>
            </div>
          </div>
        </q-card-section>
        <q-card-actions align="right" class="text-primary">
          <q-btn flat label="Cancelar" @click="closeAddLaminateDetailModal()" v-close-popup />
          <q-btn flat label="Agregar" @click="addLaminateDetail()" />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <q-dialog v-model="addInBulkDetailModal" persistent>
      <q-card style="min-width: 400px;">
        <q-card-section>
          <div class="text-h6">Agregar fibra abierta a carrito de compras</div>
        </q-card-section>
        <q-card-section>
          <div class="row">
            <div class="col-xs-12">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="inBulkDetailQty"
                label="Cantidad"
                :rules="inBulkDetailQtyRules"
                :suffix="`/${formatter.format(inBulkDetailAvailableQty)} Kg.`"
              >
                <template v-slot:prepend>
                  <q-icon name="emoji_objects" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-6" style="padding-right: 10px;">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="unitPrice"
                label="Precio kg."
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-dollar-sign" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-6" style="padding-left: 10px;">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="inBulkPrice"
                label="Importe"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-dollar-sign" />
                </template>
              </q-input>
            </div>
          </div>
        </q-card-section>
        <q-card-actions align="right" class="text-primary">
          <q-btn flat label="Cancelar" @click="closeAddInBulkDetailModal()" v-close-popup />
          <q-btn flat label="Agregar" @click="addInBulkDetail()" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
  <q-page class="flex flex-center" v-else>
    <img alt="Alpez" src="~assets/logo.png">
  </q-page>
</template>

<style>
</style>

<script>
import api from '../commons/api.js'
const { required, decimal } = require('vuelidate/lib/validators')

export default {
  name: 'Index',
  validations: {
    fiberDetailQty: { required, decimal },
    laminateDetailQty: { required, decimal },
    inBulkDetailQty: { required, decimal }
  },
  data () {
    return {
      formatter: new Intl.NumberFormat('en-US'),
      currencyFormatter: new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
      }),
      products: [],
      categories: [],
      fibers: [],
      inBulkProducts: [],
      laminates: [],
      shoppingCartBaleDetails: [],
      shoppingCartInBulkDetails: [],
      shoppingCartLaminateDetails: [],
      categoryOptions: [
        { value: 5, label: 'LAMINADO' },
        { value: 6, label: 'FIBRAS' },
        { value: 13, label: 'FIBRA GRANEL' }
      ],
      fiberOptions: [],
      includeNotAvailable: false,
      price: {
        min: 0,
        max: 200
      },
      serverUrl: process.env.API,
      addFiberDetailModal: false,
      addLaminateDetailModal: false,
      addInBulkDetailModal: false,
      selectedProduct: null,
      fiberDetailQty: null,
      laminateDetailQty: null,
      inBulkDetailQty: null
    }
  },
  beforeCreate () {
    if (this.$store.getters['users/roles'].includes(14) || this.$store.getters['users/roles'].includes(16)) {
      this.$q.loading.show()
      api.get('/products/active').then(({ data }) => {
        this.products = data.products.filter(product => Number(product.category_id) === 5 || Number(product.category_id) === 6 || Number(product.category_id) === 13)
        this.price.min = this.minPrice
        this.price.max = this.maxPrice
        api.get('/storages/5/fibers').then(({ data }) => {
          this.fibers = data.products
          api.get('/storages/10/bulk-products').then(({ data }) => {
            this.inBulkProducts = data.products
            api.get('/storages/13/laminates').then(({ data }) => {
              this.laminates = data.products
              api.get('/shopping-cart-bale-details').then(({ data }) => {
                this.shoppingCartBaleDetails = data.details
                api.get('/shopping-cart-in-bulk-details').then(({ data }) => {
                  this.shoppingCartInBulkDetails = data.details
                  api.get('/shopping-cart-laminate-details').then(({ data }) => {
                    this.shoppingCartLaminateDetails = data.details
                    this.$q.loading.hide()
                  })
                })
              })
            })
          })
        })
      })
    }
  },
  computed: {
    filteredProducts () {
      const fibersProductsIds = []
      const inBulkProductsIds = []
      const laminatesProductsIds = []
      this.fibers.forEach(fiber => {
        fibersProductsIds.push(Number(fiber.product_id))
      })
      this.inBulkProducts.forEach(product => {
        inBulkProductsIds.push(Number(product.product_id))
      })
      this.laminates.forEach(laminate => {
        laminatesProductsIds.push(Number(laminate.product_id))
      })
      const f1Products = []
      this.products.forEach(product => {
        if (this.includeNotAvailable) {
          if (product.price >= this.price.min && product.price <= this.price.max) {
            f1Products.push(product)
          }
        } else {
          if ((fibersProductsIds.includes(Number(product.id)) || inBulkProductsIds.includes(Number(product.id)) || laminatesProductsIds.includes(Number(product.id))) && product.price >= this.price.min && product.price <= this.price.max) {
            f1Products.push(product)
          }
        }
      })
      if (this.categories.length > 0) {
        const f2Products = []
        f1Products.forEach(product => {
          if (this.categories.includes(Number(product.category_id))) {
            f2Products.push(product)
          }
        })
        return f2Products
      }
      return f1Products
    },
    minPrice () {
      return 0
    },
    maxPrice () {
      let maxPrice = 0
      this.products.forEach(product => {
        if (Number(product.price) > Number(maxPrice)) {
          maxPrice = Number(product.price)
        }
      })
      return maxPrice
    },
    unitPrice () {
      if (this.selectedProduct != null && this.selectedProduct.id != null) {
        return this.currencyFormatter.format(this.selectedProduct.price)
      }
      return this.currencyFormatter.format(0)
    },
    fiberPrice () {
      if (this.selectedProduct != null && this.selectedProduct.id != null && this.fiberDetailQty != null) {
        return this.currencyFormatter.format(this.fiberDetailQty * this.selectedProduct.price)
      }
      return this.currencyFormatter.format(0)
    },
    laminatePrice () {
      if (this.selectedProduct != null && this.selectedProduct.id != null && this.laminateDetailQty != null) {
        return this.currencyFormatter.format(this.laminateDetailQty * this.selectedProduct.price)
      }
      return this.currencyFormatter.format(0)
    },
    inBulkPrice () {
      if (this.selectedProduct != null && this.selectedProduct.id != null && this.inBulkDetailQty != null) {
        return this.currencyFormatter.format(this.inBulkDetailQty * this.selectedProduct.price)
      }
      return this.currencyFormatter.format(0)
    },
    totalPrice () {
      let price = 0
      this.shoppingCartBaleDetails.forEach(detail => {
        price += Number(detail.amount)
      })
      this.shoppingCartInBulkDetails.forEach(detail => {
        price += Number(detail.amount)
      })
      this.shoppingCartLaminateDetails.forEach(detail => {
        price += Number(detail.amount)
      })
      return price
    },
    fiberDetailQtyRules (val) {
      return [
        val => (this.$v.fiberDetailQty.required) || 'El campo Cantidad es requerido.',
        val => (this.$v.fiberDetailQty.decimal) || 'El campo Cantidad debe ser numérico.'
      ]
    },
    laminateDetailQtyRules (val) {
      return [
        val => (this.$v.laminateDetailQty.required) || 'El campo Cantidad es requerido.',
        val => (this.$v.laminateDetailQty.decimal) || 'El campo Cantidad debe ser numérico.'
      ]
    },
    inBulkDetailQtyRules (val) {
      return [
        val => (this.$v.inBulkDetailQty.required) || 'El campo Cantidad es requerido.',
        val => (this.$v.inBulkDetailQty.decimal) || 'El campo Cantidad debe ser numérico.'
      ]
    },
    fiberDetailAvailableQty () {
      if (this.selectedProduct != null && this.selectedProduct.id != null && Number(this.selectedProduct.category_id) === 6) {
        const filteredFibers = this.fibers.filter(fiber => Number(fiber.product_id) === Number(this.selectedProduct.id))
        if (filteredFibers.length === 1) {
          const availableQty = filteredFibers[0].stock
          return availableQty
        }
      }
      return 0
    },
    laminateDetailAvailableQty () {
      if (this.selectedProduct != null && this.selectedProduct.id != null && Number(this.selectedProduct.category_id) === 5) {
        const filteredLaminates = this.laminates.filter(laminate => Number(laminate.product_id) === Number(this.selectedProduct.id))
        if (filteredLaminates.length === 1) {
          const availableQty = filteredLaminates[0].stock
          return availableQty
        }
      }
      return 0
    },
    inBulkDetailAvailableQty () {
      if (this.selectedProduct != null && this.selectedProduct.id != null && Number(this.selectedProduct.category_id) === 13) {
        const filteredInBulkProducts = this.inBulkProducts.filter(inBulkProduct => Number(inBulkProduct.product_id) === Number(this.selectedProduct.id))
        if (filteredInBulkProducts.length === 1) {
          const availableQty = filteredInBulkProducts[0].stock
          return availableQty
        }
      }
      return 0
    }
  },
  methods: {
    addToFavorites (product) {
      alert('Agregar a favoritos')
    },
    openCompareModal (product) {
      alert('Comparar producto')
    },
    openDetailsModal (product) {
      alert('Ver detalles')
    },
    openAddDetailModal (product) {
      this.$q.loading.show()
      switch (Number(product.category_id)) {
        case 5:
          this.selectedProduct = product
          this.addLaminateDetailModal = true
          this.$q.loading.hide()
          break
        case 6:
          this.selectedProduct = product
          this.addFiberDetailModal = true
          this.$q.loading.hide()
          break
        case 13:
          this.selectedProduct = product
          this.addInBulkDetailModal = true
          this.$q.loading.hide()
          break
        default:
          this.$q.loading.hide()
          this.$q.notify({
            message: 'El producto seleccionado no se puede agregar a carrito de compras',
            position: 'top',
            color: 'warning'
          })
          break
      }
    },
    closeAddFiberDetailModal () {
      this.addFiberDetailModal = false
      this.selectedProduct = null
      this.fiberDetailQty = null
    },
    addFiberDetail () {
      this.$v.fiberDetailQty.$reset()
      this.$v.fiberDetailQty.$touch()
      if (this.$v.fiberDetailQty.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = []
      params.productId = Number(this.selectedProduct.id)
      params.qty = Number(this.fiberDetailQty)
      api.post('/shopping-cart-bale-details', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          api.get('/shopping-cart-bale-details').then(({ data }) => {
            this.shoppingCartBaleDetails = data.details
            this.$q.loading.hide()
            this.closeAddFiberDetailModal()
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    closeAddLaminateDetailModal () {
      this.addLaminateDetailModal = false
      this.selectedProduct = null
      this.laminateDetailQty = null
    },
    addLaminateDetail () {
      this.$v.laminateDetailQty.$reset()
      this.$v.laminateDetailQty.$touch()
      if (this.$v.laminateDetailQty.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = []
      params.productId = Number(this.selectedProduct.id)
      params.qty = Number(this.laminateDetailQty)
      api.post('/shopping-cart-laminate-details', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          api.get('/shopping-cart-laminate-details').then(({ data }) => {
            this.shoppingCartLaminateDetails = data.details
            this.$q.loading.hide()
            this.closeAddLaminateDetailModal()
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    closeAddInBulkDetailModal () {
      this.addInBulkDetailModal = false
      this.selectedProduct = null
      this.inBulkDetailQty = null
    },
    addInBulkDetail () {
      this.$v.inBulkDetailQty.$reset()
      this.$v.inBulkDetailQty.$touch()
      if (this.$v.inBulkDetailQty.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = []
      params.productId = Number(this.selectedProduct.id)
      params.qty = Number(this.inBulkDetailQty)
      api.post('/shopping-cart-in-bulk-details', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          api.get('/shopping-cart-in-bulk-details').then(({ data }) => {
            this.shoppingCartInBulkDetails = data.details
            this.$q.loading.hide()
            this.closeAddInBulkDetailModal()
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    removeBaleDetail (id) {
      this.$q.loading.show()
      api.delete(`/shopping-cart-bale-details/${id}`).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          api.get('/shopping-cart-bale-details').then(({ data }) => {
            this.shoppingCartBaleDetails = data.details
            this.$q.loading.hide()
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    removeInBulkDetail (id) {
      this.$q.loading.show()
      api.delete(`/shopping-cart-in-bulk-details/${id}`).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          api.get('/shopping-cart-in-bulk-details').then(({ data }) => {
            this.shoppingCartInBulkDetails = data.details
            this.$q.loading.hide()
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    removeLaminateDetail (id) {
      this.$q.loading.show()
      api.delete(`/shopping-cart-laminate-details/${id}`).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          api.get('/shopping-cart-laminate-details').then(({ data }) => {
            this.shoppingCartLaminateDetails = data.details
            this.$q.loading.hide()
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    removeShoppingCart () {
      this.$q.loading.show()
      api.delete('/shopping-carts').then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          api.get('/shopping-cart-bale-details').then(({ data }) => {
            this.shoppingCartBaleDetails = data.details
            api.get('/shopping-cart-in-bulk-details').then(({ data }) => {
              this.shoppingCartInBulkDetails = data.details
              api.get('/shopping-cart-laminate-details').then(({ data }) => {
                this.shoppingCartLaminateDetails = data.details
                this.$q.loading.hide()
              })
            })
          })
        } else {
          this.$q.loading.hide()
        }
      })
    }
  }
}
</script>

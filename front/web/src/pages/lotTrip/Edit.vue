<template>
  <q-page>
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-9">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Embarques" to='/trips' />
              <q-breadcrumbs-el :label="principalSerial" :to='idUrlBack' />
              <q-breadcrumbs-el label="Editar envio" v-text= "lot.fields.folio"/>
            </q-breadcrumbs>
          </div>
        </div>
        <!-- <div class="col-sm-3 pull-right" v-if="saveFlag">
          <div class="q-pa-sm q-gutter-sm">
            <q-btn color="positive"  icon="offline_bolt" label="Guardar" @click="saveShipping()"/>
          </div>
        </div> -->
      </div>
    </div>

    <div class="q-pa-md bg-grey-3">
      <div class="row bg-white border-panel">
        <div class="col q-pa-md">
          <div class="row q-mb-sm" style="visibility: hidden;">
            <div class="col-sm-1 offset-11 pull-right">
              <q-btn color="primary" label="Editar" />
            </div>
          </div>
          <div class="row q-col-gutter-xs">
            <div class="col-xs-12 col-sm-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                disable
                v-model="lot.fields.folio"
                label="Folio"
              >
                <template v-slot:prepend>
                  <q-icon name="fingerprint" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-3 text-center">
                <q-select color="dark"
                bg-color="secondary"
                filled
                :disable="status !== 'NUEVO'"
                :rules="dateRules"
                v-model="lot.fields.date"
                :error="$v.lot.fields.date.$error"
                mask="date"
                label="Fecha">
                <template v-slot:prepend>
                    <q-icon name="event"></q-icon>
                </template>
                <q-popup-proxy
                ref="date_ref"
                transition-show="scale"
                transition-hide="scale">
                    <div class="col-sm-12">
                        <q-date
                        color="secondary"
                        text-color="white"
                        mask="DD/MM/YYYY"
                        v-model="lot.fields.date"
                        @input="() => $refs.date_ref.hide()"
                        today-btn>
                        </q-date>
                    </div>
                </q-popup-proxy>
                </q-select>
            </div>
            <div class="col-xs-12 col-sm-6">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                :disable="status !== 'NUEVO'"
                v-model="lot.fields.client"
                :error="$v.lot.fields.client.$error"
                label="Cliente"
                :rules="clientRules"
                @filter="filterClient"
                :options="clientOptionsFilter"
                use-input
                hide-selected
                fill-input
                input-debounce="0"
                hint="Basic autocomplete"
                emit-value
                map-options
                >
                <template v-slot:prepend>
                  <q-icon name="person" />
                </template>
                <template v-slot:no-option>
                  <q-item>
                    <q-item-section class="groups">
                      No hay Resultados
                    </q-item-section>
                  </q-item>
                </template>
              </q-select>
            </div>
          </div>
          <div v-if="status === 'NUEVO'" class="row q-mb-sm q-mt-md">
            <div class="col-xs-12 col-sm-2 offset-sm-10 pull-right">
              <q-btn color="positive" icon="save" label="Actualizar" @click="updateLotTrip()" />
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="q-pa-md bg-grey-3">
      <div class="row bg-white border-panel">
        <div class="col q-pa-md">
          <div v-if="status === 'NUEVO'" class="row q-col-gutter-xs">
          <!-- <div class="col-xs-12 col-md-3" style="padding: 3px;">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-if="!editFlag"
                v-model="branchOffice"
                :options="branchOfficeOptions"
                label="Sucursal"
                @input="() => {storage = { value: null, label: 'TODOS' }}"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-store-alt" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-md-3" style="padding: 3px;">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-if="!editFlag"
                v-model="storage"
                :options="filteredStorageOptions"
                label="Almacén"
                @input="getProductsByStorage()"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-warehouse" />
                </template>
              </q-select>
            </div> -->
            <div class="col-xs-12 col-sm-10">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                :disabled="editFlag"
                v-model="lotProducts.fields.product"
                :error="$v.lotProducts.fields.product.$error"
                label="Producto"
                :rules="productRules"
                :options="productsOptionsFilter"
                @filter="filterProducts"
                use-input
                emit-value
                map-options
                @input="getStock()"
                >
                <template v-slot:prepend>
                  <q-icon name="shopping_cart" />
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
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="lotProducts.fields.qty"
                :rules="qtyRules"
                :error="$v.lotProducts.fields.qty.$error"
                label="Cantidad"
                hint="Basic autocomplete"
                :suffix="`/${selectProductStock}`"
                emit-value
                map-options
                >
                <template v-slot:prepend>
                  <q-icon name="production_quantity_limits" />
                </template>
              </q-input>
            </div>
            <div v-if="!editFlag" class="col-xs-12 col-sm-2 offset-sm-10 pull-right">
              <q-btn color="positive" icon="add" label="Agregar" @click="addProduct()" />
            </div>
            <div v-if="editFlag" class="col-xs-12 col-sm-12 pull-right">
              <q-btn color="negative" style="margin-right: 7px" icon="cancel" label="Cancelar" @click="cancelEditProdduct()" />
              <q-btn color="positive" icon="add" label="Actualizar" @click="editProduct()" />
            </div>
          </div>
            <div style="margin-top: 10px" class="row bg-white border-panel">
              <div class="col q-pa-md">
                <q-table
                  flat
                  bordered
                  :data="lotProductData"
                  :columns="status === 'NUEVO' ? lotProductColumns:lotProductColumnsExit"
                  row-key="code"
                >
                  <template v-slot:body="props">
                    <q-tr :props="props">
                      <q-td key="code" style="text-align: center;" :props="props">{{ props.row.code }}</q-td>
                      <q-td key="name" style="text-align: left;" :props="props">{{ props.row.product }}</q-td>
                      <q-td key="category" style="text-align: left;" :props="props">{{ props.row.category }}</q-td>
                      <q-td key="line" style="text-align: left;" :props="props">{{ props.row.line }}</q-td>
                      <q-td key="qty" style="text-align: center;" :props="props">{{ props.row.qty }}</q-td>
                      <q-td key="actions" style="text-align: center;" :props="props">
                        <q-btn color="primary" icon="fas fa-edit" flat @click.native="selectEditRow(props.row.id)" size="10px">
                          <q-tooltip content-class="bg-primary">Editar</q-tooltip>
                        </q-btn>
                        <q-btn color="red" icon="fas fa-trash-alt" flat @click.native="deleteSelectProductRow(props.row.id)" size="10px">
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

  </q-page>
</template>

<script>
import api from '../../commons/api.js'
const { required } = require('vuelidate/lib/validators')

export default {
  name: 'EditShipping',
  validations: {
    lot: {
      fields: {
        date: { required },
        client: { required }
      }
    },
    lotProducts: {
      fields: {
        product: { required },
        qty: { required }
      }
    },
    branchOffice: { required },
    storage: { required }
  },
  data () {
    return {
      lot: {
        fields: {
          id: Number(this.$route.params.id),
          folio: null,
          date: null,
          client: null,
          trip_id: null
        }
      },
      lotProducts: {
        fields: {
          shipping_id: Number(this.$route.params.id),
          product: null,
          qty: null
        }
      },
      idUrlBack: null,
      clientOptions: [],
      clientOptionsFilter: [],
      lotProductsOptions: [],
      productsOptionsFilter: [],
      lotProductData: [],
      lotProductColumns: [
        { name: 'code', align: 'center', label: 'CÓDIGO', field: 'code', sortable: false },
        { name: 'name', align: 'center', label: 'PRODUCTO', field: 'name', sortable: false },
        { name: 'qty', align: 'center', label: 'CANTIDAD', field: 'qty', sortable: false },
        { name: 'actions', align: 'center', label: 'ACCIONES', field: 'actions', sortable: false }
      ],
      lotProductColumnsExit: [
        { name: 'code', align: 'center', label: 'CÓDIGO', field: 'code', sortable: false },
        { name: 'name', align: 'center', label: 'PRODUCTO', field: 'name', sortable: false },
        { name: 'qty', align: 'center', label: 'CANTIDAD', field: 'qty', sortable: false }
      ],
      editFlag: false,
      selectRow: null,
      branchOffice: { value: null, label: 'TODAS' },
      storage: { value: null, label: 'TODOS' },
      branchOfficeOptions: [],
      storageOptions: [],
      selectProductStock: null,
      principalSerial: null,
      saveFlag: false,
      status: null
    }
  },
  computed: {
    dateRules (val) {
      return [
        val => (this.$v.lot.fields.date.required) || 'El campo Fecha es requerido.'
      ]
    },
    clientRules (val) {
      return [
        val => (this.$v.lot.fields.client.required) || 'El campo Cliente es requerido.'
      ]
    },
    productRules (val) {
      return [
        val => (this.$v.lotProducts.fields.product.required) || 'El campo Producto es requerido.'
      ]
    },
    qtyRules (val) {
      return [
        val => (this.$v.lotProducts.fields.qty.required) || 'El campo Cantidad es requerido.'
      ]
    },
    filteredStorageOptions () {
      const options = (this.branchOffice != null && this.branchOffice.value != null) ? this.storageOptions.filter(so => Number(so.branchOffice) === Number(this.branchOffice.value)) : this.storageOptions
      options.unshift({ value: null, label: 'TODOS' })
      return options
    }
  },
  beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(10)) {
      this.$router.push('/')
    }
  },
  created () {
    this.$q.loading.show()
    this.fetchFromServer()
    this.getDetails()
    this.getClient()
    // this.getProducts()
    // this.getStoragOptions()
    this.getProductsByStorage()
    this.$q.loading.hide()
  },
  methods: {
    fetchFromServer () {
      this.$q.loading.show()
      const id = this.$route.params.id
      api.get(`/shippings/${id}`).then(({ data }) => {
        const shipping = data.shipping
        this.lot.fields.id = shipping.id
        this.lot.fields.folio = shipping.serial
        this.lot.fields.date = shipping.date.substr(8, 2) + '/' + shipping.date.substr(5, 2) + '/' + shipping.date.substr(0, 4)
        this.lot.fields.client = { value: shipping.client_id, label: shipping.client_name }
        this.lot.fields.trip_id = shipping.trip_id
        this.idUrlBack = '/trips/' + shipping.trip_id
        this.status = shipping.status
        this.principalSerial = shipping.serial.substr(0, 9)
        this.$q.loading.hide()
      })
    },
    getClient () {
      api.get('customers/options').then(data => {
        this.clientOptions = data.data.options
      })
    },
    filterClient (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.clientOptionsFilter = this.clientOptions.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    updateLotTrip () {
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
      this.$q.loading.show()
      const params = { ...this.lot.fields }
      params.client = this.lot.fields.client
      if (this.lot.fields.client.value) {
        params.client = this.lot.fields.client.value
      }
      params.date = this.lot.fields.date.substr(6, 10) + '-' + this.lot.fields.date.substr(3, 2) + '-' + this.lot.fields.date.substr(0, 2)
      api.put(`/shippings/${params.id}`, params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
        } else {
          this.$q.loading.hide()
        }
      })
    },
    getDetails () {
      const id = this.$route.params.id
      api.get(`/shipping-details/all/${id}`).then(({ data }) => {
        this.lotProductData = data.products
      })
    },
    /* getProducts () {
      api.get('shipping-details/options').then(data => {
        this.lotProductsOptions = data.data.options
      })
    }, */
    filterProducts (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.productsOptionsFilter = this.lotProductsOptions.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    clearInputsDetails () {
      this.lotProducts.fields.product = null
      this.lotProducts.fields.qty = null
      this.branchOffice = { value: null, label: 'TODAS' }
      this.storage = { value: null, label: 'TODOS' }
    },
    addProduct () {
      this.$v.lotProducts.fields.$reset()
      this.$v.lotProducts.fields.$touch()
      if (this.$v.lotProducts.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      if (Number(this.lotProducts.fields.qty) > Number(this.selectProductStock)) {
        this.$q.dialog({
          title: 'Error',
          message: 'La cantidad ingresada es mayor a la cantidad del inventario.',
          persistent: true
        })
        return false
      }
      const params = { ...this.lotProducts.fields }
      this.$q.loading.show()
      api.post('/shipping-details/', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.saveFlag = true
          this.$q.loading.hide()
          this.clearInputsDetails()
          this.getDetails()
        } else {
          this.$q.loading.hide()
        }
      })
    },
    selectEditRow (id) {
      this.$q.loading.show()
      this.editFlag = true
      this.selectRow = id
      api.get(`/shipping-details/${id}`).then(data => {
        this.lotProducts.fields.product = { value: Number(data.data.product.id), label: data.data.product.product }
        this.lotProducts.fields.qty = Number(data.data.product.qty)
        api.get(`/shipping-details/productInventoryOptions/null/null/null/null/${Number(data.data.product.id)}/null`).then(({ data }) => {
          if (data.result) {
            this.selectProductStock = data.stock[0].stock
          }
          this.$q.loading.hide()
        })
      })
    },
    cancelEditProdduct () {
      this.editFlag = false
      this.selectRow = null
      this.clearInputsDetails()
    },
    editProduct () {
      this.$v.lotProducts.fields.$reset()
      this.$v.lotProducts.fields.$touch()
      if (this.$v.lotProducts.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      if (Number(this.lotProducts.fields.qty) > Number(this.selectProductStock)) {
        this.$q.dialog({
          title: 'Error',
          message: 'La cantidad ingresada es mayor a la cantidad del inventario.',
          persistent: true
        })
        return false
      }
      const params = { ...this.lotProducts.fields }
      params.product = this.lotProducts.fields.product
      if (this.lotProducts.fields.product.value) {
        params.product = this.lotProducts.fields.product.value
      }
      this.$q.loading.show()
      api.put(`/shipping-details/${this.selectRow}`, params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.saveFlag = true
          this.cancelEditProdduct()
          this.getDetails()
          this.$q.loading.hide()
        } else {
          this.$q.loading.hide()
        }
      })
    },
    deleteSelectProductRow (id) {
      this.$q.dialog({
        title: 'Confirmación',
        message: '¿Desea eliminar este Producto',
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
        api.delete(`/shipping-details/${id}`).then(({ data }) => {
          this.$q.notify({
            message: data.message.content,
            position: 'top',
            color: (data.result ? 'positive' : 'warning'),
            icon: (data.result ? 'thumb_up' : 'close')
          })
          if (data.result) {
            this.saveFlag = true
            this.getDetails()
          }
        })
      }).onCancel(() => {})
    },
    /* getStoragOptions () {
      api.get('/branch-offices/options').then(({ data }) => {
        this.branchOfficeOptions = data.options
        this.branchOfficeOptions.unshift({ value: null, label: 'TODOS' })
        api.get('/storages/options').then(({ data }) => {
          this.storageOptions = data.options
        })
      })
    }, */
    getProductsByStorage () {
      this.$q.loading.show()
      api.get('/shipping-details/productInventoryOptions/5/28/null/null/null/nul').then(({ data }) => {
        if (data.result) {
          this.lotProductsOptions = data.stock
        }
        this.$q.loading.hide()
      })
    },
    getStock () {
      this.$q.loading.show()
      let product = this.lotProducts.fields.product
      if (this.lotProducts.fields.product.value) {
        product = this.lotProducts.fields.product.value
      }
      api.get(`/shipping-details/productInventoryOptions/null/null/null/null/${product}/null`).then(({ data }) => {
        if (data.result) {
          this.selectProductStock = data.stock[0].stock
        }
        this.$q.loading.hide()
      })
    },
    saveShipping () {
      this.$router.push(this.idUrlBack)
    }
  }
}
</script>

<style>
</style>

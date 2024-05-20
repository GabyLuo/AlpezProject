<template>
  <q-page>
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-8">
          <q-btn color="primary" icon="keyboard_backspace" label="Regresar" @click="backToGrid()" />
          <span class="q-ml-md grey-8 fs28 page-title">Apertura de paca {{ $route.params.id }}</span>
        </div>
        <div class="col-sm-4">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="right">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Apertura de pacas" to="/bale-opening" />
              <q-breadcrumbs-el label="Editar" />
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
                v-model="baleOpening.fields.date"
                mask="date"
                label="Fecha"
                :rules="dateRules"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="event" />
                </template>
                <q-popup-proxy
                  ref="baleOpeningFieldsSaleDate"
                  transition-show="scale"
                  transition-hide="scale"
                >
                  <div class="col-sm-12">
                    <q-date
                      v-model="baleOpening.fields.date"
                      @input="() => $refs.baleOpeningFieldsSaleDate.hide()"
                      today-btn
                    />
                  </div>
                </q-popup-proxy>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-6 text-center">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="baleOpening.fields.branchOffice"
                :options="branchOfficeOptions"
                label="Sucursal"
                @input="() => {baleOpening.fields.baleStorage = null;baleOpening.fields.inBulkStorage = null}"
                :rules="branchOfficeRules"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-store-alt" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-3 text-center">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="baleOpening.fields.status"
                label="Estatus"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="battery_full" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-6 text-center">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="baleOpening.fields.baleStorage"
                :options="filteredStorageOptions"
                label="Almacén de pacas"
                :rules="baleStorageRules"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-warehouse" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-6 text-center">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="baleOpening.fields.inBulkStorage"
                :options="filteredStorageOptions"
                label="Almacén fibra abierta"
                :rules="inBulkStorageRules"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-warehouse" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-6 text-center">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="baleOpening.fields.operator"
                :options="operatorOptions"
                label="Operador"
                :rules="operatorRules"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="fa fa-user" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-6 text-center">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="baleOpening.fields.openedBy"
                label="Responsable"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="fa fa-user" />
                </template>
              </q-input>
            </div>
          </div>

          <div class="row q-mb-sm q-mt-md">
            <div class="col-xs-12 col-sm-2 offset-sm-10 pull-right" v-if="baleOpening.fields.status == 'NUEVO'">
              <q-btn color="warning" icon="fas fa-bolt" label="Ejecutar" @click="execute()" style="margin-right: 5px;" />
            </div>
          </div>
        </div>
      </div>

      <br>

      <div class="row bg-white border-panel">
        <div class="col q-pa-md">
          <div class="row q-col-gutter-xs" style="padding-right: 2%;" v-if="baleOpening.fields.status == 'NUEVO'">
            <div class="col-xs-12 col-sm-4 offset-sm-8 pull-right">
              <q-btn color="primary" icon="add" label="Agregar" @click="addDetail()" />
            </div>
          </div>

          <div class="row q-col-gutter-xs" style="padding: 2%;" v-if="baleOpening.fields.status == 'NUEVO'">
            <div class="col-xs-12 col-sm-4">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                ref="detailFieldsBaleRef"
                v-model="detail.fields.product"
                :options="productOptions"
                label="Producto Paca"
                :rules="productRules"
                @input="() => detail.fields.bale = null"
              >
                <template v-slot:prepend>
                  <q-icon name="emoji_objects" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-4">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                ref="detailFieldsBaleRef"
                v-model="detail.fields.bale"
                :options="filteredBaleOptions"
                label="Paca"
                :rules="baleRules"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-th-large" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-4">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                ref="detailFieldsBaleRef"
                v-model="detail.fields.bulkFiberProduct"
                :options="bulkFiberProductOptions"
                label="Producto fibra abierta"
                :rules="bulkFiberProductRules"
              >
                <template v-slot:prepend>
                  <q-icon name="emoji_objects" />
                </template>
              </q-select>
            </div>
          </div>
          <div class="q-col-gutter-xs" style="padding: 2%;">
            <q-table
              flat
              bordered
              :data="details"
              :columns="detailsColumns"
              row-key="bale_id"
              :pagination.sync="pagination"
            >
              <template v-slot:body="props">
                <q-tr :props="props">
                  <q-td key="bale_id" style="text-align: left; width: 20%;" :props="props">{{ (props.row.bale_id ? `PACA ${props.row.bale_id}` : null) }}</q-td>
                  <q-td key="bale_product_name" style="text-align: left; width: 25%;" :props="props">{{ props.row.bale_product_name }}</q-td>
                  <q-td key="bulk_product_name" style="text-align: left; width: 25%;" :props="props">{{ props.row.bulk_product_name }}</q-td>
                  <q-td key="qty" style="text-align: right; width: 15%;" :props="props">{{ `${formatter.format(props.row.qty)} KG.` }}</q-td>
                  <q-td key="actions" style="width: 10%;" :props="props" v-if="props.row.id && baleOpening.fields.status == 'NUEVO'">
                    <q-btn color="primary" icon="fas fa-trash-alt" flat @click.native="removeDetail(props.row.id)" size="10px">
                      <q-tooltip content-class="bg-red">Eliminar</q-tooltip>
                    </q-btn>
                  </q-td>
                  <q-td key="actions" style="width: 10%;" :props="props" v-else></q-td>
                </q-tr>
              </template>
            </q-table>
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
  name: 'EditBaleOpening',
  validations: {
    baleOpening: {
      fields: {
        date: { required },
        operator: { required },
        branchOffice: { required },
        baleStorage: { required },
        inBulkStorage: { required },
        openedBy: { required }
      }
    },
    detail: {
      fields: {
        product: { required },
        bale: { required },
        bulkFiberProduct: { required }
      }
    }
  },
  data () {
    return {
      formatter: new Intl.NumberFormat('en-US'),
      baleOpening: {
        fields: {
          id: null,
          date: null,
          operator: null,
          branchOffice: null,
          baleStorage: null,
          inBulkStorage: null,
          openedBy: null,
          status: null
        }
      },
      detail: {
        fields: {
          id: null,
          product: null,
          bale: null,
          bulkFiberProduct: null
        }
      },
      branchOfficeOptions: [],
      storageOptions: [],
      operatorOptions: [],
      productOptions: [],
      bulkFiberProductOptions: [],
      availableBales: [],
      details: [],
      detailsColumns: [
        { name: 'bale_id', align: 'center', label: 'Paca', field: 'bale_id', style: 'width: 20%', sortable: true },
        { name: 'bale_product_name', align: 'center', label: 'Producto Paca', field: 'bale_product_name', style: 'width: 30%', sortable: true },
        { name: 'bulk_product_name', align: 'center', label: 'Producto fibra abierta', field: 'bulk_product_name', style: 'width: 30%', sortable: true },
        { name: 'qty', align: 'center', label: 'Peso', field: 'qty', style: 'width: 10%', sortable: true },
        { name: 'actions', align: 'center', label: 'Acciones', field: 'actions', style: 'width: 10%', sortable: false }
      ],
      pagination: {
        rowsPerPage: 50
      }
    }
  },
  computed: {
    dateRules (val) {
      return [
        val => (this.$v.baleOpening.fields.date.required) || 'El campo Fecha de venta es requerido.'
      ]
    },
    branchOfficeRules (val) {
      return [
        val => this.$v.baleOpening.fields.branchOffice.required || 'El campo Sucursal es requerido.'
      ]
    },
    operatorRules (val) {
      return [
        val => this.$v.baleOpening.fields.operator.required || 'El campo Operador es requerido.'
      ]
    },
    baleStorageRules (val) {
      return [
        val => this.$v.baleOpening.fields.baleStorage.required || 'El campo Almacén de pacas es requerido.'
      ]
    },
    inBulkStorageRules (val) {
      return [
        val => this.$v.baleOpening.fields.inBulkStorage.required || 'El campo Almacén fibra abierta es requerido.'
      ]
    },
    productRules (val) {
      return [
        val => this.$v.detail.fields.product.required || 'El campo Producto Paca es requerido.'
      ]
    },
    baleRules (val) {
      return [
        val => (this.$v.detail.fields.bale.required) || 'El campo Paca es requerido.'
      ]
    },
    bulkFiberProductRules (val) {
      return [
        val => (this.$v.detail.fields.bulkFiberProduct.required) || 'El campo Producto fibra abierta es requerido.'
      ]
    },
    filteredStorageOptions () {
      let options = []
      if (this.baleOpening.fields.branchOffice != null && this.baleOpening.fields.branchOffice.value != null) {
        options = this.storageOptions.filter(so => (Number(so.branchOffice) === Number(this.baleOpening.fields.branchOffice.value)))
      }
      return options
    },
    filteredBaleOptions () {
      if (this.detail.fields.product && !isNaN(this.detail.fields.product.value)) {
        return this.availableBales.filter(bale => Number(bale.product_id) === Number(this.detail.fields.product.value))
      }
      return []
    },
    totalDetailsQties () {
      const formatter = new Intl.NumberFormat('en-US')
      let totalQties = 0
      this.details.forEach(detail => {
        if (detail.qty) {
          totalQties += Number(detail.qty)
        }
      })
      return formatter.format(totalQties)
    }
  },
  beforeCreate () {
    if (!(this.$store.getters['users/roles'].includes(1) || this.$store.getters['users/roles'].includes(3) || this.$store.getters['users/roles'].includes(7) || this.$store.getters['users/roles'].includes(2) || this.$store.getters['users/roles'].includes(3) || this.$store.getters['users/roles'].includes(4) || this.$store.getters['users/roles'].includes(5) || this.$store.getters['users/roles'].includes(13))) {
      this.$router.push('/')
    }
  },
  created () {
    this.fetchFromServer()
  },
  methods: {
    fetchFromServer () {
      this.$q.loading.show()
      const id = this.$route.params.id
      api.get(`/bale-openings/${id}`).then(({ data }) => {
        if (!data.baleOpening) {
          this.$router.push('/bale-opening')
        } else {
          this.baleOpening.fields.id = id
          this.baleOpening.fields.date = data.baleOpening.date
          this.baleOpening.fields.operator = { value: data.baleOpening.operator_id, label: data.baleOpening.operator_name }
          this.baleOpening.fields.branchOffice = { value: data.baleOpening.branch_office_id, label: data.baleOpening.branch_office_name }
          this.baleOpening.fields.baleStorage = { value: data.baleOpening.bale_storage_id, label: data.baleOpening.bale_storage_name }
          this.baleOpening.fields.inBulkStorage = data.baleOpening.in_bulk_storage_id && data.baleOpening.in_bulk_storage_name ? { value: data.baleOpening.in_bulk_storage_id, label: data.baleOpening.in_bulk_storage_name } : null
          this.baleOpening.fields.openedBy = data.baleOpening.opened_by
          this.baleOpening.fields.status = data.baleOpening.status
          api.get('/products/options/category/13').then(({ data }) => {
            this.bulkFiberProductOptions = data.options
            api.get(`/bale-opening-details/${id}`).then(({ data }) => {
              this.details = data.details
              const balesUsed = []
              data.details.forEach(detail => {
                balesUsed.push(Number(detail.bale_id))
              })
              api.get(`/storages/${this.baleOpening.fields.baleStorage.value}/bales`).then(({ data }) => {
                this.availableBales = []
                const availableBales = data.bales
                availableBales.forEach(bale => {
                  if (!balesUsed.includes(Number(bale.bale_id))) {
                    this.availableBales.push({ value: bale.bale_id, label: `PACA ${bale.bale_id} (${bale.stock} KG.)`, product_id: bale.product_id })
                  }
                  if (this.productOptions.filter(po => Number(po.value) === Number(bale.product_id)).length === 0) {
                    this.productOptions.push({ value: bale.product_id, label: `${bale.category_code}-${bale.line_code}-${bale.product_name}` })
                  }
                })
                this.$q.loading.hide()
              })
            })
          })
        }
      })
    },
    backToGrid () {
      this.$router.push('/bale-opening')
    },
    execute () {
      this.$q.loading.show()
      api.put(`/bale-openings/execute/${this.$route.params.id}`).then(({ data }) => {
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
      if (this.detail.fields.bale.value == null) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, seleccione la paca.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = []
      params.baleOpeningId = Number(this.$route.params.id)
      params.baleId = Number({ ...this.detail.fields }.bale.value)
      params.bulkFiberProductId = Number({ ...this.detail.fields }.bulkFiberProduct.value)
      api.post('/bale-opening-details', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.detail.fields.id = null
          this.detail.fields.product = null
          this.detail.fields.bale = null
          this.detail.fields.bulkFiberProduct = null
          this.$q.loading.hide()
          this.fetchFromServer()
        } else {
          this.$q.loading.hide()
        }
      })
    },
    removeDetail (id) {
      this.$q.loading.show()
      api.delete(`/bale-opening-details/${id}`).then(({ data }) => {
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
    }
  }
}
</script>

<style>
</style>

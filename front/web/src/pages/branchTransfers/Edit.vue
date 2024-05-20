<template>
  <q-page>
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-12 col-md-8">
          <span class="q-ml-md grey-8 fs28 page-title">Traspaso Sucursales {{ $route.params.id }}</span>
        </div>
        <div class="col-sm-12 col-md-4">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="right">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Traspasos Sucursales" to="/branch-transfers" />
              <q-breadcrumbs-el label="Editar" />
            </q-breadcrumbs>
          </div>
        </div>
      </div>
    </div>

    <div class="q-pa-md bg-grey-3">
      <div class="row bg-white border-panel">
        <div class="col q-pa-md">
          <div class="row q-mb-sm">
            <div class="col-sm-12 col-md-2 pull-left">
              <q-btn color="positive" icon="fas fa-file-pdf" label="Ver reporte" @click="openBranchTransferPdf()" style="margin-right: 5px;" v-if="branchTrasfer.fields.status == 1" />
            </div>
            <div class="col-sm-12 col-md-2 offset-md-8 pull-right">
              <q-btn color="warning" icon="fas fa-bolt" label="Ejecutar" @click="executeTransfer()" style="margin-right: 5px;" v-if="branchTrasfer.fields.status == 0 && (bales.length > 0 || laminates.length > 0 || inBulks.length > 0 || rawMaterials.length > 0)" />
            </div>
          </div>
          <div class="row q-col-gutter-xs">
            <div class="col-sm-12 col-md-6">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="branchTrasfer.fields.originBranchOffice"
                :options="branchOfficeOptions"
                label="Sucursal origen"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-store-alt" />
                </template>
              </q-select>
            </div>
            <div class="col-sm-12 col-md-4">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="branchTrasfer.fields.originStorage"
                :options="storageOptions"
                label="Almacén origen"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-warehouse" />
                </template>
              </q-select>
            </div>
            <div class="col-sm-12 col-md-2">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="branchTrasfer.fields.statusStr"
                label="Estatus"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="battery_full" />
                </template>
              </q-input>
            </div>
            <div class="col-sm-12 col-md-6">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="branchTrasfer.fields.destinationBranchOffice"
                :options="branchOfficeOptions"
                label="Sucursal destino"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-store-alt" />
                </template>
              </q-select>
            </div>
            <div class="col-sm-12 col-md-4">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="branchTrasfer.fields.destinationStorage"
                :options="filteredDestinationStorage"
                label="Almacén destino"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-warehouse" />
                </template>
              </q-select>
            </div>
            <!-- <div class="col-sm-12 col-md-2">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="branchTrasfer.fields.operator"
                :options="operatorOptions"
                label="Chofer"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="fa fa-user" />
                </template>
              </q-select>
            </div> -->
          </div>
        </div>
      </div>
      <br>
      <div class="bg-white border-panel">
        <q-tabs v-model="currentTab" dense class="text-grey" active-color="primary" indicator-color="primary" align="justify" narrow-indicator @input="changeModel">
          <q-tab name="bales" :disable="baleTab" label="Pacas de producto terminado" />
          <q-tab name="bulk" :disable="inBulkTab" label="Fibra Abierta" />
          <q-tab name="laminates" :disable="lamiTab" label="Laminados" />
          <q-tab name="rawMaterials" :disable="rawTab" label="Materias primas" />
        </q-tabs>
        <q-separator />
        <q-tab-panels v-model="currentTab" animated>
          <q-tab-panel name="bales">
            <div style="font-weight: normal;">
              <div class="row q-col-gutter-xs" style="padding-right: 2%;" v-if="branchTrasfer.fields.status == 0">
                <div class="col-xs-12 col-sm-4 offset-sm-8 pull-right">
                  <q-btn color="primary" icon="add" label="Agregar" @click="addBale()"/>
                </div>
              </div>
              <div class="row q-col-gutter-xs" style="padding: 2%;" v-if="branchTrasfer.fields.status == 0">
                <div class="col-sm-12 col-md-6">
                  <q-select
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="bale.fields.product"
                    :error="$v.bale.fields.product.$error"
                    :options="baleProductOptions"
                    label="Producto"
                    :rules="baleProductRules"
                    @input="bale.fields.bale = null"
                  >
                    <template v-slot:prepend>
                      <q-icon name="emoji_objects" />
                    </template>
                  </q-select>
                </div>
                <div class="col-sm-12 col-md-6">
                  <q-select
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="bale.fields.bale"
                    :error="$v.bale.fields.bale.$error"
                    :options="filteredBalesOptions"
                    label="Paca"
                    :rules="baleBaleRules"
                  >
                    <template v-slot:prepend>
                      <q-icon name="fas fa-th-large" />
                    </template>
                  </q-select>
                </div>
              </div>
              <div class="q-col-gutter-xs" style="padding: 2%;">
                <q-table
                  flat
                  bordered
                  :data="bales"
                  :columns="balesColumns"
                  row-key="id"
                  :pagination.sync="pagination"
                  :filter="filter"
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
                      <q-td key="id" style="text-align: right; width: 10%;" :props="props">{{ props.row.id }}</q-td>
                      <q-td key="product" style="text-align: left; width: 75%;" :props="props">{{ props.row.product_name }}</q-td>
                      <q-td key="qty" style="text-align: right; width: 10%;" :props="props">{{ formatPrice(props.row.qty) }} KG</q-td>
                      <q-td key="actions" style="text-align: center; width: 5%;" :props="props" v-if="branchTrasfer.fields.status == 0">
                        <q-btn color="primary" icon="fas fa-trash-alt" flat @click.native="removeBale(props.row.id)" size="10px">
                          <q-tooltip content-class="bg-red">Eliminar</q-tooltip>
                        </q-btn>
                      </q-td>
                      <q-td key="actions" style="text-align: center; width: 5%;" :props="props" v-else>
                        -
                      </q-td>
                    </q-tr>
                  </template>
                </q-table>
              </div>
            </div>
          </q-tab-panel>
          <q-tab-panel name="bulk">
            <div style="font-weight: normal;">
              <div class="row q-col-gutter-xs" style="padding-right: 2%;" v-if="branchTrasfer.fields.status == 0">
                <div class="col-xs-12 col-sm-4 offset-sm-8 pull-right">
                  <q-btn color="primary" icon="add" label="Agregar" @click="addBulk()"/>
                </div>
              </div>
              <div class="row q-col-gutter-xs" style="padding: 2%;" v-if="branchTrasfer.fields.status == 0">
                <div class="col-sm-12 col-md-6">
                  <q-select
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="inBulk.fields.product"
                    :error="$v.inBulk.fields.product.$error"
                    :options="inBulkProductsOptions"
                    label="Producto"
                    :rules="inBulkProductRules"
                    @input="inBulk.fields.qty = null"
                  >
                    <template v-slot:prepend>
                      <q-icon name="emoji_objects" />
                    </template>
                  </q-select>
                </div>
                <div class="col-sm-12 col-md-6">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="inBulk.fields.qty"
                    label="Cantidad"
                    :rules="inBulkProductRules"
                    :suffix="`/${inBulkAvailableQty}`"
                  >
                    <template v-slot:prepend>
                      <q-icon name="emoji_objects" />
                    </template>
                  </q-input>
                </div>
              </div>
              <div class="q-col-gutter-xs" style="padding: 2%;">
                <q-table
                  flat
                  bordered
                  :data="inBulks"
                  :columns="inbulkColumns"
                  row-key="id"
                  :pagination.sync="pagination"
                >
                  <template v-slot:body="props">
                    <q-tr :props="props">
                      <q-td key="product" style="text-align: left; width: 80%;" :props="props">{{ props.row.name_product }}</q-td>
                      <q-td key="qty" style="text-align: right; width: 10%;" :props="props">{{ formatPrice(props.row.qty) }} KG</q-td>
                      <q-td key="actions" style="text-align: center; width: 10%;" :props="props" v-if="branchTrasfer.fields.status == 0">
                        <q-btn color="primary" icon="fas fa-trash-alt" flat @click.native="removeinBulk(props.row.id)" size="10px">
                          <q-tooltip content-class="bg-red">Eliminar</q-tooltip>
                        </q-btn>
                      </q-td>
                      <q-td key="actions" style="text-align: center; width: 5%;" :props="props" v-else>
                        -
                      </q-td>
                    </q-tr>
                  </template>
                </q-table>
              </div>
            </div>
          </q-tab-panel>
          <q-tab-panel name="laminates">
            <div style="font-weight: normal;">
              <div class="row q-col-gutter-xs" style="padding-right: 2%;" v-if="branchTrasfer.fields.status == 0">
                <div class="col-xs-12 col-sm-4 offset-sm-8 pull-right">
                  <q-btn color="primary" icon="add" label="Agregar" @click="addLaminate()"/>
                </div>
              </div>
              <div class="row q-col-gutter-xs" style="padding: 2%;" v-if="branchTrasfer.fields.status == 0">
                <div class="col-sm-12 col-md-6">
                  <q-select
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="laminate.fields.product"
                    :error="$v.laminate.fields.product.$error"
                    :options="laminateProductOptions"
                    label="Producto"
                    :rules="laminateProductRules"
                    @input="laminate.fields.qty = null"
                  >
                    <template v-slot:prepend>
                      <q-icon name="emoji_objects" />
                    </template>
                  </q-select>
                </div>
                <div class="col-sm-12 col-md-6">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="laminate.fields.qty"
                    label="Cantidad"
                    :rules="laminateQtyRules"
                    :suffix="`/${laminateAvailableQty}`"
                  >
                    <template v-slot:prepend>
                      <q-icon name="emoji_objects" />
                    </template>
                  </q-input>
                </div>
              </div>
              <div class="q-col-gutter-xs" style="padding: 2%;">
                <q-table
                  flat
                  bordered
                  :data="laminates"
                  :columns="laminatesColumns"
                  row-key="id"
                  :pagination.sync="pagination"
                >
                  <template v-slot:body="props">
                    <q-tr :props="props">
                      <q-td key="product" style="text-align: left; width: 80%;" :props="props">{{ props.row.name_product }}</q-td>
                      <q-td key="qty" style="text-align: right; width: 10%;" :props="props">{{ formatPrice(props.row.qty) }} KG</q-td>
                      <q-td key="actions" style="text-align: center; width: 10%;" :props="props" v-if="branchTrasfer.fields.status == 0">
                        <q-btn color="primary" icon="fas fa-trash-alt" flat @click.native="removeLaminate(props.row.id)" size="10px">
                          <q-tooltip content-class="bg-red">Eliminar</q-tooltip>
                        </q-btn>
                      </q-td>
                      <q-td key="actions" style="text-align: center; width: 5%;" :props="props" v-else>
                        -
                      </q-td>
                    </q-tr>
                  </template>
                </q-table>
              </div>
            </div>
          </q-tab-panel>
          <q-tab-panel name="rawMaterials">
            <div style="font-weight: normal;">
              <div class="row q-col-gutter-xs" style="padding-right: 2%;" v-if="branchTrasfer.fields.status == 0">
                <div class="col-xs-12 col-sm-4 offset-sm-8 pull-right">
                  <q-btn color="primary" icon="add" label="Agregar" @click="addRawMaterial()"/>
                </div>
              </div>
              <div class="row q-col-gutter-xs" style="padding: 2%;" v-if="branchTrasfer.fields.status == 0">
                <div class="col-sm-12 col-md-6">
                  <q-select
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="rawMaterial.fields.product"
                    :error="$v.rawMaterial.fields.product.$error"
                    :options="rawMaterialProductOptions"
                    label="Producto"
                    :rules="rawMaterialProductRules"
                    @input="rawMaterial.fields.qty = null"
                  >
                    <template v-slot:prepend>
                      <q-icon name="emoji_objects" />
                    </template>
                  </q-select>
                </div>
                <div class="col-sm-12 col-md-6">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="rawMaterial.fields.qty"
                    label="Cantidad"
                    :rules="rawMaterialQtyRules"
                    :suffix="`/${rawMaterialAvailableQty}`"
                  >
                    <template v-slot:prepend>
                      <q-icon name="emoji_objects" />
                    </template>
                  </q-input>
                </div>
              </div>
              <div class="q-col-gutter-xs" style="padding: 2%;">
                <q-table
                  flat
                  bordered
                  :data="rawMaterials"
                  :columns="rawMaterialsColumns"
                  row-key="id"
                  :pagination.sync="pagination"
                >
                  <template v-slot:body="props">
                    <q-tr :props="props">
                      <q-td key="product" style="text-align: left; width: 80%;" :props="props">{{ props.row.product_name }}</q-td>
                      <q-td key="qty" style="text-align: right; width: 10%;" :props="props">{{ formatPrice(props.row.qty) }} KG</q-td>
                      <q-td key="actions" style="text-align: center; width: 10%;" :props="props" v-if="branchTrasfer.fields.status == 0">
                        <q-btn color="primary" icon="fas fa-trash-alt" flat @click.native="removeRawMaterial(props.row.id)" size="10px">
                          <q-tooltip content-class="bg-red">Eliminar</q-tooltip>
                        </q-btn>
                      </q-td>
                      <q-td key="actions" style="text-align: center; width: 5%;" :props="props" v-else>
                        -
                      </q-td>
                    </q-tr>
                  </template>
                </q-table>
              </div>
            </div>
          </q-tab-panel>
        </q-tab-panels>
      </div>
    </div>
  </q-page>
</template>

<script>
import api from '../../commons/api.js'
const { required, decimal } = require('vuelidate/lib/validators')

export default {
  name: 'EditBranchTransfer',
  validations: {
    branchTrasfer: {
      fields: {
        destinationBranchOffice: { required },
        destinationStorage: { required }
      }
    },
    bale: {
      fields: {
        product: { required },
        bale: { required }
      }
    },
    inBulk: {
      fields: {
        product: { required },
        qty: { required, decimal }
      }
    },
    laminate: {
      fields: {
        product: { required },
        qty: { required, decimal }
      }
    },
    rawMaterial: {
      fields: {
        product: { required },
        qty: { required, decimal }
      }
    }
  },
  data () {
    return {
      currentTab: null,
      baleTab: true,
      inBulkTab: true,
      lamiTab: true,
      rawTab: true,
      branchTrasfer: {
        fields: {
          id: null,
          originBranchOffice: null,
          originStorage: null,
          destinationBranchOffice: null,
          destinationStorage: null,
          // operator: null,
          status: null,
          statusStr: null,
          transactionId: null
        }
      },
      // operatorOptions: [],
      branchOfficeOptions: [],
      storageOptions: [],
      bale: {
        fields: {
          id: null,
          product: null,
          bale: null
        }
      },
      inBulk: {
        fields: {
          id: null,
          product: null,
          qty: null
        }
      },
      laminate: {
        fields: {
          id: null,
          product: null,
          qty: null
        }
      },
      rawMaterial: {
        fields: {
          id: null,
          product: null,
          qty: null
        }
      },
      availableBales: [],
      availableLaminates: [],
      laminateProductOptions: [],
      laminateProducts: [],
      inBulkProducts: [],
      inBulkProductsOptions: [],
      rawMaterialProductOptions: [],
      rawMaterialProducts: [],
      bales: [],
      laminates: [],
      inBulks: [],
      rawMaterials: [],
      balesUsed: [],
      pagination: {
        rowsPerPage: 50
      },
      filter: '',
      balesColumns: [
        { name: 'id', align: 'center', label: 'Paca', field: 'id', style: 'width: 10%', sortable: true },
        { name: 'product', align: 'center', label: 'Producto', field: 'product', style: 'width: 75%', sortable: true },
        { name: 'qty', align: 'center', label: 'Cantidad', field: 'qty', style: 'width: 10%', sortable: true },
        { name: 'actions', align: 'center', label: 'Acciones', field: 'actions', style: 'width: 5%', sortable: false }
      ],
      laminatesColumns: [
        { name: 'product', align: 'center', label: 'Producto', field: 'product', style: 'width: 80%', sortable: true },
        { name: 'qty', align: 'center', label: 'Cantidad', field: 'qty', style: 'width: 10%', sortable: true },
        { name: 'actions', align: 'center', label: 'Acciones', field: 'actions', style: 'width: 10%', sortable: false }
      ],
      inbulkColumns: [
        { name: 'product', align: 'center', label: 'Producto', field: 'product', style: 'width: 80%', sortable: true },
        { name: 'qty', align: 'center', label: 'Cantidad', field: 'qty', style: 'width: 10%', sortable: true },
        { name: 'actions', align: 'center', label: 'Acciones', field: 'actions', style: 'width: 10%', sortable: false }
      ],
      rawMaterialsColumns: [
        { name: 'product', align: 'center', label: 'Producto', field: 'product', style: 'width: 80%', sortable: true },
        { name: 'qty', align: 'center', label: 'Cantidad', field: 'qty', style: 'width: 10%', sortable: true },
        { name: 'actions', align: 'center', label: 'Acciones', field: 'actions', style: 'width: 10%', sortable: false }
      ]
    }
  },
  computed: {
    filteredDestinationStorage () {
      if (this.branchTrasfer.fields.destinationBranchOffice != null && !isNaN(this.branchTrasfer.fields.destinationBranchOffice.value)) {
        return this.storageOptions.filter(op => Number(op.branchOffice) === Number(this.branchTrasfer.fields.destinationBranchOffice.value) && Number(op.value) !== 5)
      }
      return []
    },
    baleProductRules (val) {
      return [
        val => (this.$v.bale.fields.product.required) || 'El campo Producto es requerido.'
      ]
    },
    baleBaleRules (val) {
      return [
        val => (this.$v.bale.fields.bale.required) || 'El campo Paca es requerido.'
      ]
    },
    laminateProductRules (val) {
      return [
        val => (this.$v.laminate.fields.product.required) || 'El campo Producto es requerido.'
      ]
    },
    rawMaterialProductRules (val) {
      return [
        val => (this.$v.rawMaterial.fields.product.required) || 'El campo Producto es requerido.'
      ]
    },
    laminateQtyRules (val) {
      return [
        val => (this.$v.laminate.fields.qty.required) || 'El campo Cantidad es requerido.',
        val => (this.$v.laminate.fields.qty.decimal) || 'El campo Cantidad debe ser numérico.'
      ]
    },
    inBulkProductRules (val) {
      return [
        val => (this.$v.inBulk.fields.qty.required) || 'El campo Cantidad es requerido.',
        val => (this.$v.inBulk.fields.qty.decimal) || 'El campo Cantidad debe ser numérico.'
      ]
    },
    rawMaterialQtyRules (val) {
      return [
        val => (this.$v.rawMaterial.fields.qty.required) || 'El campo Cantidad es requerido.',
        val => (this.$v.rawMaterial.fields.qty.decimal) || 'El campo Cantidad debe ser numérico.'
      ]
    },
    baleProductOptions () {
      const productOptions = []
      const productIds = []
      this.availableBales.forEach(bale => {
        if (!productIds.includes(Number(bale.product_id))) {
          productOptions.push({ value: bale.product_id, label: `${bale.category_code}-${bale.line_code}-${bale.product_name}` })
          productIds.push(Number(bale.product_id))
        }
      })
      return productOptions
    },
    filteredBalesOptions () {
      const bales = []
      if (this.bale.fields.product != null && this.bale.fields.product.value != null) {
        const usedBales = []
        this.bales.forEach(bale => {
          usedBales.push(Number(bale.id))
        })
        const productBales = this.availableBales.filter(bale => Number(this.bale.fields.product.value) === Number(bale.product_id))
        productBales.forEach(bale => {
          if (!usedBales.includes(Number(bale.bale_id))) {
            bales.push({ value: bale.bale_id, label: `PACA ${bale.bale_id} (${bale.stock} KG.)` })
          }
        })
      }
      return bales
    },
    laminateAvailableQty () {
      let availableQty = 0
      if (this.laminate.fields.product != null && !isNaN(this.laminate.fields.product.value)) {
        availableQty = this.laminateProducts.filter(ibp => Number(ibp.product_id) === Number(this.laminate.fields.product.value))[0].stock
      }
      return availableQty
    },
    inBulkAvailableQty () {
      let availableQty = 0
      if (this.inBulk.fields.product != null && !isNaN(this.inBulk.fields.product.value)) {
        availableQty = this.inBulkProducts.filter(ibp => Number(ibp.product_id) === Number(this.inBulk.fields.product.value))[0].stock
      }
      return availableQty
    },
    rawMaterialAvailableQty () {
      let availableQty = 0
      if (this.rawMaterial.fields.product != null && !isNaN(this.rawMaterial.fields.product.value)) {
        availableQty = this.rawMaterialProducts.filter(ibp => Number(ibp.product_id) === Number(this.rawMaterial.fields.product.value))[0].stock
      }
      return availableQty
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
      const id = this.$route.params.id
      api.get(`/branch-transfers/${id}`).then(({ data }) => {
        if (!data.branchTransfer) {
          this.$router.push('/branch-transfers')
        }
        this.branchTrasfer.fields.id = data.branchTransfer.id
        // this.branchTrasfer.fields.operator = { value: data.branchTransfer.operator_id, label: data.branchTransfer.operator_name }
        this.branchTrasfer.fields.originBranchOffice = { value: data.branchTransfer.origin_branch_office_id, label: data.branchTransfer.origin_branch_office_name }
        this.branchTrasfer.fields.originStorage = { value: data.branchTransfer.origin_storage_id, label: data.branchTransfer.origin_storage_name }
        this.branchTrasfer.fields.destinationBranchOffice = { value: data.branchTransfer.destination_branch_office_id, label: data.branchTransfer.destination_branch_office_name }
        this.branchTrasfer.fields.destinationStorage = { value: data.branchTransfer.destination_storage_id, label: data.branchTransfer.destination_storage_name }
        this.branchTrasfer.fields.status = data.branchTransfer.status
        this.branchTrasfer.fields.statusStr = Number(data.branchTransfer.status) === 0 ? 'NO EJECUTADO' : 'EJECUTADO'
        this.branchTrasfer.fields.transactionId = Number(data.branchTransfer.transaction_id)
        const valTab = data.branchTransfer.origin_storage_id
        if (valTab === 19 || valTab === 21 || valTab === 25) {
          this.currentTab = 'laminates'
          this.lamiTab = false
        }
        if (valTab === 18 || valTab === 24 || valTab === 27) {
          this.currentTab = 'bulk'
          this.inBulkTab = false
        }
        if (valTab === 4 || valTab === 5 || valTab === 22) {
          this.currentTab = 'bales'
          this.baleTab = false
        }
        if (valTab === 2 || valTab === 6 || valTab === 7 || valTab === 20 || valTab === 23 || valTab === 26) {
          this.currentTab = 'rawMaterials'
          this.rawTab = false
        }
        api.get(`/laminates/transaction/${this.branchTrasfer.fields.transactionId}`).then(({ data }) => {
          this.laminates = data.laminates
          api.get(`/branch-transfer-details/branch-transfer/${this.branchTrasfer.fields.transactionId}/inBulk-transaction`).then(({ data }) => {
            this.inBulks = data.inbulks
          })
          api.get(`/bales/transaction/${this.branchTrasfer.fields.transactionId}`).then(({ data }) => {
            this.bales = data.bales
            this.balesUsed = []
            data.bales.forEach(bale => {
              this.balesUsed.push(Number(bale.id))
            })
            api.get(`/branch-transfer-details/branch-transfer/${this.branchTrasfer.fields.transactionId}/raw-materials`).then(({ data }) => {
              this.rawMaterials = data.rawMaterials
              this.$q.loading.hide()
            })
            this.get_data()
          })
        })
      })
    },
    openBranchTransferPdf () {
      const uri = process.env.API + `branch-transfers/pdf/${this.$route.params.id}`
      window.open(uri, '_blank')
    },
    executeTransfer () {
      this.$q.loading.show()
      api.put(`/transactions/execute/${this.branchTrasfer.fields.transactionId}`).then(({ data }) => {
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
    changeModel (newModel) {
      if (Number(this.branchTrasfer.fields.status) === 0) {
        this.$q.loading.show()
        if (newModel === 'bales') {
          api.get(`/storages/${this.branchTrasfer.fields.originStorage.value}/bales`).then(({ data }) => {
            this.availableBales = data.bales
            this.$q.loading.hide()
          })
        } else if (newModel === 'bulk') {
          api.get(`/storages/${this.branchTrasfer.fields.originStorage.value}/bulk-products`).then(({ data }) => {
            this.inBulkProducts = []
            this.inBulkProductsOptions = []
            data.products.forEach(product => {
              const details = this.inBulks.filter(det => Number(det.product_id) === Number(product.product_id))
              details.forEach(det => {
                product.stock -= det.qty
              })
              this.inBulkProducts.push(product)
              this.inBulkProductsOptions.push({ value: product.product_id, label: `${product.category_code}-${product.line_code}-${product.product_name}` })
            })
            this.$q.loading.hide()
          })
        } else if (newModel === 'laminates') {
          api.get(`/storages/${this.branchTrasfer.fields.originStorage.value}/laminates`).then(({ data }) => {
            this.laminateProducts = []
            this.laminateProductOptions = []
            data.products.forEach(product => {
              const details = this.laminates.filter(det => Number(det.product_id) === Number(product.product_id))
              details.forEach(det => {
                product.stock -= det.qty
              })
              this.laminateProducts.push(product)
              this.laminateProductOptions.push({ value: product.product_id, label: `${product.category_code}-${product.line_code}-${product.product_name}` })
            })
            this.$q.loading.hide()
          })
        } else if (newModel === 'rawMaterials') {
          api.get(`/storages/${this.branchTrasfer.fields.originStorage.value}/raw-materials`).then(({ data }) => {
            this.rawMaterialProducts = []
            this.rawMaterialProductOptions = []
            data.products.forEach(product => {
              const details = this.rawMaterials.filter(det => Number(det.product_id) === Number(product.product_id))
              details.forEach(det => {
                product.stock -= det.qty
              })
              this.rawMaterialProducts.push(product)
              this.rawMaterialProductOptions.push({ value: product.product_id, label: `${product.category_code}-${product.line_code}-${product.product_name}` })
            })
            this.$q.loading.hide()
          })
        }
      }
    },
    get_data () {
      api.get(`/storages/${this.branchTrasfer.fields.originStorage.value}/bales`).then(({ data }) => {
        this.availableBales = data.bales
        this.$q.loading.hide()
      })
      api.get(`/storages/${this.branchTrasfer.fields.originStorage.value}/bulk-products`).then(({ data }) => {
        this.inBulkProducts = []
        this.inBulkProductOptions = []
        data.products.forEach(product => {
          const details = this.inBulks.filter(det => Number(det.product_id) === Number(product.product_id))
          details.forEach(det => {
            product.stock -= det.qty
          })
          this.inBulkProducts.push(product)
          this.inBulkProductOptions.push({ value: product.product_id, label: `${product.category_code}-${product.line_code}-${product.product_name}` })
        })
        this.$q.loading.hide()
      })
      api.get(`/storages/${this.branchTrasfer.fields.originStorage.value}/laminates`).then(({ data }) => {
        this.laminateProducts = []
        this.laminateProductOptions = []
        data.products.forEach(product => {
          const details = this.laminates.filter(det => Number(det.product_id) === Number(product.product_id))
          details.forEach(det => {
            product.stock -= det.qty
          })
          this.laminateProducts.push(product)
          this.laminateProductOptions.push({ value: product.product_id, label: `${product.category_code}-${product.line_code}-${product.product_name}` })
        })
        this.$q.loading.hide()
      })
      api.get(`/storages/${this.branchTrasfer.fields.originStorage.value}/raw-materials`).then(({ data }) => {
        this.rawMaterialProducts = []
        this.rawMaterialProductOptions = []
        data.products.forEach(product => {
          const details = this.rawMaterials.filter(det => Number(det.product_id) === Number(product.product_id))
          details.forEach(det => {
            product.stock -= det.qty
          })
          this.rawMaterialProducts.push(product)
          this.rawMaterialProductOptions.push({ value: product.product_id, label: `${product.category_code}-${product.line_code}-${product.product_name}` })
        })
        this.$q.loading.hide()
      })
    },
    addBale () {
      this.$v.bale.fields.$reset()
      this.$v.bale.fields.$touch()
      if (this.$v.bale.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = []
      params.branchTransferId = Number(this.$route.params.id)
      params.baleId = Number({ ...this.bale.fields }.bale.value)
      api.post('/branch-transfer-details', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.bale.fields.id = null
          this.bale.fields.product = null
          this.bale.fields.bale = null
          this.$q.loading.hide()
          this.fetchFromServer()
        } else {
          this.$q.loading.hide()
        }
      })
    },
    addLaminate () {
      this.$v.laminate.fields.$reset()
      this.$v.laminate.fields.$touch()
      if (this.$v.laminate.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = []
      params.branchTransferId = Number(this.$route.params.id)
      params.productId = Number({ ...this.laminate.fields }.product.value)
      params.qty = Number({ ...this.laminate.fields }.qty)
      this.laminate.fields.id = null
      this.laminate.fields.product = null
      this.laminate.fields.qty = null
      api.post('/branch-transfer-details', params).then(({ data }) => {
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
    addBulk () {
      this.$v.inBulk.fields.$reset()
      this.$v.inBulk.fields.$touch()
      if (this.$v.inBulk.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = []
      params.branchTransferId = Number(this.$route.params.id)
      params.productId = Number({ ...this.inBulk.fields }.product.value)
      params.qty = Number({ ...this.inBulk.fields }.qty)
      this.inBulk.fields.id = null
      this.inBulk.fields.product = null
      this.inBulk.fields.qty = null
      api.post('/branch-transfer-details', params).then(({ data }) => {
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
    addRawMaterial () {
      this.$v.rawMaterial.fields.$reset()
      this.$v.rawMaterial.fields.$touch()
      if (this.$v.rawMaterial.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = []
      params.branchTransferId = Number(this.$route.params.id)
      params.productId = Number({ ...this.rawMaterial.fields }.product.value)
      params.qty = Number({ ...this.rawMaterial.fields }.qty)
      this.rawMaterial.fields.id = null
      this.rawMaterial.fields.product = null
      this.rawMaterial.fields.qty = null
      api.post('/branch-transfer-details', params).then(({ data }) => {
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
    removeBale (baleId) {
      this.$q.loading.show()
      api.delete(`/branch-transfer-details/branch-transfer/${this.$route.params.id}/bale/${baleId}`).then(({ data }) => {
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
    removeLaminate (laminateId) {
      this.$q.loading.show()
      api.delete(`/branch-transfer-details/branch-transfer/${this.$route.params.id}/laminate/${laminateId}`).then(({ data }) => {
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
    removeinBulk (inBulkId) {
      this.$q.loading.show()
      api.delete(`/branch-transfer-details/branch-transfer/${this.$route.params.id}/inbulk/${inBulkId}`).then(({ data }) => {
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
    removeRawMaterial (rawMaterialId) {
      this.$q.loading.show()
      api.delete(`/branch-transfer-details/branch-transfer/${this.$route.params.id}/raw-material/${rawMaterialId}`).then(({ data }) => {
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
// C

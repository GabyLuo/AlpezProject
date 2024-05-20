<template>
  <q-page>
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-9">
          <span class="q-ml-md grey-8 fs28 page-title">Laminado {{ $route.params.id }}</span>
        </div>
        <div class="col-sm-3">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="right">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Laminados" to="/laminates" />
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
            <div class="col-xs-12 col-sm-2 offset-sm-10 pull-right" v-if="laminate.fields.status == 'NUEVO' && materials.length > 0">
              <q-btn color="warning" icon="fas fa-cogs" label="Producir" @click="openOperatorModal()" style="margin-right: 5px;" />
            </div>
            <div class="col-xs-12 col-sm-2 offset-sm-10 pull-right" v-if="laminate.fields.status == 'PRODUCIENDO' && materials.length > 0">
              <q-btn color="positive" icon="check" label="Terminar" @click="finish()" style="margin-right: 5px;" />
            </div>
          </div>

          <div class="row q-col-gutter-xs">
            <div class="col-sm-12 col-md-2">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="laminate.fields.scheduledDate"
                :error="$v.laminate.fields.scheduledDate.$error"
                mask="date"
                label="Fecha programada"
                :rules="scheduledDateRules"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="event" />
                </template>
                <q-popup-proxy
                  ref="laminateFieldsScheduledDateRef"
                  transition-show="scale"
                  transition-hide="scale"
                >
                  <div class="col-sm-12">
                    <q-date
                      v-model="laminate.fields.scheduledDate"
                      @input="() => $refs.laminateFieldsScheduledDateRef.hide()"
                      today-btn
                    />
                  </div>
                </q-popup-proxy>
              </q-select>
            </div>
            <div class="col-sm-12 col-md-2">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="laminate.fields.product"
                :error="$v.laminate.fields.product.$error"
                :options="productOptions"
                label="Producto"
                :rules="productRules"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="emoji_objects" />
                </template>
              </q-select>
            </div>
            <div class="col-sm-12 col-md-3">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="laminate.fields.branchOffice"
                :error="$v.laminate.fields.branchOffice.$error"
                :options="branchOfficeOptions"
                label="Sucursal"
                :rules="branchOfficeRules"
                @input="branchOfficeUpdated"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-store-alt" />
                </template>
              </q-select>
            </div>
            <div class="col-sm-12 col-md-3">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="laminate.fields.storage"
                :error="$v.laminate.fields.storage.$error"
                :options="storageOptions"
                label="Almacén"
                :rules="storageRules"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-warehouse" />
                </template>
              </q-select>
            </div>
            <div class="col-sm-12 col-md-2 text-center">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="laminate.fields.status"
                label="Estatus"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="battery_full" />
                </template>
              </q-input>
            </div>
          </div>
        </div>
      </div>

      <br>

      <div class="bg-white border-panel">
        <q-tabs
          v-model="currentTab"
          dense
          class="text-grey"
          active-color="primary"
          indicator-color="primary"
          align="justify"
          narrow-indicator
        >
          <q-tab name="materials" label="Materiales" />
          <q-tab name="additives" label="Aditivos" v-if="laminate.fields.status == 'PRODUCIENDO' || laminate.fields.status == 'TERMINADO'" />
        </q-tabs>
        <q-separator />
        <q-tab-panels v-model="currentTab" animated>
          <q-tab-panel name="materials">
            <div style="font-weight: normal;">
              <div class="row q-col-gutter-xs" style="padding-right: 2%;" v-if="laminate.fields.status == 'NUEVO'">
                <div class="col-xs-12 col-sm-4 offset-sm-8 pull-right">
                  <q-btn color="primary" icon="add" label="Agregar" @click="addMaterial()" />
                </div>
              </div>
              <div class="row q-col-gutter-xs" style="padding: 2%;" v-if="laminate.fields.status == 'NUEVO'">
                <div class="col-xs-12 col-sm-4">
                  <q-select
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="material.fields.storage"
                    :error="$v.material.fields.storage.$error"
                    :options="storageOptions"
                    label="Almacén"
                    :rules="storageRules"
                    disable
                  >
                    <template v-slot:prepend>
                      <q-icon name="fas fa-warehouse" />
                    </template>
                  </q-select>
                </div>
                <div class="col-xs-12 col-sm-4">
                  <q-select
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="material.fields.product"
                    :options="materialProductOptions"
                    label="Producto"
                    :rules="materialProductRules"
                    @input="() => material.fields.bale = null"
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
                    v-model="material.fields.bale"
                    :options="filteredBaleOptions"
                    label="Paca"
                    :rules="baleRules"
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
                  :data="materials"
                  :columns="materialsColumns"
                  :pagination.sync="pagination"
                  row-key="bale"
                >
                  <template v-slot:body="props">
                    <q-tr :props="props">
                      <q-td key="bale" style="text-align: left; width: 20%;" :props="props">{{ (props.row.bale_id ? `PACA ${props.row.bale_id}` : null) }}</q-td>
                      <q-td key="product" style="text-align: left; width: 40%;" :props="props" v-if="props.row.id">{{ props.row.product }}</q-td>
                      <q-td key="product" style="text-align: right; width: 40%; color: #3CB371;" :props="props" v-else>{{ props.row.product }}</q-td>
                      <q-td key="qty" style="text-align: right; width: 20%;" :props="props" v-if="props.row.id">{{ formatPrice(props.row.qty) }} KG.</q-td>
                      <q-td key="qty" style="text-align: right; width: 20%; color: #3CB371;" :props="props" v-else>{{ `${ formatPrice(totalMaterialsQties)} KG. (${materials.length - 1} SACOS)` }}</q-td>
                      <q-td key="actions" style="width: 20%;" :props="props" v-if="laminate.fields.status == 'NUEVO' && props.row.bale_id">
                        <q-btn color="primary" icon="fas fa-trash-alt" flat @click.native="removeMaterial(props.row.id)" size="10px">
                          <q-tooltip content-class="bg-red">Eliminar</q-tooltip>
                        </q-btn>
                      </q-td>
                      <q-td key="actions" style="width: 20%;" :props="props" v-else></q-td>
                    </q-tr>
                  </template>
                </q-table>
              </div>
            </div>
          </q-tab-panel>
          <q-tab-panel name="additives" v-if="laminate.fields.status == 'PRODUCIENDO' || laminate.fields.status == 'TERMINADO'">
            <div class="q-col-gutter-xs" style="padding: 2%;">
              <q-table
                flat
                bordered
                :data="additives"
                :columns="additivesColumns"
                :pagination.sync="pagination"
                row-key="bale"
              >
                <template v-slot:body="props">
                  <q-tr :props="props">
                    <q-td key="product" style="text-align: left; width: 75%;" :props="props" v-if="props.row.id">{{ props.row.product }}</q-td>
                    <q-td key="qty" style="text-align: right; width: 25%;" :props="props" v-if="props.row.id">{{ formatPrice(props.row.qty) }} KG.</q-td>
                  </q-tr>
                </template>
              </q-table>
            </div>
          </q-tab-panel>
        </q-tab-panels>
      </div>
    </div>

    <q-dialog v-model="selectOperatorModal" persistent>
      <q-card style="min-width: 400px;">
        <q-card-section>
          <div class="text-h6">Seleccionar Operador</div>
        </q-card-section>
        <q-card-section>
          <q-select
            color="white"
            bg-color="primary"
            filled
            dark
            v-model="operator"
            :error="$v.operator.$error"
            :options="operatorOptions"
            label="Operador"
            :rules="operatorRules"
          >
            <template v-slot:prepend>
              <q-icon name="fa fa-user" />
            </template>
          </q-select>
        </q-card-section>
        <q-card-actions align="right" class="text-primary">
          <q-btn flat label="Cancelar" @click="cancelProduce()" v-close-popup />
          <q-btn flat label="Registrar operador" @click="produce()" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import api from '../../commons/api.js'
const { required } = require('vuelidate/lib/validators')

export default {
  name: 'EditLaminate',
  validations: {
    laminate: {
      fields: {
        scheduledDate: { required },
        product: { required },
        branchOffice: { required },
        storage: { required }
      }
    },
    material: {
      fields: {
        storage: { required },
        product: { required },
        bale: { required }
      }
    },
    operator: { required }
  },
  data () {
    return {
      formatter: new Intl.NumberFormat('en-US'),
      laminate: {
        fields: {
          scheduledDate: null,
          product: null,
          branchOffice: null,
          storage: null
        }
      },
      productOptions: [],
      branchOfficeOptions: [],
      storageOptions: [],
      currentTab: 'materials',
      materialProductOptions: [],
      baleOptions: [],
      materials: [],
      materialsColumns: [
        { name: 'bale', align: 'center', label: 'Paca', field: 'bale', style: 'width: 20%', sortable: true },
        { name: 'product', align: 'center', label: 'Producto', field: 'product', style: 'width: 40%', sortable: true },
        { name: 'qty', align: 'center', label: 'Peso', field: 'qty', style: 'width: 20%', sortable: true },
        { name: 'actions', align: 'center', label: 'Acciones', field: 'actions', style: 'width: 20%', sortable: false }
      ],
      pagination: {
        rowsPerPage: 50
      },
      additives: [],
      additivesColumns: [
        { name: 'product', align: 'center', label: 'Producto', field: 'product', style: 'width: 75%', sortable: true },
        { name: 'qty', align: 'center', label: 'Peso', field: 'qty', style: 'width: 25%', sortable: true }
      ],
      material: {
        fields: {
          storage: null,
          product: null,
          bale: null
        }
      },
      selectOperatorModal: false,
      operator: null,
      operatorOptions: []
    }
  },
  computed: {
    scheduledDateRules (val) {
      return [
        val => (this.$v.laminate.fields.scheduledDate.required) || 'El campo Fecha programada es requerido.'
      ]
    },
    productRules (val) {
      return [
        val => (this.$v.laminate.fields.product.required) || 'El campo Producto es requerido.'
      ]
    },
    branchOfficeRules (val) {
      return [
        val => (this.$v.laminate.fields.branchOffice.required) || 'El campo Sucursal es requerido.'
      ]
    },
    storageRules (val) {
      return [
        val => (this.$v.laminate.fields.storage.required) || 'El campo Almacén es requerido.'
      ]
    },
    materialProductRules (val) {
      return [
        val => (this.$v.material.fields.product.required) || 'El campo Producto es requerido.'
      ]
    },
    baleRules (val) {
      return [
        val => (this.$v.material.fields.bale.required) || 'El campo Paca es requerido.'
      ]
    },
    operatorRules (val) {
      return [
        val => (this.$v.operator.required) || 'El campo Operador es requerido.'
      ]
    },
    filteredBaleOptions () {
      if (this.material.fields.product != null && this.material.fields.product.value != null) {
        return this.baleOptions.filter(bale => Number(bale.product_id) === Number(this.material.fields.product.value))
      }
      return []
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
      api.get(`/laminates/${id}`).then(({ data }) => {
        if (!data.laminate) {
          this.$router.push('/laminates')
        }
        this.laminate.fields.id = data.laminate.id
        this.laminate.fields.scheduledDate = data.laminate.scheduled_date
        this.laminate.fields.product = { value: data.laminate.product_id, label: data.laminate.product }
        this.laminate.fields.branchOffice = { value: data.laminate.branch_office_id, label: data.laminate.branch_office }
        this.laminate.fields.storage = { value: data.laminate.storage_id, label: data.laminate.storage }
        this.laminate.fields.status = data.laminate.status
        api.get('/products/options').then(({ data }) => {
          this.productOptions = data.options
          api.get('/branch-offices/options').then(({ data }) => {
            this.branchOfficeOptions = data.options
            api.get('/storages/options').then(({ data }) => {
              this.storageOptions = data.options
              const filteredMaterialStorages = this.storageOptions.filter(so => (Number(so.branchOffice) === Number(this.laminate.fields.branchOffice.value) && Number(so.storageType) === 1))
              if (filteredMaterialStorages.length > 0) {
                this.material.fields.storage = { value: filteredMaterialStorages[0].value, label: filteredMaterialStorages[0].label }
                api.get(`/storages/${filteredMaterialStorages[0].value}/bales`).then(({ data }) => {
                  this.baleOptions = []
                  data.bales.forEach(bale => {
                    this.baleOptions.push({ value: bale.bale_id, label: `PACA ${bale.bale_id} (${bale.stock} KG.)`, product_id: bale.product_id })
                  })
                  this.productOptions.forEach(product => {
                    if (this.baleOptions.filter(bale => Number(bale.product_id) === Number(product.value)).length > 0) {
                      this.materialProductOptions.push({ value: product.value, label: product.label })
                    }
                  })
                  api.get(`/laminate-materials/laminate/${id}`).then(({ data }) => {
                    this.materials = data.materials
                    api.get(`/laminate-additives/laminate/${id}`).then(({ data }) => {
                      this.additives = data.additives
                      this.$q.loading.hide()
                    })
                  })
                })
              } else {
                this.material.fields.storage = null
                this.$q.loading.hide()
              }
            })
          })
        })
      })
    },
    openOperatorModal () {
      this.$q.loading.show()
      api.get('/operators/options').then(({ data }) => {
        this.operatorOptions = data.options
        this.selectOperatorModal = true
        this.$q.loading.hide()
      })
    },
    cancelProduce () {
      this.operator = null
      this.selectOperatorModal = false
    },
    produce () {
      this.$v.operator.$reset()
      this.$v.operator.$touch()
      if (this.$v.operator.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = []
      params.operatorId = Number({ ...this.operator }.value)
      this.operator = null
      this.selectOperatorModal = false
      api.put(`/laminates/${this.$route.params.id}/produce`, params).then(({ data }) => {
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
    finish () {
      this.$q.loading.show()
      api.put(`/laminates/${this.$route.params.id}/finish`).then(({ data }) => {
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
    branchOfficeUpdated () {
      if (this.laminate.fields.branchOffice != null && this.laminate.fields.branchOffice.value != null) {
        const filteredLaminateStorages = this.storageOptions.filter(so => (Number(so.branchOffice) === Number(this.laminate.fields.branchOffice.value) && Number(so.storageType) === 8))
        const filteredMaterialStorages = this.storageOptions.filter(so => (Number(so.branchOffice) === Number(this.laminate.fields.branchOffice.value) && Number(so.storageType) === 1))
        if (filteredLaminateStorages.length > 0) {
          this.laminate.fields.storage = { value: filteredLaminateStorages[0].value, label: filteredLaminateStorages[0].label }
        } else {
          this.laminate.fields.storage = null
        }
        if (filteredMaterialStorages.length > 0) {
          this.material.fields.storage = { value: filteredMaterialStorages[0].value, label: filteredMaterialStorages[0].label }
        } else {
          this.material.fields.storage = null
        }
      }
    },
    addMaterial () {
      this.$v.material.fields.$reset()
      this.$v.material.fields.$touch()
      if (this.$v.material.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = []
      params.laminateId = Number(this.$route.params.id)
      params.baleId = Number({ ...this.material.fields }.bale.value)
      api.post('/laminate-materials', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.material.fields.product = null
          this.material.fields.bale = null
          api.get(`/laminate-materials/laminate/${this.$route.params.id}`).then(({ data }) => {
            this.materials = data.materials
            this.$q.loading.hide()
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    removeMaterial (materialId) {
      this.$q.loading.show()
      api.delete(`/laminate-materials/${materialId}`).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          api.get(`/laminate-materials/laminate/${this.$route.params.id}`).then(({ data }) => {
            this.materials = data.materials
            this.$q.loading.hide()
          })
        } else {
          this.$q.loading.hide()
        }
      })
    }
  }
}
</script>

<style>
</style>

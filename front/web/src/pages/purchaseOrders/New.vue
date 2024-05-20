<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-12 col-md-8">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Ordenes de compra" to="/purchase-orders" />
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
            <div class="col-sm-12 col-md-2">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="order.fields.requested_date"
                mask="date"
                label="Arribo"
              >
                <template v-slot:prepend>
                  <q-icon name="event" />
                </template>
                <q-popup-proxy
                  ref="orderFieldsRequestedDateRef"
                  transition-show="scale"
                  transition-hide="scale"
                >
                  <div class="col-sm-12">
                    <q-date
                     :locale="myLocale"
                      mask="DD/MM/YYYY"
                      v-model="order.fields.requested_date"
                      @input="() => $refs.orderFieldsRequestedDateRef.hide()"
                      today-btn
                    />
                  </div>
                </q-popup-proxy>
              </q-select>
            </div>
            <div class="col-sm-12 col-md-6">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                use-input
                hide-selected
                fill-input
                input-debounce="0"
                @filter="filterSupplier"
                v-model="order.fields.supplier"
                :options="filteredSupplierOptions"
                :error="$v.order.fields.supplier.$error"
                label="Proveedor"
                :rules="supplierRules"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-building" />
                </template>
              </q-select>
            </div>
            <div class="col-sm-12 col-md-2 text-center">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="order.fields.reference"
                label="# Referencia"
              >
                <template v-slot:prepend>
                  <q-icon name="fact_check" />
                </template>
              </q-input>
            </div>
            <div class="col-sm-12 col-md-2 text-center">
              <q-input
                color="white"
                bg-color="blue"
                filled
                dark
                disable
                v-model="order.fields.status"
                :error="$v.order.fields.status.$error"
                label="Estatus"
                :rules="statusRules"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-battery-empty" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-3 text-center">
                            <q-select
                            color="dark"
                            bg-color="secondary"
                            filled
                            :options="branchesList"
                            v-model="order.fields.branch_id"
                            :rules="branchOfficeRules"
                            :error="$v.order.fields.branch_id.$error"
                            label="Sucursal Entrada">
                            <template v-slot:prepend>
                                <q-icon name="business"></q-icon>
                            </template>
                            </q-select>
                        </div>
                        <div class="col-xs-12 col-sm-3 text-center">
                            <q-select
                            color="dark"
                            bg-color="secondary"
                            filled
                            v-model="order.fields.storage"
                            :options="filteredStorageOptions"
                            :rules="storageRules"
                            :error="$v.order.fields.storage.$error"
                            label="Almacén Entrada">
                            <template v-slot:prepend>
                                <q-icon name="store"></q-icon>
                            </template>
                            </q-select>
                        </div>
          </div>

          <div class="row q-mb-sm q-mt-md">
            <div class="col-sm-12 col-md-2 offset-md-10 pull-right">
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
const { required, maxLength } = require('vuelidate/lib/validators')

export default {
  name: 'NewPurchaseOrder',
  validations: {
    order: {
      fields: {
        status: { required, maxLength: maxLength(10) },
        supplier: { required },
        producer: { maxLength: maxLength(100) },
        branch_id: { required },
        storage: { required },
        refence: { }
      }
    }
  },
  data () {
    return {
      myLocale: {
        /* starting with Sunday */
        days: 'Domingo_Lunes_Martes_Miércoles_Jueves_Viernes_Sábado'.split('_'),
        daysShort: 'Dom_Lun_Mar_Mié_Jue_Vie_Sáb'.split('_'),
        months: 'Enero_Febrero_Marzo_Abril_Mayo_Junio_Julio_Agosto_Septiembre_Octubre_Noviembre_Diciembre'.split('_'),
        monthsShort: 'Ene_Feb_Mar_Abr_May_Jun_Jul_Ago_Sep_Oct_Nov_Dic'.split('_'),
        firstDayOfWeek: 1
      },
      order: {
        fields: {
          id: null,
          serial: null,
          status: 'NUEVO',
          supplier: null,
          producer: null,
          branch_id: null,
          storage: null,
          reference: null,
          requested_date: null
        }
      },
      branchesList: [],
      storageOptions: [],
      supplierOptions: [],
      filteredSupplierOptions: []
    }
  },
  computed: {
    roleId () {
      const user = this.$store.getters['users/rol']
      return parseInt(user)
    },
    statusRules (val) {
      return [
        val => (this.$v.order.fields.status.required) || 'El campo Estatus es requerido.',
        val => (this.$v.order.fields.status.maxLength) || 'El campo Estatus no debe exceder los 10 dígitos.'
      ]
    },
    branchOfficeRules (val) {
      return [
        val => (this.$v.order.fields.branch_id.required) || 'El campo Sucursal es requerido.'
      ]
    },
    storageRules (val) {
      return [
        val => (this.$v.order.fields.storage.required) || 'El campo Almacén es requerido.'
      ]
    },
    supplierRules (val) {
      return [
        val => this.$v.order.fields.supplier.required || 'El campo Proveedor es requerido.'
      ]
    },
    producerRules (val) {
      return [
        val => (this.$v.order.fields.producer.maxLength) || 'El campo Fabricante no debe exceder los 100 dígitos.'
      ]
    },
    filteredStorageOptions () {
      if (this.order.fields.branch_id != null && this.order.fields.branch_id.value != null) {
        return this.storageOptions.filter(storage => storage.branchOffice === this.order.fields.branch_id.value)
      }
      return []
    }
  },
  beforeRouteEnter (to, from, next) {
    next(vm => {
      const propiedades = vm.$store.getters['users/rol']
      console.log(propiedades)
      if (propiedades === 1 || propiedades === 3 || propiedades === 7 || propiedades === 2 || propiedades === 20 || propiedades === 4 || propiedades === 27 || propiedades === 20 || propiedades === 22 | propiedades === 28) {
        next()
      } else {
        next('/')
      }
    })
  },
  /* beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(5) && !this.$store.getters['users/roles'].includes(10) && !this.$store.getters['users/roles'].includes(22) && !this.$store.getters['users/roles'].includes(26)) {
      this.$router.push('/')
    }
  }, */
  created () {
    this.fetchFromServer()
    this.getBranchesList()
    this.getStoragesList()
  },
  methods: {
    invertDate (date) {
      if (date !== null) {
        var info = date.split('/').reverse().join('-')
      } else {
        return null
      }
      return info
    },
    filterSupplier (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.filteredSupplierOptions = this.supplierOptions.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    fetchFromServer () {
      this.$q.loading.show()
      api.get('/suppliers/options').then(({ data }) => {
        this.supplierOptions = data.options
        this.$q.loading.hide()
      })
    },
    backToGrid () {
      this.$router.push('/purchase-orders')
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
      const params = { ...this.order.fields }
      params.requested_date = this.invertDate(this.order.fields.requested_date)
      params.supplier_id = params.supplier.value
      console.log(params)
      api.post('/purchase-orders', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.$router.push(`/purchase-orders/${data.order.id}`)
        } else {
          this.$q.loading.hide()
        }
      })
    },
    getBranchesList () {
      api.get('branch-offices/options').then(data => {
        this.branchesList = data.data.options
      })
    },
    getStoragesList () {
      api.get('storages/options').then(data => {
        this.storageOptions = data.data.options
      }
      )
    }
  }
}
</script>

<style>
</style>

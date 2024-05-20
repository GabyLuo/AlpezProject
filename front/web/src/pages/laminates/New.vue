<template>
  <q-page>
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-9">
          <span class="q-ml-md grey-8 fs28 page-title">Nuevo Laminado</span>
        </div>
        <div class="col-sm-3">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="right">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Laminados" to="/laminates" />
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
                v-model="laminate.fields.scheduledDate"
                :error="$v.laminate.fields.scheduledDate.$error"
                mask="date"
                label="Fecha programada"
                :rules="scheduledDateRules"
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
            <div class="col-xs-12 col-sm-3">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="laminate.fields.product"
                :error="$v.laminate.fields.product.$error"
                :options="productOptions"
                label="Producto"
                :rules="productRules"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-store-alt" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-3">
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
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-warehouse" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-3">
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
          </div>

          <div class="row q-mb-sm q-mt-md">
            <div class="col-xs-12 col-sm-2 offset-sm-10 pull-right">
              <q-btn color="positive" icon="save" label="Guardar" @click="createLaminate()" />
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
  name: 'NewLaminate',
  validations: {
    laminate: {
      fields: {
        scheduledDate: { required },
        product: { required },
        branchOffice: { required },
        storage: { required }
      }
    }
  },
  data () {
    return {
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
      storageOptions: []
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
    }
  },
  created () {
    this.fetchFromServer()
  },
  methods: {
    fetchFromServer () {
      this.$q.loading.show()
      api.get('/products/options/category/5').then(({ data }) => {
        this.productOptions = data.options
        api.get('/branch-offices/options').then(({ data }) => {
          this.branchOfficeOptions = data.options
          api.get('/storages/options').then(({ data }) => {
            this.storageOptions = data.options
            this.$q.loading.hide()
          })
        })
      })
    },
    branchOfficeUpdated () {
      if (this.laminate.fields.branchOffice != null && this.laminate.fields.branchOffice.value != null) {
        const filteredStorages = this.storageOptions.filter(so => (Number(so.branchOffice) === Number(this.laminate.fields.branchOffice.value) && Number(so.storageType) === 8))
        if (filteredStorages.length > 0) {
          this.laminate.fields.storage = { value: filteredStorages[0].value, label: filteredStorages[0].label }
        } else {
          this.laminate.fields.storage = null
        }
      }
    },
    createLaminate () {
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
      params.scheduledDate = { ...this.laminate.fields }.scheduledDate
      params.productId = Number({ ...this.laminate.fields }.product.value)
      params.branchOfficeId = Number({ ...this.laminate.fields }.branchOffice.value)
      api.post('/laminates', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.$router.push(`/laminates/${data.laminate.id}`)
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

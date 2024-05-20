<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-8">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Movimientos" to="/movements" />
              <q-breadcrumbs-el label="Nuevo Movimiento" />
            </q-breadcrumbs>
          </div>
        </div>
      </div>
    </div>

    <div class="q-pa-md bg-grey-3">
      <div class="row bg-white border-panel">
        <div class="col q-pa-md">
          <div class="row q-col-gutter-xs">
            <div class="col-xs-12 col-sm-2 text-center">
                <q-select color="dark"
                bg-color="secondary"
                filled
                :rules="dateRules"
                v-model="movement.fields.date"
                mask="date"
                label="Fecha">
                <template v-slot:prepend>
                    <q-icon name="event"></q-icon>
                </template>
                <q-popup-proxy ref="date" transition-show="scale" transition-hide="scale">
                    <div class="col-sm-12">
                        <q-date
                        color="secondary"
                        text-color="white"
                        mask="DD/MM/YYYY"
                        v-model="movement.fields.date"
                        @input="() => $refs.date.hide()"
                        today-btn>
                        </q-date>
                    </div>
                </q-popup-proxy>
                </q-select>
            </div>
            <div class="col-sm-12 col-md-2">
              <q-input
                color="white"
                dark
                bg-color="blue"
                filled
                v-model="movement.fields.statusStr"
                label="Estatus"
                readonly
              >
                <template v-slot:prepend>
                  <q-icon name="battery_full" />
                </template>
              </q-input>
            </div>
          </div>
          <div class="row q-col-gutter-xs">
            <div class="col-xs-12 col-sm-4 text-center">
                <q-select
                color="white"
                dark
                bg-color="negative"
                filled
                readonly
                v-model="movement.fields.type_movement_1"
                filter
                label="Tipo de Movimiento">
                <template v-slot:prepend>
                    <q-icon name="swap_horiz"></q-icon>
                </template>
                </q-select>
            </div>
            <div class="col-sm-12 col-md-4">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                @input="() => movement.fields.originStorage=''"
                v-model="movement.fields.originBranchOffice"
                :options="branchOfficeOptions"
                label="Estación origen"
                emit-value map-options
                :rules="originBranchOfficeRules"
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
                v-model="movement.fields.originStorage"
                :options="filteredOriginStorageOptions"
                label="Almacén origen"
                :rules="originStorageRules"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-warehouse" />
                </template>
              </q-select>
            </div>
            <!-- <div class="col-xs-12 col-sm-3 text-center">
              <q-input
                  color="dark"
                  bg-color="secondary"
                  filled
                  readonly
                  v-model="movement.fields.folio_1"
                  label="Folio">
                <template v-slot:prepend>
                    <q-icon name="style"></q-icon>
                </template>
              </q-input>
            </div> -->
            <div class="col-xs-12 col-sm-4 text-center">
                <q-select
                color="dark"
                dark
                bg-color="positive"
                filled
                readonly
                v-model="movement.fields.type_movement_2"
                filter
                label="Tipo de Movimiento">
                <template v-slot:prepend>
                    <q-icon name="swap_horiz"></q-icon>
                </template>
                </q-select>
            </div>
            <div class="col-sm-12 col-md-4">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                @input="() => movement.fields.destinationStorage=''"
                v-model="movement.fields.destinationBranchOffice"
                :options="branchOfficeOptions2"
                label="Estación destino"
                :rules="destinationBranchOfficeRules"
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
                v-model="movement.fields.destinationStorage"
                :options="filteredDestinationStorage"
                label="Almacén destino"
                :rules="destinationStorageRules"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-warehouse" />
                </template>
              </q-select>
            </div>
            <!-- <div class="col-xs-12 col-sm-3 text-center">
              <q-input
                  color="dark"
                  bg-color="secondary"
                  filled
                  readonly
                  v-model="movement.fields.folio_1"
                  label="Folio">
                <template v-slot:prepend>
                    <q-icon name="style"></q-icon>
                </template>
              </q-input>
            </div> -->
            <div class="col-sm-12 col-md-12 pull-right">
              <q-btn color="positive" icon="save" label="Guardar" @click="createBranchOfficeTransfer()" />
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
  name: 'NewBranchTransfer',
  validations: {
    movement: {
      fields: {
        originBranchOffice: { required },
        originStorage: { required },
        destinationBranchOffice: { required },
        destinationStorage: { required },
        date: { required }
      }
    }
  },
  data () {
    return {
      movement: {
        fields: {
          id: null,
          date: null,
          originBranchOffice: null,
          originStorage: null,
          destinationBranchOffice: null,
          destinationStorage: null,
          statusStr: 'NUEVO',
          transactionId: null,
          folio_1: null,
          folio_2: null,
          type_movement_1: 'TRASPASO (SALIDA)',
          type_movement_2: 'TRASPASO (ENTRADA)'
        }
      },
      branchOfficeOptions: [],
      branchOfficeOptions2: [],
      storageOptions: [],
      storageOptions2: []
    }
  },
  computed: {
    dateRules (val) {
      return [
        val => (this.$v.movement.fields.date.required) || 'El campo Fecha es requerido.'
      ]
    },
    originBranchOfficeRules (val) {
      return [
        val => (this.$v.movement.fields.originBranchOffice.required) || 'El campo Sucursal origen es requerido.'
      ]
    },
    originStorageRules (val) {
      return [
        val => (this.$v.movement.fields.originStorage.required) || 'El campo Almacén origen es requerido.'
      ]
    },
    destinationBranchOfficeRules (val) {
      return [
        val => (this.$v.movement.fields.destinationBranchOffice.required) || 'El campo Sucursal destino es requerido.'
      ]
    },
    destinationStorageRules (val) {
      return [
        val => (this.$v.movement.fields.destinationStorage.required) || 'El campo Almacén destino es requerido.'
      ]
    },
    filteredOriginStorageOptions () {
      // Seleccionamos un almacen de origen
      if (this.movement.fields.originBranchOffice != null && !isNaN(this.movement.fields.originBranchOffice)) {
        return this.storageOptions.filter(op => Number(op.branchOffice) === Number(this.movement.fields.originBranchOffice))
      }
      return []
    },
    filteredDestinationStorage () {
      // console.log('Oficina destino')
      // console.log(this.movement.fields.destinationBranchOffice)
      if (this.movement.fields.destinationBranchOffice != null && !isNaN(this.movement.fields.destinationBranchOffice.value)) {
        return this.storageOptions2.filter(op => Number(op.branchOffice) === Number(this.movement.fields.destinationBranchOffice.value) && Number(this.movement.fields.originStorage.value) !== Number(op.value))
      }
      return []
    }
  },
  /* beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(2)) {
      this.$router.push('/')
    }
  }, */
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
  created () {
    this.fetchFromServer()
    // this.getFolioConsecutivo()
  },
  methods: {
    fetchFromServer () {
      this.$q.loading.show()
      api.get('/branch-offices/options').then(({ data }) => {
        // Se guardan las mismas sucursales en los arreglos para despues ser filtradas
        this.branchOfficeOptions = data.options
        this.branchOfficeOptions2 = data.options
        api.get('/storages/options').then(({ data }) => {
          this.storageOptions = data.options
          this.storageOptions2 = data.options
          this.$q.loading.hide()
        })
      })
    },
    invertDate (date) {
      if (date !== null) {
        var info = date.split('/').reverse().join('-')
      }
      return info
    },
    createBranchOfficeTransfer () {
      this.$v.movement.fields.$reset()
      this.$v.movement.fields.$touch()
      if (this.$v.movement.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = []
      params.date = this.invertDate(this.movement.fields.date)
      params.originStorage = Number({ ...this.movement.fields }.originStorage.value)
      params.destinationStorage = Number({ ...this.movement.fields }.destinationStorage.value)
      params.status = { ...this.movement.fields }.statusStr
      console.log(params)
      api.post('/branch-transfers', params).then(({ data }) => {
        console.log(data)
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.$router.push(`/movements/${data.branchTransfer.id}`)
        } else {
          this.$q.loading.hide()
        }
      })
    }
    // prueba () {
    //   this.movement.fields.originStorage = null // Almacen de origen
    //   const id = this.movement.fields.originBranchOffice // Sucursal origen
    //   this.branchOfficeOptions2 = []
    //   this.branchOfficeOptions.forEach(element => {
    //     if (id !== element.value) {
    //       this.branchOfficeOptions2.push({ label: element.label, value: element.value })
    //     }
    //   })
    // },
    // test () {
    //   const id = this.movement.fields.originStorage.value // Sucursal origen
    //   this.storageOptions2 = [] // Almacenes a mostrar en la segunda opcion
    //   this.storageOptions.forEach(element => {
    //     if (id !== element.value) {
    //       this.storageOptions2.push({ label: element.label, value: element.value })
    //     }
    //   })
    // }
    // getFolioConsecutivo () {
    //   const id = this.$route.params.id
    //   console.log(id)
    //   this.movement.fields.folio = ''
    //   api.get(`/movements/getFolio/${id}`).then(({ data }) => {
    //     console.log(data)
    //     if (data.result === 'success') {
    //       this.movement.fields.folio_1 = data.folio
    //       this.movement.fields.folio_2 = data.folio
    //     }
    //   })
    // }
  }
}
</script>

<style>
</style>

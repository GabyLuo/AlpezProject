<template>
    <q-page class="bg-grey-3">
        <div class="q-pa-sm panel-header">
            <div class="row">
                <div class="col-sm-8">
                    <div class="q-pa-md q-gutter-sm">
                        <q-breadcrumbs align="left" style="font-size: 20px">
                            <q-breadcrumbs-el label="" icon="home" to="/"/>
                            <q-breadcrumbs-el label="Movimientos" to="/movements" />
                            <q-breadcrumbs-el label="Nuevo Movimiento"/>
                        </q-breadcrumbs>
                    </div>
                </div>
            </div>
        </div>
        <div class="q-pa-md bg-grey-3">
            <div class="row bg-white border-panel">
                <div class="col q-pa-md">
                    <div class="row q-col-gutter-xs">
                        <div class="col-xs-12 col-sm-3 text-center" style="font-smooth: never; !important">
                            <q-select
                            color="white"
                            :bg-color="color"
                            filled
                            dark
                            readonly
                            v-model="movement.fields.type_movement"
                            filter
                            label="Tipo de Movimiento">
                            <template v-slot:prepend>
                                <q-icon name="swap_horiz"></q-icon>
                            </template>
                            </q-select>
                        </div>
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
                            <q-popup-proxy
                            ref="date_ref"
                            transition-show="scale"
                            transition-hide="scale">
                                <div class="col-sm-12">
                                    <q-date
                                    color="secondary"
                                    :locale="myLocale"
                                    text-color="white"
                                    mask="DD/MM/YYYY"
                                    v-model="movement.fields.date"
                                    @input="() => $refs.date_ref.hide()"
                                    today-btn>
                                    </q-date>
                                </div>
                            </q-popup-proxy>
                            </q-select>
                        </div>
                        <div class="col-xs-12 col-sm-2 text-center">
                            <q-select
                            color="dark"
                            bg-color="secondary"
                            filled
                            :options="branchesList"
                            v-model="movement.fields.branch_id"
                            :rules="branchOfficeRules"
                            :error="$v.movement.fields.branch_id.$error"
                            label="Estación">
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
                            v-model="movement.fields.storage"
                            :options="filteredStorageOptions"
                            :rules="storageRules"
                            :error="$v.movement.fields.storage.$error"
                            label="Almacén">
                            <template v-slot:prepend>
                                <q-icon name="store"></q-icon>
                            </template>
                            </q-select>
                        </div>
                        <div class="col-xs-12 col-sm-2 text-center">
                            <q-input
                            bg-color="blue"
                            filled
                            dark
                            readonly
                            emit-value
                            v-model="movement.fields.status"
                            label="Estatus">
                            <template v-slot:prepend>
                                <q-icon name="fas fa-info"></q-icon>
                            </template>
                            </q-input>
                        </div>
                        <div class="col-xs-12 col-sm-12 pull-right" style="margin-top:1%">
                            <q-btn color="positive" icon="save" label="Guardar" @click="createMovement()"/>
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
  name: 'newMovement',
  validations: {
    movement: {
      fields: {
        branch_id: { required },
        storage: { required },
        date: { required }
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
      movement: {
        fields: {
          type_movement: null,
          status: 'NUEVO',
          folio: null,
          branch_id: null,
          storage: null,
          type_id: null,
          date: null
        }
      },
      show: true,
      poList: [],
      branchesList: [],
      storageOptions: [],
      color: 'positive'
    }
  },
  created () {
    this.getBranchesList()
    this.getStoragesList()
    // this.getPoList()
  },
  mounted () {
    const id = this.$route.params.id
    if (id === '1') {
      this.movement.fields.type_movement = 'ENTRADA'
      this.movement.fields.type_id = Number(1)
    } else if (id === '2') {
      this.movement.fields.type_movement = 'SALIDA'
      this.movement.fields.type_id = Number(2)
      this.color = 'negative'
    } else if (id === '3') {
      this.movement.fields.type_movement = 'INVENTARIO FÍSICO'
      this.movement.fields.type_id = Number(3)
      this.color = 'light-blue'
    } else if (id === '6') {
      this.movement.fields.type_movement = 'MERMA'
      this.movement.fields.type_id = Number(6)
      this.color = 'purple'
    }
    // this.getFolioConsecutivo()
  },
  computed: {
    dateRules (val) {
      return [
        val => (this.$v.movement.fields.date.required) || 'El campo Fecha es requerido.'
      ]
    },
    branchOfficeRules (val) {
      return [
        val => (this.$v.movement.fields.branch_id.required) || 'El campo Sucursal es requerido.'
      ]
    },
    storageRules (val) {
      return [
        val => (this.$v.movement.fields.storage.required) || 'El campo Almacén es requerido.'
      ]
    },
    filteredStorageOptions () {
      if (this.movement.fields.branch_id != null && this.movement.fields.branch_id.value != null) {
        return this.storageOptions.filter(storage => storage.branchOffice === this.movement.fields.branch_id.value)
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
      if (propiedades === 1 || propiedades === 3 || propiedades === 7 || propiedades === 2 || propiedades === 20 || propiedades === 4 || propiedades === 27 || propiedades === 22 || propiedades === 26) {
        next()
      } else {
        next('/')
      }
    })
  },
  methods: {
    isEntrada () {
      return this.movement.fields.type_id === 1
    },
    createMovement () {
      this.$v.movement.fields.$reset()
      this.$v.movement.fields.$touch()

      if (this.$v.movement.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones',
          persisten: true
        })
        return false
      }
      this.$q.loading.show()
      const params = []
      params.date = this.invertDate(this.movement.fields.date)
      params.type_id = Number({ ...this.movement.fields }.type_id) // Tipo de movimiento
      params.storage_id = Number({ ...this.movement.fields }.storage.value)
      params.status = { ...this.movement.fields }.status
      console.log(params)
      api.post('/movements', params).then(({ data }) => {
        const idMovement = data.movement_id
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.$router.push(`/movements/${idMovement}`)
        } else {
          this.$q.loading.hide()
        }
      })
    },
    invertDate (date) {
      if (date !== null) {
        var info = date.split('/').reverse().join('-')
      }
      return info
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
    },
    getFolioConsecutivo () {
      const id = this.$route.params.id
      this.movement.fields.folio = ''
      api.get(`/movements/getFolio/${id}`).then(({ data }) => {
        if (data.result === 'success') {
          this.movement.fields.folio = data.folio
        }
      })
    }
  }
}
</script>

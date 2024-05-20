<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-8">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Movimientos" />
            </q-breadcrumbs>
          </div>
        </div>
        <div class="col-xs-6 col-md-4 pull-right">
          <div class="q-pa-sm q-gutter-sm">
            <q-btn class="bg-primary" style="color: white" icon="add" label="Nuevo" @click.native="$router.push('/trade-movements/new')" />
          </div>
        </div>
      </div>
    </div>

    <div class="q-pa-md bg-grey-3">
      <div class="row bg-white border-panel">
        <div class="col q-pa-md">
         <div class="row q-mb-sm">
           <div class="col-xs-12 col-sm-2" style="padding-left: 20px">
              <q-select color="dark" bg-color="secondary" filled v-model="search.fields.year"  @input="fetchFromServer ()" label="Año" :options="selectYears" emit-value map-options>
                <template v-slot:prepend>
                  <q-icon name="fa fa-calendar" />
                </template>
              </q-select>
            </div>
           <div class="col-xs-12 col-sm-2" style="padding-left: 20px">
              <q-select color="dark" bg-color="secondary" filled v-model="search.fields.month"  @input="changeMonth ()" label="Mes" :options="selectMonths" emit-value map-options>
                <template v-slot:prepend>
                  <q-icon name="fa fa-calendar" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-3" style="padding-left: 20px">
              <q-select color="dark" bg-color="secondary" filled v-model="search.fields.account"  @input="fetchFromServer ()" label="Cuenta" :options="accountOptions" emit-value map-options>
                <template v-slot:prepend>
                  <q-icon name="fa fa-building" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-3" style="padding-left: 20px">
              <q-select color="dark" bg-color="secondary" filled v-model="search.fields.output_type"  @input="fetchFromServer ()" label="Rubro" :options="outputOptions" emit-value map-options>
                <template v-slot:prepend>
                  <q-icon name="fa fa-building" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-2" style="padding-left: 20px">
              <q-select color="dark" bg-color="secondary" filled v-model="search.fields.movement_type"  @input="fetchFromServer ()" label="Movimiento" :options="movementOptions" emit-value map-options>
                <template v-slot:prepend>
                  <q-icon name="fa fa-building" />
                </template>
              </q-select>
            </div>
             <div class="col-xs-12 col-md-12 pull-right" style="padding: 3px;">
                       <q-btn  icon="fas fa-eraser" class="full-height" @click="cleanFilters()" color="primary" style="margin-left: 6px;">
                        <q-tooltip content-class="bg-primary">Limpiar filtros</q-tooltip>
                      </q-btn>
            </div>

          </div>
          <q-table
            flat
            bordered
            :data="data"
            :columns="columns"
            row-key="code"
            :pagination.sync="pagination"
            @request="onRequest"
          >
            <template v-slot:top>
            </template>
            <template v-slot:body="props">
              <q-tr :props="props">
                <q-td key="actions"  style="width: 18%;" :props="props" class="pull-left">
                  <q-btn v-show="props.row.date != ''" color="primary" icon="fas fa-edit" flat @click.native="editSelectedRow(props.row.id)" size="10px">
                    <q-tooltip content-class="bg-primary">Editar</q-tooltip>
                  </q-btn>
                  <q-btn v-show="props.row.date != ''" color="red" icon="fas fa-trash-alt" flat @click.native="deleteSelectedRow(props.row.id)" size="10px">
                    <q-tooltip content-class="bg-red">Eliminar</q-tooltip>
                  </q-btn>
                </q-td>
                <q-td key="date" style="text-align: center;" :props="props">{{ props.row.date }}</q-td>
                <q-td key="movement_type" style="text-align: left;" :props="props">{{ props.row.movement }}</q-td>
                <q-td key="account_type" style="text-align: left;" :props="props">{{ props.row.account }}</q-td>
                <q-td key="output_type" style="text-align: left;" :props="props">{{ props.row.output }}</q-td>
                <q-td key="description" :class="props.row.movement == '' ? 'text-bold' : ''" style="text-align: left;" :props="props">{{ props.row.description }}</q-td>
                <q-td  key="amount" :class="props.row.movement == '' ? 'text-bold' : ''" style="text-align: right; color:white; " class="bg-red" :props="props">{{  formatPrice(Number(props.row.amount)) }}</q-td>
                <q-td  key="amount_abono" :class="props.row.movement == '' ? 'text-bold' : ''" style="text-align: right; color:white; " class="bg-positive" :props="props">{{ formatPrice(Number(props.row.amount_abono)) }}</q-td>
              </q-tr>
            </template>
             <template v-slot:bottom-row>
              <q-tr>
                <q-td></q-td>
                <q-td></q-td>
                <q-td></q-td>
                <q-td></q-td>
                <q-td></q-td>
                <q-td class="text-weight-bold text-right">TOTAL</q-td>
                <q-td></q-td>
                <q-td style="text-align: center;color:white;  " :style="(total) >= 0 ? 'background-color: green !important;' : 'background-color: red !important;' ">
                  {{ formatPrice(Number.parseFloat(total.toFixed(2)))}}
                </q-td>
              </q-tr>
            </template>
          </q-table>
        </div>
      </div>
    </div>

  </q-page>
</template>

<script>
import api from '../../commons/api.js'

export default {
  name: 'Index',
  data () {
    return {
      pagination: {
        sortBy: 'id',
        descending: true,
        rowsPerPage: 25,
        page: 1,
        rowsNumber: 0
      },
      columns: [
        { name: 'actions', align: 'center', label: '', field: 'actions', style: 'width: 10%', sortable: false },
        { name: 'date', align: 'center', label: 'FECHA', field: 'date', sortable: true },
        { name: 'movement_type', align: 'center', label: 'MOVIMIENTO', field: 'movement_type', sortable: true },
        { name: 'account_type', align: 'center', label: 'CUENTA', field: 'account_type', sortable: true },
        { name: 'output_type', align: 'center', label: 'RUBRO', field: 'output_type', sortable: true },
        { name: 'description', align: 'center', label: 'DESCRIPCIÓN', field: 'description', sortable: true },
        { name: 'amount', align: 'center', label: 'CARGOS', field: 'amount', sortable: true },
        { name: 'amount_abono', align: 'center', label: 'ABONOS', style: 'width: 8%', field: 'amount_abono', sortable: true }
      ],
      data: [],
      filter: '',
      total: 0,
      totalCargo: 0,
      totalAbono: 0,
      search: {
        fields:
         {
           output_type: 0,
           year: 0,
           month: 0,
           account: 0,
           movement_type: 'all'
         }
      },
      selectYears: [],
      selectMonths: [],
      outputOptions: [],
      movementOptions: [
        { value: 'all', label: 'TODOS' },
        { value: 'CARGO', label: 'CARGO' },
        { value: 'ABONO', label: 'ABONO' }
      ],
      accountOptions: []
    }
  },
  beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(2) && !this.$store.getters['users/roles'].includes(24)) {
      this.$router.push('/')
    }
  },
  mounted () {
    this.$q.loading.show()
    this.search.fields.month = this.$q.localStorage.getItem('month')
    if (!this.search.fields.month) {
      this.search.fields.month = 0
    }
    this.fetchFromServer()
    this.$q.loading.hide()
    this.getOutputs()
    this.getMonths()
    this.getAccounts()
    this.getYears()
  },
  methods: {
    onRequest (requestProp) {
      this.pagination = requestProp.pagination
      this.filter = requestProp.filter
      this.fetchFromServer()
    },
    formatPrice (value) {
      const options2 = { style: 'currency', currency: 'USD', maximumFractionDigits: 13 }
      const numberFormat2 = new Intl.NumberFormat('en-US', options2)
      return numberFormat2.format(value)
    },
    changeMonth () {
      this.$q.localStorage.set('month', this.search.fields.month)
      this.fetchFromServer()
    },
    fetchFromServer () {
      this.$q.loading.show()

      const params = Object.assign(this.pagination, this.search.fields, this.filter)
      console.log(params)
      api.post('movement-trade/getMovements', params).then(({ data }) => {
        this.data = data.movements
        this.pagination.rowsNumber = data.count
        this.totalCargo = data.total_cargo ? data.total_cargo : 0
        this.totalAbono = data.total_abono ? data.total_abono : 0
        this.total = Number.parseFloat(this.totalAbono) + Number.parseFloat(this.totalCargo)
        this.$q.loading.hide()
      })
    },
    editSelectedRow (id) {
      this.$router.push(`/trade-movements/${id}`)
    },
    deleteSelectedRow (id) {
      this.$q.dialog({
        title: 'Confirmación',
        message: '¿Desea eliminar esta Movimiento?',
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
        api.delete(`/movement-trade/${id}`).then(({ data }) => {
          this.$q.notify({
            message: data.message.content,
            position: 'top',
            color: (data.result ? 'positive' : 'warning'),
            icon: (data.result ? 'thumb_up' : 'close')
          })
          if (data.result) {
            this.fetchFromServer()
          }
        })
      }).onCancel(() => {})
    },
    getOutputs () {
      this.$q.loading.show()
      api.get('/output_type/options').then(({ data }) => {
        this.outputOptions = data.options
        this.outputOptions.unshift({ label: 'TODOS', value: 0 })
        this.$q.loading.hide()
      })
    },
    getAccounts () {
      this.$q.loading.show()
      api.get('/account-trade/options').then(({ data }) => {
        this.accountOptions = data.options
        this.accountOptions.unshift({ label: 'TODOS', value: 0 })
        this.$q.loading.hide()
      })
    },
    getYears () {
      this.$q.loading.show()
      api.get('/movement-trade/optionsYears').then(({ data }) => {
        this.selectYears = data.options
        this.selectYears.unshift({ label: 'TODOS', value: 0 })
        this.$q.loading.hide()
      })
    },
    getMonths () {
      this.selectMonths = []
      const monthNames = ['TODOS', 'ENERO', 'FEBRERO', 'MARZO', 'ABRIR', 'MAYO', 'JUNIO',
        'JULIO', 'AGOSTO', 'SEPTIEMBRE', 'OCTUBRE', 'NOVIEMBRE', 'DICIEMBRE'
      ]
      for (const row in monthNames) {
        this.selectMonths.push({ label: monthNames[row], value: parseInt(row) })
      }
    },
    cleanFilters () {
      this.search.fields.year = 0
      this.search.fields.month = 0
      this.search.fields.account = 0
      this.search.fields.output_type = 0
      this.search.fields.movement_type = 'all'
      this.$q.localStorage.set('month', 0)
      this.fetchFromServer()
    }
  }
}
</script>

<style>
</style>

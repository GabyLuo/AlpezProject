<template>
  <q-page>
    <div class="q-pa-md panel-header">
      <div class="row">
        <div class="col-sm-8">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Incentivos producción." />
            </q-breadcrumbs>
          </div>
        </div>
        <div style="margin-top: 10px;" class="col-sm-4 pull-right">
          <q-btn style="margin-right: 10px" color="primary" icon="fas fa-file-pdf" @click="getPdf()" />
          <q-btn style="margin-right: 10px" color="positive" icon="fas fa-file-excel" @click="getCsv()" />
        </div>
      </div>
    </div>
    <div class="q-pa-md bg-grey-3">
      <div class="row bg-white">
        <div class="col-md-3 q-pa-md">
          <q-select  color="dark" bg-color="secondary" filled
            use-input
            hide-selected
            fill-input
            v-model="employee"
            input-debounce="0"
            label="Empleado"
            :options="selectEmployees"
            emit-value map-options>
          </q-select>
        </div>
        <div class="col-md-3 q-pa-md">
          <q-select color="white"
            bg-color="secondary"
            filled
            dark
            v-model="date_start"
            mask="date"
            label="Fecha inicio">
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
                    v-model="date_start"
                    @input="() => $refs.date_ref.hide()"
                    today-btn>
                    </q-date>
                </div>
            </q-popup-proxy>
          </q-select>
        </div>
        <div class="col-md-3 q-pa-md">
          <q-select color="white"
            bg-color="secondary"
            filled
            dark
            v-model="date_end"
            mask="date"
            label="Fecha fin">
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
                    v-model="date_end"
                    @input="() => $refs.date_ref.hide()"
                    today-btn>
                    </q-date>
                </div>
            </q-popup-proxy>
          </q-select>
        </div>
        <div class="col-md-3 q-pa-md pull-right">
          <q-btn  icon="fas fa-eraser" class="full-height" @click="cleanFilters()" color="secondary" style="margin-left: 6px;">
            <q-tooltip content-class="bg-primary">Limpiar filtros</q-tooltip>
          </q-btn>
          <q-btn icon="fas fa-search" class="full-height" @click="generatFilter()" color="secondary" style="margin-left: 3px;">
            <q-tooltip content-class="bg-primary">Buscar</q-tooltip>
          </q-btn>
        </div>
      </div>
      <div class="row bg-white" >
        <div class="col q-pa-md">
          <q-table
            flat
            bordered
            :data="data"
            :columns="columns"
            row-key="code"
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
                <q-td key="position" flat style="text-align: left;" class="cursor-pointer" :props="props">{{ props.row.position}}</q-td>
                <q-td key="employee" flat class="cursor-pointer" style="text-align: left;" :props="props">{{ props.row.employee }}</q-td>
                <q-td key="product" style="text-align: left;" :props="props">{{ props.row.product }}</q-td>
                <q-td key="equipment" style="text-align: left;" :props="props">{{ }}</q-td>
                <q-td key="date" style="text-align: left;" :props="props">{{ props.row.date }}</q-td>
                <q-td key="parts" style="text-align: left;" :props="props">{{ Number(props.row.qty).toFixed(2) }}</q-td>
                <q-td key="parts_time" style="text-align: left;" :props="props">{{ Number(props.row.parts_time).toFixed(2) }}</q-td>
                <q-td key="min_job" style="text-align: left;" :props="props">{{ Number(props.row.min_job).toFixed(2) }}</q-td>
                <q-td key="factor" style="text-align: left;" :props="props">{{ props.row.factor }}</q-td>
                <q-td key="incentive" style="text-align: left;" :props="props">{{ Number(props.row.qty)*Number(props.row.factor) || '' }}</q-td>
                <q-td key="efficiency" style="text-align: left;" :props="props">{{ Number(props.row.efficiency).toFixed(2) + '%' }}</q-td>
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
  name: 'IndexStorageExits',
  data () {
    return {
      pagination: {
        sortBy: 'code',
        descending: true,
        page: 1,
        rowsNumber: 0,
        rowsPerPage: 25
      },
      filter: '',
      columns: [
        { name: 'position', align: 'center', label: 'PUESTO', field: 'position', sortable: true },
        { name: 'employee', align: 'center', label: 'EMPLEADO', field: 'employee', sortable: true },
        { name: 'product', align: 'center', label: 'PRODUCTO', field: 'product', sortable: true },
        { name: 'equipment', align: 'center', label: 'EQUIPO', field: 'equipment', sortable: true },
        { name: 'date', align: 'center', label: 'FECHA', field: 'date', sortable: true },
        { name: 'parts', align: 'center', label: 'PIEZAS', field: 'parts', sortable: true },
        { name: 'parts_time', align: 'center', label: 'PZS X MIN', field: 'parts_time', sortable: true },
        { name: 'min_job', align: 'center', label: 'MIN.TRAB', field: 'min_job', sortable: true },
        { name: 'factor', align: 'center', label: 'FACTOR', field: 'factor', sortable: true },
        { name: 'incentive', align: 'center', label: 'INCENTIVO', field: 'incentive', sortable: true },
        { name: 'efficiency', align: 'center', label: 'EFICIENCIA', field: 'efficiency', sortable: true }
      ],
      data: [],
      employee: null,
      date_start: null,
      date_end: null,
      selectEmployees: []
    }
  },
  computed: {
    roleId () {
      const user = this.$store.getters['users/rol']
      return parseInt(user)
    },
    haspermissionv1 () {
      let permission = false
      if (this.$store.getters['users/roles'].includes(1) || this.$store.getters['users/roles'].includes(3) || this.$store.getters['users/roles'].includes(7) || this.$store.getters['users/roles'].includes(4)) {
        permission = true
      }
      return permission
    }
  },
  /* beforeCreate () {
    if (!(this.$store.getters['users/roles'].includes(1) || this.$store.getters['users/roles'].includes(3) || this.$store.getters['users/roles'].includes(7) || this.$store.getters['users/roles'].includes(4))) {
      this.$router.push('/')
    }
  }, */
  beforeRouteEnter (to, from, next) {
    next(vm => {
      const propiedades = vm.$store.getters['users/rol']
      console.log(propiedades)
      if (propiedades === 1 || propiedades === 2 || propiedades === 12 || propiedades === 17) {
        next()
      } else {
        next('/')
      }
    })
  },
  mounted () {
    this.$q.loading.show()
    this.fetchFromServer()
    this.getEmployees()
    this.$q.loading.hide()
  },
  methods: {
    fetchFromServer () {
      this.$q.loading.show()
      api.get('/incentives-production').then(({ data }) => {
        this.data = data.employees
        this.$q.loading.hide()
      })
    },
    getEmployees () {
      api.get('/employees/options').then(({ data }) => {
        this.selectEmployees = data.options
      })
    },
    async cleanFilters () {
      this.$q.loading.show()
      this.employee = null
      this.date_start = null
      this.date_end = null
      await this.fetchFromServer()
      this.$q.loading.hide()
    },
    generatFilter () {
      this.$q.loading.show()
      let dateStart = this.date_start
      let dateEnd = this.date_end
      if (this.date_start) {
        dateStart = this.date_start.substr(6, 4) + '-' + this.date_start.substr(3, 2) + '-' + this.date_start.substr(0, 2)
      }
      if (this.date_end) {
        dateEnd = this.date_end.substr(6, 4) + '-' + this.date_end.substr(3, 2) + '-' + this.date_end.substr(0, 2)
      }
      api.get(`/incentives-production/${this.employee}/${dateStart}/${dateEnd}`).then(({ data }) => {
        this.data = data.employees
        this.$q.loading.hide()
      })
    },
    getCsv () {
      let dateStart = this.date_start
      let dateEnd = this.date_end
      if (this.date_start) {
        dateStart = this.date_start.substr(6, 4) + '-' + this.date_start.substr(3, 2) + '-' + this.date_start.substr(0, 2)
      }
      if (this.date_end) {
        dateEnd = this.date_end.substr(6, 4) + '-' + this.date_end.substr(3, 2) + '-' + this.date_end.substr(0, 2)
      }
      const uri = process.env.API + `incentives-production/csv/${this.employee}/${dateStart}/${dateEnd}`
      window.open(uri, '_blank')
    },
    getPdf () {
      let dateStart = this.date_start
      let dateEnd = this.date_end
      if (this.date_start) {
        dateStart = this.date_start.substr(6, 4) + '-' + this.date_start.substr(3, 2) + '-' + this.date_start.substr(0, 2)
      }
      if (this.date_end) {
        dateEnd = this.date_end.substr(6, 4) + '-' + this.date_end.substr(3, 2) + '-' + this.date_end.substr(0, 2)
      }
      const uri = process.env.API + `incentives-production/pdf/${this.employee}/${dateStart}/${dateEnd}`
      window.open(uri, '_blank')
    }
  }
}
</script>

<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-8">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Solicitud de vacaciones" />
            </q-breadcrumbs>
          </div>
        </div>
        <div class="col-sm-4 pull-right">
        </div>
      </div>
    </div>

    <div class="q-pa-md bg-grey-3">
      <div class="row bg-white border-panel">
        <div class="col q-pa-md">
          <div class="row q-mb-sm">
            <div class="col-xs-12 col-md-3" style="padding-bottom: 1%;">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="status"
                :options="[
                  {value: true, label: 'APROBADOS'},
                  {value: false, label: 'NO APROBADOS'}
                ]"
                label="Estado"
                @input="getRequestStatus()"
              >
                <template v-slot:prepend>
                  <q-icon name='battery_full'/>
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-md-5" style="padding-bottom: 1%; padding-left: 1%;">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                use-input
                input-debounce="0"
                :options="filterEmployeesOptions"
                @filter="filterEmployees"
                v-model="employeeOption"
                label="Empleados"
                @input="getSelectEmployees(employeeOption.value)"
              >
                <template v-slot:prepend>
                  <q-icon name='battery_full'/>
                </template>
              </q-select>
            </div>
          </div>
          <q-table
            flat
            bordered
            :data="data"
            :columns="status.value ? columnsTrue : columnsFalse"
            row-key="code"
            :pagination.sync="pagination"
            :filter="filter"
          >
            <template v-slot:body="props">
              <q-tr :props="props">
                <q-td key="employee" style="text-align: left;" :props="props">{{ props.row.name }}</q-td>
                <q-td key="date" style="text-align: center;" :props="props">{{ props.row.date_requested.substr(8, 9) + '/' + props.row.date_requested.substr(5, 2) + '/' + props.row.date_requested.substr(0, 4)}}</q-td>
                <q-td key="status" :props="props">
                  <q-chip square dense :color="props.row.status === true ? 'positive': 'negative'" style="text-align: center;" text-color="white">
                    {{ props.row.status ? "APROBADO" : "NO APROBADO"}}
                  </q-chip>
                </q-td>
                <q-td v-if="!props.row.status" key="actions" style="text-align: center;" :props="props">
                  <q-btn color="positive" icon="check_circle_outline" flat @click.native="approveRequestRow(props.row.id)" size="10px">
                    <q-tooltip content-class="bg-primary">Aprobar</q-tooltip>
                  </q-btn>
                  <q-btn color="negative" icon="do_not_disturb_on" flat @click.native="deniedRequestRow(props.row.id)" size="10px">
                    <q-tooltip content-class="bg-primary">Denegar</q-tooltip>
                  </q-btn>
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
  name: 'IndexVacation',
  data () {
    return {
      pagination: {
        sortBy: 'code',
        descending: false,
        rowsPerPage: 25
      },
      columnsTrue: [
        { name: 'employee', align: 'center', label: 'EMPLEADO', field: 'employee', sortable: true },
        { name: 'date', align: 'center', label: 'FECHA SOLICITADA', field: 'date', sortable: true },
        { name: 'status', align: 'center', label: 'ESTATUS', field: 'status', sortable: true }
      ],
      columnsFalse: [
        { name: 'employee', align: 'center', label: 'EMPLEADO', field: 'employee', sortable: true },
        { name: 'date', align: 'center', label: 'FECHA SOLICITADA', field: 'date', sortable: true },
        { name: 'status', align: 'center', label: 'ESTATUS', field: 'status', sortable: true },
        { name: 'actions', align: 'center', label: 'ACCIONES', field: 'actions', style: 'width: 10%', sortable: false }
      ],
      data: [],
      filter: '',
      status: { value: false, label: 'NO APROBADOS' },
      employees: [],
      employeeOption: null,
      filterEmployeesOptions: []
    }
  },
  beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(4) && !this.$store.getters['users/roles'].includes(5)) {
      this.$router.push('/')
    }
  },
  mounted () {
    this.fetchFromServer()
  },
  methods: {
    filterEmployees (val, update) {
      update(() => {
        const needle = val.toLowerCase()
        this.filterEmployeesOptions = this.employees.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
        console.log(this.filterEmployeesOptions)
      })
    },
    fetchFromServer () {
      this.$q.loading.show()
      api.get('/vacations/vacationRequest').then(({ data }) => {
        this.data = data.vacationsRequest
        this.getEmployeesFilter()
        this.$q.loading.hide()
      })
    },
    approveRequestRow (id) {
      api.put(`/vacations/approve-request/${id}`).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        this.fetchFromServer()
      })
    },
    getRequestStatus () {
      this.employeeOption = null
      if (this.status.value) {
        this.$q.loading.show()
        api.get('/vacations/vacationRequestTrue').then(({ data }) => {
          this.data = data.vacationsRequest
          this.getEmployeesFilter()
          this.$q.loading.hide()
        })
      } else {
        this.$q.loading.show()
        api.get('/vacations/vacationRequest').then(({ data }) => {
          this.data = data.vacationsRequest
          this.getEmployeesFilter()
          this.$q.loading.hide()
        })
      }
    },
    getEmployeesFilter () {
      api.get('/employees/options').then(({ data }) => {
        this.employees = data.options
      })
    },
    deniedRequestRow (id) {
      this.$q.dialog({
        title: 'Confirmación',
        message: '¿Desea denegar esta solicitud?',
        persistent: true,
        ok: { label: 'Aceptar', color: 'positive' },
        cancel: { label: 'Cancelar', color: 'negative' }
      }).onOk(() => {
        api.delete(`/vacations/deniedRequest/${id}`).then(({ data }) => {
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
    getSelectEmployees (id) {
      if (this.status.value) {
        this.$q.loading.show()
        api.get(`/vacations/vacationRequestFilterTrue/${id}`).then(({ data }) => {
          this.data = data.vacationsRequestFilter
          this.$q.loading.hide()
        })
      } else {
        this.$q.loading.show()
        api.get(`/vacations/vacationRequestFilter/${id}`).then(({ data }) => {
          this.data = data.vacationsRequestFilter
          this.$q.loading.hide()
        })
      }
    }
  }
}
</script>

<style>
</style>

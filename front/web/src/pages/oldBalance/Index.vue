<template>
  <q-page class="bg-grey-3">
      <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-6">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Antigüedad de saldos" />
            </q-breadcrumbs>
          </div>
        </div>
        <div class="col-xs-12 col-md-6  pull-right">
          <div class="col-xs-12 col-md-4 offset-md-10">
            <div>
              <q-btn color="green" style="margin-left: 10px;" icon="fas fa-file-excel" @click="generateCSV()">
                <q-tooltip>GENERAR CSV</q-tooltip>
              </q-btn>
              <q-btn color="red" style="margin-left: 10px;" icon="fas fa-file-pdf" @click="generatePDF()">
                <q-tooltip>GENERAR PDF</q-tooltip>
              </q-btn>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="q-pa-md bg-grey-3">
        <div class="row bg-white border-panel">
            <div class="col q-pa-md">
                <div class="row q-col-gutter-xs">
                  <div class="col-md-4 q-pb-md">
          <q-select  color="dark" bg-color="secondary" filled
                    v-model="customer"
                    :options="filteredCustomerOptions"
                    use-input
                    hide-selected
                    fill-input
                    input-debounce="0"
                    @filter="filtrarClientes"
                    @input="fetchFromServer()"
                    label="Cliente"
                    emit-value map-options>
          </q-select>
        </div>
        <div class="col-sm-4">
              <!-- Quite este metodo filterGrid() -->
                  <q-select color="dark" bg-color="secondary" filled
                            v-model="branches"
                            :options="branchesOptions"
                            @input="fetchFromServer()"
                            use-input
                            label="Estación"
                            emit-value
                            map-options
                            >
                    <!-- <template v-slot:prepend>
                      <q-icon name="grade" />
                    </template> -->
                  </q-select>
                </div>
                <div class="col-xs-12 col-sm-12">
                    <q-table
                    flat
                    bordered
                    :data="dataoldbalance"
                    :columns="columns"
                    row-key="serial"
                    :pagination.sync="pagination"
                    :filter="filter"
                    @request="qTableRequest">
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
                                <q-td key="customer" style="text-align: left;" :props="props" v-if="props.row.customer !== false">{{ props.row.customer }}</q-td>
                                <q-td key="customer" style="text-align: right;" :props="props" v-else>TOTAL: </q-td>
                                <q-td key="totalbalance" style="text-align: right;" :props="props" v-if="props.row.customer !== false">{{ props.row.currentBalance }}</q-td>
                                <q-td key="totalbalance" style="text-align: right;" :props="props" v-else>{{ props.row.currentBalance }}</q-td>
                                <q-td key="thirty" style="text-align: right;" :props="props" v-if="props.row.customer !== false">{{ props.row.thirty }}</q-td>
                                <q-td key="thirty" style="text-align: right;" :props="props" v-else>{{ props.row.thirty }}</q-td>
                                <q-td key="sixty" style="text-align: right;" :props="props" v-if="props.row.customer !== false">{{ props.row.sixty }}</q-td>
                                <q-td key="sixty" style="text-align: right;" :props="props" v-else>{{ props.row.sixty }}</q-td>
                                <q-td key="ninety" style="text-align: right;" :props="props" v-if="props.row.customer !== false">{{ props.row.ninety }}</q-td>
                                <q-td key="ninety" style="text-align: right;" :props="props" v-else>{{ props.row.ninety }}</q-td>
                                <q-td key="overninety" style="text-align: right;" :props="props" v-if="props.row.customer !== false">{{ props.row.overninety }}</q-td>
                                <q-td key="overninety" style="text-align: right;" :props="props" v-else>{{ props.row.overninety }}</q-td>
                                <q-td key="pastduebalance" style="text-align: right;" :props="props" v-if="props.row.customer !== false">{{ props.row.pastduebalance }}</q-td>
                                <q-td key="pastduebalance" style="text-align: right;" :props="props" v-else>{{ props.row.pastduebalance }}</q-td>
                                <q-td key="total" style="text-align: right;" :props="props" v-if="props.row.customer !== false">{{ props.row.sumAll }}</q-td>
                                <q-td key="total" style="text-align: right;" :props="props" v-else>{{ props.row.sumAll }}</q-td>
                            </q-tr>
                        </template>
                    </q-table>
                    </div>
                        </div>
            </div>
        </div>
    </div>
  </q-page>
</template>
<script>
import api from '../../commons/api.js'
export default {
  name: 'CrmIndex',
  data () {
    return {
      branchesOptions: [],
      branches: 'TODOS',
      filteredCustomerOptions: [],
      customerOptions: [],
      customer: 'TODOS',
      dataoldbalance: [],
      columns: [
        { name: 'customer', align: 'center', label: 'CLIENTE', field: 'customer', sortable: false },
        { name: 'totalbalance', align: 'center', label: 'CORRIENTE', field: 'totalbalance', sortable: false, headerClasses: 'bg-green' },
        { name: 'thirty', align: 'center', label: '0 - 30', field: 'thirty', sortable: false, headerClasses: 'bg-amber', style: 'width: 10%' },
        { name: 'sixty', align: 'center', label: '31 - 60', field: 'sixty', sortable: false, headerClasses: 'bg-orange', style: 'width: 10%' },
        { name: 'ninety', align: 'center', label: '61 - 90', field: 'ninety', sortable: false, headerClasses: 'bg-red', style: 'width: 10%' },
        { name: 'overninety', align: 'center', label: '90+', field: 'overninety', sortable: false, headerClasses: 'bg-purple', style: 'width: 10%' },
        { name: 'pastduebalance', align: 'center', label: 'VENCIDO', field: 'pastduebalance', sortable: false, headerClasses: 'bg-brown' },
        { name: 'total', align: 'center', label: 'TOTAL', field: 'total', sortable: false, headerClasses: 'bg-dark' }
        // { name: 'actions', align: 'center', label: 'ACCIONES', field: 'actions', sortable: true }
      ],
      filter: '',
      /* pagination: {
        sortBy: 'serial',
        descending: false,
        page: 1,
        rowsNumber: 0,
        rowsPerPage: 25
      }, */
      /* pagination: {
        page: 1,
        rowsPerPage: 25
      } */
      pagination: {
        sortBy: 'serial',
        descending: false,
        page: 1,
        rowsNumber: 0,
        rowsPerPage: 25
      }
    }
  },
  mounted () {
    this.fetchFromServer()
    this.getClients()
    this.getBranchsOffices()
  },
  methods: {
    getBranchsOffices () {
      api.get('/branch-offices/getBranchsOffices').then(({ data }) => {
        this.branchesOptions = data.branchs
        this.branchesOptions.unshift({ label: 'TODOS', value: 'TODOS' })
      })
    },
    generateCSV () {
      const customer = this.customer
      const status = [2, 3]
      const branches = this.branches
      const filter = this.filter === null || this.filter === '' ? 'TODOS' : this.filter
      const uri = process.env.API + `oldbalance/getCSV/${customer}/${status}/${branches}/${filter}`
      window.open(uri, '_blank')
    },
    generatePDF () {
      const customer = this.customer
      const status = [2, 3]
      const branches = this.branches
      const filter = this.filter === null || this.filter === '' ? 'TODOS' : this.filter
      const uri = process.env.API + `oldbalance/getPdf/${customer}/${status}/${branches}/${filter}`
      window.open(uri, '_blank')
    },
    getClients () {
      api.get('/oldbalance/getClients').then(({ data }) => {
        this.customerOptions = data.options
        this.customerOptions.push({ label: 'TODOS', value: 'TODOS' })
      })
    },
    filtrarClientes (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.filteredCustomerOptions = this.customerOptions.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    async fetchFromServer () {
      this.qTableRequest({
        pagination: this.pagination,
        filter: this.filter
      })
      /* await api.get('/oldbalance/getOldbalance').then(({ data }) => {
        if (data.result) {
          this.dataoldbalance = data.oldbalance
        }
      }) */
    },
    async qTableRequest (props) {
      this.$q.loading.show()
      // this.pagination = props.pagination
      this.filter = props.filter
      this.pagination = props.pagination
      this.data = []
      const params = []
      params.customer = this.customer
      params.status = [2, 3]
      params.saleDatev1 = null
      params.saleDatev2 = null
      params.type = 0
      params.pagination = this.pagination
      params.filter = this.filter
      params.branches = this.branches
      await api.post('/oldbalance/getOldbalance', params).then(({ data }) => {
        console.log(data)
        this.dataoldbalance = data.oldbalance
        this.pagination.rowsNumber = data.oldbalanceCount
        // this.pagination.rowsNumber = data.invoicesCount
      }).catch(error => error)
      this.$q.loading.hide()
    },
    editSelectedRow (idFunnel) {
      const id = idFunnel
      this.$router.push(`/oldbalance/${id}`)
    },
    deleteSelectedRow (id) {
      this.$q.dialog({
        title: 'Confirmación',
        message: '¿Desea eliminar este oldbalance?',
        persistent: true,
        ok: {
          label: 'Aceptar',
          color: 'green'
        },
        cancel: {
          label: 'Cancelar',
          color: 'red'
        }
      }).onOk(async () => {
        this.$q.loading.show()
        await api.delete(`/oldbalance/${id}`).then(({ data }) => {
          this.$q.notify({
            message: data.message.content,
            position: 'top',
            color: (data.result ? 'positive' : 'warning')
          })
          if (data.result) {
            this.fetchFromServer()
            this.$q.loading.hide()
          }
        })
      }).onCancel(() => {})
      this.$q.loading.hide()
    }
  }
}
</script>

<style>
.pintar-columna{
 position: -webkit-sticky;
   position: sticky;
   top: 0px;
   background: rgb(5, 221, 5)!important;
   z-index: 1;
   opacity: 1 !important;
 font-size: 0.95em;
}
.my-color th{
  position: -webkit-sticky;
   position: sticky;
   top: 0px;
   background: red!important;
   z-index: 1;
   opacity: 1 !important;
 font-size: 0.95em;
}
</style>

<template>
  <q-page class="bg-grey-3">
      <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-6">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Compras de proveedor" />
            </q-breadcrumbs>
          </div>
        </div>
        <div class="col-xs-12 col-md-6  pull-right">
          <div class="q-pa-sm q-gutter-sm">
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
    <div class="q-pa-md bg-grey-3">
        <div class="row bg-white border-panel">
            <div class="col q-pa-md">
                <div class="row bg-white q-pb-md q-col-gutter-xs q-col-gutter-md">
                  <div class="col-sm-2">
          <q-select
            color="dark"
            bg-color="secondary"
            filled
            v-model="saleDatev1"
            mask="dd/mm/Y"
            label="Desde"
          >
            <q-popup-proxy
              ref="date"
              transition-show="scale"
              transition-hide="scale"
            >
              <div class="col-sm-12">
                <q-date
                  v-model="saleDatev1"
                  @input="fetchFromServer()"
                  today-btn
                />
              </div>
            </q-popup-proxy>
          </q-select>
          </div>
          <div class="col-md-2">
          <q-select
            color="dark"
            bg-color="secondary"
            filled
            v-model="saleDatev2"
            mask="date"
            label="Hasta"
          >
            <q-popup-proxy
              ref="date"
              transition-show="scale"
              transition-hide="scale"
            >
              <div class="col-sm-12">
                <q-date
                  v-model="saleDatev2"
                  @input="fetchFromServer()"
                  today-btn
                />
              </div>
            </q-popup-proxy>
          </q-select>
        </div>
        <div class="col-md-3">
          <q-select  color="dark" bg-color="secondary" filled
                    v-model="officeBranch"
                    :options="filtrarOfficeBranch"
                    use-input
                    hide-selected
                    fill-input
                    input-debounce="0"
                    @filter="filteredOfficeBranch"
                    @input="fetchFromServer()"
                    label="Sucursal"
                    emit-value map-options>
          </q-select>
        </div>
        <div class="col-md-3">
          <q-select  color="dark" bg-color="secondary" filled
                    v-model="supplier"
                    :options="filteredSupplierOptions"
                    use-input
                    hide-selected
                    fill-input
                    input-debounce="0"
                    @filter="filtrarSupplier"
                    @input="fetchFromServer()"
                    label="Proveedor"
                    emit-value map-options>
          </q-select>
        </div>
               </div>
                <div class="row q-col-gutter-xs">
                <div class="col-xs-12 col-sm-12">
                    <q-table
                    flat
                    bordered
                    :data="datashoppingsuppliers"
                    :columns="columns"
                    row-key="serial"
                    :pagination.sync="pagination"
                    :filter="filter">
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
                                <q-td key="supplier" style="text-align: left;" :props="props">{{ props.row.supplier }}</q-td>
                                <q-td key="total" style="text-align: right;" :props="props">{{ props.row.total }}</q-td>
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
      saleDatev1: null,
      saleDatev2: null,
      supplier: 'TODOS',
      filteredSupplierOptions: [],
      supplierOptions: [],
      datashoppingsuppliers: [],
      columns: [
        { name: 'supplier', align: 'center', label: 'PROVEEDOR', field: 'supplier', sortable: false },
        { name: 'total', align: 'center', label: 'TOTAL', field: 'total', sortable: false }
      ],
      filter: '',
      pagination: {
        page: 1,
        rowsPerPage: 25
      },
      filtrarOfficeBranch: [],
      filteredOfficeBranchOptions: [],
      officeBranch: 'TODOS'
    }
  },
  mounted () {
    this.getSuppliers()
    this.getBranchOfficesToReportShopping()
    this.fetchFromServer()
  },
  methods: {
    generateCSV () {
      const dataini = this.saleDatev1 !== null ? this.$formatDate(this.saleDatev1) : null
      const datafin = this.saleDatev2 !== null ? this.$formatDate(this.saleDatev2) : null
      const supplier = this.supplier
      const branch = this.officeBranch
      const uri = process.env.API + `purchase-order-details/getReportShoppingToCSVShoppingSupplier/${supplier}/${dataini}/${datafin}/${branch}`
      window.open(uri, '_blank')
    },
    generatePDF () {
      const dataini = this.saleDatev1 !== null ? this.$formatDate(this.saleDatev1) : null
      const datafin = this.saleDatev2 !== null ? this.$formatDate(this.saleDatev2) : null
      const supplier = this.supplier
      const branch = this.officeBranch
      const uri = process.env.API + `purchase-order-details/shoppingOfSuppliersPDF/${supplier}/${dataini}/${datafin}/${branch}`
      window.open(uri, '_blank')
    },
    async getSuppliers () {
      await api.get('/suppliers/getSuppliersToReportSales').then(({ data }) => {
        if (data.result) {
          this.supplierOptions = data.suppliers
          this.supplierOptions.unshift({ label: 'TODOS', value: 'TODOS' })
        }
      })
    },
    async getBranchOfficesToReportShopping () {
      await api.get('/branch-offices/getBranchOfficesToReportShopping').then(({ data }) => {
        if (data.result) {
          this.filteredOfficeBranchOptions = data.branch
          this.filteredOfficeBranchOptions.unshift({ label: 'TODOS', value: 'TODOS' })
        }
      })
    },
    filteredOfficeBranch (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.filtrarOfficeBranch = this.filteredOfficeBranchOptions.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    filtrarSupplier (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.filteredSupplierOptions = this.supplierOptions.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    async fetchFromServer () {
      this.qTableRequest({
        pagination: this.pagination,
        filter: this.filter
      })
    },
    async qTableRequest (props) {
      this.$q.loading.show()
      const params = []
      params.dataini = this.saleDatev1
      params.saleDatev2 = this.saleDatev2
      params.pagination = props.pagination
      params.filter = props.filter
      params.supplier = this.supplier
      params.branch = this.officeBranch
      await api.post('/purchase-order-details/shoppingOfSuppliers', params).then(({ data }) => {
        if (data.result) {
          this.datashoppingsuppliers = data.shopping
        }
      })
      this.$q.loading.hide()
    }
  }
}
</script>
<style>
</style>

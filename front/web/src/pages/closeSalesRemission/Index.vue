<template>
  <q-page class="bg-grey-3">
      <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-6">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Corte de caja remisión" />
            </q-breadcrumbs>
          </div>
        </div>
        <div class="col-xs-12 col-md-6 q-pr-sm pull-right">
            <q-btn color="positive" icon="fas fa-file-excel" @click="generateCsv()"><q-tooltip content-class="bg-primary">Descargar CSV</q-tooltip></q-btn>
            <q-btn color="red" style="margin-left: 10px;" icon="fas fa-file-pdf" @click="closeSales()">
              <q-tooltip>CORTE DE CAJA</q-tooltip>
            </q-btn>
        </div>
      </div>
    </div>
    <div class="q-pa-md bg-grey-3">
        <div class="row bg-white border-panel">
            <div class="col q-pa-md">
                <div class="row q-col-gutter-xs">
                  <div class="col-md-3">
          <q-select
            color="dark"
            bg-color="secondary"
            filled
            v-model="saleDatev1"
            mask="YYYY-DD-MM"
            label="Buscar por fecha"
          >
            <q-popup-proxy
              ref="date"
              transition-show="scale"
              transition-hide="scale"
            >
              <div class="col-sm-12">
                <q-date
                mask="DD/MM/YYYY"
                  v-model="saleDatev1"
                  @input="filterGrid()"
                  today-btn
                />
              </div>
            </q-popup-proxy>
          </q-select>
        </div>
                <div class="col-xs-12 col-sm-12">
                    <q-table
                    flat
                    bordered
                    :data="closeSale"
                    :columns="columns"
                    row-key="serial"
                    :pagination.sync="pagination"
                    :filter="filter">
                        <!-- <template v-slot:top>
                            <div style="width: 100%;">
                                <q-input dense debounce="300" v-model="filter" placeholder="Buscar" @input="v => { filter = v.toUpperCase() }">
                                <template v-slot:append>
                                    <q-icon name="search" />
                                </template>
                                </q-input>
                            </div>
                        </template> -->
                        <template v-slot:body="props">
                            <q-tr :props="props">
                                <q-td key="name" style="text-align: left;" :props="props">{{ props.row.name }}</q-td>
                                <q-td key="id" style="text-align: center;" :props="props" v-if="props.row.name !== ''">{{ props.row.id }}</q-td>
                                <q-td key="id" style="text-align: center;" :props="props" v-else>{{ props.row.total }}</q-td>
                                <q-td key="subtotal" style="text-align: right;" :props="props" v-if="props.row.name !== ''">$ {{ $formatNumberPrice(props.row.subtotal) }}</q-td>
                                <q-td key="subtotal" style="text-align: right;" :props="props" v-else>$ {{ $formatNumberPrice(props.row.subtotal) }}</q-td>
                                <q-td key="iva" style="text-align: right;" :props="props" v-if="props.row.name !== ''">$ {{ $formatNumberPrice(props.row.iva) }}</q-td>
                                <q-td key="iva" style="text-align: right;" :props="props" v-else>$ {{ $formatNumberPrice(props.row.iva) }}</q-td>
                                <q-td key="total" style="text-align: right;" :props="props" v-if="props.row.name !== ''">$ {{ $formatNumberPrice(props.row.total) }}</q-td>
                                <q-td key="total" style="text-align: right;" :props="props" v-else>$ {{ $formatNumberPrice(props.row.neto) }}</q-td>
                                <q-td key="remission" style="text-align: right;" :props="props" v-if="props.row.name !== ''">$ {{ $formatNumberPrice(props.row.remission) }}</q-td>
                                <q-td key="remission" style="text-align: right;" :props="props" v-else>$ {{ $formatNumberPrice(props.row.countremission) }}</q-td>
                                <q-td key="credit" style="text-align: right;" :props="props" v-if="props.row.name !== ''">$ {{ props.row.payment_method === 'CREDITO' ?  $formatNumberPrice(props.row.total) : $formatNumberPrice(0) }}</q-td>
                                <q-td key="credit" style="text-align: right;" :props="props" v-else>$ {{ $formatNumberPrice(props.row.credit) }}</q-td>
                                <q-td key="counted" style="text-align: right;" :props="props" v-if="props.row.name !== ''">$ {{ props.row.payment_method === 'CONTADO' ?  $formatNumberPrice(props.row.total) : $formatNumberPrice(0) }}</q-td>
                                <q-td key="counted" style="text-align: right;" :props="props" v-else>$ {{ $formatNumberPrice(props.row.counted) }}</q-td>
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
      closeSale: [],
      columns: [
        { name: 'name', align: 'center', label: 'NOMBRE DEL CLIENTE', field: 'name', sortable: false },
        { name: 'id', align: 'center', label: 'NO. REMISIÓN', field: 'id', sortable: false },
        { name: 'subtotal', align: 'center', label: 'SUBTOTAL', field: 'subtotal', sortable: false },
        { name: 'iva', align: 'center', label: 'IVA', field: 'iva', sortable: false },
        { name: 'total', align: 'center', label: 'NETO', field: 'total', sortable: false },
        { name: 'remission', align: 'center', label: 'TICKET', field: 'remission', sortable: false },
        { name: 'credit', align: 'center', label: 'CRÉDITO', field: 'credit', sortable: false },
        { name: 'counted', align: 'center', label: 'CONTADO', field: 'counted', sortable: false }
      ],
      filter: '',
      pagination: {
        page: 1,
        rowsPerPage: 25
      }
    }
  },
  computed: {
    getDate () {
      var today = new Date()
      var dd = today.getDate()
      var mm = today.getMonth() + 1
      var yyyy = today.getFullYear()
      if (dd < 10) {
        dd = '0' + dd
      }
      if (mm < 10) {
        mm = '0' + mm
      }
      today = yyyy + '-' + mm + '-' + dd
      return today
    }
  },
  created () {
    this.fetchFromServer()
  },
  methods: {
    filterGrid () {
      console.log(this.saleDatev1)
      const date = this.$formatDate(this.saleDatev1)
      this.$q.loading.show()
      api.get(`/close-sale/getCloseSalesRemission/${date}`).then(({ data }) => {
        if (data.result) {
          this.closeSale = data.closeSale
          console.log(this.closeSale)
        }
      })
      this.$q.loading.hide()
    },
    generateCsv () {
      const remission = 'si'
      const uri = process.env.API + `close-sale/getCsvCloseSales/${this.saleDatev1 === null ? this.getDate : this.$formatDate(this.saleDatev1)}/${remission}`
      this.$q.loading.show()
      api.fileDownload(uri).then(({ data }) => {
        const url = window.URL.createObjectURL(new Blob([data], { type: 'application/csv' }))
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', 'Cierre' + (this.saleDatev1 === null ? this.getDate : this.$formatDate(this.saleDatev1)) + '.csv')
        document.body.appendChild(link)
        this.$q.loading.hide()
        link.click()
      })
    },
    closeSales () {
      const remission = 'si'
      const uri = process.env.API + `close-sale/closeSales/${this.saleDatev1 === null ? this.getDate : this.$formatDate(this.saleDatev1)}/${remission}`
      this.$q.loading.show()
      api.fileDownload(uri).then(({ data }) => {
        const url = window.URL.createObjectURL(new Blob([data], { type: 'application/pdf' }))
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', 'Cierre' + (this.saleDatev1 === null ? this.getDate : this.$formatDate(this.saleDatev1)) + '.pdf')
        document.body.appendChild(link)
        this.$q.loading.hide()
        link.click()
      })
    },
    fetchFromServer () {
      console.log(this.getDate)
      this.$q.loading.show()
      api.get(`/close-sale/getCloseSalesRemission/${this.getDate}`).then(({ data }) => {
        if (data.result) {
          this.closeSale = data.closeSale
          console.log(this.closeSale)
        }
      })
      this.$q.loading.hide()
    },
    editSelectedRow (idFunnel) {
      const id = idFunnel
      this.$router.push(`/corte de caja/${id}`)
    },
    deleteSelectedRow (id) {
      this.$q.dialog({
        title: 'Confirmación',
        message: '¿Desea eliminar este Corte de caja?',
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
        await api.delete(`/corte de caja/${id}`).then(({ data }) => {
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
</style>

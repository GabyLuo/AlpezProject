<template>
  <q-page>
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-8">
          <span class="q-ml-md grey-8 fs28 page-title">Recepciones PET</span>
        </div>
        <div class="col-sm-4 pull-right">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="right">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Recepciones PET" />
            </q-breadcrumbs>
          </div>
        </div>
      </div>
    </div>

    <div class="q-pa-md bg-grey-3">
      <div class="row bg-white border-panel">
        <div class="col q-pa-md">
          <q-table
            flat
            bordered
            :data="data"
            :columns="columns"
            row-key="serial"
            :pagination.sync="pagination"
          >
            <template v-slot:body="props">
              <q-tr :props="props">
                <q-td key="purchase_order_serial" style="text-align: right; width: 10%;" :props="props">{{ props.row.purchase_order_serial }}</q-td>
                <q-td key="serial" style="text-align: right; width: 10%;" :props="props">{{ props.row.serial }}</q-td>
                <q-td key="receive_date" style="width: 10%;" :props="props">{{ props.row.receive_date }}</q-td>
                <q-td key="total_weight" style="text-align: right; width: 8%;" :props="props">{{ `${formatter.format(props.row.total_weight)} KG.` }}</q-td>
                <q-td key="real_weight" style="text-align: right; width: 8%;" :props="props">{{ `${formatter.format(props.row.real_weight)} KG.` }}</q-td>
                <q-td key="supplier" style="text-align: left; width: 39%;" :props="props">{{ props.row.supplier ? props.row.supplier : 0 }}</q-td>
                <q-td key="status" style="width: 10%;" :props="props">
                  <q-chip square dense :color="props.row.status == 'NUEVO' ? 'secondary' : (props.row.status == 'RECIBIDO' ? 'orange' : (props.row.status == 'ANALIZADO' ? 'positive' : (props.row.status == 'RECHAZADO' ? 'negative' : 'blue')))" text-color="white">
                    {{ props.row.status }}
                  </q-chip>
                </q-td>
                <q-td key="actions" style="width: 5%;" :props="props">
                  <q-btn color="primary" icon="fas fa-edit" flat @click.native="editShipment(props.row)" size="10px">
                    <q-tooltip content-class="bg-primary">Editar</q-tooltip>
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
  name: 'IndexAnalyzedShipments',
  data () {
    return {
      formatter: new Intl.NumberFormat('en-US'),
      pagination: {
        rowsPerPage: 50
      },
      columns: [
        { name: 'purchase_order_serial', align: 'center', label: 'Folio OC', field: 'purchase_order_serial', style: 'width: 10%', sortable: true },
        { name: 'serial', align: 'center', label: 'Folio', field: 'serial', style: 'width: 10%', sortable: true },
        { name: 'receive_date', align: 'center', label: 'Fecha de recepción', field: 'receive_date', style: 'width: 10%', sortable: true },
        { name: 'total_weight', align: 'center', label: 'Peso báscula proveedor', field: 'total_weight', style: 'width: 8%', sortable: true },
        { name: 'real_weight', align: 'center', label: 'Peso real', field: 'real_weight', style: 'width: 8%', sortable: true },
        { name: 'supplier', align: 'center', label: 'Proveedor', field: 'supplier', style: 'width: 39%', sortable: true },
        { name: 'status', align: 'center', label: 'Estatus', field: 'status', style: 'width: 10%', sortable: true },
        { name: 'actions', align: 'center', label: 'Acciones', field: 'actions', style: 'width: 5%', sortable: false }
      ],
      data: []
    }
  },
  beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(2) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7)) {
      this.$router.push('/')
    }
  },
  mounted () {
    this.fetchFromServer()
  },
  methods: {
    fetchFromServer () {
      this.$q.loading.show()
      api.get('/shipments/analyzed').then(({ data }) => {
        this.data = data.shipments
        this.$q.loading.hide()
      })
    },
    editShipment (shipmentElement) {
      this.$router.push(`/shipments/${shipmentElement.id}`)
    }
  }
}
</script>

<style>
</style>

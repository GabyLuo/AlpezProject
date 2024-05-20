<template>
  <q-page>
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-8">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Traspasos Sucursales" />
            </q-breadcrumbs>
          </div>
        </div>
      </div>
    </div>

    <div class="q-pa-md bg-grey-3">
      <div class="row bg-white border-panel">
        <div class="col q-pa-md">
          <div class="row q-mb-sm">
            <div class="col-xs-12 pull-right">
              <q-btn color="primary" icon="add" label="Nuevo" @click.native="$router.push('/branch-transfers/new')" />
            </div>
          </div>

          <q-table
            flat
            bordered
            :data="data"
            :columns="columns"
            row-key="id"
            :pagination.sync="pagination"
          >
            <template v-slot:body="props">
              <q-tr :props="props">
                <q-td key="id" style="text-align: right; width: 5%;" :props="props">{{ props.row.id }}</q-td>
                <q-td key="origin_storage_name" style="text-align: left; width: 30%;" :props="props">{{ props.row.origin_storage_name }}</q-td>
                <q-td key="destination_storage_name" style="text-align: left; width: 30%;" :props="props">{{ props.row.destination_storage_name }}</q-td>
                <!-- <q-td key="operator_name" style="text-align: left; width: 10%;" :props="props">{{ props.row.operator_name }}</q-td> -->
                <q-td key="date" style="width: 10%;" :props="props">{{ props.row.date }}</q-td>
                <q-td key="status" style="width: 10%;" :props="props">
                  <q-chip square dense :color="props.row.status == 0 ? 'primary' : (props.row.status == 1 ? 'positive' : 'accent')" text-color="white">
                    {{ Number(props.row.status) == 0 ? 'NO EJECUTADO' : 'EJECUTADO' }}
                  </q-chip>
                </q-td>
                <q-td key="actions" style="width: 5%;" :props="props">
                  <q-btn color="primary" icon="fas fa-file-pdf" flat @click.native="openBranchTransferPdf(props.row.id)" size="10px" v-if="props.row.status == 1">
                    <q-tooltip content-class="bg-primary">Ver reporte</q-tooltip>
                  </q-btn>
                  <q-btn color="primary" icon="fas fa-edit" flat @click.native="editSelectedRow(props.row.id)" size="10px">
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
  name: 'IndeBranchTransfers',
  data () {
    return {
      pagination: {
        sortBy: 'id',
        descending: true,
        rowsPerPage: 50
      },
      columns: [
        { name: 'id', align: 'center', label: 'No. Traspaso', field: 'id', style: 'width: 5%', sortable: true, sort: (a, b) => Number(a, 10) - Number(b, 10) },
        { name: 'origin_storage_name', align: 'center', label: 'Almacén de origen', field: 'origin_storage_name', style: 'width: 30%', sortable: true },
        { name: 'destination_storage_name', align: 'center', label: 'Almacén de destino', field: 'destination_storage_name', style: 'width: 30%', sortable: true },
        // { name: 'operator_name', align: 'center', label: 'Operador', field: 'operator_name', style: 'width: 10%', sortable: true },
        { name: 'date', align: 'center', label: 'Fecha', field: 'date', style: 'width: 10%', sortable: true },
        { name: 'status', align: 'center', label: 'Estatus', field: 'status', style: 'width: 10%', sortable: true },
        { name: 'actions', align: 'center', label: 'Acciones', field: 'actions', style: 'width: 5%', sortable: false }
      ],
      data: []
    }
  },
  beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(2) && !this.$store.getters['users/roles'].includes(10)) {
      this.$router.push('/')
    }
  },
  mounted () {
    this.fetchFromServer()
  },
  methods: {
    fetchFromServer () {
      this.$q.loading.show()
      api.get('/branch-transfers').then(({ data }) => {
        this.data = data.branchTransfers
        this.$q.loading.hide()
      })
    },
    openBranchTransferPdf (id) {
      const uri = process.env.API + `branch-transfers/pdf/${id}`
      window.open(uri, '_blank')
    },
    editSelectedRow (id) {
      this.$router.push(`/branch-transfers/${id}`)
    }
  }
}
</script>

<style>
</style>

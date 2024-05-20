<template>
  <q-page>
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-8">
          <span class="q-ml-md grey-8 fs28 page-title">Apertura de pacas</span>
        </div>
        <div class="col-sm-4 pull-right">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="right">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Apertura de pacas" />
            </q-breadcrumbs>
          </div>
        </div>
      </div>
    </div>

    <div class="q-pa-md bg-grey-3">
      <div class="row bg-white border-panel">
        <div class="col q-pa-md">
          <div class="row q-mb-sm">
            <div class="col-xs-12 col-md-2 offset-md-10 pull-right">
              <q-btn color="primary" icon="add" label="Nuevo" @click.native="$router.push('/bale-opening/new')" />
            </div>
          </div>

          <q-table
            flat
            bordered
            :data="data"
            :columns="columns"
            row-key="code"
            :pagination.sync="pagination"
          >
            <template v-slot:body="props">
              <q-tr :props="props">
                <q-td key="code" style="width: 10%;" :props="props">{{ props.row.id }}</q-td>
                <q-td key="date" style="width: 10%;" :props="props">{{ props.row.date }}</q-td>
                <q-td key="branch_office" style="text-align: left; width: 30%;" :props="props">{{ props.row.branch_office_name }}</q-td>
                <q-td key="operator_name" style="text-align: left; width: 30%;" :props="props">{{ props.row.operator_name }}</q-td>
                <q-td key="kgs" style="text-align: center;" :props="props">{{ props.row.qty }}</q-td>
                <q-td key="status" style="width: 10%;" :props="props">
                  <q-chip square dense :color="props.row.status == 'NUEVO' ? 'primary' : (props.row.status == 'EJECUTADO' ? 'positive' : 'gray')" text-color="white">
                    {{ props.row.status }}
                  </q-chip>
                </q-td>
                <q-td key="actions" style="width: 10%;" :props="props">
                  <q-btn color="primary" icon="fas fa-edit" flat @click.native="editSelectedRow(props.row.id)" size="10px">
                    <q-tooltip content-class="bg-primary">Editar</q-tooltip>
                  </q-btn>
                  <q-btn color="primary" icon="fas fa-trash-alt" flat @click.native="deleteSelectedRow(props.row.id)" size="10px" v-if="props.row.status == 'NUEVO'">
                    <q-tooltip content-class="bg-red">Eliminar</q-tooltip>
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
  name: 'IndexBaleOpening',
  data () {
    return {
      pagination: {
        sortBy: 'date',
        descending: true,
        rowsPerPage: 50
      },
      columns: [
        { name: 'code', align: 'center', label: 'Código', field: 'code', style: 'width: 10%', sortable: true },
        { name: 'date', align: 'center', label: 'Fecha', field: 'date', style: 'width: 10%', sortable: true },
        { name: 'branch_office', align: 'center', label: 'Sucursal', field: 'branch_office', style: 'width: 30%', sortable: true },
        { name: 'operator_name', align: 'center', label: 'Operador', field: 'operator_name', style: 'width: 30%', sortable: true },
        { name: 'kgs', align: 'center', label: 'Kgs', field: 'kgs', sortable: true },
        { name: 'status', align: 'center', label: 'Estatus', field: 'status', style: 'width: 10%', sortable: true },
        { name: 'actions', align: 'center', label: 'Acciones', field: 'actions', style: 'width: 10%', sortable: false }
      ],
      data: []
    }
  },
  beforeCreate () {
    if (!(this.$store.getters['users/roles'].includes(1) || this.$store.getters['users/roles'].includes(3) || this.$store.getters['users/roles'].includes(7) || this.$store.getters['users/roles'].includes(2) || this.$store.getters['users/roles'].includes(3) || this.$store.getters['users/roles'].includes(4) || this.$store.getters['users/roles'].includes(5) || this.$store.getters['users/roles'].includes(13))) {
      this.$router.push('/')
    }
  },
  mounted () {
    this.fetchFromServer()
  },
  methods: {
    fetchFromServer () {
      this.$q.loading.show()
      api.get('/bale-openings').then(({ data }) => {
        this.data = data.baleOpenings
        this.$q.loading.hide()
      })
    },
    editSelectedRow (id) {
      this.$router.push(`/bale-opening/${id}`)
    },
    deleteSelectedRow (id) {
      this.$q.loading.show()
      api.delete(`/bale-openings/${id}`).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.fetchFromServer()
        }
      })
    }
  }
}
</script>

<style>
</style>

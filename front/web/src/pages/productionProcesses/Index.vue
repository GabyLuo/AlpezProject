<template>
  <q-page>
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-8">
          <span class="q-ml-md grey-8 fs28 page-title">Procesos</span>
        </div>
        <div class="col-sm-4 pull-right">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="right">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Procesos" />
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
              <q-btn color="primary" icon="add" label="Nuevo" @click.native="$router.push('/production-processes/new')" />
            </div>
          </div>

          <q-table
            flat
            bordered
            :data="data"
            :columns="columns"
            :pagination.sync="pagination"
          >
            <template v-slot:body="props">
              <q-tr :props="props">
                <q-td key="name" style="text-align: left; padding-left: 40%; width: 85%;" :props="props">{{ props.row.name }}</q-td>
                <q-td key="actions" style="width: 15%;" :props="props">
                  <q-btn color="primary" icon="fas fa-edit" flat @click.native="editSelectedRow(props.row.id)" size="10px">
                    <q-tooltip content-class="bg-primary">Editar</q-tooltip>
                  </q-btn>
                  <q-btn color="primary" icon="fas fa-trash-alt" flat @click.native="deleteSelectedRow(props.row.id)" size="10px">
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
  name: 'IndexProductionProcesses',
  data () {
    return {
      pagination: {
        descending: false,
        rowsPerPage: 50
      },
      columns: [
        { name: 'name', align: 'center', label: 'Nombre', field: 'name', style: 'width: 85%', sortable: true },
        { name: 'actions', align: 'center', label: 'Acciones', field: 'actions', style: 'width: 15%', sortable: false }
      ],
      data: []
    }
  },
  beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1)) {
      this.$router.push('/')
    }
  },
  mounted () {
    this.fetchFromServer()
  },
  methods: {
    fetchFromServer () {
      this.$q.loading.show()
      api.get('/production-processes').then(({ data }) => {
        this.data = data.productionProcesses
        this.$q.loading.hide()
      })
    },
    editSelectedRow (id) {
      this.$router.push(`/production-processes/${id}`)
    },
    deleteSelectedRow (id) {
      this.$q.loading.show()
      api.delete(`/production-processes/${id}`).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.fetchFromServer()
        } else {
          this.$q.loading.hide()
        }
      })
    }
  }
}
</script>

<style>
</style>

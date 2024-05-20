<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-4">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Puestos" />
            </q-breadcrumbs>
          </div>
        </div>
        <div class="col-xs-8 col-md-8 pull-right">
          <div class="q-pa-sm q-gutter-sm">
            <q-btn class="bg-primary" style="color: white" icon="add" label="Nuevo" @click.native="$router.push('/positions/new')" />
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
            :data="positions"
            :columns="columns"
            :pagination.sync="pagination"
            :filter="filter"
            @request="qTableRequest"
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
                <q-td key="departments" style="text-align: left;" :props="props">{{ props.row.departments }}</q-td>
                <q-td key="areas" style="text-align: left;" :props="props">{{ props.row.areas }}</q-td>
                <q-td key="name" style="text-align: left;" :props="props">{{ props.row.name }}</q-td>
                <q-td key="actions" :props="props">
                  <q-btn color="primary" icon="fas fa-edit" flat @click.native="editSelectedRow(props.row.id)" size="10px">
                    <q-tooltip content-class="bg-primary">Editar</q-tooltip>
                  </q-btn>
                  <q-btn color="negative" icon="fas fa-trash-alt" flat @click.native="deleteSelectedRow(props.row.id)" size="10px">
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
  name: 'IndexOisitions',
  data () {
    return {
      pagination: {
        sortBy: 'code',
        descending: true,
        page: 1,
        rowsNumber: 0,
        rowsPerPage: 25
      },
      columns: [
        { name: 'departments', align: 'center', label: 'DEPARTAMENTOS', field: 'departments', style: 'width: 15%', sortable: true },
        { name: 'areas', align: 'center', label: 'ÁREAS', field: 'areas', style: 'width: 10%', sortable: true },
        { name: 'name', align: 'center', label: 'NOMBRE', field: 'name', style: 'width: 15%', sortable: true },
        { name: 'actions', align: 'center', label: 'ACCIONES', field: 'actions', style: 'width: 10%', sortable: false }
      ],
      positions: [],
      filter: '',
      serverUrl: process.env.API
    }
  },
  beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(4)) {
      this.$router.push('/')
    }
  },
  mounted () {
    this.fetchFromServer()
  },
  methods: {
    PadLeft (value, length) {
      return (value.toString().length < length) ? this.PadLeft('0' + value, length) : value
    },
    fetchFromServer () {
      this.$q.loading.show()
      this.qTableRequest({
        pagination: this.pagination,
        filter: this.filter
      })
    },
    async qTableRequest (props) {
      this.pagination = props.pagination
      this.filter = props.filter
      this.positions = []
      const params = []
      params.pagination = this.pagination
      params.filter = this.filter
      await api.post('/positions/pag', params).then(({ data }) => {
        this.$q.loading.hide()
        this.positions = data.positions
        this.pagination.rowsNumber = data.positionsCount
      }).catch(error => error)
    },
    editSelectedRow (id) {
      this.$router.push(`/positions/${id}`)
    },
    deleteSelectedRow (id) {
      this.$q.dialog({
        title: 'Confirmación',
        message: '¿Desea eliminar este Puesto?',
        persistent: true,
        ok: { label: 'Aceptar', color: 'positive' },
        cancel: { label: 'Cancelar', color: 'negative' }
      }).onOk(() => {
        api.delete(`/positions/${id}`).then(({ data }) => {
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
    }
  },
  computed: {
  }
}
</script>

<style>
</style>

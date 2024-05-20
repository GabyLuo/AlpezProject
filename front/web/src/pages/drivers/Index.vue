<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-4">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Choferes" />
            </q-breadcrumbs>
          </div>
        </div>
          <div class="col-xs-8 col-md-8 pull-right">
          <div class="q-pa-sm q-gutter-sm">
              <q-btn color="primary" icon="add" label="Nuevo" @click.native="$router.push('/drivers/new')" />
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
                <q-td key="name" style="width: 50%;" :props="props" class="pull-left">{{ props.row.name }}</q-td>
                <q-td key="rfc" style="width: 20%;" :props="props" class="pull-left">{{ props.row.rfc }}</q-td>
                <q-td key="license" style="width: 20%;" :props="props" class="pull-left">{{ props.row.license }}</q-td>
                <q-td key="active" style="width: 20%;" :props="props" class="pull-left">
                  <q-chip square dense :color="props.row.active ? 'positive' : 'negative'" text-color="white">
                    {{ (props.row.active ? 'ACTIVO' : 'INACTIVO') }}
                  </q-chip>
                </q-td>
                <q-td key="actions" style="width: 10%;" :props="props" class="pull-left">
                  <q-btn color="primary" icon="fas fa-edit" flat @click.native="editSelectedRow(props.row.id)" size="10px">
                    <q-tooltip content-class="bg-primary">Editar</q-tooltip>
                  </q-btn>
                  <q-btn color="red" icon="fas fa-trash-alt" flat @click.native="deleteSelectedRow(props.row.id)" size="10px">
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
  name: 'IndexDrivers',
  data () {
    return {
      pagination: {
        sortBy: 'code',
        descending: false,
        rowsPerPage: 25
      },
      columns: [
        { name: 'name', align: 'center', label: 'NOMBRE', field: 'name', style: 'width: 50%', sortable: true },
        { name: 'rfc', align: 'center', label: 'RFC', field: 'rfc', style: 'width: 50%', sortable: true },
        { name: 'license', align: 'center', label: 'LICENCIA', field: 'license', style: 'width: 50%', sortable: true },
        { name: 'active', align: 'center', label: 'ESTATUS', field: 'active', style: 'width: 50%', sortable: true },
        { name: 'actions', align: 'center', label: 'ACCIONES', field: 'actions', style: 'width: 10%', sortable: false }
      ],
      data: [],
      filter: ''
    }
  },
  beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(2) && !this.$store.getters['users/roles'].includes(8)) {
      this.$router.push('/')
    }
  },
  mounted () {
    this.fetchFromServer()
  },
  methods: {
    fetchFromServer () {
      this.$q.loading.show()
      api.get('/drivers').then(({ data }) => {
        this.data = data.drivers
        this.$q.loading.hide()
      })
    },
    editSelectedRow (id) {
      this.$router.push(`/drivers/${id}`)
    },
    deleteSelectedRow (id) {
      this.$q.dialog({
        title: 'Confirmación',
        message: '¿Desea eliminar el Chofer?',
        persistent: true,
        ok: {
          label: 'Aceptar',
          color: 'green'
        },
        cancel: {
          label: 'Cancelar',
          color: 'red'
        }
      }).onOk(() => {
        this.$q.loading.show()
        api.delete(`/drivers/${id}`).then(({ data }) => {
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
        this.$q.loading.hide()
      }).onCancel(() => {})
    }
  }
}
</script>

<style>
</style>

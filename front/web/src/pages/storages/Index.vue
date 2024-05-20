<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-8">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Almacenes" />
            </q-breadcrumbs>
          </div>
        </div>
        <div class="col-xs-6 col-md-4 pull-right">
          <div class="q-pa-sm q-gutter-sm">
            <q-btn class="bg-primary" style="color: white" icon="add" label="Nuevo" @click.native="$router.push('/storages/new')" />
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
                <q-td key="code" style="text-align: center; width: 10%;" :props="props">{{ props.row.code }}</q-td>
                <q-td key="name" style="text-align: left; width: 40%;" :props="props">{{ props.row.name }}</q-td>
                <q-td key="branchOffice" style="text-align: left; width: 40%;" :props="props">{{ props.row.branch_office }}</q-td>
                <q-td key="storageType" style="text-align: left; width: 40%;" :props="props">{{ props.row.storage_type }}</q-td>
                <q-td key="actions" style="width: 10%;" :props="props">
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
  name: 'IndexStorages',
  data () {
    return {
      pagination: {
        sortBy: 'code',
        descending: false,
        rowsPerPage: 25
      },
      columns: [
        { name: 'code', align: 'center', label: 'CÓDIGO', field: 'code', style: 'width: 10%', sortable: true },
        { name: 'name', align: 'center', label: 'NOMBRE', field: 'name', style: 'width: 40%', sortable: true },
        { name: 'branchOffice', align: 'center', label: 'ESTACIÓN', field: 'branchOffice', style: 'width: 40%', sortable: true },
        { name: 'storageType', align: 'center', label: 'TIPO DE ALMACÉN', field: 'storageType', style: 'width: 40%', sortable: true },
        { name: 'actions', align: 'center', label: 'ACCIONES', field: 'actions', style: 'width: 10%', sortable: false }
      ],
      data: [],
      filter: ''
    }
  },
  /* beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(2)) {
      this.$router.push('/')
    }
  }, */
  mounted () {
    this.fetchFromServer()
  },
  methods: {
    fetchFromServer () {
      this.$q.loading.show()
      api.get('/storages/all').then(({ data }) => {
        console.log(data.storages)
        this.data = data.storages
        this.$q.loading.hide()
      })
    },
    editSelectedRow (id) {
      this.$router.push(`/storages/${id}`)
    },
    deleteSelectedRow (id) {
      api.get(`/storages/validation/${id}`).then(({ data }) => {
        if (data.validation !== null) {
          this.$q.notify({
            message: 'No se puede eliminar el Almacen. Existen movimientos que dependen de el.',
            position: 'top',
            color: 'warning',
            icon: 'close'
          })
        } else {
          this.$q.dialog({
            title: 'Confirmación',
            message: '¿Desea eliminar este Almacén?',
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
            api.delete(`/storages/${id}`).then(({ data }) => {
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
      })
    }
  }
}
</script>

<style>
</style>

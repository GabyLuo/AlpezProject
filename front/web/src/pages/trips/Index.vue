<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-8">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Embarques" />
            </q-breadcrumbs>
          </div>
        </div>
        <div class="col-xs-6 col-md-4 pull-right">
          <div class="q-pa-sm q-gutter-sm">
            <q-btn color="primary" icon="add" label="Nuevo" @click.native="$router.push('/trips/new')" />
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
                <q-td key="folio" style="text-align: center;" :props="props">{{ props.row.folio }}</q-td>
                <q-td key="invoice_id" style="text-align: left;" :props="props">
                  <label @click="$router.push(`/storage-exits/${props.row.invoice_id}`)" class="text-primary" style="text-decoration: underline; cursor: pointer;">{{ props.row.invoice_id }}</label>
                </q-td>
                <q-td key="customer" style="text-align: left;" :props="props">{{ props.row.customer }}</q-td>
                <q-td key="type" style="text-align: left;" :props="props">{{ props.row.type }}</q-td>
                <q-td key="brand" style="text-align: left;" :props="props">{{ props.row.brand }}</q-td>
                <q-td key="model" style="text-align: left;" :props="props">{{ props.row.model }}</q-td>
                <q-td key="economic_number" style="text-align: left;" :props="props">{{ props.row.economic_number }}</q-td>
                <q-td key="sucursal" style="text-align: right;" :props="props">{{ props.row.sucursal }}</q-td>
                <q-td key="date" style="text-align: right;" :props="props">{{ props.row.date }}</q-td>
                <q-td key="status"  style="text-align: center;" :props="props">
                  <q-chip square dense :color="colorTimbrado[props.row.status_timbrado]" text-color="white">{{ statusTimbrado[props.row.status_timbrado] }}</q-chip>
                </q-td>
                <q-td key="actions" style="width: 18%; text-align: center;" :props="props" class="pull-left">
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
  name: 'IndexRanges',
  data () {
    return {
      pagination: {
        sortBy: 'name',
        descending: false,
        rowsPerPage: 25
      },
      columns: [
        { name: 'folio', align: 'center', label: 'FOLIO', field: 'folio', sortable: true },
        { name: 'invoice_id', align: 'center', label: 'REMISION', field: 'invoice_id', sortable: true },
        { name: 'customer', align: 'center', label: 'CLIENTE', field: 'customer', sortable: true },
        { name: 'type', align: 'center', label: 'VEHÍCULO', field: 'type', sortable: true },
        { name: 'brand', align: 'center', label: 'MARCA', field: 'brand', sortable: true },
        { name: 'model', align: 'center', label: 'MODELO', field: 'model', sortable: true },
        { name: 'economic_number', align: 'center', label: 'NÚMERO DE UNIDAD', field: 'economic_number', sortable: true },
        { name: 'sucursal', align: 'center', label: 'ESTACIÓN', field: 'sucursal', sortable: true },
        { name: 'date', align: 'center', label: 'FECHA DE SALIDA', field: 'date', sortable: true },
        { name: 'status', align: 'center', label: 'ESTATUS', field: 'status', sortable: true },
        { name: 'actions', align: 'center', label: 'ACCIONES', field: 'actions', style: 'width: 18%', sortable: false }
      ],
      data: [],
      filter: '',
      statusTimbrado: ['NUEVO', 'TIMBRADO', 'CANCELADO', 'CANCELANDO', 'TIMBRANDO', 'CANCELANDO', 'ERROR', 'ERROR AL CANCELAR'],
      colorTimbrado: ['blue-6', 'green-6', 'purple-6', 'warning', 'warning', 'warning', 'red-6', 'red-6']
    }
  },
  beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(8)) {
      this.$router.push('/')
    }
  },
  mounted () {
    this.fetchFromServer()
  },
  methods: {
    fetchFromServer () {
      this.$q.loading.show()
      api.get('/trips').then(({ data }) => {
        this.data = data.trips
        this.$q.loading.hide()
      })
    },
    editSelectedRow (id) {
      this.$router.push(`/trips/${id}`)
    },
    deleteSelectedRow (id) {
      this.$q.dialog({
        title: 'Confirmación',
        message: '¿Desea eliminar este Embarque',
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
        api.delete(`/trips/${id}`).then(({ data }) => {
          console.log(data)
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
  }
}
</script>

<style>
</style>

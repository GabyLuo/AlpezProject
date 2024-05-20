<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-8">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Ordenes de compra" />
            </q-breadcrumbs>
          </div>
        </div>
        <div class="col-xs-6 col-md-4 pull-right" v-if="roleId === 1 ||  roleId === 22 || roleId === 20 || roleId === 28">
          <div class="q-pa-sm q-gutter-sm">
            <q-btn class="bg-primary" style="color: white" icon="add" label="Nuevo" @click.native="$router.push('/purchase-orders/new')" />
          </div>
        </div>
      </div>
    </div>

    <div class="q-pa-md bg-grey-3">
      <div class="row bg-white border-panel">
        <div class="col q-pa-md">
          <div class="row bg-white q-pa-md q-col-gutter-xs q-col-gutter-md">
            <div class="col-sm-2">
          <q-select
            color="dark"
            bg-color="secondary"
            filled
            v-model="saleDatev1"
            mask="date"
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
                  @input="filterGrid()"
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
                  @input="filterGrid()"
                  today-btn
                />
              </div>
            </q-popup-proxy>
          </q-select>
        </div>
        <div class="col-md-3">
          <q-select  color="dark" bg-color="secondary" filled
                    v-model="suppliers"
                    :options="optionsSupp"
                    use-input
                    emit-value map-options
                    @filter="filterSuppliers"
                    @input="filterGrid()"
                    label="Proveedor"
          >
          </q-select>
        </div>
        <div class="col-sm-2">
          <q-select  color="dark" bg-color="secondary" filled
                    v-model="status"
                    :options="[
                      {label: 'TODOS', value: 'TODOS'},
                      {label: 'NUEVO', value: 'NUEVO'},
                      {label: 'COTIZADO', value: 'COTIZADO'},
                      {label: 'PEDIDO', value: 'PEDIDO'},
                      {label: 'RECIBIDO', value: 'RECIBIDO'}
                    ]"
                    emit-value map-options
                    @input="filterGrid()"
                    label="Estatus"
          >
          </q-select>
        </div>
        </div>
          <div class="row bg-white" >
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
                <q-td key="serial" style="text-align: center; width: 10%;" :props="props">{{ props.row.serial }}</q-td>
                <q-td key="supplier" style="text-align: left; width: 35%;" :props="props">{{ props.row.supplier }}</q-td>
                <q-td key="order_date" style="width: 15%;" :props="props">{{ props.row.order_date }}</q-td>
                <q-td key="requested_date" style="width: 15%;" :props="props">{{ props.row.requested_date }}</q-td>
                <q-td key="status" style="width: 10%; text-align: center" :props="props">
                  <q-chip square dense :color="props.row.status == 'NUEVO' ? 'blue' : (props.row.status == 'COTIZADO' ? 'warning' : (props.row.status == 'PEDIDO' ? 'orange' : (props.row.status == 'EMBARCADO' ? 'purple-6' : (props.row.status == 'ARRIBO' ? 'accent' : (props.row.status == 'RECEPCION' ? 'light-green' : (props.row.status == 'RECIBIDO' ? 'green' : (props.row.status == 'PARCIAL' ? 'red-4' : 'red-6')))))))" text-color="white">
                    {{ props.row.status }}
                  </q-chip>
                </q-td>
                <q-td key="actions" style="width: 10%;  text-align: left" :props="props" v-if="props.row.status != 'Cerrado'">
                  <q-btn color="primary" icon="fas fa-edit" flat @click.native="editSelectedRow(props.row.id)" size="10px">
                    <q-tooltip content-class="bg-primary">Editar</q-tooltip>
                  </q-btn>
                  <q-btn color="negative" v-if="(roleId === 1 || roleId === 5 || roleId === 10 || roleId === 19 || roleId === 20) && props.row.status == 'NUEVO'" icon="fas fa-trash-alt" flat @click.native="deleteSelectedRow(props.row.id)" size="10px">
                    <q-tooltip content-class="bg-red">Eliminar</q-tooltip>
                  </q-btn>
                </q-td>
                <q-td key="actions" style="width: 10%;" :props="props" v-else>
                  <q-btn color="primary" icon="remove_red_eye" flat @click.native="editSelectedRow(props.row.id)" size="10px">
                    <q-tooltip content-class="bg-primary">Ver detalles</q-tooltip>
                  </q-btn>
                </q-td>
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
  name: 'IndexPurchaseOrders',
  data () {
    return {
      optionsSupp: [],
      optionsSuppliers: [],
      filterSupp: [],
      suppliers: { value: 0, label: 'TODOS' },
      status: { label: 'TODOS', value: 'TODOS' },
      saleDatev1: null,
      saleDatev2: null,
      pagination: {
        sortBy: 'serial',
        descending: true,
        rowsPerPage: 25
      },
      columns: [
        { name: 'serial', align: 'center', label: 'FOLIO', field: 'serial', style: 'width: 10%', sortable: true },
        { name: 'supplier', align: 'center', label: 'PROVEEDOR', field: 'supplier', style: 'width: 35%', sortable: true },
        { name: 'order_date', align: 'center', label: 'FECHA DE PEDIDO', field: 'order_date', style: 'width: 15%', sortable: true },
        { name: 'requested_date', align: 'center', label: 'FECHA DE ARRIBO', field: 'requested_date', style: 'width: 15%', sortable: true },
        { name: 'status', align: 'center', label: 'ESTATUS', field: 'status', style: 'width: 10%', sortable: true },
        { name: 'actions', align: 'center', label: 'ACCIONES', field: 'actions', style: 'width: 10%', sortable: false }
      ],
      data: []
    }
  },
  computed: {
    roleId () {
      const user = this.$store.getters['users/rol']
      return parseInt(user)
    }
  },
  /* beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(22) && !this.$store.getters['users/roles'].includes(21) && !this.$store.getters['users/roles'].includes(26)) {
      this.$router.push('/')
    }
  }, */
  beforeRouteEnter (to, from, next) {
    next(vm => {
      const propiedades = vm.$store.getters['users/rol']
      console.log(propiedades)
      if (propiedades === 1 || propiedades === 3 || propiedades === 7 || propiedades === 2 || propiedades === 20 || propiedades === 4 || propiedades === 27 || propiedades === 20 || propiedades === 22 | propiedades === 28) {
        next()
      } else {
        next('/')
      }
    })
  },
  mounted () {
    this.fetchFromServer()
    this.getSuppliersOrders()
  },
  methods: {
    filterSuppliers (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.optionsSupp = this.optionsSuppliers.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    getSuppliersOrders () {
      api.get('/suppliers/getSuppliersOrders').then(({ data }) => {
        this.optionsSuppliers = data.suppliers
        this.optionsSuppliers.unshift({ value: 0, label: 'TODOS' })
      })
    },
    filterGrid () {
      this.data = []
      const params = []
      params.suppliers = this.suppliers
      params.saleDatev1 = this.saleDatev1
      params.saleDatev2 = this.$formatDate(this.saleDatev2)
      params.status = this.status
      /* console.log(this.saleDatev1)
      console.log(this.saleDatev2)
      console.log(this.suppliers)
      console.log(this.status) */
      api.post('/purchase-orders/getGrid', params).then(({ data }) => {
        if (data.result) {
          this.data = data.orders
        }
      })
    },
    fetchFromServer () {
      console.log(this.$store.getters['users/roles'])
      this.$q.loading.show()
      api.get('/purchase-orders/all').then(({ data }) => {
        console.log(data)
        if (data.result) {
          this.data = data.orders
        } else {
          this.$q.notify({
            message: data.message.content,
            position: 'top',
            color: 'warning',
            icon: 'close'
          })
        }
        this.$q.loading.hide()
      })
    },
    editSelectedRow (id) {
      this.$router.push(`/purchase-orders/${id}`)
    },
    deleteSelectedRow (id) {
      this.$q.dialog({
        title: 'Confirmación',
        message: '¿Desea eliminar esta Orden de Compra?',
        persistent: true,
        ok: 'Eliminar',
        cancel: 'Cancelar'
      }).onOk(() => {
        api.delete(`/purchase-orders/${id}`).then(({ data }) => {
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

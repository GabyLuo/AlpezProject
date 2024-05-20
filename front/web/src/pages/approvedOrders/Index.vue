<template>
  <q-page>

    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-8">
          <span class="q-ml-md grey-8 fs28 page-title">Pedidos autorizados</span>
        </div>
        <div class="col-sm-4 pull-right">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="right">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Pedidos autorizados" />
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
            row-key="id"
            :pagination.sync="pagination"
          >
            <template v-slot:body="props">
              <q-tr :props="props">
                <q-td key="id" :props="props">{{ props.row.id }}</q-td>
                <q-td key="customerName" :props="props">{{ props.row.customer_name }}</q-td>
                <q-td key="userName" :props="props">{{ props.row.user_name }}</q-td>
                <q-td key="userEmail" :props="props">{{ props.row.user_email }}</q-td>
                <q-td key="comments" :props="props">{{ props.row.comments }}</q-td>
                <q-td key="actions" :props="props">
                  <q-btn color="primary" icon="remove_red_eye" flat @click.native="editSelectedRow(props.row.id)" size="10px">
                    <q-tooltip content-class="bg-primary">Ver</q-tooltip>
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
  name: 'IndexRequestedOrders',
  data () {
    return {
      pagination: {
        sortBy: 'id',
        descending: true,
        rowsPerPage: 50
      },
      columns: [
        { name: 'id', align: 'center', label: '#', field: 'id', sortable: true, sort: (a, b) => Number(a, 10) - Number(b, 10) },
        { name: 'customerName', align: 'center', label: 'Cliente', field: 'customerName', sortable: true },
        { name: 'userName', align: 'center', label: 'Usuario', field: 'userName', sortable: true },
        { name: 'userEmail', align: 'center', label: 'E-mail', field: 'userEmail', sortable: true },
        { name: 'comments', align: 'center', label: 'Comentarios', field: 'comments', sortable: true },
        { name: 'actions', align: 'center', label: 'Acciones', field: 'actions', sortable: false }
      ],
      data: []
    }
  },
  beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(2)) {
      this.$router.push('/')
    }
  },
  mounted () {
    this.fetchFromServer()
  },
  methods: {
    fetchFromServer () {
      this.$q.loading.show()
      api.get('/shopping-carts/approved').then(({ data }) => {
        this.data = data.orders
        this.$q.loading.hide()
      })
    },
    editSelectedRow (id) {
      this.$router.push(`/approved-orders/${id}`)
    }
  }
}
</script>

<style>
</style>

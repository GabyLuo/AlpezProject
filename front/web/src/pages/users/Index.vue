<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-8">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Usuarios" />
            </q-breadcrumbs>
          </div>
        </div>
        <div class="col-xs-6 col-md-4 pull-right">
          <div class="q-pa-sm q-gutter-sm">
            <q-btn class="bg-primary" style="color: white" icon="add" label="Nuevo" @click.native="$router.push('/users/new')" />
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
            row-key="email"
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
                <q-td key="email" style="text-align: left; width: 20%;" :props="props">{{ props.row.email }}</q-td>
                <q-td key="nickname" style="text-align: left; width: 30%;" :props="props">{{ props.row.nickname }}</q-td>
                <q-td key="roles" style="text-align: left; width: 40%;" :props="props">{{ props.row.roles }}</q-td>
                <q-td key="sucursal" style="text-align: left; width: 30%;" :props="props">{{ props.row.sucursal }}</q-td>
                <q-td key="actions" style="width: 10%;" :props="props">
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
  name: 'IndexUsers',
  data () {
    return {
      pagination: {
        sortBy: 'code',
        descending: false,
        rowsPerPage: 25
      },
      columns: [
        { name: 'email', align: 'center', label: 'EMAIL', field: 'email', style: 'width: 20%', sortable: true },
        { name: 'nickname', align: 'center', label: 'NOMBRE', field: 'nickname', style: 'width: 30%', sortable: true },
        { name: 'roles', align: 'center', label: 'ROLES', field: 'roles', style: 'width: 20%', sortable: true },
        { name: 'sucursal', align: 'center', label: 'ESTACIÓN', field: 'sucursal', style: 'width: 20%', sortable: true },
        { name: 'actions', align: 'center', label: 'ACCIONES', field: 'actions', style: 'width: 10%', sortable: false }
      ],
      data: [],
      filter: ''
    }
  },
  beforeRouteEnter (to, from, next) {
    next(vm => {
      const propiedades = vm.$store.getters['users/rol']
      console.log(propiedades)
      if (propiedades === 1 || propiedades === 3 || propiedades === 7) {
        next()
      } else {
        next('/')
      }
    })
  },
  /* beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(7)) {
      this.$router.push('/')
    }
  }, */
  mounted () {
    if (this.$store.getters['users/roles'].includes(1) || this.$store.getters['users/roles'].includes(3) || this.$store.getters['users/roles'].includes(7)) {
      this.fetchFromServer()
    }
  },
  methods: {
    fetchFromServer () {
      this.$q.loading.show()
      api.get('/users').then(({ data }) => {
        this.data = data.users
        this.$q.loading.hide()
      })
    },
    editSelectedRow (id) {
      this.$router.push(`/users/${id}`)
    }
  }
}
</script>

<style>
</style>

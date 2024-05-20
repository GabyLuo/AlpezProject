<template>
  <q-page class="bg-grey-3">
   <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-3 pull-right">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left">
              <q-breadcrumbs-el class="fs20" label="" icon="home" to="/" />
              <q-breadcrumbs-el class="q-ml-md grey-8 fs20 page-title" label="Roles" />
            </q-breadcrumbs>
          </div>
        </div>
        <div class="col-sm-9 pull-right">
          <div class="q-pa-md q-gutter-sm">
           <q-btn color="primary" icon="add" label="Nuevo" @click.native="$router.push('/roles/new')" />
         </div>
       </div>
     </div>
   </div>

    <div class="q-pa-md bg-grey-3">
      <div class="row bg-white border-panel">
        <div class="col q-pa-md">

          <q-table flat bordered :data="data" :columns="columns" row-key="email" :pagination.sync="pagination" :filter="filter">
           <template v-slot:top>
              <div style="width: 100%;">
                <q-input dense debounce="300" v-model="filter" placeholder="Buscar">
                  <template v-slot:append>
                    <q-icon name="search" />
                  </template>
                </q-input>
              </div>
            </template>
            <template v-slot:body="props">
              <q-tr :props="props">
                <q-td key="name" :props="props" class="pull-left">{{ props.row.name }}</q-td>
                <q-td key="actions" :props="props" class="pull-left">
                  <q-btn class="pull-left" color="primary" icon="fas fa-edit" flat @click.native="editSelectedRow(props.row.id)" size="10px"><q-tooltip content-class="bg-positive">EDITAR</q-tooltip></q-btn>
                  <q-btn class="action-btn pull-left" color="red" icon="fas fa-trash-alt" flat @click.native="deleteSelectedRow(props.row.id)" size="10px"><q-tooltip content-class="bg-positive">ELIMINAR</q-tooltip></q-btn>
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
        { name: 'name', align: 'center', label: 'ROL', field: 'name', sortable: true },
        { name: 'actions', align: 'center', label: 'Acciones', field: 'actions', sortable: false }
      ],
      data: [],
      filter: ''
    }
  },
  /* beforeRouteEnter (to, from, next) {
    next(vm => {
      const propiedades = vm.$store.getters['users/user']
      if (propiedades.role_id === 1) {
        next()
      } else {
        next('/')
      }
    })
  }, */
  mounted () {
    this.fetchFromServer()
  },
  methods: {
    fetchFromServer () {
      this.$q.loading.show()
      api.get('/roles').then(({ data }) => {
        this.data = data.roles
      }).catch(error => error)
      this.$q.loading.hide()
    },
    editSelectedRow (id) {
      this.$router.push(`/roles/${id}`)
    },
    deleteSelectedRow (id) {
      this.$q.dialog({
        message: '¿Desea borrar este Rol?',
        ok: {
          label: 'Aceptar',
          color: 'green'
        },
        cancel: {
          label: 'Cancelar',
          color: 'red'
        }
      }).onOk(() => {
        this.showLoading()
        api.delete(`/roles/${id}`).then(({ data }) => {
          // this.$showNotify(data.message.content, data.result ? 'positive' : 'negative')
          if (data.result) {
            this.fetchFromServer()
          }
          this.$q.notify({
            message: data.message.content,
            color: data.result ? 'positive' : 'red',
            position: 'top'
          })
          this.beforeDestroy()
        })
      }).onCancel(() => {})
    },
    showLoading () {
      this.$q.loading.show({
        spinnerColor: 'primary',
        spinnerSize: 140,
        backgroundColor: 'white',
        message: 'Cargando..',
        messageColor: 'black'
      })
    },
    beforeDestroy () {
      this.$q.loading.hide()
    }
  }
}
</script>

<style>
</style>

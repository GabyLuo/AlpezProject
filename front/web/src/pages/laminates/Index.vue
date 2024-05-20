<template>
  <q-page>
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-8">
          <span class="q-ml-md grey-8 fs28 page-title">Laminados</span>
        </div>
        <div class="col-sm-4 pull-right">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="right">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Laminado" />
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
              <q-btn color="primary" icon="add" label="Nuevo" @click.native="$router.push('/laminates/new')" />
            </div>
          </div>

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
                <q-td key="id" style="text-align: right; width: 10%;" :props="props">{{ props.row.id }}</q-td>
                <q-td key="scheduled_date" style="width: 10%;" :props="props">{{ props.row.scheduled_date }}</q-td>
                <q-td key="product" style="text-align: left; width: 15%;" :props="props">{{ props.row.product }}</q-td>
                <q-td key="branch_office" style="text-align: left; width: 15%;" :props="props">{{ props.row.branch_office }}</q-td>
                <q-td key="storage" style="text-align: left; width: 10%;" :props="props">{{ props.row.storage }}</q-td>
                <q-td key="operator" style="text-align: left; width: 10%;" :props="props">{{ props.row.operator }}</q-td>
                <q-td key="weight" style="text-align: right; width: 10%;" :props="props">{{ props.row.weight ? formatPrice(props.row.weight)+'KG' : null }} </q-td>
                <q-td key="status" style="width: 10%;" :props="props">
                  <q-chip square dense :color="props.row.status == 'NUEVO' ? 'primary' : (props.row.status == 'PRODUCIENDO' ? 'secondary' : (props.row.status == 'TERMINADO' ? 'positive' : 'accent'))" text-color="white">
                    {{ props.row.status }}
                  </q-chip>
                </q-td>
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
  name: 'IndexLaminates',
  data () {
    return {
      pagination: {
        sortBy: 'code',
        descending: false,
        rowsPerPage: 50
      },
      columns: [
        { name: 'id', align: 'center', label: 'No. Laminado', field: 'id', style: 'width: 10%', sortable: true },
        { name: 'scheduled_date', align: 'center', label: 'Fecha programada', field: 'scheduled_date', style: 'width: 10%', sortable: true },
        { name: 'product', align: 'center', label: 'Producto', field: 'product', style: 'width: 15%', sortable: true },
        { name: 'branch_office', align: 'center', label: 'Sucursal', field: 'branch_office', style: 'width: 15%', sortable: true },
        { name: 'storage', align: 'center', label: 'Almacén', field: 'storage', style: 'width: 10%', sortable: true },
        { name: 'operator', align: 'center', label: 'Operador', field: 'operator', style: 'width: 10%', sortable: true },
        { name: 'weight', align: 'center', label: 'Peso ejecutado', field: 'weight', style: 'width: 10%', sortable: true },
        { name: 'status', align: 'center', label: 'Estatus', field: 'status', style: 'width: 10%', sortable: true },
        { name: 'actions', align: 'center', label: 'Acciones', field: 'actions', style: 'width: 10%', sortable: false }
      ],
      data: []
    }
  },
  beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(10) && !this.$store.getters['users/roles'].includes(13)) {
      this.$router.push('/')
    }
  },
  mounted () {
    this.fetchFromServer()
  },
  methods: {
    formatPrice (value) {
      const val = (value / 1).toFixed(1).replace('.', ',')
      return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')
    },
    fetchFromServer () {
      this.$q.loading.show()
      api.get('/laminates').then(({ data }) => {
        console.log(data)
        this.data = data.laminates
        this.$q.loading.hide()
      })
    },
    editSelectedRow (id) {
      this.$router.push(`/laminates/${id}`)
    }
  }
}
</script>

<style>
</style>

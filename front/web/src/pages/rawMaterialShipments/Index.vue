<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-8">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Recepciones" />
            </q-breadcrumbs>
          </div>
        </div>
        <!-- <div class="col-xs-6 col-md-4 pull-right">
          <div class="q-pa-sm q-gutter-sm">
            <q-btn color="primary" icon="add" label="Nuevo" @click.native="$router.push('/raw-material-shipments/new')" />
          </div>
        </div> -->
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
            row-key="serial"
            :pagination.sync="pagination"
          >
            <template v-slot:body="props">
              <q-tr :props="props">
                <q-td key="serial" style="text-align: center; width: 10%;" :props="props">{{ props.row.order_id }}</q-td>
                <q-td key="oc" style="text-align: center; width: 10%;" :props="props">{{ props.row.serial }}</q-td>
                <q-td key="supplier" style="text-align: left; width: 25%;" :props="props">{{ props.row.supplier }}</q-td>
                <q-td key="branch_office" style="text-align: left; width: 25%;" :props="props">{{ props.row.branch_office }}</q-td>
                <q-td key="storage" style="text-align: left; width: 25%;" :props="props">{{ props.row.storage }}</q-td>
                <q-td key="status" style="width: 10%;" :props="props">
                  <q-chip square dense :color="props.row.status == 'PARCIAL' ? 'pink' : (props.row.status == 'PEDIDO' ? 'orange' : 'secondary')" text-color="white">
                    {{ props.row.status }}
                  </q-chip>
                </q-td>
                <q-td key="actions" style="width: 5%;" :props="props">
                  <q-btn color="primary" :icon="props.row.hayrestante || props.row.shipment_id === null ? 'add' : 'edit'" flat @click.native="props.row.hayrestante || props.row.shipment_id === null ? newShipment(props.row.order_id) : editShipment(props.row.shipment_id)" size="10px">
                    <q-tooltip v-if="props.row.hayrestante || props.row.shipment_id === null" content-class="bg-primary">Nueva Recepción</q-tooltip>
                    <q-tooltip v-else content-class="bg-primary">Editar Recepción</q-tooltip>
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
  name: 'IndexRawMaterialShipments',
  data () {
    return {
      pagination: {
        sortBy: 'serial',
        descending: true,
        rowsPerPage: 25
      },
      columns: [
        { name: 'serial', align: 'center', label: 'FOLIO', field: 'serial', style: 'width: 10%', sortable: true },
        { name: 'oc', align: 'center', label: 'OC', field: 'oc', style: 'width: 10%', sortable: true },
        { name: 'supplier', align: 'center', label: 'PROVEEDOR', field: 'supplier', style: 'width: 25%', sortable: true },
        { name: 'branch_office', align: 'center', label: 'ESTACIÓN', field: 'branch_office', style: 'width: 25%', sortable: true },
        { name: 'storage', align: 'center', label: 'ALMACÉN', field: 'storage', style: 'width: 25%', sortable: true },
        { name: 'status', align: 'center', label: 'ESTATUS', field: 'status', style: 'width: 10%', sortable: true },
        { name: 'actions', align: 'center', label: 'ACCIONES', field: 'actions', style: 'width: 5%', sortable: false }
      ],
      order_id: null,
      data: [],
      edit: true
    }
  },
  /* beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(2)) {
      this.$router.push('/')
    }
  }, */
  beforeRouteEnter (to, from, next) {
    next(vm => {
      const propiedades = vm.$store.getters['users/rol']
      console.log(propiedades)
      if (propiedades === 1 || propiedades === 3 || propiedades === 7 || propiedades === 2 || propiedades === 20 || propiedades === 4 || propiedades === 27 || propiedades === 22 || propiedades === 26) {
        next()
      } else {
        next('/')
      }
    })
  },
  mounted () {
    this.fetchFromServer()
  },
  methods: {
    fetchFromServer () {
      this.$q.loading.show()
      api.get('/shipments/arribeOcs').then(({ data }) => {
        console.log(data.shipments)
        this.data = data.shipments
        this.$q.loading.hide()
      })
    },
    newShipment (id) {
      const orderid = Number(id)
      this.$router.push(`/shipments/purcharse-order/${orderid}/${0}`)
    },
    editShipment (id) {
      const orderid = Number(id)
      this.$router.push(`/shipments/${orderid}/${0}`)
    }
  }
}
</script>

<style>
</style>

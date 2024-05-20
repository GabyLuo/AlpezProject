<template>
    <q-page class="bg-grey-3">
        <div class="q-pa-sm panel-header">
            <div class="row">
                <div class="col-sm-4">
                    <div class="q-pa-md q-gutter-sm">
                        <q-breadcrumbs style="font-size: 20px">
                            <q-breadcrumbs-el label="" icon="home" to="/"/>
                            <q-breadcrumbs-el label="Movimientos"/>
                        </q-breadcrumbs>
                    </div>
                </div>
                 <div class="col-sm-8 pull-right">
                     <div class="q-pa-sm q-gutter-sm">
                         <q-btn @click.native="$router.push(`/movements/new/${1}`)" color="positive" icon="add" label="Entrada"/>
                         <q-btn @click.native="$router.push(`/movements/new/${2}`)" color="negative" icon="remove" label="Salida"/>
                         <q-btn @click.native="$router.push(`/movements/newTransfer/${4}`)" color="warning" icon="compare_arrows" label="Traspaso"/>
                         <q-btn @click.native="$router.push(`/movements/new/${3}`)" color="light-blue" icon="restore" label="Inventario Físico"/>
                         <!-- <q-btn @click.native="$router.push(`/movements/new/${6}`)" color="purple" icon="restore" label="Merma"/> -->
                     </div>
                 </div>
            </div>
        </div>
        <div class="q-pa-md bg-grey-3">
            <div class="row bg-white border-panel">
              <div class="col-md-4 col-xs-4 q-pl-md q-pt-md">
                <q-select
                label="Tipo de Movimiento"
                color="dark"
                filled
                bg-color="secondary"
                v-model="filterType"
                :options="[
                { label: 'TODOS', value: 0 },
                { label: 'ENTRADA', value: 1 },
                { label: 'SALIDA', value: 2 },
                { label: 'INVENTARIO FÍSICO', value: 3 },
                { label: 'TRASPASO (ENTRADA)', value: 4 },
                { label: 'TRASPASO (SALIDA)', value: 5 },
                { label: 'MERMA', value: 6 }
                ]"
                map-options
                >
                <template v-slot:prepend>
                  <q-icon name="fas fa-cubes" />
                </template>
                </q-select>
              </div>
              <div class="col-md-4 col-xs-4 q-pl-md q-pt-md">
                <q-select
                label="Almacén"
                color="dark"
                filled
                bg-color="secondary"
                v-model="storage"
                :options="storageOptions"
                map-options
                >
                <template v-slot:prepend>
                  <q-icon name="fas fa-cubes" />
                </template>
                </q-select>
              </div>
                <div class="col-md-12 col-xs-12 q-pa-md">
                    <div class="row q-mb-sm">
                    </div>
                    <q-table
                    flat
                    bordered
                    :data="filterTM"
                    row-key="folio"
                    :pagination.sync="pagination"
                    :columns="columns"
                    :filter="filter"
                    >
                     <template v-slot:top>
                         <div style="width: 100%">
                             <q-input dense debounce="300" v-model="filter" placeholder="Buscar">
                                 <template v-slot:append>
                                 <q-icon name="search"></q-icon>
                                 </template>
                             </q-input>
                         </div>
                     </template>
                     <template v-slot:body="props">
                         <q-tr :props="props">
                             <q-td style="text-align: center;" key="folio" :props="props">{{ props.row.folio }}</q-td>
                             <q-td key="created" :props="props">{{ props.row.date }}</q-td>
                             <q-td key="type_id" bg :props="props">
                              <q-chip text-color="white" square dense :color="props.row.color">
                                {{ props.row.type_id }}
                              </q-chip>
                             </q-td>
                             <q-td key="branch_name" :props="props">{{ props.row.branch_name }}</q-td>
                             <q-td key="storage_name" :props="props">{{ props.row.storage_name }}</q-td>
                             <q-td key="status" :props="props">
                              <q-chip dense icon="add" :color="props.row.status == 'NUEVO' ? 'blue' : (props.row.status === 'EJECUTADO' ? 'positive' : (props.row.status === 'CANCELADO' ? 'negative' : negative ))" text-color="white">
                                {{ props.row.status }}
                              </q-chip>
                             </q-td>
                             <q-td key="actions" :props="props">
                                 <q-btn color="primary" flat icon="fas fa-edit"  @click.native="editSelectedRow(props.row.id)" size="10px">
                                   <q-tooltip content-class="bg-primary">Editar</q-tooltip>
                                 </q-btn>
                                 <q-btn color="negative" flat icon="fas fa-trash-alt" @click.native="deleteSelectedRow(props.row.id)" size="10px">
                                   <q-tooltip content-class="bg-red">Eliminar</q-tooltip>
                                 </q-btn>
                                 <q-btn color="negative" icon="fas fa-ban" flat @click.native="cancelSelectedRow(props.row.id)" size="10px" v-if="props.row.status == 'EJECUTADO' && haspermissionv1">
                                  <q-tooltip content-class="bg-red">Cancelar</q-tooltip>
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
  name: 'IndexMovements',
  data () {
    return {
      storage: { label: 'TODOS', value: 0 },
      storageOptions: [],
      filter: '',
      pagination: {
        sortBy: 'code',
        descending: false,
        rowsPerPage: 25
      },
      columns: [
        { name: 'folio', align: 'center', label: 'FOLIO', field: 'folio', style: 'width: 10%', sortable: true },
        { name: 'created', align: 'center', label: 'FECHA', field: 'created', style: 'width: 15%', sortable: true },
        { name: 'type_id', align: 'center', label: 'MOVIMIENTOS', field: 'type_id', style: 'width: 10%', sortable: true },
        { name: 'branch_name', align: 'center', label: 'ESTACIÓN', field: 'branch_name', style: 'width: 10%', sortable: true },
        { name: 'storage_name', align: 'center', label: 'ALMACÉN', field: 'storage_name', style: 'width: 10%', sortable: true },
        { name: 'status', align: 'center', label: 'ESTATUS', field: 'status', style: 'width: 10%', sortable: true },
        { name: 'actions', align: 'center', label: 'ACCIONES', field: 'actions', style: 'width: 10%', sortable: false }
      ],
      filterType: { label: 'TODOS', value: 0 },
      data: [],
      dataTemp: []
    }
  },
  mounted () {
    this.fetchFromServer()
  },
  computed: {
    filterTM () {
      var data2 = this.data
      console.log(data2)
      if ((this.storage !== null && this.storage.label !== 'TODOS') && (this.filterType !== null && this.filterType.label !== 'TODOS')) {
        data2 = this.data.filter(m => m.storage_id === this.storage.value && m.type_id === this.filterType.label)
      } else if (this.filterType !== null && this.filterType.label !== 'TODOS') {
        data2 = this.data.filter(m => m.type_id === this.filterType.label)
      } else if (this.storage !== null && this.storage.label !== 'TODOS') {
        data2 = this.data.filter(m => m.storage_id === this.storage.value)
      }
      return data2
    },
    haspermissionv1 () {
      return this.$store.getters['users/roles'].includes(1)
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
  methods: {
    invertDate (date) {
      if (date !== null) {
        var info = date.split('/').reverse().join('-')
      }
      return info
    },
    deleteSelectedRow (id) {
      this.$q.dialog({
        title: 'Confirmación',
        message: '¿Desea eliminar este Movimiento?',
        persistent: true,
        ok: { label: 'Aceptar', color: 'positive' },
        cancel: { label: 'Cancelar', color: 'negative' }
      }).onOk(() => {
        this.$q.loading.show()
        api.delete(`/movements/${id}`).then(({ data }) => {
          this.$q.notify({
            message: data.message.content,
            position: 'top',
            color: (data.result ? 'positive' : 'warning'),
            icon: (data.result ? 'thumb_up' : 'close')
          })
          if (data.result) {
            this.$q.loading.hide()
            this.fetchFromServer()
          } else {
            this.$q.loading.hide()
          }
        })
      }).onCancel(() => {})
    },
    editSelectedRow (id) {
      this.$router.push(`/movements/${id}`)
    },
    fetchFromServer () {
      this.$q.loading.show()
      api.get('/movements/').then(({ data }) => {
        this.data = data.movements
        for (var i = 0; i < this.data.length; i++) {
          if (this.data[i].type_id === 1) {
            this.data[i].type_id = 'ENTRADA'
            this.data[i].color = 'positive'
          } else if (this.data[i].type_id === 2) {
            this.data[i].type_id = 'SALIDA'
            this.data[i].color = 'negative'
          } else if (this.data[i].type_id === 3) {
            this.data[i].type_id = 'INVENTARIO FÍSICO'
            this.data[i].color = 'light-blue'
          } else if (this.data[i].type_id === 4) {
            this.data[i].type_id = 'TRASPASO (ENTRADA)'
            this.data[i].color = 'positive'
          } else if (this.data[i].type_id === 5) {
            this.data[i].type_id = 'TRASPASO (SALIDA)'
            this.data[i].color = 'negative'
          } else if (this.data[i].type_id === 6) {
            this.data[i].type_id = 'MERMA'
            this.data[i].color = 'purple'
          }
        }
      })
      api.get('/storages/options').then(({ data }) => {
        this.storageOptions = data.options
        this.storageOptions.push({ label: 'TODOS', value: 0 })
      })
      this.$q.loading.hide()
    },
    cancelSelectedRow (id) {
      this.$q.dialog({
        title: 'Confirmación',
        message: '¿Desea cancelar este Movimiento?',
        persistent: true,
        ok: { label: 'Aceptar', color: 'positive' },
        cancel: { label: 'Cancelar', color: 'negative' }
      }).onOk(() => {
        this.$q.loading.show()
        api.put(`/movements/cancel/${id}`).then(({ data }) => {
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
      }).onCancel(() => {})
    }
  }
}
</script>

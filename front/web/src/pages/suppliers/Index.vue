<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-6">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Proveedores" />
            </q-breadcrumbs>
          </div>
        </div>
        <div class="col-xs-12 col-md-6 pull-right">
          <div class="q-pa-sm q-gutter-sm">
            <q-btn v-if="roleId !== 20" color="negative" icon="fas fa-file-pdf"  @click="generatePdf()">
            <q-tooltip content-class="bg-negative">Descargar PDF</q-tooltip>
            </q-btn>
            <q-btn v-if="roleId !== 20" color="positive" icon="fas fa-file-csv" @click="generateCsv()">
              <q-tooltip content-class="bg-green">Descargar CSV</q-tooltip>
            </q-btn>
            <q-btn v-if="roleId !== 20" class="bg-primary" style="color: white" icon="add" label="Nuevo" @click.native="$router.push('/suppliers/new')" />
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
            :data="filteredSuppliers"
            :columns="columns"
            row-key="serial"
            :pagination.sync="pagination"
            :filter="filter"
            @request="qTableRequest"
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
                <q-td key="serial" flat @click.native="editSelectedRow(props.row.id)" class="text-primary cursor-pointer" style=" text-align: center; text-decoration: underline;" :props="props">{{ props.row.serial }}</q-td>
                <q-td key="name" style="text-align: left;" :props="props">{{ props.row.name }}</q-td>
                <q-td key="rfc" style="text-align: center;" :props="props">{{ props.row.rfc }}</q-td>
                <q-td key="email" style="text-align: left;" :props="props">{{ props.row.email }}</q-td>
                <q-td key="contact_phone" style="text-align: center;" :props="props">{{ props.row.contact_phone }}</q-td>
                <q-td key="contact_phone_two" style="text-align: center;" :props="props">{{ props.row.contact_phone_two }}</q-td>
                <q-td key="street" style="text-align: left;" :props="props">{{ props.row.street }}</q-td>
                <q-td key="outdoor_number" style="text-align: center;" :props="props">{{ props.row.outdoor_number }}</q-td>
                <q-td key="state" style="text-align: left;" :props="props">{{ props.row.state }}</q-td>
                <q-td key="zip_code" style="text-align: center;" :props="props">{{ props.row.zip_code }}</q-td>
                <q-td key="credit_days" style="text-align: center;" :props="props">{{ props.row.credit_days }}</q-td>
                <q-td key="currency" style="text-align: center;" :props="props">{{ props.row.currency }}</q-td>
                <q-td key="country" style="text-align: center;" :props="props">{{ props.row.country }}</q-td>
                <q-td key="active" :props="props">
                  <q-chip square dense :color="props.row.active ? 'positive' : 'negative'" text-color="white">
                    {{ (props.row.active ? 'ACTIVO' : 'INACTIVO') }}
                  </q-chip>
                </q-td>
                <q-td key="actions" :props="props" v-if="roleId !== 20">
                  <q-btn color="primary" icon="photo" flat @click.native="editPhoto(props.row.id)" size="10px">
                    <q-tooltip content-class="bg-primary">Fotografía</q-tooltip>
                  </q-btn>
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
   <q-dialog v-model="photoModal" persistent>
      <q-card>
        <q-card-section class="row">
          <div class="col-xs-12 col-sm-10 text-h6">Foto: {{ photoCode }}</div>
          <q-btn class="col-xs-12 col-sm-2 pull-right" icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-card-section>
          <q-uploader
            accept=".jpg, image/*"
            method="POST"
            ref="photoRef"
            hide-upload-btn
            :factory="factoryFn"
          />
        </q-card-section>
        <q-card-section>
          <q-img
            :src="photoUrl"
            spinner-color="white"
          />
        </q-card-section>
        <q-card-actions align="right" class="text-primary">
          <q-btn flat label="Subir foto" @click="uploadPhoto()" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import api from '../../commons/api.js'

export default {
  name: 'IndexSuppliers',
  data () {
    return {
      pagination: {
        sortBy: 'serial',
        descending: true,
        page: 1,
        rowsNumber: 0,
        rowsPerPage: 25
      },
      columns: [
        { name: 'serial', align: 'center', label: 'CÓDIGO', field: 'serial', sortable: true },
        { name: 'name', align: 'center', label: 'RAZÓN SOCIAL', field: 'name', sortable: true },
        { name: 'rfc', align: 'center', label: 'RFC', field: 'rfc', sortable: true },
        { name: 'email', align: 'center', label: 'EMAIL', field: 'email', sortable: true },
        { name: 'contact_phone', align: 'center', label: 'TEL. OFICINA', field: 'contact_phone', sortable: true },
        { name: 'contact_phone_two', align: 'center', label: 'TEL. CELULAR', field: 'contact_phone_two', sortable: true },
        { name: 'street', align: 'center', label: 'CALLE', field: 'street', sortable: true },
        { name: 'outdoor_number', align: 'center', label: 'NUMERO EXT', field: 'outdoor_number', sortable: true },
        { name: 'state', align: 'center', label: 'ESTADO', field: 'state', sortable: true },
        { name: 'zip_code', align: 'center', label: 'CP', field: 'zip_code', sortable: true },
        { name: 'credit_days', align: 'center', label: 'DÍAS DE CREDITO', field: 'credit_days', sortable: true },
        { name: 'currency', align: 'center', label: 'MONEDA', field: 'currency', sortable: true },
        { name: 'country', align: 'center', label: 'PAÍS', field: 'country', sortable: true },
        { name: 'active', align: 'center', label: 'ESTATUS', field: 'active', sortable: true },
        { name: 'actions', align: 'center', label: 'ACCIONES', field: 'actions', sortable: false }
      ],
      data: [],
      filter: '',
      branchOffice: { value: null, label: 'TODOS' },
      branchOfficeOptions: [],
      serverUrl: process.env.API,
      customerId: null,
      photoModal: false,
      photoUrl: null,
      photoCode: null
    }
  },
  beforeRouteEnter (to, from, next) {
    next(vm => {
      const propiedades = vm.$store.getters['users/rol']
      console.log(propiedades)
      if (propiedades === 1 || propiedades === 3 || propiedades === 7 || propiedades === 2 || propiedades === 20 || propiedades === 4 || propiedades === 27 || propiedades === 22 || propiedades === 17 || propiedades === 28) {
        next()
      } else {
        next('/')
      }
    })
  },
  /* beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(22)) {
      this.$router.push('/')
    }
  }, */
  mounted () {
    this.fetchFromServer()
    this.getBranchOffices()
  },
  computed: {
    roleId () {
      const user = this.$store.getters['users/rol']
      return parseInt(user)
    },
    filteredSuppliers () {
      const suppliers = this.data.filter(supplier => supplier.active === true)
      if (this.branchOffice != null && this.branchOffice.value != null) {
        return suppliers.filter(supplier => Number(supplier.branch_id) === Number(this.branchOffice.value))
      }
      return suppliers
    }
  },
  methods: {
    generateCsv () {
      let branchOfficeId = null
      if (this.branchOffice) { branchOfficeId = this.branchOffice.value }
      const uri = process.env.API + `suppliers/csv/${branchOfficeId}`
      window.open(uri, '_blank')
    },
    generatePdf () {
      const uri = process.env.API + 'suppliers/pdf/'
      window.open(uri, '_blank')
    },
    fetchFromServer () {
      this.$q.loading.show()
      this.qTableRequest({
        pagination: this.pagination,
        filter: this.filter
      })
      // api.get('/suppliers/getAll').then(({ data }) => {
      //   this.data = data.suppliers
      // })
    },
    async qTableRequest (props) {
      this.pagination = props.pagination
      this.filter = props.filter
      // this.data = []
      const params = []
      params.branch = this.branchOffice.value
      params.pagination = this.pagination
      params.filter = this.filter
      await api.post('/suppliers/pag', params).then(({ data }) => {
        this.$q.loading.hide()
        this.data = data.suppliers
        this.pagination.rowsNumber = data.suppliersCount
      }).catch(error => error)
    },
    getBranchOffices () {
      api.get('branch-offices/options').then(({ data }) => {
        this.branchOfficeOptions = data.options
        this.branchOfficeOptions.unshift({ value: null, label: 'TODOS' })
      })
    },
    editSelectedRow (id) {
      this.$router.push(`/suppliers/${id}`)
    },
    deleteSelectedRow (id) {
      this.$q.dialog({
        title: 'Confirmación',
        message: '¿Desea eliminar este Proveedor?',
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
        api.delete(`/suppliers/${id}`).then(({ data }) => {
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
    },
    factoryFn (files) {
      this.$q.loading.show()
      const formData = new FormData()
      formData.append('fileReg', files[0])
      formData.append('id', this.supplierId)
      api.file('/suppliers/photo/', formData).then(({ data }) => {
        this.fetchFromServer()
        this.$showNotify(data.message.content, data.result ? 'positive' : 'negative')
        this.photoModal = false
        this.$q.loading.hide()
      })
    },
    editPhoto (id) {
      const supplier = this.data.filter(p => Number(p.id) === Number(id))[0]
      this.photoUrl = (supplier.photo != null ? this.serverUrl + 'assets/images/suppliers/' + supplier.photo : null)
      this.supplierId = supplier.id
      this.photoCode = supplier.serial
      this.photoModal = true
    },
    uploadPhoto () {
      this.$refs.photoRef.upload()
    }
  }
}
</script>

<style>
</style>

<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-6">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Clientes" />
            </q-breadcrumbs>
          </div>
        </div>
        <div class="col-xs-12 col-md-6  pull-right">
          <div class="q-pa-sm q-gutter-sm">
            <q-btn v-if="(roleId !== 20 && roleId !== 4 && roleId !== 22  && roleId !== 28 && roleId !== 29 && roleId !== 27)" color="positive" icon="fas fa-file-csv" label="Descargar CSV" @click="generateCsv()"/>
            <q-btn v-if="(roleId !== 20 && roleId !== 4 && roleId !== 22  && roleId !== 28 && roleId !== 29 && roleId !== 27)" color="positive" icon="cloud_upload" label="CARGA MASIVA CLIENTES" @click="openUploadFileModal()"/>
            <q-btn v-if="(roleId !== 20 && roleId !== 29 && roleId !== 27)" class="bg-primary" style="color: white" icon="add" label="Nuevo" @click.native="$router.push('/customers/new')" />
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
            :data="filteredCustomers"
            :columns="roleId !== 27 ? columns : columnsMostrador"
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
              <q-tr :props="props" :style="props.row.yesornotdatafs === 'No' ? 'background: red; color: white;' : 'background: white;'">
                <q-td key="serial" class="text-primary cursor-pointer" style=" text-align: center; text-decoration: underline;" :props="props" flat @click.native="editSelectedRow(props.row.id)">{{ props.row.serial }}</q-td>
                <q-td key="name" style="text-align: left;" :props="props">{{ props.row.name }}</q-td>
                <q-td key="email" style="text-align: left;" :props="props">{{ props.row.email }}</q-td>
                <q-td key="contact_phone" style="text-align: center;" :props="props">{{ props.row.contact_phone }}</q-td>
                <q-td key="price_list" style="text-align: center;" :props="props">{{ props.row.price_list }}</q-td>
                <q-td key="country" style="text-align: left;" :props="props">{{ props.row.country }}</q-td>
                <!-- <q-td key="used_coin" style="text-align: center;" :props="props">{{ props.row.used_coin }}</q-td> -->
                <q-td key="active" :props="props">
                  <q-chip square dense :color="props.row.active ? 'positive' : 'negative'" text-color="white">
                    {{ (props.row.active ? 'ACTIVO' : 'INACTIVO') }}
                  </q-chip>
                </q-td>
                <q-td key="actions" :props="props" v-if="roleId !== 29 && roleId !== 27">
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
    <q-dialog v-model="documentFileModal" persistent>
        <q-card>
          <q-card-section class="row">
            <div class="col-xs-12 col-sm-10 text-h6">Archivo: {{ documentName }}</div>
            <q-btn class="col-xs-12 col-sm-2 pull-right" icon="close" flat round dense v-close-popup />
          </q-card-section>
          <q-card-section>
            <q-uploader
              :url="fileDocumentUrl"
              :headers="[{name: 'Authorization', value: token}]"
              method="POST"
              ref="fileDocumentRef"
              hide-upload-btn
              @uploaded="afterUploadDocumentFile"
            />
          </q-card-section>
          <q-card-actions align="right" class="text-secondary">
            <q-btn flat label="Subir archivo" @click="uploadDocumentFile()" />
          </q-card-actions>
        </q-card>
      </q-dialog>
  </q-page>
</template>

<script>
import api from '../../commons/api.js'

export default {
  name: 'IndexCustomers',
  data () {
    return {
      documentName: null,
      documentFileModal: false,
      pagination: {
        sortBy: 'serial',
        descending: false,
        page: 1,
        rowsNumber: 0,
        rowsPerPage: 25
      },
      columns: [
        { name: 'serial', align: 'center', label: 'CÓDIGO', field: 'serial', sortable: true },
        { name: 'name', align: 'center', label: 'CLIENTE', field: 'name', sortable: true },
        { name: 'email', align: 'center', label: 'EMAIL', field: 'email', sortable: true },
        { name: 'contact_phone', align: 'center', label: 'TELÉFONO', field: 'contact_phone', sortable: true },
        { name: 'price_list', align: 'center', label: 'PRECIO DE LISTA', field: 'price_list', sortable: true },
        { name: 'country', align: 'center', label: 'PAÍS', field: 'country', sortable: true },
        // { name: 'used_coin', align: 'center', label: 'MONEDA', field: 'used_coin', sortable: true },
        { name: 'active', align: 'center', label: 'ESTATUS', field: 'active', sortable: true },
        { name: 'actions', align: 'center', label: 'ACCIONES', field: 'actions', sortable: false }
      ],
      columnsMostrador: [
        { name: 'serial', align: 'center', label: 'CÓDIGO', field: 'serial', sortable: true },
        { name: 'name', align: 'center', label: 'CLIENTE', field: 'name', sortable: true },
        { name: 'rfc', align: 'center', label: 'RFC', field: 'rfc', sortable: true },
        { name: 'email', align: 'center', label: 'EMAIL', field: 'email', sortable: true },
        { name: 'contact_phone', align: 'center', label: 'TELÉFONO', field: 'contact_phone', sortable: true },
        { name: 'price_list', align: 'center', label: 'PRECIO DE LISTA', field: 'price_list', sortable: true },
        { name: 'country', align: 'center', label: 'PAÍS', field: 'country', sortable: true },
        // { name: 'used_coin', align: 'center', label: 'MONEDA', field: 'used_coin', sortable: true },
        { name: 'active', align: 'center', label: 'ESTATUS', field: 'active', sortable: true }
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
  computed: {
    roleId () {
      const user = this.$store.getters['users/rol']
      return parseInt(user)
    },
    token () {
      const token = 'Bearer ' + localStorage.getItem('JWT')
      return token
    },
    filteredCustomers () {
      const customers = this.data.filter(customer => customer.active === true || customer.active === false)
      if (this.branchOffice != null && this.branchOffice.value != null) {
        return customers.filter(customer => Number(customer.branch_id) === Number(this.branchOffice.value))
      }
      return customers
    }
  },
  beforeRouteEnter (to, from, next) {
    next(vm => {
      const propiedades = vm.$store.getters['users/rol']
      console.log(propiedades)
      if (propiedades === 1 || propiedades === 3 || propiedades === 7 || propiedades === 2 || propiedades === 20 || propiedades === 4 || propiedades === 27 || propiedades === 17 || propiedades === 22 || propiedades === 28 || propiedades === 29) {
        next()
      } else {
        next('/')
      }
    })
  },
  /* beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(4) && !this.$store.getters['users/roles'].includes(20)) {
      this.$router.push('/')
    }
  }, */
  mounted () {
    this.fetchFromServer()
    this.getBranchOffices()
  },
  methods: {
    afterUploadDocumentFile (response) {
      console.log(response)
      const data = JSON.parse(response.xhr.response)
      this.$q.notify({
        message: data.message.content,
        position: 'top',
        color: (data.result ? 'positive' : 'warning')
      })
      if (data.result) {
        this.fetchFromServer()
        this.documentFileModal = false
      }
    },
    openUploadFileModal () {
      this.documentFileModal = true
    },
    generateCsv () {
      let branchOfficeId = null
      if (this.branchOffice) { branchOfficeId = this.branchOffice.value }
      const uri = process.env.API + `customers/csv/${branchOfficeId}`
      window.open(uri, '_blank')
    },
    fetchFromServer () {
      this.$q.loading.show()
      this.qTableRequest({
        pagination: this.pagination,
        filter: this.filter
      })
      // api.get('/customers/getAll').then(({ data }) => {
      //   this.data = data.customers
      //   this.$q.loading.hide()
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
      await api.post('/customers/pag', params).then(({ data }) => {
        this.$q.loading.hide()
        this.data = data.customers
        this.pagination.rowsNumber = data.customersCount
      }).catch(error => error)
    },
    getBranchOffices () {
      api.get('branch-offices/options').then(({ data }) => {
        this.branchOfficeOptions = data.options
        this.branchOfficeOptions.unshift({ value: null, label: 'TODOS' })
      })
    },
    factoryFn (files) {
      this.$q.loading.show()
      const formData = new FormData()
      formData.append('fileReg', files[0])
      formData.append('id', this.customerId)
      api.file('/customers/photo/', formData).then(({ data }) => {
        this.fetchFromServer()
        this.$showNotify(data.message.content, data.result ? 'positive' : 'negative')
        this.photoModal = false
        this.$q.loading.hide()
      })
    },
    editPhoto (id) {
      const customer = this.data.filter(p => Number(p.id) === Number(id))[0]
      this.photoUrl = (customer.photo != null ? this.serverUrl + 'assets/images/customers/' + customer.photo : null)
      this.customerId = customer.id
      this.photoCode = customer.serial
      this.photoModal = true
    },
    uploadPhoto () {
      this.$refs.photoRef.upload()
    },
    editSelectedRow (id) {
      if (this.roleId !== 27) {
        this.$router.push(`/customers/${id}`)
      }
    },
    deleteSelectedRow (id) {
      this.$q.dialog({
        title: 'Confirmación',
        message: '¿Desea eliminar este Cliente?',
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
        api.delete(`/customers/${id}`).then(({ data }) => {
          this.$q.notify({
            message: data.message.content,
            position: 'top',
            icon: (data.result ? 'thumb_up' : 'close'),
            color: (data.result ? 'positive' : 'warning')
          })
          if (data.result) {
            this.fetchFromServer()
          }
        })
      }).onCancel(() => {})
    },
    uploadDocumentFile () {
      this.$refs.fileDocumentRef.upload()
    },
    fileDocumentUrl () {
      // const id = this.$route.params.id
      return `${process.env.API}customers/file`
    }
  }
}
</script>

<style>
</style>

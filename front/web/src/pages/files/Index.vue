<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-8">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el :label="repository.name" />
            </q-breadcrumbs>
          </div>
        </div>
        <div class="col-xs-6 col-md-4 pull-right">
          <div class="q-pa-sm q-gutter-sm">
            <q-btn color="primary" icon="add" label="Nuevo" @click="modal = true" />
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
                <q-td key="description" style="text-align: left; width: 35%;" :props="props">{{ props.row.description }}</q-td>
                <q-td key="name" style="text-align: left; width: 25%;" :props="props">{{ props.row.name }}</q-td>
                <q-td key="date" style="text-align: center; width: 10%;" :props="props">{{ props.row.date }}</q-td>
                <q-td key="ext" style="text-align: left; width: 5%;" :props="props">{{ props.row.ext }}</q-td>
                <q-td key="size" style="text-align: right; width: 5%;" :props="props">{{ props.row.size }}</q-td>
                <q-td key="actions" style="width: 20%;" :props="props">
                  <q-btn color="green" icon="fas fa-download" flat @click.native="getFile(props.row)" size="10px">
                    <q-tooltip content-class="bg-green">Descargar</q-tooltip>
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
    <q-dialog v-model="modal" persistent>
     <q-card style="min-width: 50%">
      <q-card-section>
        <div class="text-h6">Agregar Archivo</div>
      </q-card-section>
      <q-card-section>
        <div class="row q-col-gutter-xs">
          <div class="col-xs-12 col-sm-4 pull-right">
                      <q-file
                        v-model="info.fields.file"
                        :error="$v.info.fields.file.$error"
                        color="dark"
                        bg-color="secondary"
                        filled
                        :rules="fileRules"
                        label="Archivo"
                      />
          </div>
          <div class="col-xs-12 col-sm-8">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="info.fields.description"
                :error="$v.info.fields.description.$error"
                label="Descripción"
                :rules="descriptionRules"
                @input="v => { info.fields.description = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-signature" />
                </template>
              </q-input>
            </div>
        </div>
          </q-card-section>
          <q-card-actions align="right" class="text-primary">
            <q-btn label="Cancelar" color="red" v-close-popup @click.native="closeModal()"/>
            <q-btn label="Guardar" color="green" @click.native="factoryFn()" />
          </q-card-actions>
        </q-card>
        </q-dialog>

  </q-page>
</template>

<script>
import api from '../../commons/api.js'
const { required, maxLength } = require('vuelidate/lib/validators')
export default {
  name: 'Index',
  validations: {
    info: {
      fields: {
        description: { required, maxLength: maxLength(255) },
        file: { required }
      }
    }
  },
  data () {
    return {
      modal: false,
      info: {
        fields: {
          description: null,
          file: null
        }
      },
      pagination: {
        sortBy: 'name',
        descending: false,
        rowsPerPage: 25
      },
      file: null,
      columns: [
        { name: 'description', align: 'center', label: 'DESCRIPCIÓN', field: 'description', style: 'width: 35%', sortable: true },
        { name: 'name', align: 'center', label: 'NOMBRE', field: 'name', style: 'width: 25%', sortable: true },
        { name: 'date', align: 'center', label: 'FECHA', field: 'date', style: 'width: 10%', sortable: true },
        { name: 'ext', align: 'center', label: 'EXT', field: 'ext', style: 'width: 5%', sortable: true },
        { name: 'size', align: 'center', label: 'TAMAÑO (MB)', field: 'size', style: 'width: 5%', sortable: true },
        { name: 'actions', align: 'center', label: 'ACCIONES', field: 'actions', style: 'width: 20%', sortable: false }
      ],
      data: [],
      repository: {
        name: 'Archivos'
      }
    }
  },
  computed: {
    descriptionRules (val) {
      return [
        val => (this.$v.info.fields.description.required) || 'El campo Descripción es requerido.',
        val => (this.$v.info.fields.description.maxLength) || 'El campo Descripción no debe exceder los 255 dígitos.'
      ]
    },
    fileRules (val) {
      return [
        val => (this.$v.info.fields.file.required) || 'El campo Archivo es requerido.'
      ]
    }
  },
  beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(7)) {
      this.$router.push('/')
    }
  },
  mounted () {
    if (this.$store.getters['users/roles'].includes(1) || this.$store.getters['users/roles'].includes(3) || this.$store.getters['users/roles'].includes(7)) {
      this.fetchFromServer()
    }
  },
  beforeRouteUpdate  (to, from, next) {
    if (to.path.includes('files') && to.params.id > 0) {
      this.fetchFromServer(to.params.id)
    }
    next()
  },
  methods: {
    fetchFromServer (id) {
      this.$q.loading.show()
      if (!id) {
        id = this.$route.params.id
      }
      api.get(`/repositories/${id}`).then(({ data }) => {
        api.get(`/dirFiles/${id}`).then(({ data }) => {
          this.data = data.info
        })
        this.repository = data.info
        this.$q.loading.hide()
      })
    },
    factoryFn () {
      this.$v.info.fields.$reset()
      this.$v.info.fields.$touch()
      if (this.$v.info.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const formData = new FormData()
      formData.append('fileReg', this.info.fields.file)
      formData.append('id', this.$route.params.id)
      formData.append('description', this.info.fields.description)
      this.$q.loading.show()
      api.file('/dirFiles/', formData).then(({ data }) => {
        this.fetchFromServer()
        this.$showNotify(data.message.content, data.result ? 'positive' : 'negative')
        this.closeModal()
        this.$q.loading.hide()
      })
    },
    closeModal () {
      this.$v.info.fields.$reset()
      this.info.fields.file = null
      this.info.fields.description = null
      this.modal = false
    },
    getFile (row) {
      this.$q.loading.show()
      api.fileDownload(`/dirFiles/getfile/${row.id}`).then(({ data }) => {
        this.$q.loading.hide()
        const url = window.URL.createObjectURL(new Blob([data], { type: row.type }))
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', row.name) // or any other extension
        document.body.appendChild(link)
        link.click()
      })
    },
    deleteSelectedRow (id) {
      this.$q.loading.show()
      api.delete(`/dirFiles/${id}`).then(({ data }) => {
        this.$showNotify(data.message.content, data.result ? 'positive' : 'negative')
        this.fetchFromServer()
        this.$q.loading.hide()
      })
    }
  }
}
</script>

<style>
</style>

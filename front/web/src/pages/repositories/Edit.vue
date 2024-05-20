<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-9">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Dirección" to="/repositories" />
              <q-breadcrumbs-el label="Editar Dirección" v-text= "info.fields.name"/>
            </q-breadcrumbs>
          </div>
        </div>
        <!-- <div class="col-sm-3">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="right">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Categorías" to="/categories" />
              <q-breadcrumbs-el label="Editar" />
            </q-breadcrumbs>
          </div>
        </div> -->
      </div>
    </div>

    <div class="q-pa-md bg-grey-3">
      <div class="row bg-white border-panel">
        <div class="col q-pa-md">
          <div class="row q-mb-sm" style="visibility: hidden;">
            <div class="col-sm-1 offset-11 pull-right">
              <q-btn color="primary" label="Editar" />
            </div>
          </div>

          <div class="row q-col-gutter-xs">
            <div class="col-xs-12 col-sm-4">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="info.fields.name"
                :error="$v.info.fields.name.$error"
                label="Nombre"
                :rules="nameRules"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-signature" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-4">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="info.fields.icon"
                :error="$v.info.fields.icon.$error"
                label="Icono"
                :rules="iconRules"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-signature" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-4">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="info.fields.sequence"
                :error="$v.info.fields.sequence.$error"
                label="Orden"
                :rules="sequenceRules"
                @input="v => { info.fields.sequence = v.replace(/[^0-9\\.]/g, '') }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-signature" />
                </template>
              </q-input>
            </div>
          </div>

          <div class="row q-mb-sm q-mt-md">
            <div class="col-xs-12 col-sm-2 offset-sm-10 pull-right">
              <q-btn color="positive" icon="save" label="Actualizar" @click="edit()" />
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="q-pa-md bg-grey-3">
      <div class="row bg-white border-panel">
        <div class="col q-pa-md">
          <div class="row q-mb-sm q-mt-md">
            <div class="col-xs-12 col-sm-2 offset-sm-10 pull-right">
              <q-btn color="positive" icon="save" label="Agregar" @click="modalOrders = true" />
            </div>
          </div>
            <q-table
              flat
              bordered
              :data="data"
              :columns="columns"
              row-key="code"
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
                  <q-td key="name" style="text-align: left;" :props="props">{{ props.row.name }}</q-td>
                  <q-td key="icon" style="text-align: left;" :props="props">{{ props.row.icon }}</q-td>
                  <q-td key="sequence" style="text-align: right;" :props="props">{{ props.row.sequence }}</q-td>
                  <q-td key="actions" style="width: 18%;" :props="props" class="pull-left">
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
       <q-dialog v-model="modalOrders" persistent>
                <q-card style="min-width: 30%">
                  <q-card-section>
                    <div class="text-h6">Agregar</div>
                  </q-card-section>
      <q-card-section>
        <div class="row q-col-gutter-xs">
          <div class="col-xs-12 col-sm-6">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="child.fields.name"
                :error="$v.child.fields.name.$error"
                label="Nombre"
                :rules="nameRules"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-signature" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-6">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="child.fields.icon"
                :error="$v.child.fields.icon.$error"
                label="Icono"
                :rules="iconRules"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-signature" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-6">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="child.fields.sequence"
                :error="$v.child.fields.sequence.$error"
                label="Orden"
                @input="v => { child.fields.sequence = v.replace(/[^0-9\\.]/g, '') }"
                :rules="sequenceRules"
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
            <q-btn label="Guardar" color="green" @click.native="create()" />
          </q-card-actions>
        </q-card>
        </q-dialog>
  </q-page>
</template>

<script>
import api from '../../commons/api.js'
const { required, maxLength, decimal } = require('vuelidate/lib/validators')

export default {
  name: 'Edit',
  validations: {
    info: {
      fields: {
        name: { required, maxLength: maxLength(50) },
        icon: { required, maxLength: maxLength(50) },
        sequence: { decimal, required, maxLength: maxLength(50) }
      }
    },
    child: {
      fields: {
        name: { required, maxLength: maxLength(50) },
        icon: { required, maxLength: maxLength(50) },
        sequence: { decimal, required, maxLength: maxLength(50) }
      }
    }
  },
  data () {
    return {
      modalOrders: false,
      info: {
        fields: {
          id: null,
          name: null,
          icon: null,
          sequence: null
        }
      },
      child: {
        fields: {
          id: null,
          name: null,
          icon: null,
          sequence: null,
          route: null
        }
      },
      pagination: {
        sortBy: 'name',
        descending: false,
        rowsPerPage: 25
      },
      columns: [
        { name: 'name', align: 'center', label: 'NOMBRE', field: 'name', sortable: true },
        { name: 'icon', align: 'center', label: 'ICONO', field: 'icon', sortable: true },
        { name: 'sequence', align: 'center', label: 'ORDEN', field: 'sequence', sortable: true },
        { name: 'actions', align: 'center', label: 'ACCIONES', field: 'actions', style: 'width: 18%', sortable: false }
      ],
      data: [],
      filter: ''
    }
  },
  computed: {
    nameRules (val) {
      return [
        val => (this.$v.info.fields.name.required) || 'El campo Nombre es requerido.',
        val => (this.$v.info.fields.name.maxLength) || 'El campo Nombre no debe exceder los 50 dígitos.'
      ]
    },
    iconRules (val) {
      return [
        val => (this.$v.info.fields.name.required) || 'El campo Icono es requerido.',
        val => (this.$v.info.fields.name.maxLength) || 'El campo Icono no debe exceder los 50 dígitos.'
      ]
    },
    sequenceRules (val) {
      return [
        val => (this.$v.info.fields.name.required) || 'El campo Orden es requerido.',
        val => (this.$v.info.fields.name.maxLength) || 'El campo Orden no debe exceder los 50 dígitos.'
      ]
    }
  },
  beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1)) {
      this.$router.push('/')
    }
  },
  created () {
    this.fetchFromServer()
  },
  methods: {
    fetchFromServer () {
      this.$q.loading.show()
      const id = this.$route.params.id
      api.get(`/repositories/${id}`).then(({ data }) => {
        if (!data.info) {
          this.$router.push('/repositories')
        } else {
          this.info.fields = data.info
          api.get(`/repositories/getByParent/${id}`).then(({ data }) => {
            this.data = data.info
            this.$q.loading.hide()
          })
        }
      })
    },
    updateCategoryFields () {
      this.$v.info.fields.$reset()
      this.$v.info.fields.$touch()
    },
    editSelectedRow (id) {
      this.$q.loading.show()
      api.get(`/repositories/${id}`).then(({ data }) => {
        if (!data.info) {
          this.$router.push('/repositories')
        } else {
          this.child.fields = data.info
          this.modalOrders = true
          this.$q.loading.hide()
        }
      })
    },
    edit () {
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
      const params = { ...this.info.fields }
      api.put(`/repositories/${params.id}`, params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.$router.push('/repositories')
        } else {
          this.$q.loading.hide()
        }
      })
    },
    create () {
      this.$v.child.fields.$reset()
      this.$v.child.fields.$touch()
      if (this.$v.child.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      this.child.fields.parent_id = this.$route.params.id
      const params = { ...this.child.fields }
      if (this.child.fields.id > 0) {
        this.updateRep(params)
      } else {
        this.createRep(params)
      }
    },
    createRep (params) {
      api.post('/repositories/', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.closeModal()
          this.fetchFromServer()
        } else {
          this.$q.loading.hide()
        }
      })
    },
    updateRep (params) {
      api.put(`/repositories/${params.id}`, params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.closeModal()
          this.fetchFromServer()
        } else {
          this.$q.loading.hide()
        }
      })
    },
    closeModal () {
      this.$v.child.fields.$reset()
      this.child.fields.id = null
      this.child.fields.name = null
      this.child.fields.route = null
      this.child.fields.sequence = null
      this.child.fields.icon = null
      this.modalOrders = false
    },
    deleteSelectedRow (id) {
      this.$q.dialog({
        title: 'Confirmación',
        message: '¿Desea eliminar esta sub dirección?',
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
        api.delete(`/repositories/${id}`).then(({ data }) => {
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

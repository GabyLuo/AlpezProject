<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-9">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Tipos de Almacén" to="/storage-types" />
              <q-breadcrumbs-el label="Editar" v-text="storageType.fields.name" />
            </q-breadcrumbs>
          </div>
        </div>
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
            <div class="col-xs-12">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="storageType.fields.name"
                :error="$v.storageType.fields.name.$error"
                label="Nombre"
                :rules="nameRules"
                @input="v => { storageType.fields.name = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-signature" />
                </template>
              </q-input>
            </div>
          </div>

          <div class="row q-mb-sm q-mt-md">
            <div class="col-xs-12 col-sm-2 offset-sm-10 pull-right">
              <q-btn color="positive" icon="save" label="Actualizar" @click="updateStorageType()" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </q-page>
</template>

<script>
import api from '../../commons/api.js'
const { required, maxLength } = require('vuelidate/lib/validators')

export default {
  name: 'EditStorage',
  validations: {
    storageType: {
      fields: {
        name: { required, maxLength: maxLength(100) }
      }
    }
  },
  data () {
    return {
      storageType: {
        fields: {
          id: null,
          name: null
        }
      }
    }
  },
  computed: {
    nameRules (val) {
      return [
        val => (this.$v.storageType.fields.name.required) || 'El campo Nombre es requerido.',
        val => (this.$v.storageType.fields.name.maxLength) || 'El campo Nombre no debe exceder los 100 dígitos.'
      ]
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
      if (propiedades === 1 || propiedades === 3 || propiedades === 7 || propiedades === 2 || propiedades === 20 || propiedades === 4 || propiedades === 27) {
        next()
      } else {
        next('/')
      }
    })
  },
  created () {
    this.fetchFromServer()
  },
  methods: {
    fetchFromServer () {
      this.$q.loading.show()
      const id = this.$route.params.id
      api.get(`/storage-types/${id}`).then(({ data }) => {
        if (!data.storageType) {
          this.$router.push('/storage-types')
        } else {
          this.storageType.fields = data.storageType
        }
        this.$q.loading.hide()
      })
    },
    updateStorageType () {
      this.$v.storageType.fields.$reset()
      this.$v.storageType.fields.$touch()
      if (this.$v.storageType.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = { ...this.storageType.fields }
      api.put(`/storage-types/${params.id}`, params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.$router.push('/storage-types')
        } else {
          this.$q.loading.hide()
        }
      })
    }
  }
}
</script>

<style>
</style>

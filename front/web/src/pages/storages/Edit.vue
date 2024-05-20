<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-9">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Almacenes" to="/storages" />
              <q-breadcrumbs-el label="Editar" v-text= "storage.fields.name" />
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
            <div class="col-xs-12 col-sm-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="storage.fields.code"
                :error="$v.storage.fields.code.$error"
                label="Código"
                :rules="codeRules"
                @input="v => { storage.fields.code = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fingerprint" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="storage.fields.name"
                :error="$v.storage.fields.name.$error"
                label="Nombre"
                :rules="nameRules"
                @input="v => { storage.fields.name = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-signature" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="storage.fields.branchOffice"
                :options="branchOfficeOptions"
                label="Estación"
                :rules="branchOfficeRules"
                @input="updateStorageFields"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-store-alt" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="storage.fields.storageType"
                :options="storageTypeOptions"
                label="Tipo de Almacén"
                :rules="storageTypeRules"
                @input="updateStorageFields"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-store-alt" />
                </template>
              </q-select>
            </div>
            <!-- DIRECCION -->
            <div class="col-xs-12 col-sm-3">
                        <q-input
                            color="dark"
                            bg-color="secondary"
                            filled
                            v-model="storage.fields.street"
                            @input="v => { storage.fields.street = v.toUpperCase() }"
                            label="Calle"
                            :error="$v.storage.fields.street.$error"
                            :rules="ruleStreet">
                            <template v-slot:prepend>
                                <q-icon name="fas fa-road" />
                            </template>
                        </q-input>
                  </div>
                  <div class="col-xs-12 col-sm-3">
                        <q-input
                            color="dark"
                            bg-color="secondary"
                            filled
                            v-model="storage.fields.suburb"
                            @input="v => { storage.fields.suburb = v.toUpperCase() }"
                            label="Colonia"
                            :error="$v.storage.fields.suburb.$error"
                            :rules="ruleSuburb">
                            <template v-slot:prepend>
                                <q-icon name="fas fa-building" />
                            </template>
                        </q-input>
                  </div>
                  <div class="col-xs-12 col-sm-3">
                        <q-input
                            color="dark"
                            bg-color="secondary"
                            filled
                            v-model="storage.fields.zip"
                            @input="v => { storage.fields.zip = v.toUpperCase() }"
                            label="CP"
                            :error="$v.storage.fields.zip.$error"
                            :rules="ruleZip">
                            <template v-slot:prepend>
                                <q-icon name="fas fa-mail-bulk" />
                            </template>
                        </q-input>
                  </div>
                  <div class="col-xs-12 col-sm-3">
                        <q-input
                            color="dark"
                            bg-color="secondary"
                            filled
                            v-model="storage.fields.city"
                            @input="v => { storage.fields.city = v.toUpperCase() }"
                            label="Ciudad"
                            :error="$v.storage.fields.city.$error"
                            :rules="ruleCity">
                            <template v-slot:prepend>
                                <q-icon name="fas fa-building" />
                            </template>
                        </q-input>
                  </div>
          </div>

          <div class="row q-mb-sm q-mt-md">
            <div class="col-xs-12 col-sm-2 offset-sm-10 pull-right">
              <q-btn color="positive" icon="save" label="Actualizar" @click="updateStorage()" />
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
    storage: {
      fields: {
        name: { required, maxLength: maxLength(100) },
        code: { required, maxLength: maxLength(5) },
        branchOffice: { required },
        storageType: { required },
        street: { required },
        suburb: { required },
        zip: { required },
        city: { required }
      }
    }
  },
  data () {
    return {
      storage: {
        fields: {
          id: null,
          name: null,
          code: null,
          branchOffice: null,
          storageType: null,
          street: null,
          suburb: null,
          zip: null,
          city: null
        }
      },
      branchOfficeOptions: [],
      storageTypeOptions: []
    }
  },
  computed: {
    nameRules (val) {
      return [
        val => (this.$v.storage.fields.name.required) || 'El campo Nombre es requerido.',
        val => (this.$v.storage.fields.name.maxLength) || 'El campo Nombre no debe exceder los 100 dígitos.'
      ]
    },
    codeRules (val) {
      return [
        val => (this.$v.storage.fields.code.required) || 'El campo Código es requerido.',
        val => (this.$v.storage.fields.code.maxLength) || 'El campo Código no debe exceder los 5 dígitos.'
      ]
    },
    branchOfficeRules (val) {
      return [
        val => (this.$v.storage.fields.branchOffice.required) || 'El campo Sucursal es requerido.'
      ]
    },
    storageTypeRules (val) {
      return [
        val => (this.$v.storage.fields.storageType.required) || 'El campo Tipo de Almacén es requerido.'
      ]
    },
    ruleStreet (val) {
      return [
        val => (this.$v.storage.fields.street.required) || 'El campo calle es requerido.'
      ]
    },
    ruleSuburb (val) {
      return [
        val => (this.$v.storage.fields.suburb.required) || 'El campo colonia es requerido.'
      ]
    },
    ruleZip (val) {
      return [
        val => (this.$v.storage.fields.zip.required) || 'El campo CP es requerido.'
      ]
    },
    ruleCity (val) {
      return [
        val => (this.$v.storage.fields.city.required) || 'La ciudad es requerida.'
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
      api.get(`/storages/${id}`).then(({ data }) => {
        if (!data.storage) {
          this.$router.push('/storages')
        } else {
          console.log('Mi storage')
          console.log(data)
          this.storage.fields = data.storage
          this.storage.fields.branchOffice = { value: data.storage.branch_office_id, label: data.storage.branch_office }
          this.storage.fields.storageType = { value: data.storage.storage_type_id, label: data.storage.storage_type }
          api.get('/branch-offices/options').then(({ data }) => {
            this.branchOfficeOptions = data.options
            api.get('/storage-types/options').then(({ data }) => {
              this.storageTypeOptions = data.options
            })
          })
        }
        this.$q.loading.hide()
      })
    },
    updateStorageFields () {
      this.$v.storage.fields.$reset()
      this.$v.storage.fields.$touch()
    },
    updateStorage () {
      this.$v.storage.fields.$reset()
      this.$v.storage.fields.$touch()
      if (this.$v.storage.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = { ...this.storage.fields }
      params.branch_office_id = Number(params.branchOffice.value)
      params.storage_type_id = Number(params.storageType.value)
      api.put(`/storages/${params.id}`, params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.$router.push('/storages')
        }
      })
    }
  }
}
</script>

<style>
</style>

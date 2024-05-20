<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-9">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Turnos" to="/shifts" />
              <q-breadcrumbs-el label="Editar Turno" v-text= "shift.fields.name"/>
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
            <div class="col-xs-12 col-sm-12">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="shift.fields.name"
                :error="$v.shift.fields.name.$error"
                label="Nombre"
                :rules="nameRules"
                @input="v => { shift.fields.name = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-signature" />
                </template>
              </q-input>
            </div>
          </div>

          <div class="row q-mb-sm q-mt-md">
            <div class="col-xs-12 col-sm-2 offset-sm-10 pull-right">
              <q-btn color="positive" icon="save" label="Actualizar" @click="editShift()" />
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
  name: 'EditShift',
  validations: {
    shift: {
      fields: {
        name: { required, maxLength: maxLength(20) }
      }
    }
  },
  data () {
    return {
      shift: {
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
        val => (this.$v.shift.fields.name.required) || 'El campo Nombre es requerido.',
        val => (this.$v.shift.fields.name.maxLength) || 'El campo Nombre no debe exceder los 20 dígitos.'
      ]
    }
  },
  beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(4)) {
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
      api.get(`/shifts/${id}`).then(({ data }) => {
        if (!data.shift) {
          this.$router.push('/shifts')
        } else {
          this.shift.fields = data.shift
          this.$q.loading.hide()
        }
      })
    },
    updateShiftFields () {
      this.$v.shift.fields.$reset()
      this.$v.shift.fields.$touch()
    },
    editShift () {
      this.$v.shift.fields.$reset()
      this.$v.shift.fields.$touch()
      if (this.$v.shift.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = { ...this.shift.fields }
      api.put(`/shifts/${params.id}`, params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.$router.push('/shifts')
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

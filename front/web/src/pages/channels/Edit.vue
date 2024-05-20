<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-9">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Canales" to="/channels" />
              <q-breadcrumbs-el label="Editar Canal" v-text= "channel.fields.name"/>
            </q-breadcrumbs>
          </div>
        </div>
        <!-- <div class="col-sm-3">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="right">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Categorías" to="/channels" />
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
            <div class="col-xs-12 col-sm-6">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="channel.fields.code"
                :error="$v.channel.fields.code.$error"
                label="Código"
                :rules="codeRules"
                @input="v => { channel.fields.code = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fingerprint" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-6">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="channel.fields.name"
                :error="$v.channel.fields.name.$error"
                label="Nombre"
                :rules="nameRules"
                @input="v => { channel.fields.name = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-signature" />
                </template>
              </q-input>
            </div>
          </div>

          <div class="row q-mb-sm q-mt-md">
            <div class="col-xs-12 col-sm-2 offset-sm-10 pull-right">
              <q-btn color="positive" icon="save" label="Actualizar" @click="editChannel()" />
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
  name: 'EditChannel',
  validations: {
    channel: {
      fields: {
        name: { required, maxLength: maxLength(50) },
        code: { required, maxLength: maxLength(5) }
      }
    }
  },
  data () {
    return {
      channel: {
        fields: {
          id: null,
          name: null,
          code: null
        }
      }
    }
  },
  computed: {
    nameRules (val) {
      return [
        val => (this.$v.channel.fields.name.required) || 'El campo Nombre es requerido.',
        val => (this.$v.channel.fields.name.maxLength) || 'El campo Nombre no debe exceder los 50 dígitos.'
      ]
    },
    codeRules (val) {
      return [
        val => (this.$v.channel.fields.code.required) || 'El campo Código es requerido.',
        val => (this.$v.channel.fields.code.maxLength) || 'El campo Código no debe exceder los 5 dígitos.'
      ]
    }
  },
  /* beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(2)) {
      this.$router.push('/')
    }
  }, */
  created () {
    this.fetchFromServer()
  },
  methods: {
    fetchFromServer () {
      this.$q.loading.show()
      const id = this.$route.params.id
      api.get(`/channels/${id}`).then(({ data }) => {
        if (!data.channel) {
          this.$router.push('/channels')
        } else {
          this.channel.fields = data.channel
          this.$q.loading.hide()
        }
      })
    },
    updateChannelFields () {
      this.$v.channel.fields.$reset()
      this.$v.channel.fields.$touch()
    },
    editChannel () {
      this.$v.channel.fields.$reset()
      this.$v.channel.fields.$touch()
      if (this.$v.channel.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = { ...this.channel.fields }
      api.put(`/channels/${params.id}`, params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.$router.push('/channels')
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

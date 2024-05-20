<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-12">
          <span class="q-ml-md grey-8 fs28 page-title">Configuración</span>
        </div>
      </div>
    </div>

    <div class="q-pa-md bg-grey-3">
      <div class="row bg-white border-panel">
        <div class="col q-pa-md">
          Programación Lotes
          <div class="row q-col-gutter-xs">
            <div class="col-xs-12 col-md-6">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                ref="actionsFieldsDailyProductionTime1SelectRef"
                v-model="actions.fields.daily_production_time_1"
                mask="time"
                label="Hora de inicio programada 1"
                :rules="actionsDailyProductionTime1Rules"
              >
                <template v-slot:prepend>
                  <q-icon name="access_time" />
                </template>
                <q-popup-proxy
                  ref="actionsFieldsDailyProductionTime1Ref"
                  transition-show="scale"
                  transition-hide="scale"
                >
                  <div class="col-sm-12">
                    <q-time
                      v-model="actions.fields.daily_production_time_1"
                      @input="() => $refs.actionsFieldsDailyProductionTime1Ref.hide()"
                      with-seconds
                    />
                  </div>
                </q-popup-proxy>
              </q-select>
            </div>
            <div class="col-xs-12 col-md-6">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                ref="actionsFieldsDailyProductionTime2SelectRef"
                v-model="actions.fields.daily_production_time_2"
                mask="time"
                label="Hora de inicio programada 2"
                :rules="actionsDailyProductionTime2Rules"
              >
                <template v-slot:prepend>
                  <q-icon name="access_time" />
                </template>
                <q-popup-proxy
                  ref="actionsFieldsDailyProductionTime2Ref"
                  transition-show="scale"
                  transition-hide="scale"
                >
                  <div class="col-sm-12">
                    <q-time
                      v-model="actions.fields.daily_production_time_2"
                      @input="() => $refs.actionsFieldsDailyProductionTime2Ref.hide()"
                      with-seconds
                    />
                  </div>
                </q-popup-proxy>
              </q-select>
            </div>
          </div>
          <q-separator />
          Datos de correo electrónico
          <div class="row q-col-gutter-xs">
            <div class="col-xs-12 col-md-2">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="actions.fields.host"
                label="Host"
                @input="v => { actions.fields.host = v.toLowerCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-route" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-md-2">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="actions.fields.encryption"
                label="Encryption"
                @input="v => { actions.fields.encryption = v.toLowerCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-lock" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-md-2">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="actions.fields.port"
                label="Port"
                :rules="portRules"
                @input="v => { actions.fields.port = v.replace(/[^0-9]/g, '') }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-network-wired" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-md-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="actions.fields.username"
                label="Username"
                type="email"
                :rules="usernameRules"
                @input="v => { actions.fields.username = v.toLowerCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-envelope" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-md-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="actions.fields.password"
                label="Password"
                :type="isPwd ? 'password' : 'text'"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-key" />
                </template>
                <template v-slot:append>
                  <q-icon
                    :name="isPwd ? 'visibility_off' : 'visibility'"
                    class="cursor-pointer"
                    @click="isPwd = !isPwd"
                  />
                </template>
              </q-input>
            </div>
          </div>

          <div class="row q-mb-sm q-mt-md">
            <div class="col-xs-12 col-md-2 offset-sm-10 pull-right">
              <q-btn color="positive" icon="save" label="Guardar" @click="saveActions()" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </q-page>
</template>

<script>
import api from '../commons/api.js'
const { required, integer, email } = require('vuelidate/lib/validators')

export default {
  name: 'Actions',
  validations: {
    actions: {
      fields: {
        daily_production_time_1: { required },
        daily_production_time_2: { required },
        port: { integer },
        username: { email }
      }
    }
  },
  /* beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7)) {
      this.$router.push('/')
    }
  }, */
  beforeRouteEnter (to, from, next) {
    next(vm => {
      const propiedades = vm.$store.getters['users/rol']
      console.log(propiedades)
      if (propiedades === 1 || propiedades === 3 || propiedades === 7) {
        next()
      } else {
        next('/')
      }
    })
  },
  data () {
    return {
      actions: {
        fields: {
          daily_production_time_1: '',
          daily_production_time_2: '',
          host: null,
          encryption: null,
          port: null,
          username: null,
          password: null
        }
      },
      isPwd: true
    }
  },
  computed: {
    actionsDailyProductionTime1Rules (val) {
      return [
        val => (this.$v.actions.fields.daily_production_time_1.required) || 'El campo Hora de inicio programada 1 es requerido.'
      ]
    },
    actionsDailyProductionTime2Rules (val) {
      return [
        val => (this.$v.actions.fields.daily_production_time_2.required) || 'El campo Hora de inicio programada 2 es requerido.'
      ]
    },
    portRules (val) {
      return [
        val => (this.$v.actions.fields.port.integer) || 'El campo Port debe ser un valor entero.'
      ]
    },
    usernameRules (val) {
      return [
        val => (this.$v.actions.fields.username.email) || 'El campo Username debe ser una dirección de correo electrónico.'
      ]
    }
  },
  created () {
    this.fetchFromServer()
  },
  methods: {
    fetchFromServer () {
      this.$q.loading.show()
      api.get('/actions').then(({ data }) => {
        if (data.result) {
          this.actions.fields = data.actions
        }
        this.$q.loading.hide()
      })
    },
    saveActions () {
      this.$v.actions.fields.$reset()
      this.$v.actions.fields.$touch()
      if (this.$v.actions.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = { ...this.actions.fields }
      api.put('/actions', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        this.$q.loading.hide()
        if (data.result) {
          this.fetchFromServer()
        }
      })
    }
  }
}
</script>

<style>
</style>

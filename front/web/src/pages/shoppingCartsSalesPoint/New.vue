<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-9">
          <span class="q-ml-md grey-8 fs28 page-title">Nuevo Pedido</span>
        </div>
        <div class="col-sm-3">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="right">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Pedidos" to="/shopping-carts" />
              <q-breadcrumbs-el label="Nuevo" />
            </q-breadcrumbs>
          </div>
        </div>
      </div>
    </div>

    <div class="q-pa-md bg-grey-3">
      <div class="row bg-white border-panel">
        <div class="col q-pa-md">
          <div class="row q-col-gutter-xs">
            <div class="col-xs-12">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="customer"
                :options="customerOptions"
                label="Cliente"
                :rules="customerRules"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-shopping-cart" />
                </template>
              </q-select>
            </div>
          </div>

          <div class="row q-mb-sm q-mt-md">
            <div class="col-xs-12 col-sm-2 offset-sm-10 pull-right">
              <q-btn color="positive" icon="save" label="Guardar" @click="createShoppingCart()" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </q-page>
</template>

<script>
import api from '../../commons/api.js'
const { required } = require('vuelidate/lib/validators')

export default {
  name: 'NewShoppingCart',
  validations: {
    customer: { required }
  },
  data () {
    return {
      customer: null,
      customerOptions: []
    }
  },
  computed: {
    customerRules (val) {
      return [
        val => (this.$v.customer.required) || 'El campo Nombre es requerido.'
      ]
    }
  },
  beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(4) && !this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(12) && !this.$store.getters['users/roles'].includes(15) && !this.$store.getters['users/roles'].includes(17)) {
      this.$router.push('/')
    }
  },
  created () {
    this.fetchFromServer()
  },
  methods: {
    fetchFromServer () {
      this.$q.loading.show()
      api.get('/customers/options').then(({ data }) => {
        this.customerOptions = data.options
        this.$q.loading.hide()
      })
    },
    createShoppingCart () {
      this.$v.customer.$reset()
      this.$v.customer.$touch()
      if (this.$v.customer.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = []
      params.customerId = this.customer.value
      api.post('/shopping-carts', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.$router.push(`/shopping-carts/${data.shoppingCart.id}`)
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

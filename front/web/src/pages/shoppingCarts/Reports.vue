<template>
  <q-page>
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-3">
          <div class="q-pa-md q-gutter-sm">
              <q-breadcrumbs style="font-size: 20px">
                  <q-breadcrumbs-el label="" icon="home" to="/"/>
                  <q-breadcrumbs-el label="Reportes"/>
              </q-breadcrumbs>
          </div>
        </div>
      </div>
    </div>

    <div class="q-pa-md bg-grey-3">
      <div class="col-md-12 col-xs-12 col-lg-12 bg-white">
        <q-tabs
          v-model="currentTab"
          dense
          active-text-color="light-green"
          class="text-grey-8"
          active-color="red"
          indicator-color="red"
          align="justify"
          narrow-indicator
          @input="changeModel"
          style="size: 95%"
        >
          <!-- <q-tab name="line"  label="LÍNEA" icon="fas fa-grip-lines-vertical">
          </q-tab> -->
          <q-tab name="clients"  label="CLIENTE" icon="fas fa-shopping-cart">
          </q-tab>
          <!-- <q-tab name="mark" icon="fas fa-grip-lines-vertical" label="MARCA">
          </q-tab> -->
          <q-tab name="collect"  label="UTILIDAD" icon="receipt_long">
          </q-tab>
          <q-tab name="sales"  label="VENDEDOR" icon="fas fa-handshake">
          </q-tab>
        </q-tabs>
      </div>
        <div class="col">
          <q-tab-panels v-model="currentTab" animated>
            <!-- <q-tab-panel name="mark" class="bg-grey-3">
            </q-tab-panel> -->
            <q-tab-panel name="clients" class="bg-grey-3">
                           <cliente></cliente>
            </q-tab-panel>
            <q-tab-panel name="sales" class="bg-grey-3">
              <Vendedor/>
            </q-tab-panel>
              <!-- <q-tab-panel name="line" class="bg-grey-3">
            </q-tab-panel> -->
              <q-tab-panel name="production" class="bg-grey-3">
            </q-tab-panel>
            <q-tab-panel name="banks" class="bg-grey-3">
            </q-tab-panel>
            <q-tab-panel name="management" class="bg-grey-3">
            </q-tab-panel>
          </q-tab-panels>
        </div>
    </div>
  </q-page>
</template>

<script>
// import api from '../../commons/api.js'
// (no carga) import { Quasar, QCircularProgress } from 'quasar'
import VueApexCharts from 'vue-apexcharts'
import Vue from 'vue'
import cliente from '../../components/reports/cliente/cliente'
import Vendedor from '../../components/reports/vendedor/vendedor.vue'
// import Payment from '../../components/dashboard/Payment'
// import Paymentv2 from '../../components/dashboard/Paymentv2'
// import Sales from '../../components/dashboard/Sales'
// import SalesCharts from '../../components/dashboard/SalesCharts'

// import BanksGpi from '../../components/dashboard/banks/BanksGpi'
// import BanksCharts from '../../components/dashboard/banks/BanksCharts'

// import Managment from '../../components/dashboard/managment/Managment'

// import ProductionKpis from '../../components/dashboard/production/ProductionKpis'
// import ProductionCharts from '../../components/dashboard/production/ProductionCharts'

// const { required, integer, between } = require('vuelidate/lib/validators')
Vue.use(VueApexCharts)
Vue.component('apexchart', VueApexCharts)
// (no carga)Vue.use(Quasar, {
// (no carga)  components: {
// (no carga)    QCircularProgress
// (no carga)  }
// (no carga)})
export default {
  name: 'Dashboard',
  components: {
    cliente,
    Vendedor
  },
  data () {
    return {
      currencyFormatter: new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
      }),
      startDate: null,
      currentTab: 'sales',
      ocAmount: 0,
      productsAmount: 0,
      entryAmount: 0,
      purchasesInArribo: 0,
      weeklyArribos: 0,
      inventaryCostAmount: 0,
      inventaryCostAmountAVG: 0,
      loadingDaily: false,
      loadingMonthly: true,
      pendientes: 0,
      autorizados: 0,
      enviados: 0,
      nuevos: 0,
      solicitados: 0,
      parciales: 0,
      entregados: 0,
      actualmonth: 0
    }
  },
  computed: {
  },
  beforeRouteEnter (to, from, next) {
    next(vm => {
      const propiedades = vm.$store.getters['users/rol']
      console.log(propiedades)
      if (propiedades === 1 || propiedades === 3 || propiedades === 7 || propiedades === 2 || propiedades === 20 || propiedades === 4 || propiedades === 27 || propiedades === 17 || propiedades === 22 || propiedades === 28 || propiedades === 29 || propiedades === 17) {
        next()
      } else {
        next('/')
      }
    })
  },
  /* beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(2) && !this.$store.getters['users/roles'].includes(5) && !this.$store.getters['users/roles'].includes(10)) {
      this.$router.push('/')
    }
  }, */
  created () {
  },
  mounted () {
  },
  methods: {
    changeModel (newModel) {
    },
    async loadCommercial () {
    },
    async loadExpenses () {
    }
  }
}
</script>

<style lang="stylus" scoped>
.my-card
  width 100%
.my-card
  width 100%
  #chart {
  max-width: 650px;
  margin: 35px auto;
}
.fig{
  margin-top: -40px;
  margin-bottom: -50px;
}
.kpi-card{
  min-width: 240px;
}
.kpi-card-top{
  min-width: 150px !important;
}
.kpi-data-vs{
  vertical-align: middle;
}

</style>

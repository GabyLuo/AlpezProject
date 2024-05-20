<template>
    <div class="row q-col-gutter-md" >
      <div class="col-xs-12 col-sm-6 col-md-3 paddings">
        <q-card class="kpi-card-top bg-positive text-white" style=" min-width: 150px;">
          <q-card-section horizontal class="row">
            <q-card-section class="col-4">
              <q-icon name="trending_up" class="kpi-icon col-4" style="font-size: 4em;margin-left: -13px;" />
            </q-card-section>
            <q-card-section class="col-8 items-center">
              <div class="text-h4 kpi-data-v3 text-center"  style="padding-top: 15px;margin-right: -15px;">{{ convertAmount(gpiOne.initialBalance) }}</div>
            </q-card-section>
          </q-card-section>
          <div class="text-subtitle2 kpi-data-v2 text-center text-white">{{ titlesGpi.SI }}</div>
        </q-card>
      </div>
      <div class="col-xs-12 col-sm-6 col-md-3  paddings">
        <q-card class="kpi-card-top bg-positive text-white" style=" min-width: 150px;">
          <q-card-section horizontal class="row">
            <q-card-section class="col-4">
              <q-icon name="check_circle_outline" class="kpi-icon col-4" style="font-size: 4em;margin-left: -13px;" />
            </q-card-section>
            <q-card-section class="col-8 items-center">
              <div class="text-h4 kpi-data-v3 text-center"  style="padding-top: 15px;margin-right: -15px;">{{ convertAmount(gpiOne.abono) }}</div>
            </q-card-section>
          </q-card-section>
          <div class="text-subtitle2 kpi-data-v2 text-center text-white">{{ titlesGpi.AB }}</div>
        </q-card>
      </div>
      <div class="col-xs-12 col-sm-6 col-md-3  paddings">
        <q-card class="kpi-card-top bg-negative text-white" style=" min-width: 150px;">
          <q-card-section horizontal class="row">
            <q-card-section class="col-4">
              <q-icon name="highlight_off" class="kpi-icon col-4" style="font-size: 4em;margin-left: -13px;" />
            </q-card-section>
            <q-card-section class="col-8 items-center">
              <div class="text-h4 kpi-data-v3 text-center"  style="padding-top: 15px;margin-right: -15px;">{{ convertAmount(gpiOne.charge) }}</div>
            </q-card-section>
          </q-card-section>
          <div class="text-subtitle2 kpi-data-v2 text-center text-white">{{ titlesGpi.CA }}</div>
        </q-card>
      </div>
      <div class="col-xs-12 col-sm-6 col-md-3  paddings">
        <q-card class="kpi-card-top text-white" :class="gpiOne.endBalance < 0 ? 'bg-negative' : 'bg-positive'" style=" min-width: 150px;">
          <q-card-section horizontal class="row">
            <q-card-section class="col-3">
              <q-icon :name="gpiOne.endBalance < 0 ? 'trending_down' : 'trending_up'" class="kpi-icon col-3" style="font-size: 4em;margin-left: -13px;" />
            </q-card-section>
            <q-card-section class="col-9 items-center">
              <div class="text-h4 kpi-data-v3 text-center" style="padding-top: 15px;margin-right: -15px;">{{ convertAmount(gpiOne.endBalance) }}</div>
            </q-card-section>
          </q-card-section>
          <div class="text-subtitle2 kpi-data-v2 text-center text-white">{{ titlesGpi.SF }}</div>
        </q-card>
      </div>
      <div v-for="(account, index) in gpiTwo" v-bind:key="index" class="col-xs-12 col-sm-6 col-md-3  paddings">
        <!-- <q-card class="kpi-card-top  text-white"  :class="account.total < 0 ? 'bg-negative' : 'bg-positive'" style=" min-width: 150px;"> -->
        <q-card class="kpi-card-top  text-white"  :style="getColor(account.code)" style=" min-width: 150px;">
          <q-card-section horizontal class="row">
            <q-card-section class="col-3">
              <q-icon :name="account.total < 0 ? 'trending_down' : 'trending_up'" class="kpi-icon col-3" style="font-size: 4em;margin-left: -13px;" />
            </q-card-section>
            <q-card-section class="col-9 items-center">
              <div class="text-h4 kpi-data-v3 text-center" style="padding-top: 15px;margin-right: -15px;">{{ '$' + (Number.parseFloat(account.total) / 1000).toFixed(1).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,') + 'K' }}</div>
            </q-card-section>
          </q-card-section>
          <div class="text-subtitle2 kpi-data-v2 text-center text-white">Gastos Mensuales {{ account.name }}</div>
        </q-card>
      </div>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex'
import VueApexCharts from 'vue-apexcharts'
import Vue from 'vue'
Vue.use(VueApexCharts)
Vue.component('apexchart', VueApexCharts)

export default {
  name: 'Banks',
  props: {
  },
  created () {
  },
  data () {
    return {
      formatter: new Intl.NumberFormat('en-US'),
      currencyFormatter: new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
      }),
      titlesGpi: {
        SI: 'Saldo inicial',
        CA: 'Cargos',
        AB: 'Abonos',
        SF: 'Saldo final'
      },
      gpiOne: {
        initialBalance: 0,
        abono: 0,
        charge: 0,
        endBalance: 0
      },
      gpiTwo: {
        accounts: []
      }
    }
  },
  computed: {
    ...mapGetters({
      // empleados: 'payment/refresh'
    })
  },
  mounted () {
    this.loadAll()
  },
  methods: {
    ...mapActions({
      getGpiOne: 'dashboard/banks/gpiOne',
      getGpiTwo: 'dashboard/banks/gpiTwo'
    }),
    convertAmount (amount) {
      const cost = amount
      if (cost >= 1000 && cost <= 999900) {
        return '$' + (Number.parseFloat(cost) / 1000).toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,') + 'K'
      } else if (cost > 999900) {
        return '$' + (Number.parseFloat(cost) / 1000000).toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,') + 'M'
      } else {
        return '$' + Number.parseFloat(cost).toFixed(2)
      }
    },
    getColor (code) {
      console.log(code)
      // colors: ['#008ffb', '#00e396', '#feb019', '#ff4560', '#775dd0'],
      return code === 'MK' ? 'background-color: #008ffb' : (code === 'AD' ? 'background-color: #feb019' : (code === 'OP' ? 'background-color: #21ba45' : (code === 'VE' ? 'background-color: #775dd0' : (code === 'LG' ? 'background-color: #ff4560' : (code === 'MP' ? 'background-color: #008ffb' : 'background-color: #008ffb')))))
    },
    loadAll () {
      this.getGpiOne().then(({ data }) => {
        if (data) {
          this.gpiOne = data
        }
      })
      this.getGpiTwo().then(({ data }) => {
        if (data.accounts) {
          console.log(data.accounts)
          this.gpiTwo = data.accounts
        }
      })
    }
  },
  validations: {
  }
}
</script>

<style lang="stylus" scoped>
.kpi-card {
}

.kpi-data{
  vertical-align: middle;
  font-family: Roboto,-apple-system,Helvetica Neue,Helvetica,Arial,sans-serif;
  font-size: 3.7em;
  font-weight: bold;
}

.kpi-data-v2{
  /*font-size: 1.2em;*/
}

.kpi-data-v3{
  vertical-align: middle;
  font-family: Roboto,-apple-system,Helvetica Neue,Helvetica,Arial,sans-serif;
  font-size: 2.2em;
  font-weight: bold;
}

.kpi-icon{
  vertical-align: middle;
}
</style>

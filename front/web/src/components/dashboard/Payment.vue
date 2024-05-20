<template>
    <div class="row" >
      <div class="col-xs-12 col-sm-6 col-md-4 col-lg-2 paddings">
        <q-card class="kpi-card-top bg-positive text-white" style=" min-width: 150px;">
          <q-card-section horizontal class="row">
            <q-card-section class="col-4">
              <q-icon name="check_circle_outline" class="kpi-icon col-4" style="font-size: 4em;margin-left: -13px;" />
            </q-card-section>
            <q-card-section class="col-8 items-center">
              <div class="text-h4 kpi-data-v3 text-center"  style="padding-top: 15px;margin-right: -15px;">{{ contentKpi.dataofkpi.fields.totalCustomerBalancetoBeat }}</div>
            </q-card-section>
          </q-card-section>
          <div class="text-subtitle2 kpi-data-v2 text-center text-white">{{ contentKpi.titlesofkpi.fields.totalCustomerBalancetoBeat }}</div>
        </q-card>
      </div>
      <div class="col-xs-12 col-sm-6 col-md-4 col-lg-2 paddings">
        <q-card class="kpi-card-top bg-negative text-white" style=" min-width: 150px;">
          <q-card-section horizontal class="row">
            <q-card-section class="col-4">
              <q-icon name="highlight_off" class="kpi-icon col-4" style="font-size: 4em;margin-left: -13px;" />
            </q-card-section>
            <q-card-section class="col-8 items-center">
              <div class="text-h4 kpi-data-v3 text-center"  style="padding-top: 15px;margin-right: -15px;">{{ contentKpi.dataofkpi.fields.totalCustomerBalanceDefeated }}</div>
            </q-card-section>
          </q-card-section>
          <div class="text-subtitle2 kpi-data-v2 text-center text-white">{{ contentKpi.titlesofkpi.fields.totalCustomerBalanceDefeated }}</div>
        </q-card>
      </div>
      <div class="col-xs-12 col-sm-6 col-md-4 col-lg-2 paddings">
        <q-card class="kpi-card-top bg-deep-purple text-white" style=" min-width: 150px;">
          <q-card-section horizontal class="row">
            <q-card-section class="col-4">
              <q-icon name="blur_on" class="kpi-icon col-4" style="font-size: 4em;margin-left: -13px;" />
            </q-card-section>
            <q-card-section class="col-8 items-center">
              <div class="text-h4 kpi-data-v3 text-center"  style="padding-top: 15px;margin-right: -15px;">{{ contentKpi.dataofkpi.fields.totalCustomerBalance }}</div>
            </q-card-section>
          </q-card-section>
          <div class="text-subtitle2 kpi-data-v2 text-center text-white">{{ contentKpi.titlesofkpi.fields.totalCustomerBalance }}</div>
        </q-card>
      </div>
      <div class="col-xs-12 col-sm-6 col-md-4 col-lg-2 paddings">
        <q-card class="kpi-card-top bg-negative text-white" style=" min-width: 150px;">
          <q-card-section horizontal class="row">
            <q-card-section class="col-3">
              <q-icon name="trending_down" class="kpi-icon col-3" style="font-size: 4em;margin-left: -13px;" />
            </q-card-section>
            <q-card-section class="col-9 items-center">
              <div class="text-h4 kpi-data-v3 text-center" style="padding-top: 15px;margin-right: -15px;">{{ contentKpi.dataofkpi.fields.averageTotalRecovery }}</div>
            </q-card-section>
          </q-card-section>
          <div class="text-subtitle2 kpi-data-v2 text-center text-white">{{ contentKpi.titlesofkpi.fields.averageTotalRecovery }}</div>
        </q-card>
      </div>
      <div class="col-xs-12 col-sm-6 col-md-4 col-lg-2 paddings">
        <q-card class="kpi-card-top bg-indigo text-white" style=" min-width: 150px;">
          <q-card-section horizontal class="row">
            <q-card-section class="col-4">
              <q-icon name="trending_up" class="kpi-icon col-4" style="font-size: 4em;margin-left: -13px;" />
            </q-card-section>
            <q-card-section class="col-8 items-center">
              <div class="text-h4 kpi-data-v3 text-center"  style="padding-top: 15px;margin-right: -15px;">{{ contentKpi.dataofkpi.fields.averageMonthlyRecovery }}</div>
            </q-card-section>
          </q-card-section>
          <div class="text-subtitle2 kpi-data-v2 text-center text-white">{{ contentKpi.titlesofkpi.fields.averageMonthlyRecovery }} {{ contentKpi.dataofkpi.fields.averageMonthlyRecoverym }}</div>
        </q-card>
      </div>
      <div class="col-xs-12 col-sm-6 col-md-4 col-lg-2 paddings">
        <q-card class="kpi-card-top bg-cyan-10 text-white" style=" min-width: 150px;">
          <q-card-section horizontal class="row">
            <q-card-section class="col-4">
              <q-icon name="rotate_right" class="kpi-icon col-4" style="font-size: 4em;margin-left: -13px;" />
            </q-card-section>
            <q-card-section class="col-8 items-center">
              <div class="text-h4 kpi-data-v3 text-center"  style="padding-top: 15px;margin-right: -15px;">{{ contentKpi.dataofkpi.fields.rotationCxC }}</div>
            </q-card-section>
          </q-card-section>
          <div class="text-subtitle2 kpi-data-v2 text-center text-white">{{ contentKpi.titlesofkpi.fields.rotationCxC }}</div>
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
  name: 'Payment',
  props: {
  },
  created () {
  },
  data () {
    return {
      contentKpi: {
        titlesofkpi: {
          fields: {
            totalCustomerBalancetoBeat: 'CxC al corriente',
            totalCustomerBalanceDefeated: 'CxC vencidas',
            totalCustomerBalance: 'CxC Total',
            averageTotalRecovery: 'Morosidad promedio',
            averageMonthlyRecovery: 'Recuperación',
            rotationCxC: 'Rotación CxC'
          }
        },
        dataofkpi: {
          fields: {
            totalCustomerBalancetoBeat: 0,
            totalCustomerBalanceDefeated: 0,
            totalCustomerBalance: 0,
            averageTotalRecovery: 0,
            averageMonthlyRecovery: 0,
            averageMonthlyRecoverym: '',
            rotationCxC: 0
          }
        }
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
      totalCustomerBalance: 'dashboard/payment/totalCustomerBalance',
      totalCustomerBalanceDefeated: 'dashboard/payment/totalCustomerBalanceDefeated',
      totalCustomerBalancetoBeat: 'dashboard/payment/totalCustomerBalancetoBeat',
      averageTotalRecovery: 'dashboard/payment/averageTotalRecovery',
      averageMonthlyRecovery: 'dashboard/payment/averageMonthlyRecovery',
      rotationCxC: 'dashboard/payment/rotationCxC'
    }),
    loadAll () {
      this.totalCustomerBalance().then(({ data }) => {
        if (data.totalCustomerBalance) {
          this.contentKpi.dataofkpi.fields.totalCustomerBalance = data.totalCustomerBalance
        }
      })
      this.totalCustomerBalancetoBeat().then(({ data }) => {
        if (data.totalCustomerBalancetoBeat) {
          this.contentKpi.dataofkpi.fields.totalCustomerBalancetoBeat = data.totalCustomerBalancetoBeat
        }
      })
      this.totalCustomerBalanceDefeated().then(({ data }) => {
        if (data.totalCustomerBalanceDefeated) {
          this.contentKpi.dataofkpi.fields.totalCustomerBalanceDefeated = data.totalCustomerBalanceDefeated
        }
      })
      this.averageTotalRecovery().then(({ data }) => {
        if (data.averageTotalRecovery) {
          this.contentKpi.dataofkpi.fields.averageTotalRecovery = data.averageTotalRecovery
        }
      })
      this.averageMonthlyRecovery().then(({ data }) => {
        if (data.averageMonthlyRecovery) {
          this.contentKpi.dataofkpi.fields.averageMonthlyRecovery = data.averageMonthlyRecovery
        }
        this.contentKpi.dataofkpi.fields.averageMonthlyRecoverym = data.month_p7b
      })
      this.rotationCxC().then(({ data }) => {
        if (data.rotationCxC) {
          this.contentKpi.dataofkpi.fields.rotationCxC = data.rotationCxC
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
.paddings{
  padding: 2% .5% 2% .5%;
}
</style>

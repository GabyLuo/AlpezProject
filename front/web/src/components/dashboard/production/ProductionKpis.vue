<template>
    <div class="row q-col-gutter-md" >
      <div class="col-xs-12 col-sm-6 col-md-3 paddings">
        <q-card class="kpi-card-top bg-primary text-white" style=" min-width: 150px;">
          <q-card-section horizontal class="row">
            <q-card-section class="col-4">
              <q-icon name="align_vertical_bottom" class="kpi-icon col-4" style="font-size: 4em;margin-left: -13px;" />
            </q-card-section>
            <q-card-section class="col-8 items-center">
              <div class="text-h4 kpi-data-v3 text-center"  style="padding-top: 15px;margin-right: -15px;">{{ gpiOne.globalProduction }}</div>
            </q-card-section>
          </q-card-section>
          <div class="text-subtitle2 kpi-data-v2 text-center text-white">{{ titlesGpi.PG }}</div>
        </q-card>
      </div>
      <div class="col-xs-12 col-sm-6 col-md-3  paddings">
        <q-card class="kpi-card-top bg-yellow-8 text-white" style=" min-width: 150px;">
          <q-card-section horizontal class="row">
            <q-card-section class="col-4">
              <q-icon name="align_vertical_bottom" class="kpi-icon col-4" style="font-size: 4em;margin-left: -13px;" />
            </q-card-section>
            <q-card-section class="col-8 items-center">
              <div class="text-h4 kpi-data-v3 text-center"  style="padding-top: 15px;margin-right: -15px;">{{ gpiOne.morningProduction }}</div>
            </q-card-section>
          </q-card-section>
          <div class="text-subtitle2 kpi-data-v2 text-center text-white">{{ titlesGpi.PM }}</div>
        </q-card>
      </div>
      <div class="col-xs-12 col-sm-6 col-md-3  paddings">
        <q-card class="kpi-card-top bg-blue-6 text-white" style=" min-width: 150px;">
          <q-card-section horizontal class="row">
            <q-card-section class="col-4">
              <q-icon name="align_vertical_bottom" class="kpi-icon col-4" style="font-size: 4em;margin-left: -13px;" />
            </q-card-section>
            <q-card-section class="col-8 items-center">
              <div class="text-h4 kpi-data-v3 text-center"  style="padding-top: 15px;margin-right: -15px;">{{ gpiOne.afternonProduction }}</div>
            </q-card-section>
          </q-card-section>
          <div class="text-subtitle2 kpi-data-v2 text-center text-white">{{ titlesGpi.PV }}</div>
        </q-card>
      </div>
      <div class="col-xs-12 col-sm-6 col-md-3  paddings">
        <q-card class="kpi-card-top bg-indigo-6 text-white" style=" min-width: 150px;">
          <q-card-section horizontal class="row">
            <q-card-section class="col-4">
              <q-icon name="align_vertical_bottom" class="kpi-icon col-4" style="font-size: 4em;margin-left: -13px;" />
            </q-card-section>
            <q-card-section class="col-8 items-center">
              <div class="text-h4 kpi-data-v3 text-center"  style="padding-top: 15px;margin-right: -15px;">{{ gpiOne.nightProduction }}</div>
            </q-card-section>
          </q-card-section>
          <div class="text-subtitle2 kpi-data-v2 text-center text-white">{{ titlesGpi.PN }}</div>
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
  name: 'Production',
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
        PG: 'Producción Global Por Producto (Cajas)',
        PM: 'Produccion Mensual Matutina',
        PV: 'Produccion Mensual Vespertina',
        PN: 'Produccion Mensual Nocturna'
      },
      gpiOne: {
        globalProduction: 0,
        morningProduction: 0,
        afternonProduction: 0,
        nightProduction: 0
      },
      gpiTwo: {
        accounts: []
      }
    }
  },
  computed: {
    ...mapGetters({
    })
  },
  mounted () {
    this.loadAll()
  },
  methods: {
    ...mapActions({
      getGpiOne: 'dashboard/production/gpiOne'
    }),
    loadAll () {
      this.getGpiOne().then(({ data }) => {
        if (data) {
          this.gpiOne = data
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

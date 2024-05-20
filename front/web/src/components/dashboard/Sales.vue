<template>
    <div class="row col-md-12 col-xs-12 col-sm-12">
        <div class="row col-md-12 col-xs-12 col-sm-12" >
            <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4" style="padding: 15px;">
              <q-card class="  kpi-card-top bg-blue-7 text-white" style=" min-width: 150px;">
                <q-card-section horizontal class="row">
                  <q-card-section class="col-4">
                    <q-icon name="today" class="kpi-icon text-white-2 col-4" style="font-size: 4em;margin-left: -13px;" />
                  </q-card-section>
                  <q-card-section class="col-8 items-center">
                    <div class="text-h4 kpi-data text-center" style="padding-top: 15px;margin-right: -15px;font-family: Roboto,-apple-system,Helvetica Neue,Helvetica,Arial,sans-serif !important; font-size: 2.2em; font-weight: bold;">${{$formatCost(sales.fields.day)}}</div>
                  </q-card-section>
                </q-card-section>
                <div class="text-subtitle2 text-center text-white-2">Vendido hoy</div>
              </q-card>
            </div>
            <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4" style="padding: 15px;">
              <q-card class="  kpi-card-top bg-blue-7 text-white" style=" min-width: 150px;">
                <q-card-section horizontal class="row">
                  <q-card-section class="col-4">
                    <q-icon name="view_week" class="kpi-icon text-white-2 col-4" style="font-size: 4em;margin-left: -13px;" />
                  </q-card-section>
                  <q-card-section class="col-8 items-center">
                    <div class="text-h4 kpi-data text-center" style="padding-top: 15px;margin-right: -15px;font-family: Roboto,-apple-system,Helvetica Neue,Helvetica,Arial,sans-serif !important; font-size: 2.2em; font-weight: bold;">${{$formatCost(sales.fields.week)}}</div>
                  </q-card-section>
                </q-card-section>
                <div class="text-subtitle2 text-center text-white-2">Vendido  semana</div>
              </q-card>
            </div>
            <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4 kpi-card-top" style="padding: 15px;">
            <q-card class="  kpi-card-top bg-blue-7 text-white" style=" min-width: 150px;">
              <q-card-section horizontal class="row">
                <q-card-section class="col-4">
                  <q-icon name="view_module" class="kpi-icon text-white-2 col-4" style="font-size: 4em;margin-left: -13px;" />
                </q-card-section>
                <q-card-section class="col-8 items-center">
                  <div class="text-h4 kpi-data text-center" style="padding-top: 15px;margin-right: -15px;font-family: Roboto,-apple-system,Helvetica Neue,Helvetica,Arial,sans-serif !important; font-size: 2.2em; font-weight: bold;">${{$formatCost(sales.fields.month)}}</div>
                </q-card-section>
              </q-card-section>
              <div class="text-subtitle2 text-center text-white-2">Vendido  mes</div>
            </q-card>
          </div>
        </div>
        <!--   Cobrado -------------------------------------------->
        <!-- <div class="row  col-md-12 col-xs-12 col-sm-12" >
          <div class="col-md-3 col-xs-12 col-sm-3" style="padding: 15px;">
            <q-card class="kpi-card bg-positive text-white" style=" min-width: 230px;">
              <q-card-section horizontal class="row">
                <q-card-section class="col-4">
                  <q-icon name="today" class="kpi-icon text col-4" style="font-size: 5em;" />
                </q-card-section>
                <q-card-section class="col-8 items-center" >
                  <div class="text-h4 kpi-data text-center" style="padding-top: 15px;margin-left: -25px;font-family: Roboto,-apple-system,Helvetica Neue,Helvetica,Arial,sans-serif !important; font-size: 2.2em; font-weight: bold;">{{$formatCost(sales.fields.dayP)}}</div>
                </q-card-section>
              </q-card-section>
              <div class="text-subtitle2 text-center " style="margin-left: -25px;">Balance  hoy</div>
            </q-card>
          </div>
          <div class="col-md-3 col-xs-12 col-sm-3" style="padding: 15px;">
            <q-card class="kpi-card bg-positive text-white" style=" min-width: 230px;">
              <q-card-section horizontal class="row">
                <q-card-section class="col-4">
                  <q-icon name="view_week" class="kpi-icon col-4" style="font-size: 5em;" />
                </q-card-section>
                <q-card-section class="col-8 items-center">
                  <div class="text-h4 kpi-data text-center" style="padding-top: 15px;margin-left: -25px;font-family: Roboto,-apple-system,Helvetica Neue,Helvetica,Arial,sans-serif !important; font-size: 2.2em; font-weight: bold;">{{$formatCost(sales.fields.weekP)}}</div>
                </q-card-section>
              </q-card-section>
              <div class="text-subtitle2 text-center" style="margin-left: -25px;">Balance semana</div>
            </q-card>
          </div>
          <div class="col-md-3 col-xs-12 col-sm-3" style="padding: 15px;">
            <q-card class="kpi-card bg-positive text-white" style=" min-width: 230px;">
              <q-card-section horizontal class="row">
                <q-card-section class="col-4">
                  <q-icon name="view_module" class="kpi-icon col-4" style="font-size: 5em;" />
                </q-card-section>
                <q-card-section class="col-8 items-center">
                  <div class="text-h4 kpi-data-v2 text-center" style="padding-top: 15px;margin-left: -25px;font-family: Roboto,-apple-system,Helvetica Neue,Helvetica,Arial,sans-serif !important; font-size: 2.2em; font-weight: bold;">{{$formatCost(sales.fields.monthP)}}</div>
                </q-card-section>
              </q-card-section>
              <div class="text-subtitle2 text-center " style="margin-left: -25px;">Balance  mes</div>
            </q-card>
          </div>
          <div class="col-md-3 col-xs-12 col-sm-3" style="padding: 15px;">
            <q-card class="kpi-card bg-positive text-white" style=" min-width: 230px;">
              <q-card-section horizontal class="row">
                <q-card-section class="col-4">
                  <q-icon name="wysiwyg" class="kpi-icon col-4" style="font-size: 5em;" />
                </q-card-section>
                <q-card-section class="col-8 items-center">
                  <div class="text-h4 kpi-data text-center" style="padding-top: 15px;margin-left: -25px;font-family: Roboto,-apple-system,Helvetica Neue,Helvetica,Arial,sans-serif !important; font-size: 2.2em; font-weight: bold;">{{$formatCost(sales.fields.yearP)}}</div>
                </q-card-section>
              </q-card-section>
              <div class="text-subtitle2 text-center " style="margin-left: -25px;">Balance anual</div>
            </q-card>
          </div>
        </div> -->
      </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex'
import VueApexCharts from 'vue-apexcharts'
import Vue from 'vue'
Vue.use(VueApexCharts)
Vue.component('apexchart', VueApexCharts)

export default {
  name: 'Sales',
  props: {
  },
  created () {
  },
  data () {
    return {
      sales: {
        fields: {
          day: 0,
          week: 0,
          month: 0,
          dayP: 0,
          weeKP: 0,
          monthP: 0,
          yearP: 0
        }
      }
    }
  },
  computed: {
    ...mapGetters({
    })
  },
  async mounted () {
    this.loadAll()
  },
  methods: {
    ...mapActions({
      daySale: 'dashboard/payment/daySale',
      weekSale: 'dashboard/payment/weekSales',
      monthSale: 'dashboard/payment/monthSales'
      // dayPay: 'dashboard/payment/dayPays',
      // weekPay: 'dashboard/payment/weekPays',
      // monthPay: 'dashboard/payment/monthPays',
      // yearPay: 'dashboard/payment/yearPays'
    }),
    async loadAll () {
      this.$q.loading.show()
      await this.daySale().then(({ data }) => {
        this.sales.fields.day = data.daySales.daysale
      })
      await this.weekSale().then(({ data }) => {
        this.sales.fields.week = data.weekSales.weeksale
      })
      await this.monthSale().then(({ data }) => {
        this.sales.fields.month = data.monthSales.monthsale
      })
      this.$q.loading.hide()
      // await this.dayPay().then(({ data }) => {
      //   this.sales.fields.dayP = data.dayPays.daypay
      // })
      // await this.weekPay().then(({ data }) => {
      //   this.sales.fields.weekP = data.weekPays.weekpay
      // })
      // await this.monthPay().then(({ data }) => {
      //   this.sales.fields.monthP = data.monthPays.monthpay
      // })
      // await this.yearPay().then(({ data }) => {
      //   this.sales.fields.yearP = data.yearPays.yearpay
      // })
    }
  },
  validations: {
  }
}
</script>

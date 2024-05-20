<template>
    <div class="row col-md-12 col-xs-12 col-sm-12">
        <div class="row col-md-12 col-xs-12 col-sm-12" >
            <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3 kpi-card-top q-pa-xs">
            <q-card class="  kpi-card-top bg-blue-7 text-white" style=" min-width: 150px;">
              <q-card-section horizontal class="row">
                <q-card-section class="col-4">
                  <q-icon name="fas fa-dollar-sign" class="kpi-icon text-white-2 col-4" style="font-size: 4em;margin-left: -13px;" />
                </q-card-section>
                <q-card-section class="col-8 items-center">
                  <div class="text-h4 kpi-data text-center" style="padding-top: 15px;margin-right: -15px;font-family: Roboto,-apple-system,Helvetica Neue,Helvetica,Arial,sans-serif !important; font-size: 2.2em; font-weight: bold;">{{convertinventaryCostAmount(sales.fields.month)}}</div>
                </q-card-section>
              </q-card-section>
              <div class="text-subtitle2 text-center text-white-2">Ventas mes</div>
            </q-card>
          </div>
            <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3 kpi-card-top q-pa-xs">
            <q-card class="  kpi-card-top bg-blue-7 text-white" style=" min-width: 150px;">
              <q-card-section horizontal class="row">
                <q-card-section class="col-4">
                  <q-icon name="align_vertical_bottom" class="kpi-icon text-white-2 col-4" style="font-size: 4em;margin-left: -13px;" />
                </q-card-section>
                <q-card-section class="col-8 items-center">
                  <div class="text-h4 kpi-data text-center" style="padding-top: 15px;margin-right: -15px;font-family: Roboto,-apple-system,Helvetica Neue,Helvetica,Arial,sans-serif !important; font-size: 2.2em; font-weight: bold;">{{convertinventaryCostAmount(inventaryCostAmount) }}</div>
                </q-card-section>
              </q-card-section>
              <div class="text-subtitle2 text-center text-white-2">Costo de inventario</div>
            </q-card>
          </div>
      <div class="col-xs-12 col-sm-2 col-md-2 col-lg-2 kpi-card-top q-pa-xs">
        <q-card class="kpi-card-top text-white" :class="endBalance < 0 ? 'bg-negative' : 'bg-positive'" style=" min-width: 150px;">
          <q-card-section horizontal class="row">
            <q-card-section class="col-3">
              <q-icon :name="endBalance < 0 ? 'trending_down' : 'trending_up'" class="kpi-icon col-3" style="font-size: 4em;margin-left: -13px;" />
            </q-card-section>
            <q-card-section class="col-9 items-center">
              <div class="text-h4 kpi-data text-center" style="padding-top: 15px;margin-right: -15px;font-family: Roboto,-apple-system,Helvetica Neue,Helvetica,Arial,sans-serif !important; font-size: 2.2em; font-weight: bold;">{{ convertinventaryCostAmount(endBalance) }}</div>
            </q-card-section>
          </q-card-section>
          <div class="text-subtitle2 kpi-data-v2 text-center text-white">Saldo bancario</div>
        </q-card>
      </div>
      <div class="col-xs-12 col-sm-2 col-md-2 col-lg-2 kpi-card-top q-pa-xs" >
        <q-card class="kpi-card-top text-white bg-red" style=" min-width: 150px;">
          <q-card-section horizontal class="row">
            <q-card-section class="col-3">
              <q-icon name="trending_down" class="kpi-icon col-3" style="font-size: 4em;margin-left: -13px;" />
            </q-card-section>
            <q-card-section class="col-9 items-center">
              <div class="text-h4 kpi-data text-center" style="padding-top: 15px;margin-right: -15px;font-family: Roboto,-apple-system,Helvetica Neue,Helvetica,Arial,sans-serif !important; font-size: 2.2em; font-weight: bold;">{{ convertinventaryCostAmount(accountsReceivable) }}</div>
            </q-card-section>
          </q-card-section>
          <div class="text-subtitle2 kpi-data-v2 text-center text-white">Cuentas por cobrar</div>
        </q-card>
      </div>
      <div class="col-xs-12 col-sm-2 col-md-2 col-lg-2 kpi-card-top q-pa-xs" >
        <q-card class="kpi-card-top text-white bg-teal" style=" min-width: 150px;">
          <q-card-section horizontal class="row">
            <q-card-section class="col-3">
              <q-icon name="align_vertical_bottom" class="kpi-icon col-3" style="font-size: 4em;margin-left: -13px;" />
            </q-card-section>
            <q-card-section class="col-9 items-center">
              <div class="text-h4 kpi-data text-center" style="padding-top: 15px;margin-right: -15px;font-family: Roboto,-apple-system,Helvetica Neue,Helvetica,Arial,sans-serif !important; font-size: 2.2em; font-weight: bold;">{{ convertProduction(monthTotalProduction) }}</div>
            </q-card-section>
          </q-card-section>
          <div class="text-subtitle2 kpi-data-v2 text-center text-white">Producción mensual</div>
        </q-card>
      </div>
        </div>
        <div class="row col-md-12 col-xs-12 col-sm-12">
          <div class="col-xs-12 col-md-6 q-pa-md">
            <q-card class="col-xs-12 col-md-12">
              <q-card-section class="bg-dark text-secondary">
                <div class="text-h20"><center>Egresos por cuenta</center></div>
              </q-card-section>
              <q-separator />
              <div id="chart">
                <apexchart ref="chart1" type="donut" height="500px" width="600px" :options="chartOptions1" :series="series1" ></apexchart>
              </div>
              <template>
              </template>
            </q-card>
          </div>
      <div class="col-xs-12 col-md-6 q-pa-md">
        <q-card class="col-xs-12 col-md-12">
          <q-card-section class="bg-dark text-secondary">
            <div class="text-h20"><center>Ingresos por rubro</center></div>
          </q-card-section>
          <q-separator />
          <q-card-section>
          <div id="chart">
            <apexchart ref="chart2" type="donut" height="500px" width="535px" :options="chartOptions2" :series="series2"></apexchart>
          </div>
          </q-card-section>
          <template>
          </template>
        </q-card>
      </div>
      <div class="col-xs-12 col-md-12 q-pa-md">
        <q-card class="col-xs-12 col-md-12">
          <q-card-section class="bg-dark text-secondary">
            <div class="text-h20"><center>Flujo Mensual</center></div>
          </q-card-section>
          <q-separator />
          <div id="chart">
            <apexchart ref="chart3" type="bar" height="500" :options="chartOptions3" :series="series3" class="fig"></apexchart>
          </div>
          <template>
          </template>
        </q-card>
      </div>
        </div>
      </div>
</template>

<script>
import api from '../../../commons/api.js'
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
    this.mesesx()
  },
  data () {
    return {
      monthTotalProduction: 0,
      inventaryCostAmount: 0,
      endBalance: 0,
      accountsReceivable: 0,
      sales: {
        fields: {
          month: 0
        }
      },
      series1: [],
      series2: [],
      chartOptions1: {
        // colors: ['#008ffb', '#00e396', '#feb019', '#ff4560', '#775dd0'],
        chart: {
          type: 'donut'
        },
        labels: [],
        responsive: [{
          breakpoint: 480,
          options: {
            chart: {
              width: 100
            },
            legend: {
              position: 'bottom'
            }
          }
        }]
      },
      chartOptions2: {
        // colors: ['#008ffb', '#00e396', '#feb019', '#ff4560', '#775dd0'],
        chart: {
          type: 'donut'
        },
        labels: [],
        responsive: [{
          breakpoint: 480,
          options: {
            chart: {
              width: 100
            },
            legend: {
              position: 'bottom'
            }
          }
        }]
      },
      series3: [],
      chartOptions3: {
        colors: ['#008ffb', '#00e396', '#feb019', '#ff4560', '#775dd0', '#008ffb', '#00e396', '#feb019', '#ff4560', '#775dd0'],
        chart: {
          stacked: true,
          height: 500,
          type: 'char',
          toolbar: {
            show: false
          }
        },
        dataLabels: {
          enabled: true,
          enabledOnSeries: [0, 1, 2, 3],
          formatter: function (val) {
            const cost = val
            if (cost >= 1000 && cost <= 999900) {
              return '$' + (Number.parseFloat(cost) / 1000).toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,') + 'K'
            } else if (cost > 999900) {
              return (Number.parseFloat(cost) / 1000000).toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,') + 'M'
            } else {
              return '$' + Number.parseFloat(cost).toFixed(2)
            }
          }
        },
        stroke: {
          curve: 'smooth'
        },
        title: {
          text: '',
          align: 'left'
        },
        grid: {
          borderColor: '#e7e7e7',
          row: {
            colors: ['#f3f3f3', 'transparent'], // takes an array which will be repeated on columns
            opacity: 0.5
          }
        },
        markers: {
          size: 1
        },
        xaxis: {
          categories: [],
          title: {
            text: 'Mes'
          },
          labels: {
            type: 'String',
            show: true
          }
        },
        yaxis: {
          max: function (max) {
            return max
          },
          title: {
            text: 'Pesos'
          },
          labels: {
            type: 'String',
            // show: true,
            formatter: function (val) {
              if (Math.trunc(val) === 0) {
                return '$' + (Number.parseFloat(val) / 1000).toFixed(1).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,') + 'K'
              } else {
                if (val >= 1000 && val <= 999900) {
                  return '$' + (Number.parseFloat(val) / 1000).toFixed(1).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,') + 'K'
                } else if (val > 999900) {
                  return (Number.parseFloat(val) / 1000000).toFixed(1).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,') + 'M'
                } else {
                  return '$' + Number.parseFloat(val).toFixed(1)
                }
              }
            }
          }
        },
        legend: {
          show: false,
          position: 'top',
          horizontalAlign: 'right',
          floating: true,
          offsetY: -25,
          offsetX: -5
        }
      }
    }
  },
  computed: {
    ...mapGetters({
    })
  },
  async mounted () {
    this.$q.loading.show()
    await this.loadAll()
    this.$q.loading.hide()
  },
  methods: {
    ...mapActions({
      getGpiOne: 'dashboard/banks/gpiOne',
      monthSale: 'dashboard/payment/monthSales',
      getChartOne: 'dashboard/banks/chartOne',
      getChartThree: 'dashboard/banks/chartThree',
      getChartTwoByPayment: 'dashboard/banks/chartTwoPayment',
      getGpiOneP: 'dashboard/production/gpiOne'
    }),
    convertinventaryCostAmount (amount) {
      const cost = amount
      if (cost >= 1000 && cost <= 999900) {
        return '$' + (Number.parseFloat(cost) / 1000).toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,') + 'K'
      } else if (cost > 999900) {
        return '$' + (Number.parseFloat(cost) / 1000000).toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,') + 'M'
      } else {
        return '$' + Number.parseFloat(cost).toFixed(2)
      }
    },
    convertProduction (boxes) {
      const cost = boxes
      if (cost >= 1000 && cost <= 999900) {
        return (Number.parseInt(cost) / 1000) + 'K'
      } else if (cost > 999900) {
        return (Number.parseInt(cost) / 1000000) + 'M'
      } else {
        return Number.parseInt(cost)
      }
    },
    async loadAll () {
      this.getGpiOne().then(({ data }) => {
        if (data) {
          this.endBalance = data.endBalance
        }
      })
      this.getGpiOneP().then(({ data }) => {
        if (data) {
          this.monthTotalProduction = data.monthTotalProduction
        }
      })
      await this.monthSale().then(({ data }) => {
        this.sales.fields.month = data.monthSales.monthsale
      })
      await api.get('/dashboard/getPurchases/null').then(({ data }) => {
        this.inventaryCostAmount = data.inventaryCost
      }).catch(error => error)
      await api.get('/dashboard/accountsReceivable').then(({ data }) => {
        this.accountsReceivable = data.accountsReceivable
      }).catch(error => error)
      this.getChartOne().then(async ({ data }) => {
        if (data) {
          this.series1 = data.series
          this.chartOptions1 = {
            ...this.chartOptions1,
            ...{
              labels: data.names
            }
          }
          this.$refs.chart1.updateOptions({ series: this.series1 })
        }
      })
      this.getChartTwoByPayment().then(async ({ data }) => {
        if (data) {
          this.series2 = data.series
          this.chartOptions2 = {
            ...this.chartOptions2,
            ...{
              labels: data.names
            }
          }
          this.$refs.chart2.updateOptions({ series: this.series2 })
        }
      })
      this.getChartThree().then(async ({ data }) => {
        for (let i = 0; i < data.info.length; i++) {
          if (data.info[i].type !== 'line') {
            this.series3.push({ name: data.info[i].name, type: data.info[i].type, data: data.info[i].series })
          }
        }
        this.$refs.chart3.updateOptions({ series: this.series3 })
      })
    },
    async mesesx () {
      var meses = ['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC']
      var f = new Date()
      var mes = []
      for (var i = 0; i <= 11; i++) {
        var check = f.getMonth() - i
        if (check >= 0) {
          mes.push(meses[check])
        } else {
          mes.push(meses[check + 12])
        }
      }
      mes[0] = 'ACTUAL'

      this.chartOptions3.xaxis.categories = mes.reverse()
    }
  },
  validations: {
  }
}
</script>

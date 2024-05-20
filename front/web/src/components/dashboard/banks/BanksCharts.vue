<template>
  <div class="row">
      <div class="col-xs-12 col-md-6 q-pa-md">
        <q-card class="col-xs-12 col-md-12">
          <q-card-section class="bg-dark text-secondary">
            <div class="text-h20"><center>Egresos por cuenta</center></div>
          </q-card-section>
          <q-separator />
          <div id="chart">
            <apexchart ref="chart1" type="donut" height="500px" width="600px" :options="chartOptions1" :series="series1"></apexchart>
          </div>
          <template>
          </template>
        </q-card>
      </div>
      <div class="col-xs-12 col-md-6 q-pa-md">
        <q-card class="col-xs-12 col-md-12">
          <q-card-section class="bg-dark text-secondary">
            <div class="text-h20"><center>Egresos por rubro</center></div>
          </q-card-section>
          <q-separator />
          <q-card-section>
          <div id="chart">
            <apexchart ref="chart2" type="donut"  height="500px" width="667px" :options="chartOptions2" :series="series2"></apexchart>
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
            <apexchart ref="chart3" type="line" height="500" :options="chartOptions3" :series="series3" class="fig"></apexchart>
          </div>
          <template>
          </template>
        </q-card>
      </div>
      <div class="col-xs-12 col-md-12 q-pa-md">
        <q-card class="col-xs-12 col-md-12">
          <q-card-section class="bg-dark text-secondary">
            <div class="text-h20"><center>Ingresos por Cuenta</center></div>
          </q-card-section>
          <q-separator />
          <div id="chart">
            <apexchart ref="chart4" type="bar" height="500" :options="chartOptions4" :series="series4" class="fig"></apexchart>
          </div>
          <template>
          </template>
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
  data () {
    return {
      series1: [],
      chartOptions1: {
        colors: ['#008ffb', '#21ba45', '#feb019', '#ff4560', '#775dd0'],
        chart: {
          type: 'donut'
        },
        labels: [],
        responsive: [{
          breakpoint: 480,
          options: {
            chart: {
              width: 200
            },
            legend: {
              position: 'bottom'
            }
          }
        }]
      },
      chartOptions2: {
        colors: ['#008ffb', '#21ba45', '#feb019', '#ff4560', '#775dd0'],
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
      series2: [],
      series3: [],
      series4: [],
      chartOptions3: {
        colors: ['#008ffb', '#21ba45', '#feb019', '#ff4560', '#775dd0', '#008ffb', '#21ba45', '#feb019', '#21ba45', '#775dd0'],
        chart: {
          stacked: true,
          height: 500,
          type: 'column',
          toolbar: {
            show: false
          }
        },
        dataLabels: {
          enabled: true,
          // enabledOnSeries: [0, 1, 2, 3],
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
            return Number.parseFloat(max / 1.6)
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
                  return '$' + (Number.parseFloat(val) / 1000).toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,') + 'K'
                } else if (val > 999900) {
                  return (Number.parseFloat(val) / 1000000).toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,') + 'M'
                } else {
                  return '$' + Number.parseFloat(val).toFixed(2)
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
      },
      chartOptions4: {
        chart: {
          stacked: true,
          height: 700,
          type: 'bar',
          // dropShadow: {
          //   enabled: true,
          //   color: '#000',
          //   top: 18,
          //   left: 7,
          //   blur: 10,
          //   opacity: 0.2
          // },
          toolbar: {
            show: false
          }
        },
        dataLabels: {
          enabled: true,
          formatter: function (val) {
            const cost = val
            if (cost >= 1000 && cost <= 999900) {
              return '$' + (Number.parseFloat(cost) / 1000).toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,') + 'K'
            } else if (cost > 999900) {
              return (Number.parseFloat(cost) / 1000000).toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,') + 'M'
            } else {
              return '$' + Number.parseFloat(cost).toFixed(2)
            }
            // return (Number.parseFloat(val) / 1000).toFixed(1).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,') + 'K'
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
          categories: ['Sem5', 'Sem4', 'Sem3', 'Sem2', 'Sem1', 'ACTUAL'],
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
                  return '$' + (Number.parseFloat(val) / 1000).toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,') + 'K'
                } else if (val > 999900) {
                  return (Number.parseFloat(val) / 1000000).toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,') + 'M'
                } else {
                  return '$' + Number.parseFloat(val).toFixed(2)
                }
              }
            }
          }
        },
        legend: {
          show: false,
          position: 'top',
          horizontalAlign: 'right',
          floating: false,
          offsetY: -25,
          offsetX: -5
        }
      }
    }
  },
  async created () {
    this.mesesx()
    await this.loadAll()
  },
  computed: {
    ...mapGetters({
      // empleados: 'payment/refresh'
    })
  },
  mounted () {
  },
  methods: {
    ...mapActions({
      getChartOne: 'dashboard/banks/chartOne',
      getChartTwo: 'dashboard/banks/chartTwo',
      getChartThree: 'dashboard/banks/chartThree'
    }),
    loadAll () {
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
      this.getChartTwo().then(async ({ data }) => {
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
        for (let i = 0; i < data.total_abonos.length; i++) {
          this.series4.push({ name: data.total_abonos[i].name, type: 'column', data: data.total_abonos[i].total_abonos })
        }
        for (let i = 0; i < data.info.length; i++) {
          this.series3.push({ name: data.info[i].name, type: data.info[i].type, data: data.info[i].series })
        }
        // this.series3.push({ name: data.abonos[0].name, type: 'line', data: data.abonos[0].series_abonos })
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
      this.chartOptions4.xaxis.categories = mes
    }
  },
  validations: {
  }
}
</script>

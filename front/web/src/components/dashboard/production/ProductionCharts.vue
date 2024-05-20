<template>
  <div class="row">
      <div class="col-xs-12 col-md-6 q-pa-md">
        <q-card class="col-xs-12 col-md-12">
          <q-card-section class="bg-dark text-secondary">
            <div class="text-h20"><center>Producción diaria</center></div>
          </q-card-section>
          <q-separator />
          <div id="chart">
            <apexchart ref="chart1" type="bar" height="500" :options="chartOptions1" :series="series1" class="fig"></apexchart>
          </div>
          <template>
            <div>
            </div>
          </template>
        </q-card>
      </div>
      <div class="col-xs-12 col-md-6 q-pa-md">
        <q-card class="col-xs-12 col-md-12">
          <q-card-section class="bg-dark text-secondary">
            <div class="text-h20"><center>Producción semanal</center></div>
          </q-card-section>
          <q-separator />
          <div id="chart">
            <apexchart ref="chart2" type="bar" height="500" :options="chartOptions2" :series="series2" class="fig"></apexchart>
          </div>
        </q-card>
      </div>
      <div class="col-xs-12 col-md-12 q-pa-md">
        <q-card class="col-xs-12 col-md-12">
          <q-card-section class="bg-dark text-secondary">
            <div class="text-h20"><center>Producción mensual</center></div>
          </q-card-section>
          <q-separator />
          <div id="chart">
            <apexchart ref="chart3" type="bar" height="500" :options="chartOptions3" :series="series3" class="fig"></apexchart>
          </div>
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
  name: 'Sales',
  props: {
  },
  data () {
    return {
      chartOptions1: {
        colors: ['#fbc02d', '#2196f3', '#3f51b5'],
        chart: {
          height: 500,
          type: 'bar',
          stacked: true,
          fontFamily: 'Nunito',
          toolbar: {
            show: false,
            tools: {
              download: true,
              selection: false,
              pan: false,
              zoom: false,
              zoomin: false,
              zoomout: false,
              reset: false
            }
          }
        },
        stroke: {
          curve: ['smooth', 'straight', 'straight', 'straight'],
          width: [4, 0, 0, 4]
        },
        dataLabels: {
          enabled: true,
          enabledOnSeries: [0, 1, 2, 3],
          formatter: function (val) {
            const cost = val
            if (cost >= 1000 && cost <= 999900) {
              return (Number.parseInt(cost) / 1000) + 'K'
            } else if (cost > 999900) {
              return (Number.parseInt(cost) / 1000000) + 'M'
            } else {
              return Number.parseInt(cost)
            }
          }
        },
        xaxis: {
          categories: [],
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
            text: 'Cajas'
          },
          labels: {
            type: 'String',
            show: true,
            formatter: function (val) {
              const cost = val
              if (cost >= 1000 && cost <= 999900) {
                return (Number.parseInt(cost) / 1000) + 'K'
              } else if (cost > 999900) {
                return (Number.parseInt(cost) / 1000000) + 'M'
              } else {
                return Number.parseInt(cost)
              }
            }
          }
        },
        grid: {
          borderColor: '#c6c2cF',
          row: {
            colors: ['#f3f3f3', 'transparent'],
            opacity: 0.8
          }
        }
        // legend: {
        //   inverseOrder: true
        // }
      },
      chartOptions2: {
        colors: ['#fbc02d', '#2196f3', '#3f51b5'],
        chart: {
          height: 500,
          type: 'line',
          stacked: true,
          fontFamily: 'Nunito',
          toolbar: {
            show: true,
            tools: {
              download: true,
              selection: false,
              pan: false,
              zoom: false,
              zoomin: false,
              zoomout: false,
              reset: false
            }
          }
        },
        stroke: {
          curve: ['smooth', 'straight', 'straight', 'straight'],
          width: [4, 0, 0, 4]
        },
        dataLabels: {
          enabled: true,
          enabledOnSeries: [0, 1, 2, 3],
          formatter: function (val) {
            const cost = val
            if (cost >= 1000 && cost <= 999900) {
              return (Number.parseInt(cost) / 1000) + 'K'
            } else if (cost > 999900) {
              return (Number.parseInt(cost) / 1000000) + 'M'
            } else {
              return Number.parseInt(cost)
            }
          }
        },
        xaxis: {
          categories: ['Sem5', 'Sem4', 'Sem3', 'Sem2', 'Sem1', 'ACTUAL'],
          labels: {
            type: 'String',
            show: true
            // formatter: function (val) {
            //   return val + 'M'
            // }
          }
        },
        yaxis: {
          max: function (max) {
            return max
          },
          title: {
            text: 'Cajas'
          },
          labels: {
            type: 'String',
            show: true,
            formatter: function (val) {
              const cost = val
              if (cost >= 1000 && cost <= 999900) {
                return (Number.parseInt(cost) / 1000) + 'K'
              } else if (cost > 999900) {
                return (Number.parseInt(cost) / 1000000) + 'M'
              } else {
                return Number.parseInt(cost)
              }
            }
          }
        },
        grid: {
          borderColor: '#c6c2cF',
          row: {
            colors: ['#f3f3f3', 'transparent'],
            opacity: 0.8
          }
        }
        // legend: {
        //   inverseOrder: true
        // }
      },
      chartOptions3: {
        colors: ['#fbc02d', '#2196f3', '#3f51b5'],
        chart: {
          height: 500,
          type: 'line',
          stacked: true,
          fontFamily: 'Nunito',
          toolbar: {
            show: false,
            tools: {
              download: true,
              selection: false,
              pan: false,
              zoom: false,
              zoomin: false,
              zoomout: false,
              reset: false
            }
          }
        },
        stroke: {
          curve: ['smooth', 'straight', 'straight', 'straight'],
          width: [4, 0, 0, 4]
        },
        dataLabels: {
          enabled: true,
          enabledOnSeries: [0, 1, 2, 3],
          formatter: function (val) {
            const cost = val
            if (cost >= 1000 && cost <= 999900) {
              return (Number.parseInt(cost) / 1000) + 'K'
            } else if (cost > 999900) {
              return (Number.parseInt(cost) / 1000000) + 'M'
            } else {
              return Number.parseInt(cost)
            }
          }
        },
        xaxis: {
          categories: [],
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
            text: 'Cajas'
          },
          labels: {
            type: 'String',
            // show: true,
            formatter: function (val) {
              const cost = val
              if (cost >= 1000 && cost <= 999900) {
                return (Number.parseInt(cost) / 1000) + 'K'
              } else if (cost > 999900) {
                return (Number.parseInt(cost) / 1000000) + 'M'
              } else {
                return Number.parseInt(cost)
              }
            }
          }
        },
        grid: {
          borderColor: '#c6c2cF',
          row: {
            colors: ['#f3f3f3', 'transparent'],
            opacity: 0.8
          }
        }
      },
      series1: [],
      series2: [],
      series3: []
    }
  },
  async created () {
    this.mesesx()
    this.diasSemanax()
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
      dayProductionCharts: 'dashboard/production/dayProductionCharts',
      weekProductionCharts: 'dashboard/production/weekProductionCharts',
      monthProductionCharts: 'dashboard/production/monthProductionCharts'
    }),
    loadAll () {
      this.dayProductionCharts().then(async ({ data }) => {
        for (let i = 0; i < data.dayProductionCharts.length; i++) {
          this.series1.push({ name: data.dayProductionCharts[i].time, data: data.dayProductionCharts[i].production.reverse() })
          this.$refs.chart1.updateOptions({ series: this.series1 })
        }
      })
      this.weekProductionCharts().then(async ({ data }) => {
        console.log(data)
        for (let i = 0; i < data.weekProductionCharts.length; i++) {
          this.series2.push({ name: data.weekProductionCharts[i].time, data: data.weekProductionCharts[i].production.reverse() })
          this.$refs.chart2.updateOptions({ series: this.series2 })
        }
      })
      this.monthProductionCharts().then(async ({ data }) => {
        for (let i = 0; i < data.monthProductionCharts.length; i++) {
          this.series3.push({ name: data.monthProductionCharts[i].name, data: data.monthProductionCharts[i].series })
          this.$refs.chart3.updateOptions({ series: this.series3 })
        }
      })
    },
    async diasSemanax () {
      var diasSemana = ['DOM', 'LUN', 'MAR', 'MIE', 'JUE', 'VIE', 'SAB', 'DOM']
      var f = new Date()
      var days = []
      for (var i = 0; i <= 7; i++) {
        var check = f.getDay() - i
        if (check >= 0) {
          days.push(diasSemana[check])
        } else {
          days.push(diasSemana[check + 7])
        }
      }
      days[0] = 'HOY'
      this.chartOptions1.xaxis.categories = days.reverse()
    },
    async mesesx () {
      var meses = ['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC']
      var f = new Date()
      var mes = []
      for (var i = 0; i <= 12; i++) {
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

<template>
  <div class="row">
      <div class="col-xs-12 col-md-6 q-pa-md">
        <q-card class="col-xs-12 col-md-12">
          <q-card-section class="bg-dark text-secondary">
            <div class="text-h20"><center>Facturación Mensual (Pesos)</center></div>
          </q-card-section>
          <q-separator />
          <div id="chart">
            <apexchart ref="chart3" type="line" height="500" :options="chartOptions3" :series="series3" class="fig"></apexchart>
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
            <div class="text-h20"><center>Ventas Mensuales por Vendedor (Pesos)</center></div>
          </q-card-section>
          <q-separator />
          <div id="chart">
            <apexchart ref="chart5" type="bar" height="500" :options="chartOptions5" :series="series5" class="fig"></apexchart>
          </div>
        </q-card>
      </div>
      <div class="col-xs-12 col-md-6 q-pa-md">
        <q-card class="col-xs-12 col-md-12">
          <q-card-section class="bg-dark text-secondary">
            <div class="text-h20"><center>Cajas Mensuales por Vendedor</center></div>
          </q-card-section>
          <q-separator />
          <div id="chart">
            <apexchart ref="chart6" type="bar" height="500" :options="chartOptions6" :series="series6" class="fig"></apexchart>
          </div>
        </q-card>
      </div>
      <div class="col-xs-12 col-md-6 q-pa-md">
        <q-card class="col-xs-12 col-md-12">
          <q-card-section class="bg-dark text-secondary">
            <div class="text-h20"><center>Top 10 Clientes</center></div>
          </q-card-section>
          <q-separator />
          <div id="chart">
            <apexchart ref="chart7" type="bar" height="500" :options="chartOptions7" :series="series7" class="fig"></apexchart>
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

        colors: ['#2196f3'],
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
              return '$' + (Number.parseFloat(cost) / 1000).toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,') + 'K'
            } else if (cost > 999900) {
              return (Number.parseFloat(cost) / 1000000).toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,') + 'M'
            } else {
              return '$' + Number.parseFloat(cost).toFixed(2)
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
            text: 'Pesos'
          },
          labels: {
            type: 'String',
            show: true,
            formatter: function (val) {
              if (Math.trunc(val) === 0) {
                return '$' + (Number.parseFloat(val) / 1000).toFixed(1).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,') + 'K'
              } else {
                return '$' + (Number.parseFloat(val) / 1000).toFixed(1).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,') + 'K'
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

        colors: ['#e91e63'],
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
            // return val
            // return (Number.parseFloat(val) / 1000).toFixed(1).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,') + 'K'
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
            text: 'Pesos'
          },
          labels: {
            type: 'String',
            show: true,
            formatter: function (val) {
              if (Math.trunc(val) === 0) {
                return '$' + (Number.parseFloat(val) / 1000).toFixed(1).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,') + 'K'
              } else {
                return '$' + (Number.parseFloat(val) / 1000).toFixed(1).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,') + 'K'
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
        colors: ['#4caf50'],
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
              return '$' + (Number.parseFloat(cost) / 1000).toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,') + 'K'
            } else if (cost > 999900) {
              return (Number.parseFloat(cost) / 1000000).toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,') + 'M'
            } else {
              return '$' + Number.parseFloat(cost).toFixed(2)
            }
            // return (Number.parseFloat(val) / 1000).toFixed(1).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,') + 'K'
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
            text: 'Pesos'
          },
          labels: {
            type: 'String',
            // show: true,
            formatter: function (val) {
              if (Math.trunc(val) === 0) {
                return '$' + (Number.parseFloat(val) / 1000).toFixed(1).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,') + 'K'
              } else {
                return '$' + (Number.parseFloat(val) / 1000).toFixed(1).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,') + 'K'
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
      series7: [],
      chartOptions7: {
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
              return '$' + (Number.parseFloat(cost) / 1000).toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,') + 'K'
            } else if (cost > 999900) {
              return (Number.parseFloat(cost) / 1000000).toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,') + 'M'
            } else {
              return '$' + Number.parseFloat(cost).toFixed(2)
            }
            // return (Number.parseFloat(val) / 1000).toFixed(1).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,') + 'K'
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
            text: 'Pesos'
          },
          labels: {
            type: 'String',
            // show: true,
            formatter: function (val) {
              if (Math.trunc(val) === 0) {
                return '$' + (Number.parseFloat(val) / 1000).toFixed(1).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,') + 'K'
              } else {
                return '$' + (Number.parseFloat(val) / 1000).toFixed(1).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,') + 'K'
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
            text: 'Semana'
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
                return '$' + (Number.parseFloat(val) / 1000).toFixed(1).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,') + 'K'
              }
            }
          }
        },
        legend: {
          position: 'top',
          horizontalAlign: 'right',
          floating: true,
          offsetY: -25,
          offsetX: -5
        }
      },
      chartOptions5: {
        chart: {
          stacked: true,
          height: 500,
          type: 'line',
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
                return '$' + (Number.parseFloat(val) / 1000).toFixed(1).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,') + 'K'
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
      chartOptions6: {
        chart: {
          stacked: true,
          height: 700,
          type: 'line',
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
              return (cost / 1000) + 'K'
            } else if (cost > 999900) {
              return (cost / 1000000) + 'M'
            } else {
              return cost
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
        legend: {
          show: false,
          position: 'top',
          horizontalAlign: 'right',
          floating: true,
          offsetY: -25,
          offsetX: -5
        }
      },
      series6: [],
      series5: [],
      series3: [{
        name: 'Almacén',
        type: 'column',
        stacked: true,
        data: []
      }]
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
      yearSalesCharts: 'dashboard/payment/yearSalesCharts',
      monthSellerSalesCharts: 'dashboard/payment/monthSellerSalesCharts',
      monthSellerBoxesCharts: 'dashboard/payment/monthSellerBoxesCharts',
      top10BestCustomers: 'dashboard/payment/top10BestCustomers'
    }),
    loadAll () {
      this.yearSalesCharts().then(async ({ data }) => {
        this.series3 = [{ data: Object.values(data.yearSalesCharts.yearSale).reverse() }]
        this.$refs.chart3.updateOptions(this.chartOptions3)
      })
      this.monthSellerSalesCharts().then(async ({ data }) => {
        for (let i = 0; i < data.monthSellerSalesCharts.length; i++) {
          this.series5.push({ name: data.monthSellerSalesCharts[i].name, data: data.monthSellerSalesCharts[i].series })
          this.$refs.chart5.updateOptions({ series: this.series5 })
        }
      })
      this.monthSellerBoxesCharts().then(async ({ data }) => {
        for (let i = 0; i < data.monthSellerBoxesCharts.length; i++) {
          this.series6.push({ name: data.monthSellerBoxesCharts[i].name, data: data.monthSellerBoxesCharts[i].series })
          this.$refs.chart6.updateOptions({ series: this.series6 })
        }
      })
      this.top10BestCustomers().then(async ({ data }) => {
        console.log(data)
        for (let i = 0; i < data.top10BestCustomers.length; i++) {
          this.series7.push({ name: data.top10BestCustomers[i].name, data: data.top10BestCustomers[i].series })
          this.$refs.chart7.updateOptions({ series: this.series7 })
        }
      })
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
      this.chartOptions5.xaxis.categories = mes
      this.chartOptions7.xaxis.categories = mes
      this.chartOptions6.xaxis.categories = mes
    }
  },
  validations: {
  }
}
</script>

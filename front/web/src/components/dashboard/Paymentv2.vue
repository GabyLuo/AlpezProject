<template>
  <div>
    <div class="row">
      <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4" style="padding: .5%;">
        <q-card class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
          <q-card-section class="bg-dark text-secondary">
            <div class="text-h20"><center>Cobranza por Agente</center></div>
          </q-card-section>
          <q-separator />
          <div id="chart" style="min-height: 370px; max-height: 400px;">
            <apexchart type="line" height="350" :options="chartPaymentsPerAgents" :series="graph.series.fields.paymentPerAgents"></apexchart>
          </div>
        </q-card>
      </div>
      <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4" style="padding: .5%;">
        <q-card class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
          <q-card-section class="bg-dark text-secondary">
            <div class="text-h20"><center>Tipo de remisiones: {{this.graph.data.accountsDue}}</center></div>
          </q-card-section>
          <q-separator />
          <div class="graphs" style="min-height: 374px; max-height: 414px;">
            <apexchart class="prueba" type="donut" :options="chartTypeOfSale" :series="graph.series.fields.accountsDue"></apexchart>
          </div>
        </q-card>
      </div>
      <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4" style="padding: .5%;">
        <q-card class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
          <q-card-section class="bg-dark text-secondary">
            <div class="text-h20"><center>Cuentas CxC</center></div>
          </q-card-section>
          <q-separator />
          <div class="graphs" style="min-height: 370px; max-height: 410px;">
            <apexchart  type="donut" :options="chartAccountsDue" :series="graph.series.fields.typeOfSale"></apexchart>
          </div>
        </q-card>
      </div>
    </div>
    <div class="row" >
      <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6" style="padding: .5%;">
        <q-card class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
          <q-card-section class="bg-dark text-secondary">
            <div class="text-h20"><center>Mejores 7 Clientes</center></div>
          </q-card-section>
          <q-separator />
          <div id="chart">
            <apexchart type="line" height="350" :options="chartBest5" :series="graph.series.fields.best5"></apexchart>
          </div>
        </q-card>
      </div>
      <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6" style="padding: .5%;">
        <q-card class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
          <q-card-section class="bg-dark text-secondary">
            <div class="text-h20"><center>Peores 7 Clientes</center></div>
          </q-card-section>
          <q-separator />
          <div id="chart">
            <apexchart type="line" height="350" :options="chartWorst5" :series="graph.series.fields.worst5"></apexchart>
          </div>
        </q-card>
      </div>
    </div>
    <div class="row" >
      <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6" style="padding: .5%;">
        <q-card class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
          <q-card-section class="bg-dark text-secondary">
            <div class="text-h20"><center>Forecast 8 semanas</center></div>
          </q-card-section>
          <q-separator />
          <div id="chart">
            <apexchart type="line" height="350" :options="chartforecast8w" :series="graph.series.fields.forecast8w"></apexchart>
          </div>
        </q-card>
      </div>
      <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6" style="padding: .5%;">
        <q-card class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
          <q-card-section class="bg-dark text-secondary">
            <div class="text-h20"><center>Historial de Recuperación Promedio</center></div>
          </q-card-section>
          <q-separator />
          <q-card-section>
            <apexchart type="line" height="350" :options="chartaverageRecoveryHistory" :series="graph.series.fields.averageRecoveryHistory"></apexchart>
          </q-card-section>
        </q-card>
      </div>
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
  name: 'Paymentv2',
  components: {
    apexchart: VueApexCharts
  },
  props: {
  },
  created () {
  },
  data () {
    return {
      chartTypeOfSale: {
        chart: {
          type: 'donut'
        },
        tooltip: {
          enabled: false
        },
        responsive: [{
          breakpoint: undefined,
          options: {
          }
        }],
        plotOptions: {
          pie: {
            donut: {
              labels: {
                show: true,
                name: {
                  show: true,
                  fontSize: '15 px',
                  fontFamily: 'Nunito',
                  fontWeight: 600,
                  color: undefined,
                  offsetY: -10,
                  formatter: function (val) {
                    return val
                  }
                },
                value: {
                  formatter: function (val, index) {
                    return val + ' Rem.'
                  }
                }
              }
            },
            size: '90%'
          },
          customScale: 1.1
        },
        colors: ['#C10015', '#21BA45', '#F2C037'],
        labels: ['CREDITO', 'CONTADO', 'ANTICIPADO']
      },
      chartAccountsDue: {
        chart: {
          type: 'donut'
        },
        tooltip: {
          enabled: false
        },
        plotOptions: {
          pie: {
            donut: {
              labels: {
                show: true,
                name: {
                  show: true,
                  fontSize: '15px',
                  fontFamily: 'Nunito',
                  fontWeight: 'normal',
                  color: undefined,
                  offsetY: -10,
                  formatter: function (val) {
                    return val
                  }
                },
                value: {
                  formatter: function (val, index) {
                    if (Math.trunc(val) >= 1000000) {
                      return Number.parseFloat(val / 1000000).toFixed(2) + ' M'
                    } else if (Math.trunc(val) >= 100000) {
                      return Number.parseFloat(val / 100000).toFixed(2) + ' K'
                    }
                  }
                }
              }
            }
          }
        },
        responsive: [{
          breakpoint: undefined,
          options: {
            legend: {
              position: 'bottom',
              offsetX: -10,
              offsetY: 0
            }
          }
        }],
        colors: ['#c10015', '#21ba45'],
        labels: ['VENCIDAS', 'AL CORRIENTE']
      },
      chartPaymentsPerAgents: {
        colors: ['#21ba45', '#c10015'],
        chart: {
          height: 700,
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
          curve: ['straight', 'straight'],
          width: [0, 0]
        },
        dataLabels: {
          enabled: true,
          enabledOnSeries: [0, 1],
          formatter: function (val) {
            if (val > 1000000) {
              return Number.parseFloat(val / 1000000).toFixed(1) + 'M'
            } else if (val > 100000) {
              return Number.parseFloat(val / 1000).toFixed(1) + 'K'
            } else {
              return Number.parseFloat(val / 1000).toFixed(1) + 'K'
            }
          }
        },
        plotOptions: {
          bar: {
            borderRadius: 3,
            horizontal: false,
            customScale: 1.1
          }
        },
        xaxis: {
          type: 'category',
          categories: [],
          labels: {
            show: true,
            rotate: -45,
            rotateAlways: false,
            hideOverlappingLabels: true,
            showDuplicates: false,
            trim: false,
            minHeight: undefined,
            maxHeight: 120,
            style: {
              colors: [],
              fontSize: '12px',
              fontFamily: 'Nunito',
              fontWeight: 400,
              cssClass: 'apexcharts-xaxis-label'
            }
          },
          tickPlacement: 'between',
          position: 'bottom',
          tooltip: {
            enabled: true,
            formatter: undefined,
            offsetY: 0,
            style: {
              fontSize: 16,
              fontFamily: 'Nunito'
            }
          }
        },
        yaxis: {
          max: function (max) {
            return max * 1
          },
          title: {
            text: ''
          },
          labels: {
            type: 'String',
            show: true,
            formatter: function (val) {
              if (val > 1000000) {
                return Number.parseFloat(val / 1000000).toFixed(1) + 'M'
              } else if (val > 100000) {
                return Number.parseFloat(val / 1000).toFixed(1) + 'K'
              }
            }
          }
        }
      },
      chartBest5: {
        colors: ['#21ba45'],
        chart: {
          height: 700,
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
          curve: ['straight', 'straight'],
          width: [0, 0]
        },
        dataLabels: {
          enabled: true,
          enabledOnSeries: [0, 1],
          formatter: function (val) {
            return Number.parseFloat(val)
          }
        },
        xaxis: {
          type: 'category',
          categories: [],
          labels: {
            show: true,
            rotate: -45,
            rotateAlways: false,
            hideOverlappingLabels: true,
            showDuplicates: false,
            trim: false,
            minHeight: undefined,
            maxHeight: 120,
            style: {
              colors: [],
              fontSize: '10px',
              fontFamily: 'Nunito',
              fontWeight: 400,
              cssClass: 'apexcharts-xaxis-label'
            }
          },
          tickPlacement: 'between',
          position: 'bottom',
          tooltip: {
            enabled: true,
            formatter: undefined,
            offsetY: 0,
            style: {
              fontSize: 16,
              fontFamily: 'Nunito'
            }
          }
        },
        yaxis: {
          max: function (max) {
            return max * 1
          },
          title: {
            text: 'No. de Remisiones'
          },
          labels: {
            type: 'String',
            show: true,
            formatter: function (val) {
              if (Math.trunc(val) === 0) {
                return Number.parseFloat(val).toFixed(0)
              } else {
                return Number.parseFloat(val).toFixed(0)
              }
            }
          }
        }
      },
      chartWorst5: {
        colors: ['#c10015'],
        chart: {
          height: 700,
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
          curve: ['straight', 'straight'],
          width: [0, 0]
        },
        dataLabels: {
          enabled: true,
          enabledOnSeries: [0, 1],
          formatter: function (val) {
            return Number.parseFloat(val)
          }
        },
        xaxis: {
          type: 'category',
          categories: [],
          labels: {
            show: true,
            rotate: -45,
            rotateAlways: false,
            hideOverlappingLabels: true,
            showDuplicates: false,
            trim: false,
            minHeight: undefined,
            maxHeight: 120,
            style: {
              colors: [],
              fontSize: '10px',
              fontFamily: 'Nunito',
              fontWeight: 400,
              cssClass: 'apexcharts-xaxis-label'
            }
          },
          tickPlacement: 'between',
          position: 'bottom',
          tooltip: {
            enabled: true,
            formatter: undefined,
            offsetY: 0,
            style: {
              fontSize: 16,
              fontFamily: 'Nunito'
            }
          }
        },
        yaxis: {
          max: function (max) {
            return max * 1
          },
          title: {
            text: 'No. de Remisiones'
          },
          labels: {
            type: 'String',
            show: true,
            formatter: function (val) {
              if (Math.trunc(val) === 0) {
                return Number.parseFloat(val).toFixed(0)
              } else {
                return Number.parseFloat(val).toFixed(0)
              }
            }
          }
        }
      },
      chartforecast8w: {
        colors: ['#21ba45'],
        height: 700,
        type: 'bar',
        stacked: true,
        fontFamily: 'Nunito',
        toolbar: {
          show: false,
          tools: {
            download: false,
            selection: false,
            pan: false,
            zoom: false,
            zoomin: false,
            zoomout: false,
            reset: false
          }
        },
        stroke: {
          curve: ['straight', 'straight'],
          width: [0, 0]
        },
        dataLabels: {
          enabled: true,
          formatter: function (val) {
            if (val > 1000000) {
              return Number.parseFloat(val / 1000000).toFixed(1) + 'M'
            } else if (val > 100000) {
              return Number.parseFloat(val / 1000).toFixed(1) + 'K'
            } else if (val > 1000) {
              return Number.parseFloat(val / 1000).toFixed(1) + 'K'
            } else if (val > 1) {
              return Number.parseFloat(val).toFixed(1)
            }
          }
        },
        xaxis: {
          type: 'category',
          categories: [],
          labels: {
            show: true,
            rotate: -45,
            rotateAlways: false,
            hideOverlappingLabels: true,
            showDuplicates: false,
            trim: false,
            minHeight: undefined,
            maxHeight: 120,
            style: {
              colors: [],
              fontSize: '10px',
              fontFamily: 'Nunito',
              fontWeight: 400,
              cssClass: 'apexcharts-xaxis-label'
            }
          },
          tickPlacement: 'between',
          position: 'bottom',
          tooltip: {
            enabled: true,
            formatter: undefined,
            offsetY: 0,
            style: {
              fontSize: 16,
              fontFamily: 'Nunito'
            }
          }
        },
        yaxis: {
          max: function (max) {
            return max * 1
          },
          title: {
            text: ''
          },
          labels: {
            type: 'String',
            show: true,
            formatter: function (val) {
              if (val > 1000000) {
                return Number.parseFloat(val / 10000).toFixed(0) + 'M'
              } else if (val > 100000) {
                return Number.parseFloat(val / 1000).toFixed(1) + 'K'
              } else {
                return Number.parseFloat(val / 1000).toFixed(1) + 'K'
              }
            }
          }
        }
      },
      chartaverageRecoveryHistory: {
        colors: ['#c10015', '#21ba45'],
        height: 700,
        type: 'bar',
        stacked: true,
        fontFamily: 'Nunito',
        toolbar: {
          show: false,
          tools: {
            download: false,
            selection: false,
            pan: false,
            zoom: false,
            zoomin: false,
            zoomout: false,
            reset: false
          }
        },
        stroke: {
          curve: ['straight'],
          width: [0]
        },
        plotOptions: {
          bar: {
            colors: {
              ranges: [{
                from: 0,
                to: -500,
                color: '#F15B46'
              }, {
                from: 1,
                to: 500,
                color: '#21ba45'
              }]
            },
            columnWidth: '80%'
          }
        },
        dataLabels: {
          enabled: true,
          enabledOnSeries: [0, 1],
          style: {
            colors: ['#1976d2'],
            fontSize: '12px',
            fontWeight: 'bold'
          },
          formatter: function (val) {
            return Number.parseFloat(val).toFixed(0)
          }
        },
        yaxis: {
          title: {
            text: 'No. Remisiones'
          },
          labels: {
            formatter: function (val) {
              return val.toFixed(0)
            }
          }
        },
        xaxis: {
          type: 'category',
          categories: [],
          labels: {
            show: true,
            rotate: -45,
            rotateAlways: false,
            hideOverlappingLabels: true,
            showDuplicates: false,
            trim: false,
            minHeight: undefined,
            maxHeight: 120,
            style: {
              colors: [],
              fontSize: '10px',
              fontFamily: 'Nunito',
              fontWeight: 400,
              cssClass: 'apexcharts-xaxis-label'
            }
          },
          tickPlacement: 'between',
          position: 'bottom',
          tooltip: {
            enabled: true,
            formatter: undefined,
            offsetY: 0,
            style: {
              fontSize: 16,
              fontFamily: 'Nunito'
            }
          }
        }
      },
      graph: {
        series: {
          fields: {
            accountsDue: [],
            typeOfSale: [],
            paymentPerAgents: [],
            best5: [],
            worst5: [],
            forecast8w: [],
            averageRecoveryHistory: []
          }
        },
        data: {
          accountsDue: ''
        }
      },
      label: [],
      color: []
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
      paymentsPerAgents: 'dashboard/payment/paymentsPerAgents',
      typeOfSale: 'dashboard/payment/typeOfSale',
      accountsDue: 'dashboard/payment/accountsDue',
      best5: 'dashboard/payment/best5',
      worst5: 'dashboard/payment/worst5',
      averageRecoveryHistory: 'dashboard/payment/averageRecoveryHistory',
      forecast8w: 'dashboard/payment/forecast8w'
    }),
    loadAll () {
      this.paymentsPerAgents().then(({ data }) => {
        this.graph.series.fields.paymentPerAgents = [{ name: 'CORRIENTE', stacked: false, type: 'bar', data: data.series_corriente }, { name: 'VENCIDAS', type: 'bar', stacked: false, data: data.series_vencida }]
        this.label = Object.values(data.label)
        this.label.forEach(det => {
          this.chartPaymentsPerAgents.xaxis.categories.push(' ' + det)
        })
      })
      this.typeOfSale().then(({ data }) => {
        this.graph.series.fields.typeOfSale = data.series
        this.chartTypeOfSale.labels = Object.values(data.label)
      })
      this.accountsDue().then(({ data }) => {
        this.graph.series.fields.accountsDue = data.series
        this.graph.data.accountsDue = data.month_11
      })
      this.best5().then(({ data }) => {
        this.graph.series.fields.best5 = [{ name: 'MEJORES 5', stacked: false, type: 'bar', data: data.series }]
        this.label = Object.values(data.label)
        this.label.forEach(det => {
          this.chartBest5.xaxis.categories.push(' ' + det)
        })
      })
      this.worst5().then(({ data }) => {
        this.graph.series.fields.worst5 = [{ name: 'PEORES 5', stacked: false, type: 'bar', data: data.series }]
        this.label = Object.values(data.label)
        this.label.forEach(det => {
          this.chartWorst5.xaxis.categories.push(' ' + det)
        })
      })
      this.averageRecoveryHistory().then(({ data }) => {
        this.graph.series.fields.averageRecoveryHistory = [{ name: ' ', stacked: false, type: 'bar', data: data.series }]
        this.label = Object.values(data.label)
        this.label.forEach(det => {
          this.chartaverageRecoveryHistory.xaxis.categories.push(' ' + det)
        })
        // this.color = Object.values(data.colors)
        // this.color.forEach(det => {
        //   this.chartaverageRecoveryHistory.dataLabels.style.colors.push(det)
        // })
      })
      this.forecast8w().then(({ data }) => {
        this.graph.series.fields.forecast8w = [{ name: ' ', stacked: false, type: 'bar', data: data.series }]
        this.label = Object.values(data.label)
        this.label.forEach(det => {
          this.chartforecast8w.xaxis.categories.push(' ' + det)
        })
      })
    }
  },
  validations: {
  }
}
</script>

<style lang="stylus" scoped>
.kpi-data-v2{
  font-size: 1.2em;
  font-family: 'Nunito';
}
.graphs{
  display: flex;
  justify-content: center;
  align-items: center;
  transform: scale(1);
}
</style>

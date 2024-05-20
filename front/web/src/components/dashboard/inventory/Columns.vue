<template>
  <div id="chart">
    <apexchart type="bar" height="350" :options="chartOptions" :series="series"></apexchart>
  </div>
</template>

<script>
import VueApexCharts from 'vue-apexcharts'
import Vue from 'vue'
Vue.use(VueApexCharts)
Vue.component('apexchart', VueApexCharts)

export default {
  name: 'Columns',
  props: ['labelsD', 'seriesD', 'colorsD'],
  data () {
    return {
      series: [],
      chartOptions: {},
      labels: [],
      colors: []
    }
  },
  created () {
    this.loadSeries()
    this.loadData()
  },
  mounted () {
  },
  methods: {
    loadData () {
      this.chartOptions = {
        chart: {
          height: 280,
          type: 'bar',
          toolbar: {
            show: false
          }
        },
        grid: {
          xaxis: {
            lines: {
              show: false
            }
          },
          yaxis: {
            lines: {
              show: false
            }
          }
        },
        colors: this.colorsD,
        plotOptions: {
          bar: {
            columnWidth: '45%',
            distributed: true
          }
        },
        annotations: {
          yaxis: [
            {
              y: 70,
              borderColor: '#F44336',
              label: {
                borderColor: '#F44336',
                style: {
                  color: '#fff',
                  background: '#F44336'
                },
                text: '70% capacidad'
              }
            }
          ]
        },
        dataLabels: {
          enabled: true,
          formatter: function (val) {
            return val
          }
        },
        xaxis: {
          categories: this.labelsD
        },
        yaxis: {
          min: 0,
          max: 100,
          lines: {
            show: false
          }
        },
        tooltip: {
          y: {
            formatter: function (val) {
              return val + '%'
            }
          }
        },
        title: {
          text: 'PORCENTAJE DE EXISTENCIAS',
          align: 'center',
          style: {
            fontSize: '13px',
            fontWeight: 'bold'
          }
        },
        legend: {
          position: 'bottom', // ,
          offsetY: 40
        }
      }
    },
    loadSeries () {
      this.series = [{
        name: 'Existencias',
        data: this.seriesD
      }]
    }
  }
}
</script>

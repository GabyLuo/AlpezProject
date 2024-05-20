<template>
  <apexchart type="radialBar" height="300" :options="this.chartOptions" :series="this.seriesD"></apexchart>
</template>

<script>
import VueApexCharts from 'vue-apexcharts'
import Vue from 'vue'
Vue.use(VueApexCharts)
Vue.component('apexchart', VueApexCharts)

export default {
  name: 'Radial',
  props: ['labelsD', 'seriesD', 'colorsD', 'totalD'],
  data () {
    return {
      series: [],
      chartOptions: {},
      labels: [],
      colors: []
    }
  },
  created () {
    this.loadData()
  },
  mounted () {
  },
  methods: {
    loadData () {
      this.chartOptions = {
        chart: {
          alignment: 'center',
          height: 200,
          width: 130,
          type: 'radialBar'
        },
        plotOptions: {
          radialBar: {
            offsetY: -15,
            startAngle: 0,
            endAngle: 270,
            hollow: {
              margin: 5,
              size: '30%',
              background: 'transparent',
              image: undefined
            },
            dataLabels: {
              name: {
                // show: false,
                fontSize: '12px'
              },
              value: {
                // show: false,
                fontSize: '14px'
              },
              total: {
                show: true,
                label: 'AVG ' + this.totalD + '%',
                formatter: function (w) {
                  // By default this function returns the average of all series. The below is just an example to show the use of custom formatter function
                  return this.totalD
                }
              }
            }
          }
        },
        title: {
          text: 'EXISTENCIAS ACTUALES',
          align: 'center',
          style: {
            fontSize: '13px',
            fontWeight: 'bold'
          }
        },
        colors: this.colorsD,
        labels: this.labelsD,
        legend: {
          show: true,
          floating: true,
          fontSize: '12px',
          position: 'left',
          offsetX: -15,
          offsetY: 13,
          labels: {
            useSeriesColors: true
          },
          markers: {
            size: 0
          },
          formatter: function (seriesName, opts) {
            return seriesName // + ':  ' + opts.w.globals.series[opts.seriesIndex]
          },
          itemMargin: {
            vertical: 3
          }
        },
        responsive: [{
          breakpoint: 480,
          options: {
            legend: {
              show: true
            }
          }
        }]
      }
    }
  }
}
</script>

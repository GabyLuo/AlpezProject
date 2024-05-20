<template>
  <div id="chart">
    <apexchart type="line" height="350" :options="chartOptions" :series="series"></apexchart>
  </div>
</template>

<script>
import VueApexCharts from 'vue-apexcharts'
import Vue from 'vue'
Vue.use(VueApexCharts)
Vue.component('apexchart', VueApexCharts)

export default {
  name: 'InventoryKpi',
  props: ['labelsD', 'seriesD', 'categoriesD', 'title'],
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
          type: 'line',
          events: {
            click: function (chart, w, e) {
              return e
            }
          },
          toolbar: {
            show: false
          }
        },
        colors: ['#1d1d1d', '#f44336', '#4caf50'],
        plotOptions: {
          bar: {
            columnWidth: '35%',
            distributed: true
          }
        },
        dataLabels: {
          enabled: false
        },
        xaxis: {
          categories: this.categoriesD
        },
        tooltip: {
          y: {
            formatter: function (val) {
              return val + 'K Litros'
            }
          }
        },
        title: {
          text: this.title,
          align: 'center',
          style: {
            fontSize: '13px',
            fontWeight: 'bold'
          }
        },
        stroke: {
          show: true,
          curve: 'smooth',
          lineCap: 'butt',
          colors: undefined,
          width: 5,
          dashArray: 0
        },
        legend: {
          position: 'bottom', // ,
          offsetY: 40
        }
      }
    },
    loadSeries () {
      this.series = this.seriesD
    }
  }
}
</script>

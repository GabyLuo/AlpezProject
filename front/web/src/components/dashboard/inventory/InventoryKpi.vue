<template>
  <div>
  <div class="row q-col-gutter-md">
    <div class="col-xs-12 col-md-3">
      <q-card class="my-card bg-warning text-white" square bordered>
        <q-item>
          <q-item-section avatar>
            <q-avatar >
              <q-icon name="event" size="40px" color="white"/>
            </q-avatar>
          </q-item-section>
          <q-item-section class="text-center">
            <q-item-label caption style="font-size: 32px;" class="text-bold text-white">{{ ocAmount }}</q-item-label>
            <q-item-label style="font-size: 16px;">OCs Pendientes por Recibir</q-item-label>
          </q-item-section>
        </q-item>
        <q-inner-loading>
          <q-spinner-audio size="50px" color="white" />
        </q-inner-loading>
      </q-card>
    </div>
    <div class="col-xs-12 col-md-3">
      <q-card class="my-card bg-positive text-white" square bordered>
        <q-item>
          <q-item-section avatar>
            <q-avatar >
              <q-icon name="event" size="40px" color="white" flat/>
            </q-avatar>
          </q-item-section>
          <q-item-section class="text-center">
            <q-item-label caption style="font-size: 32px;" class="text-bold text-white">{{ productsAmount }}</q-item-label>
            <q-item-label style="font-size: 16px;">Articulos sin Existencia</q-item-label>
          </q-item-section>
        </q-item>
        <q-inner-loading :showing="loadingDaily">
          <q-spinner-audio size="50px" color="primary" />
        </q-inner-loading>
      </q-card>
    </div>
    <div class="col-xs-12 col-md-3">
      <q-card class="my-card bg-indigo-3 text-white" square bordered>
        <q-item>
          <q-item-section avatar>
            <q-avatar>
              <q-icon name="align_vertical_bottom" size="40px" color="deep-purple"/>
            </q-avatar>
          </q-item-section>
          <q-item-section class="text-center">
            <q-item-label caption style="font-size: 32px;" class="text-bold">{{ entryAmount }}</q-item-label>
            <q-item-label style="font-size: 16px;">Entradas del Día</q-item-label>
          </q-item-section>
        </q-item>
        <q-inner-loading :showing="loadingDaily">
          <q-spinner-audio size="50px" color="primary" />
        </q-inner-loading>
      </q-card>
    </div>
    <div class="col-xs-12 col-md-3">
      <q-card class="my-card bg-positive text-white" square bordered>
        <q-item>
          <q-item-section avatar>
            <q-avatar>
              <q-icon name="align_vertical_bottom" size="40px" color="white"/>
            </q-avatar>
          </q-item-section>
          <q-item-section class="text-center">
            <q-item-label caption style="font-size: 32px;" class="text-bold text-white">{{ convertinventaryCostAmount(inventaryCostAmount) }}</q-item-label>
            <q-item-label style="font-size: 16px;">Costo de Inventario</q-item-label>
          </q-item-section>
        </q-item>
        <q-inner-loading :showing="loadingDaily">
          <q-spinner-audio size="50px" color="primary" />
        </q-inner-loading>
      </q-card>
    </div>
  </div>
  <div class="row q-col-gutter-md q-pt-md">
    <div class="col-xs-12 col-md-6">
      <q-card class="my-card bg-blue-7 text-white" square bordered>
        <q-item>
          <q-item-section avatar>
            <q-avatar>
              <q-icon name="event" size="40px" color="white"/>
            </q-avatar>
          </q-item-section>
          <q-item-section class="text-center">
            <q-item-label caption style="font-size: 32px;" class="text-bold text-white">{{ (purchasesInArribo) }}</q-item-label>
            <q-item-label style="font-size: 16px;">OCs en Tránsito</q-item-label>
          </q-item-section>
        </q-item>
        <q-inner-loading :showing="loadingDaily">
          <q-spinner-audio size="50px" color="primary" />
        </q-inner-loading>
      </q-card>
    </div>
    <div class="col-xs-12 col-md-3">
      <q-card class="my-card bg-blue-7 text-white" square bordered>
        <q-item>
          <q-item-section avatar>
            <q-avatar>
              <q-icon name="event" size="40px" color="white"/>
            </q-avatar>
          </q-item-section>
          <q-item-section class="text-center">
            <q-item-label caption style="font-size: 32px;" class="text-bold text-white">{{ weeklyArribos }}</q-item-label>
            <q-item-label style="font-size: 16px;">Arribos de esta semana</q-item-label>
          </q-item-section>
        </q-item>
        <q-inner-loading :showing="loadingDaily">
          <q-spinner-audio size="50px" color="primary" />
        </q-inner-loading>
      </q-card>
    </div>
    <div class="col-xs-12 col-md-3">
      <q-card class="my-card bg-positive text-white" square bordered>
        <q-item>
          <q-item-section avatar>
            <q-avatar>
              <q-icon name="align_vertical_bottom" size="40px" color="white"/>
            </q-avatar>
          </q-item-section>
          <q-item-section class="text-center">
            <q-item-label caption style="font-size: 30px;" class="text-bold text-white">{{ convertinventaryCostAmountAVG }}</q-item-label>
            <q-item-label style="font-size: 14px;">Costo Promedio de Inventario</q-item-label>
          </q-item-section>
        </q-item>
        <q-inner-loading :showing="loadingDaily">
          <q-spinner-audio size="50px" color="primary" />
        </q-inner-loading>
      </q-card>
    </div>
  </div>
  <div class="row">
    <div class="col-xs-12 col-md-12 q-pa-md">
      <q-card class="col-xs-12 col-md-12">
        <q-card-section class="bg-green-5 text-white text-bold">
          <div class="text-h20"><center>Existencias totales (litros)</center></div>
        </q-card-section>
        <q-separator />
        <div id="chartStock">
          <apexchart  type="line" height="300" :options="chartStock1" :series="seriesStock"></apexchart>
        </div>
        <template>
        </template>
      </q-card>
    </div>
    <div class="col-xs-12 col-md-6 q-pa-md">
      <q-card class="col-xs-12 col-md-12">
        <q-card-section class="bg-green-5 text-white text-bold">
          <div class="text-h20"><center>Existencias semanales (litros)</center></div>
        </q-card-section>
        <q-separator />
        <div id="chartStock">
          <apexchart  type="line" height="300" :options="stockOptionsWeekly" :series="seriesStockWeekly"></apexchart>
        </div>
        <template>
        </template>
      </q-card>
    </div>
    <div class="col-xs-12 col-md-6 q-pa-md">
      <q-card class="col-xs-12 col-md-12">
        <q-card-section class="bg-green-5 text-white text-bold">
          <div class="text-h20"><center>Existencia anual (litros)</center></div>
        </q-card-section>
        <q-separator />
        <div id="chartStock">
          <apexchart  type="line" height="300" :options="stockOptionsAnual" :series="seriesStockAnual"></apexchart>
        </div>
        <template>
        </template>
      </q-card>
    </div>
    <div class="col-xs-12 col-md-12 q-pa-md">
      <q-card class="col-xs-12 col-md-12">
        <q-card-section class="bg-green-5 text-white text-bold">
          <div class="text-h20"><center>Costo de Inventario Diario (Pesos)</center></div>
        </q-card-section>
        <q-separator />
        <div id="chart">
          <apexchart  type="line" height="300" :options="chartOptions1" :series="series" class="fig"></apexchart>
        </div>
        <template>
        </template>
      </q-card>
    </div>
    <div class="col-xs-12 col-md-6 q-pa-md">
      <q-card class="col-xs-12 col-md-12">
        <q-card-section class="bg-green-5 text-white text-bold">
          <div class="text-h20"><center>Costo de Inventario Semanal (Pesos)</center></div>
        </q-card-section>
        <q-separator />
        <div id="chart">
          <apexchart  type="line" height="300" :options="chartOptions2" :series="series2" class="fig"></apexchart>
        </div>
        <template>
        </template>
      </q-card>
    </div>
    <div class="col-xs-12 col-md-6 q-pa-md">
      <q-card class="col-xs-12 col-md-12">
        <q-card-section class="bg-green-5 text-white text-bold">
          <div class="text-h20"><center>Costo de Inventario Mensual (Pesos)</center></div>
        </q-card-section>
        <q-separator />
        <div id="chart">
          <apexchart  type="line" height="300" :options="chartOptions3" :series="series3" class="fig"></apexchart>
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
// import { mapActions, mapGetters } from 'vuex'
import VueApexCharts from 'vue-apexcharts'
// import axios from 'axios'
import Vue from 'vue'
Vue.use(VueApexCharts)
Vue.component('apexchart', VueApexCharts)

export default {
  name: 'InventoryKpi',
  data () {
    return {
      startDate: null,
      currentTab: 'sales',
      ocAmount: 0,
      productsAmount: 0,
      entryAmount: 0,
      purchasesInArribo: 0,
      weeklyArribos: 0,
      inventaryCostAmount: 0,
      inventaryCostAmountAVG: 0,
      loadingDaily: false,
      loadingMonthly: true,
      pendientes: 0,
      autorizados: 0,
      enviados: 0,
      nuevos: 0,
      solicitados: 0,
      parciales: 0,
      entregados: 0,
      actualmonth: 0,
      stockLabel: [],
      stockSeries: [],
      //
      seriesStock: [],
      labelStock: [],
      stockOptionsWeekly: {
        colors: ['#1d1d1d', '#f44336', '#4caf50'],
        chart: {
          height: 700,
          type: 'bar'
        },
        dataLabels: {
          enabled: true
        },
        plotOptions: {
          bar: {
            horizontal: false,
            columnWidth: '65%',
            endingShape: 'rounded'
          }
        },
        stroke: {
          show: true,
          width: 2,
          colors: ['transparent']
        },
        xaxis: {
          categories: ['Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct']
        },
        fill: {
          opacity: 1
        },
        tooltip: {
          y: {
            formatter: function (val) {
              return val + 'K'
            }
          }
        }
      },
      seriesStockWeekly: [],
      stockOptionsAnual: {
        colors: ['#1d1d1d', '#f44336', '#4caf50'],
        chart: {
          height: 700,
          type: 'bar'
        },
        dataLabels: {
          enabled: true
        },
        plotOptions: {
          bar: {
            horizontal: false,
            columnWidth: '65%',
            endingShape: 'rounded'
          }
        },
        stroke: {
          show: true,
          width: 2,
          colors: ['transparent']
        },
        xaxis: {
          categories: ['Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct']
        },
        fill: {
          opacity: 1
        },
        tooltip: {
          y: {
            formatter: function (val) {
              return val + 'K'
            }
          }
        }
      },
      seriesStockAnual: [],
      chartStock1: {
        colors: ['#1d1d1d', '#f44336', '#4caf50'],
        chart: {
          height: 700,
          type: 'bar'
        },
        dataLabels: {
          enabled: true
        },
        plotOptions: {
          bar: {
            horizontal: false,
            columnWidth: '65%',
            endingShape: 'rounded'
          }
        },
        stroke: {
          show: true,
          width: 2,
          colors: ['transparent']
        },
        xaxis: {
          categories: ['Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct']
        },
        fill: {
          opacity: 1
        },
        tooltip: {
          y: {
            formatter: function (val) {
              return val + 'K'
            }
          }
        }
      },
      chartOptions1: {

        colors: ['#2196f3'],
        chart: {
          height: 700,
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
          }
        },
        xaxis: {
          categories: [],
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
      chartOptions2: {

        colors: ['#e91e63'],
        chart: {
          height: 700,
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
          height: 700,
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
      stockOptions: {
        colors: ['#e91e63'],
        chart: {
          height: 700,
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
          categories: [],
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
      series: [{
        name: 'Almacén',
        data: []
      }],
      series2: [{
        name: 'Almacén',
        data: []
      }],
      series3: [{
        name: 'Almacén',
        data: []
      }],
      semanas: [],
      semanasLabel: []
    }
  },
  computed: {
    convertinventaryCostAmountAVG () {
      const cost = this.inventaryCostAmountAVG
      if (cost >= 1000 && cost <= 999900) {
        return '$' + (Number.parseFloat(cost) / 1000).toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,') + 'K'
      } else if (cost > 999900) {
        return (Number.parseFloat(cost) / 1000000).toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,') + 'M'
      } else {
        return '$' + Number.parseFloat(cost).toFixed(2)
      }
    }
  },
  created () {
    this.all()
  },
  mounted () {
  },
  methods: {
    async all () {
      this.$q.loading.show()
      await this.loadData()
      await this.grafsStock1()
      await this.diasSemanax()
      await this.grafsStockWeekly()
      await this.grafsStockAnual()
      await this.grafs1()
      await this.grafs2()
      await this.grafs3()
      await this.mesesx()
      this.$q.loading.hide()
    },
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
    loadData () {
      this.$q.loading.show()
      // this.loadExpenses()
      api.get('/dashboard/getPurchases/null').then(({ data }) => {
        this.entryAmount = data.entryDays
        this.ocAmount = data.ocs
        this.inventaryCostAmount = data.inventaryCost
        this.inventaryCostAmountAVG = data.inventaryCostAVG
        this.productsAmount = data.noStock
        this.purchasesInArribo = data.embarco
        this.weeklyArribos = data.weeklyArribos
        // this.$q.loading.hide()
      })
    },
    async grafsStock1 () {
      await api.get('/dashboard/getStockCostDaily').then(({ data }) => {
        this.seriesStock = [{ name: 'Diesel', data: data.inventory_daily.inventory_amountsD }, { name: 'Premium', data: data.inventory_daily.inventory_amountsP }, { name: 'Regular', data: data.inventory_daily.inventory_amountsR }]
        this.diasSemanax2()
      }).catch(error => error)
      this.chartStock1 = {
        colors: ['#1d1d1d', '#f44336', '#4caf50'],
        chart: {
          height: 300,
          type: 'bar'
        },
        dataLabels: {
          enabled: true
        },
        plotOptions: {
          bar: {
            horizontal: false,
            columnWidth: '65%',
            endingShape: 'rounded'
          }
        },
        stroke: {
          show: true,
          width: 2,
          colors: ['transparent']
        },
        series: this.seriesStock,
        xaxis: {
          categories: this.labelStock,
          labels: {
            type: 'String',
            show: true
          }
        },
        fill: {
          opacity: 1
        },
        tooltip: {
          y: {
            formatter: function (val) {
              return val + 'K'
            }
          }
        }
      }
    },
    async grafsStockWeekly () {
      await api.get('/dashboard/getStockCostWeekly').then(({ data }) => {
        this.seriesStockWeekly = [{ name: 'Diesel', data: data.inventory_daily.inventory_amountsD }, { name: 'Premium', data: data.inventory_daily.inventory_amountsP }, { name: 'Regular', data: data.inventory_daily.inventory_amountsR }]
      }).catch(error => error)
      this.stockOptionsWeekly = {
        colors: ['#1d1d1d', '#f44336', '#4caf50'],
        chart: {
          height: 300,
          type: 'bar'
        },
        dataLabels: {
          enabled: true
        },
        plotOptions: {
          bar: {
            horizontal: false,
            columnWidth: '65%',
            endingShape: 'rounded'
          }
        },
        stroke: {
          show: true,
          width: 2,
          colors: ['transparent']
        },
        series: this.seriesStockWeekly,
        xaxis: {
          categories: ['Sem5', 'Sem4', 'Sem3', 'Sem2', 'Sem1', 'ACTUAL'],
          labels: {
            type: 'String',
            show: true
          }
        },
        fill: {
          opacity: 1
        },
        tooltip: {
          y: {
            formatter: function (val) {
              return val + 'K'
            }
          }
        }
      }
    },
    async grafsStockAnual () {
      await api.get('/dashboard/getStockCostAnual').then(({ data }) => {
        this.seriesStockAnual = [{ name: 'Diesel', data: data.inventory_daily.inventory_amountsD }, { name: 'Premium', data: data.inventory_daily.inventory_amountsP }, { name: 'Regular', data: data.inventory_daily.inventory_amountsR }]
      }).catch(error => error)
      this.stockOptionsAnual = {
        colors: ['#1d1d1d', '#f44336', '#4caf50'],
        chart: {
          height: 300,
          type: 'bar'
        },
        dataLabels: {
          enabled: true
        },
        plotOptions: {
          bar: {
            horizontal: false,
            columnWidth: '65%',
            endingShape: 'rounded'
          }
        },
        stroke: {
          show: true,
          width: 2,
          colors: ['transparent']
        },
        series: this.seriesStockAnual,
        xaxis: {
          categories: ['Sem5', 'Sem4', 'Sem3', 'Sem2', 'Sem1', 'ACTUAL'],
          labels: {
            type: 'String',
            show: true
          }
        },
        fill: {
          opacity: 1
        },
        tooltip: {
          y: {
            formatter: function (val) {
              return val + 'K'
            }
          }
        }
      }
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
      // this.chartStock1.xaxis.categories = days.reverse()
      this.chartOptions1.xaxis.categories = days
    },
    async diasSemanax2 () {
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
      this.labelStock = days.reverse()
    },
    async grafs1 () {
      await api.get('/dashboard/getInventoryCostDaily').then(({ data }) => {
        this.chartOptions1.yaxis.max = parseFloat(data.inventory_daily.maximo)
        this.series = [{ name: 'Almácen', type: 'line', stacked: true, data: Object.values(data.inventory_daily.inventory_amounts).reverse() }]
      }).catch(error => error)
      this.chartOptions1 = {

        colors: ['#2196f3'],
        chart: {
          height: 300,
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
          }
        },
        series: this.series,
        xaxis: {
          categories: this.labelStock,
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
      }
    },
    async grafs2 () {
      await api.get('/dashboard/getInventoryCostWeekly').then(({ data }) => {
        this.chartOptions2.yaxis.max = parseFloat(data.inventory_weekly.maximo)
        this.series2 = [{ name: 'Almácen', type: 'line', stacked: true, data: Object.values(data.inventory_weekly.inventory_amounts).reverse() }]
        this.chartOptions2.xaxis.categories = data.inventory_weekly.week.reverse()
      }).catch(error => error)
    },
    async grafs3 () {
      await api.get('/dashboard/getInventoryCostAnual').then(({ data }) => {
        this.chartOptions3.yaxis.max = parseFloat(data.inventory_anual.maximo)
        this.series3 = [{ name: 'Almácen', type: 'line', stacked: true, data: Object.values(data.inventory_anual.inventory_amounts).reverse() }]
        this.mesesx()
      }).catch(error => error)
      this.chartOptions3 = {
        colors: ['#4caf50'],
        chart: {
          height: 300,
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
        series: this.series3,
        xaxis: {
          categories: this.semanasLabel,
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
      }
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
      this.semanasLabel = mes.reverse()
    }
  }
}
</script>

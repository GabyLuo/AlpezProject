<template>
  <q-page>
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-3">
          <div class="q-pa-md q-gutter-sm">
              <q-breadcrumbs style="font-size: 20px">
                  <q-breadcrumbs-el label="" icon="home" to="/"/>
                  <q-breadcrumbs-el label="Dashboard"/>
              </q-breadcrumbs>
          </div>
        </div>
      </div>
    </div>

    <div class="q-pa-md bg-grey-3">
      <div class="col-md-12 col-xs-12 col-lg-12 bg-white">
        <q-tabs
          v-model="currentTab"
          dense
          active-text-color="light-green"
          class="text-grey-8"
          active-color="red"
          indicator-color="red"
          align="justify"
          narrow-indicator
          @input="changeModel"
        >
  <!--         <q-tab name="management" icon="arrow_drop_down_circle" label="DIRECCIÓN">
          </q-tab> -->
          <q-tab name="sales"  label="VENTAS" icon="point_of_sale">
          </q-tab>
          <q-tab name="payments"  label="COBRANZA" icon="credit_card">
          </q-tab>
<!--           <q-tab name="banks"  label="BANCOS" icon="credit_card">
          </q-tab> -->
          <q-tab name="inventory" icon="fas fa-clipboard-list" label="INVENTARIO">
          </q-tab>
          <q-tab name="collect"  label="COMPRAS" icon="receipt_long">
          </q-tab>
<!--           <q-tab name="production"  label="PRODUCCIÓN" icon="local_grocery_store">
          </q-tab> -->
          <q-tab name="commercial"  label="PEDIDOS" icon="fas fa-handshake">
          </q-tab>
        </q-tabs>
      </div>
        <div class="col">
          <q-tab-panels v-model="currentTab" animated>
            <q-tab-panel name="inventory" class="bg-grey-3">
        <div class="row  q-col-gutter-md">
        <div class="col-xs-12 col-md-3 q-pb-md">
          <q-select
            color="dark"
            bg-color="secondary"
            filled
            v-model="startDate"
            mask="date"
            label="Filtrar Entradas del Día"
          >
            <template v-slot:prepend>
              <q-icon name="event" />
            </template>
            <q-popup-proxy
              ref="startDateRef"
              transition-show="scale"
              transition-hide="scale"
            >
              <div class="col-sm-12">
                <q-date
                  color="secondary"
                  text-color="white"
                  v-model="startDate"
                  @input="filterEntryDays()"
                  today-btn
                />
              </div>
            </q-popup-proxy>
          </q-select>
        </div>
          </div>
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
                <q-card-section class="bg-dark text-secondary">
                  <div class="text-h20"><center>Existencias totales (litros)</center></div>
                </q-card-section>
                <q-separator />
                <div id="chartStock">
                  <apexchart  type="line" height="300" :options="chartStock1" :series="seriesStock" class="fig"></apexchart>
                </div>
                <template>
                  <div>
                    <!--  <apexchart  type="bar" :options="options" :series="series"></apexchart> -->
                  </div>
                </template>
              </q-card>
            </div>
            <div class="col-xs-12 col-md-6 q-pa-md">
              <q-card class="col-xs-12 col-md-12">
                <q-card-section class="bg-dark text-secondary">
                  <div class="text-h20"><center>Existencias semanales (litros)</center></div>
                </q-card-section>
                <q-separator />
                <div id="chartStock">
                  <apexchart  type="line" height="300" :options="stockOptionsWeekly" :series="seriesStockWeekly" class="fig"></apexchart>
                </div>
                <template>
                  <div>
                    <!--  <apexchart  type="bar" :options="options" :series="series"></apexchart> -->
                  </div>
                </template>
              </q-card>
            </div>
            <div class="col-xs-12 col-md-12 q-pa-md">
              <q-card class="col-xs-12 col-md-12">
                <q-card-section class="bg-dark text-secondary">
                  <div class="text-h20"><center>Costo de Inventario Diario (Pesos)</center></div>
                </q-card-section>
                <q-separator />
                <div id="chart">
                  <apexchart  type="line" height="300" :options="chartOptions1" :series="series" class="fig"></apexchart>
                </div>
                <template>
                  <div>
                    <!--  <apexchart  type="bar" :options="options" :series="series"></apexchart> -->
                  </div>
                </template>
              </q-card>
            </div>
            <div class="col-xs-12 col-md-6 q-pa-md">
              <q-card class="col-xs-12 col-md-12">
                <q-card-section class="bg-dark text-secondary">
                  <div class="text-h20"><center>Costo de Inventario Semanal (Pesos)</center></div>
                </q-card-section>
                <q-separator />
                <div id="chart">
                  <apexchart  type="line" height="300" :options="chartOptions2" :series="series2" class="fig"></apexchart>
                </div>
                <template>
                  <div>
                    <!--  <apexchart  type="bar" :options="options" :series="series"></apexchart> -->
                  </div>
                </template>
              </q-card>
            </div>
            <div class="col-xs-12 col-md-6 q-pa-md">
              <q-card class="col-xs-12 col-md-12">
                <q-card-section class="bg-dark text-secondary">
                  <div class="text-h20"><center>Costo de Inventario Mensual (Pesos)</center></div>
                </q-card-section>
                <q-separator />
                <div id="chart">
                  <apexchart  type="line" height="300" :options="chartOptions3" :series="series3" class="fig"></apexchart>
                </div>
                <template>
                  <div>
                    <!--  <apexchart  type="bar" :options="options" :series="series"></apexchart> -->
                  </div>
                </template>
              </q-card>
            </div>
        </div>
            </q-tab-panel>
            <q-tab-panel name="payments" class="bg-grey-3">
              <payment></payment>
              <paymentv2></paymentv2>
            </q-tab-panel>
            <q-tab-panel name="commercial" class="bg-grey-3">
          <div class="row q-col-gutter-md">
            <div class="col-xs-12 col-md-3">
              <q-card class="my-card bg-warning text-white" square bordered>
                <q-item>
                  <q-item-section avatar>
                    <q-avatar >
                      <q-icon name="align_vertical_bottom" size="40px" color="white"/>
                    </q-avatar>
                  </q-item-section>
                  <q-item-section class="text-center">
                    <q-item-label caption style="font-size: 32px;" class="text-bold text-white">{{ pendientes }}</q-item-label>
                    <q-item-label style="font-size: 16px;">Pedidos Pendientes</q-item-label>
                  </q-item-section>
                </q-item>
                <q-inner-loading>
                  <q-spinner-audio size="50px" color="white" />
                </q-inner-loading>
              </q-card>
            </div>
            <div class="col-xs-12 col-md-3">
              <q-card class="my-card bg-blue-7 text-white" square bordered>
                <q-item>
                  <q-item-section avatar>
                    <q-avatar>
                      <q-icon name="align_vertical_bottom" size="40px" color="white"/>
                    </q-avatar>
                  </q-item-section>

                  <q-item-section class="text-center">
                    <q-item-label caption style="font-size: 32px;" class="text-bold">{{ nuevos }}</q-item-label>
                    <q-item-label style="font-size: 16px;">Pedidos Nuevos</q-item-label>
                  </q-item-section>
                </q-item>
                <q-inner-loading :showing="loadingDaily">
                  <q-spinner-audio size="50px" color="primary" />
                </q-inner-loading>
              </q-card>
            </div>
            <div class="col-xs-12 col-md-3">
              <q-card class="my-card bg-yellow-8 text-white" square bordered>
                <q-item>
                  <q-item-section avatar>
                    <q-avatar>
                      <q-icon name="align_vertical_bottom" size="40px" color="white"/>
                    </q-avatar>
                  </q-item-section>

                  <q-item-section class="text-center">
                    <q-item-label caption style="font-size: 32px;" class="text-bold">{{ solicitados }}</q-item-label>
                    <q-item-label style="font-size: 16px;">Pedidos Solicitados</q-item-label>
                  </q-item-section>
                </q-item>
                <q-inner-loading :showing="loadingDaily">
                  <q-spinner-audio size="50px" color="primary" />
                </q-inner-loading>
              </q-card>
            </div>
            <div class="col-xs-12 col-md-3">
              <q-card class="my-card bg-green text-white" square bordered>
                <q-item>
                  <q-item-section avatar>
                    <q-avatar >
                      <q-icon name="align_vertical_bottom" size="40px" color="white"/>
                    </q-avatar>
                  </q-item-section>
                  <q-item-section class="text-center">
                    <q-item-label caption style="font-size: 32px;" class="text-bold text-white">{{ autorizados }}</q-item-label>
                    <q-item-label style="font-size: 16px;">Pedidos Autorizados</q-item-label>
                  </q-item-section>
                </q-item>
                <q-inner-loading>
                  <q-spinner-audio size="50px" color="white" />
                </q-inner-loading>
              </q-card>
            </div>
            <div class="col-xs-12 col-md-3">
              <q-card class="my-card bg-red-4 text-white" square bordered>
                <q-item>
                  <q-item-section avatar>
                    <q-avatar >
                      <q-icon name="align_vertical_bottom" size="40px" color="white" flat/>
                    </q-avatar>
                  </q-item-section>

                  <q-item-section class="text-center">
                    <q-item-label caption style="font-size: 32px;" class="text-bold text-white">{{ enviados }}</q-item-label>
                    <q-item-label style="font-size: 16px;">Pedidos Enviados</q-item-label>
                  </q-item-section>
                </q-item>
                <q-inner-loading :showing="loadingDaily">
                  <q-spinner-audio size="50px" color="primary" />
                </q-inner-loading>
              </q-card>
            </div>
            <div class="col-xs-12 col-md-3">
              <q-card class="my-card bg-red-3 text-white" square bordered>
                <q-item>
                  <q-item-section avatar>
                    <q-avatar>
                      <q-icon name="align_vertical_bottom" size="40px" color="white"/>
                    </q-avatar>
                  </q-item-section>

                  <q-item-section class="text-center">
                    <q-item-label caption style="font-size: 32px;" class="text-bold">{{ parciales }}</q-item-label>
                    <q-item-label style="font-size: 16px;">Pedidos Parciales</q-item-label>
                  </q-item-section>
                </q-item>
                <q-inner-loading :showing="loadingDaily">
                  <q-spinner-audio size="50px" color="primary" />
                </q-inner-loading>
              </q-card>
            </div>
            <div class="col-xs-12 col-md-3">
              <q-card class="my-card bg-purple-6 text-white" square bordered>
                <q-item>
                  <q-item-section avatar>
                    <q-avatar>
                      <q-icon name="align_vertical_bottom" size="40px" color="white"/>
                    </q-avatar>
                  </q-item-section>

                  <q-item-section class="text-center">
                    <q-item-label caption style="font-size: 32px;" class="text-bold">{{ entregados }}</q-item-label>
                    <q-item-label style="font-size: 16px;">Pedidos Entregados</q-item-label>
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
                    <q-item-label caption style="font-size: 32px;" class="text-bold">{{ actualmonth }}</q-item-label>
                    <q-item-label style="font-size: 16px;">Pedidos Por Mes Actual</q-item-label>
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
                  <q-card-section class="bg-primary text-white">
                    <div class="text-h20"><center>Pedidos Por Mes</center></div>
                  </q-card-section>
                  <q-separator />
                  <div id="chart">
                    <apexchart ref="chart5" type="line" height="500" :options="chartOptions5" :series="series5" class="fig"></apexchart>
                  </div>
                  <template>
                  </template>
                </q-card>
              </div>
              <!-- <div class="col-xs-12 col-md-12 q-pa-md">
                <q-card class="col-xs-12 col-md-12">
                  <q-card-section class="bg-primary text-white">
                    <div class="text-h20"><center>Pedidos Por Cliente (Mes)</center></div>
                  </q-card-section>
                  <q-separator />
                  <div id="chart">
                    <apexchart ref="chart6" type="bar" height="500" :options="chartOptions6" :series="series6" class="fig"></apexchart>
                  </div>
                  <template>
                  </template>
                </q-card>
              </div> -->
              <div class="col-xs-12 col-md-6 q-pa-md">
                <q-card class="col-xs-12 col-md-12">
                  <q-card-section class="bg-primary text-white">
                    <div class="text-h20"><center>Pedidos Por Vendedor (Día)</center></div>
                  </q-card-section>
                  <q-separator />
                  <div id="chart">
                    <apexchart ref="chart8" type="bar" height="500" :options="chartOptions8" :series="series8" class="fig"></apexchart>
                  </div>
                  <template>
                  </template>
                </q-card>
              </div>
              <div class="col-xs-12 col-md-6 q-pa-md">
                <q-card class="col-xs-12 col-md-12">
                  <q-card-section class="bg-primary text-white">
                    <div class="text-h20"><center>Pedidos Por Vendedor (Semana)</center></div>
                  </q-card-section>
                  <q-separator />
                  <div id="chart">
                    <apexchart ref="chart9" type="bar" height="500" :options="chartOptions9" :series="series9" class="fig"></apexchart>
                  </div>
                  <template>
                  </template>
                </q-card>
              </div>
              <div class="col-xs-12 col-md-12 q-pa-md">
                <q-card class="col-xs-12 col-md-12">
                  <q-card-section class="bg-primary text-white">
                    <div class="text-h20"><center>Pedidos Por Vendedor (Mes)</center></div>
                  </q-card-section>
                  <q-separator />
                  <div id="chart">
                    <apexchart ref="chart7" type="bar" height="500" :options="chartOptions7" :series="series7" class="fig"></apexchart>
                  </div>
                  <template>
                  </template>
                </q-card>
              </div>
              <div class="col-xs-12 col-md-12 q-pa-md">
                <q-card class="col-xs-12 col-md-12">
                  <q-card-section class="bg-primary text-white">
                    <div class="text-h20"><center>Montos (Mes)</center></div>
                  </q-card-section>
                  <q-separator />
                  <div id="chart">
                    <apexchart ref="chart10" type="line" height="500" :options="chartOptions10" :series="series10" class="fig"></apexchart>
                  </div>
                  <template>
                  </template>
                </q-card>
              </div>
          </div>
            </q-tab-panel>
              <q-tab-panel name="sales" class="bg-grey-3">
                <sales></sales>
                <SalesCharts></SalesCharts>
            </q-tab-panel>
              <q-tab-panel name="production" class="bg-grey-3">
                <ProductionKpis></ProductionKpis>
                <ProductionCharts></ProductionCharts>
            </q-tab-panel>
            <q-tab-panel name="banks" class="bg-grey-3">
                <BanksGpi></BanksGpi>
                <BanksCharts></BanksCharts>
            </q-tab-panel>
            <q-tab-panel name="management" class="bg-grey-3">
                <Managment></Managment>
            </q-tab-panel>
          </q-tab-panels>
        </div>
    </div>
  </q-page>
</template>

<script>
import api from '../commons/api.js'
// (no carga) import { Quasar, QCircularProgress } from 'quasar'
import VueApexCharts from 'vue-apexcharts'
import Vue from 'vue'
import Payment from '../components/dashboard/Payment'
import Paymentv2 from '../components/dashboard/Paymentv2'
import Sales from '../components/dashboard/Sales'
import SalesCharts from '../components/dashboard/SalesCharts'

import BanksGpi from '../components/dashboard/banks/BanksGpi'
import BanksCharts from '../components/dashboard/banks/BanksCharts'

import Managment from '../components/dashboard/managment/Managment'

import ProductionKpis from '../components/dashboard/production/ProductionKpis'
import ProductionCharts from '../components/dashboard/production/ProductionCharts'

// const { required, integer, between } = require('vuelidate/lib/validators')
Vue.use(VueApexCharts)
Vue.component('apexchart', VueApexCharts)
// (no carga)Vue.use(Quasar, {
// (no carga)  components: {
// (no carga)    QCircularProgress
// (no carga)  }
// (no carga)})
export default {
  name: 'Dashboard',
  components: {
    Payment,
    Paymentv2,
    Sales,
    SalesCharts,
    BanksGpi,
    BanksCharts,
    ProductionKpis,
    Managment,
    ProductionCharts
  },
  data () {
    return {
      currencyFormatter: new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
      }),
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
      chartOptions5: {
        colors: ['#00a3e8', '#f2c037', '#21ba45'],
        chart: {
          stacked: true,
          height: 700,
          type: 'line',
          toolbar: {
            show: false
          }
        },
        dataLabels: {
          enabled: true,
          formatter: function (val) {
            return val
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
            text: 'Cantidad'
          },
          labels: {
            type: 'String',
            // show: true,
            formatter: function (val) {
              return parseInt(val)
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
      chartOptions6: {
        chart: {
          stacked: true,
          height: 700,
          type: 'line',
          toolbar: {
            show: false
          }
        },
        dataLabels: {
          enabled: true,
          formatter: function (val) {
            return val
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
            text: 'Cantidad'
          },
          labels: {
            type: 'String',
            // show: true,
            formatter: function (val) {
              return parseInt(val)
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
      chartOptions7: {
        chart: {
          stacked: true,
          height: 700,
          type: 'line',
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
            show: true,
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
      series7: [],
      chartOptions8: {
        chart: {
          stacked: true,
          height: 700,
          type: 'line',
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
            text: 'Día'
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
            show: true,
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
      series8: [],
      chartOptions9: {
        chart: {
          stacked: true,
          height: 700,
          type: 'line',
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
            show: true,
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
      series9: [],
      chartOptions10: {
        chart: {
          stacked: true,
          height: 700,
          type: 'line',
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
            show: true,
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
      series10: [],
      series5: [],
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
      seriesStock: [],
      chartStock1: {

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
      seriesStockWeekly: [],
      stockOptionsWeekly: {

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
      semanas: []
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
  beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(2) && !this.$store.getters['users/roles'].includes(5) && !this.$store.getters['users/roles'].includes(10)) {
      this.$router.push('/')
    }
  },
  created () {
    this.diasSemanax()
    this.mesesx()
    this.changeModel(this.currentTab)
    // this.loadExpenses()
  },
  mounted () {
  },
  methods: {
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
    filterEntryDays () {
      this.$refs.startDateRef.hide()
      let sDate = null
      if (this.startDate) {
        sDate = this.startDate
        while (sDate.includes('/')) {
          sDate = sDate.replace('/', '-')
        }
      }
      this.$q.loading.show()
      api.get(`/dashboard/getPurchases/${sDate}`).then(({ data }) => {
        this.entryAmount = data.entryDays
        this.$q.loading.hide()
      })
    },
    changeModel (newModel) {
      if (newModel === 'inventory') {
        this.$q.loading.show()
        this.loadExpenses()
        api.get('/dashboard/getPurchases/null').then(({ data }) => {
          this.entryAmount = data.entryDays
          this.ocAmount = data.ocs
          this.inventaryCostAmount = data.inventaryCost
          this.inventaryCostAmountAVG = data.inventaryCostAVG
          this.productsAmount = data.noStock
          this.purchasesInArribo = data.embarco
          this.weeklyArribos = data.weeklyArribos
          this.$q.loading.hide()
        })
      } else if (newModel === 'commercial') {
        this.loadCommercial()
        api.get('/dashboard/getShoppingCartKpis').then(({ data }) => {
          this.pendientes = data.pendientes
          this.enviados = data.enviados
          this.actualmonth = data.actualmonth
          this.autorizados = data.autorizados
          this.parciales = data.parciales
          this.solicitados = data.solicitados
          this.nuevos = data.nuevos
          this.entregados = data.entregados
          this.$q.loading.hide()
        })
      }
    },
    async loadData () {
      await api.get('/dashboard/stockProducts').then(({ data }) => {
        this.stockOptions.yaxis.max = parseFloat(1000)
        this.stockOptions.xaxis.categories = data.label_stock
        this.stockSeries = [{ name: 'Almácen', type: 'line', stacked: true, data: Object.values(data.series_stock).reverse() }]
      })
    },
    async loadCommercial () {
      await this.grafs5()
      await this.grafs7()
      await this.grafs8()
      await this.grafs9()
      await this.grafs10()
    },
    async loadExpenses () {
      await this.grafs1()
      await this.grafsStock1()
      await this.grafsStockWeekly()
      await this.grafs2()
      await this.grafs3()
      await this.loadData()
    },
    async grafs1 () {
      await api.get('/dashboard/getInventoryCostDaily').then(({ data }) => {
        this.chartOptions1.yaxis.max = parseFloat(data.inventory_daily.maximo)
        this.series = [{ name: 'Almácen', type: 'column', stacked: true, data: Object.values(data.inventory_daily.inventory_amounts).reverse() }]
      }).catch(error => error)
    },
    async grafsStock1 () {
      await api.get('/dashboard/getStockCostDaily').then(({ data }) => {
        this.chartStock1.yaxis.max = parseFloat(data.inventory_daily.maximo)
        this.seriesStock = [{ name: 'Almácen', type: 'column', stacked: true, data: Object.values(data.inventory_daily.inventory_amounts).reverse() }]
      }).catch(error => error)
    },
    async grafs2 () {
      await api.get('/dashboard/getInventoryCostWeekly').then(({ data }) => {
        this.chartOptions2.yaxis.max = parseFloat(data.inventory_weekly.maximo)
        this.series2 = [{ name: 'Almácen', type: 'column', stacked: true, data: Object.values(data.inventory_weekly.inventory_amounts).reverse() }]
        this.chartOptions2.xaxis.categories = data.inventory_weekly.week.reverse()
      }).catch(error => error)
    },
    async grafsStockWeekly () {
      await api.get('/dashboard/getStockCostWeekly').then(({ data }) => {
        this.stockOptionsWeekly.yaxis.max = parseFloat(data.inventory_daily.maximo)
        this.seriesStockWeekly = [{ name: 'Almácen', type: 'column', stacked: true, data: Object.values(data.inventory_daily.inventory_amounts).reverse() }]
        this.stockOptionsWeekly.xaxis.categories = data.inventory_weekly.week.reverse()
      }).catch(error => error)
    },
    async grafs3 () {
      await api.get('/dashboard/getInventoryCostAnual').then(({ data }) => {
        this.chartOptions3.yaxis.max = parseFloat(data.inventory_anual.maximo)
        this.series3 = [{ name: 'Almácen', type: 'column', stacked: true, data: Object.values(data.inventory_anual.inventory_amounts).reverse() }]
      }).catch(error => error)
    },
    async grafs5 () {
      await api.get('/dashboard/getQtyByStatusShoppingCart').then(({ data }) => {
        for (let i = 0; i < data.monthCountByStatusShoppingCart.length; i++) {
          this.series5.push({ name: data.monthCountByStatusShoppingCart[i].status, data: data.monthCountByStatusShoppingCart[i].counts.reverse() })
        }
      }).catch(error => error)
    },
    // async grafs6 () {
    //   await api.get('/dashboard/salesByCustomer').then(({ data }) => {
    //     for (let i = 0; i < data.salesByCustomer.length; i++) {
    //       this.series6.push({ name: data.salesByCustomer[i].name, data: data.salesByCustomer[i].series })
    //     }
    //   }).catch(error => error)
    // },
    async grafs7 () {
      await api.get('/dashboard/salesBySeller').then(({ data }) => {
        for (let i = 0; i < data.salesBySeller.length; i++) {
          this.series7.push({ name: data.salesBySeller[i].name, data: data.salesBySeller[i].series })
        }
      }).catch(error => error)
    },
    async grafs8 () {
      await api.get('/dashboard/salesBySellerDaily').then(({ data }) => {
        for (let i = 0; i < data.salesBySellerDaily.length; i++) {
          this.series8.push({ name: data.salesBySellerDaily[i].seller, data: data.salesBySellerDaily[i].counts.reverse() })
        }
      }).catch(error => error)
    },
    async grafs9 () {
      await api.get('/dashboard/salesBySellerWeekly').then(({ data }) => {
        for (let i = 0; i < data.salesBySellerWeekly.length; i++) {
          this.series9.push({ name: data.salesBySellerWeekly[i].seller, data: data.salesBySellerWeekly[i].counts.reverse() })
        }
      }).catch(error => error)
    },
    async grafs10 () {
      await api.get('/dashboard/monthAmounts').then(({ data }) => {
        for (let i = 0; i < data.monthAmounts.length; i++) {
          this.series10.push({ name: this.chartOptions10.xaxis.categories[i], data: data.monthAmounts[i].series })
        }
      }).catch(error => error)
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
      this.chartStock1.xaxis.categories = days
      this.chartOptions8.xaxis.categories = days
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
      this.chartOptions6.xaxis.categories = mes
      this.chartOptions7.xaxis.categories = mes
      this.chartOptions10.xaxis.categories = mes
    }
  }
}
</script>

<style lang="stylus" scoped>
.my-card
  width 100%
.my-card
  width 100%
  #chart {
  max-width: 650px;
  margin: 35px auto;
}
.fig{
  margin-top: -40px;
  margin-bottom: -50px;
}
.kpi-card{
  min-width: 240px;
}
.kpi-card-top{
  min-width: 150px !important;
}
.kpi-data-vs{
  vertical-align: middle;
}

</style>

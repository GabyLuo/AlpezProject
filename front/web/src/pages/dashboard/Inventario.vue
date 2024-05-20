<template>
  <q-page class="bg-grey-3">
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
      <div class="row q-col-gutter-xs q-pb-md">
        <div class="col-xs-12 col-sm-3">
          <q-select
            color="dark"
            bg-color="secondary"
            filled
            emit-value
            map-options
            :options="filters.clusterOptions"
            v-model="filters.fields.cluster_id"
            @input="eventCluster"
            label="Cluster">
            <template v-slot:prepend>
              <q-icon name="business"></q-icon>
            </template>
          </q-select>
        </div>
        <div class="col-xs-12 col-sm-3">
          <q-select
            color="dark"
            bg-color="secondary"
            filled
            :options="filters.branchesList"
            v-model="filters.fields.station_id"
            @input="eventStation"
            label="Estaciones"
            emit-value
            map-options>
            <template v-slot:prepend>
              <q-icon name="business"></q-icon>
            </template>
          </q-select>
        </div>
      </div>
      <div class="col-md-12 col-xs-12 col-lg-12 bg-white">
        <q-card>
          <q-tabs
            v-model="currentTab"
            dense
            class="text-grey-8"
            active-color="primary"
            indicator-color="primary"
            align="justify"
            narrow-indicator>
            <q-tab name="trends" label="Dirección" icon="hotel_class"/>
            <q-tab name="stations" label="Estaciones" icon="multiple_stop"/>
            <q-tab name="mermas" label="Mermas" icon="multiple_stop"/>
            <q-tab name="store" label="Clusters" icon="store"/>
            <q-tab name="history" label="Histórico" icon="trending_up"/>
            <q-tab name="alarms" label="Alertas" icon="notifications_active"/>
            <q-tab name="comparatives" label="Comparativos" icon="addchart"/>
          </q-tabs>
          <q-separator />
          <q-tab-panels v-model="currentTab" animated>
            <q-tab-panel name="stations">
              <div class="row">
                <div class="col-xs-12 col-md-12 q-pa-md" v-for="stt in dataStation" :key="stt.id">
                  <q-card class="col-xs-12 col-md-12">
                    <q-card-section class="bg-green-5 text-white text-bold">
                      <div class="text-h7"><center>{{ stt.name }}</center></div>
                    </q-card-section>
                    <q-separator />
                    <div class="col q-pr-xs">
                      <div class="row q-col-gutter-xs">
                        <div class="col-xs-12 col-sm-3 q-pt-md" style="display: flex; justify-content: center; align-items: center;">
                          <radial :labelsD="stt.products" :seriesD="stt.stock" :colorsD="stt.colors" :totalD="stt.total" style="min-width: 225px; max-width: 225px;"/>
                        </div>
                        <div class="col-xs-12 col-sm-3 q-pt-md">
                          <columns :labelsD="stt.products_name_array" :seriesD="stt.stock" :colorsD="stt.colors"/>
                        </div>
                        <div class="col-xs-12 col-sm-3 q-pt-md">
                          <lines :labelsD="stt.products_name" :seriesD="stt.graphic_lines_merma" :categoriesD="stt.label_lines_merma" :title="'MERMA'"/>
                        </div>
                        <div class="col-xs-12 col-sm-3 q-pt-md">
                          <lines :labelsD="stt.products_name" :seriesD="stt.graphic_lines" :categoriesD="stt.label_lines" :title="'TENDENCIA'"/>
                        </div>
                      </div>
                    </div>
                  </q-card>
                </div>
              </div>
            </q-tab-panel>

            <q-tab-panel name="store">
              <div class="row">
                <div class="col-xs-12 col-md-6 q-pa-md" v-for="stt in dataCluster" :key="stt.id">
                  <q-card class="col-xs-12 col-md-6">
                    <q-card-section class="bg-green-5 text-white text-bold">
                      <div class="text-h7"><center>{{ stt.name }}</center></div>
                    </q-card-section>
                    <q-separator />
                    <div class="col q-pr-xs">
                      <div class="row q-col-gutter-xs">
                        <div class="col-xs-12 col-sm-12 q-pt-md">
                          <columns :labelsD="stt.products" :seriesD="stt.stock" :colorsD="stt.colors"/>
                        </div>
                      </div>
                    </div>
                  </q-card>
                </div>
              </div>
            </q-tab-panel>

            <q-tab-panel name="mermas">
              <div class="row">
                <div class="col-xs-12 col-md-3 q-pa-md" v-for="stt in dataStationMerma" :key="stt.id">
                  <q-card class="col-xs-12 col-md-3">
                    <q-card-section class="bg-green-5 text-white text-bold">
                      <div class="text-h7"><center>{{ stt.name }}</center></div>
                    </q-card-section>
                    <q-separator />
                    <div class="col q-pr-xs">
                      <div class="row q-col-gutter-xs">
                        <div class="col-xs-12 col-sm-12 q-pt-md">
                          <lines :labelsD="stt.products_name" :seriesD="stt.graphic_lines" :categoriesD="stt.label_lines" :title="'MERMA'"/>
                        </div>
                      </div>
                    </div>
                  </q-card>
                </div>
              </div>
            </q-tab-panel>

            <q-tab-panel name="alarms">
            </q-tab-panel>

            <q-tab-panel name="history">
              <inventory-kpi/>
            </q-tab-panel>

            <q-tab-panel name="comparatives">
            </q-tab-panel>

            <q-tab-panel name="trends">
              <div class="row">
                <div class="col-xs-12 col-md-4 q-pa-md" v-for="stt in dataStation" :key="stt.id">
                  <q-card class="col-xs-12 col-md-4">
                    <q-card-section class="bg-green-5 text-white text-bold">
                      <div class="text-h7"><center>{{ stt.name }}</center></div>
                    </q-card-section>
                    <q-separator />
                    <div class="col q-pr-xs">
                      <div class="row q-col-gutter-xs">
                        <div class="col-xs-12 col-sm-12 q-pt-md">
                          <columns :labelsD="stt.products_name_array" :seriesD="stt.stock" :colorsD="stt.colors"/>
                        </div>
                      </div>
                    </div>
                  </q-card>
                </div>
              </div>
            </q-tab-panel>
          </q-tab-panels>
        </q-card>
      </div>
    </div>
  </q-page>
</template>

<script>
import api from '../../commons/api.js'
import InventoryKpi from '../../components/dashboard/inventory/InventoryKpi'
import Radial from '../../components/dashboard/inventory/Radial'
import Columns from '../../components/dashboard/inventory/Columns'
import Lines from '../../components/dashboard/inventory/Lines'

export default {
  name: 'Dashboard',
  components: {
    InventoryKpi,
    Radial,
    Columns,
    Lines
  },
  data () {
    return {
      currentTab: 'trends',
      dataStation: [],
      dataStationMerma: [],
      dataCluster: [],
      filters: {
        fields: {
          cluster_id: 0,
          station_id: 0
        },
        clusterOptions: [],
        branchesList: []
      }
    }
  },
  computed: {},
  created () {
    this.loadData()
  },
  mounted () {
  },
  methods: {
    async loadData () {
      this.$q.loading.show()
      await this.getClustersList()
      await this.getBranchesList()
      await this.getData()
      await this.getDataMerma()
      this.$q.loading.hide()
    },
    async getData () {
      this.$q.loading.show()
      await api.get('/dashboard/getDataStation' + '/' + this.filters.fields.cluster_id + '/' + this.filters.fields.station_id).then(({ data }) => {
        this.dataStation = data.data
        this.dataCluster = data.data_s
      })
      this.$q.loading.hide()
    },
    async getDataMerma () {
      this.$q.loading.show()
      await api.get('/dashboard/getDataMermas' + '/' + this.filters.fields.cluster_id + '/' + this.filters.fields.station_id).then(({ data }) => {
        this.dataStationMerma = data.data
      })
      this.$q.loading.hide()
    },
    async getClustersList () {
      this.filters.clusterOptions = []
      await api.get('supercluster/getOptions').then(data => {
        this.filters.clusterOptions = data.data.options
        if (this.filters.clusterOptions.length > 1) {
          this.filters.clusterOptions.push({ value: 0, label: '--Todos--' })
        }
        if (this.filters.clusterOptions.length === 1) {
          this.filters.fields.cluster_id = data.data.options[0].value
        }
      })
    },
    async getBranchesList () {
      this.filters.branchesList = []
      this.filters.fields.station_id = 0
      await api.get('branch-offices/getByCluster' + '/' + this.filters.fields.cluster_id).then(data => {
        this.filters.branchesList = data.data.options
        if (this.filters.branchesList.length > 1) {
          this.filters.branchesList.push({ value: 0, label: '--Todos--' })
        }
        if (this.filters.branchesList.length === 1) {
          this.filters.fields.station_id = data.data.options[0].value
        }
      })
    },
    async eventCluster () {
      this.$q.loading.show()
      await this.getBranchesList()
      await this.getData()
      await this.getDataMerma()
      this.$q.loading.hide()
    },
    async eventStation () {
      this.$q.loading.show()
      await this.getData()
      await this.getDataMerma()
      this.$q.loading.hide()
    }
  }
}
</script>

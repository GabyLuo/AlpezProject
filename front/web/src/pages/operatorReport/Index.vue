<template>
  <q-page>
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-7">
          <span class="q-ml-md grey-8 fs28 page-title">Producción por operador</span>
        </div>
        <div class="col-sm-5 pull-right">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="right">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Producción por operador" />
            </q-breadcrumbs>
          </div>
        </div>
      </div>
    </div>

    <div class="q-pa-md bg-grey-3">
      <div class="row bg-white border-panel">
        <div class="col q-pa-md">
          <div class="row q-mb-sm">
            <div class="col-xs-12 col-md-3" style="padding: 3px;">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="startDate"
                mask="date"
                label="Fecha inicio"
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
                      v-model="startDate"
                      @input="() => $refs.startDateRef.hide()"
                      today-btn
                    />
                  </div>
                </q-popup-proxy>
              </q-select>
            </div>
            <div class="col-xs-12 col-md-3" style="padding: 3px;">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="endingDate"
                mask="date"
                label="Fecha fin"
              >
                <template v-slot:prepend>
                  <q-icon name="event" />
                </template>
                <q-popup-proxy
                  ref="endingDateRef"
                  transition-show="scale"
                  transition-hide="scale"
                >
                  <div class="col-sm-12">
                    <q-date
                      v-model="endingDate"
                      @input="() => $refs.endingDateRef.hide()"
                      today-btn
                    />
                  </div>
                </q-popup-proxy>
              </q-select>
            </div>
            <div class="col-xs-12 col-md-3" style="padding: 3px;">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="operator"
                use-input
                hide-selected
                fill-input
                input-debounce="0"
                :options="filteredOperatorOptions"
                @filter="filterOperators"
                label="Operador"
              >
                <template v-slot:prepend>
                  <q-icon name="fa fa-user" />
                </template>
                <template v-slot:no-option>
                  <q-item>
                    <q-item-section class="text-grey">
                      Sin resultados
                    </q-item-section>
                  </q-item>
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-md-3" style="padding: 3px;">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="status"
                :options="[
                  {label: 'PRODUCIENDO Y TERMINADO', value: null},
                  {label: 'PRODUCIENDO', value: 'PRODUCIENDO'},
                  {label: 'TERMINADO', value: 'TERMINADO'}
                ]"
                label="Estatus"
              >
                <template v-slot:prepend>
                  <q-icon name="battery_std" />
                </template>
              </q-select>
            </div>
          </div>

          <div class="row q-mb-sm">
            <div class="col-xs-12 pull-right">
              <q-btn color="primary" icon="fas fa-search" label="Buscar" style="height: 70%;margin-right: 5px;" @click="generateOperatorReport()" />
              <q-btn color="primary" icon="fas fa-eraser" label="Limpiar" style="height: 70%;margin-right: 5px;" @click="cleanFilters()" />
              <q-btn color="positive" icon="fas fa-file-pdf" label="PDF" style="height: 70%;margin-right: 5px;" @click="generatePdfOperatorReport()" />
            </div>
          </div>

          <br>

          <q-table
            flat
            bordered
            :data="data"
            :columns="columns"
            row-key="laminate_number"
            :pagination.sync="pagination"
          >
            <template v-slot:body="props">
              <q-tr :props="props">
                <q-td key="laminate_number" style="width: 10%;" :props="props">{{ props.row.id }}</q-td>
                <q-td key="scheduled_date" style="width: 10%;" :props="props">{{ props.row.scheduled_date }}</q-td>
                <q-td key="operator_name" style="text-align: left; width: 25%;" :props="props">{{ props.row.operator_name }}</q-td>
                <q-td key="product_name" style="text-align: left; width: 25%;" :props="props">{{ props.row.product_name }}</q-td>
                <q-td key="qty" style="text-align: right; width: 15%;" :props="props">{{ formatPrice(props.row.qty) }} KG.</q-td>
                <q-td key="status" style="width: 15%;" :props="props">
                  <q-chip square dense :color="props.row.status == 'PRODUCIENDO' ? 'secondary' : (props.row.status == 'TERMINADO' ? 'positive' : 'blue')" text-color="white">
                    {{ props.row.status }}
                  </q-chip>
                </q-td>
              </q-tr>
            </template>
          </q-table>
        </div>
      </div>
    </div>

  </q-page>
</template>

<script>
import api from '../../commons/api.js'

export default {
  name: 'OperatorReport',
  data () {
    return {
      formatter: new Intl.NumberFormat('en-US'),
      startDate: null,
      endingDate: null,
      status: { label: 'PRODUCIENDO Y TERMINADO', value: null },
      operator: { label: 'TODOS', value: null },
      operatorOptions: [],
      filteredOperatorOptions: [],
      pagination: {
        sortBy: 'scheduled_date',
        descending: true,
        rowsPerPage: 50
      },
      columns: [
        { name: 'laminate_number', align: 'center', label: 'No. laminado', field: 'laminate_number', style: 'width: 10%', sortable: true },
        { name: 'scheduled_date', align: 'center', label: 'Fecha programada', field: 'scheduled_date', style: 'width: 10%', sortable: true },
        { name: 'operator_name', align: 'center', label: 'Operador', field: 'operator_name', style: 'width: 25%', sortable: true },
        { name: 'product_name', align: 'center', label: 'Producto', field: 'product_name', style: 'width: 25%', sortable: true },
        { name: 'qty', align: 'center', label: 'Cantidad', field: 'qty', style: 'width: 15%', sortable: true, sort: (a, b) => Number(a, 10) - Number(b, 10) },
        { name: 'status', align: 'center', label: 'Estatus', field: 'status', style: 'width: 15%', sortable: true }
      ],
      data: []
    }
  },
  beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(2) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(4) && !this.$store.getters['users/roles'].includes(5)) {
      this.$router.push('/')
    }
  },
  created () {
    this.fetchFromServer()
  },
  methods: {
    formatPrice (value) {
      const val = (value / 1).toFixed(1).replace('.', ',')
      return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')
    },
    fetchFromServer () {
      this.$q.loading.show()
      api.get('/operators/options').then(({ data }) => {
        this.filteredOperatorOptions = this.operatorOptions = data.options
        this.filteredOperatorOptions.unshift({ value: null, label: 'TODOS' })
        this.$q.loading.hide()
      })
    },
    filterOperators (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.filteredOperatorOptions = this.operatorOptions.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    generateOperatorReport () {
      this.$q.loading.show()
      let sDate = null
      let eDate = null
      const operatorId = (this.operator != null && this.operator.value != null) ? Number(this.operator.value) : null
      const status = this.status.value
      if (this.startDate) {
        sDate = this.startDate
        while (sDate.includes('/')) {
          sDate = sDate.replace('/', '-')
        }
      }
      if (this.endingDate) {
        eDate = this.endingDate
        while (eDate.includes('/')) {
          eDate = eDate.replace('/', '-')
        }
      }
      api.get(`/laminates/operator/${operatorId}/${sDate}/${eDate}/${status}`).then(({ data }) => {
        if (data.result) {
          this.data = data.laminates
          this.$q.loading.hide()
        } else {
          this.$q.loading.hide()
        }
      })
    },
    generatePdfOperatorReport () {
      let sDate = null
      let eDate = null
      const operatorId = (this.operator != null && this.operator.value != null) ? Number(this.operator.value) : null
      const status = this.status.value
      if (this.startDate) {
        sDate = this.startDate
        while (sDate.includes('/')) {
          sDate = sDate.replace('/', '-')
        }
      }
      if (this.endingDate) {
        eDate = this.endingDate
        while (eDate.includes('/')) {
          eDate = eDate.replace('/', '-')
        }
      }
      const uri = process.env.API + `laminates/operator/pdf/${operatorId}/${sDate}/${eDate}/${status}`
      window.open(uri, '_blank')
    },
    cleanFilters () {
      this.startDate = null
      this.endingDate = null
      this.status = { label: 'PRODUCIENDO Y TERMINADO', value: null }
      this.operator = { label: 'TODOS', value: null }
      this.data = []
    }
  }
}
</script>

<style>
</style>

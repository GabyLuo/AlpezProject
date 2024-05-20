<template>
  <div class="q-pa-md bg-grey-3">
    <div class="row">
      <div class="col-xs-6 col-sm-6 col-md-3 col-lg-2" align="left">
        <q-btn color="primary" :label="bmonth" icon="navigate_before" @click="calendarPrev"/>
      </div>
      <div class="col-md-6 col-lg-8" align="center">
        <q-btn color="primary" :label="amonth"/>
      </div>
      <div class="col-xs-6 col-sm-6 col-md-3 col-lg-2" align="right">
        <q-btn color="primary" :label="nmonth" icon-right="navigate_next" @click="calendarNext"/>
      </div>
    </div>
    <div class="row" style="margin-top: 10px;">
      <q-calendar
        ref="calendar"
        v-model="date"
        class="col-xs-12 col-sm-12 col-md-12 col-lg-12"
        view="month"
        locale="es"
        enable-outside-days
        :day-height="110"
        v-touch-swipe.mouse.left.right="handleSwipe"
        animated
        transition-prev="slide-right"
        transition-next="slide-left"
        style="overflow: hidden;"
      >
        <template #day="{ timestamp }">
          <template v-for="(event, index) in getEvents(timestamp.date)">
            <q-badge
              :class="event.bgcolor"
              :key="index"
              class="badge"
              @click="showModal(event.date, event.details)"
              style="text-align: right">
              <label class="ellipsis" style="cursor: pointer;">$ {{ formatPrice(event.title) }}</label>
              <q-tooltip transition-show="scale" transition-hide="scale" :content-class="event.bgcolor" content-style="font-size: 14px">PLAZO {{ event.details}}</q-tooltip>
            </q-badge>
          </template>
        </template>
      </q-calendar>
    </div>
    <q-dialog v-model="modal" >
      <q-card style="min-width: 70%;max-height: 90vh">
        <div>
          <div class="bg-primary">
            <div class="row">
              <div class="col-sm-11 text-h6" style="color:white;padding-top: 5px; padding-bottom: 5px;">&nbsp;&nbsp; DETALLES CUENTAS POR COBRAR</div>
              <div class="col-sm-1 pull-right" style="color:white;padding-top: 5px; padding-bottom: 5px;" ><q-btn color="white" flat round @click.native="closeModal()" dense icon="close" /></div>
            </div>
          </div>
        </div>
        <div class="row q-pa-md q-col-gutter-md">
          <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
            <q-input
              color="white"
              bg-color="primary"
              filled
              dark
              disable
              v-model="dateModal"
              label="FECHA"
            >
              <template v-slot:prepend>
                <q-icon name="calendar_today" />
              </template>
            </q-input>
          </div>
          <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
            <q-input
              color="white"
              bg-color="primary"
              filled
              dark
              disable
              v-model="term"
              label="PLAZO"
            >
              <template v-slot:prepend>
                <q-icon name="date_range" />
              </template>
            </q-input>
          </div>
          <div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
            <q-input
              color="white"
              bg-color="primary"
              filled
              disable
              dark
              v-model="total"
              label="ACUMULADO"
            >
              <template v-slot:prepend>
                <q-icon name="attach_money" />
              </template>
            </q-input>
          </div>
          <!-- <div class="col-xs-12 col-sm-6 col-md-12 col-lg-2 pull-right">
            <q-btn style="margin-top: 10px;" color="purple" icon="mail" @click.native="sendMail()">
              <q-tooltip transition-show="scale" transition-hide="scale" content-class="bg-purple text-white shadow-10" content-style="font-size: 14px">ENVIAR CORREO</q-tooltip>
            </q-btn>
            <q-btn style="margin-top: 10px; margin-left: 10px;" class="bg-red text-white" icon="fas fa-file-pdf" @click="generatePDF()">
              <q-tooltip transition-show="scale" transition-hide="scale" content-class="bg-red text-white shadow-10" content-style="font-size: 14px">GENERAR PDF</q-tooltip>
            </q-btn>
          </div> -->
        </div>
        <q-tabs v-model="currentTab" inline-label dense class="text-grey" active-color="primary" indicator-color="primary" align="justify" narrow-indicator @input="changeModel">
          <q-tab name="client" icon="person" label="POR CLIENTE" />
          <q-tab name="rem" icon="outbox" label="POR REMISION" />
        </q-tabs>
        <q-tab-panels v-model="currentTab" animated>
          <q-tab-panel name="client">
            <div class="row q-pa-md">
              <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <q-table style="max-height:60vh" color="primary" :data="info" :columns="infoColumnsClient" row-key="id" :pagination.sync="pagination">
                  <template v-slot:body="props">
                    <q-tr :props="props">
                      <q-td  class="pull-left" key="customer_name" style="text-left: center; width:15%;" :props="props"><label> {{ props.row.customer_name }}</label></q-td>
                      <q-td key="contador" style="text-align: center; width:15%;" :props="props"><label> {{ props.row.contador }}</label></q-td>
                      <q-td key="total" style="text-align: right; width:15%;" :props="props"><label>$ {{ formatPrice(props.row.total) }}</label></q-td>
                      <q-td key="paid" style="text-align: right; width:15%;" :props="props"><label>$ {{ formatPrice(props.row.paid) }}</label></q-td>
                      <q-td key="remaining" style="text-align: right; width:15%;" :props="props">
                        <q-badge transparent  class="bg-red-14">
                          <label style="font-size: 13px;font-weight: bold;">$ {{ formatPrice(props.row.remaining) }}</label>
                        </q-badge>
                      </q-td>
                    </q-tr>
                  </template>
                </q-table>
              </div>
            </div>
          </q-tab-panel>
          <q-tab-panel name="rem">
            <div class="row q-pa-md">
              <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <q-table style="max-height:60vh; width: 100%;" color="primary" :data="info" :columns="infoColumnsRem" row-key="id" :pagination.sync="pagination">
                  <template v-slot:body="props">
                    <q-tr :props="props">
                      <q-td  key="rem" style="text-align: center; width:10%;" :props="props"><label> {{ props.row.rem }}</label></q-td>
                      <q-td  class="pull-left" key="customer_name" style="text-align: left; width:25%;" :props="props"><label> {{ props.row.customer_name }}</label></q-td>
                      <q-td key="total" style="text-align: right; width:15%;" :props="props"><label>$ {{ formatPrice(props.row.total) }}</label></q-td>
                      <q-td key="paid" style="text-align: right; width:15%;" :props="props"><label>$ {{ formatPrice(props.row.paid) }}</label></q-td>
                      <q-td key="remaining" style="text-align: right; width:15%;" :props="props">
                        <q-badge transparent  class="bg-red-14">
                          <label style="font-size: 13px;font-weight: bold;">$ {{ formatPrice(props.row.remaining) }}</label>
                        </q-badge>
                      </q-td>
                    </q-tr>
                  </template>
                </q-table>
              </div>
            </div>
          </q-tab-panel>
        </q-tab-panels>
        <q-card-actions align="right">
          <br>
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<script>
// import api from '../../commons/api'
import { mapActions, mapGetters } from 'vuex'
import { QCalendar } from '@quasar/quasar-ui-qcalendar'
import api from '../../commons/api'

import { date } from 'quasar'
const timeStamp = Date.now()
const formattedString = date.formatDate(timeStamp, 'DD/MM/YYYY')
const amonth = date.formatDate(timeStamp, 'MMMM')
const n = date.addToDate(timeStamp, { month: 1 })
const nmonth = date.formatDate(n, 'MMMM')
const b = date.subtractFromDate(timeStamp, { month: 1 })
const bmonth = date.formatDate(b, 'MMMM')

export default {
  components: {
    QCalendar
  },
  name: 'Forecast',
  props: {
  },
  created () {
  },
  data () {
    return {
      date: '',
      amonth: '',
      bmonth: '',
      nmonth: '',
      month: timeStamp,
      term: null,
      dateModal: formattedString,
      total: null,
      events: [],
      modal: false,
      data: [],
      info: [],
      infoColumnsClient: [
        { name: 'customer_name', align: 'center', label: 'CLIENTE', field: 'customer_name', sortable: true },
        { name: 'contador', align: 'center', label: 'CANT. REMISIONES', field: 'contador', sortable: true },
        { name: 'total', align: 'center', label: 'TOTAL', field: 'total', sortable: true },
        { name: 'paid', align: 'center', label: 'ABONADO', field: 'paid', sortable: true },
        { name: 'remaining', align: 'center', label: 'RESTANTE', field: 'remaining', sortable: true }
      ],
      infoColumnsRem: [
        { name: 'rem', align: 'center', label: 'REM.', field: 'rem', sortable: true },
        { name: 'customer_name', align: 'center', label: 'CLIENTE', field: 'customer_name', sortable: true },
        { name: 'total', align: 'center', label: 'TOTAL', field: 'total', sortable: true },
        { name: 'paid', align: 'center', label: 'ABONADO', field: 'paid', sortable: true },
        { name: 'remaining', align: 'center', label: 'RESTANTE', field: 'remaining', sortable: true }
      ],
      infoPagination: {
        sortBy: 'customer_name',
        descending: false,
        rowsPerPage: 25
      },
      pagination: {
        sortBy: 'id',
        descending: false,
        rowsPerPage: 25
      },
      currentTab: 'client'
    }
  },
  computed: {
    ...mapGetters({
    })
  },
  validations: {
  },
  mounted () {
    this.loadAll()
  },
  methods: {
    formatPrice (value) {
      const val = (value / 1).toFixed(2).replace(',', '.')
      return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    },
    ...mapActions({
      getDataCalendar: 'payments/forecast/dataC'
    }),
    loadAll () {
      this.$q.loading.show()
      this.getDataCalendar().then(({ data }) => {
        this.events = data.data
        this.$q.loading.hide()
      })
      this.amonth = amonth
      this.bmonth = bmonth
      this.nmonth = nmonth
    },
    getEvents (dt) {
      const events = []
      for (let i = 0; i < this.events.length; ++i) {
        const added = false
        if (this.events[i].date === dt) {
          if (!added) {
            this.events[i].side = undefined
            events.push(this.events[i])
          }
        }
      }
      return events
    },
    showModal (date, tipo) {
      this.modal = true
      this.getDataClient(date, tipo)
    },
    getDataClient (date, tipo) {
      this.$q.loading.show()
      this.term = tipo
      this.date = date
      const params = []
      params.date = date
      params.type = tipo
      api.post('/invoices/getDetailsForecastperClient', params).then(({ data }) => {
        this.info = data.data
        this.total = data.sm
        this.$q.loading.hide()
      })
      // this.$q.loading.hide()
    },
    getDataRem (date, tipo) {
      this.$q.loading.show()
      this.term = tipo
      this.date = date
      const params = []
      params.date = date
      params.type = tipo
      api.post('/invoices/getDetailsForecastperRem', params).then(({ data }) => {
        this.info = data.data
        this.total = data.sm
        this.$q.loading.hide()
      })
      // this.$q.loading.hide()
    },
    closeModal () {
      this.modal = false
      this.currentTab = 'client'
    },
    changeModel () {
      this.info = []
      if (this.currentTab === 'client') {
        this.getDataClient(this.date, this.term)
      } else if (this.currentTab === 'rem') {
        this.getDataRem(this.date, this.term)
      }
    },
    calendarNext () {
      this.$refs.calendar.next()
      this.month = date.addToDate(this.month, { month: 1 })
      this.amonth = date.formatDate(this.month, 'MMMM')
      const m = date.addToDate(this.month, { month: 1 })
      this.nmonth = date.formatDate(m, 'MMMM')
      const m2 = date.subtractFromDate(this.month, { month: 1 })
      this.bmonth = date.formatDate(m2, 'MMMM')
    },
    calendarPrev () {
      this.$refs.calendar.prev()
      this.month = date.subtractFromDate(this.month, { month: 1 })
      this.amonth = date.formatDate(this.month, 'MMMM')
      const m = date.subtractFromDate(this.month, { month: 1 })
      this.bmonth = date.formatDate(m, 'MMMM')
      const m2 = date.addToDate(this.month, { month: 1 })
      this.nmonth = date.formatDate(m2, 'MMMM')
    },
    handleSwipe ({ evt, ...info }) {
      if (info.duration >= 30) {
        if (info.direction === 'right') {
          this.calendarPrev()
        } else if (info.direction === 'left') {
          this.calendarNext()
        }
      }
      evt.cancelable !== false && evt.preventDefault()
      evt.stopPropagation()
    }
  }
}
</script>

<style lang="stylus" scoped>
.badge {
  margin-left: auto;
  margin-right: auto;
  display: block;
  cursor: pointer;
  height: 25px;
  max-height: 25px;
  margin-top: 5px;
  padding-top: 6px;
  width: 75%;
  font-size: 1em;
  font-family: 'Nunito';
}
</style>
<style src="src/css/calendar.css"></style>

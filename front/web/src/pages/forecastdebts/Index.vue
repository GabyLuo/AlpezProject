<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-8">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Forecast cuentas por pagar" />
            </q-breadcrumbs>
          </div>
        </div>
        <div class="col-sm-4">
          <div class="q-pa-md q-gutter-sm">
            <q-select  color="dark" bg-color="secondary" filled
                    v-model="branche"
                    :options="optionsBranches"
                    fill-input
                    input-debounce="0"
                    @input="fetchFromServer()"
                    label="Estación"
                    emit-value map-options>
          </q-select>
          </div>
        </div>
      </div>
    </div>
    <ForecastComponent :branche="branche"></ForecastComponent>
  </q-page>
</template>

<script>
import ForecastComponent from '../../components/debtstopay/ForecastComponent.vue'
import api from '../../commons/api.js'
export default {
  components: {
    ForecastComponent
  },
  name: 'IndexTrainings',
  data () {
    return {
      optionsBranches: [],
      branche: 0,
      pagination: {
        sortBy: 'id',
        descending: false,
        rowsPerPage: 25
      },
      filter: ''
    }
  },
  /* beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(2) && !this.$store.getters['users/roles'].includes(22)) {
      this.$router.push('/')
    }
  }, */
  mounted () {
    this.$q.loading.show()
    this.getBranchOptions()
    this.fetchFromServer()
    this.$q.loading.hide()
  },
  computed: {
    roleId () {
      const user = this.$store.getters['users/rol']
      return parseInt(user)
    }
  },
  beforeRouteEnter (to, from, next) {
    next(vm => {
      const propiedades = vm.$store.getters['users/rol']
      // console.log(propiedades)
      if (propiedades === 1 || propiedades === 3 || propiedades === 7 || propiedades === 2 || propiedades === 20 || propiedades === 4 || propiedades === 27 || propiedades === 17 || propiedades === 22 || propiedades === 28) {
        next()
      } else {
        next('/')
      }
    })
  },
  methods: {
    getBranchOptions () {
      api.get('/branch-offices/getBranchsOffices').then(({ data }) => {
        if (data.result) {
          this.optionsBranches = data.branchs
          this.optionsBranches.unshift({ label: 'TODOS', value: 0 })
        }
      })
    },
    fetchFromServer () {
      // this.$q.loading.show()
      /* api.get('/trainings').then(({ data }) => {
        this.data = data.trainings
        this.$q.loading.hide()
      }) */
    }
  }
}
</script>

<style>
</style>

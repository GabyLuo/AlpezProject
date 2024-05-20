<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-6">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Kardex" />
            </q-breadcrumbs>
          </div>
        </div>
        <div class="col-sm-6">
          <div class="col-sm-4 col-sm-4 pull-right" style="padding: 16px;">
            <q-btn color="negative" icon="fas fa-file-pdf"  @click="generatePdfKardex()" style="margin-left: 6px;">
              <q-tooltip content-class="bg-negative">Generar PDF</q-tooltip>
            </q-btn>
            <q-btn color="positive" icon="fas fa-download"  @click="generateCsvKardex()" style="margin-left: 6px;">
              <q-tooltip content-class="bg-positive">Generar CSV</q-tooltip>
            </q-btn>
          </div>
        </div>
      </div>
    </div>

    <div class="q-pa-md bg-grey-3">
      <div class="row bg-white border-panel">
        <div class="col q-pa-md">
          <div class="row q-mb-sm">
            <div class="col-xs-12 col-md-12 pull-right" style="padding: 3px;">
            </div>
              <div class="col-xs-12 col-sm-2 text-center" style="padding: 3px;">
                <q-select color="dark"
                bg-color="secondary"
                filled
                v-model="startDate"
                mask="date"
                label="Desde">
                <template v-slot:prepend>
                    <q-icon name="event"></q-icon>
                </template>
                <q-popup-proxy
                ref="date_ref"
                transition-show="scale"
                transition-hide="scale">
                    <div class="col-sm-12">
                        <q-date
                        color="secondary"
                        text-color="white"
                        mask="DD/MM/YYYY"
                        v-model="startDate"
                        @input="() => $refs.date_ref.hide()"
                        today-btn>
                        </q-date>
                    </div>
                </q-popup-proxy>
                </q-select>
            </div>
            <div class="col-xs-12 col-sm-2 text-center" style="padding: 3px;">
                <q-select color="dark"
                bg-color="secondary"
                filled
                v-model="endingDate"
                mask="date"
                label="Hasta">
                <template v-slot:prepend>
                    <q-icon name="event"></q-icon>
                </template>
                <q-popup-proxy
                ref="date_ref"
                transition-show="scale"
                transition-hide="scale">
                    <div class="col-sm-12">
                        <q-date
                        color="secondary"
                        text-color="white"
                        mask="DD/MM/YYYY"
                        v-model="endingDate"
                        @input="() => $refs.date_ref.hide()"
                        today-btn>
                        </q-date>
                    </div>
                </q-popup-proxy>
                </q-select>
            </div>
            <div class="col-xs-12 col-md-4" style="padding: 3px;">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="branchOffice"
                :options="branchOfficeOptions"
                label="Estación"
                @input="() => {this.storage = null}"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-store-alt" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-md-4" style="padding: 3px;">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="storage"
                :options="filteredStorageOptions"
                label="Almacén"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-warehouse" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-md-5" style="padding: 3px;">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="product"
                use-input
                hide-selected
                fill-input
                input-debounce="0"
                :options="filteredProductOptions"
                @filter="filterProducts"
                label="Producto"
              >
                <template v-slot:prepend>
                  <q-icon name="emoji_objects" />
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
            <div class="col-xs-12 col-md-5 pull-left" style="padding: 3px;" >
              <q-btn  icon="fas fa-eraser" class="full-height" @click="cleanFilters()" color="primary" style="margin-left: 6px;">
                <q-tooltip content-class="bg-primary">Limpiar filtros</q-tooltip>
              </q-btn>
              <q-btn icon="fas fa-search" class="full-height" @click="generateKardex()" color="primary" style="margin-left: 3px;">
                <q-tooltip content-class="bg-primary">Buscar</q-tooltip>
              </q-btn>
            </div>
          </div>
          <br>
          <q-table
            flat
            bordered
            :data="data"
            :columns="columns"
            row-key="serial"
            :pagination.sync="pagination"
          >
            <template v-slot:body="props">
              <q-tr :props="props">
                <!-- <q-td key="mdid" style="text-align: left;" :props="props">{{ props.row.mdid }}</q-td> -->
                <q-td key="date" :props="props">{{ props.row.date }}</q-td>
                <q-td key="movementType" :props="props">
                  <q-chip square dense :color="setTypeColor(props)" text-color="white"
                  :icon="setTypeIcon(props)">
                    {{ (props.row.type_id == 1 ? 'ENTRADA' : (props.row.type_id == 2 ? 'SALIDA' : (props.row.type_id == 3 ? 'INVENTARIO FÍSICO' : (props.row.type_id == 6 ? 'MERMA' : (props.row.type_id == 5 ? 'TRASPASO (SALIDA)' : 'TRASPASO (ENTRADA)'))))) }}
                  </q-chip>
                </q-td>
                <q-td key="branchOffice" style="text-align: left;" :props="props">{{ props.row.branch_office_name }}</q-td>
                <q-td key="storage" style="text-align: left;" :props="props">{{ props.row.storage_name }}</q-td>
                <q-td key="product" style="text-align: left;" :props="props">{{ `${props.row.category_code}-${props.row.line_code}-${PadLeft(props.row.product_code, 5)}` }}</q-td>
                <q-td key="foli" style="text-align: left;" :props="props">{{ props.row.folio }}</q-td>
                <q-td key="qty" style="text-align: right;" :props="props">{{ formatPrice(props.row.qty)}} </q-td>
                <q-td key="stock" style="text-align: right;" :props="props">{{ formatPrice(props.row.stock)}} </q-td>
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
  name: 'Kardex',
  data () {
    return {
      formatter: new Intl.NumberFormat('en-US'),
      startDate: null,
      endingDate: null,
      branchOffice: null,
      storage: null,
      product: null,
      branchOfficeOptions: [],
      storageOptions: [],
      productOptions: [],
      filteredProductOptions: [],
      pagination: {
        sortBy: 'serial',
        descending: false,
        rowsPerPage: 25
      },
      columns: [
        // { name: 'mdid', align: 'center', label: 'IDMD', field: 'mdid', sortable: true },
        { name: 'date', align: 'center', label: 'FECHA', field: 'date', sortable: false },
        { name: 'movementType', align: 'center', label: 'TIPO', field: 'movementType', sortable: false },
        { name: 'branchOffice', align: 'center', label: 'ESTACIÓN', field: 'branchOffice', sortable: false, sort: (a, b) => Number(a, 10) - Number(b, 10) },
        { name: 'storage', align: 'center', label: 'ALMACÉN', field: 'storage', sortable: false, sort: (a, b) => Number(a, 10) - Number(b, 10) },
        { name: 'product', align: 'center', label: 'PRODUCTO', field: 'product', sortable: false },
        { name: 'foli', align: 'center', label: 'FOLIO', field: 'foli', sortable: false },
        { name: 'qty', align: 'center', label: 'CANTIDAD', field: 'qty', sortable: false, sort: (a, b) => Number(a, 10) - Number(b, 10) },
        { name: 'stock', align: 'center', label: 'SALDO', field: 'stock', sortable: false, sort: (a, b) => Number(a, 10) - Number(b, 10) }
      ],
      data: []
    }
  },
  computed: {
    filteredStorageOptions () {
      let storages = []
      if (this.branchOffice != null && this.branchOffice.value != null) {
        storages = this.storageOptions.filter(so => (Number(so.branchOffice) === Number(this.branchOffice.value)))
      }
      return storages
    }
  },
  beforeRouteEnter (to, from, next) {
    next(vm => {
      const propiedades = vm.$store.getters['users/rol']
      console.log(propiedades)
      if (propiedades === 1 || propiedades === 3 || propiedades === 7 || propiedades === 2 || propiedades === 20 || propiedades === 4 || propiedades === 27 || propiedades === 22 || propiedades === 26) {
        next()
      } else {
        next('/')
      }
    })
  },
  /* beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(2)) {
      this.$router.push('/')
    }
  }, */
  created () {
    this.fetchFromServer()
  },
  methods: {
    PadLeft (value, length) {
      return (value.toString().length < length) ? this.PadLeft('0' + value, length) : value
    },
    setTypeIcon (m) {
      let icon = null
      if (m.row.type_id === 1) {
        icon = 'add'
      } else if (m.row.type_id === 2) {
        icon = 'remove'
      } else if (m.row.type_id === 3) {
        icon = 'restore'
      } else if (m.row.type_id === 4 || m.row.type_id === 5) {
        icon = 'compare_arrows'
      } else if (m.row.type_id === 6) {
        icon = 'restore'
      }
      return icon
    },
    setTypeColor (movement) {
      // props.row.movement_type == 1 ? 'green' : (props.row.movement_type == 2 ? 'orange' : (props.row.movement_type == 3 ? 'blue' : 'red'))
      let color = null
      if (movement.row.type_id === 1 || movement.row.type_id === 4) {
        color = 'positive'
      } else if (movement.row.type_id === 2 || movement.row.type_id === 5) {
        color = 'red'
      } else if (movement.row.type_id === 3) {
        color = 'light-blue'
      } else if (movement.row.type_id === 6) {
        color = 'purple'
      }
      return color
    },
    formatPrice (value) {
      const val = (value / 1).toFixed(3).replace(',', '.')
      return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    },
    fetchFromServer () {
      this.$q.loading.show()
      api.get('/branch-offices/options').then(({ data }) => {
        this.branchOfficeOptions = data.options
        api.get('/storages/options').then(({ data }) => {
          this.storageOptions = data.options
          api.get('/products/options/kardex').then(({ data }) => {
            this.productOptions = data.options
            this.filteredProductOptions = this.productOptions
            this.$q.loading.hide()
          })
        })
      })
    },
    filterProducts (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.filteredProductOptions = this.productOptions.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    // resetBagSelect () {
    //   this.bag = { value: null, label: 'TODOS' }
    // },
    cleanFilters () {
      this.startDate = null
      this.endingDate = null
      this.storage = { value: null, label: 'TODOS' }
      this.product = null
      // this.bag = { value: null, label: 'TODOS' }
      this.data = []
    },
    generateKardex () {
      if (this.product == null || this.product.value == null) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, seleccione el producto.',
          persistent: true
        })
        return false
      }
      // this.$q.loading.show()
      let sDate = null
      let eDate = null
      let branchOfficeId = null
      let storageId = null
      const productId = this.product.value
      if (this.startDate) {
        sDate = this.startDate.substring(6, 10) + '-' + this.startDate.substring(3, 5) + '-' + this.startDate.substring(0, 2)
      }
      if (this.endingDate) {
        eDate = this.endingDate.substring(6, 10) + '-' + this.endingDate.substring(3, 5) + '-' + this.endingDate.substring(0, 2)
      }
      if (this.branchOffice) {
        branchOfficeId = this.branchOffice.value
      }
      if (this.storage) {
        storageId = this.storage.value
      }
      api.get(`/movements/kardex/${sDate}/${eDate}/${branchOfficeId}/${storageId}/${productId}`).then(({ data }) => {
        if (data.result) {
          this.data = data.kardex
          this.$q.loading.hide()
        } else {
          this.$q.loading.hide()
        }
      })
    },
    generateCsvKardex () {
      if (this.product == null || this.product.value == null) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, seleccione el producto.',
          persistent: true
        })
        return false
      }
      let sDate = null
      let eDate = null
      let branchOfficeId = null
      let storageId = null
      const productId = this.product.value
      // let bagId = null
      // const baleId = null
      if (this.startDate) {
        sDate = this.startDate.substring(6, 10) + '-' + this.startDate.substring(3, 5) + '-' + this.startDate.substring(0, 2)
      }
      if (this.endingDate) {
        eDate = this.endingDate.substring(6, 10) + '-' + this.endingDate.substring(3, 5) + '-' + this.endingDate.substring(0, 2)
      }
      if (this.branchOffice) {
        branchOfficeId = this.branchOffice.value
      }
      if (this.storage) {
        storageId = this.storage.value
      }
      const uri = process.env.API + `movements/kardex/csv/${sDate}/${eDate}/${branchOfficeId}/${storageId}/${productId}`
      this.$q.loading.show()
      api.fileDownload(uri).then(({ data }) => {
        const url = window.URL.createObjectURL(new Blob([data], { type: 'application/csv' }))
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', 'Kardex.csv')
        document.body.appendChild(link)
        this.$q.loading.hide()
        link.click()
      })
    },
    generatePdfKardex () {
      if (this.product == null || this.product.value == null) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, seleccione el producto.',
          persistent: true
        })
        return false
      }
      let sDate = null
      let eDate = null
      let branchOfficeId = null
      let storageId = null
      const productId = this.product.value
      if (this.startDate) {
        sDate = this.startDate.substring(6, 10) + '-' + this.startDate.substring(3, 5) + '-' + this.startDate.substring(0, 2)
      }
      if (this.endingDate) {
        eDate = this.endingDate.substring(6, 10) + '-' + this.endingDate.substring(3, 5) + '-' + this.endingDate.substring(0, 2)
      }
      if (this.branchOffice) {
        branchOfficeId = this.branchOffice.value
      }
      if (this.storage) {
        storageId = this.storage.value
      }
      const uri = process.env.API + `movements/kardex/pdf/${sDate}/${eDate}/${branchOfficeId}/${storageId}/${productId}`
      window.open(uri, '_blank')
      // this.$q.loading.show()
      /* api.fileDownload(uri).then(({ data }) => {
        const url = window.URL.createObjectURL(new Blob([data], { type: 'application/pdf' }))
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', 'Kardex.pdf')
        document.body.appendChild(link)
        this.$q.loading.hide()
        link.click()
      }) */
    },
    format (num) {
      const separador = ',' // separador para los miles
      const sepDecimal = '.' // separador para los decimales
      num += ''
      const splitStr = num.split('.')
      let splitLeft = splitStr[0]
      const splitRight = splitStr.length > 1 ? sepDecimal + splitStr[1] : ''
      const regx = /(\d+)(\d{3})/
      while (regx.test(splitLeft)) {
        splitLeft = splitLeft.replace(regx, '$1' + separador + '$2')
      }
      return splitLeft + splitRight
    }
  }
}
</script>

<style>
</style>

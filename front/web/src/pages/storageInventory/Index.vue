<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-8">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Existencias" />
            </q-breadcrumbs>
          </div>
        </div>
        <div class="col-sm-4 pull-right ">
          <div class="q-pa-sm q-gutter-sm">
             <!-- <q-btn color="red" icon="fas fa-file-pdf" @click="exportPDF2()">
              <q-tooltip content-class="bg-purple-6">correo</q-tooltip>
            </q-btn> -->
             <q-btn color="positive" icon="fas fa-file-csv" @click="generateCsv()">
              <q-tooltip content-class="bg-purple-6">Generar Reporte CSV</q-tooltip>
            </q-btn>
            <q-btn color="negative" icon="fas fa-file-pdf" @click="generatePDF()">
              <q-tooltip content-class="bg-purple-6">Generar Reporte</q-tooltip>
            </q-btn>
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
                v-model="branchOffice"
                :options="branchOfficeOptions"
                label="Estación"
                @input="() => {storage = { value: null, label: 'TODOS' }}"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-store-alt" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-md-3" style="padding: 3px;">
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
            <div class="col-xs-12 col-md-3" style="padding: 3px;">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="category"
                :options="categoryOptions"
                label="Categorías"
                @input="() => {lines = { value: null, label: 'TODOS' }}"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-cubes" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-md-3" style="padding: 3px;">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="line"
                :options="filteredLineOptions"
                label="Subcategoría"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-grip-lines-vertical" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-md-5" style="padding: 3px;">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                use-input
                hide-selected
                fill-input
                @filter="filterProducts"
                input-debounce="0"
                v-model="product"
                :options="filteredProductOptionsOut"
                label="Producto"
              >
                <template v-slot:prepend>
                  <q-icon name="emoji_objects" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-md-3" style="padding: 3px;">
              <q-select
                          filled
                          color="dark"
                          bg-color="secondary"
                          v-model="mark"
                          label="Marcas"
                          :options="options"
                          use-input
                          hide-selected
                          fill-input
                          @filter="filterMarcas"
                          >
                          <template v-slot:prepend>
                        <q-icon name="fas fa-grip-lines-vertical" />
                        </template>
                        </q-select>
            </div>
            <div class="col-xs-12 col-md-12 pull-right" style="padding: 3px;">
              <q-btn color="primary" icon="fas fa-search" @click="fetchFromServer()" style="height: 96%;" />
            </div>
            <div class="col-xs-12 col-md-12 pull-right" style="padding: 3px; padding-top: 10px;">
              <label style="font-size: 18px;font-style: italic;color:#A9A9A9;">Nota: Los productos marcados en rojo, son productos inactivos.</label>
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
            @request="qTableRequest"
          >
            <template v-slot:body="props">
              <q-tr :props="props">
                <q-td key="category" style="text-align: left; width: 20%;" :props="props">{{ props.row.category_name }}</q-td>
                <q-td key="line" style="text-align: left; width: 20%;" :props="props">{{ props.row.line_name }}</q-td>
                <q-td style="text-align: center width: 10%;;" key="code" :props="props">{{ props.row.category_code + '-' + props.row.line_code + '-' + props.row.product_code }}</q-td>
                <q-td key="product" style="text-align: left; width: 40%;" :props="props">{{ props.row.product_name }}</q-td>
                <q-td key="almacen" style="text-align: left; width: 40%;" :props="props">{{ props.row.almacen }}</q-td>
                <q-td key="price" style="text-align: right; width: 40%;" :props="props">{{ props.row.price }}</q-td>
                <q-td key="stock" style="text-align: right; width: 10%;" :props="props" v-if="props.row.category_id === 3 || props.row.category_id === 6">
<!--                  <label>{{ formatPrice(props.row.stock) }} KG</label></q-td>-->
                  <label @click="openModal(props.row.product_id, props.row.category_id)" style="text-decoration: underline; cursor: pointer; ">{{ formatPrice(props.row.stock) }} </label></q-td>
                <q-td key="stock" style="text-align: right; width: 10%;" :props="props" v-else>
                  <label >{{ formatPrice(props.row.stock) }} </label></q-td>
              </q-tr>
            </template>
          </q-table>
        </div>
      </div>
    </div>
    <q-dialog v-model="modalProducts" >
      <q-card style="min-width: 1100px;">
        <div>
          <div class="bg-primary">
            <div class="row">
              <div class="col-sm-11 text-h6" style="color:white;">&nbsp;&nbsp;Datos del Producto</div>
              <div class="col-sm-1 pull-right"><q-btn color="white" flat round @click.native="closeModal()" dense icon="close" /></div>
            </div>
          </div>
        </div>
        <div class="row" style="padding-top: 10px;">
          <div class="col-md-3" style="margin-left: 35px">
            <q-input
              color="white"
              bg-color="primary"
              filled
              dark
              disable="true"
              v-model="productInfo.fields.category"
              label="Categoria"
            />
          </div>
          <div class="col-md-1"></div>
          <div class="col-md-3">
            <q-input
              color="white"
              bg-color="primary"
              filled
              dark
              disable="true"
              v-model="productInfo.fields.line"
              label="Linea"
            />
          </div>
          <div class="col-md-1"></div>
          <div class="col-md-3">
            <q-input
              color="white"
              bg-color="primary"
              filled
              dark
              disable="true"
              v-model="productInfo.fields.product"
              label="Producto"
            />
          </div>
        </div>
        <div class="row" style="padding-top: 10px;">
          <q-separator inset style="margin-top:10px;" />
          <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12" style="margin-top:10px;">
            <div class="row q-col-gutter-md">
              <div class="col-md-2" style="text-align: center;">
                <label style="font-size: 14px; font-weight: bold;" v-if="productInfo.fields.category === 'PET PROCESADO' || productInfo.fields.category === 'PET BOTELLA'">JUMBO</label>
                <label style="font-size: 14px; font-weight: bold;" v-if="productInfo.fields.category === 'FIBRAS'">PACA</label>
              </div>
              <div class="col-md-2" style="text-align: center;">
                <label style="font-size: 14px; font-weight: bold;">PESO</label>
              </div>
              <div class="col-md-3" style="text-align: center;">
                <label style="font-size: 14px; font-weight: bold;">SUCURSAL</label>
              </div>
              <div class="col-md-4" style="text-align: center;">
                <label style="font-size: 14px; font-weight: bold;">ALMACÉN</label>
              </div>
              <div class="col-md-1" style="text-align: center;">
                <label style="font-size: 14px; font-weight: bold;"></label>
              </div>
            </div>
            <div style="overflow-y: auto; height: 350px;">
              <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12" v-for="product in productData" v-bind:key="product.id">
                <div class="row q-col-gutter-md">
                  <div class="col-md-2" style="text-align: center;">
                    <label>{{ product.id }}</label>
                  </div>
                  <div class="col-md-2" style="text-align: center;">
                    <label>{{ `${formatter.format(product.qty)} KG.` }}</label>
                  </div>
                  <div class="col-md-3" style="text-align: center;">
                    <label>{{ product.office }}</label>
                  </div>
                  <div class="col-md-4" style="text-align: center;">
                    <label>{{product.sucursal}}</label>
                  </div>
                  <div class="col-md-1" style="text-align: center;"></div>
                </div>
            </div>
            </div>
          </div>
          <q-separator inset style="margin-top:10px;" />
          <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12" style="margin-top:10px;">
            <div class="row">
              <div class="col-md-7"></div>
              <div class="col-md-4 pull-right">
                <label style="font-size: 20px;">Peso: {{ `${formatter.format(totalQtys)} KG` }}</label>
              </div>
              <div class="col-md-1"></div>
            </div>
          </div>
        </div>
        <q-card-actions align="right">
          <br>
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import api from '../../commons/api.js'

export default {
  name: 'StorageInventory',
  data () {
    return {
      formatter: new Intl.NumberFormat('en-US'),
      branchOffice: { value: null, label: 'TODAS' },
      storage: { value: null, label: 'TODOS' },
      category: { value: null, label: 'TODAS' },
      line: { value: null, label: 'TODAS' },
      product: { value: null, label: 'TODOS' },
      date: null,
      branchOfficeOptions: [],
      storageOptions: [],
      categoryOptions: [],
      lineOptions: [],
      productOptions: [],
      options: this.markOptions,
      markOptions: [],
      productInfo: {
        fields: {
          category: null,
          line: null,
          product: null
        }
      },
      mark: 'null',
      productData: [],
      modalProducts: false,
      pagination: {
        sortBy: 'code',
        descending: true,
        page: 1,
        rowsNumber: 0,
        rowsPerPage: 25
      },
      columns: [
        { name: 'category', align: 'center', label: 'CATEGORÍAS', field: 'category', style: 'width: 20%', sortable: true },
        { name: 'line', align: 'center', label: 'SUBCATEGORÍA', field: 'line', style: 'width: 20%', sortable: true },
        { name: 'code', align: 'center', label: 'CÓDIGO', field: 'code', style: 'width: 10%', sortable: true },
        { name: 'product', align: 'center', label: 'PRODUCTO', field: 'product', style: 'width: 40%', sortable: true },
        { name: 'almacen', align: 'center', label: 'ALMACEN', field: 'almacen', style: 'width: 40%', sortable: true },
        { name: 'price', align: 'center', label: 'ÚLTIMO PRECIO', field: 'price', style: 'width: 40%', sortable: true },
        { name: 'stock', align: 'center', label: 'EXISTENCIA', field: 'stock', style: 'width: 10%', sortable: true, sort: (a, b) => Number(a, 10) - Number(b, 10) }
      ],
      data: [],
      fil: {
        flagOption: false
      },
      filteredProductOptionsOut: [],
      filter: ''
    }
  },
  computed: {
    filteredStorageOptions () {
      const options = (this.branchOffice != null && this.branchOffice.value != null) ? this.storageOptions.filter(so => Number(so.branchOffice) === Number(this.branchOffice.value)) : this.storageOptions
      options.unshift({ value: null, label: 'TODOS' })
      return options
    },
    filteredLineOptions () {
      const options = (this.category != null && this.category.value != null) ? this.lineOptions.filter(lo => Number(lo.category) === Number(this.category.value)) : this.lineOptions
      options.unshift({ value: null, label: 'TODOS' })
      return options
    },
    filteredProductOptions () {
      const optionss = (this.line != null && this.line.value != null) ? this.productOptions.filter(po => Number(po.line) === Number(this.line.value)) : this.productOptions
      optionss.unshift({ value: null, label: 'TODOS' })
      return optionss
    },
    totalQtys () {
      let price = 0
      this.productData.forEach(detail => {
        price += Number(detail.qty)
      })
      return price
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
    this.$q.loading.show()
    this.$q.loading.hide()
    // this.searchStock()
    this.getMarks()
    api.get('/branch-offices/options').then(({ data }) => {
      this.branchOfficeOptions = data.options
      this.branchOfficeOptions.unshift({ value: null, label: 'TODOS' })
      api.get('/storages/options').then(({ data }) => {
        this.storageOptions = data.options
        api.get('/categories/options').then(({ data }) => {
          this.categoryOptions = data.options
          this.categoryOptions.unshift({ value: null, label: 'TODAS' })
          api.get('/lines/options').then(({ data }) => {
            this.lineOptions = data.options
            api.get('/products/options').then(({ data }) => {
              this.productOptions = data.options
            })
          })
        })
      })
    })
  },
  methods: {
    getMarks () {
      this.mark = { label: 'TODOS', value: null }
      api.get('/marks/options').then(({ data }) => {
        this.markOptions = data.options
        this.markOptions.unshift({ value: null, label: 'TODOS' })
      })
    },
    exportPDF2 () {
      api.get('mail/generatePDF')
      /* const uri = process.env.API + 'mail/generatePDF'
      window.open(uri, '_blank') */
    },
    filterMarcas (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.options = this.markOptions.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    filterProducts (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.filteredProductOptionsOut = this.filteredProductOptions.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    formatPrice (value) {
      const val = (value / 1).toFixed(3).replace(',', '.')
      return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    },
    fetchFromServer () {
      this.$q.loading.show()
      this.qTableRequest({
        pagination: this.pagination,
        filter: this.filter
      })
    },
    async qTableRequest (props) {
      this.$q.loading.show()
      this.pagination = props.pagination
      this.filter = props.filter
      this.products = []
      const params = []
      let branchOfficeId = null
      let storageId = null
      let productId = null
      let mark = null
      if (this.branchOffice) {
        branchOfficeId = this.branchOffice.value
      }
      if (this.storage) {
        storageId = this.storage.value
      }
      if (this.product) {
        productId = this.product.value
      }
      if (this.mark) {
        mark = this.mark.value
      }
      params.branchOfficeId = branchOfficeId
      params.storageId = storageId
      params.categoryId = this.category.value
      params.lineId = this.line.value
      params.status = true
      params.pagination = this.pagination
      params.filter = this.filter
      params.product = productId
      params.mark = mark
      await api.post('/movements/storageInventoryByMark', params).then(({ data }) => {
        console.log(data)
        this.$q.loading.hide()
        this.data = data.stock.data
        this.pagination.rowsNumber = data.stock.rowCounts
      }).catch(error => error)
    },
    searchStock () {
      /* if (this.product.value === null) {
        this.$q.notify({
          message: 'Por favor selecione un producto.',
          color: 'red',
          position: 'top'
        })
        return false
      } */
      this.$q.loading.show()
      let branchOfficeId = null
      let storageId = null
      let categoryId = null
      let lineId = null
      let productId = null
      let date = null
      let mark = null
      if (this.branchOffice) {
        branchOfficeId = this.branchOffice.value
      }
      if (this.storage) {
        storageId = this.storage.value
      }
      if (this.category) {
        categoryId = this.category.value
      }
      if (this.line) {
        lineId = this.line.value
      }
      if (this.product) {
        productId = this.product.value
      }
      if (this.date) {
        date = this.date
        while (date.includes('/')) {
          date = date.replace('/', '-')
        }
      }
      mark = this.mark.value
      api.get(`/movements/storageInventoryv2/${branchOfficeId}/${storageId}/${categoryId}/${lineId}/${productId}/${date}/${mark}`).then(({ data }) => {
        if (data.result) {
          this.data = data.stock
        }
        this.$q.loading.hide()
      })
    },
    openModal (productId, categoryID) {
      const params = []
      params.product = productId
      params.category = categoryID
      params.branchoffice = this.branchOffice.value
      params.storage = this.storage.value
      api.post('/movements/getDataProducts', params).then(({ data }) => {
        if (data.result) {
          this.productInfo.fields.category = data.product[0].category
          this.productInfo.fields.line = data.product[0].line
          this.productInfo.fields.product = data.product[0].product
          this.productData = data.productData
          this.modalProducts = true
        }
      })
    },
    closeModal () {
      this.modalProducts = false
    },
    generatePDF () {
      const user = this.$store.getters['users/id']
      let branchOfficeId = null
      let storageId = null
      let categoryId = null
      let lineId = null
      let productId = null
      let date = null
      let mark = null
      if (this.branchOffice) {
        branchOfficeId = this.branchOffice.value
      }
      if (this.storage) {
        storageId = this.storage.value
      }
      if (this.category) {
        categoryId = this.category.value
      }
      if (this.line) {
        lineId = this.line.value
      }
      if (this.product) {
        productId = this.product.value
      }
      if (this.date) {
        date = this.date
        while (date.includes('/')) {
          date = date.replace('/', '-')
        }
      }
      if (this.mark) {
        mark = this.mark.value
      }
      const uri = process.env.API + `movements/inventorybymark/pdf/${branchOfficeId}/${storageId}/${categoryId}/${lineId}/${productId}/${user}/${mark}`
      window.open(uri, '_blank')
      // this.$q.loading.show()
      /* api.fileDownload(uri).then(({ data }) => {
        const url = window.URL.createObjectURL(new Blob([data], { type: 'application/pdf' }))
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', 'existencias.pdf')
        document.body.appendChild(link)
        this.$q.loading.hide()
        link.click()
      }) */
    },
    generateCsv () {
      const user = this.$store.getters['users/id']
      let branchOfficeId = null
      let storageId = null
      let categoryId = null
      let lineId = null
      let productId = null
      let date = null
      let mark = null
      if (this.branchOffice) {
        branchOfficeId = this.branchOffice.value
      }
      if (this.storage) {
        storageId = this.storage.value
      }
      if (this.category) {
        categoryId = this.category.value
      }
      if (this.line) {
        lineId = this.line.value
      }
      if (this.product) {
        productId = this.product.value
      }
      if (this.date) {
        date = this.date
        while (date.includes('/')) {
          date = date.replace('/', '-')
        }
      }
      if (this.mark) {
        mark = this.mark.value
      }
      const uri = process.env.API + `movements/inventorybymark/csv/${branchOfficeId}/${storageId}/${categoryId}/${lineId}/${productId}/${user}/${mark}`
      window.open(uri, '_blank')
    }
  }
}
</script>

<style>
</style>

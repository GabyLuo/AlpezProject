<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-4">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Artículos" />
            </q-breadcrumbs>
          </div>
        </div>
        <div class="col-xs-8 col-md-8 pull-right">
          <div class="q-pa-sm q-gutter-sm">
            <q-btn color="positive" icon="fas fa-file-csv" label="Descargar Productos" @click="updateproducts()"></q-btn>
            <q-btn color="positive" icon="fas fa-file-csv" label="Descargar Productos" @click="generateCsv()"></q-btn>

            <q-btn color="positive" icon="fas fa-file-excel" @click="generateCsvPrices()"><q-tooltip content-class="bg-primary">Descargar Precios</q-tooltip></q-btn>
            <q-btn color="positive" icon="fas fa-file-upload" @click="openUploadFileModalPrices()"><q-tooltip content-class="bg-primary">Actualizar Precios</q-tooltip></q-btn>
             <!--<q-btn color="positive" icon="fas fa-file-csv" label="Actualizar codigo CSV" @click="uodateCodeProduct()"/>-->
            <!--<q-btn color="positive" icon="fas fa-file-csv" label="Actualizar codigo CSV" @click="uodateCodeProduct()"/>-->
            <!--<q-btn color="positive" icon="cloud_upload" label="CARGA MASIVA" @click="openUploadFileModal2()"/>-->
            <q-btn class="bg-primary" style="color: white" icon="add" label="Nuevo" @click.native="$router.push('/products/new')" />
          </div>
        </div>
      </div>
    </div>

    <div class="q-pa-md bg-grey-3">
      <div class="row bg-white border-panel">
        <div class="col q-pa-md">
          <div class="row q-mb-sm">
           <div class="col-xs-12 col-md-3" style="padding: 1%;">
              <q-select
                          filled
                          color="dark"
                          bg-color="secondary"
                          v-model="category"
                          label="Categorías"
                          :options="optionsCategory"
                          use-input
                          hide-selected
                          fill-input
                          @filter="filterCategory"
                          @input="fetchFromServer()"
                          >
                          <template v-slot:prepend>
                        <q-icon name="fas fa-grip-lines-vertical" />
                        </template>
                        </q-select>
            </div>
            <div class="col-xs-12 col-md-3" style="padding: 1%;">
              <q-select
                          filled
                          color="dark"
                          bg-color="secondary"
                          v-model="line"
                          label="Subcategoría"
                          :options="optionsLine"
                          use-input
                          hide-selected
                          fill-input
                          @filter="filterLine"
                          @input="fetchFromServer()"
                          >
                          <template v-slot:prepend>
                        <q-icon name="fas fa-grip-lines-vertical" />
                        </template>
                        </q-select>
            </div>
            <div class="col-xs-12 col-md-3" style="padding: 1%;">
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
                          @input="fetchFromServer()"
                          >
                          <template v-slot:prepend>
                        <q-icon name="fas fa-grip-lines-vertical" />
                        </template>
                        </q-select>
            </div>
            <div class="col-xs-12 col-md-3" style="padding: 1%;">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="status"
                :options="[
                  {value: true, label: 'ACTIVO'},
                  {value: false, label: 'INACTIVO'}
                ]"
                label="Estatus"
                @input="fetchForStatus()"
              >
                <template v-slot:prepend>
                  <q-icon :name="(status.value ? 'battery_full' : 'battery_alert')" />
                </template>
              </q-select>
            </div>
          </div>
          <q-table
            flat
            bordered
            :data="filteredProducts"
            :columns="columns"
            row-key="code"
            :pagination.sync="pagination"
            :filter="filter"
            @request="qTableRequest"
          >
            <template v-slot:top>
              <div style="width: 100%;">
                <q-input dense debounce="300" v-model="filter" placeholder="Buscar" @input="v => { filter = v.toUpperCase() }">
                  <template v-slot:append>
                    <q-icon name="search" />
                  </template>
                </q-input>
              </div>
            </template>
            <template v-slot:body="props">
              <q-tr :props="props">
                <q-td key="code" flat @click.native="editSelectedRow(props.row.id)" class="text-primary cursor-pointer" style=" text-align: center; text-decoration: underline;" :props="props">{{ props.row.product_code + PadLeft(props.row.code, 5)}}</q-td>
                <q-td key="old_code" flat @click.native="editSelectedRow(props.row.id)" class="text-primary cursor-pointer" style=" text-align: center; text-decoration: underline;" :props="props">{{ props.row.old_code }}</q-td>
                <q-td key="rebasa_code" style="text-align: left;" :props="props">{{ props.row.rebasa_code }}</q-td>
                <q-td key="name" style="text-align: left;" :props="props">{{ props.row.name }}</q-td>
                <!-- <q-td key="family" style="text-align: left;" :props="props">{{ props.row.family }}</q-td> -->
                <q-td key="category" style="text-align: left;" :props="props">{{ props.row.category }}</q-td>
                <q-td key="line" style="text-align: left;" :props="props">{{ props.row.line }}</q-td>
                <q-td key="description" style="text-align: left;" :props="props">{{ props.row.description }}</q-td>
                <!-- <q-td key="photo"  :props="props">
                  <q-img
                    :src="(props.row.photo != null ? serverUrl + 'assets/images/products/' + props.row.photo : null)"
                    spinner-color="white"
                    :ratio="0"
                  />
                </q-td> -->
                <q-td key="mark_name" style="text-align: left;" :props="props">{{ props.row.mark_name }}</q-td>
                <q-td key="status"  :props="props" style="text-align: center;">
                  <q-chip square dense :color="props.row.active ? 'positive' : 'negative'" text-color="white">
                    {{ (props.row.active ? 'ACTIVO' : 'INACTIVO') }}
                  </q-chip>
                </q-td>
                <q-td key="actions" :props="props" class="pull-left">
                  <q-btn color="primary" icon="photo" flat @click.native="editPhoto(props.row.id)" size="10px">
                    <q-tooltip content-class="bg-primary">Fotografía</q-tooltip>
                  </q-btn>
                  <q-btn color="primary" icon="fas fa-edit" flat @click.native="editSelectedRow(props.row.id)" size="10px">
                    <q-tooltip content-class="bg-primary">Editar</q-tooltip>
                  </q-btn>
                  <q-btn color="negative" icon="fas fa-trash-alt" flat @click.native="deleteSelectedRow(props.row.id)" size="10px">
                    <q-tooltip content-class="bg-red">Eliminar</q-tooltip>
                  </q-btn>
                </q-td>
              </q-tr>
            </template>
          </q-table>
        </div>
      </div>
    </div>

    <q-dialog v-model="photoModal" persistent>
      <q-card style="min-width: 25%; !important;">
        <q-card-section class="row bg-primary text-white">
          <div class="col-xs-12 col-sm-10 text-h6">Foto: {{ photoProductCode }}</div>
          <q-btn class="col-xs-12 col-sm-2 pull-right" icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-card-section style="max-height: 60vh" class="scroll">
          <q-uploader
            accept=".jpg, image/*"
            method="POST"
            ref="photoProductRef"
            hide-upload-btn
            :factory="factoryFn"
            @uploaded="afterUploadPhoto"
          />
           <q-card-section style="max-height: 60vh">
      <q-carousel
      v-if="cndcarousel == true"
        animated
        v-model="slide"
        arrows
        navigation
        infinite
      >
      <q-carousel-slide v-for="slide in slides" :key="slide.id" :name="slide.id" :img-src="slide.url" >
          <div class="text-right">
            <q-btn class="text-right" color="red" icon="fas fa-trash-alt" flat @click.native="deletePhoto(slide)" size="15px">
      <q-tooltip content-class="bg-positive">Eliminar</q-tooltip>
      </q-btn>
    </div>
        </q-carousel-slide>
      </q-carousel>
      </q-card-section>
           </q-card-section>
        <q-card-actions align="right" class="text-primary">
          <q-btn color="positive" label="Subir foto" @click="uploadPhoto()" />
        </q-card-actions>
      </q-card>
    </q-dialog>
    <!--<q-dialog v-model="documentFileModal" persistent>
        <q-card>
          <q-card-section class="row">
            <div class="col-xs-12 col-sm-10 text-h6">Archivo: {{ documentName }}</div>
            <q-btn class="col-xs-12 col-sm-2 pull-right" icon="close" flat round dense v-close-popup />
          </q-card-section>
          <q-card-section>
            <q-uploader
              :url="fileDocumentUrl"
              :headers="[{name: 'Authorization', value: token}]"
              method="POST"
              ref="fileDocumentRef"
              hide-upload-btn
              @uploaded="afterUploadDocumentFile"
            />
          </q-card-section>
          <q-card-actions align="right" class="text-secondary">
            <q-btn flat label="Subir archivo" @click="uploadDocumentFile()" />
          </q-card-actions>
        </q-card>
      </q-dialog>-->
      <q-dialog v-model="documentFileModal2" persistent>
        <q-card>
          <q-card-section class="row">
            <div class="col-xs-12 col-sm-10 text-h6">Archivo: {{ documentName2 }}</div>
            <q-btn class="col-xs-12 col-sm-2 pull-right" icon="close" flat round dense v-close-popup />
          </q-card-section>
          <q-card-section>
            <q-uploader
              :url="fileDocumentUrl2"
              :headers="[{name: 'Authorization', value: token}]"
              method="POST"
              ref="fileDocumentRef2"
              hide-upload-btn
              @uploaded="afterUploadDocumentFile2"
            />
          </q-card-section>
          <q-card-actions align="right" class="text-secondary">
            <q-btn flat label="Subir archivo" @click="uploadDocumentFile2()" />
          </q-card-actions>
        </q-card>
      </q-dialog>
      <q-dialog v-model="documentFileModalUpdatePrices" persistent>
        <q-card>
          <q-card-section class="row">
            <div class="col-xs-12 col-sm-10 text-h6">Archivo: {{ documentNamePrices }}</div>
            <q-btn class="col-xs-12 col-sm-2 pull-right" icon="close" flat round dense v-close-popup />
          </q-card-section>
          <q-card-section>
            <q-uploader
              :url="fileDocumentUrlPrices"
              :headers="[{name: 'Authorization', value: token}]"
              method="POST"
              ref="fileDocumentRefPrices"
              hide-upload-btn
              @uploaded="afterUploadDocumentFilePrices"
            />
          </q-card-section>
          <q-card-actions align="right" class="text-secondary">
            <q-btn flat label="Subir archivo" @click="uploadDocumentFilePrices()" />
          </q-card-actions>
        </q-card>
      </q-dialog>
  </q-page>
</template>

<script>
import api from '../../commons/api.js'
export default {
  name: 'IndexProducts',
  data () {
    return {
      cndcarousel: false,
      slide: null,
      slides: [],
      category: { value: null, label: 'TODOS' },
      line: { value: null, label: 'TODOS' },
      status: { value: true, label: 'ACTIVO' },
      categoryOptions: [],
      lineOptions: [],
      pagination: {
        sortBy: 'code',
        descending: true,
        page: 1,
        rowsNumber: 0,
        rowsPerPage: 25
      },
      columns: [
        { name: 'old_code', align: 'center', label: 'CÓDIGO', field: 'old_code', style: 'width: 10%', sortable: true },
        { name: 'rebasa_code', align: 'center', label: 'CÓDIGO INTERNO', field: 'rebasa_code', style: 'width: 10%', sortable: true },
        { name: 'name', align: 'center', label: 'NOMBRE', field: 'name', style: 'width: 15%', sortable: true },
        { name: 'category', align: 'center', label: 'CATEGORÍA', field: 'category', style: 'width: 15%', sortable: true },
        { name: 'line', align: 'center', label: 'SUBCATEGORÍA', field: 'line', style: 'width: 10%', sortable: true },
        { name: 'mark_name', align: 'center', label: 'MARCA', field: 'mark_name', style: 'width: 10%', sortable: true },
        { name: 'status', align: 'center', label: 'ESTATUS', field: 'status', style: 'width: 10%', sortable: false },
        { name: 'actions', align: 'center', label: 'ACCIONES', field: 'actions', style: 'width: 10%', sortable: false }
      ],
      products: [],
      filter: '',
      photoModal: false,
      photoUrl: null,
      photoProductId: null,
      photoProductCode: null,
      mark: null,
      options: this.markOptions,
      optionsCategory: this.categoryOptions,
      optionsLine: this.lineOptions,
      markOptions: [],
      serverUrl: process.env.API,
      documentFileModal: false,
      documentFileModal2: false,
      documentFileModalUpdatePrices: false,
      documentName: null,
      documentName2: null,
      documentNamePrices: null
    }
  },
  beforeRouteEnter (to, from, next) {
    next(vm => {
      const propiedades = vm.$store.getters['users/rol']
      console.log(propiedades)
      if (propiedades === 1 || propiedades === 3 || propiedades === 7 || propiedades === 2 || propiedades === 20 || propiedades === 4 || propiedades === 27 || propiedades === 22) {
        next()
      } else {
        next('/')
      }
    })
  },
  /* beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(2) && !this.$store.getters['users/roles'].includes(22) && !this.$store.getters['users/roles'].includes(4)) {
      this.$router.push('/')
    }
  }, */
  mounted () {
    this.fetchFromServer()
    this.getCategories()
    this.getLines()
    this.getMarks()
  },
  methods: {
    uploadDocumentFile () {
      this.$refs.fileDocumentRef.upload()
    },
    afterUploadDocumentFile (response) {
      const data = JSON.parse(response.xhr.response)
      this.$q.notify({
        message: data.message.content,
        position: 'top',
        color: (data.result ? 'positive' : 'warning')
      })
      if (data.result) {
        this.fetchFromServer()
        this.documentFileModal = false
      }
    },
    fileDocumentUrl () {
      // const id = this.$route.params.id
      return `${process.env.API}products/file`
    },
    uodateCodeProduct () {
      this.documentFileModal = true
    },
    PadLeft (value, length) {
      return (value.toString().length < length) ? this.PadLeft('0' + value, length) : value
    },
    generateCsv () {
      let categoryId = 'TODOS'
      let lineId = 'TODOS'
      if (this.category) { categoryId = Number(this.category.value) }
      if (this.line) { lineId = Number(this.line.value) }
      const uri = process.env.API + `products/csv/${categoryId}/${lineId}`
      window.open(uri, '_blank')
    },
    generateCsvPrices () {
      let categoryId = 'TODOS'
      let lineId = 'TODOS'
      let markId = 'TODOS'
      if (this.category) { categoryId = Number(this.category.value) }
      if (this.line) { lineId = Number(this.line.value) }
      if (this.mark) { markId = Number(this.mark.value) }
      const uri = process.env.API + `products/csvprices/${categoryId}/${lineId}/${markId}`
      window.open(uri, '_blank')
    },
    fetchForCategory () {
      this.line = { label: 'TODOS', value: null }
      this.fetchFromServer()
    },
    fetchFromServer () {
      this.$q.loading.show()
      this.qTableRequest({
        pagination: this.pagination,
        filter: this.filter
      })
      // api.get('/products').then(({ data }) => {
      //   this.products = data.products
      //   console.log(data)
      // })
    },
    async qTableRequest (props) {
      this.pagination = props.pagination
      this.filter = props.filter
      this.products = []
      const params = []
      params.category = this.category.value
      params.line = this.line.value
      params.status = this.status.value
      params.pagination = this.pagination
      params.filter = this.filter
      params.mark = this.mark
      await api.post('/products/pag', params).then(({ data }) => {
        this.$q.loading.hide()
        this.products = data.products
        this.pagination.rowsNumber = data.productsCount
      }).catch(error => error)
    },
    getCategories () {
      api.get('/categories/options').then(({ data }) => {
        this.categoryOptions = data.options
        this.categoryOptions.unshift({ value: null, label: 'TODOS' })
      })
    },
    getLines () {
      api.get('/lines/options').then(({ data }) => {
        this.lineOptions = data.options
        this.lineOptions.unshift({ value: null, label: 'TODOS' })
        this.$q.loading.hide()
      })
    },
    fetchForStatus () {
      this.fetchFromServer()
    },
    editPhoto (id) {
      this.photoModal = true
      api.get(`/products/getPhotos/${id}`).then(({ data }) => {
        if (data.photos.length > 0) {
          this.cndcarousel = true
          this.slide = data.photos[0].id
        } else {
          this.cndcarousel = false
        }
        var labels = []
        for (var i = 0; i < data.photos.length; i++) {
          labels.push({ id: data.photos[i].id, url: this.serverUrl + 'assets/images/products/' + data.photos[i].url })
        }
        this.slides = labels
      })
      const product = this.products.filter(p => Number(p.id) === Number(id))[0]
      this.photoProductId = product.id
      this.photoProductCode = product.code
      this.photoModal = true
    },
    editSelectedRow (id) {
      this.$router.push(`/products/${id}`)
    },
    deleteSelectedRow (id) {
      this.$q.dialog({
        title: 'Confirmación',
        message: '¿Desea eliminar este Producto?',
        persistent: true,
        ok: {
          label: 'Aceptar',
          color: 'green'
        },
        cancel: {
          label: 'Cancelar',
          color: 'red'
        }
      }).onOk(() => {
        this.$q.loading.show()
        api.delete(`/products/${id}`).then(({ data }) => {
          this.$q.notify({
            message: data.message.content,
            position: 'top',
            color: (data.result ? 'positive' : 'warning'),
            icon: (data.result ? 'thumb_up' : 'close')
          })
          if (data.result) {
            this.$q.loading.hide()
            this.fetchFromServer()
          }
        })
      }).onCancel(() => {})
    },
    afterUploadPhoto (response) {
      this.$q.loading.true()
      const data = JSON.parse(response.xhr.response)
      this.$q.notify({
        message: data.message.content,
        position: 'top',
        color: (data.result ? 'positive' : 'warning')
      })
      if (data.result) {
        this.$q.loading.hide()
        this.fetchFromServer()
        this.photoModal = false
      } else {
        this.$q.loading.hide()
      }
    },
    uploadPhoto () {
      this.$refs.photoProductRef.upload()
    },
    factoryFn (files) {
      this.$q.loading.show()
      const formData = new FormData()
      formData.append('fileReg', files[0])
      formData.append('product_id', this.photoProductId)
      api.file('/products/photo/', formData).then(({ data }) => {
        this.fetchFromServer()
        this.$showNotify(data.message.content, data.result ? 'positive' : 'negative')
        this.photoModal = false
        this.$q.loading.hide()
      })
    },
    filterMarcas (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.options = this.markOptions.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    filterCategory (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.optionsCategory = this.categoryOptions.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    filterLine (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.optionsLine = this.lineOptions.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    getMarks () {
      this.mark = { label: 'TODOS', value: null }
      api.get('/marks/options').then(({ data }) => {
        this.markOptions = data.options
        this.markOptions.unshift({ value: null, label: 'TODOS' })
      })
    },
    deletePhoto (row) {
      var params = []
      params.id = row.id
      api.post('/products/photoDelete', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.fetchFromServer()
          this.photoModal = false
        }
      })
    },
    openUploadFileModal2 () {
      this.documentFileModal2 = true
    },
    uploadDocumentFile2 () {
      this.$refs.fileDocumentRef2.upload()
    },
    fileDocumentUrl2 () {
      // const id = this.$route.params.id
      return `${process.env.API}products/file2`
    },
    afterUploadDocumentFile2 (response) {
      const data = JSON.parse(response.xhr.response)
      this.$q.notify({
        message: data.message.content,
        position: 'top',
        color: (data.result ? 'positive' : 'warning')
      })
      if (data.result) {
        this.fetchFromServer()
        this.documentFileModal2 = false
      }
    },
    openUploadFileModalPrices () {
      this.documentFileModalUpdatePrices = true
    },
    uploadDocumentFilePrices () {
      this.$refs.fileDocumentRefPrices.upload()
    },
    fileDocumentUrlPrices () {
      // const id = this.$route.params.id
      return `${process.env.API}products-prices/filePrices`
    },
    afterUploadDocumentFilePrices (response) {
      const data = JSON.parse(response.xhr.response)
      this.$q.notify({
        message: data.message.content,
        position: 'top',
        color: (data.result ? 'positive' : 'warning')
      })
      if (data.result) {
        this.fetchFromServer()
        this.documentFileModalUpdatePrices = false
      }
    }
  },
  computed: {
    roleId () {
      const user = this.$store.getters['users/rol']
      return parseInt(user)
    },
    token () {
      const token = 'Bearer ' + localStorage.getItem('JWT')
      return token
    },
    filteredLineOptions () {
      if (this.category != null && this.category.value != null) {
        return this.lineOptions.filter(l => Number(l.category) === Number(this.category.value))
      }
      return this.lineOptions
    },
    filteredProducts () {
      const products = this.products.filter(product => product.active === this.status.value)
      if (this.line != null && this.line.value != null) {
        return products.filter(product => Number(product.line_id) === Number(this.line.value))
      } else if (this.category != null && this.category.value != null) {
        return products.filter(product => Number(product.category_id) === Number(this.category.value))
      }
      return products
    },
    photoProductUrl () {
      return process.env.API + 'products/photo/' + this.photoProductId
    }
  }
}
</script>

<style>
</style>

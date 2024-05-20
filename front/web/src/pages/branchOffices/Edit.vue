<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-9">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Estaciones" to="/branch-offices" />
              <q-breadcrumbs-el label="Editar" v-text="branchOffice.fields.name" />
            </q-breadcrumbs>
          </div>
        </div>
        <div class="col-sm-3 pull-right">
          <div class="q-pa-md q-gutter-sm">
              <q-btn color="positive" icon="save" label="Actualizar" @click="updateBranchOffice()" />
          </div>
        </div>
      </div>
    </div>

    <div class="q-pa-md bg-grey-3">
      <div class="row bg-white border-panel">
        <div class="col q-pa-md">
          <div class="row q-mb-sm">
            <div class="col-sm-1 offset-11 pull-right">
              <q-btn color="primary" label="Editar" style="visibility: hidden;" />
            </div>
          </div>

          <div class="row q-col-gutter-xs">
            <div class="col-xs-12 col-sm-2">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="branchOffice.fields.code"
                :error="$v.branchOffice.fields.code.$error"
                label="Código"
                :rules="codeRules"
                @input="v => { branchOffice.fields.code = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-signature" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-22 col-sm-2">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="branchOffice.fields.name"
                :error="$v.branchOffice.fields.name.$error"
                label="Nombre"
                :rules="nameRules"
                @input="v => { branchOffice.fields.name = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-signature" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-2 col-sm-4 col-md-4 col-lg-4">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="branchOffice.fields.address"
                :error="$v.branchOffice.fields.address.$error"
                label="Calle"
                :rules="addressRules"
                @input="v => { branchOffice.fields.address = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-road" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-2 col-sm-2 col-md-2 col-ld-2">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="branchOffice.fields.outdoor_number"
                :error="$v.branchOffice.fields.outdoor_number.$error"
                label="Número exterior"
                :rules="outdoor_numberRules"
                @input="v => { branchOffice.fields.outdoor_number = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-hashtag" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-2 col-sm-2 col-md-2 col-ld-2">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="branchOffice.fields.indoor_number"
                label="Número interior"
                @input="v => { branchOffice.fields.indoor_number = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-hashtag" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-2 col-sm-3 col-md-3 col-lg-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="branchOffice.fields.between_street"
                :error="$v.branchOffice.fields.between_street.$error"
                label="Entre calles"
                :rules="between_streetRules"
                @input="v => { branchOffice.fields.between_street = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-road" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-2 col-md-2 col-lg-2">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="branchOffice.fields.postal_code_id"
                :rules="cpRules"
                :error="$v.branchOffice.fields.postal_code_id.$error"
                :options="postal_codes"
                label="Código Postal"
                use-input
                emit-value
                @filter="filterPostalCodes"
                @input="getSuburbsByPostalCode(branchOffice.fields.postal_code_id)"
                map-options
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-mail-bulk" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="branchOffice.fields.suburb_id"
                :options="suburbs_options"
                :rules="suburbRules"
                :error="$v.branchOffice.fields.suburb_id.$error"
                label="Colonia"
                use-input
                emit-value
                @filter="filterSuburbs"
                map-options
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-city" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-2 col-sm-2">
              <q-select
              filled
              class="uppcase"
              color="dark"
              bg-color="secondary"
              v-model="branchOffice.fields.municipio_id"
              :error="$v.branchOffice.fields.municipio_id.$error"
              label="Municipio"
              :options="options"
              use-input
              hide-selected
              fill-input
              input-debounce="0"
              emit-value
              @filter="filterMunicipio"
              map-options>
              <template v-slot:no-option>
                <q-item>
                  <q-item-section class="text-grey">
                    No hay Resultados
                  </q-item-section>
                </q-item>
              </template>
            </q-select>
            </div>
            <div class="col-xs-2 col-sm-2">
              <q-select
              filled
              class="uppcase"
              color="dark"
              bg-color="secondary"
              v-model="branchOffice.fields.city_id"
              label="Ciudad"
              :options="cityOptions"
              use-input
              hide-selected
              fill-input
              @filter="filterCity"
              input-debounce="0"
              emit-value
              :disable="true"
              map-options>
              <template v-slot:no-option>
                <q-item>
                  <q-item-section class="text-grey">
                    No hay Resultados
                  </q-item-section>
                </q-item>
              </template>
            </q-select>
            </div>
            <div class="col-xs-3 col-sm-2">
              <q-select
              filled
              class="uppcase"
              color="dark"
              bg-color="secondary"
              v-model="branchOffice.fields.estado_id"
              :error="$v.branchOffice.fields.estado_id.$error"
              label="ESTADO"
              :options="options2"
              @input="getMunicipiosbyEstado(branchOffice.fields.estado_id, true)"
              use-input
              hide-selected
              fill-input
              input-debounce="0"
              emit-value
              @filter="filterEstado"
              map-options>
                <template v-slot:no-option>
                  <q-item>
                    <q-item-section class="text-grey">
                      No hay Resultados
                    </q-item-section>
                  </q-item>
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-2">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="branchOffice.fields.serie"
                :error="$v.branchOffice.fields.serie.$error"
                :rules="serieRules"
                label="Serie facturación"
              >
                <template v-slot:prepend>
                  <q-icon name="description" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-2">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="branchOffice.fields.serie_pagos"
                :error="$v.branchOffice.fields.serie_pagos.$error"
                :rules="serie_pagosRules"
                label="Serie pagos facturacion"
              >
                <template v-slot:prepend>
                  <q-icon name="description" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-3">
                <q-select
                color="dark"
                bg-color="secondary"
                filled
                emit-value
                map-options
                :options="clusterOptions"
                v-model="branchOffice.fields.cluster_id"
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
                emit-value
                map-options
                :options="customerOptions"
                v-model="branchOffice.fields.customer_id"
                label="Cliente">
                  <template v-slot:prepend>
                    <q-icon name="fa solid fa-user"></q-icon>
                  </template>
              </q-select>
              </div>
          </div>

        </div>
      </div>

      <br>

      <div class="row bg-white border-panel">
        <div class="col q-pa-md">
            <div class="row" style="padding-bottom: 15px;">
            Datos Bancarios
          </div>
          <div class="row q-col-gutter-xs">
            <div class="col-xs-12 col-sm-4 col-md-4 col-lg-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="branchOffice.fields.rfc_banco"
                label="RFC Banco"
                @input="v => { branchOffice.fields.rfc_banco = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="description" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-4 col-md-4 col-lg-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="branchOffice.fields.cuenta"
                label="Cuenta Bancaria"
                @input="v => { branchOffice.fields.cuenta = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-hashtag" />
                </template>
              </q-input>
            </div>
          </div>

        </div>
      </div>

      <br>

      <div class="bg-white border-panel">
        <q-tabs
        v-model="currentTab"
        dense
        class="text-grey"
        active-color="primary"
        indicator-color="primary"
        align="justify"
        narrow-indicator
        >
        <q-tab name="almacenes" label="Almacenes" />
        <q-tab name="fiscales" label="Datos fiscales" />
        </q-tabs>
        <q-tab-panels v-model="currentTab" animated>
          <q-tab-panel name="almacenes">
            <div style="font-weight: normal;">
            <div class="q-col-gutter-xs" style="padding: 2%;">
              <q-table
                flat
                bordered
                :data="storages"
                :columns="storageColumns"
                row-key="code"
                :pagination.sync="pagination"
              >
                <template v-slot:body="props">
                  <q-tr :props="props">
                    <q-td key="code" :props="props">{{ props.row.code }}</q-td>
                    <q-td key="name" :props="props">{{ props.row.name }}</q-td>
                  </q-tr>
                </template>
              </q-table>
            </div>
          </div>
          </q-tab-panel>
          <q-tab-panel name="fiscales">
            <div style="font-weight: normal;">
              <div class="row q-col-gutter-xs" style="padding: 2%;">
                <div class="col-xs-12 col-sm-3">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="fiscal.fields.razon_social"
                    :error="$v.fiscal.fields.razon_social.$error"
                    label="Razon social"
                  >
                    <template v-slot:prepend>
                      <q-icon name="description" />
                    </template>
                  </q-input>
                </div>
                <div class="col-xs-12 col-sm-3">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="fiscal.fields.rfc"
                    :error="$v.fiscal.fields.rfc.$error"
                    label="RFC"
                  >
                    <template v-slot:prepend>
                      <q-icon name="description" />
                    </template>
                  </q-input>
                </div>
                <div class="col-xs-12 col-sm-3">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="fiscal.fields.lugar_expedicion"
                    :error="$v.fiscal.fields.lugar_expedicion.$error"
                    label="Código postal"
                    type=“number”
                    maxlength="5"
                  >
                    <template v-slot:prepend>
                      <q-icon name="description" />
                    </template>
                  </q-input>
                </div>
                <div class="col-xs-12 col-sm-3">
                  <q-select
                    color="dark"
                    bg-color="secondary"
                    filled
                    emit-value
                    map-options
                    v-model="fiscal.fields.metodo_pago"
                    :error="$v.fiscal.fields.metodo_pago.$error"
                    label="Método de pago"
                    :options="metodoPagoOptions"
                  >
                    <template v-slot:prepend>
                      <q-icon name="description" />
                    </template>
                  </q-select>
                </div>
                <div class="col-xs-12 col-sm-3">
                  <q-select
                    color="dark"
                    bg-color="secondary"
                    filled
                    emit-value
                    map-options
                    v-model="fiscal.fields.forma_pago"
                    :error="$v.fiscal.fields.forma_pago.$error"
                    label="Forma de pago"
                    :options="formaPagoOptions"
                  >
                    <template v-slot:prepend>
                      <q-icon name="description" />
                    </template>
                  </q-select>
                </div>
                <div class="col-xs-12 col-sm-3">
                  <q-select
                    color="dark"
                    bg-color="secondary"
                    filled
                    map-options
                    v-model="fiscal.fields.uso_cfdi"
                    :error="$v.fiscal.fields.uso_cfdi.$error"
                    label="Uso CFDI"
                    :options="usoCFDIOptions"
                  >
                    <template v-slot:prepend>
                      <q-icon name="description" />
                    </template>
                  </q-select>
                </div>
                <div class="col-xs-12 col-sm-3">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="fiscal.fields.email"
                    :error="$v.fiscal.fields.email.$error"
                    label="Dirección de correo electrónico"
                    @input="v => { fiscal.fields.email = v.toLowerCase() }"
                  >
                    <template v-slot:prepend>
                      <q-icon name="email" />
                    </template>
                  </q-input>
                </div>
                <div class="col-xs-12 col-sm-3">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="fiscal.fields.serie"
                    label="Serie"
                  >
                    <template v-slot:prepend>
                      <q-icon name="description" />
                    </template>
                  </q-input>
                </div>
                <div class="col-xs-12 pull-right">
                  <q-btn color="primary" icon="add" label="Agregar" style="height: 100%;margin-right: 5px;" @click="addFiscal()" v-show="fiscal.fields.id == null" />
                  <q-btn color="positive" icon="save" label="Guardar" style="height: 100%;margin-right: 5px;" @click="updateFiscal()" v-show="fiscal.fields.id != null" />
                  <q-btn color="negative" icon="cancel" label="Cancelar" style="height: 100%;margin-right: 5px;" @click="cancelEditFormulaFiscal()" v-show="fiscal.fields.id != null" />
                </div>
              </div>
              <div class="q-col-gutter-xs" style="padding: 2%;">
                <q-table
                  flat
                  bordered
                  :data="fiscales"
                  :columns="fiscalesColumns"
                  row-key="name"
                  :pagination.sync="pagination"
                >
                  <template v-slot:body="props">
                    <q-tr :props="props">
                      <q-td key="razon_social" :props="props">{{ props.row.razon_social }}</q-td>
                      <q-td key="rfc" :props="props">{{ props.row.rfc }}</q-td>
                      <q-td key="lugar_expedicion" :props="props">{{ props.row.lugar_expedicion }}</q-td>
                      <q-td key="metodo_pago" v-if="props.row.metodo_pago === 'PUE'" :props="props">PUE - Pago en una sola exhibición</q-td>
                      <q-td key="metodo_pago" v-else :props="props">PPD - Pago en parcialidades</q-td>
                      <q-td key="forma_pago" :props="props">{{ props.row.forma_pago_label }}</q-td>
                      <q-td key="uso_cfdi" :props="props" style="text-align: center;">{{ props.row.uso_cfdi_label }}</q-td>
                      <q-td key="actions" :props="props" style="width: 10%;">
                        <q-btn color="primary" icon="fas fa-edit" flat @click.native="editSelectedRowFiscal(props.row)" size="10px">
                          <q-tooltip content-class="bg-primary">Editar</q-tooltip>
                        </q-btn>
                        <q-btn color="negative" icon="fas fa-trash-alt" flat @click.native="deleteSelectedRowFiscal(props.row.id)" size="10px">
                          <q-tooltip content-class="bg-red">Eliminar</q-tooltip>
                        </q-btn>
                      </q-td>
                    </q-tr>
                  </template>
                </q-table>
              </div>
            </div>
          </q-tab-panel>
        </q-tab-panels>
      </div>
    </div>
  </q-page>
</template>

<script>
import api from '../../commons/api.js'
const { required, maxLength, email } = require('vuelidate/lib/validators')

export default {
  name: 'NewBranchOffice',
  validations: {
    branchOffice: {
      fields: {
        name: { required, maxLength: maxLength(50) },
        address: { required, maxLength: maxLength(100) },
        codigo_postal: { },
        estado_id: { },
        municipio_id: { },
        code: { required, maxLength: maxLength(20) },
        city: { },
        serie: { required },
        serie_pagos: { required },
        postal_code_id: { required },
        suburb_id: { required },
        indoor_number: { },
        outdoor_number: { required },
        between_street: { required }
      }
    },
    fiscal: {
      fields: {
        razon_social: { required },
        rfc: { required },
        lugar_expedicion: { required },
        metodo_pago: { required },
        forma_pago: { required },
        uso_cfdi: { required },
        email: { required, email }
        // serie: { required }
      }
    }
  },
  data () {
    return {
      branchOffice: {
        fields: {
          name: null,
          address: null,
          codigo_postal: null,
          estado_id: null,
          municipio_id: null,
          code: null,
          city: null,
          rfc_banco: null,
          cuenta: null,
          serie: null,
          serie_pagos: null,
          postal_code_id: null,
          suburb_id: null,
          indoor_number: null,
          outdoor_number: null,
          municipality: null,
          state: null,
          between_street: null,
          city_id: null,
          cluster_id: null,
          customer_id: null
        }
      },
      storages: [],
      storageColumns: [
        { name: 'code', align: 'left', label: 'Código', field: 'code', sortable: true },
        { name: 'name', align: 'left', label: 'Nombre', field: 'name', sortable: true }
      ],
      pagination: {
        rowsPerPage: 50
      },
      fiscal: {
        fields: {
          id: null,
          razon_social: '',
          rfc: '',
          lugar_expedicion: '',
          metodo_pago: 'PUE',
          forma_pago: '',
          uso_cfdi: '',
          email: null,
          serie: null
        }
      },
      currentTab: 'almacenes',
      storageOptions: [],
      selectEstados: [],
      selectMunicipio: [],
      selectMunicipios: [],
      options: this.selectMunicipio,
      options2: this.selectEstados,
      postal_codes: [],
      postal_codes_options: [],
      suburbs: [],
      suburbs_options: [],
      cities: [],
      cityOptions: [],
      fiscales: [],
      fiscalesColumns: [
        { name: 'razon_social', align: 'center', label: 'Razon social'.toUpperCase(), field: 'razon_social', sortable: true },
        { name: 'rfc', align: 'center', label: 'RFC'.toUpperCase(), field: 'rfc', sortable: true },
        { name: 'lugar_expedicion', align: 'center', label: 'Código Postal'.toUpperCase(), field: 'lugar_expedicion', sortable: true },
        { name: 'metodo_pago', align: 'center', label: 'Método de pago'.toUpperCase(), field: 'metodo_pago', sortable: true },
        { name: 'forma_pago', align: 'center', label: 'Forma de pago'.toUpperCase(), field: 'forma_pago', sortable: true },
        { name: 'uso_cfdi', align: 'center', label: 'Uso CFDI'.toUpperCase(), field: 'uso_cfdi', sortable: true },
        { name: 'actions', align: 'center', label: 'Acciones'.toUpperCase(), field: 'actions', style: 'width: 10%', sortable: false }
      ],
      metodoPagoOptions: [{ label: 'PUE - Pago en una sola exhibición', value: 'PUE' }, { label: 'PPD - Pago en parcialidades', value: 'PPD' }],
      formaPagoOptions: [],
      usoCFDIOptions: [],
      clusterOptions: [],
      customerOptions: []
    }
  },
  async created () {
    this.getEstados()
    this.fetchFromServer()
    this.getClustersList()
    this.getCustomerList()
  },
  computed: {
    nameRules (val) {
      return [
        val => (this.$v.branchOffice.fields.name.required) || 'El campo Nombre es requerido.',
        val => (this.$v.branchOffice.fields.name.maxLength) || 'El campo Nombre no debe exceder los 50 dígitos.'
      ]
    },
    cityRules (val) {
      return [
        val => (this.$v.branchOffice.fields.city.required) || 'La ciudad es requeridoa.'
      ]
    },
    codeRules (val) {
      return [
        val => (this.$v.branchOffice.fields.code.required) || 'El campo codigo es requerido.',
        val => (this.$v.branchOffice.fields.code.maxLength) || 'El campo codigo no debe exceder los 20 dígitos.'
      ]
    },
    addressRules (val) {
      return [
        val => (this.$v.branchOffice.fields.address.required) || 'El campo Dirección es requerido.',
        val => (this.$v.branchOffice.fields.address.maxLength) || 'El campo Dirección no debe exceder los 100 dígitos.'
      ]
    },
    cpRules (val) {
      return [
        val => (this.$v.branchOffice.fields.postal_code_id.required) || 'El campo código postal es requerido.'
      ]
    },
    serieRules (val) {
      return [
        val => (this.$v.branchOffice.fields.serie.required) || 'El campo serie facturacion es requerido.'
      ]
    },
    serie_pagosRules (val) {
      return [
        val => (this.$v.branchOffice.fields.serie_pagos.required) || 'El campo serie pagos facturacion es requerido.'
      ]
    },
    outdoor_numberRules (val) {
      return [
        val => (this.$v.branchOffice.fields.outdoor_number.required) || 'El campo Número exterior es requerido.'
      ]
    },
    suburbRules (val) {
      return [
        val => (this.$v.branchOffice.fields.suburb_id.required) || 'El campo Colonia es requerido.'
      ]
    },
    between_streetRules (val) {
      return [
        val => (this.$v.branchOffice.fields.between_street.required) || 'El campo Entre calles es requerido.'
      ]
    }
  },
  methods: {
    isNumber (evt, input) {
      switch (input) {
        case 'codigo_postal':
          this.branchOffice.fields.codigo_postal = this.branchOffice.fields.codigo_postal.replace(/[^0-9.]/g, '')
          this.$v.branchOffice.fields.codigo_postal.$touch()
          break
        default:
          break
      }
    },
    async fetchFromServer () {
      this.$q.loading.show()
      const id = this.$route.params.id
      api.get(`/branch-offices/${id}`).then(async ({ data }) => {
        if (!data.branchOffice) {
          this.$router.push('/branch-offices')
        } else {
          this.branchOffice.fields = data.branchOffice
          const suburbId = data.branchOffice.suburb_id
          this.branchOffice.fields.municipio_id = null
          await this.getPostalCodes(this.branchOffice.fields.postal_code_id)
          this.getSuburbsByPostalCode(this.branchOffice.fields.postal_code_id)
          this.branchOffice.fields.suburb_id = suburbId
          api.get(`/storages/branch-office/${id}`).then(({ data }) => {
            this.storages = data.storages
            this.$q.loading.hide()
          })
          api.get('/invoices/formaPagoOptions').then(({ data }) => {
            this.formaPagoOptions = data.options
            api.get('/invoices/usoCFDIOptions').then(({ data }) => {
              this.usoCFDIOptions = data.options
              api.get(`/branch-offices-tax-companies/branchOffice/${id}`).then(({ data }) => {
                this.fiscales = data.branchOfficeTaxCompanies
              })
            })
            this.$q.loading.hide()
          })
        }
      })
    },
    updateBranchOffice () {
      this.$v.branchOffice.fields.$reset()
      this.$v.branchOffice.fields.$touch()
      if (this.$v.branchOffice.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = { ...this.branchOffice.fields }
      api.put(`/branch-offices/${this.$route.params.id}`, params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.$router.push('/branch-offices')
        } else {
          this.$q.loading.hide()
        }
      })
    },
    addFiscal () {
      this.$v.fiscal.fields.$reset()
      this.$v.fiscal.fields.$touch()
      if (this.$v.fiscal.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = { ...this.fiscal.fields }
      params.metodo_pago = (typeof this.fiscal.fields.metodo_pago.value === 'undefined') ? this.fiscal.fields.metodo_pago : this.fiscal.fields.metodo_pago.value
      params.forma_pago = this.fiscal.fields.forma_pago
      params.uso_cfdi = this.fiscal.fields.uso_cfdi.value
      params.branch_office_id = this.$route.params.id
      api.post('/branch-offices-tax-companies', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.fiscal.fields.razon_social = null
          this.fiscal.fields.rfc = null
          this.fiscal.fields.lugar_expedicion = null
          this.fiscal.fields.metodo_pago = 'PUE'
          this.fiscal.fields.forma_pago = null
          this.fiscal.fields.uso_cfdi = null
          this.fiscal.fields.email = null
          this.fiscal.fields.serie = null
          this.$v.fiscal.fields.$reset()
          api.get(`/branch-offices-tax-companies/branchOffice/${this.$route.params.id}`).then(({ data }) => {
            this.fiscales = data.branchOfficeTaxCompanies
            this.$q.loading.hide()
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    updateFiscal () {
      this.$v.fiscal.fields.$reset()
      this.$v.fiscal.fields.$touch()
      if (this.$v.fiscal.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = { ...this.fiscal.fields }
      params.forma_pago = this.fiscal.fields.forma_pago
      params.uso_cfdi = (typeof this.fiscal.fields.uso_cfdi.value === 'undefined') ? this.fiscal.fields.uso_cfdi : this.fiscal.fields.uso_cfdi.value
      params.branch_office_id = this.$route.params.id
      api.put(`/branch-offices-tax-companies/${params.id}`, params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.fiscal.fields.id = null
          this.fiscal.fields.razon_social = null
          this.fiscal.fields.rfc = null
          this.fiscal.fields.lugar_expedicion = null
          this.fiscal.fields.metodo_pago = 'PUE'
          this.fiscal.fields.forma_pago = null
          this.fiscal.fields.uso_cfdi = null
          this.fiscal.fields.email = null
          this.fiscal.fields.serie = null
          this.$v.fiscal.fields.$reset()
          api.get(`/branch-offices-tax-companies/branchOffice/${this.$route.params.id}`).then(({ data }) => {
            this.fiscales = data.branchOfficeTaxCompanies
            this.$q.loading.hide()
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    getEstados () {
      this.selectEstados = []
      api.get('/branch-offices/states').then(({ data }) => {
        this.selectEstados = data.options
        // console.log(data)
      }).catch(error => error)
    },
    cancelEditFormulaFiscal () {
      this.fiscal.fields.id = null
      this.fiscal.fields.razon_social = null
      this.fiscal.fields.rfc = null
      this.fiscal.fields.lugar_expedicion = null
      this.fiscal.fields.metodo_pago = 'PUE'
      this.fiscal.fields.forma_pago = null
      this.fiscal.fields.uso_cfdi = null
      this.fiscal.fields.email = null
      this.fiscal.fields.serie = null
    },
    editSelectedRowFiscal (fiscal) {
      this.fiscal.fields.id = fiscal.id
      this.fiscal.fields.razon_social = fiscal.razon_social
      this.fiscal.fields.rfc = fiscal.rfc
      this.fiscal.fields.lugar_expedicion = fiscal.lugar_expedicion
      this.fiscal.fields.metodo_pago = fiscal.metodo_pago
      this.fiscal.fields.forma_pago = fiscal.forma_pago
      this.fiscal.fields.uso_cfdi = fiscal.uso_cfdi
      this.fiscal.fields.email = fiscal.email
      this.fiscal.fields.serie = fiscal.serie
    },
    deleteSelectedRowFiscal (fiscal) {
      this.$q.loading.show()
      api.delete(`/branch-offices-tax-companies/${fiscal}`).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          api.get(`/branch-offices-tax-companies/branchOffice/${this.$route.params.id}`).then(({ data }) => {
            this.fetchFromServer()
            this.$q.loading.hide()
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    async getPostalCodes (code = '') {
      code = code === '' ? '0' : code
      await api.get(`/branch-offices/postal_codes/${code}/${this.branchOffice.fields.municipio_id}`).then(({ data }) => {
        this.postal_codes = data.options
      }).catch(error => error)
    },
    async getMunicipiosbyEstado (id, postalReset = false) {
      this.branchOffice.fields.postal_code_id = postalReset ? null : this.branchOffice.fields.postal_code_id
      this.branchOffice.fields.municipio_id = null
      this.branchOffice.fields.city_id = null
      this.selectMunicipio = []
      this.getcities(id)
      await api.get(`/branch-offices/municipalities/${id}/${this.branchOffice.fields.postal_code_id}`).then(({ data }) => {
        this.selectMunicipio = data.options
      }).catch(error => error)
    },
    async getcities (id) {
      this.cities = []
      await api.get(`/branch-offices/cities/${id}/${this.branchOffice.fields.postal_code_id}`).then(({ data }) => {
        this.cities = data.options
      }).catch(error => error)
    },
    async getSuburbsByPostalCode (id) {
      console.log(id)
      this.suburbs = []
      this.branchOffice.fields.suburb_id = null
      this.branchOffice.fields.municipio_id = null
      this.branchOffice.fields.estado_id = null
      this.selectMunicipio = []
      if (id !== null) {
        this.branchOffice.fields.estado_id = this.postal_codes.filter(v => v.value === id)[0].state_id
        this.options2 = this.selectEstados.filter(v => v.value === this.branchOffice.fields.estado_id)
        await this.getMunicipiosbyEstado(this.branchOffice.fields.estado_id)
        this.branchOffice.fields.municipio_id = this.postal_codes.filter(v => v.value === id)[0].municipality_id
        this.options = this.selectMunicipio.filter(v => v.value === this.branchOffice.fields.municipio_id)
        await this.getcities(this.branchOffice.fields.estado_id)
        this.branchOffice.fields.city_id = this.postal_codes.filter(v => v.value === id)[0].city_id
        this.cityOptions = this.cities.filter(v => v.value === this.branchOffice.fields.city_id)

        api.get(`/branch-offices/suburbs/${id}`).then(({ data }) => {
          this.suburbs = data.options
          this.suburbs_options = data.options
        }).catch(error => error)
      }
    },
    filterMunicipio (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.options = this.selectMunicipio.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    filterEstado (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.options2 = this.selectEstados.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    filterPostalCodes (val, update, abort) {
      update(async () => {
        const needle = val.toLowerCase()
        await this.getPostalCodes(needle)
      })
    },
    filterSuburbs (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.suburbs_options = this.suburbs.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    filterCity (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.cityOptions = this.cities.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    getClustersList () {
      api.get('supercluster/getOptions').then(data => {
        this.clusterOptions = data.data.options
      })
    },
    getCustomerList () {
      api.get('customers/options').then(data => {
        this.customerOptions = data.data.options
      })
    }
  }
}
</script>

<style>
</style>

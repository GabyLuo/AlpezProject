<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-9">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Clientes" to="/customers" />
              <q-breadcrumbs-el label="Nuevo Cliente" />
            </q-breadcrumbs>
          </div>
        </div>
      </div>
    </div>

    <div class="q-pa-md bg-grey-3">
            <div class="row bg-white border-panel">
        <div class="col q-pa-md">
          <div class="row" style="padding-bottom: 15px;">
            Información General
          </div>
          <div class="row q-col-gutter-xs">
            <div class="col-xs-12 col-sm-2">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                disable
                v-model="customer.fields.serial"
                label="Código"
                :rules="serialRules"
                @input="v => { customer.fields.serial = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="code" />
                </template>
              </q-input>
            </div>
              <div class="col-xs-12 col-sm-2 text-center">
                <q-select color="dark"
                bg-color="secondary"
                filled
                v-model="customer.fields.admission_date"
                :error="$v.customer.fields.admission_date.$error"
                mask="date"
                label="Alta"
                :rules="admissionRules"
                @input="v => { customer.fields.admission_date = v.toUpperCase() }"
                >
                <template v-slot:prepend>
                    <q-icon name="event"></q-icon>
                </template>
                <q-popup-proxy ref="date" transition-show="scale" transition-hide="scale">
                    <div class="col-sm-12">
                        <q-date
                        color="secondary"
                        text-color="white"
                        mask="DD/MM/YYYY"
                        @input="() => this.$refs.date.hide()"
                        v-model="customer.fields.admission_date"
                        today-btn>
                        </q-date>
                    </div>
                </q-popup-proxy>
                </q-select>
              </div>
            <div class="col-xs-12 col-sm-6">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="customer.fields.name"
                :error="$v.customer.fields.name.$error"
                label="Razón social"
                :rules="nameRules"
                @blur="labelName()"
                @input="v => { customer.fields.name = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-signature" />
                </template>
              </q-input>
            </div>
            <!-- <div class="col-xs-12 col-sm-5"> -->
            <div class="col-xs-12 col-sm-2 text-center">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="customer.fields.active"
                :options="[
                  {label: 'ACTIVO', value: true},
                  {label: 'INACTIVO', value: false}
                ]"
                label="Estatus"
              >
                <template v-slot:prepend>
                  <q-icon :name="(customer.fields.active.value ? 'battery_full' : 'battery_alert')" />
                </template>
              </q-select>
            </div>
            <!-- <div class="col-xs-12 col-sm-6">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="customer.fields.contact_name"
                :error="$v.customer.fields.contact_name.$error"
                label="Nombre contacto"
                :rules="contactNameRules"
                @input="v => { customer.fields.contact_name = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-id-card" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-2">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="customer.fields.rfc"
                :error="$v.customer.fields.rfc.$error"
                label="RFC"
                :rules="rfcRules"
                @input="v => { customer.fields.rfc = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="insert_drive_file" />
                </template>
              </q-input>
            </div> -->
            <div class="col-xs-12 col-sm-2">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="customer.fields.contact_phone"
                :error="$v.customer.fields.contact_phone.$error"
                label="Teléfono"
                :rules="contactPhoneRules"
                @input="v => { customer.fields.contact_phone = v.replace(/[^0-9]/g, '').substr(0, 20) }"
              >
                <template v-slot:prepend>
                  <q-icon name="contact_phone" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-2">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="customer.fields.contact_phone_res"
                :error="$v.customer.fields.contact_phone_res.$error"
                label="Teléfono 2"
                :rules="contactPhoneRulesRes"
                @input="v => { customer.fields.contact_phone_res = v.replace(/[^0-9]/g, '').substr(0, 20) }"
              >
                <template v-slot:prepend>
                  <q-icon name="contact_phone" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-4">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="customer.fields.email"
                :error="$v.customer.fields.email.$error"
                label="Dirección de correo electrónico"
                :rules="emailRules"
                @input="v => { customer.fields.email = v.toLowerCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="email" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-4">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="customer.fields.email2"
                :error="$v.customer.fields.email2.$error"
                label="Dirección de correo electrónico 2"
                :rules="email2Rules"
                @input="v => { customer.fields.email2 = v.toLowerCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="email" />
                </template>
              </q-input>
            </div>
            </div>
        </div>
      </div>
      <br>
       <div class="row bg-white border-panel">
        <div class="col q-pa-md">
          <div class="row" style="padding-bottom: 15px;">
            Información Comercial
          </div>
          <div class="row q-col-gutter-xs">
            <div class="col-xs-12 col-sm-6">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="customer.fields.tradename"
                label="Nombre comercial"
                @input="v => { customer.fields.tradename = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="business" />
                </template>
              </q-input>
            </div>
             <div class="col-xs-12 col-sm-3">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="customer.fields.payment_method"
                :error="$v.customer.fields.payment_method.$error"
                label="Forma de pago"
                map-options
                :options="[
                {label: 'CONTADO', value: 'CONTADO'},
                {label: 'CREDITO', value: 'CREDITO'}
                ]"
                :rules="paymentMethodRules"
                @input="methodSelected()"
                emit-value
              >
                <template v-slot:prepend>
                  <q-icon name="payment" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-3" v-if="enableFields">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="customer.fields.credit_days"
                :error="$v.customer.fields.credit_days.$error"
                label="Días de Crédito"
                :rules="creditDayRules"
                mask="#"
                reverse-fill-mask
              >
                <template v-slot:prepend>
                  <q-icon name="far fa-clock" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-3" v-if="enableFields">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="customer.fields.credit_limit"
                :error="$v.customer.fields.credit_limit.$error"
                label="Límite de Crédito"
                :rules="creditLimitRules"
              >
                <template v-slot:prepend>
                  <q-icon name="attach_money" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="customer.fields.currency"
                :error="$v.customer.fields.currency.$error"
                label="Moneda"
                :rules="currencyRules"
                :options="currencyOptions"
                emit-value
                map-options
              >
                <template v-slot:prepend>
                  <q-icon name="attach_money" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-2 text-center">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="customer.fields.priceList"
                :error="$v.customer.fields.priceList.$error"
                :rules="priceListRules"
                :options="[
                  {label: 'A', value: 'A'},
                  {label: 'B', value: 'B'},
                  {label: 'C', value: 'C'},
                  {label: 'D', value: 'D'},
                  {label: 'E', value: 'E'}
                ]"
                label="Precio de lista"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-tag" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-2 text-center">
              <q-input
              color="dark"
              bg-color="secondary"
                label="Descuento Autorizado"
                :rules="discountRules"
                v-model="customer.fields.discount"
                :error="$v.customer.fields.discount.$error"
                :disable="(!$store.getters['users/roles'].includes(1)) && (!$store.getters['users/roles'].includes(25))"
                filled
              >
              <template v-slot:prepend>
                  <q-icon name="fas fa-percent" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-2 text-center">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="customer.fields.seller"
                :error="$v.customer.fields.seller.$error"
                :rules="sellerRules"
                :options="sellerOptions"
                label="Vendedor"
                emit-value
                map-options
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-id-badge" />
                </template>
              </q-select>
            </div>
             <!-- <div class="col-xs-12 col-sm-3">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="customer.fields.term"
                :error="$v.customer.fields.term.$error"
                label="Plazo"
                @input="termSelected()"
                :rules="termRules"
                emit-value
                map-options
                :options="[
                {label: 'CONTADO', value: 'CONTADO'},
                {label: 'CREDITO', value: 'CREDITO'}
                ]"
              >
                <template v-slot:prepend>
                  <q-icon name="far fa-clock" />
                </template>
              </q-select>
            </div> -->
            <div class="col-xs-12 col-sm-3 text-center">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="customer.fields.channel_id"
                :options="channelOptions"
                label="Canal"
                emit-value
                map-options
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-id-badge" />
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
            Domicilio Corporativo
          </div>
          <div class="row q-col-gutter-xs">
          <div class="col-xs-12 col-sm-4">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="customer.fields.street"
                :error="$v.customer.fields.street.$error"
                label="Calle"
                :rules="streetRules"
                @input="v => { customer.fields.street = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-road" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-2">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="customer.fields.outdoor_number"
                :error="$v.customer.fields.outdoor_number.$error"
                label="Número ext."
                :rules="outdoorNumberRules"
                @input="v => { customer.fields.outdoor_number = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-hashtag" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-2">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="customer.fields.indoor_number"
                :error="$v.customer.fields.indoor_number.$error"
                label="Número int."
                :rules="indoorNumberRules"
                @input="v => { customer.fields.indoor_number = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-hashtag" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-4">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="customer.fields.city"
                :error="$v.customer.fields.city.$error"
                label="Ciudad"
                :rules="cityRules"
                @input="v => { customer.fields.city = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="map" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-4">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="customer.fields.municipality"
                :error="$v.customer.fields.municipality.$error"
                label="Municipio"
                :rules="municipalityRules"
                @input="v => { customer.fields.municipality = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-city" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="customer.fields.suburb"
                :error="$v.customer.fields.suburb.$error"
                label="Colonia"
                :rules="suburbRules"
                @input="v => { customer.fields.suburb = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-city" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-2">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="customer.fields.zip_code"
                :error="$v.customer.fields.zip_code.$error"
                label="CP"
                :rules="zipCodeRules"
                @input="v => { customer.fields.zip_code = v.replace(/[^0-9]/g, '').substr(0, 5) }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-mail-bulk" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="customer.fields.state"
                :error="$v.customer.fields.state.$error"
                label="Estado"
                :rules="stateRules"
                @input="v => { customer.fields.state = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="map" />
              </template>
            </q-input>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="customer.fields.country"
                :error="$v.customer.fields.country.$error"
                label="País"
                @input="v => { customer.fields.country = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="map" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-2 offset-sm-10 pull-right">
              <q-btn color="positive" icon="save" label="Guardar" @click="createCustomer()" />
            </div>
          </div>
        </div>
      </div>
            <br>
        <!-- <div class="row bg-white border-panel">
        <div class="col q-pa-md">
            <div class="row" style="padding-bottom: 15px;">
            Requerimientos
          </div>
          <div class="row q-col-gutter-xs">
            <div class="col-xs-12 col-sm-6">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                type="textarea"
                v-model="customer.fields.requirements"
                label="Requerimientos especiales"
                @input="v => { customer.fields.requirements = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-user-tag" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-6">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                type="textarea"
                v-model="customer.fields.documents"
                label="Documentos requeridos"
                @input="v => { customer.fields.documents = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-file-alt" />
                </template>
              </q-input>
            </div>
          </div>
        </div>
      </div> -->
    </div>
  </q-page>
</template>

<script>
import api from '../../commons/api.js'
const { required, requiredIf, maxLength, minLength, integer, minValue, email, decimal } = require('vuelidate/lib/validators')

export default {
  name: 'NewCustomer',
  validations: {
    customer: {
      fields: {
        serial: { required, maxLength: maxLength(10) },
        admission_date: { required },
        name: { required, maxLength: maxLength(100) },
        // tradename: { required, maxLength: maxLength(100) },
        contact_name: { maxLength: maxLength(100) },
        contact_phone: { minLength: minLength(10), maxLength: maxLength(20), integer, minValue: minValue(1) },
        discount: { decimal, minValue: minValue(1) },
        contact_phone_res: { minLength: minLength(10), maxLength: maxLength(20), integer, minValue: minValue(1) },
        street: { maxLength: maxLength(100) },
        outdoor_number: { maxLength: maxLength(10) },
        indoor_number: { maxLength: maxLength(10) },
        suburb: { maxLength: maxLength(100) },
        municipality: { maxLength: maxLength(100) },
        country: { maxLength: maxLength(100) },
        state: { maxLength: maxLength(100) },
        city: { maxLength: maxLength(100) },
        zip_code: { maxLength: maxLength(5), integer, minValue: minValue(1) },
        rfc: { maxLength: maxLength(20) },
        term: { maxLength: maxLength(100) },
        payment_method: { required, maxLength: maxLength(100) },
        currency: { required },
        seller: { required },
        priceList: { required },
        email: { required, email },
        email2: { email },
        email3: { email },
        email4: { email },
        credit_days: { required: requiredIf(function () { return this.enableFields }) },
        credit_limit: { required: requiredIf(function () { return this.enableFields }), decimal }
      }
    }
  },
  data () {
    return {
      channelOptions: [],
      customer: {
        fields: {
          serial: null,
          name: null,
          seller: null,
          tradename: null,
          contact_name: null,
          contact_phone: null,
          contact_phone_res: null,
          street: null,
          outdoor_number: null,
          indoor_number: null,
          suburb: null,
          municipality: null,
          state: null,
          country: null,
          city: null,
          zip_code: null,
          rfc: null,
          term: null,
          payment_method: null,
          currency: null,
          active: { label: 'ACTIVO', value: true },
          priceList: null,
          email: null,
          email2: null,
          email3: null,
          email4: null,
          admission_date: null,
          branch_id: 9,
          credit_days: null,
          credit_limit: null,
          channel_id: null,
          documents: null,
          discount: null,
          requirements: null,
          postal_code_id: null,
          municipio_id: null,
          suburb_id: null
        }
      },
      branchesList: [],
      enableFields: false,
      sellerOptions: [],
      currencyOptions: [],
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
      cityOptions: []
    }
  },
  mounted () {
    this.getSellers()
    this.getCurrencies()
    this.getChannels()
  },
  created () {
    api.get('/branch-offices/options').then(data => {
      this.branchesList = data.data.options
      api.get('customers/getLastCode').then(data => {
        this.customer.fields.serial = data.data.data.nextserial
      })
    })
  },
  computed: {
    roleId () {
      const user = this.$store.getters['users/rol']
      return parseInt(user)
    },
    serialRules (val) {
      return [
        val => (this.$v.customer.fields.serial.required) || 'El campo Código es requerido.',
        val => (this.$v.customer.fields.serial.maxLength) || 'El campo Código no debe exceder los 10 dígitos.'
      ]
    },
    sellerRules (val) {
      return [
        val => this.$v.customer.fields.seller.required || 'El campo Vendedor es requerido.'
      ]
    },
    discountRules (val) {
      return [
        val => this.$v.customer.fields.discount.decimal || 'El campo Descuento debe ser númerico.',
        val => this.$v.customer.fields.discount.minValue || 'El campo Descuento debe ser positivo.'
      ]
    },
    admissionRules (val) {
      return [
        val => (this.$v.customer.fields.admission_date.required) || 'El campo Fecha de alta es requerido.'
      ]
    },
    nameRules (val) {
      return [
        val => this.$v.customer.fields.name.required || 'El campo Nombre es requerido.',
        val => this.$v.customer.fields.name.maxLength || 'El campo Nombre no debe exceder los 100 dígitos.'
      ]
    },
    /* tradenameRules (val) {
      return [
        val => this.$v.customer.fields.tradename.required || 'El campo Nombre comercial es requerido.',
        val => this.$v.customer.fields.tradename.maxLength || 'El campo Nombre comercial no debe exceder los 100 dígitos.'
      ]
    }, */
    contactNameRules (val) {
      return [
        val => this.$v.customer.fields.contact_name.maxLength || 'El campo Nombre Contacto no debe exceder los 100 dígitos.'
      ]
    },
    creditDayRules (val) {
      return [
        val => this.$v.customer.fields.credit_days.required || 'El campo Días de Crédito es requerido.'
      ]
    },
    creditLimitRules (val) {
      return [
        val => this.$v.customer.fields.credit_limit.required || 'El campo Límite de Crédito es requerido.',
        val => this.$v.customer.fields.credit_limit.decimal || 'El campo Límite de Crédito debe ser númerico.'
      ]
    },
    contactPhoneRules (val) {
      return [
        val => this.$v.customer.fields.contact_phone.integer || 'El campo Teléfono debe ser numérico.',
        val => this.$v.customer.fields.contact_phone.minValue || 'El campo Teléfono debe ser positivo.',
        val => this.$v.customer.fields.contact_phone.minLength || 'El campo Teléfono debe tener al menos 10 dígitos.',
        val => this.$v.customer.fields.contact_phone.maxLength || 'El campo Teléfono no debe exceder los 20 dígitos.'
      ]
    },
    contactPhoneRulesRes (val) {
      return [
        val => this.$v.customer.fields.contact_phone_res.integer || 'El campo Teléfono debe ser numérico.',
        val => this.$v.customer.fields.contact_phone_res.minValue || 'El campo Teléfono debe ser positivo.',
        val => this.$v.customer.fields.contact_phone_res.minLength || 'El campo Teléfono debe tener al menos 10 dígitos.',
        val => this.$v.customer.fields.contact_phone_res.maxLength || 'El campo Teléfono no debe exceder los 20 dígitos.'
      ]
    },
    streetRules (val) {
      return [
        val => this.$v.customer.fields.street.maxLength || 'El campo Calle no debe exceder los 100 dígitos.'
      ]
    },
    outdoorNumberRules (val) {
      return [
        val => this.$v.customer.fields.outdoor_number.maxLength || 'El campo Número ext. no debe exceder los 10 dígitos.'
      ]
    },
    indoorNumberRules (val) {
      return [
        val => this.$v.customer.fields.indoor_number.maxLength || 'El campo Número int. no debe exceder los 10 dígitos.'
      ]
    },
    suburbRules (val) {
      return [
        val => this.$v.customer.fields.suburb.maxLength || 'El campo Colonia no debe exceder los 100 dígitos.'
      ]
    },
    municipalityRules (val) {
      return [
        val => this.$v.customer.fields.municipality.maxLength || 'El campo Municipio no debe exceder los 100 dígitos.'
      ]
    },
    stateRules (val) {
      return [
        val => this.$v.customer.fields.state.maxLength || 'El campo Estado no debe exceder los 100 dígitos.'
      ]
    },
    cityRules (val) {
      return [
        val => this.$v.customer.fields.city.maxLength || 'El campo Ciudad no debe exceder los 100 dígitos.'
      ]
    },
    zipCodeRules (val) {
      return [
        val => this.$v.customer.fields.zip_code.integer || 'El campo CP debe ser numérico.',
        val => this.$v.customer.fields.zip_code.minValue || 'El campo CP debe ser positivo.',
        val => this.$v.customer.fields.zip_code.maxLength || 'El campo CP no debe exceder los 5 dígitos.'
      ]
    },
    rfcRules (val) {
      return [
        val => this.$v.customer.fields.rfc.maxLength || 'El campo RFC no debe exceder los 20 dígitos.'
      ]
    },
    termRules (val) {
      return [
        val => this.$v.customer.fields.term.required || 'El campo Plazo es requerido.',
        val => this.$v.customer.fields.term.maxLength || 'El campo Plazo no debe exceder los 20 dígitos.'
      ]
    },
    paymentMethodRules (val) {
      return [
        val => this.$v.customer.fields.payment_method.required || 'El campo Forma de pago es requerido.',
        val => this.$v.customer.fields.payment_method.maxLength || 'El campo Forma de pago no debe exceder los 100 dígitos.'
      ]
    },
    priceListRules (val) {
      return [
        val => this.$v.customer.fields.priceList.required || 'El campo Precio de lista es requerido.'
      ]
    },
    currencyRules (val) {
      return [
        val => this.$v.customer.fields.currency.required || 'El campo Moneda es requerido.'
      ]
    },
    emailRules (val) {
      return [
        val => this.$v.customer.fields.email.required || 'El campo Dirección de correo electrónico es requerido.',
        val => this.$v.customer.fields.email.email || 'El campo Dirección de correo electrónico debe contener una dirección de correo electrónico válida.'
      ]
    },
    email2Rules (val) {
      return [
        val => this.$v.customer.fields.email2.email || 'El campo Dirección de correo electrónico 2 debe contener una dirección de correo electrónico válida.'
      ]
    },
    email3Rules (val) {
      return [
        val => this.$v.customer.fields.email3.email || 'El campo Dirección de correo electrónico 3 debe contener una dirección de correo electrónico válida.'
      ]
    },
    email4Rules (val) {
      return [
        val => this.$v.customer.fields.email4.email || 'El campo Dirección de correo electrónico 4 debe contener una dirección de correo electrónico válida.'
      ]
    },
    customerZipCodeRules (val) {
      return [
        val => this.$v.customer.fields.postal_code_id.required || 'El campo Codigo postal es requerido.'
      ]
    },
    customersuburbRules (val) {
      return [
        val => (this.$v.customer.fields.suburb_id.required) || 'El campo Colonia es requerido.'
      ]
    },
    customerbetween_streetRules (val) {
      return [
        val => (this.$v.customer.fields.between_street.required) || 'El campo Entre calles es requerido.'
      ]
    }
  },
  /* beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(4) && !this.$store.getters['users/roles'].includes(20)) {
      this.$router.push('/')
    }
  }, */
  beforeRouteEnter (to, from, next) {
    next(vm => {
      const propiedades = vm.$store.getters['users/rol']
      if (propiedades === 1 || propiedades === 3 || propiedades === 7 || propiedades === 2 || propiedades === 20 || propiedades === 4 || propiedades === 27 || propiedades === 17 || propiedades === 22 || propiedades === 28 || propiedades === 29) {
        next()
      } else {
        next('/')
      }
    })
  },
  methods: {
    methodSelected () {
      if (this.customer.fields.payment_method === 'CREDITO') {
        this.enableFields = true
      } else {
        this.enableFields = false
        this.customer.fields.credit_days = null
        this.customer.fields.credit_limit = null
      }
    },
    getSellers () {
      api.get('/users/getSeller').then(({ data }) => {
        this.sellerOptions = data.options
        this.$q.loading.hide()
      })
    },
    getCurrencies () {
      api.get('/currencies/options').then(({ data }) => {
        this.currencyOptions = data.options
        this.customer.fields.currency = 4
        this.$q.loading.hide()
      })
    },
    getChannels () {
      api.get('/channels/options').then(({ data }) => {
        this.channelOptions = data.options
        this.$q.loading.hide()
      })
    },
    createCustomer () {
      this.$v.customer.fields.$reset()
      this.$v.customer.fields.$touch()
      if (this.$v.customer.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = { ...this.customer.fields }
      params.admission_date = this.customer.fields.admission_date.substr(6, 4) + '-' + this.customer.fields.admission_date.substr(3, 2) + '-' + this.customer.fields.admission_date.substr(0, 2)
      params.active = params.active.value
      params.price_list = params.priceList.value
      params.seller_id = this.customer.fields.seller
      api.post('/customers', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          // this.$router.push('/customers')
          this.$router.push(`/customers/${data.id}`)
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
    async getPostalCodes (code = '') {
      code = code === '' ? '0' : code
      await api.get(`/branch-offices/postal_codes/${code}/${this.customer.fields.municipio_id}`).then(({ data }) => {
        this.postal_codes = data.options
      }).catch(error => error)
    },
    async labelName () {
      if (this.customer.fields.tradename === null) {
        this.customer.fields.tradename = this.customer.fields.name
      }
    },
    async getMunicipiosbyEstado (id, postalReset = false) {
      this.customer.fields.postal_code_id = postalReset ? null : this.customer.fields.postal_code_id
      this.customer.fields.suburb_id = postalReset ? null : this.customer.fields.suburb_id
      this.customer.fields.municipio_id = null
      this.customer.fields.city_id = null
      this.selectMunicipio = []
      this.getcities(id)
      await api.get(`/branch-offices/municipalities/${id}/${this.customer.fields.postal_code_id}`).then(({ data }) => {
        this.selectMunicipio = data.options
      }).catch(error => error)
    },
    async getcities (id) {
      this.cities = []
      await api.get(`/branch-offices/cities/${id}/${this.customer.fields.postal_code_id}`).then(({ data }) => {
        this.cities = data.options
      }).catch(error => error)
    },
    async getSuburbsByPostalCode (id) {
      this.suburbs = []
      this.customer.fields.suburb_id = null
      this.customer.fields.municipio_id = null
      this.customer.fields.estado_id = null
      this.selectMunicipio = []
      if (id !== null) {
        this.customer.fields.estado_id = this.postal_codes.filter(v => v.value === id)[0].state_id
        this.options2 = this.selectEstados.filter(v => v.value === this.customer.fields.estado_id)
        await this.getMunicipiosbyEstado(this.customer.fields.estado_id)
        this.customer.fields.municipio_id = this.postal_codes.filter(v => v.value === id)[0].municipality_id
        this.options = this.selectMunicipio.filter(v => v.value === this.customer.fields.municipio_id)
        await this.getcities(this.customer.fields.estado_id)
        this.customer.fields.city_id = this.postal_codes.filter(v => v.value === id)[0].city_id
        this.cityOptions = this.cities.filter(v => v.value === this.customer.fields.city_id)
        api.get(`/branch-offices/suburbs/${id}`).then(({ data }) => {
          this.suburbs = data.options
          this.suburbs.push({ value: 0, label: 'OTRA' })
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
    }
  }/* ,
  watch: {
    'customer.fields.tradename': function (newValue) {
      if (newValue.value < 0) {
        console.log(this.customer.fields.tradename)
        if (newValue.value === null) {
          // customer.fields.tradename = customer.fields.name
        }
      }
    }
  } */
}
</script>

<style>
</style>

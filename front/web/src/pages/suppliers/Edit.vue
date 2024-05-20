<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-9">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Proveedores" to="/suppliers" />
              <q-breadcrumbs-el label="Editar" v-text="supplier.fields.name" />
            </q-breadcrumbs>
          </div>
        </div>
      </div>
    </div>

    <div class="q-pa-md bg-grey-3">
      <div class="row bg-white border-panel">
        <div class="col q-pa-md">
          <div class="row q-col-gutter-xs">
            <div class="col-xs-12 col-sm-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="supplier.fields.serial"
                :error="$v.supplier.fields.serial.$error"
                label="Código"
                :rules="serialRules"
                @input="v => { supplier.fields.serial = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="code" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-3 text-center">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="supplier.fields.active"
                :options="[
                  {label: 'ACTIVO', value: true},
                  {label: 'INACTIVO', value: false}
                ]"
                label="Estatus"
              >
                <template v-slot:prepend>
                  <q-icon :name="(supplier.fields.active.value ? 'battery_full' : 'battery_alert')" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="supplier.fields.name"
                :error="$v.supplier.fields.name.$error"
                label="Razón social"
                :rules="nameRules"
                @input="v => { supplier.fields.name = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-signature" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="supplier.fields.tradename"
                :error="$v.supplier.fields.tradename.$error"
                label="Nombre comercial"
                :rules="tradenameRules"
                @input="v => { supplier.fields.tradename = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="business" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="supplier.fields.contact_name"
                :error="$v.supplier.fields.contact_name.$error"
                label="Nombre contacto"
                :rules="contactNameRules"
                @input="v => { supplier.fields.contact_name = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-id-card" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="supplier.fields.contact_phone"
                :error="$v.supplier.fields.contact_phone.$error"
                label="Oficina"
                :rules="contactPhoneRules"
                @input="v => { supplier.fields.contact_phone = v.replace(/[^0-9]/g, '').substr(0, 20) }"
              >
                <template v-slot:prepend>
                  <q-icon name="contact_phone" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="supplier.fields.contact_phone_two"
                :error="$v.supplier.fields.contact_phone_two.$error"
                label="Celular"
                :rules="contactPhoneRulesTwo"
                @input="v => { supplier.fields.contact_phone_two = v.replace(/[^0-9]/g, '').substr(0, 20) }"
              >
                <template v-slot:prepend>
                  <q-icon name="contact_phone" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="supplier.fields.street"
                :error="$v.supplier.fields.street.$error"
                label="Calle"
                :rules="streetRules"
                @input="v => { supplier.fields.street = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-road" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="supplier.fields.outdoor_number"
                :error="$v.supplier.fields.outdoor_number.$error"
                label="Número ext."
                :rules="outdoorNumberRules"
                @input="v => { supplier.fields.outdoor_number = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-hashtag" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="supplier.fields.indoor_number"
                :error="$v.supplier.fields.indoor_number.$error"
                label="Número int."
                :rules="indoorNumberRules"
                @input="v => { supplier.fields.indoor_number = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-hashtag" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="supplier.fields.suburb"
                :error="$v.supplier.fields.suburb.$error"
                label="Colonia"
                :rules="suburbRules"
                @input="v => { supplier.fields.suburb = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-city" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="supplier.fields.municipality"
                :error="$v.supplier.fields.municipality.$error"
                label="Municipio"
                :rules="municipalityRules"
                @input="v => { supplier.fields.municipality = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-city" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="supplier.fields.state"
                :error="$v.supplier.fields.state.$error"
                label="Estado"
                :rules="stateRules"
                @input="v => { supplier.fields.state = v.toUpperCase() }"
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
                v-model="supplier.fields.country"
                :error="$v.supplier.fields.country.$error"
                label="Pais"
                :rules="countryRules"
                @input="v => { supplier.fields.country = v.toUpperCase() }"
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
                v-model="supplier.fields.zip_code"
                :error="$v.supplier.fields.zip_code.$error"
                label="CP"
                :rules="zipCodeRules"
                @input="v => { supplier.fields.zip_code = v.replace(/[^0-9]/g, '').substr(0, 5) }"
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
                v-model="supplier.fields.rfc"
                :error="$v.supplier.fields.rfc.$error"
                label="RFC"
                :rules="rfcRules"
                @input="v => { supplier.fields.rfc = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="insert_drive_file" />
                </template>
              </q-input>
            </div>
            <!-- <div class="col-xs-12 col-sm-2">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="supplier.fields.term"
                :error="$v.supplier.fields.term.$error"
                label="Plazo"
                :rules="termRules"
                @input="v => { supplier.fields.term = v.toUpperCase() }"
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
                v-model="supplier.fields.credit_days"
                label="Días de crédito"
                @input="v => { supplier.fields.credit_days = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="insert_drive_file" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-2">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="supplier.fields.currency"
                :error="$v.supplier.fields.currency.$error"
                label="Moneda"
                :rules="currencyRules"
                @input="v => { supplier.fields.currency = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="attach_money" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-4">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="supplier.fields.payment_method"
                :error="$v.supplier.fields.payment_method.$error"
                label="Forma de pago"
                :rules="paymentMethodRules"
                @input="v => { supplier.fields.payment_method = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="payment" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-4">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="supplier.fields.email"
                :error="$v.supplier.fields.email.$error"
                label="Dirección de correo electrónico"
                :rules="emailRules"
                @input="v => { supplier.fields.email = v.toLowerCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="email" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-4">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="supplier.fields.email2"
                :error="$v.supplier.fields.email2.$error"
                label="Dirección de correo electrónico 2"
                :rules="emailRules"
                @input="v => { supplier.fields.email2 = v.toLowerCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="email" />
                </template>
              </q-input>
            </div>
          </div>

          <div class="row q-mb-sm q-mt-md" v-if="roleId !== 20">
            <div class="col-xs-12 col-sm-2 offset-sm-10 pull-right">
              <q-btn color="positive" icon="save" label="Actualizar" @click="editSupplier()" />
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
          <q-tab name="contacts" label="Contactos" />
        <!--   <q-tab name="dossiers" label="Expedientes" /> -->
        </q-tabs>
        <q-tab-panels v-model="currentTab" animated>
          <q-tab-panel name="contacts">
            <div style="font-weight: normal">
              <div class="row q-col-gutter-xs q-pa-xs">
                <div class="col-xs-12 col-sm-3">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="contacts.fields.name"
                    :error="$v.contacts.fields.name.$error"
                    label="Nombre"
                    :rules="contactsNameRules"
                    @input="v => { contacts.fields.name = v.toUpperCase() }"
                  >
                    <template v-slot:prepend>
                      <q-icon name="fas fa-signature" />
                    </template>
                  </q-input>
                </div>
                <div class="col-xs-12 col-sm-3">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="contacts.fields.tel"
                    :error="$v.contacts.fields.tel.$error"
                    label="Teléfono"
                    :rules="contactsTelRules"
                  >
                    <template v-slot:prepend>
                      <q-icon name="fas fa-signature" />
                    </template>
                  </q-input>
                </div>
                <div class="col-xs-12 col-sm-3">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="contacts.fields.phone"
                    :error="$v.contacts.fields.phone.$error"
                    label="Celular"
                    :rules="contactsPhoneRules"
                  >
                    <template v-slot:prepend>
                      <q-icon name="fas fa-signature" />
                    </template>
                  </q-input>
                </div>
                <div class="col-xs-12 col-sm-3">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="contacts.fields.email"
                    :error="$v.contacts.fields.email.$error"
                    label="Correo electrónico"
                    :rules="contactsEmailRules"
                  >
                    <template v-slot:prepend>
                      <q-icon name="fas fa-signature" />
                    </template>
                  </q-input>
                </div>
                <div class="col-xs-12 pull-right" v-if="roleId !== 20">
                  <q-btn color="primary" icon="add" label="Agregar" style="height: 100%;margin-right: 5px;" @click="addContact()" v-show="contacts.fields.id == null" />
                  <q-btn color="positive" icon="save" label="Guardar" style="height: 100%;margin-right: 5px;" @click="updateContact()" v-show="contacts.fields.id != null" />
                  <q-btn color="negative" icon="cancel" label="Cancelar" style="height: 100%;margin-right: 5px;" @click="cancelEditContact()" v-show="contacts.fields.id != null" />
                </div>
              </div>
              <div class="q-col-gutter-xs" style="padding: 2%;">
                <q-table
                  flat
                  bordered
                  :data="contactsList"
                  :columns="contactsColumns"
                  row-key="name"
                  :pagination.sync="pagination"
                >
                  <template v-slot:body="props">
                    <q-tr :props="props">
                      <q-td key="name" :props="props" style="text-align: left; width: 20%;">{{ props.row.name }}</q-td>
                      <q-td key="tel" :props="props" style="text-align: left; width: 20%;">{{ props.row.tel }}</q-td>
                      <q-td key="phone" :props="props" style="text-align: left; width: 10%;">{{ props.row.phone }}</q-td>
                      <q-td key="email" :props="props" style="text-align: left; width: 10%;">{{ props.row.email }}</q-td>
                      <q-td key="actions" :props="props" style="width: 10%;" v-if="roleId !== 20">
                        <q-btn color="primary" icon="fas fa-edit" flat @click.native="editSelectedRowContacts(props.row)" size="10px">
                          <q-tooltip content-class="bg-primary">Editar</q-tooltip>
                        </q-btn>
                        <q-btn color="negative" icon="fas fa-trash-alt" flat @click.native="deleteSelectedRowContact(props.row.id)" size="10px">
                          <q-tooltip content-class="bg-red">Eliminar</q-tooltip>
                        </q-btn>
                      </q-td>
                    </q-tr>
                  </template>
                </q-table>
              </div>
            </div>
          </q-tab-panel>
          <q-tab-panel name="dossiers">
            <div style="font-weight: normal">
              <div class="row q-col-gutter-xs q-pa-xs">
                <div class="col-xs-12 col-sm-6">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="dossiers.fields.name"
                    :error="$v.dossiers.fields.name.$error"
                    label="Nombre"
                    :rules="dossiersNameRules"
                    @input="v => { dossiers.fields.name = v.toUpperCase() }"
                  >
                    <template v-slot:prepend>
                      <q-icon name="fas fa-signature" />
                    </template>
                  </q-input>
                </div>
                <div class="col-xs-12 col-sm-6">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="dossiers.fields.description"
                    label="Descripción del documento"
                    @input="v => { dossiers.fields.description = v.toUpperCase() }"
                  >
                    <template v-slot:prepend>
                      <q-icon name="fas fa-signature" />
                    </template>
                  </q-input>
                </div>
                <div class="col-xs-12 pull-right">
                  <q-btn color="primary" icon="add" label="Agregar" style="height: 100%;margin-right: 5px;" @click="addDossier()" v-show="dossiers.fields.id == null" />
                  <q-btn color="positive" icon="save" label="Guardar" style="height: 100%;margin-right: 5px;" @click="updateDossier()" v-show="dossiers.fields.id != null" />
                  <q-btn color="negative" icon="cancel" label="Cancelar" style="height: 100%;margin-right: 5px;" @click="cancelEditDossier()" v-show="dossiers.fields.id != null" />
                </div>
              </div>
              <div class="q-col-gutter-xs" style="padding: 2%;">
                <q-table
                  flat
                  bordered
                  :data="dossiersList"
                  :columns="dossiersColumns"
                  row-key="name"
                  :pagination.sync="pagination"
                >
                  <template v-slot:body="props">
                    <q-tr :props="props">
                      <q-td key="name" :props="props" style="text-align: left; width: 20%;">{{ props.row.name }}</q-td>
                      <q-td key="description" :props="props" style="text-align: left; width: 20%;">{{ props.row.description }}</q-td>
                      <q-td key="actions" :props="props" style="width: 10%;">
                        <q-btn color="primary" icon="fas fa-edit" flat @click.native="editSelectedRowDossier(props.row)" size="10px">
                          <q-tooltip content-class="bg-primary">Editar</q-tooltip>
                        </q-btn>
                        <q-btn color="negative" icon="fas fa-trash-alt" flat @click.native="deleteSelectedRowDossier(props.row.id)" size="10px">
                          <q-tooltip content-class="bg-red">Eliminar</q-tooltip>
                        </q-btn>
                        <q-btn color="negative" icon="fas fa-file-pdf" flat @click.native="openDossierFile(props.row.id)" size="10px">
                          <q-tooltip content-class="bg-red">Agregar Documento</q-tooltip>
                        </q-btn>
                      </q-td>
                    </q-tr>
                  </template>
                </q-table>
              </div>
            </div>
          </q-tab-panel>
        </q-tab-panels>
            <q-dialog v-model="openDossierFileModal" persistent>
      <q-card style="min-width: 500px;">
        <q-card-section class="bg-primary">
          <div class="text-h6 text-white text-center">Seleccionar Archivo</div>
        </q-card-section>
        <q-card-section>
            <div align="center" class="col-xs-6 q-pa-xs vertical-middle">
            <q-uploader
                method="POST"
                label="Elegir archivo a subir"
                ref="fileDocumentCitationRef"
                max-files="1"
                flat
                auto-upload
                no-thumbnails
                :headers="[{name: 'Authorization', value: 'Bearer ' + this.JWT}]"
                @uploaded="afterUploadDocumentFileCitation"
              />
            </div>
        </q-card-section>
        <q-card-actions align="center" class="text-primary">
          <q-btn color="red" label="Cancelar" v-close-popup />
          <q-btn color="positive" label="Generar" @click="request()" />
        </q-card-actions>
      </q-card>
    </q-dialog>
      </div>
    </div>
  </q-page>
</template>

<script>
import api from '../../commons/api.js'
const { required, maxLength, minLength, integer, minValue, email } = require('vuelidate/lib/validators')

export default {
  name: 'EditSupplier',
  validations: {
    supplier: {
      fields: {
        serial: { required, integer, maxLength: maxLength(10) },
        name: { required, maxLength: maxLength(100) },
        tradename: { required, maxLength: maxLength(100) },
        contact_name: { maxLength: maxLength(100) },
        contact_phone: { minLength: minLength(10), maxLength: maxLength(20), integer, minValue: minValue(1) },
        contact_phone_two: { minLength: minLength(10), maxLength: maxLength(20), integer, minValue: minValue(1) },
        street: { required, maxLength: maxLength(100) },
        outdoor_number: { required, maxLength: maxLength(10) },
        indoor_number: { maxLength: maxLength(10) },
        suburb: { required, maxLength: maxLength(100) },
        municipality: { required, maxLength: maxLength(100) },
        state: { required, maxLength: maxLength(100) },
        zip_code: { required, maxLength: maxLength(5), integer, minValue: minValue(1) },
        rfc: { required, maxLength: maxLength(20) },
        /* term: { required, maxLength: maxLength(100) }, */
        payment_method: { required, maxLength: maxLength(100) },
        currency: { required, maxLength: maxLength(20) },
        email: { required, email },
        email2: { email },
        country: { required },
        credit_days: { required }
      }
    },
    contacts: {
      fields: {
        name: { required },
        phone: { minLength: minLength(10), maxLength: maxLength(20), integer, minValue: minValue(1) },
        email: { required, email },
        tel: { minLength: minLength(10), maxLength: maxLength(20), integer, minValue: minValue(1) }
      }
    },
    dossiers: {
      fields: {
        name: { required }
      }
    }
  },
  data () {
    return {
      currentTab: 'contacts',
      supplier: {
        fields: {
          id: null,
          serial: null,
          name: null,
          tradename: null,
          contact_name: null,
          contact_phone: null,
          contact_phone_two: null,
          street: null,
          outdoor_number: null,
          indoor_number: null,
          suburb: null,
          municipality: null,
          state: null,
          zip_code: null,
          rfc: null,
          term: null,
          payment_method: null,
          currency: null,
          active: false,
          email: null,
          email2: null,
          country: null,
          branch_id: 7,
          credit_days: null
        }
      },
      contacts: {
        fields: {
          id: null,
          name: null,
          phone: null,
          email: null,
          tel: null
        }
      },
      dossiers: {
        fields: {
          id: null,
          name: null,
          description: null
        }
      },
      openDossierFileModal: false,
      contactsColumns: [
        { name: 'name', align: 'center', label: 'Nombre'.toUpperCase(), field: 'name', style: 'width: 20%', sortable: true },
        { name: 'tel', align: 'center', label: 'Teléfono'.toUpperCase(), field: 'tel', style: 'width: 20%', sortable: true },
        { name: 'phone', align: 'center', label: 'Celular'.toUpperCase(), field: 'phone', style: 'width: 20%', sortable: true },
        { name: 'email', align: 'center', label: 'Correo Electrónico'.toUpperCase(), field: 'email', style: 'width: 20%', sortable: true },
        { name: 'actions', align: 'center', label: 'Acciones'.toUpperCase(), field: 'actions', style: 'width: 20%', sortable: false }
      ],
      dossiersColumns: [
        { name: 'name', align: 'center', label: 'Nombre'.toUpperCase(), field: 'name', style: 'width: 20%', sortable: true },
        { name: 'description', align: 'center', label: 'Descripción del documento'.toUpperCase(), field: 'description', style: 'width: 20%', sortable: true },
        { name: 'actions', align: 'center', label: 'Acciones'.toUpperCase(), field: 'actions', style: 'width: 20%', sortable: false }
      ],
      pagination: {
        sortBy: 'code',
        descending: false,
        rowsPerPage: 25
      },
      branchesList: [],
      contactsList: [],
      dossiersList: []
    }
  },
  computed: {
    roleId () {
      const user = this.$store.getters['users/rol']
      return parseInt(user)
    },
    contactsNameRules (val) {
      return [
        val => (this.$v.contacts.fields.name.required) || 'El campo Nombre es requerido.'
      ]
    },
    JWT () {
      return localStorage.getItem('JWT')
    },
    fileDocumentUrlCitation () {
      return `${process.env.API}shopping-carts/file3/${this.$route.params.id}`
    },
    dossiersNameRules (val) {
      return [
        val => (this.$v.dossiers.fields.name.required) || 'El campo Nombre de es requerido.'
      ]
    },
    contactsPhoneRules (val) {
      return [
        val => this.$v.contacts.fields.phone.integer || 'El campo Celular debe ser numérico.',
        val => this.$v.contacts.fields.phone.minValue || 'El campo Celular debe ser positivo.',
        val => this.$v.contacts.fields.phone.minLength || 'El campo Celular debe tener al menos 10 dígitos.',
        val => this.$v.contacts.fields.phone.maxLength || 'El campo Celular no debe exceder los 20 dígitos.'
      ]
    },
    contactsEmailRules (val) {
      return [
        val => (this.$v.contacts.fields.email.required) || 'El campo Correo electrónico es requerido.',
        val => this.$v.contacts.fields.email.email || 'El campo Correo electrónico debe contener una dirección de correo electrónico válida.'
      ]
    },
    contactsTelRules (val) {
      return [
        val => this.$v.contacts.fields.tel.integer || 'El campo Teléfono debe ser numérico.',
        val => this.$v.contacts.fields.tel.minValue || 'El campo Teléfono debe ser positivo.',
        val => this.$v.contacts.fields.tel.minLength || 'El campo Teléfono debe tener al menos 10 dígitos.',
        val => this.$v.contacts.fields.tel.maxLength || 'El campo Teléfono no debe exceder los 20 dígitos.'
      ]
    },
    serialRules (val) {
      return [
        val => (this.$v.supplier.fields.serial.required) || 'El campo Código es requerido.',
        val => (this.$v.supplier.fields.serial.maxLength) || 'El campo Código no debe exceder los 10 dígitos.',
        val => this.$v.supplier.fields.serial.integer || 'El campo Código debe ser numérico.'
      ]
    },
    nameRules (val) {
      return [
        val => this.$v.supplier.fields.name.required || 'El campo Nombre es requerido.',
        val => this.$v.supplier.fields.name.maxLength || 'El campo Nombre no debe exceder los 100 dígitos.'
      ]
    },
    tradenameRules (val) {
      return [
        val => this.$v.supplier.fields.tradename.required || 'El campo Nombre comercial es requerido.',
        val => this.$v.supplier.fields.tradename.maxLength || 'El campo Nombre comercial no debe exceder los 100 dígitos.'
      ]
    },
    contactNameRules (val) {
      return [
        val => this.$v.supplier.fields.contact_name.maxLength || 'El campo Nombre Contacto no debe exceder los 100 dígitos.'
      ]
    },
    contactPhoneRules (val) {
      return [
        val => this.$v.supplier.fields.contact_phone.integer || 'El campo Teléfono debe ser numérico.',
        val => this.$v.supplier.fields.contact_phone.minValue || 'El campo Teléfono debe ser positivo.',
        val => this.$v.supplier.fields.contact_phone.minLength || 'El campo Teléfono debe tener al menos 10 dígitos.',
        val => this.$v.supplier.fields.contact_phone.maxLength || 'El campo Teléfono no debe exceder los 20 dígitos.'
      ]
    },
    contactPhoneRulesTwo (val) {
      return [
        val => this.$v.supplier.fields.contact_phone.integer || 'El campo Teléfono 2 debe ser numérico.',
        val => this.$v.supplier.fields.contact_phone.minValue || 'El campo Teléfono 2 debe ser positivo.',
        val => this.$v.supplier.fields.contact_phone.minLength || 'El campo Teléfono 2 debe tener al menos 10 dígitos.',
        val => this.$v.supplier.fields.contact_phone.maxLength || 'El campo Teléfono 2 no debe exceder los 20 dígitos.'
      ]
    },
    streetRules (val) {
      return [
        val => this.$v.supplier.fields.street.required || 'El campo Calle es requerido.',
        val => this.$v.supplier.fields.street.maxLength || 'El campo Calle no debe exceder los 100 dígitos.'
      ]
    },
    outdoorNumberRules (val) {
      return [
        val => this.$v.supplier.fields.outdoor_number.required || 'El campo Número ext. es requerido.',
        val => this.$v.supplier.fields.outdoor_number.maxLength || 'El campo Número ext. no debe exceder los 10 dígitos.'
      ]
    },
    indoorNumberRules (val) {
      return [
        val => this.$v.supplier.fields.indoor_number.maxLength || 'El campo Número int. no debe exceder los 10 dígitos.'
      ]
    },
    suburbRules (val) {
      return [
        val => this.$v.supplier.fields.suburb.required || 'El campo Colonia es requerido.',
        val => this.$v.supplier.fields.suburb.maxLength || 'El campo Colonia no debe exceder los 100 dígitos.'
      ]
    },
    municipalityRules (val) {
      return [
        val => this.$v.supplier.fields.municipality.required || 'El campo Municipio es requerido.',
        val => this.$v.supplier.fields.municipality.maxLength || 'El campo Municipio no debe exceder los 100 dígitos.'
      ]
    },
    stateRules (val) {
      return [
        val => this.$v.supplier.fields.state.required || 'El campo Estado es requerido.',
        val => this.$v.supplier.fields.state.maxLength || 'El campo Estado no debe exceder los 100 dígitos.'
      ]
    },
    countryRules (val) {
      return [
        val => this.$v.supplier.fields.state.required || 'El campo Pais es requerido.'
      ]
    },
    zipCodeRules (val) {
      return [
        val => this.$v.supplier.fields.zip_code.required || 'El campo CP es requerido.',
        val => this.$v.supplier.fields.zip_code.integer || 'El campo CP debe ser numérico.',
        val => this.$v.supplier.fields.zip_code.minValue || 'El campo CP debe ser positivo.',
        val => this.$v.supplier.fields.zip_code.maxLength || 'El campo CP no debe exceder los 5 dígitos.'
      ]
    },
    rfcRules (val) {
      return [
        val => this.$v.supplier.fields.rfc.required || 'El campo RFC es requerido.',
        val => this.$v.supplier.fields.rfc.maxLength || 'El campo RFC no debe exceder los 20 dígitos.'
      ]
    },
    /* termRules (val) {
      return [
        val => this.$v.supplier.fields.term.required || 'El campo Plazo es requerido.',
        val => this.$v.supplier.fields.term.maxLength || 'El campo Plazo no debe exceder los 20 dígitos.'
      ]
    }, */
    paymentMethodRules (val) {
      return [
        val => this.$v.supplier.fields.payment_method.required || 'El campo Forma de pago es requerido.',
        val => this.$v.supplier.fields.payment_method.maxLength || 'El campo Forma de pago no debe exceder los 100 dígitos.'
      ]
    },
    currencyRules (val) {
      return [
        val => this.$v.supplier.fields.currency.required || 'El campo Moneda es requerido.',
        val => this.$v.supplier.fields.currency.maxLength || 'El campo Moneda no debe exceder los 20 dígitos.'
      ]
    },
    emailRules (val) {
      return [
        val => this.$v.supplier.fields.email.required || 'El campo Dirección de correo electrónico es requerido.',
        val => this.$v.supplier.fields.email.email || 'El campo Dirección de correo electrónico debe contener una dirección de correo electrónico válida.'
      ]
    }
  },
  mounted () {
    this.getBranchesList()
  },
  created () {
    this.$q.loading.show()
    const id = this.$route.params.id
    api.get(`/suppliers/${id}`).then(({ data }) => {
      if (!data.supplier) {
        this.$router.push('/suppliers')
      } else {
        this.supplier.fields = data.supplier
        if (!data.supplier.active) {
          this.supplier.fields.active = { label: 'INACTIVO', value: false }
        } else {
          this.supplier.fields.active = { label: 'ACTIVO', value: true }
        }
        api.get(`/supplier-contacts/supplier/${this.$route.params.id}`).then(({ data }) => {
          this.contactsList = data.contacts
          console.log(data.contacts)
          this.$q.loading.hide()
        })
        api.get(`/supplier-dossiers/all/${this.$route.params.id}`).then(({ data }) => {
          this.dossiersList = data.dossiers
          console.log(data.dossiers)
          this.$q.loading.hide()
        })
      }
    })
  },
  beforeRouteEnter (to, from, next) {
    next(vm => {
      const propiedades = vm.$store.getters['users/rol']
      console.log(propiedades)
      if (propiedades === 1 || propiedades === 3 || propiedades === 7 || propiedades === 2 || propiedades === 20 || propiedades === 4 || propiedades === 27 || propiedades === 22 || propiedades === 17 || propiedades === 28) {
        next()
      } else {
        next('/')
      }
    })
  },
  /* beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(22)) {
      this.$router.push('/')
    }
  }, */
  methods: {
    clearContactsInputs () {
      this.contacts.fields.name = null
      this.contacts.fields.phone = null
      this.contacts.fields.tel = null
      this.contacts.fields.email = null
    },
    afterUploadDocumentFileCitation (response) {
      const data = JSON.parse(response.xhr.response)
      this.$q.notify({
        message: data.message.content,
        position: 'top',
        color: (data.result ? 'positive' : 'warning')
      })
    },
    clearDossierInputs () {
      this.dossiers.fields.name = null
      this.dossiers.fields.description = null
    },
    openDossierFile (id) {
      this.openDossierFileModal = true
    },
    addContact () {
      this.$v.contacts.fields.$reset()
      this.$v.contacts.fields.$touch()
      if (this.$v.contacts.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = { ...this.contacts.fields }
      params.supplier_id = this.$route.params.id
      console.log(params)
      api.post('/supplier-contacts', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.clearContactsInputs()
          api.get(`/supplier-contacts/supplier/${this.$route.params.id}`).then(({ data }) => {
            this.contactsList = data.contacts
            this.$q.loading.hide()
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    addDossier () {
      this.$v.dossiers.fields.$reset()
      this.$v.dossiers.fields.$touch()
      if (this.$v.dossiers.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = { ...this.dossiers.fields }
      params.supplier_id = this.$route.params.id
      console.log(params)
      api.post('/supplier-dossiers', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.clearDossierInputs()
          api.get(`/supplier-dossiers/all/${this.$route.params.id}`).then(({ data }) => {
            this.dossiersList = data.dossiers
            this.$q.loading.hide()
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    updateContact () {
      this.$v.contacts.fields.$reset()
      this.$v.contacts.fields.$touch()
      if (this.$v.contacts.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = { ...this.contacts.fields }
      params.supplier_id = this.$route.params.id
      api.put(`/supplier-contacts/${params.id}`, params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.clearContactsInputs()
          api.get(`/supplier-contacts/supplier/${this.$route.params.id}`).then(({ data }) => {
            this.contactsList = data.contacts
            this.$q.loading.hide()
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    updateDossier () {
      this.$v.dossiers.fields.$reset()
      this.$v.dossiers.fields.$touch()
      if (this.$v.dossiers.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = { ...this.dossiers.fields }
      params.supplier_id = this.$route.params.id
      api.put(`/supplier-dossiers/${params.id}`, params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.clearDossierInputs()
          api.get(`/supplier-dossiers/all/${this.$route.params.id}`).then(({ data }) => {
            this.dossiersList = data.dossiers
            this.$q.loading.hide()
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    editSelectedRowContacts (contact) {
      this.contacts.fields.id = contact.id
      this.contacts.fields.name = contact.name
      this.contacts.fields.phone = contact.phone
      this.contacts.fields.tel = contact.tel
      this.contacts.fields.email = contact.email
    },
    cancelEditContact () {
      this.contacts.fields.id = null
      this.contacts.fields.name = null
      this.contacts.fields.phone = null
      this.contacts.fields.tel = null
      this.contacts.fields.email = null
    },
    editSelectedRowDossier (dossier) {
      this.dossiers.fields.id = dossier.id
      this.dossiers.fields.name = dossier.name
      this.dossiers.fields.description = dossier.description
    },
    cancelEditDossier () {
      this.dossiers.fields.id = null
      this.dossiers.fields.name = null
      this.dossiers.fields.description = null
    },
    deleteSelectedRowContact (contactId) {
      this.$q.loading.show()
      api.delete(`/supplier-contacts/${contactId}`).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          api.get(`/supplier-contacts/supplier/${this.$route.params.id}`).then(({ data }) => {
            this.contactsList = data.contacts
            this.$q.loading.hide()
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    deleteSelectedRowDossier (dossierId) {
      this.$q.loading.show()
      api.delete(`/supplier-dossiers/${dossierId}`).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          api.get(`/supplier-dossiers/all/${this.$route.params.id}`).then(({ data }) => {
            this.dossiersList = data.dossiers
            this.$q.loading.hide()
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    getBranchesList () {
      this.$q.loading.show()
      api.get('/branch-offices/options').then(data => {
        this.$q.loading.hide()
        this.branchesList = data.data.options
      })
    },
    updateSupplierFields () {
      this.$v.supplier.fields.$reset()
      this.$v.supplier.fields.$touch()
    },
    editSupplier () {
      this.$v.supplier.fields.$reset()
      this.$v.supplier.fields.$touch()
      if (this.$v.supplier.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = { ...this.supplier.fields }
      params.active = params.active.value
      api.put(`/suppliers/${params.id}`, params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.$router.push('/suppliers')
        } else {
          this.$q.loading.hide()
        }
      })
    }
  }
}
</script>

<style>
</style>

<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-6">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Clientes" to="/customers" />
              <q-breadcrumbs-el label="Editar" v-text="customer.fields.name"/>
            </q-breadcrumbs>
          </div>
        </div>
                <div class="col-xs-12 col-md-6  pull-right">
          <div class="q-pa-sm q-gutter-sm" v-if="roleId !== 29">
              <q-btn color="positive" icon="save" label="Actualizar" @click="updateCustomer()" />
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
                :error="$v.customer.fields.serial.$error"
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
                @input="v => { customer.fields.name = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-signature" />
                </template>
              </q-input>
            </div>
            <!-- <div class="col-xs-12 col-sm-2">
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
            <!-- <div class="col-xs-12 col-sm-4">
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
                :rules="paymentMethodRules"
                @input="termSelected()"
                emit-value
                map-options
                :options="[
                {label: 'CONTADO', value: 'CONTADO'},
                {label: 'CREDITO', value: 'CREDITO'}
                ]"
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
                map-options
                emit-value
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
                emit-value
                map-options
                v-model="customer.fields.price_list"
                :error="$v.customer.fields.price_list.$error"
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
                v-model="customer.fields.discount"
                :error="$v.customer.fields.discount.$error"
                :rules="discountRules"
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
                v-model="customer.fields.seller_id"
                :error="$v.customer.fields.seller_id.$error"
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
            <div class="col-xs-2 col-sm-4 col-md-4 col-lg-4">
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
            <div class="col-xs-12 col-sm-2 col-md-2 col-lg-2">
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
            <div class="col-xs-12 col-sm-2">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="customer.fields.country"
                :error="$v.customer.fields.country.$error"
                label="País"
                :rules="countryRules"
                @input="v => { customer.fields.country = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="map" />
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
          <q-tab name="sucursales" label="Estaciones" />
          <q-tab name="fiscales" label="Datos fiscales" />
          <q-tab name="contacts" label="Contactos" />
          <q-tab name="requeriments" label="Requisitos" />
        </q-tabs>
        <q-tab-panels v-model="currentTab" animated>
          <q-tab-panel name="sucursales">
            <div style="font-weight: normal;">
              <div class="row q-col-gutter-xs" style="padding: 2%;">
                <div class="col-xs-12 col-sm-4">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="branchOffice.fields.name"
                    :error="$v.branchOffice.fields.name.$error"
                    label="Nombre"
                    :rules="branchOfficeNameRules"
                    @input="v => { branchOffice.fields.name = v.toUpperCase() }"
                  >
                    <template v-slot:prepend>
                      <q-icon name="fas fa-signature" />
                    </template>
                  </q-input>
                </div>
                <div class="col-xs-12 col-sm-4">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="branchOffice.fields.street"
                    :error="$v.branchOffice.fields.street.$error"
                    label="Calle"
                    :rules="branchOfficeStreetRules"
                    @input="v => { branchOffice.fields.street = v.toUpperCase() }"
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
                    v-model="branchOffice.fields.outdoor_number"
                    :error="$v.branchOffice.fields.outdoor_number.$error"
                    label="Número exterior "
                    :rules="branchOfficeOutdoorNumberRules"
                    @input="v => { branchOffice.fields.outdoor_number = v.toUpperCase() }"
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
                    v-model="branchOffice.fields.int_number"
                    :error="$v.branchOffice.fields.int_number.$error"
                    label="Número interior"
                    :rules="branchOfficeIntNumberRules"
                    @input="v => { branchOffice.fields.int_number = v.toUpperCase() }"
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
                    v-model="branchOffice.fields.zip_code"
                    :error="$v.branchOffice.fields.zip_code.$error"
                    label="CP"
                    :rules="branchOfficeZipCodeRules"
                    @input="v => { branchOffice.fields.zip_code = v.replace(/[^0-9]/g, '').substr(0, 10) }"
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
                    v-model="branchOffice.fields.municipality"
                    :error="$v.branchOffice.fields.municipality.$error"
                    label="Municipio"
                    :rules="branchOfficeMunicipalityRules"
                    @input="v => { branchOffice.fields.municipality = v.toUpperCase() }"
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
                    v-model="branchOffice.fields.city"
                    :error="$v.branchOffice.fields.city.$error"
                    label="Ciudad"
                    :rules="branchOfficeCityRules"
                    @input="v => { branchOffice.fields.city = v.toUpperCase() }"
                  >
                    <template v-slot:prepend>
                      <q-icon name="map" />
                    </template>
                  </q-input>
                </div>
                <div class="col-xs-12 col-sm-2">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="branchOffice.fields.state"
                    :error="$v.branchOffice.fields.state.$error"
                    label="Estado"
                    :rules="branchOfficeStateRules"
                    @input="v => { branchOffice.fields.state = v.toUpperCase() }"
                  >
                    <template v-slot:prepend>
                      <q-icon name="map" />
                    </template>
                  </q-input>
                </div>
                <!-- <div class="col-xs-12 col-sm-2">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="branchOffice.fields.colony"
                    :error="$v.branchOffice.fields.colony.$error"
                    label="Colonia"
                    :rules="branchOfficeColonyRules"
                    @input="v => { branchOffice.fields.colony = v.toUpperCase() }"
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
                    v-model="branchOffice.fields.phone_number"
                    :error="$v.branchOffice.fields.phone_number.$error"
                    label="Teléfono"
                    :rules="branchOfficePhoneNumberRules"
                    @input="v => { branchOffice.fields.phone_number = v.replace(/[^0-9]/g, '').substr(0, 20) }"
                  >
                    <template v-slot:prepend>
                      <q-icon name="contact_phone" />
                    </template>
                  </q-input>
                </div> -->
                <div class="col-xs-12 pull-right">
                  <q-btn color="primary" icon="add" label="Agregar" style="height: 100%;margin-right: 5px;" @click="addBranchOffice()" v-show="branchOffice.fields.id == null" />
                  <q-btn color="positive" icon="save" label="Guardar" style="height: 100%;margin-right: 5px;" @click="updateBranchOffice()" v-show="branchOffice.fields.id != null" />
                  <q-btn color="negative" icon="cancel" label="Cancelar" style="height: 100%;margin-right: 5px;" @click="cancelEditFormula()" v-show="branchOffice.fields.id != null" />
                </div>
              </div>
              <div class="q-col-gutter-xs" style="padding: 2%;">
                <q-table
                  flat
                  bordered
                  :data="branchOffices"
                  :columns="branchOfficeColumns"
                  row-key="name"
                  :pagination.sync="pagination"
                >
                  <template v-slot:body="props">
                    <q-tr :props="props">
                      <q-td key="name" :props="props" style="text-align: left; width: 10%;">{{ props.row.name }}</q-td>
                      <q-td key="street" :props="props" style="text-align: left; width: 10%;">{{ props.row.street }}</q-td>
                      <q-td key="outdoor_number" :props="props" style="text-align: left; width: 5%;">{{ props.row.outdoor_number }}</q-td>
                      <q-td key="int_number" :props="props" style="text-align: left; width: 5%;">{{ props.row.int_number }}</q-td>
                      <q-td key="zip_code" :props="props" style="text-align: left; width: 10%;">{{ props.row.zip_code }}</q-td>
                      <q-td key="municipality" :props="props" style="text-align: left; width: 10%;">{{ props.row.municipality }}</q-td>
                      <q-td key="city" :props="props" style="text-align: left; width: 10%;">{{ props.row.city }}</q-td>
                      <q-td key="state" :props="props" style="text-align: left; width: 10%;">{{ props.row.state }}</q-td>
                      <q-td key="colony" :props="props" style="text-align: left; width: 10%;">{{ props.row.colony }}</q-td>
                      <q-td key="phone_number" :props="props" style="text-align: left; width: 10%;">{{ props.row.phone_number }}</q-td>
                      <q-td key="open_horary" :props="props" style="text-align: center; width: 10%;">{{ props.row.open_horary }}</q-td>
                      <q-td key="close_horary" :props="props" style="text-align: center; width: 10%;">{{ props.row.close_horary }}</q-td>
                      <q-td key="actions" :props="props" style="width: 10%;" v-if="roleId !== 29">
                        <q-btn color="primary" icon="fas fa-edit" flat @click.native="editSelectedRow(props.row)" size="10px">
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
                  <q-select
                    color="dark"
                    bg-color="secondary"
                    filled
                    map-options
                    v-model="fiscal.fields.regimen_fiscal"
                    :error="$v.fiscal.fields.regimen_fiscal.$error"
                    label="Régimen fiscal"
                    :options="regimenFiscalOptions"
                    emit-value
                  >
                    <template v-slot:prepend>
                      <q-icon name="description" />
                    </template>
                  </q-select>
                </div>
                <!--<div class="col-xs-12 col-sm-3">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="fiscal.fields.serie"
                    :error="$v.fiscal.fields.serie.$error"
                    label="Serie"
                  >
                    <template v-slot:prepend>
                      <q-icon name="description" />
                    </template>
                  </q-input>
                </div> -->
                <div class="col-xs-12 col-sm-3">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="fiscal.fields.lugar_expedicion"
                    :error="$v.fiscal.fields.lugar_expedicion.$error"
                    label="Código postal"
                    @keyup="isNumber($event,'codigo_postal')"
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
                <!-- <div class="col-xs-12 col-sm-3">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="fiscal.fields.serie"
                    :error="$v.fiscal.fields.serie.$error"
                    label="Serie"
                  >
                    <template v-slot:prepend>
                      <q-icon name="description" />
                    </template>
                  </q-input>
                </div> -->
                <div class="col-xs-12 col-sm-3">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="fiscal.fields.rfc_banco"
                    label="RFC Banco"
                    @input="v => { fiscal.fields.rfc_banco = v.toUpperCase() }"
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
                    v-model="fiscal.fields.cuenta"
                    label="Cuenta bancaria"
                    @input="v => { fiscal.fields.cuenta = v.toUpperCase() }"
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
                    v-model="fiscal.fields.banco"
                    label="Banco"
                    @input="v => { fiscal.fields.banco = v.toUpperCase() }"
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
                    map-options
                    emit-value
                    v-model="fiscal.fields.immex"
                    label="IMMEX"
                    :options="[{label: 'Si', value: true}, {label: 'No', value: false}]"
                  >
                    <template v-slot:prepend>
                      <q-icon name="description" />
                    </template>
                  </q-select>
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
                      <q-td key="actions" :props="props" style="width: 10%;" v-if="roleId !== 29">
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
            <div class="col-xs-12 col-sm-3">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="contacts.fields.apartment"
                    :error="$v.contacts.fields.apartment.$error"
                    label="Departamento "
                    :rules="contactsApartmentRules"
                  >
                    <template v-slot:prepend>
                      <q-icon name="fas fa-signature" />
                    </template>
                  </q-input>
                </div>
            <div class="col-xs-12 col-sm-3">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="contacts.fields.send_invoice"
                :error="$v.contacts.fields.send_invoice.$error"
                label="¿Enviar Facturas?"
                :options="[
                  {label: 'SI', value: 'SI'},
                  {label: 'NO', value: 'NO'}
                ]"
              >
                <template v-slot:prepend>
                  <q-icon name="attach_money" />
                </template>
              </q-select>
            </div>
                <div class="col-xs-12 pull-right">
                  <q-btn color="primary" icon="add" label="Agregar" style="height: 100%;margin-right: 5px;" @click="addContact()" v-show="contacts.fields.id == null" />
                  <q-btn color="positive" icon="save" label="Guardar" style="height: 100%;margin-right: 5px;" @click="updateContact()" v-show="contacts.fields.id != null" />
                  <q-btn color="negative" icon="fas fa-ban" label="Cancelar" style="height: 100%;margin-right: 5px;" @click="cancelEditContact()" v-show="contacts.fields.id != null" />
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
                      <q-td key="tel" :props="props" style="text-align: center; width: 20%;">{{ props.row.tel }}</q-td>
                      <q-td key="phone" :props="props" style="text-align: center; width: 20%;">{{ props.row.phone }}</q-td>
                      <q-td key="email" :props="props" style="text-align: center; width: 20%;">{{ props.row.email }}</q-td>
                      <q-td key="apartment" :props="props" style="text-align: left; width: 20%;">{{ props.row.apartment }}</q-td>
                      <q-td key="send_invoice" :props="props" style="text-align: left; width: 20%;">{{ props.row.send_invoice }}</q-td>
                      <q-td key="actions" :props="props" style="width: 20%;" v-if="roleId !== 29">
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
          <q-tab-panel name="requeriments">
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
                v-model="document.fields.requirements"
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
                v-model="document.fields.documents"
                label="Documentos requeridos"
                @input="v => { customer.fields.documents = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-file-alt" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 pull-right">
              <q-btn color="primary" icon="add" label="Agregar" style="height: 100%;margin-right: 5px;" @click="addRequirement()" v-show="contacts.fields.id == null" />
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
const { required, requiredIf, maxLength, minLength, integer, minValue, email, decimal } = require('vuelidate/lib/validators')

export default {
  name: 'EditCustomer',
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
        street: { required, maxLength: maxLength(100) },
        outdoor_number: { required, maxLength: maxLength(10) },
        indoor_number: { maxLength: maxLength(10) },
        suburb: { required, maxLength: maxLength(100) },
        municipality: { maxLength: maxLength(100) },
        state: { required, maxLength: maxLength(100) },
        city: { maxLength: maxLength(100) },
        zip_code: { required, maxLength: maxLength(5), minValue: minValue(1) },
        rfc: { maxLength: maxLength(20) },
        term: { maxLength: maxLength(100) },
        payment_method: { required, maxLength: maxLength(100) },
        currency: { required },
        seller_id: { required },
        price_list: { required },
        email: { required, email },
        email2: { email },
        email3: { email },
        email4: { email },
        country: { required },
        credit_days: { required: requiredIf(function () { return this.enableFields }) },
        credit_limit: { required: requiredIf(function () { return this.enableFields }), decimal },
        between_street: { },
        postal_code_id: { },
        suburb_id: { }
      }
    },
    branchOffice: {
      fields: {
        name: { required, minLength: minLength(5), maxLength: maxLength(50) },
        street: { required, maxLength: maxLength(100) },
        outdoor_number: { required, maxLength: maxLength(10) },
        zip_code: { required, maxLength: maxLength(5) },
        // phone_number: { required, minLength: minLength(10) },
        int_number: { maxLength: maxLength(10) },
        city: { maxLength: maxLength(50) },
        // colony: { maxLength: maxLength(50), required: requiredIf(function () { return this.branchOffice.fields.suburb_id === 0 }) },
        municipality: { maxLength: maxLength(50) },
        state: { maxLength: maxLength(50) },
        between_street: { },
        postal_code_id: { },
        suburb_id: { }
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
        email: { required, email },
        serie: { },
        regimen_fiscal: { required }
      }
    },
    contacts: {
      fields: {
        name: { required },
        phone: { minLength: minLength(10), maxLength: maxLength(20), integer, minValue: minValue(1) },
        email: { required, email },
        tel: { minLength: minLength(10), maxLength: maxLength(20), integer, minValue: minValue(1) },
        send_invoice: { required },
        apartment: { maxLength: maxLength(20) }
      }
    }
  },
  data () {
    return {
      branchesList: [],
      channelOptions: [],
      enableFields: false,
      customer: {
        fields: {
          id: null,
          serial: null,
          seller_id: null,
          admission_date: null,
          name: null,
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
          city: null,
          zip_code: null,
          rfc: null,
          term: null,
          payment_method: null,
          currency: null,
          active: false,
          price_list: null,
          email: null,
          email2: null,
          email3: null,
          email4: null,
          branch_id: null,
          country: null,
          credit_days: null,
          credit_limit: null,
          channel_id: null,
          discount: null,
          municipio_id: null
        }
      },
      branchOffice: {
        fields: {
          id: null,
          name: null,
          street: null,
          outdoor_number: null,
          zip_code: null,
          phone_number: null,
          open_horary: null,
          close_horary: null,
          city: null,
          municipality: null,
          colony: null,
          int_number: null,
          state: null,
          postal_code_id: null,
          suburb_id: null,
          between_street: null,
          municipio_id: null,
          suburb: null
        }
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
          serie: null,
          rfc_banco: null,
          cuenta: null,
          banco: null,
          regimen_fiscal: null,
          immex: false
        }
      },
      contacts: {
        fields: {
          id: null,
          name: null,
          phone: null,
          email: null,
          tel: null,
          send_invoice: null,
          apartment: null
        }
      },
      document: {
        fields: {
          requirements: null,
          documents: null
        }
      },
      branchOffices: [],
      sellerOptions: [],
      contactsList: [],
      currencyOptions: [],
      branchOfficeColumns: [
        { name: 'name', align: 'center', label: 'Nombre'.toUpperCase(), field: 'name', style: 'width: 10%', sortable: true },
        { name: 'street', align: 'center', label: 'Calle'.toUpperCase(), field: 'street', style: 'width: 10%', sortable: true },
        { name: 'outdoor_number', align: 'center', label: 'Número ext'.toUpperCase(), field: 'outdoor_number', style: 'width: 5%', sortable: true },
        { name: 'int_number', align: 'center', label: 'Número int'.toUpperCase(), field: 'int_number', style: 'width: 5%', sortable: true },
        { name: 'zip_code', align: 'center', label: 'CP'.toUpperCase(), field: 'zip_code', style: 'width: 5%', sortable: true },
        { name: 'municipality', align: 'center', label: 'Municipio'.toUpperCase(), field: 'municipality', style: 'width: 10%', sortable: true },
        { name: 'city', align: 'center', label: 'Ciudad'.toUpperCase(), field: 'city', style: 'width: 10%', sortable: true },
        { name: 'state', align: 'center', label: 'Estado'.toUpperCase(), field: 'state', style: 'width: 10%', sortable: true },
        { name: 'actions', align: 'center', label: 'Acciones'.toUpperCase(), field: 'actions', style: 'width: 10%', sortable: false }
      ],
      contactsColumns: [
        { name: 'name', align: 'center', label: 'Nombre'.toUpperCase(), field: 'name', style: 'width: 20%', sortable: true },
        { name: 'tel', align: 'center', label: 'Teléfono'.toUpperCase(), field: 'tel', style: 'width: 20%', sortable: true },
        { name: 'phone', align: 'center', label: 'Celular'.toUpperCase(), field: 'phone', style: 'width: 20%', sortable: true },
        { name: 'email', align: 'center', label: 'Correo Electrónico'.toUpperCase(), field: 'email', style: 'width: 20%', sortable: true },
        { name: 'apartment', align: 'center', label: 'DEPARTAMENTO'.toUpperCase(), field: 'email', style: 'width: 20%', sortable: true },
        { name: 'send_invoice', align: 'center', label: 'ENVIAR FACTURA'.toUpperCase(), field: 'email', style: 'width: 20%', sortable: true },
        { name: 'actions', align: 'center', label: 'Acciones'.toUpperCase(), field: 'actions', style: 'width: 20%', sortable: false }
      ],
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
      pagination: {
        sortBy: 'code',
        descending: false,
        rowsPerPage: 25
      },
      currentTab: 'sucursales',
      metodoPagoOptions: [{ label: 'PUE - Pago en una sola exhibición', value: 'PUE' }, { label: 'PPD - Pago en parcialidades', value: 'PPD' }],
      formaPagoOptions: [],
      usoCFDIOptions: [],
      selectEstados: [],
      selectMunicipio: [],
      options: this.selectMunicipio,
      options2: this.selectEstados,
      postal_codes: [],
      postal_codes_options: [],
      suburbs: [],
      suburbs_options: [],
      cities: [],
      cityOptions: [],
      postal_codes_options_customer: [],
      suburbsCustomer: [],
      suburbs_options_customer: [],
      citiesCustomer: [],
      cityOptionsCustomer: [],
      selectMunicipioCustomer: [],
      selectMunicipioCustomerOptions: [],
      selectEstadosCustomer: [],
      regimenFiscalOptions: [
        { label: 'General de Ley Personas Morales', value: 601 },
        { label: 'Personas Morales con Fines no Lucrativos', value: 603 },
        { label: 'Sueldos y Salarios e Ingresos Asimilados a Salarios', value: 605 },
        { label: 'Arrendamiento', value: 606 },
        { label: 'Demás ingresos', value: 608 },
        { label: 'Residentes en el Extranjero sin Establecimiento Permanente en México', value: 610 },
        { label: 'Ingresos por Dividendos (socios y accionistas)', value: 611 },
        { label: 'Personas Físicas con Actividades Empresariales y Profesionales', value: 612 },
        { label: 'Ingresos por intereses', value: 614 },
        { label: 'Sin obligaciones fiscales', value: 616 },
        { label: 'Sociedades Cooperativas de Producción que optan por diferir sus ingresos', value: 620 },
        { label: 'Incorporación Fiscal', value: 621 },
        { label: 'Actividades Agrícolas, Ganaderas, Silvícolas y Pesqueras', value: 622 },
        { label: 'Opcional para Grupos de Sociedades', value: 623 },
        { label: 'Coordinados', value: 624 },
        { label: 'Hidrocarburos', value: 628 },
        { label: 'Régimen de Enajenación o Adquisición de Bienes', value: 607 },
        { label: 'De los Regímenes Fiscales Preferentes y de las Empresas Multinacionales', value: 629 },
        { label: 'Enajenación de acciones en bolsa de valores', value: 630 },
        { label: 'Régimen de los ingresos por obtención de premios', value: 615 },
        { label: 'Régimen de las Actividades Empresariales con ingresos a través de Plataformas Tecnológicas', value: 625 },
        { label: 'Régimen Simplificado de Confianza', value: 626 }
      ]
    }
  },
  computed: {
    roleId () {
      const user = this.$store.getters['users/rol']
      return parseInt(user)
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
    contactsInvoice (val) {
      return [
        val => (this.$v.contacts.fields.send_invoice.required) || 'El campo Enviar Factura es requerido.'
      ]
    },
    contactsNameRules (val) {
      return [
        val => (this.$v.contacts.fields.name.required) || 'El campo Nombre es requerido.'
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
    discountRules (val) {
      return [
        val => this.$v.customer.fields.discount.decimal || 'El campo Descuento debe ser númerico.',
        val => this.$v.customer.fields.discount.minValue || 'El campo Descuento debe ser positivo.'
      ]
    },
    contactsEmailRules (val) {
      return [
        val => (this.$v.contacts.fields.email.required) || 'El campo Correo electrónico es requerido.',
        val => this.$v.contacts.fields.email.email || 'El campo Correo electrónico debe contener una dirección de correo electrónico válida.'
      ]
    },
    contactsApartmentRules (val) {
      return [
        val => (this.$v.contacts.fields.apartment.maxLength) || 'El campo Departamento no debe exceder los 20 caracteres.'
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
        val => (this.$v.customer.fields.serial.required) || 'El campo Código es requerido.',
        val => (this.$v.customer.fields.serial.maxLength) || 'El campo Código no debe exceder los 10 dígitos.'
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
        val => this.$v.customer.fields.contact_phone_res.integer || 'El campo Teléfono 2 debe ser numérico.',
        val => this.$v.customer.fields.contact_phone_res.minValue || 'El campo Teléfono 2 debe ser positivo.',
        val => this.$v.customer.fields.contact_phone_res.minLength || 'El campo Teléfono 2 debe tener al menos 10 dígitos.',
        val => this.$v.customer.fields.contact_phone_res.maxLength || 'El campo Teléfono 2 no debe exceder los 20 dígitos.'
      ]
    },
    streetRules (val) {
      return [
        val => this.$v.customer.fields.street.required || 'El campo Calle es requerido.',
        val => this.$v.customer.fields.street.maxLength || 'El campo Calle no debe exceder los 100 dígitos.'
      ]
    },
    outdoorNumberRules (val) {
      return [
        val => this.$v.customer.fields.outdoor_number.required || 'El campo Número ext. es requerido.',
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
        val => this.$v.customer.fields.suburb.required || 'El campo Colonia es requerido.',
        val => this.$v.customer.fields.suburb.maxLength || 'El campo Colonia no debe exceder los 100 dígitos.'
      ]
    },
    municipalityRules (val) {
      return [
        val => this.$v.customer.fields.municipality.maxLength || 'El campo Municipio no debe exceder los 100 dígitos.'
      ]
    },
    countryRules (val) {
      return [
        val => this.$v.customer.fields.country.required || 'El campo País es requerido.'
      ]
    },
    stateRules (val) {
      return [
        val => this.$v.customer.fields.state.required || 'El campo Estado es requerido.',
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
        val => this.$v.customer.fields.zip_code.required || 'El campo CP es requerido.',
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
        val => this.$v.customer.fields.price_list.required || 'El campo Precio de lista es requerido.'
      ]
    },
    sellerRules (val) {
      return [
        val => this.$v.customer.fields.seller_id.required || 'El campo Vendedor es requerido.'
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
    branchOfficeNameRules (val) {
      return [
        val => this.$v.branchOffice.fields.name.required || 'El campo Nombre es requerido.',
        val => this.$v.branchOffice.fields.name.minLength || 'El campo Nombre debe tener una longitud mínima de 5 caracteres.',
        val => this.$v.branchOffice.fields.name.maxLength || 'El campo Nombre debe tener una longitud máxima de 50 caracteres.'
      ]
    },
    branchOfficeStreetRules (val) {
      return [
        val => this.$v.branchOffice.fields.street.required || 'El campo Calle es requerido.',
        val => this.$v.branchOffice.fields.street.maxLength || 'El campo Calle no debe exceder los 100 dígitos.'
      ]
    },
    branchOfficeOutdoorNumberRules (val) {
      return [
        val => this.$v.branchOffice.fields.outdoor_number.required || 'El campo Número exterior es requerido.',
        val => this.$v.branchOffice.fields.outdoor_number.maxLength || 'El campo Número exterior debe tener una longitud máxima de 10 caracteres.'
      ]
    },
    branchOfficeIntNumberRules (val) {
      return [
        val => this.$v.branchOffice.fields.int_number.maxLength || 'El campo Número interior debe tener una longitud máxima de 10 caracteres.'
      ]
    },
    branchOfficeCityRules (val) {
      return [
        val => this.$v.branchOffice.fields.city.maxLength || 'El campo Ciudad debe tener una longitud máxima de 50 caracteres.'
      ]
    },
    branchOfficeStateRules (val) {
      return [
        val => this.$v.branchOffice.fields.state.maxLength || 'El campo Estado debe tener una longitud máxima de 50 caracteres.'
      ]
    },
    branchOfficeColonyRules (val) {
      return [
        val => this.$v.branchOffice.fields.colony.maxLength || 'El campo Colonia debe tener una longitud máxima de 50 caracteres.',
        val => this.$v.branchOffice.fields.colony.required || 'El campo Colonia es requerido.'
      ]
    },
    branchOfficeMunicipalityRules (val) {
      return [
        val => this.$v.branchOffice.fields.municipality.maxLength || 'El campo Municipio debe tener una longitud máxima de 50 caracteres.'
      ]
    },
    branchOfficeZipCodeRules (val) {
      return [
        val => this.$v.branchOffice.fields.zip_code.required || 'El campo CP es requerido.',
        val => this.$v.branchOffice.fields.zip_code.maxLength || 'El campo CP no debe exceder los 5 dígitos.'
      ]
    },
    branchOfficePhoneNumberRules (val) {
      return [
        val => this.$v.branchOffice.fields.phone_number.required || 'El campo Teléfono es requerido.',
        val => this.$v.branchOffice.fields.phone_number.minLength || 'El campo Teléfono debe tener una longitud de 10 caracteres.'
        // val => this.$v.branchOffice.fields.phone_number.maxLength || 'El campo Teléfono debe tener una longitud máxima de 10 caracteres.'
      ]
    },
    branchOfficesuburbIdRules (val) {
      return [
        val => (this.$v.branchOffice.fields.suburb_id.required) || 'El campo Colonia es requerido.'
      ]
    },
    branchOfficesuburbRules (val) {
      return [
        val => (this.$v.branchOffice.fields.suburb.required) || 'El campo Colonia es requerido.'
      ]
    },
    branchOfficebetween_streetRules (val) {
      return [
        val => (this.$v.branchOffice.fields.between_street.required) || 'El campo Entre calles es requerido.'
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
  mounted () {
    this.getBranchesList()
    this.getSellers()
    this.getChannels()
  },
  created () {
    this.$q.loading.show()
    this.getCurrencies()
    this.fetchFromServer()
  },
  /* beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(4) && !this.$store.getters['users/roles'].includes(20)) {
      this.$router.push('/')
    }
  }, */
  beforeRouteEnter (to, from, next) {
    next(vm => {
      const propiedades = vm.$store.getters['users/rol']
      console.log(propiedades)
      if (propiedades === 1 || propiedades === 3 || propiedades === 7 || propiedades === 2 || propiedades === 20 || propiedades === 4 || propiedades === 27 || propiedades === 17 || propiedades === 22 || propiedades === 28 || propiedades === 29) {
        next()
      } else {
        next('/')
      }
    })
  },
  methods: {
    async fetchFromServer () {
      // this.getEstados()
      const id = this.$route.params.id
      api.get(`/customers/${id}`).then(async ({ data }) => {
        if (!data.customer) {
          this.$router.push('/customers')
        } else {
          this.customer.fields = data.customer
          const suburbId = data.customer.suburb_id
          const postalCode = data.customer.postal_code_id
          this.customer.fields.municipio_id = null
          // await this.getPostalCodes(data.customer.postal_code_id)
          this.customer.fields.postal_code_id = postalCode
          // await this.getSuburbsByPostalCodeCustomer(postalCode)
          this.customer.fields.suburb_id = suburbId === null ? 0 : suburbId
          if (!data.customer.active) {
            this.customer.fields.active = { label: 'INACTIVO', value: false }
          } else {
            this.customer.fields.active = { label: 'ACTIVO', value: true }
          }
          api.get(`/customer-branch-offices/customer/${id}`).then(({ data }) => {
            this.branchOffices = data.CustomerBranchOffices
            api.get(`/customer-contacts/customer/${this.$route.params.id}`).then(({ data }) => {
              this.contactsList = data.contacts
              api.get('/invoices/formaPagoOptions').then(({ data }) => {
                this.formaPagoOptions = data.options
                api.get('/invoices/usoCFDIOptions').then(({ data }) => {
                  this.usoCFDIOptions = data.options
                  api.get(`/customer-tax-companies/customer/${id}`).then(({ data }) => {
                    this.fiscales = data.customerTaxCompanies
                  })
                })
              })
            })
            this.$q.loading.hide()
          })
        }
        this.termSelected()
      })
    },
    getEstados () {
      this.selectEstados = []
      api.get('/branch-offices/states').then(({ data }) => {
        this.selectEstados = data.options
        // console.log(data)
      }).catch(error => error)
    },
    isNumber (evt, input) {
      switch (input) {
        case 'codigo_postal':
          this.fiscal.fields.lugar_expedicion = this.fiscal.fields.lugar_expedicion.replace(/[^0-9.]/g, '')
          this.$v.fiscal.fields.lugar_expedicion.$touch()
          break
        default:
          break
      }
    },
    async getPostalCodes (code = '') {
      code = code === '' ? '0' : code
      await api.get(`/branch-offices/postal_codes/${code}/${this.customer.fields.municipio_id}`).then(({ data }) => {
        this.postal_codes = data.options
      }).catch(error => error)
    },
    async getPostalCodesBranchoffice (code = '') {
      code = code === '' ? '0' : code
      await api.get(`/branch-offices/postal_codes/${code}/${this.branchOffice.fields.municipio_id}`).then(({ data }) => {
        this.postal_codes_options = data.options
      }).catch(error => error)
    },
    getChannels () {
      api.get('/channels/options').then(({ data }) => {
        this.channelOptions = data.options
        this.$q.loading.hide()
      })
    },
    termSelected () {
      if (this.customer.fields.payment_method === 'CREDITO') {
        this.enableFields = true
      } else {
        this.enableFields = false
        this.customer.fields.credit_days = null
        this.customer.fields.credit_limit = null
      }
      console.log(this.enableFields)
    },
    getBranchesList () {
      this.$q.loading.show()
      api.get('/branch-offices/options').then(data => {
        this.$q.loading.hide()
        this.branchesList = data.data.options
      })
    },
    getSellers () {
      api.get('/users/getSeller').then(({ data }) => {
        this.sellerOptions = data.options
        this.$q.loading.hide()
      })
    },
    getCurrencies () {
      api.get('/currencies').then(({ data }) => {
        this.currencyOptions = data.currencies.reduce((opt, val) => {
          opt.push({ label: val.name, value: val.id })
          return opt
        }, [])
        this.$q.loading.hide()
      })
    },
    updateCustomer () {
      this.$v.customer.fields.$reset()
      this.$v.customer.fields.$touch()
      if (this.$v.customer.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      } else {
        this.$q.loading.show()
        const params = { ...this.customer.fields }
        params.active = params.active.value
        // if (params.priceList.value) {
        //   params.price_list = params.priceList.value
        // }
        console.log(params)
        api.put(`/customers/${params.id}`, params).then(({ data }) => {
          this.$q.notify({
            message: data.message.content,
            position: 'top',
            color: (data.result ? 'positive' : 'warning')
          })
          if (data.result) {
            this.$q.loading.hide()
          } else {
            this.$q.loading.hide()
          }
        })
      }
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
      params.customer_id = this.$route.params.id
      params.send_invoice = params.send_invoice.value
      console.log(params)
      api.post('/customer-contacts', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.clearContactsInputs()
          api.get(`/customer-contacts/customer/${this.$route.params.id}`).then(({ data }) => {
            this.contactsList = data.contacts
            this.$q.loading.hide()
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    addRequirement () {
      this.$q.loading.show()
      const params = { ...this.document.fields }
      params.customer_id = this.$route.params.id
      api.put(`/customers/requirement/${params.customer_id}`, params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
        } else {
          this.$q.loading.hide()
        }
      })
    },
    clearContactsInputs () {
      this.contacts.fields.name = null
      this.contacts.fields.phone = null
      this.contacts.fields.tel = null
      this.contacts.fields.email = null
      this.contacts.fields.send_invoice = null
      this.contacts.fields.apartment = null
    },
    addBranchOffice () {
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
      params.customer_id = this.$route.params.id
      api.post('/customer-branch-offices', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.clearBranchOffice()
          api.get(`/customer-branch-offices/customer/${this.$route.params.id}`).then(({ data }) => {
            this.branchOffices = data.CustomerBranchOffices
            this.$q.loading.hide()
          })
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
      params.forma_pago = this.fiscal.fields.forma_pago.value
      params.uso_cfdi = this.fiscal.fields.uso_cfdi.value
      params.customer_id = this.$route.params.id
      api.post('/customer-tax-companies', params).then(({ data }) => {
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
          this.fiscal.fields.rfc_banco = null
          this.fiscal.fields.cuenta = null
          this.fiscal.fields.banco = null
          this.fiscal.fields.regimen_fiscal = null
          this.fiscal.fields.immex = false
          this.$v.fiscal.fields.$reset()
          api.get(`/customer-tax-companies/customer/${this.$route.params.id}`).then(({ data }) => {
            this.fiscales = data.customerTaxCompanies
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
      params.customer_id = this.$route.params.id
      params.send_invoice = params.send_invoice.value
      api.put(`/customer-contacts/${params.id}`, params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.clearContactsInputs()
          api.get(`/customer-contacts/customer/${this.$route.params.id}`).then(({ data }) => {
            this.contactsList = data.contacts
            this.$q.loading.hide()
          })
        } else {
          this.$q.loading.hide()
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
      params.customer_id = this.$route.params.id
      console.log(params)
      api.put(`/customer-branch-offices/${params.id}`, params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.clearBranchOffice()
          api.get(`/customer-branch-offices/customer/${this.$route.params.id}`).then(({ data }) => {
            this.branchOffices = data.CustomerBranchOffices
            this.$q.loading.hide()
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    clearBranchOffice () {
      this.branchOffice.fields.id = null
      this.branchOffice.fields.name = null
      this.branchOffice.fields.street = null
      this.branchOffice.fields.outdoor_number = null
      this.branchOffice.fields.int_number = null
      this.branchOffice.fields.zip_code = null
      this.branchOffice.fields.phone_number = null
      this.branchOffice.fields.city = null
      this.branchOffice.fields.municipality = null
      this.branchOffice.fields.colony = null
      this.branchOffice.fields.state = null
      this.$v.branchOffice.fields.$reset()
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
      params.forma_pago = (typeof this.fiscal.fields.forma_pago.value === 'undefined') ? this.fiscal.fields.forma_pago : this.fiscal.fields.forma_pago.value
      params.uso_cfdi = (typeof this.fiscal.fields.uso_cfdi.value === 'undefined') ? this.fiscal.fields.uso_cfdi : this.fiscal.fields.uso_cfdi.value
      params.customer_id = this.$route.params.id
      api.put(`/customer-tax-companies/${params.id}`, params).then(({ data }) => {
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
          this.fiscal.fields.rfc_banco = null
          this.fiscal.fields.cuenta = null
          this.fiscal.fields.banco = null
          this.fiscal.fields.regimen_fiscal = null
          this.fiscal.fields.immex = false
          this.$v.fiscal.fields.$reset()
          api.get(`/customer-tax-companies/customer/${this.$route.params.id}`).then(({ data }) => {
            this.fiscales = data.customerTaxCompanies
            this.$q.loading.hide()
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    cancelEditFormula () {
      this.branchOffice.fields.id = null
      this.branchOffice.fields.name = null
      this.branchOffice.fields.street = null
      this.branchOffice.fields.outdoor_number = null
      this.branchOffice.fields.phone_number = null
      this.branchOffice.fields.int_number = null
      this.branchOffice.fields.zip_code = null
      this.branchOffice.fields.state = null
      this.branchOffice.fields.municipality = null
      this.branchOffice.fields.city = null
      this.branchOffice.fields.colony = null
    },
    cancelEditContact () {
      this.contacts.fields.id = null
      this.contacts.fields.name = null
      this.contacts.fields.phone = null
      this.contacts.fields.tel = null
      this.contacts.fields.email = null
      this.contacts.fields.apartment = null
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
      this.fiscal.fields.regimen_fiscal = null
    },
    async editSelectedRow (branchOffice) {
      this.clearBranchOffice()
      this.branchOffice.fields.id = branchOffice.id
      this.branchOffice.fields.name = branchOffice.name
      this.branchOffice.fields.street = branchOffice.street
      this.branchOffice.fields.outdoor_number = branchOffice.outdoor_number
      this.branchOffice.fields.phone_number = branchOffice.phone_number
      this.branchOffice.fields.int_number = branchOffice.int_number
      this.branchOffice.fields.zip_code = branchOffice.zip_code
      this.branchOffice.fields.city = branchOffice.city
      this.branchOffice.fields.state = branchOffice.state
      this.branchOffice.fields.municipality = branchOffice.municipality
      this.branchOffice.fields.colony = branchOffice.colony
    },
    editSelectedRowContacts (contact) {
      this.contacts.fields.id = contact.id
      this.contacts.fields.name = contact.name
      this.contacts.fields.phone = contact.phone
      this.contacts.fields.tel = contact.tel
      this.contacts.fields.email = contact.email
      this.contacts.fields.send_invoice = { label: contact.send_invoice, value: contact.send_invoice }
      this.contacts.fields.apartment = contact.apartment
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
      this.fiscal.fields.rfc_banco = fiscal.rfc_banco
      this.fiscal.fields.cuenta = fiscal.cuenta
      this.fiscal.fields.banco = fiscal.banco
      this.fiscal.fields.regimen_fiscal = fiscal.regimen_fiscal
      this.fiscal.fields.immex = fiscal.immex
    },
    deleteSelectedRow (branchOfficeId) {
      this.$q.loading.show()
      api.delete(`/customer-branch-offices/${branchOfficeId}`).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          api.get(`/customer-branch-offices/customer/${this.$route.params.id}`).then(({ data }) => {
            this.branchOffices = data.CustomerBranchOffices
            this.$q.loading.hide()
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    deleteSelectedRowContact (contactId) {
      this.$q.loading.show()
      api.delete(`/customer-contacts/${contactId}`).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          api.get(`/customer-contacts/customer/${this.$route.params.id}`).then(({ data }) => {
            this.contactsList = data.contacts
            this.$q.loading.hide()
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    deleteSelectedRowFiscal (fiscal) {
      this.$q.loading.show()
      api.delete(`/customer-tax-companies/${fiscal}`).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          api.get(`/customer-tax-companies/customer/${this.$route.params.id}`).then(({ data }) => {
            this.fiscales = data.customerTaxCompanies
            this.$q.loading.hide()
          })
        } else {
          this.$q.loading.hide()
        }
      })
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
      this.suburbs = []
      this.branchOffice.fields.suburb_id = null
      this.branchOffice.fields.municipio_id = null
      this.branchOffice.fields.estado_id = null
      this.selectMunicipio = []
      if (id !== null) {
        this.branchOffice.fields.estado_id = this.postal_codes_options.filter(v => v.value === id)[0].state_id
        this.options2 = this.selectEstados.filter(v => v.value === this.branchOffice.fields.estado_id)
        await this.getMunicipiosbyEstado(this.branchOffice.fields.estado_id)
        this.branchOffice.fields.municipio_id = this.postal_codes_options.filter(v => v.value === id)[0].municipality_id
        this.options = this.selectMunicipio.filter(v => v.value === this.branchOffice.fields.municipio_id)
        await this.getcities(this.branchOffice.fields.estado_id)
        this.branchOffice.fields.city_id = this.postal_codes_options.filter(v => v.value === id)[0].city_id
        this.cityOptions = this.cities.filter(v => v.value === this.branchOffice.fields.city_id)
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
        await this.getPostalCodesBranchoffice(needle)
      })
    },
    filterSuburbs (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.suburbs_options = this.suburbs.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    filterPostalCodesCustomer (val, update, abort) {
      update(async () => {
        const needle = val.toLowerCase()
        await this.getPostalCodes(needle)
      })
    },
    async getMunicipiosbyEstadoCustomer (id, postalReset = false) {
      this.customer.fields.postal_code_id = postalReset ? null : this.customer.fields.postal_code_id
      this.customer.fields.suburb_id = postalReset ? null : this.customer.fields.suburb_id
      this.suburbsCustomer = []
      this.customer.fields.municipio_id = null
      this.customer.fields.city_id = null
      this.selectMunicipioCustomer = []
      this.getcities(id)
      await api.get(`/branch-offices/municipalities/${id}/${this.customer.fields.postal_code_id}`).then(({ data }) => {
        this.selectMunicipioCustomer = data.options
      }).catch(error => error)
    },
    async getcitiesCustomer (id) {
      this.citiesCustomer = []
      await api.get(`/branch-offices/cities/${id}/${this.customer.fields.postal_code_id}`).then(({ data }) => {
        this.citiesCustomer = data.options
      }).catch(error => error)
    },
    async getSuburbsByPostalCodeCustomer (id) {
      this.suburbsCustomer = []
      this.customer.fields.suburb_id = null
      this.customer.fields.municipio_id = null
      this.customer.fields.estado_id = null
      this.selectMunicipioCustomer = []
      if (id !== null) {
        this.customer.fields.estado_id = this.postal_codes.filter(v => v.value === id)[0].state_id
        this.selectEstadosCustomer = this.selectEstados.filter(v => v.value === this.customer.fields.estado_id)
        await this.getMunicipiosbyEstadoCustomer(this.customer.fields.estado_id)
        this.customer.fields.municipio_id = this.postal_codes.filter(v => v.value === id)[0].municipality_id
        this.selectMunicipioCustomerOptions = this.selectMunicipioCustomer.filter(v => v.value === this.customer.fields.municipio_id)
        await this.getcitiesCustomer(this.customer.fields.estado_id)
        this.customer.fields.city_id = this.postal_codes.filter(v => v.value === id)[0].city_id
        this.cityOptionsCustomer = this.citiesCustomer.filter(v => v.value === this.customer.fields.city_id)
        api.get(`/branch-offices/suburbs/${id}`).then(({ data }) => {
          this.suburbsCustomer = data.options
          this.suburbsCustomer.push({ value: 0, label: 'OTRA' })
          this.suburbs_options_customer = data.options
        }).catch(error => error)
      }
    },
    filterMunicipioCustomer (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.selectMunicipioCustomerOptions = this.selectMunicipioCustomer.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    filterEstadoCustomer (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.selectEstadosCustomer = this.selectEstados.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    filterSuburbsCustomer (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.suburbs_options_customer = this.suburbsCustomer.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    }
  }
}
</script>

<style>
</style>

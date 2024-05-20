<template>
  <q-page>
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-4">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Embarques" to="/trips" />
              <q-breadcrumbs-el label="Editar Embarque" v-text= "trip.fields.folio"/>
            </q-breadcrumbs>
          </div>
        </div>
        <div class="col-sm-8 pull-right">
          <div class="q-pa-sm q-gutter-sm">
            <q-btn :icon="iconTimbrado[trip.fields.status_timbrado]" disable :color="colorTimbrado[trip.fields.status_timbrado]" :label="statusTimbrado[trip.fields.status_timbrado]" v-if="trip.fields.status_timbrado !== 0">
              </q-btn>
            <q-tooltip v-if="trip.fields.status_timbrado === 6">{{trip.fields.message}}</q-tooltip><q-tooltip v-if="trip.fields.status_timbrado === 7">{{trip.fields.message_cancelacion}}</q-tooltip>
            <q-btn color="green-6"  icon="fas fa-certificate" label="TIMBRAR" @click="timbrar()" v-if="trip.fields.status_timbrado === 0 || trip.fields.status_timbrado === 6"/>
            <q-btn disabled v-if="trip.fields.status_timbrado === 1" color="orange" class="pull-right">{{this.trip.fields.uuid}}</q-btn>
            <q-btn v-if="trip.fields.status_timbrado === 1" color="green-8" icon="fas fa-file-excel" style="margin-left: 10px;" label="XML" @click="getCFDIInvoice(trip.fields.id_request)" :loading="loadingFiscal"></q-btn>
            <q-btn v-if="trip.fields.status_timbrado === 1" style="margin-left: 10px;" @click="getPdfInvoice(trip.fields.id_request,trip.fields.pdf)" color="brown" icon="fas fa-file-pdf" label="PDF" :loading="loadingFiscal"></q-btn>
            <q-btn v-if="trip.fields.status_timbrado === 1 || trip.fields.status_timbrado === 7" style="margin-left: 10px;" @click="cancelarTimbrado = true"  color="red-9" icon="cancel" label="Cancelar" :loading="loadingFiscal"></q-btn>
          </div>
        </div>
        <div class="col-sm-3 pull-right" v-if="trip.fields.status === 'ENTREGADO'">
          <div class="q-pa-sm q-gutter-sm">
            <q-btn :color="colorButon"  icon="offline_bolt" label="CERRADO" @click="changeStatus('CERRADO')"/>
          </div>
        </div>
      </div>
      <!-- <div class="row q-col-gutter-xs">
          <div class="col-xs-12 col-sm-8 offset-sm-4 q-col-gutter-xs pull-right">
            <div>
              <q-btn color="green-6"  icon="fas fa-certificate" label="TIMBRAR" @click="timbrar()" v-if="trip.fields.status_timbrado === 0 || trip.fields.status_timbrado === 6"/>
              <q-btn disabled v-if="trip.fields.status_timbrado === 1" color="orange" class="pull-right">{{this.trip.fields.uuid}}</q-btn>
              <q-btn v-if="trip.fields.status_timbrado === 1" color="green-8" icon="fas fa-file-excel" style="margin-left: 10px;" label="XML" @click="getCFDIInvoice(trip.fields.id_request)" :loading="loadingFiscal"></q-btn>
              <q-btn v-if="trip.fields.status_timbrado === 1" style="margin-left: 10px;" @click="getPdfInvoice(trip.fields.id_request,trip.fields.pdf)" color="brown" icon="fas fa-file-pdf" label="PDF" :loading="loadingFiscal"></q-btn>
              <q-btn v-if="trip.fields.status_timbrado === 1 || trip.fields.status_timbrado === 7" style="margin-left: 10px;" @click="cancelarTimbrado = true"  color="red-9" icon="cancel" label="Cancelar" :loading="loadingFiscal"></q-btn>
            </div>
          </div>
        </div> -->
    </div>

    <div class="q-pa-md bg-grey-3">
      <div class="row bg-white border-panel">
        <div class="col q-pa-md">
          <div class="row q-mb-sm" style="visibility: hidden;">
            <div class="col-sm-1 offset-11 pull-right">
              <q-btn color="primary" label="Editar" />
            </div>
          </div>

          <div class="row q-col-gutter-xs">
            <div class="col-xs-12 col-sm-2">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                disable
                v-model="trip.fields.folio"
                label="Folio"
                emit-value
                map-options
                >
                <template v-slot:prepend>
                  <q-icon name="format_list_numbered" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-2 col-md-2 col-lg-2">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="trip.fields.invoice_id"
                :error="$v.trip.fields.invoice_id.$error"
                label="Remision / Factura"
                :rules="invoiceRules"
                :options="invoicesOptionsFilter"
                use-input
                hide-selected
                fill-input
                input-debounce="0"
                emit-value
                map-options
                >
                <template v-slot:prepend>
                  <q-icon name="fact_check" />
                </template>
                <template v-slot:no-option>
                  <q-item>
                    <q-item-section class="text-grey">
                      No hay Resultados
                    </q-item-section>
                  </q-item>
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-2 text-center">
                <q-select color="dark"
                bg-color="secondary"
                filled
                :rules="dateRules"
                v-model="trip.fields.date"
                :error="$v.trip.fields.date.$error"
                mask="DD/MM/YYYY HH:mm:ss"
                label="Fecha salida">
                <template v-slot:prepend>
                    <q-icon name="event"></q-icon>
                </template>
                <q-popup-proxy
                ref="date_ref"
                transition-show="scale"
                transition-hide="scale">
                    <div class="row">
                      <div class="col-sm-6">
                        <q-date
                          color="secondary"
                          text-color="white"
                          v-model="trip.fields.date"
                          mask="DD/MM/YYYY HH:mm:ss"
                        />
                      </div>
                      <div class="col-sm-6">
                        <q-time
                          color="secondary"
                          text-color="white"
                          v-model="trip.fields.date"
                          mask="DD/MM/YYYY HH:mm:ss"
                          @input="() => $refs.date_ref.hide()"
                          now-btn
                        />
                      </div>
                    </div>
                </q-popup-proxy>
                </q-select>
            </div>
            <!-- <div class="col-xs-12 col-sm-4">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="trip.fields.destiny"
                :error="$v.trip.fields.destiny.$error"
                label="Destino"
                :rules="destinyRules"
                :options="destinysOptionsFilter"
                use-input
                hide-selected
                fill-input
                input-debounce="0"
                @filter="filterDestinys"
                emit-value
                map-options
                >
                <template v-slot:prepend>
                  <q-icon name="fmd_good" />
                </template>
                <template v-slot:no-option>
                  <q-item>
                    <q-item-section class="text-grey">
                      No hay Resultados
                    </q-item-section>
                  </q-item>
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-5">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="trip.fields.driver"
                :error="$v.trip.fields.driver.$error"
                label="Chofer"
                :rules="driverRules"
                :options="driversOptionsFilter"
                use-input
                hide-selected
                fill-input
                input-debounce="0"
                @filter="filterDrivers"
                emit-value
                map-options
                >
                <template v-slot:prepend>
                  <q-icon name="drive_eta" />
                </template>
                <template v-slot:no-option>
                  <q-item>
                    <q-item-section class="text-grey">
                      No hay Resultados
                    </q-item-section>
                  </q-item>
                </template>
              </q-select>
            </div> -->
            <div class="col-xs-12 col-sm-3">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="trip.fields.vehicle_id"
                :error="$v.trip.fields.vehicle_id.$error"
                label="Número de unidad"
                :rules="vehicle_idRules"
                :options="economicNumberOptions"
                @input="getVehicleData()"
                emit-value
                map-options
                >
                <template v-slot:prepend>
                  <q-icon name="tag" />
                </template>
                <template v-slot:no-option>
                  <q-item>
                    <q-item-section class="text-grey">
                      No hay Resultados
                    </q-item-section>
                  </q-item>
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                disable
                v-model="vehicle.fields.type"
                label="Tipo de vehículo"
                emit-value
                map-options
                >
                <template v-slot:prepend>
                  <q-icon name="format_list_numbered" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-2">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                disable
                v-model="vehicle.fields.brand"
                label="Marca de vehículo"
                emit-value
                map-options
                >
                <template v-slot:prepend>
                  <q-icon name="book" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-2">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                disable
                v-model="vehicle.fields.model"
                label="Modelo de vehiculo"
                emit-value
                map-options
                >
                <template v-slot:prepend>
                  <q-icon name="format_list_bulleted" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-2">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                disable
                v-model="vehicle.fields.plate"
                label="Placas"
                emit-value
                map-options
                >
                <template v-slot:prepend>
                  <q-icon name="format_list_bulleted" />
                </template>
              </q-input>
            </div>
          </div>

          <div class="row q-mb-sm q-mt-md" v-if="trip.fields.status_timbrado === 0 || trip.fields.status_timbrado === 6">
            <div class="col-xs-12 col-sm-2 offset-sm-10 pull-right">
              <q-btn color="positive" v-if="trip.fields.status !== 'CERRADO' && trip.fields.status !== 'ENTREGADO' && trip.fields.status !== 'EN TRÁNSITO'" icon="save" label="Actualizar" @click="editTrip()" />
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="q-pa-md bg-grey-3">
      <div class="q-gutter-y-md" style="width: 100%">
        <q-card>
          <q-tabs
            v-model="tab"
            dense
            class="text-grey"
            active-color="primary"
            indicator-color="primary"
            align="justify"
            narrow-indicator
          >
            <q-tab name="destinitions" icon="fas fa-warehouse" label="DESTINOS" />
            <q-tab name="operators" icon="person" label="OPERADORES" />
            <!-- <q-tab name="expenses" icon="payments" label="GASTOS" v-if="trip.fields.status !== 'NUEVO' && trip.fields.status !== 'EN TRÁNSITO'"/> -->
          </q-tabs>
          <q-tab-panels v-model="tab" animated class="">
            <q-tab-panel name="destinitions">
              <div class="row q-col-gutter-xs" v-if="trip.fields.status_timbrado === 0 || trip.fields.status_timbrado === 6">
                <div class="col-xs-2 col-sm-3 col-md-3 col-lg-3">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="destination.fields.street"
                    :error="$v.destination.fields.street.$error"
                    label="Calle"
                    :rules="streetRules"
                    @input="v => { destination.fields.street = v.toUpperCase() }"
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
                    v-model="destination.fields.outdoor_number"
                    :error="$v.destination.fields.outdoor_number.$error"
                    label="Número exterior"
                    :rules="outdoor_numberRules"
                    @input="v => { destination.fields.outdoor_number = v.toUpperCase() }"
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
                    v-model="destination.fields.indoor_number"
                    label="Número interior"
                    @input="v => { destination.fields.indoor_number = v.toUpperCase() }"
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
                    v-model="destination.fields.between_street"
                    :error="$v.destination.fields.between_street.$error"
                    label="Entre calles"
                    :rules="between_streetRules"
                    @input="v => { destination.fields.between_street = v.toUpperCase() }"
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
                    v-model="destination.fields.postal_code_id"
                    :rules="cpRules"
                    :error="$v.destination.fields.postal_code_id.$error"
                    :options="postal_codes"
                    label="Codigo Postal"
                    use-input
                    emit-value
                    @filter="filterPostalCodes"
                    @input="getSuburbsByPostalCode(destination.fields.postal_code_id)"
                    map-options
                  >
                    <template v-slot:prepend>
                      <q-icon name="fas fa-mail-bulk" />
                    </template>
                  </q-select>
                </div>
                <div class="col-xs-12 col-sm-2 col-md-2 col-lg-2">
                  <q-select
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="destination.fields.suburb_id"
                    :options="suburbs_options"
                    :rules="suburbRules"
                    :error="$v.destination.fields.suburb_id.$error"
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
                <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">
                  <q-select
                  filled
                  class="uppcase"
                  color="dark"
                  bg-color="secondary"
                  v-model="destination.fields.municipio_id"
                  label="Municipio"
                  :options="MunicipioOptions"
                  use-input
                  hide-selected
                  fill-input
                  input-debounce="0"
                  emit-value
                  @filter="filterMunicipio"
                  @input="getPostalCodes()"
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
                  v-model="destination.fields.city_id"
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
                  v-model="destination.fields.estado_id"
                  label="ESTADO"
                  :options="estadoOptions"
                  @input="getMunicipiosbyEstado(destination.fields.estado_id, true)"
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
                <div class="col-xs-2 col-sm-2 col-md-2 col-ld-2">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="destination.fields.distance"
                    :error="$v.destination.fields.distance.$error"
                    :rules="distanceRules"
                    label="Distancia recorrida (KM)"
                    @input="v => { destination.fields.distance = v.toUpperCase() }"
                  >
                    <template v-slot:prepend>
                      <q-icon name="fas fa-hashtag" />
                    </template>
                  </q-input>
                </div>
                <div class="col-xs-12 col-sm-2 text-center">
                    <q-select color="dark"
                    bg-color="secondary"
                    filled
                    :rules="destinationDateRules"
                    v-model="destination.fields.date"
                    :error="$v.destination.fields.date.$error"
                    mask="DD/MM/YYYY HH:mm:ss"
                    label="Fecha de entrega">
                    <template v-slot:prepend>
                        <q-icon name="event"></q-icon>
                    </template>
                    <q-popup-proxy
                    ref="date_ref"
                    transition-show="scale"
                    transition-hide="scale">
                        <div class="row">
                          <div class="col-sm-6">
                            <q-date
                              color="secondary"
                              text-color="white"
                              v-model="destination.fields.date"
                              mask="DD/MM/YYYY HH:mm:ss"
                            />
                          </div>
                          <div class="col-sm-6">
                            <q-time
                              color="secondary"
                              text-color="white"
                              v-model="destination.fields.date"
                              mask="DD/MM/YYYY HH:mm:ss"
                              @input="() => $refs.date_ref.hide()"
                              now-btn
                            />
                          </div>
                        </div>
                    </q-popup-proxy>
                    </q-select>
                </div>

              </div>
              <div class="row q-col-gutter-xs" v-if="trip.fields.status_timbrado === 0 || trip.fields.status_timbrado === 6 ">
                <div v-if="!editFlag" class="col-xs-12 col-sm-2 offset-sm-10 pull-right">
                  <q-btn color="positive" v-if="trip.fields.status !== 'CERRADO' && trip.fields.status !== 'ENTREGADO' && trip.fields.status !== 'EN TRÁNSITO'" icon="add" label="Agregar" @click="createDestination()" />
                </div>
                <div v-if="editFlag" class="col-xs-12 col-sm-12 pull-right">
                  <q-btn color="negative" style="margin-right: 7px" icon="cancel" label="Cancelar" @click="cancelEditDestination()" />
                  <q-btn color="positive" icon="add" label="Actualizar" @click="updateDestination()" />
                </div>
              </div>
              <div style="margin-top: 10px" class="row bg-white border-panel">
                <div class="col q-pa-md">
                  <q-table
                    flat
                    bordered
                    :data="destinations"
                    :columns="destinationColumns"
                    row-key="code"
                  >
                    <template v-slot:body="props">
                      <q-tr :props="props">
                        <q-td key="street" style="text-align: center;" :props="props">{{ props.row.street + ', #' + props.row.outdoor_number + (props.row.indoor_number !== ''? ', int: ' + props.row.indoor_number : '') }}</q-td>
                        <q-td key="postal_code" style="text-align: left;" :props="props">{{ props.row.postal_code }}</q-td>
                        <q-td key="suburb" style="text-align: center;" :props="props">{{ props.row.suburb }}</q-td>
                        <q-td key="municipality" style="text-align: center;" :props="props">{{ props.row.municipality }}</q-td>
                        <q-td key="location" style="text-align: center;" :props="props">{{ props.row.location }}</q-td>
                        <q-td key="state" style="text-align: center;" :props="props">{{ props.row.state }}</q-td>
                        <q-td key="distance" style="text-align: center;" :props="props">{{ props.row.distance }}</q-td>

                        <q-td key="date" style="text-align: center;" :props="props">{{ props.row.date }}</q-td>
                        <q-td key="actions" style="text-align: center;" :props="props">
                          <q-btn v-if="trip.fields.status_timbrado === 0" color="primary" icon="fas fa-edit" flat @click.native="editDestination(props.row)" size="10px">
                            <q-tooltip content-class="bg-primary">Editar</q-tooltip>
                          </q-btn>
                          <q-btn v-if="trip.fields.status_timbrado === 0" color="red" icon="fas fa-trash-alt" flat @click.native="deleteDestination(props.row.id)" size="10px">
                            <q-tooltip content-class="bg-red">Eliminar</q-tooltip>
                          </q-btn>
                          <q-btn color="primary" v-if="trip.fields.status !== 'NUEVO'" icon="visibility" flat @click.native="editSelectedRowShippings(props.row.id)" size="10px">
                            <q-tooltip content-class="bg-primary">Ver envío</q-tooltip>
                          </q-btn>
                          <q-btn :color="props.row.file ? 'positive':'negative'" v-if="trip.fields.status === 'ENTREGADO'" icon="backup" flat @click.native="uploadFileShipping(props.row.id)" size="10px">
                            <q-tooltip content-class="bg-primary">Capturar archivo</q-tooltip>
                          </q-btn>
                          <q-btn color="positive" v-if="props.row.file" icon="find_in_page" flat @click.native="viewFileShipping(props.row)" size="10px">
                            <q-tooltip content-class="bg-primary">Ver archivo</q-tooltip>
                          </q-btn>
                        </q-td>
                      </q-tr>
                    </template>
                  </q-table>
                </div>
              </div>
            </q-tab-panel>

            <q-tab-panel name="operators">
              <div class="row q-col-gutter-xs" v-if="trip.fields.status_timbrado === 0 || trip.fields.status_timbrado === 6 ">
                <div class="col-xs-12 col-sm-3">
                  <q-select
                  filled
                  class="uppcase"
                  color="dark"
                  bg-color="secondary"
                  v-model="operator_id"
                  :error="$v.operator_id.$error"
                  :rules="operator_idRules"
                  label="Operador"
                  :options="operatorOptions"
                  use-input
                  hide-selected
                  fill-input
                  input-debounce="0"
                  emit-value
                  @filter="filterOperator"
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
                <div class="col-xs-12 col-sm-2 offset-sm-7 pull-right">
                  <q-btn color="positive" v-if="trip.fields.status !== 'CERRADO' && trip.fields.status !== 'ENTREGADO' && trip.fields.status !== 'EN TRÁNSITO'" icon="add" label="Agregar" @click="addOperator()" />
                </div>
              </div>
              <div style="margin-top: 10px" class="row bg-white border-panel">
                <div class="col q-pa-md">
                  <q-table
                    flat
                    bordered
                    :data="operatorsData"
                    :columns="opertorColumns"
                    row-key="code"
                  >
                    <template v-slot:body="props">
                      <q-tr :props="props">
                        <q-td key="name" style="text-align: center;" :props="props">{{ props.row.name }}</q-td>
                        <q-td key="rfc" style="text-align: left;" :props="props">{{ props.row.rfc }}</q-td>
                        <q-td key="license" style="text-align: center;" :props="props">{{ props.row.license }}</q-td>
                        <q-td key="actions" style="text-align: center;" :props="props">
                          <q-btn v-if="trip.fields.status_timbrado === 0" color="red" icon="fas fa-trash-alt" flat @click.native="deleteOperator(props.row.id)" size="10px">
                            <q-tooltip content-class="bg-red">Eliminar</q-tooltip>
                          </q-btn>
                        </q-td>
                      </q-tr>
                    </template>
                  </q-table>
                </div>
              </div>
            </q-tab-panel>

            <q-tab-panel name="lots">
              <div class="row q-col-gutter-xs">
                <div class="col-xs-12 col-sm-2 offset-sm-10 pull-right">
                  <q-btn color="positive" v-if="trip.fields.status !== 'CERRADO' && trip.fields.status !== 'ENTREGADO' && trip.fields.status !== 'EN TRÁNSITO'" icon="add" label="Agregar" @click="createLot()" />
                </div>
              </div>
              <div style="margin-top: 10px" class="row bg-white border-panel">
                <div class="col q-pa-md">
                  <q-table
                    flat
                    bordered
                    :data="lotData"
                    :columns="lotColumns"
                    row-key="code"
                  >
                    <template v-slot:body="props">
                      <q-tr :props="props">
                        <q-td key="folio" style="text-align: center;" :props="props">{{ props.row.serial }}</q-td>
                        <q-td key="customer" style="text-align: left;" :props="props">{{ props.row.client }}</q-td>
                        <!-- <q-td key="invoice" style="text-align: center;" :props="props">{{ }}</q-td> -->
                        <q-td key="date" style="text-align: center;" :props="props">{{ props.row.date.substr(8, 2) + '/' + props.row.date.substr(5,2) + '/' + props.row.date.substr(0, 4) }}</q-td>
                        <q-td key="actions" style="text-align: center;" :props="props">
                          <q-btn v-if="trip.fields.status !== 'CERRADO' && trip.fields.status !== 'ENTREGADO' && trip.fields.status !== 'EN TRÁNSITO'" color="primary" icon="fas fa-edit" flat @click.native="editSelectedRowShippings(props.row.id)" size="10px">
                            <q-tooltip content-class="bg-primary">Editar</q-tooltip>
                          </q-btn>
                          <q-btn v-if="trip.fields.status !== 'CERRADO' && trip.fields.status !== 'ENTREGADO' && trip.fields.status !== 'EN TRÁNSITO'" color="red" icon="fas fa-trash-alt" flat @click.native="deleteSelectedRowShipping(props.row.id)" size="10px">
                            <q-tooltip content-class="bg-red">Eliminar</q-tooltip>
                          </q-btn>
                          <q-btn color="primary" v-if="trip.fields.status !== 'NUEVO'" icon="visibility" flat @click.native="editSelectedRowShippings(props.row.id)" size="10px">
                            <q-tooltip content-class="bg-primary">Ver envío</q-tooltip>
                          </q-btn>
                          <q-btn :color="props.row.file ? 'positive':'negative'" v-if="trip.fields.status === 'ENTREGADO'" icon="backup" flat @click.native="uploadFileShipping(props.row.id)" size="10px">
                            <q-tooltip content-class="bg-primary">Capturar archivo</q-tooltip>
                          </q-btn>
                          <q-btn color="positive" v-if="props.row.file" icon="find_in_page" flat @click.native="viewFileShipping(props.row)" size="10px">
                            <q-tooltip content-class="bg-primary">Ver archivo</q-tooltip>
                          </q-btn>
                        </q-td>
                      </q-tr>
                    </template>
                  </q-table>
                </div>
              </div>
            </q-tab-panel>

            <q-tab-panel name="expenses">
              <div class="row q-col-gutter-xs" v-if="trip.fields.status !== 'CERRADO'">
                <div class="col-xs-12 col-sm-4">
                  <q-select
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="expenses.fields.expense_type"
                    :error="$v.expenses.fields.expense_type.$error"
                    label="Tipo de gastos"
                    :rules="expenseTypeRules"
                    :options="expensesOptions"
                    use-input
                    emit-value
                    map-options
                    >
                    <template v-slot:prepend>
                      <q-icon name="shopping_cart" />
                    </template>
                    <template v-slot:no-option>
                      <q-item>
                        <q-item-section class="text-grey">
                          No hay Resultados
                        </q-item-section>
                      </q-item>
                    </template>
                  </q-select>
                </div>
                <div class="col-xs-12 col-sm-4">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="expenses.fields.expense_amount"
                    :rules="expenseAmountRules"
                    :error="$v.expenses.fields.expense_amount.$error"
                    label="Monto"
                    emit-value
                    map-options
                    >
                    <template v-slot:prepend>
                      <q-icon name="attach_money" />
                    </template>
                  </q-input>
                </div>
                <div class="col-xs-12 col-sm-4 text-center">
                  <q-select color="white"
                  bg-color="secondary"
                  filled
                  dark
                  :rules="expenseDateRules"
                  v-model="expenses.fields.expense_date"
                  :error="$v.expenses.fields.expense_date.$error"
                  mask="date"
                  label="Fecha">
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
                            v-model="expenses.fields.expense_date"
                            @input="() => $refs.date_ref.hide()"
                            today-btn>
                            </q-date>
                        </div>
                    </q-popup-proxy>
                  </q-select>
                </div>
                <div v-if="!editFlag" class="col-xs-12 col-sm-2 offset-sm-10 pull-right">
                  <q-btn color="positive" icon="add" label="Crear" @click="createExpense()" />
                </div>
                <div v-if="editFlag" class="col-xs-12 col-sm-12 pull-right">
                  <q-btn color="negative" style="margin-right: 7px" icon="cancel" label="Cancelar" @click="cancelEditExpense()" />
                  <q-btn color="positive" icon="add" label="Actualizar" @click="editExpense()" />
                </div>
              </div>
              <div style="margin-top: 10px" class="row bg-white border-panel">
                <div class="col q-pa-md">
                  <q-table
                    flat
                    bordered
                    :data="expensesData"
                    :columns="expensesColumns"
                    row-key="code"
                  >
                    <template v-slot:body="props">
                      <q-tr :props="props">
                        <q-td key="type" style="text-align: left;" :props="props">{{ props.row.type }}</q-td>
                        <q-td key="amount" style="text-align: center;" :props="props">{{ '$' + currencyFormat(props.row.amount) }}</q-td>
                        <q-td key="date" style="text-align: center;" :props="props">{{ props.row.date.substr(8, 2) + '/' + props.row.date.substr(5,2) + '/' + props.row.date.substr(0, 4) }}</q-td>
                        <q-td key="actions" style="text-align: center;" :props="props">
                          <q-btn color="primary" v-if="trip.fields.status !== 'CERRADO'" icon="fas fa-edit" flat @click.native="editSelectedRowExpenses(props.row.id)" size="10px">
                            <q-tooltip content-class="bg-primary">Editar</q-tooltip>
                          </q-btn>
                          <q-btn :color="props.row.file ? 'positive':'negative'" v-if="trip.fields.status === 'ENTREGADO'" icon="backup" flat @click.native="uploadFileExpense(props.row.id)" size="10px">
                            <q-tooltip content-class="bg-primary">Capturar archivo</q-tooltip>
                          </q-btn>
                          <q-btn color="positive" v-if="props.row.file" icon="find_in_page" flat @click.native="viewFileExpense(props.row)" size="10px">
                            <q-tooltip content-class="bg-primary">Ver archivo</q-tooltip>
                          </q-btn>
                          <q-btn color="red" v-if="trip.fields.status !== 'CERRADO'" icon="fas fa-trash-alt" flat @click.native="deleteSelectedRowExpenses(props.row.id)" size="10px">
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
        </q-card>
      </div>
    </div>

    <q-dialog v-model="fileModalShipping" persistent>
      <q-card>
        <q-card-section class="row">
          <div class="col-xs-12 col-sm-10 text-h6">Archivo: {{ fileShippingCode }}</div>
          <q-btn class="col-xs-12 col-sm-2 pull-right" icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-card-section>
          <q-uploader
            :url="fileShippingUrl"
            method="POST"
            ref="fileShippingRef"
            hide-upload-btn
            @uploaded="afterUploadFile"
          />
        </q-card-section>
        <q-card-section>
          <q-img
            :src="fileUrl"
            spinner-color="white"
          />
        </q-card-section>
        <q-card-actions align="right" class="text-primary">
          <q-btn flat label="Subir archivo" @click="uploadFiles()" />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <q-dialog v-model="fileModalExpense" persistent>
      <q-card>
        <q-card-section class="row">
          <div class="col-xs-12 col-sm-10 text-h6">Archivo: {{ fileExpenseCode }}</div>
          <q-btn class="col-xs-12 col-sm-2 pull-right" icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-card-section>
          <q-uploader
            :url="fileExpenseUrl"
            method="POST"
            ref="fileShippingRef"
            hide-upload-btn
            @uploaded="afterUploadFileExpense"
          />
        </q-card-section>
        <q-card-section>
          <q-img
            :src="fileUrl"
            spinner-color="white"
          />
        </q-card-section>
        <q-card-actions align="right" class="text-primary">
          <q-btn flat label="Subir archivo" @click="uploadFilesExpense()" />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <q-dialog v-model="cancelarTimbrado" persistent>
      <q-card>
        <q-card-section class="bg-primary">
          <div class="row">
            <div class="col-xs-11 col-sm-11 col-md-11 col-lg-11 text-h6" style="color:white;">Cancelar Carta Porte</div>
            <div class="col-xs-1 col-sm-1 col-md-1 col-lg-1 pull-right"><q-btn color="white" flat v-close-popup round dense icon="close" /></div>
          </div>
        </q-card-section>
        <q-card-section class="items-center">
          <span class="row text-h6">¿Está seguro de cancelar esta Carta Porte?</span>
         </q-card-section>
        <q-card-section class="items-center">
          <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <q-select
                  color="dark"
                  bg-color="secondary"
                  filled
                  map-options
                  emit-value
                  v-model="trip.fields.motivo_cancelacion"
                  label="Motivo de cancelacion"
                  :options="CancelacionOptions"
                >
                  <template v-slot:prepend>
                    <q-icon name="description" />
                  </template>
                </q-select>
              </div>
          </div>
        </q-card-section>
        <q-card-section class="items-center" v-if="trip.fields.motivo_cancelacion === '01'">
          <div class="row">
              <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <q-input
                  color="dark"
                  bg-color="secondary"
                  filled
                  v-model="trip.fields.folio_sustituye"
                  :error="$v.trip.fields.folio_sustituye.$error"
                  label="Folio que sustituye"
                  :rules="tripFolioSustituye"
                  @keyup="isNumber($event,'folio_sustituye')"
                >
                  <template v-slot:prepend>
                    <q-icon name="description" />
                  </template>
                </q-input>
              </div>
          </div>
        </q-card-section>
        <q-card-section class="text-primary">
          <div class="row q-col-gutter-xs q-col-gutter-sm q-col-gutter-md row q-col-gutter-xs q-col-gutter-lg">
            <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
              <q-btn flat label="Cancelar" color="white" style="background-color: #f44336;" @click="cancelarTimbrado = false" v-close-popup />
            </div>
            <div align="right" class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
              <q-btn flat label="Confirmar" color="white" style="background-color: #21ba45; text-align: right;" @click="cancelarFactura()" />
            </div>
          </div>
        </q-card-section>
      </q-card>
    </q-dialog>

  </q-page>
</template>

<script>
import api from '../../commons/api.js'
const { required, decimal, maxLength, requiredIf } = require('vuelidate/lib/validators')

export default {
  name: 'EditTrip',
  validations: {
    trip: {
      fields: {
        date: { required },
        vehicle_id: { required },
        invoice_id: { required },
        folio_sustituye: { required: requiredIf(function () { return this.trip.fields.motivo_cancelacion === '01' }) }
      }
    },
    expenses: {
      fields: {
        expense_type: { required },
        expense_amount: { required, decimal },
        expense_date: { required }
      }
    },
    destination: {
      fields: {
        street: { required, maxLength: maxLength(100) },
        postal_code_id: { required },
        suburb_id: { required },
        indoor_number: { },
        outdoor_number: { required },
        between_street: { required },
        distance: { required },
        date: { required }
      }
    },
    operator_id: { required }
  },
  data () {
    return {
      tab: 'destinitions',
      trip: {
        fields: {
          date: null,
          vehicle_id: null,
          invoice_id: null,
          status_timbrado: null,
          id_request: null,
          folio_sustituye: null
        }
      },
      destination: {
        fields: {
          id: null,
          street: null,
          estado_id: null,
          municipio_id: null,
          postal_code_id: null,
          suburb_id: null,
          indoor_number: null,
          outdoor_number: null,
          between_street: null,
          city_id: null,
          distance: null,
          date: null
        }
      },
      vehicle: {
        fields: {
          type: null,
          brand: null,
          model: null,
          plate: null
        }
      },
      expenses: {
        fields: {
          trip_id: Number(this.$route.params.id),
          expense_type: null,
          expense_amount: null,
          expense_date: null,
          id: null
        }
      },
      driversOptions: [],
      destinysOptions: [],
      economicNumberOptions: [],
      driversOptionsFilter: [],
      destinysOptionsFilter: [],
      expensesOptions: [],
      expensesColumns: [
        { name: 'type', align: 'center', label: 'TIPO DE GASTO', field: 'type', sortable: false },
        { name: 'amount', align: 'center', label: 'COSTO', field: 'amount', sortable: false },
        { name: 'date', align: 'center', label: 'FECHA', field: 'date', sortable: false },
        { name: 'actions', align: 'center', label: 'ACCIÓNES', field: 'actions', sortable: false }
      ],
      lotColumns: [
        { name: 'folio', align: 'center', label: 'FOLIO', field: 'folio', sortable: false },
        { name: 'customer', align: 'center', label: 'CLIENTE', field: 'customer', sortable: false },
        // { name: 'invoice', align: 'center', label: 'No. FACTURA', field: 'invoice', sortable: false },
        { name: 'date', align: 'center', label: 'FECHA', field: 'date', sortable: false },
        { name: 'actions', align: 'center', label: 'ACCIÓNES', field: 'actions', sortable: false }
      ],
      destinationColumns: [
        { name: 'street', align: 'center', label: 'CALLE', field: 'street', sortable: false },
        { name: 'postal_code', align: 'center', label: 'CP', field: 'postal_code', sortable: false },
        { name: 'suburb', align: 'center', label: 'COLONIA', field: 'suburb', sortable: false },
        { name: 'municipality', align: 'center', label: 'MUNICIPIO', field: 'municipality', sortable: false },
        { name: 'location', align: 'center', label: 'CIUDAD', field: 'location', sortable: false },
        { name: 'state', align: 'center', label: 'ESTADO', field: 'state', sortable: false },
        { name: 'date', align: 'center', label: 'FECHA DE ENTREGA', field: 'date', sortable: false },
        { name: 'actions', align: 'center', label: 'ACCIÓNES', field: 'actions', sortable: false }
      ],
      opertorColumns: [
        { name: 'name', align: 'center', label: 'NOMBRE', field: 'name', sortable: false },
        { name: 'rfc', align: 'center', label: 'RFC', field: 'rfc', sortable: false },
        { name: 'license', align: 'center', label: 'LICENCIA', field: 'license', sortable: false },
        { name: 'actions', align: 'center', label: 'ACCIÓNES', field: 'actions', sortable: false }
      ],
      statusTimbrado: ['Nuevo', 'Timbrado', 'Cancelado', 'Cancelando', 'Timbrando', 'Cancelando', 'Error', 'Error al cancelar'],
      colorTimbrado: ['blue-6', 'green-6', 'purple-6', 'warning', 'warning', 'warning', 'red-6', 'red-6'],
      iconTimbrado: ['add', 'done', 'cancel', 'fas fa-ellipsis-h', 'fas fa-ellipsis-h', 'fas fa-ellipsis-h', 'cancel', 'cancel'],
      expensesData: [],
      editFlag: false,
      lotData: [],
      colorStatus: null,
      colorButon: null,
      dataFiles: [],
      serverUrl: process.env.API,
      fileModalShipping: false,
      fileUrl: null,
      fileShippingId: null,
      fileShippingCode: null,
      fileModalExpense: null,
      fileExpenseCode: null,
      fileExpenseId: null,
      invoicesOptionsFilter: [],
      postal_codes: [],
      postal_codes_options: [],
      suburbs: [],
      suburbs_options: [],
      cities: [],
      cityOptions: [],
      selectMunicipio: [],
      MunicipioOptions: [],
      selectEstados: [],
      estadoOptions: [],
      destinations: [],
      operator_id: null,
      operators: [],
      operatorOptions: [],
      operatorsData: [],
      interval: null,
      loadingFiscal: false,
      cancelarTimbrado: false,
      CancelacionOptions: [
        { label: '01 - Comprobantes emitidos con errores con relación', value: '01' },
        { label: '02 - Comprobantes emitidos con errores sin relación.', value: '02' },
        { label: '03 - No se llevó a cabo la operación.', value: '03' },
        { label: '04 - Operación nominativa relacionada en una factura global.', value: '04' }
      ],
      pagoId: null,
      motivos: ['',
        '01 - Comprobantes emitidos con errores con relación',
        '02 - Comprobantes emitidos con errores sin relación.',
        '03 - No se llevó a cabo la operación.',
        '04 - Operación nominativa relacionada en una factura global.'
      ]
    }
  },
  computed: {
    invoiceRules (val) {
      return [
        val => (this.$v.trip.fields.invoice_id.required) || 'El campo Remision / Factura es requerido.'
      ]
    },
    fileShippingUrl () {
      return process.env.API + 'shippings/file/shipping/' + this.fileShippingId
    },
    fileExpenseUrl () {
      return process.env.API + 'trip-expenses/file/expense/' + this.fileExpenseId
    },
    driverRules (val) {
      return [
        val => (this.$v.trip.fields.driver.required) || 'El campo Chofer es requerido.'
      ]
    },
    destinyRules (val) {
      return [
        val => (this.$v.trip.fields.destiny.required) || 'El campo Destino es requerido.'
      ]
    },
    dateRules (val) {
      return [
        val => (this.$v.trip.fields.date.required) || 'El campo Fecha es requerido.'
      ]
    },
    vehicle_idRules (val) {
      return [
        val => (this.$v.trip.fields.vehicle_id.required) || 'El campo Número de unidad es requerido.'
      ]
    },
    expenseTypeRules (val) {
      return [
        val => (this.$v.expenses.fields.expense_type.required) || 'El campo Tipo de gasto es requerido.'
      ]
    },
    expenseAmountRules (val) {
      return [
        val => (this.$v.expenses.fields.expense_amount.required) || 'El campo Monto es requerido.',
        val => (this.$v.expenses.fields.expense_amount.decimal) || 'El campo Monto es numerico.'
      ]
    },
    expenseDateRules (val) {
      return [
        val => (this.$v.expenses.fields.expense_date.required) || 'El campo Fecha es requerido.'
      ]
    },
    streetRules (val) {
      return [
        val => (this.$v.destination.fields.street.required) || 'El campo Calle es requerido.',
        val => (this.$v.destination.fields.street.maxLength) || 'El campo Calle no debe exceder los 100 dígitos.'
      ]
    },
    cpRules (val) {
      return [
        val => (this.$v.destination.fields.postal_code_id.required) || 'El campo código postal es requerido.'
      ]
    },
    outdoor_numberRules (val) {
      return [
        val => (this.$v.destination.fields.outdoor_number.required) || 'El campo Número exterior es requerido.'
      ]
    },
    suburbRules (val) {
      return [
        val => (this.$v.destination.fields.suburb_id.required) || 'El campo Colonia es requerido.'
      ]
    },
    between_streetRules (val) {
      return [
        val => (this.$v.destination.fields.between_street.required) || 'El campo Entre calles es requerido.'
      ]
    },
    distanceRules (val) {
      return [
        val => (this.$v.destination.fields.distance.required) || 'El campo Distancia recorrida es requerido.'
      ]
    },
    destinationDateRules (val) {
      return [
        val => (this.$v.destination.fields.date.required) || 'El campo Fecha de entrega es requerido.'
      ]
    },
    operator_idRules (val) {
      return [
        val => (this.$v.operator_id.required) || 'El campo Operador es requerido.'
      ]
    },
    tripFolioSustituye (val) {
      return [
        val => (this.$v.trip.fields.folio_sustituye.required) || 'El campo Folio que sustituye es requerido.'
      ]
    }
  },
  beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(8)) {
      this.$router.push('/')
    }
  },
  created () {
    this.$q.loading.show()
    this.getEstados()
    this.fetchFromServer()
    this.getDrivers()
    this.getDestinys()
    this.getEconomicNumber()
    this.getExpensesType()
    this.getGridExpenses()
    this.getShippings()
    this.getColors()
    this.getOperators()
    this.getGridDrivers()
    this.$q.loading.hide()
  },
  methods: {
    currencyFormat (num) {
      return Number.parseFloat(num).toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
    },
    fetchFromServer () {
      this.$q.loading.show()
      const id = this.$route.params.id
      api.get(`/trips/${id}`).then(({ data }) => {
        if (!data.trip) {
          this.$router.push('/trips')
        } else {
          this.trip.fields.folio = data.trip.folio
          this.invoicesOptionsFilter = [{ value: data.trip.invoice_id, label: data.trip.invoice }]
          this.economicNumberOptions = [{ value: data.trip.vehicle_id, label: data.trip.economic_number }]
          this.trip.fields.invoice_id = data.trip.invoice_id
          this.trip.fields.date = data.trip.date
          this.trip.fields.vehicle_id = data.trip.vehicle_id
          this.trip.fields.status = data.trip.status
          this.trip.fields.status_timbrado = data.trip.status_timbrado
          this.trip.fields.uuid = data.trip.uuid
          this.trip.fields.pdf = data.trip.pdf
          this.trip.fields.message = data.trip.message
          this.trip.fields.id_request = data.trip.id_request
          console.log(data)
          this.getVehicleData()
          this.getColors()
          this.getGridDestination()
          if ((data.trip.status_timbrado !== 3 && data.trip.status_timbrado !== 4 && data.trip.status_timbrado !== 5) && this.interval !== null) {
            clearInterval(this.interval)
            this.interval = null
          } else if ((data.trip.status_timbrado === 4 || data.trip.status_timbrado === 3 || data.trip.status_timbrado === 5) && this.interval === null) {
            this.interval = setInterval(() => {
              this.revisarTimbrado()
            }, 10000)
          }
          this.$q.loading.hide()
        }
      })
    },
    editTrip () {
      this.$v.trip.fields.$reset()
      this.$v.trip.fields.$touch()
      if (this.$v.trip.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = { ...this.trip.fields }
      if (this.trip.fields.driver.value) {
        params.driver = this.trip.fields.driver.value
      }
      if (this.trip.fields.destiny.value) {
        params.destiny = this.trip.fields.destiny.value
      }
      if (this.trip.fields.economic_number.value) {
        params.economic_number = this.trip.fields.economic_number.value
      }
      const id = this.$route.params.id
      api.put(`/trips/${id}`, params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        this.$q.loading.hide()
      })
    },
    getDrivers () {
      api.get('drivers/options').then(data => {
        this.driversOptions = data.data.options
      })
    },
    filterDrivers (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.driversOptionsFilter = this.driversOptions.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    getDestinys () {
      api.get('ranges/options').then(data => {
        this.destinysOptions = data.data.options
      })
    },
    filterDestinys (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.destinysOptionsFilter = this.destinysOptions.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    getEconomicNumber () {
      api.get('vehicle/options').then(data => {
        this.economicNumberOptions = data.data.options
      })
    },
    getVehicleData () {
      let id = this.trip.fields.vehicle_id
      if (this.trip.fields.vehicle_id.value) {
        id = this.trip.fields.vehicle_id.value
      }
      this.$q.loading.show()
      api.get(`vehicle/vehicle-data/${id}`).then(data => {
        this.vehicle.fields = data.data.vehicle
        this.$q.loading.hide()
      })
    },
    getExpensesType () {
      api.get('expenses/options').then(data => {
        this.expensesOptions = data.data.options
      })
    },
    createExpense () {
      this.$v.expenses.fields.$reset()
      this.$v.expenses.fields.$touch()
      if (this.$v.expenses.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = { ...this.expenses.fields }
      params.expense_date = this.expenses.fields.expense_date.substr(6, 10) + '-' + this.expenses.fields.expense_date.substr(3, 2) + '-' + this.expenses.fields.expense_date.substr(0, 2)
      api.post('trip-expenses/', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.clearExpenseData()
          this.getGridExpenses()
          this.$q.loading.hide()
        } else {
          this.$q.loading.hide()
        }
      })
    },
    getGridExpenses () {
      this.$q.loading.show()
      const id = this.$route.params.id
      api.get(`/trip-expenses/all/${Number(id)}`).then(({ data }) => {
        this.expensesData = data.expenses
        this.$q.loading.hide()
      })
    },
    editSelectedRowExpenses (id) {
      if (this.editFlag) {
        this.editFlag = false
      } else {
        this.editFlag = true
        api.get(`/trip-expenses/${Number(id)}`).then(({ data }) => {
          this.expenses.fields.expense_type = { value: data.expense.type_id, label: data.expense.type }
          this.expenses.fields.expense_amount = data.expense.amount
          this.expenses.fields.expense_date = data.expense.date.substr(8, 10) + '/' + data.expense.date.substr(5, 2) + '/' + data.expense.date.substr(0, 4)
          this.expenses.fields.id = data.expense.id
        })
      }
    },
    clearExpenseData () {
      this.expenses.fields.expense_type = null
      this.expenses.fields.expense_amount = null
      this.expenses.fields.expense_date = null
      this.editFlag = false
    },
    cancelEditExpense () {
      this.editFlag = false
      this.clearExpenseData()
    },
    editExpense () {
      this.$v.expenses.fields.$reset()
      this.$v.expenses.fields.$touch()
      if (this.$v.expenses.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const id = this.expenses.fields.id
      const params = { ...this.expenses.fields }
      params.expense_date = this.expenses.fields.expense_date.substr(6, 10) + '-' + this.expenses.fields.expense_date.substr(3, 2) + '-' + this.expenses.fields.expense_date.substr(0, 2)
      params.expense_type = this.expenses.fields.expense_type
      if (this.expenses.fields.expense_type.value) {
        params.expense_type = this.expenses.fields.expense_type.value
      }
      api.put(`/trip-expenses/${id}`, params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.clearExpenseData()
          this.getGridExpenses()
          this.$q.loading.hide()
        } else {
          this.$q.loading.hide()
        }
      })
    },
    deleteSelectedRowExpenses (id) {
      this.$q.dialog({
        title: 'Confirmación',
        message: '¿Desea eliminar este Gasto?',
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
        api.delete(`/trip-expenses/${id}`).then(({ data }) => {
          this.$q.notify({
            message: data.message.content,
            position: 'top',
            color: (data.result ? 'positive' : 'warning'),
            icon: (data.result ? 'thumb_up' : 'close')
          })
          if (data.result) {
            this.getGridExpenses()
            this.$q.loading.hide()
          }
        })
      }).onCancel(() => {})
    },
    createLot () {
      const id = this.$route.params.id
      this.$router.push(`/lotTrip/new/${id}`)
    },
    getShippings () {
      this.$q.loading.show()
      const id = this.$route.params.id
      api.get(`/shippings/all/${Number(id)}`).then(({ data }) => {
        this.lotData = data.shippings
        this.$q.loading.hide()
      })
    },
    editSelectedRowShippings (id) {
      this.$router.push(`/lotTrip/${id}`)
    },
    deleteSelectedRowShipping (id) {
      this.$q.dialog({
        title: 'Confirmación',
        message: '¿Desea eliminar este Envío?',
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
        api.delete(`/shippings/${id}`).then(({ data }) => {
          this.$q.notify({
            message: data.message.content,
            position: 'top',
            color: (data.result ? 'positive' : 'warning'),
            icon: (data.result ? 'thumb_up' : 'close')
          })
          if (data.result) {
            this.getShippings()
            this.$q.loading.hide()
          }
        })
      }).onCancel(() => {})
    },
    getColors () {
      if (this.trip.fields.status === 'NUEVO') {
        this.colorStatus = 'light-blue-6'
        this.colorButon = 'purple-10'
      }
      if (this.trip.fields.status === 'EN TRÁNSITO') {
        this.colorStatus = 'purple-10'
        this.colorButon = 'blue-9'
      }
      if (this.trip.fields.status === 'ENTREGADO') {
        this.colorStatus = 'blue-9'
        this.colorButon = 'positive'
      }
      if (this.trip.fields.status === 'CERRADO') {
        this.colorStatus = 'positive'
      }
    },
    async changeStatus (status) {
      this.$q.loading.show()
      const id = this.$route.params.id
      const params = { status: status }
      if (this.trip.fields.status === 'NUEVO') {
        if (this.lotData.length === 0) {
          this.$q.dialog({
            title: 'Error',
            message: 'El Embarque debe tener por lo menos un envío.',
            persistent: true
          })
          this.$q.loading.hide()
          return false
        }
        let flagProducts = false
        let flagFolio = null
        for (let i = 0; i < this.lotData.length; i++) {
          await api.get(`/shipping-details/all/${this.lotData[i].id}`).then(({ data }) => {
            const productShipping = data.products.length
            if (productShipping === 0) {
              flagProducts = true
              flagFolio = this.lotData[i].serial
            }
          })
        }
        if (flagProducts) {
          this.$q.dialog({
            title: 'Error',
            message: 'El Envío ' + flagFolio + ' debe tener por lo menos un producto.',
            persistent: true
          })
          this.$q.loading.hide()
          return false
        }
      }
      if (this.trip.fields.status === 'ENTREGADO') {
        if (this.expensesData.length === 0) {
          this.$q.dialog({
            title: 'Error',
            message: 'El Envío debe tener por lo menos un gasto.',
            persistent: true
          })
          this.$q.loading.hide()
          return false
        }
      }
      await api.put(`/trips/status/${id}`, params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.fetchFromServer()
          this.$q.loading.hide()
        } else {
          this.$q.loading.hide()
        }
      })
    },
    uploadFileShipping (id) {
      const file = this.lotData.filter(ld => Number(ld.id) === Number(id))[0]
      this.fileUrl = (file.file != null ? this.serverUrl + 'assets/shippings/files/' + file.file : null)
      this.fileShippingId = file.id
      this.fileModalShipping = true
      this.fileShippingCode = file.code
    },
    afterUploadFile (response) {
      this.$q.loading.show()
      const data = JSON.parse(response.xhr.response)
      this.$q.notify({
        message: data.message.content,
        position: 'top',
        color: (data.result ? 'positive' : 'warning')
      })
      if (data.result) {
        this.$q.loading.hide()
        this.getShippings()
        this.fileModalShipping = false
      } else {
        this.$q.loading.hide()
      }
    },
    uploadFiles () {
      this.$refs.fileShippingRef.upload()
    },
    viewFileShipping (file) {
      window.open(`${this.serverUrl}assets/shippings/files/${file.file}`, '_blank')
    },
    uploadFileExpense (id) {
      const file = this.expensesData.filter(ld => Number(ld.id) === Number(id))[0]
      this.fileUrl = (file.file != null ? this.serverUrl + 'assets/expense/files/' + file.file : null)
      this.fileExpenseId = file.id
      this.fileModalExpense = true
      this.fileExpenseCode = file.code
    },
    afterUploadFileExpense (response) {
      this.$q.loading.show()
      const data = JSON.parse(response.xhr.response)
      this.$q.notify({
        message: data.message.content,
        position: 'top',
        color: (data.result ? 'positive' : 'warning')
      })
      if (data.result) {
        this.$q.loading.hide()
        this.getGridExpenses()
        this.fileModalExpense = false
      } else {
        this.$q.loading.hide()
      }
    },
    uploadFilesExpense () {
      this.$refs.fileShippingRef.upload()
    },
    viewFileExpense (file) {
      window.open(`${this.serverUrl}assets/expense/files/${file.file}`, '_blank')
    },
    getEstados () {
      this.selectEstados = []
      api.get('/branch-offices/states').then(({ data }) => {
        this.selectEstados = data.options
        // console.log(data)
      }).catch(error => error)
    },
    getOperators () {
      this.selectEstados = []
      api.get('/drivers/options').then(({ data }) => {
        this.operators = data.options
      }).catch(error => error)
    },
    async getPostalCodes (code = '') {
      code = code === '' ? '0' : code
      await api.get(`/branch-offices/postal_codes/${code}/${this.destination.fields.municipio_id}`).then(({ data }) => {
        this.postal_codes = data.options
      }).catch(error => error)
    },
    async getMunicipiosbyEstado (id, postalReset = false) {
      this.destination.fields.postal_code_id = postalReset ? null : this.destination.fields.postal_code_id
      this.destination.fields.municipio_id = null
      this.destination.fields.city_id = null
      this.selectMunicipio = []
      this.getcities(id)
      await api.get(`/branch-offices/municipalities/${id}/${this.destination.fields.postal_code_id}`).then(({ data }) => {
        this.selectMunicipio = data.options
      }).catch(error => error)
    },
    async getcities (id) {
      this.cities = []
      await api.get(`/branch-offices/cities/${id}/${this.destination.fields.postal_code_id}`).then(({ data }) => {
        this.cities = data.options
      }).catch(error => error)
    },
    async getSuburbsByPostalCode (id) {
      this.suburbs = []
      this.destination.fields.suburb_id = null
      this.destination.fields.municipio_id = null
      this.destination.fields.estado_id = null
      this.destination.fields.city_id = null
      this.selectMunicipio = []
      if (id !== null) {
        this.destination.fields.estado_id = this.postal_codes.filter(v => v.value === id)[0].state_id
        this.estadoOptions = this.selectEstados.filter(v => v.value === this.destination.fields.estado_id)
        await this.getMunicipiosbyEstado(this.destination.fields.estado_id)
        this.destination.fields.municipio_id = this.postal_codes.filter(v => v.value === id)[0].municipality_id
        this.MunicipioOptions = this.selectMunicipio.filter(v => v.value === this.destination.fields.municipio_id)
        await this.getcities(this.destination.fields.estado_id)
        this.destination.fields.city_id = this.postal_codes.filter(v => v.value === id)[0].city_id
        this.cityOptions = this.cities.filter(v => v.value === this.destination.fields.city_id)

        await api.get(`/branch-offices/suburbs/${id}`).then(({ data }) => {
          this.suburbs = data.options
          this.suburbs_options = data.options
        }).catch(error => error)
      }
    },
    filterMunicipio (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.MunicipioOptions = this.selectMunicipio.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    filterEstado (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.estadoOptions = this.selectEstados.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
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
    createDestination () {
      this.$v.destination.fields.$reset()
      this.$v.destination.fields.$touch()
      if (this.$v.destination.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = { ...this.destination.fields }
      params.date = this.destination.fields.date.split(' ')[0].split('/').reverse().join('-') + ' ' + this.trip.fields.date.split(' ')[1]
      params.trip_id = this.$route.params.id
      api.post('trip-destination/', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.clearDestinationData()
          this.getGridDestination()
          this.$q.loading.hide()
        } else {
          this.$q.loading.hide()
        }
      })
    },
    clearDestinationData () {
      this.destination.fields = {
        id: null,
        street: null,
        estado_id: null,
        municipio_id: null,
        postal_code_id: null,
        suburb_id: null,
        indoor_number: null,
        outdoor_number: null,
        municipality: null,
        state: null,
        between_street: null,
        city_id: null,
        distance: null,
        date: null
      }
      this.editFlag = false
    },
    getGridDestination () {
      this.$q.loading.show()
      const id = this.$route.params.id
      api.get(`/trip-destination/getbytrip/${Number(id)}`).then(({ data }) => {
        this.destinations = data.destinations
        this.$q.loading.hide()
      })
    },
    async editDestination (row) {
      const postal = row.postal_code_id
      const suburb = row.suburb_id
      this.destination.fields.municipio_id = row.municipality_id
      await this.getPostalCodes(row.postal_code)
      this.destination.fields.postal_code_id = postal
      await this.getSuburbsByPostalCode(postal)
      this.destination.fields.id = row.id
      this.destination.fields.street = row.street
      this.destination.fields.outdoor_number = row.outdoor_number
      this.destination.fields.indoor_number = row.indoor_number
      this.destination.fields.between_street = row.between_street
      this.destination.fields.suburb_id = suburb
      this.destination.fields.distance = row.distance
      this.destination.fields.date = row.date

      this.editFlag = true
    },
    cancelEditDestination () {
      this.editFlag = false
      this.clearDestinationData()
    },
    updateDestination () {
      this.$v.destination.fields.$reset()
      this.$v.destination.fields.$touch()
      if (this.$v.destination.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const id = this.destination.fields.id
      const params = { ...this.destination.fields }
      params.date = this.destination.fields.date.split(' ')[0].split('/').reverse().join('-') + ' ' + this.trip.fields.date.split(' ')[1]
      api.put(`/trip-destination/${id}`, params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.clearDestinationData()
          this.getGridDestination()
          this.$q.loading.hide()
        } else {
          this.$q.loading.hide()
        }
      })
    },
    deleteDestination (id) {
      this.$q.dialog({
        title: 'Confirmación',
        message: '¿Desea eliminar este Destino?',
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
        api.delete(`/trip-destination/${id}`).then(({ data }) => {
          this.$q.notify({
            message: data.message.content,
            position: 'top',
            color: (data.result ? 'positive' : 'warning'),
            icon: (data.result ? 'thumb_up' : 'close')
          })
          if (data.result) {
            this.getGridDestination()
            this.$q.loading.hide()
          }
        })
      }).onCancel(() => {})
    },
    filterOperator (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.operatorOptions = this.operators.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    addOperator () {
      this.$v.operator_id.$reset()
      this.$v.operator_id.$touch()
      if (this.$v.operator_id.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = []
      params.operator_id = this.operator_id
      params.trip_id = this.$route.params.id
      api.post('trips/driver', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.operator_id = null
          this.getGridDrivers()
          this.$q.loading.hide()
        } else {
          this.$q.loading.hide()
        }
      })
    },
    getGridDrivers () {
      this.$q.loading.show()
      const id = this.$route.params.id
      api.get(`/trips/drivers/${Number(id)}`).then(({ data }) => {
        this.operatorsData = data.drivers
        this.$q.loading.hide()
      })
    },
    deleteOperator (id) {
      this.$q.dialog({
        title: 'Confirmación',
        message: '¿Desea eliminar este Operador?',
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
        api.delete(`/trips/driver/${id}`).then(({ data }) => {
          this.$q.notify({
            message: data.message.content,
            position: 'top',
            color: (data.result ? 'positive' : 'warning'),
            icon: (data.result ? 'thumb_up' : 'close')
          })
          if (data.result) {
            this.getGridDrivers()
            this.$q.loading.hide()
          }
        })
      }).onCancel(() => {})
    },
    timbrar () {
      // this.$q.loading.show()
      const id = this.$route.params.id
      api.get(`/trips/timbrar/${Number(id)}`).then(({ data }) => {
        this.fetchFromServer()
        this.$q.loading.hide()
      })
    },
    revisarTimbrado () {
      this.loadingFiscal = true
      api.put(`/trips/revisarTimbrado/${this.$route.params.id}`).then(({ data }) => {
        console.log(data)
        if (data.response.status === 'done' || data.response.status === 'error' || data.response.status === true) {
          this.fetchFromServer()
        }
        // if ((data.response.status === 'done' || data.response.status === true) && this.status_email === 'NUEVO') {
        //   const params2 = []
        //   params2.id = { ...this.invoice.fields }.id
        //   params2.email = { ...this.emailInvoice.fields }.email_cliente
        // api.post('/invoices/sendNewPdfInvoiceToCustomer', params2).then(({ data }) => {
        //   this.$q.notify({
        //     message: data.message.content,
        //     position: 'top',
        //     color: (data.result ? 'positive' : 'warning')
        //   })
        // })
        // }
      }).catch(error => {
        console.error(error)
      })
      this.loadingFiscal = false
    },
    getCFDIInvoice (idRequest) {
      var url = process.env.API === 'https://api_alpez.wasp.mx/' ? 'https://batuta.wasp.mx' : 'http://batuta.beta.antfarm.mx'
      window.open(url + '/api/download_xml/' + idRequest, '_blank')
    },
    getPdfInvoice (idRequest, name) {
      this.$q.loading.show()
      api.fileDownload(`/invoices/pdfi/${idRequest}`).then(({ data }) => {
        const url = window.URL.createObjectURL(new Blob([data], { type: 'application/pdf' }))
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', name)
        document.body.appendChild(link)
        this.$q.loading.hide()
        link.click()
      })
    },
    isNumber (evt, input) {
      switch (input) {
        case 'folio_sustituye':
          this.trip.fields.folio_sustituye = this.trip.fields.folio_sustituye.replace(/[^0-9.]/g, '')
          this.$v.trip.fields.folio_sustituye.$touch()
          break
        default:
          break
      }
    },
    cancelarFactura () {
      this.$v.trip.fields.$reset()
      this.$v.trip.fields.$touch()
      if (this.$v.trip.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      const params = {}
      params.motivo_cancelacion = this.trip.fields.motivo_cancelacion
      params.folio_sustituye = this.trip.fields.folio_sustituye
      this.$q.loading.show()
      api.put(`/trips/cancelar/${this.$route.params.id}`, params).then(({ data }) => {
        this.$q.loading.hide()
        this.cancelarTimbrado = false
        this.trip.fields.motivo_cancelacion = '02'
        if (data.result === 'error') {
          this.$q.notify({
            message: data.message.content,
            caption: data.errors ?? '',
            multiLine: true,
            position: 'top',
            timeout: 7000,
            color: (data.result !== 'error' ? 'positive' : 'warning')
          })
        } else {
          this.fetchFromServer()
        }
      }).catch(error => {
        console.error(error)
      })
    }
  }
}
</script>

<style>
</style>

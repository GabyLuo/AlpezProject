<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row q-col-gutter-xs q-col-gutter-md">
        <div class="col-sm-4" style="font-size: 20px">
          <div class="q-pa-md q-gutter-sm">
              <q-breadcrumbs>
                  <q-breadcrumbs-el label="" icon="home" to="/"/>
                  <q-breadcrumbs-el label="Pedidos"/>
              </q-breadcrumbs>
          </div>
        </div>
        <div class="col-sm-8">
          <div class="col-xs-12 col-md-4 offset-md-10 pull-right">
            <div >
<!--              <q-btn color="purple" icon="mail" @click.native="sendMail()">-->
<!--                <q-tooltip>ENVIAR CORREO</q-tooltip>-->
<!--              </q-btn>-->
              <q-btn v-if="haspermissionv1" color="green" style="margin-left: 10px;" icon="fas fa-file-excel" @click="generateCSV()">
                <q-tooltip>GENERAR CSV</q-tooltip>
              </q-btn>
              <q-btn v-if="haspermissionv1" color="red" style="margin-left: 10px;" icon="fas fa-file-pdf" @click="generatePDF()">
                <q-tooltip>GENERAR PDF</q-tooltip>
              </q-btn>
              <q-btn v-if="haspermissionv2" class="bg-primary" style="margin-left: 10px; color: white;" icon="add" label="Nuevo" @click.native="openModal()">
                <q-tooltip>Crear Pedido</q-tooltip>
              </q-btn>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="q-pa-md bg-grey-3">
      <div class="row bg-white border-panel">
        <div class="col q-pa-md">
          <div class="row q-col-gutter-xs q-col-gutter-md">
            <div class="col-sm-2"></div>
            <div class="col-md-2">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="saleDatev1"
                mask="date"
                label="Desde"
              >
                <q-popup-proxy
                  ref="date"
                  transition-show="scale"
                  transition-hide="scale"
                >
                  <div class="col-sm-12">
                    <q-date
                      v-model="saleDatev1"
                      @input="filterGrid()"
                      today-btn
                    />
                  </div>
                </q-popup-proxy>
              </q-select>
            </div>
            <div class="col-md-2">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="saleDatev2"
                mask="date"
                label="Hasta"
              >
                <q-popup-proxy
                  ref="date"
                  transition-show="scale"
                  transition-hide="scale"
                >
                  <div class="col-sm-12">
                    <q-date
                      v-model="saleDatev2"
                      @input="filterGrid()"
                      today-btn
                    />
                  </div>
                </q-popup-proxy>
              </q-select>
            </div>
            <div class="col-sm-2">
              <q-select  color="dark" bg-color="secondary" filled
                        v-model="sellerG"
                        :options="sellerOptionsG"
                        emit-value map-options
                        label="Vendedor"
                        :disable="isnotAdmin"
                        @input="filterGrid()"
              >
              </q-select>
            </div>
            <div class="col-sm-2">
              <q-select  color="dark" bg-color="secondary" filled
                        v-model="customerG"
                        :options="filteredCustomerOptions2"
                        @filter="filtrarClientes2"
                        @input="filterGrid()"
                        label="Cliente"
                        emit-value map-options>
              </q-select>
            </div>
            <div class="col-sm-2">
              <q-select  color="dark" bg-color="secondary" filled
                        v-model="status"
                        :options="$store.getters['users/roles'].includes(2) ? [
                      {label: 'TODOS', value: 'TODOS'},
                      {label: 'AUTORIZADO', value: 'AUTORIZADO'},
                      {label: 'PARCIAL', value: 'PARCIAL'},
                      {label: 'REMISIONADO', value: 'REMISIONADO'},
                      {label: 'ENTREGADO', value: 'ENVIADO'}
                    ] : [
                      {label: 'TODOS', value: 'TODOS'},
                      {label: 'NUEVO', value: 'NUEVO'},
                      {label: 'SOLICITADO', value: 'SOLICITADO'},
                      {label: 'AUTORIZADO', value: 'AUTORIZADO'},
                      {label: 'PARCIAL', value: 'PARCIAL'},
                      {label: 'REMISIONADO', value: 'REMISIONADO'},
                      {label: 'ENTREGADO', value: 'ENVIADO'}
                    ]"
                        emit-value map-options
                        label="Estatus"
                        @input="filterGrid()"
              >
              </q-select>
            </div>
          </div>
          <div style="padding-top: 20px;">
            <q-table
              flat
              bordered
              :data="data"
              :columns="columns"
              row-key="code"
              :pagination.sync="pagination"
              :filter="filter"
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
                  <q-td key="id" :props="props" style="width: 5%; text-align: center;"><label @click="openActions(props.row.id, props.row.status)" class="text-primary" style="text-decoration: underline; cursor: pointer;">{{ props.row.id }}</label></q-td>
                  <q-td key="old_folio" :props="props" style="width: 10%;">{{ props.row.old_folio }}</q-td>
                  <q-td key="status" :props="props" style="width: 10%; text-align: center;">
                    <q-chip square dense :color="props.row.status == 'SOLICITADO' ? 'yellow-8' : (props.row.status == 'NUEVO' ? 'blue-7' : (props.row.status == 'AUTORIZADO' ? 'green' : (props.row.status == 'PARCIAL' ? 'red-4' : (props.row.status == 'ENTREGADO' ? 'purple-6': (props.row.status == 'CANCELADO' ? 'red': 'red-4')))))" text-color="white">
                      {{ props.row.status }}
                    </q-chip>
                  </q-td>
                  <q-td key="date" :props="props" style="width: 10%;">{{ props.row.date }}</q-td>
                  <q-td key="branchofficeorigin" :props="props" style="width: 10%;">{{ props.row.branchofficeorigin }}</q-td>
                  <q-td key="invoices" :props="props" style="width: 5%; text-align: center;">
                    <div v-for="invoice in props.row.array_invoices" v-bind:key="invoice.id" style="display:inline;">
                      <label @click="openActionRemision(invoice)" style="text-decoration: underline black; cursor: pointer; padding-left: 10px;">{{ invoice }}</label>
                    </div>
                  </q-td>
                  <q-td key="customer" :props="props" style="width: 20%;"><label @click="openModalclients(props.row.id_client,props.row.branchofficedestiny)" style="text-decoration: underline black; cursor: pointer;">{{ props.row.customer_name }}</label></q-td>
<!--                   <q-td key="clientbranchoffice" :props="props" style="width: 10%;"><label @click="openModalclientsBranch(props.row.branchofficedestiny)" style="text-decoration: underline black; cursor: pointer;">{{ props.row.clientbranchoffice }}</label></q-td>
                  <q-td key="inbulk" :props="props" style="width: 10%;">{{ `${formatter.format(Number(props.row.inbulk))}` }}</q-td>
                  <q-td key="unit" :props="props" style="width: 10%;">{{props.row.unit}}</q-td> -->
                  <q-td key="total" :props="props" style="width: 10%;">{{ `${currencyFormatter.format( Number(props.row.montoinbulk) )}` }}</q-td>
                  <q-td key="user_name" :props="props" style="width: 10%;">{{ props.row.user_name }}</q-td>
                  <!-- <q-td key="rating" :props="props">
                    <div>
                      <q-rating
                      class="q-gutter-y-xs" style="width: 120px"
                        v-model="ratingModel"
                        size="20px"
                        :color="props.row.rater == 1 ? ['blue-7', 'grey-4'] : (props.row.rater == 2 ? ['grey-4', 'yellow-8', 'grey-4'] : (props.row.rater == 3 ? ['grey-4', 'grey-4', 'green', 'grey-4'] : (props.row.rater == 4 ? ['grey-4', 'grey-4', 'grey-4', 'red-4', 'grey-4'] : (props.row.rater == 5 ? ['grey-4', 'grey-4', 'grey-4', 'grey-4', 'purple-6'] : 'grey-4')))) "
                        :icon="icons"
                        readonly
                      />
                    </div>
                  </q-td> -->
                </q-tr>
              </template>
            </q-table>
          </div>
        </div>
      </div>
    </div>
    <q-dialog v-model="modalPedido" persistent>
      <q-card style="min-width: 40%; !important;">
        <q-card-section class="bg-primary">
         <div class="row">
           <div class="col-sm-11 text-h6" style="color: white">CREAR PEDIDO</div>
           <div class="col-sm-1 pull-right"><q-btn color="white" flat v-close-popup round dense icon="close" /></div>
         </div>
        </q-card-section>
        <q-separator />
        <q-card-section style="max-height: 50vh" class="scroll">
          <div class="text-overline" style="font-size: 16px;">Tipo de Pedido</div>
          <div class="row bg-white border-panel">
            <div class="col q-pa-md">
              <div class="row q-col-gutter-xs q-col-gutter-md">
                <div class="col-xs-12 col-md-6">
                  <q-select color="dark" bg-color="secondary" filled
                            v-model="type_order"
                            :error="$v.type_order.$error"
                            :options="
                            [ {label: 'MOSTRADOR', value: 1},
                              {label: 'CONSIGNACIÓN', value: 2},
                              {label: 'MAYOREO', value: 3},
                            ]"
                            use-input
                            label="Tipo"
                            map-options
                            >
                    <template v-slot:prepend>
                      <q-icon name="person" />
                    </template>
                  </q-select>
                </div>
              </div>
            </div>
          </div>
          <div class="text-overline" style="font-size: 16px;">Datos del cliente</div>
          <div class="row bg-white border-panel">
            <div class="col q-pa-md">
              <div class="row q-col-gutter-xs q-col-gutter-md">
                <div class="col-xs-12 col-md-6">
                  <q-select color="dark" bg-color="secondary"
                            filled
                            v-model="customer"
                            :error="$v.customer.$error"
                            :options="filteredCustomerOptions"
                            @filter="filtrarClientes"
                            use-input
                            @input="getOfficebyClient()"
                            label="Cliente"
                            emit-value map-options
                            input-debounce="0"
                            :rules="customerRules">
                    <template v-slot:prepend>
                      <q-icon name="person" />
                    </template>
                  </q-select>
                </div>
                <div class="col-xs-12 col-md-6">
                  <q-select color="dark" bg-color="secondary" filled
                            v-model="clientofficeDestiny"
                            :error="$v.clientofficeDestiny.$error"
                            :options="officeClientsOptions"
                            use-input
                            label="Sucursal Cliente"
                            map-options
                            :rules="officeClientRules">
                    <template v-slot:prepend>
                      <q-icon name="person" />
                    </template>
                  </q-select>
                </div>
              </div>
            </div>
          </div>
          <div class="text-overline" style="font-size: 16px;">Origen de venta</div>
          <div class="row bg-white border-panel">
            <div class="col q-pa-md">
              <div class="row q-col-gutter-xs q-col-gutter-md">
                <div class="col-xs-12 col-md-6">
                  <q-select color="dark" bg-color="secondary" filled
                            v-model="branch"
                            :error="$v.branch.$error"
                            :options="branchOffices"
                            label="Sucursal de envío"
                            @input="searchStorage(branch)"
                            emit-value map-options
                            :rules="branchRules">
                    <template v-slot:prepend>
                      <q-icon name="person" />
                    </template>
                  </q-select>
                </div>
                <div class="col-xs-12 col-md-6">
                  <q-select color="dark" bg-color="secondary" filled
                            v-model="storageShopping"
                            :options="storageOptions"
                            label="Almacén"
                            :rules="storageShoppingRules"
                            :error="$v.storageShopping.$error"
                            :disable="isnotAdmin">
                    <template v-slot:prepend>
                      <q-icon name="fas fa-warehouse" />
                    </template>
                  </q-select>
                </div>
                <div class="col-xs-12 col-md-6">
                  <q-select color="dark" bg-color="secondary" filled
                            v-model="seller"
                            :options="sellerOptions"
                            label="Vendedor"
                            :rules="sellerRules"
                            :error="$v.seller.$error"
                            :disable="isnotAdmin">
                    <template v-slot:prepend>
                      <q-icon name="person" />
                    </template>
                  </q-select>
                </div>
                <div class="col-xs-12 col-md-6">
                  <q-select color="dark" bg-color="secondary" filled
                            v-model="taxInvoice"
                            :error="$v.taxInvoice.$error"
                            :options="[
                            { label: 'FACTURA', value: 0 },
                            { label: 'REMISIÓN', value: 1}
                            ]"
                            :rules="taxInvoicesRules"
                            label="¿Desea factura o remisión?"
                            >
                    <template v-slot:prepend>
                      <q-icon name="fas fa-cash-register" />
                    </template>
                  </q-select>
                </div>
              </div>
            </div>
          </div>
        </q-card-section>
        <q-separator />
        <q-card-actions align="right" style="vertical-align: bottom">
          <q-btn flat label="Crear" color="white" style="background-color: #21ba45;" @click="createShoppingCart()" />
        </q-card-actions>
      </q-card>
    </q-dialog>
    <q-dialog v-model="modalClient" persistent>
      <q-card style="min-width: 50%; !important;">
        <q-card-section class="bg-primary">
         <div class="row">
           <div class="col-sm-11 text-h6" style="color:white;">DATOS DEL CLIENTE</div>
           <div class="col-sm-1 pull-right"><q-btn color="white" flat v-close-popup round dense icon="close" /></div>
         </div>
        </q-card-section>
        <q-separator />
        <q-card-section style="max-height: 50vh" class="scroll">
          <div class="row bg-white">
            <div class="col q-pa-md">
              Cliente
              <div class="row q-col-gutter-xs q-col-gutter-md">
                <div class="col-xs-12 col-md-8">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="dataClient.fields.client"
                    label="Cliente"
                    disable
                  >
                    <template v-slot:prepend>
                      <q-icon name="fas fa-shopping-cart" />
                    </template>
                  </q-input>
                </div>
                <div class="col-xs-12 col-md-4">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="dataClient.fields.phone"
                    label="Telefono de contacto"
                    disable
                  >
                    <template v-slot:prepend>
                      <q-icon name="fas fa-shopping-cart" />
                    </template>
                  </q-input>
                </div>
                <div class="col-xs-12 col-md-8">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="dataClient.fields.address"
                    label="Direccion"
                    disable
                    autogrow
                  >
                    <template v-slot:prepend>
                      <q-icon name="fas fa-shopping-cart" />
                    </template>
                  </q-input>
                </div>
                <div class="col-xs-12 col-md-4">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="dataClient.fields.term"
                    label="Plazo"
                    disable
                  >
                    <template v-slot:prepend>
                      <q-icon name="fas fa-shopping-cart" />
                    </template>
                  </q-input>
                </div>
                </div>
                <br>
                  Sucursal
                <div class="row q-col-gutter-xs q-col-gutter-md">
                <div class="col-xs-12 col-md-8">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="dataClient.fields.branchOffice"
                    label="Nombre de la sucursal"
                    disable
                  >
                    <template v-slot:prepend>
                      <q-icon name="fas fa-building" />
                    </template>
                  </q-input>
                </div>
                <div class="col-xs-12 col-md-4">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="dataClient.fields.branch_phone"
                    label="Telefono"
                    disable
                  >
                    <template v-slot:prepend>
                      <q-icon name="fas fa-phone"/>
                    </template>
                  </q-input>
                </div>
                <div class="col-xs-12 col-md-6">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="dataClient.fields.branchAddress"
                    label="Calle"
                    disable
                    autogrow
                  >
                    <template v-slot:prepend>
                      <q-icon name="fas fa-road" />
                    </template>
                  </q-input>
                </div>
                <div class="col-xs-12 col-md-3">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="dataClient.fields.outdoor"
                    label="N. Exterior"
                    disable
                  >
                    <template v-slot:prepend>
                      <q-icon name="fas fa-hashtag" />
                    </template>
                  </q-input>
                </div>
                <div class="col-xs-12 col-md-3">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="dataClient.fields.indoor"
                    label="N. Interior"
                    disable
                  >
                    <template v-slot:prepend>
                      <q-icon name="fas fa-hashtag" />
                    </template>
                  </q-input>
                </div>
                <div class="col-xs-12 col-md-5">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="dataClient.fields.colony"
                    label="Colonia"
                    disable
                    autogrow
                  >
                    <template v-slot:prepend>
                      <q-icon name="fas fa-city" />
                    </template>
                  </q-input>
                </div>
                <div class="col-xs-12 col-md-4">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="dataClient.fields.municipality"
                    label="Municipio"
                    disable
                  >
                    <template v-slot:prepend>
                      <q-icon name="fas fa-city" />
                    </template>
                  </q-input>
                </div>
                <div class="col-xs-12 col-md-3">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="dataClient.fields.CP"
                    label="CP"
                    disable
                  >
                    <template v-slot:prepend>
                      <q-icon name="fas fa-mail-bulk" />
                    </template>
                  </q-input>
              </div>
                </div>
            </div>
          </div>
        </q-card-section>
        <q-separator />
      </q-card>
    </q-dialog>
      <q-dialog v-model="modalClientBranch" persistent>
      <q-card style="min-width: 50%; !important;">
        <q-card-section class="bg-primary">
         <div class="row">
           <div class="col-sm-11 text-h6" style="color:white;">DATOS DE LA SUCURSAL DEL CLIENTE</div>
           <div class="col-sm-1 pull-right"><q-btn color="white" flat v-close-popup round dense icon="close" /></div>
         </div>
        </q-card-section>
        <q-separator />
        <q-card-section style="max-height: 50vh" class="scroll">
          <div class="row bg-white border-panel">
            <div class="col q-pa-md">
              <div class="row q-col-gutter-xs q-col-gutter-md">
                <div class="col-xs-12 col-md-8">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="dataClientBranch.fields.branchOffice"
                    label="Nombre de la sucursal"
                    disable
                  >
                    <template v-slot:prepend>
                      <q-icon name="fas fa-building" />
                    </template>
                  </q-input>
                </div>
                <div class="col-xs-12 col-md-4">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="dataClientBranch.fields.phone"
                    label="Telefono"
                    disable
                  >
                    <template v-slot:prepend>
                      <q-icon name="fas fa-phone"/>
                    </template>
                  </q-input>
                </div>
                <div class="col-xs-12 col-md-6">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="dataClientBranch.fields.branchAddress"
                    label="Calle"
                    disable
                    autogrow
                  >
                    <template v-slot:prepend>
                      <q-icon name="fas fa-road" />
                    </template>
                  </q-input>
                </div>
                <div class="col-xs-12 col-md-3">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="dataClientBranch.fields.outdoor"
                    label="N. Exterior"
                    disable
                  >
                    <template v-slot:prepend>
                      <q-icon name="fas fa-hashtag" />
                    </template>
                  </q-input>
                </div>
                <div class="col-xs-12 col-md-3">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="dataClientBranch.fields.indoor"
                    label="N. Interior"
                    disable
                  >
                    <template v-slot:prepend>
                      <q-icon name="fas fa-hashtag" />
                    </template>
                  </q-input>
                </div>
                <div class="col-xs-12 col-md-5">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="dataClientBranch.fields.colony"
                    label="Colonia"
                    disable
                    autogrow
                  >
                    <template v-slot:prepend>
                      <q-icon name="fas fa-city" />
                    </template>
                  </q-input>
                </div>
                <div class="col-xs-12 col-md-4">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="dataClientBranch.fields.municipality"
                    label="Municipio"
                    disable
                  >
                    <template v-slot:prepend>
                      <q-icon name="fas fa-city" />
                    </template>
                  </q-input>
                </div>
                <div class="col-xs-12 col-md-3">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="dataClientBranch.fields.CP"
                    label="CP"
                    disable
                  >
                    <template v-slot:prepend>
                      <q-icon name="fas fa-mail-bulk" />
                    </template>
                  </q-input>
                </div>
              </div>
            </div>
          </div>
        </q-card-section>
        <q-separator />
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import api from '../../commons/api.js'
const { required } = require('vuelidate/lib/validators')

export default {
  name: 'IndexShoppingCarts',
  validations: {
    customer: { required },
    branch: { required },
    seller: { required },
    clientofficeDestiny: { required },
    taxInvoice: { required },
    storageShopping: { required },
    type_order: { required }
  },
  data () {
    return {
      currencyFormatter: new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
      }),
      icons: [
        'fas fa-star',
        'fas fa-anchor',
        'fas fa-check',
        'fas fa-file-alt',
        'fas fa-thumbs-up'
      ],
      ratingModel: 5,
      formatter: new Intl.NumberFormat('en-US'),
      role: null,
      taxInvoice: null,
      modalPedido: false,
      modalClient: false,
      modalClientBranch: false,
      type_order: 1,
      customer: null,
      saleDatev1: null,
      saleDatev2: null,
      clientofficeDestiny: null,
      customerG: 'TODOS',
      branch: null,
      seller: null,
      sellerG: 'TODOS',
      status: 'TODOS',
      storageShopping: null,
      storageOptions: [],
      customerOptions: [],
      customerOptionsG: [],
      officeClientsOptions: [],
      sellerOptions: [],
      sellerOptionsG: [],
      branchOffices: [],
      filteredCustomerOptions: [],
      filteredCustomerOptions2: [],
      dataClient: {
        fields: {
          id: null,
          client: null,
          address: null,
          phone: null,
          term: null,
          branchOffice: null,
          branchAddress: null,
          indoor: null,
          outdoor: null,
          colony: null,
          municipality: null,
          CP: null,
          branch_phone: null
        }
      },
      dataClientBranch: {
        fields: {
          id: null,
          branchOffice: null,
          branchAddress: null,
          indoor: null,
          outdoor: null,
          colony: null,
          municipality: null,
          CP: null,
          phone: null
        }
      },
      pagination: {
        sortBy: 'id',
        descending: true,
        rowsPerPage: 25
      },
      columns: [
        { name: 'id', align: 'right', label: '# PEDIDO', field: 'id', style: 'width: 5%', sortable: true, sort: (a, b) => Number(a, 10) - Number(b, 10) },
        { name: 'old_folio', align: 'center', label: 'FOLIO ANTERIOR', field: 'old_folio', style: 'width: 10%', sortable: true },
        { name: 'status', align: 'center', label: 'STATUS', field: 'status', style: 'width: 10%', sortable: true },
        { name: 'date', align: 'center', label: 'FECHA', field: 'date', style: 'width: 10%', sortable: true },
        { name: 'branchofficeorigin', align: 'center', label: 'SUCURSAL', field: 'branchofficeorigin', style: 'width: 10%', sortable: true },
        { name: 'invoices', align: 'center', label: 'REMISIÓN', field: 'invoices', style: 'width: 5%', sortable: true, sort: (a, b) => Number(a, 10) - Number(b, 10) },
        { name: 'customer', align: 'center', label: 'CLIENTE', field: 'customer', style: 'width: 20%', sortable: true },
        /*         { name: 'clientbranchoffice', align: 'left', label: 'SUCURSAL', field: 'clientbranchoffice', style: 'width: 10%', sortable: true },
        { name: 'inbulk', align: 'center', label: 'CANT', field: 'inbulk', style: 'width: 10%', sortable: true },
        { name: 'unit', align: 'center', label: 'UNIDAD', field: 'unit', style: 'width: 10%', sortable: true }, */
        { name: 'total', align: 'center', label: 'MONTO', field: 'total', style: 'width: 10%', sortable: true },
        { name: 'user_name', align: 'center', label: 'VENDEDOR', field: 'user_name', style: 'width: 10%', sortable: true }
        // { name: 'rating', align: 'center', label: 'RATING', field: 'rating', style: 'width: 20%', sortable: false }

      ],
      data: [],
      filter: '',
      sellerFlag: []
    }
  },
  computed: {
    isnotAdmin () {
      let ban = false
      if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(4) && !this.$store.getters['users/roles'].includes(3)) {
        ban = true
      }
      return ban
    },
    storageShoppingRules () {
      return [
        val => (this.$v.storageShopping.required) || 'El campo Almacén es requerido.'
      ]
    },
    customerRules (val) {
      return [
        val => (this.$v.customer.required) || 'El campo Nombre es requerido.'
      ]
    },
    officeClientRules (val) {
      return [
        val => (this.$v.clientofficeDestiny.required) || 'El la sucursal del cliente es requerida.'
      ]
    },
    branchRules (val) {
      return [
        val => (this.$v.branch.required) || 'El campo de Sucursal es requerido.'
      ]
    },
    sellerRules (val) {
      return [
        val => (this.$v.seller.required) || 'El campo de Vendedor es requerido.'
      ]
    },
    taxInvoicesRules (val) {
      return [
        val => (this.$v.taxInvoice.required) || 'El campo de Factura o Remisión es requerido.'
      ]
    },
    haspermissionv1 () {
      let permission = false
      if (this.$store.getters['users/roles'].includes(1) || this.$store.getters['users/roles'].includes(20) || this.$store.getters['users/roles'].includes(3) || this.$store.getters['users/roles'].includes(4) || this.$store.getters['users/roles'].includes(7) || this.$store.getters['users/roles'].includes(25) || this.$store.getters['users/roles'].includes(17)) {
        permission = true
      }
      return permission
    },
    haspermissionv2 () {
      let permission = false
      if (this.$store.getters['users/roles'].includes(1) || this.$store.getters['users/roles'].includes(3) || this.$store.getters['users/roles'].includes(4) || this.$store.getters['users/roles'].includes(20) || this.$store.getters['users/roles'].includes(12)) {
        permission = true
      }
      return permission
    }
  },
  beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(4) && !this.$store.getters['users/roles'].includes(2) && !this.$store.getters['users/roles'].includes(23) && !this.$store.getters['users/roles'].includes(20) && !this.$store.getters['users/roles'].includes(17)) {
      this.$router.push('/')
    }
  },
  mounted () {
    this.getClients()
    this.getSellers()
    this.fetchFromServer()
  },
  methods: {
    searchStorage (id) {
      api.get(`/storages/getStoragesOfBranch/${id}`).then(({ data }) => {
        if (data.result) {
          this.storageOptions = data.storage
          console.log(this.storageOptions)
        }
      }).catch()
    },
    getClients () {
      const sellerId = this.$store.getters['users/id']
      api.get(`/customers/getCustomersBySeller/${sellerId}`).then(({ data }) => {
        this.customerOptions = data.options
        this.customerOptionsG = this.customerOptions
        this.customerOptionsG.push({ label: 'TODOS', value: 'TODOS' })
      })
    },
    getSellers () {
      api.get('/users/getSeller').then(({ data }) => {
        this.sellerOptions = data.options
        this.sellerOptionsG = data.options2
        this.sellerOptionsG.push({ label: 'TODOS', value: 'TODOS' })
        this.$q.loading.hide()
      })
    },
    fetchFromServer () {
      this.role = this.$store.getters['users/roles'][0]
      console.log(this.$store.getters)
      this.$q.loading.show()
      this.data = []
      const params = []
      params.customer = this.customerG
      params.status = this.status
      params.seller = this.sellerG
      params.sellerId = this.$store.getters['users/id']
      params.saleDatev1 = this.saleDatev1
      params.saleDatev2 = this.saleDatev2
      params.sellerId = this.$store.getters['users/id']
      api.post('/shopping-carts/getGrid', params).then(({ data }) => {
        console.log(data)
        this.data = data.shoppingCarts
        console.log(this.data)
        this.$q.loading.hide()
      })
    },
    getOfficebyClient () {
      this.officeClientsOptions = []
      const params = []
      params.customer = this.customer
      api.post('/customers/officeoptions', params).then(({ data }) => {
        console.log(data.options)
        if (data.result) {
          this.officeClientsOptions = data.options
          if (data.options.length !== 0) {
            console.log(data.options[0].label)
            this.clientofficeDestiny = { label: data.options[0].label, value: data.options[0].value }
          }
        }
      })
    },
    openSelectedRow (id, status) {
      if (status === 'NUEVO') {
        this.$router.push(`/shopping-carts/${id}`)
      } else {
        this.$router.push(`/shopping-carts/orders/${id}`)
      }
    },
    createShoppingCart () {
      this.$v.customer.$reset()
      this.$v.customer.$touch()
      this.$v.branch.$reset()
      this.$v.branch.$touch()
      this.$v.seller.$reset()
      this.$v.seller.$touch()
      this.$v.clientofficeDestiny.$reset()
      this.$v.clientofficeDestiny.$touch()
      this.$v.taxInvoice.$reset()
      this.$v.taxInvoice.$touch()
      this.$v.storageShopping.$reset()
      this.$v.storageShopping.$touch()
      this.$v.type_order.$reset()
      this.$v.type_order.$touch()
      if (this.$v.customer.$error || this.$v.branch.$error || this.$v.seller.$error || this.$v.clientofficeDestiny.$error || this.$v.taxInvoice.$error || this.$v.storageShopping.$error || this.$v.type_order.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = []
      params.customerId = this.customer
      params.branchOfficeId = this.branch
      params.sellerId = this.seller.value
      params.officedestiny = this.clientofficeDestiny.value
      params.taxInvoice = this.taxInvoice.value
      params.storage_id = this.storageShopping.value
      params.type_order = this.type_order
      api.post('/shopping-carts', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.$router.push(`/shopping-carts-sales-point/${data.shoppingCart.id}`)
        } else {
          this.$q.loading.hide()
        }
      })
    },
    openModal () {
      this.modalPedido = true
      this.seller = { label: this.$store.getters['users/nickname'], value: this.$store.getters['users/id'] }
      // api.get('/users/getSeller').then(({ data }) => {
      //   this.sellerOptions = data.options
      //   this.$q.loading.hide()
      // })
      api.get('/branch-offices/options').then(({ data }) => {
        this.branchOffices = data.options
        this.$q.loading.hide()
      })
    },
    filtrarClientes (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.filteredCustomerOptions = this.customerOptions.filter(v => v.label.toLowerCase().indexOf(needle) > -1 && v.label !== 'TODOS')
      })
    },
    filtrarClientes2 (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.filteredCustomerOptions2 = this.customerOptionsG.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    filterGrid () {
      this.data = []
      const params = []
      params.customer = this.customerG
      params.status = this.status
      params.seller = this.sellerG
      params.sellerId = this.$store.getters['users/id']
      params.saleDatev1 = this.saleDatev1
      params.saleDatev2 = this.saleDatev2
      console.log(params)
      api.post('/shopping-carts/getGrid', params).then(({ data }) => {
        if (data.result) {
          this.data = data.shoppingCarts
        }
      })
    },
    openModalclients (id, branch) {
      // this.dataClient = []
      const params = []
      console.log(params)
      params.id = id
      params.branch = branch
      api.post('/customers/getDataClient', params).then(({ data }) => {
        console.log(data)
        if (data.result) {
          this.modalClient = true
          this.dataClient.fields.id = data.data[0].id
          this.dataClient.fields.branch = data.data[0].branch
          this.dataClient.fields.client = data.data[0].name_client
          this.dataClient.fields.address = data.data[0].address
          this.dataClient.fields.phone = data.data[0].contact_phone
          this.dataClient.fields.term = data.data[0].term
          this.dataClient.fields.branchOffice = data.data[0].branch_name
          this.dataClient.fields.branchAddress = data.data[0].branch_street
          this.dataClient.fields.outdoor = data.data[0].branch_outdoor_number
          this.dataClient.fields.indoor = data.data[0].branch_int_number
          this.dataClient.fields.colony = data.data[0].branch_colony
          this.dataClient.fields.municipality = data.data[0].branch_municipality
          this.dataClient.fields.CP = data.data[0].branch_zip_code
          this.dataClient.fields.branch_phone = data.data[0].branch_phone_number
        }
      })
    },
    openModalclientsBranch (id) {
      const params = []
      params.id = id
      api.post('/customers/getDataClientBranch', params).then(({ data }) => {
        console.log(data)
        if (data.result) {
          this.modalClientBranch = true
          this.dataClientBranch.fields.id = data.data[0].id
          this.dataClientBranch.fields.branchOffice = data.data[0].name
          this.dataClientBranch.fields.branchAddress = data.data[0].street
          this.dataClientBranch.fields.outdoor = data.data[0].outdoor_number
          this.dataClientBranch.fields.indoor = data.data[0].int_number
          this.dataClientBranch.fields.colony = data.data[0].colony
          this.dataClientBranch.fields.municipality = data.data[0].municipality
          this.dataClientBranch.fields.CP = data.data[0].zip_code
          this.dataClientBranch.fields.phone = data.data[0].phone_number
        }
      })
    },
    openActions (id, status) {
      if (status === 'NUEVO') {
        this.$router.push(`/shopping-carts-sales-point/${id}`)
      } else {
        this.$router.push(`/shopping-carts/orders/${id}`)
      }
    },
    openActionRemision (id) {
      this.$router.push(`/storage-exits/${id}`)
    },
    generatePDF () {
      const params = []
      params.customer = this.customerG
      params.status = this.status
      params.seller = this.sellerG
      if (this.saleDatev1) {
        params.saleDatev1 = this.saleDatev1
        while (params.saleDatev1.includes('/')) {
          params.saleDatev1 = params.saleDatev1.replace('/', '-')
        }
      } else {
        params.saleDatev1 = null
      }
      if (this.saleDatev2) {
        params.saleDatev2 = this.saleDatev2
        while (params.saleDatev2.includes('/')) {
          params.saleDatev2 = params.saleDatev2.replace('/', '-')
        }
      } else {
        params.saleDatev2 = null
      }
      const idrole = this.$store.getters['users/id']
      const uri = process.env.API + `shopping-carts/getPdfFromShoppingCarts/${params.customer}/${params.status}/${params.seller}/${params.saleDatev1}/${params.saleDatev2}/${idrole}`
      window.open(uri, '_blank')
    },
    generateCSV () {
      const params = []
      params.customer = this.customerG
      params.status = this.status
      params.seller = this.sellerG
      if (this.saleDatev1) {
        params.saleDatev1 = this.saleDatev1
        while (params.saleDatev1.includes('/')) {
          params.saleDatev1 = params.saleDatev1.replace('/', '-')
        }
      } else {
        params.saleDatev1 = null
      }
      if (this.saleDatev2) {
        params.saleDatev2 = this.saleDatev2
        while (params.saleDatev2.includes('/')) {
          params.saleDatev2 = params.saleDatev2.replace('/', '-')
        }
      } else {
        params.saleDatev2 = null
      }
      const idrol = this.$store.getters['users/id']
      const uri = process.env.API + `shopping-carts/getCSVFromShoppingCarts/${params.customer}/${params.status}/${params.seller}/${params.saleDatev1}/${params.saleDatev2}/${idrol}`
      window.open(uri, '_blank')
    }
  }
}
</script>

<style>
</style>

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
              <q-btn v-if="roleId === 1 || roleId === 2 || roleId === 3 || roleId === 4 || roleId === 7 || roleId === 17 || roleId === 20 || roleId === 25 || roleId === 29 || roleId === 27 || roleId === 17" color="green" style="margin-left: 10px;" icon="fas fa-file-excel" @click="generateCSV()">
                <q-tooltip>GENERAR CSV</q-tooltip>
              </q-btn>
              <q-btn v-if="roleId === 1 || roleId === 2 || roleId === 3 || roleId === 4 || roleId === 7 || roleId === 17 || roleId === 20 || roleId === 25 || roleId === 29 || roleId === 27 || roleId === 17" color="red" style="margin-left: 10px;" icon="fas fa-file-pdf" @click="generatePDF()">
                <q-tooltip>GENERAR PDF</q-tooltip>
              </q-btn>
              <q-btn v-if="roleId === 1 || roleId === 3 || roleId === 4 || roleId === 12 || roleId === 20 || roleId === 29 || roleId === 27 || roleId === 22 || roleId === 28 || roleId === 17" class="bg-primary" style="margin-left: 10px; color: white;" icon="add" label="Nuevo" @click.native="openModal()">
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
            <!-- <div class="col-sm-2"></div> -->
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
                      @input="fetchFromServer()"
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
                      @input="fetchFromServer()"
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
                        @input="fetchFromServer()"
              >
              </q-select>
            </div>
            <div class="col-sm-2">
              <!-- Quite este metodo filterGrid() -->
                  <q-select color="dark" bg-color="secondary" filled
                            v-model="filter_special_order"
                            :options="
                            [
                              {label: 'TODOS', value: 'TODOS'},
                              {label: 'NORMAL', value: 0},
                              {label: 'ESPECIAL', value: 1}
                            ]"
                            @input="fetchFromServer()"
                            use-input
                            label="Pedido"
                            emit-value
                            map-options
                            >
                    <!-- <template v-slot:prepend>
                      <q-icon name="grade" />
                    </template> -->
                  </q-select>
                </div>
            <div class="col-sm-2">
              <q-select  color="dark" bg-color="secondary" filled
                        v-model="customerG"
                        :options="filteredCustomerOptions2"
                        @filter="filterCustomer"
                        @input="fetchFromServer()"
                        use-input
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
                      {label: 'COTIZADO', value: 'COTIZADO'},
                      {label: 'AUTORIZADO', value: 'AUTORIZADO'},
                      {label: 'PARCIAL', value: 'PARCIAL'},
                      {label: 'REMISIONADO', value: 'REMISIONADO'},
                      {label: 'ENTREGADO', value: 'ENVIADO'}
                    ]"
                        emit-value map-options
                        label="Estatus"
                        @input="fetchFromServer()"
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
                  <q-td key="id" :props="props" style="width: 5%; text-align: center;"><label @click="openActions(props.row.id, props.row.status)" class="text-primary" style="text-decoration: underline; cursor: pointer;">{{ props.row.id }}</label></q-td>
                  <q-td key="old_folio" :props="props" style="width: 10%;">{{ props.row.old_folio }}</q-td>
                  <q-td key="status" :props="props" style="width: 10%; text-align: center;">
                    <q-chip square dense :color="props.row.status == 'COTIZADO' ? 'yellow-8' : (props.row.status == 'NUEVO' ? 'blue-7' : (props.row.status == 'AUTORIZADO' ? 'green' : (props.row.status == 'PARCIAL' ? 'red-4' : (props.row.status == 'ENTREGADO' ? 'purple-6': (props.row.status == 'CANCELADO' ? 'red': 'red-4')))))" text-color="white">
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
                  <q-td key="special_order" :props="props" style="width: 20%;"><label >{{ props.row.special_order === 0 || props.row.special_order === null ? 'NORMAL' : 'ESPECIAL' }}</label></q-td>
                  <q-td key="customer_name" :props="props" style="width: 20%;"><label @click="openModalclients(props.row.id_client,props.row.branchofficedestiny)" style="text-decoration: underline black; cursor: pointer;">{{ props.row.customer_name }}</label></q-td>
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
      <q-card style="min-width: 90%; !important;min-hight: 100%">
        <q-card-section class="bg-primary">
         <div class="row">
           <div class="col-sm-11 text-h6" style="color: white">CREAR PEDIDO</div>
           <div class="col-sm-1 pull-right"><q-btn color="white" flat v-close-popup round dense icon="close" /></div>
         </div>
        </q-card-section>
        <q-separator />
        <q-card-section style="max-height: 70vh" class="">
          <div class="text-overline" style="font-size: 16px;">Datos del cliente</div>
          <div class="row bg-white border-panel">
            <div class="col q-pa-md">
              <div class="row q-col-gutter-xs q-col-gutter-md">
                <div class="col-xs-12 col-md-3">
                  <q-select
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="station"
                    :options="branchOptions"
                    :error="$v.station.$error"
                    use-input
                    label="Estación"
                    hide-selected
                    fill-input
                    input-debounce="0"
                    @filter="filterBranchOffices"
                    emit-value
                    map-options
                    :rules="stationRules">
                    <template v-slot:prepend>
                      <q-icon name="person" />
                    </template>
                  </q-select>
                </div>
                <div class="col-xs-12 col-md-3">
                  <q-select color="dark" bg-color="secondary"
                            filled
                            v-model="customer"
                            :options="customerOptionsModal"
                            label="Cliente"
                            >
                    <template v-slot:prepend>
                      <q-icon name="person" />
                    </template>
                  </q-select>
                </div>
                <div class="col-xs-3">
                    <q-select
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="orderDate"
                    mask="date"
                    label="Fecha pedido">
                      <q-popup-proxy ref="date" transition-show="scale" transition-hide="scale">
                        <div class="col-sm-12">
                          <q-date
                          v-model="orderDate"
                          mask="DD/MM/YYYY"
                          today-btn/>
                        </div>
                      </q-popup-proxy>
                      <template v-slot:prepend>
                        <q-icon name="date_range"/>
                      </template>
                    </q-select>
                  </div>
                <div class="col-xs-3">
                    <q-select
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="pledgeDate"
                    mask="date"
                    label="Fecha compromiso">
                      <q-popup-proxy ref="date" transition-show="scale" transition-hide="scale">
                        <div class="col-sm-12">
                          <q-date v-model="pledgeDate" mask="DD/MM/YYYY" today-btn/>
                        </div>
                      </q-popup-proxy>
                      <template v-slot:prepend>
                        <q-icon name="date_range"/>
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
                <div class="col-xs-12 col-md-3">
                  <q-select
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="supplier"
                    :options="supplierOptions"
                    :error="$v.supplier.$error"
                    use-input
                    label="Proveedor"
                    hide-selected
                    fill-input
                    input-debounce="0"
                    @filter="filterSuppliers"
                    emit-value
                    map-options
                    :rules="supplierRules">
                    <template v-slot:prepend>
                      <q-icon name="person" />
                    </template>
                  </q-select>
                </div>
                <div class="col-xs-12 col-md-3">
                  <q-select color="dark" bg-color="secondary" filled
                            v-model="branch"
                            :error="$v.branch.$error"
                            :options="branchOffices"
                            label="Estación de envío"
                            emit-value map-options
                            @input="searchStorage(branch)"
                            :rules="branchRules">
                    <template v-slot:prepend>
                      <q-icon name="person" />
                    </template>
                  </q-select>
                </div>
                <div class="col-xs-12 col-md-3">
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
                <!-- <div class="col-xs-12 col-md-3">
                  <q-select color="dark" bg-color="secondary" filled
                            v-model="seller"
                            :options="filterSelleroptions"
                            label="Vendedor"
                            :rules="sellerRules"
                            :error="$v.seller.$error"
                            :disable="isnotAdmin">
                    <template v-slot:prepend>
                      <q-icon name="person" />
                    </template>
                  </q-select>
                </div> -->
                <!-- <div class="col-xs-12 col-md-3">
                  <q-select color="dark" bg-color="secondary" filled
                            v-model="taxInvoice"
                            :error="$v.taxInvoice.$error"
                            :options="[
                            { label: 'FACTURA', value: 0 },
                            { label: 'REMISIÓN', value: 1}
                            ]"
                            :rules="taxInvoicesRules"
                            label="¿Desea factura o remisión?"
                            @input="checkRemision()"
                            >
                    <template v-slot:prepend>
                      <q-icon name="fas fa-cash-register" />
                    </template>
                  </q-select>
                </div> -->
                <div class="col-xs-12 col-md-12 text-right">
              <q-btn flat label="Crear" color="white" style="background-color: #21ba45;" @click="createShoppingCart()" />
              </div>
              </div>
            </div>
          </div>
        </q-card-section>
        <!-- <q-separator />
        <q-card-actions align="right">
          <q-btn flat label="Crear" color="white" style="background-color: #21ba45;" @click="createShoppingCart()" />
        </q-card-actions> -->
      </q-card>
    </q-dialog>
    <q-dialog v-model="modalClient" persistent>
      <q-card style="min-width: 50%; !important;">
        <q-card-section class="bg-primary">
         <div class="row">
           <div class="col-sm-11 text-h6" style="color:white;">DATOS DEL CLIENTE</div>
           <div class="col-sm-1 pull-right"><q-btn color="white" flat v-close-popup round dense icon="close" @click="cleanClient()" /></div>
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
    <q-dialog v-model="cliente_modal" >
      <q-card style="min-width: 85%; !important;">
        <q-card-section class="bg-primary">
         <div class="row">
           <div class="col-sm-11 text-h6" style="color:white;">REGISTRO DE UN CLIENTE</div>
           <div class="col-sm-1 pull-right"><q-btn color="white" flat v-close-popup round dense icon="close" @click="cleanNewClient()"/></div>
         </div>
        </q-card-section>
        <q-separator />
        <q-card-section style="max-height: 50vh" class="scroll">
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
                v-model="customerCreated.fields.serial"
                :error="$v.customerCreated.fields.serial.$error"
                label="Código"
                :rules="serialRules"
                @input="v => { customerCreated.fields.serial = v.toUpperCase() }"
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
                v-model="customerCreated.fields.admission_date"
                :error="$v.customerCreated.fields.admission_date.$error"
                mask="date"
                label="Fecha de Alta"
                :rules="admissionRules"
                @input="v => { customerCreated.fields.admission_date = v.toUpperCase() }"
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
                        v-model="customerCreated.fields.admission_date"
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
                v-model="customerCreated.fields.name"
                :error="$v.customerCreated.fields.name.$error"
                label="Razón social"
                :rules="nameRules"
                @input="v => { customerCreated.fields.name = v.toUpperCase() }"
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
                v-model="customerCreated.fields.active"
                :options="[
                  {label: 'ACTIVO', value: true},
                  {label: 'INACTIVO', value: false}
                ]"
                label="Estatus"
              >
                <template v-slot:prepend>
                  <q-icon :name="(customerCreated.fields.active.value ? 'battery_full' : 'battery_alert')" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-6">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="customerCreated.fields.contact_name"
                :error="$v.customerCreated.fields.contact_name.$error"
                label="Nombre contacto"
                :rules="contactNameRules"
                @input="v => { customerCreated.fields.contact_name = v.toUpperCase() }"
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
                v-model="customerCreated.fields.rfc"
                :error="$v.customerCreated.fields.rfc.$error"
                label="RFC"
                :rules="rfcRules"
                @input="v => { customerCreated.fields.rfc = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="insert_drive_file" />
                </template>
              </q-input>
            </div>
                    <div class="col-xs-12 col-sm-6">
                      <q-input
                        color="dark"
                        bg-color="secondary"
                        filled
                        v-model="customerCreated.fields.email"
                        :error="$v.customerCreated.fields.email.$error"
                        label="Dirección de correo electrónico"
                        :rules="emailRulesCreated"
                        @input="v => { customerCreated.fields.email = v.toLowerCase() }"
                      >
                        <template v-slot:prepend>
                          <q-icon name="email" />
                        </template>
                      </q-input>
                    </div>
            <div class="col-xs-12 col-sm-6">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="customerCreated.fields.email2"
                :error="$v.customerCreated.fields.email2.$error"
                label="Dirección de correo electrónico 2"
                :rules="email2Rules"
                @input="v => { customerCreated.fields.email2 = v.toLowerCase() }"
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
                v-model="customerCreated.fields.tradename"
                label="Nombre comercial"
                @input="v => { customerCreated.fields.tradename = v.toUpperCase() }"
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
                v-model="customerCreated.fields.contact_phone"
                :error="$v.customerCreated.fields.contact_phone.$error"
                label="Teléfono"
                :rules="contactPhoneRules"
                @input="v => { customerCreated.fields.contact_phone = v.replace(/[^0-9]/g, '').substr(0, 20) }"
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
                v-model="customerCreated.fields.contact_phone_res"
                :error="$v.customerCreated.fields.contact_phone_res.$error"
                label="Teléfono 2"
                :rules="contactPhoneRulesRes"
                @input="v => { customerCreated.fields.contact_phone_res = v.replace(/[^0-9]/g, '').substr(0, 20) }"
              >
                <template v-slot:prepend>
                  <q-icon name="contact_phone" />
                </template>
              </q-input>
            </div>
             <div class="col-xs-12 col-sm-3">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="customerCreated.fields.payment_method"
                :error="$v.customerCreated.fields.payment_method.$error"
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
                v-model="customerCreated.fields.credit_days"
                :error="$v.customerCreated.fields.credit_days.$error"
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
                v-model="customerCreated.fields.credit_limit"
                :error="$v.customerCreated.fields.credit_limit.$error"
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
                v-model="customerCreated.fields.currency"
                :error="$v.customerCreated.fields.currency.$error"
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
                v-model="customerCreated.fields.priceList"
                :error="$v.customerCreated.fields.priceList.$error"
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
                v-model="customerCreated.fields.discount"
                :error="$v.customerCreated.fields.discount.$error"
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
                v-model="customerCreated.fields.seller"
                :error="$v.customerCreated.fields.seller.$error"
                :rules="sellerRulesCreated"
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
                v-model="customerCreated.fields.term"
                :error="$v.customerCreated.fields.term.$error"
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
                v-model="customerCreated.fields.channel_id"
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
                v-model="customerCreated.fields.street"
                :error="$v.customerCreated.fields.street.$error"
                label="Calle"
                :rules="streetRules"
                @input="v => { customerCreated.fields.street = v.toUpperCase() }"
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
                v-model="customerCreated.fields.outdoor_number"
                :error="$v.customerCreated.fields.outdoor_number.$error"
                label="Número ext."
                :rules="outdoorNumberRules"
                @input="v => { customerCreated.fields.outdoor_number = v.toUpperCase() }"
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
                v-model="customerCreated.fields.indoor_number"
                :error="$v.customerCreated.fields.indoor_number.$error"
                label="Número int."
                :rules="indoorNumberRules"
                @input="v => { customerCreated.fields.indoor_number = v.toUpperCase() }"
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
                v-model="customerCreated.fields.city"
                :error="$v.customerCreated.fields.city.$error"
                label="Ciudad"
                :rules="cityRules"
                @input="v => { customerCreated.fields.city = v.toUpperCase() }"
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
                v-model="customerCreated.fields.municipality"
                :error="$v.customerCreated.fields.municipality.$error"
                label="Municipio"
                :rules="municipalityRules"
                @input="v => { customerCreated.fields.municipality = v.toUpperCase() }"
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
                v-model="customerCreated.fields.suburb"
                :error="$v.customerCreated.fields.suburb.$error"
                label="Colonia"
                :rules="suburbRules"
                @input="v => { customerCreated.fields.suburb = v.toUpperCase() }"
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
                v-model="customerCreated.fields.zip_code"
                :error="$v.customerCreated.fields.zip_code.$error"
                label="CP"
                :rules="zipCodeRules"
                @input="v => { customerCreated.fields.zip_code = v.replace(/[^0-9]/g, '').substr(0, 5) }"
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
                v-model="customerCreated.fields.state"
                :error="$v.customerCreated.fields.state.$error"
                label="Estado"
                :rules="stateRules"
                @input="v => { customerCreated.fields.state = v.toUpperCase() }"
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
                v-model="customerCreated.fields.country"
                :error="$v.customerCreated.fields.country.$error"
                label="País"
                :rules="countryRules"
                @input="v => { customerCreated.fields.country = v.toUpperCase() }"
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
        <div class="row bg-white border-panel">
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
                v-model="customerCreated.fields.requirements"
                label="Requerimientos especiales"
                @input="v => { customerCreated.fields.requirements = v.toUpperCase() }"
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
                v-model="customerCreated.fields.documents"
                label="Documentos requeridos"
                @input="v => { customerCreated.fields.documents = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-file-alt" />
                </template>
              </q-input>
            </div>
          </div>
        </div>
      </div>
    </div>
        </q-card-section>
        <q-separator />
        <q-card-section>
          <div class="row">
            <div class="col-sm-9 pull-right">
            </div>
           <div class="col-sm-3 pull-right">
            <q-btn color="positive" icon="save" label="Guardar" @click="createcustomerCreated()" />
          </div>
         </div>
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import api from '../../commons/api.js'
const { required, requiredIf, email, maxLength, minLength, integer, minValue, decimal } = require('vuelidate/lib/validators')

export default {
  name: 'IndexShoppingCarts',
  validations: {
    customer: { required },
    branch: { required },
    station: { required },
    supplier: { required },
    seller: { required },
    taxInvoice: { },
    storageShopping: { },
    commercial_terms: { },
    validity: { },
    lab: { },
    loan: { },
    customerEmail: { email },
    customerCreated: {
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
        country: { required, maxLength: maxLength(100) },
        state: { required, maxLength: maxLength(100) },
        city: { maxLength: maxLength(100) },
        zip_code: { required, maxLength: maxLength(5), integer, minValue: minValue(1) },
        rfc: { required, maxLength: maxLength(20) },
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
      filter_special_order: 'TODOS',
      special_order: 0,
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
      currencyOptions: [],
      cliente_modal: false,
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
      station: null,
      supplier: null,
      customerG: 'TODOS',
      branch: null,
      seller: null,
      sellerG: 'TODOS',
      status: 'TODOS',
      loan: 0,
      payment_method: null,
      storageShopping: null,
      storageOptions: [],
      customerOptions: [],
      customerOptionsG: [],
      customerOptionsFilter: [],
      officeClientsOptions: [],
      branchOptions: this.branchOfficeOptions,
      supplierOptions: this.optionsSuppliers,
      branchOfficeOptions: [],
      optionsSuppliers: [],
      sellerOptions: [],
      sellerOptionsG: [],
      branchOffices: [],
      filteredCustomerOptions: [],
      filteredCustomerOptions2: [],
      commercial_terms: null,
      validity: null,
      emailfactura: null,
      selectLoan: [
        { label: 'NO', value: 0 },
        { label: 'SI', value: 1 }
      ],
      lab: null,
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
      customerCreated: {
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
        sortBy: 'code',
        descending: true,
        page: 1,
        rowsNumber: 0,
        rowsPerPage: 25
      },
      columns: [
        { name: 'id', align: 'right', label: '# PEDIDO', field: 'id', style: 'width: 5%', sortable: true, sort: (a, b) => Number(a, 10) - Number(b, 10) },
        { name: 'old_folio', align: 'center', label: 'FOLIO ANTERIOR', field: 'old_folio', style: 'width: 10%', sortable: true },
        { name: 'status', align: 'center', label: 'STATUS', field: 'status', style: 'width: 10%', sortable: true },
        { name: 'date', align: 'center', label: 'FECHA', field: 'date', style: 'width: 10%', sortable: true },
        { name: 'branchofficeorigin', align: 'center', label: 'ESTACIÓN', field: 'branchofficeorigin', style: 'width: 10%', sortable: true },
        { name: 'invoices', align: 'center', label: 'REMISIÓN', field: 'invoices', style: 'width: 5%', sortable: true, sort: (a, b) => Number(a, 10) - Number(b, 10) },
        { name: 'special_order', align: 'CENTER', label: 'PEDIDO', field: 'special_order', style: 'width: 20%', sortable: true },
        { name: 'customer_name', align: 'CENTER', label: 'CLIENTE', field: 'customer_name', style: 'width: 20%', sortable: true },
        /*         { name: 'clientbranchoffice', align: 'left', label: 'SUCURSAL', field: 'clientbranchoffice', style: 'width: 10%', sortable: true },
        { name: 'inbulk', align: 'center', label: 'CANT', field: 'inbulk', style: 'width: 10%', sortable: true },
        { name: 'unit', align: 'center', label: 'UNIDAD', field: 'unit', style: 'width: 10%', sortable: true }, */
        { name: 'total', align: 'CENTER', label: 'MONTO', field: 'total', style: 'width: 10%', sortable: true },
        { name: 'user_name', align: 'CENTER', label: 'VENDEDOR', field: 'user_name', style: 'width: 10%', sortable: true }
        // { name: 'rating', align: 'center', label: 'RATING', field: 'rating', style: 'width: 20%', sortable: false }

      ],
      data: [],
      filter: '',
      sellerFlag: [],
      dateShopping: null,
      orderDate: null,
      pledgeDate: null,
      commentShopping: null,
      customerOptionsModal: [],
      optionContacts: [],
      optionsContactsFilter: [],
      customerOptionsByBranch: [],
      createOrder: false,
      auxCliente: null,
      customerEmail: null,
      channelOptions: [],
      enableFields: false
    }
  },
  computed: {
    emailRules (val) {
      return [
        val => this.$v.customerEmail.email || 'El campo Dirección de correo electrónico debe contener una dirección de correo electrónico válida.'
      ]
    },
    isnotAdmin () {
      const user = this.$store.getters['users/rol']
      let ban = false
      if (user !== 1 && user !== 4 && user !== 3 && user !== 20 && user !== 29 && user !== 27 && user !== 22 && user !== 17) {
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
    serialRules (val) {
      return [
        val => (this.$v.customerCreated.fields.serial.required) || 'El campo Código es requerido.',
        val => (this.$v.customerCreated.fields.serial.maxLength) || 'El campo Código no debe exceder los 10 dígitos.'
      ]
    },
    sellerRulesCreated (val) {
      return [
        val => this.$v.customerCreated.fields.seller.required || 'El campo Vendedor es requerido.'
      ]
    },
    discountRules (val) {
      return [
        val => this.$v.customerCreated.fields.discount.decimal || 'El campo Descuento debe ser númerico.',
        val => this.$v.customerCreated.fields.discount.minValue || 'El campo Descuento debe ser positivo.'
      ]
    },
    admissionRules (val) {
      return [
        val => (this.$v.customerCreated.fields.admission_date.required) || 'El campo Fecha de alta es requerido.'
      ]
    },
    nameRules (val) {
      return [
        val => this.$v.customerCreated.fields.name.required || 'El campo Nombre es requerido.',
        val => this.$v.customerCreated.fields.name.maxLength || 'El campo Nombre no debe exceder los 100 dígitos.'
      ]
    },
    /* tradenameRules (val) {
      return [
        val => this.$v.customerCreated.fields.tradename.required || 'El campo Nombre comercial es requerido.',
        val => this.$v.customerCreated.fields.tradename.maxLength || 'El campo Nombre comercial no debe exceder los 100 dígitos.'
      ]
    }, */
    contactNameRules (val) {
      return [
        val => this.$v.customerCreated.fields.contact_name.maxLength || 'El campo Nombre Contacto no debe exceder los 100 dígitos.'
      ]
    },
    creditDayRules (val) {
      return [
        val => this.$v.customerCreated.fields.credit_days.required || 'El campo Días de Crédito es requerido.'
      ]
    },
    creditLimitRules (val) {
      return [
        val => this.$v.customerCreated.fields.credit_limit.required || 'El campo Límite de Crédito es requerido.',
        val => this.$v.customerCreated.fields.credit_limit.decimal || 'El campo Límite de Crédito debe ser númerico.'
      ]
    },
    contactPhoneRules (val) {
      return [
        val => this.$v.customerCreated.fields.contact_phone.integer || 'El campo Teléfono debe ser numérico.',
        val => this.$v.customerCreated.fields.contact_phone.minValue || 'El campo Teléfono debe ser positivo.',
        val => this.$v.customerCreated.fields.contact_phone.minLength || 'El campo Teléfono debe tener al menos 10 dígitos.',
        val => this.$v.customerCreated.fields.contact_phone.maxLength || 'El campo Teléfono no debe exceder los 20 dígitos.'
      ]
    },
    contactPhoneRulesRes (val) {
      return [
        val => this.$v.customerCreated.fields.contact_phone_res.integer || 'El campo Teléfono debe ser numérico.',
        val => this.$v.customerCreated.fields.contact_phone_res.minValue || 'El campo Teléfono debe ser positivo.',
        val => this.$v.customerCreated.fields.contact_phone_res.minLength || 'El campo Teléfono debe tener al menos 10 dígitos.',
        val => this.$v.customerCreated.fields.contact_phone_res.maxLength || 'El campo Teléfono no debe exceder los 20 dígitos.'
      ]
    },
    streetRules (val) {
      return [
        val => this.$v.customerCreated.fields.street.required || 'El campo Calle es requerido.',
        val => this.$v.customerCreated.fields.street.maxLength || 'El campo Calle no debe exceder los 100 dígitos.'
      ]
    },
    outdoorNumberRules (val) {
      return [
        val => this.$v.customerCreated.fields.outdoor_number.required || 'El campo Número ext. es requerido.',
        val => this.$v.customerCreated.fields.outdoor_number.maxLength || 'El campo Número ext. no debe exceder los 10 dígitos.'
      ]
    },
    indoorNumberRules (val) {
      return [
        val => this.$v.customerCreated.fields.indoor_number.maxLength || 'El campo Número int. no debe exceder los 10 dígitos.'
      ]
    },
    suburbRules (val) {
      return [
        val => this.$v.customerCreated.fields.suburb.required || 'El campo Colonia es requerido.',
        val => this.$v.customerCreated.fields.suburb.maxLength || 'El campo Colonia no debe exceder los 100 dígitos.'
      ]
    },
    municipalityRules (val) {
      return [
        val => this.$v.customerCreated.fields.municipality.maxLength || 'El campo Municipio no debe exceder los 100 dígitos.'
      ]
    },
    stateRules (val) {
      return [
        val => this.$v.customerCreated.fields.state.required || 'El campo Estado es requerido.',
        val => this.$v.customerCreated.fields.state.maxLength || 'El campo Estado no debe exceder los 100 dígitos.'
      ]
    },
    countryRules (val) {
      return [
        val => this.$v.customerCreated.fields.country.required || 'El campo País es requerido.'
      ]
    },
    cityRules (val) {
      return [
        val => this.$v.customerCreated.fields.city.maxLength || 'El campo Ciudad no debe exceder los 100 dígitos.'
      ]
    },
    zipCodeRules (val) {
      return [
        val => this.$v.customerCreated.fields.zip_code.required || 'El campo CP es requerido.',
        val => this.$v.customerCreated.fields.zip_code.integer || 'El campo CP debe ser numérico.',
        val => this.$v.customerCreated.fields.zip_code.minValue || 'El campo CP debe ser positivo.',
        val => this.$v.customerCreated.fields.zip_code.maxLength || 'El campo CP no debe exceder los 5 dígitos.'
      ]
    },
    rfcRules (val) {
      return [
        val => this.$v.customerCreated.fields.rfc.required || 'El campo RFC es requerido.',
        val => this.$v.customerCreated.fields.rfc.maxLength || 'El campo RFC no debe exceder los 20 dígitos.'
      ]
    },
    termRules (val) {
      return [
        val => this.$v.customerCreated.fields.term.required || 'El campo Plazo es requerido.',
        val => this.$v.customerCreated.fields.term.maxLength || 'El campo Plazo no debe exceder los 20 dígitos.'
      ]
    },
    paymentMethodRules (val) {
      return [
        val => this.$v.customerCreated.fields.payment_method.required || 'El campo Forma de pago es requerido.',
        val => this.$v.customerCreated.fields.payment_method.maxLength || 'El campo Forma de pago no debe exceder los 100 dígitos.'
      ]
    },
    priceListRules (val) {
      return [
        val => this.$v.customerCreated.fields.priceList.required || 'El campo Precio de lista es requerido.'
      ]
    },
    currencyRules (val) {
      return [
        val => this.$v.customerCreated.fields.currency.required || 'El campo Moneda es requerido.'
      ]
    },
    emailRulesCreated (val) {
      return [
        val => this.$v.customerCreated.fields.email.required || 'El campo Dirección de correo electrónico es requerido.',
        val => this.$v.customerCreated.fields.email.email || 'El campo Dirección de correo electrónico debe contener una dirección de correo electrónico válida.'
      ]
    },
    email2Rules (val) {
      return [
        val => this.$v.customerCreated.fields.email2.email || 'El campo Dirección de correo electrónico 2 debe contener una dirección de correo electrónico válida.'
      ]
    },
    email3Rules (val) {
      return [
        val => this.$v.customerCreated.fields.email3.email || 'El campo Dirección de correo electrónico 3 debe contener una dirección de correo electrónico válida.'
      ]
    },
    email4Rules (val) {
      return [
        val => this.$v.customerCreated.fields.email4.email || 'El campo Dirección de correo electrónico 4 debe contener una dirección de correo electrónico válida.'
      ]
    },
    customerCreatedZipCodeRules (val) {
      return [
        val => this.$v.customerCreated.fields.postal_code_id.required || 'El campo Codigo postal es requerido.'
      ]
    },
    customerCreatedsuburbRules (val) {
      return [
        val => (this.$v.customerCreated.fields.suburb_id.required) || 'El campo Colonia es requerido.'
      ]
    },
    customerCreatedSuburbRules (val) {
      return [
        val => (this.$v.customerCreated.fields.suburb.required) || 'El campo Colonia es requerido.'
      ]
    },
    customerCreatedbetween_streetRules (val) {
      return [
        val => (this.$v.customerCreated.fields.between_street.required) || 'El campo Entre calles es requerido.'
      ]
    },
    /* officeClientRules (val) {
      return [
        val => (this.$v.clientofficeDestiny.required) || 'La estación del cliente es requerida.'
      ]
    }, */
    stationRules (val) {
      return [
        val => (this.$v.station.required) || 'La estación es requerida.'
      ]
    },
    supplierRules (val) {
      return [
        val => (this.$v.supplier.required) || 'El proveedor es requerido.'
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
    /* taxInvoicesRules (val) {
      return [
        val => (this.$v.taxInvoice.required) || 'El campo de Factura o Remisión es requerido.'
      ]
    }, */
    roleId () {
      const user = this.$store.getters['users/rol']
      return parseInt(user)
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
    },
    filterSelleroptions () {
      if (this.branch != null) {
        return this.sellerOptions.filter(se => se.branch_office_id === this.branch || se.branch_office_id === 0)
      }
      return []
    }
  },
  beforeRouteEnter (to, from, next) {
    next(vm => {
      const propiedades = vm.$store.getters['users/rol']
      console.log(propiedades)
      if (propiedades === 1 || propiedades === 3 || propiedades === 7 || propiedades === 2 || propiedades === 20 || propiedades === 4 || propiedades === 27 || propiedades === 22 || propiedades === 29 || propiedades === 28 || propiedades === 17) {
        next()
      } else {
        next('/')
      }
    })
  },
  /* beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(4) && !this.$store.getters['users/roles'].includes(2) && !this.$store.getters['users/roles'].includes(23) && !this.$store.getters['users/roles'].includes(20) && !this.$store.getters['users/roles'].includes(17)) {
      this.$router.push('/')
    }
  }, */
  mounted () {
    console.log(this.$store.getters['users/id'])
    this.getClients()
    // this.getClientsToFilter()
    this.getSellers()
    this.fetchFromServer()
    this.getBranchOffices()
    this.getSuppliers()
    this.getCurrencies()
  },
  methods: {
    getClientsToFilter () {
      const sellerId = this.$store.getters['users/id']
      api.get(`/customers/getCustomersBySeller/${sellerId}`).then(({ data }) => {
        this.customerOptionsFilter = data.options
      })
    },
    filterCustomer (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.filteredCustomerOptions2 = this.customerOptionsFilter.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    methodSelected () {
      if (this.customerCreated.fields.payment_method === 'CREDITO') {
        this.enableFields = true
      } else {
        this.enableFields = false
        this.customerCreated.fields.credit_days = null
        this.customerCreated.fields.credit_limit = null
      }
      console.log(this.enableFields)
    },
    checkRemision () {
      if (this.taxInvoice.value === 1 && this.loan === 1) {
        this.$q.notify({
          message: 'Para una remisión el pedido no debe ser tipo prestamo.',
          position: 'top',
          color: 'warning'
        })
        // this.taxInvoice = { label: 'REMISIÓN', value: 0 }
        this.taxInvoice = { label: 'FACTURA', value: 0 }
      }
    },
    checkLoan () {
      if (this.loan === 1) {
        this.taxInvoice = { label: 'FACTURA', value: 0 }
      } else {
        this.taxInvoice = null
      }
    },
    searchStorage (id) {
      // this.seller = null
      this.storageShopping = null
      api.get(`/storages/getStoragesOfBranch/${id}`).then(({ data }) => {
        if (data.result) {
          this.storageOptions = data.storage
          console.log(data.storage)
          var storagerebasa = data.storage.filter(st => st.value === 39)
          var storagereManguera = data.storage.filter(st => st.value === 44)
          var storagereSalle = data.storage.filter(st => st.value === 47)
          var storagereRodamientos = data.storage.filter(st => st.value === 46)
          if (this.branch.value === 9 || this.branch === 9) {
            this.storageShopping = { label: storagerebasa[0].label, value: storagerebasa[0].value }
          }
          if (this.branch.value === 12 || this.branch === 12) {
            this.storageShopping = { label: storagereManguera[0].label, value: storagereManguera[0].value }
          }
          if (this.branch.value === 13 || this.branch === 13) {
            this.storageShopping = { label: storagereSalle[0].label, value: storagereSalle[0].value }
          }
          if (this.branch.value === 14 || this.branch === 14) {
            this.storageShopping = { label: storagereRodamientos[0].label, value: storagereRodamientos[0].value }
          }
          // console.log(this.storageOptions)
        }
      }).catch()
    },
    getClients () {
      const sellerId = this.$store.getters['users/id']
      api.get(`/customers/getCustomersBySeller/${sellerId}`).then(({ data }) => {
        this.customerOptions = data.options
        this.customerOptionsG = this.customerOptions
        this.customerOptionsG.push({ label: 'TODOS', value: 'TODOS' })
        this.customerOptionsFilter = data.options
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
      this.$q.loading.show()
      this.qTableRequest({
        pagination: this.pagination,
        filter: this.filter
      })
      /* this.role = this.$store.getters['users/roles'][0]
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
        this.data = data.shoppingCarts
        this.$q.loading.hide()
      }) */
    },
    async qTableRequest (props) {
      this.pagination = props.pagination
      this.filter = props.filter
      this.role = this.$store.getters['users/roles'][0]
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
      params.pagination = this.pagination
      params.filter = this.filter
      params.filter_special_order = this.filter_special_order
      await api.post('/shopping-carts/getGridByPagination', params).then(({ data }) => {
        this.$q.loading.hide()
        this.data = data.shoppingCarts
        this.pagination.rowsNumber = data.productsCount
      }).catch(error => error)
    },
    getOfficebyClient () {
      // this.officeClientsOptions = []
      const params = []
      params.customer = this.customer
      /* api.post('/customers/officeoptions', params).then(({ data }) => {
        if (data.result) {
          this.officeClientsOptions = data.options
          if (data.options.length !== 0) {
            console.log(data.options[0].label)
            this.clientofficeDestiny = { label: data.options[0].label, value: data.options[0].value }
          }
        }
      }) */
      const id = this.customer
      api.get(`/customer-contacts/getContacts/${id}`).then(({ data }) => {
        if (data.result) {
          this.optionContacts = data.contacts
        }
      })
      api.get(`/customers/${id}`).then(({ data }) => {
        if (data.result) {
          this.payment_method = data.customer.payment_method
          this.customerEmail = data.customer.email
        }
      })
      if (id != null) {
        api.get(`/customer-tax-companies/getcustomerTaxCompanyByClient/${id}`).then(({ data }) => {
          if (data.result) {
            this.auxCliente = data.customerTaxCompanies.yesornotdatafs
          }
        })
      }
    },
    getBranchOffices () {
      api.get('/branch-offices/branchOptions').then(({ data }) => {
        this.branchOfficeOptions = data.options
      })
    },
    filterBranchOffices (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.branchOptions = this.branchOfficeOptions.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    getSuppliers () {
      api.get('/suppliers/options').then(({ data }) => {
        this.optionsSuppliers = data.options
      })
    },
    filterSuppliers (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.supplierOptions = this.optionsSuppliers.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    /* getCustomerByBranch () {
      this.customer = { value: null, label: null }
      api.get(`/branch-offices/customer/${this.station}`).then(({ data }) => {
        this.customer = { value: data.customers.customer_id, label: data.customers.customer }
        console.log(this.customer)
      })
    }, */
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
      this.$v.station.$reset()
      this.$v.station.$touch()
      this.$v.taxInvoice.$reset()
      this.$v.taxInvoice.$touch()
      this.$v.supplier.$reset()
      this.$v.supplier.$touch()
      this.$v.storageShopping.$reset()
      this.$v.storageShopping.$touch()
      this.$v.commercial_terms.$reset()
      this.$v.commercial_terms.$touch()
      this.$v.validity.$reset()
      this.$v.validity.$touch()
      this.$v.lab.$reset()
      this.$v.lab.$touch()
      this.$v.loan.$reset()
      this.$v.loan.$touch()
      this.$v.customerEmail.$reset()
      this.$v.customerEmail.$touch()
      if (this.$v.customerEmail.$error || this.$v.customer.$error || this.$v.branch.$error || this.$v.seller.$error || this.$v.station.$error || this.$v.storageShopping.$error || this.$v.supplier.$error || this.$v.loan.$error) {
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
      params.station = this.station
      params.supplier = this.supplier
      // params.taxInvoice = this.taxInvoice.value
      params.storage_id = this.storageShopping.value
      params.type_order = this.type_order
      params.commercial_terms = this.commercial_terms
      params.validity = this.validity
      params.lab = this.lab
      params.loan = this.loan
      params.special_order = this.special_order
      params.orderDate = this.$formatDate(this.orderDate)
      params.pledgeDate = this.$formatDate(this.pledgeDate)
      // params.inmediatedate = this.dateShopping === null ? null : this.dateShopping.replace(/\//g, '-')
      params.contact_client_id = this.contactCustomer
      params.email = this.customerEmail
      /* var f = new Date()
      var dd = f.getDate()
      var mm = f.getMonth() + 1
      var yyyy = f.getFullYear()
      if (dd < 10) {
        dd = '0' + dd
      }
      if (mm < 10) {
        mm = '0' + mm
      }
      const date = dd + '/' + mm + '/' + yyyy
      console.log(this.dateShopping + ' >= ' + date)
      if (this.dateShopping >= date) { */
      api.post('/shopping-carts', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.$router.push(`/shopping-carts/${data.shoppingCart.id}`)
        } else {
          this.$q.loading.hide()
        }
      })
      /* } else {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor elija una fecha actual o venidera.',
          persistent: true
        })
        this.$q.loading.hide()
        return false
      } */
      this.$q.loading.hide()
    },
    openModal () {
      this.modalPedido = true
      this.seller = { label: this.$store.getters['users/nickname'], value: this.$store.getters['users/id'] }
      // api.get('/users/getSeller').then(({ data }) => {
      //   this.sellerOptions = data.options
      //   this.$q.loading.hide()
      // })
      // branch-offices/options
      api.get('/branch-offices/options/IndexShopping').then(({ data }) => {
        this.branchOffices = data.options
        console.log(this.branchOffices)
        this.branch = data.options[0].value
        this.searchStorage(data.options[0].value)
        this.$q.loading.hide()
      })
    },
    filtrarClientes (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.filteredCustomerOptions = this.customerOptions.filter(v => v.label.toLowerCase().indexOf(needle) > -1 && v.label !== 'TODOS')
      })
    },
    filtrarContacts (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.optionsContactsFilter = this.optionContacts.filter(v => v.label.toLowerCase().indexOf(needle) > -1 && v.label !== 'TODOS')
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
      params.filter_special_order = this.filter_special_order
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
      // console.log(params)
      params.id = id
      params.branch = branch
      console.log(params)
      api.post('/customers/getDataClient', params).then(({ data }) => {
        // console.log(data)
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
    cleanClient () {
      this.dataClient.fields.id = null
      this.dataClient.fields.branch = null
      this.dataClient.fields.client = null
      this.dataClient.fields.address = null
      this.dataClient.fields.phone = null
      this.dataClient.fields.term = null
      this.dataClient.fields.branchOffice = null
      this.dataClient.fields.branchAddress = null
      this.dataClient.fields.outdoor = null
      this.dataClient.fields.indoor = null
      this.dataClient.fields.colony = null
      this.dataClient.fields.municipality = null
      this.dataClient.fields.CP = null
      this.dataClient.fields.branch_phone = null
    },
    cleanNewClient () {
      this.customerCreated.fields.serial = null
      this.customerCreated.fields.name = null
      this.customerCreated.fields.seller = null
      this.customerCreated.fields.tradename = null
      this.customerCreated.fields.contact_name = null
      this.customerCreated.fields.contact_phone = null
      this.customerCreated.fields.contact_phone_res = null
      this.customerCreated.fields.street = null
      this.customerCreated.fields.outdoor_number = null
      this.customerCreated.fields.indoor_number = null
      this.customerCreated.fields.suburb = null
      this.customerCreated.fields.municipality = null
      this.customerCreated.fields.state = null
      this.customerCreated.fields.country = null
      this.customerCreated.fields.city = null
      this.customerCreated.fields.zip_code = null
      this.customerCreated.fields.rfc = null
      this.customerCreated.fields.term = null
      this.customerCreated.fields.payment_method = null
      this.customerCreated.fields.currency = null
      this.customerCreated.fields.active = { label: 'ACTIVO', value: true }
      this.customerCreated.fields.priceList = null
      this.customerCreated.fields.email = null
      this.customerCreated.fields.email2 = null
      this.customerCreated.fields.email3 = null
      this.customerCreated.fields.email4 = null
      this.customerCreated.fields.admission_date = null
      this.customerCreated.fields.branch_id = 9
      this.customerCreated.fields.credit_days = null
      this.customerCreated.fields.credit_limit = null
      this.customerCreated.fields.channel_id = null
      this.customerCreated.fields.documents = null
      this.customerCreated.fields.discount = null
      this.customerCreated.fields.requirements = null
      this.customerCreated.fields.postal_code_id = null
      this.customerCreated.fields.municipio_id = null
      this.customerCreated.fields.suburb_id = null
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
        this.$router.push(`/shopping-carts/${id}`)
      } else {
        this.$router.push(`/shopping-carts/orders/${id}`)
      }
    },
    openActionRemision (id) {
      this.$router.push(`/storage-exits/${id}`)
    },
    addCliente () {
      this.cliente_modal = true
      api.get('customers/getLastCode').then(data => {
        this.customerCreated.fields.serial = data.data.data.nextserial
        console.log(data.data)
      })
    },
    createCustomer () {
      this.$v.customerCreate.fields.$reset()
      this.$v.customerCreate.fields.$touch()
      if (this.$v.customerCreate.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = { ...this.customerCreate.fields }
      params.admission_date = this.customerCreate.fields.admission_date.substr(6, 4) + '-' + this.customerCreate.fields.admission_date.substr(3, 2) + '-' + this.customerCreate.fields.admission_date.substr(0, 2)
      params.active = params.active.value
      params.price_list = params.priceList.value
      api.post('/customers', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.getClients()
          this.customer = { value: data.id, label: data.name }
          this.getOfficebyClient()
          this.cliente_modal = false
        } else {
          this.$q.loading.hide()
          this.cliente_modal = false
        }
      })
    },
    createcustomerCreated () {
      this.$v.customerCreated.fields.$reset()
      this.$v.customerCreated.fields.$touch()
      if (this.$v.customerCreated.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = { ...this.customerCreated.fields }
      params.admission_date = this.customerCreated.fields.admission_date.substr(6, 4) + '-' + this.customerCreated.fields.admission_date.substr(3, 2) + '-' + this.customerCreated.fields.admission_date.substr(0, 2)
      params.active = params.active.value
      params.price_list = params.priceList.value
      params.seller_id = this.customerCreated.fields.seller
      api.post('/customers', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.getClients()
          this.cleanNewClient()
          this.cliente_modal = false
          // this.$q.loading.hide()
          // this.$router.push('/customerCreateds')
          // this.$router.push(`/customerCreateds/${data.id}`)
          this.$q.loading.hide()
        } else {
          this.$q.loading.hide()
          this.cleanNewClient()
          this.cliente_modal = false
        }
        this.cleanNewClient()
        this.cliente_modal = false
      })
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
      params.specialorder = this.filter_special_order
      const idrole = this.$store.getters['users/id']
      const uri = process.env.API + `shopping-carts/getPdfFromShoppingCarts/${params.customer}/${params.status}/${params.seller}/${params.saleDatev1}/${params.saleDatev2}/${idrole}/${params.specialorder}`
      this.$q.loading.show()
      api.fileDownload(uri).then(({ data }) => {
        const url = window.URL.createObjectURL(new Blob([data], { type: 'application/pdf' }))
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', 'Reporte_de_Ventas.pdf')
        document.body.appendChild(link)
        this.$q.loading.hide()
        link.click()
      })
    },
    getCurrencies () {
      api.get('/currencies/options').then(({ data }) => {
        this.currencyOptions = data.options
        this.customerCreated.fields.currency = 4
        this.$q.loading.hide()
      })
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
      params.specialorder = this.filter_special_order
      const idrol = this.$store.getters['users/id']
      const uri = process.env.API + `shopping-carts/getCSVFromShoppingCarts/${params.customer}/${params.status}/${params.seller}/${params.saleDatev1}/${params.saleDatev2}/${idrol}/${params.specialorder}`
      // window.open(uri, '_blank')
      this.$q.loading.show()
      api.fileDownload(uri).then(({ data }) => {
        const url = window.URL.createObjectURL(new Blob([data], { type: 'application/csv' }))
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', 'Reporte_Ventas.csv')
        document.body.appendChild(link)
        this.$q.loading.hide()
        link.click()
      })
    }
  },
  watch: {
    station: function (newValue) {
      if (newValue.value !== 0 || newValue.value !== null) {
        this.customer = { value: null, label: null }
        api.get(`/branch-offices/customer/${this.station}`).then(({ data }) => {
          this.customerOptionsModal = data.customers
          if (this.customerOptionsModal.length > 0) {
            this.customer = { value: this.customerOptionsModal[0].value, label: this.customerOptionsModal[0].label }
          }
        })
      }
    }
  }
}
</script>

<style>
</style>

<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
          <div class="col-sm-3">
              <div class="q-pa-md q-gutter-sm">
                  <q-breadcrumbs align="left" style="font-size: 20px">
                      <q-breadcrumbs-el label="" icon="home" to="/"/>
                      <q-breadcrumbs-el label="Remisiones" to="/storage-exits" />
                      <q-breadcrumbs-el label="" v-text="$route.params.id"/>
                  </q-breadcrumbs>
              </div>
          </div>
          <div class="col-sm-9 pull-right">
            <div class="q-pa-md q-gutter-sm">
            <q-btn style="margin-left: 8px;" color="blue" :icon="documentId === null ? 'cloud_upload' : 'visibility'" @click="documentId === null ? showDocumentFileOc() : downloadDocumentOc()">
            <!-- <q-tooltip content-class="bg-blue">{{ documentId === null ? 'Subir orden de compra' : 'Descargar orden de compra' }}</q-tooltip> -->
            <q-menu v-if="documentId === null ? false : true">
                              <q-list link separator class="scroll" style="min-width: 100px">
                              <q-item :clickable="1 > 0" @click.native="showDocumentFileOcMenu()">
                                  <q-item-section>Ver</q-item-section>
                                </q-item>
                                <q-item :clickable="1 > 0" @click.native="downloadDocumentFileOc()">
                                  <q-item-section>Descargar</q-item-section>
                                </q-item>
                                <q-item :clickable="1 > 0" @click.native="deleteFileOc()">
                                  <q-item-section>Eliminar</q-item-section>
                                </q-item>
                              </q-list>
                            </q-menu>
          </q-btn>
          <q-btn color="blue" icon="fas fa-file-pdf" @click.native="quotationNotePDF()" style="margin-left: 8px;">
            <q-tooltip content-class="bg-blue">Generar pedido</q-tooltip>
          </q-btn>
              <!--<q-btn  color="deep-purple-6" icon="fas fa-check" label="Enviar Histórico" @click="confirmHistoricRemissionDialog = true" v-if="invoice.fields.status == 'NUEVO' && $store.getters['users/roles'].includes(1) && (invoice.fields.canRemisionate || this.inBulkDetails.length > 0)" />-->
              <q-btn  color="positive" icon="fas fa-check" label="Enviar" @click="confirmRemissionDialog = true" v-if="invoice.fields.status == 'REMISIONADO' && (invoice.fields.canRemisionate || this.inBulkDetails.length > 0)" />
                <q-btn style="margin-left: 8px;" v-if="invoice.fields.status == 'ENVIADO'" color="warning" icon="fas fa-file-pdf" @click="generarTicket()">
            <q-tooltip content-class="bg-positive">Generar Ticket</q-tooltip>
          </q-btn>
              <q-btn  color="purple" icon="mail" @click="sendSaleReferral()" v-if="invoice.fields.status == 'ENVIADO'" :loading="loadingSendingMailBtn">
                <q-tooltip content-class="bg-purple-6">Enviar email</q-tooltip>
              </q-btn>
              <q-btn  color="negative" icon="block"  label="Cancelar" @click="CancelRemision()" v-if="invoice.fields.status_timbrado == 0 || invoice.fields.status_timbrado == 2"/>
              <q-btn  color="positive" icon="save"  label="Actualizar" @click="updateSale()" v-if="invoice.fields.status == 'NUEVO'" />
              <q-btn  color="positive" icon="fas fa-file-pdf" @click="saleReferral()" v-if="invoice.fields.status == 'ENVIADO'" >
                <q-tooltip content-class="bg-blue">Remisión de venta</q-tooltip>
              </q-btn>
            </div>
          </div>
          <!--<div class="row q-col-gutter-md">
          </div>-->
      </div>
    </div>

    <div class="q-pa-md bg-grey-3">
      <div class="row bg-white border-panel">
        <div class="col q-pa-md">
          <div class="row q-col-gutter-xs">
            <div class="col-xs-12 col-sm-2">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="invoice.fields.saleDate"
                mask="date"
                label="Fecha de venta"
                :rules="saleDateRules"
                :disable = "true"
              >
                <template v-slot:prepend>
                  <q-icon name="event" />
                </template>
                <q-popup-proxy
                  ref="invoiceFieldsSaleDate"
                  transition-show="scale"
                  transition-hide="scale"
                >
                  <div class="col-sm-12">
                    <q-date
                      mask="DD/MM/YYYY"
                      v-model="invoice.fields.saleDate"
                      @input="() => $refs.invoiceFieldsSaleDate.hide()"
                      today-btn
                    />
                  </div>
                </q-popup-proxy>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-4">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="invoice.fields.branchOffice"
                :options="branchOfficeOptions"
                label="Sucursal"
                @input="() => {invoice.fields.inBulkStorage = null;}"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-store-alt" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-4">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="invoice.fields.inBulkStorage"
                :options="filteredStorageOptions"
                label="Almacén"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-warehouse" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-2">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="invoice.fields.status"
                label="Estatus"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="battery_full" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-4">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="invoice.fields.customer"
                :options="filteredCustomerOptions"
                label="Cliente"
                @input="() => {invoice.fields.customerBranchOffice = null}"
                :rules="customerRules"
                :disable = "true"
                @filter="filtrarClientes"
                use-input
                input-debounce="0"
                behavior="menu"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-shopping-cart" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-4">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="invoice.fields.customerBranchOffice"
                :options="filteredCustomerBranchOfficeOptions"
                label="Sucursal de cliente"
                :rules="customerBranchOfficeRules"
                :disable = "true"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-store-alt" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-4">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                :disable="invoice.fields.status === 'ENTREGADO' ? true : false"
                autogrow
                v-model="invoice.fields.comments"
                label="Comentarios"
                type="textarea"
                @input="v => { invoice.fields.comments = v.toUpperCase() }"
              />
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="invoice.fields.oc_referenceshp"
                label="No. Orden"
                @blur="editReeference()"
              >
                <template v-slot:prepend>
                  <q-icon name="code" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="tax_invoice"
                label="Factura"
                disable
                map-options
              >
                <template v-slot:prepend>
                  <q-icon name="attach_money" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="loan"
                label="Prestamo"
                disable
                map-options
              >
                <template v-slot:prepend>
                  <q-icon name="attach_money" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-md-3">
                  <q-select color="dark" bg-color="secondary" filled
                            v-model="special_order"
                            :options="
                            [ {label: 'NORMAL', value: 0},
                              {label: 'ESPECIAL', value: 1},
                            ]"
                            disable
                            use-input
                            label="Pedido"
                            emit-value
                            map-options
                            >
                    <template v-slot:prepend>
                      <q-icon name="grade" />
                    </template>
                  </q-select>
                </div>
            <div class="col-xs-12 col-md-3" v-if="invoice.fields.status == 'ENVIADO' || invoice.fields.status == 'ENTREGADO'">
                <q-select
              color="dark"
              bg-color="secondary"
              filled
              v-model="invoice.fields.date_delivered"
              :disable="invoice.fields.date_delivered === null ? false : true"
              mask="date"
              label="Fecha entrega">
                <q-popup-proxy ref="date1" transition-show="scale" transition-hide="scale">
                  <div class="col-sm-12">
                    <q-date v-model="invoice.fields.date_delivered" :disable="invoice.fields.date_delivered === null ? false : true" @input="showconfirmDelivered()" today-btn/>
                  </div>
                </q-popup-proxy>
                <template v-slot:prepend>
                  <q-icon name="date_range"/>
                </template>
              </q-select>
            </div>
          </div>
          <!-- <div class="row q-col-gutter-xs"> -->
            <!-- <div class="col-xs-12 col-md-4">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="invoice.fields.driver"
                :options="driverOptions"
                label="Chofer"
                :rules="driverRules"
                :disable="invoice.fields.status != 'NUEVO'"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-truck" />
                </template>
              </q-select>
            </div> -->
          <!-- </div> -->
        </div>
      </div>
      <br>
      <div class="bg-white border-panel">
        <q-tabs
          v-model="currentTab"
          dense
          style="font-weight: bold; background-color: #16122f75"
          active-bg-color="light-green"
          class="text-white text-bold"
          active-color="white"
          indicator-color="black"
          align="justify"
          narrow-indicator
          @input="changeModel"
        >
          <q-tab name="inBulkDetails" label="Productos" :disable="bulkTab" v-if="invoice.fields.inBulkStorage != null && !isNaN(invoice.fields.inBulkStorage.value)" />
          <q-tab name="invoiceDetails" label="Datos fiscales" v-if="((invoice.fields.status === 'ENVIADO' || invoice.fields.status === 'PAGADO') && tax_invoice.value === 0) && isRoleInvoice" />
          <q-tab name="paymentDetails" label="Pagos" v-if="isDone && fiscal.fields.metodo_pago.value === 'PPD'"/>
          <q-tab name="history" label="historial de facturacion" v-if="((invoice.fields.status === 'ENVIADO' || invoice.fields.status === 'PAGADO') && tax_invoice.value === 0) && isRoleInvoice"/>
          <q-tab name="logistic" label="Logística"/>
        </q-tabs>
        <q-separator />
        <q-tab-panels v-model="currentTab" animated>
          <q-tab-panel name="inBulkDetails" v-if="invoice.fields.inBulkStorage != null && !isNaN(invoice.fields.inBulkStorage.value)">
            <div style="font-weight: normal;">
              <div class="row q-col-gutter-xs q-md-xl" v-if="cndButtonIB" style="padding-top: 5px; padding-bottom: 20px;">
                <div class="col-xs-12 col-sm-6 col-md-6">
                  <q-select
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="inBulkDetail.fields.product"
                    :options="inBulkProductOptions"
                    label="Producto"
                    emit-value map-options
                    :rules="inBulkDetailProductRules"
                    @input="() => inBulkDetail.fields.qty = null"
                    disable
                  >
                    <template v-slot:prepend>
                      <q-icon name="emoji_objects" />
                    </template>
                  </q-select>
                </div>
                <div class="col-xs-12 col-sm-6 col-md-2">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="inBulkDetail.fields.qty"
                    label="Cantidad"
                    :rules="inBulkDetailQtyRules"
                  >
                    <template v-slot:prepend>
                      <q-icon name="emoji_objects" />
                    </template>
                  </q-input>
                </div>
                <!--<div class="col-xs-12 col-sm-6 col-md-2">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="inBulkDetailUnitPrice"
                    label="Precio unitario"
                    disable
                  >
                    <template v-slot:prepend>
                      <q-icon name="fas fa-dollar-sign" />
                    </template>
                  </q-input>
                </div>-->
                <div class="col-xs-12 col-sm-2 pull-right q-pt-md" v-if="cndButtonIB">
<!--                  <q-btn color="primary" icon="add" label="Agregar" @click="addInBulkDetail()" />-->
                  <q-btn color="positive" label="Actualizar" @click="editIB()" />
                </div>
                <!-- <div class="col-xs-12 col-sm-6 col-md-2">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="inBulkDetail.fields.packagesQty"
                    label="Bultos"
                    :rules="inBulkDetailPackagesQtyRules"
                  >
                    <template v-slot:prepend>
                      <q-icon name="emoji_objects" />
                    </template>
                  </q-input>
                </div> -->
              </div>
              <div class="q-col-gutter-xs" style="height:400px;">
                <q-table
                  flat
                  style="height:380px; overflow-y:auto;"
                  bordered
                  :data="inBulkDetails"
                  :columns="inBulkDetailsColumns"
                  row-key="product"
                  :pagination.sync="pagination"
                >
                  <template v-slot:body="props">
                    <q-tr :props="props">
                      <q-td key="product" style="text-align: left; width: 50%;" :props="props" v-if="props.row.id">{{ props.row.product }}</q-td>
                      <q-td key="product" style="text-align: right; width: 50%; color: #3CB371;" :props="props" v-else>{{ props.row.product }}</q-td>
                      <q-td key="qty" style="text-align: right; width: 10%;" :props="props" v-if="props.row.id">{{ `${formatter.format(props.row.qty)} PZAS.` }}</q-td>
                      <q-td key="qty" style="text-align: right; width: 10%; color: #3CB371;" :props="props" v-else>{{ `${formatter.format(totalInBulkDetailsQties)} PZAS.` }}</q-td>
                      <q-td key="unitPrice" style="text-align: right; width: 10%;" :props="props">{{ (props.row.unit_price ? `${currencyFormatter.format(props.row.unit_price)}` : null) }}</q-td>
                      <q-td key="totalPrice" style="text-align: right; width: 10%;" :props="props" v-if="props.row.id">{{ `${currencyFormatter.format(props.row.total_price)}` }}</q-td>
                      <q-td key="totalPrice" style="text-align: right; width: 10%; color: #3CB371;" :props="props" v-else>{{ `${currencyFormatter.format(totalInBulkDetailsPrice)}` }}</q-td>
                      <!-- <q-td key="packagesQty" style="text-align: right; width: 10%;" :props="props" v-if="props.row.id">{{ `${formatter.format(props.row.packages_qty)}` }}</q-td> -->
                      <q-td key="actions" style="width: 10%;" :props="props" v-if="props.row.id && isRoleAdmin && (invoice.fields.status_timbrado == 0 || invoice.fields.status_timbrado == 2) && loan.value === 1">
                        <q-btn color="primary" icon="edit" flat @click.native="editInBulkDetail(props.row)" size="10px">
                          <q-tooltip content-class="bg-red">Editar</q-tooltip>
                        </q-btn>
                        <q-btn v-if="props.row.id && isRoleAdmin && (invoice.fields.status_timbrado == 0 || invoice.fields.status_timbrado == 2) && loan.value === 1" color="negative" icon="fas fa-trash-alt" flat @click.native="removeInBulkDetail(props.row.id)" size="10px">
                          <q-tooltip content-class="bg-red">Eliminar</q-tooltip>
                        </q-btn>
                      </q-td>
                      <q-td key="actions" style="width: 15%;" :props="props" v-else></q-td>
                    </q-tr>
                  </template>
                </q-table>
              </div>
            </div>
          </q-tab-panel>
          <q-tab-panel name="logistic">
            <div class="row q-col-gutter-xs">
              <div class="col-xs-12 col-sm-4 text-center">
                <q-select
                  color="dark"
                  bg-color="secondary"
                  filled
                  :error="$v.invoice.fields.carrier.$error"
                  v-model="invoice.fields.carrier"
                  :options="carriers"
                  label="Paquetería"
                  emit-value
                  map-options
                  :rules="carrierRules"
                >
                  <template v-slot:prepend>
                    <q-icon name="fas fa-truck" />
                  </template>
                </q-select>
              </div>
              <div class="col-xs-12 col-sm-4">
                <q-input
                  color="dark"
                  bg-color="secondary"
                  filled
                  v-model="invoice.fields.guideNumber"
                  :error="$v.invoice.fields.guideNumber.$error"
                  label="Número de Guía"
                  reverse-fill-mask
                  :rules="guideNumberRules"
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
                  v-model="invoice.fields.carrierName"
                  :error="$v.invoice.fields.carrierName.$error"
                  label="Nombre del Chofer"
                  :rules="carrierNameRules"
                  @input="v => { invoice.fields.carrierName = v.toUpperCase() }"
                >
                  <template v-slot:prepend>
                    <q-icon name="fas fa-signature" />
                  </template>
                </q-input>
              </div>
            <div class="col-xs-12 col-sm-4 q-pa-xs">
              <q-uploader
                  v-if="invoice.fields.pallet == null"
                  style="min-width: 100%"
                  :url="fileDocumentUrl"
                  method="POST"
                  text-color="dark"
                  color="secondary"
                  label="Foto de Tarima"
                  ref="fileDocumentRef"
                  max-files="1"
                  auto-upload
                  flat
                  no-thumbnails
                  :headers="[{name: 'Authorization', value: 'Bearer ' + this.JWT}]"
                  @uploaded="afterUploadDocumentFile"
                />
                    <div v-else class="text-center q-px-md">
                      <label class="text-h5 text-primary"><b>Documento {{invoice.fields.pallet}}</b></label> <br>
                      <q-btn-group push spread>
                      <q-btn color="positive" glossy icon="cloud_upload" flat size="15px" @click.native="openUploadFileModal()">
                        <q-tooltip content-class="bg-positive">Actualizar documento</q-tooltip>
                      </q-btn>
                      <q-btn color="primary" glossy icon="remove_red_eye" @click.native="showDocumentFile()" flat size="15px">
                        <q-tooltip content-class="bg-positive">Visualizar</q-tooltip>
                      </q-btn>
                      <q-btn color="positive" glossy icon="cloud_download" @click.native="downloadDocumentFile()" flat size="15px">
                        <q-tooltip content-class="bg-positive">Descargar</q-tooltip>
                      </q-btn>
                      <q-btn color="negative" glossy icon="fas fa-trash-alt" @click.native="removeDocument()"  flat size="15px">
                        <q-tooltip content-class="bg-positive">Eliminar</q-tooltip>
                      </q-btn>
                      </q-btn-group>
                    </div>
            </div>
            <div class="col-md-8 pull-right">
              <q-btn  color="positive" icon="save"  label="Guardar" @click="saveLogistic()" v-if="invoice.fields.status == 'REMISIONADO'" />
            </div>
            </div>
          </q-tab-panel>
          <q-tab-panel name="invoiceDetails" v-if="(invoice.fields.status === 'ENVIADO' || invoice.fields.status === 'PAGADO')">
            <div class="row q-col-gutter-xs">
              <div class="col-xs-12 col-sm-1 offset-sm-11 q-col-gutter-xs pull-right" v-if="invoice.fields.status_timbrado !== 0">
                <div>
                  <q-btn :icon="iconTimbrado[invoice.fields.status_timbrado]" disable :color="colorTimbrado[invoice.fields.status_timbrado]" :label="statusTimbrado[invoice.fields.status_timbrado]">
                  </q-btn>
                  <q-tooltip v-if="invoice.fields.status_timbrado === 6">{{invoice.fields.message}}</q-tooltip><q-tooltip v-if="invoice.fields.status_timbrado === 7">{{invoice.fields.message_cancelacion}}</q-tooltip>
                </div>
              </div>
            </div>
            <br/>
            <div class="row q-col-gutter-xs">
              <div class="col-xs-12 col-sm-12 q-col-gutter-xs pull-right">
                <q-btn v-if="(invoice.fields.status === 'ENVIADO' || invoice.fields.status === 'PAGADO') && !isChecking && !isDone" color="positive" icon="fas fa-certificate" style="margin-left: 10px;" label="Timbrar" @click="timbrarRemision()" :loading="loadingFiscal"></q-btn>
                <q-btn disabled v-if="invoice.fields.status_timbrado === 1" color="orange" class="pull-right">{{this.invoice.fields.uuid}}</q-btn>
                <q-btn v-if="invoice.fields.status_timbrado === 1" color="green-8" icon="fas fa-file-excel" style="margin-left: 10px;" label="XML" @click="getCFDIInvoice(invoice.fields.id_request)" :loading="loadingFiscal"></q-btn>
                <q-btn v-if="invoice.fields.status_timbrado === 1" style="margin-left: 10px;" @click="getPdfInvoice(invoice.fields.id_request,invoice.fields.pdf)" color="brown" icon="fas fa-file-pdf" label="PDF" :loading="loadingFiscal"></q-btn>
                <q-btn color="primary" icon="mail" style="margin-left: 10px;" label="Email" @click="selectEmail()" v-if="isDone" :loading="loadingSendingMailBtn">
                </q-btn>
                <q-dialog v-model="promptEmail" persistent>
                  <q-card style="min-width: 350px">
                    <q-card-section>
                      <div class="text-h6">Ingrese su email</div>
                    </q-card-section>

                    <q-card-section class="q-pt-none">
                      <q-input dense v-model="emailInvoice.fields.email_cliente" :error="$v.emailInvoice.fields.email_cliente.$error" :rules="emailRule" autofocus @keyup.enter="promptEmail = false" />
                    </q-card-section>

                    <q-card-actions align="right" class="text-primary">
                      <q-btn flat label="Cancel" v-close-popup />
                      <q-btn flat label="Enviar" @click="sendEmailInvoice()" :loading="loadingSendingMailBtn" />
                    </q-card-actions>
                  </q-card>
                </q-dialog>
                <q-btn v-if="invoice.fields.status_timbrado === 1 || invoice.fields.status_timbrado === 7" style="margin-left: 10px;" @click="cancelarTimbrado = true"  color="red-9" icon="cancel" label="Cancelar" :loading="loadingFiscal"></q-btn>
                <q-btn color="orange" v-if="!isDone && !isChecking" style="margin-left: 10px;" @click="actualizarFiscal()" icon="save" :loading="loadingFiscal" label="Actualizar"></q-btn>
              </div>
            </div>
            <div class="row q-col-gutter-xs" style="padding: 2%;">
              <div class="col-xs-12 col-sm-3">
                <q-select
                  color="dark"
                  bg-color="secondary"
                  filled
                  map-options
                  v-model="fiscal.fields.taxCompanyId"
                  label="Seleccionar cliente"
                  :options="taxCompanyListOptions"
                  :readonly="isDone || isChecking"
                  @input="onTypeClientChange()"
                >
                  <template v-slot:prepend>
                    <q-icon name="person" />
                  </template>
                </q-select>
              </div>
              <div class="col-xs-12 col-sm-3">
                <q-input
                  color="dark"
                  bg-color="secondary"
                  filled
                  v-model="fiscal.fields.razon_social"
                  :error="$v.fiscal.fields.razon_social.$error"
                  label="Razon social del receptor"
                  :rules="fiscalRazonSocial"
                  readonly
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
                  label="RFC del receptor"
                  :rules="fiscalRFC"
                  readonly
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
                  v-model="fiscal.fields.fecha_factura"
                  :error="$v.fiscal.fields.fecha_factura.$error"
                  mask="YYYY-MM-DD HH:mm:ss"
                  label="Fecha de emisión"
                  :rules="fiscalFechaFactura"
                  :readonly="isDone || isChecking"
                >
                  <template v-slot:prepend>
                    <q-icon name="event" />
                  </template>
                  <q-popup-proxy
                    ref="fiscalFechaFactura"
                    transition-show="scale"
                    transition-hide="scale"
                  >
                    <div class="row">
                      <div class="col-sm-6">
                        <q-date
                          v-model="fiscal.fields.fecha_factura"
                          mask="YYYY-MM-DD HH:mm:ss"
                        />
                      </div>
                      <div class="col-sm-6">
                        <q-time
                          v-model="fiscal.fields.fecha_factura"
                          mask="YYYY-MM-DD HH:mm:ss"
                          @input="() => $refs.fiscalFechaFactura.hide()"
                          now-btn
                        />
                      </div>
                    </div>
                  </q-popup-proxy>
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
                  :rules="fiscalSerie"
                  :readonly="isDone || isChecking"
                  @blur="siguienteFolio()"
                >
                  <template v-slot:prepend>
                    <q-icon name="description" />
                  </template>
                </q-input>
              </div>-->
              <div class="col-xs-12 col-sm-3" v-if="invoice.fields.status_timbrado === 1">
                <q-input
                  color="dark"
                  bg-color="secondary"
                  filled
                  v-model="fiscal.fields.folio_fiscal"
                  :error="$v.fiscal.fields.folio_fiscal.$error"
                  label="Folio"
                  :rules="fiscalFolio"
                  readonly
                >
                  <template v-slot:prepend>
                    <q-icon name="description" />
                  </template>
                </q-input>
              </div>
              <div class="col-xs-12 col-sm-3" v-else>
                <q-input
                  color="black"
                  bg-color="primary"
                  filled
                  dark
                  v-model="fiscal.fields.folio_fiscal"
                  :error="$v.fiscal.fields.folio_fiscal.$error"
                  label="Folio"
                  :rules="fiscalFolio"
                  readonly
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
                  label="Lugar de expedición"
                  :rules="fiscalLugarExpedicion"
                  @keyup="isNumber($event,'codigo_postal')"
                  maxlength="5"
                  :readonly="isDone || isChecking"
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
                  v-model="fiscal.fields.tipo_comprobante"
                  :error="$v.fiscal.fields.tipo_comprobante.$error"
                  label="Tipo de comprobante"
                  :rules="fiscalTipoComprobante"
                  :options="tiposComprobanteOptions"
                  :readonly="isDone || isChecking"
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
                  v-model="fiscal.fields.metodo_pago"
                  :error="$v.fiscal.fields.metodo_pago.$error"
                  label="Método de pago"
                  :rules="fiscalMetodoPago"
                  :options="metodoPagoOptions"
                  :readonly="isDone || isChecking"
                  @input="setFormaPago"
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
                  :rules="fiscalFormaPago"
                  :options="formaPagoOptions"
                  :readonly="isDone || isChecking"
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
                  :rules="fiscalUsoCFDI"
                  :options="usoCFDIOptions"
                  :readonly="isDone || isChecking"
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
              <div class="col-xs-12 col-sm-3">
                <q-input
                  color="dark"
                  bg-color="secondary"
                  filled
                  v-model="fiscal.fields.folio_relacionado"
                  label="Folio Relacionado"
                >
                  <template v-slot:prepend>
                    <q-icon name="description" />
                  </template>
                </q-input>
              </div>
              <div class="col-xs-12 col-sm-3" v-if="fiscal.fields.immex">
                <q-input
                  color="dark"
                  bg-color="secondary"
                  filled
                  v-model="fiscal.fields.import"
                  :error="$v.fiscal.fields.import.$error"
                  label="Pedimento Importación"
                >
                  <template v-slot:prepend>
                    <q-icon name="description" />
                  </template>
                </q-input>
              </div>
              <div class="col-xs-12 col-sm-3" v-if="fiscal.fields.immex">
                <q-input
                  color="dark"
                  bg-color="secondary"
                  filled
                  :error="$v.fiscal.fields.export.$error"
                  v-model="fiscal.fields.export"
                  label="Pedimento exportación"
                >
                  <template v-slot:prepend>
                    <q-icon name="description" />
                  </template>
                </q-input>
              </div>
            </div>
          </q-tab-panel>
          <q-tab-panel name="paymentDetails" v-if="isDone">
            <!--<div class="row q-col-gutter-xs">
              <div class="col-xs-12 col-sm-8 offset-sm-4 pull-right">
                <q-btn v-if="isRoleInvoice" color="positive" @click="agregarPago()" icon="add" :loading="loadingFiscal" label="Agregar"></q-btn>
              </div>
            </div>
            <div class="row q-col-gutter-xs" style="padding: 2%;" v-if="isRoleInvoice">
              <div class="col-xs-12 col-sm-3">
                <q-select
                  color="dark"
                  bg-color="secondary"
                  filled
                  v-model="payments.fields.fecha_pago"
                  :error="$v.payments.fields.fecha_pago.$error"
                  mask="YYYY-MM-DD HH:mm:ss"
                  label="Fecha del pago"
                  :rules="paymentsFechaFactura"
                >
                  <template v-slot:prepend>
                    <q-icon name="event" />
                  </template>
                  <q-popup-proxy
                    ref="paymentsFechaFactura"
                    transition-show="scale"
                    transition-hide="scale"
                  >
                    <div class="row">
                      <div class="col-sm-6">
                        <q-date
                          v-model="payments.fields.fecha_pago"
                          mask="YYYY-MM-DD HH:mm:ss"
                        />
                      </div>
                      <div class="col-sm-6">
                        <q-time
                          v-model="payments.fields.fecha_pago"
                          mask="YYYY-MM-DD HH:mm:ss"
                          @input="() => $refs.paymentsFechaFactura.hide()"
                          now-btn
                        />
                      </div>
                    </div>
                  </q-popup-proxy>
                </q-select>
              </div>
              <div class="col-xs-12 col-sm-3">
                <q-input
                  color="dark"
                  bg-color="secondary"
                  filled
                  v-model="payments.fields.total"
                  :error="$v.payments.fields.total.$error"
                  label="Total del pago"
                  :rules="paymentsTotal"
                  @keyup="isNumber($event,'total')"
                >
                  <template v-slot:prepend>
                    <q-icon name="fa fa-dollar-sign" />
                  </template>
                </q-input>
              </div>
              <div class="col-xs-12 col-sm-3">
                <q-select
                  color="dark"
                  bg-color="secondary"
                  filled
                  map-options
                  v-model="payments.fields.forma_pago"
                  :error="$v.payments.fields.forma_pago.$error"
                  label="Forma de pago"
                  :rules="paymentsFormaPago"
                  :options="formaPagoOptions"
                >
                  <template v-slot:prepend>
                    <q-icon name="description" />
                  </template>
                </q-select>
              </div>
              <div class="col-xs-12 col-sm-3"></div>
            </div>-->
            <div class="row q-col-gutter-xs">
              <div class="col-sm-12 q-mt-sm" id="sticky-table-scroll">
                <q-table id="sticky-table"
                  :data="pagos"
                  :columns="payments.columns"
                  :selected.sync="payments.selected"
                  :filter="payments.filter"
                  color="positive"
                  title=""
                  :dense="true"
                  :pagination.sync="payments.pagination"
                  :loading="payments.loading"
                  :rows-per-page-options="payments.rowsOptions"
                  selection="multiple">
                  <template slot="top-selection">
                    <div class="col" />
                    <q-btn color="primary" icon="mail" style="margin-left: 10px;" label="Email" @click="selectEmailsPagos()" :loading="loadingSendingMailBtn">
                      <q-tooltip>Enviar emails</q-tooltip>
                    </q-btn>
                    <q-dialog v-model="promptEmailPagos" persistent>
                      <q-card style="min-width: 350px">
                        <q-card-section>
                          <div class="text-h6">Ingrese su email</div>
                        </q-card-section>

                        <q-card-section class="q-pt-none">
                          <q-input dense v-model="emailInvoice.fields.email_cliente" :error="$v.emailInvoice.fields.email_cliente.$error" :rules="emailRule" autofocus @keyup.enter="promptEmail = false" />
                        </q-card-section>

                        <q-card-actions align="right" class="text-primary">
                          <q-btn flat label="Cancel" v-close-popup />
                          <q-btn flat label="Enviar" @click="sendEmailsPagos()" :loading="loadingSendingMailBtn" />
                        </q-card-actions>
                      </q-card>
                    </q-dialog>
                  </template>
                  <template slot="body" slot-scope="props">
                    <q-tr :props="props">
                      <q-td key="check" v-if="props.row.status_timbrado === 1"><q-checkbox color="positive" v-model="props.selected" /></q-td>
                      <q-td key="check" auto-width v-else><q-checkbox v-model="props.selected" color="positive" style="display:none"/></q-td>
                      <q-td key="num_parcialidad" :props="props">{{ props.row.num_parcialidad }}</q-td>
                      <q-td key="fecha_pago" :props="props">{{ props.row.fecha_pago }}</q-td>
                      <q-td key="total" :props="props">{{ props.row.total }}</q-td>
                      <q-td key="status_timbrado" :props="props"><q-chip dense :icon="iconTimbrado[props.row.status_timbrado]" :color="colorTimbrado[props.row.status_timbrado]" text-color="white">{{ statusTimbrado[props.row.status_timbrado] }}<q-tooltip v-if="props.row.status_timbrado === 6">{{props.row.message}}</q-tooltip><q-tooltip v-if="props.row.status_timbrado === 7">{{props.row.message_cancelacion}}</q-tooltip></q-chip></q-td>
                      <q-td key="acciones" :props="props">
                      <q-btn v-if="props.row.status_timbrado === 0 || props.row.status_timbrado === 6" small flat @click="timbrarPago(props.row)" color="green-6" icon="fas fa-certificate">
                          <q-tooltip>Timbrar</q-tooltip>
                        </q-btn>
                        <!--<q-btn v-if="props.row.status_timbrado === 0 || props.row.status_timbrado === 6" small flat @click="borrarPago(props.row.id)" color="red-6" icon="delete">
                          <q-tooltip>Eliminar</q-tooltip>
                        </q-btn>-->
                        <q-btn v-if="props.row.status_timbrado === 1" small flat @click="getPdfInvoice(props.row.id_request,props.row.folio + '_' + invoice.fields.pdf.split('_')[1])" color="brown" class="pull-right" icon-right="fas fa-file-pdf" :loading="loadingFiscal">
                          <q-tooltip>PDF</q-tooltip>
                        </q-btn>
                        <q-btn v-if="props.row.status_timbrado === 1" small flat @click="getCFDIInvoice(props.row.id_request)" color="green-8" class="pull-right" icon-right="fas fa-file-excel" :loading="loadingFiscal">
                          <q-tooltip>XML</q-tooltip>
                        </q-btn>
                        <q-btn v-if="(props.row.status_timbrado === 1 || props.row.status_timbrado === 7 )&& isRoleInvoice" small flat @click="cancelarTimbrado = true, pagoId = props.row.id" class="pull-right" color="red-9" icon-right="cancel" :loading="loadingFiscal">
                          <q-tooltip>Cancelar</q-tooltip>
                        </q-btn>
                      </q-td>
                    </q-tr>
                  </template>
                </q-table>
                <q-inner-loading :visible="payments.loading">
                  <q-spinner-dots size="64px" color="primary" />
                </q-inner-loading>
              </div>
            </div>
          </q-tab-panel>

          <q-tab-panel name="history" v-if="(invoice.fields.status === 'ENVIADO' || invoice.fields.status === 'PAGADO')">
            <div class="row q-col-gutter-xs">
              <div class="col-sm-12 q-mt-sm" id="sticky-table-scroll">
                <q-table id="sticky-table"
                  :data="histories"
                  :columns="history.columns"
                  :filter="history.filter"
                  color="positive"
                  title=""
                  :dense="true"
                  :pagination.sync="history.pagination"
                  :loading="history.loading"
                  :rows-per-page-options="history.rowsOptions">
                  <template slot="body" slot-scope="props">
                    <q-tr :props="props">
                      <q-td key="folio_fiscal" :props="props">{{ props.row.folio_fiscal }}</q-td>
                      <q-td key="serie" :props="props">{{ props.row.serie }}</q-td>
                      <q-td key="uuid" :props="props">{{ props.row.uuid }}</q-td>
                      <q-td key="message" :props="props">{{props.row.message}}</q-td>
                      <q-td key="id_cancelacion" :props="props">{{props.row.id_cancelacion}}</q-td>
                      <q-td key="fecha_cancelacion_envio" :props="props">{{props.row.fecha_cancelacion_envio}}</q-td>
                      <q-td key="motivo_cancelacion" :props="props">{{motivos[Number(props.row.motivo_cancelacion)] + (props.row.motivo_cancelacion == '01' ? ': ' + props.row.folio_sustituye : '')}}</q-td>
                      <q-td key="fecha_cancelacion_recibido" :props="props">{{props.row.fecha_cancelacion_recibido}}</q-td>
                      <q-td key="acciones" :props="props">
                        <q-btn v-if="props.row.status_timbrado === 1 || props.row.status_timbrado === 2" small flat @click="getPdfInvoice(props.row.id_request,props.row.folio_fiscal + '_' + invoice.fields.pdf.split('_')[1])" :color="props.row.status_timbrado==1?'brown':'red'" class="pull-right" icon-right="fas fa-file-pdf" :loading="loadingFiscal">
                          <q-tooltip>PDF</q-tooltip>
                        </q-btn>
                        <q-btn v-if="props.row.status_timbrado === 1" small flat @click="getCFDIInvoice(props.row.id_request)" color="green-8" class="pull-right" icon-right="fas fa-file-excel" :loading="loadingFiscal">
                          <q-tooltip>XML</q-tooltip>
                        </q-btn>
                      </q-td>
                    </q-tr>
                  </template>
                </q-table>
                <q-inner-loading :visible="history.loading">
                  <q-spinner-dots size="64px" color="primary" />
                </q-inner-loading>
              </div>
            </div>
          </q-tab-panel>

        </q-tab-panels>
      </div>
    </div>
    <q-dialog v-model="confirmRemissionDialog" persistent>
      <q-card>
        <q-card-section class="bg-primary">
          <div class="row">
            <div class="col-xs-11 col-sm-11 col-md-11 col-lg-11 text-h6" style="color:white;">Enviar Pedido</div>
            <div class="col-xs-1 col-sm-1 col-md-1 col-lg-1 pull-right"><q-btn color="white" flat v-close-popup round dense icon="close" /></div>
          </div>
        </q-card-section>
        <q-card-section class="row items-center">
          <span class="text-h6">¿Está seguro de enviar y cambiar el estatus de la remisión?</span>
        </q-card-section>
        <q-card-section class="text-primary">
          <div class="row q-col-gutter-xs q-col-gutter-sm q-col-gutter-md row q-col-gutter-xs q-col-gutter-lg">
            <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
              <q-btn flat label="Cancelar" color="white" style="background-color: #f44336;" @click="closeGenerateInvoiceModal()" v-close-popup />
            </div>
            <div align="right" class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
              <q-btn flat label="Confirmar" color="white" style="background-color: #21ba45; text-align: right;" @click="remission()" />
            </div>
          </div>
        </q-card-section>
      </q-card>
    </q-dialog>
    <q-dialog v-model="confirmDelivered" persistent>
      <q-card>
        <q-card-section class="bg-primary">
          <div class="row">
            <div class="col-xs-11 col-sm-11 col-md-11 col-lg-11 text-h6" style="color:white;">Confirma de Recibido</div>
            <div class="col-xs-1 col-sm-1 col-md-1 col-lg-1 pull-right"><q-btn color="white" flat v-close-popup round dense icon="close" /></div>
          </div>
        </q-card-section>
        <q-card-section class="row items-center">
          <span class="text-h6">¿Desea cambiar el estatus a Entregado?</span>
        </q-card-section>
        <q-card-section class="text-primary">
          <div class="row q-col-gutter-xs q-col-gutter-sm q-col-gutter-md row q-col-gutter-xs q-col-gutter-lg">
            <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
              <q-btn flat label="Cancelar" color="white" style="background-color: #f44336;" @click="cancelConfirDelivered()" v-close-popup />
            </div>
            <div align="right" class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
              <q-btn flat label="Confirmar" color="white" style="background-color: #21ba45; text-align: right;" @click="changeToDelivered()" />
            </div>
          </div>
        </q-card-section>
      </q-card>
    </q-dialog>
    <q-dialog v-model="confirmHistoricRemissionDialog" persistent>
      <q-card>
        <q-card-section class="bg-primary">
          <div class="row">
            <div class="col-xs-11 col-sm-11 col-md-11 col-lg-11 text-h6" style="color:white;">Enviar Pedido</div>
            <div class="col-xs-1 col-sm-1 col-md-1 col-lg-1 pull-right"><q-btn color="white" flat v-close-popup round dense icon="close" /></div>
          </div>
        </q-card-section>
        <q-card-section class="row items-center">
          <span class="text-h6">¿Realmente desea cambiar el estatus a Enviado?</span>
        </q-card-section>
        <q-card-section class="text-primary">
          <div class="row q-col-gutter-xs q-col-gutter-sm q-col-gutter-md row q-col-gutter-xs q-col-gutter-lg">
            <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
              <q-btn flat label="Cancelar" color="white" style="background-color: #f44336;" @click="closeGenerateInvoiceModal()" v-close-popup />
            </div>
            <div align="right" class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
              <q-btn flat label="Confirmar" color="white" style="background-color: #21ba45; text-align: right;" @click="remission()" />
            </div>
          </div>
        </q-card-section>
      </q-card>
    </q-dialog>
    <q-dialog v-model="documentFileModal" persistent>
      <q-card style="width: 400px">
        <q-card-section class="row">
          <div class="col-xs-12 col-sm-8 text-h6">Archivo: {{ invoice.fields.pallet }}</div>
          <div  class="col-xs-12 col-sm-4 pull-right">
            <q-btn icon="close" flat round dense v-close-popup />
          </div>
        </q-card-section>
        <q-card-section>
          <q-uploader
            style="min-width: 100%"
            :url="fileDocumentUrl"
            method="POST"
            text-color="dark"
            color="secondary"
            label="Foto de Tarima"
            ref="fileDocumentRef"
            max-files="1"
            auto-upload
            flat
            no-thumbnails
            :headers="[{name: 'Authorization', value: 'Bearer ' + this.JWT}]"
            @uploaded="afterUploadDocumentFile"
          />
        </q-card-section>
        <!-- <q-card-actions align="right" class="text-primary">
          <q-btn flat label="Subir archivo" @click="uploadDocumentFile()" />
        </q-card-actions> -->
      </q-card>
    </q-dialog>
      <!-- <q-card>
        <q-card-section class="row items-center">
          <span class="q-ml-sm">¿Realmente desea cambiar el estatus a Documentado?</span>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancelar" color="primary" v-close-popup />
          <q-btn flat label="Confirmar" color="primary" @click="remission()" />
        </q-card-actions>
      </q-card> -->
      <q-dialog v-model="documentFileModalOrderBuy" persistent>
        <q-card>
          <q-card-section class="row">
            <div class="col-xs-12 col-sm-10 text-h6">Subir archivo</div>
            <q-btn class="col-xs-12 col-sm-2 pull-right" icon="close" flat round dense v-close-popup />
          </q-card-section>
          <q-card-section>
            <q-uploader
              :url="fileDocumentUrlOC"
              :headers="[{name: 'Authorization', value: 'Bearer ' + this.JWT}]"
              method="POST"
              ref="fileDocumentOCRef"
              hide-upload-btn
              @uploaded="afterUploadDocumentFileOC"
            />
          </q-card-section>
          <q-card-actions align="right" class="text-secondary">
            <q-btn flat label="Subir archivo" @click="uploadDocumentFileOc()" />
          </q-card-actions>
        </q-card>
      </q-dialog>
      <q-dialog v-model="cancelarTimbrado" persistent>
      <q-card>
        <q-card-section class="bg-primary">
          <div class="row">
            <div class="col-xs-11 col-sm-11 col-md-11 col-lg-11 text-h6" style="color:white;">Cancelar Factura</div>
            <div class="col-xs-1 col-sm-1 col-md-1 col-lg-1 pull-right"><q-btn color="white" flat v-close-popup round dense icon="close" /></div>
          </div>
        </q-card-section>
        <q-card-section class="items-center">
          <span class="row text-h6">¿Está seguro de cancelar esta factura?</span>
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
                  v-model="fiscal.fields.motivo_cancelacion"
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
        <q-card-section class="items-center" v-if="fiscal.fields.motivo_cancelacion === '01'">
          <div class="row">
              <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <q-input
                  color="dark"
                  bg-color="secondary"
                  filled
                  v-model="fiscal.fields.folio_sustituye"
                  :error="$v.fiscal.fields.folio_sustituye.$error"
                  label="Folio que sustituye"
                  :rules="fiscalFolioSustituye"
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
              <q-btn flat label="Confirmar" color="white" style="background-color: #21ba45; text-align: right;" @click="pagoId == null ? cancelarFactura() : cancelarPago(pagoId)" />
            </div>
          </div>
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import api from '../../commons/api.js'
const { required, decimal, numeric, minValue, maxValue, email, requiredIf } = require('vuelidate/lib/validators')

export default {
  name: 'EditStorageExit',
  validations: {
    invoice: {
      fields: {
        saleDate: { required },
        customer: { required },
        customerBranchOffice: { required },
        carrierName: { required: requiredIf(function () { return this.isLogistic }) },
        carrier: { required: requiredIf(function () { return this.isLogistic }) },
        guideNumber: { required: requiredIf(function () { return this.isLogistic }) }
      }
    },
    inBulkDetail: {
      fields: {
        product: { required },
        qty: { required, decimal },
        packagesQty: { numeric, minValue: minValue(0), maxValue: maxValue(32767) }
      }
    },
    fiscal: {
      fields: {
        razon_social: { required },
        rfc: { required },
        folio_fiscal: { required },
        serie: { },
        fecha_factura: { required },
        lugar_expedicion: { required },
        tipo_comprobante: { required },
        metodo_pago: { required },
        forma_pago: { required },
        uso_cfdi: { required },
        regimen_fiscal: { required },
        folio_sustituye: { required: requiredIf(function () { return this.fiscal.fields.motivo_cancelacion === '01' }) },
        import: { required: requiredIf(function () { return this.fiscal.fields.immex }) },
        export: { required: requiredIf(function () { return this.fiscal.fields.immex }) }
      }
    },
    payments: {
      fields: {
        fecha_pago: { required },
        forma_pago: { required },
        total: { required }
      }
    },
    emailInvoice: {
      fields: {
        email_cliente: { required, email }
      }
    }
  },
  data () {
    return {
      special_order: 0,
      documentFileModalOrderBuy: false,
      isLogistic: false,
      pallet: false,
      documentFileModal: false,
      serverUrl: process.env.API,
      formatter: new Intl.NumberFormat('en-US'),
      currencyFormatter: new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
      }),
      carrier: null,
      carriers: [],
      inBulkCheck: false,
      totalQtyBales: null,
      cndButton: false,
      cndButtonIB: false,
      idDetail: null,
      idDetailIB: null,
      fibraTab: true,
      bulkTab: true,
      lamiTab: true,
      invoiceTab: true,
      paymentTab: true,
      promptEmail: false,
      promptEmailPagos: false,
      tax_invoice: null,
      status_email: null,
      loan: null,
      scid: null,
      invoice: {
        fields: {
          id: null,
          saleDate: null,
          inBulkStorage: null,
          branchOffice: null,
          customer: null,
          customerBranchOffice: null,
          customerPriceList: null,
          driver: null,
          comments: null,
          canRemisionate: false,
          status: null,
          status_timbrado: 0,
          id_request: null,
          uuid: null,
          message: null,
          message_cancelacion: null,
          qtyBales: null,
          qtyinBulk: null,
          qtyLaminates: null,
          carrierName: null,
          carrier: null,
          guideNumber: null,
          pallet: null,
          date_delivered: null,
          oc_referenceshp: null,
          pdf: null
        }
      },
      inBulkDetail: {
        fields: {
          product: null,
          qty: null,
          packagesQty: null
        }
      },
      balesFromSC: [],
      fiscal: {
        fields: {
          razon_social: '',
          rfc: '',
          folio_fiscal: '',
          serie: '',
          fecha_factura: null, // `${new Date().getFullYear()}-${(new Date().getMonth() + 1).toString().padStart(2, '0')}-${(new Date().getDate()).toString().padStart(2, '0')} 12:00:00`,
          lugar_expedicion: '',
          tipo_comprobante: 'I',
          metodo_pago: { value: 'PUE', label: 'PUE - Pago en una sola exhibición' },
          forma_pago: '',
          uso_cfdi: '',
          tipo_cliente: 'cliente',
          taxCompanyId: null,
          motivo_cancelacion: '02',
          folio_sustituye: null,
          folio_relacionado: null,
          regimen_fiscal: null,
          immex: false
        }
      },
      payments: {
        fields: {
          folio_fiscal: '',
          serie: '',
          fecha_pago: '',
          total: '',
          forma_pago: '',
          num_parcialidad: 1
        },
        columns: [
          { name: 'num_parcialidad', label: 'NO. PARCIALIDAD', field: 'num_parcialidad', sortable: true, type: 'string', align: 'left' },
          { name: 'fecha_pago', label: 'FECHA DE PAGO', field: 'fecha_pago', sortable: true, type: 'string', align: 'left' },
          { name: 'total', label: 'TOTAL', field: 'total', sortable: true, type: 'string', align: 'left' },
          { name: 'status_timbrado', label: 'ESTATUS', field: 'status_timbrado', sortable: true, type: 'string', align: 'left' },
          { name: 'acciones', label: 'ACCIONES', field: 'acciones', sortable: false, type: 'string', align: 'right' }
        ],
        rowsOptions: [3, 5, 7, 10, 15, 20, 25, 50],
        pagination: { rowsPerPage: 50 },
        selected: [],
        filter: '',
        loading: false
      },
      history: {
        columns: [
          { name: 'folio_fiscal', label: 'FOLIO', field: 'folio_fiscal', sortable: true, type: 'string', align: 'left' },
          { name: 'serie', label: 'SERIE', field: 'serie', sortable: true, type: 'string', align: 'left' },
          { name: 'uuid', label: 'UUID', field: 'uuid', sortable: true, type: 'string', align: 'left' },
          { name: 'message', label: 'MENSAJE', field: 'message', sortable: true, type: 'string', align: 'left' },
          { name: 'id_cancelacion', label: 'ID CANCELACION', field: 'id_cancelacion', sortable: true, type: 'string', align: 'left' },
          { name: 'fecha_cancelacion_envio', label: 'ENVIO CANCELACION', field: 'fecha_cancelacion_envio', sortable: true, type: 'string', align: 'left' },
          { name: 'motivo_cancelacion', label: 'MOTIVO CANCELACION', field: 'motivo_cancelacion', sortable: true, type: 'string', align: 'left' },
          { name: 'fecha_cancelacion_recibido', label: 'CONFIRMACION CANCELACION', field: 'fecha_cancelacion_recibido', sortable: true, type: 'string', align: 'left' },
          { name: 'acciones', label: 'ACCIONES', field: 'acciones', sortable: false, type: 'string', align: 'right' }
        ],
        rowsOptions: [3, 5, 7, 10, 15, 20, 25, 50],
        pagination: { rowsPerPage: 50 },
        selected: [],
        filter: '',
        loading: false
      },
      emailInvoice: {
        fields: {
          email_cliente: ''
        }
      },
      taxCompanyList: [],
      taxCompanyListOptions: [],
      interval: null,
      inBulkDetailcnd: null,
      branchOfficeOptions: [],
      storageOptions: [],
      customerOptions: [],
      productOptions: [],
      customerBranchOfficeOptions: [],
      filteredCustomerOptions: [],
      driverOptions: [],
      inBulkProducts: [],
      inBulkProductOptions: [],
      pagos: [],
      histories: [],
      tiposClientesOptions: [{ label: 'Cliente de la remisión', value: 'cliente' }, { label: 'Público en general', value: 'publico' }],
      tiposComprobanteOptions: [{ label: 'Ingreso', value: 'I' }],
      metodoPagoOptions: [{ label: 'PUE - Pago en una sola exhibición', value: 'PUE' }, { label: 'PPD - Pago en parcialidades', value: 'PPD' }],
      statusTimbrado: ['Nuevo', 'Timbrado', 'Cancelado', 'Cancelando', 'Timbrando', 'Cancelando', 'Error', 'Error al cancelar'],
      colorTimbrado: ['blue-6', 'green-6', 'purple-6', 'warning', 'warning', 'warning', 'red-6', 'red-6'],
      iconTimbrado: ['add', 'done', 'cancel', 'fas fa-ellipsis-h', 'fas fa-ellipsis-h', 'fas fa-ellipsis-h', 'cancel', 'cancel'],
      formaPagoOptions: [],
      usoCFDIOptions: [],
      currentTab: null,
      inBulkDetails: [],
      inBulkDetailsColumns: [
        { name: 'product', align: 'center', label: 'PRODUCTO', field: 'product', style: 'width: 50%', sortable: true },
        { name: 'qty', align: 'center', label: 'PIEZAS', field: 'qty', style: 'width: 10%', sortable: true },
        { name: 'unitPrice', align: 'center', label: 'PRECIO', field: 'unitPrice', style: 'width: 10%', sortable: true },
        { name: 'totalPrice', align: 'center', label: 'IMPORTE', field: 'totalPrice', style: 'width: 10%', sortable: true },
        // { name: 'packagesQty', align: 'center', label: 'BULTOS', field: 'packagesQty', style: 'width: 10%', sortable: true },
        { name: 'actions', align: 'center', label: 'ACCIONES', field: 'actions', style: 'width: 10%', sortable: false }
      ],
      pagination: {
        rowsPerPage: 50
      },
      confirmRemissionDialog: false,
      confirmHistoricRemissionDialog: false,
      loadingSendingMailBtn: false,
      loadingFiscal: false,
      productsPrices: [],
      confirmDelivered: false,
      info: [],
      dataDocument: [],
      download: false,
      documentId: null,
      idShoppingCart: null,
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
      ],
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
    JWT () {
      return localStorage.getItem('JWT')
    },
    fileDocumentUrlOC () {
      return `${process.env.API}documents/file/${this.idShoppingCart}`
    },
    fileDocumentUrl () {
      return `${process.env.API}invoices/file/${this.$route.params.id}`
    },
    guideNumberRules (val) {
      return [
        val => (this.$v.invoice.fields.guideNumber.required) || 'El campo Número de Guía es requerido.'
      ]
    },
    carrierNameRules (val) {
      return [
        val => (this.$v.invoice.fields.carrierName.required) || 'El campo Nombre del Chofer es requerido.'
      ]
    },
    carrierRules (val) {
      return [
        val => (this.$v.invoice.fields.carrier.required) || 'El campo Paquetería es requerido.'
      ]
    },
    saleDateRules (val) {
      return [
        val => (this.$v.invoice.fields.saleDate.required) || 'El campo Fecha de venta es requerido.'
      ]
    },
    customerRules (val) {
      return [
        val => (this.$v.invoice.fields.customer.required) || 'El campo Cliente es requerido.'
      ]
    },
    productRules (val) {
      return [
        val => this.$v.detail.fields.product.required || 'El campo Producto es requerido.'
      ]
    },
    inBulkDetailProductRules (val) {
      return [
        val => (this.$v.inBulkDetail.fields.product.required) || 'El campo Producto es requerido.'
      ]
    },
    inBulkDetailQtyRules (val) {
      return [
        val => (this.$v.inBulkDetail.fields.qty.required) || 'El campo Cantidad es requerido.',
        val => (this.$v.inBulkDetail.fields.qty.decimal) || 'El campo Cantidad debe ser numérico.'
      ]
    },
    inBulkDetailPackagesQtyRules (val) {
      return [
        val => (this.$v.inBulkDetail.fields.packagesQty.numeric) || 'El campo Bultos debe ser entero.',
        val => (this.$v.inBulkDetail.fields.packagesQty.minValue) || 'El campo Bultos no puede ser negativo.',
        val => (this.$v.inBulkDetail.fields.packagesQty.maxValue) || 'El campo Bultos no debe exceder de 32,767.'
      ]
    },
    customerBranchOfficeRules (val) {
      return [
        val => (this.$v.invoice.fields.customerBranchOffice.required) || 'El campo Sucursal de cliente es requerido.'
      ]
    },
    driverRules (val) {
      return [
        val => (this.$v.invoice.fields.driver.required) || 'El campo Chofer es requerido.'
      ]
    },
    fiscalRazonSocial (val) {
      return [
        val => (this.$v.fiscal.fields.razon_social.required) || 'El cliente no cuenta con razon social.'
      ]
    },
    fiscalRFC (val) {
      return [
        val => (this.$v.fiscal.fields.rfc.required) || 'El cliente no cuenta con un RFC.'
      ]
    },
    fiscalSerie (val) {
      return [
        val => (this.$v.fiscal.fields.serie.required) || 'El campo Serie es requerido.'
      ]
    },
    fiscalFolio (val) {
      return [
        val => (this.$v.fiscal.fields.folio_fiscal.required) || 'El campo Folio es requerido.'
      ]
    },
    fiscalFechaFactura (val) {
      return [
        val => (this.$v.fiscal.fields.fecha_factura.required) || 'El campo Fecha de emisión es requerido.'
      ]
    },
    fiscalLugarExpedicion (val) {
      return [
        val => (this.$v.fiscal.fields.lugar_expedicion.required) || 'El campo Lugar de expedición es requerido.'
      ]
    },
    fiscalTipoComprobante (val) {
      return [
        val => (this.$v.fiscal.fields.tipo_comprobante.required) || 'El campo Tipo de comprobante es requerido.'
      ]
    },
    fiscalMetodoPago (val) {
      return [
        val => (this.$v.fiscal.fields.metodo_pago.required) || 'El campo Método de pago es requerido.'
      ]
    },
    fiscalFormaPago (val) {
      return [
        val => (this.$v.fiscal.fields.forma_pago.required) || 'El campo Forma de pago es requerido.'
      ]
    },
    fiscalUsoCFDI (val) {
      return [
        val => (this.$v.fiscal.fields.uso_cfdi.required) || 'El campo Uso CFDI es requerido.'
      ]
    },
    paymentsFechaFactura (val) {
      return [
        val => (this.$v.payments.fields.fecha_pago.required) || 'El campo Fecha de pago es requerido.'
      ]
    },
    paymentsTotal (val) {
      return [
        val => (this.$v.payments.fields.total.required) || 'El campo Total es requerido.'
      ]
    },
    paymentsFormaPago (val) {
      return [
        val => (this.$v.payments.fields.forma_pago.required) || 'El campo Forma de pago es requerido.'
      ]
    },
    emailRule (val) {
      return [
        val => (this.$v.emailInvoice.fields.email_cliente.email) || 'El campo debe contener un correo válido.'
      ]
    },
    fiscalFolioSustituye (val) {
      return [
        val => (this.$v.fiscal.fields.folio_sustituye.required) || 'El campo Folio que sustituye es requerido.'
      ]
    },
    filteredStorageOptions () {
      let options = []
      if (this.invoice.fields.branchOffice != null && this.invoice.fields.branchOffice.value != null) {
        options = this.storageOptions.filter(so => (Number(so.branchOffice) === Number(this.invoice.fields.branchOffice.value)))
      }
      return options
    },
    filteredCustomerBranchOfficeOptions () {
      let options = []
      if (this.invoice.fields.customer != null && this.invoice.fields.customer.value != null) {
        options = this.customerBranchOfficeOptions.filter(cboo => (Number(cboo.customer) === Number(this.invoice.fields.customer.value)))
      }
      return options
    },
    unitPrice () {
      let price = 0
      if (this.detail.fields.product != null && this.detail.fields.product.value != null && this.invoice.fields.customerPriceList != null) {
        this.balesFromSC.forEach(bale => {
          if (bale.product_id === this.detail.fields.product.value) {
            price = bale.price_product
          }
        })
      }
      return price

      // if (this.detail.fields.product != null && this.detail.fields.product.value != null && this.invoice.fields.customerPriceList != null) {
      //   let prices = this.productsPrices.filter(pp => Number(pp.product_id) === Number(this.detail.fields.product.value) && pp.price_level === this.invoice.fields.customerPriceList)
      //   if (prices.length > 0) {
      //     return prices[0].price
      //   }
      // }
      // return 0
    },
    totalDetailsQties () {
      let totalQties = 0
      this.details.forEach(detail => {
        if (detail.qty) {
          totalQties += Number(detail.qty)
        }
      })
      return totalQties
    },
    totalDetailsPrice () {
      let totalPrice = 0
      this.details.forEach(detail => {
        if (detail.total_price) {
          totalPrice += Number(detail.total_price)
        }
      })
      return totalPrice
    },
    inBulkDetailUnitPrice () {
      if (this.inBulkDetail.fields.product != null && this.inBulkDetail.fields.product != null && this.invoice.fields.customerPriceList != null) {
        const prices = this.productsPrices.filter(pp => Number(pp.product_id) === Number(this.inBulkDetail.fields.product) && pp.price_level === this.invoice.fields.customerPriceList)
        if (prices.length > 0) {
          return prices[0].price
        }
      }
      return 0
    },
    totalInBulkDetailsQties () {
      let totalQties = 0
      this.inBulkDetails.forEach(detail => {
        if (detail.qty) {
          totalQties += Number(detail.qty)
        }
      })
      return totalQties
    },
    totalInBulkDetailsPrice () {
      let totalPrice = 0
      this.inBulkDetails.forEach(detail => {
        if (detail.total_price) {
          totalPrice += Number(detail.total_price)
        }
      })
      return totalPrice
    },
    inBulkDetailAvailableQty () {
      let availableQty = 0
      if (this.inBulkDetail.fields.product != null && !isNaN(this.inBulkDetail.fields.product)) {
        availableQty = this.inBulkProducts.filter(ibp => Number(ibp.product_id) === Number(this.inBulkDetail.fields.qty))
        // let filteredDetails = this.inBulkDetails.filter(ibd => Number(ibd.product_id) === Number(this.inBulkDetail.fields.product))
        // filteredDetails.forEach(detail => {
        //   availableQty -= Number(detail.qty)
        // })
      }
      return availableQty
    },
    isDone () {
      return this.invoice.fields.status_timbrado === 1
    },
    isChecking () {
      return this.invoice.fields.status_timbrado === 3 || this.invoice.fields.status_timbrado === 4 || this.invoice.fields.status_timbrado === 5 || this.invoice.fields.status_timbrado === 7 || this.invoice.fields.status_timbrado === 17
    },
    isRoleInvoice () {
      return this.$store.getters['users/roles'].includes(1) || this.$store.getters['users/rol'] === 27 || this.$store.getters['users/rol'] === 22 || this.$store.getters['users/rol'] === 20 || this.$store.getters['users/rol'] === 4 || this.$store.getters['users/rol'] === 28 || this.$store.getters['users/rol'] === 29 || this.$store.getters['users/rol'] === 17
    },
    isRoleAdmin () {
      return (this.$store.getters['users/rol'] === 1 || this.$store.getters['users/rol'] === 20)
    },
    allQtyBales () {
      let qty = []
      this.details.forEach(detail => {
        if (detail.total_price) {
          qty += Number(detail.qty)
        }
      })
      return qty
    }
  },
  /* beforeCreate () {
    if (!(this.$store.getters['users/roles'].includes(1) || this.$store.getters['users/roles'].includes(17) || this.$store.getters['users/roles'].includes(18) || this.$store.getters['users/roles'].includes(4) || this.$store.getters['users/roles'].includes(2) || this.$store.getters['users/roles'].includes(20))) {
      this.$router.push('/')
    }
  }, */
  beforeRouteEnter (to, from, next) {
    next(vm => {
      const propiedades = vm.$store.getters['users/rol']
      if (propiedades === 1 || propiedades === 3 || propiedades === 7 || propiedades === 2 || propiedades === 20 || propiedades === 4 || propiedades === 27 || propiedades === 22 || propiedades === 29 || propiedades === 28 || propiedades === 17) {
        next()
      } else {
        next('/')
      }
    })
  },
  beforeDestroy: function () {
    clearInterval(this.interval)
  },
  created () {
    this.fetchFromServer()
    this.getCarriers()
    this.getDateCurrent()
  },
  methods: {
    getDateCurrent () {
      api.get('/invoices/getDateCurrent').then(({ data }) => {
        this.fiscal.fields.fecha_factura = data.dateCurrent
      })
    },
    async editReeference () {
      const params = {}
      params.oc_referenceshp = this.invoice.fields.oc_referenceshp
      this.$q.loading.show()
      await api.put(`/shopping-carts/editReeference/${this.idShoppingCart}`, params).then(({ data }) => {
        if (data.result) {
          this.fetchFromServer()
        }
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
      })
      this.$q.loading.hide()
    },
    downloadDocumentOc () {
      return true
    },
    async deleteFileOc () {
      await api.get(`/shopping-carts/getDataOfOrderShoppingcart/${this.$route.params.id}/${'Si'}`).then(({ data }) => {
        if (data.result) {
          this.dataDocument = data.datadocument
          this.download = true
        }
      })
      api.delete(`/documents/${this.dataDocument.id}`).then(({ data }) => {
        if (data.result) {
          this.fetchFromServer()
        }
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
      })
    },
    async downloadDocumentFileOc () {
      await api.get(`/shopping-carts/getDataOfOrderShoppingcart/${this.$route.params.id}/${'Si'}`).then(({ data }) => {
        if (data.result) {
          this.dataDocument = data.datadocument
          this.download = true
        }
      })
      api.fileDownload(`/documents/getFileOrderShoppingCart/${this.dataDocument.id}`).then(({ data }) => {
        this.info.image = data
        const url = window.URL.createObjectURL(new Blob([data], { type: this.dataDocument.mimetype }))
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', this.dataDocument.filename)
        document.body.appendChild(link)
        link.click()
      })
    },
    async showDocumentFileOcMenu () {
      await api.get(`/shopping-carts/getDataOfOrderShoppingcart/${this.$route.params.id}/${'Si'}`).then(({ data }) => {
        if (data.result) {
          this.dataDocument = data.datadocument
          this.download = true
        }
      })
      if (this.download) {
        api.fileDownload(`/documents/getFileOrderShoppingCart/${this.dataDocument.id}`).then(({ data }) => {
          this.info.image = data
          const url = window.URL.createObjectURL(new Blob([data], { type: this.dataDocument.mimetype }))
          window.open(url, '_blank')
        })
      }
    },
    uploadDocumentFileOc () {
      this.$refs.fileDocumentOCRef.upload()
    },
    afterUploadDocumentFileOC (response) {
      const data = JSON.parse(response.xhr.response)
      this.$q.notify({
        message: data.message.content,
        position: 'top',
        color: (data.result ? 'positive' : 'warning')
      })
      if (data.result) {
        this.fetchFromServer()
        this.documentFileModalOrderBuy = false
      }
    },
    quotationNotePDF () {
      const order = 'no'
      // const uri = process.env.API + `shopping-carts/quotationNotePDF/${this.idShoppingCart}/${order}`
      const uri = process.env.API + `invoices/quotationNotePDF/${this.$route.params.id}/${order}`
      window.open(uri, '_blank')
    },
    async showDocumentFileOc () {
      this.documentFileModalOrderBuy = true
      /* this.$q.loading.show() */
      /* await api.get(`/shopping-carts/getDataOfOrderShoppingcart/${this.$route.params.id}/${'Si'}`).then(({ data }) => {
        if (data.result) {
          this.dataDocument = data.datadocument
          this.download = true
        }
      })
      if (this.download) {
        api.fileDownload(`/documents/getFileOrderShoppingCart/${this.dataDocument.id}`).then(({ data }) => {
          this.info.image = data
          const url = window.URL.createObjectURL(new Blob([data], { type: this.dataDocument.mimetype }))
          window.open(url, '_blank')
        })
      }
      this.$q.loading.hide() */
    },
    async downloadDocumentFileOrder () {
      this.$q.loading.show()
      await api.get(`/shopping-carts/getDataOfOrderShoppingcart/${this.$route.params.id}/${'Si'}`).then(({ data }) => {
        if (data.result) {
          this.dataDocument = data.datadocument
          this.download = true
        }
      })
      if (this.download) {
        api.fileDownload(`/documents/getFileOrderShoppingCart/${this.dataDocument.id}`).then(({ data }) => {
          this.$q.loading.hide()
          this.info.image = data
          const url = window.URL.createObjectURL(new Blob([data], { type: this.dataDocument.mimetype }))
          const link = document.createElement('a')
          link.href = url
          link.setAttribute('download', this.dataDocument.filename) // or any other extension
          document.body.appendChild(link)
          link.click()
        })
      }
      this.$q.loading.hide()
    },
    showconfirmDelivered () {
      this.confirmDelivered = true
    },
    cancelConfirDelivered () {
      this.invoice.fields.date_delivered = null
      this.confirmDelivered = false
    },
    changeToDelivered () {
      var f = new Date()
      const date = f.getFullYear() + '/' + (f.getMonth() + 1) + '/' + f.getDate()
      const id = this.$route.params.id
      const params = []
      params.date_delivered = this.invoice.fields.date_delivered
      if (this.invoice.fields.date_delivered > date) {
        this.$q.notify({
          message: 'La fecha ingresada es mayor a la actual',
          position: 'top',
          color: 'warning',
          icon: 'close'
        })
        this.invoice.fields.date_delivered = null
      } else {
        this.$q.loading.show()
        api.put(`/invoices/dateDeliveredUpdate/${id}`, params).then(({ data }) => {
          this.$q.notify({
            message: data.message.content,
            position: 'top',
            color: (data.result ? 'positive' : 'warning'),
            icon: (data.result ? 'thumb_up' : 'close')
          })
          if (data.result) {
            this.fetchFromServer()
          }
          this.invoice.fields.date_delivered = null
        })
      }
      this.invoice.fields.date_delivered = null
      this.$q.loading.hide()
      this.confirmDelivered = false
    },
    saveLogistic () {
      this.isLogistic = true
      this.$v.invoice.fields.$reset()
      this.$v.invoice.fields.$touch()
      if (this.$v.invoice.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = []
      params.carrier_name = this.invoice.fields.carrierName
      params.guide_number = this.invoice.fields.guideNumber
      params.carrier_id = this.invoice.fields.carrier
      api.put(`/invoices/${this.invoice.fields.id}`, params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          api.get(`/invoices/${this.$route.params.id}`).then(({ data }) => {
            this.invoice.fields.id = this.$route.params.id
            this.invoice.fields.saleDate = data.invoice.sale_date
            this.invoice.fields.branchOffice = { value: data.invoice.in_bulk_branch_office_id, label: data.invoice.in_bulk_branch_office }
            this.invoice.fields.inBulkStorage = data.invoice.in_bulk_storage_id && data.invoice.in_bulk_storage ? { value: data.invoice.in_bulk_storage_id, label: data.invoice.in_bulk_storage } : null
            this.invoice.fields.customer = { value: data.invoice.customer_id, label: data.invoice.customer }
            this.invoice.fields.customerBranchOffice = { value: data.invoice.customer_branch_office_id, label: data.invoice.customer_branch_office }
            this.invoice.fields.status = data.invoice.status
            this.invoice.fields.carrier = data.invoice.carrier_id
            this.invoice.fields.carrierName = data.invoice.carrier_name
            this.invoice.fields.guideNumber = data.invoice.guide_number
            this.invoice.fields.pallet = data.invoice.pallet_document
            this.$q.loading.hide()
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    openUploadFileModal () {
      this.documentFileModal = true
    },
    showDocumentFile () {
      if (this.invoice.fields.pallet != null) {
        const extension = this.invoice.fields.pallet.split('.')
        const name = this.$route.params.id
        window.open(`${this.serverUrl}assets/invoices/pallets/${name + '.' + extension[extension.length - 1]}`, '_blank')
      } else {
        this.$q.notify({
          message: 'El documento no cuenta con un archivo subido',
          position: 'top',
          color: 'warning'
        })
      }
    },
    downloadDocumentFile () {
      if (this.invoice.fields.pallet != null) {
        window.open(`${process.env.API}invoices/file/${this.$route.params.id}/download`, '_blank')
      } else {
        this.$q.notify({
          message: 'El pedido no tiene una Foto de Tarima subida',
          position: 'top',
          color: 'warning'
        })
      }
    },
    removeDocument () {
      this.$q.dialog({
        title: 'Confirmar',
        message: '¿Desea borrar este documento?',
        ok: { color: 'positive', label: 'Aceptar' },
        cancel: { color: 'red', label: 'Cancelar' }
      }).onOk(() => {
        this.$q.loading.show()
        api.put(`/invoices/file/${this.$route.params.id}`).then(({ data }) => {
          this.$q.notify({
            message: data.message.content,
            position: 'top',
            color: (data.result ? 'positive' : 'warning')
          })
          if (data.result) {
            this.pallet = false
            api.get(`/invoices/${this.$route.params.id}`).then(({ data }) => {
              this.invoice.fields.pallet = data.invoice.pallet_document
            })
            this.$q.loading.hide()
          } else {
            this.$q.loading.hide()
          }
        })
      })
    },
    afterUploadDocumentFile (response) {
      const data = JSON.parse(response.xhr.response)
      this.$q.notify({
        message: data.message.content,
        position: 'top',
        color: (data.result ? 'positive' : 'warning')
      })
      if (data.result) {
        this.documentFileModal = false
        api.get(`/invoices/${this.$route.params.id}`).then(({ data }) => {
          this.invoice.fields.pallet = data.invoice.pallet_document
        })
      }
    },
    getCarriers () {
      api.get('carriers/options').then(({ data }) => {
        this.carriers = data.options
      })
    },
    fetchFromServer () {
      this.$q.loading.show()
      api.get(`/invoices/${this.$route.params.id}`).then(({ data }) => {
        if (!data.invoice) {
          this.$q.notify({
            message: data.message.content,
            position: 'top',
            color: ('negative')
          })
          this.$router.push('/storage-exits')
          this.$q.loading.hide()
        } else {
          api.get(`/invoices/getHistory/${this.$route.params.id}`).then(({ data }) => { this.histories = data.history })
          this.balesFromSC = data.qtysFromBales
          // data.qtysFromBales.forEach(bale => {
          //   this.productOptions.push({ value: bale.product_id, label: `${bale.product_cat}-${bale.product_line}-${bale.product_name}` })
          // })
          this.invoice.fields.id = this.$route.params.id
          this.invoice.fields.saleDate = data.invoice.sale_date
          this.status_email = data.invoice.status_email
          if (data.invoice.special_order === null || data.invoice.special_order === 0) {
            this.special_order = 0
          } else {
            this.special_order = 1
          }
          if (data.invoice.in_bulk_branch_office_id) {
            this.invoice.fields.branchOffice = { value: data.invoice.in_bulk_branch_office_id, label: data.invoice.in_bulk_branch_office }
          }
          if (data.invoice.in_bulk_storage_id) {
            this.invoice.fields.inBulkStorage = data.invoice.in_bulk_storage_id && data.invoice.in_bulk_storage ? { value: data.invoice.in_bulk_storage_id, label: data.invoice.in_bulk_storage } : null
            this.currentTab = 'inBulkDetails'
            this.bulkTab = false
          }
          if (data.QtyinBulk) {
            this.invoice.fields.qtyinBulk = data.QtyinBulk
          } else {
            this.inBulkCheck = true
          }
          this.scid = data.invoice.shopping_cart_id
          if (data.invoice.tax_invoice === 0) {
            this.tax_invoice = { value: data.invoice.tax_invoice, label: 'SI' }
          } else {
            this.tax_invoice = { value: data.invoice.tax_invoice, label: 'NO' }
          }
          if (data.invoice.loan === 0) {
            this.loan = { value: data.invoice.loan, label: 'NO' }
          } else {
            this.loan = { value: data.invoice.loan, label: 'SI' }
          }
          // this.tax_invoice = data.invoice.tax_invoice
          this.invoice.fields.date_delivered = data.invoice.date_delivered
          this.invoice.fields.customer = { value: data.invoice.customer_id, label: data.invoice.customer }
          this.invoice.fields.customerBranchOffice = { value: data.invoice.customer_branch_office_id, label: data.invoice.customer_branch_office }
          this.invoice.fields.customerPriceList = data.invoice.customer_price_list
          this.invoice.fields.driver = { value: data.invoice.driver_id, label: data.invoice.driver }
          this.invoice.fields.comments = data.invoice.comments
          this.invoice.fields.canRemisionate = data.invoice.canRemisionate
          this.invoice.fields.status = data.invoice.status
          this.invoice.fields.status_timbrado = data.invoice.status_timbrado
          this.invoice.fields.message = data.invoice.message
          this.invoice.fields.message_cancelacion = data.invoice.message_cancelacion
          this.invoice.fields.id_request = data.invoice.id_request
          this.invoice.fields.uuid = data.invoice.uuid
          this.invoice.fields.carrier = data.invoice.carrier_id
          this.invoice.fields.carrierName = data.invoice.carrier_name
          this.invoice.fields.guideNumber = data.invoice.guide_number
          this.invoice.fields.pallet = data.invoice.pallet_document
          this.invoice.fields.oc_referenceshp = data.invoice.oc_referenceshp
          this.invoice.fields.pdf = data.invoice.pdf
          this.documentId = data.invoice.document_id
          this.idShoppingCart = data.invoice.shopping_cart_id
          this.fiscal.fields.folio_relacionado = data.invoice.folio_relacionado
          if ((this.invoice.fields.status_timbrado !== 3 && this.invoice.fields.status_timbrado !== 4 && this.invoice.fields.status_timbrado !== 5) && this.interval !== null) {
            clearInterval(this.interval)
            this.interval = null
          } else if ((this.invoice.fields.status_timbrado === 4 || this.invoice.fields.status_timbrado === 3 || this.invoice.fields.status_timbrado === 5) && this.interval === null) {
            this.currentTab = 'invoiceDetails'
            this.interval = setInterval(() => {
              this.revisarTimbrado()
            }, 10000)
          }
          if (this.invoice.fields.status === 'NUEVO') {
            api.get('/customers/options').then(({ data }) => {
              this.customerOptions = data.options
              api.get('/customer-branch-offices/options').then(({ data }) => {
                this.customerBranchOfficeOptions = data.options
                api.get('/drivers/options').then(({ data }) => {
                  this.driverOptions = data.options
                  api.get('/products-prices').then(({ data }) => {
                    this.productsPrices = data.productsPrices
                    this.changeModel(this.currentTab)
                    this.$q.loading.hide()
                  })
                })
              })
            })
          } else {
            if (this.invoice.fields.status === 'ENVIADO' || this.invoice.fields.status === 'PAGADO') {
              this.invoiceTab = false
              api.get('/invoices/formaPagoOptions').then(({ data }) => {
                this.formaPagoOptions = data.options
                api.get('/invoices/usoCFDIOptions').then(({ data }) => {
                  this.usoCFDIOptions = data.options
                  api.get(`/customer-tax-companies/customer/${this.invoice.fields.customer.value}`).then(({ data }) => {
                    this.taxCompanyList = []
                    this.taxCompanyListOptions = []
                    if (data.customerTaxCompanies.length > 0) {
                      data.customerTaxCompanies.forEach(customer => {
                        this.taxCompanyList[customer.id] = { rfc: customer.rfc, razon_social: customer.razon_social }
                        this.taxCompanyListOptions.push({ value: customer.id, label: `${customer.rfc}` })
                      })
                      this.taxCompanyList[0] = { rfc: 'XAXX010101000', razon_social: 'PUBLICO EN GENERAL' }
                      this.taxCompanyListOptions.push({ value: 0, label: 'XAXX010101000' })
                      this.fiscal.fields.taxCompanyId = this.taxCompanyListOptions[0]
                      this.fiscal.fields.razon_social = this.taxCompanyList[this.fiscal.fields.taxCompanyId.value].razon_social
                      this.fiscal.fields.rfc = this.taxCompanyList[this.fiscal.fields.taxCompanyId.value].rfc
                      api.get(`/customer-tax-companies/${this.fiscal.fields.taxCompanyId.value}`).then(({ data }) => {
                        this.fiscal.fields.tipo_comprobante = { value: 'I', label: 'Ingreso' }
                        const mpLabel = data.customerTaxCompanies.metodo_pago !== 'PPD' ? 'PUE - Pago en una sola exhibición' : 'PPD - Pago en parcialidades'
                        this.fiscal.fields.metodo_pago = { value: data.customerTaxCompanies.metodo_pago, label: mpLabel }
                        this.fiscal.fields.forma_pago = { value: data.customerTaxCompanies.forma_pago, label: data.customerTaxCompanies.forma_pago_label }
                        this.fiscal.fields.uso_cfdi = { value: data.customerTaxCompanies.uso_cfdi, label: data.customerTaxCompanies.uso_cfdi_label }
                        this.emailInvoice.fields.email_cliente = data.customerTaxCompanies.email
                        this.fiscal.fields.regimen_fiscal = data.customerTaxCompanies.regimen_fiscal
                        this.fiscal.fields.immex = data.customerTaxCompanies.immex
                        api.get(`/invoices/getFiscal/${this.$route.params.id}`).then(({ data }) => {
                          const request = data.fiscal
                          this.fiscal.fields.fecha_factura = request.fecha_factura
                          this.fiscal.fields.folio_fiscal = request.folio_fiscal
                          this.fiscal.fields.serie = request.serie
                          this.fiscal.fields.lugar_expedicion = request.lugar_expedicion
                          if (request.tax_company_id !== null) {
                            this.fiscal.fields.folio_fiscal = request.folio_fiscal
                            this.fiscal.fields.tipo_comprobante = { value: 'I', label: 'Ingreso' }
                            const mpLabel = request.metodo_pago !== 'PPD' ? 'PUE - Pago en una sola exhibición' : 'PPD - Pago en parcialidades'
                            this.fiscal.fields.metodo_pago = { value: request.metodo_pago, label: mpLabel }
                            this.fiscal.fields.forma_pago = { value: request.forma_pago_id, label: request.forma_pago }
                            this.fiscal.fields.uso_cfdi = { value: request.uso_cfdi_id, label: request.uso_cfdi }
                            this.fiscal.fields.razon_social = request.razon_social !== null ? request.razon_social : this.fiscal.fields.razon_social
                            this.fiscal.fields.rfc = request.rfc !== null ? request.rfc : this.fiscal.fields.rfc
                            this.fiscal.fields.taxCompanyId = request.tax_company_id !== null ? { value: request.tax_company_id, label: request.rfc } : this.fiscal.fields.taxCompanyId
                            this.emailInvoice.fields.email_cliente = request.email_cliente
                            this.fiscal.fields.regimen_fiscal = request.regimen_fiscal
                            this.fiscal.fields.immex = request.immex
                            this.fiscal.fields.import = request.import
                            this.fiscal.fields.export = request.export
                          } else {
                            if (request.folio_fiscal == null) {
                              api.get(`/invoices/nextFolio/${this.$route.params.id}/${this.fiscal.fields.serie}`).then(({ data }) => {
                                this.fiscal.fields.folio_fiscal = data.folio_fiscal
                              })
                            }
                          }
                          if (this.isDone && this.fiscal.fields.metodo_pago.value === 'PPD') {
                            this.currentTab = 'paymentDetails'
                            api.get(`/invoices/getPagos/${this.$route.params.id}`).then(({ data }) => {
                              this.pagos = data.pagos
                              api.get(`/invoices/keepCheckingPayments/${this.$route.params.id}`).then(({ data }) => {
                                if (!data.result) {
                                  clearInterval(this.interval)
                                  this.interval = null
                                } else if (this.interval === null) {
                                  this.interval = setInterval(() => {
                                    this.revisarPagos()
                                  }, 10000)
                                }
                              })
                            })
                          }
                        })
                      })
                    } else {
                      this.taxCompanyList[0] = { rfc: 'XAXX010101000', razon_social: 'PUBLICO EN GENERAL' }
                      this.taxCompanyListOptions.push({ value: 0, label: 'XAXX010101000' })
                      this.fiscal.fields.taxCompanyId = this.taxCompanyListOptions[0]
                      this.fiscal.fields.razon_social = this.taxCompanyList[this.fiscal.fields.taxCompanyId.value].razon_social
                      this.fiscal.fields.rfc = this.taxCompanyList[this.fiscal.fields.taxCompanyId.value].rfc
                      this.fiscal.fields.serie = 'A'
                      api.get(`/invoices/getFiscal/${this.$route.params.id}`).then(({ data }) => {
                        const request = data.fiscal
                        this.fiscal.fields.fecha_factura = request.fecha_factura
                        /* if (request.fecha_factura === null) {
                          this.fiscal.fields.fecha_factura = `${new Date().getFullYear()}-${(new Date().getMonth() + 1).toString().padStart(2, '0')}-${(new Date().getDate()).toString().padStart(2, '0')} 12:00:00`
                        } else {
                          this.fiscal.fields.fecha_factura = request.fecha_factura
                        } */
                        if (request.serie != null) { this.fiscal.fields.serie = request.serie }
                        if (request.folio_fiscal != null) { this.fiscal.fields.folio_fiscal = request.folio_fiscal } else { this.fiscal.fields.folio_fiscal = 1 }
                        if (request.lugar_expedicion != null) { this.fiscal.fields.lugar_expedicion = request.lugar_expedicion }
                        this.fiscal.fields.tipo_comprobante = { value: 'I', label: 'Ingreso' }
                        if (request.metodo_pago != null) {
                          var mpResponse = request.metodo_pago
                          const mpLabel = request.metodo_pago === 'PUE' ? 'PUE - Pago en una sola exhibición' : 'PPD - Pago en parcialidades'
                          const mpValue = request.metodo_pago === 'PUE' ? 'PUE' : 'PPD'
                          this.fiscal.fields.metodo_pago = { value: mpValue, label: mpLabel }
                        }
                        if (request.forma_pago != null) { this.fiscal.fields.forma_pago = { value: request.forma_pago_id, label: request.forma_pago } }
                        if (request.uso_cfdi != null) { this.fiscal.fields.uso_cfdi = { value: request.uso_cfdi_id, label: request.uso_cfdi } }
                        if (request.timbrado != null && request.status_timbrado === 1) {
                          this.fiscal.fields.razon_social = request.razon_social !== null ? request.razon_social : this.fiscal.fields.razon_social
                          this.fiscal.fields.rfc = request.rfc !== null ? request.rfc : this.fiscal.fields.rfc
                          this.fiscal.fields.taxCompanyId = { value: request.tax_company_id, label: request.rfc }
                        } else {
                          if (data.email_cliente) { this.emailInvoice.fields.email_cliente = request.email_cliente }
                        }
                        if (this.isDone && mpResponse === 'PPD') {
                          this.currentTab = 'paymentDetails'
                          api.get(`/invoices/getPagos/${this.$route.params.id}`).then(({ data }) => {
                            this.pagos = data.pagos
                            api.get(`/invoices/keepCheckingPayments/${this.$route.params.id}`).then(({ data }) => {
                              if (!data.result) {
                                clearInterval(this.interval)
                                this.interval = null
                              } else if (this.interval === null) {
                                this.interval = setInterval(() => {
                                  this.revisarPagos()
                                }, 10000)
                              }
                            })
                          })
                        }
                      })
                    }
                  })
                })
              })
            }
            this.changeModel(this.currentTab)
            this.$q.loading.hide()
          }
        }
      })
    },
    isNumber (evt, input) {
      switch (input) {
        case 'total':
          this.payments.fields.total = this.payments.fields.total.replace(/[^0-9.]/g, '')
          this.$v.payments.fields.total.$touch()
          break
        case 'codigo_postal':
          this.fiscal.fields.lugar_expedicion = this.fiscal.fields.lugar_expedicion.replace(/[^0-9.]/g, '')
          this.$v.fiscal.fields.lugar_expedicion.$touch()
          break
        case 'folio_sustituye':
          this.fiscal.fields.folio_sustituye = this.fiscal.fields.folio_sustituye.replace(/[^0-9.]/g, '')
          this.$v.fiscal.fields.folio_sustituye.$touch()
          break
        default:
          break
      }
    },
    backToGrid () {
      this.$router.push('/storage-exits')
    },
    remission () {
      if (this.confirmHistoricRemissionDialog) {
        this.isLogistic = false
      } else {
        this.isLogistic = true
      }
      this.$v.invoice.fields.$reset()
      this.$v.invoice.fields.$touch()
      /* if (this.$v.invoice.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        this.currentTab = 'logistic'
        this.confirmRemissionDialog = false
        return false
      } else { */
      this.confirmRemissionDialog = false
      const cnd = this.canRemisionate()
      if (cnd === true) {
        /* if (this.invoice.fields.pallet === null && this.isLogistic === true) {
          this.$q.notify({
            message: 'Favor de Subir La Foto de Tarima',
            position: 'top',
            color: 'warning'
          })
          this.currentTab = 'logistic'
        } else { */
        this.$q.loading.show()
        const params = []
        if (this.confirmHistoricRemissionDialog) {
          params.isHistoric = true
        } else {
          params.isHistoric = false
        }
        params.carrier_name = this.invoice.fields.carrierName
        params.guide_number = this.invoice.fields.guideNumber
        params.carrier_id = this.invoice.fields.carrier
        api.put(`/invoices/${this.invoice.fields.id}/remission`, params).then(({ data }) => {
          this.$q.notify({
            message: data.message.content,
            position: 'top',
            color: (data.result ? 'positive' : 'warning')
          })
          if (data.result) {
            this.fetchFromServer()
            // api.get(`/invoices/${this.$route.params.id}`).then(({ data }) => {
            //   this.confirmHistoricRemissionDialog = false
            //   this.invoice.fields.id = this.$route.params.id
            //   this.invoice.fields.saleDate = data.invoice.sale_date
            //   if (data.invoice.in_bulk_branch_office_id) {
            //     this.invoice.fields.branchOffice = { value: data.invoice.in_bulk_branch_office_id, label: data.invoice.in_bulk_branch_office }
            //   }
            //   this.invoice.fields.inBulkStorage = data.invoice.in_bulk_storage_id && data.invoice.in_bulk_storage ? { value: data.invoice.in_bulk_storage_id, label: data.invoice.in_bulk_storage } : null
            //   this.invoice.fields.customer = { value: data.invoice.customer_id, label: data.invoice.customer }
            //   this.invoice.fields.customerBranchOffice = { value: data.invoice.customer_branch_office_id, label: data.invoice.customer_branch_office }
            //   this.invoice.fields.driver = { value: data.invoice.driver_id, label: data.invoice.driver }
            //   this.invoice.fields.status = data.invoice.status
            //   this.invoice.fields.carrier = data.invoice.carrier_id
            //   this.invoice.fields.carrierName = data.invoice.carrier_name
            //   this.invoice.fields.guideNumber = data.invoice.guide_number
            //   this.invoice.fields.pallet = data.invoice.pallet_document
            //   this.invoiceTab = false
            //   api.get('/invoices/formaPagoOptions').then(({ data }) => {
            //     this.formaPagoOptions = data.options
            //     api.get('/invoices/usoCFDIOptions').then(({ data }) => {
            //       this.usoCFDIOptions = data.options
            //       api.get(`/customer-tax-companies/customer/${this.invoice.fields.customer.value}`).then(({ data }) => {
            //         this.taxCompanyList = []
            //         this.taxCompanyListOptions = []
            //         data.customerTaxCompanies.forEach(customer => {
            //           this.taxCompanyList[customer.id] = { rfc: customer.rfc, razon_social: customer.razon_social }
            //           this.taxCompanyListOptions.push({ value: customer.id, label: `${customer.rfc}` })
            //         })
            //         this.taxCompanyList[0] = { rfc: 'XAXX010101000', razon_social: 'PUBLICO EN GENERAL' }
            //         this.taxCompanyListOptions.push({ value: 0, label: 'XAXX010101000' })
            //         this.fiscal.fields.taxCompanyId = this.taxCompanyListOptions[0]
            //         this.fiscal.fields.razon_social = this.taxCompanyList[this.fiscal.fields.taxCompanyId.value].razon_social
            //         this.fiscal.fields.rfc = this.taxCompanyList[this.fiscal.fields.taxCompanyId.value].rfc
            //         api.get(`/customer-tax-companies/${this.fiscal.fields.taxCompanyId.value}`).then(({ data }) => {
            //           this.fiscal.fields.lugar_expedicion = data.customerTaxCompanies.lugar_expedicion
            //           this.fiscal.fields.tipo_comprobante = { value: 'I', label: 'Ingreso' }
            //           const mpLabel = data.customerTaxCompanies.metodo_pago !== 'PPD' ? 'PUE - Pago en una sola exhibición' : 'PPD - Pago en parcialidades'
            //           this.fiscal.fields.metodo_pago = { value: data.customerTaxCompanies.metodo_pago, label: mpLabel }
            //           this.fiscal.fields.forma_pago = { value: data.customerTaxCompanies.forma_pago, label: data.customerTaxCompanies.forma_pago_label }
            //           this.fiscal.fields.uso_cfdi = { value: data.customerTaxCompanies.uso_cfdi, label: data.customerTaxCompanies.uso_cfdi_label }
            //           this.fiscal.fields.serie = data.customerTaxCompanies.serie
            //           this.emailInvoice.fields.email_cliente = data.customerTaxCompanies.email
            //           api.get(`/invoices/nextFolio/${this.$route.params.id}/${this.fiscal.fields.serie}`).then(({ data }) => {
            //             this.fiscal.fields.folio_fiscal = data.folio_fiscal
            //           })
            //         })
            //       })
            //     })
            //   })
            //   this.$q.loading.hide()
            // })
          } else {
            this.$q.loading.hide()
          }
        })
        // }
      } else {
        this.$q.loading.hide()
        this.$q.notify({
          message: 'Tiene detalles en cantidad 0, porfavor comuniquese con el administrador, para poder eliminarlos',
          position: 'top',
          color: 'negative'
        })
      }
      // }
    },
    canRemisionate () {
      let cnd = true
      this.inBulkDetails.forEach(detail => {
        if (detail.qty === 0 || detail.qty === '0') {
          cnd = false
        }
      })
      return cnd
    },
    saleReferral () {
      const uri = process.env.API + `invoices/pdf/${this.$route.params.id}`
      window.open(uri, '_blank')
    },
    sendSaleReferral () {
      this.loadingSendingMailBtn = true
      api.get(`/invoices/${this.$route.params.id}/send-mail`).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        this.loadingSendingMailBtn = false
      })
    },
    updateSale () {
      this.isLogistic = false
      this.$v.invoice.fields.$reset()
      this.$v.invoice.fields.$touch()
      if (this.$v.invoice.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = []
      params.comments = { ...this.invoice.fields }.comments
      api.put(`/invoices/${this.invoice.fields.id}`, params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          api.get(`/invoices/${this.$route.params.id}`).then(({ data }) => {
            this.invoice.fields.id = this.$route.params.id
            this.invoice.fields.saleDate = data.invoice.sale_date
            this.invoice.fields.branchOffice = { value: data.invoice.in_bulk_branch_office_id, label: data.invoice.in_bulk_branch_office }
            this.invoice.fields.inBulkStorage = data.invoice.in_bulk_storage_id && data.invoice.in_bulk_storage ? { value: data.invoice.in_bulk_storage_id, label: data.invoice.in_bulk_storage } : null
            this.invoice.fields.customer = { value: data.invoice.customer_id, label: data.invoice.customer }
            this.invoice.fields.customerBranchOffice = { value: data.invoice.customer_branch_office_id, label: data.invoice.customer_branch_office }
            this.invoice.fields.driver = { value: data.invoice.driver_id, label: data.invoice.driver }
            this.invoice.fields.status = data.invoice.status
            this.invoice.fields.carrier = data.invoice.carrier_id
            this.invoice.fields.carrierName = data.invoice.carrier_name
            this.invoice.fields.guideNumber = data.invoice.guide_number
            this.invoice.fields.pallet = data.invoice.pallet_document
            this.$q.loading.hide()
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    changeModel (newModel) {
      if (newModel === 'inBulkDetails' && this.inBulkDetails.length === 0) {
        this.$q.loading.show()
        api.get(`/invoice-in-bulk-details/invoice/${this.$route.params.id}`).then(({ data }) => {
          this.inBulkDetails = data.invoiceDetails
          if (this.inBulkDetails.length > 0) {
            this.inBulkDetails.push({ id: null, invoice_id: null, product_id: null, product: 'TOTAL:', qty: null })
          }
          if (this.invoice.fields.status === 'NUEVO' && this.inBulkProducts.length === 0 && this.inBulkProductOptions.length === 0) {
            api.get(`/storages/${this.invoice.fields.inBulkStorage.value}/bulk-products`).then(({ data }) => {
              this.inBulkProducts = []
              this.inBulkProductOptions = []
              data.products.forEach(product => {
                const details = this.inBulkDetails.filter(det => Number(det.product_id) === Number(product.product_id))
                details.forEach(det => {
                  product.qty -= det.qty
                })
                this.inBulkProducts.push(product)
                this.inBulkProductOptions.push({ value: product.product_id, label: `${product.category_code}-${product.line_code}-${product.product_name}` })
              })
              this.$q.loading.hide()
            })
          } else {
            this.$q.loading.hide()
          }
        })
      }
    },
    addInBulkDetail () {
      this.$v.inBulkDetail.fields.$reset()
      this.$v.inBulkDetail.fields.$touch()
      if (this.$v.inBulkDetail.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = []
      params.product_id = Number({ ...this.inBulkDetail.fields }.product.value)
      params.qty = Number({ ...this.inBulkDetail.fields }.qty)
      params.packages_qty = Number({ ...this.inBulkDetail.fields }.packagesQty)
      params.invoice_id = Number(this.$route.params.id)
      this.inBulkDetail.fields.product = null
      this.inBulkDetail.fields.qty = null
      this.inBulkDetail.fields.packagesQty = null
      api.post('/invoice-in-bulk-details', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          api.get(`/invoice-in-bulk-details/invoice/${this.$route.params.id}`).then(({ data }) => {
            this.inBulkDetails = data.invoiceDetails
            if (this.inBulkDetails.length > 0) {
              api.get(`/storages/${this.invoice.fields.inBulkStorage.value}/bulk-products`).then(({ data }) => {
                this.inBulkProducts = []
                this.inBulkProductOptions = []
                data.products.forEach(product => {
                  const details = this.inBulkDetails.filter(det => Number(det.product_id) === Number(product.product_id))
                  details.forEach(det => {
                    product.qty -= det.qty
                  })
                  this.inBulkProducts.push(product)
                  this.inBulkProductOptions.push({ value: product.product_id, label: `${product.category_code}-${product.line_code}-${product.product_name}` })
                })
                this.$q.loading.hide()
              })
              this.inBulkDetails.push({ id: null, invoice_id: null, product_id: null, product: 'TOTAL:', qty: null })
            } else {
              this.$q.loading.hide()
            }
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    removeInBulkDetail (detailElementId) {
      this.$q.loading.show()
      api.delete(`/invoice-in-bulk-details/${detailElementId}`).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          api.get(`/invoice-in-bulk-details/invoice/${this.$route.params.id}`).then(({ data }) => {
            this.inBulkDetails = data.invoiceDetails
            if (this.inBulkDetails.length > 0) {
              api.get(`/storages/${this.invoice.fields.inBulkStorage.value}/bulk-products`).then(({ data }) => {
                this.inBulkProducts = []
                this.inBulkProductOptions = []
                data.products.forEach(product => {
                  const details = this.inBulkDetails.filter(det => Number(det.product_id) === Number(product.product_id))
                  details.forEach(det => {
                    product.qty -= det.qty
                  })
                  this.inBulkProducts.push(product)
                  this.inBulkProductOptions.push({ value: product.product_id, label: `${product.category_code}-${product.line_code}-${product.product_name}` })
                })
              })
              this.inBulkDetails.push({ id: null, invoice_id: null, product_id: null, product: 'TOTAL:', qty: null })
            }
            this.$q.loading.hide()
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    editInBulkDetail (row) {
      // if (row.qty > 0) {
      this.cndButtonIB = true
      this.inBulkDetail.fields.product = { value: row.product_id, label: row.product }
      this.inBulkDetail.fields.qty = row.qty
      this.inBulkDetailcnd = row.qtyfromcart
      // this.inBulkDetailAvailableQty = row.unit_price
      this.idDetailIB = row.id
      // }
    },
    editIB () {
      this.$q.loading.show()
      if (Number(this.inBulkDetail.fields.qty) > Number(this.inBulkDetailcnd * 1.20)) {
        this.$q.notify({
          message: 'La cantidad excede el limite del pedido.',
          position: 'top',
          color: ('negative')
        })
        this.$q.loading.hide()
      } else {
        const params = []
        params.detailId = this.idDetailIB
        params.newQty = this.inBulkDetail.fields.qty
        params.bulks = this.inBulkDetail.fields.packagesQty
        params.status = this.invoice.fields.status
        params.product_id = this.inBulkDetail.fields.product
        api.post('/invoice-in-bulk-details/editinBulksDetails', params).then(({ data }) => {
          this.$q.notify({
            message: data.message.content,
            position: 'top',
            color: (data.result ? 'positive' : 'warning')
          })
          if (data.result) {
            this.cndButtonIB = false
            this.cndButton = false
            this.refreshinBulk()
            this.cleanFieldsInbulk()
            this.$q.loading.hide()
          } else {
            this.refreshinBulk()
            this.cleanFieldsInbulk()
            this.cndButtonIB = true
            this.cndButton = true
            this.$q.loading.hide()
          }
        })
      }
    },
    filtrarClientes (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.filteredCustomerOptions = this.customerOptions.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    siguienteFolio () {
      // var serie = { ...this.fiscal.fields }.serie
      /* api.get(`/invoices/nextFolio/${this.$route.params.id}`).then(({ data }) => {
        this.fiscal.fields.folio_fiscal = data.folio_fiscal
      }) */
    },
    actualizarFiscal () {
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
      const params = []
      params.tipo_cliente = { ...this.fiscal.fields }.tipo_cliente.value
      params.folio_fiscal = { ...this.fiscal.fields }.folio_fiscal
      params.serie = { ...this.fiscal.fields }.serie
      params.fecha_factura = { ...this.fiscal.fields }.fecha_factura
      params.lugar_expedicion = { ...this.fiscal.fields }.lugar_expedicion
      params.tipo_comprobante = { ...this.fiscal.fields }.tipo_comprobante.value
      params.metodo_pago = { ...this.fiscal.fields }.metodo_pago.value
      params.forma_pago = { ...this.fiscal.fields }.forma_pago.value
      params.uso_cfdi = { ...this.fiscal.fields }.uso_cfdi.value
      params.folio_relacionado = { ...this.fiscal.fields }.folio_relacionado
      params.regimen_fiscal = { ...this.fiscal.fields }.regimen_fiscal
      params.tax_company_id = { ...this.fiscal.fields }.taxCompanyId.value
      api.put(`/invoices/fiscalData/${this.invoice.fields.id}`, params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        this.$q.loading.hide()
        this.fetchFromServer()
      })
    },
    async timbrarRemision () {
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
      const params = []
      params.tipo_cliente = { ...this.fiscal.fields }.tipo_cliente.value
      params.folio_fiscal = { ...this.fiscal.fields }.folio_fiscal
      params.serie = { ...this.fiscal.fields }.serie
      params.fecha_factura = { ...this.fiscal.fields }.fecha_factura
      params.lugar_expedicion = { ...this.fiscal.fields }.lugar_expedicion
      params.tipo_comprobante = { ...this.fiscal.fields }.tipo_comprobante.value
      params.metodo_pago = { ...this.fiscal.fields }.metodo_pago.value
      params.forma_pago = { ...this.fiscal.fields }.forma_pago.value
      params.uso_cfdi = { ...this.fiscal.fields }.uso_cfdi.value
      params.folio_relacionado = { ...this.fiscal.fields }.folio_relacionado
      params.regimen_fiscal = { ...this.fiscal.fields }.regimen_fiscal
      params.import = { ...this.fiscal.fields }.import
      params.export = { ...this.fiscal.fields }.export
      params.tax_company_id = (typeof this.fiscal.fields.taxCompanyId.value === 'undefined') ? this.fiscal.fields.taxCompanyId : this.fiscal.fields.taxCompanyId.value
      await api.put(`/invoices/timbrar/${this.invoice.fields.id}`, params).then(({ data }) => {
        this.$q.loading.hide()
        if (data.result === 'error') {
          this.$q.notify({
            message: data.message.content,
            position: 'top',
            color: (data.result !== 'error' ? 'positive' : 'warning')
          })
        } else {
        }
      }).catch(error => {
        console.error(error)
      })
      this.fetchFromServer()
    },
    cancelarFactura () {
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
      const params = {}
      params.motivo_cancelacion = this.fiscal.fields.motivo_cancelacion
      params.folio_sustituye = this.fiscal.fields.folio_sustituye
      this.$q.loading.show()
      api.put(`/invoices/cancelar/${this.invoice.fields.id}`, params).then(({ data }) => {
        this.$q.loading.hide()
        this.cancelarTimbrado = false
        this.fiscal.fields.motivo_cancelacion = '02'
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
    },
    revisarTimbrado () {
      this.loadingFiscal = true
      api.put(`/invoices/revisarTimbrado/${this.invoice.fields.id}`).then(({ data }) => {
        if (data.response.status === 'done' || data.response.status === 'error' || data.response.status === true) {
          this.fetchFromServer()
        }
        if ((data.response.status === 'done' || data.response.status === true) && this.status_email === 'NUEVO') {
          const params2 = []
          params2.id = { ...this.invoice.fields }.id
          params2.email = { ...this.emailInvoice.fields }.email_cliente
          api.post('/invoices/sendNewPdfInvoiceToCustomer', params2).then(({ data }) => {
            this.$q.notify({
              message: data.message.content,
              position: 'top',
              color: (data.result ? 'positive' : 'warning')
            })
          })
        }
      }).catch(error => {
        console.error(error)
      })
      this.loadingFiscal = false
    },
    agregarPago () {
      this.$v.payments.fields.$reset()
      this.$v.payments.fields.$touch()
      if (this.$v.payments.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = []
      params.fecha_pago = { ...this.payments.fields }.fecha_pago
      params.forma_pago = { ...this.payments.fields }.forma_pago.value
      params.total = { ...this.payments.fields }.total
      api.post(`/invoices/agregarPago/${this.invoice.fields.id}`, params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        this.payments.fields.fecha_pago = ''
        this.payments.fields.forma_pago = ''
        this.payments.fields.total = ''
        this.$v.payments.fields.$reset()
        this.fetchFromServer()
        this.$q.loading.hide()
      })
    },
    timbrarPago (row) {
      this.$q.loading.show()
      api.put('/invoices/timbrarPago/' + row.id).then(({ data }) => {
        this.$q.loading.hide()
        if (data.result === 'error') {
          this.$q.notify({
            message: data.message.content,
            position: 'top',
            color: (data.result ? 'positive' : 'warning')
          })
        } else {
          this.fetchFromServer()
        }
      }).catch(error => {
        console.error(error)
      })
    },
    revisarPagos () {
      this.loadingFiscal = true
      api.put(`/invoices/revisarPagos/${this.invoice.fields.id}`).then(({ data }) => {
        if (data.response.status === 'done' || data.response.status === 'error' || data.response.status === true) {
          this.fetchFromServer()
        }
      }).catch(error => {
        console.error(error)
      })
      this.loadingFiscal = false
    },
    cancelarPago (id) {
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
      const params = {}
      params.motivo_cancelacion = this.fiscal.fields.motivo_cancelacion
      params.folio_sustituye = this.fiscal.fields.folio_sustituye
      this.$q.loading.show()
      api.put('/invoices/cancelarPago/' + id, params).then(({ data }) => {
        this.$q.loading.hide()
        this.fiscal.fields.motivo_cancelacion = '02'
        this.pagoId = null
        this.cancelarTimbrado = false
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
    },
    borrarPago (pagoId) {
      this.$q.loading.show()
      api.delete(`/invoices/borrarPago/${pagoId}`).then(({ data }) => {
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
      // var url = process.env.API === 'http://api.tf.antfarm.mx/' ? 'batuta.antfarm.mx' : 'batuta.beta.antfarm.mx'
      // window.open('http://' + url + '/api/get_pdf/' + idRequest + '/0', '_blank')
    },
    cleanFieldsInbulk () {
      this.inBulkDetail.fields.product = null
      this.inBulkDetail.fields.qty = ''
      this.idDetailsIB = ''
    },
    refreshinBulk () {
      api.get(`/invoice-in-bulk-details/invoice/${this.$route.params.id}`).then(({ data }) => {
        this.inBulkDetails = data.invoiceDetails
        if (this.inBulkDetails.length > 0) {
          this.inBulkDetails.push({ id: null, invoice_id: null, product_id: null, product: 'TOTAL:', qty: null })
        }
        if (this.invoice.fields.status === 'NUEVO' && this.inBulkProducts.length === 0 && this.inBulkProductOptions.length === 0) {
          api.get(`/storages/${this.invoice.fields.inBulkStorage.value}/bulk-products`).then(({ data }) => {
            this.inBulkProducts = []
            this.inBulkProductOptions = []
            data.products.forEach(product => {
              const details = this.inBulkDetails.filter(det => Number(det.product_id) === Number(product.product_id))
              details.forEach(det => {
                product.qty -= det.qty
              })
              this.inBulkProducts.push(product)
              this.inBulkProductOptions.push({ value: product.product_id, label: `${product.category_code}-${product.line_code}-${product.product_name}` })
            })
            this.$q.loading.hide()
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    selectEmail () {
      this.promptEmail = true
    },
    selectEmailsPagos () {
      this.promptEmailPagos = true
    },
    sendEmailInvoice () {
      this.$v.emailInvoice.fields.$reset()
      this.$v.emailInvoice.fields.$touch()
      if (this.$v.emailInvoice.fields.$error) {
        return false
      }
      this.loadingSendingMailBtn = true
      const params = []
      params.id = { ...this.invoice.fields }.id
      params.email = { ...this.emailInvoice.fields }.email_cliente
      api.post('/invoices/sendNewPdfInvoiceToCustomer', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.promptEmail = false
          this.emailInvoice.fields.email_cliente = data.email_cliente
          this.loadingSendingMailBtn = false
        } else {
          this.loadingSendingMailBtn = false
        }
      })
    },
    sendEmailsPagos () {
      this.$v.emailInvoice.fields.$reset()
      this.$v.emailInvoice.fields.$touch()
      if (this.$v.emailInvoice.fields.$error) {
        return false
      }
      this.loadingSendingMailBtn = true
      const ids = []
      this.payments.selected.forEach(row => {
        if (parseInt(row.status_timbrado) === 1) {
          ids.push(row.id)
        }
      })
      const params = []
      params.ids = ids
      params.email = { ...this.emailInvoice.fields }.email_cliente
      api.post('/invoices/sendEmailsPagos', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.promptEmailPagos = false
          this.emailInvoice.fields.email_cliente = data.email_cliente
          this.loadingSendingMailBtn = false
        } else {
          this.loadingSendingMailBtn = false
        }
      })
    },
    onTypeClientChange () {
      this.fiscal.fields.razon_social = this.taxCompanyList[this.fiscal.fields.taxCompanyId.value].razon_social
      this.fiscal.fields.rfc = this.taxCompanyList[this.fiscal.fields.taxCompanyId.value].rfc
      this.fiscal.fields.immex = false
      if (this.fiscal.fields.taxCompanyId.value !== 0) {
        api.get(`/customer-tax-companies/${this.fiscal.fields.taxCompanyId.value}`).then(({ data }) => {
          this.fiscal.fields.tipo_comprobante = { value: 'I', label: 'Ingreso' }
          const mpLabel = data.customerTaxCompanies.metodo_pago !== 'PPD' ? 'PUE - Pago en una sola exhibición' : 'PPD - Pago en parcialidades'
          this.fiscal.fields.metodo_pago = { value: data.customerTaxCompanies.metodo_pago, label: mpLabel }
          this.fiscal.fields.forma_pago = { value: data.customerTaxCompanies.forma_pago, label: data.customerTaxCompanies.forma_pago_label }
          this.fiscal.fields.uso_cfdi = { value: data.customerTaxCompanies.uso_cfdi, label: data.customerTaxCompanies.uso_cfdi_label }
          this.emailInvoice.fields.email_cliente = data.customerTaxCompanies.email
          this.fiscal.fields.immex = data.customerTaxCompanies.immex
          this.siguienteFolio()
        })
      }
    },
    generarTicket () {
      var user = this.$store.getters['users/id']
      // const uri = process.env.API + `ticket/crearTicket/${this.scid}/${user}`
      const uri = process.env.API + `ticket/crearTicket/${this.$route.params.id}/${user}`
      window.open(uri, '_blank')
    },
    CancelRemision () {
      this.$q.dialog({
        title: 'Confirmación',
        message: '¿Desea Cancelar esta Remision?',
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
        const params = []
        params.id = this.invoice.fields.id
        api.post('/invoices/cancelRemision', params).then(({ data }) => {
          this.$q.notify({
            message: data.message.content,
            position: 'top',
            color: (data.result ? 'positive' : 'warning')
          })
        })
      }).onCancel(() => {})
    },
    setFormaPago () {
      if (this.fiscal.fields.metodo_pago.value === 'PPD') {
        this.fiscal.fields.forma_pago = 22
      }
    }
  }
}
</script>

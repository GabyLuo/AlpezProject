<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-8 col-md-8 row">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="right" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Ordenes de compra" to="/purchase-orders" />
              <q-breadcrumbs-el :label="this.order.fields.serial + ''" />
            </q-breadcrumbs>
          </div>
        </div>
        <div class="q-pa-md q-gutter-sm col-xs-12 col-sm-4 pull-right" v-if="details.length > 0">
          <q-btn style="margin-left: 8px;" v-if="(order.fields.status === 'NUEVO' || order.fields.status === 'RECIBIDO' || order.fields.status === 'PEDIDO') && (roleId === 1 || roleId === 22 || roleId === 26)" color="blue" icon="fas fa-file-pdf" @click="confirmSendPDFQuote()">
            <q-tooltip content-class="bg-blue">PDF cotización</q-tooltip>
          </q-btn>
          <!-- <q-btn style="margin-left: 8px;" v-if="order.fields.status === 'NUEVO' && (roleId === 1 || roleId === 22 || roleId === 26)" color="purple" icon="mail" @click="confirmSendPDFQuote()">
            <q-tooltip content-class="bg-blue">Enviar cotización</q-tooltip>
          </q-btn> -->
          <q-btn class="bg-red" color="white" style="margin-left: 10px;" icon="fas fa-file-pdf" @click="generatePurchaseOrderPdf()" v-if="order.fields.status === 'PARCIAL' || order.fields.status === 'RECIBIDO' || order.fields.status === 'PEDIDO'">
            <q-tooltip content-class="bg-red" >Imprimir OC</q-tooltip>
          </q-btn>
          <q-btn style="margin-left: 8px;" v-if="order.fields.status === 'PEDIDO' && (roleId === 1 || roleId === 22 || roleId === 26)" color="purple" icon="mail" @click="confirmSendPDF()">
            <q-tooltip content-class="bg-positive">Enviar email</q-tooltip>
          </q-btn>
          <q-btn color="warning" icon="assignment" label="Cotizado" style="margin-left: 5px;" @click="changeStatus('COTIZADO')" v-if="order.fields.status == 'NUEVO'" />
          <q-btn color="orange" icon="assignment" label="Enviar OC" style="margin-left: 5px;" @click="changeStatus('PEDIDO')" v-if="order.fields.status == 'COTIZADO' && ($store.getters['users/rol'] === 26 || $store.getters['users/rol'] === 1 || $store.getters['users/rol'] === 3 || $store.getters['users/rol'] === 22)" />
          <!-- <q-btn color="purple-6" icon="assignment" label="Embarque" style="margin-left: 5px;" @click="changeStatus('EMBARCADO')" v-if="order.fields.status == 'PEDIDO'" /> -->
          <!-- <q-btn color="accent" icon="assignment" label="Llegada" style="margin-left: 5px;" @click="changeStatus('ARRIBO')" v-if="order.fields.status == 'EMBARCADO'" /> -->
          <q-btn color="light-green" icon="assignment_turned_in" label="Aprobar" style="margin-left: 5px;" @click="approvePurchaseOrder()" v-if="($store.getters['users/rol'] === 1 || $store.getters['users/rol'] === 11 || $store.getters['users/rol'] === 22) && order.fields.status == 'SOLICITADO'" />
          <q-btn color="positive" icon="done" label="Cerrar" style="margin-left: 5px;" @click="closePurchaseOrder()" v-if="($store.getters['users/rol'] === 1 || $store.getters['users/rol'] === 10 || $store.getters['users/rol'] === 19) && order.fields.status == 'APROBADO'" />
          <!-- <q-btn color="negative" icon="clear" label="Cancelar" style="margin-left: 5px;" @click="cancelPurchaseOrder()" v-if="order.fields.status != 'CERRADO' && order.fields.status != 'CANCELADO'" /> -->
          <q-btn color="positive" icon="clear" label="Restaurar" style="margin-left: 5px;" @click="openPurchaseOrder()" v-if="order.fields.status == 'CANCELADO' && $store.getters['users/rol'] === 1" />
        </div>
        <div class="q-pa-md q-gutter-sm col-xs-4 pull-right" v-else>
          <!-- <q-btn color="negative" icon="clear" label="Cancelar" style="margin-left: 5px;" @click="cancelPurchaseOrder()" v-if="order.fields.status != 'CERRADO' && order.fields.status != 'CANCELADO'" /> -->
          <q-btn color="positive" icon="clear" label="Restaurar" style="margin-left: 5px;" @click="openPurchaseOrder()" v-if="order.fields.status == 'CANCELADO' && $store.getters['users/rol'] === 1" />
        </div>
      </div>
    </div>

    <div class="q-pa-md bg-grey-3">
      <div class="row bg-white border-panel">
        <div class="col q-pa-md">
          <div class="row q-col-gutter-xs">
            <div class="col-xs-12 col-md-2">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                disable
                v-model="order.fields.serial"
                label="Folio"
              >
                <template v-slot:prepend>
                  <q-icon name="code" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-md-6">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                use-input
                hide-selected
                fill-input
                input-debounce="0"
                v-model="supplier_id"
                @filter="filterSupplier"
                :options="filteredSupplierOptions"
                label="Proveedor"
                :error="$v.order.fields.supplier.$error"
                :rules="supplierRules"
                :disable="order.fields.status == 'APROBADO' || order.fields.status == 'CERRADO' || order.fields.status == 'CANCELADO'"
                emit-value
                map-options
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-building" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-md-2">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="order.fields.requested_date"
                mask="date"
                label="Fecha de arribo"
                :rules="requestedDateRules"
                :disable="order.fields.status == 'APROBADO' || order.fields.status == 'CERRADO' || order.fields.status == 'CANCELADO'"
              >
                <template v-slot:prepend>
                  <q-icon name="event" />
                </template>
                <q-popup-proxy
                  ref="orderFieldsRequestedDateRef"
                  transition-show="scale"
                  transition-hide="scale"
                >
                  <div class="col-sm-12">
                    <q-date
                      :local="myLocale"
                      mask="DD/MM/YYYY"
                      v-model="order.fields.requested_date"
                      @input="() => $refs.orderFieldsRequestedDateRef.hide()"
                      today-btn
                    />
                  </div>
                </q-popup-proxy>
              </q-select>
            </div>
            <!-- <div class="col-xs-12 col-md-2" v-show="false">
              <q-select
                @input="getExchange()"
                color="dark"
                bg-color="secondary"
                filled
                v-model="order.fields.acc_currency_type_id"
                :options="currencyOptions"
                label="Tipo de moneda"
                emit-value
                map-options
                :disable="true"
              >
                <template v-slot:prepend>
                  <q-icon name="attach_money" />
                </template>
              </q-select>
            </div> -->
            <div class="col-xs-12 col-md-2 text-center">
              <q-input
                color="white"
                :bg-color="statusColors[order.fields.status]"
                filled
                dark
                disable
                v-model="order.fields.status"
                :error="$v.order.fields.status.$error"
                label="Estatus"
                :rules="statusRules"
              >
                <template v-slot:prepend>
                  <q-icon name="dvr" />
                </template>
              </q-input>
            </div>
            <!-- <div class="col-xs-12 col-md-2 text-center" v-if="isCotizado">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                ref="detailFieldsQtyRef"
                v-model="order.fields.proform"
                :error="$v.order.fields.proform.$error"
                label="Proforma"
                :rules="proformRules"
                type="text"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-hashtag" />
                </template>
              </q-input>
            </div> -->
            <div class="col-xs-12 col-md-2" v-if="isPedido">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="order.fields.embargo_date"
                mask="date"
                label="Fecha de embarque"
              >
                <template v-slot:prepend>
                  <q-icon name="event" />
                </template>
                <q-popup-proxy
                  ref="orderFieldsEmbargoDateRef"
                  transition-show="scale"
                  transition-hide="scale"
                >
                  <div class="col-sm-12">
                    <q-date
                      :locale="myLocale"
                      mask="DD/MM/YYYY"
                      v-model="order.fields.embargo_date"
                      @input="() => $refs.orderFieldsEmbargoDateRef.hide()"
                      today-btn
                    />
                  </div>
                </q-popup-proxy>
              </q-select>
            </div>
            <!-- <div class="col-xs-12 col-md-2 text-center" v-if="isPedido">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="order.fields.petition_number"
                :error="$v.order.fields.petition_number.$error"
                label="Pedimento"
                :rules="petitionRules"
                type="text"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-hashtag" />
                </template>
              </q-input>
            </div> -->
            <div class="col-xs-12 col-md-2 text-center" v-if="isPedido">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="order.fields.shipping_price"
                :error="$v.order.fields.shipping_price.$error"
                label="Costo de envio"
                :rules="shippingRules"
                type="text"
                @blur="updateDateInvoices()"
              >
                <template v-slot:prepend>
                  <q-icon name="attach_money" />
                </template>
              </q-input>
            </div>
            <!-- <div class="col-xs-12 col-md-2 text-center" v-if="isPedido">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="order.fields.tax_price"
                :error="$v.order.fields.tax_price.$error"
                label="Impuestos"
                :rules="taxRules"
                type="text"
              >
                <template v-slot:prepend>
                  <q-icon name="attach_money" />
                </template>
              </q-input>
            </div> -->
            <!--<div class="col-xs-12 col-md-2 text-center" v-if="isPedido">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="order.fields.mxn_price"
                :error="$v.order.fields.mxn_price.$error"
                label="Costo MXN"
                :rules="mxnRules"
                type="text"
                readonly
              >
                <template v-slot:prepend>
                  <q-icon name="attach_money" />
                </template>
              </q-input>
            </div>-->
            <!-- <div class="col-xs-12 col-md-2" v-show="false">
              <q-input
                color="white"
                :bg-color="current_currency === 0.00 ? 'red' : 'secondary'"
                filled
                dark
                v-model="current_currency"
                label="Tipo de cambio"
              >
                <template v-slot:prepend>
                  <q-icon name="attach_money" />
                </template>
              </q-input>
            </div> -->
            <div class="col-xs-12 col-md-2">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                disable
                v-model="subtotal_price"
                label="Subtotal"
              >
                <template v-slot:prepend>
                  <q-icon name="attach_money" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-md-2">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                disable
                v-model="vat_price"
                label="IVA"
              >
                <template v-slot:prepend>
                  <q-icon name="attach_money" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-md-2">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                disable
                v-model="neto_price"
                label="Neto (MXN)"
              >
                <template v-slot:prepend>
                  <q-icon name="attach_money" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-md-2" v-if="order.fields.status != 'NUEVO' && order.fields.status != 'COTIZADO'">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                @input="() => this.$v.orer.fields.order_date.$touch()"
                v-model="order.fields.order_date"
                mask="date"
                label="Fecha de Pedido"
              >
                <template v-slot:prepend>
                  <q-icon name="event" />
                </template>
                <q-popup-proxy
                  ref="orderFieldsOrderDateRef"
                  transition-show="scale"
                  transition-hide="scale"
                >
                  <div class="col-sm-12">
                    <q-date
                      :local="myLocale"
                      mask="DD/MM/YYYY"
                      v-model="order.fields.order_date"
                      @input="() => $refs.orderFieldsOrderDateRef.hide()"
                      today-btn
                    />
                  </div>
                </q-popup-proxy>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-3 text-center">
                            <q-select
                            color="dark"
                            disable
                            bg-color="secondary"
                            filled
                            :options="branchesList"
                            v-model="order.fields.branch_id"
                            @input="() => order.fields.storage=''"
                            label="Sucursal Entrada">
                            <template v-slot:prepend>
                                <q-icon name="business"></q-icon>
                            </template>
                            </q-select>
                        </div>
                        <div class="col-xs-12 col-md-2" v-if="order.fields.status === 'PEDIDO' || order.fields.status === 'RECIBIDO'">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="order.fields.date_invoicee"
                mask="date"
                label="Fecha de factura"
              >
                <template v-slot:prepend>
                  <q-icon name="event" />
                </template>
                <q-popup-proxy
                ref="mydadeInvoice"
                  transition-show="scale"
                  transition-hide="scale"
                >
                  <div class="col-sm-12">
                    <q-date
                      mask="DD/MM/YYYY"
                      v-model="order.fields.date_invoicee"
                      today-btn
                      @input="updateDateInvoices()"
                    />
                  </div>
                </q-popup-proxy>
              </q-select>
            </div>
                        <div class="col-xs-12 col-sm-3 text-center">
                            <q-select
                            disable
                            color="dark"
                            bg-color="secondary"
                            filled
                            v-model="order.fields.storage"
                            :options="filteredStorageOptions"
                            label="Almacén Entrada">
                            <template v-slot:prepend>
                                <q-icon name="store"></q-icon>
                            </template>
                            </q-select>
                        </div>
                        <div class="col-xs-12 col-md-2">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="order.fields.reference"
                label="# Referencia"
                @blur="updateDateInvoices()"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-file-invoice-dollar" />
                </template>
              </q-input>
            </div>
          </div>
          <div class="row">
            <div class="col-xs-6 pull-left">
              <q-btn color="positive" icon="fas fa-file-pdf" label="Imprimir OC" style="margin: 5px;" @click="generatePurchaseOrderPdf()" v-if="(order.fields.status == 'APROBADO') && ($store.getters['users/rol'] === 1 || $store.getters['users/rol'] === 2 || $store.getters['users/rol'] === 5 || $store.getters['users/rol'] === 10 || $store.getters['users/rol'] === 19)" />
              <q-btn :loading="loadingSendingMailBtn" color="primary" icon="mail" label="Enviar a proveedor" style="margin: 5px;" @click="sendPurchaseOrderPdfToSupplier()" v-if="(order.fields.status == 'APROBADO') && ($store.getters['users/rol'] === 1 || $store.getters['users/rol'] === 2 || $store.getters['users/rol'] === 5 || $store.getters['users/rol'] === 10 || $store.getters['users/rol'] === 19)">
                <template v-slot:loading>
                  <q-spinner class="on-left" />
                  Enviando correo...
                </template>
              </q-btn>
            </div>
            <!--<div class="col-xs-6 pull-right">
              <q-btn color="positive" icon="save" label="Actualizar" style="margin-left: 5px;" @click="updateOrder()" v-if="order.fields.status != 'RECIBIDO' || order.fields.status != 'APROBADO' ||  order.fields.status != 'CERRADO' || order.fields.status != 'CANCELADO'" />
            </div>-->
            <div class="col-xs-6 pull-right">
              <q-btn color="positive" icon="save" label="Actualizar" style="margin-left: 5px;" @click="updateOrder()" v-if="order.fields.status == 'NUEVO' || order.fields.status == 'PEDIDO' ||  order.fields.status == 'COTIZADO'" />
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
          <q-tab name="details" icon="fas fa-cubes" label="Detalles" />
          <q-tab name="shipments" icon="fas fa-cart-plus" label="Recepciones" v-if="order.fields.status === 'PEDIDO' || order.fields.status === 'RECIBIDO' || order.fields.status === 'PARCIAL'"  />
          <q-tab name="documents" icon="fas fa-file" label="Documentos" v-if="order.fields.status != 'NUEVO' && order.fields.status != 'SOLICITADO'" />
          <q-tab name="history" icon="fas fa-edit" label="Historial" />
        </q-tabs>
        <q-separator />
        <q-tab-panels v-model="currentTab" animated>
          <q-tab-panel name="details">
            <div style="font-weight: normal;" v-if="!$store.getters['users/rol'] === 1 & !$store.getters['users/rol'] === 5 && !$store.getters['users/rol'] === 21 && !$store.getters['users/rol'] === 22 && !$store.getters['users/rol'] === 2 && !$store.getters['users/rol'] === 26 && !$store.getters['users/rol'] === 28">
              <div class="q-col-gutter-xs" style="padding: 2%;">
                <q-table
                  flat
                  bordered
                  :data="details"
                  :columns="detailsColumnsStore"
                  row-key="name"
                  :pagination.sync="pagination"
                >
                  <template v-slot:body="props">
                    <q-tr :props="props">
                      <q-td key="product" style="text-align: left; width: 50%;" :props="props">{{ props.row.product }}</q-td>
                      <q-td key="qty" style="text-align: left; width: 15%;" :props="props">{{ formatPrice(props.row.qty) }} KG.</q-td>
                      <q-td key="color" style="text-align: left; width: 20%;" :props="props">{{ props.row.color }}</q-td>
                      <q-td key="quality" style="width: 15%;" :props="props">{{ props.row.quality }}</q-td>
                    </q-tr>
                  </template>
                </q-table>
              </div>
            </div>
            <div style="font-weight: normal;" v-else>
              <div class="row q-col-gutter-xs" style="padding-right: 2%;" v-if="order.fields.status != 'APROBADO' && order.fields.status != 'CERRADO' && order.fields.status != 'CANCELADO'">
                <div class="col-sm-12 col-md-4 offset-md-8 pull-right">
                  <q-btn color="positive" icon="save" label="Actualizar" style="margin-left: 5px;" @click="updateDetail()" v-show="detailIdToEdit != null" />
                  <q-btn color="negative" icon="cancel" label="Cancelar" style="margin-left: 5px;" @click="cancelEditDetail()" v-show="detailIdToEdit != null" />
                  <q-btn color="positive" icon="add" label="Agregar" style="margin-left: 5px;" @click="addDetail()" v-show="detailIdToEdit == null && (order.fields.status === 'NUEVO' || order.fields.status === 'COTIZADO' || order.fields.status === 'PEDIDO' || order.fields.status === 'PARCIAL')" />
                </div>
              </div>
              <div class="row q-col-gutter-xs" style="padding: 2%;" v-if="order.fields.status != 'APROBADO' && order.fields.status != 'CERRADO' && order.fields.status != 'CANCELADO'">
                <div class="col-sm-12 col-md-6">
                  <q-select
                    color="dark"
                    bg-color="secondary"
                    filled
                    use-input
                    hide-selected
                    fill-input
                    input-debounce="0"
                    ref="detailFieldsProductRef"
                    @filter="filterProduct"
                    v-model="detail.fields.product"
                    :options="filteredProductsOptions"
                    label="Producto"
                    :rules="productRules"
                    @input="getLastPrice()"
                  >
                    <template v-slot:prepend>
                      <q-icon name="emoji_objects" />
                    </template>
                  </q-select>
                </div>
                <div class="col-sm-12 col-md-2">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    ref="detailFieldsQtyRef"
                    v-model="lastPriceProduct"
                    label="Ultimo precio"
                    readonly
                  >
                    <template v-slot:prepend>
                      <q-icon name="attach_money" />
                    </template>
                  </q-input>
                </div>
                <div class="col-sm-12 col-md-2">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    ref="detailFieldsQtyRef"
                    v-model="detail.fields.qty"
                    :error="$v.detail.fields.qty.$error"
                    label="Cantidad"
                    :rules="qtyRules"
                    @input="v => { detail.fields.qty = v.replace(/[^0-9\\.]/g, '') }"
                    min="1"
                  >
                    <template v-slot:prepend>
                      <q-icon name="fas fa-hashtag" />
                    </template>
                  </q-input>
                </div>
              <div class="col-sm-12 col-md-2">
                <q-select
                label="IVA"
                color="dark" bg-color="secondary"
                filled
                v-model="vat_rate"
                :options="[
                { label: '0%', value: 0 },
                { label: '8%', value: 8 },
                { label: '16%', value: 16 }
                ]"
                map-options
                emit-value
                >
                <template v-slot:prepend>
                  <q-icon name="fas fa-percentage" />
                </template>
                </q-select>
              </div>
                <div class="col-sm-12 col-md-2">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    ref="detailFieldsQtyRef"
                    v-model="detail.fields.price"
                    :error="$v.detail.fields.price.$error"
                    label="Precio unitario"
                    :rules="priceRules"
                    type="text"
                  >
                    <template v-slot:prepend>
                      <q-icon name="attach_money" />
                    </template>
                  </q-input>
                </div>
                <div class="col-xs-12 col-md-6 text-center">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="detail.fields.observation"
                    label="Observaciones"
                    type="text"
                  >
                    <template v-slot:prepend>
                      <q-icon name="fas fa-eye" />
                    </template>
                  </q-input>
                </div>
              </div>
              <div class="q-col-gutter-xs" style="padding: 2%;">
                <q-table
                  flat
                  bordered
                  :data="details"
                  :columns="detailsColumns"
                  row-key="name"
                  :pagination.sync="pagination"
                >
                  <template v-slot:body="props">
                    <q-tr :props="props">
                      <q-td key="product" style="text-align: left; width: 30%;" :props="props">{{ props.row.product }}</q-td>
                      <q-td key="qty" style="text-align: right; width: 5%;" :props="props">{{ $formatNumberThree(props.row.qty) }}</q-td>
                      <q-td key="price" style="text-align:right; width: 5%;" :props="props">{{ `${currencyFormatter.format(props.row.price)}` }}</q-td>
                      <q-td key="subtotal" style="text-align:right; width: 5%;" :props="props">{{ `${currencyFormatter.format(props.row.price * props.row.qty)}` }}</q-td>
                      <q-td key="vat" style="text-align:right; width: 5%;" :props="props">{{ `${currencyFormatter.format(props.row.vat)}` }}</q-td>
                      <q-td key="totalPrice" style="text-align: right; width: 5%;" :props="props">{{ `${currencyFormatter.format(Number(props.row.price) * Number(props.row.qty) + Number.parseFloat(props.row.vat))}` }}</q-td>
                      <q-td key="entrada" style="text-align: right; width: 5%;" :props="props">{{ $formatNumberThree(props.row.entrada) }}</q-td>
                      <q-td key="restante" style="text-align: right; width: 5%;" :props="props">{{ $formatNumberThree(props.row.restante) }}</q-td>
                      <q-td key="observation" style="text-align: left; width: 35%;" :props="props">{{ props.row.observation }}</q-td>
                      <q-td key="actions" style="width: 10%;" :props="props" v-if="order.fields.status != 'APROBADO' && order.fields.status != 'CERRADO' && order.fields.status != 'CANCELADO'">
                        <q-btn color="primary" icon="fas fa-edit" flat @click.native="editDetail(props.row)" size="10px">
                          <q-tooltip content-class="bg-primary">Editar</q-tooltip>
                        </q-btn>
                        <q-btn color="negative" icon="fas fa-trash-alt" flat @click.native="removeDetail(props.row)" size="10px" v-if="!isPedido">
                          <q-tooltip content-class="bg-red">Eliminar</q-tooltip>
                        </q-btn>
                      </q-td>
                      <q-td key="actions" style="width: 10%;" :props="props" v-else>
                        -
                      </q-td>
                    </q-tr>
                  </template>
                </q-table>
              </div>
            </div>
          </q-tab-panel>
          <q-tab-panel name="shipments" v-if="order.fields.status != 'NUEVO' && order.fields.status != 'SOLICITADO'">
            <div style="font-weight: normal;">
              <div class="row q-col-gutter-xs" style="padding-right: 2%; padding-bottom: 2%;" v-if="roleId === 1 || roleId === 22 || roleId === 3 || roleId === 4 || roleId === 28 && order.fields.status != 'CERRADO' && order.fields.status != 'CANCELADO' && canAddNewShipment && order.fields.status != 'RECIBIDO'">
                <div class="col-xs-12 col-sm-12 col-md-2 offset-md-10 pull-right">
                  <q-btn color="blue" icon="add" label="NUEVO" @click="newShipment()" />
                </div>
              </div>
              <div class="q-col-gutter-xs" style="padding-right: 2%; padding-left: 2%; padding-bottom: 2%;">
                <q-table
                  flat
                  bordered
                  :data="shipments"
                  :columns="shipmentColumns"
                  row-key="name"
                  :pagination.sync="pagination"
                >
                  <template v-slot:body="props">
                    <q-tr :props="props">
                      <q-td key="serial" style="text-align: center; width: 10%;" :props="props">{{ props.row.serial }}</q-td>
                      <q-td key="receive_date" style="width: 30%;" :props="props">{{ props.row.receive_date }}</q-td>
                      <q-td key="status" style="width: 30%;" :props="props">
                        <q-chip square dense :color="props.row.status == 'NUEVO' ? 'secondary' : (props.row.status == 'MUESTREADO' ? 'warning' : (props.row.status == 'RECIBIDO' ? 'orange' : (props.row.status == 'ANALIZADO' ? 'positive' : (props.row.status == 'RECHAZADO' ? 'negative' : 'blue'))))" text-color="white">
                          {{ props.row.status }}
                        </q-chip>
                      </q-td>
                      <q-td key="actions" style="width: 10%;" :props="props" v-if="order.fields.status != 'CERRADO' && order.fields.status != 'CANCELADO' && props.row.status != 'RECIBIDO' && props.row.status != 'RECHAZADO' && $store.getters['users/rol'] === 1 || $store.getters['users/rol'] === 3 || $store.getters['users/rol'] === 22">
                        <q-btn :color="props.row.invoice ? 'positive' : 'negative'" icon="cloud_upload" flat @click.native="openUploadShipmentInvoiceModal(props.row)" size="10px">
                          <q-tooltip :content-class="props.row.invoice ? 'bg-positive' : 'bg-negative'">Subir archivo</q-tooltip>
                        </q-btn>
                        <q-btn color="primary" icon="remove_red_eye" flat @click.native="showShipmentInvoiceFile(props.row)" size="10px" v-if="props.row.invoice">
                          <q-tooltip content-class="bg-primary">Ver factura</q-tooltip>
                        </q-btn>
                        <q-btn color="primary" icon="cloud_download" flat @click.native="downloadShipmentInvoiceFile(props.row)" size="10px" v-if="props.row.invoice">
                          <q-tooltip content-class="bg-primary">Descargar factura</q-tooltip>
                        </q-btn>
                        <q-btn color="positive" icon="far fa-arrow-alt-circle-right" flat @click.native="saveStorageEntry(props.row.id)" size="10px" v-if="props.row.canExecute" />
                        <q-btn color="primary" icon="fas fa-edit" flat @click.native="editShipment(props.row)" size="10px">
                          <q-tooltip content-class="bg-primary">Editar</q-tooltip>
                        </q-btn>
                        <q-btn color="primary" icon="fas fa-trash-alt" flat @click.native="removeShipment(props.row)" size="10px">
                          <q-tooltip content-class="bg-red">Eliminar</q-tooltip>
                        </q-btn>
                      </q-td>
                      <q-td key="actions" style="width: 10%;" :props="props" v-else>
                        <q-btn :color="props.row.invoice ? 'positive' : 'negative'" icon="cloud_upload" flat @click.native="openUploadShipmentInvoiceModal(props.row)" size="10px">
                          <q-tooltip :content-class="props.row.invoice ? 'bg-positive' : 'bg-negative'">Subir factura</q-tooltip>
                        </q-btn>
                        <q-btn color="primary" icon="remove_red_eye" flat @click.native="showShipmentInvoiceFile(props.row)" size="10px" v-if="props.row.invoice">
                          <q-tooltip content-class="bg-primary">Ver factura</q-tooltip>
                        </q-btn>
                        <q-btn color="primary" icon="cloud_download" flat @click.native="downloadShipmentInvoiceFile(props.row)" size="10px" v-if="props.row.invoice">
                          <q-tooltip content-class="bg-primary">Descargar factura</q-tooltip>
                        </q-btn>
                        <q-btn color="primary" icon="mail" flat @click.native="sendShipmentPdfToSupplier(props.row.id)" size="10px" v-if="props.row.status == 'RECIBIDO' && order.fields.status == 'APROBADO'">
                          <q-tooltip content-class="bg-primary">Enviar ticket de entrada</q-tooltip>
                        </q-btn>
                        <q-btn color="primary" icon="fas fa-file-pdf" flat @click.native="generateShipmentPdf(props.row.id)" size="10px" v-if="props.row.status == 'RECIBIDO'">
                          <q-tooltip content-class="bg-primary">Ver ticket de entrada</q-tooltip>
                        </q-btn>
                        <q-btn v-if="$store.getters['users/rol'] === 1 || $store.getters['users/rol'] === 3" color="primary" icon="remove_red_eye" flat @click.native="editShipment(props.row)" size="10px">
                          <q-tooltip content-class="bg-primary">Ver detalles</q-tooltip>
                        </q-btn>
                      </q-td>
                    </q-tr>
                  </template>
                </q-table>
              </div>
            </div>
          </q-tab-panel>
          <q-tab-panel name="documents" v-if="order.fields.status != 'NUEVO' && order.fields.status != 'SOLICITADO'">
            <div style="font-weight: normal;">
              <div class="row q-col-gutter-xs" style="padding-right: 2%;">
                <div class="col-xs-12 col-md-4 offset-md-8 pull-right" v-if="document.fields.id">
                  <q-btn color="positive" icon="save" label="Actualizar" @click="updateDocument()" style="margin-right: 5px;" />
                  <q-btn color="negative" icon="cancel" label="Cancelar" @click="cancelDocument()" style="margin-right: 5px;" />
                </div>
                <div class="col-xs-12 col-md-2 offset-md-10 pull-right" v-else>
                  <q-btn color="positive" icon="add" label="Agregar" @click="addDocument()" />
                </div>
              </div>
              <div class="row q-col-gutter-xs" style="padding: 2%;">
                <div class="col-xs-12 col-md-4">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="document.fields.name"
                    :error="$v.document.fields.name.$error"
                    label="Nombre"
                    :rules="documentNameRules"
                    @input="v => { document.fields.name = v.toUpperCase() }"
                  >
                    <template v-slot:prepend>
                      <q-icon name="fas fa-signature" />
                    </template>
                  </q-input>
                </div>
                <div class="col-xs-12 col-md-8">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="document.fields.observations"
                    :error="$v.document.fields.observations.$error"
                    label="Observaciones"
                    :rules="documentObservationsRules"
                    @input="v => { document.fields.observations = v.toUpperCase() }"
                  >
                    <template v-slot:prepend>
                      <q-icon name="remove_red_eye" />
                    </template>
                  </q-input>
                </div>
              </div>

              <div class="q-col-gutter-xs" style="padding: 2%;">
                <q-table
                  flat
                  bordered
                  :data="documents"
                  :columns="documentColumns"
                  row-key="name"
                  :pagination.sync="pagination"
                >
                  <template v-slot:body="props">
                    <q-tr :props="props">
                      <q-td key="name" style="text-align: left; width: 30%;" :props="props">{{ props.row.name }}</q-td>
                      <q-td key="observations" style="text-align: left; width: 30%;" :props="props">{{ props.row.observations }}</q-td>
                      <q-td key="document_name" style="text-align: left; width: 30%;" :props="props">{{ props.row.document_name }}</q-td>
                      <q-td key="actions" style="width: 10%;" :props="props">
                        <q-btn :color="props.row.document_name ? 'positive' : 'negative'" icon="cloud_upload" flat @click.native="openUploadFileModal(props.row)" size="10px">
                          <q-tooltip :content-class="props.row.document_name ? 'bg-positive' : 'bg-negative'">Subir archivo</q-tooltip>
                        </q-btn>
                        <q-btn color="primary" icon="remove_red_eye" flat @click.native="showDocumentFile(props.row)" size="10px" v-if="props.row.document_name">
                          <q-tooltip content-class="bg-primary">Ver archivo</q-tooltip>
                        </q-btn>
                        <q-btn color="positive" icon="cloud_download" flat @click.native="downloadDocumentFile(props.row)" size="10px" v-if="props.row.document_name">
                          <q-tooltip content-class="bg-positive">Descargar archivo</q-tooltip>
                        </q-btn>
                        <q-btn color="primary" icon="fas fa-edit" flat @click.native="editDocument(props.row)" size="10px">
                          <q-tooltip content-class="bg-primary">Editar documento</q-tooltip>
                        </q-btn>
                        <q-btn color="negative" icon="fas fa-trash-alt" flat @click.native="removeDocument(props.row.id)" size="10px">
                          <q-tooltip content-class="bg-negative">Eliminar</q-tooltip>
                        </q-btn>
                      </q-td>
                    </q-tr>
                  </template>
                </q-table>
              </div>
            </div>
          </q-tab-panel>
          <q-tab-panel name="history">
            <div style="font-weight: normal;">
              <div class="row q-col-gutter-xs" style="padding: 2%;">
                <div class="col-xs-12 col-md-12">
                  <q-table
                    flat
                    bordered
                    :data="history"
                    :columns="historyColumns"
                    row-key="name"
                    :pagination.sync="pagination"
                  >
                    <template v-slot:body="props">
                      <q-tr :props="props">
                        <q-td key="created" style="text-align: center; width: 30%;" :props="props">{{ props.row.created }}</q-td>
                        <q-td key="status" style="text-align: center; width: 30%;" :props="props">
                          <q-chip square dense :color="props.row.status == 'NUEVO' ? 'blue' : (props.row.status == 'COTIZADO' ? 'warning' : (props.row.status == 'PEDIDO' ? 'orange' : (props.row.status == 'RECEPCION' ? 'light-green' : (props.row.status == 'RECIBIDO' ? 'green' : (props.row.status == 'PARCIAL' ? 'red-4' : 'red-6')))))" text-color="white">
                              {{ props.row.status }}
                            </q-chip>
                        </q-td>
                      </q-tr>
                    </template>
                  </q-table>
                </div>
              </div>
            </div>
          </q-tab-panel>
        </q-tab-panels>
      </div>
    </div>

    <q-dialog v-model="shipmentInvoiceModal" persistent>
      <q-card>
        <q-card-section class="row">
          <div class="col-xs-12 col-sm-10 text-h6">Factura: Recepción {{ shipmentInvoice.fields.shipmentSerial }}</div>
          <q-btn class="col-xs-12 col-sm-2 pull-right" icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-card-section>
          <q-uploader
            :url="fileShipmentInvoiceUrl"
            method="POST"
            ref="fileShipmentInvoiceRef"
            hide-upload-btn
            @uploaded="afterUploadShipmentInvoiceFile"
          />
        </q-card-section>
        <q-card-actions align="right" class="text-primary">
          <q-btn flat label="Subir archivo" @click="uploadShipmentInvoiceFile()" />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <q-dialog v-model="documentFileModal" persistent>
      <q-card>
        <q-card-section class="row bg-teal-7 text-white">
          <div class="col-xs-12 col-sm-10 text-h6">Archivo: {{ documentName }}</div>
          <q-btn class="col-xs-12 col-sm-2 pull-right" icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-card-section>
          <q-uploader
            :url="fileDocumentUrl"
            method="POST"
            ref="fileDocumentRef"
            hide-upload-btn
            :headers="[{name: 'Authorization', value: 'Bearer ' + this.JWT}]"
            @uploaded="afterUploadDocumentFile"
          />
        </q-card-section>
        <q-card-actions align="right" class="text-primary">
          <q-btn color="positive" label="Subir archivo" @click="uploadDocumentFile()" />
        </q-card-actions>
      </q-card>
    </q-dialog>
    <q-dialog v-model="confirmModalPDF" persistent>
      <q-card style="width:50%;">
        <q-card-section class="bg-primary text-white">
          <div class="row">
            <div class="col-sm-11 text-h6">CONFIRMACIÓN DE ENVIO</div>
            <div class="col-sm-1 pull-right"><q-btn color="white" flat v-close-popup round dense icon="close" /></div>
          </div>
        </q-card-section>
        <br>
        <q-card-section class="row items-center">
          <label style="font-size: 23px;">¿Desea enviar el PDF del pedido al Proveedor?</label>
        </q-card-section>
        <br>
        <q-card-actions align="right">
          <q-btn label="Cancelar" color="negative" v-close-popup />
          <q-btn label="Aceptar" color="positive" @click="sendPDF()"/>
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import api from '../../commons/api.js'
const { required, requiredIf, decimal, maxLength } = require('vuelidate/lib/validators')

export default {
  name: 'EditPurchaseOrder',
  validations: {
    order: {
      fields: {
        status: { required, maxLength: maxLength(10) },
        supplier: { required },
        producer: { maxLength: maxLength(100) },
        requested_date: { required },
        // proform: { required: requiredIf(function () { return this.isCotizado }), integer },
        // petition_number: { required: requiredIf(function () { return this.isPedido }), integer },
        shipping_price: { required: requiredIf(function () { return this.isPedido }), decimal },
        // tax_price: { required: requiredIf(function () { return this.isPedido }), decimal },
        mxn_price: { decimal },
        storage: { },
        branche_id: { },
        reference: { }
      }
    },
    detail: {
      fields: {
        qty: { required, decimal },
        product: { required },
        price: { required, decimal }
      }
    },
    document: {
      fields: {
        name: { required, maxLength: maxLength(100) },
        observations: { required, maxLength: maxLength(100) }
      }
    },
    shipmentToEntry: { required }
  },
  data () {
    return {
      confirmModalPDF: false,
      vat_rate: 16,
      pageOC: 1,
      ready: false,
      history: null,
      statusColors: { NUEVO: 'blue', COTIZADO: 'warning', PEDIDO: 'orange', RECEPCION: 'light-green', RECIBIDO: 'green', CANCELADO: 'red-6', PARCIAL: 'red-4' },
      formatter: new Intl.NumberFormat('en-US'),
      currencyFormatter: new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
      }),
      myLocale: {
        /* starting with Sunday */
        days: 'Domingo_Lunes_Martes_Miércoles_Jueves_Viernes_Sábado'.split('_'),
        daysShort: 'Dom_Lun_Mar_Mié_Jue_Vie_Sáb'.split('_'),
        months: 'Enero_Febrero_Marzo_Abril_Mayo_Junio_Julio_Agosto_Septiembre_Octubre_Noviembre_Diciembre'.split('_'),
        monthsShort: 'Ene_Feb_Mar_Abr_May_Jun_Jul_Ago_Sep_Oct_Nov_Dic'.split('_'),
        firstDayOfWeek: 1
      },
      current_currency: 0.00,
      subtotal: 0.00,
      vat_total: 0.00,
      mxn_total: 0.00,
      total: 0.00,
      supplier_id: null,
      order: {
        fields: {
          id: null,
          serial: null,
          status: 'NUEVO',
          supplier: null,
          producer: null,
          requested_date: null,
          order_date: null,
          proform: null,
          embargo_date: null,
          petition_number: null,
          shipping_price: null,
          tax_price: null,
          mxn_price: null,
          acc_currency_type_id: 1,
          storage: null,
          branch_id: null,
          reference: null,
          date_invoicee: null
        }
      },
      detail: {
        fields: {
          id: null,
          qty: null,
          product: { value: null, label: 'Seleccione el producto' },
          price: null,
          quality: { value: null, label: 'Calidad' },
          color: { value: null, label: 'Seleccione el color' },
          observation: null
        }
      },
      shipmentInvoice: {
        fields: {
          shipmentId: null,
          shipmentSerial: null
        }
      },
      document: {
        fields: {
          id: null,
          name: null,
          observations: null
        }
      },
      supplierOptions: [],
      productOptions: [],
      currencyOptions: [],
      branchesList: [],
      storageOptions: [],
      details: [],
      shipments: [],
      documents: [],
      detailsColumns: [
        { name: 'product', align: 'center', label: 'PRODUCTO', field: 'product', style: 'width: 30%', sortable: true },
        { name: 'qty', align: 'center', label: 'CANT', field: 'qty', style: 'width: 5%', sortable: true },
        { name: 'price', align: 'center', label: 'UNITARIO', field: 'price', style: 'width: 5%', sortable: true },
        { name: 'subtotal', align: 'center', label: 'IMPORTE', field: 'subtotal', style: 'width: 5%', sortable: true },
        { name: 'vat', align: 'center', label: 'IVA', field: 'vat', style: 'width: 5%', sortable: true },
        { name: 'totalPrice', align: 'center', label: 'NETO', field: 'totalPrice', style: 'width: 5%', sortable: true },
        { name: 'entrada', align: 'center', label: 'ENTRADA', field: 'entrada', style: 'width: 5%', sortable: true },
        { name: 'restante', align: 'center', label: 'RESTANTES', field: 'restante', style: 'width: 5%', sortable: true },
        { name: 'observation', align: 'center', label: 'OBSERVACIONES', field: 'observation', style: 'width: 35%', sortable: true },
        { name: 'actions', align: 'center', label: 'ACCIONES', field: 'actions', style: 'width: 10%', sortable: false }
      ],
      detailsColumnsStore: [
        { name: 'product', align: 'center', label: 'PRODUCTO', field: 'product', style: 'width: 50%', sortable: true },
        { name: 'qty', align: 'center', label: 'KILOGRAMOS', field: 'qty', style: 'width: 15%', sortable: true },
        { name: 'color', align: 'center', label: 'COLOR', field: 'color', style: 'width: 20%', sortable: true },
        { name: 'quality', align: 'center', label: 'CALIDAD', field: 'quality', style: 'width: 15%', sortable: true }
      ],
      shipmentColumns: [
        { name: 'serial', align: 'center', label: 'FOLIO', field: 'serial', style: 'width: 10%', sortable: true },
        { name: 'receive_date', align: 'center', label: 'FECHA DE RECEPCION', field: 'receive_date', style: 'width: 30%', sortable: true },
        { name: 'status', align: 'center', label: 'ESTATUS', field: 'status', style: 'width: 30%', sortable: true },
        { name: 'actions', align: 'center', label: 'ACCIONES', field: 'actions', style: 'width: 10%', sortable: false }
      ],
      documentColumns: [
        { name: 'name', align: 'center', label: 'NOMBRE', field: 'name', style: 'width: 30%', sortable: true },
        { name: 'observations', align: 'center', label: 'OBSERVACIONES', field: 'observations', style: 'width: 30%', sortable: true },
        { name: 'document_name', align: 'center', label: 'NOMBRE DE ARCHIVO', field: 'document_name', style: 'width: 30%', sortable: true },
        { name: 'actions', align: 'center', label: 'ACCIONES', field: 'actions', style: 'width: 10%', sortable: false }
      ],
      historyColumns: [
        { name: 'created', align: 'center', label: 'FECHA Y HORA', field: 'created', style: 'width: 30%', sortable: true },
        { name: 'status', align: 'center', label: 'ESTATUS', field: 'status', style: 'width: 30%', sortable: true }
      ],
      pagination: {
        rowsPerPage: 50
      },
      detailIdToEdit: null,
      shipmentToEntry: null,
      qualityOptions: [{ value: 'A', label: 'A' }, { value: 'B', label: 'B' }, { value: 'C', label: 'C' }],
      colorOptions: [{ value: 'BLANCO', label: 'BLANCO' }, { value: 'MULTICOLOR', label: 'MULTICOLOR' }, { value: 'VERDE', label: 'VERDE' }],
      loadingSendingMailBtn: false,
      documentFileModal: false,
      shipmentInvoiceModal: false,
      documentName: null,
      documentId: null,
      serverUrl: process.env.API,
      currentTab: 'details',
      filteredSupplierOptions: [],
      filteredProductsOptions: [],
      lastPriceProduct: null
    }
  },
  computed: {
    roleId () {
      const user = this.$store.getters['users/rol']
      console.log('super roles')
      console.log(user)
      return parseInt(user)
    },
    JWT () {
      return localStorage.getItem('JWT')
    },
    isCotizado () {
      return this.order.fields.status !== 'NUEVO'
    },
    isPedido () {
      return this.isCotizado && this.order.fields.status !== 'COTIZADO'
    },
    statusRules (val) {
      return [
        val => (this.$v.order.fields.status.required) || 'El campo Estatus es requerido.',
        val => (this.$v.order.fields.status.maxLength) || 'El campo Estatus no debe exceder los 10 dígitos.'
      ]
    },
    supplierRules (val) {
      return [
        val => this.$v.order.fields.supplier.required || 'El campo Proveedor es requerido.'
      ]
    },
    producerRules (val) {
      return [
        val => (this.$v.order.fields.producer.maxLength) || 'El campo Fabricante no debe exceder los 100 dígitos.'
      ]
    },
    requestedDateRules (val) {
      return [
        val => (this.$v.order.fields.requested_date.required) || 'El campo Fecha de arribo es requerido.'
      ]
    },
    proformRules (val) {
      return [
        val => (this.$v.order.fields.proform.required) || 'El campo Proforma es requerido.',
        val => (this.$v.order.fields.proform.integer) || 'El campo Proforma debe ser numérico.'
      ]
    },
    petitionRules (val) {
      return [
        val => (this.$v.order.fields.petition_number.required) || 'El campo Pedimento es requerido.',
        val => (this.$v.order.fields.petition_number.integer) || 'El campo Pedimento debe ser numérico.'
      ]
    },
    shippingRules (val) {
      return [
        val => (this.$v.order.fields.shipping_price.required) || 'El campo Costo de envio es requerido.',
        val => (this.$v.order.fields.shipping_price.decimal) || 'El campo Costo de envio debe ser numérico.'
      ]
    },
    taxRules (val) {
      return [
        val => (this.$v.order.fields.tax_price.required) || 'El campo Impuestos es requerido.',
        val => (this.$v.order.fields.tax_price.decimal) || 'El campo Impuestos debe ser numérico.'
      ]
    },
    mxnRules (val) {
      return [
        val => (this.$v.order.fields.mxn_price.required) || 'El campo Costo MXN es requerido.',
        val => (this.$v.order.fields.mxn_price.decimal) || 'El campo Costo MXN debe ser numérico.'
      ]
    },
    productRules (val) {
      return [
        val => this.$v.detail.fields.product.required || 'El campo Producto es requerido.'
      ]
    },
    qtyRules (val) {
      return [
        val => (this.$v.detail.fields.qty.required) || 'El campo Cantidad es requerido.',
        val => (this.$v.detail.fields.qty.decimal) || 'El campo Cantidad debe ser numérico.'
      ]
    },
    priceRules (val) {
      return [
        val => (this.$v.detail.fields.price.required) || 'El campo Precio unitario es requerido.',
        val => (this.$v.detail.fields.price.decimal) || 'El campo Precio unitario debe ser numérico.'
      ]
    },
    qualityRules (val) {
      return [
        val => this.$v.detail.fields.quality.required || 'El campo Calidad es requerido.'
      ]
    },
    colorRules (val) {
      return [
        val => this.$v.detail.fields.color.required || 'El campo Color es requerido.'
      ]
    },
    documentNameRules (val) {
      return [
        val => this.$v.document.fields.name.required || 'El campo Nombre es requerido.',
        val => this.$v.document.fields.name.maxLength || 'El campo Nombre no debe exceder los 100 dígitos.'
      ]
    },
    documentObservationsRules (val) {
      return [
        val => this.$v.document.fields.observations.required || 'El campo Observaciones es requerido.',
        val => this.$v.document.fields.observations.maxLength || 'El campo Observaciones no debe exceder los 100 dígitos.'
      ]
    },
    filteredStorageOptions () {
      if (this.order.fields.branch != null && this.order.fields.branch.value != null) {
        return this.storageList.filter(storage => storage.branchOffice === this.order.fields.branch.value)
      }
      return []
    },
    // Computeds para transferencias
    filteredOriginStorageOptions () {
      if (this.order.fields.originBranchOffice != null && !isNaN(this.order.fields.originBranchOffice)) {
        return this.storageOptions.filter(op => Number(op.branchOffice) === Number(this.order.fields.originBranchOffice))
      }
      return []
    },
    filteredProductOptions () {
      const options = []
      const products = []
      this.details.forEach(d => {
        products.push(d.product_id)
      })
      this.productOptions.forEach(p => {
        if (!products.includes(p.value)) {
          options.push(p)
        }
      })
      return options
    },
    canAddNewShipment () {
      console.log('mis shippements')
      console.log(this.shipments)
      const shipmentsUnreicived = this.shipments.filter(s => s.status === 'NUEVO' || s.status === 'ANALIZADO')
      if (shipmentsUnreicived.length > 0) {
        return false
      }
      return true
    },
    fileDocumentUrl () {
      return `${process.env.API}purchase-order-documents/file/${this.documentId}`
    },
    fileShipmentInvoiceUrl () {
      return `${process.env.API}shipments/invoice-file/${this.shipmentInvoice.fields.shipmentId}`
    },
    subtotal_price () {
      return this.currencyFormatter.format(this.subtotal)
    },
    vat_price () {
      return this.currencyFormatter.format(this.vat_total)
    },
    neto_price () {
      return this.currencyFormatter.format(this.mxn_total)
    }
  },
  /* beforeCreate () {
    // Rol 21 es Dirección
    console.log(this.$store.getters['users/roles'])
    if (this.$store.getters['users/roles'].includes(2)) {
      this.$router.push('/orders')
    } else if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(22) && !this.$store.getters['users/roles'].includes(21) && !this.$store.getters['users/roles'].includes(26)) {
      this.$router.push('/')
    }
  }, */
  beforeRouteEnter (to, from, next) {
    next(vm => {
      const propiedades = vm.$store.getters['users/rol']
      console.log(propiedades)
      if (propiedades === 1 || propiedades === 3 || propiedades === 7 || propiedades === 2 || propiedades === 20 || propiedades === 4 || propiedades === 27 || propiedades === 20 || propiedades === 22 | propiedades === 28) {
        next()
      } else {
        next('/')
      }
    })
  },
  created () {
    if (!this.$store.getters['users/roles'].includes(2)) {
      this.fetchFromServer()
      this.getBranchesList()
      this.getStoragesList()
    }
  },
  watch: {
    order: function (val) {
      console.log(val)
    }
  },
  methods: {
    async updateDateInvoices () {
      const params = {}
      params.date_invoicee = this.order.fields.date_invoicee !== null ? this.$formatDate(this.order.fields.date_invoicee) : null
      params.shipping_price = this.order.fields.shipping_price
      params.reference = this.order.fields.reference
      this.$q.loading.show()
      await api.put(`/purchase-orders/updateDateInvoices/${this.$route.params.id}`, params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.fetchFromServer()
        }
      })
      this.$q.loading.hide()
    },
    confirmSendPDFQuote () {
      const uri = process.env.API + `purchase-order-details/getPdfquotationNote/${this.$route.params.id}`
      window.open(uri, '_blank')
    },
    getLastPrice () {
      const idsupplier = this.supplier_id.value
      const idProduct = this.detail.fields.product.value
      api.get(`/purchase-order-details/getLastPrice/${idsupplier}/${idProduct}`).then(({ data }) => {
        if (data.result) {
          this.lastPriceProduct = data.lastprice
        }
      })
    },
    sendPDF () {
      this.$q.loading.show()
      const id = Number(this.$route.params.id)
      api.post(`/purchase-orders/sendEmail/${id}`).then(({ data }) => {
        if (data.result) {
          this.$q.loading.hide()
          this.$q.notify({
            message: data.message.content,
            position: 'top',
            color: (data.result ? 'positive' : 'warning')
          })
          this.confirmModalPDF = false
        } else {
          this.$q.loading.hide()
          this.$q.notify({
            message: data.message.content,
            position: 'top',
            color: (data.result ? 'positive' : 'warning')
          })
          this.confirmModalPDF = false
        }
      })
    },
    confirmSendPDF () {
      this.confirmModalPDF = true
    },
    generatePDF () {
      const uri = process.env.API + 'purchase-orders/getPdf'
      window.open(uri, '_blank')
    },
    formatPrice (value) {
      const val = (value / 1).toFixed(1).replace('.', ',')
      return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')
    },
    fetchFromServer () {
      this.$q.loading.show()
      const id = this.$route.params.id
      api.get('/suppliers/options').then(({ data }) => {
        this.supplierOptions = data.options
        api.get(`/purchase-orders/${id}`).then(({ data }) => {
          console.log('data')
          console.log(data)
          if (!data.order) {
            this.$router.push('/purchase-orders')
          } else {
            this.order.fields = data.order
            this.order.fields.branch_id = { value: data.order.id_branch, label: data.order.name_branch }
            this.order.fields.storage = { value: data.order.storage, label: data.order.name_storage }
            this.history = data.history
            this.ready = data.ready
            this.supplier_id = { value: data.order.supplier_id, label: data.order.po }
            this.order.fields.acc_currency_type_id = 1
            this.order.fields.supplier = this.supplierOptions.filter(option => option.value === this.order.fields.supplier_id)[0]
            api.get('/products/options/category/0').then(({ data }) => {
              this.productOptions = data.options
              api.get(`/purchase-order-details/order/${id}`).then(({ data }) => {
                console.log(data)
                this.details = data.orderDetails
                this.subtotal = data.total_order
                this.vat_total = data.total_vat
                this.mxn_total = data.total_neto
                api.get(`/purchase-order-documents/purchase-order/${id}`).then(({ data }) => {
                  this.documents = data.documents
                  api.get(`/shipments/order/${id}`).then(({ data }) => {
                    this.shipments = data.shipments
                    console.log('can ejecutar')
                    console.log(this.shipments)
                    api.get('/currencies/options').then(({ data }) => {
                      this.currencyOptions = data.options
                      this.getExchange()
                      this.$q.loading.hide()
                    })
                  })
                })
              })
            })
          }
        })
      })
    },
    backToGrid () {
      this.$router.push('/purchase-orders')
    },
    generatePurchaseOrderPdf () {
      const uri = process.env.API + `purchase-orders/pdf/${this.order.fields.id}`
      window.open(uri, '_blank')
    },
    sendPurchaseOrderPdfToSupplier () {
      this.loadingSendingMailBtn = true
      api.get(`/purchase-orders/${this.order.fields.id}/send-mail`).then(({ data }) => {
        this.$q.notify({
          message: data.message,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        this.loadingSendingMailBtn = false
      })
    },
    requestPurchaseOrder () {
      this.$q.loading.show()
      const params = []
      params.id = this.order.fields.id
      api.put(`/purchase-orders/request/${params.id}`, params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          const id = this.$route.params.id
          api.get(`/purchase-orders/${id}`).then(({ data }) => {
            this.order.fields = data.order
            this.order.fields.supplier = this.supplierOptions.filter(option => option.value === data.order.supplier_id)[0]
            this.$q.loading.hide()
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    approvePurchaseOrder () {
      this.$q.loading.show()
      const params = []
      params.id = this.order.fields.id
      api.put(`/purchase-orders/approve/${params.id}`, params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          const id = this.$route.params.id
          api.get(`/purchase-orders/${id}`).then(({ data }) => {
            this.order.fields = data.order
            this.order.fields.supplier = this.supplierOptions.filter(option => option.value === data.order.supplier_id)[0]
            this.$q.loading.hide()
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    closePurchaseOrder () {
      this.$q.loading.show()
      const params = []
      params.id = this.order.fields.id
      api.put(`/purchase-orders/close/${params.id}`, params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          const id = this.$route.params.id
          api.get(`/purchase-orders/${id}`).then(({ data }) => {
            this.order.fields = data.order
            this.order.fields.supplier = this.supplierOptions.filter(option => option.value === data.order.supplier_id)[0]
            this.$q.loading.hide()
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    reOpenPurchaseOrder () {
      this.$q.loading.show()
      const params = []
      params.id = this.order.fields.id
      api.put(`/purchase-orders/re-open/${params.id}`, params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          const id = this.$route.params.id
          api.get(`/purchase-orders/${id}`).then(({ data }) => {
            this.order.fields = data.order
            this.order.fields.supplier = this.supplierOptions.filter(option => option.value === data.order.supplier_id)[0]
            this.$q.loading.hide()
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    cancelPurchaseOrder () {
      this.$q.loading.show()
      const params = []
      params.id = this.order.fields.id
      api.put(`/purchase-orders/cancel/${params.id}`, params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          const id = this.$route.params.id
          api.get(`/purchase-orders/${id}`).then(({ data }) => {
            this.order.fields = data.order
            this.order.fields.supplier = this.supplierOptions.filter(option => option.value === data.order.supplier_id)[0]
            this.$q.loading.hide()
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    openPurchaseOrder () {
      this.$q.loading.show()
      const params = []
      params.id = this.order.fields.id
      api.put(`/purchase-orders/open/${params.id}`, params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          const id = this.$route.params.id
          api.get(`/purchase-orders/${id}`).then(({ data }) => {
            this.order.fields = data.order
            this.history = data.history
            this.ready = data.ready
            this.order.fields.supplier = this.supplierOptions.filter(option => option.value === data.order.supplier_id)[0]
            this.$q.loading.hide()
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    invertDate (date) {
      if (date !== null) {
        var info = date.split('/').reverse().join('-')
      }
      return info
    },
    updateOrder () {
      this.$v.order.fields.$reset()
      this.$v.order.fields.$touch()
      if (this.$v.order.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = { ...this.order.fields }
      params.supplier_id = Number(this.supplier_id)
      params.embargo_date = this.invertDate(this.order.fields.embargo_date)
      params.requested_date = this.invertDate(this.order.fields.requested_date)
      params.order_date = this.invertDate(this.order.fields.order_date)
      params.date_invoicee = this.$formatDate(this.order.fields.date_invoicee)
      console.log(params)
      api.put(`/purchase-orders/${params.id}`, params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          const id = this.$route.params.id
          api.get(`/purchase-orders/${id}`).then(({ data }) => {
            this.order.fields = data.order
            this.order.fields.branch_id = { value: data.order.id_branch, label: data.order.name_branch }
            this.order.fields.storage = { value: data.order.storage, label: data.order.name_storage }
            this.history = data.history
            this.order.fields.supplier = this.supplierOptions.filter(option => option.value === data.order.supplier_id)[0]
            this.$q.loading.hide()
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    updateDetail () {
      this.$v.detail.fields.$reset()
      this.$v.detail.fields.$touch()
      if (this.$v.detail.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      if (this.detail.fields.product.value == null) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, seleccione un producto.',
          persistent: true
        })
        return false
      }
      if (this.detail.fields.qty <= 0) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, ingrese una cantidad válida.',
          persistent: true
        })
        return false
      }
      if (this.detail.fields.price == null || this.detail.fields.price == null) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, ingrese un precio válido.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = { ...this.detail.fields }
      params.product_id = Number(params.product.value)
      params.vat_rate = Number(this.vat_rate)
      api.put(`/purchase-order-details/${this.detailIdToEdit}`, params).then(({ data }) => {
        this.$v.detail.fields.$reset()
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.cancelEditDetail()
          const id = this.$route.params.id
          api.get(`/purchase-order-details/order/${id}`).then(({ data }) => {
            this.details = data.orderDetails
            this.subtotal = data.total_order
            this.vat_total = data.total_vat
            this.mxn_total = data.total_neto
            this.$q.loading.hide()
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    cancelEditDetail () {
      this.detailIdToEdit = null
      this.detail.fields.product = null
      this.detail.fields.qty = null
      this.detail.fields.price = null
      this.detail.fields.quality = null
      this.detail.fields.color = null
      this.detail.fields.observation = null
      this.lastPriceProduct = null
    },
    addDetail () {
      this.$v.detail.fields.$reset()
      this.$v.detail.fields.$touch()
      if (this.$v.detail.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      if (this.detail.fields.product.value == null) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, seleccione un producto.',
          persistent: true
        })
        return false
      }
      if (this.detail.fields.qty <= 0) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, ingrese una cantidad válida.',
          persistent: true
        })
        return false
      }
      if (this.detail.fields.price == null || this.detail.fields.price == null) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, ingrese un precio válido.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = { ...this.detail.fields }
      params.po_id = Number(this.order.fields.id)
      params.product_id = Number(params.product.value)
      params.vat_rate = Number(this.vat_rate)
      params.last_price = this.lastPriceProduct
      console.log('params')
      console.log(params)
      api.post('/purchase-order-details', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.cancelEditDetail()
          this.$v.detail.fields.$reset()
          const id = this.$route.params.id
          api.get(`/purchase-order-details/order/${id}`).then(({ data }) => {
            this.details = data.orderDetails
            this.subtotal = data.total_order
            this.vat_total = data.total_vat
            this.mxn_total = data.total_neto
            this.$q.loading.hide()
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    editDetail (detailElement) {
      console.log(detailElement)
      this.vat_rate = detailElement.vat_rate
      this.detailIdToEdit = detailElement.id
      this.detail.fields.product = { value: detailElement.product_id, label: detailElement.product }
      this.detail.fields.qty = detailElement.qty
      this.detail.fields.price = detailElement.price
      this.detail.fields.quality = { value: detailElement.quality, label: detailElement.quality }
      this.detail.fields.color = { value: detailElement.color, label: detailElement.color }
      this.detail.fields.observation = detailElement.observation
      this.lastPriceProduct = detailElement.last_price
    },
    removeDetail (detailElement) {
      this.$q.loading.show()
      api.delete(`/purchase-order-details/${detailElement.id}`).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          const id = this.$route.params.id
          api.get(`/purchase-order-details/order/${id}`).then(({ data }) => {
            console.log(data)
            this.$v.detail.fields.$reset()
            this.details = data.orderDetails
            this.subtotal = data.total_order
            this.vat_total = data.total_vat
            this.mxn_total = data.total_neto
            this.detailIdToEdit = null
            this.$q.loading.hide()
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    newShipment () {
      this.$router.push(`/shipments/purcharse-order/${this.order.fields.id}/${1}`)
    },
    editShipment (shipmentElement) {
      this.$router.push(`/shipments/${shipmentElement.id}/${1}`)
    },
    removeShipment (shipment) {
      this.$q.loading.show()
      api.delete(`/shipments/${shipment.id}`).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          const id = this.$route.params.id
          api.get(`/shipments/order/${id}`).then(({ data }) => {
            this.shipments = data.shipments
            this.$q.loading.hide()
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    sendShipmentPdfToSupplier (id) {
      this.$q.loading.show()
      api.get(`/shipments/${id}/send-mail`).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        this.$q.loading.hide()
      })
    },
    generateShipmentPdf (id) {
      const uri = process.env.API + `shipments/pdf/${id}`
      window.open(uri, '_blank')
    },
    saveStorageEntry (shipmentId) {
      this.$q.loading.show()
      api.put(`/shipments/${shipmentId}/entry`, []).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          api.get(`/shipments/order/${this.$route.params.id}`).then(({ data }) => {
            this.shipments = data.shipments
            this.$q.loading.hide()
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    updateDocument () {
      this.$v.document.fields.$reset()
      this.$v.document.fields.$touch()
      if (this.$v.document.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = { ...this.document.fields }
      this.document.fields.id = null
      this.document.fields.name = null
      this.document.fields.observations = null
      api.put(`/purchase-order-documents/${params.id}`, params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          api.get(`/purchase-order-documents/purchase-order/${this.$route.params.id}`).then(({ data }) => {
            this.documents = data.documents
            this.$q.loading.hide()
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    cancelDocument () {
      this.document.fields.id = null
      this.document.fields.name = null
      this.document.fields.observations = null
    },
    addDocument () {
      this.$v.document.fields.$reset()
      this.$v.document.fields.$touch()
      if (this.$v.document.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = { ...this.document.fields }
      params.order_id = Number(this.$route.params.id)
      this.document.fields.name = null
      this.document.fields.observations = null
      api.post('/purchase-order-documents', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          api.get(`/purchase-order-documents/purchase-order/${this.$route.params.id}`).then(({ data }) => {
            this.documents = data.documents
            this.$q.loading.hide()
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    openUploadFileModal (document) {
      this.documentId = document.id
      this.documentName = document.name
      this.documentFileModal = true
    },
    openUploadShipmentInvoiceModal (shipment) {
      this.shipmentInvoice.fields.shipmentId = shipment.id
      this.shipmentInvoice.fields.shipmentSerial = shipment.serial
      this.shipmentInvoiceModal = true
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
    afterUploadShipmentInvoiceFile (response) {
      console.log(response)
      const data = JSON.parse(response.xhr.response)
      this.$q.notify({
        message: data.message.content,
        position: 'top',
        color: (data.result ? 'positive' : 'warning')
      })
      if (data.result) {
        this.fetchFromServer()
        this.shipmentInvoiceModal = false
      }
    },
    uploadDocumentFile () {
      this.$refs.fileDocumentRef.upload()
    },
    uploadShipmentInvoiceFile () {
      this.$refs.fileShipmentInvoiceRef.upload()
    },
    showDocumentFile (document) {
      if (document.document_name) {
        console.log(document)
        const extension = document.document_name.split('.')
        console.log(document.order_id + document.id + '.' + extension[1])
        window.open(`${this.serverUrl}assets/purchase-orders/documents/${document.order_id + '' + document.id + '.' + extension[1]}`, '_blank')
      } else {
        this.$q.notify({
          message: 'El documento no cuenta con un archivo subido',
          position: 'top',
          color: 'warning'
        })
      }
    },
    showShipmentInvoiceFile (shipment) {
      if (shipment.invoice) {
        window.open(`${this.serverUrl}assets/shipments/${shipment.invoice}`, '_blank')
      } else {
        this.$q.notify({
          message: 'La recepción no cuenta con una factura subida',
          position: 'top',
          color: 'warning'
        })
      }
    },
    downloadDocumentFile (document) {
      if (document.document_name) {
        window.open(`${process.env.API}purchase-order-documents/file/${document.id}/download`, '_blank')
      } else {
        this.$q.notify({
          message: 'El documento no cuenta con un archivo subido',
          position: 'top',
          color: 'warning'
        })
      }
    },
    downloadShipmentInvoiceFile (shipment) {
      if (shipment.invoice) {
        // window.open(`${process.env.API}shipments/${shipment.id}/invoice-file/download`, '_blank')
        window.open(`${process.env.API}shipments/invoice-file/download/${shipment.id}`, '_blank')
      } else {
        this.$q.notify({
          message: 'La recepción no cuenta con una factura subida',
          position: 'top',
          color: 'warning'
        })
      }
    },
    editDocument (document) {
      this.document.fields.id = document.id
      this.document.fields.name = document.name
      this.document.fields.observations = document.observations
    },
    removeDocument (id) {
      this.$q.loading.show()
      api.delete(`/purchase-order-documents/${id}`).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        console.log(data)
        if (data.result) {
          api.get(`/purchase-order-documents/purchase-order/${this.$route.params.id}`).then(({ data }) => {
            this.documents = data.documents
            this.$q.loading.hide()
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    changeStatus (status) {
      this.$q.loading.show()
      const params = { status: status, order_id: this.$route.params.id }
      api.put('/purchase-orders/changeStatus', params).then(({ data }) => {
        console.log(data)
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.message_email) {
          this.$q.notify({
            message: data.message_email.content,
            position: 'top',
            color: (data.result ? 'positive' : 'warning')
          })
        }
        if (!this.$store.getters['users/rol'] === 1 && !this.$store.getters['users/rol'] === 21) {
          this.$router.push('/purchase-orders')
        } else if (this.$store.getters['users/rol'] === 21) {
          this.$router.push('/purchase-orders')
        } else {
          if (data.result) {
            const id = this.$route.params.id
            api.get(`/purchase-orders/${id}`).then(({ data }) => {
              console.log(data.history)
              this.order.fields = data.order
              this.order.fields.branch_id = { value: data.order.id_branch, label: data.order.name_branch }
              this.order.fields.storage = { value: data.order.storage, label: data.order.name_storage }
              this.history = data.history
              this.ready = data.ready
              this.order.fields.supplier = this.supplierOptions.filter(option => option.value === data.order.supplier_id)[0]
              this.order.fields.acc_currency_type_id = 1
              this.getExchange()
              this.$q.loading.hide()
            })
          } else {
            this.$q.loading.hide()
          }
        }
      })
    },
    getExchange () {
      // if (this.order.fields.acc_currency_type_id !== 1) {
      //   api.get(`/exchanges/getExchangePO/${this.order.fields.acc_currency_type_id}/${this.order.fields.id}`).then(({ data }) => {
      //     console.log(data)
      //     this.current_currency = data.exchange === false ? 0.00 : data.exchange.current_value
      //     this.mxn_total = (this.current_currency * this.total).toFixed(2)
      //   })
      // } else {
      // }
    },
    filterSupplier (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.filteredSupplierOptions = this.supplierOptions.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    filterProduct (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.filteredProductsOptions = this.filteredProductOptions.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    getBranchesList () {
      api.get('branch-offices/options').then(({ data }) => {
        this.branchesList = data.options
        this.branchOfficeOptions = data.options
        this.branchOfficeOptions2 = data.options
      })
    },
    getStoragesList () {
      api.get('storages/options').then(({ data }) => {
        this.storageList = data.options
        this.storageOptions = data.options
        this.storageOptions2 = data.options
      })
    }
  }
}
</script>

<style>
</style>

<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row q-col-gutter-md">
        <div class="col-sm-3">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
                <q-breadcrumbs-el label="" icon="home" to="/"/>
                <q-breadcrumbs-el label="Pedidos" to="/shopping-carts" />
                <q-breadcrumbs-el v-text="$route.params.id"/>
            </q-breadcrumbs>
          </div>
        </div>
        <div class="col-sm-9 pull-right">
          <div class="q-pa-sm gutter-sm">
          <!--<q-btn v-if="cartStatus !== 'NUEVO' && (this.role === 1 || this.role === 20 || this.role === 17)" color="teal-6" icon="assignment" @click="downloadOC()">
            <q-tooltip content-class="bg-positive">Orden de Compra</q-tooltip>
          </q-btn>
          <q-btn style="margin-left: 8px;" v-if="cartStatus !== 'NUEVO' && (this.role === 1 || this.role === 20 || this.role === 17)" color="positive" icon="fas fa-dollar-sign" @click="downloadTicket()">
            <q-tooltip content-class="bg-positive">Ticket de Pago</q-tooltip>
          </q-btn>
          <q-btn style="margin-left: 8px;" v-if="cartStatus !== 'NUEVO' && (this.role === 1 || this.role === 20 || this.role === 17)" color="cyan" icon="description" @click="downloadFormatDate()">
            <q-tooltip content-class="bg-positive">Formato de Cita</q-tooltip>
          </q-btn>-->
          <q-btn v-if="cartStatus === 'COTIZADO' || cartStatus === 'AUTORIZADO' && (this.role === 1 || this.role === 20 || this.role === 17 || this.role === 29 || this.role === 28 || this.role === 4 || this.role === 23 || this.role === 22)" style="margin-left: 8px;" color="red" icon="block" label="CANCELAR" @click="cancel()" />
          <q-btn color="blue" icon="fas fa-file-pdf" v-if="shoppingCartInBulkDetails.length > 0" @click.native="quotationNotePDF()" style="margin-left: 8px;">
            <q-tooltip content-class="bg-blue">Cotización</q-tooltip>
          </q-btn>
          <q-btn v-if="cartStatus !== 'NUEVO' && (this.role === 1 || this.role === 20 || this.role === 17 || this.role === 29 || this.role === 28 || this.role === 4 || this.role === 23 || this.role === 4 || this.role === 22)" style="margin-left: 8px;" color="blue" :icon="shoppingCart.fields.document_id === null ? 'cloud_upload' : 'visibility'" @click="shoppingCart.fields.document_id === null ? showDocumentFile() : downloadDocumentOc()">
            <q-tooltip content-class="bg-blue" v-if="shoppingCart.fields.document_id === null ? false : true">Descargar orden de compra</q-tooltip>
            <q-menu v-if="shoppingCart.fields.document_id === null ? false : true">
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
          <q-btn v-if="cartStatus !== 'NUEVO' && (this.role === 1 || this.role === 20 || this.role === 17 || this.role === 29 || this.role === 28 || this.role === 4 || this.role === 22)" style="margin-left: 8px;" color="red" icon="fas fa-file-pdf" @click="downloadPDF()">
            <q-tooltip content-class="bg-red">Descargar PDF</q-tooltip>
          </q-btn>
          <!--<q-btn style="margin-left: 8px;" v-if="cartStatus === 'REMISIONADO' && (this.role === 1 || this.role === 20 || this.role === 17)" color="warning" icon="fas fa-file-pdf" @click="generarTicket()">
            <q-tooltip content-class="bg-positive">Generar Ticket</q-tooltip>
          </q-btn>-->
          <q-btn style="margin-left: 8px;" v-if="cartStatus !== 'NUEVO' && (this.role === 1 || this.role === 20 || this.role === 17 || this.role === 25 || this.role === 28 || this.role === 29 || this.role === 4 || this.role === 22)" color="purple" icon="mail" @click="confirmSendPDF()">
            <q-tooltip content-class="bg-purple">Enviar email</q-tooltip>
          </q-btn>
          <q-btn v-if="cartStatus === 'COTIZADO' || cartStatus === 'AUTORIZADO' && (this.role === 1 || this.role === 20 || this.role === 23 || this.role === 20 || this.role === 25 || this.role === 27 || this.role === 28 || this.role === 29 || this.role === 23 || this.role === 4 || this.role === 22)" style="margin-left: 8px;" color="warning" icon="fas fa-undo-alt" @click="confirm()">
            <q-tooltip content-class="bg-positive">Rechazar Pedido</q-tooltip>
          </q-btn>
          <q-btn v-if="canCancel === true" style="margin-left: 8px;" color="negative" icon="block" @click="cancelarRemision()">
            <q-tooltip content-class="bg-positive">Cancelar</q-tooltip>
          </q-btn>
          <!--<q-btn v-if="cartStatus === 'SOLICITADO' && (this.role === 1 || this.role === 20 || this.role === 23)" style="margin-left: 8px;" color="warning" icon="edit" label="Editar Comentario" @click="editComents()" />-->
          <q-btn v-if="cartStatus === 'COTIZADO' && (this.role === 1 || this.role === 23 || this.role === 25 || this.role === 27 || this.role === 28 || this.role === 22 || this.role === 25 || this.role === 27 || this.role === 28 || this.role === 29 || this.role === 4 || this.role === 20 || this.role === 22)" style="margin-left: 8px;" color="positive" icon="check" label="AUTORIZAR" @click="approve()" />
          <q-btn v-if="cartStatus === 'AUTORIZADO' || cartStatus === 'PARCIAL' && (this.role === 1 || this.role === 2 || this.role === 20 || this.role === 4 || this.role === 27 || this.role === 29 || this.role === 4)"  style="margin-left: 8px;" :disable="this.role != 1 && this.role != 2 && this.role != 20 && this.role != 4 && this.role != 27 && this.role != 29 && this.role != 4 && this.role != 22" color="positive" icon="check" label="REMISIONAR" @click="openGenerateInvoiceModal()" />
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
                v-model="shoppingCart.fields.created"
                label="Fecha"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-shopping-cart" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="shoppingCart.fields.typeOrder"
                label="Tipo de pedido"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-cash-register" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="shoppingCart.fields.origen"
                label="Sucursal de envío"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-shopping-cart" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-input
                color="dark"
                :bg-color="colorInput"
                filled
                v-model="shoppingCart.fields.status"
                label="Estatuss"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-battery-full" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="shoppingCart.fields.storage_id"
                label="Almacen de envío"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-shopping-cart" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="shoppingCart.fields.user_name"
                label="Vendedor"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-user" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="shoppingCart.fields.customer_name"
                label="Cliente"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-shopping-cart" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="shoppingCart.fields.price_list"
                label="Precio lista"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-list" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="shoppingCart.fields.oc_reference"
                label="No. Orden"
                @blur="editReeference()"
              >
                <template v-slot:prepend>
                  <q-icon name="code" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="totalPrice"
                label="Subtotal"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-dollar-sign" />
                </template>
              </q-input>
            </div>
            <!--<div class="col-xs-12 col-sm-2">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="thisIeps"
                label="IEPS"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-dollar-sign" />
                </template>
              </q-input>
            </div>-->
            <div class="col-xs-12 col-sm-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="thisIva"
                label="IVA"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-dollar-sign" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="thisTotal"
                label="Total"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-dollar-sign" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-6">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                autogrow
                :disable="cartStatus !== 'COTIZADO' && (this.role !== 1 || this.role !== 20 || this.roles != 23)"
                v-model="shoppingCart.fields.comments"
                label="Comentarios"
                @blur="editComents()"
              >
                <template v-slot:prepend>
                  <q-icon name="chat" />
                </template>
              </q-input>
            </div>
           <!--<div class="col-xs-3">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="shoppingCart.fields.oc_date"
                mask="date"
                label="Fecha de Orden de Compra"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="event" />
                </template>
                <q-popup-proxy
                  ref="ocDate"
                  transition-show="scale"
                  transition-hide="scale"
                >
                  <div class="col-sm-12">
                    <q-date
                     :locale="myLocale"
                      mask="DD/MM/YYYY"
                      v-model="shoppingCart.fields.oc_date"
                      @input="() => $refs.ocDate.hide()"
                      today-btn
                    />
                  </div>
                </q-popup-proxy>
              </q-select>
            </div>-->
            <!--<div class="col-xs-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="shoppingCart.fields.oc_reference"
                label="# Orden de Compra"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="code" />
                </template>
              </q-input>
            </div>-->
            <div class="col-xs-3" v-if="term === 'CONTADO'">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="shoppingCart.fields.payment_date"
                mask="date"
                label="Fecha de Pago"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="event" />
                </template>
                <q-popup-proxy
                  ref="paymentDate"
                  transition-show="scale"
                  transition-hide="scale"
                >
                  <div class="col-sm-12">
                    <q-date
                     :locale="myLocale"
                      mask="DD/MM/YYYY"
                      v-model="shoppingCart.fields.payment_date"
                      @input="() => $refs.paymentDate.hide()"
                      today-btn
                    />
                  </div>
                </q-popup-proxy>
              </q-select>
            </div>
            <div class="col-xs-3" v-if="term === 'CONTADO'">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="shoppingCart.fields.payment_method"
                label="Forma de pago"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="attach_money" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-3" v-if="term === 'CONTADO'">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="shoppingCart.fields.payment_reference"
                label="Referencia"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="insert_drive_file" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-2">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="appliedDiscount"
                label="Descuento aplicado"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-percentage" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-2">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="loan"
                label="Prestamo"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-cash-register" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-md-2">
                  <q-select color="dark" bg-color="secondary" filled
                            v-model="special_order"
                            :options="
                            [ {label: 'NORMAL', value: 0},
                              {label: 'ESPECIAL', value: 1},
                            ]"
                            use-input
                            disable
                            label="Pedido"
                            emit-value
                            map-options
                            >
                    <template v-slot:prepend>
                      <q-icon name="grade" />
                    </template>
                  </q-select>
                </div>
            <!--<div class="col-xs-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="tax_invoice"
                label="Factura o Remision"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="attach_money" />
                </template>
              </q-input>
            </div>-->
          </div>
          <div v-if="cartStatus === 'COTIZADO'" class="q-pa-md">
          <br v-if="shoppingCartInBulkDetails.length > 0">
          <q-table
            title="PRODUCTOS"
            dense
            hide-bottom
            :data="shoppingCartInBulkDetails"
            :columns="inBulkColumnsSOL"
            :pagination.sync="pagination"
            row-key="product_name"
            v-if="shoppingCartInBulkDetails.length > 0"
          >
            <template v-slot:body="props">
              <q-tr :props="props">
                <q-td key="product_name" :props="props" style="width:20%;">{{ props.row.product_name }}</q-td>
                <q-td key="line_name" :props="props" style="width:15%;">{{ props.row.line_name }}</q-td>
                <q-td key="category_name" :props="props" style="width:10%;">{{ props.row.category_name }}</q-td>
                <q-td key="qty" :props="props" style="width:20%;">{{ `${formatter.format(props.row.qty)} Pzas.` }}</q-td>
                <q-td key="unit_price" :props="props" style="width:20%;">{{ `${currencyFormatter.format(props.row.unit_price)} ` }}</q-td>
                <q-td key="amount" :props="props" class="pull-right" style="width:15%;">{{ `${currencyFormatter.format(props.row.amount)} ` }}</q-td>
              </q-tr>
            </template>
          </q-table>
        </div>
          <div v-if="cartStatus === 'AUTORIZADO' || cartStatus === 'REMISIONADO' || cartStatus == 'CANCELADO' || cartStatus == 'PARCIAL'" class="q-pa-md">
            <br v-if="shoppingCartInBulkDetails.length > 0">
            <q-table
              title="PRODUCTOS"
              dense
              hide-bottom
              :data="shoppingCartInBulkDetails"
              :columns="inBulkColumnsAUT"
              row-key="product_name"
              :pagination.sync="pagination"
              v-if="shoppingCartInBulkDetails.length > 0"
            >
              <template v-slot:body="props">
                <q-tr :props="props">
                  <q-td key="product_name" :props="props" style="width:20%;">{{ props.row.product_name }}</q-td>
                  <q-td key="line_name" :props="props" style="width:10%;">{{ props.row.line_name }}</q-td>
                  <q-td key="category_name" :props="props" style="width:10%;">{{ props.row.category_name }}</q-td>
                  <q-td key="invoice_id" :props="props" style="width:10%;"><label @click="openActions(props.row.invoice_id)" style="text-decoration: underline black; cursor: pointer;">{{ props.row.invoice_id }}</label></q-td>
                  <q-td key="qty" :props="props" style="width:10%;">{{ `${formatter.format(props.row.qty)} Pzas.` }}</q-td>
                  <q-td key="unit_price" :props="props" style="width:10%;">{{ `${currencyFormatter.format(props.row.unit_price)} ` }}</q-td>
                  <q-td key="amount" :props="props" style="width:10%;">{{ `${currencyFormatter.format(props.row.amount)} ` }}</q-td>
                  <q-td key="stock" :props="props" v-if="props.row.status === 'REMISIONADO'" style="width:15%;">
                    <q-chip square dense color="positive" text-color="white">
                      REMISIONADO
                    </q-chip>
                  </q-td>
                  <q-td key="stock" :props="props" v-if="props.row.status === 'CANCELADO'" style="width:15%;">
                    <q-chip square dense color="red" text-color="white">
                      CANCELADO
                    </q-chip>
                  </q-td>
                  <q-td key="stock" :props="props" v-if="props.row.status === 'POSTERGADO'" style="width:15%;">
                    <q-chip square dense color="deep-purple-7" text-color="white">
                      PEDIDO POSTERGADO
                    </q-chip>
                  </q-td>
                  <q-td key="stock" :props="props" style="width:15%;" v-if="props.row.status === 'AUTORIZADO' && props.row.condition === true">
                    <q-chip square dense color="positive" text-color="white">
                      <label style="cursor: pointer;" @click="openModaliBD(1, props.row.qty, props.row.product_id, props.row.shopping_cart_id, props.row.id)">{{ `${formatter.format(props.row.stock)} Pzas.` }}</label>
                    </q-chip>
                  </q-td>
                  <q-td key="stock" :props="props" style="width:15%;" v-if="props.row.status === 'AUTORIZADO' && props.row.condition === false">
                    <q-chip square dense color="negative" text-color="white">
                      <label style="cursor: pointer;" @click="openModaliBD(1, props.row.qty, props.row.product_id, props.row.shopping_cart_id, props.row.id)">{{ `${formatter.format(props.row.stock)} Pzas.` }}</label>
                    </q-chip>
                  </q-td>
                  <q-td key="stock" :props="props" style="width:15%;" v-if="props.row.status === 'AUTORIZADO' && props.row.stock === false">
                    <q-chip square dense color="negative" text-color="white">
                      <label>SIN STOCK</label>
                    </q-chip>
                  </q-td>
                  <q-td key="status" :props="props" style="width:5%;">
                    <q-chip square dense :color="props.row.status == 'REMISIONADO' ? 'positive' : (props.row.status == 'AUTORIZADO' ? 'green-4' : (props.row.status == 'POSTERGADO' ? 'deep-purple-7' : (props.row.status == 'CANCELADO' ? 'red' : 'primary')))" text-color="white">
                      {{ props.row.status }}
                    </q-chip>
                  </q-td>
                </q-tr>
              </template>
            </q-table>
        </div>
        </div>
      </div>
    </div>
    <q-dialog v-model="generateInvoiceModal" persistent>
      <q-card style="min-width: 400px;">
        <q-card-section class="bg-teal-14 text-white">
          <div class="text-h6">REMISIONAR PEDIDO</div>
        </q-card-section>
        <q-card-section>
          <div class="row">
            <div class="col-xs-12">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="saleDate"
                mask="date"
                label="Fecha de venta"
                :error="$v.saleDate.$error"
                :rules="saleDateRules"
              >
                <template v-slot:prepend>
                  <q-icon name="event" />
                </template>
                <q-popup-proxy
                  ref="invoiceSaleDate"
                  transition-show="scale"
                  transition-hide="scale"
                >
                  <div class="col-sm-12">
                    <q-date
                     :locale="myLocale"
                      mask="DD/MM/YYYY"
                      v-model="saleDate"
                      @input="() => $refs.invoiceSaleDate.hide()"
                      today-btn
                    />
                  </div>
                </q-popup-proxy>
              </q-select>
            </div>
            <div class="col-xs-12">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                autogrow
                v-model="shoppingCart.fields.customer_name"
                :rules="customerBranchOfficeRules"
                label="Cliente"
                disable
              />
            </div>
            <div class="col-xs-12">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                :disable="destiny"
                v-model="customerBranchOffice"
                :options="customerBranchOfficeOptions"
                emit-value map-options
                label="Sucursal de cliente"
                :rules="customerBranchOfficeRules"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-store-alt" />
                </template>
              </q-select>
            </div>
            <!-- <div class="col-xs-12">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="driver"
                :options="driverOptions"
                label="Chofer"
                :rules="driverRules"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-truck" />
                </template>
              </q-select>
            </div> -->
            <div class="col-xs-12">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                autogrow
                v-model="comments"
                label="Comentarios"
                type="textarea"
                @input="v => { comments = v.toUpperCase() }"
              />
            </div>
            <div class="col-xs-12" style="margin-top: 15px">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="oc_reference"
                :error="$v.oc_reference.$error"
                label="# Orden de Compra"
                @input="v => { oc_reference = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="code" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12">
            <q-uploader
                :url="fileDocumentUrlOC"
                method="POST"
                label="Orden de Compra"
                ref="fileDocumentOCRef"
                max-files="1"
                flat
                auto-upload
                no-thumbnails
                :headers="[{name: 'Authorization', value: 'Bearer ' + this.JWT}]"
                @uploaded="afterUploadDocumentFileOC"
              />
            </div>
          </div>
        </q-card-section>
        <q-card-actions align="right" class="text-primary">
          <q-btn color="red" icon="fas fa-times-circle" label="Cerrar" @click="closeGenerateInvoiceModal()" v-close-popup />
          <q-btn color="positive" icon="fas fa-check" label="Remisionar" @click="generateInvoice()" />
        </q-card-actions>
      </q-card>
    </q-dialog>
    <q-dialog v-model="confirmPartialORder" persistent>
      <q-card style="width:500px;">
        <q-card-section class="bg-primary">
          <div class="row">
            <div class="col-sm-11 text-h6" style="color:white;"></div>
            <div class="col-sm-1 pull-right"><q-btn color="white" flat v-close-popup round dense icon="close" /></div>
          </div>
        </q-card-section>
        <br>
        <q-card-section class="row items-center">
          <label style="font-size: 23px;text-align: justify;">{{ textv2 }}</label>
        </q-card-section>
        <br><br>
        <q-card-actions align="right">
          <q-btn label="Cancelar" color="negative" v-close-popup />
          <q-btn label="Aceptar" color="positive" @click="openmodalGenerateInvoice()"/>
        </q-card-actions>
      </q-card>
    </q-dialog>
    <q-dialog v-model="confirmModal" persistent>
      <q-card style="width:500px;">
        <q-card-section class="bg-primary">
          <div class="row">
            <div class="col-sm-11 text-h6" style="color:white;">Alerta</div>
            <div class="col-sm-1 pull-right"><q-btn color="white" flat v-close-popup round dense icon="close" /></div>
          </div>
        </q-card-section>
        <br>
        <q-card-section class="row items-center">
          <label style="font-size: 23px;">{{ text }}</label>
        </q-card-section>
        <br><br>
        <q-card-actions align="right" v-if="buttonModal">
          <q-btn label="Cancelar" color="negative" v-close-popup />
          <q-btn label="Aceptar" color="positive" @click="edit()"/>
        </q-card-actions>
      </q-card>
    </q-dialog>
    <q-dialog v-model="confirmCancelModal" persistent>
      <q-card style="width:500px;">
        <q-card-section class="bg-primary">
          <div class="row">
            <div class="col-sm-11 text-h6" style="color:white;">Alerta</div>
            <div class="col-sm-1 pull-right"><q-btn color="white" flat v-close-popup round dense icon="close" /></div>
          </div>
        </q-card-section>
        <br>
        <q-card-section class="row items-center text-center">
          <label style="font-size: 23px;">¿Está seguro de Cancelar el Pedido?</label>
        </q-card-section>
        <br>
        <q-card-actions align="right" v-if="buttonModal">
          <q-btn label="Cancelar" color="negative" v-close-popup />
          <q-btn label="Aceptar" color="positive" @click="cancelar()"/>
        </q-card-actions>
      </q-card>
    </q-dialog>
    <q-dialog v-model="modalinBulkDetails" persistent>
      <q-card>
        <div style="width:400px;">
          <div class="bg-teal-6 q-pt-md q-pb-md">
            <div class="row">
              <div class="col-sm-10 text-h6" style="color:white;">&nbsp;&nbsp;PARCIALIZACIÓN DE PEDIDOS</div>
              <div class="col-sm-2 pull-right q-pr-xs"><q-btn color="white" flat v-close-popup round dense icon="close" /></div>
            </div>
          </div>
          <div class="row" style="margin-top: 10px;">
            <div class="col-md-7 col-xs-7 q-pa-md">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="baleQty"
                mask="#"
                reverse-fill-mask
                label="Cantidad a Entregar."
                @input="inBulk_calculation()"
              >
                <template v-slot:prepend>
                  <q-icon name="emoji_objects" />
                </template>
              </q-input>
            </div>
            <div class="col-md-5 col-xs-5 q-pa-md q-mt-md">
              <q-btn  color="positive" label="Parcializar" @click="partializationinBulk()"/>
            </div>
          </div>
          <div class="row" style="margin-top: 15px;" >
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
              <div class="row">
                <div class="col-md-4 q-pl-md" style="text-align: left;">
                  <label style="font-size: 14px; font-weight: bold;">DISPONIBLE</label>
                </div>
                <div class="col-md-3">
                  <label> {{ `${formatter.format(dataBalesWeight)} Pzas.` }} </label>
                </div>
              </div>
            </div>
            <q-separator inset style="margin-top:10px;" />
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 q-pa-md">
              <div class="row">
              </div>
            </div>
          </div>
        </div>
      </q-card>
    </q-dialog>
    <q-dialog v-model="confirmModalPDF" persistent>
      <q-card style="width:500px;">
        <q-card-section class="bg-primary">
          <div class="row">
            <div class="col-sm-11 text-h6">CONFIRMACIÓN DE ENVIO</div>
            <div class="col-sm-1 pull-right"><q-btn color="white" flat v-close-popup round dense icon="close" /></div>
          </div>
        </q-card-section>
        <br>
        <div class="col-xs-12 col-sm-12 q-mt-sm q-pl-lg">
        <div class="text-h6">¿Está seguro de enviar el correo del pedido al cliente?</div>
        </div>
        <!-- <q-card-section class="row items-center"> -->
          <!-- <div class="col-xs-12 col-sm-12 q-mt-sm"> -->
              <!-- <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="emailWithAttention"
                readonly
                :label="labelAtention"
              >
                <template v-slot:prepend>
                  <q-icon name="chat" />
                </template>
              </q-input> -->
            <!-- </div> -->
        <!-- </q-card-section> -->
        <br><br>
        <q-card-actions align="right" v-if="buttonModal">
          <q-btn label="Cancelar" color="negative" v-close-popup />
          <q-btn label="Aceptar" color="positive" @click="sendPDF()"/>
        </q-card-actions>
      </q-card>
    </q-dialog>
    <q-dialog v-model="documentFileModalOrderBuy" persistent>
        <q-card>
          <q-card-section class="row">
            <div class="col-xs-12 col-sm-10 text-h6">Subir archivo</div>
            <q-btn class="col-xs-12 col-sm-2 pull-right" icon="close" flat round dense v-close-popup />
          </q-card-section>
          <q-card-section>
            <q-uploader
              :url="fileDocumentUrlOCRef"
              :headers="[{name: 'Authorization', value: 'Bearer ' + this.JWT}]"
              method="POST"
              ref="fileDocumentOCRef"
              hide-upload-btn
              @uploaded="afterUploadDocumentFileOCRef"
            />
          </q-card-section>
          <q-card-actions align="right" class="text-secondary">
            <q-btn flat label="Subir archivo" @click="uploadDocumentFileOc()" />
          </q-card-actions>
        </q-card>
      </q-dialog>
  </q-page>
</template>

<script>
import api from '../../commons/api.js'
const { required } = require('vuelidate/lib/validators')

export default {
  name: 'EditRequestedOrder',
  validations: {
    saleDate: { required },
    customerBranchOffice: { required },
    oc_reference: { }
    // driver: { required }
  },
  data () {
    return {
      special_order: null,
      documentFileModalOrderBuy: false,
      myLocale: {
        /* starting with Sunday */
        days: 'Domingo_Lunes_Martes_Miércoles_Jueves_Viernes_Sábado'.split('_'),
        daysShort: 'Dom_Lun_Mar_Mié_Jue_Vie_Sáb'.split('_'),
        months: 'Enero_Febrero_Marzo_Abril_Mayo_Junio_Julio_Agosto_Septiembre_Octubre_Noviembre_Diciembre'.split('_'),
        monthsShort: 'Ene_Feb_Mar_Abr_May_Jun_Jul_Ago_Sep_Oct_Nov_Dic'.split('_'),
        firstDayOfWeek: 1
      },
      oc_reference: null,
      tax_invoice: null,
      term: null,
      confirmModalPDF: false,
      confirmCancelModal: false,
      role: null,
      cartStatus: '',
      confirmModal: false,
      destiny: false,
      text: '',
      textv2: '',
      buttonModal: true,
      cndEditOrder: true,
      cndPartialOrder: false,
      confirmPartialORder: false,
      modalBaleDetails: false,
      modalinBulkDetails: false,
      baleQty: 0,
      dataBales: [],
      dataBalesWeight: 0,
      dataBalesSum: 0,
      idDetail: 0,
      typePart: null,
      shoppingCart: {
        fields: {
          id: null,
          origen: null,
          customer_id: null,
          customer_name: null,
          price_list: null,
          user_id: null,
          user_name: null,
          user_email: null,
          status: null,
          comments: null,
          oc_date: null,
          oc_reference: null,
          payment_date: null,
          payment_reference: null,
          payment_method: null,
          applied_discount: null,
          storage_id: null,
          typeOrder: null,
          document_id: null
        }
      },
      serverUrl: process.env.API,
      formatter: new Intl.NumberFormat('en-US'),
      currencyFormatter: new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
      }),
      inBulkColumnsSOL: [
        { name: 'product_name', align: 'left', label: 'PRODUCTO', field: 'product_name', sortable: true },
        { name: 'line_name', align: 'left', label: 'LÍNEA', field: 'line_name', sortable: true },
        { name: 'category_name', align: 'left', label: 'CATEGORÍA', field: 'category_name', sortable: true },
        { name: 'qty', align: 'right', label: 'CANTIDAD', field: 'qty', sortable: true },
        { name: 'unit_price', align: 'right', label: 'PRECIO PZA.', field: 'unit_price', sortable: true },
        { name: 'amount', align: 'right', label: 'IMPORTE', field: 'amount', sortable: true }
      ],
      inBulkColumnsAUT: [
        { name: 'product_name', align: 'left', label: 'PRODUCTO', field: 'product_name', sortable: true },
        { name: 'line_name', align: 'left', label: 'LÍNEA', field: 'line_name', sortable: true },
        { name: 'category_name', align: 'left', label: 'CATEGORÍA', field: 'category_name', sortable: true },
        { name: 'invoice_id', align: 'left', label: 'REMISIÓN NO.', field: 'invoice_id', sortable: true },
        { name: 'qty', align: 'right', label: 'CANTIDAD', field: 'qty', sortable: true },
        { name: 'unit_price', align: 'right', label: 'PRECIO PZA.', field: 'unit_price', sortable: true },
        { name: 'amount', align: 'right', label: 'IMPORTE', field: 'amount', sortable: true },
        { name: 'stock', align: 'center', label: 'STOCK', field: 'stock', sortable: true },
        { name: 'status', align: 'center', label: 'ESTATUS', field: 'status', sortable: false }
      ],
      pagination: {
        rowsPerPage: 50
      },
      loan: null,
      shoppingCartBaleDetails: [],
      shoppingCartInBulkDetails: [],
      shoppingCartLaminateDetails: [],
      customerBranchOfficeOptions: [],
      driverOptions: [],
      generateInvoiceModal: false,
      saleDate: null,
      customerBranchOffice: null,
      driver: null,
      comments: null,
      emailWithAttention: null,
      labelAtention: null,
      canCancel: false,
      info: [],
      dataDocument: [],
      download: false,
      dataResultDocument: [],
      colorInput: null
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
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(4) && !this.$store.getters['users/roles'].includes(2) && !this.$store.getters['users/roles'].includes(23) && !this.$store.getters['users/roles'].includes(12) && !this.$store.getters['users/roles'].includes(20)) {
      this.$router.push('/')
    }
  }, */
  created () {
    this.fetchFromServer()
  },
  computed: {
    fileDocumentUrlOCRef () {
      return `${process.env.API}documents/file/${this.$route.params.id}`
    },
    appliedDiscount () {
      let discount = 0
      if (this.shoppingCart.fields.applied_discount != null) {
        discount = this.shoppingCart.fields.applied_discount
      }
      return discount + '%'
    },
    JWT () {
      return localStorage.getItem('JWT')
    },
    partialOrder () {
      let go = false
      let qty = 0
      if (this.shoppingCartBaleDetails.length > 0) {
        this.shoppingCartBaleDetails.forEach(detail => {
          if (detail.status === 'AUTORIZADO') {
            if (detail.bale_qty) {
              qty += Number(detail.bale_qty)
            } else if (detail.bales_qty) {
              qty += Number(detail.bales_qty)
            } else {
              qty += Number(0)
            }
            if (detail.qty > qty) {
              go = true
            }
          }
        })
      }
      if (this.shoppingCartInBulkDetails.length > 0) {
        this.shoppingCartInBulkDetails.forEach(detail => {
          if (detail.status === 'AUTORIZADO') {
            if (detail.stock > detail.qty) {
              go = false
            }
          }
        })
      }
      if (this.shoppingCartLaminateDetails.length > 0) {
        this.shoppingCartLaminateDetails.forEach(detail => {
          if (detail.status === 'AUTORIZADO') {
            if (detail.stock > detail.qty) {
              go = false
            }
          }
        })
      }
      return go
    },
    status () {
      let status = true
      this.shoppingCartBaleDetails.forEach(detail => {
        if (detail.status === 'REMISIONADO') {
          status = false
        }
      })
      this.shoppingCartInBulkDetails.forEach(detail => {
        if (detail.status === 'REMISIONADO') {
          status = false
        }
      })
      this.shoppingCartLaminateDetails.forEach(detail => {
        if (detail.status === 'REMISIONADO') {
          status = false
        }
      })
      return status
    },
    totalPrice () {
      let price = 0
      this.shoppingCartBaleDetails.forEach(detail => {
        if (detail.bale_qty) {
          price += Number(detail.bale_qty * detail.unit_price)
        } else if (detail.bales_qty) {
          price += Number(detail.bales_qty * detail.unit_price)
        } else {
          price += Number(detail.amount)
        }
      })
      this.shoppingCartInBulkDetails.forEach(detail => {
        price += Number(detail.amount)
      })
      this.shoppingCartLaminateDetails.forEach(detail => {
        price += Number(detail.amount)
      })
      return this.currencyFormatter.format(price)
    },
    /* thisIeps () {
      let price = 0
      let totalIeps = 0
      this.shoppingCartInBulkDetails.forEach(detail => {
        price = (Number(detail.amount) / 1.16) - (Number(detail.amount) / 1.16 / (Number(detail.ieps) / 100 + 1))
        totalIeps += price
      })
      return this.currencyFormatter.format(totalIeps)
    }, */
    thisIva () {
      let price = 0
      this.shoppingCartBaleDetails.forEach(detail => {
        if (detail.bale_qty) {
          price += Number(detail.bale_qty * detail.unit_price) * Number(0.16)
        } else if (detail.bales_qty) {
          price += Number(detail.bales_qty * detail.unit_price) * Number(0.16)
        } else {
          price += Number(detail.amount) * Number(0.16)
        }
      })
      this.shoppingCartInBulkDetails.forEach(detail => {
        price += Number(detail.amount) * Number(0.16)
      })
      this.shoppingCartLaminateDetails.forEach(detail => {
        price += Number(detail.amount) * Number(0.16)
      })
      return this.currencyFormatter.format(price)
    },
    thisTotal () {
      let price = 0
      this.shoppingCartBaleDetails.forEach(detail => {
        if (detail.bale_qty) {
          price += Number(detail.bale_qty * detail.unit_price)
          price += Number(detail.bale_qty * detail.unit_price) * Number(0.16)
        } else if (detail.bales_qty) {
          price += Number(detail.bales_qty * detail.unit_price)
          price += Number(detail.bales_qty * detail.unit_price) * Number(0.16)
        } else {
          price += Number(detail.amount)
          price += Number(detail.amount) * Number(0.16)
        }
      })
      this.shoppingCartInBulkDetails.forEach(detail => {
        price += Number(detail.amount)
        price += Number(detail.amount) * Number(0.16)
      })
      this.shoppingCartLaminateDetails.forEach(detail => {
        price += Number(detail.amount)
        price += Number(detail.amount) * Number(0.16)
      })
      return this.currencyFormatter.format(price)
    },
    canGenerateInvoice () {
      let canGenerate = true
      const inBulkDetailsWithStock = this.shoppingCartInBulkDetails.filter(detail => detail.condition && detail.status === 'AUTORIZADO')
      let inBulk = 0
      if (inBulkDetailsWithStock) {
        inBulk = inBulkDetailsWithStock.length
      }
      if (inBulk > 0) {
        canGenerate = true
      }
      inBulkDetailsWithStock.forEach(product => {
        if (product.stock === false ? true : Number(product.qty) > Number(product.stock)) {
          canGenerate = false
        }
      })
      return canGenerate
    },
    saleDateRules (val) {
      return [
        val => (this.$v.saleDate.required) || 'El campo Fecha de venta es requerido.'
      ]
    },
    customerBranchOfficeRules (val) {
      return [
        val => (this.$v.customerBranchOffice.required) || 'El campo Sucursal de cliente es requerido.'
      ]
    },
    driverRules (val) {
      return [
        val => (this.$v.driver.required) || 'El campo Chofer es requerido.'
      ]
    },
    fileDocumentUrlOC () {
      return `${process.env.API}documents/file/${this.$route.params.id}`
    }
  },
  methods: {
    async showDocumentFileOcMenu () {
      await api.get(`/shopping-carts/getDataDocument/${this.shoppingCart.fields.document_id}`).then(({ data }) => {
        if (data.result) {
          this.dataResultDocument = data.datadocument
          console.log(this.dataResultDocument)
        }
      })
      api.fileDownload(`/documents/getFileOrderShoppingCart/${this.shoppingCart.fields.document_id}`).then(({ data }) => {
        this.info.image = data
        console.log(data)
        const url = window.URL.createObjectURL(new Blob([data], { type: this.dataResultDocument[0].mimetype }))
        window.open(url, '_blank')
      })
    },
    async downloadDocumentFileOc () {
      await api.get(`/shopping-carts/getDataDocument/${this.shoppingCart.fields.document_id}`).then(({ data }) => {
        if (data.result) {
          this.dataResultDocument = data.datadocument
          console.log(this.dataResultDocument)
        }
      })
      api.fileDownload(`/documents/getFileOrderShoppingCart/${this.shoppingCart.fields.document_id}`).then(({ data }) => {
        this.info.image = data
        const url = window.URL.createObjectURL(new Blob([data], { type: this.dataResultDocument[0].mimetype }))
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', this.dataResultDocument[0].filename)
        document.body.appendChild(link)
        link.click()
      })
    },
    async deleteFileOc () {
      api.delete(`/documents/${this.shoppingCart.fields.document_id}`).then(({ data }) => {
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
    afterUploadDocumentFileOCRef (response) {
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
    uploadDocumentFileOc () {
      this.$refs.fileDocumentOCRef.upload()
    },
    async editReeference () {
      const params = {}
      params.oc_referenceshp = this.shoppingCart.fields.oc_reference
      this.$q.loading.show()
      await api.put(`/shopping-carts/editReeference/${this.$route.params.id}`, params).then(({ data }) => {
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
    async showDocumentFile () {
      this.documentFileModalOrderBuy = true
      /* this.$q.loading.show()
      await api.get(`/shopping-carts/getDataOfOrderShoppingcart/${this.shoppingCart.fields.document_id}/${'No'}`).then(({ data }) => {
        if (data.result) {
          this.dataDocument = data.datadocument
          this.download = true
        }
      })
      if (this.download) {
        api.fileDownload(`/documents/getFileOrderShoppingCart/${this.shoppingCart.fields.document_id}`).then(({ data }) => {
          this.info.image = data
          console.log(data)
          const url = window.URL.createObjectURL(new Blob([data], { type: this.dataDocument.mimetype }))
          console.log(url)
          window.open(url, '_blank')
        })
      }
      this.$q.loading.hide() */
    },
    async downloadDocumentFileOrder () {
      this.$q.loading.show()
      await api.get(`/shopping-carts/getDataOfOrderShoppingcart/${this.shoppingCart.fields.document_id}/${'No'}`).then(({ data }) => {
        if (data.result) {
          this.dataDocument = data.datadocument
          this.download = true
        }
      })
      if (this.download) {
        api.fileDownload(`/documents/getFileOrderShoppingCart/${this.shoppingCart.fields.document_id}`).then(({ data }) => {
          this.$q.loading.hide()
          console.log(data)
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
    downloadDocumentOc () {
      return true
    },
    quotationNotePDF () {
      const order = 'si'
      const uri = process.env.API + `shopping-carts/quotationNotePDF/${this.$route.params.id}/${order}`
      window.open(uri, '_blank')
    },
    downloadOC () {
      // console.log(this.shoppingCart.fields)
      if (this.shoppingCart.fields.oc_document) {
        window.open(`${process.env.API}shopping-carts/file1/${this.shoppingCart.fields.id}/${1}/download`, '_blank')
      } else {
        this.$q.notify({
          message: 'El pedido no tiene una Orden de Compra subida',
          position: 'top',
          color: 'warning'
        })
      }
    },
    generarTicket () {
      var user = this.$store.getters['users/id']
      const uri = process.env.API + `ticket/crearTicket/${this.$route.params.id}/${user}`
      window.open(uri, '_blank')
    },
    downloadTicket () {
      if (this.shoppingCart.fields.payment_document) {
        window.open(`${process.env.API}shopping-carts/file1/${this.shoppingCart.fields.id}/${2}/download`, '_blank')
      } else {
        this.$q.notify({
          message: 'El pedido no tiene un Ticket de Pago subido',
          position: 'top',
          color: 'warning'
        })
      }
    },
    downloadFormatDate () {
      if (this.shoppingCart.fields.citation_document) {
        window.open(`${process.env.API}shopping-carts/file1/${this.shoppingCart.fields.id}/${3}/download`, '_blank')
      } else {
        this.$q.notify({
          message: 'El pedido no tiene un Formato de Cita subido',
          position: 'top',
          color: 'warning'
        })
      }
    },
    fetchFromServer () {
      this.role = this.$store.getters['users/rol']
      this.$q.loading.show()
      const id = this.$route.params.id
      api.get(`/shopping-carts/${id}`).then(({ data }) => {
        if (!data.shoppingCart) {
          this.$router.push('/requested-orders')
        } else {
          this.term = data.shoppingCart.oc_term
          this.cartStatus = data.shoppingCart.status
          if (data.shoppingCart.special_order === null || data.shoppingCart.special_order === 0) {
            this.special_order = 0
          } else {
            this.special_order = 1
          }
          if (data.shoppingCart.branchofficedestiny) {
            this.customerBranchOffice = data.shoppingCart.branchofficedestiny // { label: 'MATRIZ', value: data.shoppingCart.branchofficedestiny }
            this.destiny = true
          }
          this.shoppingCart.fields = data.shoppingCart
          if (data.shoppingCart.status === 'COTIZADO') {
            this.colorInput = 'yellow-8'
          } else if (data.shoppingCart.status === 'REMISIONADO') {
            this.colorInput = 'red-4'
          } else if (data.shoppingCart.status === 'AUTORIZADO') {
            this.colorInput = 'green'
          } else if (data.shoppingCart.status === 'PARCIAL') {
            this.colorInput = 'red-4'
          } else if (data.shoppingCart.status === 'ENTREGADO') {
            this.colorInput = 'purple-6'
          } else if (data.shoppingCart.status === 'CANCELADO') {
            this.colorInput = 'red'
          }
          if (data.shoppingCart.tax_invoice === 0) {
            this.tax_invoice = 'FACTURA'
          } else {
            this.tax_invoice = 'REMISION'
          }
          this.shoppingCart.fields.storage_id = { value: data.shoppingCart.id_storage, label: data.shoppingCart.name_storage }
          this.shoppingCart.fields.typeOrder = data.shoppingCart.tax_invoice === 1 ? 'REMISIÓN' : 'FACTURA'
          if (data.shoppingCart.loan === 0) {
            this.loan = 'NO'
          } else {
            this.loan = 'SI'
          }
          this.emailWithAttention = data.shoppingCart.email_contact !== null ? data.shoppingCart.email_contact : null
          this.labelAtention = data.shoppingCart.email_contact !== null ? 'Con atencion a:' : 'No tiene asignado ningun contacto'
          // console.log('data.shoppingCar')
          // console.log(data.shoppingCart)
          if (this.cartStatus === 'COTIZADO') {
            api.get(`/shopping-cart-in-bulk-details/shopping-cart/${id}`).then(({ data }) => {
              this.shoppingCartInBulkDetails = data.details
              if (data.canCancel === 1) {
                this.canCancel = true
              }
              // console.log(this.shoppingCartInBulkDetails)
              this.$q.loading.hide()
            })
          } else if (this.cartStatus === 'DESCUENTO') {
            api.get(`/shopping-cart-in-bulk-details/shopping-cart/${id}`).then(({ data }) => {
              this.shoppingCartInBulkDetails = data.details
              if (data.canCancel === 1) {
                this.canCancel = true
              }
              this.$q.loading.hide()
            })
          } else if (this.cartStatus === 'AUTORIZADO' || this.cartStatus === 'REMISIONADO' || this.cartStatus === 'PARCIAL') {
            api.get(`/shopping-cart-in-bulk-details/shopping-cart/${id}`).then(({ data }) => {
              console.log(data)
              this.shoppingCartInBulkDetails = data.details
              if (data.canCancel === 1) {
                this.canCancel = true
              }
              api.get('/customer-branch-offices/options').then(({ data }) => {
                this.customerBranchOfficeOptions = data.options.filter(option => (Number(option.customer) === Number(this.shoppingCart.fields.customer_id)))
                this.$q.loading.hide()
                // api.get('/drivers/options').then(({ data }) => {
                //   this.driverOptions = data.options
                //   this.$q.loading.hide()
                // })
              })
            })
          } else if (this.cartStatus === 'CANCELADO') {
            api.get(`/shopping-cart-in-bulk-details/shopping-cart/${id}`).then(({ data }) => {
              this.shoppingCartInBulkDetails = data.details
              if (data.canCancel === 1) {
                this.canCancel = true
              }
              this.$q.loading.hide()
            })
          }
        }
      })
    },
    approve () {
      this.$q.loading.show()
      api.put(`/shopping-carts/${this.$route.params.id}/approve`).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          // this.sendPDF()
          // this.$router.push('/shopping-carts')
          this.fetchFromServer()
          // this.$router.push(`/shopping-carts/orders/${this.$route.params.id}`)
          // this.$router.push('/requested-orders')
          this.$q.loading.hide()
        } else {
          this.$q.loading.hide()
        }
      })
    },
    cancelar () {
      this.$q.loading.show()
      api.put(`/shopping-carts/cancel/${this.$route.params.id}`).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$router.push('/shopping-carts')
          this.$q.loading.hide()
        } else {
          this.$q.loading.hide()
        }
      })
    },
    cancel () {
      this.confirmCancelModal = true
    },
    confirm () {
      this.text = ''
      if (this.cartStatus === 'COTIZADO') {
        this.confirmModal = true
        this.text = '¿Desea modificar el estatus de este pedido?'
        this.buttonModal = true
      } else if (this.cartStatus === 'AUTORIZADO') {
        if (this.status === true) {
          this.confirmModal = true
          this.text = '¿Desea modificar el estatus de este pedido?'
          this.buttonModal = true
        } else {
          this.confirmModal = true
          this.text = 'El pedido no se puede modificar, debido a que tiene productos remisionados.'
          this.buttonModal = false
        }
      }
    },
    editComents () {
      const params = []
      params.cartId = Number(this.$route.params.id)
      params.comment = this.shoppingCart.fields.comments
      api.post('/shopping-carts/changeComments', params).then(({ data }) => {
        if (data.result) {
          this.fetchFromServer()
          // this.$router.push('/shopping-carts')
          this.$q.notify({ message: data.message.content, position: 'top', color: (data.result ? 'positive' : 'warning') })
        } else {
          this.$q.notify({ message: data.message.content, position: 'top', color: (data.result ? 'positive' : 'warning') })
        }
      })
    },
    edit () {
      const params = []
      params.cartId = Number(this.$route.params.id)
      api.post('/shopping-carts/changeStatus', params).then(({ data }) => {
        if (data.result) {
          this.$router.push('/shopping-carts')
          this.$q.notify({
            message: data.message.content,
            position: 'top',
            color: (data.result ? 'positive' : 'warning')
          })
        } else {
          this.$q.notify({
            message: data.message.content,
            position: 'top',
            color: (data.result ? 'positive' : 'warning')
          })
        }
      })
    },
    openModaliBD (type, qty, product, shoppingCart, idDetail) {
      this.typePart = type
      this.idDetail = idDetail
      this.baleQtyComparator = qty
      this.baleQty = qty
      this.bCalcProduct = product
      this.bCalcSc = shoppingCart
      this.modalinBulkDetails = true
      this.inBulk_calculation(type, qty, product, shoppingCart)
    },
    inBulk_calculation (type, qty) {
      this.baleQty = Number(this.baleQty)// Cantidad del input
      this.baleQtyComparator = Number(this.baleQtyComparator)
      this.dataBales = []
      // this.bCalcProduct = this.bCalcProduct
      // this.bCalcSc = this.bCalcSc
      if (this.baleQty > this.baleQtyComparator) {
        this.$q.dialog({
          title: 'Error',
          message: 'El calculo excede la cantidad del pedido, por favor verifique las cantidades.',
          persistent: true
        })
      } else {
        const params = []
        params.baleQty = this.baleQty
        params.cart_id = this.bCalcSc
        params.product = this.bCalcProduct
        if (this.typePart === 1) {
          api.post('/shopping-cart-in-bulk-details/getinBulkDetails', params).then(({ data }) => {
            this.dataBales = data.details
            this.dataBalesSum = data.qtyBales
            this.dataBalesWeight = data.qty
            this.typePart = 1
          })
        }
      }
    },
    partializationinBulk () {
      const params = []
      params.idDetail = this.idDetail
      params.baleQty = this.baleQty
      params.baleQtyComparator = this.baleQtyComparator
      params.product = this.bCalcProduct
      // console.log(this.baleQtyComparator)
      // console.log(this.baleQty)
      if (this.baleQty > this.baleQtyComparator) {
        this.$q.notify({
          message: 'La Cantidad excede el limite',
          position: 'top',
          color: 'warning'
        })
      } else {
        if (this.typePart === 1) {
          api.post('/shopping-cart-in-bulk-details/partialization', params).then(({ data }) => {
            this.$q.notify({
              message: data.message.content,
              position: 'top',
              color: (data.result ? 'positive' : 'warning')
            })
            if (data.result) {
              this.modalinBulkDetails = false
              this.fetchFromServer()
            } else {
              this.modalinBulkDetails = false
            }
          })
        }
      }
    },
    openmodalGenerateInvoice () {
      this.confirmPartialORder = false
      this.generateInvoiceModal = true
    },
    openGenerateInvoiceModal () {
      if (this.partialOrder === false) {
        this.generateInvoiceModal = true
      } else {
        this.confirmPartialORder = true
        this.textv2 = 'El stock del producto no cumple con la cantidad solicitada, se realizara la remision por la cantidad existente y se creara un detalle con el faltante.'
      }
    },
    closeGenerateInvoiceModal () {
      this.generateInvoiceModal = false
      this.saleDate = null
      this.driver = null
      this.comments = null
    },
    generateInvoice () {
      if (this.canGenerateInvoice) {
        this.$v.saleDate.$reset()
        this.$v.saleDate.$touch()
        this.$v.customerBranchOffice.$reset()
        this.$v.customerBranchOffice.$touch()
        this.$v.oc_reference.$reset()
        this.$v.oc_reference.$touch()
        if (this.$v.saleDate.$error || this.$v.customerBranchOffice.$error) {
          this.saleDate = ''
          this.$q.dialog({
            title: 'Error',
            message: 'Por favor, verifique las validaciones.',
            persistent: true
          })
          return false
        }
        this.$q.loading.show()
        const params = []
        params.saleDate = this.invertDate(this.saleDate)
        params.customerBranchOfficeId = this.customerBranchOffice
        params.oc_reference = this.oc_reference
        // params.driverId = null
        params.comments = this.comments
        api.put(`/shopping-carts/${this.$route.params.id}/generate-invoice`, params).then(({ data }) => {
          console.log(data)
          this.$q.notify({
            message: data.message.content,
            position: 'top',
            color: (data.result ? 'positive' : 'warning')
          })
          if (data.result) {
            this.closeGenerateInvoiceModal()
            this.$q.loading.hide()
            this.fetchFromServer()
          } else {
            this.closeGenerateInvoiceModal()
            this.$q.loading.hide()
          }
        })
      } else {
        this.$q.notify({
          message: 'El pedido no se puede remisionar debido a que no hay stock existente',
          position: 'top',
          color: ('warning')
        })
      }
    },
    invertDate (date) {
      if (date !== null) {
        var info = date.split('/').reverse().join('-')
      }
      return info
    },
    sendPDF () {
      this.$q.loading.show()
      const params = []
      params.cartId = Number(this.$route.params.id)
      // params.emailWithAttention = this.emailWithAttention
      api.post('/shopping-carts/sendPDF', params).then(({ data }) => {
        if (data.result) {
          this.$q.loading.hide()
          this.$q.notify({
            message: 'Correo enviado exitosamente',
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
      // this.emailWithAttention = null
    },
    downloadPDF () {
      const uri = process.env.API + `shopping-carts/pdf/${this.$route.params.id}`
      window.open(uri, '_blank')
    },
    confirmSendPDF () {
      this.confirmModalPDF = true
    },
    openActions (id) {
      this.$router.push(`/storage-exits/${id}`)
    },
    afterUploadDocumentFileOC (response) {
      const data = JSON.parse(response.xhr.response)
      this.$q.notify({
        message: data.message.content,
        position: 'top',
        color: (data.result ? 'positive' : 'warning')
      })
      if (data.result) {
        this.oc_document = true
      }
    },
    cancelarRemision () {
      this.$q.dialog({
        title: 'Confirmación',
        message: '¿Desea Cancelar este pedido?',
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
        params.id = this.shoppingCart.fields.id
        api.post('/shopping-carts/cancelShopping', params).then(({ data }) => {
          this.$q.notify({
            message: data.message.content,
            position: 'top',
            color: (data.result ? 'positive' : 'warning')
          })
          this.fetchFromServer()
        })
      }).onCancel(() => {})
    }
  }
}
</script>

<style>
</style>
// C

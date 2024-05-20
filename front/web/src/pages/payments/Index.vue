<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-8">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Cuentas por cobrar" />
            </q-breadcrumbs>
          </div>
        </div>
        <div class="col-sm-4 pull-right">
          <div class="col-xs-12 col-md-4 offset-md-10">
            <div>
              <!--              <q-btn color="purple" icon="mail" @click.native="sendMail()">-->
              <!--                <q-tooltip>ENVIAR CORREO</q-tooltip>-->
              <!--              </q-btn>-->
              <q-btn v-if="haspermissionv2" class="bg-blue text-white" style="margin-left: 10px;" icon="plus_one" @click="openGenerateMultipay()">
                <q-tooltip>APLICAR MULTIPAGO</q-tooltip>
              </q-btn>
              <q-btn v-if="haspermissionv1" color="green" style="margin-left: 10px;" icon="fas fa-file-excel" @click="generateCSV()">
                <q-tooltip>GENERAR CSV</q-tooltip>
              </q-btn>
              <q-btn v-if="haspermissionv1" color="red" style="margin-left: 10px;" icon="fas fa-file-pdf" @click="generatePDF()">
                <q-tooltip>GENERAR PDF</q-tooltip>
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
        <div class="col-md-4"></div>
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
        <div class="col-md-2">
          <q-select  color="dark" bg-color="secondary" filled
                    v-model="status"
                    :options="[
                      {label: 'PAGADO', value  : 1},
                      {label: 'PENDIENTE DE PAGO', value: 2},
                      {label: 'ABONADO', value: 3},
                      {label: 'VENCIDO ABONADO', value: 6},
                      {label: 'VENCIDO', value: 7}
                      ]"
                    emit-value map-options multiple
                    @input="filterGrid()"
                    label="Estatus"
          >
            <template v-slot:option="{ itemProps, itemEvents, opt, selected, toggleOption }">
              <q-item
                v-bind="itemProps"
                v-on="itemEvents"
              >
                <q-item-section>
                  <q-item-label v-html="opt.label" ></q-item-label>
                </q-item-section>
                <q-item-section side>
                  <q-toggle :value="selected" @input="toggleOption(opt)" />
                </q-item-section>
              </q-item>
            </template>
          </q-select>
        </div>
        <div class="col-md-2">
          <q-select  color="dark" bg-color="secondary" filled
                    v-model="customer"
                    :options="filteredCustomerOptions"
                    use-input
                    hide-selected
                    fill-input
                    input-debounce="0"
                    @filter="filtrarClientes"
                    @input="filterGrid()"
                    label="Cliente"
                    emit-value map-options>
          </q-select>
        </div>
      </div>
      <div class="row bg-white" >
        <div class="col q-pa-md">
          <q-table
            flat
            bordered
            :data="data"
            :columns="columns"
            row-key="sale_date"
            :pagination.sync="pagination"
            :filter="filter"
            @request="qTableRequest"
          >
            <template v-slot:top>
              <div style="width: 100%;">
                <q-input dense debounce="300" v-model="filter" placeholder="Buscar">
                  <template v-slot:append>
                    <q-icon name="search" />
                  </template>
                </q-input>
              </div>
            </template>
            <template v-slot:body="props">
              <q-tr :props="props">
                <q-td key="id" style="text-align: center;" :props="props"><label @click="openActions(props.row.id)" style="text-decoration: underline black; cursor: pointer;">{{ props.row.id }}</label></q-td>
                <q-td key="actions" :props="props">
                  <q-btn color="primary" icon="attach_money" flat @click.native="openPaymentModal(props.row.id, props.row.status, props.row)" size="10px" v-if="props.row.status == 'ENVIADO' || props.row.status == 'PAGADO' || props.row.status == 'REMISIONADO'">
                    <q-tooltip content-class="bg-primary">Agregar pago</q-tooltip>
                  </q-btn>
                </q-td>
                <q-td key="status_invoice" style="text-align: center;" :props="props">
                  <q-chip square dense :color="props.row.status == 'REMISIONADO' ? 'primary' : (props.row.status == 'ENVIADO' ? 'warning' : (props.row.status == 'ENTREGADO' ? 'positive' : (props.row.status == 'PAGADO' ? 'positive' : (props.row.status == 'FACTURADO' ? 'purple' : (props.row.status == 'CANCELADO' ? 'negative' : 'black')))))" text-color="white">
                    {{ props.row.status }}
                  </q-chip>
                </q-td>
                <q-td key="status_payment"  :props="props"><q-chip square dense :color="props.row.color_label" text-color="white">{{ props.row.vencimiento }}</q-chip>
                </q-td>
                <q-td key="status_timbrado" :props="props"><q-chip square dense :color="colorTimbrado[props.row.status_timbrado]" text-color="white">{{ statusTimbrado[props.row.status_timbrado] }}</q-chip></q-td>
                <q-td key="expired_date" :props="props">{{ props.row.fecha_vencimiento }}</q-td>
                <q-td key="sale_date" :props="props">{{ props.row.sale_date }}</q-td>
                <q-td key="shopping_cart_id" style="text-align: center;" :props="props"><label @click="openActionsv2(props.row.shopping_cart_id)" style="text-decoration: underline black; cursor: pointer;">{{ props.row.shopping_cart_id }}</label></q-td>
                <q-td key="factura" style="text-align: left;" :props="props">{{ props.row.factura }}</q-td>
                <q-td key="customer" style="text-align: left;" :props="props">{{ props.row.customer }}</q-td>
                <q-td key="total" style="text-align: right;" :props="props">{{ `${currencyFormatter.format(props.row.cantidad_total) }` }}</q-td>
                <q-td key="abonado" style="text-align: right;" :props="props">{{ `${currencyFormatter.format(props.row.abonado)}` }}</q-td>
                <q-td key="restante" style="text-align: right;" :props="props">{{ `${currencyFormatter.format(props.row.cantidad_restante)}` }}</q-td>
              </q-tr>
            </template>
          </q-table>
        </div>
      </div>
        </div>
      </div>
    </div>
    <q-dialog v-model="paymentsModal" >
      <q-card style="min-width: 1100px;">
        <div>
          <div class="bg-primary">
            <div class="row">
              <div class="col-sm-11 text-h6" style="color:white;padding-top: 5px; padding-bottom: 5px;">&nbsp;&nbsp;Pagos de la remisión</div>
              <div class="col-sm-1 pull-right" style="color:white;padding-top: 5px; padding-bottom: 5px;" ><q-btn color="white" flat round @click.native="closeModal()" dense icon="close" /></div>
            </div>
          </div>
          <!--<div class="row q-col-gutter-md" style="padding-top:5px;">
            <div class="col-md-12 pull-right">
              <q-input
                  color="dark"
                  label="Total del pedido"
                  :value="currencyFormatter.format(total_invoice)"
                  bg-color="secondary"></q-input>
            </div>
          </div>-->
           <div class="row q-pa-md q-col-gutter-md">
            <div class="col-md-4">
              <q-input
              filled
              readonly
                  color="black"
                  label="Total del pedido:"
                  :value="currencyFormatter.format(total_invoice)"
                  bg-color="green-3"></q-input>
            </div>
            <div class="col-md-4">
                  <q-input
                  filled
                  readonly
                  color="black"
                  label="Monto Abonado:"
                  :value="currencyFormatter.format(totalAmounfromPayments)"
                  bg-color="orange-3"></q-input>
                </div>
                <div class="col-md-4" style="text-align: center;">
                  <q-input
                  filled
                  readonly
                  color="black"
                  label="Monto Restante:"
                  :value="currencyFormatter.format(total_invoice - totalAmounfromPayments)"
                  bg-color="blue-3"></q-input>
                </div>
           </div>
          <div class="row q-pa-md q-col-gutter-md">
             <!--<div class="col-md-4">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="total"
                label="Total del pedido"
              />
             </div>
             <div class="col-md-4">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                label="Monto Abonado"
              />
             </div>
             <div class="col-md-4">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                label="Monto Restante"
              />
             </div>-->
            <div class="col-md-4">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="paymentDate"
                :error="$v.paymentDate.$error"
                mask="YYYY-MM-DD HH:mm:ss"
                label="Fecha de pago"
              >
                <template v-slot:prepend>
                  <q-icon name="event" />
                </template>
                <q-popup-proxy
                  ref="paymentDate"
                  transition-show="scale"
                  transition-hide="scale"
                >
                  <div class="row">
                    <div class="col-sm-6">
                      <q-date
                        v-model="paymentDate"
                        mask="YYYY-MM-DD HH:mm:ss"
                      />
                    </div>
                    <div class="col-sm-6">
                      <q-time
                        v-model="paymentDate"
                        mask="YYYY-MM-DD HH:mm:ss"
                        @input="() => $refs.paymentDate.hide()"
                        now-btn
                      />
                    </div>
                  </div>
                </q-popup-proxy>
              </q-select>
            </div>
            <div class="col-md-4">
              <!--<q-select color="dark" bg-color="secondary" filled
                        v-model="paymentMethod"
                        :options="[
                      {label: 'EFECTIVO', value: 1},
                      {label: 'TARJETA DE CREDITO', value: 2},
                      {label: 'TRANSFERENCIA', value: 3}
                    ]"
                        emit-value map-options
                        :error="$v.paymentMethod.$error"
                        label="Método de pago"
              >
              </q-select>-->
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                map-options
                v-model="paymentMethod"
                :error="$v.paymentMethod.$error"
                label="Forma de pago"
                :options="formaPagoOptions"
              >
              </q-select>
            </div>
            <div class="col-md-4">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="qty"
                :error="$v.qty.$error"
                :rules="qtyRules"
                label="Monto a pagar"
                @keyup="isNumber($event,'qty')"
              />
            </div>
            <div class="col-md-4">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="reference"
                label="Referencia"
              />
            </div>
            <!--<div class="col-xs-12 col-sm-4 pull-right">
                      <q-file
                        v-model="file"
                        color="dark"
                        bg-color="secondary"
                        filled
                        label="Archivo"
                      >
                        <template v-slot:prepend>
          <q-icon name="cloud_upload" @click.stop />
        </template>
                      </q-file>
          </div>-->
            <div class="col-md-3">
              <q-btn :disable="disableBtnAddPayment" icon="add" color="positive" label="Agregar Pago" @click="addPayment()"/>
            </div>
          </div>
          <div v-if="disableTblPayments" class="row" >
            <div v-if="tableWithoutInvoice" class="col q-pa-md" style="height:400px; overflow-y: auto;">
              <q-table id = "newstyle" color="primary" :data="dataPayments" :columns="columnsPaymentsWithoutInvoice" row-key="sale_date" :pagination.sync="paginationPayments">
                <template v-slot:body="props">
                  <q-tr :props="props">
                    <q-td key="payment_date" style="text-align: center;" :props="props"><label>{{ props.row.payment_date }}</label></q-td>
                    <q-td key="method" style="text-align: center;" :props="props"><label>{{ props.row.method_label }}</label></q-td>
                    <q-td key="amount" style="text-align: right;" :props="props">{{ `${currencyFormatter.format(props.row.amount)}` }}</q-td>
                    <q-td key="reference" style="text-align: left;" :props="props"><label>{{ props.row.reference }}</label></q-td>
                    <q-td key="actions" style="text-align: center;" :props="props">
                      <q-btn v-if="props.row.file === null" color="green" icon="cloud_upload" flat @click.native="uploaFile(props.row)" size="10px">
                        <q-tooltip content-class="bg-green">Cargar Archivo</q-tooltip>
                      </q-btn>
                      <q-btn v-if="props.row.file !== null" color="green" icon="cloud_download" flat @click.native="getFile(props.row)" size="10px">
                        <q-tooltip content-class="bg-green">Descargar Archivo</q-tooltip>
                      </q-btn>
                      <q-btn color="negative" icon="delete" flat @click.native="deletePayment(props.row.id, props.row.remision_id)" size="10px" >
                        <q-tooltip content-class="negative">Eliminar pago</q-tooltip>
                      </q-btn>
                    </q-td>
                  </q-tr>
                </template>
              </q-table>
            </div>
            <div v-if="tableWithInvoice" class="col q-pa-md" style="height:400px; overflow-y: auto;">
              <q-table id = "newstyle" color="primary" :data="dataPayments" :columns="columnsPaymentsWithInvoice" row-key="sale_date" :pagination.sync="paginationPayments">
                <template v-slot:body="props">
                  <q-tr :props="props">
                    <q-td key="num_parcialidad" style="text-align: center;" :props="props"><label>{{ props.row.num_parcialidad }}</label></q-td>
                    <q-td key="payment_date" style="text-align: center;" :props="props"><label>{{ props.row.payment_date }}</label></q-td>
                    <q-td key="method" style="text-align: center;" :props="props"><label>{{ props.row.method_label }}</label></q-td>
                    <q-td key="amount" style="text-align: right;" :props="props">{{ `${currencyFormatter.format(props.row.amount)}` }}</q-td>
                    <q-td key="reference" style="text-align: left;" :props="props"><label>{{ props.row.reference }}</label></q-td>
                    <q-td key="status_timbrado" :props="props"><q-chip dense :icon="iconTimbrado[props.row.status_timbrado]" :color="colorTimbrado[props.row.status_timbrado]" text-color="white">{{ statusTimbrado[props.row.status_timbrado] }}<q-tooltip v-if="props.row.status_timbrado === 6">{{props.row.message}}</q-tooltip><q-tooltip v-if="props.row.status_timbrado === 7">{{props.row.message_cancelacion}}</q-tooltip></q-chip></q-td>
                    <q-td key="actions" style="text-align: center;" :props="props">
                      <q-btn v-if="props.row.file === null" color="green" icon="cloud_upload" flat @click.native="uploaFile(props.row)" size="10px">
                        <q-tooltip content-class="bg-green">Cargar Archivo</q-tooltip>
                      </q-btn>
                      <q-btn v-if="props.row.file !== null" color="green" icon="cloud_download" flat @click.native="getFile(props.row)" size="10px">
                        <q-tooltip content-class="bg-green">Descargar Archivo</q-tooltip>
                      </q-btn>
                      <q-btn v-if="props.row.status_timbrado === 0 || props.row.status_timbrado === 6" small flat @click="timbrarPago(props.row)" color="green-6" icon="fas fa-certificate">
                        <q-tooltip>Timbrar</q-tooltip>
                      </q-btn>
                      <q-btn v-if="props.row.status_timbrado === 1" small flat @click="getPdfInvoice(props.row.id_request, props.row.pdf)" color="brown" class="pull-right" icon-right="fas fa-file-pdf">
                          <q-tooltip>PDF</q-tooltip>
                      </q-btn>
                      <q-btn v-if="props.row.status_timbrado === 1" small flat @click="getCFDIInvoice(props.row.id_request)" color="green-8" class="pull-right" icon-right="fas fa-file-excel">
                        <q-tooltip>XML</q-tooltip>
                      </q-btn>
                      <q-btn v-if="props.row.status_timbrado === 1" small flat @click="cancelarTimbrado = true, pagoRow = props.row" class="pull-right" color="red-9" icon-right="cancel">
                        <q-tooltip>Cancelar</q-tooltip>
                      </q-btn>
                      <q-btn v-if="props.row.status_timbrado !== 1 && props.row.status_timbrado !== 3 && props.row.status_timbrado !== 4 && props.row.status_timbrado !== 5" color="negative" icon="delete" flat @click.native="deletePayment(props.row.id, props.row.remision_id)" size="10px" >
                        <q-tooltip content-class="negative">Eliminar pago</q-tooltip>
                      </q-btn>
                    </q-td>
                  </q-tr>
                </template>
              </q-table>
            </div>
          </div>
          <div v-else class="row" style="margin-top: 15px;">
              <div class="col-md-12 col-lg-12">
                <label style="margin-left: 35%">No hay pagos registrados para esta remision.</label>
            </div>
          </div>
        </div>
        <q-card-actions align="right">
          <br>
        </q-card-actions>
      </q-card>
    </q-dialog>
    <q-dialog v-model="multipayModal" persistent>
      <q-card style="min-width: 95%;max-height: 85vh">
      <div class="bg-primary">
        <div class="row">
          <div class="col-xs-10 col-sm-11 text-h6" style="color:white;padding-top: 5px; padding-bottom: 5px;">&nbsp;&nbsp;Multi-Abono</div>
          <div class="col-xs-2  col-sm-1 pull-right" style="color:white;padding-top: 5px; padding-bottom: 5px;" ><q-btn color="white" flat round @click.native="closeModalv2()" dense icon="close" /></div>
        </div>
        </div>
        <div class="row q-pa-md q-col-gutter-md">
          <div :class="this.size">
            <q-select color="white" bg-color="primary" filled dark
                      v-model="customerv2"
                      :options="filteredCustomerOptionsv2"
                      use-input
                      hide-selected
                      fill-input
                      input-debounce="0"
                      @filter="filtrarClientesv2"
                      @input="getpendingPayments()"
                      label="Cliente"
                      emit-value map-options>
            </q-select>
          </div>
          <div class="col-xs-12 col-sm-4 col-md-3 col-lg-2" v-if="showQty">
            <q-input
              color="white"
              bg-color="primary"
              filled
              dark
              v-model="this.maximo"
              label="Adeudo total"
              disable
            />
          </div>
          <div class="col-xs-12 col-sm-4 col-md-3 col-lg-3" v-if="showQty">
            <q-input
              color="white"
              bg-color="primary"
              filled
              dark
              mask="#.##"
              fill-mask="0"
              reverse-fill-mask
              v-model="qtyAbono"
              label="Cantidad"
              @input="divideCantidad()"
            />
          </div>
        </div>
        <div class="row q-pa-md q-col-gutter-md" v-if="showPendingPaymentsTable">
          <div class="col-xs-12 col-sm-6 col-md-6 col-lg-2">
            <q-select
              color="white"
              bg-color="primary"
              filled
              dark
              :error="$v.paymentDate.$error"
              v-model="paymentDate"
              mask="DD-MM-YYYY HH:mm:ss"
              label="Fecha de pago"
            >
              <template v-slot:prepend>
                <q-icon name="event" />
              </template>
              <q-popup-proxy
                ref="paymentDate"
                transition-show="scale"
                transition-hide="scale"
              >
                <div class="row">
                  <div class="col-sm-6">
                    <q-date
                      v-model="paymentDate"
                      mask="DD-MM-YYYY HH:mm:ss"
                    />
                  </div>
                  <div class="col-sm-6">
                    <q-time
                      v-model="paymentDate"
                      mask="DD-MM-YYYY HH:mm:ss"
                      @input="() => $refs.paymentDate.hide()"
                      now-btn
                    />
                  </div>
                </div>
              </q-popup-proxy>
            </q-select>
          </div>
          <div class="col-xs-12 col-sm-6 col-md-6 col-lg-3">
            <q-select
              color="white"
              bg-color="primary"
              filled
              dark
              map-options
              :error="$v.paymentMethod.$error"
              v-model="paymentMethod"
              label="Forma de pago"
              :options="formaPagoOptions"
            >
            </q-select>
          </div>
          <div class="col-x2-12 col-sm-6 col-md-3 col-lg-2">
            <div class="col-xs-12 col-sm-4 col-md-2">
              <q-select color="white" bg-color="primary" filled dark
                        v-model="timbrarComp"
                        :options="[
                    {label: 'Si', value: 1},
                    {label: 'No', value: 2},
                  ]"
                        emit-value map-options
                        label="¿Timbrar?"
              >
              </q-select>
            </div>
          </div>
          <div class="col-xs-12 col-sm-6 col-md-6 col-lg-4">
            <q-input
              color="white"
              bg-color="primary"
              filled
              dark
              v-model="reference"
              label="Referencia"
            />
          </div>
          <div class="col-x2-12 col-sm-6 col-md-3 col-lg-1">
            <q-btn :loading="loading6" :disable="disabletablev2" color="green" @click="openModal()" label="Abonar" style="width: 100%; height: 75%; background-color: #21ba45;"/>
          </div>
        </div>
        <div class="row q-pa-md">
          <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" v-if="showPendingPaymentsTable">
            <q-table style="max-height:50vh" color="primary" :data="dataPendingPayments" :columns="columnsPendingPayments" row-key="id" :pagination.sync="paginationPendingPayments">
                <template v-slot:body="props">
                  <q-tr :props="props">
                    <q-td key="id" style="text-align: center;" :props="props"><label>{{ props.row.id }}</label></q-td>
                    <q-td key="sale_date" style="text-align: center;" :props="props"><label>{{ props.row.sale_date }}</label></q-td>
                    <q-td key="no_factura" style="text-align: center;" :props="props"><label>{{ props.row.no_factura }}</label></q-td>
                    <q-td key="vencimiento" :props="props" v-if="props.row.vencimiento ==='-'">
                      <label>-</label>
                    </q-td>
                    <q-td key="vencimiento" :props="props" v-else>
                      <q-chip size="16px" square dense :color="props.row.color_label"  text-color="white">
                        <label style="font-size: 15px;"> {{ props.row.vencimiento }} </label>
                      </q-chip>
                    </q-td>
                    <q-td key="shopping_cart_id" style="text-align: center;" :props="props"><label>{{ props.row.shopping_cart_id }}</label></q-td>
                    <q-td key="cantidad_total" style="text-align: right;" :props="props">{{ `${currencyFormatter.format(props.row.cantidad_total)}` }}</q-td>
                    <q-td key="cantidad_restante" style="text-align: right;" :props="props">{{ `${currencyFormatter.format(props.row.cantidad_restante)}` }}</q-td>
                    <q-td key="actions" style="text-align: center;" :props="props">
                      <div class="row">
                        <div class="col-md-10">
                          <q-input v-model="props.row.nuevo_abono" @input="reCalculo(props.row)" filled label="Cantidad" style="background: #D8D1CF;"/>
                        </div>
                        <div class="col-md-1" style="margin-left:5px;">
                          <q-btn
                            flat
                            round
                            @click="clearCalculo(props.row)"
                            icon="clear"
                          />
                        </div>
                      </div>
                    </q-td>
                    <q-td key="nuevo_saldo" style="text-align: right;" :props="props">{{ `${currencyFormatter.format(props.row.nuevo_saldo)}` }}</q-td>
                  </q-tr>
                </template>
              </q-table>
          </div>
        </div>
      </q-card>
    </q-dialog>
    <q-dialog v-model="modalArchivo" persistent>
      <q-card style="min-width: 25%; !important;">
        <q-card-section class="row bg-primary text-white">
            <div class="col-xs-12 col-sm-10 text-h6">Cargar Archivo</div>
            <q-btn class="col-xs-12 col-sm-2 pull-right" icon="close" flat round dense v-close-popup />
          </q-card-section>
        <q-card-section>
      <div class="col-xs-12 col-sm-4 pull-right">
                      <q-file
                        v-model="file"
                        color="dark"
                        bg-color="secondary"
                        filled
                        label="Archivo"
                      >
                        <template v-slot:prepend>
          <q-icon name="cloud_upload" @click.stop />
        </template>
                      </q-file>
          </div>
        </q-card-section>
        <q-card-actions align="right" class="text-secondary">
            <q-btn color="positive" label="Subir archivo" @click="uploadDocumentFile()" />
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
          <div class="row  items-center">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <q-select
                  color="dark"
                  bg-color="secondary"
                  filled
                  map-options
                  emit-value
                  v-model="motivo_cancelacion"
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
        <q-card-section class="items-center" v-if="motivo_cancelacion === '01'">
          <div class="row">
              <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <q-input
                  color="dark"
                  bg-color="secondary"
                  filled
                  v-model="folio_sustituye"
                  :error="$v.folio_sustituye.$error"
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
              <q-btn flat label="Confirmar" color="white" style="background-color: #21ba45; text-align: right;" @click="cancelarPago(pagoRow)" />
            </div>
          </div>
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import api from '../../commons/api.js'
const { required, requiredIf, decimal } = require('vuelidate/lib/validators')

export default {
  name: 'IndexStorageExits',
  validations: {
    paymentMethod: { required },
    paymentDate: { required },
    qty: { required, decimal },
    folio_sustituye: { required: requiredIf(function () { return this.motivo_cancelacion === '01' }) }
  },
  data () {
    return {
      money: {
        decimal: '.',
        thousands: ',',
        prefix: '',
        suffix: '',
        precision: 2,
        masked: false
      },
      currencyFormatter: new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
      }),
      total: 0,
      monto: 0,
      restante: 0,
      modalArchivo: false,
      formatter: new Intl.NumberFormat('en-US'),
      role: null,
      tableWithInvoice: false,
      tableWithoutInvoice: true,
      interval: null,
      customer: 'TODOS',
      disableBtnAddPayment: true,
      disableTblPayments: true,
      qty: null,
      qtyAbono: null,
      file: null,
      cantidad_distribuida: 0,
      maximo: 0,
      paymentDate: `${new Date().getFullYear()}-${(new Date().getMonth() + 1).toString().padStart(2, '0')}-${(new Date().getDate()).toString().padStart(2, '0')} 12:00:00`,
      paymentMethod: null,
      status: [],
      reference: null,
      total_invoice: null,
      id_invoice: null,
      paymentsModal: false,
      saleDatev1: null,
      saleDatev2: null,
      multipayModal: false,
      timbrarComp: 1,
      loading6: false,
      size: '',
      customerv2: null,
      showQty: false,
      disabletable: true,
      disabletablev2: true,
      showPendingPaymentsTable: false,
      auxRow: [],
      customerOptionsv2: [],
      filteredCustomerOptionsv2: [],
      customerOptions: [],
      filteredCustomerOptions: [],
      formaPagoOptions: [],
      dataPaymentsMultiPayments: [],
      dataPendingPayments: [],
      pagination: {
        sortBy: 'id',
        descending: true,
        page: 1,
        rowsNumber: 0,
        rowsPerPage: 25
      },
      columns: [
        { name: 'id', align: 'center', label: '# REMISIÓN', field: 'id', sortable: true },
        { name: 'actions', align: 'center', label: 'ACCIONES', field: 'actions', sortable: false },
        { name: 'status_invoice', align: 'center', label: 'ESTATUS', field: 'status_invoice', sortable: false },
        { name: 'status_payment', align: 'center', label: 'ESTATUS DE PAGO', field: 'status_payment', sortable: true },
        // { name: 'status_timbrado', align: 'center', label: 'ESTATUS TIMBRADO', field: 'status_timbrado', sortable: true },
        { name: 'expired_date', align: 'center', label: 'FECHA DE VENCIMIENTO', field: 'expired_date', sortable: true },
        { name: 'sale_date', align: 'center', label: 'FECHA DE VENTA', field: 'sale_date', sortable: true },
        { name: 'shopping_cart_id', align: 'center', label: '# PEDIDO', field: 'shopping_cart_id', sortable: true },
        { name: 'factura', align: 'center', label: 'FACTURA', field: 'factura', sortable: true },
        { name: 'customer', align: 'center', label: 'CLIENTE', field: 'customer', sortable: true },
        { name: 'total', align: 'center', label: 'MONTO TOTAL', field: 'total', sortable: true },
        { name: 'abonado', align: 'center', label: 'ABONADO', field: 'abonado', sortable: true },
        { name: 'restante', align: 'center', label: 'RESTANTE', field: 'restante', sortable: true }
      ],
      data: [],
      filter: '',
      paginationPayments: {
        rowsPerPage: 50
      },
      paginationPendingPayments: {
        rowsPerPage: 25
      },
      columnsPendingPayments: [
        { name: 'id', align: 'center', label: 'Remisión', field: 'id', sortable: true },
        { name: 'sale_date', align: 'center', label: 'Fecha', field: 'sale_date', sortable: true },
        { name: 'no_factura', align: 'center', label: 'Factura', field: 'no_factura', sortable: true },
        { name: 'vencimiento', align: 'center', label: 'Estatus', field: 'vencimiento', sortable: true },
        { name: 'shopping_cart_id', align: 'center', label: 'Pedido', field: 'shopping_cart_id', sortable: true },
        { name: 'cantidad_total', align: 'center', label: 'Total', field: 'cantidad_total', sortable: true },
        { name: 'cantidad_restante', align: 'center', label: 'Restante', field: 'cantidad_restante', sortable: true },
        { name: 'actions', align: 'center', label: 'Abono', field: 'actions', sortable: false },
        { name: 'nuevo_saldo', align: 'center', label: 'Saldo', field: 'nuevo_saldo', sortable: false }
      ],
      columnsPaymentsWithInvoice: [
        { name: 'num_parcialidad', align: 'center', label: '#', field: 'num_parcialidad', sortable: true },
        { name: 'payment_date', align: 'center', label: 'FECHA DE PAGO', field: 'payment_date', sortable: true },
        { name: 'method', align: 'center', label: 'FORMA DE PAGO', field: 'method', sortable: true },
        { name: 'amount', align: 'center', label: 'CANTIDAD', field: 'amount', sortable: true },
        { name: 'reference', align: 'center', label: 'REFERENCIA', field: 'reference', sortable: true },
        { name: 'status_timbrado', align: 'center', label: 'ESTATUS', field: 'status_timbrado', sortable: true },
        { name: 'actions', align: 'center', label: 'ACCIONES', field: 'actions', sortable: false }
      ],
      columnsPaymentsWithoutInvoice: [
        { name: 'payment_date', align: 'center', label: 'FECHA DE PAGO', field: 'payment_date', sortable: true },
        { name: 'method', align: 'center', label: 'FORMA DE PAGO', field: 'method', sortable: true },
        { name: 'amount', align: 'center', label: 'CANTIDAD', field: 'amount', sortable: true },
        { name: 'reference', align: 'center', label: 'REFERENCIA', field: 'reference', sortable: true },
        { name: 'actions', align: 'center', label: 'ACCIONES', field: 'actions', sortable: false }
      ],
      dataPayments: [],
      fiberSaleDocumentFile: {
        fields: {
          fiberSaleId: null
        }
      },
      fiberSaleDocumentFileModal: false,
      serverUrl: process.env.API,
      statusTimbrado: ['NUEVO', 'TIMBRADO', 'CANCELADO', 'CANCELANDO', 'TIMBRANDO', 'CANCELANDO', 'ERROR', 'ERROR AL CANCELAR'],
      colorTimbrado: ['blue-6', 'green-6', 'purple-6', 'warning', 'warning', 'warning', 'red-6', 'red-6'],
      statusPayment: ['PENDIENTE DE PAGO', 'ABONADO', 'PAGADO', 'VENCIDO', 'VENCIDO ABONADO'],
      colorPayment: ['blue-6', 'warning', 'green-6', 'red-14', 'red-14'],
      iconTimbrado: ['add', 'done', 'cancel', 'fas fa-ellipsis-h', 'fas fa-ellipsis-h', 'fas fa-ellipsis-h', 'cancel', 'cancel'],
      cancelarTimbrado: false,
      CancelacionOptions: [
        { label: '01 - Comprobantes emitidos con errores con relación', value: '01' },
        { label: '02 - Comprobantes emitidos con errores sin relación.', value: '02' },
        { label: '03 - No se llevó a cabo la operación.', value: '03' },
        { label: '04 - Operación nominativa relacionada en una factura global.', value: '04' }
      ],
      pagoRow: null,
      motivo_cancelacion: '02',
      folio_sustituye: null
    }
  },
  computed: {
    fiscalFolioSustituye (val) {
      return [
        val => (this.$v.folio_sustituye.required) || 'El campo Folio que sustituye es requerido.'
      ]
    },
    qtyRules (val) {
      return [
        val => this.$v.qty.required || 'El campo Monto a pagar es requerido.',
        val => this.$v.qty.decimal || 'El campo Monto a pagar debe ser decimal.'
      ]
    },
    roleId () {
      const user = this.$store.getters['users/rol']
      return parseInt(user)
    },
    fiberSaleDocumentFileUrl () {
      return `${process.env.API}invoices/${this.fiberSaleDocumentFile.fields.fiberSaleId}/document-file`
    },
    totalAmounfromPayments () {
      let price = 0
      this.dataPayments.forEach(detail => {
        price += Number(detail.amount)
      })
      return price
    },
    haspermissionv1 () {
      let permission = false
      const propiedades = this.$store.getters['users/rol']
      if (propiedades === 1 || propiedades === 3 || propiedades === 7 || propiedades === 2 || propiedades === 20 || propiedades === 4 || propiedades === 27 || propiedades === 17 || propiedades === 22 || propiedades === 28 || propiedades === 29 || propiedades === 25) {
        permission = true
      }
      return permission
    },
    haspermissionv2 () {
      let permission = false
      const propiedades = this.$store.getters['users/rol']
      if (propiedades === 1 || propiedades === 3 || propiedades === 7 || propiedades === 2 || propiedades === 20 || propiedades === 4 || propiedades === 27 || propiedades === 17 || propiedades === 22 || propiedades === 28 || propiedades === 29 || propiedades === 25) {
        permission = true
      }
      return permission
    }
  },
  /* beforeCreate () {
    if (!(this.$store.getters['users/roles'].includes(1) || this.$store.getters['users/roles'].includes(3) || this.$store.getters['users/roles'].includes(7) || this.$store.getters['users/roles'].includes(2) || this.$store.getters['users/roles'].includes(17) || this.$store.getters['users/roles'].includes(18))) {
      this.$router.push('/')
    }
  }, */
  beforeRouteEnter (to, from, next) {
    next(vm => {
      const propiedades = vm.$store.getters['users/rol']
      console.log(propiedades)
      if (propiedades === 1 || propiedades === 3 || propiedades === 7 || propiedades === 2 || propiedades === 20 || propiedades === 4 || propiedades === 27 || propiedades === 17 || propiedades === 22 || propiedades === 28) {
        next()
      } else {
        next('/')
      }
    })
  },
  mounted () {
    this.fetchFromServer()
    this.getClients()
  },
  methods: {
    // Obtencion de datos
    fetchFromServer () {
      this.$q.loading.show()
      this.qTableRequest({
        pagination: this.pagination,
        filter: this.filter
      })
      api.get('/invoices/formaPagoOptions').then(({ data }) => {
        this.formaPagoOptions = data.options
      })
      /* api.get('/invoices').then(({ data }) => {
        this.data = data.invoices
        this.$q.loading.hide()
      }) */
    },
    isNumber (evt, input) {
      switch (input) {
        case 'folio_sustituye':
          this.folio_sustituye = this.folio_sustituye.replace(/[^0-9.]/g, '')
          this.$v.folio_sustituye.$touch()
          break
        case 'qty':
          this.qty = this.qty.replace(/[^0-9.]/g, '')
          this.$v.qty.$touch()
          break
        default:
          break
      }
    },
    filtrarClientesv2 (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.filteredCustomerOptionsv2 = this.customerOptionsv2.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    async qTableRequest (props) {
      this.pagination = props.pagination
      this.filter = props.filter
      this.data = []
      const params = []
      params.customer = this.customer
      params.status = this.status
      params.saleDatev1 = this.saleDatev1
      params.saleDatev2 = this.saleDatev2
      params.type = 0
      params.pagination = this.pagination
      params.filter = this.filter
      console.log(params)
      await api.post('/invoices/pag_payments', params).then(({ data }) => {
        this.$q.loading.hide()
        console.log(data)
        this.data = data.invoices
        this.pagination.rowsNumber = data.invoicesCount
      }).catch(error => error)
    },
    getClients () {
      api.get('/customers/options').then(({ data }) => {
        this.customerOptions = data.options
        this.customerOptionsv2 = data.options
        this.customerOptions.push({ label: 'TODOS', value: 'TODOS' })
      })
    },
    getpendingPayments (customer) {
      this.$q.loading.show()
      this.qtyAbono = 0.00
      // this.paymentDate = null
      this.paymentMethod = null
      this.reference = null
      this.data = []
      this.dataPaymentsMultiPayments = []
      const params = []
      params.customer = this.customerv2
      api.post('/invoices/getpendingPayments', params).then(({ data }) => {
        this.$q.loading.hide()
        this.showPendingPaymentsTable = true
        this.dataPendingPayments = data.pendingPayments.data
        this.maximo = (data.pendingPayments.maximo).toFixed(2)
        // alert(this.maximo)
        this.showQty = true
        this.size = 'col-xs-12 col-sm-7 col-md-6 col-lg-7'
      })
    },
    getPayments (id) {
      const params = []
      params.id = this.id_invoice
      api.get('/invoices/formaPagoOptions').then(({ data }) => {
        this.formaPagoOptions = data.options
        api.post('/invoices/dataFromInvoice', params).then(({ data }) => {
          if (data.result) {
            this.total_invoice = parseFloat(data.total_invoice)
            // this.total = parseFloat(data.total_invoice)
            this.dataPayments = data.payments
            console.log(data.payments)
            if (this.dataPayments.length > 0) {
              api.get('/invoices/keepCheckingPayments/' + this.id_invoice).then(({ data }) => {
                if (!data.result) {
                  clearInterval(this.interval)
                  this.interval = null
                } else if (this.interval === null) {
                  this.interval = setInterval(() => {
                    this.revisarPagos(this.id_invoice)
                  }, 10000)
                }
                this.disableTblPayments = true
                const amounts = parseFloat(this.totalAmounfromPayments)
                this.monto = parseFloat(this.totalAmounfromPayments)
                this.restante = (this.total_invoice - this.monto)
                const amountfromInvoice = parseFloat(this.total_invoice.toFixed(2))
                if (amountfromInvoice > amounts) {
                  this.disableBtnAddPayment = false
                  this.fetchFromServer()
                }
              })
            } else {
              this.disableTblPayments = false
            }
          }
        })
      })
    },
    // Filtrado de datos
    filterGrid () {
      this.data = []
      const params = []
      params.customer = this.customer
      params.status = this.status
      params.saleDatev1 = this.saleDatev1
      params.saleDatev2 = this.saleDatev2
      params.type = 0
      params.pagination = this.pagination
      params.filter = this.filter
      api.post('/invoices/getGridPayments', params).then(({ data }) => {
        if (data.result) {
          this.data = data.invoices
          this.pagination.rowsNumber = data.invoicesCount
        }
      })
    },
    filtrarClientes (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.filteredCustomerOptions = this.customerOptions.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    // Acciones
    openPaymentModal (id, status, row) {
      if (status === 'PAGADO') {
        this.disableBtnAddPayment = true
      } else {
        this.disableBtnAddPayment = false
      }
      if (row.status_timbrado === 1 && row.metodo_pago === 'PPD') {
        this.tableWithInvoice = true
        this.tableWithoutInvoice = false
      } else {
        this.tableWithInvoice = false
        this.tableWithoutInvoice = true
      }
      this.cleanFields()
      this.id_invoice = null
      this.id_invoice = id
      this.getPayments(this.id_invoice)
      this.paymentsModal = true
    },
    addPayment () {
      this.$v.paymentDate.$reset()
      this.$v.paymentDate.$touch()
      this.$v.paymentMethod.$reset()
      this.$v.paymentMethod.$touch()
      this.$v.qty.$reset()
      this.$v.qty.$touch()
      if (this.$v.paymentDate.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      if (this.$v.paymentMethod.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      if (this.$v.qty.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      let amounts = parseFloat(this.totalAmounfromPayments) + parseFloat(this.qty)
      amounts = parseFloat(amounts.toFixed(2))
      const amountfromInvoice = parseFloat(this.total_invoice.toFixed(2))
      if (amounts > amountfromInvoice) {
        this.$q.notify({
          message: 'La cantidad excede el total del pedido',
          position: 'top',
          color: 'negative'
        })
      } else {
        let status = 'ABONADO'
        if (amounts === amountfromInvoice) {
          status = 'PAGADO'
        }
        const params = []
        params.id = this.id_invoice
        params.qty = this.qty
        params.ref = this.reference
        params.method = this.paymentMethod.value
        params.date = this.paymentDate
        params.status = status
        const formData = new FormData()
        formData.append('fileReg', this.file)
        formData.append('id', this.id_invoice)
        formData.append('qty', this.qty)
        formData.append('ref', this.reference)
        formData.append('method', this.paymentMethod.value)
        formData.append('date', this.paymentDate)
        formData.append('status', status)
        this.$q.loading.show()
        api.file('/invoices/addPayment', formData).then(({ data }) => {
          if (data.result) {
            this.cleanFields()
            this.total_invoice = data.total_invoice
            // this.total = data.total_invoice
            this.id_invoice = data.id
            const params = []
            params.id = this.id_invoice
            this.$q.notify({
              message: 'Pago agregado correctamente.',
              position: 'top',
              color: 'positive'
            })
            this.$q.loading.hide()
            this.getPayments(data.id)
          } else {
            this.$q.notify({
              message: 'Ha ocurrido un erro al intentar agregar el pago.',
              position: 'top',
              color: 'negative'
            })
          }
        })
      }
    },
    deletePayment (id, remision) {
      const params = []
      params.id = id
      params.remision = remision
      params.status = 'ABONADO'
      api.post('/invoices/deletePayment', params).then(({ data }) => {
        if (data.result) {
          this.$q.notify({
            message: 'Pago eliminado correctamente.',
            position: 'top',
            color: 'negative'
          })
          this.getPayments(data.id)
          const amounts = parseFloat(this.totalAmounfromPayments)
          this.monto = parseFloat(this.totalAmounfromPayments)
          this.restante = (this.total_invoice - this.monto)
          const amountfromInvoice = parseFloat(this.total_invoice.toFixed(2))
          if (amountfromInvoice > amounts) {
            this.disableBtnAddPayment = false
            this.fetchFromServer()
          }
        } else {
          this.$q.notify({
            message: 'Ocurrio un error al intentar eliminar el pago.',
            position: 'top',
            color: 'negative'
          })
        }
      })
    },
    timbrarPago (row) {
      this.$q.loading.show()
      api.put('/invoices/timbrarPago/' + row.invoice_payment_id).then(({ data }) => {
        this.$q.loading.hide()
        if (data.result === 'error') {
          this.$q.notify({
            message: data.message.content,
            position: 'top',
            color: (data.result ? 'positive' : 'warning')
          })
        } else {
          this.checkPayments(row.remision_id)
        }
      }).catch(error => {
        console.error(error)
      })
    },
    closeModalv2 () {
      this.showPendingPaymentsTable = false
      this.showQty = false
      this.qty = null
      this.size = 'col-xs-12 col-md-12 col-lg-12'
      this.multipayModal = false
      this.customerv2 = null
      this.dataPaymentsMultiPayments = []
      this.fetchFromServer()
    },
    cancelarPago (row) {
      this.$v.folio_sustituye.$reset()
      this.$v.folio_sustituye.$touch()
      if (this.$v.folio_sustituye.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      const params = {}
      params.motivo_cancelacion = this.motivo_cancelacion
      params.folio_sustituye = this.folio_sustituye
      this.$q.loading.show()
      api.put('/invoices/cancelarPago/' + row.invoice_payment_id, params).then(({ data }) => {
        this.$q.loading.hide()
        this.motivo_cancelacion = '02'
        this.folio_sustituye = null
        this.pagoRow = null
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
          this.checkPayments(row.remision_id)
        }
      }).catch(error => {
        console.error(error)
      })
    },
    checkPayments (remision) {
      api.get('/invoices/keepCheckingPayments/' + remision).then(({ data }) => {
        if (!data.result) {
          clearInterval(this.interval)
          this.interval = null
        } else if (this.interval === null) {
          this.interval = setInterval(() => {
            this.revisarPagos(remision)
          }, 10000)
        }
        this.getPayments(remision)
      })
    },
    revisarPagos (remision) {
      api.put('/invoices/revisarPagos/' + remision).then(({ data }) => {
        if (data.response.status === 'done' || data.response.status === 'error' || data.response.status === true) {
          this.getPayments(remision)
        }
      }).catch(error => {
        console.error(error)
      })
    },
    openActions (id) {
      this.$router.push(`/storage-exits/${id}`)
    },
    openActionsv2 (id) {
      this.$router.push(`/shopping-carts/orders/${id}`)
    },
    // Limpieza de campos
    cleanFields () {
      this.file = null
      this.qty = ''
      this.reference = ''
      // this.paymentDate = null
      this.paymentMethod = null
      this.id_invoice = null
      this.$v.paymentDate.$reset()
      this.$v.paymentMethod.$reset()
      this.$v.qty.$reset()
    },
    closeModal () {
      this.paymentsModal = false
      this.fetchFromServer()
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
      // var url = process.env.API === 'http://api_alpez.wasp.mx/' ? 'batuta.antfarm.mx' : 'batuta.beta.antfarm.mx'
      // window.open('http://' + url + '/api/get_pdf/' + idRequest + '/0', '_blank')
    },
    generatePDF () {
      const params = []
      let varStatus = []
      if (this.status.length === 0) {
        varStatus = [99]
      } else {
        varStatus = this.status
      }
      params.customer = this.customer
      params.status = varStatus
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
      console.log(params)
      const uri = process.env.API + `invoices/getPdfFromPayments/${params.customer}/${params.status}/${params.saleDatev1}/${params.saleDatev2}`
      window.open(uri, '_blank')
    },
    generateCSV () {
      const params = []
      let varStatus = []
      if (this.status.length === 0) {
        varStatus = [99]
      } else {
        varStatus = this.status
      }
      params.customer = this.customer
      params.status = varStatus
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
      const uri = process.env.API + `invoices/getCSVFromPayments/${params.customer}/${params.status}/${params.saleDatev1}/${params.saleDatev2}`
      window.open(uri, '_blank')
    },
    getFile (row) {
      this.$q.loading.show()
      api.fileDownload(`/invoices/getfile/${row.id}`).then(({ data }) => {
        this.$q.loading.hide()
        const url = window.URL.createObjectURL(new Blob([data], { type: row.type }))
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', row.file) // or any other extension
        document.body.appendChild(link)
        link.click()
      })
    },
    uploaFile (row) {
      this.modalArchivo = true
      this.auxRow = row
      this.file = null
    },
    uploadDocumentFile () {
      const params = []
      params.id = this.auxRow.id
      params.remision_id = this.auxRow.remision_id
      const formData = new FormData()
      formData.append('fileReg', this.file)
      formData.append('id', this.auxRow.id)
      formData.append('remision_id', this.auxRow.remision_id)
      this.$q.loading.show()
      api.file('/invoices/AddFile', formData).then(({ data }) => {
        if (data.result) {
          this.$q.notify({
            message: 'Archivo Agregado correctamente.',
            position: 'top',
            color: 'positive'
          })
          this.getPayments(data.id)
          const amounts = parseFloat(this.totalAmounfromPayments)
          this.monto = parseFloat(this.totalAmounfromPayments)
          this.restante = (this.total_invoice - this.monto)
          const amountfromInvoice = parseFloat(this.total_invoice.toFixed(2))
          if (amountfromInvoice > amounts) {
            this.disableBtnAddPayment = false
            this.modalArchivo = false
            this.cleanFields()
            this.fetchFromServer()
          }
          this.$q.loading.hide()
        } else {
          this.$q.notify({
            message: 'Ocurrio un error al intentar Agregar el Documento.',
            position: 'top',
            color: 'negative'
          })
          this.modalArchivo = false
          this.$q.loading.hide()
        }
        this.modalArchivo = false
        this.$q.loading.hide()
      })
    },
    openGenerateMultipay () {
      this.multipayModal = true
      this.size = 'col-xs-12 col-md-12 col-lg-12'
    },
    divideCantidad () {
      if (parseFloat(this.qtyAbono) === 0) {
        this.disabletablev2 = true
        this.disabletable = true
      } else {
        this.disabletable = false
        this.disabletablev2 = false
      }
      if (Number(this.qtyAbono.replaceAll(',', '')) > Number(this.maximo)) {
        this.$q.notify({
          message: 'la cantidad excede el adeudo total.',
          position: 'top',
          color: 'negative'
        })
      } else {
        this.cantidad_distribuida = this.qtyAbono.replaceAll(',', '')
        this.dataPendingPayments.forEach(detail => {
          if (parseFloat(this.cantidad_distribuida) > parseFloat(detail.cantidad_restante)) {
            detail.nuevo_abono = parseFloat(detail.cantidad_restante).toFixed(2)
            this.cantidad_distribuida = this.cantidad_distribuida - detail.cantidad_restante
            detail.nuevo_saldo = parseFloat(detail.cantidad_restante - detail.nuevo_abono)
            detail.bandera_abono = false
          } else {
            detail.nuevo_abono = parseFloat(this.cantidad_distribuida).toFixed(2)
            this.cantidad_distribuida = this.cantidad_distribuida - detail.nuevo_abono
            detail.nuevo_saldo = parseFloat(detail.cantidad_restante - detail.nuevo_abono)
            detail.bandera_abono = false
          }
        })
      }
    },
    openModal () {
      this.$v.paymentDate.$reset()
      this.$v.paymentDate.$touch()
      this.$v.paymentMethod.$reset()
      this.$v.paymentMethod.$touch()
      if (this.$v.paymentDate.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      if (this.$v.paymentMethod.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      if (this.consultaiguales() === false) {
        this.$q.dialog({
          title: 'Error',
          message: 'La suma total de los abonos no es igual a la cantidad total a abonar.',
          persistent: true
        })
        return false
      }
      if (this.consultanegativos() === false) {
        this.$q.dialog({
          title: 'Error',
          message: 'No puede haber saldos nuevos negativos',
          persistent: true
        })
        return false
      }
      /* if (this.consultamayor() === false) {
        this.$q.dialog({
          title: 'Error',
          message: 'No puede haber un abono mayor a la cantidada que se debe.',
          persistent: true
        })
        return false
      } */
      this.canOpen = this.consulta()
      if (this.canOpen === true) {
        this.confirmMultipay = true
      } else {
        this.generateMultiPayments()
      }
    },
    generateMultiPayments () {
      this.canGenerateMultiPayments = this.canGenerateMultiPaymentsFunction()
      if (this.canGenerateMultiPayments === true) {
        this.loading6 = true
        const params = []
        params.customer = this.customerv2
        params.paymentDate = this.paymentDate
        params.paymentMethod = this.paymentMethod.value
        params.reference = this.reference
        params.invoiceComp = this.timbrarComp
        params.dataPayments = this.dataPaymentsMultiPayments
        api.post('/invoices/addMultiPayment', params).then(({ data }) => {
          this.dataPaymentsMultiPayments = []
          console.log(data.result)
          if (data.result) {
            this.clearModalMultiPay(data.customer)
            this.$q.notify({
              message: data.message.content,
              position: 'top',
              color: (data.result ? 'positive' : 'warning')
            })
          } else {
            this.clearModalMultiPay(params.customer)
            this.$q.notify({
              message: data.message.content,
              position: 'top',
              color: ('warning')
            })
          }
        })
      } else {
        this.loading6 = false
        this.$q.notify({
          message: 'Error en las cantidades, por favor revise.',
          position: 'top',
          color: 'negative'
        })
      }
    },
    canGenerateMultiPaymentsFunction () {
      this.canGenerateMultiPaymentsv2 = true
      this.canGenerateMultiPaymentsv3 = true
      this.suma_limites = 0
      this.dataPendingPayments.forEach(detail => {
        if (parseFloat(detail.nuevo_abono) > 0) {
          this.dataPaymentsMultiPayments.push({ id: detail.id, nuevo_abono: detail.nuevo_abono, cantidad_restante: detail.cantidad_restante })
        }
        if (parseFloat(this.nuevo_abono) > parseFloat(detail.cantidad_restante)) {
          this.canGenerateMultiPaymentsv2 = false
        }
        this.suma_limites += parseFloat(detail.nuevo_abono)
      })
      if (parseFloat(this.suma_limites).toFixed(2) > parseFloat(this.maximo) || parseFloat(this.suma_limites).toFixed(2) > parseFloat(this.qtyAbono.replaceAll(',', ''))) {
        this.canGenerateMultiPaymentsv3 = false
      }
      if (this.qtyAbono > 0) {
        if (this.canGenerateMultiPaymentsv2 === true && this.canGenerateMultiPaymentsv3 === true) {
          return true
        } else {
          return false
        }
      } else {
        return false
      }
    },
    clearModalMultiPay (customer) {
      this.loading6 = false
      this.qtyAbono = 0.00
      this.timbrarComp = 1
      this.confirmMultipay = false
      this.getpendingPayments(customer)
    },
    consulta () {
      this.newAbono = 0
      this.dataPendingPayments.forEach(detail => {
        this.newAbono += Number(detail.nuevo_abono)
      })
      if (parseFloat(this.newAbono).toFixed(2) < parseFloat(this.qtyAbono.replaceAll(',', '')).toFixed(2)) {
        return true
      } else {
        return false
      }
    },
    consultaiguales () {
      this.newAbono = 0
      this.dataPendingPayments.forEach(detail => {
        this.newAbono += Number(detail.nuevo_abono)
      })
      if (parseFloat(this.newAbono).toFixed(2) === parseFloat(this.qtyAbono.replaceAll(',', '')).toFixed(2)) {
        return true
      } else {
        return false
      }
    },
    consultamayor () {
      this.aux = 0
      this.dataPendingPayments.forEach(detail => {
        console.log(parseFloat(detail.nuevo_abono).toFixed(2))
        console.log()
        if (parseFloat(detail.nuevo_abono).toFixed(2) > parseFloat(detail.total.replaceAll(',', '')).toFixed(2)) {
          this.aux = this.aux + 1
        }
      })
      if (this.aux === 0) {
        return true
      } else {
        return false
      }
    },
    consultanegativos () {
      this.aux = 0
      this.dataPendingPayments.forEach(detail => {
        if (parseFloat(detail.nuevo_saldo).toFixed(2) < 0) {
          this.aux = this.aux + 1
        }
      })
      if (this.aux === 0) {
        return true
      } else {
        return false
      }
    },
    clearCalculo (row) {
      row.nuevo_abono = 0
      this.reCalculo(row)
    },
    reCalculo (row) {
      if (row.nuevo_abono > 0) {
        // row.nuevo_abono = 0
        row.bandera_abono = true
      }
      this.suma_modificables = 0
      this.contador_modificables = 0
      row.bandera_abono = true
      row.nuevo_saldo = row.cantidad_restante - row.nuevo_abono
      this.dataPendingPayments.forEach(detail => {
        if (detail.bandera_abono === true) {
          if (parseFloat(row.nuevo_abono) > parseFloat(row.cantidad_restante)) {
            this.$q.notify({
              message: 'La cantidad se excede.',
              position: 'top',
              color: 'negative'
            })
          } else if (parseFloat(row.nuevo_abono) > parseFloat(this.qtyAbono)) {
            detail.nuevo_abono = 0
            this.$q.notify({
              message: 'La cantidad se excede.',
              position: 'top',
              color: 'negative'
            })
          } else {
            this.suma_modificables += parseFloat(detail.nuevo_abono)
            this.contador_modificables++
          }
        }
      })
      if (parseFloat(this.suma_modificables > this.qtyAbono.replaceAll(',', ''))) {
        this.$q.notify({
          message: 'La cantidad se excede.',
          position: 'top',
          color: 'negative'
        })
      } else {
        this.diferencia = parseFloat((this.qtyAbono.replaceAll(',', '') - this.suma_modificables)).toFixed(2)
        this.dataPendingPayments.forEach(detail => {
          if (detail.bandera_abono === false) {
            if (parseFloat(this.diferencia) > parseFloat(detail.cantidad_restante)) {
              detail.nuevo_abono = parseFloat(detail.cantidad_restante).toFixed(2)
              detail.nuevo_saldo = parseFloat(detail.cantidad_restante - detail.nuevo_abono)
              this.diferencia = this.diferencia - detail.cantidad_restante
            } else {
              detail.nuevo_abono = parseFloat(this.diferencia).toFixed(2)
              detail.nuevo_saldo = parseFloat(detail.cantidad_restante - detail.nuevo_abono)
              this.diferencia = this.diferencia - detail.nuevo_abono
            }
          }
        })
      }
    }
  }
}
</script>

<style>
.changeDocumentsReturnedByDriverNotify {
  width: 100px;
}
</style>
// C

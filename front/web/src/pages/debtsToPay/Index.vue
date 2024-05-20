<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-8">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Cuentas por pagar" />
            </q-breadcrumbs>
          </div>
        </div>
        <div class="col-sm-4 pull-right">
          <div class="col-xs-12 col-md-4 offset-md-10">
            <div>
              <!--              <q-btn color="purple" icon="mail" @click.native="sendMail()">-->
              <!--                <q-tooltip>ENVIAR CORREO</q-tooltip>-->
              <!--              </q-btn>-->
              <!-- <q-btn v-if="haspermissionv1" color="green" icon="fas fa-file-excel" @click="generateCSV()">
                <q-tooltip>GENERAR CSV</q-tooltip>
              </q-btn> -->
              <q-btn color="red" class="q-mr-md q-mt-md" icon="fas fa-file-pdf" @click="generatePDF()">
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
                      {label: 'PENDIENTE', value: 0},
                      {label: 'ABONADO', value: 1},
                      {label: 'PAGADO', value: 2}
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
                    v-model="supplier"
                    :options="filteredSupplierOptions"
                    use-input
                    hide-selected
                    fill-input
                    input-debounce="0"
                    @filter="filterSuppliers"
                    @input="filterGrid()"
                    label="Proveedor"
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
            row-key="order_date"
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
                <q-td key="serial" style="text-align: center;" :props="props"><label @click="openActions(props.row.id)" style="text-decoration: underline black; cursor: pointer;">{{ props.row.serial }}</label></q-td>
                <q-td key="actions" :props="props">
                  <q-btn color="primary" icon="attach_money" flat @click.native="openPaymentModal(props.row.id, props.row.status, props.row)" size="10px" v-if="(props.row.status == 'PEDIDO' || props.row.status == 'PARCIAL' || props.row.status == 'RECIBIDO') && props.row.invoice_date !== null">
                    <q-tooltip content-class="bg-primary">Agregar pago</q-tooltip>
                  </q-btn>
                  <q-btn color="red" icon="attach_money" flat disable @click.native="openPaymentModal(props.row.id, props.row.status, props.row)" size="10px" v-else>
                    <q-tooltip content-class="bg-red">Agregar fecha de factura</q-tooltip>
                  </q-btn>
                </q-td>
                <q-td key="invoice"  :props="props">{{ props.row.reference }}</q-td>
                <q-td key="status_payment"  :props="props"><q-chip square dense :color="colorPayment[props.row.status_payment]" text-color="white">{{ statusPayment[props.row.status_payment] }}</q-chip>
                </q-td>
                <q-td key="invoice_date"  :props="props">{{ props.row.invoice_date }}</q-td>
                <!-- <q-td key="order_date" :props="props">{{ props.row.order_date }}</q-td> -->
                <q-td key="requested_date" style="text-align: center;" :props="props">{{ props.row.requested_date }}</q-td>
                <q-td key="expiration" style="text-align: center;" :props="props">{{ props.row.expiration }}</q-td>
                <q-td key="supplier" style="text-align: left;" :props="props">{{ props.row.supplier }}</q-td>
                <q-td key="totalamount" style="text-align: right;" :props="props">{{ `${currencyFormatter.format(props.row.totalamount) }` }}</q-td>
                <q-td key="abonado" style="text-align: right;" :props="props">{{ `${currencyFormatter.format(props.row.abonado)}` }}</q-td>
                <q-td key="restante" style="text-align: right;" :props="props">{{ `${currencyFormatter.format(props.row.restante)}` }}</q-td>
              </q-tr>
            </template>
          </q-table>
        </div>
      </div>
        </div>
      </div>
    </div>
    <q-dialog v-model="paymentsModal" >
      <q-card class="col-sm-12 col-xs-12" style="max-width: 1000px">
        <div>
          <div class="bg-primary">
            <div class="row">
              <div class="col-sm-11 text-h6" style="color:white;padding-top: 5px; padding-bottom: 5px;">&nbsp;&nbsp;Abonos Orden de compra {{po_serial}}</div>
              <div class="col-sm-1 pull-right" style="color:white;padding-top: 5px; padding-bottom: 5px;" ><q-btn color="white" flat round @click.native="closeModal()" dense icon="close" /></div>
            </div>
          </div>
          <div class="row q-col-gutter-md q-pa-md">
            <!-- Quité col-lg-12 -->
              <div class="col-xs-12 col-md-4">
              <q-input
                color="dark"
                bg-color="green-3"
                filled
                readonly
                v-model="totalAmount"
                label="Total del pedido"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-dollar-sign" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-md-4">
              <q-input
                color="dark"
                bg-color="orange-3"
                filled
                readonly
                v-model="abonadoAmount"
                label="Monto abonado"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-dollar-sign" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-md-4">
              <q-input
                color="dark"
                bg-color="blue-3"
                filled
                readonly
                v-model="restanteAmount"
                label="Monto restante"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-dollar-sign" />
                </template>
              </q-input>
            </div>
            <!-- <div class="col-md-3 col-xs-12 col-lg-3">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                :options="[
                { label: 'SI', value: 1 },
                { label: 'NO', value: 0 }
                ]"
                v-model="bankRequired"
                label="Bancos"
                emit-value
                map-options
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-university" />
                </template>
              </q-select>
            </div> -->
          </div>
          <q-separator/>
          <div class="row q-col-gutter-x-sm q-col-gutter-y-xs q-px-md">
            <div class="col-md-4 col-xs-6 col-lg-3">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                :rules="paymentDateRules"
                v-model="paymentDate"
                :error="$v.paymentDate.$error"
                mask="DD/MM/YYYY"
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
                    <div class="col-sm-12 col-xs-6">
                      <q-date
                        :locale="myLocale"
                        v-model="paymentDate"
                        mask="DD/MM/YYYY"
                        @input="() => $refs.paymentDate.hide()"
                        today-btn
                      />
                    </div>
                </q-popup-proxy>
              </q-select>
            </div>
            <div class="col-md-4 col-xs-6 col-lg-3">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                map-options
                :rules="paymentMethodRules"
                v-model="paymentMethod"
                :error="$v.paymentMethod.$error"
                label="Forma de pago"
                :options="filteredformaPagoOptions"
                @filter="filterPaymentMethods"
                use-input
                input-debounce="0"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-file-invoice" />
                </template>
              </q-select>
            </div>
            <div class="col-md-4 col-xs-6 col-lg-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="qty"
                :rules="paymentQtyRules"
                :error="$v.qty.$error"
                label="Monto a pagar"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-dollar-sign" />
                </template>
              </q-input>
            </div>
            <!-- <div class="col-xs-12 col-sm-6" v-show="this.bankRequired == 1">
              <q-select
                readonly
                color="dark"
                bg-color="secondary"
                filled
                :options="accountOptions"
                v-model="account"
                label="Cuenta"
                emit-value
                map-options
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-building" />
                </template>
              </q-select>
            </div> -->
            <!-- <div class="col-xs-12 col-sm-3" v-show="this.bankRequired == 1">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                :options="filteredOutputOptions"
                v-model="output"
                label="Rubro"
                :rules="outputRules"
                :error="$v.output.$error"
                @filter="filterOutputs"
                use-input
                emit-value
                map-options
                input-debounce="0"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-building" />
                </template>
              </q-select>
            </div> -->
            <!-- <div class="col-md-3 col-xs-6 col-lg-3">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                map-options
                emit-value
                v-model="bank"
                label="Banco"
                :options="bankOptions"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-building" />
                </template>
              </q-select>
            </div> -->
            <div class="col-md-4 col-xs-6 col-lg-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="reference"
                label="Referencia"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-signature" />
                </template>
              </q-input>
            </div>
            <!-- <div class="col-md-3 col-xs-6 col-lg-3">
          <q-uploader
          style="width: 100%"
          text-color="dark"
          :url="fileDocumentUrl"
            method="POST"
            max-files="1"
            flat
            label="Evidencia"
            no-thumbnails
            hide-upload-btn
            color="secondary"
            ref="fileDocumentRef"
            :headers="[{name: 'Authorization', value: 'Bearer ' + this.JWT}]"
            @uploaded="afterUploadDocumentFile"
          />
          </div> -->
            <div class="col-md-8 col-sm-12 col-xs-12 pull-left">
              <q-btn  icon="fas fa-credit-card" :disable="disableBtnAddPayment" color="positive" label="Agregar Pago" @click="addPayment()"/>
            </div>
          </div>
          <div v-if="disableTblPayments" class="row" >
            <div v-if="tableWithoutInvoice" class="col q-pa-md" style="overflow-y: auto;">
              <q-table id = "newstyle" color="primary" :data="dataPayments" :columns="columnsPaymentsWithoutInvoice" row-key="sale_date" :pagination.sync="paginationPayments">
                <template v-slot:body="props">
                  <q-tr :props="props">
                    <q-td key="payment_date" style="text-align: center;" :props="props"><label>{{ props.row.payment_date }}</label></q-td>
                    <q-td key="method" style="text-align: center;" :props="props"><label>{{ props.row.method_label }}</label></q-td>
                    <!-- <q-td key="account" style="text-align: center;" :props="props"><label>{{ props.row.account }}</label></q-td>
                    <q-td key="output" style="text-align: center;" :props="props"><label>{{ props.row.output }}</label></q-td> -->
                    <q-td key="amount" style="text-align: right;" :props="props">{{ `${currencyFormatter.format(props.row.amount)}` }}</q-td>
                    <q-td key="reference" style="text-align: left;" :props="props"><label>{{ props.row.reference }}</label></q-td>
                    <!-- <q-td key="bank" style="text-align: left;" :props="props"><label>{{ props.row.bank }}</label></q-td>
                    <q-td key="movement_id" :props="props">
                      <label @click="openMovement(props.row.movement_id)" style="text-decoration: underline; cursor: pointer; padding-left: 10px;">{{ props.row.movement_id }}</label>
                    </q-td> -->
                    <q-td key="actions" style="text-align: center;" :props="props">
                      <q-btn v-if="props.row.document_id === null" color="green" icon="fas fa-cloud-upload-alt" flat @click.native="uploadFile(props.row)" size="10px">
                        <q-tooltip content-class="bg-green">Subir referencia de pago</q-tooltip>
                      </q-btn>
                      <q-btn color="blue" icon="fas fa-eye" flat size="10px" v-if="props.row.document_id !== null">
                            <q-menu>
                              <q-list link separator class="scroll" style="min-width: 100px">
                              <q-item :clickable="1 > 0" @click.native="showDocumentFile(props.row)">
                                  <q-item-section>Ver</q-item-section>
                                </q-item>
                                <q-item :clickable="1 > 0" @click.native="downloadDocumentFile(props.row)">
                                  <q-item-section>Descargar</q-item-section>
                                </q-item>
                                <q-item :clickable="1 > 0" @click.native="deleteFilePayment(props.row)">
                                  <q-item-section>Eliminar</q-item-section>
                                </q-item>
                              </q-list>
                            </q-menu>
                          </q-btn>
                      <q-btn color="negative" icon="delete" flat @click.native="deletePayment(props.row.id, props.row.po_id)" size="10px" >
                        <q-tooltip content-class="negative">Eliminar pago</q-tooltip>
                      </q-btn>
                    </q-td>
                  </q-tr>
                </template>
              </q-table>
            </div>
            <div v-if="tableWithInvoice" class="col q-pa-md" style="overflow-y: auto;">
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
                      <q-btn v-if="props.row.file !== ''" color="green" icon="fas fa-download" flat @click.native="getFile(props.row)" size="10px">
                        <q-tooltip content-class="bg-green">Descargar</q-tooltip>
                      </q-btn>
                      <q-btn v-if="props.row.status_timbrado === 0 || props.row.status_timbrado === 6" small flat @click="timbrarPago(props.row)" color="green-6" icon="fas fa-certificate">
                        <q-tooltip>Timbrar</q-tooltip>
                      </q-btn>
                      <q-btn v-if="props.row.status_timbrado === 1" small flat @click="getPdfInvoice(props.row.id_request)" color="brown" class="pull-right" icon-right="fas fa-file-pdf">
                          <q-tooltip>PDF</q-tooltip>
                      </q-btn>
                      <q-btn v-if="props.row.status_timbrado === 1" small flat @click="getCFDIInvoice(props.row.id_request)" color="green-8" class="pull-right" icon-right="fas fa-file-excel">
                        <q-tooltip>XML</q-tooltip>
                      </q-btn>
                      <q-btn v-if="props.row.status_timbrado === 1" small flat @click="cancelarPago(props.row)" class="pull-right" color="red-9" icon-right="cancel">
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
    <!-- <q-dialog v-model="modalUploadFile" persistent>
      <q-card style="width: 400px; height: 300px">
        <q-card-section class="row">
          <div class="col-xs-12 col-sm-10 text-h6">Subir archivo</div>
          <q-btn class="col-xs-12 col-sm-2 pull-right" icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-card-section>
            <q-uploader
              :url="fileDocumentUrl"
              :headers="[{name: 'Authorization', value: token}]"
              method="POST"
              ref="fileDocumentRef"
              hide-upload-btn
              @uploaded="afterUploadDocumentFile"
            />
        </q-card-section>
        <q-card-actions align="right" class="text-secondary">
            <q-btn flat label="Subir archivo" @click="uploadDocumentFileRFP()" />
          </q-card-actions>
      </q-card>
    </q-dialog> -->
    <q-dialog v-model="modalUploadFile" persistent>
        <q-card>
          <q-card-section class="row">
            <div class="col-xs-12 col-sm-10 text-h6">Subir archivo</div>
            <q-btn class="col-xs-12 col-sm-2 pull-right" icon="close" flat round dense v-close-popup />
          </q-card-section>
          <q-card-section>
            <q-uploader
              :url="fileDocumentUrl"
              :headers="[{name: 'Authorization', value: token}]"
              method="POST"
              ref="fileDocumentRef"
              hide-upload-btn
              @uploaded="afterUploadDocumentFile"
            />
          </q-card-section>
          <q-card-actions align="right" class="text-secondary">
            <q-btn flat label="Subir archivo" @click="uploadDocumentFileRFP()" />
          </q-card-actions>
        </q-card>
      </q-dialog>
    <!-- <q-dialog v-model="modalUploadFile" persistent>
        <q-card>
          <q-card-section class="row">
            <div class="col-xs-12 col-sm-10 text-h6">Archivo</div>
            <q-btn class="col-xs-12 col-sm-2 pull-right" icon="close" flat round dense v-close-popup />
          </q-card-section>
          <q-card-section>
            <q-uploader
            style="width: 100%"
              :url="fileDocumentUrl"
              :headers="[{name: 'Authorization', value: token}]"
              method="POST"
              ref="fileDocumentRef"
              hide-upload-btn
              @uploaded="afterUploadDocumentFile"
            />
          </q-card-section>
          <q-card-actions align="right" class="text-secondary">
            <q-btn flat label="Subir archivo" @click="uploadDocumentFilePrices()" />
          </q-card-actions>
        </q-card>
      </q-dialog> -->
  </q-page>
</template>

<script>
import api from '../../commons/api.js'
const { required, decimal, requiredIf } = require('vuelidate/lib/validators')

export default {
  name: 'IndexStorageExits',
  validations: {
    paymentMethod: { required },
    paymentDate: { required },
    qty: { required, decimal },
    account: { required: requiredIf(function () { return this.bankRequired === 1 }) },
    output: { required: requiredIf(function () { return this.bankRequired === 1 }) }
    // bank: { required }
  },
  data () {
    return {
      info: {
        image: []
      },
      idPo: null,
      refPay: null,
      modalUploadFile: false,
      accountOptions: [],
      outputOptions: [],
      filteredOutputOptions: [],
      documentFileModal: false,
      documentName: null,
      documentId: null,
      myLocale: {
        /* starting with Sunday */
        days: 'Domingo_Lunes_Martes_Miércoles_Jueves_Viernes_Sábado'.split('_'),
        daysShort: 'Dom_Lun_Mar_Mié_Jue_Vie_Sáb'.split('_'),
        months: 'Enero_Febrero_Marzo_Abril_Mayo_Junio_Julio_Agosto_Septiembre_Octubre_Noviembre_Diciembre'.split('_'),
        monthsShort: 'Ene_Feb_Mar_Abr_May_Jun_Jul_Ago_Sep_Oct_Nov_Dic'.split('_'),
        firstDayOfWeek: 1
      },
      currencyFormatter: new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
      }),
      formatter: new Intl.NumberFormat('en-US'),
      role: null,
      tableWithInvoice: false,
      tableWithoutInvoice: true,
      interval: null,
      supplier: 'TODOS',
      disableBtnAddPayment: true,
      disableTblPayments: true,
      qty: null,
      file: null,
      bank: null,
      paymentDate: null,
      paymentMethod: null,
      status: [],
      reference: null,
      output: null,
      account: null,
      total_invoice: null,
      id_po: null,
      po_serial: null,
      paymentsModal: false,
      saleDatev1: null,
      saleDatev2: null,
      supplierOptions: [],
      bankOptions: [],
      filteredSupplierOptions: [],
      formaPagoOptions: [],
      filteredformaPagoOptions: [],
      pagination: {
        sortBy: 'id',
        descending: true,
        page: 1,
        rowsNumber: 0,
        rowsPerPage: 25
      },
      columns: [
        { name: 'serial', align: 'center', label: 'FOLIO', field: 'serial', sortable: true },
        { name: 'actions', align: 'center', label: 'ACCIONES', field: 'actions', sortable: false },
        { name: 'invoice', align: 'center', label: '# REFERENCIA/FACTURA', field: 'invoice', sortable: true },
        { name: 'status_payment', align: 'center', label: 'ESTATUS', field: 'status_payment', sortable: true },
        { name: 'invoice_date', align: 'center', label: 'FACTURA', field: 'invoice_date', sortable: true },
        // { name: 'order_date', align: 'center', label: 'FECHA DE PEDIDO', field: 'order_date', sortable: true },
        { name: 'requested_date', align: 'center', label: 'ARRIBO', field: 'requested_date', sortable: true },
        { name: 'expiration', align: 'center', label: 'VENCIMIENTO', field: 'expiration', sortable: true },
        { name: 'supplier', align: 'center', label: 'PROVEEDOR', field: 'supplier', sortable: true },
        { name: 'totalamount', align: 'center', label: 'MONTO TOTAL', field: 'totalamount', sortable: true },
        { name: 'abonado', align: 'center', label: 'ABONADO', field: 'abonado', sortable: true },
        { name: 'restante', align: 'center', label: 'RESTANTE', field: 'restante', sortable: true }
      ],
      data: [],
      filter: '',
      paginationPayments: {
        rowsPerPage: 50
      },
      columnsPaymentsWithInvoice: [
        { name: 'num_parcialidad', align: 'center', label: '#', field: 'num_parcialidad', sortable: true },
        { name: 'payment_date', align: 'center', label: 'FECHA', field: 'payment_date', sortable: true },
        { name: 'method', align: 'center', label: 'FORMA', field: 'method', sortable: true },
        { name: 'amount', align: 'center', label: 'CANTIDAD', field: 'amount', sortable: true },
        { name: 'reference', align: 'center', label: 'REFERENCIA', field: 'reference', sortable: true },
        { name: 'status_timbrado', align: 'center', label: 'ESTATUS', field: 'status_timbrado', sortable: true },
        { name: 'actions', align: 'center', label: 'ACCIONES', field: 'actions', sortable: false }
      ],
      columnsPaymentsWithoutInvoice: [
        { name: 'payment_date', align: 'center', label: 'FECHA DE PAGO', field: 'payment_date', sortable: true },
        { name: 'method', align: 'center', label: 'FORMA DE PAGO', field: 'method', sortable: true },
        /* { name: 'account', align: 'center', label: 'CUENTA', field: 'account', sortable: true },
        { name: 'output', align: 'center', label: 'RUBRO', field: 'output', sortable: true }, */
        { name: 'amount', align: 'center', label: 'CANTIDAD', field: 'amount', sortable: true },
        { name: 'reference', align: 'center', label: 'REFERENCIA', field: 'reference', sortable: true },
        /* { name: 'bank', align: 'center', label: 'BANCO', field: 'bank', sortable: true }, */
        /* { name: 'movement_id', align: 'center', label: 'MOVIMIENTO', field: 'movement_id', sortable: true }, */
        { name: 'actions', align: 'center', label: 'ACCIONES', field: 'actions', sortable: false }
      ],
      dataPayments: [],
      fiberSaleDocumentFile: {
        fields: {
          fiberSaleId: null
        }
      },
      bankRequired: 1,
      fiberSaleDocumentFileModal: false,
      serverUrl: process.env.API,
      statusTimbrado: ['Nuevo', 'Timbrado', 'Cancelado', 'Cancelando', 'Timbrando', 'Cancelando', 'Error', 'Error al cancelar'],
      colorTimbrado: ['blue-6', 'green-6', 'purple-6', 'warning', 'warning', 'warning', 'red-6', 'red-6'],
      statusPayment: ['PENDIENTE DE PAGO', 'ABONADO', 'PAGADO'],
      colorPayment: ['blue-6', 'warning', 'green-6'],
      iconTimbrado: ['add', 'done', 'cancel', 'fas fa-ellipsis-h', 'fas fa-ellipsis-h', 'fas fa-ellipsis-h', 'cancel', 'cancel']
    }
  },
  computed: {
    roleId () {
      const user = this.$store.getters['users/rol']
      return parseInt(user)
    },
    token () {
      const token = 'Bearer ' + localStorage.getItem('JWT')
      return token
    },
    abonadoAmount () {
      let abonado = 0
      if (this.totalAmounfromPayments != null) {
        abonado = this.totalAmounfromPayments
      }
      return this.currencyFormatter.format(abonado)
    },
    restanteAmount () {
      let restante = 0
      if (this.total_invoice != null && this.totalAmounfromPayments != null) {
        restante = this.total_invoice - this.totalAmounfromPayments
      }
      return this.currencyFormatter.format(restante)
    },
    totalAmount () {
      let total = 0
      if (this.total_invoice != null) {
        total = this.total_invoice
      }
      return this.currencyFormatter.format(total)
    },
    JWT () {
      return localStorage.getItem('JWT')
    },
    fileDocumentUrl () {
      return `${process.env.API}purchase-orders/uploadPaymentFile/${this.refPay}`
    },
    accountRules (val) {
      return [
        val => (this.$v.account.required) || 'El campo Cuenta es requerido.'
      ]
    },
    outputRules (val) {
      return [
        val => (this.$v.output.required) || 'El campo Rubro es requerido.'
      ]
    },
    paymentMethodRules (val) {
      return [
        val => (this.$v.paymentMethod.required) || 'El campo Forma de pago es requerido.'
      ]
    },
    bankRules (val) {
      return [
        val => (this.$v.bank.required) || 'El campo Banco es requerido.'
      ]
    },
    paymentDateRules (val) {
      return [
        val => (this.$v.paymentDate.required) || 'El campo Fecha de pago es requerido.'
      ]
    },
    paymentQtyRules (val) {
      return [
        val => (this.$v.qty.required) || 'El campo Monto a pagar es requerido.',
        val => (this.$v.qty.decimal) || 'El campo Monto a pagar debe ser númerico.'
      ]
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
      if (this.$store.getters['users/roles'].includes(1) || this.$store.getters['users/roles'].includes(3) || this.$store.getters['users/roles'].includes(7) || this.$store.getters['users/roles'].includes(17) || this.$store.getters['users/roles'].includes(18)) {
        permission = true
      }
      return permission
    }
  },
  /* beforeCreate () {
    if (!(this.$store.getters['users/roles'].includes(1) || this.$store.getters['users/roles'].includes(3) || this.$store.getters['users/roles'].includes(7) || this.$store.getters['users/roles'].includes(2) || this.$store.getters['users/roles'].includes(17) || this.$store.getters['users/roles'].includes(18) || this.$store.getters['users/roles'].includes(22) || this.$store.getters['users/roles'].includes(26) || this.$store.getters['users/roles'].includes(24))) {
      this.$router.push('/')
    }
  }, */
  beforeRouteEnter (to, from, next) {
    next(vm => {
      const propiedades = vm.$store.getters['users/rol']
      console.log(propiedades)
      if (propiedades === 1 || propiedades === 3 || propiedades === 7 || propiedades === 2 || propiedades === 20 || propiedades === 4 || propiedades === 27 || propiedades === 17 || propiedades === 22 | propiedades === 29 | propiedades === 28 | propiedades === 25) {
        next()
      } else {
        next('/')
      }
    })
  },
  async mounted () {
    /* await this.getAccount()
    await this.getOutputs() */
    await this.getClients()
    // await this.getBanks()
    await this.fetchFromServer()
  },
  methods: {
    deleteFilePayment (row) {
      api.put(`/purchase-orders/deleteFilePayment/${row.id}`).then(({ data }) => {
        if (data.result) {
          this.getPayments(this.idPo)
        }
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
      })
    },
    showDocumentFile (row) {
      this.$q.loading.show()
      // this.documentName = row.name
      this.seeImage = true
      api.fileDownload(`/documents/getfile/${row.document_id}`).then(({ data }) => {
        this.info.image = data
        console.log(data)
        const url = window.URL.createObjectURL(new Blob([data], { type: row.mimetype }))
        console.log('aaa')
        console.log(url)
        window.open(url, '_blank')
        this.$q.loading.hide()
      })
      this.info.image = null
    },
    downloadDocumentFile (row) {
      this.$q.loading.show()
      api.fileDownload(`/documents/getfile/${row.document_id}`).then(({ data }) => {
        this.$q.loading.hide()
        this.info.image = data
        const url = window.URL.createObjectURL(new Blob([data], { type: row.mimetype }))
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', row.filename) // or any other extension
        document.body.appendChild(link)
        link.click()
      })
    },
    showDocumentFilePallet (iddoc) {
      api.get(`documents/getDocumentOfPay/${iddoc.document_id}`).then(({ data }) => {
        if (data.result) {
          window.open(`${this.serverUrl}public/assets/documentspay/${data.documents[0].id}`, '_blank')
        }
      })
    },
    uploadDocumentFileRFP () {
      this.$refs.fileDocumentRef.upload()
      // this.getPayments(data.id)
    },
    openMovement (movementId) {
      this.$router.push(`trade-movements/${movementId}`)
    },
    filterPaymentMethods (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.filteredformaPagoOptions = this.formaPagoOptions.filter(v => v.label.toLowerCase().indexOf(needle) > -1 && v.label !== 'TODOS')
      })
    },
    filterOutputs (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.filteredOutputOptions = this.outputOptions.filter(v => v.label.toLowerCase().indexOf(needle) > -1 && v.label !== 'TODOS')
      })
    },
    // filterAccount (val, update, abort) {
    //   update(() => {
    //     const needle = val.toLowerCase()
    //     this.filteredAccountOptions = this.accountOptions.filter(v => v.label.toLowerCase().indexOf(needle) > -1 && v.label !== 'TODOS')
    //   })
    // },
    getAccount () {
      this.$q.loading.show()
      api.get('/account-trade/options').then(({ data }) => {
        this.accountOptions = data.options
        this.$q.loading.hide()
      })
    },
    getOutputs () {
      this.$q.loading.show()
      api.get('/output_type/options').then(({ data }) => {
        this.outputOptions = data.options
        this.$q.loading.hide()
      })
    },
    openUploadFileModal () {
      this.documentFileModal = true
    },
    afterUploadDocumentFile (response) {
      const data = JSON.parse(response.xhr.response)
      this.$q.notify({
        message: data.message.content,
        position: 'top',
        color: (data.result ? 'positive' : 'warning')
      })
      if (data.result) {
        this.modalUploadFile = false
        this.getPayments(this.idPo)
      }
    },
    invertDate (date) {
      if (date !== null) {
        var info = date.split('/').reverse().join('-')
      }
      return info
    },
    // Obtencion de datos
    fetchFromServer () {
      this.$q.loading.show()
      this.qTableRequest({
        pagination: this.pagination,
        filter: this.filter
      })
      /* api.get('/invoices').then(({ data }) => {
        this.data = data.invoices
        this.$q.loading.hide()
      }) */
    },
    async qTableRequest (props) {
      this.pagination = props.pagination
      this.filter = props.filter
      this.data = []
      const params = []
      params.supplier = this.supplier
      params.status = this.status
      params.saleDatev1 = this.saleDatev1
      params.saleDatev2 = this.saleDatev2
      params.type = 0
      params.pagination = this.pagination
      params.filter = this.filter
      // this.$clg(params)
      await api.post('/purchase-orders/getPurchaseGridPayments', params).then(({ data }) => {
        this.$q.loading.hide()
        console.log(data)
        this.data = data.purchases
        this.pagination.rowsNumber = data.purchasesCount
      }).catch(error => error)
    },
    getClients () {
      api.get('/suppliers/options').then(({ data }) => {
        this.supplierOptions = data.options
        this.supplierOptions.unshift({ label: 'TODOS', value: 'TODOS' })
      })
    },
    getBanks () {
      api.get('/bank-accounts/options').then(({ data }) => {
        this.bankOptions = data.options
      })
    },
    getPayments (id) {
      const params = []
      params.id = this.id_po
      api.get('/invoices/formaPagoOptions').then(({ data }) => {
        this.formaPagoOptions = data.options
        api.post('/purchase-orders/dataFromPurchaseOrder', params).then(({ data }) => {
          if (data.result) {
            console.log('data')
            console.log(data)
            this.total_invoice = parseFloat(data.total_amount)
            this.dataPayments = data.payments
            console.log(this.dataPayments)
            this.account = data.account
            if (this.dataPayments.length > 0) {
              this.disableTblPayments = true
              const amounts = parseFloat(this.totalAmounfromPayments)
              const amountfromInvoice = parseFloat(this.total_invoice.toFixed(2))
              if (amountfromInvoice > amounts) {
                this.disableBtnAddPayment = false
                this.fetchFromServer()
              }
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
      params.supplier = this.supplier
      params.status = this.status
      params.saleDatev1 = this.saleDatev1
      params.saleDatev2 = this.saleDatev2
      params.type = 0
      params.pagination = this.pagination
      params.filter = this.filter
      api.post('/purchase-orders/getGridPayments', params).then(({ data }) => {
        if (data.result) {
          console.log(data)
          this.data = data.purchases
          this.pagination.rowsNumber = data.purchasesCount
        }
      })
    },
    filterSuppliers (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.filteredSupplierOptions = this.supplierOptions.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    // Acciones
    openPaymentModal (id, status, row) {
      if (row.status_payment === 2) {
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
      this.id_po = null
      this.id_po = id
      this.po_serial = row.serial
      this.getPayments(this.id_po)
      this.paymentsModal = true
    },
    addPayment () {
      this.$v.paymentDate.$reset()
      this.$v.paymentDate.$touch()
      this.$v.paymentMethod.$reset()
      this.$v.paymentMethod.$touch()
      this.$v.qty.$reset()
      this.$v.qty.$touch()
      /* this.$v.account.$reset()
      this.$v.account.$touch()
      this.$v.output.$reset()
      this.$v.output.$touch() */
      if (this.$v.paymentDate.$error || this.$v.paymentMethod.$error || this.$v.qty.$error) {
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
        params.fileReg = this.file
        params.id = this.id_po
        params.qty = this.qty
        params.ref = this.reference
        params.account = this.account
        params.output = this.output
        params.method = this.paymentMethod.value
        params.date = this.invertDate(this.paymentDate)
        params.status = status
        params.bank_account_id = this.bank
        params.save_payment = this.bankRequired
        this.$q.loading.show()
        api.post('/purchase-orders/addPayment', params).then(({ data }) => {
          if (data.result) {
            // this.$refs.fileDocumentRef.upload()
            this.cleanFields()
            this.total_invoice = data.total_invoice
            this.id_po = data.id
            const params = []
            params.id = this.id_po
            this.$q.notify({
              message: data.message.content,
              position: 'top',
              color: data.result ? 'positive' : 'negative'
            })
            /* if (data.message_bank) {
              this.$q.notify({
                message: data.message_bank.content,
                position: 'top',
                color: 'positive'
              })
            } */
            this.$q.loading.hide()
            this.getPayments(data.id)
            if (amounts === amountfromInvoice) {
              this.paymentsModal = false
              this.fetchFromServer()
            }
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
    deletePayment (id, orderid) {
      const params = []
      params.id = id
      params.po_id = orderid
      params.status = 'ABONADO'
      api.post('/purchase-orders/deletePayment', params).then(({ data }) => {
        if (data.result) {
          this.$q.notify({
            message: 'Pago eliminado correctamente.',
            position: 'top',
            color: 'negative'
          })
          this.getPayments(data.id)
          const amounts = parseFloat(this.totalAmounfromPayments)
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
    cancelarPago (row) {
      this.$q.loading.show()
      api.put('/invoices/cancelarPago/' + row.invoice_payment_id).then(({ data }) => {
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
    openActions (id) {
      this.$router.push(`/purchase-orders/${id}`)
    },
    openActionsv2 (id) {
      this.$router.push(`/shopping-carts/orders/${id}`)
    },
    // Limpieza de campos
    cleanFields () {
      this.file = null
      this.qty = ''
      this.reference = ''
      this.paymentDate = null
      this.paymentMethod = null
      this.id_po = null
      this.bank = null
      this.account = null
      this.output = null
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
    getPdfInvoice (idRequest) {
      this.$q.loading.show()
      api.fileDownload(`/invoices/pdfi/${idRequest}`).then(({ data }) => {
        const url = window.URL.createObjectURL(new Blob([data], { type: 'application/pdf' }))
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', idRequest + '.pdf')
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
      params.supplier = this.supplier
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
      const uri = process.env.API + `purchase-orders/getPdfFromPurchasePayments/${params.supplier}/${params.status}/${params.saleDatev1}/${params.saleDatev2}`
      this.$q.loading.show()
      api.fileDownload(uri).then(({ data }) => {
        const url = window.URL.createObjectURL(new Blob([data], { type: 'application/pdf' }))
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', 'reporte_pagos.pdf')
        document.body.appendChild(link)
        this.$q.loading.hide()
        link.click()
      })
    },
    generateCSV () {
      const params = []
      let varStatus = []
      if (this.status.length === 0) {
        varStatus = [99]
      } else {
        varStatus = this.status
      }
      params.supplier = this.supplier
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
      const uri = process.env.API + `invoices/getCSVFromPayments/${params.supplier}/${params.status}/${params.saleDatev1}/${params.saleDatev2}`
      window.open(uri, '_blank')
    },
    uploadFile (idpay) {
      this.refPay = idpay.id
      this.modalUploadFile = true
      this.idPo = idpay.po_id
    },
    getFile (row) {
      /* this.$q.loading.show()
      if (row.file != null && row.file_type != null) {
        api.fileDownload(`/purchase-orders/getfile/${row.id}`).then(({ data }) => {
          this.$q.loading.hide()
          const url = window.URL.createObjectURL(new Blob([data], { type: row.type }))
          const link = document.createElement('a')
          link.href = url
          link.setAttribute('download', row.file) // or any other extension
          document.body.appendChild(link)
          link.click()
        })
      } else {
        this.$q.notify({
          message: 'No hay archivo anexado a este abono',
          position: 'top',
          color: 'warning'
        })
        this.$q.loading.hide()
      } */
    }
  }
}
</script>

<style>
.changeDocumentsReturnedByDriverNotify {
  width: 100px;
}
</style>

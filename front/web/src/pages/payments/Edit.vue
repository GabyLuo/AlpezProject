<template>
  <q-page>
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-xs-12 col-md-9 row">
          <div class="col-xs-12 col-md-2">
            <q-btn color="primary" icon="keyboard_backspace" label="Regresar" @click="backToGrid()" />
          </div>
          <div class="col-xs-12 col-md-10">
            <span class="q-ml-md grey-8 fs28 page-title">Remision {{ $route.params.id }}</span>
          </div>
        </div>
        <div class="col-xs-12 col-md-3">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="right">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Salidas de almacén" to="/storage-exits" />
              <q-breadcrumbs-el label="Editar" />
            </q-breadcrumbs>
          </div>
        </div>
      </div>
    </div>

    <div class="q-pa-md bg-grey-3">
      <div class="row bg-white border-panel">
        <div class="col q-pa-md">
          <div class="row q-col-gutter-xs" style="padding-bottom: 10px;">
            <div class="col-xs-12 col-sm-4">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="invoice.fields.saleDate"
                mask="date"
                label="Fecha de venta"
                :rules="saleDateRules"
                :disable="invoice.fields.status != 'NUEVO'"
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
                      v-model="invoice.fields.saleDate"
                      @input="() => $refs.invoiceFieldsSaleDate.hide()"
                      today-btn
                    />
                  </div>
                </q-popup-proxy>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-4 text-center">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="invoice.fields.branchOffice"
                :options="branchOfficeOptions"
                label="Sucursal"
                @input="() => {invoice.fields.baleStorage = null;invoice.fields.inBulkStorage = null;invoice.fields.laminateStorage = null}"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-store-alt" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-4 text-center">
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
            <div class="col-xs-12 col-sm-4 text-center">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="invoice.fields.baleStorage"
                :options="filteredStorageOptions"
                label="Almacén de pacas"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-warehouse" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-4 text-center">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="invoice.fields.inBulkStorage"
                :options="filteredStorageOptions"
                label="Almacén fibra abierta"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-warehouse" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-4 text-center">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="invoice.fields.laminateStorage"
                :options="filteredStorageOptions"
                label="Almacén de laminado"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-warehouse" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-4 text-center">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="invoice.fields.customer"
                :options="filteredCustomerOptions"
                label="Cliente"
                @input="() => {invoice.fields.customerBranchOffice = null}"
                :rules="customerRules"
                :disable="invoice.fields.status != 'NUEVO'"
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
            <div class="col-xs-12 col-sm-4 text-center">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="invoice.fields.customerBranchOffice"
                :options="filteredCustomerBranchOfficeOptions"
                label="Sucursal de cliente"
                :rules="customerBranchOfficeRules"
                :disable="invoice.fields.status != 'NUEVO'"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-store-alt" />
                </template>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-4 text-center">
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
            </div>
            <div class="col-xs-12 text-center">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                autogrow
                v-model="invoice.fields.comments"
                label="Comentarios"
                type="textarea"
                @input="v => { invoice.fields.comments = v.toUpperCase() }"
              />
            </div>
          </div>

          <div class="row q-mb-sm">
            <div class="col-xs-12 offset-md-6 col-md-6 pull-right">
              <q-btn color="positive" icon="fas fa-check" style="margin-left: 10px;" label="Documentar" @click="confirmRemissionDialog = true" v-if="invoice.fields.status == 'NUEVO' && (invoice.fields.canRemisionate || this.details.length > 0 || this.inBulkDetails.length > 0 || this.laminateDetails.length > 0)" />
              <q-btn color="primary" icon="mail" style="margin-left: 10px;" label="Enviar a cliente" @click="sendSaleReferral()" v-if="invoice.fields.status == 'ENVIADO'" :loading="loadingSendingMailBtn">
                <template v-slot:loading>
                  <q-spinner class="on-left" />
                  Enviando correo...
                </template>
              </q-btn>
              <q-btn color="positive" icon="save" style="margin-left: 10px;" label="Guardar" @click="updateSale()" v-if="invoice.fields.status == 'NUEVO'" />
              <q-btn color="positive" icon="fas fa-file-pdf" style="margin-left: 10px;" label="Remisión de venta" @click="saleReferral()" v-if="invoice.fields.status == 'ENVIADO'" />
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
          @input="changeModel"
        >
          <q-tab name="baleDetails" label="Pacas" :disable="fibraTab" />
          <q-tab name="inBulkDetails" label="Fibra abierta" :disable="bulkTab" v-if="invoice.fields.inBulkStorage != null && !isNaN(invoice.fields.inBulkStorage.value)" />
          <q-tab name="laminateDetails" label="Laminado" :disable="lamiTab" v-if="invoice.fields.laminateStorage != null && !isNaN(invoice.fields.laminateStorage.value)" />
        </q-tabs>
        <q-separator />
        <q-tab-panels v-model="currentTab" animated>
          <q-tab-panel name="baleDetails">
            <div style="font-weight: normal;">
              <div class="row q-col-gutter-xs" style="padding-right: 2%;" v-if="invoice.fields.status == 'NUEVO'">
                <div class="col-xs-12 col-sm-4 offset-sm-8 pull-right">
<!--                  <q-btn color="primary" icon="add" label="Agregar" @click="addDetail()" />-->
                  <q-btn color="primary" icon="add" label="Agregar" @click="addDetail()" />
                </div>
              </div>
              <div class="row q-col-gutter-xs" style="padding: 2%;" v-if="invoice.fields.status == 'NUEVO'">
                <div class="col-xs-12 col-sm-4">
                  <q-select
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="detail.fields.product"
                    :options="productOptions"
                    label="Producto"
                    :rules="productRules"
                    @input="() => detail.fields.bale = null"
                  >
                    <template v-slot:prepend>
                      <q-icon name="emoji_objects" />
                    </template>
                  </q-select>
                </div>
                <div class="col-xs-12 col-sm-4">
                  <q-select
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="detail.fields.bale"
                    :options="filteredBaleOptions"
                    label="Paca"
                    :rules="baleRules"
                  >
                    <template v-slot:prepend>
                      <q-icon name="fas fa-th-large" />
                    </template>
                  </q-select>
                </div>
                <div class="col-xs-12 col-sm-4">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="unitPrice"
                    label="Precio unitario"
                    disable
                  >
                    <template v-slot:prepend>
                      <q-icon name="fas fa-dollar-sign" />
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
                  row-key="bale_id"
                  :pagination.sync="pagination"
                >
                  <template v-slot:body="props">
                    <q-tr :props="props">
                      <q-td key="bale_id" style="text-align: left; width: 20%;" :props="props">{{ (props.row.bale_id ? `PACA ${props.row.bale_id}` : null) }}</q-td>
                      <q-td key="product" style="text-align: left; width: 40%;" :props="props" v-if="props.row.id">{{ props.row.product }}</q-td>
                      <q-td key="product" style="text-align: right; width: 40%; color: #3CB371;" :props="props" v-else>{{ props.row.product }}</q-td>
                      <q-td key="qty" style="text-align: right; width: 10%;" :props="props" v-if="props.row.id">{{ `${formatter.format(props.row.qty)} KG.` }}</q-td>
                      <q-td key="qty" style="text-align: right; width: 10%; color: #3CB371;" :props="props" v-else>{{ `${formatter.format(totalDetailsQties)} KG. (${details.length - 1} PACAS)` }}</q-td>
                      <q-td key="unitPrice" style="text-align: right; width: 10%;" :props="props">{{ (props.row.unit_price ? `${currencyFormatter.format(props.row.unit_price)}` : null) }}</q-td>
                      <q-td key="totalPrice" style="text-align: right; width: 10%;" :props="props" v-if="props.row.id">{{ `${currencyFormatter.format(props.row.total_price)}` }}</q-td>
                      <q-td key="totalPrice" style="text-align: right; width: 10%; color: #3CB371;" :props="props" v-else>{{ `${currencyFormatter.format(totalDetailsPrice)}` }}</q-td>
                      <q-td key="actions" style="width: 10%;" :props="props" v-if="props.row.bale_id && invoice.fields.status == 'NUEVO'">
                        <q-btn color="primary" icon="fas fa-trash-alt" flat @click.native="removeDetail(props.row.id)" size="10px">
                          <q-tooltip content-class="bg-red">Eliminar</q-tooltip>
                        </q-btn>
                      </q-td>
                      <q-td key="actions" style="width: 10%;" :props="props" v-else></q-td>
                    </q-tr>
                  </template>
                </q-table>
              </div>
            </div>
          </q-tab-panel>
          <q-tab-panel name="inBulkDetails" v-if="invoice.fields.inBulkStorage != null && !isNaN(invoice.fields.inBulkStorage.value)">
            <div style="font-weight: normal;">
              <div class="row q-col-gutter-xs" style="padding-right: 2%;" v-if="invoice.fields.status == 'NUEVO' && cndButtonIB">
                <div class="col-xs-12 col-sm-4 offset-sm-8 pull-right">
<!--                  <q-btn color="primary" icon="add" label="Agregar" @click="addInBulkDetail()" />-->
                  <q-btn color="primary" icon="add" label="Actualizar" @click="editIB()" />
                </div>
              </div>
              <div class="row q-col-gutter-xs" style="padding: 2%;" v-if="invoice.fields.status == 'NUEVO' && cndButtonIB">
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
                    :suffix="`/${inBulkDetailAvailableQty}`"
                  >
                    <template v-slot:prepend>
                      <q-icon name="emoji_objects" />
                    </template>
                  </q-input>
                </div>
                <div class="col-xs-12 col-sm-6 col-md-2">
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
                </div>
                <div class="col-xs-12 col-sm-6 col-md-2">
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
                </div>
              </div>
              <div class="q-col-gutter-xs" style="padding: 2%;">
                <q-table
                  flat
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
                      <q-td key="qty" style="text-align: right; width: 10%;" :props="props" v-if="props.row.id">{{ `${formatter.format(props.row.qty)} KG.` }}</q-td>
                      <q-td key="qty" style="text-align: right; width: 10%; color: #3CB371;" :props="props" v-else>{{ `${formatter.format(totalInBulkDetailsQties)} KG.` }}</q-td>
                      <q-td key="unitPrice" style="text-align: right; width: 10%;" :props="props">{{ (props.row.unit_price ? `${currencyFormatter.format(props.row.unit_price)}` : null) }}</q-td>
                      <q-td key="totalPrice" style="text-align: right; width: 10%;" :props="props" v-if="props.row.id">{{ `${currencyFormatter.format(props.row.total_price)}` }}</q-td>
                      <q-td key="totalPrice" style="text-align: right; width: 10%; color: #3CB371;" :props="props" v-else>{{ `${currencyFormatter.format(totalInBulkDetailsPrice)}` }}</q-td>
                      <q-td key="packagesQty" style="text-align: right; width: 10%;" :props="props" v-if="props.row.id">{{ `${formatter.format(props.row.packages_qty)}` }}</q-td>
                      <q-td key="actions" style="width: 10%;" :props="props" v-if="props.row.id && invoice.fields.status == 'NUEVO'">
<!--                        <q-btn color="primary" icon="fas fa-trash-alt" flat @click.native="removeInBulkDetail(props.row.id)" size="10px">-->
<!--                          <q-tooltip content-class="bg-red">Eliminar</q-tooltip>-->
<!--                        </q-btn>-->
                        <q-btn color="primary" icon="edit" flat @click.native="editInBulkDetail(props.row)" size="10px">
                          <q-tooltip content-class="bg-red">Editar</q-tooltip>
                        </q-btn>
                      </q-td>
                      <q-td key="actions" style="width: 15%;" :props="props" v-else></q-td>
                    </q-tr>
                  </template>
                </q-table>
              </div>
            </div>
          </q-tab-panel>
          <q-tab-panel name="laminateDetails" v-if="invoice.fields.laminateStorage != null && !isNaN(invoice.fields.laminateStorage.value)">
            <div style="font-weight: normal;">
              <div class="row q-col-gutter-xs" style="padding-right: 2%;" v-if="invoice.fields.status == 'NUEVO' && cndButton">
                <div class="col-xs-12 col-sm-4 offset-sm-8 pull-right">
<!--                  <q-btn color="primary" icon="add" label="Agregar" @click="addLaminateDetail()" />-->
                  <q-btn color="primary" icon="add" label="Actualizar" @click="editLD()" />
                </div>
              </div>
              <div class="row q-col-gutter-xs" style="padding: 2%;" v-if="invoice.fields.status == 'NUEVO' && cndButton">
                <div class="col-xs-12 col-sm-4">
                  <q-select
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="laminateDetail.fields.product"
                    :options="laminateProductOptions"
                    emit-value map-options
                    label="Producto"
                    :rules="laminateDetailProductRules"
                    @input="() => laminateDetail.fields.qty = null"
                  >
                    <template v-slot:prepend>
                      <q-icon name="emoji_objects" />
                    </template>
                  </q-select>
                </div>
                <div class="col-xs-12 col-sm-4">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="laminateDetail.fields.qty"
                    label="Cantidad"
                    :rules="laminateDetailQtyRules"
                    :suffix="`/${laminateDetailAvailableQty}`"
                  >
                    <template v-slot:prepend>
                      <q-icon name="emoji_objects" />
                    </template>
                  </q-input>
                </div>
                <div class="col-xs-12 col-sm-4">
                  <q-input
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="laminateDetailUnitPrice"
                    label="Precio unitario"
                    disable
                  >
                    <template v-slot:prepend>
                      <q-icon name="fas fa-dollar-sign" />
                    </template>
                  </q-input>
                </div>
              </div>
              <div class="q-col-gutter-xs" style="padding: 2%;">
                <q-table
                  flat
                  bordered
                  :data="laminateDetails"
                  :columns="laminateDetailsColumns"
                  row-key="product"
                  :pagination.sync="pagination"
                >
                  <template v-slot:body="props">
                    <q-tr :props="props">
                      <q-td key="product" style="text-align: left; width: 50%;" :props="props" v-if="props.row.id">{{ props.row.product }}</q-td>
                      <q-td key="product" style="text-align: right; width: 50%; color: #3CB371;" :props="props" v-else>{{ props.row.product }}</q-td>
                      <q-td key="qty" style="text-align: right; width: 15%;" :props="props" v-if="props.row.id">{{ `${formatter.format(props.row.qty)} KG.` }}</q-td>
                      <q-td key="qty" style="text-align: right; width: 15%; color: #3CB371;" :props="props" v-else>{{ `${formatter.format(totallaminateDetailsQties)} KG.` }}</q-td>
                      <q-td key="unitPrice" style="text-align: right; width: 10%;" :props="props">{{ (props.row.unit_price ? `${currencyFormatter.format(props.row.unit_price)}` : null) }}</q-td>
                      <q-td key="totalPrice" style="text-align: right; width: 10%;" :props="props" v-if="props.row.id">{{ `${currencyFormatter.format(props.row.total_price)}` }}</q-td>
                      <q-td key="totalPrice" style="text-align: right; width: 10%; color: #3CB371;" :props="props" v-else>{{ `${currencyFormatter.format(totallaminateDetailsPrice)}` }}</q-td>
                      <q-td key="actions" style="width: 15%;" :props="props" v-if="props.row.id && invoice.fields.status == 'NUEVO'">
<!--                        <q-btn color="primary" icon="fas fa-trash-alt" flat @click.native="removeLaminateDetail(props.row.id)" size="10px">-->
<!--                          <q-tooltip content-class="bg-red">Eliminar</q-tooltip>-->
<!--                        </q-btn>-->
                        <q-btn color="primary" icon="edit" flat @click.native="editLaminateDetail(props.row)" size="10px">
                          <q-tooltip content-class="warning">Editar</q-tooltip>
                        </q-btn>
                      </q-td>
                      <q-td key="actions" style="width: 15%;" :props="props" v-else></q-td>
                    </q-tr>
                  </template>
                </q-table>
              </div>
            </div>
          </q-tab-panel>
        </q-tab-panels>
      </div>
    </div>

    <q-dialog v-model="confirmRemissionDialog" persistent>
      <q-card>
        <q-card-section class="row items-center">
          <span class="q-ml-sm">¿Realmente desea cambiar el estatus a ENVIADO?</span>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancelar" color="primary" v-close-popup />
          <q-btn flat label="Confirmar" color="primary" @click="remission()" />
        </q-card-actions>
      </q-card>
    </q-dialog>

  </q-page>
</template>

<script>
import api from '../../commons/api.js'
const { required, decimal, numeric, minValue, maxValue } = require('vuelidate/lib/validators')

export default {
  name: 'EditStorageExit',
  validations: {
    invoice: {
      fields: {
        saleDate: { required },
        customer: { required },
        customerBranchOffice: { required },
        driver: { required }
      }
    },
    detail: {
      fields: {
        product: { required },
        bale: { required }
      }
    },
    inBulkDetail: {
      fields: {
        product: { required },
        qty: { required, decimal },
        packagesQty: { numeric, minValue: minValue(0), maxValue: maxValue(32767) }
      }
    },
    laminateDetail: {
      fields: {
        product: { required },
        qty: { required, decimal }
      }
    }
  },
  data () {
    return {
      formatter: new Intl.NumberFormat('en-US'),
      currencyFormatter: new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
      }),
      totalQtyBales: null,
      cndButton: false,
      cndButtonIB: false,
      idDetail: null,
      idDetailIB: null,
      fibraTab: true,
      bulkTab: true,
      lamiTab: true,
      invoice: {
        fields: {
          id: null,
          saleDate: null,
          baleStorage: null,
          inBulkStorage: null,
          laminateStorage: null,
          branchOffice: null,
          customer: null,
          customerBranchOffice: null,
          customerPriceList: null,
          driver: null,
          comments: null,
          canRemisionate: false,
          status: null
        }
      },
      detail: {
        fields: {
          id: null,
          product: null,
          bale: null
        }
      },
      inBulkDetail: {
        fields: {
          product: null,
          qty: null,
          packagesQty: null
        }
      },
      laminateDetail: {
        fields: {
          product: null,
          qty: null
        }
      },
      laminateDetailcnd: null,
      inBulkDetailcnd: null,
      branchOfficeOptions: [],
      storageOptions: [],
      customerOptions: [],
      productOptions: [],
      baleOptions: [],
      customerBranchOfficeOptions: [],
      filteredCustomerOptions: [],
      driverOptions: [],
      inBulkProducts: [],
      laminateProducts: [],
      inBulkProductOptions: [],
      laminateProductOptions: [],
      currentTab: null,
      details: [],
      detailsColumns: [
        { name: 'bale_id', align: 'center', label: 'Paca', field: 'bale_id', style: 'width: 20%', sortable: true },
        { name: 'product', align: 'center', label: 'Producto', field: 'product', style: 'width: 40%', sortable: true },
        { name: 'qty', align: 'center', label: 'Peso', field: 'qty', style: 'width: 10%', sortable: true },
        { name: 'unitPrice', align: 'center', label: 'Precio', field: 'unitPrice', style: 'width: 10%', sortable: true },
        { name: 'totalPrice', align: 'center', label: 'Importe', field: 'totalPrice', style: 'width: 10%', sortable: true },
        { name: 'actions', align: 'center', label: 'Acciones', field: 'actions', style: 'width: 10%', sortable: false }
      ],
      inBulkDetails: [],
      inBulkDetailsColumns: [
        { name: 'product', align: 'center', label: 'Producto', field: 'product', style: 'width: 50%', sortable: true },
        { name: 'qty', align: 'center', label: 'Peso', field: 'qty', style: 'width: 10%', sortable: true },
        { name: 'unitPrice', align: 'center', label: 'Precio', field: 'unitPrice', style: 'width: 10%', sortable: true },
        { name: 'totalPrice', align: 'center', label: 'Importe', field: 'totalPrice', style: 'width: 10%', sortable: true },
        { name: 'packagesQty', align: 'center', label: 'Bultos', field: 'packagesQty', style: 'width: 10%', sortable: true },
        { name: 'actions', align: 'center', label: 'Acciones', field: 'actions', style: 'width: 10%', sortable: false }
      ],
      laminateDetails: [],
      laminateDetailsColumns: [
        { name: 'product', align: 'center', label: 'Producto', field: 'product', style: 'width: 50%', sortable: true },
        { name: 'qty', align: 'center', label: 'Peso', field: 'qty', style: 'width: 15%', sortable: true },
        { name: 'unitPrice', align: 'center', label: 'Precio', field: 'unitPrice', style: 'width: 10%', sortable: true },
        { name: 'totalPrice', align: 'center', label: 'Importe', field: 'totalPrice', style: 'width: 10%', sortable: true },
        { name: 'actions', align: 'center', label: 'Acciones', field: 'actions', style: 'width: 15%', sortable: false }
      ],
      pagination: {
        rowsPerPage: 50
      },
      confirmRemissionDialog: false,
      loadingSendingMailBtn: false,
      productsPrices: []
    }
  },
  computed: {
    roleId () {
      const user = this.$store.getters['users/rol']
      return parseInt(user)
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
    baleRules (val) {
      return [
        val => (this.$v.detail.fields.bale.required) || 'El campo Paca es requerido.'
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
    laminateDetailProductRules (val) {
      return [
        val => (this.$v.laminateDetail.fields.product.required) || 'El campo Producto es requerido.'
      ]
    },
    laminateDetailQtyRules (val) {
      return [
        val => (this.$v.laminateDetail.fields.qty.required) || 'El campo Cantidad es requerido.',
        val => (this.$v.laminateDetail.fields.qty.decimal) || 'El campo Cantidad debe ser numérico.'
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
    filteredBaleOptions () {
      if (this.detail.fields.product && !isNaN(this.detail.fields.product.value)) {
        return this.baleOptions.filter(op => Number(op.product_id) === Number(this.detail.fields.product.value))
      }
      return []
    },
    unitPrice () {
      if (this.detail.fields.product != null && this.detail.fields.product.value != null && this.invoice.fields.customerPriceList != null) {
        const prices = this.productsPrices.filter(pp => Number(pp.product_id) === Number(this.detail.fields.product.value) && pp.price_level === this.invoice.fields.customerPriceList)
        if (prices.length > 0) {
          return prices[0].price
        }
      }
      return 0
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
    laminateDetailUnitPrice () {
      if (this.laminateDetail.fields.product != null && this.laminateDetail.fields.product != null && this.invoice.fields.customerPriceList != null) {
        const prices = this.productsPrices.filter(pp => Number(pp.product_id) === Number(this.laminateDetail.fields.product) && pp.price_level === this.invoice.fields.customerPriceList)
        if (prices.length > 0) {
          return prices[0].price
        }
      }
      return 0
    },
    totallaminateDetailsQties () {
      let totalQties = 0
      this.laminateDetails.forEach(detail => {
        if (detail.qty) {
          totalQties += Number(detail.qty)
        }
      })
      return totalQties
    },
    totallaminateDetailsPrice () {
      let totalPrice = 0
      this.laminateDetails.forEach(detail => {
        if (detail.total_price) {
          totalPrice += Number(detail.total_price)
        }
      })
      return totalPrice
    },
    inBulkDetailAvailableQty () {
      let availableQty = 0
      if (this.inBulkDetail.fields.product != null && !isNaN(this.inBulkDetail.fields.product)) {
        availableQty = this.inBulkProducts.filter(ibp => Number(ibp.product_id) === Number(this.inBulkDetail.fields.product))[0].stock
        const filteredDetails = this.inBulkDetails.filter(ibd => Number(ibd.product_id) === Number(this.inBulkDetail.fields.product))
        filteredDetails.forEach(detail => {
          availableQty -= Number(detail.qty)
        })
      }
      return availableQty
    },
    laminateDetailAvailableQty () {
      let availableQty = 0
      if (this.laminateDetail.fields.product != null && !isNaN(this.laminateDetail.fields.product)) {
        availableQty = this.laminateProducts.filter(ibp => Number(ibp.product_id) === Number(this.laminateDetail.fields.product))[0].stock
        const filteredDetails = this.laminateDetails.filter(ld => Number(ld.product_id) === Number(this.laminateDetail.fields.product))
        filteredDetails.forEach(detail => {
          availableQty -= Number(detail.qty)
        })
      }
      return availableQty
    }
  },
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
  /* beforeCreate () {
    if (!(this.$store.getters['users/roles'].includes(1) || this.$store.getters['users/roles'].includes(3) || this.$store.getters['users/roles'].includes(7) || this.$store.getters['users/roles'].includes(12))) {
      this.$router.push('/')
    }
  }, */
  created () {
    this.fetchFromServer()
  },
  methods: {
    fetchFromServer () {
      this.$q.loading.show()
      api.get(`/invoices/${this.$route.params.id}`).then(({ data }) => {
        if (!data.invoice) {
          this.$router.push('/storage-exits')
        } else {
          this.invoice.fields.id = this.$route.params.id
          this.invoice.fields.saleDate = data.invoice.sale_date
          if (data.invoice.bale_branch_office_id) {
            this.invoice.fields.branchOffice = { value: data.invoice.bale_branch_office_id, label: data.invoice.bale_branch_office }
          } else if (data.invoice.in_bulk_branch_office_id) {
            this.invoice.fields.branchOffice = { value: data.invoice.in_bulk_branch_office_id, label: data.invoice.in_bulk_branch_office }
          } else if (data.invoice.laminate_branch_office_id) {
            this.invoice.fields.branchOffice = { value: data.invoice.laminate_branch_office_id, label: data.invoice.laminate_branch_office }
          }
          if (data.invoice.laminate_storage_id) {
            this.invoice.fields.laminateStorage = data.invoice.laminate_storage_id && data.invoice.laminate_storage ? { value: data.invoice.laminate_storage_id, label: data.invoice.laminate_storage } : null
            this.currentTab = 'laminateDetails'
            this.lamiTab = false
          }
          if (data.invoice.in_bulk_storage_id) {
            this.invoice.fields.inBulkStorage = data.invoice.in_bulk_storage_id && data.invoice.in_bulk_storage ? { value: data.invoice.in_bulk_storage_id, label: data.invoice.in_bulk_storage } : null
            this.currentTab = 'inBulkDetails'
            this.bulkTab = false
          }
          if (data.invoice.bale_storage_id) {
            this.invoice.fields.baleStorage = { value: data.invoice.bale_storage_id, label: data.invoice.bale_storage }
            this.currentTab = 'baleDetails'
            this.fibraTab = false
          }
          this.invoice.fields.customer = { value: data.invoice.customer_id, label: data.invoice.customer }
          this.invoice.fields.customerBranchOffice = { value: data.invoice.customer_branch_office_id, label: data.invoice.customer_branch_office }
          this.invoice.fields.customerPriceList = data.invoice.customer_price_list
          this.invoice.fields.driver = { value: data.invoice.driver_id, label: data.invoice.driver }
          this.invoice.fields.comments = data.invoice.comments
          this.invoice.fields.canRemisionate = data.invoice.canRemisionate
          this.invoice.fields.status = data.invoice.status
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
            this.changeModel(this.currentTab)
            this.$q.loading.hide()
          }
        }
      })
    },
    backToGrid () {
      this.$router.push('/storage-exits')
    },
    remission () {
      this.$q.loading.show()
      this.confirmRemissionDialog = false
      api.put(`/invoices/${this.invoice.fields.id}/remission`, []).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          api.get(`/invoices/${this.$route.params.id}`).then(({ data }) => {
            this.invoice.fields.id = this.$route.params.id
            this.invoice.fields.saleDate = data.invoice.sale_date
            this.invoice.fields.branchOffice = { value: data.invoice.bale_branch_office_id, label: data.invoice.bale_branch_office }
            this.invoice.fields.baleStorage = { value: data.invoice.bale_storage_id, label: data.invoice.bale_storage }
            this.invoice.fields.inBulkStorage = data.invoice.in_bulk_storage_id && data.invoice.in_bulk_storage ? { value: data.invoice.in_bulk_storage_id, label: data.invoice.in_bulk_storage } : null
            this.invoice.fields.customer = { value: data.invoice.customer_id, label: data.invoice.customer }
            this.invoice.fields.customerBranchOffice = { value: data.invoice.customer_branch_office_id, label: data.invoice.customer_branch_office }
            this.invoice.fields.driver = { value: data.invoice.driver_id, label: data.invoice.driver }
            this.invoice.fields.status = data.invoice.status
            this.$q.loading.hide()
          })
        } else {
          this.$q.loading.hide()
        }
      })
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
      params.sale_date = { ...this.invoice.fields }.saleDate
      params.customer_branch_office_id = Number({ ...this.invoice.fields }.customerBranchOffice.value)
      params.driver_id = Number({ ...this.invoice.fields }.driver.value)
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
            this.invoice.fields.branchOffice = { value: data.invoice.bale_branch_office_id, label: data.invoice.bale_branch_office }
            this.invoice.fields.baleStorage = { value: data.invoice.bale_storage_id, label: data.invoice.bale_storage }
            this.invoice.fields.inBulkStorage = data.invoice.in_bulk_storage_id && data.invoice.in_bulk_storage ? { value: data.invoice.in_bulk_storage_id, label: data.invoice.in_bulk_storage } : null
            this.invoice.fields.customer = { value: data.invoice.customer_id, label: data.invoice.customer }
            this.invoice.fields.customerBranchOffice = { value: data.invoice.customer_branch_office_id, label: data.invoice.customer_branch_office }
            this.invoice.fields.driver = { value: data.invoice.driver_id, label: data.invoice.driver }
            this.invoice.fields.status = data.invoice.status
            this.$q.loading.hide()
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    changeModel (newModel) {
      if (newModel === 'baleDetails' && this.details.length === 0) {
        this.$q.loading.show()
        api.get(`/invoice-details/invoice/${this.$route.params.id}`).then(({ data }) => {
          this.details = data.invoiceDetails
          if (this.details.length > 0) {
            this.details.push({ id: null, bale_id: null, invoice_id: null, product_id: null, product: 'TOTAL:', qty: null })
          }
          if (this.invoice.fields.status === 'NUEVO' && this.productOptions.length === 0 && this.baleOptions.length === 0) {
            api.get(`/storages/${this.invoice.fields.baleStorage.value}/bales`).then(({ data }) => {
              this.productOptions = []
              this.baleOptions = []
              data.bales.forEach(bale => {
                if (this.details.filter(det => Number(det.bale_id) === Number(bale.bale_id)).length === 0) {
                  this.baleOptions.push({ value: bale.bale_id, label: `PACA ${bale.bale_id} (${bale.stock} KG.)`, qty: bale.stock, product_id: bale.product_id, product_code: bale.product_code, product: bale.product })
                }
                if (this.productOptions.filter(po => Number(po.value) === Number(bale.product_id)).length === 0) {
                  this.productOptions.push({ value: bale.product_id, label: `${bale.category_code}-${bale.line_code}-${bale.product_name}` })
                }
              })
              this.$q.loading.hide()
            })
          } else {
            this.$q.loading.hide()
          }
        })
      } else if (newModel === 'inBulkDetails' && this.inBulkDetails.length === 0) {
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
      } else if (newModel === 'laminateDetails' && this.laminateDetails.length === 0) {
        this.$q.loading.show()
        api.get(`/invoice-laminate-details/invoice/${this.$route.params.id}`).then(({ data }) => {
          this.laminateDetails = data.invoiceDetails
          if (this.laminateDetails.length > 0) {
            this.laminateDetails.push({ id: null, invoice_id: null, product_id: null, product: 'TOTAL:', qty: null })
          }
          if (this.invoice.fields.status === 'NUEVO' && this.laminateProducts.length === 0 && this.laminateProductOptions.length === 0) {
            api.get(`/storages/${this.invoice.fields.laminateStorage.value}/laminates`).then(({ data }) => {
              this.laminateProducts = []
              this.laminateProductOptions = []
              data.products.forEach(product => {
                const details = this.laminateDetails.filter(det => Number(det.product_id) === Number(product.product_id))
                details.forEach(det => {
                  product.stock -= det.qty
                })
                this.laminateProducts.push(product)
                this.laminateProductOptions.push({ value: product.product_id, label: `${product.category_code}-${product.line_code}-${product.product_name}` })
              })
              this.$q.loading.hide()
            })
          } else {
            this.$q.loading.hide()
          }
        })
      }
      console.log(this.laminateDetails)
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
      if (this.detail.fields.bale.value == null) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, seleccione la paca.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = []
      params.bale_id = Number({ ...this.detail.fields }.bale.value)
      params.invoice_id = Number(this.$route.params.id)
      this.detail.fields.product = null
      this.detail.fields.bale = null
      api.post('/invoice-details', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.details = []
          api.get(`/invoice-details/invoice/${this.$route.params.id}`).then(({ data }) => {
            this.details = data.invoiceDetails
            if (this.details.length > 0) {
              this.details.push({ id: null, bale_id: null, invoice_id: null, product_id: null, product: 'TOTAL:', qty: null })
            }
            api.get(`/storages/${this.invoice.fields.baleStorage.value}/bales`).then(({ data }) => {
              this.productOptions = []
              this.baleOptions = []
              data.bales.forEach(bale => {
                if (this.details.filter(det => Number(det.bale_id) === Number(bale.bale_id)).length === 0) {
                  this.baleOptions.push({ value: bale.bale_id, label: `PACA ${bale.bale_id} (${bale.stock} KG.)`, qty: bale.stock, product_id: bale.product_id, product_code: bale.product_code, product: bale.product })
                }
                if (this.productOptions.filter(po => Number(po.value) === Number(bale.product_id)).length === 0) {
                  this.productOptions.push({ value: bale.product_id, label: `${bale.category_code}-${bale.line_code}-${bale.product_name}` })
                }
              })
              this.$q.loading.hide()
            })
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    removeDetail (detailElementId) {
      this.totalQtyBales = this.totalDetailsQties
      this.$q.loading.show()
      api.delete(`/invoice-details/${detailElementId}`).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.details = []
          api.get(`/invoice-details/invoice/${this.$route.params.id}`).then(({ data }) => {
            this.details = data.invoiceDetails
            if (this.details.length > 0) {
              this.details.push({ id: null, bale_id: null, invoice_id: null, product_id: null, product: 'TOTAL:', qty: null })
            }
            api.get(`/storages/${this.invoice.fields.baleStorage.value}/bales`).then(({ data }) => {
              this.productOptions = []
              this.baleOptions = []
              data.bales.forEach(bale => {
                if (this.details.filter(det => Number(det.bale_id) === Number(bale.bale_id)).length === 0) {
                  this.baleOptions.push({ value: bale.bale_id, label: `PACA ${bale.bale_id} (${bale.stock} KG.)`, qty: bale.stock, product_id: bale.product_id, product_code: bale.product_code, product: bale.product })
                }
                if (this.productOptions.filter(po => Number(po.value) === Number(bale.product_id)).length === 0) {
                  this.productOptions.push({ value: bale.product_id, label: `${bale.category_code}-${bale.line_code}-${bale.product_name}` })
                }
              })
              this.$q.loading.hide()
            })
          })
        } else {
          this.$q.loading.hide()
        }
      })
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
      if (row.qty > 0) {
        this.cndButtonIB = true
        this.inBulkDetail.fields.product = row.product_id
        this.inBulkDetail.fields.qty = row.qty
        this.inBulkDetailcnd = row.qty
        this.inBulkDetailAvailableQty = row.unit_price
        this.idDetailIB = row.id
      }
    },
    editIB () {
      this.$q.loading.show()
      if (Number(this.inBulkDetail.fields.qty) > Number(this.inBulkDetailcnd * 1.20)) {
        this.$q.notify({
          message: 'Cantidad Excedida???',
          position: 'top',
          color: ('negative')
        })
        this.$q.loading.hide()
      } else {
        const params = []
        params.detailId = this.idDetailIB
        params.newQty = this.inBulkDetail.fields.qty
        params.bulks = this.inBulkDetail.fields.packagesQty
        api.post('/invoice-in-bulk-details/editinBulksDetails', params).then(({ data }) => {
          this.$q.notify({
            message: data.message.content,
            position: 'top',
            color: (data.result ? 'positive' : 'warning')
          })
          if (data.result === true) {
            this.cndButton = false
            this.fetchFromServer()
            this.cleanFieldsInbulk()
            this.$q.loading.hide()
          } else {
            this.cleanFieldsInbulk()
            this.cndButton = true
            this.$q.loading.hide()
          }
        })
      }
    },
    addLaminateDetail () {
      this.$v.laminateDetail.fields.$reset()
      this.$v.laminateDetail.fields.$touch()
      if (this.$v.laminateDetail.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = []
      params.product_id = Number({ ...this.laminateDetail.fields }.product.value)
      params.qty = Number({ ...this.laminateDetail.fields }.qty)
      params.invoice_id = Number(this.$route.params.id)
      this.laminateDetail.fields.product = null
      this.laminateDetail.fields.qty = null
      api.post('/invoice-laminate-details', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          api.get(`/invoice-laminate-details/invoice/${this.$route.params.id}`).then(({ data }) => {
            this.laminateDetails = data.invoiceDetails
            if (this.laminateDetails.length > 0) {
              api.get(`/storages/${this.invoice.fields.laminateStorage.value}/laminates`).then(({ data }) => {
                this.laminateProducts = []
                this.laminateProductOptions = []
                data.products.forEach(product => {
                  const details = this.laminateDetails.filter(det => Number(det.product_id) === Number(product.product_id))
                  details.forEach(det => {
                    product.stock -= det.qty
                  })
                  this.laminateProducts.push(product)
                  this.laminateProductOptions.push({ value: product.product_id, label: `${product.category_code}-${product.line_code}-${product.product_name}` })
                })
                this.$q.loading.hide()
              })
              this.laminateDetails.push({ id: null, invoice_id: null, product_id: null, product: 'TOTAL:', qty: null })
            } else {
              this.$q.loading.hide()
            }
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    removeLaminateDetail (detailElementId) {
      this.$q.loading.show()
      api.delete(`/invoice-laminate-details/${detailElementId}`).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          api.get(`/invoice-laminate-details/invoice/${this.$route.params.id}`).then(({ data }) => {
            this.laminateDetails = data.invoiceDetails
            if (this.laminateDetails.length > 0) {
              api.get(`/storages/${this.invoice.fields.laminateStorage.value}/laminates`).then(({ data }) => {
                this.laminateProducts = []
                this.laminateProductOptions = []
                data.products.forEach(product => {
                  const details = this.laminateDetails.filter(det => Number(det.product_id) === Number(product.product_id))
                  details.forEach(det => {
                    product.stock -= det.qty
                  })
                  this.laminateProducts.push(product)
                  this.laminateProductOptions.push({ value: product.product_id, label: `${product.category_code}-${product.line_code}-${product.product_name}` })
                })
                this.$q.loading.hide()
              })
              this.laminateDetails.push({ id: null, invoice_id: null, product_id: null, product: 'TOTAL:', qty: null })
            } else {
              this.$q.loading.hide()
            }
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    editLaminateDetail (row) {
      if (row.qty > 0) {
        this.cndButton = true
        this.laminateDetail.fields.product = row.product_id
        this.laminateDetail.fields.qty = row.qty
        this.laminateDetailcnd = row.qty
        this.laminateDetailUnitPrice = row.unit_price
        this.idDetail = row.id
      }
    },
    editLD () {
      this.$q.loading.show()
      if (Number(this.laminateDetail.fields.qty) > Number(this.laminateDetailcnd * 1.20)) {
        this.$q.notify({
          message: 'Cantidad Excedida???',
          position: 'top',
          color: ('negative')
        })
        this.$q.loading.hide()
      } else {
        const params = []
        params.detailId = this.idDetail
        params.newQty = this.laminateDetail.fields.qty
        api.post('/invoice-laminate-details/editLaminateDetails', params).then(({ data }) => {
          this.$q.notify({
            message: data.message.content,
            position: 'top',
            color: (data.result ? 'positive' : 'warning')
          })
          if (data.result === true) {
            this.cndButton = false
            this.refreshLaminate()
            this.cleanFieldsLaminate()
            this.$q.loading.hide()
          } else {
            this.cleanFieldsLaminate()
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
    cleanFieldsLaminate () {
      this.laminateDetail.fields.qty = ''
      this.idDetails = ''
    },
    cleanFieldsInbulk () {
      this.inBulkDetail.fields.qty = ''
      this.idDetailsIB = ''
    },
    refreshLaminate () {
      api.get(`/invoice-laminate-details/invoice/${this.$route.params.id}`).then(({ data }) => {
        this.laminateDetails = data.invoiceDetails
        if (this.laminateDetails.length > 0) {
          this.laminateDetails.push({ id: null, invoice_id: null, product_id: null, product: 'TOTAL:', qty: null })
        }
        if (this.invoice.fields.status === 'NUEVO' && this.laminateProducts.length === 0 && this.laminateProductOptions.length === 0) {
          api.get(`/storages/${this.invoice.fields.laminateStorage.value}/laminates`).then(({ data }) => {
            this.laminateProducts = []
            this.laminateProductOptions = []
            data.products.forEach(product => {
              const details = this.laminateDetails.filter(det => Number(det.product_id) === Number(product.product_id))
              details.forEach(det => {
                product.stock -= det.qty
              })
              this.laminateProducts.push(product)
              this.laminateProductOptions.push({ value: product.product_id, label: `${product.category_code}-${product.line_code}-${product.product_name}` })
            })
            this.$q.loading.hide()
          })
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

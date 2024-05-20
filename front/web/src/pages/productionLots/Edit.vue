<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-9">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Ordenes de producción" to="/production-orders" />
              <q-breadcrumbs-el label="Lote" style="cursor: pointer;" v-text="lot.fields.order_number" @click="editSelectedRow()"/>
              <q-breadcrumbs-el label="Editar" v-text="lot.fields.lot_number"/>
            </q-breadcrumbs>
          </div>
        </div>
         <div class="col-xs-12 col-sm-3 pull-right">
          <q-btn class="bg-primary" @click="execute()" v-if="lot.fields.status === 'NUEVO' || lot.fields.status === 'ASIGNADO'" icon="fas fa-bolt" label="INICIAR" style="margin-right:10px " />
           <q-btn color="primary" @click="finalizeLot()" v-if="lot.fields.status === 'INICIADO'" icon="fas fa-check" label="FINALIZAR" style="margin-right:10px " />
        </div>
      </div>
    </div>
    <div class="q-pa-md bg-grey-3">
      <div class="row bg-white border-panel">
        <div class="col q-pa-md">
          <div class="row q-col-gutter-xs">
            <div class="col-xs-12 col-sm-4">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                disable
                v-model="lot.fields.order_number"
                label="Número de orden"
              >
                <template v-slot:prepend>
                  <q-icon name="code" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-4">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="lot.fields.production_date"
                mask="date"
                label="Fecha programada"
                :rules="creationDateRules"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="event" />
                </template>
                <q-popup-proxy
                  ref="orderFieldsCreationDateRef"
                  transition-show="scale"
                  transition-hide="scale"
                >
                  <div class="col-sm-12">
                    <q-date
                      v-model="lot.fields.production_date"
                      @input="() => $refs.orderFieldsCreationDateRef.hide()"
                      today-btn
                    />
                  </div>
                </q-popup-proxy>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-4">
              <q-input
                color="white"
                 :bg-color="lot.fields.status === 'NUEVO' ? 'primary' : (lot.fields.status === 'INICIADO' ? 'blue' : (lot.fields.status === 'FINALIZADO' ? 'green' : 'red'))"
                filled
                dark
                v-model="lot.fields.status"
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
                        v-model="lot.fields.family"
                        :error="$v.lot.fields.family.$error"
                        label="Categorías"
                        :rules="bomcategoryRules"
                        @input="getLinesByCategories()"
                        :options="options1"
                        use-input
                        hide-selected
                        fill-input
                        input-debounce="0"
                        @filter="filterCategory"
                        hint="Basic autocomplete"
                        emit-value
                        map-options
                        disable
                        >
                        <template v-slot:prepend>
                          <q-icon name="fas fa-cubes" />
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
                    <q-select
                    filled
                    color="dark"
                    bg-color="secondary"
                    v-model="lot.fields.product"
                    :error="$v.lot.fields.product.$error"
                    label="Producto"
                    :options="options2"
                    use-input
                    hide-selected
                    fill-input
                    input-debounce="0"
                    @filter="filterProduct"
                    hint="Basic autocomplete"
                    emit-value
                    map-options
                    disable
                    >
                    <template v-slot:prepend>
                   <q-icon name="fas fa-grip-lines-vertical" />
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
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="lot.fields.qty"
                :error="$v.lot.fields.qty.$error"
                label="Cantidad a producir"
                :rules="qtyRules"
                @input="v => { lot.fields.qty = v.replace(/[^0-9\\.]/g, '') }"
                :disable="!canModifyOrder"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-weight-hanging" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-2 text-center">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="lot.fields.qty_real"
                label="Cantidad producida"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-weight-hanging" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 text-center">
              <q-linear-progress stripe size="33px" :value="shipmentsProgress" color="positive">
                <q-chip dense square color="secondary" text-color="white">
                  {{ shipmentsProgressLabel }}
                </q-chip>
              </q-linear-progress>
            </div>
          </div>
        </div>
      </div>
      <br>
<div class="q-pa-xs">
  <div class="q-gutter-y-md" style="width: 100%">
    <q-card >
      <q-tabs
      v-model="tab"
      dense
      class="text-grey"
      active-color="primary"
      indicator-color="primary"
      align="justify"
      narrow-indicator
      >
      <q-tab name="bom" icon="fas fa-cubes" label="BOM"/>
      <q-tab name="work" icon="fas fa-hard-hat" label="MANO DE OBRA"/>
    </q-tabs>
    <q-separator />
    <q-tab-panels v-model="tab" animated class="">
      <q-tab-panel name="bom">
        <div class="" style="background-color:white">
          <div class="col q-pa-md">
      </div>
  </div>
  <q-table flat bordered :data="dataBom" :columns="columns" row-key="product" :pagination.sync="pagination">
    <template v-slot:body="props">
      <q-tr :props="props">
        <q-td key="code_product" :props="props" >{{ props.row.product_code }}</q-td>
        <q-td key="product_name" :props="props" >{{ props.row.product_name }}</q-td>
        <q-td key="code_line" :props="props" >{{ props.row.product_category }}</q-td>
        <q-td key="name_category" :props="props" >{{ props.row.category_name }}</q-td>
        <q-td key="amount" :props="props" >{{ $formatNumberThree(props.row.amount) }}</q-td>
        <q-td key="lastprice" :props="props" >{{  `${currencyFormatter.format(props.row.lastprice)}` }}</q-td>
        <q-td key="totalprice" :props="props" >{{  `${currencyFormatter.format(props.row.lastprice * props.row.amount)}` }}</q-td>
        <q-td key="stock" :props="props"  v-if="(props.row.qty * props.row.amount) <= props.row.existencia" style="background-color:green">{{ $formatNumberThree(props.row.existencia) }}</q-td>
        <q-td key="stock" :props="props" v-else style="background-color:red">{{$formatNumberThree(props.row.existencia) }}</q-td>
        <q-td key="total" :props="props" >{{ $formatNumberThree(props.row.stock) }}</q-td>
      </q-tr>
    </template>
  </q-table>
</q-tab-panel>
<q-tab-panel name="work">
        <div class="" style="background-color:white">
          <div class="col q-pa-md">
            <div class="row q-col-gutter-xs">
              <q-dialog v-model="modalWork" persistent>
                <q-card style="min-width: 50%">
                  <q-card-section>
                    <div class="text-h6">MANO DE OBRA</div>
                  </q-card-section>
                  <q-card-section>
          </q-card-section>
          <q-card-actions align="right" class="text-primary">
          </q-card-actions>
        </q-card>
        </q-dialog>
      </div>
    </div>
  </div>
  <q-table flat bordered :data="dataWork" :columns="columnswork" row-key="nombre" :pagination.sync="pagination">
  <q-tr slot="bottom-row" slot-scope="">
      <q-td></q-td>
      <q-td></q-td>
      <q-td></q-td>
      <q-td></q-td>
      <q-td></q-td>
      <q-td></q-td>
        <q-td  style="text-align: right;">
          <strong>Total: ${{ tot }}</strong>
        </q-td>
      </q-tr>
    <template v-slot:body="props">
      <q-tr :props="props" v-if="props.row.handiwork_id !== null">
        <q-td key="name" :props="props" class="pull-left">{{ props.row.name_work }}</q-td>
        <q-td key="price_hour" :props="props" class="pull-right" >${{ currencyFormat(props.row.price_hour) }}</q-td>
        <q-td key="price_minute" :props="props" class="pull-right" >${{ currencyFormat(props.row.price_minute) }}</q-td>
        <q-td key="time_job" :props="props" class="pull-right">{{ props.row.time_job }}</q-td>
        <q-td key="price_qty" :props="props" class="pull-right">${{ currencyFormat(props.row.price_qty)}}</q-td>
        <q-td key="qty" :props="props" v-if="props.row.qtyw !== null" class="pull-right">{{ props.row.qtyw }}</q-td>
        <q-td key="qty" :props="props" v-else  class="pull-right">{{ props.row.qty }}</q-td>
        <q-td key="amount" :props="props" v-if="props.row.amountw !== null" class="pull-right">${{ props.row.amountw }}</q-td>
        <q-td key="amount" :props="props" v-else class="pull-right">${{ props.row.amount }}</q-td>
        <q-td key="employee_name" :props="props" >{{ props.row.employee_name }}</q-td>
        <q-td key="actions" :props="props" class="pull-left">
          <q-btn class="action-btn" v-if="props.row.employee_id === null" :color="props.row.employee_id === null ? 'red' : 'green'" icon="manage_accounts" flat style="text-align:left" @click.native="OpenModalOperador(props.row)"><q-tooltip content-class="bg-positive" >AGREGAR OPERADOR</q-tooltip></q-btn>
          <q-btn class="action-btn" v-else :color="props.row.employee_id === null ? 'red' : 'green'" icon="manage_accounts" flat style="text-align:left"  @click.native="OpenModalOperadorEdit(props.row)" ><q-tooltip content-class="bg-positive" >OPERADOR</q-tooltip></q-btn>
        </q-td>
      </q-tr>
    </template>
  </q-table>
</q-tab-panel>
</q-tab-panels>
</q-card>
</div>
</div>
</div>
<q-dialog v-model="modalOperador" persistent>
  <q-card style="min-width: 50%">
    <q-card-section>
      <div class="text-h6">Agregar Operador </div>
    </q-card-section>
         <q-card-section>
           <div class="row q-col-gutter-xs">
              <div class="col-xs-12 col-sm-">
              <q-select
                        color="dark"
                        bg-color="secondary"
                        filled
                        v-model="employee.fields.employee_id"
                        label="OPERADOR"
                        :options="selectEmployees"
                        use-input
                        hide-selected
                        fill-input
                        input-debounce="0"
                        hint="Basic autocomplete"
                        emit-value
                        map-options                        >
                        <template v-slot:prepend>
                          <q-icon name="fas fa-cubes" />
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
            </div>
          </q-card-section>
    <q-card-actions align="right" class="text-primary">
        <q-btn label="Cancelar" color="red" v-close-popup />
        <q-btn label="Guardar" color="green" @click.native="saveEmployee()" />
        </q-card-actions>
    </q-card>
</q-dialog>
<q-dialog v-model="modalOperadorEdit" persistent>
  <q-card style="min-width: 50%">
    <q-card-section>
      <div class="text-h6">Editar Operador </div>
    </q-card-section>
         <q-card-section>
           <div class="row q-col-gutter-xs">
              <div class="col-xs-12 col-sm-">
              <q-select
                        color="dark"
                        bg-color="secondary"
                        filled
                        v-model="employeeEdit.fields.employee_id"
                        label="OPERADOR"
                        :options="selectEmployees"
                        use-input
                        hide-selected
                        fill-input
                        input-debounce="0"
                        hint="Basic autocomplete"
                        emit-value
                        map-options                        >
                        <template v-slot:prepend>
                          <q-icon name="fas fa-cubes" />
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
            </div>
          </q-card-section>
    <q-card-actions align="right" class="text-primary">
        <q-btn label="Cancelar" color="red" v-close-popup />
        <q-btn label="Guardar" color="green" @click.native="updateEmployee()" />
        </q-card-actions>
    </q-card>
</q-dialog>
<q-dialog v-model="modalFinish" persistent>
  <q-card style="min-width: 50%">
    <q-card-section>
      <div class="text-h6">Capturar Devolución </div>
    </q-card-section>
         <q-card-section>
           <div class="row q-col-gutter-xs">
              <div class="col-xs-12 col-sm-4">
              <q-select
                        color="dark"
                        bg-color="secondary"
                        filled
                        v-model="lotFinish.fields.product"
                        label="Producto"
                        :rules="bomcategoryRules"
                        @input="getLinesByCategories()"
                        :options="options1"
                        use-input
                        hide-selected
                        fill-input
                        input-debounce="0"
                        @filter="filterCategory"
                        hint="Basic autocomplete"
                        emit-value
                        map-
                        disable
                        >
                        <template v-slot:prepend>
                          <q-icon name="fas fa-cubes" />
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
                v-model="lotFinish.fields.amount"
                mask="date"
                label="Cantidad Solicitada"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="event" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-4">
               <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="lotFinish.fields.qty"
                mask="date"
                label="Producido"
              >
                <template v-slot:prepend>
                  <q-icon name="event" />
                </template>
              </q-input>
            </div>
            </div>
          </q-card-section>
    <q-card-actions align="right" class="text-primary">
        <q-btn label="Cancelar" color="red" v-close-popup />
        <q-btn label="Guardar" color="green" @click.native="finalizeLotReturnMaterial()" />
        </q-card-actions>
    </q-card>
</q-dialog>
  </q-page>
</template>

<script>
import api from '../../commons/api.js'
const { required, decimal, helpers } = require('vuelidate/lib/validators')
const num = helpers.regex('num', /[0-9]+$/)
export default {
  name: 'EditLot',
  validations: {
    lot: {
      fields: {
        order_number: { required },
        production_date: { required },
        product: { required },
        family: { required },
        qty: { required, decimal },
        status: { required },
        category_id: {},
        qty_real: {}
      }
    },
    lotFinish: {
      fields: {
        product: {},
        amount: {},
        qty: { required }
      }
    },
    bom: {
      fields: {
        category: { },
        product: { required },
        cantidad: { required }
      }
    },
    formula: {
      fields: {
        product: { required },
        qty: { required, decimal },
        quality: { required }
      }
    },
    raw_material: {
      fields: {
        product: { required },
        bag: { required }
      }
    },
    return_raw_material: {
      fields: {
        returned_qty: { required, decimal }
      }
    },
    measurement: {
      fields: {
        value: { required, decimal },
        measure: { required }
      }
    },
    scrap: {
      fields: {
        qty: { required, decimal }
      }
    },
    work: {
      fields: {
        work_id: { required },
        time_job: { required, num }
      }
    },
    finished_product: {
      fields: {
        product: { required },
        weight: { required, decimal }
      }
    },
    employee: {
      fields: {
        employee_id: { },
        lot: {}
      }
    },
    employeeEdit: {
      fields: {
        employee_id: { },
        lot: {}
      }
    }
  },
  data () {
    return {
      workEdit: null,
      tab: 'bom',
      modalFinish: false,
      modalWork: false,
      modalOperador: false,
      modalOperadorEdit: false,
      tot: 0,
      emple: false,
      options1: this.categoryOptions,
      options2: this.ProductsOptionsbyLines,
      formatter: new Intl.NumberFormat('en-US'),
      currencyFormatter: new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
      }),
      currentTab: 'formula',
      lot: {
        fields: {
          id: null,
          order_id: null,
          order_number: null,
          production_date: null,
          product: null,
          qty: null,
          status: null,
          family: 1,
          category_id: null,
          movement_id: null,
          raw_material_movement_id: null,
          order_product_id: null,
          rawMaterialExecuted: false,
          returnedRawMaterialExecuted: false,
          qty_real: 0
        }
      },
      lotFinish: {
        fields: {
          product: null,
          amount: null,
          qty: null
        }
      },
      bom: {
        fields: {
          idedit: null,
          category: null,
          product: null,
          cantidad: null
        }
      },
      work: {
        fields: {
          idedit: null,
          product_id: null,
          time_job: null,
          work_id: null
        }
      },
      formula: {
        fields: {
          id: null,
          product: null,
          qty: null,
          quality: null
        }
      },
      raw_material: {
        fields: {
          id: null,
          origin_storage: 'ALMACEN RECIBO MP',
          consumption_storage: 'ALMACEN PRODUCCION MP',
          product: null,
          bag: null,
          weight: 0,
          returned_qty: 0
        }
      },
      return_raw_material: {
        fields: {
          id: null,
          returned_qty: 0
        }
      },
      measurement: {
        fields: {
          id: null,
          process_id: null,
          process: null,
          value: null,
          measure: null,
          zone_number: null,
          dryer_number: null,
          finished: null
        }
      },
      scrap: {
        fields: {
          id: null,
          qty: null,
          process_id: null,
          process: null,
          dryer_number: null,
          finished: null
        }
      },
      finished_product: {
        fields: {
          id: null,
          storage: 'ALMACEN PT FIBRA',
          product: null,
          weight: null
        }
      },
      employee: {
        fields: {
          employee_id: null,
          lot: null
        }
      },
      employeeEdit: {
        fields: {
          employee_id: null,
          lot: null
        }
      },
      columns: [
        { name: 'code_product', align: 'center', label: 'CÓDIGO PRODUCTO', field: 'code_product', sortable: false },
        { name: 'product_name', align: 'center', label: 'NOMBRE PRODUCTO', field: 'product_name', sortable: false },
        { name: 'name_category', align: 'center', label: 'CATEGORÍAS', field: 'name_category', sortable: false },
        { name: 'amount', align: 'center', label: 'CANTIDAD POR PIEZA', field: 'amount', sortable: false },
        { name: 'lastprice', align: 'center', label: 'COSTO ÚLTIMA COMPRA', field: 'lastprice', sortable: false },
        { name: 'totalprice', align: 'center', label: 'COSTO TOTAL', field: 'totalprice', sortable: false },
        { name: 'stock', align: 'center', label: 'EXISTENCIA', field: 'stok', sortable: false },
        { name: 'total', align: 'center', label: 'TOTAL REQUERIDO', field: 'total', sortable: false }
      ],
      columnswork: [
        { name: 'name', align: 'center', label: 'NOMBRE', field: 'name', sortable: false },
        { name: 'price_hour', align: 'center', label: ' COSTO POR HORA', field: 'price_hour', sortable: false },
        { name: 'price_minute', align: 'center', label: ' COSTO POR MINUTO', field: 'price_minute', sortable: false },
        { name: 'time_job', align: 'center', label: 'TIEMPO REQUERIDO', field: 'time_job', sortable: false },
        { name: 'price_qty', align: 'center', label: ' TOTAL REQUERIDO', field: 'price_qty', sortable: false },
        { name: 'qty', align: 'center', label: 'CANTIDAD A PRODUCIR', field: 'qty', sortable: false },
        { name: 'amount', align: 'center', label: 'COSTO FINAL', field: 'amount', sortable: false },
        { name: 'employee_name', align: 'center', label: 'OPERADOR', field: 'employee_name', sortable: false },
        { name: 'actions', align: 'center', label: 'ACCIONES', field: 'actions', sortable: false }
      ],
      pagination: {
        sortBy: 'id',
        descending: false,
        rowsPerPage: 25
      },
      processesPagination: {
        sortBy: 'code',
        descending: false,
        page: 1,
        rowsPerPage: 0
      },
      rawMaterialsPagination: {
        rowsPerPage: 50
      },
      processMeasurementsPagination: {
        rowsPerPage: 50
      },
      dataBom: [],
      formulas: [],
      rawMaterials: [],
      returnedRawMaterials: [],
      measurements: [],
      scraps: [],
      processMeasurements: [],
      processes: [],
      finishedProducts: [],
      productOptions: [],
      lotProductOptions: [],
      lotCustomerOptions: [],
      rawMaterialOptions: [],
      qualityOptions: [{ value: 'A', label: 'A' }, { value: 'B', label: 'B' }, { value: 'C', label: 'C' }],
      bagOptions: [],
      processIdToEdit: null,
      inputQtyReturnRawMaterialModal: false,
      measurementsModal: false,
      scrapModal: false,
      categoryOptions: [],
      ProductsOptionsbyLines: [],
      dataWork: [],
      selectEmployees: [],
      rowwork: []
    }
  },
  computed: {
    creationDateRules (val) {
      return [
        val => (this.$v.lot.fields.production_date.required) || 'El campo Fecha de creación es requerido.'
      ]
    },
    canModifyOrder () {
      if (this.lot.fields.status === 'NUEVO' || this.lot.fields.status === 'FORMULADO') {
        // if (this.lots.filter(l => (l.status !== 'NUEVO' || l.status !== 'FORMULADO')).length === 0) {
        return true
        // }
      }
      return false
    },
    bomcategoryRules (val) {
      return [
        val => this.$v.lot.fields.family.required || 'El campo Categorías es requerido.'
      ]
    },
    bomproductsRules (val) {
      return [
        val => this.$v.lot.fields.product.required || 'El campo Productos es requerido.'
      ]
    },
    qtyRules (val) {
      return [
        val => (this.$v.lot.fields.qty.required) || 'El campo Cantidad es requerido.',
        val => (this.$v.lot.fields.qty.decimal) || 'El campo Cantidad debe ser numérico.'
      ]
    },
    shipmentsProgress () {
      return Number(this.lot.fields.status === 'NUEVO' ? 0 : (this.lot.fields.status === 'INICIADO' ? 0.5 : (this.lot.fields.status === 'FINALIZADO' ? 1 : 0)))
    },
    shipmentsProgressLabel () {
      return 'Avance: ' + (this.shipmentsProgress * 100).toFixed(2) + '%'
    },
    qualityVolumeRules (val) {
      return [
        val => (this.$v.quality.fields.volume.decimal) || 'El campo Volumen debe ser numérico.'
      ]
    },
    zoneIcon () {
      return ((this.measurement.fields.zone_number && Number(this.measurement.fields.zone_number.value) === 2) ? 'fas fa-dice-two' : ((this.measurement.fields.zone_number && Number(this.measurement.fields.zone_number.value) === 3) ? 'fas fa-dice-three' : ((this.measurement.fields.zone_number && Number(this.measurement.fields.zone_number.value) === 4) ? 'fas fa-dice-four' : ((this.measurement.fields.zone_number && Number(this.measurement.fields.zone_number.value) === 5) ? 'fas fa-dice-five' : 'fas fa-dice-one'))))
    },
    familyFilteredProductOptions () {
      if (this.lot.fields.product != null && this.lot.fields.product.value != null) {
        return this.productOptions.filter(op => ((Number(op.family) === Number(this.lot.fields.product.value)) || (Number(op.value) === Number(this.lot.fields.product.value))))
      }
      return []
    },
    filteredBagOptions () {
      const auxRawMaterials = []
      this.rawMaterials.forEach(rm => auxRawMaterials.push(Number(rm.bag_id)))
      if (this.raw_material.fields.product != null && this.raw_material.fields.product.value != null) {
        return this.bagOptions.filter(bo => (Number(bo.product_id) === Number(this.raw_material.fields.product.value) && !auxRawMaterials.includes(Number(bo.value))))
      }
      return []
    },
    filteredRawMaterialWithBagsOptions () {
      const productsWithBag = []
      this.bagOptions.forEach(function (bag) {
        if (!productsWithBag.includes(Number(bag.product_id))) {
          productsWithBag.push(Number(bag.product_id))
        }
      })
      return this.rawMaterialOptions.filter(rawMaterial => productsWithBag.includes(Number(rawMaterial.value)))
    },
    lotProductRules (val) {
      return [
        val => (this.$v.lot.fields.product.required) || 'El campo Producto es requerido.'
      ]
    },
    lotWeightRules (val) {
      return [
        val => (this.$v.lot.fields.weight.required) || 'El campo Peso es requerido.',
        val => (this.$v.lot.fields.weight.decimal) || 'El campo Peso debe ser numérico.'
      ]
    },
    formulaProductRules (val) {
      return [
        val => (this.$v.formula.fields.product.required) || 'El campo Producto es requerido.'
      ]
    },
    formulaQtyRules (val) {
      return [
        val => (this.$v.formula.fields.qty.required) || 'El campo Cantidad es requerido.',
        val => (this.$v.formula.fields.qty.decimal) || 'El campo Cantidad debe ser numérico.'
      ]
    },
    qualityRules (val) {
      return [
        val => (this.$v.formula.fields.quality.required) || 'El campo Calidad es requerido.'
      ]
    },
    rawMaterialProductRules (val) {
      return [
        val => (this.$v.raw_material.fields.product.required) || 'El campo Producto es requerido.'
      ]
    },
    rawMaterialBagRules (val) {
      return [
        val => (this.$v.raw_material.fields.bag.required) || 'El campo Saco es requerido.'
      ]
    },
    measurementValueRules (val) {
      return [
        val => this.$v.measurement.fields.value.required || 'El campo Valor es requerido.',
        val => this.$v.measurement.fields.value.decimal || 'El campo Valor debe ser numérico.'
      ]
    },
    measurementZoneRules (val) {
      return [
        val => this.$v.measurement.fields.zone.required || 'El campo Zona es requerido.'
      ]
    },
    measurementMeasureRules (val) {
      return [
        val => this.$v.measurement.fields.measure.required || 'El campo Medición es requerido.'
      ]
    },
    finishedProductProductRules (val) {
      return [
        val => this.$v.finished_product.fields.product.required || 'El campo Producto es requerido.'
      ]
    },
    finishedProductWeightRules (val) {
      return [
        val => (this.$v.finished_product.fields.weight.required) || 'El campo Peso es requerido.',
        val => (this.$v.finished_product.fields.weight.decimal) || 'El campo Peso debe ser numérico.'
      ]
    },
    rawMaterialQtyToReturnRules (val) {
      return [
        val => (this.$v.return_raw_material.fields.returned_qty.required) || 'El campo Cantidad a devolver es requerido.',
        val => (this.$v.return_raw_material.fields.returned_qty.decimal) || 'El campo Cantidad a devolver debe ser numérico.'
      ]
    },
    scrapQtyRules (val) {
      return [
        val => (this.$v.scrap.fields.qty.required) || 'El campo Peso es requerido.',
        val => (this.$v.scrap.fields.qty.decimal) || 'El campo Peso debe ser numérico.'
      ]
    },
    measurementUnit () {
      if (this.measurement.fields.measure != null && this.measurement.fields.measure.value != null) {
        if (this.measurement.fields.measure.value.split('(')[1] != null && this.measurement.fields.measure.value.split('(')[1].split(')')[0] != null) {
          return this.measurement.fields.measure.value.split('(')[1] != null && this.measurement.fields.measure.value.split('(')[1].split(')')[0]
        }
      }
      return null
    },
    totalFormulaQties () {
      let totalQties = 0
      this.formulas.forEach(formula => {
        if (formula.qty) {
          totalQties += Number(formula.qty)
        }
      })
      return totalQties
    },
    totalRawMaterialWeight () {
      let totalWeight = 0
      this.rawMaterials.forEach(rawMaterial => {
        if (rawMaterial.weight) {
          totalWeight += Number(rawMaterial.weight)
        }
      })
      return totalWeight
    },
    totalFinishedProductWeight () {
      let totalWeight = 0
      this.finishedProducts.forEach(finishedProduct => {
        if (finishedProduct.weight) {
          totalWeight += Number(finishedProduct.weight)
        }
      })
      return totalWeight
    }
  },
  beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(4) && !this.$store.getters['users/roles'].includes(5) && !this.$store.getters['users/roles'].includes(6) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(8) && !this.$store.getters['users/roles'].includes(9) && !this.$store.getters['users/roles'].includes(10)) {
      this.$router.push('/')
    }
  },
  created () {
    this.fetchFromServer()
  },
  methods: {
    fetchFromServer () {
      this.$q.loading.show()
      const id = this.$route.params.id
      api.get(`/production-lots/${id}`).then(({ data }) => {
        console.log(data.bom)
        if (!data.lot) {
        } else {
          this.lot.fields = data.lot
          this.lot.fields.start_date = this.lot.fields.start_date ? this.lot.fields.start_date.split('-').join('/') : null
          this.lot.fields.finish_date = this.lot.fields.finish_date ? this.lot.fields.finish_date.split('-').join('/') : null
          this.lot.fields.product = { value: data.lot.product_id, label: data.lot.product }
          this.lot.fields.customer = { value: data.lot.customer_id, label: data.lot.customer }
          this.lot.fields.family = { value: data.lot.category_id, label: data.lot.category_name }
          this.dataBom = data.bom
          this.lot.fields.order_id = data.lot.order_id
          this.lot.fields.order_number = data.lot.order_number
          this.lot.fields.production_date = data.lot.scheduled_start_date
          this.lot.fields.qty = data.lot.weight
          api.get(`/works-lots/getby/${id}`).then(({ data }) => {
            this.$q.loading.hide()
            this.dataWork = data.info
            this.tot = data.tot
            this.workEdit = data.info.idw
          }).catch(error => error)
        }
      })
    },
    fetchFromServerWorks () {
      const id = this.$route.params.id
      api.get(`/works-lots/getby/${id}`).then(({ data }) => {
        this.dataWork = data.info
        this.tot = data.tot
        this.workEdit = data.info.idw
      }).catch(error => error)
    },
    filterCategory (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.options1 = this.categoryOptions.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    filterProduct (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.options2 = this.ProductsOptionsbyLines.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    backToProductionOrder () {
      this.$router.push(`/production-orders/${this.lot.fields.order_id}`)
    },
    updateLot () {
      this.$v.lot.fields.$reset()
      this.$v.lot.fields.$touch()
      if (this.$v.lot.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = { ...this.lot.fields }
      params.product_id = params.product.value
      if (params.customer != null && params.customer.value != null) {
        params.customer_id = params.customer.value
      }
      api.put(`/production-lots/${params.id}`, params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          const id = this.$route.params.id
          api.get(`/production-lots/${id}`).then(({ data }) => {
            this.lot.fields = data.lot
            this.lot.fields.start_date = this.lot.fields.start_date ? this.lot.fields.start_date.split('-').join('/') : null
            this.lot.fields.finish_date = this.lot.fields.finish_date ? this.lot.fields.finish_date.split('-').join('/') : null
            this.$q.loading.hide()
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    packingList () {
      const uri = process.env.API + `production-lots/packing-list/pdf/${this.$route.params.id}`
      window.open(uri, '_blank')
    },
    execute () {
      for (var i = 0; i < this.dataWork.length; i++) {
        if (this.dataWork[i].employee_id === null && this.dataWork[i].handiwork_id !== null) {
          this.$q.dialog({
            title: 'Error',
            message: 'Por favor, agregue operador.',
            persistent: true
          })
          return false
        }
      }
      this.showLoading()
      const id = this.$route.params.id
      api.get(`/production-lots/${id}`).then(({ data }) => {
        var aux = 'si'
        for (var j = 0; j < data.bom.length; j++) {
          if (data.bom[j].cantidades_suficientes === 'no') {
            aux = 'no'
          }
        }
        if (aux === 'si') {
          const params = { ...this.lot.fields }
          api.post(`/production-lots/executelot/${params.id}`, params).then(({ data }) => {
            if (data) {
              this.$q.notify({
                message: data.message.content,
                position: 'top',
                color: (data.result ? 'positive' : 'red')
              })
              if (data.result) {
                this.fetchFromServer()
                this.beforeDestroy()
              } else {
                this.fetchFromServer()
                this.beforeDestroy()
              }
            }
          })
        } else {
          this.beforeDestroy()
          for (var i = 0; i < data.bom.length; i++) {
            if ((data.bom[i].category_code === 'PRI' || data.bom[i].category_name === 'PROD. INTERMEDIO') && (data.bom[i].cantidades_suficientes === 'no')) {
              const params = data.bom[i]
              this.$q.dialog({
                title: 'PRODUCTO: ' + data.bom[i].product_code + ' (Insuficiente)',
                message: '¿Desea crear un Lote para la cantidad faltante del producto?',
                ok: {
                  label: 'Aceptar',
                  color: 'green'
                },
                cancel: {
                  label: 'Cancelar',
                  color: 'red'
                },
                persistent: true
              }).onOk(() => {
                this.showLoading()
                api.post('/production-lots', params).then(({ data }) => {
                  this.$q.notify({
                    message: data.message.content,
                    position: 'top',
                    color: (data.result ? 'positive' : 'green')
                  })
                  if (data.resul) {
                    this.beforeDestroy()
                  } else {
                    this.beforeDestroy()
                  }
                })
              }).onCancel(() => {
                this.beforeDestroy()
              })
            }
            if (data.bom[i].category_code === 'MTP' || data.bom[i].category_name === 'MAT. PRIMA') {
              if (data.bom[i].cantidades_suficientes === 'no') {
                this.$q.notify({
                  message: 'Falta de Materia Prima: ' + data.bom[i].product_code,
                  position: 'top',
                  color: 'red'
                })
              }
            }
          }
          this.fetchFromServer()
        }
      })
    },
    finalizeLot () {
      this.$q.dialog({
        title: 'Finalización del lote',
        message: '¿Desea finalizar y dar por terminado el lote?',
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
        this.showLoading()
        const params = { ...this.lot.fields }
        api.post(`/production-lots/getfinalizelot/${params.id}`, params).then(({ data }) => {
          if (data.finalizeLot) {
            this.lotFinish.fields.product = this.lot.fields.product
            this.lotFinish.fields.amount = data.finalizeLot.weight
            this.modalFinish = true
            this.beforeDestroy()
          } else {
            const params = { ...this.lot.fields }
            api.post(`/production-lots/finalizeLot/${params.id}`, params).then(({ data }) => {
              this.$q.notify({
                message: data.message.content,
                position: 'top',
                color: (data.result ? 'positive' : 'red')
              })
              if (data.result) {
                this.fetchFromServer()
                this.beforeDestroy()
              }
            })
          }
        })
      }).onCancel(() => {
        this.beforeDestroy()
        return false
      })
    },
    editSelectedRow () {
      this.$router.push(`/production-orders/${this.lot.fields.order_id}`)
    },
    finalizeLotReturnMaterial () {
      this.showLoading()
      const params = { ...this.lot.fields }
      params.returned_qty = this.lotFinish.fields.qty
      if (parseInt(this.lotFinish.fields.qty) === parseInt(this.lot.fields.qty)) {
        this.modalFinish = false
        api.post(`/production-lots/finalizeLot/${params.id}`, params).then(({ data }) => {
          this.$q.notify({
            message: data.message.content,
            position: 'top',
            color: (data.result ? 'positive' : 'red')
          })
          this.fetchFromServer()
          this.modalFinish = false
          if (data) {
            this.beforeDestroy()
          }
        })
      }
      if (parseInt(this.lotFinish.fields.qty) < parseInt(this.lot.fields.qty) && parseInt(this.lotFinish.fields.qty) > 0) {
        this.modalFinish = false
        api.post(`/production-lots/finalizeLotReturMaterial/${params.id}`, params).then(({ data }) => {
          this.$q.notify({
            message: data.message.content,
            position: 'top',
            color: (data.result ? 'positive' : 'red')
          })
          this.fetchFromServer()
          this.modalFinish = false
          if (data) {
            this.beforeDestroy()
          }
        })
      }
    },
    showLoading () {
      this.$q.loading.show({
        spinnerColor: 'primary',
        spinnerSize: 140,
        backgroundColor: 'white',
        message: 'Cargando..',
        messageColor: 'black'
      })
    },
    beforeDestroy () {
      this.$q.loading.hide()
    },
    currencyFormat (num) {
      return Number.parseFloat(num).toFixed(3).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
    },
    OpenModalOperador (row) {
      this.rowwork = row
      this.modalOperador = true
      this.employee.fields.lot = row.plw_id
      api.get('/employees/options').then(({ data }) => {
        this.selectEmployees = data.options
      }).catch(error => error)
    },
    OpenModalOperadorEdit (row) {
      this.rowwork = row
      this.modalOperadorEdit = true
      this.employee.fields.lot = row.plw_id
      this.workEdit = row.idw
      api.get('/employees/options').then(({ data }) => {
        this.selectEmployees = data.options
        this.employeeEdit.fields.employee_id = { value: row.employee_id, label: row.employee_name }
      }).catch(error => error)
    },
    saveEmployee () {
      this.$v.employee.fields.$reset()
      this.$v.employee.fields.$touch()
      if (this.$v.employee.fields.$error) {
        this.$q.notify({
          message: 'Por favor selecione una opcion.',
          color: 'warning',
          position: 'top'
        })
        return false
      }
      if (this.rowwork.employee_id !== null) {
        this.$q.notify({
          message: 'Operador ya asignado.',
          color: 'warning',
          position: 'top'
        })
        return false
      }
      this.modalOperador = false
      this.showLoading()
      const params = { ...this.employee.fields }
      params.row = this.rowwork
      api.post('/works-lots/', params).then(({ data }) => {
        if (data.result) {
          this.employee.fields.employee_id = null
          this.fetchFromServerWorks()
        }
        this.$q.notify({
          message: data.message.content,
          color: data.result ? 'positive' : 'red',
          position: 'top'
        })
        this.beforeDestroy()
      })
    },
    updateEmployee () {
      this.$v.employee.fields.$reset()
      this.$v.employee.fields.$touch()
      if (this.$v.employee.fields.$error) {
        this.$q.notify({
          message: 'Por favor selecione una opcion.',
          color: 'warning',
          position: 'top'
        })
        return false
      }
      this.modalOperadorEdit = false
      this.showLoading()
      const params = { ...this.employeeEdit.fields }
      api.put(`/works-lots/${this.workEdit}`, params).then(({ data }) => {
        if (data.result) {
          this.employee.fields.employee_id = null
          this.fetchFromServerWorks()
        }
        this.$q.notify({
          message: data.message.content,
          color: data.result ? 'positive' : 'red',
          position: 'top'
        })
        this.beforeDestroy()
      })
    }
  }
}
</script>

<style>
</style>

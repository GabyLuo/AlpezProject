<template>
    <q-page class="bg-grey-3">
        <div class="q-pa-sm panel-header">
            <div class="row">
                <div class="col-sm-8">
                    <div class="q-pa-md q-gutter-sm">
                        <q-breadcrumbs align="left" style="font-size: 20px">
                            <q-breadcrumbs-el label="" icon="home" to="/"/>
                            <q-breadcrumbs-el label="Movimientos" to="/movements" />
                            <q-breadcrumbs-el v-if="movement.fields.type_id !== 5 && movement.fields.type_id !== 4" label="Editar Movimiento" v-text="movement.fields.folio"/>
                            <q-breadcrumbs-el v-if="movement.fields.type_id === 5 || movement.fields.type_id === 4" label="Editar Movimiento" v-text="movement_transfer.fields.folio_1"/>
                        </q-breadcrumbs>
                    </div>
                </div>
                <div class="col-sm-4 pull-right" v-if="execute">
                  <div class="q-pa-sm q-gutter-sm">
                    <!-- <q-btn color="red" icon="cancel" label="Cancelar" @click="cancel()"/> -->
                    <q-btn color="red" class="q-mr-sm" icon="fas fa-file-pdf" @click="generatePDF()">
                      <q-tooltip content-class="bg-positive">Generar Reporte</q-tooltip>
                    </q-btn>
                    <!-- <q-btn color="positive" v-if="this.movement.fields.type_movement === 'SALIDA'" icon="fas fa-file-pdf"  @click="generatePDFSI()">
                       <q-tooltip content-class="bg-positive">Generar Reporte S/I</q-tooltip>
                    </q-btn> -->
                  </div>
                </div>
                <div class="col-sm-4 pull-right" v-if="!execute">
                  <div class="q-pa-sm q-gutter-sm">
                    <q-btn v-if="dataMD.length!==0 && !canModify" color="positive"  icon="offline_bolt" label="Ejecutar" @click="executeMovement()"/>
                  </div>
                </div>
            </div>
        </div>
        <div class="q-pa-md bg-grey-3">
            <div class="row bg-white border-panel">
                <div class="col q-pa-md">
                    <div class="row q-col-gutter-xs" v-if="movement.fields.type_id !== 5 && movement.fields.type_id !== 4">
                        <div class="col-xs-12 col-sm-2 text-center">
                            <q-select
                            color="white"
                            :bg-color="color"
                            filled
                            dark
                            readonly
                            v-model="movement.fields.type_movement"

                            filter
                            label="Tipo de Movimiento">
                            <template v-slot:prepend>
                                <q-icon name="swap_horiz"></q-icon>
                            </template>
                            </q-select>
                        </div>
                        <div class="col-xs-12 col-sm-2 text-center">
                            <q-select color="dark"
                            bg-color="secondary"
                            filled
                            :readonly="execute"
                            mask="date"
                            v-model="movement.fields.date"
                            :rules="dateRules"
                            label="Fecha"
                            :disable="canModify">
                            <template v-slot:prepend>
                                <q-icon name="event"></q-icon>
                            </template>
                            <q-popup-proxy ref="date_ref" transition-show="scale" transition-hide="scale" >
                                <div class="col-sm-12">
                                    <q-date
                                    :readonly="execute"
                                    color="secondary"
                                    text-color="white"
                                    :locale="myLocale"
                                    v-model="movement.fields.date"
                                    :disable="canModify"
                                    mask="DD/MM/YYYY"
                                    @input="() => $refs.date_ref.hide()"
                                    today-btn>
                                    </q-date>
                                </div>
                            </q-popup-proxy>
                            </q-select>
                        </div>
                        <div class="col-xs-12 col-sm-2 text-center">
                            <q-input
                            color="dark"
                            bg-color="secondary"
                            filled
                            readonly
                            v-model="movement.fields.folio"
                            label="Folio"
                            :disable="canModify">
                            <template v-slot:prepend>
                                <q-icon name="style"></q-icon>
                            </template>
                            </q-input>
                        </div>
                        <div class="col-xs-12 col-sm-2 text-center">
                            <q-select
                            color="dark"
                            bg-color="secondary"
                            filled
                            :readonly="execute"
                            :options="branchesList"
                            v-model="movement.fields.branch"
                            @input="() => movement.fields.storage=''"
                            :rules="branchOfficeRules"
                            :error="$v.movement.fields.branch.$error"
                            label="Estación"
                            :disable="canModify">
                            <template v-slot:prepend>
                                <q-icon name="business"></q-icon>
                            </template>
                            </q-select>
                        </div>
                        <div class="col-xs-12 col-sm-2 text-center">
                            <q-select
                            color="dark"
                            bg-color="secondary"
                            filled
                            :readonly="execute"
                            v-model="movement.fields.storage"
                            :options="filteredStorageOptions"
                            :rules="storageRules"
                            :error="$v.movement.fields.storage.$error"
                            label="Almacén"
                            :disable="canModify">
                            <template v-slot:prepend>
                                <q-icon name="store"></q-icon>
                            </template>
                            </q-select>
                        </div>
                        <div class="col-xs-12 col-sm-2 text-center">
                            <q-input
                            color="dark"
                            dark
                            :bg-color="movement.fields.status === 'NUEVO' ? 'blue' : (movement.fields.status === 'EJECUTADO' ? 'positive' : (movement.fields.status === 'CANCELADO' ? 'negative' : 'negative'))"
                            filled
                            readonly
                            emit-value
                            v-model="movement.fields.status"
                            label="Estatus">
                            <template v-slot:prepend>
                                <q-icon name="fas fa-info"></q-icon>
                            </template>
                            </q-input>
                        </div>
                      <div class="col-xs-12 col-sm-12 pull-right">
                        <div class="q-pa-sm q-gutter-sm">
                          <q-btn color="positive" icon="save" label="Actualizar" @click="updateMovement()" v-if="!canModify"/>
                        </div>
                      </div>
                    </div>
                    <div class="row q-col-gutter-xs">
                      <!-- <div class="col-xs-12 col-sm-12 pull-right">
                        <div class="q-pa-sm q-gutter-sm">
                          <q-btn color="blue" icon="save" label="Guardar" @click="updateMovement()"/>
                        </div>
                      </div> -->
                    </div>
                    <!-- Separación -->
                   <div class="row q-col-gutter-xs" v-if="movement.fields.type_id === 5 || movement.fields.type_id === 4" >
                     <div class="col-xs-12 col-sm-2 text-center">
                         <q-select
                         color="dark"
                         bg-color="secondary"
                         filled
                         :rules="dateRulesTransfer"
                         v-model="movement_transfer.fields.date"
                         mask="date"
                         label="Fecha">
                         <template v-slot:prepend>
                             <q-icon name="event"></q-icon>
                         </template>
                         <q-popup-proxy ref="date" transition-show="scale" transition-hide="scale">
                             <div class="col-sm-12">
                                 <q-date
                                 color="secondary"
                                 text-color="white"
                                 mask="DD/MM/YYYY"
                                 v-model="movement_transfer.fields.date"
                                 today-btn>
                                 </q-date>
                             </div>
                         </q-popup-proxy>
                         </q-select>
                     </div>
                     <div class="col-sm-12 col-md-2">
                       <q-input
                         color="dark"
                         dark
                         :bg-color="movement_transfer.fields.statusStr === 'NUEVO' ? 'blue' : (movement_transfer.fields.statusStr === 'EJECUTADO' ? 'positive' : (movement_transfer.fields.statusStr === 'CANCELADO' ? 'negative' : 'negative'))"
                         filled
                         v-model="movement_transfer.fields.statusStr"
                         label="Estatus"
                         readonly
                       >
                         <template v-slot:prepend>
                           <q-icon name="battery_full" />
                         </template>
                       </q-input>
                     </div>
                    </div>
                    <div class="row q-col-gutter-xs" v-if="movement.fields.type_id === 5 || movement.fields.type_id === 4">
                      <div class="col-xs-12 col-sm-2 text-center">
                          <q-select
                          color="white"
                          bg-color="negative"
                          filled
                          dark
                          readonly
                          v-model="movement_transfer.fields.type_movement_1"
                          filter
                          label="Tipo de Movimiento">
                          <template v-slot:prepend>
                              <q-icon name="swap_horiz"></q-icon>
                          </template>
                          </q-select>
                      </div>
                      <div class="col-sm-12 col-md-4">
                        <q-select
                          color="dark"
                          bg-color="secondary"
                          filled
                          @input="() => movement_transfer.fields.originStorage=''"
                          v-model="movement_transfer.fields.originBranchOffice"
                          :options="branchOfficeOptions"
                          label="Estación origen"
                          emit-value map-options
                          :rules="originBranchOfficeRules"
                        >
                          <template v-slot:prepend>
                            <q-icon name="fas fa-store-alt" />
                          </template>
                        </q-select>
                      </div>
                      <div class="col-sm-12 col-md-4">
                        <q-select
                          color="dark"
                          bg-color="secondary"
                          filled
                          v-model="movement_transfer.fields.originStorage"
                          :options="filteredOriginStorageOptions"
                          label="Almacén origen"
                          :rules="originStorageRules"
                        >
                          <template v-slot:prepend>
                            <q-icon name="fas fa-warehouse" />
                          </template>
                        </q-select>
                      </div>
                      <div class="col-xs-12 col-sm-2 text-center">
                        <q-input
                            color="dark"
                            bg-color="secondary"
                            filled
                            readonly
                            v-model="movement_transfer.fields.folio_1"
                            label="Folio">
                          <template v-slot:prepend>
                              <q-icon name="style"></q-icon>
                          </template>
                        </q-input>
                      </div>
                      <div class="col-xs-12 col-sm-2 text-center">
                          <q-select
                          color="white"
                          bg-color="positive"
                          filled
                          dark
                          readonly
                          v-model="movement_transfer.fields.type_movement_2"
                          filter
                          label="Tipo de Movimiento">
                          <template v-slot:prepend>
                              <q-icon name="swap_horiz"></q-icon>
                          </template>
                          </q-select>
                      </div>
                      <div class="col-sm-12 col-md-4">
                        <q-select
                          color="dark"
                          bg-color="secondary"
                          filled
                          @input="() => movement_transfer.fields.destinationStorage=''"
                          v-model="movement_transfer.fields.destinationBranchOffice"
                          :options="branchOfficeOptions2"
                          label="Estación destino"
                          :rules="destinationBranchOfficeRules"
                        >
                          <template v-slot:prepend>
                            <q-icon name="fas fa-store-alt" />
                          </template>
                        </q-select>
                      </div>
                      <div class="col-sm-12 col-md-4">
                        <q-select
                          color="dark"
                          bg-color="secondary"
                          filled
                          v-model="movement_transfer.fields.destinationStorage"
                          :options="filteredDestinationStorage"
                          label="Almacén destino"
                          :rules="destinationStorageRules"
                        >
                          <template v-slot:prepend>
                            <q-icon name="fas fa-warehouse" />
                          </template>
                        </q-select>
                      </div>
                      <div class="col-xs-12 col-sm-2 text-center">
                        <q-input
                            color="dark"
                            bg-color="secondary"
                            filled
                            readonly
                            v-model="movement_transfer.fields.folio_1"
                            label="Folio">
                          <template v-slot:prepend>
                              <q-icon name="style"></q-icon>
                          </template>
                        </q-input>
                      </div>
                      <div class="col-xs-12 col-sm-12 pull-right">
                        <div class="q-pa-sm q-gutter-sm">
                          <q-btn color="positive" icon="save" label="Actualizar" @click="updateMovement()"/>
                        </div>
                      </div>
                    </div>
                  </div>
            </div>
            <!-- Mostrar el CRUD para las transferencias de movimientos -->
          <!-- Detalles del movimiento -->
          <div class="bg-grey-3" style="padding-top: 17px;">
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
                  <q-tab name="details" icon="info" label="DETALLES DE MOVIMIENTO" />
                  <q-tab name="notes" icon="speaker_notes" label="COMENTARIOS" />
                </q-tabs>
                <q-tab-panels v-model="tab" animated class="">
                  <q-tab-panel name="details">
                  <div class="row bg-white border-panel" style="margin-top:16px">
                      <div class="col q-pa-md">
                          <div class="row q-col-gutter-xs" >
                              <div class="col-xs-12 col-sm-8" style="font-size: 20px; margin-top:8px">
                                  <span>Detalles del movimiento</span>
                              </div>
                              <div v-if="this.movement.fields.type_movement === 'INVENTARIO FÍSICO'" class="col-xs-12 col-sm-12 pull-right" style="font-size: 20px; margin-bottom: 8px;">
                                  <q-btn style="margin: 2px" color="positive" icon="fas fa-file-excel" label="LAYOUT" @click="getLayoutCsv()"/>
                                  <q-btn style="margin: 2px" color="positive" icon="cloud_upload" label="CARGA MASIVA" @click="openUploadFileModal()"/>
                              </div>
                          </div>
                          <div class="row q-col-gutter-xs" v-if="!execute">
                              <!--<div class="col-xs-12 col-sm-3 text-center" v-if="movement.fields.type_id === 1 || movement.fields.type_id === 3">
                                  <q-select
                                  color="dark"
                                  bg-color="secondary"
                                  filled
                                  use-input
                                  emit-value
                                  map-options
                                  :options="productsList"
                                  v-model="movement_details.fields.product"
                                  :rules="productsRules"
                                  :error="$v.movement_details.fields.product.$error"
                                  @input="getUnit()"
                                  @filter="searchProducts"
                                  label="Producto">
                                  <template v-slot:prepend>
                                      <q-icon name="fas fa-box"></q-icon>
                                  </template>
                                  <template v-slot:no-option>
                                    <q-item>
                                      <q-item-section class="text-grey">
                                        Sin resultados
                                      </q-item-section>
                                    </q-item>
                                  </template>
                                  <template v-slot:append>
                                    <q-icon
                                    v-if="movement_details.fields.product !== null"
                                    class="cursor-pointer"
                                    name="clear"
                                    @click.stop="movement_details.fields.product = null"
                                    />
                                  </template>
                                  </q-select>
                              </div>-->
                              <!--<div class="col-xs-12 col-sm-3 text-center" v-if="movement.fields.type_id === 1 || movement.fields.type_id === 3">
                              <q-select
                              color="dark"
                              bg-color="secondary"
                              filled
                              v-model="movement_details.fields.family"
                              :error="$v.movement_details.fields.family.$error"
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
                          </div>-->
                        <div class="col-xs-12 col-sm-3 text-center" v-if="movement.fields.type_id === 1 || movement.fields.type_id === 3">
                          <q-select
                          filled
                          color="dark"
                          bg-color="secondary"
                          v-model="movement_details.fields.product"
                          :error="$v.movement_details.fields.product.$error"
                          label="Producto"
                          @input="getUnit()"
                          :options="options2"
                          use-input
                          hide-selected
                          fill-input
                          input-debounce="0"
                          @filter="filterProduct"
                          map-options
                          :disable="canModify"
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
                              <div class="col-xs-12 col-sm-3 text-center" v-if="movement.fields.type_id === 2 || movement.fields.type_id === 4 || movement.fields.type_id === 5 || movement.fields.type_id === 6">
                                  <q-select
                                  color="dark"
                                  bg-color="secondary"
                                  filled
                                  map-options
                                  use-input
                                  hide-selected
                                  fill-input
                                  input-debounce="0"
                                  @filter="filterProduct"
                                  :options="options2"
                                  v-model="movement_details.fields.product"
                                  :error="$v.movement_details.fields.product.$error"
                                  @input="getQtyProducts(movement_details.fields.product)"
                                  label="Producto"
                                  :disable="canModify">
                                  <template v-slot:prepend>
                                      <q-icon name="fas fa-box"></q-icon>
                                  </template>
                                  </q-select>
                              </div>
                              <div class="col-xs-12 col-sm-3 text-center">
                                  <q-select
                                  color="dark"
                                  bg-color="secondary"
                                  filled
                                  emit-value
                                  map-options
                                  readonly
                                  v-model="movement_details.fields.unit"
                                  :rules="unitsRules"
                                  :error="$v.movement_details.fields.unit.$error"
                                  label="Unidad"
                                  :disable="canModify">
                                  <template v-slot:prepend>
                                      <q-icon name="fas fa-box"></q-icon>
                                  </template>
                                  </q-select>
                              </div>
                              <div class="col-xs-12 col-sm-3 text-center" v-if="movement.fields.type_id === 2 || movement.fields.type_id === 4 || movement.fields.type_id === 5">
                                  <q-input
                                  color="dark"
                                  bg-color="secondary"
                                  filled
                                  :rules="amountRules"
                                  v-model="movement_details.fields.qty"
                                  :suffix="`/${suffixQty}`"
                                  @input="validateQty(movement_details.fields.qty)"
                                  label="Cantidad"
                                  :disable="canModify">
                                  <template v-slot:prepend>
                                      <q-icon name="style"></q-icon>
                                  </template>
                                  </q-input>
                              </div>
                              <div class="col-xs-12 col-sm-3 text-center" v-if="movement.fields.type_id === 6">
                                  <q-input
                                  color="dark"
                                  bg-color="secondary"
                                  filled
                                  :rules="amountRules"
                                  v-model="movement_details.fields.qty"
                                  @input="validateQty(movement_details.fields.qty)"
                                  label="Cantidad"
                                  :disable="canModify">
                                  <template v-slot:prepend>
                                      <q-icon name="style"></q-icon>
                                  </template>
                                  </q-input>
                              </div>
                              <div class="col-xs-12 col-sm-3 text-center" v-if="movement.fields.type_id === 1 || movement.fields.type_id === 3" >
                                  <q-input
                                  color="dark"
                                  bg-color="secondary"
                                  filled
                                  :rules="amountRules"
                                  v-model="movement_details.fields.qty"
                                  label="Cantidad"
                                  :disable="canModify">
                                  <template v-slot:prepend>
                                      <q-icon name="style"></q-icon>
                                  </template>
                                  </q-input>
                              </div>
                              <div class="col-xs-12 col-sm-3 text-center">
                                  <q-input
                                  v-if="movement.fields.type_id === 1 || movement.fields.type_id === 3"
                                  :required="show"
                                  color="dark"
                                  bg-color="secondary"
                                  filled
                                  v-model="movement_details.fields.cost"
                                  label="Costo Unitario"
                                  :disable="canModify">
                                  <template v-slot:prepend>
                                      <q-icon name="fas fa-dollar-sign"></q-icon>
                                  </template>
                                  </q-input>
                              </div>
                              <div class="col-sm-12 pull-right" v-if="!execute">
                                <div class="q-pa-sm q-gutter-sm" style="padding-bottom: 10px" v-if="!updateFlag">
                                  <q-btn class="bg-primary" color="positive" icon="add" label="Agregar" @click="addProduct()" v-if="!canModify"/>
                                </div>
                                <div class="q-pa-sm q-gutter-sm" style="padding-bottom: 10px" v-if="updateFlag">
                                  <q-btn color="positive" icon="save" label="Actualizar" @click="updateDetail()"/>
                                </div>
                              </div>
                          </div>
                          <div class="row q-mb-sm q-mt-md">
                              <div class="col-sm-12 ">
                                <div class="q-pa-sm q-gutter-sm">
                                  <q-table flat bordered :data="dataMD" :pagination.sync="pagination" :columns="movement.fields.type_id == 1 || movement.fields.type_id == 2 ? movement.fields.type_id == 1 ? columns:columnsExit:movement.fields.type_id == 3 ? columnsFisic:columnsTrans || movement.fields.type_id == 6 ? columnsFisic:columnsMerma" :filter="filter" row-key="product_name" >
                                    <template v-slot:top>
                                      <div style="width: 100%">
                                        <q-input dense debounce="300" v-model="filter" placeholder="Buscar">
                                          <template v-slot:append>
                                            <q-icon name="search"></q-icon>
                                          </template>
                                        </q-input>
                                      </div>
                                    </template>
                                    <template v-slot:body="props">
                                        <q-tr :props="props">
                                          <q-td key="code" style="text-align: center" :props="props">{{ props.row.code }}</q-td>
                                            <q-td key="product_name" style="width:20%; text-align: left" :props="props">{{ props.row.product_name }}</q-td>
                                            <!-- <q-td key="stock" style="text-align: right;" :props="props">{{ formatPrice(props.row.current) || 0}}</q-td> -->
                                            <q-td key="quantity" style="text-align: right;" :props="props">{{ $formatNumberThree(Number(props.row.qty)) || 0 }}</q-td>
                                            <q-td key="cost" style="text-align: right;" :props="props">{{'$ ' + $formatNumberPrice(props.row.cost) }}</q-td>
                                            <q-td key="qty" style="text-align: right;" :props="props">{{ '$ ' + $formatNumberPrice(props.row.qty * props.row.cost) }}</q-td>
                                            <q-td  :class="(props.row.balance < 0 )?'bg-red text-white':'bg-white text-black'" key="balance" :props="props" v-if="movement.fields.type_id !== 3 ? true : false">{{ props.row.balance ?  $formatNumberThree(Number(props.row.balance)): $formatNumberThree(Number(props.row.qty)) }}</q-td>
                                            <q-td key="actions" style="text-align: center;" :props="props">
                                                <!-- <q-btn  v-if="!execute" color="secondary" flat icon="fas fa-edit"  @click.native="editSelectedRow(props.row.id)" size="10px">
                                                  <q-tooltip content-class="bg-secondary">Editar</q-tooltip>
                                                </q-btn> -->
                                                <q-btn v-if="!execute && !canModify" color="red" flat icon="fas fa-trash-alt" @click.native="deleteSelectedRow(props.row.id)" size="10px">
                                                  <q-tooltip content-class="bg-red">Eliminar</q-tooltip>
                                                </q-btn>
                                                <q-icon name="check" size="30px" color="positive" v-if="execute && !canModify">
                                                  <q-tooltip content-class="bg-primary">Ejecutado</q-tooltip>
                                                </q-icon>
                                            </q-td>
                                        </q-tr>
                                    </template>
                                  </q-table>
                                </div>
                              </div>
                          </div>
                      </div>
                  </div>
                </q-tab-panel>
                <q-tab-panel name="notes">
                  <div class="row bg-white border-panel" style="margin-top:16px">
                    <div class="col q-pa-md">
                      <div class="row q-col-gutter-xs" >
                        <div class="col-xs-12 col-sm-12" style="font-size: 20px; margin-top:8px">
                          <span>Comentarios</span>
                        </div>
                        <div class="col-sm-12 col-md-10">
                          <q-input
                            color="dark"
                            bg-color="secondary"
                            filled
                            v-model="note.fields.note"
                            label="Comentarios"
                            :rules="noteRules"
                            :error="$v.note.fields.note.$error"
                            :disable="canModify"
                          >
                            <template v-slot:prepend>
                              <q-icon name="note" />
                            </template>
                          </q-input>
                        </div>
                        <div class="col-xs-12 col-sm-2 pull-right" style="padding-top: 19px">
                          <q-btn class="bg-primary" color="positive" icon="add" label="Agregar" @click="createNote()" v-if="!canModify"/>
                        </div>
                      </div>
                      <div class="row q-mb-sm q-mt-md">
                        <div class="col-sm-12 ">
                          <div class="q-gutter-sm">
                            <q-table flat bordered :data="dataNotes" :pagination.sync="pagination" :columns="columnsNotes" :filter="filter" row-key="product_name" >
                              <!-- <template v-slot:top>
                                <div style="width: 100%">
                                  <q-input dense debounce="300" v-model="filter" placeholder="Buscar">
                                    <template v-slot:append>
                                      <q-icon name="search"></q-icon>
                                    </template>
                                  </q-input>
                                </div>
                              </template> -->
                              <template v-slot:body="props">
                                <q-tr :props="props">
                                  <q-td key="note" style="text-align: left;" :props="props">{{ props.row.note }}</q-td>
                                  <q-td key="note_date" style="text-align: center;" :props="props">{{ props.row.note_date.substr(8, 8) + props.row.note_date.substr(4, 4) + props.row.note_date.substr(0, 4) }}</q-td>
                                  <!-- <q-td key="note_time" style="text-align: center;" :props="props">{{ props.row.note_time }}</q-td> -->
                                  <q-td key="actions" style="text-align: center;" :props="props">
                                    <q-btn color="red"  flat icon="fas fa-trash-alt" @click.native="deleteSelectedNote(props.row.id)" size="10px">
                                      <q-tooltip content-class="bg-red">Eliminar</q-tooltip>
                                    </q-btn>
                                  </q-td>
                                </q-tr>
                              </template>
                            </q-table>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </q-tab-panel>
              </q-tab-panels>
            </q-card>
          </div>
        </div>
      </div>
      <q-dialog v-model="documentFileModal" persistent>
        <q-card>
          <q-card-section class="row">
            <div class="col-xs-12 col-sm-10 text-h6">Archivo: {{ documentName }}</div>
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
            <q-btn flat label="Subir archivo" @click="uploadDocumentFile()" />
          </q-card-actions>
        </q-card>
      </q-dialog>
    </q-page>
</template>
<script>
import api from '../../commons/api.js'
const { required, maxLength } = require('vuelidate/lib/validators')
export default {
  name: 'entry',
  validations: {
    movement_transfer: {
      fields: {
        originBranchOffice: { required },
        originStorage: { required },
        destinationBranchOffice: { required },
        destinationStorage: { required },
        date: { required }
      }
    },
    movement: {
      fields: {
        branch: { required },
        storage: { required },
        date: { required },
        po: { required }
      }
    },
    movement_details: {
      fields: {
        unit: { required },
        qty: { required },
        product: { required }
      }
    },
    note: {
      fields: {
        note: { required, maxLength: maxLength(250) }
      }
    }
  },
  data () {
    return {
      suffixQty: 0,
      tab: 'details',
      myLocale: {
        /* starting with Sunday */
        days: 'Domingo_Lunes_Martes_Miércoles_Jueves_Viernes_Sábado'.split('_'),
        daysShort: 'Dom_Lun_Mar_Mié_Jue_Vie_Sáb'.split('_'),
        months: 'Enero_Febrero_Marzo_Abril_Mayo_Junio_Julio_Agosto_Septiembre_Octubre_Noviembre_Diciembre'.split('_'),
        monthsShort: 'Ene_Feb_Mar_Abr_May_Jun_Jul_Ago_Sep_Oct_Nov_Dic'.split('_'),
        firstDayOfWeek: 1
      },
      filter: '',
      pagination: {
        sortBy: 'code',
        descending: false,
        rowsPerPage: 25
      },
      columns: [
        { name: 'product_name', align: 'center', label: 'Producto'.toUpperCase(), field: 'product_name', style: 'width: 20%', sortable: true },
        { name: 'quantity', align: 'center', label: 'Cantidad'.toUpperCase(), field: 'quantity', style: 'width: 10%', sortable: true },
        { name: 'cost', align: 'center', label: 'Costo'.toUpperCase(), field: 'cost', style: 'width: 10%', sortable: true },
        { name: 'qty', align: 'center', label: 'Monto'.toUpperCase(), field: 'qty', style: 'width: 10%', sortable: true },
        { name: 'actions', align: 'center', label: 'Acciones'.toUpperCase(), field: 'actions', style: 'width: 10%', sortable: false }
      ],
      columnsExit: [
        { name: 'product_name', align: 'center', label: 'Producto'.toUpperCase(), field: 'product_name', style: 'width: 20%', sortable: true },
        // { name: 'stock', align: 'center', label: 'Existencias'.toUpperCase(), field: 'stock', style: 'width: 10%', sortable: true },
        { name: 'quantity', align: 'center', label: 'Cantidad'.toUpperCase(), field: 'quantity', style: 'width: 10%', sortable: true },
        { name: 'cost', align: 'center', label: 'Costo'.toUpperCase(), field: 'cost', style: 'width: 10%', sortable: true },
        { name: 'qty', align: 'center', label: 'Monto'.toUpperCase(), field: 'qty', style: 'width: 10%', sortable: true },
        // { name: 'balance', align: 'center', label: 'Saldo'.toUpperCase(), field: 'balance', style: 'width: 10%', sortable: true },
        { name: 'actions', align: 'center', label: 'Acciones'.toUpperCase(), field: 'actions', style: 'width: 10%', sortable: false }
      ],
      columnsTrans: [
        { name: 'product_name', align: 'center', label: 'Producto'.toUpperCase(), field: 'product_name', style: 'width: 20%', sortable: true },
        // { name: 'stock', align: 'center', label: 'Existencias'.toUpperCase(), field: 'stock', style: 'width: 10%', sortable: true },
        { name: 'quantity', align: 'center', label: 'Cantidad'.toUpperCase(), field: 'quantity', style: 'width: 10%', sortable: true },
        { name: 'balance', align: 'center', label: 'Saldo'.toUpperCase(), field: 'balance', style: 'width: 10%', sortable: true },
        { name: 'actions', align: 'center', label: 'Acciones'.toUpperCase(), field: 'actions', style: 'width: 10%', sortable: false }
      ],
      columnsMerma: [
        { name: 'product_name', align: 'center', label: 'Producto'.toUpperCase(), field: 'product_name', style: 'width: 20%', sortable: true },
        // { name: 'stock', align: 'center', label: 'Existencias'.toUpperCase(), field: 'stock', style: 'width: 10%', sortable: true },
        { name: 'quantity', align: 'center', label: 'Cantidad'.toUpperCase(), field: 'quantity', style: 'width: 10%', sortable: true },
        // { name: 'balance', align: 'center', label: 'Saldo'.toUpperCase(), field: 'balance', style: 'width: 10%', sortable: true },
        { name: 'actions', align: 'center', label: 'Acciones'.toUpperCase(), field: 'actions', style: 'width: 10%', sortable: false }
      ],
      columnsFisic: [
        { name: 'code', align: 'center', label: 'Código'.toUpperCase(), field: 'code', style: 'width: 20%', sortable: true },
        { name: 'product_name', align: 'center', label: 'Producto'.toUpperCase(), field: 'product_name', style: 'width: 20%', sortable: true },
        // { name: 'stock', align: 'center', label: 'Existencias'.toUpperCase(), field: 'stock', style: 'width: 10%', sortable: true },
        { name: 'quantity', align: 'center', label: 'Cantidad'.toUpperCase(), field: 'quantity', style: 'width: 10%', sortable: true },
        // { name: 'cost', align: 'center', label: 'Costo'.toUpperCase(), field: 'cost', style: 'width: 10%', sortable: true },
        // { name: 'balance', align: 'center', label: 'Saldo'.toUpperCase(), field: 'balance', style: 'width: 10%', sortable: true },
        { name: 'actions', align: 'center', label: 'Acciones'.toUpperCase(), field: 'actions', style: 'width: 10%', sortable: false }
      ],
      columnsNotes: [
        { name: 'note', align: 'center', label: 'Nota'.toUpperCase(), field: 'note', style: 'width: 20%', sortable: true },
        { name: 'note_date', align: 'center', label: 'Fecha'.toUpperCase(), field: 'note_date', style: 'width: 20%', sortable: true }
        /* { name: 'note_time', align: 'center', label: 'Hora'.toUpperCase(), field: 'note_time', style: 'width: 20%', sortable: true } */
      ],
      movement_transfer: {
        fields: {
          entry_id: null,
          exit_id: null,
          date: null,
          originBranchOffice: null,
          originStorage: null,
          destinationBranchOffice: null,
          destinationStorage: null,
          statusStr: null,
          transactionId: null,
          folio_1: null,
          folio_2: null,
          type_movement_1: 'TRASPASO (SALIDA)',
          type_movement_2: 'TRASPASO (ENTRADA)'
        }
      },
      movement: {
        fields: {
          type_movement: null,
          status: null,
          folio: null,
          branch: null,
          storage: null,
          type_id: null,
          date: null
        }
      },
      movement_details: {
        fields: {
          id: null,
          unit: null,
          qty: null,
          cost: null,
          movement_id: null,
          // family: null,
          product: null
        }
      },
      note: {
        fields: {
          note: null,
          movement_id: this.$route.params.id,
          date: null,
          time: null
        }
      },
      execute: false,
      balanceValidation: false,
      productInvalid: null,
      show: true,
      updateFlag: false,
      branchesList: [],
      storageList: [],
      productsList: [],
      unitsList: [],
      data: [],
      dataMD: [],
      dataNotes: [],
      branchOfficeOptions: [],
      branchOfficeOptions2: [],
      storageOptions: [],
      storageOptions2: [],
      rawMaterialProductOptions: [],
      rawMaterialProducts: [],
      rawMaterials: [],
      options1: this.categoryOptions,
      options2: this.ProductsOptionsbyLines,
      categoryOptions: [],
      ProductsOptionsbyLines: [],
      color: 'positive',
      documentFileModal: false,
      documentName: null,
      filteredProductOptions: []
    }
  },
  created () {
    this.fetchFromServer()
    this.getAllProducts()
    this.getBranchesList()
    this.getStoragesList()
    // this.getProductsList()
    this.getCategories()
    this.getNotes()
    console.log('que soy ' + this.movement.fields.type_id)
  },
  mounted () {
  },
  computed: {
    token () {
      const token = 'Bearer ' + localStorage.getItem('JWT')
      return token
    },
    amountRules (val) {
      return [
        val => (this.$v.movement_details.fields.qty.required) || 'El campo Cantidad es requerido.'
      ]
      /* const reg = /^([0-9])*$/
      if (!reg.test(this.movement_details.fields.qty) && this.movement_details.fields.qty !== null) {
        return [
          val => 'Solo números enteros'
        ]
      } else {
        return [
          val => (this.$v.movement_details.fields.qty.required) || 'El campo Cantidad es requerido.'
        ]
      } */
    },
    canModify () {
      return this.movement.fields.status === 'CANCELADO'
    },
    dateRules (val) {
      return [
        val => (this.$v.movement.fields.date.required) || 'El campo Fecha es requerido.'
      ]
    },
    dateRulesTransfer (val) {
      return [
        val => (this.$v.movement_transfer.fields.date.required) || 'El campo Fecha es requerido.'
      ]
    },
    productsRules (val) {
      return [
        val => (this.$v.movement_details.fields.product.required) || 'El campo Producto es requerido.'
      ]
    },
    unitsRules (val) {
      return [
        val => (this.$v.movement_details.fields.unit.required) || 'El campo Unidad es requerido.'
      ]
    },
    noteRules (val) {
      return [
        val => (this.$v.note.fields.note.required) || 'El campo Notas es requerido.',
        val => (this.$v.note.fields.note.maxLength) || 'El campo Notas no debe exceder los 250 caracteres.'
      ]
    },
    branchOfficeRules (val) {
      return [
        val => (this.$v.movement.fields.branch.required) || 'El campo estación es requerido.'
      ]
    },
    storageRules (val) {
      return [
        val => (this.$v.movement.fields.storage.required) || 'El campo Almacén es requerido.'
      ]
    },
    filteredStorageOptions () {
      if (this.movement.fields.branch != null && this.movement.fields.branch.value != null) {
        return this.storageList.filter(storage => storage.branchOffice === this.movement.fields.branch.value)
      }
      return []
    },
    // Computeds para transferencias
    filteredOriginStorageOptions () {
      if (this.movement_transfer.fields.originBranchOffice != null && !isNaN(this.movement_transfer.fields.originBranchOffice)) {
        return this.storageOptions.filter(op => Number(op.branchOffice) === Number(this.movement_transfer.fields.originBranchOffice))
      }
      return []
    },
    filteredDestinationStorage () {
      if (this.movement_transfer.fields.destinationBranchOffice != null && !isNaN(this.movement_transfer.fields.destinationBranchOffice.value)) {
        return this.storageOptions2.filter(op => Number(op.branchOffice) === Number(this.movement_transfer.fields.destinationBranchOffice.value) && Number(this.movement_transfer.fields.originStorage.value) !== Number(op.value))
      }
      return []
    },
    originBranchOfficeRules (val) {
      return [
        val => (this.$v.movement_transfer.fields.originBranchOffice.required) || 'El campo estación origen es requerido.'
      ]
    },
    originStorageRules (val) {
      return [
        val => (this.$v.movement_transfer.fields.originStorage.required) || 'El campo Almacén origen es requerido.'
      ]
    },
    destinationBranchOfficeRules (val) {
      return [
        val => (this.$v.movement_transfer.fields.destinationBranchOffice.required) || 'El campo estación destino es requerido.'
      ]
    },
    destinationStorageRules (val) {
      return [
        val => (this.$v.movement_transfer.fields.destinationStorage.required) || 'El campo Almacén destino es requerido.'
      ]
    },
    bomcategoryRules (val) {
      return [
        val => this.$v.movement_details.fields.family || 'El campo Categorías es requerido.'
      ]
    },
    bomproductsRules (val) {
      return [
        val => this.$v.movement_details.fields.product.required || 'El campo Productos es requerido.'
      ]
    },
    rawMaterialAvailableQty () {
      let availableQty = 0
      if (this.movement_details.fields.product !== null && !isNaN(this.movement_details.fields.product.value)) {
        availableQty = this.rawMaterialProducts.filter(ibp => Number(ibp.product_id) === Number(this.movement_details.fields.product.value))[0].stock
      } else if (this.movement_details.fields.product !== null && !isNaN(this.movement_details.fields.product)) {
        availableQty = this.rawMaterialProducts.filter(ibp => Number(ibp.product_id) === Number(this.movement_details.fields.product))[0].stock
      }
      return availableQty
    }
  },
  /* beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(2)) {
      this.$router.push('/')
    }
  }, */
  beforeRouteEnter (to, from, next) {
    next(vm => {
      const propiedades = vm.$store.getters['users/rol']
      console.log(propiedades)
      if (propiedades === 1 || propiedades === 3 || propiedades === 7 || propiedades === 2 || propiedades === 20 || propiedades === 4 || propiedades === 27 || propiedades === 22 || propiedades === 26) {
        next()
      } else {
        next('/')
      }
    })
  },
  methods: {
    validateQty (qty) {
      if (Number(this.suffixQty) < Number(qty)) {
        this.$q.notify({
          message: 'Stock insuficiente para dar salida: ',
          position: 'top',
          color: 'warning',
          icon: 'close'
        })
        this.movement_details.fields.qty = null
      }
    },
    formatPrice (value) {
      const val = (value / 1).toFixed(1).replace(',', '.')
      return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',') + 0
    },
    generatePDF () {
      const id = this.$route.params.id
      const uri = process.env.API + `movements/movementPdf/${id}`
      window.open(uri, '_blank')
    },
    generatePDFSI () {
      const id = this.$route.params.id
      const uri = process.env.API + `movements/movementPdfsi/${id}`
      window.open(uri, '_blank')
    },
    filterProducts (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.filteredProductOptions = this.rawMaterialProductOptions.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    executeMovement () {
      const idMovement = this.$route.params.id
      if (this.balanceValidation) {
        this.$q.notify({
          message: 'Stock insuficiente para el producto: ' + this.productInvalid,
          position: 'top',
          color: 'warning',
          icon: 'close'
        })
      } else {
        this.$q.dialog({
          title: 'Confirmación',
          message: '¿Desea ejecutar el movimiento?',
          persistent: true,
          ok: { label: 'Aceptar', color: 'positive' },
          cancel: { label: 'Cancelar', color: 'negative' }
        }).onOk(() => {
          api.put(`movements/execute/${idMovement}`).then(({ data }) => {
            this.$q.notify({
              message: data.message.content,
              position: 'top',
              color: (data.result ? 'positive' : 'warning')
            })
            if (data.result) {
              this.$q.loading.hide()
              this.$router.push('/movements/')
            }
          })
        }).onCancel(() => {})
      }
    },
    updateDetail () {
      this.$v.movement_details.fields.$reset()
      this.$v.movement_details.fields.$touch()
      if (this.$v.movement_details.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones',
          persisten: true
        })
        return false
      } else {
        this.$q.loading.show()
        var params = { ...this.movement_details.fields }
        api.put(`movement-details/${params.id}`, params).then(({ data }) => {
          this.$q.notify({
            message: data.message.content,
            position: 'top',
            color: (data.result ? 'positive' : 'warning')
          })
          if (data.result) {
            this.$q.loading.hide()
            this.fetchFromServer()
            this.clearInputs()
            this.updateFlag = !this.updateFlag
          }
        })
      }
    },
    invertDate (date) {
      if (date !== null) {
        var info = date.split('/').reverse().join('-')
      }
      return info
    },
    updateMovement () {
      var params = []
      if (this.movement.fields.type_id !== 4 && this.movement.fields.type_id !== 5) {
        this.$v.movement.fields.$reset()
        this.$v.movement.fields.$touch()
        this.$q.loading.show()
        params.id = Number(this.movement.fields.id)
        params.date = this.invertDate(this.movement.fields.date)
        params.storage_id = Number(this.movement.fields.storage.value)
        api.put(`movements/${params.id}`, params).then(({ data }) => {
          this.$q.notify({
            message: data.message.content,
            position: 'top',
            color: (data.result ? 'positive' : 'warning')
          })
          if (data.result) {
            this.$q.loading.hide()
            this.fetchFromServer()
            this.clearInputs()
          }
        })
      } else {
        this.$q.loading.show()
        if (this.$v.movement_transfer.fields.$error) {
          this.$q.dialog({
            title: 'Error',
            message: 'Por favor, verifique las validaciones',
            persisten: true
          })
          return false
        } else {
          params.id = Number(this.movement_transfer.fields.exit_id)
          params.id_entry = Number(this.movement_transfer.fields.entry_id)
          params.date = this.invertDate(this.movement_transfer.fields.date)
          params.storage_entry_id = Number(this.movement_transfer.fields.destinationStorage.value)
          params.storage_exit_id = Number(this.movement_transfer.fields.originStorage.value)
          api.put(`branch-transfers/${params.id}`, params).then(({ data }) => {
            this.$q.notify({
              message: data.message.content,
              position: 'top',
              color: (data.result ? 'positive' : 'warning')
            })
            if (data.result) {
              this.$q.loading.hide()
              this.fetchFromServer()
              this.clearInputs()
            }
          })
        }
      }
    },
    clearInputs () {
      this.movement_details.fields.category = null
      this.movement_details.fields.product = null
      this.movement_details.fields.qty = null
      this.movement_details.fields.cost = null
      this.movement_details.fields.unit = null
      this.suffixQty = 0
    },
    deleteSelectedRow (id) {
      this.$q.loading.show()
      api.delete(`/movement-details/${id}`).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.balanceValidation = false
          this.fetchFromServer()
        } else {
          this.$q.loading.hide()
        }
      })
    },
    editSelectedRow (id) {
      this.$q.loading.show()
      api.get(`/movement-details/${id}`).then(({ data }) => {
        this.movement_details.fields = data.movement_detail
        this.movement_details.fields.product = { value: data.movement_detail.product, label: data.movement_detail.product }
        this.movement_details.fields.unit = { value: data.movement_detail.unit_id, label: data.movement_detail.unit }
        this.updateFlag = true
      })
      this.$q.loading.hide()
    },
    addProduct () {
      this.$v.movement_details.fields.$reset()
      this.$v.movement_details.fields.$touch()
      if (this.$v.movement_details.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones',
          persisten: true
        })
        return false
      }
      this.$q.loading.show()
      const params = []
      params.product_id = Number({ ...this.movement_details.fields }.product.value)
      params.qty = Number({ ...this.movement_details.fields }.qty)
      params.unit_price = Number({ ...this.movement_details.fields }.cost)
      if (this.movement.fields.type_id === 4) {
        params.movement_id = Number({ ...this.movement_transfer.fields }.entry_id)
      } else if (this.movement.fields.type_id === 5) {
        params.movement_id = Number({ ...this.movement_transfer.fields }.exit_id)
      } else {
        params.movement_id = Number({ ...this.movement.fields }.movement_id)
      }
      api.post('movement-details/', params).then(({ data }) => {
        // const idMovement = data.movement_id
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.fetchFromServer()
          this.clearInputs()
        } else {
          this.$q.loading.hide()
        }
      })
    },
    searchPO (val, update) {
      if (val.length < 3) {
        return false
      }
      update(() => {
        api.get(`purchase-orders/searchClave/${val}`).then(({ data }) => {
          this.poList = data.claves
        })
      })
    },
    searchProducts (val, update) {
      if (val.length < 3) {
        return false
      }
      update(() => {
        api.get(`products/searchProducts/${val}`).then(({ data }) => {
          this.productsList = data.claves
        })
      })
    },
    getQtyProducts (idproducto) {
      const params = {}
      params.product_id = idproducto.value
      const originStorage = this.movement.fields.storage.value
      api.post(`/storages/${originStorage}/raw-materials-data-to-out`, params).then(({ data }) => {
        // console.log(data)
        // this.movement_details.fields.product = { value: data.products[0].product_id, label: data.products[0].product_name }
        this.suffixQty = data.stock
        // movement_details.fields.unit
        api.get(`/storages/getUnitProduct/${idproducto.value}`).then(({ data }) => {
          this.movement_details.fields.unit = data.unit[0].name
        })
      })
    },
    getUnit () {
      this.movement_details.fields.qty = null
      const id = this.movement_details.fields.product
      console.log(id)
      api.get(`products/${id.value}`).then(({ data }) => {
        this.movement_details.fields.unit = { value: data.product.unit_id, label: data.product.unit }
      })
    },
    fetchFromServer () {
      this.$q.loading.show()
      const id = this.$route.params.id
      api.get(`movements/out/${id}`).then(({ data }) => {
        if (data.movement.status === 'EJECUTADO') {
          this.execute = true
        }
        if (!data.movement) {
          this.$q.notify({
            message: data.message.content,
            position: 'top-right',
            color: (data.result ? 'positive' : 'warning')
          })
        } else {
          if (data.movement.type_id === 1) {
            api.get(`movements/entry/${id}`).then(({ data }) => {
              this.movement.fields = data.movement
              this.movement.fields.type_movement = 'ENTRADA'
              this.movement.fields.movement_id = data.movement.id
              this.movement.fields.branch = { value: data.movement.branch_id, label: data.movement.branch }
              this.movement.fields.storage = { value: data.movement.storage_id, label: data.movement.storage }
              this.getAvailableQtyEntry(this.movement.fields.storage.value, data.movement.details)
            })
          } else if (data.movement.type_id === 2) {
            this.movement.fields = data.movement
            this.movement.fields.type_movement = 'SALIDA'
            this.color = 'negative'
            this.movement.fields.movement_id = data.movement.id
            this.movement.fields.branch = { value: data.movement.branch_id, label: data.movement.branch }
            this.movement.fields.storage = { value: data.movement.storage_id, label: data.movement.storage }
            this.movement.fields.po = { value: null, label: null }
            this.dataMD = data.movement.details
            this.getAvailableQtyEntry(this.movement.fields.storage.value, data.movement.details)
            // this.getAvailableQty(this.movement.fields.storage.value, data.movement.details)
          } else if (data.movement.type_id === 3) {
            this.movement.fields = data.movement
            this.movement.fields.type_movement = 'INVENTARIO FÍSICO'
            this.movement.fields.movement_id = data.movement.id
            this.movement.fields.branch = { value: data.movement.branch_id, label: data.movement.branch }
            this.movement.fields.storage = { value: data.movement.storage_id, label: data.movement.storage }
            this.movement.fields.po = { value: null, label: null }
            this.dataMD = data.movement.details
            this.color = 'light-blue'
          } else if (data.movement.type_id === 5 || data.movement.type_id === 4) {
            this.movement.fields.type_id = data.movement.type_id
            this.dataMD = data.movement.details
            console.log('entre:')
            console.log(data.movement.details)
            api.get(`branch-transfers/${id}`).then(({ data }) => {
              this.movement_transfer.fields.originBranchOffice = { label: data.branchTransfer.origin_branch_office_name, value: data.branchTransfer.origin_branch_office_id }
              this.movement_transfer.fields.originStorage = { label: data.branchTransfer.origin_storage_name, value: data.branchTransfer.origin_storage_id }
              this.movement_transfer.fields.destinationBranchOffice = { label: data.branchTransfer.destination_branch_office_name, value: data.branchTransfer.destination_branch_office_id }
              this.movement_transfer.fields.destinationStorage = { label: data.branchTransfer.destination_storage_name, value: data.branchTransfer.destination_storage_id }
              this.movement_transfer.fields.folio_1 = data.branchTransfer.origin_folio
              this.movement_transfer.fields.folio_2 = data.branchTransfer.destination_folio
              this.movement_transfer.fields.statusStr = data.branchTransfer.status
              this.movement_transfer.fields.date = data.branchTransfer.date
              this.movement_transfer.fields.entry_id = data.branchTransfer.destination_movement_id // Entrada
              this.movement_transfer.fields.exit_id = data.branchTransfer.origin_movement_id // Salida
              this.movement.fields.storage = { label: data.branchTransfer.origin_storage_name, value: data.branchTransfer.origin_storage_id }
              // this.getAvailableQty(this.movement_transfer.fields.originStorage.value)
            })
          } else if (data.movement.type_id === 6) {
            this.movement.fields = data.movement
            this.movement.fields.type_movement = 'MERMA'
            this.color = 'purple'
            this.movement.fields.movement_id = data.movement.id
            this.movement.fields.branch = { value: data.movement.branch_id, label: data.movement.branch }
            this.movement.fields.storage = { value: data.movement.storage_id, label: data.movement.storage }
            this.movement.fields.po = { value: null, label: null }
            this.dataMD = data.movement.details
            this.getAvailableQtyEntry(this.movement.fields.storage.value, data.movement.details)
            // this.getAvailableQty(this.movement.fields.storage.value, data.movement.details)
          }
        }
        this.$q.loading.hide()
      })
    },
    getAvailableQty (originStorage, details) {
      const params = details
      api.post(`/storages/${originStorage}/raw-materials-data`, params).then(({ data }) => {
        this.rawMaterials = []
        this.rawMaterialProducts = []
        this.rawMaterialProductOptions = []
        let stock = 0
        data.products.forEach(product => {
          const details = this.dataMD.filter(det => Number(det.product_id) === Number(product.product_id))
          details.forEach(det => {
            product.stock -= det.qty
            stock += Number(det.qty)
          })
          stock += Number(product.stock) // Stock General
          details.forEach(p => {
            p.stock = stock
            if (this.movement.fields.type_movement === 'SALIDA') {
              p.balance = Number(p.stock)
            }
            if (this.movement.fields.type_id === 5 || this.movement.fields.type_id === 4) {
              p.balance = Number(p.stock)
            }
            stock = p.balance
            if (p.balance < 0 && this.balanceValidation === false) {
              this.balanceValidation = !this.balanceValidation
              this.productInvalid = p.product_name
            }
            this.rawMaterials.push(p)
          })
          stock = 0
          this.rawMaterialProducts.push(product)
          this.rawMaterialProductOptions.push({ value: product.product_id, label: `${product.category_code}-${product.line_code}-${product.product_name}` })
        })
        this.dataMD = this.rawMaterials
        this.$q.loading.hide()
      })
    },
    // getPoList () {
    //   api.get('purchase-orders/getOptions2').then(({ data }) => {
    //     this.poList = data.options
    //   })
    // },
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
    },
    getProductsList () {
      api.get('products/options').then(({ data }) => {
        this.productsList = data.options
      })
    },
    getCategories () {
      api.get('/categories/options').then(({ data }) => {
        this.categoryOptions = data.options
      })
    },
    getNotes () {
      const id = this.$route.params.id
      api.get(`/notes/${id}`).then(({ data }) => {
        this.dataNotes = data.notes
      })
    },
    getAllProducts () {
      api.get('/products/category2').then(({ data }) => {
        this.ProductsOptionsbyLines = data.products
        /* if (data.products.length > 0) {
          data.products.forEach(branch => {
            this.ProductsOptionsbyLines.push({ label: branch.label, value: branch.id })
          })
        } */
      })
    },
    getLinesByCategories () {
      this.ProductsOptionsbyLines = []
      this.ProductsOptionsbyLines.unshift({ value: null, label: 'NINGUNO' })
      api.get(`/products/category1/${this.movement_details.fields.family}`).then(({ data }) => {
        // this.ProductsOptionsbyLines = data.products
        if (data.products.length > 0) {
          data.products.forEach(branch => {
            this.ProductsOptionsbyLines.push({ label: branch.label, value: branch.id })
          })
        }
      })
    },
    filterCategory (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.options1 = this.categoryOptions.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    filterProduct (val, update, abort) {
      const listproducts = []
      this.ProductsOptionsbyLines.forEach(product => {
        listproducts.push({ label: product.label, value: product.id })
      })
      console.log(this.ProductsOptionsbyLines)
      update(() => {
        const needle = val.toLowerCase()
        this.options2 = listproducts.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    deleteSelectedNote (id) {
      this.$q.dialog({
        title: 'Confirmación',
        message: '¿Desea eliminar esta Nota?',
        persistent: true,
        ok: { label: 'Aceptar', color: 'positive' },
        cancel: { label: 'Cancelar', color: 'negative' }
      }).onOk(() => {
        api.delete(`/notes/${id}`).then(({ data }) => {
          this.$q.notify({
            message: data.message.content,
            position: 'top',
            color: (data.result ? 'positive' : 'warning'),
            icon: (data.result ? 'thumb_up' : 'close')
          })
          if (data.result) {
            this.$q.loading.hide()
            this.getNotes()
          } else {
            this.$q.loading.hide()
          }
        })
      }).onCancel(() => {})
    },
    createNote () {
      this.$v.note.fields.$reset()
      this.$v.note.fields.$touch()
      if (this.$v.note.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = { ...this.note.fields }
      const currentTime = new Date()
      params.time = currentTime.getHours() + ':' + currentTime.getMinutes() + ':' + currentTime.getSeconds()
      params.date = `${new Date().getFullYear()}/${(new Date().getMonth() + 1).toString().padStart(2, '0')}/${(new Date().getDate()).toString().padStart(2, '0')}`
      this.$q.loading.hide()
      api.post('/notes', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.note.fields.note = null
          this.getNotes()
        } else {
          this.$q.loading.hide()
        }
      })
    },
    decimales (n) {
      const t = n + ''
      const regex = /(\d*.\d{0,4})/
      return t.match(regex)[0]
    },
    getAvailableQtyEntry (originStorage, details) {
      var params = details
      api.post(`/storages/${originStorage}/raw-materials-data`, params).then(({ data }) => {
        this.dataMD = details
        for (let i = 0; i < data.products.length; i++) {
          for (let z = 0; z < this.dataMD.length; z++) {
            if (data.products[i].product_id === this.dataMD[z].product_id && this.movement.fields.status === 'NUEVO') {
              const stock = data.products[i].stock
              const balance = data.products[i].stock + Number(this.dataMD[z].qty)
              this.dataMD[z].stock = stock
              this.dataMD[z].balance = balance
            }
            if (data.products[i].product_id === this.dataMD[z].product_id && this.movement.fields.status === 'EJECUTADO') {
              const stock = this.dataMD[z].stock - this.dataMD[z].qty
              const balance = stock + Number(this.dataMD[z].qty)
              this.dataMD[z].stock = stock
              this.dataMD[z].balance = balance
            }
          }
        }
      })
    },
    getLayoutCsv () {
      const uri = process.env.API + 'movement-details/csv'
      window.open(uri, '_blank')
    },
    openUploadFileModal () {
      this.documentFileModal = true
    },
    uploadDocumentFile () {
      this.$refs.fileDocumentRef.upload()
    },
    fileDocumentUrl () {
      const id = this.$route.params.id
      return `${process.env.API}movement-details/file/${id}`
    },
    afterUploadDocumentFile (response) {
      console.log(response)
      const data = JSON.parse(response.xhr.response)
      this.$q.notify({
        message: data.message.content,
        position: 'top',
        color: (data.result ? 'positive' : 'warning')
      })
      if (data.result) {
        this.fetchFromServer()
        this.documentFileModal = false
      } else {
        this.documentFileModal = false
      }
    }
  }
}
</script>

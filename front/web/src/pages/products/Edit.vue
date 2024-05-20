<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-9">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Artículos" to="/products" />
              <q-breadcrumbs-el label="Editar" v-text="product.fields.product_code + product.fields.code"/>
            </q-breadcrumbs>
          </div>
        </div>
        <div class="col-sm-3">
        </div>
      </div>
    </div>

    <div class="q-pa-md bg-grey-3">
      <div class="row bg-white border-panel">
        <div class="col q-pa-md">
          <div class="row q-col-gutter-xs">
            <div class="col-xs-12 col-sm-3">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="product.fields.category"
                :options="categoryOptions"
                label="Categorías"
                :rules="categoryRules"
              >
              <template v-slot:prepend>
                <q-icon name="fas fa-cubes" />
              </template>
            </q-select>
          </div>
          <div class="col-xs-12 col-sm-3">
            <q-select
              color="dark"
              bg-color="secondary"
              filled
              v-model="product.fields.line"
              :options="filteredLineOptions"
              label="Subcategoría"
              :rules="lineRules"
              @input="updateProductFields"
            >
            <template v-slot:prepend>
              <q-icon name="fas fa-grip-lines-vertical" />
            </template>
          </q-select>
        </div>
        <div class="col-xs-12 col-sm-3">
          <q-select
            color="dark"
            bg-color="secondary"
            filled
            v-model="product.fields.unit"
            :options="unitOptions"
            label="Unidad"
            :rules="unitRules"
          >
          <template v-slot:prepend>
            <q-icon name="fas fa-grip-lines-vertical" />
          </template>
        </q-select>
      </div>
      <div class="col-xs-12 col-sm-3">
              <q-select
                          filled
                          color="dark"
                          bg-color="secondary"
                          v-model="mark"
                          label="Marca"
                          :options="options"
                          use-input
                          hide-selected
                          fill-input
                          input-debounce="0"
                          @filter="filterMarcas"
                          emit-value
                          map-options
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
        <!--<div class="col-xs-12 col-sm-3">
          <q-input
            color="dark"
            bg-color="secondary"
            filled
            v-model="product.fields.old_code"
            :error="$v.product.fields.old_code.$error"
            label="Código Antiguo"
            :rules="codeRules"
            @input="v => { product.fields.old_code = v.toUpperCase() }"
          >
          <template v-slot:prepend>
            <q-icon name="fingerprint" />
          </template>
        </q-input>
      </div>-->
      <div class="col-xs-12 col-sm-3">
          <q-input
            color="dark"
            bg-color="secondary"
            filled
            v-model="product.fields.code"
            :error="$v.product.fields.code.$error"
            label="Código"
            :rules="codeRules"
            @input="v => { product.fields.code = v.toUpperCase() }"
          >
          <template v-slot:prepend>
            <q-icon name="fingerprint" />
          </template>
        </q-input>
      </div>
      <div class="col-xs-12 col-sm-3 text-center">
        <q-select
          color="dark"
          bg-color="secondary"
          filled
          v-model="product.fields.active"
          :options="[
          {label: 'ACTIVO', value: true},
          {label: 'INACTIVO', value: false}
          ]"
          label="Estatus"
          :disable="!$store.getters['users/roles'].includes(1)"
        >
        <template v-slot:prepend>
          <q-icon :name="(product.fields.active.value ? 'battery_full' : 'battery_alert')" />
        </template>
      </q-select>
    </div>
    <div class="col-xs-12 col-sm-6">
      <q-input
        color="dark"
        bg-color="secondary"
        filled
        v-model="product.fields.name"
        :error="$v.product.fields.name.$error"
        label="Nombre"
        :rules="nameRules"
        @input="v => { product.fields.name = v.toUpperCase() }"
      >
      <template v-slot:prepend>
        <q-icon name="fas fa-signature" />
      </template>
    </q-input>
  </div>
  <div class="col-xs-12 col-sm-2">
    <q-input
      color="dark"
      bg-color="secondary"
      filled
      autogrow
      :rules="weightRules"
      :error="$v.product.fields.weight.$error"
      v-model="product.fields.weight"
      label="Peso KG"
    >
      <template v-slot:prepend>
        <q-icon name="fas fa-weight-hanging" />
      </template>
    </q-input>
  </div>
  <div class="col-xs-12 col-sm-3">
      <q-input
        color="dark"
        bg-color="secondary"
        filled
        v-model="product.fields.rebasa_code"
        :error="$v.product.fields.rebasa_code.$error"
        label="Código Interno"
        :rules="codeRebasa"
        @input="v => { product.fields.rebasa_code = v.toUpperCase() }"
      >
      <template v-slot:prepend>
        <q-icon name="fas fa-signature" />
      </template>
    </q-input>
  </div>
  <div class="col-xs-12 col-sm-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="product.fields.supplier_code"
                :error="$v.product.fields.supplier_code.$error"
                label="Código de proveedor"
                :rules="codeSuplier"
                @input="v => { product.fields.supplier_code = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-box-open" />
                </template>
              </q-input>
            </div>
  <div class="col-xs-12 col-sm-4">
    <q-select
      color="dark"
      bg-color="secondary"
      filled
      use-input
      input-debounce="0"
      emit-value
      label="Clave producto servicio"
      v-model="product.fields.clave_producto_id"
      :options="claveProdOptions"
      @filter="searchClave"
      clear
    >
    <template v-slot:prepend>
      <q-icon name="fas fa-key" />
    </template>
    <template v-slot:no-option>
      <q-item>
        <q-item-section class="text-grey">
          No results
        </q-item-section>
      </q-item>
    </template>
    <template v-slot:append>
      <q-icon
      v-if="product.fields.clave_producto_id !== null"
      class="cursor-pointer"
      name="clear"
      @click.stop="product.fields.clave_producto_id = null"
      />
      </template>
    </q-select>
  </div>
  <div class="col-xs-12 col-sm-5">
    <q-input
      color="dark"
      bg-color="secondary"
      filled
      autogrow
      :error="$v.product.fields.barcode.$error"
      v-model="product.fields.barcode"
      label="Código de barras"
    >
      <template v-slot:prepend>
        <q-icon name="view_week" />
      </template>
    </q-input>
  </div>
  <div class="col-xs-12 col-sm-7">
    <q-input
    color="dark"
    bg-color="secondary"
    filled
    autogrow
    v-model="product.fields.description"
    label="Descripción"
    @input="v => { product.fields.description = v.toUpperCase() }"
    >
      <template v-slot:prepend>
        <q-icon name="fas fa-signature" />
      </template>
    </q-input>
  </div>
  <div class="col-xs-12 col-sm-5">
    <q-input
    color="dark"
    type="textarea"
    bg-color="secondary"
    filled
    autogrow
    v-model="product.fields.additional_information"
    label="Información adicional"
    @input="v => { product.fields.additional_information = v.toUpperCase() }"
    >
      <template v-slot:prepend>
        <q-icon name="fas fa-signature" />
      </template>
    </q-input>
  </div>
</div>

<div class="row q-col-gutter-xs">
  <div style="font-weight: normal;">
    <span class="fs25">Precios del producto</span>
    <br>
    <br>
    <div class="row q-col-gutter-xs">
      <div class="col-xs-12 col-sm-2">
        <q-input
        color="dark"
        bg-color="secondary"
        filled
        v-model="prices.fields.a"
        :error="$v.prices.fields.a.$error"
        label="Precio A"
        :rules="priceARules"
        @input="v => { prices.fields.a = v.replace(/[^0-9\\.]/g, '') }"
        >
        <template v-slot:prepend>
          <q-icon name="fas fa-dollar-sign" />
        </template>
      </q-input>
    </div>
    <div class="col-xs-12 col-sm-2">
      <q-input
      color="dark"
      bg-color="secondary"
      filled
      v-model="prices.fields.b"
      :error="$v.prices.fields.b.$error"
      label="Precio B"
      :rules="priceBRules"
      @input="v => { prices.fields.b = v.replace(/[^0-9\\.]/g, '') }"
      >
      <template v-slot:prepend>
        <q-icon name="fas fa-dollar-sign" />
      </template>
    </q-input>
  </div>
  <div class="col-xs-12 col-sm-2">
    <q-input
    color="dark"
    bg-color="secondary"
    filled
    v-model="prices.fields.c"
    :error="$v.prices.fields.c.$error"
    label="Precio C"
    :rules="priceCRules"
    @input="v => { prices.fields.c = v.replace(/[^0-9\\.]/g, '') }"
    >
    <template v-slot:prepend>
      <q-icon name="fas fa-dollar-sign" />
    </template>
  </q-input>
</div>
<div class="col-xs-12 col-sm-2">
  <q-input
  color="dark"
  bg-color="secondary"
  filled
  v-model="prices.fields.d"
  :error="$v.prices.fields.d.$error"
  label="Precio D"
  :rules="priceDRules"
  @input="v => { prices.fields.d = v.replace(/[^0-9\\.]/g, '') }"
  >
  <template v-slot:prepend>
    <q-icon name="fas fa-dollar-sign" />
  </template>
</q-input>
</div>
<div class="col-xs-12 col-sm-2">
  <q-input
  color="dark"
  bg-color="secondary"
  filled
  v-model="prices.fields.e"
  :error="$v.prices.fields.e.$error"
  label="Precio Mostrador"
  :rules="priceERules"
  @input="v => { prices.fields.e = v.replace(/[^0-9\\.]/g, '') }"
  >
  <template v-slot:prepend>
    <q-icon name="fas fa-dollar-sign" />
  </template>
</q-input>
</div>
</div>
</div>
</div>

<div class="row q-mb-sm q-mt-md">
  <div class="col-xs-12 col-sm-2 offset-sm-10 pull-right">
    <q-btn color="positive" icon="save" label="Actualizar" @click="updateProduct()" />
  </div>
</div>
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
      <q-tab name="equivalence" icon="fas fa-cubes" label="EQUIVALENCIA"/>
      <q-tab name="bom" icon="fas fa-cubes" label="BOM"/>
      <q-tab name="minimum-stock" icon="fas fa-cubes" label="STOCKS"/>
      <q-tab name="work" icon="fas fa-hard-hat" label="MANO DE OBRA"/>
    </q-tabs>
    <q-separator />
    <q-tab-panels v-model="tab" animated class="">
      <q-tab-panel name="bom">
        <div class="" style="background-color:white">
          <div class="col q-pa-md">
            <div class="row q-col-gutter-xs">
              <div class="col-xs-12 col-sm-12">
                <q-btn clas="border-panel" class="float-right" color="positive" icon="add" label="Agregar" @click="modalBom = true" />
              </div>
              <q-dialog v-model="modalBom" persistent>
                <q-card style="min-width: 100%">
                  <q-card-section>
                    <div class="text-h6">BOM</div>
                  </q-card-section>
                  <q-card-section>
                    <div class="row q-col-gutter-xs">
                      <div class="col-xs-12 col-sm-4">
                        <q-select
                        color="dark"
                        bg-color="secondary"
                        filled
                        v-model="bom.fields.category"
                        :error="$v.bom.fields.category.$error"
                        label="Categorías"
                        :rules="bomcategoryRules"
                        @input="getLinesByCategories()"
                        :options="options1"
                        use-input
                        hide-selected
                        fill-input
                        input-debounce="0"
                        @filter="filterCategory"
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
                    </div>
                  <div class="col-xs-12 col-sm-4">
                    <q-select
                    filled
                    color="dark"
                    bg-color="secondary"
                    v-model="bom.fields.product"
                    :error="$v.bom.fields.product.$error"
                    label="Producto"
                    :options="options2"
                    use-input
                    hide-selected
                    fill-input
                    input-debounce="0"
                    @filter="filterProduct"
                    emit-value
                    map-options
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
                <div class="col-xs-12 col-sm-4">
                  <q-input
                  color="dark"
                  bg-color="secondary"
                  filled
                  v-model="bom.fields.cantidad"
                  :error="$v.bom.fields.cantidad.$error"
                  label="Cantidad"
                  :rules="CantidadRules"
                  >
                  <template v-slot:prepend>
                    <q-icon name="fas fa-hashtag" />
                  </template>
                </q-input>
              </div>
            </div>
          </q-card-section>
          <q-card-actions align="right" class="text-primary">
            <q-btn label="Cancelar" color="red" @click.native="closeModalBomEdit()" v-close-popup />
            <q-btn v-if="editar_bom === false" label="Agregar" color="green" @click.native="saveBom()" />
            <q-btn v-if="editar_bom === true" label="Agregar" color="green" @click.native="updateBom()" />
          </q-card-actions>
        </q-card>
        </q-dialog>
      </div>
    </div>
  </div>

  <q-table flat bordered :data="dataBom" :columns="columns" row-key="nombre" :pagination.sync="pagination">
    <template v-slot:body="props">
      <q-tr :props="props">
        <q-td key="code" flat @click.native="editSelectedRow(props.row.material_id)" style="text-align: center; text-decoration:underline;" class="cursor-pointer" :props="props">{{ props.row.code }}</q-td>
        <q-td key="name" :props="props" class="pull-left">{{ props.row.name }}</q-td>
        <q-td key="unit" :props="props" >{{ props.row.unit }}</q-td>
        <q-td key="amount" :props="props" >{{ props.row.amount }}</q-td>
        <q-td key="lastoc" :props="props" >{{  `${currencyFormatter.format(props.row.lastprice)}` }}</q-td>
        <q-td key="total" :props="props" >{{  `${currencyFormatter.format(props.row.lastprice * props.row.amount)}` }}</q-td>
        <q-td key="actions" :props="props" class="pull-left">
          <q-btn class="action-btn" color="blue" icon="fas fa-edit" flat @click.native="editSelectedRowBomModal(props.row)" size="10px" style="text-align:left"><q-tooltip content-class="bg-positive">EDITAR</q-tooltip></q-btn>
          <q-btn class="action-btn" color="red" icon="fas fa-trash-alt" flat @click.native="deleteSelectedRowBom(props.row.id)" size="10px" style="text-align:left"><q-tooltip content-class="bg-positive">ELIMINAR</q-tooltip></q-btn>
        </q-td>
      </q-tr>
    </template>
  </q-table>
</q-tab-panel>

<q-tab-panel name="work">
        <div class="" style="background-color:white">
          <div class="col q-pa-md">
            <div class="row q-col-gutter-xs">
              <div class="col-xs-12 col-sm-12">
                <q-btn clas="border-panel" class="float-right" color="positive" icon="add" label="Agregar" @click="ModalWorks()" />
              </div>
              <q-dialog v-model="modalWork" persistent>
                <q-card style="min-width: 50%">
                  <q-card-section>
                    <div class="text-h6">MANO DE OBRA</div>
                  </q-card-section>
                  <q-card-section>
                    <div class="row q-col-gutter-xs">
                      <div class="col-xs-6 col-sm-6">
                        <q-select class="dark" borderless bg-color="secondary" filled v-model="work.fields.work_id"  :error="$v.work.fields.work_id.$error" label="Mano de Obra" :options="selectWork" emit-value map-options></q-select>
                      </div>
                      <div class="col-xs-6 col-sm-6">
                        <q-input
                          color="dark"
                          bg-color="secondary"
                          filled
                          v-model="work.fields.time_job"
                          :error="$v.work.fields.time_job.$error"
                          label="Tiempo/Minutos"
                          :rules="timeRules"
                          @input="v => { work.fields.time_job = v.toUpperCase() }"
                        >
                          <template v-slot:prepend>
                            <q-icon name="fas fa-clock" />
                          </template>
                        </q-input>
                      </div>
                      <div class="col-xs-6 col-sm-6">
                        <q-input
                          color="dark"
                          bg-color="secondary"
                          filled
                          v-model="work.fields.minimal"
                          :error="$v.work.fields.minimal.$error"
                          label="Mínimo"
                          :rules="minimalRules"
                        >
                          <template v-slot:prepend>
                            <q-icon name="close_fullscreen" />
                          </template>
                        </q-input>
                      </div>
                      <div class="col-xs-6 col-sm-6">
                        <q-input
                          color="dark"
                          bg-color="secondary"
                          filled
                          v-model="work.fields.factor"
                          :error="$v.work.fields.factor.$error"
                          label="Factor"
                          :rules="factorRules"
                        >
                          <template v-slot:prepend>
                            <q-icon name="fact_check" />
                          </template>
                        </q-input>
                      </div>
                    </div>
                  </q-card-section>
                  <q-card-actions align="right" class="text-primary">
                    <q-btn label="Cancelar" @click.native="closeModalWorkEdit()" color="red" v-close-popup />
                    <q-btn v-if="editar_work === false" label="Agregar" color="green" @click.native="saveWork()" />
                    <q-btn v-if="editar_work === true" label="Actualizar" color="green" @click.native="updateWork()" />
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
        <q-td  style="text-align: right;">
          <strong>Total: ${{ tot }}</strong>
        </q-td>
      </q-tr>
    <template v-slot:body="props">
      <q-tr :props="props">
        <q-td key="name" :props="props" class="pull-left">{{ props.row.name }}</q-td>
        <q-td key="price_hour" :props="props" class="pull-right" >${{ props.row.price_hour }}</q-td>
        <q-td key="price_minute" :props="props" class="pull-right" >${{ props.row.price_minute }}</q-td>
        <q-td key="time_job" :props="props" class="pull-right">{{ props.row.time_job }}</q-td>
        <q-td key="price_qty" :props="props" class="pull-right">${{ props.row.price_qty}}</q-td>
        <q-td key="actions" :props="props" class="pull-left">
          <q-btn class="action-btn" color="blue" icon="fas fa-edit" flat @click.native="editSelectedRowBomWork(props.row)" size="10px" style="text-align:left"><q-tooltip content-class="bg-positive">EDITAR</q-tooltip></q-btn>
          <q-btn class="action-btn" color="red" icon="fas fa-trash-alt" flat @click.native="deleteSelectedRowWork(props.row.id)" size="10px" style="text-align:left"><q-tooltip content-class="bg-positive">ELIMINAR</q-tooltip></q-btn>
        </q-td>
      </q-tr>
    </template>
  </q-table>
</q-tab-panel>
<q-tab-panel name="equivalence">
  <div class="col q-pa-md">
    <div class="row q-col-gutter-xs">
    <div class="col-xs-4 col-sm-4">
                    <q-select
                    filled
                    color="dark"
                    bg-color="secondary"
                    v-model="equivalence.fields.product"
                    :error="$v.equivalence.fields.product.$error"
                    label="Producto"
                    :options="optionsProducts"
                    use-input
                    hide-selected
                    fill-input
                    input-debounce="0"
                    @filter="filterProducts"
                    emit-value
                    map-options
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
                <div class="col-sm-4 col-md-8 pull-right" v-if="cndEdit === false" style="">
                          <q-btn class="pull-right" style="margin-left: 20px," color="positive" icon="add" @click.native="AddEquivalence()"  label="Agregar"/>
                </div>
      <div class="col-sm-6 col-md-4 pull-right" style="">
      </div>

                        <div class="col-sm- col-md-2 pull-right" v-if="cndEdit === true">
                          <q-btn class="pull-left" style="width: 145px" color="red" icon="fas fa-times-circle" @click.native="Cancelar()"  label="Cancelar"/>
                        </div>
                        <div class="col-sm- col-md-2 pull-right" v-if="cndEdit === true">
                          <q-btn class="pull-right" style="width: 145px" color="positive" icon="save" @click.native="EditEquivalence()"  label="Editar"/>
                        </div>
              </div>
                    </div>
        <div class="" style="background-color:white">
          <div class="col q-pa-md">
            <div class="row q-col-gutter-xs">
              <q-dialog v-model="modalWork" persistent>
                <q-card style="min-width: 50%">
                  <q-card-section>
                    <div class="text-h6">Destajo</div>
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
  <q-table flat bordered :data="dataEquivalence" :columns="columnsEquivalence" row-key="nombre" :pagination.sync="pagination">
    <template v-slot:body="props">
      <q-tr :props="props">
        <q-td key="name" :props="props" class="pull-left">{{ props.row.name }}</q-td>
        <q-td key="actions" :props="props" class="pull-left">
          <q-btn class="action-btn" color="secondary" icon="fas fa-edit" flat @click.native="editSelectedRowEquivalente(props.row.id)" size="10px" style="text-align:left"><q-tooltip content-class="bg-secondary">EDITAR</q-tooltip></q-btn>
          <q-btn class="action-btn" color="red" icon="fas fa-trash-alt" flat @click.native="deleteSelectedRowEquivalente(props.row.id)" size="10px" style="text-align:left"><q-tooltip content-class="bg-red">ELIMINAR</q-tooltip></q-btn>
        </q-td>
      </q-tr>
    </template>
  </q-table>
</q-tab-panel>
<q-tab-panel name="minimum-stock">
  <div class="col q-pa-md">
    <div class="row q-col-gutter-xs">
                <div class="col-xs-12 col-sm-3 text-center">
                            <q-select
                            color="dark"
                            bg-color="secondary"
                            filled
                            :options="branchesList"
                            v-model="stocks.fields.branch_id"
                            :rules="branchOfficeRules"
                            :error="$v.stocks.fields.branch_id.$error"
                            label="Estación">
                            <template v-slot:prepend>
                                <q-icon name="business"></q-icon>
                            </template>
                            </q-select>
                        </div>
                        <div class="col-xs-12 col-sm-3 text-center">
                            <q-select
                            color="dark"
                            bg-color="secondary"
                            filled
                            v-model="stocks.fields.storage"
                            :options="filteredStorageOptions"
                            :rules="storageRules"
                            :error="$v.stocks.fields.storage.$error"
                            label="Almacén">
                            <template v-slot:prepend>
                                <q-icon name="store"></q-icon>
                            </template>
                            </q-select>
                        </div>
                        <div class="col-xs-12 col-sm-3">
                  <q-input
                  color="dark"
                  bg-color="secondary"
                  filled
                  v-model="stocks.fields.min_operation"
                  label="Mínimo operación"
                  :error="$v.stocks.fields.storage.$error"
                  :rules="CantidadRulesMinOperation"
                  >
                  <template v-slot:prepend>
                    <q-icon name="fas fa-hashtag" />
                  </template>
                </q-input>
              </div>
              <div class="col-xs-12 col-sm-3">
                  <q-input
                  color="dark"
                  bg-color="secondary"
                  filled
                  v-model="stocks.fields.stock"
                  :error="$v.stocks.fields.stock.$error"
                  label="Stock mínimo"
                  :rules="CantidadRulesStock"
                  >
                  <template v-slot:prepend>
                    <q-icon name="fas fa-hashtag" />
                  </template>
                </q-input>
              </div>
              <div class="col-xs-12 col-sm-3">
                  <q-input
                  color="dark"
                  bg-color="secondary"
                  filled
                  v-model="stocks.fields.max_operation"
                  label="Máximo operación"
                  :error="$v.stocks.fields.storage.$error"
                  :rules="CantidadRulesMaxOperation"
                  >
                  <template v-slot:prepend>
                    <q-icon name="fas fa-hashtag" />
                  </template>
                </q-input>
              </div>
              <div class="col-xs-12 col-sm-3">
                  <q-input
                  color="dark"
                  bg-color="secondary"
                  filled
                  v-model="stocks.fields.capacity"
                  label="Capacidad"
                  :error="$v.stocks.fields.storage.$error"
                  :rules="CantidadRulesCapacity"
                  >
                  <template v-slot:prepend>
                    <q-icon name="fas fa-hashtag" />
                  </template>
                </q-input>
              </div>
              <div class="col-sm-4 col-md-12 pull-right" v-if="cndEdits === false" style="">
                <q-btn class="pull-right" style="margin-left: 20px," color="positive" icon="add" @click.native="AddStocks()"  label="Agregar"/>
              </div>
              <div class="col-sm- col-md-12 pull-right" v-if="cndEdits === true">
                <div class="q-pa-sm q-gutter-sm">
                <q-btn class="pull-left" style="margin-left: 20px," color="red" icon="fas fa-times-circle" @click.native="CancelarStocks()"  label="Cancelar"/>
                <q-btn class="pull-right" style="margin-left: 20px," color="positive" icon="save" @click.native="EditStocks()"  label="Editar"/>
              </div>
              </div>
            </div>
          </div>
        <div class="" style="background-color:white">
          <div class="col q-pa-md">
            <div class="row q-col-gutter-xs">
              <q-dialog v-model="modalWork" persistent>
                <q-card style="min-width: 50%">
                  <q-card-section>
                    <div class="text-h6">Destajo</div>
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
  <q-table flat bordered :data="dataStocks" :columns="columnsStocks" row-key="nombre" :pagination.sync="pagination">
    <template v-slot:body="props">
      <q-tr :props="props">
        <q-td key="name" :props="props" class="pull-center">{{ props.row.name }}</q-td>
        <q-td key="sucursal" :props="props" class="pull-center">{{ props.row.sucursal }}</q-td>
        <q-td key="almacen" :props="props" class="pull-center">{{ props.row.almacen }}</q-td>
        <q-td key="min_operation" :props="props" class="pull-center">{{ props.row.min_operation }}</q-td>
        <q-td key="stock" :props="props" class="pull-center">{{ props.row.stock }}</q-td>
        <q-td key="max_operation" :props="props" class="pull-center">{{ props.row.max_operation }}</q-td>
        <q-td key="capacity" :props="props" class="pull-center">{{ props.row.capacity }}</q-td>
        <q-td key="actions" :props="props" class="pull-right">
          <q-btn class="action-btn" color="secondary" icon="fas fa-edit" flat @click.native="editSelectedRowStocks(props.row.id)" size="10px" style="text-align:left"><q-tooltip content-class="bg-secondary">EDITAR</q-tooltip></q-btn>
          <q-btn class="action-btn" color="red" icon="fas fa-trash-alt" flat @click.native="deleteSelectedRowStocks(props.row.id)" size="10px" style="text-align:left"><q-tooltip content-class="bg-red">ELIMINAR</q-tooltip></q-btn>
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
</div>
</div>
</q-page>
</template>

<script>
import api from '../../commons/api.js'
const { required, maxLength, decimal, helpers, integer } = require('vuelidate/lib/validators')
const cantidad = helpers.regex('cantidad', /[0-9]+$/)
const num = helpers.regex('num', /[0-9]+$/)
export default {
  name: 'EditProduct',
  mark: {},
  validations: {
    product: {
      fields: {
        name: { required, maxLength: maxLength(100) },
        old_code: { maxLength: maxLength(40) },
        code: { required, maxLength: maxLength(40) },
        family: { required },
        rebasa_code: { maxLength: maxLength(40) },
        supplier_code: { maxLength: maxLength(40) },
        category: { required },
        line: { required },
        unit: { required },
        barcode: { maxLength: maxLength(20), integer },
        weight: { required, decimal }
      }
    },
    bom: {
      fields: {
        category: { },
        product: { required },
        cantidad: { required, cantidad }
      }
    },
    work: {
      fields: {
        work_id: { required },
        time_job: { required, num },
        minimal: { required, decimal },
        factor: { required, decimal }
      }
    },
    prices: {
      fields: {
        a: { decimal },
        b: { decimal },
        c: { decimal },
        d: { decimal },
        e: { decimal }
      }
    },
    equivalence: {
      fields: {
        idedit: { },
        product: { required }
      }
    },
    stocks: {
      fields: {
        idedit: { },
        branch_id: { required },
        storage: { required },
        stock: { required },
        min_operation: { required },
        max_operation: { required },
        capacity: { required }
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
      tot: 0,
      tab: 'bom',
      editar_bom: false,
      editar_work: false,
      modalBom: false,
      modalWork: false,
      options1: this.categoryOptions,
      options2: this.ProductsOptionsbyLines,
      options: this.markOptions,
      optionsProducts: this.familyOptions,
      branchesList: [],
      storageOptions: [],
      mark: null,
      cndEdit: false,
      cndEdits: false,
      product: {
        fields: {
          id: null,
          name: null,
          code: null,
          family: null,
          category: null,
          line: null,
          active: { value: true, label: 'ACTIVO' },
          old_code: null,
          unit: null,
          rebasa_code: null,
          supplier_code: null,
          code_temp: null,
          description: null,
          barcode: null,
          weight: null,
          additional_information: null
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
          work_id: null,
          minimal: null,
          factor: null
        }
      },
      prices: {
        fields: {
          a: null,
          b: null,
          c: null,
          d: null,
          e: null
        }
      },
      equivalence: {
        fields: {
          idedit: null,
          product: null
        }
      },
      stocks: {
        fields: {
          idedit: null,
          branch_id: null,
          storage: null,
          stock: null,
          min_operation: null,
          max_operation: null,
          capacity: null
        }
      },
      columns: [
        { name: 'code', align: 'center', label: 'CÓDIGO', field: 'code', sortable: false },
        { name: 'name', align: 'center', label: 'NOMBRE', field: 'name', sortable: false },
        { name: 'unit', align: 'center', label: 'UNIDAD', field: 'unit', sortable: false },
        { name: 'amount', align: 'center', label: 'CANTIDAD', field: 'amount', sortable: false },
        { name: 'lastoc', align: 'center', label: 'COSTO ÚLTIMA COMPRA', field: 'lastoc', sortable: false },
        { name: 'total', align: 'center', label: 'COSTO TOTAL', field: 'total', sortable: false },
        { name: 'actions', align: 'center', label: 'ACCIONES', field: 'actions', sortable: false }
      ],
      columnswork: [
        { name: 'name', align: 'center', label: 'NOMBRE', field: 'name', sortable: false },
        { name: 'price_hour', align: 'center', label: ' COSTO POR HORA', field: 'price_hour', sortable: false },
        { name: 'price_minute', align: 'center', label: ' COSTO POR MINUTO', field: 'price_minute', sortable: false },
        { name: 'time_job', align: 'center', label: 'TIEMPO REQUERIDO', field: 'time_job', sortable: false },
        { name: 'price_qty', align: 'center', label: ' TOTAL REQUERIDO', field: 'price_qty', sortable: false },
        { name: 'actions', align: 'center', label: 'ACCIONES', field: 'actions', sortable: false }
      ],
      columnsEquivalence: [
        { name: 'name', align: 'center', label: 'NOMBRE', field: 'name', sortable: false },
        { name: 'actions', align: 'center', label: 'ACCIONES', field: 'actions', sortable: false }
      ],
      columnsStocks: [
        { name: 'name', align: 'center', label: 'PRODUCTO', field: 'name', sortable: false },
        { name: 'sucursal', align: 'center', label: 'ESTACIÓN', field: 'sucursal', sortable: false },
        { name: 'almacen', align: 'center', label: 'ALMACEN', field: 'almacen', sortable: false },
        { name: 'min_operation', align: 'center', label: 'MIN OPERACIÓN', field: 'min_operation', sortable: false },
        { name: 'stock', align: 'center', label: 'STOCK MIN', field: 'stock', sortable: false },
        { name: 'max_operation', align: 'center', label: 'MAX OPERACIÓN', field: 'max_operation', sortable: false },
        { name: 'capacity', align: 'center', label: 'CAPACIDAD', field: 'capacity', sortable: false },
        { name: 'actions', align: 'center', label: 'ACCIONES', field: 'actions', sortable: false }
      ],
      pagination: {
        sortBy: 'id',
        descending: false,
        rowsPerPage: 25
      },
      categoryOptions: [],
      lineOptions: [],
      markOptions: [],
      lineOptionsbyCategories: [],
      ProductsOptionsbyLines: [],
      unitOptions: [],
      familyOptions: [],
      claveProdOptions: [],
      dataBom: [],
      dataWork: [],
      dataStocks: [],
      selectWork: [],
      dataEquivalence: []
    }
  },
  computed: {
    filteredStorageOptions () {
      if (this.stocks.fields.branch_id != null && this.stocks.fields.branch_id.value != null) {
        return this.storageOptions.filter(storage => storage.branchOffice === this.stocks.fields.branch_id.value)
      }
      return []
    },
    branchOfficeRules (val) {
      return [
        val => (this.$v.stocks.fields.branch_id.required) || 'El campo Sucursal es requerido.'
      ]
    },
    storageRules (val) {
      return [
        val => (this.$v.stocks.fields.storage.required) || 'El campo Almacén es requerido.'
      ]
    },
    roleId () {
      const user = this.$store.getters['users/rol']
      return parseInt(user)
    },
    unitRules (val) {
      return [
        val => this.$v.product.fields.unit.required || 'El campo Unidad es requerido.'
      ]
    },
    nameRules (val) {
      return [
        val => (this.$v.product.fields.name.required) || 'El campo Nombre es requerido.',
        val => (this.$v.product.fields.name.maxLength) || 'El campo Nombre no debe exceder los 100 dígitos.'
      ]
    },
    codeRules (val) {
      return [
        val => (this.$v.product.fields.code.required) || 'El campo Código es requerido.',
        val => (this.$v.product.fields.code.maxLength) || 'El campo Código no debe exceder los 40 dígitos.'
      ]
    },
    codeRulesErp (val) {
      return [
        val => (this.$v.product.fields.code.required) || 'El campo Código es requerido.',
        val => (this.$v.product.fields.code.maxLength) || 'El campo Código no debe exceder los 40 dígitos.'
      ]
    },
    codeRebasa (val) {
      return [
        val => (this.$v.product.fields.rebasa_code.maxLength) || 'El campo Código no debe exceder los 40 dígitos.'
      ]
    },
    codeSuplier (val) {
      return [
        val => (this.$v.product.fields.supplier_code.maxLength) || 'El campo Código no debe exceder los 40 dígitos.'
      ]
    },
    categoryRules (val) {
      return [
        val => this.$v.product.fields.category.required || 'El campo Categorías es requerido.'
      ]
    },
    lineRules (val) {
      return [
        val => this.$v.product.fields.line.required || 'El campo Líneas es requerido.'
      ]
    },
    bomcategoryRules (val) {
      return [
        val => this.$v.bom.fields.category || 'El campo Categorías es requerido.'
      ]
    },
    bomproductsRules (val) {
      return [
        val => this.$v.bom.fields.product.required || 'El campo Productos es requerido.'
      ]
    },
    priceARules (val) {
      return [
        val => this.$v.prices.fields.a.decimal || 'El campo Precio A debe ser un número válido.'
      ]
    },
    priceBRules (val) {
      return [
        val => this.$v.prices.fields.b.decimal || 'El campo Precio B debe ser un número válido.'
      ]
    },
    priceCRules (val) {
      return [
        val => this.$v.prices.fields.c.decimal || 'El campo Precio C debe ser un número válido.'
      ]
    },
    priceDRules (val) {
      return [
        val => this.$v.prices.fields.d.decimal || 'El campo Precio D debe ser un número válido.'
      ]
    },
    priceERules (val) {
      return [
        val => this.$v.prices.fields.e.decimal || 'El campo Precio E debe ser un número válido.'
      ]
    },
    barcodeRules (val) {
      return [
        val => this.$v.product.fields.barcode.required || 'El campo Código de barras es requerido.',
        val => this.$v.product.fields.barcode.maxLength || 'El campo Código de barras no debe exceder 20 dígitos.',
        val => this.$v.product.fields.barcode.integer || 'El campo Código de barras es numérico.'
      ]
    },
    weightRules (val) {
      return [
        val => this.$v.product.fields.weight.decimal || 'El campo Peso es numérico.'
      ]
    },
    /* claveRules (val) {
      return [
        val => this.$v.product.fields.clave_producto_id.required || 'El campo Clave producto servicio es requerido.'
      ]
    }, */
    filteredLineOptions () {
      if (this.product.fields.category != null && this.product.fields.category.value != null) {
        return this.lineOptions.filter(line => line.category === this.product.fields.category.value)
      }
      return []
    },
    CantidadRules (val) {
      return [
        val => this.$v.bom.fields.cantidad.required || 'El campo Cantidad es requerido',
        val => this.$v.bom.fields.cantidad.cantidad || 'El campo Cantidad debe ser númerico'
      ]
    },
    CantidadRulesStock (val) {
      return [
        val => this.$v.stocks.fields.stock.required || 'El campo stock es requerido'
      ]
    },
    CantidadRulesMinOperation (val) {
      return [
        val => this.$v.stocks.fields.min_operation.required || 'El campo Mínimo operación es requerido'
      ]
    },
    CantidadRulesMaxOperation (val) {
      return [
        val => this.$v.stocks.fields.max_operation.required || 'El campo Máximo operación es requerido'
      ]
    },
    CantidadRulesCapacity (val) {
      return [
        val => this.$v.stocks.fields.capacity.required || 'El campo Capacidad es requerido'
      ]
    },
    timeRules () {
      return [
        val => (this.$v.work.fields.time_job.required) || 'El campo Tiempo/Minutos es requerido.'
      ]
    },
    minimalRules () {
      return [
        val => (this.$v.work.fields.minimal.required) || 'El campo Mínimo es requerido.',
        val => (this.$v.work.fields.minimal.decimal) || 'El campo Mínimo es numérico.'
      ]
    },
    factorRules () {
      return [
        val => (this.$v.work.fields.factor.required) || 'El campo Factor es requerido.',
        val => (this.$v.work.fields.factor.decimal) || 'El campo Factor es numérico.'
      ]
    }
  },
  /* beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(2) && !this.$store.getters['users/roles'].includes(22) && !this.$store.getters['users/roles'].includes(4)) {
      this.$router.push('/')
    }
  }, */
  beforeRouteEnter (to, from, next) {
    next(vm => {
      const propiedades = vm.$store.getters['users/rol']
      console.log(propiedades)
      if (propiedades === 1 || propiedades === 3 || propiedades === 7 || propiedades === 2 || propiedades === 20 || propiedades === 4 || propiedades === 27 || propiedades === 22) {
        next()
      } else {
        next('/')
      }
    })
  },
  mounted () {
    this.fetchFromServer()
    this.getCategories()
    this.getLines()
    this.getProducts()
    this.getUnits()
    this.getMarks()
    // this.getLinesByCategories()
    this.fetchFromServerEquivalence()
    this.fetchFromServerBom()
    this.fetchFromServerStocks()
    this.getWork()
    this.fetchFromServerWorks()
    this.getBranchesList()
    this.getStoragesList()
  },
  methods: {
    PadLeft (value, length) {
      return (value.toString().length < length) ? this.PadLeft('0' + value, length) : value
    },
    fetchFromServer () {
      this.$q.loading.show()
      const id = this.$route.params.id
      api.get(`/products/${id}`).then(({ data }) => {
        if (!data.product) {
          this.$router.push('/products')
        } else {
          this.product.fields = data.product
          this.product.fields.category = { value: data.product.category_id, label: data.product.category }
          this.product.fields.line = { value: data.product.line_id, label: data.product.line }
          this.product.fields.code = this.PadLeft(data.product.code, 5)
          this.product.fields.unit = { value: data.product.unit_id, label: data.product.unit }
          this.product.fields.description = data.product.description
          this.product.fields.barcode = data.product.barcode
          this.product.fields.rebasa_code = data.product.rebasa_code
          this.product.fields.supplier_code = data.product.supplier_code
          this.mark = { value: data.product.mark_id, label: data.product.mark_name }
          // console.log(data.product)
          // console.log(data.product.barcode)
          if (data.product.active) {
            this.product.fields.active = { value: true, label: 'ACTIVO' }
          } else {
            this.product.fields.active = { value: false, label: 'INACTIVO' }
          }
          if (data.product.family_id == null) {
            this.product.fields.family = { value: null, label: 'NINGUNO' }
          } else {
            this.product.fields.family = { value: data.product.family_id, label: data.product.family }
          }
          api.get(`/products-prices/${id}`).then(({ data }) => {
            data.productsPrices.forEach(price => {
              switch (price.price_level) {
                case 'A':
                  this.prices.fields.a = price.price
                  break

                case 'B':
                  this.prices.fields.b = price.price
                  break

                case 'C':
                  this.prices.fields.c = price.price
                  break

                case 'D':
                  this.prices.fields.d = price.price
                  break

                case 'E':
                  this.prices.fields.e = price.price
                  break
              }
            })
          })
          this.$q.loading.hide()
          // onsole.log(this.product.fields)
        }
      })
    },
    getCategories () {
      api.get('/categories/options').then(({ data }) => {
        this.categoryOptions = data.options
      })
    },
    getLines () {
      api.get('/lines/options').then(({ data }) => {
        this.lineOptions = data.options
      })
    },
    getMarks () {
      api.get('/marks/options').then(({ data }) => {
        this.markOptions = data.options
      })
    },
    getUnits () {
      api.get('/units/options').then(({ data }) => {
        this.unitOptions = data.options
      })
    },
    getProducts () {
      this.ProductsOptionsbyLines = []
      const id = this.$route.params.id
      api.get('/products/options').then(({ data }) => {
        this.familyOptions = data.options.filter(product => product.family == null && product.value !== id)
        this.familyOptions.unshift({ value: null, label: 'NINGUNO' })
      })
    },
    getLinesByCategories () {
      this.ProductsOptionsbyLines = []
      // this.bom.fields.product = null
      api.get(`/products/category1/${this.bom.fields.category}`).then(({ data }) => {
        this.ProductsOptionsbyLines = data.products
        this.ProductsOptionsbyLines.unshift({ value: null, label: 'NINGUNO' })
      })
    },
    getLinesByCategoriesEdit (id) {
      this.ProductsOptionsbyLines = []
      // this.bom.fields.product = null
      api.get(`/products/category1/${id}`).then(({ data }) => {
        // console.log(data.products)
        this.ProductsOptionsbyLines = data.products
        this.ProductsOptionsbyLines.unshift({ value: null, label: 'NINGUNO' })
      })
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
    filterProducts (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.optionsProducts = this.familyOptions.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    filterMarcas (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.options = this.markOptions.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    fetchFromServerBom () {
      api.get(`/bom/${this.$route.params.id}`).then(({ data }) => {
        // console.log(data.bom)
        this.dataBom = data.bom
      })
    },
    saveBom () {
      // const id = this.$route.params.id
      // console.log(id)
      this.$v.bom.fields.$reset()
      this.$v.bom.fields.$touch()
      if (this.$v.bom.fields.$error) {
        this.$q.notify({
          message: 'Por favor revise los campos.',
          color: 'red',
          position: 'top'
        })
        return false
      }
      // this.modalratificantes = false
      // this.showLoading()
      this.$q.loading.show()
      const params = { ...this.bom.fields }
      params.id = this.$route.params.id
      api.post('/bom', params).then(({ data }) => {
        if (data.result) {
          this.fetchFromServerBom()
          // this.bom.fields.category = null
          this.bom.fields.line = null
          this.bom.fields.product = null
          this.bom.fields.cantidad = null
        }
        this.$q.notify({
          message: data.message.content,
          color: data.result ? 'positive' : 'red',
          position: 'top'
        })
        // this.beforeDestroy()
        this.$q.loading.hide()
      })
    },
    editSelectedRowBomModal (row) {
      // console.log(row)
      this.bom.fields.idedit = row.id
      this.bom.fields.category = { id: row.category_id, label: row.category_name }
      this.bom.fields.line = { id: row.line_id, label: row.line_name }
      this.bom.fields.product = { id: row.material_id, label: row.product_name }
      this.bom.fields.cantidad = row.amount
      this.editar_bom = true
      this.modalBom = true
      this.getLinesByCategoriesEdit(row.category_id)
    },
    editSelectedRowBomWork (row) {
      // console.log(row)
      this.getWork()
      this.work.fields.idedit = row.id
      this.work.fields.work_id = { id: row.work_id, label: row.name }
      this.work.fields.time_job = row.time_job
      this.work.fields.minimal = row.minimal
      this.work.fields.factor = row.factor
      this.editar_work = true
      this.modalWork = true
      // this.getLinesByCategoriesEdit(row.category_id)
    },
    closeModalBomEdit () {
      this.ProductsOptionsbyLines = []
      this.bom.fields.category = null
      this.bom.fields.line = null
      this.bom.fields.product = null
      this.bom.fields.cantidad = null
      this.editar_bom = false
    },
    closeModalWorkEdit () {
      this.selectWork = []
      this.work.fields.time_job = null
      this.work.fields.product_id = null
      this.work.fields.work_id = null
      this.work.fields.minimal = null
      this.work.fields.factor = null
      this.editar_work = false
    },
    updateBom () {
      this.$v.bom.fields.$reset()
      this.$v.bom.fields.$touch()
      if (this.$v.bom.fields.$error) {
        this.$q.notify({
          message: 'Por favor revise los campos.',
          color: 'red',
          position: 'top'
        })
        return false
      }
      // this.modalratificantes = false
      // this.showLoading()
      this.$q.loading.show()
      const params = { ...this.bom.fields }
      params.id = this.$route.params.id
      // console.log(params)
      api.put(`/bom/${this.bom.fields.idedit}`, params).then(({ data }) => {
        if (data.result) {
          this.fetchFromServerBom()
          this.bom.fields.category = null
          this.bom.fields.line = null
          this.bom.fields.product = null
          this.bom.fields.cantidad = null
        }
        this.$q.notify({
          message: data.message.content,
          color: data.result ? 'positive' : 'red',
          position: 'top'
        })
        // this.beforeDestroy()
        this.editar_bom = false
        this.modalBom = false
        this.$q.loading.hide()
      })
    },
    updateWork () {
      this.$v.work.fields.$reset()
      this.$v.work.fields.$touch()
      if (this.$v.work.fields.$error) {
        this.$q.notify({
          message: 'Por favor revise los campos.',
          color: 'red',
          position: 'top'
        })
        return false
      }
      // this.modalratificantes = false
      // this.showLoading()
      this.$q.loading.show()
      const params = { ...this.work.fields }
      params.id = this.$route.params.id
      // console.log(params)
      params.minimal = Number(this.work.fields.minimal).toFixed(2)
      params.factor = Number(this.work.fields.factor).toFixed(7)
      api.put(`/works-products/${this.work.fields.idedit}`, params).then(({ data }) => {
        if (data.result) {
          this.fetchFromServerWorks()
        }
        this.$q.notify({
          message: data.message.content,
          color: data.result ? 'positive' : 'red',
          position: 'top'
        })
        // this.beforeDestroy()
        this.editar_work = false
        this.modalWork = false
        this.work.fields.work_id = null
        this.$q.loading.hide()
      })
    },
    deleteSelectedRowBom (id) {
      this.$q.dialog({
        message: '¿Desea eliminar el Componente?',
        ok: {
          label: 'Aceptar',
          color: 'green'
        },
        cancel: {
          label: 'Cancelar',
          color: 'red'
        }
      }).onOk(() => {
        this.$q.loading.show()
        api.delete(`/bom/${id}`).then(({ data }) => {
          if (data.result) {
            this.fetchFromServerBom()
          }
          this.$q.notify({
            message: data.message.content,
            color: data.result ? 'positive' : 'red',
            position: 'top'
          })
          this.$q.loading.hide()
        })
      }).onCancel(() => {})
    },
    updateProductFields () {
      this.$v.product.fields.$reset()
      this.$v.product.fields.$touch()
    },
    updateProduct () {
      this.$v.product.fields.$reset()
      this.$v.product.fields.$touch()
      if (this.$v.product.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      this.$q.loading.show()
      const params = []
      params.id = Number(this.product.fields.id)
      params.name = this.product.fields.name
      params.description = this.product.fields.description
      params.code = this.product.fields.code
      params.line_id = Number(this.product.fields.line.value)
      params.active = Number(this.product.fields.active.value)
      params.clave_producto_id = this.product.fields.clave_producto_id
      params.unit_id = this.product.fields.unit
      params.barcode = this.product.fields.barcode
      params.rebasa_code = this.product.fields.rebasa_code
      params.supplier_code = this.product.fields.supplier_code
      params.weight = this.product.fields.weight
      params.mark = this.mark
      params.additional_information = this.product.fields.additional_information
      // console.log(params)
      if (this.product.fields.family != null && this.product.fields.family.value != null) {
        params.family_id = Number(this.product.fields.family.value)
      }
      api.put(`/products/${params.id}`, params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.updatePrices()
          this.fetchFromServer()
        } else {
          this.$q.loading.hide()
        }
      })
    },
    updatePrices () {
      const params = []
      const prices = []
      if (this.prices.fields.a != null) {
        prices.push({ price_level: 'A', price: Number(this.prices.fields.a) })
      }
      if (this.prices.fields.b != null) {
        prices.push({ price_level: 'B', price: Number(this.prices.fields.b) })
      }
      if (this.prices.fields.c != null) {
        prices.push({ price_level: 'C', price: Number(this.prices.fields.c) })
      }
      if (this.prices.fields.d != null) {
        prices.push({ price_level: 'D', price: Number(this.prices.fields.d) })
      }
      if (this.prices.fields.e != null) {
        prices.push({ price_level: 'E', price: Number(this.prices.fields.e) })
      }
      params.prices = prices
      api.put(`/products/${this.$route.params.id}/prices`, params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.fetchFromServer()
        }
      })
    },
    searchClave (val, update) {
      if (val.length < 3) {
        return false
      }

      update(() => {
        api.get(`/products/searchClave/${val}`).then(({ data }) => {
          this.claveProdOptions = data.claves
        })
      })
    },
    editSelectedRow (id) {
      // console.log(id)
      this.$router.push(`/products/${id}`)
      this.fetchFromServer()
      this.getCategories()
      this.getLines()
      this.getProducts()
      this.getUnits()
      // this.getLinesByCategories()
      this.fetchFromServerBom()
      this.fetchFromServerWorks()
    },
    ModalWorks () {
      this.getWork()
      this.work.fields.time_job = null
      this.work.fields.product_id = null
      this.work.fields.work_id = null
      this.modalWork = true
    },
    getWork () {
      this.selectWork = []
      // this.bom.fields.product = null
      api.get('/works/options').then(({ data }) => {
        // console.log(data.options)
        this.selectWork = data.options
        // this.work.fields.work_id = null
        // this.work.product_id = null
        // console.log(data.tot)
        this.selectWork.unshift({ value: null, label: 'NINGUNO' })
      })
    },
    saveWork () {
      this.$v.work.fields.$reset()
      this.$v.work.fields.$touch()
      if (this.$v.work.fields.$error) {
        this.$q.notify({
          message: 'Por favor selecione una opcion.',
          color: 'warning',
          position: 'top'
        })
        return false
      }
      this.modalWork = false
      // this.showLoading()
      const params = { ...this.work.fields }
      params.id = Number(this.product.fields.id)
      params.minimal = Number(this.work.fields.minimal).toFixed(2)
      params.factor = Number(this.work.fields.factor).toFixed(7)
      api.post('/works-products', params).then(({ data }) => {
        if (data.result) {
          this.work.fields.time_job = null
          this.work.fields.product_id = null
          this.work.fields.work_id = null
          this.fetchFromServerWorks()
        }
        this.$q.notify({
          message: data.message.content,
          color: data.result ? 'positive' : 'red',
          position: 'top'
        })
        // this.beforeDestroy()
      })
    },
    fetchFromServerWorks () {
      // const id = this.product.fields.id
      const id = this.$route.params.id
      if (id) {
        this.$q.loading.show()
        api.get(`/works-products/getby/${id}`).then(({ data }) => {
          this.dataWork = data.info
          this.tot = data.tot
        }).catch(error => error)
        this.$q.loading.hide()
      }
    },
    deleteSelectedRowWork (id) {
      this.$q.dialog({
        message: '¿Desea eliminar la Mano de Obra?',
        ok: {
          label: 'Aceptar',
          color: 'green'
        },
        cancel: {
          label: 'Cancelar',
          color: 'red'
        }
      }).onOk(() => {
        this.$q.loading.show()
        api.delete(`/works-products/${id}`).then(({ data }) => {
          if (data.result) {
            this.fetchFromServerWorks()
          }
          this.$q.notify({
            message: data.message.content,
            color: data.result ? 'positive' : 'red',
            position: 'top'
          })
          this.$q.loading.hide()
        })
      }).onCancel(() => {})
    },
    AddEquivalence () {
      const id = this.$route.params.id
      this.$v.equivalence.fields.$reset()
      this.$v.equivalence.fields.$touch()
      if (this.$v.equivalence.fields.$error) {
        this.$q.notify({
          message: 'Por favor selecione una opcion.',
          color: 'warning',
          position: 'top'
        })
        return false
      }
      // this.showLoading()
      const params = { ...this.equivalence.fields }
      params.id = id
      api.post('/equivalence/', params).then(({ data }) => {
        if (data.result) {
          this.equivalence.fields.product = null
          this.fetchFromServerEquivalence()
          this.fetchFromServer()
        }
        this.$q.notify({
          message: data.message.content,
          color: data.result ? 'positive' : 'red',
          position: 'top'
        })
        // this.beforeDestroy()
      })
    },
    fetchFromServerEquivalence () {
      // const id = this.product.fields.id
      const id = this.$route.params.id
      if (id) {
        this.$q.loading.show()
        api.get(`/equivalence/getbyProduct/${id}`).then(({ data }) => {
          this.dataEquivalence = data.equivalence
          console.log(data.equivalence)
        }).catch(error => error)
        this.$q.loading.hide()
      }
    },
    deleteSelectedRowEquivalente (id) {
      this.$q.dialog({
        message: '¿Desea eliminar el Equivalente?',
        ok: {
          label: 'Aceptar',
          color: 'green'
        },
        cancel: {
          label: 'Cancelar',
          color: 'red'
        }
      }).onOk(() => {
        this.$q.loading.show()
        api.delete(`/equivalence/${id}`).then(({ data }) => {
          if (data.result) {
            this.fetchFromServerEquivalence()
          }
          this.$q.notify({
            message: data.message.content,
            color: data.result ? 'positive' : 'red',
            position: 'top'
          })
          this.$q.loading.hide()
        })
      }).onCancel(() => {})
    },
    editSelectedRowEquivalente (id) {
      this.equivalence.fields.idedit = id
      const idlotw = this.$route.params.id
      api.get(`/equivalence/get/${id}/${idlotw}`).then(({ data }) => {
        console.log(data.info)
        this.equivalence.fields.product = { value: data.info[0].equivalence_id, label: data.info[0].name }
      })
      this.cndEdit = true
    },
    Cancelar () {
      this.cndEdit = false
      this.equivalence.fields.product = null
      this.equivalence.fields.idedit = null
    },
    EditEquivalence () {
      // const id = this.$route.params.id
      this.$v.equivalence.fields.$reset()
      this.$v.equivalence.fields.$touch()
      if (this.$v.equivalence.fields.$error) {
        this.$q.notify({
          message: 'Por favor selecione una opcion.',
          color: 'warning',
          position: 'top'
        })
        return false
      }
      this.cndEdit = false
      this.$q.loading.show()
      // this.showLoading()
      const params = { ...this.equivalence.fields }
      api.put('/equivalence/EditEquivalence', params).then(({ data }) => {
        if (data.result) {
          this.cndEdit = false
          this.equivalence.fields.product = null
          this.fetchFromServerEquivalence()
        }
        this.$q.notify({
          message: data.message.content,
          color: data.result ? 'positive' : 'red',
          position: 'top'
        })
        this.$q.loading.hide()
        // this.beforeDestroy()
      })
    },
    AddStocks () {
      const id = this.$route.params.id
      this.$v.stocks.fields.$reset()
      this.$v.stocks.fields.$touch()
      if (this.$v.stocks.fields.$error) {
        this.$q.notify({
          message: 'Por favor selecione una opcion.',
          color: 'warning',
          position: 'top'
        })
        return false
      }
      // this.showLoading()
      const params = { ...this.stocks.fields }
      params.id = id
      api.post('/minimum-stock/', params).then(({ data }) => {
        if (data.result) {
          this.stocks.fields.product = null
          this.stocks.fields.idedit = null
          this.stocks.fields.stock = null
          this.stocks.fields.branch_id = null
          this.stocks.fields.storage = null
          this.stocks.fields.min_operation = null
          this.stocks.fields.max_operation = null
          this.stocks.fields.capacity = null
          this.fetchFromServerStocks()
          this.fetchFromServer()
        }
        this.$q.notify({
          message: data.message.content,
          color: data.result ? 'positive' : 'red',
          position: 'top'
        })
        // this.beforeDestroy()
      })
    },
    fetchFromServerStocks () {
      // const id = this.product.fields.id
      const id = this.$route.params.id
      if (id) {
        this.$q.loading.show()
        api.get(`/minimum-stock/getbyProduct/${id}`).then(({ data }) => {
          this.dataStocks = data.equivalence
          // console.log(data.equivalence)
        }).catch(error => error)
        this.$q.loading.hide()
      }
    },
    deleteSelectedRowStocks (id) {
      this.$q.dialog({
        message: '¿Desea eliminar el stock minimo?',
        ok: {
          label: 'Aceptar',
          color: 'green'
        },
        cancel: {
          label: 'Cancelar',
          color: 'red'
        }
      }).onOk(() => {
        this.$q.loading.show()
        api.delete(`/minimum-stock/${id}`).then(({ data }) => {
          if (data.result) {
            this.fetchFromServerStocks()
          }
          this.$q.notify({
            message: data.message.content,
            color: data.result ? 'positive' : 'red',
            position: 'top'
          })
          this.$q.loading.hide()
        })
      }).onCancel(() => {})
    },
    editSelectedRowStocks (id) {
      this.stocks.fields.idedit = id
      const idlotw = this.$route.params.id
      api.get(`/minimum-stock/get/${id}/${idlotw}`).then(({ data }) => {
        console.log(data.info)
        this.stocks.fields.storage = { value: data.info[0].almacen_id, label: data.info[0].almacen }
        this.stocks.fields.branch_id = { value: data.info[0].sucursal_id, label: data.info[0].sucursal }
        this.stocks.fields.stock = data.info[0].stock
        this.stocks.fields.min_operation = data.info[0].min_operation
        this.stocks.fields.max_operation = data.info[0].max_operation
        this.stocks.fields.capacity = data.info[0].capacity
      })
      this.cndEdits = true
    },
    CancelarStocks () {
      this.cndEdits = false
      this.stocks.fields.idedit = null
      this.stocks.fields.stock = null
      this.stocks.fields.branch_id = null
      this.stocks.fields.storage = null
      this.stocks.fields.min_operation = null
      this.stocks.fields.max_operation = null
      this.stocks.fields.capacity = null
    },
    EditStocks () {
      // const id = this.$route.params.id
      this.$v.stocks.fields.$reset()
      this.$v.stocks.fields.$touch()
      if (this.$v.stocks.fields.$error) {
        this.$q.notify({
          message: 'Por favor selecione una opcion.',
          color: 'warning',
          position: 'top'
        })
        return false
      }
      this.cndEdits = false
      this.$q.loading.show()
      // this.showLoading()
      const params = { ...this.stocks.fields }
      console.log(params)
      api.put('/minimum-stock/EditEquivalence', params).then(({ data }) => {
        if (data.result) {
          this.cndEdits = false
          this.stocks.fields.product = null
          this.stocks.fields.idedit = null
          this.stocks.fields.stock = null
          this.stocks.fields.branch_id = null
          this.stocks.fields.storage = null
          this.stocks.fields.min_operation = null
          this.stocks.fields.max_operation = null
          this.stocks.fields.capacity = null
          this.fetchFromServerStocks()
        }
        this.$q.notify({
          message: data.message.content,
          color: data.result ? 'positive' : 'red',
          position: 'top'
        })
        this.$q.loading.hide()
        // this.beforeDestroy()
      })
    },
    getBranchesList () {
      api.get('branch-offices/options').then(data => {
        this.branchesList = data.data.options
      })
    },
    getStoragesList () {
      api.get('storages/options').then(data => {
        this.storageOptions = data.data.options
      }
      )
    },
    currencyFormat (num) {
      return Number.parseFloat(num).toFixed(5).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
    }
  }
}
</script>

<style>
</style>

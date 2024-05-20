<template>
  <q-page class="q-pa-sm bg-grey-3" v-if="status == 'NUEVO'">
    <div class="q-pa-sm q-pa-md panel-header">
      <div class="row">
        <div class="col-sm-12 col-md-3">
          <p class="q-ml-md fs28 page-title" style="color:#16122f;"><strong>NUEVO PEDIDO</strong></p>
        </div>
        <div class="col-xs-12 col-md-3" style="text-align:center;">
          <q-btn-toggle
        v-model="cardStyle"
        toggle-color="primary"
        :options="[
          {value: '0', slot: 'one'},
          {value: '1', slot: 'two'}
        ]"
      >
        <template v-slot:one>
          <div class="row items-center">
          <i class="fas fa-grip-horizontal fa-lg" style="margin-right:4px"> </i>
            <div class="text-center">
              Cuadricula
            </div>
          </div>
        </template>

        <template v-slot:two>
          <div class="row items-center">
            <q-icon name="list"></q-icon>
            <div class="text-center">
              Lista
            </div>
          </div>
        </template>
      </q-btn-toggle>
        </div>
        <div class="col-xs-12 col-md-3" style="text-align:center;">
          <q-input dense debounce="200" v-focusshearch="focusShearchProducts" color="dark" v-model="filter" placeholder="Buscar producto" @input="GetProductsByFilter()">
            <template v-slot:append>
              <q-icon name="search" />
            </template>
          </q-input>
          <!--<q-input  dense debounce="200" color="dark" v-model="filter" placeholder="Buscar producto" @input="v => { filter = v.toUpperCase() }">
            <template v-slot:append>
              <q-icon name="search" />
            </template>
          </q-input>-->
          <!-- <q-btn-toggle v-model="categories" style="margin-top: 10px;" toggle-color="primary" :options="categoryOptions"/> -->
        </div>
        <div class="col-sm-12 col-md-3 pull-right">
          <q-btn class="col-xs-3" color="blue" icon="fas fa-file-pdf" v-if="shoppingCartInBulkDetails.length > 0" @click.native="quotationNotePDF()" size="13px" style="margin-top: 10px; margin-right: 5px;">
            <q-tooltip content-class="bg-blue">Cotización</q-tooltip>
          </q-btn>
          <q-btn class="col-xs-3" color="warning" icon="edit" @click.native="editOrder(cartId)" size="13px" style="margin-top: 10px">
            <q-tooltip content-class="bg-warning">Editar Pedido</q-tooltip>
          </q-btn>
          <q-btn v-if="roleId === 1 || roleId === 20 || roleId === 23 || roleId === 4 || roleId === 9 || roleId === 27 || roleId === 22 || roleId === 28 || roleId === 29 || roleId === 17" style="margin-left: 8px;margin-top: 10px" color="red" icon="block" label="CANCELAR" @click="cancel()" />
        </div>
      </div>
    </div>
    <div class="q-col-gutter-md row items-start fstpadding">
      <div class="row col-sm-12 col-md-10" v-if="cardStyle === '0'">
        <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 col-xl-2" v-for="product in filteredProducts" v-bind:key="product.id">
          <q-card style="margin-bottom: 10px; margin-left: 5px; margin-right: 5px;">
            <div class="q-pa-md">
              <q-img :src="product.photo ? serverUrl + 'assets/images/products/' + product.photo : serverUrl + 'images/logo.png'" style="height: 180px; width: 100%;">
              <template v-slot:error>
                <div class="absolute-full flex flex-center bg-gray text-white">
                  SIN IMAGEN
                </div>
              </template>
            </q-img>
          </div>
          <q-card-section class="bg-grey-4" style="padding-top: 0px;padding-bottom: 0px">
              <div class="row text-subtitle1 text-center">
                <q-btn v-if="product.photo != null" class="col-xs-6" color="blue" icon="photo" flat @click.native="viewPhoto(product)" size="15px">
                  <q-tooltip content-class="bg-positive">Ver Fotos</q-tooltip>
                </q-btn>
                <q-btn class="col-xs-6" color="positive" icon="add_shopping_cart"  :disable="cardCND" flat @click.native="openAddDetailModal(product)" size="15px">
                  <q-tooltip content-class="bg-positive">Agregar a carrito</q-tooltip>
                </q-btn>
              </div>
            </q-card-section>
            <q-card-section>
              <div class="row  no-wrap items-center">
                <div class="col text-primary">
                  <div style="height: 20px; line-height: 22px; font-size: 16px">
                    {{ product.name }}
                  </div>
                  <!-- <strong>{{ product.name }}</strong> -->
                </div>
              </div>
            </q-card-section>
            <q-card-section class="q-pt-none">
              <strong>{{ currencyFormatter.format(product.price) }} / Pza.</strong>
              <q-separator />
              <div class="text-subtitle1">
                {{ product.code }}
              </div>
            </q-card-section>
          </q-card>
        </div>
      </div>
      <div class="col-sm-12 col-md-10" v-if="cardStyle === '1'">
    <div>
            <q-table
              flat
              bordered
              :data="filteredProducts"
              :columns="columns"
              row-key="product"
              :pagination.sync="pagination"
            >
              <!--<template v-slot:top>
                <div style="width: 100%;">
                  <q-input dense debounce="300" v-model="filter" placeholder="Buscar" @input="v => { filter = v.toUpperCase() }">
                    <template v-slot:append>
                      <q-icon name="search" />
                    </template>
                  </q-input>
                </div>
              </template>-->
              <template v-slot:body="props">
                <q-tr :props="props">
                  <q-td key="name" :props="props" style="width: 5%; text-align: left;"><label>{{ props.row.name }}</label></q-td>
                  <q-td key="code" :props="props" style="width: 5%; text-align: center;"><label>{{ props.row.code }}</label></q-td>
                  <q-td key="precio_piece" :props="props" style="width: 5%; text-align: right;"><label>{{ currencyFormatter.format(props.row.price) }} / Pza</label></q-td>
                  <q-td key="actions" :props="props" style="width: 5%; text-align: left;">
                    <!--<q-btn class="col-xs-6"  color="negative" icon="compare_arrows" flat @click.native="openAddFreeDetailModal(props.row)" :disable="cardCND" size="15px">
                      <q-tooltip content-class="bg-primary">Agregar Muestra</q-tooltip>
                    </q-btn>-->
                    <q-btn v-if="props.row.photo != null" class="col-xs-6" color="blue" icon="photo" flat @click.native="viewPhoto(props.row)" size="15px">
                  <q-tooltip content-class="bg-positive">Ver Fotos</q-tooltip>
                </q-btn>
                    <q-btn class="col-xs-6" color="positive" icon="add_shopping_cart" flat @click.native="openAddDetailModal(props.row)" size="15px">
                    <q-tooltip content-class="bg-positive">Agregar a carrito</q-tooltip>
                  </q-btn>
                  </q-td>
                </q-tr>
              </template>
            </q-table>
          </div>
    </div>
      <q-dialog v-model="photoModal" persistent>
      <q-card style="min-width: 25%; !important;">
        <q-card-section class="row bg-teal-6 text-white">
          <div class="col-xs-12 col-sm-10 text-h6">Foto: {{ photoProductCode }}</div>
          <q-btn class="col-xs-12 col-sm-2 pull-right" icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-card-section style="max-height: 60vh">
           <q-card-section style="max-height: 60vh">
      <q-carousel
      v-if="cndcarousel == true"
        animated
        v-model="slide"
        arrows
        navigation
        infinite
      >
      <q-carousel-slide v-for="slide in slides" :key="slide.id" :name="slide.id" :img-src="slide.url" >
          <div class="text-right">
    </div>
        </q-carousel-slide>
      </q-carousel>
      </q-card-section>
           </q-card-section>
        <q-card-actions align="right" class="text-primary">
        </q-card-actions>
      </q-card>
    </q-dialog>
      <div class="row col-sm-12 col-md-2">
        <div class="row bg-white border-panel" style="width: 100%;">
          <div class="col q-pa-md">
            <div class="row q-col-gutter-xs q-col-gutter-md">
              <div class="col-xs-12 col-md-12">
                <div class="row">
                  <div class="col-xs-12 col-md-12">
                    <q-btn color="positive" stack icon="check" label="Checkout" @click.native="openCheckout" style="width: 100%;" :disable="shoppingCartInBulkDetails.length == 0 || roleId != 1 && roleId != 20 && roleId != 4 && roleId != 29 && roleId != 27 && roleId != 22 && roleId != 28 && roleId != 29 && roleId != 17" >
                      <strong>{{ currencyFormatter.format(totalPrice * 1.16) }}</strong>
                    </q-btn>
                  </div>
                  <div class="col-xs-12 col-md-12" style="padding-top: 8px;">
                    <q-btn color="warning"  icon-right="comment" label="NOTAS" @click.native="comments" style="width: 100%;" :disable="shoppingCartInBulkDetails.length == 0 || cardCND"/>
                  </div>
                </div>
              </div>
              <div class="col-xs-12 col-md-12">
                <div v-for="detail in shoppingCartInBulkDetails" v-bind:key="detail.id" style="margin-top: 10px; margin-bottom: 10px;">
                  <q-card>
                    <q-card-section class="row">
                      <div class="col-xs-11 col-md-7 col-lg-10">
                        <label>{{ detail.product_name }} <strong>({{ detail.code }})</strong></label>
                      </div>
                      <div class="col-xs-1 col-md-5 col-lg-2">
                        <q-btn color="negative" class="pull-right" flat icon="delete" @click.native="removeInBulkDetail(detail.id)" :disable="cardCND" size="size:10px;">
                          <q-tooltip content-class="bg-negative">Eliminar</q-tooltip>
                        </q-btn>
                      </div>
                      <div class="col-xs-12 col-md-12 col-lg-12">
                        <label> {{ detail.line_name }}</label>
                      </div>
                      <div class="col-xs-12 col-md-12 col-lg-12 pull-right">
                        <label> <strong>{{ currencyFormatter.format(detail.unit_price * 1.16) }} × {{ formatter.format(detail.qty) }} = {{ currencyFormatter.format(detail.amount * 1.16) }}</strong></label>
                      </div>
                    </q-card-section>
                  </q-card>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <q-dialog v-model="addInBulkDetailModal" persistent>
      <q-card style="min-width: 400px;">
        <q-card-section class="bg-blue-7 text-white">
          <div class="text-h6">Agregar producto</div>
        </q-card-section>
        <q-card-section>
          <div class="row">
            <div class="col-xs-12">
              <q-input
                v-focusqty="focusAddQty"
                color="dark"
                bg-color="secondary"
                filled
                v-model="inBulkDetailQty"
                label="Cantidad"
                reverse-fill-mask
                :rules="inBulkDetailQtyRules"
                @keyup.enter="addInBulkDetail()"
                @keyup.esc="closeAddInBulkDetailModal()"
                :suffix="`/${formatter.format(selectedProduct?selectedProduct.stock:0)} Pza.`"
              >
                <template v-slot:prepend>
                  <q-icon name="emoji_objects" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 q-mb-sm" v-if="type_order !== 1">
                    <q-select
                    color="dark"
                    bg-color="secondary"
                    filled
                    v-model="dateShopping"
                    mask="DD/MM/YYYY"
                    label="Entrega">
                      <q-popup-proxy ref="date1" transition-show="scale" transition-hide="scale">
                        <div class="col-sm-12">
                          <q-date v-model="dateShopping" mask="DD/MM/YYYY" today-btn/>
                        </div>
                      </q-popup-proxy>
                      <template v-slot:prepend>
                        <q-icon name="date_range"/>
                      </template>
                    </q-select>
                  </div>
            <div :class="isFreeDetail ? 'col-xs-6 q-pr-xs' : 'col-xs-5 q-pr-xs'">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="pzaPrice"
                label="Precio pza."
                :disable="conditionDisable"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-dollar-sign" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-2 col-md-2" v-if="!isFreeDetail">
              <q-btn v-if="cndButton === 1 && (roleId === 4 || roleId === 20 || roleId === 1 || roleId === 22 || roleId === 29)" flat icon="edit" class="pad" color="warning" @click="editPrice()" />
              <q-btn v-if="cndButton !== 1 && (roleId === 4 || roleId === 20 || roleId === 1 || roleId === 22 || roleId === 29)" flat icon="cancel" class="pad" color="negative" @click="editPricev2(2)" />
            </div>
            <div :class="isFreeDetail ? 'col-xs-6 q-pl-xs' : 'col-xs-5 q-pl-xs'">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="inBulkPrice"
                label="Importe"
                disable
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-dollar-sign" />
                </template>
              </q-input>
            </div>
          </div>
        </q-card-section>
        <q-card-actions align="right" class="text-primary">
          <q-btn color="red" label="Cancelar" @click="closeAddInBulkDetailModal()" v-close-popup />
          <q-btn color="positive" class="q-mr-sm" label="Agregar" @click="addInBulkDetail()" />
        </q-card-actions>
      </q-card>
    </q-dialog>
    <q-dialog v-model="openCheckoutModal" persistent>
      <q-card style="min-width: 700px;">
        <q-card-section class="bg-primary">
          <div class="text-h6 text-white text-center">Capturar para continuar</div>
        </q-card-section>
        <q-card-section>
          <!-- <div class="row">
            <div class="col-xs-6 q-pa-xs">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="oc_date"
                mask="date"
                label="Fecha de Orden de Compra"
                :rules="ocDateRules"
                :error="$v.oc_date.$error"
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
                      v-model="oc_date"
                      @input="() => $refs.ocDate.hide()"
                      today-btn
                    />
                  </div>
                </q-popup-proxy>
              </q-select>
            </div> -->
            <div class="row" v-if="type_order === 1">
            <div class="col-xs-6 q-pa-xs">
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
            <div class="col-xs-6 q-pa-xs">
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
            <!-- <div class="col-xs-6 q-pa-xs">
            <q-uploader
                :url="fileDocumentUrlCitation"
                method="POST"
                label="Formato de Cita"
                ref="fileDocumentCitationRef"
                max-files="1"
                flat
                auto-upload
                no-thumbnails
                :headers="[{name: 'Authorization', value: 'Bearer ' + this.JWT}]"
                @uploaded="afterUploadDocumentFileCitation"
              />
            </div>
          </div> -->
          <div class="row" v-if="term === 'CONTADO'">
            <div class="col-xs-6 q-pa-xs">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="payment_date"
                mask="date"
                label="Fecha de Pago"
                :rules="paymentDateRules"
                :error="$v.payment_date.$error"
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
                      v-model="payment_date"
                      @input="() => $refs.paymentDate.hide()"
                      today-btn
                    />
                  </div>
                </q-popup-proxy>
              </q-select>
            </div>
            <div class="col-xs-6 q-pa-xs">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="payment_method"
                :rules="paymentMethodRules"
                :error="$v.payment_method.$error"
                label="Forma de pago"
                @input="v => { payment_method = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="attach_money" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-6 q-pa-xs">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="payment_reference"
                :rules="paymentReferenceRules"
                :error="$v.payment_reference.$error"
                label="Referencia"
                @input="v => { payment_reference = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="insert_drive_file" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-6 q-pa-xs">
            <q-uploader
                :url="fileDocumentUrlPayment"
                method="POST"
                ref="fileDocumentPaymentRef"
                label="Ticket de Pago"
                max-files="1"
                flat
                auto-upload
                no-thumbnails
                :headers="[{name: 'Authorization', value: 'Bearer ' + this.JWT}]"
                @uploaded="afterUploadDocumentFilePayment"
              />
            </div>
          </div>
          <div class="row">
            <div class="col-xs-3 q-pa-xs">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="subtotal"
                label="Subtotal"
                readonly
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-dollar-sign" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-3 q-pa-xs">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="discount"
                label="Descuento %"
                @input="verifyDiscount()"
                :error="$v.discount.$error"
                :rules="discountRules"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-percentage" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-3 q-pa-xs">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="calcDiscount"
                label="Descuento $"
                readonly
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-tags" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-3 q-pa-xs">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="calcTotal"
                label="Total (IVA)"
                readonly
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-dollar-sign" />
                </template>
              </q-input>
            </div>
          </div>
        </q-card-section>
        <q-card-actions align="right" class="text-primary">
          <q-btn color="red" icon="fas fa-times-circle" label="Cancelar" @click="closeCheckout()" v-close-popup />
          <q-btn color="positive" icon="fas fa-cart-arrow-down" label="ACEPTAR" @click="request()" />
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
        <q-card-actions align="right">
          <q-btn label="Cancelar" color="negative" v-close-popup />
          <q-btn label="Aceptar" color="positive" @click="cancelar()"/>
        </q-card-actions>
      </q-card>
    </q-dialog>
    <q-dialog v-model="modalComment" persistent>
      <q-card style="min-width: 40%; !important;">
        <q-card-section class="bg-primary">
          <div class="row">
            <div class="col-sm-11 text-h6" style="color:white;">AGREGAR COMENTARIOS AL PEDIDO</div>
            <div class="col-sm-1 pull-right"><q-btn color="white" flat v-close-popup round dense icon="close" /></div>
          </div>
        </q-card-section>
        <q-separator />
        <br>
        <div class="row">
          <div class="col-sm-1"></div>
          <div class="col-sm-10">
            <q-input v-model="commentscart" filled clearable autogrow label="Comentarios sobre el pedido." style="background: #D8D1CF;">
            <template v-slot:prepend>
              <q-icon name="chat" />
            </template>
            </q-input>
          </div>
          </div>
          <div class="col-sm-1"></div>
        <br>
        <q-separator />
        <q-card-actions align="right" style="vertical-align: bottom">
          <q-btn flat label="Agregar" color="white" style="background-color: #21ba45;" @click="addComments()" />
        </q-card-actions>
      </q-card>
    </q-dialog>
    <q-dialog v-model="editOrderModal" persistent>
      <q-card style="min-width: 90%; !important;min-height: 100%">
        <q-card-section class="bg-primary">
          <div class="row">
            <div class="col-sm-11 text-h6" style="color:white;">EDITAR PEDIDO</div>
            <div class="col-sm-1 pull-right"><q-btn color="white" flat v-close-popup round dense icon="close" /></div>
          </div>
        </q-card-section>
        <q-separator />
        <q-card-section>
          <div class="text-overline" style="font-size: 16px;">Tipo de Pedido</div>
          <div class="row bg-white border-panel">
            <div class="col q-pl-md q-pt-md">
              <div class="row q-col-gutter-xs q-col-gutter-md">
                <div class="col-xs-12 col-md-4">
                  <q-select color="dark" bg-color="secondary" filled
                            v-model="type_order_edt"
                            :error="$v.type_order_edt.$error"
                            :options="
                            [ {label: 'MOSTRADOR', value: 1},
                              {label: 'CONSIGNACIÓN', value: 2},
                              {label: 'MAYOREO', value: 3},
                            ]"
                            use-input
                            label="Tipo"
                            map-options
                            >
                    <template v-slot:prepend>
                      <q-icon name="person" />
                    </template>
                  </q-select>
                </div>
                <div class="col-xs-12 col-sm-4">
              <div class="col-xs-12 col-sm-6">
              <q-select
                    filled
                    color="dark"
                    bg-color="secondary"
                    v-model="loan"
                    :error="$v.loan.$error"
                    label="Prestamo"
                    :options="selectLoan"
                    use-input
                    hide-selected
                    fill-input
                    input-debounce="0"
                    emit-value
                    map-options
                    @input="checkLoan()"
                    >
                    <template v-slot:prepend>
                   <q-icon name="fas fa-dollar-sign" />
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
            <div class="col-xs-12 col-md-4">
                  <q-select color="dark" bg-color="secondary" filled
                            v-model="special_order"
                            :options="
                            [ {label: 'NORMAL', value: 0},
                              {label: 'ESPECIAL', value: 1},
                            ]"
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
              </div>
            </div>
          </div>
        </q-card-section>
        <q-card-section>
          <div class="text-overline" style="font-size: 16px;">Datos del cliente</div>
          <div class="row bg-white border-panel">
            <div class="col q-pl-md q-pt-md q-pr-md">
              <div class="row q-col-gutter-xs q-col-gutter-md">
                <div class="col-xs-12 col-md-4">
                  <q-select color="dark" bg-color="secondary"
                            filled
                            v-model="customer"
                            :options="filteredCustomerOptions"
                            @filter="filtrarClientes"
                            @input="getOfficebyClient(), getContactOfInput()"
                            label="Cliente"
                            use-input>
                    <template v-slot:prepend>
                      <q-icon name="person" />
                    </template>
                  </q-select>
                </div>
                <div class="col-xs-12 col-md-4">
                  <!-- Quité esto :error="$v.contactCustomer.$error" -->
                  <!-- :rules="contactCustomerRules" -->
                  <q-select color="dark" bg-color="secondary"
                            filled
                            v-model="contactCustomer"
                            :options="optionsContactsFilter"
                            @filter="filtrarContacts"
                            use-input
                            label="Con Atencion a:">
                    <template v-slot:prepend>
                      <q-icon name="person" />
                    </template>
                  </q-select>
                </div>
                <div class="col-xs-12 col-md-4">
                  <q-select color="dark" bg-color="secondary" filled
                            v-model="clientofficeDestiny"
                            :options="officeClientsOptions"
                            use-input
                            label="Sucursal Cliente"
                            emit-value map-options>
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
                v-model="commercial_terms"
                :error="$v.commercial_terms.$error"
                label="Condiciones Comerciales"
                @input="v => { commercial_terms = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-signature" />
                </template>
              </q-input>
            </div>
            <div class="col-md-3">
              <q-select
                color="dark"
                bg-color="secondary"
                filled
                v-model="validity"
                mask="date"
                label="VIGENCIA"
              >
                <q-popup-proxy
                  ref="date"
                  transition-show="scale"
                  transition-hide="scale"
                >
                  <div class="col-sm-3">
                    <q-date
                      v-model="validity"
                      today-btn
                    />
                  </div>
                </q-popup-proxy>
              </q-select>
            </div>
            <div class="col-xs-12 col-sm-3">
              <q-input
                color="dark"
                bg-color="secondary"
                filled
                v-model="lab"
                :error="$v.lab.$error"
                label="L.A.B"
                @input="v => { lab = v.toUpperCase() }"
              >
                <template v-slot:prepend>
                  <q-icon name="fas fa-signature" />
                </template>
              </q-input>
            </div>
              </div>
            </div>
          </div>
        </q-card-section>
        <q-card-section style="max-height: 40vh">
          <div class="text-overline" style="font-size: 16px;">Origen de venta</div>
          <div class="row bg-white border-panel">
            <div class="col q-pt-md q-pl-md q-pr-md">
              <div class="row q-col-gutter-xs q-col-gutter-md">
                <div class="col-xs-12 col-md-3">
                  <q-select color="dark" bg-color="secondary" filled
                            v-model="branch"
                            :options="branchOffices"
                            @input="searchStorage(branch)"
                            label="Sucursal de envío"
                            emit-value map-options>
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
                            :error="$v.storageShopping.$error">
                    <template v-slot:prepend>
                      <q-icon name="fas fa-warehouse" />
                    </template>
                  </q-select>
                </div>
                <div class="col-xs-12 col-md-3">
                  <q-select color="dark" bg-color="secondary" filled
                            v-model="seller"
                            :options="sellerOptions"
                            label="Vendedor"
                            disable
                            emit-value map-options>
                    <template v-slot:prepend>
                      <q-icon name="person" />
                    </template>
                  </q-select>
                </div>
                <div class="col-xs-12 col-md-3">
                  <q-select color="dark" bg-color="secondary" filled
                            v-model="taxInvoice"
                            :error="$v.taxInvoice.$error"
                            :options="[
                            { label: 'FACTURA', value: 0 },
                            { label: 'REMISIÓN', value: 1}
                            ]"
                            :rules="taxInvoicesRules"
                            label="¿Desea factura o remisión?"
                            map-options
                            emit-value
                            @input="checkRemision"
                            >
                    <template v-slot:prepend>
                      <q-icon name="fas fa-cash-register" />
                    </template>
                  </q-select>
                </div>
              </div>
            </div>
          </div>
        </q-card-section>
        <q-card-actions align="right" style="vertical-align: bottom">
          <q-btn flat label="Editar" color="white" style="background-color: #21ba45;" @click="editShoppingCart()" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
  <q-page class="q-pa-md" v-else>
      <div class="row">
        <div class="col-sm-8">
          <span class="q-ml-md grey-8 fs28 page-title">Detalles del pedido #{{ $route.params.id }}</span>
        </div>
        <div class="col-sm-4 pull-right">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="right">
              <q-breadcrumbs-el label="Pedidos" to="/shopping-carts" />
              <q-breadcrumbs-el label="Ver" />
            </q-breadcrumbs>
          </div>
        </div>
      </div>
    <div class="q-pa-md bg-grey-3">
      <div class="row bg-white border-panel">
        <div class="col q-pa-md">
          <div class="q-pa-md">
            <br v-if="shoppingCartInBulkDetails.length > 0">
            <q-table
              title="Fibra abierta"
              dense
              hide-bottom
              :data="shoppingCartInBulkDetails"
              :columns="inBulkColumns"
              row-key="product_name"
              v-if="shoppingCartInBulkDetails.length > 0"
            >
              <template v-slot:body="props">
                <q-tr :props="props">
                  <q-td key="product_name" :props="props" style="width:20%;">{{ props.row.product_name }}</q-td>
                  <q-td key="line_name" :props="props" style="width:15%;">{{ props.row.line_name }}</q-td>
                  <q-td key="category_name" :props="props" style="width:10%;">{{ props.row.category_name }}</q-td>
                  <q-td key="qty" :props="props" style="width:20%;">{{ `${formatter.format(props.row.qty)} KG.` }}</q-td>
                  <q-td key="unit_price" :props="props" style="width:20%;">{{ `${currencyFormatter.format(props.row.unit_price)} MXN` }}</q-td>
                  <q-td key="amount" :props="props" style="width:15%;">{{ `${currencyFormatter.format(props.row.amount)} MXN` }}</q-td>
                </q-tr>
              </template>
            </q-table>
          </div>
          <div class="q-pa-md pull-right">
            <h4>IMPORTE TOTAL: {{ `${currencyFormatter.format(totalPrice)} MXN` }}</h4>
          </div>
        </div>
      </div>
    </div>
  </q-page>
</template>

<script>
import api from '../../commons/api.js'
const { required, requiredIf, maxValue, integer, decimal } = require('vuelidate/lib/validators')

export default {
  name: 'EditShoppingCart',
  validations: {
    inBulkDetailQty: { required, decimal },
    customer: { required },
    branch: { required },
    seller: { required },
    clientofficeDestiny: { required },
    payment_date: { required: requiredIf(function () { return this.term === 'CONTADO' }) },
    payment_method: { required: requiredIf(function () { return this.term === 'CONTADO' }) },
    payment_reference: { required: requiredIf(function () { return this.term === 'CONTADO' }) },
    oc_reference: { },
    commercial_terms: { },
    validity: { },
    lab: { },
    loan: { },
    /* oc_date: { required }, */
    discount: { required, maxValue: maxValue(100), integer },
    storageShopping: { required },
    taxInvoice: { required },
    type_order_edt: { required }
    /* contactCustomer: { required: requiredIf(function () { return this.type_order_edt.label === 'CONSIGNACIÓN' || this.type_order_edt.label === 'MAYOREO' }) } */
  },
  data () {
    return {
      special_order: 0,
      myLocale: {
        /* starting with Sunday */
        days: 'Domingo_Lunes_Martes_Miércoles_Jueves_Viernes_Sábado'.split('_'),
        daysShort: 'Dom_Lun_Mar_Mié_Jue_Vie_Sáb'.split('_'),
        months: 'Enero_Febrero_Marzo_Abril_Mayo_Junio_Julio_Agosto_Septiembre_Octubre_Noviembre_Diciembre'.split('_'),
        monthsShort: 'Ene_Feb_Mar_Abr_May_Jun_Jul_Ago_Sep_Oct_Nov_Dic'.split('_'),
        firstDayOfWeek: 1
      },
      cardStyle: '0',
      cndcarousel: false,
      photoProductId: null,
      photoProductCode: null,
      photoModal: false,
      slide: 0,
      slides: [],
      taxInvoice: null,
      storageShopping: null,
      storageOptions: [],
      authorized_discount: 0,
      discount: 0,
      term: null,
      payment_method: null,
      payment_date: null,
      payment_reference: null,
      payment_document: false,
      oc_date: null,
      oc_reference: null,
      oc_document: false,
      filter: null,
      cartId: 0,
      editOrderModal: false,
      customer: null,
      seller: null,
      clientofficeDestiny: null,
      role: null,
      branch: null,
      cardCND: false,
      formatter: new Intl.NumberFormat('en-US'),
      currencyFormatter: new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
      }),
      openCheckoutModal: false,
      isFreeDetail: false,
      products: [],
      confirmCancelModal: false,
      branchOffices: [],
      filteredCustomerOptions: [],
      customerOptions: [],
      officeClientsOptions: [],
      sellerOptions: [],
      memoryPrice: null,
      customer_id: null,
      commentscart: null,
      categories: 0,
      inBulkProducts: [],
      toogle: 0,
      unitPrices: null,
      pzaPrice: null,
      conditionDisable: true,
      cndButton: 1,
      filteredProducts: [],
      shoppingCartInBulkDetails: [],
      selectLoan: [
        { label: 'NO', value: 0 },
        { label: 'SI', value: 1 }
      ],
      categoryOptions: [
        { value: 0, label: 'TODOS' }
        // { value: 13, label: 'FIBRA ABIERTA' },
        // { value: 6, label: 'FIBRAS' },
        // { value: 5, label: 'LAMINADO' }
      ],
      includeNotAvailable: true,
      price: {
        min: 0,
        max: 200
      },
      loan: null,
      serverUrl: process.env.API,
      addInBulkDetailModal: false,
      modalComment: false,
      selectedProduct: null,
      inBulkDetailQty: null,
      dateShoppingInBulk: null,
      commentShoppingInbulk: null,
      status: null,
      pagination: {
        sortBy: 'id',
        descending: true,
        rowsPerPage: 25
      },
      columns: [
        { name: 'name', align: 'center', label: 'PRODUCTO', field: 'name', sortable: true },
        { name: 'code', align: 'center', label: 'CÓDIGO', field: 'code', sortable: true },
        { name: 'precio_piece', align: 'center', label: 'PRECIO / PZA', field: 'precio_piece', sortable: true },
        { name: 'actions', align: 'center', label: 'ACCIONES', field: 'actions', sortable: true }
      ],
      inBulkColumns: [
        { name: 'product_name', align: 'left', label: 'Producto', field: 'product_name', sortable: true },
        { name: 'line_name', align: 'left', label: 'Línea', field: 'line_name', sortable: true },
        { name: 'category_name', align: 'left', label: 'Categoría', field: 'category_name', sortable: true },
        { name: 'qty', align: 'right', label: 'Cantidad', field: 'qty', sortable: true },
        { name: 'unit_price', align: 'right', label: 'Precio pza.', field: 'unit_price', sortable: true },
        { name: 'amount', align: 'right', label: 'Importe', field: 'amount', sortable: true }
      ],
      type_order: 0,
      branchOfficeCliente: 0,
      shoppingCartCantInvoice: [],
      dateShopping: null,
      type_order_edt: 1,
      contactCustomer: null,
      optionsContactsFilter: [],
      optionContacts: [],
      commercial_terms: null,
      validity: null,
      lab: null,
      timer: 0,
      controller: null,
      auxStorage: null,
      focusShearchProducts: true,
      focusAddQty: false,
      focusShoppingOrder: false
    }
  },
  /* beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(12) && !this.$store.getters['users/roles'].includes(4) && !this.$store.getters['users/roles'].includes(20)) {
      this.$router.push('/')
    }
  }, */
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
  mounted () {
    this.fetchFromServer()
  },
  computed: {
    roleId () {
      const user = this.$store.getters['users/rol']
      return parseInt(user)
    },
    /* contactCustomerRules (val) {
      return [
        val => (this.$v.contactCustomer.required) || 'El campo Contacto es requerido.'
      ]
    }, */
    taxInvoicesRules (val) {
      return [
        val => (this.$v.taxInvoice.required) || 'El campo de Factura o Remisión es requerido.'
      ]
    },
    storageShoppingRules () {
      return [
        val => (this.$v.storageShopping.required) || 'El campo Almacén es requerido.'
      ]
    },
    calcDiscount () {
      let totalDiscount = 0
      if (this.discount != null) {
        totalDiscount = ((this.totalPrice * this.discount) / 100)
      }
      return this.currencyFormatter.format(totalDiscount)
    },
    calcTotal () {
      let total = 0
      if (this.discount != null) {
        const totalDiscount = ((this.totalPrice * this.discount) / 100)
        total = (this.totalPrice * 1.16) - totalDiscount
      }
      return this.currencyFormatter.format(total)
    },
    discountRules (val) {
      return [
        val => (this.$v.discount.required) || 'El campo Descuento es requerido.',
        val => (this.$v.discount.integer) || 'El campo Descuento debe ser entero',
        val => (this.$v.discount.maxValue) || 'El campo Descuento no debe exceder del 100%'
      ]
    },
    subtotal () {
      let subtotal = 0
      if (this.totalPrice != null) {
        subtotal = this.currencyFormatter.format(this.totalPrice)
      }
      return subtotal
    },
    JWT () {
      return localStorage.getItem('JWT')
    },
    fileDocumentUrlOC () {
      return `${process.env.API}documents/file/${this.$route.params.id}`
    },
    fileDocumentUrlCitation () {
      return `${process.env.API}shopping-carts/file3/${this.$route.params.id}`
    },
    fileDocumentUrlPayment () {
      return `${process.env.API}shopping-carts/file2/${this.$route.params.id}`
    },
    customerRules (val) {
      return [
        val => (this.$v.customer.required) || 'El campo Nombre es requerido.'
      ]
    },
    ocReferenceRules (val) {
      return [
        val => (this.$v.oc_reference.required) || 'El campo Orden de Compra es requerido.'
      ]
    },
    ocDateRules (val) {
      return [
        val => (this.$v.oc_date.required) || 'El campo Fecha de Orden de Compra es requerido.'
      ]
    },
    paymentReferenceRules (val) {
      return [
        val => (this.$v.payment_reference.required) || 'El campo Referencia es requerido.'
      ]
    },
    paymentDateRules (val) {
      return [
        val => (this.$v.payment_date.required) || 'El campo Fecha de Pago es requerido.'
      ]
    },
    paymentMethodRules (val) {
      return [
        val => (this.$v.payment_method.required) || 'El campo Forma de Pago es requerido.'
      ]
    },
    officeClientRules (val) {
      return [
        val => (this.$v.clientofficeDestiny.required) || 'El la sucursal del cliente es requerida.'
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
    /* filteredProducts () {
      const inBulkProductsIds = []
      var f1Products = []
      this.inBulkProducts.forEach(product => {
        inBulkProductsIds.push(Number(product.product_id))
      })
      this.products.forEach(product => {
        if (this.includeNotAvailable) {
          if (product.price >= this.price.min && product.price <= this.price.max) {
            f1Products.push(product)
          }
        } else {
          if ((inBulkProductsIds.includes(Number(product.id))) && product.price >= this.price.min && product.price <= this.price.max) {
            f1Products.push(product)
          }
        }
      })
      if (this.filter !== null || this.filter === '') {
        // alert(this.filter)
        f1Products = f1Products.filter(p => p.name.includes(this.filter) || p.code.includes(this.filter))
      }
      return f1Products
    }, */
    minPrice () {
      return 0
    },
    maxPrice () {
      let maxPrice = 0
      this.products.forEach(product => {
        if (Number(product.price) > Number(maxPrice)) {
          maxPrice = Number(product.price)
        }
      })
      return maxPrice
    },
    unitPrice () {
      if (this.selectedProduct != null && this.selectedProduct.id != null) {
        return this.selectedProduct.price
      }
      return this.currencyFormatter.format(0)
    },
    inBulkPrice () {
      if (this.selectedProduct != null && this.selectedProduct.id != null && this.inBulkDetailQty != null) {
        // return this.currencyFormatter.format(this.inBulkDetailQty * this.kgPrice)
        return this.currencyFormatter.format(this.inBulkDetailQty * this.pzaPrice)
      }
      return this.currencyFormatter.format(0)
    },
    totalPrice () {
      let price = 0
      this.shoppingCartInBulkDetails.forEach(detail => {
        price += Number(detail.amount)
      })
      return price
    },
    inBulkDetailQtyRules (val) {
      return [
        val => (this.$v.inBulkDetailQty.required) || 'El campo Cantidad es requerido.',
        val => (this.$v.inBulkDetailQty.decimal) || 'El campo Cantidad debe ser numérico.'
      ]
    },
    inBulkDetailAvailableQty () {
      if (this.selectedProduct != null && this.selectedProduct.id != null) {
        const filteredInBulkProducts = this.inBulkProducts.filter(inBulkProduct => Number(inBulkProduct.product_id) === Number(this.selectedProduct.id))
        if (filteredInBulkProducts.length === 1) {
          const availableQty = filteredInBulkProducts[0].stock
          return availableQty
        }
      }
      return 0
    },
    canGenerateInvoice () {
      let canGenerate = true
      /* const inBulkDetailsWithStock = this.shoppingCartInBulkDetails.filter(detail => detail.condition && detail.status === 'AUTORIZADO')
      let inBulk = 0
      if (inBulkDetailsWithStock) {
        inBulk = inBulkDetailsWithStock.length
      }
      if (inBulk > 0) {
        canGenerate = true
      } */
      // console.log('caninvoice?')
      // console.log(this.shoppingCartCantInvoice)
      this.shoppingCartCantInvoice.forEach(product => {
        if (product.stock === false ? true : Number(product.qty) > Number(product.stock)) {
          canGenerate = false
        }
      })
      return canGenerate
    }
  },
  directives: {
    focusshearch: {
      update: function (el, binding) {
        if (binding.value) {
          el.focus()
        }
      }
    },
    focusqty: {
      update: function (el, binding) {
        if (binding.value) {
          el.focus()
        }
      }
    }
  },
  methods: {
    closeCheckout () {
      this.openCheckoutModal = false
      this.focusShoppingOrder = false
      this.focusShearchProducts = true
    },
    checkRemision () {
      if (this.taxInvoice === 1 && this.loan.value === 1) {
        this.$q.notify({
          message: 'Para una remisión el pedido no debe ser tipo prestamo.',
          position: 'top',
          color: 'warning'
        })
        // this.taxInvoice = { label: 'REMISIÓN', value: 0 }
        this.taxInvoice = 0
      }
    },
    checkLoan () {
      console.log()
      if (this.loan === 1) {
        this.taxInvoice = 0
      } else {
        this.taxInvoice = null
      }
    },
    filtrarContacts (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.optionsContactsFilter = this.optionContacts.filter(v => v.label.toLowerCase().indexOf(needle) > -1 && v.label !== 'TODOS')
      })
    },
    getContactOfInput () {
      const id = this.customer.value
      this.contactCustomer = null
      api.get(`/customer-contacts/getContacts/${id}`).then(({ data }) => {
        if (data.result) {
          // console.log(data.contacts)
          this.optionContacts = data.contacts
        }
      })
    },
    getContactCustomer (id) {
      // console.log(id)
      api.get(`/customer-contacts/getContacts/${id}`).then(({ data }) => {
        if (data.result) {
          // console.log(data.contacts)
          this.optionContacts = data.contacts
        }
      })
    },
    GetProductsByFilter () {
      api.get(`/shopping-carts/${this.$route.params.id}`).then(({ data }) => {
        var aux = null
        if (this.filter != null) {
          aux = this.filter
        }
        var idStorage = data.shoppingCart.id_storage
        var params = []
        params.priceLevel = data.shoppingCart.price_list
        params.filter = aux
        params.type_order = data.shoppingCart.type_order
        params.idStorage = idStorage
        // alert(data.shoppingCart.price_list)
        if (this.controller) {
          api.cancel()
        }
        this.controller = api.post('/products/productsWithPriceByFilter', params).then(({ data }) => {
          this.products = data.products
          this.price.min = this.minPrice
          this.price.max = this.maxPrice
          this.$q.loading.hide()
          // const inBulkProductsIds = []
          var f1Products = []
          // api.post(`/storages/${idStorage}/bulk-products`, this.products).then(({ data }) => {
          //   this.inBulkProducts = data.products
          // })
          // this.inBulkProducts.forEach(product => {
          //   inBulkProductsIds.push(Number(product.product_id))
          // })
          this.products.forEach(product => {
            if (this.includeNotAvailable) {
              if (product.price >= this.price.min && product.price <= this.price.max) {
                f1Products.push(product)
              }
            } else {
              if (Number(product.stock) > 0 && product.price >= this.price.min && product.price <= this.price.max) {
                f1Products.push(product)
              }
            }
          })
          // if (this.filter !== null || this.filter === '') {
          // alert(this.filter)
          // f1Products = f1Products.filter(p => p.name.includes(this.filter) || p.code.includes(this.filter))
          // } filteredProducts
          this.filteredProducts = f1Products
          this.controller = null
        })
      })
    },
    quotationNotePDF () {
      const order = 'si'
      const uri = process.env.API + `shopping-carts/quotationNotePDF/${this.$route.params.id}/${order}`
      window.open(uri, '_blank')
    },
    searchStorage (id) {
      this.storageShopping = null
      api.get(`/storages/getStoragesOfBranch/${id}`).then(({ data }) => {
        if (data.result) {
          this.storageOptions = data.storage
        }
      }).catch()
    },
    searchStorage2 (id) {
      api.get(`/storages/getStoragesOfBranch/${id}`).then(({ data }) => {
        if (data.result) {
          this.storageOptions = data.storage
        }
      }).catch()
    },
    verifyDiscount () {
      if (this.authorized_discount != null && this.discount != null) {
        if (Number(this.discount) > Number(this.authorized_discount)) {
          this.$q.notify({
            message: 'El descuento capturado supera el descuento autorizado del ' + this.authorized_discount + '%',
            position: 'top',
            color: 'warning'
          })
          this.discount = 0
        }
      }
    },
    invertDate (date) {
      if (date !== null) {
        var info = date.split('/').reverse().join('-')
      } else {
        return null
      }
      return info
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
    afterUploadDocumentFilePayment (response) {
      const data = JSON.parse(response.xhr.response)
      this.$q.notify({
        message: data.message.content,
        position: 'top',
        color: (data.result ? 'positive' : 'warning')
      })
      if (data.result) {
        this.payment_document = true
      }
    },
    afterUploadDocumentFileCitation (response) {
      const data = JSON.parse(response.xhr.response)
      this.$q.notify({
        message: data.message.content,
        position: 'top',
        color: (data.result ? 'positive' : 'warning')
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
    fetchFromServer () {
      this.role = this.$store.getters['users/rol']
      if (this.role === 1 || this.role === 20 || this.role === 4 || this.role === 29 || this.role === 27 || this.role === 22 || this.role === 28 || this.role === 29 || this.role === 17) {
        this.cardCND = false
      } else {
        this.cardCND = true
      }
      this.cartId = this.$route.params.id
      api.get(`/shopping-carts/${this.$route.params.id}`).then(({ data }) => {
        if (!data.shoppingCart) {
          this.$router.push('/shopping-carts')
        } else {
          // console.log(data.shoppingCart.type_order)
          this.type_order = data.shoppingCart.type_order
          this.special_order = data.shoppingCart.special_order
          this.$q.loading.show()
          this.customer = { label: data.shoppingCart.customer_name, value: data.shoppingCart.customer_id }
          this.getOfficebyClient()
          this.seller = data.shoppingCart.user_id
          this.branch = data.shoppingCart.idbo
          this.status = data.shoppingCart.status
          this.customer_id = data.shoppingCart.customer_id
          this.commentscart = data.shoppingCart.comments
          this.commercial_terms = data.shoppingCart.commercial_terms
          this.validity = data.shoppingCart.validity
          this.lab = data.shoppingCart.lab
          this.storageShopping = { label: data.shoppingCart.name_storage, value: data.shoppingCart.id_storage }
          this.taxInvoice = data.shoppingCart.tax_invoice // === 1 ? { label: 'REMISIÓN', value: 1 } : { label: 'FACTURA', value: 1 }
          this.loan = data.shoppingCart.loan === 0 ? { label: 'NO', value: 0 } : { label: 'SI', value: 1 }
          var idStorage = data.shoppingCart.id_storage
          this.term = data.shoppingCart.term
          this.authorized_discount = Number(data.shoppingCart.authorized_discount)
          this.branchOfficeCliente = data.shoppingCart.branchofficedestiny
          this.special_order = data.shoppingCart.special_order
          // console.log('Console console')
          this.auxStorage = data.shoppingCart.id_storage
          this.getContactCustomer(data.shoppingCart.customer_id)
          if (data.shoppingCart.contact_client_id !== null) {
            this.contactCustomer = { label: data.shoppingCart.name_contact, value: data.shoppingCart.contact_client_id }
          } else {
            this.contactCustomer = null
          }
          this.type_order_edt = { value: data.shoppingCart.type_order, label: data.shoppingCart.name_type_pay }
          this.searchStorage2(this.branch)
          api.get(`/storages/getStoragesbyShoppingCart/${this.$route.params.id}`).then(({ data }) => {
            this.bulk = data[0]
            api.get(`/shopping-cart-in-bulk-details/shopping-cart/${this.$route.params.id}`).then(({ data }) => {
              this.shoppingCartInBulkDetails = data.details
            })
          })
          api.get(`/products/productsWithPrice/${data.shoppingCart.price_list}/${data.shoppingCart.type_order}/${idStorage}`).then(({ data }) => {
            this.products = data.products
            this.price.min = this.minPrice
            this.price.max = this.maxPrice
            this.$q.loading.hide()
            // const inBulkProductsIds = []
            var f1Products = []
            console.log(data.products)
            // var params = data.products
            // api.post(`/storages/${idStorage}/bulk-products`, params).then(({ data }) => {
            //   this.inBulkProducts = data.products
            // })
            // this.inBulkProducts.forEach(product => {
            //   inBulkProductsIds.push(Number(product.product_id))
            // })
            this.products.forEach(product => {
              if (this.includeNotAvailable) {
                if (product.price >= this.price.min && product.price <= this.price.max) {
                  f1Products.push(product)
                }
              } else {
                if (Number(product.stock > 0) && product.price >= this.price.min && product.price <= this.price.max) {
                  f1Products.push(product)
                }
              }
            })
            if (this.filter !== null || this.filter === '') {
            // alert(this.filter)
              f1Products = f1Products.filter(p => p.name.includes(this.filter) || p.code.includes(this.filter))
            }
            this.filteredProducts = f1Products
          })
        }
      })
    },
    addToFavorites (product) {
      alert('Agregar a favoritos')
    },
    openCompareModal (product) {
      alert('Comparar producto')
    },
    openDetailsModal (product) {
      alert('Ver detalles')
    },
    /* openAddDetailModal (product) {
      const params = []
      params.productId = Number(product.id)
      params.customerId = Number(this.customer_id)
      params.categoryId = Number(product.category_id)
      api.post('/shopping-carts/memory-prices', params).then(({ data }) => {
        if (data.result === true) {
          this.pzaPrice = data.price
        } else {
          this.pzaPrice = product.price
        }
        this.$q.loading.show()
        this.selectedProduct = product
        this.addInBulkDetailModal = true
        this.isFreeDetail = false
        this.$q.loading.hide()
      })
    }, */
    openAddDetailModal (product) {
      const params = []
      this.focusAddQty = true
      // this.focusShearchProducts = false
      params.productId = Number(product.id)
      params.customerId = Number(this.customer_id)
      params.categoryId = Number(product.category_id)
      /* api.post('/shopping-carts/memory-prices', params).then(({ data }) => {
        if (data.result === true) {
          this.pzaPrice = data.price
        } else {
        }
      }) */
      this.pzaPrice = product.price
      this.$q.loading.show()
      this.selectedProduct = product
      this.addInBulkDetailModal = true
      this.isFreeDetail = false
      this.filter = null
      this.GetProductsByFilter()
      this.$q.loading.hide()
    },
    openAddFreeDetailModal (product) {
      const params = []
      params.productId = Number(product.id)
      params.customerId = Number(this.customer_id)
      params.categoryId = Number(product.category_id)
      this.pzaPrice = 0
      this.selectedProduct = product
      this.addInBulkDetailModal = true
      this.isFreeDetail = true
    },
    closeAddInBulkDetailModal () {
      this.addInBulkDetailModal = false
      this.selectedProduct = null
      this.inBulkDetailQty = null
      this.cndButton = 1
      this.commentShoppingInbulk = null
      this.dateShoppingInBulk = null
      this.dateShopping = null
      this.focusShearchProducts = true
    },
    async addInBulkDetail () {
      this.$v.inBulkDetailQty.$reset()
      this.$v.inBulkDetailQty.$touch()
      if (this.$v.inBulkDetailQty.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        return false
      }
      var resultdate = false
      if (this.dateShopping !== null) {
        await api.get(`/shopping-cart-in-bulk-details/getDate/${this.$formatDate(this.dateShopping)}`).then(({ data }) => {
          console.log(data.result)
          resultdate = data.result
        })
        if (resultdate === false) {
          this.$q.dialog({
            title: 'Error',
            message: 'Por favor elija una fecha actual o venidera.',
            persistent: true
          })
          return false
        }
      }
      if (Number(this.inBulkDetailQty) <= 0 || this.inBulkDetailQty === null) {
        this.$q.dialog({
          title: 'Error',
          message: 'La Cantidad debe ser mayor a 0.',
          persistent: true
        })
      } else {
        if (Number(this.pzaPrice) === 0 && this.isFreeDetail) {
          // Es una muestra
          this.$q.loading.show()
          const params = []
          params.shoppingCartId = Number(this.$route.params.id)
          params.productId = Number(this.selectedProduct.id)
          params.qty = Number(this.inBulkDetailQty)
          params.price = Number(this.pzaPrice)
          params.dateShoppingInBulk = this.dateShopping === null ? null : this.dateShopping.replace(/\//g, '-')
          api.post('/shopping-cart-in-bulk-details', params).then(({ data }) => {
            this.$q.notify({
              message: data.message.content,
              position: 'top',
              color: (data.result ? 'positive' : 'warning')
            })
            if (data.result) {
              api.get(`/shopping-cart-in-bulk-details/shopping-cart/${this.$route.params.id}`).then(({ data }) => {
                this.shoppingCartInBulkDetails = data.details
                this.$q.loading.hide()
                this.closeAddInBulkDetailModal()
                this.focusShearchProducts = true
                this.cndButton = 1
              })
            } else {
              this.$q.loading.hide()
            }
          })
        } else if (Number(this.pzaPrice) <= 0) {
          this.$q.dialog({
            title: 'Error',
            message: 'El precio del producto debe ser mayor a 0.',
            persistent: true
          })
        } else {
          this.$q.loading.show()
          const params = []
          params.shoppingCartId = Number(this.$route.params.id)
          params.productId = Number(this.selectedProduct.id)
          params.qty = Number(this.inBulkDetailQty)
          params.price = Number(this.pzaPrice)
          params.dateShoppingInBulk = this.dateShopping === null ? null : this.dateShopping.replace(/\//g, '-')
          api.post('/shopping-cart-in-bulk-details', params).then(({ data }) => {
            this.$q.notify({
              message: data.message.content,
              position: 'top',
              color: (data.result ? 'positive' : 'warning')
            })
            if (data.result) {
              api.get(`/shopping-cart-in-bulk-details/shopping-cart/${this.$route.params.id}`).then(({ data }) => {
                this.shoppingCartInBulkDetails = data.details
                this.$q.loading.hide()
                this.closeAddInBulkDetailModal()
                this.focusShearchProducts = true
                this.cndButton = 1
              })
            } else {
              this.$q.loading.hide()
            }
          })
        }
      }
    },
    removeInBulkDetail (id) {
      this.$q.loading.show()
      api.delete(`/shopping-cart-in-bulk-details/${id}`).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          api.get(`/shopping-cart-in-bulk-details/shopping-cart/${this.$route.params.id}`).then(({ data }) => {
            this.shoppingCartInBulkDetails = data.details
            this.$q.loading.hide()
          })
        } else {
          this.$q.loading.hide()
        }
      })
    },
    openCheckout () {
      this.focusShearchProducts = false
      this.focusShoppingOrder = true
      this.openCheckoutModal = true
    },
    showLoading () {
      this.$q.loading.show({
        message: '<b>process</b> is in progress.<br/><span class="text-primary">Hang on...</span>',
        html: true
      })
      // hiding in 3s
      this.timer = setTimeout(() => {
        this.$q.loading.hide()
        this.timer = 0
      }, 10000)
    },
    async request () {
      // this.$refs.fileDocumentOCRef.upload()
      // if (this.term === 'CONTADO') {
      //   this.$refs.fileDocumentPaymentRef.upload()
      // }
      /* this.$v.oc_date.$reset()
      this.$v.oc_date.$touch() */
      this.$v.oc_reference.$reset()
      this.$v.oc_reference.$touch()
      this.$v.payment_method.$reset()
      this.$v.payment_method.$touch()
      this.$v.payment_date.$reset()
      this.$v.payment_date.$touch()
      this.$v.payment_reference.$reset()
      this.$v.payment_reference.$touch()
      this.$v.discount.$reset()
      this.$v.discount.$touch()
      if (this.$v.discount.$error || this.$v.payment_method.$error || this.$v.payment_date.$error || this.$v.payment_reference.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, llene todos los campos.',
          persistent: true
        })
        return false
      // } else if (!this.oc_document) {
      //   this.$q.notify({
      //     message: 'Favor de Subir la Orden de Compra',
      //     position: 'top',
      //     color: 'warning'
      //   })
      // } else if (!this.payment_document && this.term === 'CONTADO') {
      //   this.$q.notify({
      //     message: 'Favor de Subir el Ticket de Pago',
      //     position: 'top',
      //     color: 'warning'
      //   })
      } else {
        const params = {}
        params.total_cost = this.totalPrice
        params.payment_method = this.payment_method
        params.payment_reference = this.payment_reference
        params.payment_date = this.invertDate(this.payment_date)
        params.oc_date = this.invertDate(this.oc_date)
        params.oc_reference = this.oc_reference
        params.oc_term = this.term
        params.discount = Number(this.discount)
        if (this.type_order !== 1) {
          this.$q.loading.show({
            spinnerColor: 'primary',
            spinnerSize: 140,
            backgroundColor: 'white',
            message: 'Cargando..',
            messageColor: 'black'
          })
          await api.put(`/shopping-carts/request/${this.$route.params.id}`, params).then(({ data }) => {
            this.$q.notify({
              message: data.message.content,
              position: 'top',
              color: (data.result ? 'positive' : 'warning')
            })
            if (data.limit_message) {
              this.$q.notify({
                timeout: 8000,
                message: data.limit_message.content,
                position: 'top',
                color: 'warning'
              })
            }
            if (data.expired_message) {
              this.$q.notify({
                message: data.expired_message.content,
                position: 'top',
                color: 'warning'
              })
            }
            if (data.result) {
              // this.$router.push('/shopping-carts')
              this.$router.push(`/shopping-carts/orders/${this.$route.params.id}`)
              // this.approve()
              this.$q.loading.hide()
            } else {
              this.$q.loading.hide()
            }
          })
        } else {
          this.$q.loading.show()
          // Cotizado
          await api.put(`/inmediate-sales/request/${this.$route.params.id}`, params).then(({ data }) => {
            if (!data.result) {
              this.$q.notify({
                message: data.message.content,
                position: 'top',
                color: (data.result ? 'positive' : 'warning')
              })
            }
            if (data.limit_message) {
              this.$q.notify({
                timeout: 8000,
                message: data.limit_message.content,
                position: 'top',
                color: 'warning'
              })
            }
            if (data.expired_message) {
              this.$q.notify({
                message: data.expired_message.content,
                position: 'top',
                color: 'warning'
              })
            }
            if (data.result) {
              // Autorizado
              this.autorizedStatus()
            } else {
              this.$q.loading.hide()
            }
          })
        }
      }
    },
    async autorizedStatus () {
      await api.put(`/inmediate-sales/${this.$route.params.id}/approve`).then(({ data }) => {
        if (!data.result) {
          this.$q.notify({
            message: data.message.content,
            position: 'top',
            color: (data.result ? 'positive' : 'warning')
          })
        }
        if (data.result) {
          this.searchStock()
        } else {
          this.$q.loading.hide()
        }
      })
    },
    async searchStock () {
      var f = new Date()
      const date = f.getDate() + '-' + (f.getMonth() + 1) + '-' + f.getFullYear()
      const params = []
      params.customerBranchOfficeId = this.branchOfficeCliente
      params.saleDate = date
      params.comments = 'VENTA EN SUCURSAL'
      await api.get(`/shopping-cart-in-bulk-details/shopping-cart/${this.$route.params.id}`).then(({ data }) => {
        // console.log('shopping-cart-in-bulk-details')
        console.log(data)
        this.shoppingCartCantInvoice = data.details
        // Revisa si hay estock dependiendo cantidad de productos
        if (this.canGenerateInvoice) {
        // Remisionado
          this.invoicedStatus(params)
        } else {
          this.$q.notify({
            message: 'El pedido no se puede remisionar debido a que no hay stock existente',
            position: 'top',
            color: ('warning')
          })
          this.$q.loading.hide()
          this.$router.push(`/shopping-carts/orders/${this.$route.params.id}`)
        }
      })
      // this.$router.push('/requested-orders')
      this.$q.loading.hide()
    },
    async invoicedStatus (params) {
      await api.put(`/shopping-carts/${this.$route.params.id}/generate-invoice`, params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$router.push(`/storage-exits/${data.invoice.id}`)
          this.$q.loading.hide()
        } else {
          this.$q.loading.hide()
        }
      })
    },
    editPrice () {
      this.focusAddQty = false
      this.focusShearchProducts = false
      if (this.inBulkDetailQty > 0) {
        this.conditionDisable = false
        this.cndButton = 0
        this.pzaPrice = null
      } else {
        this.$q.notify({
          message: 'Agregue una cantidad del producto',
          position: 'top',
          color: ('warning')
        })
      }
    },
    editPricev2 (val) {
      this.conditionDisable = true
      this.cndButton = 1
    },
    comments () {
      this.modalComment = true
    },
    addComments () {
      const params = []
      params.cartId = Number(this.$route.params.id)
      params.cartComments = this.commentscart
      api.post('/shopping-carts/addCommentsToCart', params).then(({ data }) => {
        if (data.result) {
          this.modalComment = false
          this.$q.loading.hide()
        } else {
          this.$q.loading.hide()
        }
      })
    },
    editOrder (id) {
      if (id) {
        this.getClients()
        this.getSellers()
        this.focusShearchProducts = false
        this.editOrderModal = true
      }
    },
    getClients () {
      const sellerId = this.$store.getters['users/id']
      api.get(`/customers/getCustomersBySeller/${sellerId}`).then(({ data }) => {
        this.customerOptions = data.options
      })
      api.get('/branch-offices/options').then(({ data }) => {
        this.branchOffices = data.options
        this.$q.loading.hide()
      })
    },
    getSellers () {
      api.get('/users/getSeller').then(({ data }) => {
        this.sellerOptions = data.options
        this.$q.loading.hide()
      })
    },
    filtrarClientes (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.filteredCustomerOptions = this.customerOptions.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
      })
    },
    getOfficebyClient () {
      this.officeClientsOptions = []
      const params = []
      params.customer = this.customer.value
      api.post('/customers/officeoptions', params).then(({ data }) => {
        if (data.result) {
          this.officeClientsOptions = data.options
          if (data.options.length !== 0) {
            this.clientofficeDestiny = { label: data.options[0].label, value: data.options[0].value }
          }
        }
      })
    },
    editShoppingCart () {
      this.$v.customer.$reset()
      this.$v.customer.$touch()
      this.$v.seller.$reset()
      this.$v.seller.$touch()
      this.$v.clientofficeDestiny.$reset()
      this.$v.clientofficeDestiny.$touch()
      this.$v.branch.$reset()
      this.$v.branch.$touch()
      this.$v.storageShopping.$reset()
      this.$v.storageShopping.$touch()
      this.$v.type_order_edt.$reset()
      this.$v.type_order_edt.$touch()
      /* this.$v.contactCustomer.$reset()
      this.$v.contactCustomer.$touch() */
      this.$v.commercial_terms.$reset()
      this.$v.commercial_terms.$touch()
      this.$v.validity.$reset()
      this.$v.validity.$touch()
      this.$v.lab.$reset()
      this.$v.lab.$touch()
      this.$v.loan.$reset()
      this.$v.loan.$touch()
      /* if (this.$v.contactCustomer.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, llene todos los campos.',
          persistent: true
        })
        return false
      } */
      if (this.$v.type_order_edt.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, llene todos los campos.',
          persistent: true
        })
        return false
      }
      if (this.$v.seller.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, llene todos los campos.',
          persistent: true
        })
        return false
      }
      if (this.$v.clientofficeDestiny.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, llene todos los campos.',
          persistent: true
        })
        return false
      }
      if (this.$v.storageShopping.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, llene todos los campos.',
          persistent: true
        })
        return false
      }
      if (this.$v.customer.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, llene todos los campos.',
          persistent: true
        })
        return false
      }
      if (this.$v.branch.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, llene todos los campos.',
          persistent: true
        })
        return false
      }
      if (this.auxStorage !== this.storageShopping.value) {
        api.delete(`/shopping-cart-in-bulk-details/deleteInbulk/${this.cartId}`).then(({ data }) => {
          if (data.result) {
            this.$q.notify({
              message: data.message.content,
              position: 'top',
              color: (data.result ? 'positive' : 'warning')
            })
          }
        })
      }
      this.$q.loading.show()
      const params = []
      params.shoppingCartId = Number(this.cartId)
      params.customer = Number(this.customer.value)
      params.officecustomer = Number(this.clientofficeDestiny.value)
      params.originbranch = Number(this.branch)
      params.seller = Number(this.seller)
      params.storage_id = this.storageShopping.value
      params.tax_invoice = this.taxInvoice
      params.type_order = this.type_order_edt.value
      params.contact_client_id = this.contactCustomer
      params.commercial_terms = this.commercial_terms
      params.validity = this.validity
      params.lab = this.lab
      params.loan = this.loan
      params.special_order = this.special_order
      api.post('/shopping-carts/updateCart', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.fetchFromServer()
          // this.customer = null
          this.seller = null
          this.clientofficeDestiny = null
          this.branch = null
          this.editOrderModal = false
        } else {
          this.$q.loading.hide()
        }
      })
    },
    viewPhoto (row) {
      api.get(`/products/getPhotos/${row.id}`).then(({ data }) => {
        if (data.photos.length > 0) {
          this.cndcarousel = true
          this.slide = data.photos[0].id
        } else {
          this.cndcarousel = false
        }
        var labels = []
        for (var i = 0; i < data.photos.length; i++) {
          labels.push({ id: data.photos[i].id, url: this.serverUrl + 'assets/images/products/' + data.photos[i].url })
        }
        this.slides = labels
        this.photoProductId = row.id
        this.photoProductCode = row.code
      })
      this.photoModal = true
    }
    // aprovar
    /* approve () {
      this.$q.loading.show()
      api.put(`/shopping-carts/${this.$route.params.id}/approve`).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          // this.sendPDF()
          this.$router.push('/shopping-carts')
          // this.$router.push('/requested-orders')
          this.$q.loading.hide()
        } else {
          this.$q.loading.hide()
        }
      })
    } */
  }
}
</script>

<style>
.fstpadding{
  padding-top: 10px;
}
.pad{
  font-size: 20px;
  padding-top: 2%;
}
.a{
  width:55%;
}
.aa{
  width:45%;
}
.b{
  width:100%;
}
.c{
  width:100%;
}
</style>

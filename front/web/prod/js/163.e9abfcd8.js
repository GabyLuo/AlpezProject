(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[163],{"32f7":function(e,t,s){"use strict";s.r(t);var a=function(){var e=this,t=e.$createElement,s=e._self._c||t;return s("q-page",{staticClass:"bg-grey-3"},[s("div",{staticClass:"q-pa-sm panel-header"},[s("div",{staticClass:"row q-col-gutter-xs q-col-gutter-md"},[s("div",{staticClass:"col-sm-4",staticStyle:{"font-size":"20px"}},[s("div",{staticClass:"q-pa-md q-gutter-sm"},[s("q-breadcrumbs",[s("q-breadcrumbs-el",{attrs:{label:"",icon:"home",to:"/"}}),s("q-breadcrumbs-el",{attrs:{label:"Pedidos"}})],1)],1)]),s("div",{staticClass:"col-sm-8"},[s("div",{staticClass:"col-xs-12 col-md-4 offset-md-10 pull-right"},[s("div",[e.haspermissionv1?s("q-btn",{staticStyle:{"margin-left":"10px"},attrs:{color:"green",icon:"fas fa-file-excel"},on:{click:function(t){return e.generateCSV()}}},[s("q-tooltip",[e._v("GENERAR CSV")])],1):e._e(),e.haspermissionv1?s("q-btn",{staticStyle:{"margin-left":"10px"},attrs:{color:"red",icon:"fas fa-file-pdf"},on:{click:function(t){return e.generatePDF()}}},[s("q-tooltip",[e._v("GENERAR PDF")])],1):e._e(),e.haspermissionv2?s("q-btn",{staticClass:"bg-primary",staticStyle:{"margin-left":"10px",color:"white"},attrs:{icon:"add",label:"Nuevo"},nativeOn:{click:function(t){return e.openModal()}}},[s("q-tooltip",[e._v("Crear Pedido")])],1):e._e()],1)])])])]),s("div",{staticClass:"q-pa-md bg-grey-3"},[s("div",{staticClass:"row bg-white border-panel"},[s("div",{staticClass:"col q-pa-md"},[s("div",{staticClass:"row q-col-gutter-xs q-col-gutter-md"},[s("div",{staticClass:"col-sm-2"}),s("div",{staticClass:"col-md-2"},[s("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",mask:"date",label:"Desde"},model:{value:e.saleDatev1,callback:function(t){e.saleDatev1=t},expression:"saleDatev1"}},[s("q-popup-proxy",{ref:"date",attrs:{"transition-show":"scale","transition-hide":"scale"}},[s("div",{staticClass:"col-sm-12"},[s("q-date",{attrs:{"today-btn":""},on:{input:function(t){return e.filterGrid()}},model:{value:e.saleDatev1,callback:function(t){e.saleDatev1=t},expression:"saleDatev1"}})],1)])],1)],1),s("div",{staticClass:"col-md-2"},[s("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",mask:"date",label:"Hasta"},model:{value:e.saleDatev2,callback:function(t){e.saleDatev2=t},expression:"saleDatev2"}},[s("q-popup-proxy",{ref:"date",attrs:{"transition-show":"scale","transition-hide":"scale"}},[s("div",{staticClass:"col-sm-12"},[s("q-date",{attrs:{"today-btn":""},on:{input:function(t){return e.filterGrid()}},model:{value:e.saleDatev2,callback:function(t){e.saleDatev2=t},expression:"saleDatev2"}})],1)])],1)],1),s("div",{staticClass:"col-sm-2"},[s("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",options:e.sellerOptionsG,"emit-value":"","map-options":"",label:"Vendedor",disable:e.isnotAdmin},on:{input:function(t){return e.filterGrid()}},model:{value:e.sellerG,callback:function(t){e.sellerG=t},expression:"sellerG"}})],1),s("div",{staticClass:"col-sm-2"},[s("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",options:e.filteredCustomerOptions2,label:"Cliente","emit-value":"","map-options":""},on:{filter:e.filtrarClientes2,input:function(t){return e.filterGrid()}},model:{value:e.customerG,callback:function(t){e.customerG=t},expression:"customerG"}})],1),s("div",{staticClass:"col-sm-2"},[s("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",options:e.$store.getters["users/roles"].includes(2)?[{label:"TODOS",value:"TODOS"},{label:"AUTORIZADO",value:"AUTORIZADO"},{label:"PARCIAL",value:"PARCIAL"},{label:"REMISIONADO",value:"REMISIONADO"},{label:"ENTREGADO",value:"ENVIADO"}]:[{label:"TODOS",value:"TODOS"},{label:"NUEVO",value:"NUEVO"},{label:"SOLICITADO",value:"SOLICITADO"},{label:"AUTORIZADO",value:"AUTORIZADO"},{label:"PARCIAL",value:"PARCIAL"},{label:"REMISIONADO",value:"REMISIONADO"},{label:"ENTREGADO",value:"ENVIADO"}],"emit-value":"","map-options":"",label:"Estatus"},on:{input:function(t){return e.filterGrid()}},model:{value:e.status,callback:function(t){e.status=t},expression:"status"}})],1)]),s("div",{staticStyle:{"padding-top":"20px"}},[s("q-table",{attrs:{flat:"",bordered:"",data:e.data,columns:e.columns,"row-key":"code",pagination:e.pagination,filter:e.filter},on:{"update:pagination":function(t){e.pagination=t}},scopedSlots:e._u([{key:"top",fn:function(){return[s("div",{staticStyle:{width:"100%"}},[s("q-input",{attrs:{dense:"",debounce:"300",placeholder:"Buscar"},on:{input:function(t){e.filter=t.toUpperCase()}},scopedSlots:e._u([{key:"append",fn:function(){return[s("q-icon",{attrs:{name:"search"}})]},proxy:!0}]),model:{value:e.filter,callback:function(t){e.filter=t},expression:"filter"}})],1)]},proxy:!0},{key:"body",fn:function(t){return[s("q-tr",{attrs:{props:t}},[s("q-td",{key:"id",staticStyle:{width:"5%","text-align":"center"},attrs:{props:t}},[s("label",{staticClass:"text-primary",staticStyle:{"text-decoration":"underline",cursor:"pointer"},on:{click:function(s){return e.openActions(t.row.id,t.row.status)}}},[e._v(e._s(t.row.id))])]),s("q-td",{key:"old_folio",staticStyle:{width:"10%"},attrs:{props:t}},[e._v(e._s(t.row.old_folio))]),s("q-td",{key:"status",staticStyle:{width:"10%","text-align":"center"},attrs:{props:t}},[s("q-chip",{attrs:{square:"",dense:"",color:"SOLICITADO"==t.row.status?"yellow-8":"NUEVO"==t.row.status?"blue-7":"AUTORIZADO"==t.row.status?"green":"PARCIAL"==t.row.status?"red-4":"ENTREGADO"==t.row.status?"purple-6":"CANCELADO"==t.row.status?"red":"red-4","text-color":"white"}},[e._v("\n                      "+e._s(t.row.status)+"\n                    ")])],1),s("q-td",{key:"date",staticStyle:{width:"10%"},attrs:{props:t}},[e._v(e._s(t.row.date))]),s("q-td",{key:"branchofficeorigin",staticStyle:{width:"10%"},attrs:{props:t}},[e._v(e._s(t.row.branchofficeorigin))]),s("q-td",{key:"invoices",staticStyle:{width:"5%","text-align":"center"},attrs:{props:t}},e._l(t.row.array_invoices,(function(t){return s("div",{key:t.id,staticStyle:{display:"inline"}},[s("label",{staticStyle:{"text-decoration":"underline black",cursor:"pointer","padding-left":"10px"},on:{click:function(s){return e.openActionRemision(t)}}},[e._v(e._s(t))])])})),0),s("q-td",{key:"customer",staticStyle:{width:"20%"},attrs:{props:t}},[s("label",{staticStyle:{"text-decoration":"underline black",cursor:"pointer"},on:{click:function(s){return e.openModalclients(t.row.id_client,t.row.branchofficedestiny)}}},[e._v(e._s(t.row.customer_name))])]),s("q-td",{key:"total",staticStyle:{width:"10%"},attrs:{props:t}},[e._v(e._s(""+e.currencyFormatter.format(Number(t.row.montoinbulk))))]),s("q-td",{key:"user_name",staticStyle:{width:"10%"},attrs:{props:t}},[e._v(e._s(t.row.user_name))])],1)]}}])})],1)])])]),s("q-dialog",{attrs:{persistent:""},model:{value:e.modalPedido,callback:function(t){e.modalPedido=t},expression:"modalPedido"}},[s("q-card",{staticStyle:{"min-width":"40%"}},[s("q-card-section",{staticClass:"bg-primary"},[s("div",{staticClass:"row"},[s("div",{staticClass:"col-sm-11 text-h6",staticStyle:{color:"white"}},[e._v("CREAR PEDIDO")]),s("div",{staticClass:"col-sm-1 pull-right"},[s("q-btn",{directives:[{name:"close-popup",rawName:"v-close-popup"}],attrs:{color:"white",flat:"",round:"",dense:"",icon:"close"}})],1)])]),s("q-separator"),s("q-card-section",{staticClass:"scroll",staticStyle:{"max-height":"50vh"}},[s("div",{staticClass:"text-overline",staticStyle:{"font-size":"16px"}},[e._v("Tipo de Pedido")]),s("div",{staticClass:"row bg-white border-panel"},[s("div",{staticClass:"col q-pa-md"},[s("div",{staticClass:"row q-col-gutter-xs q-col-gutter-md"},[s("div",{staticClass:"col-xs-12 col-md-6"},[s("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.type_order.$error,options:[{label:"MOSTRADOR",value:1},{label:"CONSIGNACIÓN",value:2},{label:"MAYOREO",value:3}],"use-input":"",label:"Tipo","map-options":""},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"person"}})]},proxy:!0}]),model:{value:e.type_order,callback:function(t){e.type_order=t},expression:"type_order"}})],1)])])]),s("div",{staticClass:"text-overline",staticStyle:{"font-size":"16px"}},[e._v("Datos del cliente")]),s("div",{staticClass:"row bg-white border-panel"},[s("div",{staticClass:"col q-pa-md"},[s("div",{staticClass:"row q-col-gutter-xs q-col-gutter-md"},[s("div",{staticClass:"col-xs-12 col-md-6"},[s("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.customer.$error,options:e.filteredCustomerOptions,"use-input":"",label:"Cliente","emit-value":"","map-options":"","input-debounce":"0",rules:e.customerRules},on:{filter:e.filtrarClientes,input:function(t){return e.getOfficebyClient()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"person"}})]},proxy:!0}]),model:{value:e.customer,callback:function(t){e.customer=t},expression:"customer"}})],1),s("div",{staticClass:"col-xs-12 col-md-6"},[s("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.clientofficeDestiny.$error,options:e.officeClientsOptions,"use-input":"",label:"Sucursal Cliente","map-options":"",rules:e.officeClientRules},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"person"}})]},proxy:!0}]),model:{value:e.clientofficeDestiny,callback:function(t){e.clientofficeDestiny=t},expression:"clientofficeDestiny"}})],1)])])]),s("div",{staticClass:"text-overline",staticStyle:{"font-size":"16px"}},[e._v("Origen de venta")]),s("div",{staticClass:"row bg-white border-panel"},[s("div",{staticClass:"col q-pa-md"},[s("div",{staticClass:"row q-col-gutter-xs q-col-gutter-md"},[s("div",{staticClass:"col-xs-12 col-md-6"},[s("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.branch.$error,options:e.branchOffices,label:"Sucursal de envío","emit-value":"","map-options":"",rules:e.branchRules},on:{input:function(t){return e.searchStorage(e.branch)}},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"person"}})]},proxy:!0}]),model:{value:e.branch,callback:function(t){e.branch=t},expression:"branch"}})],1),s("div",{staticClass:"col-xs-12 col-md-6"},[s("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",options:e.storageOptions,label:"Almacén",rules:e.storageShoppingRules,error:e.$v.storageShopping.$error,disable:e.isnotAdmin},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"fas fa-warehouse"}})]},proxy:!0}]),model:{value:e.storageShopping,callback:function(t){e.storageShopping=t},expression:"storageShopping"}})],1),s("div",{staticClass:"col-xs-12 col-md-6"},[s("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",options:e.sellerOptions,label:"Vendedor",rules:e.sellerRules,error:e.$v.seller.$error,disable:e.isnotAdmin},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"person"}})]},proxy:!0}]),model:{value:e.seller,callback:function(t){e.seller=t},expression:"seller"}})],1),s("div",{staticClass:"col-xs-12 col-md-6"},[s("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.taxInvoice.$error,options:[{label:"FACTURA",value:0},{label:"REMISIÓN",value:1}],rules:e.taxInvoicesRules,label:"¿Desea factura o remisión?"},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"fas fa-cash-register"}})]},proxy:!0}]),model:{value:e.taxInvoice,callback:function(t){e.taxInvoice=t},expression:"taxInvoice"}})],1)])])])]),s("q-separator"),s("q-card-actions",{staticStyle:{"vertical-align":"bottom"},attrs:{align:"right"}},[s("q-btn",{staticStyle:{"background-color":"#21ba45"},attrs:{flat:"",label:"Crear",color:"white"},on:{click:function(t){return e.createShoppingCart()}}})],1)],1)],1),s("q-dialog",{attrs:{persistent:""},model:{value:e.modalClient,callback:function(t){e.modalClient=t},expression:"modalClient"}},[s("q-card",{staticStyle:{"min-width":"50%"}},[s("q-card-section",{staticClass:"bg-primary"},[s("div",{staticClass:"row"},[s("div",{staticClass:"col-sm-11 text-h6",staticStyle:{color:"white"}},[e._v("DATOS DEL CLIENTE")]),s("div",{staticClass:"col-sm-1 pull-right"},[s("q-btn",{directives:[{name:"close-popup",rawName:"v-close-popup"}],attrs:{color:"white",flat:"",round:"",dense:"",icon:"close"}})],1)])]),s("q-separator"),s("q-card-section",{staticClass:"scroll",staticStyle:{"max-height":"50vh"}},[s("div",{staticClass:"row bg-white"},[s("div",{staticClass:"col q-pa-md"},[e._v("\n              Cliente\n              "),s("div",{staticClass:"row q-col-gutter-xs q-col-gutter-md"},[s("div",{staticClass:"col-xs-12 col-md-8"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",label:"Cliente",disable:""},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"fas fa-shopping-cart"}})]},proxy:!0}]),model:{value:e.dataClient.fields.client,callback:function(t){e.$set(e.dataClient.fields,"client",t)},expression:"dataClient.fields.client"}})],1),s("div",{staticClass:"col-xs-12 col-md-4"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",label:"Telefono de contacto",disable:""},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"fas fa-shopping-cart"}})]},proxy:!0}]),model:{value:e.dataClient.fields.phone,callback:function(t){e.$set(e.dataClient.fields,"phone",t)},expression:"dataClient.fields.phone"}})],1),s("div",{staticClass:"col-xs-12 col-md-8"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",label:"Direccion",disable:"",autogrow:""},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"fas fa-shopping-cart"}})]},proxy:!0}]),model:{value:e.dataClient.fields.address,callback:function(t){e.$set(e.dataClient.fields,"address",t)},expression:"dataClient.fields.address"}})],1),s("div",{staticClass:"col-xs-12 col-md-4"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",label:"Plazo",disable:""},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"fas fa-shopping-cart"}})]},proxy:!0}]),model:{value:e.dataClient.fields.term,callback:function(t){e.$set(e.dataClient.fields,"term",t)},expression:"dataClient.fields.term"}})],1)]),s("br"),e._v("\n                  Sucursal\n                "),s("div",{staticClass:"row q-col-gutter-xs q-col-gutter-md"},[s("div",{staticClass:"col-xs-12 col-md-8"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",label:"Nombre de la sucursal",disable:""},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"fas fa-building"}})]},proxy:!0}]),model:{value:e.dataClient.fields.branchOffice,callback:function(t){e.$set(e.dataClient.fields,"branchOffice",t)},expression:"dataClient.fields.branchOffice"}})],1),s("div",{staticClass:"col-xs-12 col-md-4"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",label:"Telefono",disable:""},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"fas fa-phone"}})]},proxy:!0}]),model:{value:e.dataClient.fields.branch_phone,callback:function(t){e.$set(e.dataClient.fields,"branch_phone",t)},expression:"dataClient.fields.branch_phone"}})],1),s("div",{staticClass:"col-xs-12 col-md-6"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",label:"Calle",disable:"",autogrow:""},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"fas fa-road"}})]},proxy:!0}]),model:{value:e.dataClient.fields.branchAddress,callback:function(t){e.$set(e.dataClient.fields,"branchAddress",t)},expression:"dataClient.fields.branchAddress"}})],1),s("div",{staticClass:"col-xs-12 col-md-3"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",label:"N. Exterior",disable:""},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"fas fa-hashtag"}})]},proxy:!0}]),model:{value:e.dataClient.fields.outdoor,callback:function(t){e.$set(e.dataClient.fields,"outdoor",t)},expression:"dataClient.fields.outdoor"}})],1),s("div",{staticClass:"col-xs-12 col-md-3"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",label:"N. Interior",disable:""},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"fas fa-hashtag"}})]},proxy:!0}]),model:{value:e.dataClient.fields.indoor,callback:function(t){e.$set(e.dataClient.fields,"indoor",t)},expression:"dataClient.fields.indoor"}})],1),s("div",{staticClass:"col-xs-12 col-md-5"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",label:"Colonia",disable:"",autogrow:""},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"fas fa-city"}})]},proxy:!0}]),model:{value:e.dataClient.fields.colony,callback:function(t){e.$set(e.dataClient.fields,"colony",t)},expression:"dataClient.fields.colony"}})],1),s("div",{staticClass:"col-xs-12 col-md-4"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",label:"Municipio",disable:""},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"fas fa-city"}})]},proxy:!0}]),model:{value:e.dataClient.fields.municipality,callback:function(t){e.$set(e.dataClient.fields,"municipality",t)},expression:"dataClient.fields.municipality"}})],1),s("div",{staticClass:"col-xs-12 col-md-3"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",label:"CP",disable:""},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"fas fa-mail-bulk"}})]},proxy:!0}]),model:{value:e.dataClient.fields.CP,callback:function(t){e.$set(e.dataClient.fields,"CP",t)},expression:"dataClient.fields.CP"}})],1)])])])]),s("q-separator")],1)],1),s("q-dialog",{attrs:{persistent:""},model:{value:e.modalClientBranch,callback:function(t){e.modalClientBranch=t},expression:"modalClientBranch"}},[s("q-card",{staticStyle:{"min-width":"50%"}},[s("q-card-section",{staticClass:"bg-primary"},[s("div",{staticClass:"row"},[s("div",{staticClass:"col-sm-11 text-h6",staticStyle:{color:"white"}},[e._v("DATOS DE LA SUCURSAL DEL CLIENTE")]),s("div",{staticClass:"col-sm-1 pull-right"},[s("q-btn",{directives:[{name:"close-popup",rawName:"v-close-popup"}],attrs:{color:"white",flat:"",round:"",dense:"",icon:"close"}})],1)])]),s("q-separator"),s("q-card-section",{staticClass:"scroll",staticStyle:{"max-height":"50vh"}},[s("div",{staticClass:"row bg-white border-panel"},[s("div",{staticClass:"col q-pa-md"},[s("div",{staticClass:"row q-col-gutter-xs q-col-gutter-md"},[s("div",{staticClass:"col-xs-12 col-md-8"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",label:"Nombre de la sucursal",disable:""},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"fas fa-building"}})]},proxy:!0}]),model:{value:e.dataClientBranch.fields.branchOffice,callback:function(t){e.$set(e.dataClientBranch.fields,"branchOffice",t)},expression:"dataClientBranch.fields.branchOffice"}})],1),s("div",{staticClass:"col-xs-12 col-md-4"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",label:"Telefono",disable:""},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"fas fa-phone"}})]},proxy:!0}]),model:{value:e.dataClientBranch.fields.phone,callback:function(t){e.$set(e.dataClientBranch.fields,"phone",t)},expression:"dataClientBranch.fields.phone"}})],1),s("div",{staticClass:"col-xs-12 col-md-6"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",label:"Calle",disable:"",autogrow:""},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"fas fa-road"}})]},proxy:!0}]),model:{value:e.dataClientBranch.fields.branchAddress,callback:function(t){e.$set(e.dataClientBranch.fields,"branchAddress",t)},expression:"dataClientBranch.fields.branchAddress"}})],1),s("div",{staticClass:"col-xs-12 col-md-3"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",label:"N. Exterior",disable:""},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"fas fa-hashtag"}})]},proxy:!0}]),model:{value:e.dataClientBranch.fields.outdoor,callback:function(t){e.$set(e.dataClientBranch.fields,"outdoor",t)},expression:"dataClientBranch.fields.outdoor"}})],1),s("div",{staticClass:"col-xs-12 col-md-3"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",label:"N. Interior",disable:""},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"fas fa-hashtag"}})]},proxy:!0}]),model:{value:e.dataClientBranch.fields.indoor,callback:function(t){e.$set(e.dataClientBranch.fields,"indoor",t)},expression:"dataClientBranch.fields.indoor"}})],1),s("div",{staticClass:"col-xs-12 col-md-5"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",label:"Colonia",disable:"",autogrow:""},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"fas fa-city"}})]},proxy:!0}]),model:{value:e.dataClientBranch.fields.colony,callback:function(t){e.$set(e.dataClientBranch.fields,"colony",t)},expression:"dataClientBranch.fields.colony"}})],1),s("div",{staticClass:"col-xs-12 col-md-4"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",label:"Municipio",disable:""},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"fas fa-city"}})]},proxy:!0}]),model:{value:e.dataClientBranch.fields.municipality,callback:function(t){e.$set(e.dataClientBranch.fields,"municipality",t)},expression:"dataClientBranch.fields.municipality"}})],1),s("div",{staticClass:"col-xs-12 col-md-3"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",label:"CP",disable:""},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"fas fa-mail-bulk"}})]},proxy:!0}]),model:{value:e.dataClientBranch.fields.CP,callback:function(t){e.$set(e.dataClientBranch.fields,"CP",t)},expression:"dataClientBranch.fields.CP"}})],1)])])])]),s("q-separator")],1)],1)],1)},l=[],o=(s("a9e3"),s("caad"),s("2532"),s("4de4"),s("d3b7"),s("b0c0"),s("ac1f"),s("5319"),s("99af"),s("aabb")),r=s("b5ae"),i=r.required,n={name:"IndexShoppingCarts",validations:{customer:{required:i},branch:{required:i},seller:{required:i},clientofficeDestiny:{required:i},taxInvoice:{required:i},storageShopping:{required:i},type_order:{required:i}},data:function(){return{currencyFormatter:new Intl.NumberFormat("en-US",{style:"currency",currency:"USD"}),icons:["fas fa-star","fas fa-anchor","fas fa-check","fas fa-file-alt","fas fa-thumbs-up"],ratingModel:5,formatter:new Intl.NumberFormat("en-US"),role:null,taxInvoice:null,modalPedido:!1,modalClient:!1,modalClientBranch:!1,type_order:1,customer:null,saleDatev1:null,saleDatev2:null,clientofficeDestiny:null,customerG:"TODOS",branch:null,seller:null,sellerG:"TODOS",status:"TODOS",storageShopping:null,storageOptions:[],customerOptions:[],customerOptionsG:[],officeClientsOptions:[],sellerOptions:[],sellerOptionsG:[],branchOffices:[],filteredCustomerOptions:[],filteredCustomerOptions2:[],dataClient:{fields:{id:null,client:null,address:null,phone:null,term:null,branchOffice:null,branchAddress:null,indoor:null,outdoor:null,colony:null,municipality:null,CP:null,branch_phone:null}},dataClientBranch:{fields:{id:null,branchOffice:null,branchAddress:null,indoor:null,outdoor:null,colony:null,municipality:null,CP:null,phone:null}},pagination:{sortBy:"id",descending:!0,rowsPerPage:25},columns:[{name:"id",align:"right",label:"# PEDIDO",field:"id",style:"width: 5%",sortable:!0,sort:function(e,t){return Number(e,10)-Number(t,10)}},{name:"old_folio",align:"center",label:"FOLIO ANTERIOR",field:"old_folio",style:"width: 10%",sortable:!0},{name:"status",align:"center",label:"STATUS",field:"status",style:"width: 10%",sortable:!0},{name:"date",align:"center",label:"FECHA",field:"date",style:"width: 10%",sortable:!0},{name:"branchofficeorigin",align:"center",label:"SUCURSAL",field:"branchofficeorigin",style:"width: 10%",sortable:!0},{name:"invoices",align:"center",label:"REMISIÓN",field:"invoices",style:"width: 5%",sortable:!0,sort:function(e,t){return Number(e,10)-Number(t,10)}},{name:"customer",align:"center",label:"CLIENTE",field:"customer",style:"width: 20%",sortable:!0},{name:"total",align:"center",label:"MONTO",field:"total",style:"width: 10%",sortable:!0},{name:"user_name",align:"center",label:"VENDEDOR",field:"user_name",style:"width: 10%",sortable:!0}],data:[],filter:"",sellerFlag:[]}},computed:{isnotAdmin:function(){var e=!1;return this.$store.getters["users/roles"].includes(1)||this.$store.getters["users/roles"].includes(4)||this.$store.getters["users/roles"].includes(3)||(e=!0),e},storageShoppingRules:function(){var e=this;return[function(t){return e.$v.storageShopping.required||"El campo Almacén es requerido."}]},customerRules:function(e){var t=this;return[function(e){return t.$v.customer.required||"El campo Nombre es requerido."}]},officeClientRules:function(e){var t=this;return[function(e){return t.$v.clientofficeDestiny.required||"El la sucursal del cliente es requerida."}]},branchRules:function(e){var t=this;return[function(e){return t.$v.branch.required||"El campo de Sucursal es requerido."}]},sellerRules:function(e){var t=this;return[function(e){return t.$v.seller.required||"El campo de Vendedor es requerido."}]},taxInvoicesRules:function(e){var t=this;return[function(e){return t.$v.taxInvoice.required||"El campo de Factura o Remisión es requerido."}]},haspermissionv1:function(){var e=!1;return(this.$store.getters["users/roles"].includes(1)||this.$store.getters["users/roles"].includes(20)||this.$store.getters["users/roles"].includes(3)||this.$store.getters["users/roles"].includes(4)||this.$store.getters["users/roles"].includes(7)||this.$store.getters["users/roles"].includes(25)||this.$store.getters["users/roles"].includes(17))&&(e=!0),e},haspermissionv2:function(){var e=!1;return(this.$store.getters["users/roles"].includes(1)||this.$store.getters["users/roles"].includes(3)||this.$store.getters["users/roles"].includes(4)||this.$store.getters["users/roles"].includes(20)||this.$store.getters["users/roles"].includes(12))&&(e=!0),e}},beforeCreate:function(){this.$store.getters["users/roles"].includes(1)||this.$store.getters["users/roles"].includes(3)||this.$store.getters["users/roles"].includes(4)||this.$store.getters["users/roles"].includes(2)||this.$store.getters["users/roles"].includes(23)||this.$store.getters["users/roles"].includes(20)||this.$store.getters["users/roles"].includes(17)||this.$router.push("/")},mounted:function(){this.getClients(),this.getSellers(),this.fetchFromServer()},methods:{searchStorage:function(e){var t=this;o["a"].get("/storages/getStoragesOfBranch/".concat(e)).then((function(e){var s=e.data;s.result&&(t.storageOptions=s.storage,console.log(t.storageOptions))})).catch()},getClients:function(){var e=this,t=this.$store.getters["users/id"];o["a"].get("/customers/getCustomersBySeller/".concat(t)).then((function(t){var s=t.data;e.customerOptions=s.options,e.customerOptionsG=e.customerOptions,e.customerOptionsG.push({label:"TODOS",value:"TODOS"})}))},getSellers:function(){var e=this;o["a"].get("/users/getSeller").then((function(t){var s=t.data;e.sellerOptions=s.options,e.sellerOptionsG=s.options2,e.sellerOptionsG.push({label:"TODOS",value:"TODOS"}),e.$q.loading.hide()}))},fetchFromServer:function(){var e=this;this.role=this.$store.getters["users/roles"][0],console.log(this.$store.getters),this.$q.loading.show(),this.data=[];var t=[];t.customer=this.customerG,t.status=this.status,t.seller=this.sellerG,t.sellerId=this.$store.getters["users/id"],t.saleDatev1=this.saleDatev1,t.saleDatev2=this.saleDatev2,t.sellerId=this.$store.getters["users/id"],o["a"].post("/shopping-carts/getGrid",t).then((function(t){var s=t.data;console.log(s),e.data=s.shoppingCarts,console.log(e.data),e.$q.loading.hide()}))},getOfficebyClient:function(){var e=this;this.officeClientsOptions=[];var t=[];t.customer=this.customer,o["a"].post("/customers/officeoptions",t).then((function(t){var s=t.data;console.log(s.options),s.result&&(e.officeClientsOptions=s.options,0!==s.options.length&&(console.log(s.options[0].label),e.clientofficeDestiny={label:s.options[0].label,value:s.options[0].value}))}))},openSelectedRow:function(e,t){"NUEVO"===t?this.$router.push("/shopping-carts/".concat(e)):this.$router.push("/shopping-carts/orders/".concat(e))},createShoppingCart:function(){var e=this;if(this.$v.customer.$reset(),this.$v.customer.$touch(),this.$v.branch.$reset(),this.$v.branch.$touch(),this.$v.seller.$reset(),this.$v.seller.$touch(),this.$v.clientofficeDestiny.$reset(),this.$v.clientofficeDestiny.$touch(),this.$v.taxInvoice.$reset(),this.$v.taxInvoice.$touch(),this.$v.storageShopping.$reset(),this.$v.storageShopping.$touch(),this.$v.type_order.$reset(),this.$v.type_order.$touch(),this.$v.customer.$error||this.$v.branch.$error||this.$v.seller.$error||this.$v.clientofficeDestiny.$error||this.$v.taxInvoice.$error||this.$v.storageShopping.$error||this.$v.type_order.$error)return this.$q.dialog({title:"Error",message:"Por favor, verifique las validaciones.",persistent:!0}),!1;this.$q.loading.show();var t=[];t.customerId=this.customer,t.branchOfficeId=this.branch,t.sellerId=this.seller.value,t.officedestiny=this.clientofficeDestiny.value,t.taxInvoice=this.taxInvoice.value,t.storage_id=this.storageShopping.value,t.type_order=this.type_order,o["a"].post("/shopping-carts",t).then((function(t){var s=t.data;e.$q.notify({message:s.message.content,position:"top",color:s.result?"positive":"warning"}),s.result?(e.$q.loading.hide(),e.$router.push("/shopping-carts-sales-point/".concat(s.shoppingCart.id))):e.$q.loading.hide()}))},openModal:function(){var e=this;this.modalPedido=!0,this.seller={label:this.$store.getters["users/nickname"],value:this.$store.getters["users/id"]},o["a"].get("/branch-offices/options").then((function(t){var s=t.data;e.branchOffices=s.options,e.$q.loading.hide()}))},filtrarClientes:function(e,t,s){var a=this;t((function(){var t=e.toLowerCase();a.filteredCustomerOptions=a.customerOptions.filter((function(e){return e.label.toLowerCase().indexOf(t)>-1&&"TODOS"!==e.label}))}))},filtrarClientes2:function(e,t,s){var a=this;t((function(){var t=e.toLowerCase();a.filteredCustomerOptions2=a.customerOptionsG.filter((function(e){return e.label.toLowerCase().indexOf(t)>-1}))}))},filterGrid:function(){var e=this;this.data=[];var t=[];t.customer=this.customerG,t.status=this.status,t.seller=this.sellerG,t.sellerId=this.$store.getters["users/id"],t.saleDatev1=this.saleDatev1,t.saleDatev2=this.saleDatev2,console.log(t),o["a"].post("/shopping-carts/getGrid",t).then((function(t){var s=t.data;s.result&&(e.data=s.shoppingCarts)}))},openModalclients:function(e,t){var s=this,a=[];console.log(a),a.id=e,a.branch=t,o["a"].post("/customers/getDataClient",a).then((function(e){var t=e.data;console.log(t),t.result&&(s.modalClient=!0,s.dataClient.fields.id=t.data[0].id,s.dataClient.fields.branch=t.data[0].branch,s.dataClient.fields.client=t.data[0].name_client,s.dataClient.fields.address=t.data[0].address,s.dataClient.fields.phone=t.data[0].contact_phone,s.dataClient.fields.term=t.data[0].term,s.dataClient.fields.branchOffice=t.data[0].branch_name,s.dataClient.fields.branchAddress=t.data[0].branch_street,s.dataClient.fields.outdoor=t.data[0].branch_outdoor_number,s.dataClient.fields.indoor=t.data[0].branch_int_number,s.dataClient.fields.colony=t.data[0].branch_colony,s.dataClient.fields.municipality=t.data[0].branch_municipality,s.dataClient.fields.CP=t.data[0].branch_zip_code,s.dataClient.fields.branch_phone=t.data[0].branch_phone_number)}))},openModalclientsBranch:function(e){var t=this,s=[];s.id=e,o["a"].post("/customers/getDataClientBranch",s).then((function(e){var s=e.data;console.log(s),s.result&&(t.modalClientBranch=!0,t.dataClientBranch.fields.id=s.data[0].id,t.dataClientBranch.fields.branchOffice=s.data[0].name,t.dataClientBranch.fields.branchAddress=s.data[0].street,t.dataClientBranch.fields.outdoor=s.data[0].outdoor_number,t.dataClientBranch.fields.indoor=s.data[0].int_number,t.dataClientBranch.fields.colony=s.data[0].colony,t.dataClientBranch.fields.municipality=s.data[0].municipality,t.dataClientBranch.fields.CP=s.data[0].zip_code,t.dataClientBranch.fields.phone=s.data[0].phone_number)}))},openActions:function(e,t){"NUEVO"===t?this.$router.push("/shopping-carts-sales-point/".concat(e)):this.$router.push("/shopping-carts/orders/".concat(e))},openActionRemision:function(e){this.$router.push("/storage-exits/".concat(e))},generatePDF:function(){var e=[];if(e.customer=this.customerG,e.status=this.status,e.seller=this.sellerG,this.saleDatev1){e.saleDatev1=this.saleDatev1;while(e.saleDatev1.includes("/"))e.saleDatev1=e.saleDatev1.replace("/","-")}else e.saleDatev1=null;if(this.saleDatev2){e.saleDatev2=this.saleDatev2;while(e.saleDatev2.includes("/"))e.saleDatev2=e.saleDatev2.replace("/","-")}else e.saleDatev2=null;var t=this.$store.getters["users/id"],s="https://api_alpez.wasp.mx/"+"shopping-carts/getPdfFromShoppingCarts/".concat(e.customer,"/").concat(e.status,"/").concat(e.seller,"/").concat(e.saleDatev1,"/").concat(e.saleDatev2,"/").concat(t);window.open(s,"_blank")},generateCSV:function(){var e=[];if(e.customer=this.customerG,e.status=this.status,e.seller=this.sellerG,this.saleDatev1){e.saleDatev1=this.saleDatev1;while(e.saleDatev1.includes("/"))e.saleDatev1=e.saleDatev1.replace("/","-")}else e.saleDatev1=null;if(this.saleDatev2){e.saleDatev2=this.saleDatev2;while(e.saleDatev2.includes("/"))e.saleDatev2=e.saleDatev2.replace("/","-")}else e.saleDatev2=null;var t=this.$store.getters["users/id"],s="https://api_alpez.wasp.mx/"+"shopping-carts/getCSVFromShoppingCarts/".concat(e.customer,"/").concat(e.status,"/").concat(e.seller,"/").concat(e.saleDatev1,"/").concat(e.saleDatev2,"/").concat(t);window.open(s,"_blank")}}},c=n,d=s("2877"),u=s("9989"),p=s("ead5"),f=s("079e"),h=s("9c40"),v=s("05c0"),m=s("ddd8"),b=s("7cbe"),C=s("52ee"),g=s("eaac"),y=s("27f9"),q=s("0016"),x=s("bd08"),S=s("db86"),k=s("b047"),$=s("daf4"),O=s("24e8"),D=s("f09f"),_=s("a370"),w=s("eb85"),A=s("4b7e"),I=s("7f67"),E=s("eebe"),R=s.n(E),B=Object(d["a"])(c,a,l,!1,null,null,null);t["default"]=B.exports;R()(B,"components",{QPage:u["a"],QBreadcrumbs:p["a"],QBreadcrumbsEl:f["a"],QBtn:h["a"],QTooltip:v["a"],QSelect:m["a"],QPopupProxy:b["a"],QDate:C["a"],QTable:g["a"],QInput:y["a"],QIcon:q["a"],QTr:x["a"],QTd:S["a"],QChip:k["a"],QRating:$["a"],QDialog:O["a"],QCard:D["a"],QCardSection:_["a"],QSeparator:w["a"],QCardActions:A["a"]}),R()(B,"directives",{ClosePopup:I["a"]})}}]);
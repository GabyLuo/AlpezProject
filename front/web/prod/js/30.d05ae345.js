(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[30],{a68f:function(t,e,a){"use strict";a.r(e);var r=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("q-page",[a("div",{staticClass:"q-pa-sm panel-header"},[a("div",{staticClass:"row"},[a("div",{staticClass:"col-sm-6"},[a("span",{staticClass:"q-ml-md grey-8 fs28 page-title"},[t._v("Pedido autorizado "+t._s(t.$route.params.id))])]),a("div",{staticClass:"col-sm-6"},[a("div",{staticClass:"q-pa-md q-gutter-sm"},[a("q-breadcrumbs",{attrs:{align:"right"}},[a("q-breadcrumbs-el",{attrs:{label:"",icon:"home",to:"/"}}),a("q-breadcrumbs-el",{attrs:{label:"Pedidos autorizados",to:"/approved-orders"}}),a("q-breadcrumbs-el",{attrs:{label:"Detalles"}})],1)],1)])])]),a("div",{staticClass:"q-pa-md bg-grey-3"},[a("div",{staticClass:"row bg-white border-panel"},[a("div",{staticClass:"col q-pa-md"},[a("div",{staticClass:"row q-col-gutter-xs"},[a("div",{staticClass:"col-xs-12 col-md-3"},[a("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",label:"Cliente",disable:""},scopedSlots:t._u([{key:"prepend",fn:function(){return[a("q-icon",{attrs:{name:"fas fa-shopping-cart"}})]},proxy:!0}]),model:{value:t.shoppingCart.fields.customer_name,callback:function(e){t.$set(t.shoppingCart.fields,"customer_name",e)},expression:"shoppingCart.fields.customer_name"}})],1),a("div",{staticClass:"col-xs-12 col-md-2"},[a("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",label:"Nombre usuario",disable:""},scopedSlots:t._u([{key:"prepend",fn:function(){return[a("q-icon",{attrs:{name:"fas fa-user"}})]},proxy:!0}]),model:{value:t.shoppingCart.fields.user_name,callback:function(e){t.$set(t.shoppingCart.fields,"user_name",e)},expression:"shoppingCart.fields.user_name"}})],1),a("div",{staticClass:"col-xs-12 col-md-2"},[a("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",label:"Estatus",disable:""},scopedSlots:t._u([{key:"prepend",fn:function(){return[a("q-icon",{attrs:{name:"fas fa-battery-full"}})]},proxy:!0}]),model:{value:t.shoppingCart.fields.status,callback:function(e){t.$set(t.shoppingCart.fields,"status",e)},expression:"shoppingCart.fields.status"}})],1),a("div",{staticClass:"col-xs-12 col-md-2"},[a("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",label:"Precio lista",disable:""},scopedSlots:t._u([{key:"prepend",fn:function(){return[a("q-icon",{attrs:{name:"fas fa-list"}})]},proxy:!0}]),model:{value:t.shoppingCart.fields.price_list,callback:function(e){t.$set(t.shoppingCart.fields,"price_list",e)},expression:"shoppingCart.fields.price_list"}})],1),a("div",{staticClass:"col-xs-12 col-md-2"},[a("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",label:"Importe total",disable:""},scopedSlots:t._u([{key:"prepend",fn:function(){return[a("q-icon",{attrs:{name:"fas fa-dollar-sign"}})]},proxy:!0}]),model:{value:t.totalPrice,callback:function(e){t.totalPrice=e},expression:"totalPrice"}})],1),a("div",{staticClass:"col-xs-12 col-md-1 pull-right"},[a("q-btn",{attrs:{color:"positive",icon:"fas fa-shopping-cart",disabled:t.canGenerateInvoice,label:"Remisionar"},on:{click:function(e){return t.openGenerateInvoiceModal()}}})],1),a("div",{staticClass:"col-xs-12"},[a("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",label:"Comentarios",disable:""},scopedSlots:t._u([{key:"prepend",fn:function(){return[a("q-icon",{attrs:{name:"chat"}})]},proxy:!0}]),model:{value:t.shoppingCart.fields.comments,callback:function(e){t.$set(t.shoppingCart.fields,"comments",e)},expression:"shoppingCart.fields.comments"}})],1)]),a("div",{staticClass:"q-pa-md"},[t.shoppingCartBaleDetails.length>0?a("q-table",{attrs:{title:"Fibra",dense:"","hide-bottom":"",data:t.shoppingCartBaleDetails,columns:t.baleColumns,"row-key":"product_name"},scopedSlots:t._u([{key:"body",fn:function(e){return[a("q-tr",{attrs:{props:e}},[a("q-td",{key:"product_name",staticStyle:{width:"20%"},attrs:{props:e}},[t._v(t._s(e.row.product_name))]),a("q-td",{key:"line_name",staticStyle:{width:"10%"},attrs:{props:e}},[t._v(t._s(e.row.line_name))]),a("q-td",{key:"category_name",staticStyle:{width:"10%"},attrs:{props:e}},[t._v(t._s(e.row.category_name))]),a("q-td",{key:"qty",staticStyle:{width:"15%"},attrs:{props:e}},[t._v(t._s(t.formatter.format(e.row.qty)+" KG."))]),a("q-td",{key:"unit_price",staticStyle:{width:"10%"},attrs:{props:e}},[t._v(t._s(t.currencyFormatter.format(e.row.unit_price)+" "))]),a("q-td",{key:"amount",staticStyle:{width:"15%"},attrs:{props:e}},[t._v(t._s(t.currencyFormatter.format(e.row.amount)+" "))]),e.row.bale_id&&e.row.bale_qty?a("q-td",{key:"bale",staticStyle:{width:"15%"},attrs:{props:e}},[a("q-chip",{attrs:{square:"",dense:"",color:"positive","text-color":"white"}},[t._v("\n                    "+t._s("PACA "+e.row.bale_id+" ("+t.formatter.format(e.row.bale_qty)+" KG.)")+"\n                  ")])],1):e.row.bales_ids&&e.row.bales_qtys?a("q-td",{key:"bale",staticStyle:{width:"15%"},attrs:{props:e}},[a("q-chip",{attrs:{square:"",dense:"",color:"positive","text-color":"white"}},[t._v("\n                    "+t._s("PACAS "+e.row.bales_ids+" ("+t.formatter.format(e.row.bales_qty)+" KG.)")+"\n                  ")])],1):"SURTIDO"==e.row.status?a("q-td",{key:"bale",staticStyle:{width:"15%"},attrs:{props:e}},[a("q-chip",{attrs:{square:"",dense:"",color:"positive","text-color":"white"}},[t._v("\n                    SURTIDO\n                  ")])],1):a("q-td",{key:"bale",staticStyle:{width:"15%"},attrs:{props:e}},[a("q-chip",{attrs:{square:"",dense:"",color:"negative","text-color":"white"}},[t._v("\n                    SIN PACA\n                  ")])],1),a("q-td",{key:"status",staticStyle:{width:"5%"},attrs:{props:e}},[a("q-chip",{attrs:{square:"",dense:"",color:"REMISIONADO"==e.row.status?"positive":"AUTORIZADO"==e.row.status?"warning":"primary","text-color":"white"}},[t._v("\n                    "+t._s(e.row.status)+"\n                  ")])],1)],1)]}}],null,!1,3609160732)}):t._e(),t.shoppingCartInBulkDetails.length>0?a("br"):t._e(),t.shoppingCartInBulkDetails.length>0?a("q-table",{attrs:{title:"Fibra abierta",dense:"","hide-bottom":"",data:t.shoppingCartInBulkDetails,columns:t.inBulkColumns,"row-key":"product_name"},scopedSlots:t._u([{key:"body",fn:function(e){return[a("q-tr",{attrs:{props:e}},[a("q-td",{key:"product_name",staticStyle:{width:"20%"},attrs:{props:e}},[t._v(t._s(e.row.product_name))]),a("q-td",{key:"line_name",staticStyle:{width:"10%"},attrs:{props:e}},[t._v(t._s(e.row.line_name))]),a("q-td",{key:"category_name",staticStyle:{width:"10%"},attrs:{props:e}},[t._v(t._s(e.row.category_name))]),a("q-td",{key:"qty",staticStyle:{width:"15%"},attrs:{props:e}},[t._v(t._s(t.formatter.format(e.row.qty)+" KG."))]),a("q-td",{key:"unit_price",staticStyle:{width:"10%"},attrs:{props:e}},[t._v(t._s(t.currencyFormatter.format(e.row.unit_price)+" "))]),a("q-td",{key:"amount",staticStyle:{width:"15%"},attrs:{props:e}},[t._v(t._s(t.currencyFormatter.format(e.row.amount)+" "))]),e.row.stock?a("q-td",{key:"stock",staticStyle:{width:"15%"},attrs:{props:e}},[a("q-chip",{attrs:{square:"",dense:"",color:"positive","text-color":"white"}},[t._v("\n                    STOCK: "+t._s(e.row.stock)+" KG\n                  ")])],1):a("q-td",{key:"stock",staticStyle:{width:"15%"},attrs:{props:e}},[a("q-chip",{attrs:{square:"",dense:"",color:"negative","text-color":"white"}},[t._v("\n                    SIN STOCK SUFICIENTE\n                  ")])],1),a("q-td",{key:"status",staticStyle:{width:"5%"},attrs:{props:e}},[a("q-chip",{attrs:{square:"",dense:"",color:"REMISIONADO"==e.row.status?"positive":"AUTORIZADO"==e.row.status?"warning":"primary","text-color":"white"}},[t._v("\n                    "+t._s(e.row.status)+"\n                  ")])],1)],1)]}}],null,!1,1920694909)}):t._e(),t.shoppingCartLaminateDetails.length>0?a("br"):t._e(),t.shoppingCartLaminateDetails.length>0?a("q-table",{attrs:{title:"Laminado",dense:"","hide-bottom":"",data:t.shoppingCartLaminateDetails,columns:t.laminateColumns,"row-key":"product_name"},scopedSlots:t._u([{key:"body",fn:function(e){return[a("q-tr",{attrs:{props:e}},[a("q-td",{key:"product_name",staticStyle:{width:"20%"},attrs:{props:e}},[t._v(t._s(e.row.product_name))]),a("q-td",{key:"line_name",staticStyle:{width:"10%"},attrs:{props:e}},[t._v(t._s(e.row.line_name))]),a("q-td",{key:"category_name",staticStyle:{width:"10%"},attrs:{props:e}},[t._v(t._s(e.row.category_name))]),a("q-td",{key:"qty",staticStyle:{width:"15%"},attrs:{props:e}},[t._v(t._s(t.formatter.format(e.row.qty)+" KG."))]),a("q-td",{key:"unit_price",staticStyle:{width:"10%"},attrs:{props:e}},[t._v(t._s(t.currencyFormatter.format(e.row.unit_price)+" "))]),a("q-td",{key:"amount",staticStyle:{width:"15%"},attrs:{props:e}},[t._v(t._s(t.currencyFormatter.format(e.row.amount)+" "))]),e.row.stock?a("q-td",{key:"stock",staticStyle:{width:"15%"},attrs:{props:e}},[a("q-chip",{attrs:{square:"",dense:"",color:"positive","text-color":"white"}},[t._v("\n                    STOCK: "+t._s(e.row.stock)+" KG\n                  ")])],1):a("q-td",{key:"stock",staticStyle:{width:"15%"},attrs:{props:e}},[a("q-chip",{attrs:{square:"",dense:"",color:"negative","text-color":"white"}},[t._v("\n                    SIN STOCK SUFICIENTE\n                  ")])],1),a("q-td",{key:"status",staticStyle:{width:"5%"},attrs:{props:e}},[a("q-chip",{attrs:{square:"",dense:"",color:"REMISIONADO"==e.row.status?"positive":"AUTORIZADO"==e.row.status?"warning":"primary","text-color":"white"}},[t._v("\n                    "+t._s(e.row.status)+"\n                  ")])],1)],1)]}}],null,!1,1920694909)}):t._e()],1)])])]),a("q-dialog",{attrs:{persistent:""},model:{value:t.generateInvoiceModal,callback:function(e){t.generateInvoiceModal=e},expression:"generateInvoiceModal"}},[a("q-card",{staticStyle:{"min-width":"400px"}},[a("q-card-section",[a("div",{staticClass:"text-h6"},[t._v("Surtir pedido")])]),a("q-card-section",[a("div",{staticClass:"row"},[a("div",{staticClass:"col-xs-12"},[a("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",mask:"date",label:"Fecha de venta",rules:t.saleDateRules},scopedSlots:t._u([{key:"prepend",fn:function(){return[a("q-icon",{attrs:{name:"event"}})]},proxy:!0}]),model:{value:t.saleDate,callback:function(e){t.saleDate=e},expression:"saleDate"}},[a("q-popup-proxy",{ref:"invoiceSaleDate",attrs:{"transition-show":"scale","transition-hide":"scale"}},[a("div",{staticClass:"col-sm-12"},[a("q-date",{attrs:{"today-btn":""},on:{input:function(){return t.$refs.invoiceSaleDate.hide()}},model:{value:t.saleDate,callback:function(e){t.saleDate=e},expression:"saleDate"}})],1)])],1)],1),a("div",{staticClass:"col-xs-12"},[a("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",autogrow:"",rules:t.customerBranchOfficeRules,label:"Cliente",disable:""},model:{value:t.shoppingCart.fields.customer_name,callback:function(e){t.$set(t.shoppingCart.fields,"customer_name",e)},expression:"shoppingCart.fields.customer_name"}})],1),a("div",{staticClass:"col-xs-12"},[a("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",options:t.customerBranchOfficeOptions,label:"Sucursal de cliente",rules:t.customerBranchOfficeRules},scopedSlots:t._u([{key:"prepend",fn:function(){return[a("q-icon",{attrs:{name:"fas fa-store-alt"}})]},proxy:!0}]),model:{value:t.customerBranchOffice,callback:function(e){t.customerBranchOffice=e},expression:"customerBranchOffice"}})],1),a("div",{staticClass:"col-xs-12"},[a("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",options:t.driverOptions,label:"Chofer",rules:t.driverRules},scopedSlots:t._u([{key:"prepend",fn:function(){return[a("q-icon",{attrs:{name:"fas fa-truck"}})]},proxy:!0}]),model:{value:t.driver,callback:function(e){t.driver=e},expression:"driver"}})],1),a("div",{staticClass:"col-xs-12"},[a("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",autogrow:"",label:"Comentarios",type:"textarea"},on:{input:function(e){t.comments=e.toUpperCase()}},model:{value:t.comments,callback:function(e){t.comments=e},expression:"comments"}})],1)])]),a("q-card-actions",{staticClass:"text-primary",attrs:{align:"right"}},[a("q-btn",{directives:[{name:"close-popup",rawName:"v-close-popup"}],attrs:{flat:"",label:"Cancelar"},on:{click:function(e){return t.closeGenerateInvoiceModal()}}}),a("q-btn",{attrs:{flat:"",label:"Generar"},on:{click:function(e){return t.generateInvoice()}}})],1)],1)],1)],1)},s=[],o=(a("caad"),a("2532"),a("d3b7"),a("159b"),a("a9e3"),a("4de4"),a("aabb")),i=a("b5ae"),n=i.required,l={name:"EditRequestedOrder",validations:{saleDate:{required:n},customerBranchOffice:{required:n},driver:{required:n}},data:function(){return{shoppingCart:{fields:{id:null,customer_id:null,customer_name:null,price_list:null,user_id:null,user_name:null,user_email:null,status:null,comments:null}},serverUrl:"https://api_alpez.wasp.mx/",formatter:new Intl.NumberFormat("en-US"),currencyFormatter:new Intl.NumberFormat("en-US",{style:"currency",currency:"USD"}),baleColumns:[{name:"product_name",align:"left",label:"Producto",field:"product_name",sortable:!0},{name:"line_name",align:"left",label:"Línea",field:"line_name",sortable:!0},{name:"category_name",align:"left",label:"Categoría",field:"category_name",sortable:!0},{name:"qty",align:"right",label:"Cantidad",field:"qty",sortable:!0},{name:"unit_price",align:"right",label:"Precio kg.",field:"unit_price",sortable:!0},{name:"amount",align:"right",label:"Importe",field:"amount",sortable:!0},{name:"bale",align:"center",label:"Paca",field:"bale",sortable:!1},{name:"status",align:"center",label:"Estatus",field:"status",sortable:!1}],inBulkColumns:[{name:"product_name",align:"left",label:"Producto",field:"product_name",sortable:!0},{name:"line_name",align:"left",label:"Línea",field:"line_name",sortable:!0},{name:"category_name",align:"left",label:"Categoría",field:"category_name",sortable:!0},{name:"qty",align:"right",label:"Cantidad",field:"qty",sortable:!0},{name:"unit_price",align:"right",label:"Precio kg.",field:"unit_price",sortable:!0},{name:"amount",align:"right",label:"Importe",field:"amount",sortable:!0},{name:"stock",align:"center",label:"Stock",field:"stock",sortable:!0},{name:"status",align:"center",label:"Estatus",field:"status",sortable:!1}],laminateColumns:[{name:"product_name",align:"left",label:"Producto",field:"product_name",sortable:!0},{name:"line_name",align:"left",label:"Línea",field:"line_name",sortable:!0},{name:"category_name",align:"left",label:"Categoría",field:"category_name",sortable:!0},{name:"qty",align:"right",label:"Cantidad",field:"qty",sortable:!0},{name:"unit_price",align:"right",label:"Precio kg.",field:"unit_price",sortable:!0},{name:"amount",align:"right",label:"Importe",field:"amount",sortable:!0},{name:"stock",align:"center",label:"Stock",field:"stock",sortable:!0},{name:"status",align:"center",label:"Estatus",field:"status",sortable:!1}],shoppingCartBaleDetails:[],shoppingCartInBulkDetails:[],shoppingCartLaminateDetails:[],customerBranchOfficeOptions:[],driverOptions:[],generateInvoiceModal:!1,saleDate:null,customerBranchOffice:null,driver:null,comments:null}},beforeCreate:function(){this.$store.getters["users/roles"].includes(1)||this.$store.getters["users/roles"].includes(3)||this.$store.getters["users/roles"].includes(7)||this.$store.getters["users/roles"].includes(2)||this.$router.push("/")},created:function(){this.fetchFromServer()},computed:{totalPrice:function(){var t=0;return this.shoppingCartBaleDetails.forEach((function(e){t+=Number(e.amount)})),this.shoppingCartInBulkDetails.forEach((function(e){t+=Number(e.amount)})),this.shoppingCartLaminateDetails.forEach((function(e){t+=Number(e.amount)})),this.currencyFormatter.format(t)},canGenerateInvoice:function(){var t=!1,e=this.shoppingCartBaleDetails.filter((function(t){return(null!=t.bale_id&&null!=t.bale_qty||null!=t.bales_ids&&t.bales_ids.length>0&&null!=t.bales_qtys&&t.bales_qtys.length>0&&null!=t.bales_qty)&&"REMISIONADO"===t.status})),a=this.shoppingCartInBulkDetails.filter((function(t){return t.stock&&"REMISIONADO"===t.status})),r=this.shoppingCartLaminateDetails.filter((function(t){return t.stock&&"REMISIONADO"===t.status}));return e.length>0&&a.length>0&&r.length>0&&(t=!0),t},saleDateRules:function(t){var e=this;return[function(t){return e.$v.saleDate.required||"El campo Fecha de venta es requerido."}]},customerBranchOfficeRules:function(t){var e=this;return[function(t){return e.$v.customerBranchOffice.required||"El campo Sucursal de cliente es requerido."}]},driverRules:function(t){var e=this;return[function(t){return e.$v.driver.required||"El campo Chofer es requerido."}]}},methods:{fetchFromServer:function(){var t=this;this.$q.loading.show();var e=this.$route.params.id;o["a"].get("/shopping-carts/".concat(e)).then((function(a){var r=a.data;r.shoppingCart?(t.shoppingCart.fields=r.shoppingCart,o["a"].get("/shopping-cart-bale-details/shopping-cart/".concat(e)).then((function(a){var r=a.data;t.shoppingCartBaleDetails=r.details,o["a"].get("/shopping-cart-in-bulk-details/shopping-cart/".concat(e)).then((function(a){var r=a.data;t.shoppingCartInBulkDetails=r.details,o["a"].get("/shopping-cart-laminate-details/shopping-cart/".concat(e)).then((function(e){var a=e.data;t.shoppingCartLaminateDetails=a.details,o["a"].get("/customer-branch-offices/options").then((function(e){var a=e.data;t.customerBranchOfficeOptions=a.options.filter((function(e){return Number(e.customer)===Number(t.shoppingCart.fields.customer_id)})),o["a"].get("/drivers/options").then((function(e){var a=e.data;t.driverOptions=a.options,t.$q.loading.hide()}))}))}))}))}))):t.$router.push("/approved-orders")}))},openGenerateInvoiceModal:function(){this.generateInvoiceModal=!0},closeGenerateInvoiceModal:function(){this.generateInvoiceModal=!1,this.saleDate=null,this.customerBranchOffice=null,this.driver=null,this.comments=null},generateInvoice:function(){var t=this;if(this.$v.saleDate.$reset(),this.$v.saleDate.$touch(),this.$v.customerBranchOffice.$reset(),this.$v.customerBranchOffice.$touch(),this.$v.driver.$reset(),this.$v.driver.$touch(),this.$v.saleDate.$error||this.$v.customerBranchOffice.$error||this.$v.driver.$error)return this.$q.dialog({title:"Error",message:"Por favor, verifique las validaciones.",persistent:!0}),!1;this.$q.loading.show();var e=[];e.saleDate=this.saleDate,e.customerBranchOfficeId=this.customerBranchOffice.value,e.driverId=this.driver.value,e.comments=this.comments,o["a"].put("/shopping-carts/".concat(this.$route.params.id,"/generate-invoice"),e).then((function(e){var a=e.data;t.$q.notify({message:a.message.content,position:"top",color:a.result?"positive":"warning"}),a.result?(t.closeGenerateInvoiceModal(),t.$q.loading.hide(),t.fetchFromServer()):(t.closeGenerateInvoiceModal(),t.$q.loading.hide())}))}}},c=l,d=a("2877"),u=a("9989"),p=a("ead5"),m=a("079e"),f=a("27f9"),h=a("0016"),b=a("9c40"),g=a("eaac"),v=a("bd08"),_=a("db86"),y=a("b047"),q=a("24e8"),w=a("f09f"),C=a("a370"),k=a("ddd8"),S=a("7cbe"),I=a("52ee"),x=a("4b7e"),D=a("7f67"),O=a("eebe"),$=a.n(O),B=Object(d["a"])(c,r,s,!1,null,null,null);e["default"]=B.exports;$()(B,"components",{QPage:u["a"],QBreadcrumbs:p["a"],QBreadcrumbsEl:m["a"],QInput:f["a"],QIcon:h["a"],QBtn:b["a"],QTable:g["a"],QTr:v["a"],QTd:_["a"],QChip:y["a"],QDialog:q["a"],QCard:w["a"],QCardSection:C["a"],QSelect:k["a"],QPopupProxy:S["a"],QDate:I["a"],QCardActions:x["a"]}),$()(B,"directives",{ClosePopup:D["a"]})}}]);
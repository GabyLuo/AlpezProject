(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[80],{"366c":function(t,e,i){"use strict";i.r(e);var s=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("q-page",[i("div",{staticClass:"q-pa-sm panel-header"},[i("div",{staticClass:"row"},[i("div",{staticClass:"col-sm-9"},[i("div",{staticClass:"q-pa-md q-gutter-sm"},[i("q-breadcrumbs",{staticStyle:{"font-size":"20px"},attrs:{align:"left"}},[i("q-breadcrumbs-el",{attrs:{label:"",icon:"home",to:"/"}}),i("q-breadcrumbs-el",{attrs:{label:"Embarques",to:"/trips"}}),i("q-breadcrumbs-el",{attrs:{label:t.principalSerial,to:t.idUrlBack}}),i("q-breadcrumbs-el",{attrs:{label:"Editar envio"},domProps:{textContent:t._s(t.lot.fields.folio)}})],1)],1)])])]),i("div",{staticClass:"q-pa-md bg-grey-3"},[i("div",{staticClass:"row bg-white border-panel"},[i("div",{staticClass:"col q-pa-md"},[i("div",{staticClass:"row q-mb-sm",staticStyle:{visibility:"hidden"}},[i("div",{staticClass:"col-sm-1 offset-11 pull-right"},[i("q-btn",{attrs:{color:"primary",label:"Editar"}})],1)]),i("div",{staticClass:"row q-col-gutter-xs"},[i("div",{staticClass:"col-xs-12 col-sm-3"},[i("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",disable:"",label:"Folio"},scopedSlots:t._u([{key:"prepend",fn:function(){return[i("q-icon",{attrs:{name:"fingerprint"}})]},proxy:!0}]),model:{value:t.lot.fields.folio,callback:function(e){t.$set(t.lot.fields,"folio",e)},expression:"lot.fields.folio"}})],1),i("div",{staticClass:"col-xs-12 col-sm-3 text-center"},[i("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",disable:"NUEVO"!==t.status,rules:t.dateRules,error:t.$v.lot.fields.date.$error,mask:"date",label:"Fecha"},scopedSlots:t._u([{key:"prepend",fn:function(){return[i("q-icon",{attrs:{name:"event"}})]},proxy:!0}]),model:{value:t.lot.fields.date,callback:function(e){t.$set(t.lot.fields,"date",e)},expression:"lot.fields.date"}},[i("q-popup-proxy",{ref:"date_ref",attrs:{"transition-show":"scale","transition-hide":"scale"}},[i("div",{staticClass:"col-sm-12"},[i("q-date",{attrs:{color:"secondary","text-color":"white",mask:"DD/MM/YYYY","today-btn":""},on:{input:function(){return t.$refs.date_ref.hide()}},model:{value:t.lot.fields.date,callback:function(e){t.$set(t.lot.fields,"date",e)},expression:"lot.fields.date"}})],1)])],1)],1),i("div",{staticClass:"col-xs-12 col-sm-6"},[i("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",disable:"NUEVO"!==t.status,error:t.$v.lot.fields.client.$error,label:"Cliente",rules:t.clientRules,options:t.clientOptionsFilter,"use-input":"","hide-selected":"","fill-input":"","input-debounce":"0",hint:"Basic autocomplete","emit-value":"","map-options":""},on:{filter:t.filterClient},scopedSlots:t._u([{key:"prepend",fn:function(){return[i("q-icon",{attrs:{name:"person"}})]},proxy:!0},{key:"no-option",fn:function(){return[i("q-item",[i("q-item-section",{staticClass:"groups"},[t._v("\n                    No hay Resultados\n                  ")])],1)]},proxy:!0}]),model:{value:t.lot.fields.client,callback:function(e){t.$set(t.lot.fields,"client",e)},expression:"lot.fields.client"}})],1)]),"NUEVO"===t.status?i("div",{staticClass:"row q-mb-sm q-mt-md"},[i("div",{staticClass:"col-xs-12 col-sm-2 offset-sm-10 pull-right"},[i("q-btn",{attrs:{color:"positive",icon:"save",label:"Actualizar"},on:{click:function(e){return t.updateLotTrip()}}})],1)]):t._e()])])]),i("div",{staticClass:"q-pa-md bg-grey-3"},[i("div",{staticClass:"row bg-white border-panel"},[i("div",{staticClass:"col q-pa-md"},["NUEVO"===t.status?i("div",{staticClass:"row q-col-gutter-xs"},[i("div",{staticClass:"col-xs-12 col-sm-10"},[i("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",disabled:t.editFlag,error:t.$v.lotProducts.fields.product.$error,label:"Producto",rules:t.productRules,options:t.productsOptionsFilter,"use-input":"","emit-value":"","map-options":""},on:{filter:t.filterProducts,input:function(e){return t.getStock()}},scopedSlots:t._u([{key:"prepend",fn:function(){return[i("q-icon",{attrs:{name:"shopping_cart"}})]},proxy:!0},{key:"no-option",fn:function(){return[i("q-item",[i("q-item-section",{staticClass:"text-grey"},[t._v("\n                    No hay Resultados\n                  ")])],1)]},proxy:!0}],null,!1,3846668095),model:{value:t.lotProducts.fields.product,callback:function(e){t.$set(t.lotProducts.fields,"product",e)},expression:"lotProducts.fields.product"}})],1),i("div",{staticClass:"col-xs-12 col-sm-2"},[i("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",rules:t.qtyRules,error:t.$v.lotProducts.fields.qty.$error,label:"Cantidad",hint:"Basic autocomplete",suffix:"/"+t.selectProductStock,"emit-value":"","map-options":""},scopedSlots:t._u([{key:"prepend",fn:function(){return[i("q-icon",{attrs:{name:"production_quantity_limits"}})]},proxy:!0}],null,!1,2937214290),model:{value:t.lotProducts.fields.qty,callback:function(e){t.$set(t.lotProducts.fields,"qty",e)},expression:"lotProducts.fields.qty"}})],1),t.editFlag?t._e():i("div",{staticClass:"col-xs-12 col-sm-2 offset-sm-10 pull-right"},[i("q-btn",{attrs:{color:"positive",icon:"add",label:"Agregar"},on:{click:function(e){return t.addProduct()}}})],1),t.editFlag?i("div",{staticClass:"col-xs-12 col-sm-12 pull-right"},[i("q-btn",{staticStyle:{"margin-right":"7px"},attrs:{color:"negative",icon:"cancel",label:"Cancelar"},on:{click:function(e){return t.cancelEditProdduct()}}}),i("q-btn",{attrs:{color:"positive",icon:"add",label:"Actualizar"},on:{click:function(e){return t.editProduct()}}})],1):t._e()]):t._e(),i("div",{staticClass:"row bg-white border-panel",staticStyle:{"margin-top":"10px"}},[i("div",{staticClass:"col q-pa-md"},[i("q-table",{attrs:{flat:"",bordered:"",data:t.lotProductData,columns:"NUEVO"===t.status?t.lotProductColumns:t.lotProductColumnsExit,"row-key":"code"},scopedSlots:t._u([{key:"body",fn:function(e){return[i("q-tr",{attrs:{props:e}},[i("q-td",{key:"code",staticStyle:{"text-align":"center"},attrs:{props:e}},[t._v(t._s(e.row.code))]),i("q-td",{key:"name",staticStyle:{"text-align":"left"},attrs:{props:e}},[t._v(t._s(e.row.product))]),i("q-td",{key:"category",staticStyle:{"text-align":"left"},attrs:{props:e}},[t._v(t._s(e.row.category))]),i("q-td",{key:"line",staticStyle:{"text-align":"left"},attrs:{props:e}},[t._v(t._s(e.row.line))]),i("q-td",{key:"qty",staticStyle:{"text-align":"center"},attrs:{props:e}},[t._v(t._s(e.row.qty))]),i("q-td",{key:"actions",staticStyle:{"text-align":"center"},attrs:{props:e}},[i("q-btn",{attrs:{color:"primary",icon:"fas fa-edit",flat:"",size:"10px"},nativeOn:{click:function(i){return t.selectEditRow(e.row.id)}}},[i("q-tooltip",{attrs:{"content-class":"bg-primary"}},[t._v("Editar")])],1),i("q-btn",{attrs:{color:"red",icon:"fas fa-trash-alt",flat:"",size:"10px"},nativeOn:{click:function(i){return t.deleteSelectProductRow(e.row.id)}}},[i("q-tooltip",{attrs:{"content-class":"bg-red"}},[t._v("Eliminar")])],1)],1)],1)]}}])})],1)])])])])])},l=[],o=i("ded3"),r=i.n(o),a=(i("a9e3"),i("4de4"),i("d3b7"),i("caad"),i("2532"),i("aabb")),n=i("b5ae"),c=n.required,d={name:"EditShipping",validations:{lot:{fields:{date:{required:c},client:{required:c}}},lotProducts:{fields:{product:{required:c},qty:{required:c}}},branchOffice:{required:c},storage:{required:c}},data:function(){return{lot:{fields:{id:Number(this.$route.params.id),folio:null,date:null,client:null,trip_id:null}},lotProducts:{fields:{shipping_id:Number(this.$route.params.id),product:null,qty:null}},idUrlBack:null,clientOptions:[],clientOptionsFilter:[],lotProductsOptions:[],productsOptionsFilter:[],lotProductData:[],lotProductColumns:[{name:"code",align:"center",label:"CÓDIGO",field:"code",sortable:!1},{name:"name",align:"center",label:"PRODUCTO",field:"name",sortable:!1},{name:"qty",align:"center",label:"CANTIDAD",field:"qty",sortable:!1},{name:"actions",align:"center",label:"ACCIONES",field:"actions",sortable:!1}],lotProductColumnsExit:[{name:"code",align:"center",label:"CÓDIGO",field:"code",sortable:!1},{name:"name",align:"center",label:"PRODUCTO",field:"name",sortable:!1},{name:"qty",align:"center",label:"CANTIDAD",field:"qty",sortable:!1}],editFlag:!1,selectRow:null,branchOffice:{value:null,label:"TODAS"},storage:{value:null,label:"TODOS"},branchOfficeOptions:[],storageOptions:[],selectProductStock:null,principalSerial:null,saveFlag:!1,status:null}},computed:{dateRules:function(t){var e=this;return[function(t){return e.$v.lot.fields.date.required||"El campo Fecha es requerido."}]},clientRules:function(t){var e=this;return[function(t){return e.$v.lot.fields.client.required||"El campo Cliente es requerido."}]},productRules:function(t){var e=this;return[function(t){return e.$v.lotProducts.fields.product.required||"El campo Producto es requerido."}]},qtyRules:function(t){var e=this;return[function(t){return e.$v.lotProducts.fields.qty.required||"El campo Cantidad es requerido."}]},filteredStorageOptions:function(){var t=this,e=null!=this.branchOffice&&null!=this.branchOffice.value?this.storageOptions.filter((function(e){return Number(e.branchOffice)===Number(t.branchOffice.value)})):this.storageOptions;return e.unshift({value:null,label:"TODOS"}),e}},beforeCreate:function(){this.$store.getters["users/roles"].includes(1)||this.$store.getters["users/roles"].includes(3)||this.$store.getters["users/roles"].includes(7)||this.$store.getters["users/roles"].includes(10)||this.$router.push("/")},created:function(){this.$q.loading.show(),this.fetchFromServer(),this.getDetails(),this.getClient(),this.getProductsByStorage(),this.$q.loading.hide()},methods:{fetchFromServer:function(){var t=this;this.$q.loading.show();var e=this.$route.params.id;a["a"].get("/shippings/".concat(e)).then((function(e){var i=e.data,s=i.shipping;t.lot.fields.id=s.id,t.lot.fields.folio=s.serial,t.lot.fields.date=s.date.substr(8,2)+"/"+s.date.substr(5,2)+"/"+s.date.substr(0,4),t.lot.fields.client={value:s.client_id,label:s.client_name},t.lot.fields.trip_id=s.trip_id,t.idUrlBack="/trips/"+s.trip_id,t.status=s.status,t.principalSerial=s.serial.substr(0,9),t.$q.loading.hide()}))},getClient:function(){var t=this;a["a"].get("customers/options").then((function(e){t.clientOptions=e.data.options}))},filterClient:function(t,e,i){var s=this;e((function(){var e=t.toLowerCase();s.clientOptionsFilter=s.clientOptions.filter((function(t){return t.label.toLowerCase().indexOf(e)>-1}))}))},updateLotTrip:function(){var t=this;if(this.$v.lot.fields.$reset(),this.$v.lot.fields.$touch(),this.$v.lot.fields.$error)return this.$q.dialog({title:"Error",message:"Por favor, verifique las validaciones.",persistent:!0}),!1;this.$q.loading.show();var e=r()({},this.lot.fields);e.client=this.lot.fields.client,this.lot.fields.client.value&&(e.client=this.lot.fields.client.value),e.date=this.lot.fields.date.substr(6,10)+"-"+this.lot.fields.date.substr(3,2)+"-"+this.lot.fields.date.substr(0,2),a["a"].put("/shippings/".concat(e.id),e).then((function(e){var i=e.data;t.$q.notify({message:i.message.content,position:"top",color:i.result?"positive":"warning"}),i.result,t.$q.loading.hide()}))},getDetails:function(){var t=this,e=this.$route.params.id;a["a"].get("/shipping-details/all/".concat(e)).then((function(e){var i=e.data;t.lotProductData=i.products}))},filterProducts:function(t,e,i){var s=this;e((function(){var e=t.toLowerCase();s.productsOptionsFilter=s.lotProductsOptions.filter((function(t){return t.label.toLowerCase().indexOf(e)>-1}))}))},clearInputsDetails:function(){this.lotProducts.fields.product=null,this.lotProducts.fields.qty=null,this.branchOffice={value:null,label:"TODAS"},this.storage={value:null,label:"TODOS"}},addProduct:function(){var t=this;if(this.$v.lotProducts.fields.$reset(),this.$v.lotProducts.fields.$touch(),this.$v.lotProducts.fields.$error)return this.$q.dialog({title:"Error",message:"Por favor, verifique las validaciones.",persistent:!0}),!1;if(Number(this.lotProducts.fields.qty)>Number(this.selectProductStock))return this.$q.dialog({title:"Error",message:"La cantidad ingresada es mayor a la cantidad del inventario.",persistent:!0}),!1;var e=r()({},this.lotProducts.fields);this.$q.loading.show(),a["a"].post("/shipping-details/",e).then((function(e){var i=e.data;t.$q.notify({message:i.message.content,position:"top",color:i.result?"positive":"warning"}),i.result?(t.saveFlag=!0,t.$q.loading.hide(),t.clearInputsDetails(),t.getDetails()):t.$q.loading.hide()}))},selectEditRow:function(t){var e=this;this.$q.loading.show(),this.editFlag=!0,this.selectRow=t,a["a"].get("/shipping-details/".concat(t)).then((function(t){e.lotProducts.fields.product={value:Number(t.data.product.id),label:t.data.product.product},e.lotProducts.fields.qty=Number(t.data.product.qty),a["a"].get("/shipping-details/productInventoryOptions/null/null/null/null/".concat(Number(t.data.product.id),"/null")).then((function(t){var i=t.data;i.result&&(e.selectProductStock=i.stock[0].stock),e.$q.loading.hide()}))}))},cancelEditProdduct:function(){this.editFlag=!1,this.selectRow=null,this.clearInputsDetails()},editProduct:function(){var t=this;if(this.$v.lotProducts.fields.$reset(),this.$v.lotProducts.fields.$touch(),this.$v.lotProducts.fields.$error)return this.$q.dialog({title:"Error",message:"Por favor, verifique las validaciones.",persistent:!0}),!1;if(Number(this.lotProducts.fields.qty)>Number(this.selectProductStock))return this.$q.dialog({title:"Error",message:"La cantidad ingresada es mayor a la cantidad del inventario.",persistent:!0}),!1;var e=r()({},this.lotProducts.fields);e.product=this.lotProducts.fields.product,this.lotProducts.fields.product.value&&(e.product=this.lotProducts.fields.product.value),this.$q.loading.show(),a["a"].put("/shipping-details/".concat(this.selectRow),e).then((function(e){var i=e.data;t.$q.notify({message:i.message.content,position:"top",color:i.result?"positive":"warning"}),i.result?(t.saveFlag=!0,t.cancelEditProdduct(),t.getDetails(),t.$q.loading.hide()):t.$q.loading.hide()}))},deleteSelectProductRow:function(t){var e=this;this.$q.dialog({title:"Confirmación",message:"¿Desea eliminar este Producto",persistent:!0,ok:{label:"Aceptar",color:"green"},cancel:{label:"Cancelar",color:"red"}}).onOk((function(){a["a"].delete("/shipping-details/".concat(t)).then((function(t){var i=t.data;e.$q.notify({message:i.message.content,position:"top",color:i.result?"positive":"warning",icon:i.result?"thumb_up":"close"}),i.result&&(e.saveFlag=!0,e.getDetails())}))})).onCancel((function(){}))},getProductsByStorage:function(){var t=this;this.$q.loading.show(),a["a"].get("/shipping-details/productInventoryOptions/5/28/null/null/null/nul").then((function(e){var i=e.data;i.result&&(t.lotProductsOptions=i.stock),t.$q.loading.hide()}))},getStock:function(){var t=this;this.$q.loading.show();var e=this.lotProducts.fields.product;this.lotProducts.fields.product.value&&(e=this.lotProducts.fields.product.value),a["a"].get("/shipping-details/productInventoryOptions/null/null/null/null/".concat(e,"/null")).then((function(e){var i=e.data;i.result&&(t.selectProductStock=i.stock[0].stock),t.$q.loading.hide()}))},saveShipping:function(){this.$router.push(this.idUrlBack)}}},u=d,p=i("2877"),f=i("9989"),h=i("ead5"),g=i("079e"),v=i("9c40"),m=i("27f9"),b=i("0016"),q=i("ddd8"),y=i("7cbe"),$=i("52ee"),P=i("66e5"),C=i("4074"),k=i("eaac"),O=i("bd08"),w=i("db86"),x=i("05c0"),S=i("eebe"),_=i.n(S),E=Object(p["a"])(u,s,l,!1,null,null,null);e["default"]=E.exports;_()(E,"components",{QPage:f["a"],QBreadcrumbs:h["a"],QBreadcrumbsEl:g["a"],QBtn:v["a"],QInput:m["a"],QIcon:b["a"],QSelect:q["a"],QPopupProxy:y["a"],QDate:$["a"],QItem:P["a"],QItemSection:C["a"],QTable:k["a"],QTr:O["a"],QTd:w["a"],QTooltip:x["a"]})}}]);
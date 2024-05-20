(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[103],{"52ac":function(e,t,r){"use strict";r.r(t);var o=function(){var e=this,t=e._self._c;return t("q-page",{staticClass:"bg-grey-3"},[t("div",{staticClass:"q-pa-sm panel-header"},[t("div",{staticClass:"row"},[t("div",{staticClass:"col-sm-12",staticStyle:{"font-size":"20px"}},[t("div",{staticClass:"q-pa-md q-gutter-sm"},[t("q-breadcrumbs",{attrs:{align:"left"}},[t("q-breadcrumbs-el",{attrs:{label:"",icon:"home",to:"/"}}),t("q-breadcrumbs-el",{attrs:{label:"Ordenes de producción",to:"/production-orders"}}),t("q-breadcrumbs-el",{attrs:{label:"Nuevo"}})],1)],1)])])]),t("div",{staticClass:"q-pa-md bg-grey-3"},[t("div",{staticClass:"row bg-white border-panel"},[t("div",{staticClass:"col q-pa-md"},[t("div",{staticClass:"row q-col-gutter-xs"},[t("div",{staticClass:"col-xs-12 col-sm-3"},[t("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",mask:"date",label:"Fecha programada",rules:e.creationDateRules},scopedSlots:e._u([{key:"prepend",fn:function(){return[t("q-icon",{attrs:{name:"event"}})]},proxy:!0}]),model:{value:e.order.fields.production_date,callback:function(t){e.$set(e.order.fields,"production_date",t)},expression:"order.fields.production_date"}},[t("q-popup-proxy",{ref:"orderFieldsCreationDateRef",attrs:{"transition-show":"scale","transition-hide":"scale"}},[t("div",{staticClass:"col-sm-12"},[t("q-date",{attrs:{"today-btn":""},on:{input:function(){return e.$refs.orderFieldsCreationDateRef.hide()}},model:{value:e.order.fields.production_date,callback:function(t){e.$set(e.order.fields,"production_date",t)},expression:"order.fields.production_date"}})],1)])],1)],1)]),t("div",{staticClass:"row q-mb-sm q-mt-md"},[t("div",{staticClass:"col-xs-12 col-sm-2 offset-sm-10 pull-right"},[t("q-btn",{attrs:{color:"positive",icon:"save",label:"Guardar"},on:{click:function(t){return e.createOrder()}}})],1)])])])])])},s=[],i=(r("caad"),r("2532"),r("14d9"),r("3c65"),r("4de4"),r("d3b7"),r("aabb")),a=r("b5ae"),n=a.required,d={name:"NewProductionOrder",validations:{order:{fields:{production_date:{required:n},status:{required:n},category:{},product:{},cantidad:{},unit_id:{}}}},data:function(){return{options1:this.categoryOptions,options2:this.ProductsOptionsbyLines,order:{fields:{production_date:null,product:null,qty:null,status:"NUEVO",category:null,cantidad:null,unit_id:null}},productOptions:[],lotOptions:[{value:1,label:1},{value:2,label:2},{value:3,label:3},{value:4,label:4},{value:5,label:5},{value:6,label:6},{value:7,label:7},{value:8,label:8},{value:9,label:9},{value:10,label:10}],categoryOptions:[],ProductsOptionsbyLines:[]}},computed:{creationDateRules:function(e){var t=this;return[function(e){return t.$v.order.fields.production_date.required||"El campo Fecha de creación es requerido."}]},productRules:function(e){var t=this;return[function(e){return t.$v.order.fields.product.required||"El campo Producto es requerido."}]},qtyRules:function(e){var t=this;return[function(e){return t.$v.order.fields.qty.required||"El campo Cantidad es requerido."},function(e){return t.$v.order.fields.qty.decimal||"El campo Cantidad debe ser numérico."}]},lotsQtyRules:function(e){var t=this;return[function(e){return t.$v.order.fields.lotsQty.required||"El campo Lotes es requerido."}]},bomcategoryRules:function(e){var t=this;return[function(e){return t.$v.order.fields.category||"El campo Categorías es requerido."}]},bomproductsRules:function(e){var t=this;return[function(e){return t.$v.order.fields.product.required||"El campo Productos es requerido."}]},CantidadRules:function(e){var t=this;return[function(e){return t.$v.order.fields.cantidad.required||"El campo Cantidad es requerido"},function(e){return t.$v.order.fields.cantidad.cantidad||"El campo Cantidad debe ser númerico"}]}},beforeCreate:function(){this.$store.getters["users/roles"].includes(1)||this.$store.getters["users/roles"].includes(3)||this.$store.getters["users/roles"].includes(5)||this.$store.getters["users/roles"].includes(6)||this.$store.getters["users/roles"].includes(7)||this.$router.push("/")},created:function(){this.fetchFromServer()},mounted:function(){this.getCategories()},methods:{fetchFromServer:function(){this.$q.loading.show(),this.$q.loading.hide()},backToGrid:function(){this.$router.push("/production-orders")},getUnit:function(){var e=this,t=this.order.fields.product.id;i["a"].get("products/".concat(t)).then((function(t){var r=t.data;e.order.fields.unit={value:r.product.unit_id,label:r.product.unit}}))},createOrder:function(){var e=this;if(this.$v.order.fields.$reset(),this.$v.order.fields.$touch(),this.$v.order.fields.$error)return this.$q.dialog({title:"Error",message:"Por favor, verifique las validaciones.",persistent:!0}),!1;this.$q.loading.show();var t=[];t.production_date=this.order.fields.production_date,i["a"].post("/production-orders",t).then((function(t){var r=t.data;e.$q.notify({message:r.message.content,position:"top",color:r.result?"positive":"warning"}),r.result?(e.$q.loading.hide(),e.$router.push("/production-orders/".concat(r.order.id))):e.$q.loading.hide()}))},getCategories:function(){var e=this;i["a"].get("/categories/options").then((function(t){var r=t.data;e.categoryOptions=r.options}))},getLinesByCategories:function(){var e=this;this.ProductsOptionsbyLines=[],i["a"].get("/products/category1/".concat(this.order.fields.category)).then((function(t){var r=t.data;e.ProductsOptionsbyLines=r.products,e.ProductsOptionsbyLines.unshift({value:null,label:"NINGUNO"})}))},filterCategory:function(e,t,r){var o=this;t((function(){var t=e.toLowerCase();o.options1=o.categoryOptions.filter((function(e){return e.label.toLowerCase().indexOf(t)>-1}))}))},filterProduct:function(e,t,r){var o=this;t((function(){var t=e.toLowerCase();o.options2=o.ProductsOptionsbyLines.filter((function(e){return e.label.toLowerCase().indexOf(t)>-1}))}))}}},u=d,c=r("2877"),l=r("9989"),p=r("ead5"),f=r("079e"),v=r("ddd8"),h=r("0016"),b=r("7cbe"),m=r("52ee"),g=r("66e5"),q=r("4074"),y=r("27f9"),$=r("9c40"),C=r("eebe"),O=r.n(C),w=Object(c["a"])(u,o,s,!1,null,null,null);t["default"]=w.exports;O()(w,"components",{QPage:l["a"],QBreadcrumbs:p["a"],QBreadcrumbsEl:f["a"],QSelect:v["a"],QIcon:h["a"],QPopupProxy:b["a"],QDate:m["a"],QItem:g["a"],QItemSection:q["a"],QInput:y["a"],QBtn:$["a"]})}}]);
(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[19],{"9c89":function(e,t,n){"use strict";n.r(t);var i=function(){var e=this,t=e.$createElement,n=e._self._c||t;return n("q-page",[n("div",{staticClass:"q-pa-sm panel-header"},[n("div",{staticClass:"row"},[n("div",{staticClass:"col-sm-3"},[n("div",{staticClass:"q-pa-md q-gutter-sm"},[n("q-breadcrumbs",{staticStyle:{"font-size":"20px"}},[n("q-breadcrumbs-el",{attrs:{label:"",icon:"home",to:"/"}}),n("q-breadcrumbs-el",{attrs:{label:"Reportes"}})],1)],1)])])]),n("div",{staticClass:"q-pa-md bg-grey-3"},[n("div",{staticClass:"col-md-12 col-xs-12 col-lg-12 bg-white"},[n("q-tabs",{staticClass:"text-grey-8",staticStyle:{size:"95%"},attrs:{dense:"","active-text-color":"light-green","active-color":"red","indicator-color":"red",align:"justify","narrow-indicator":""},on:{input:e.changeModel},model:{value:e.currentTab,callback:function(t){e.currentTab=t},expression:"currentTab"}},[n("q-tab",{attrs:{name:"clients",label:"CLIENTE",icon:"fas fa-shopping-cart"}}),n("q-tab",{attrs:{name:"collect",label:"UTILIDAD",icon:"receipt_long"}}),n("q-tab",{attrs:{name:"sales",label:"VENDEDOR",icon:"fas fa-handshake"}})],1)],1),n("div",{staticClass:"col"},[n("q-tab-panels",{attrs:{animated:""},model:{value:e.currentTab,callback:function(t){e.currentTab=t},expression:"currentTab"}},[n("q-tab-panel",{staticClass:"bg-grey-3",attrs:{name:"clients"}},[n("cliente")],1),n("q-tab-panel",{staticClass:"bg-grey-3",attrs:{name:"sales"}},[n("Vendedor")],1),n("q-tab-panel",{staticClass:"bg-grey-3",attrs:{name:"production"}}),n("q-tab-panel",{staticClass:"bg-grey-3",attrs:{name:"banks"}}),n("q-tab-panel",{staticClass:"bg-grey-3",attrs:{name:"management"}})],1)],1)])])},l=[],s=n("7ec2"),a=n.n(s),o=n("c973"),r=n.n(o),c=n("1321"),u=n.n(c),d=n("d087"),f=function(){var e=this,t=e.$createElement,n=e._self._c||t;return n("q-page",{attrs:{padding:""}},[n("div",{staticClass:"row q-col-gutter-xs"},[n("div",{staticClass:"col-xs-12 pull-right"},[n("div",[n("q-btn",{staticStyle:{"margin-left":"10px"},attrs:{color:"green",icon:"fas fa-file-excel"},on:{click:function(t){return e.generateCSV()}}},[n("q-tooltip",[e._v("GENERAR CSV")])],1),n("q-btn",{staticStyle:{"margin-left":"10px"},attrs:{color:"red",icon:"fas fa-file-pdf"},on:{click:function(t){return e.generatePDF()}}},[n("q-tooltip",[e._v("GENERAR PDF")])],1)],1)])]),n("div",{staticClass:"row q-col-gutter-xs",staticStyle:{"margin-top":"20px"}},[n("div",{staticClass:"col-md-3"},[n("q-select",{attrs:{color:"white","bg-color":"secondary",filled:"",mask:"date",label:"Fecha de inicio"},model:{value:e.clientes.fields.DateOf,callback:function(t){e.$set(e.clientes.fields,"DateOf",t)},expression:"clientes.fields.DateOf"}},[n("q-popup-proxy",{ref:"date1",attrs:{"transition-show":"scale","transition-hide":"scale"}},[n("div",{staticClass:"col-sm-12"},[n("q-date",{attrs:{"today-btn":""},on:{input:function(t){return e.fetchFromServer()}},model:{value:e.clientes.fields.DateOf,callback:function(t){e.$set(e.clientes.fields,"DateOf",t)},expression:"clientes.fields.DateOf"}})],1)])],1)],1),n("div",{staticClass:"col-md-3"},[n("q-select",{attrs:{color:"white","bg-color":"secondary",filled:"",mask:"date",label:"Fecha de fin"},model:{value:e.clientes.fields.DateUntil,callback:function(t){e.$set(e.clientes.fields,"DateUntil",t)},expression:"clientes.fields.DateUntil"}},[n("q-popup-proxy",{ref:"date2",attrs:{"transition-show":"scale","transition-hide":"scale"}},[n("div",{staticClass:"col-sm-12"},[n("q-date",{attrs:{"today-btn":""},on:{input:function(t){return e.fetchFromServer()}},model:{value:e.clientes.fields.DateUntil,callback:function(t){e.$set(e.clientes.fields,"DateUntil",t)},expression:"clientes.fields.DateUntil"}})],1)])],1)],1),n("div",{staticClass:"col-md-3"},[n("q-select",{attrs:{color:"white","bg-color":"secondary",filled:"","map-options":"",label:"Estación",options:e.branchesList},on:{input:function(t){return e.fetchFromServer()}},model:{value:e.clientes.fields.sucursal,callback:function(t){e.$set(e.clientes.fields,"sucursal",t)},expression:"clientes.fields.sucursal"}})],1),n("div",{staticClass:"col-md-3"},[n("q-select",{attrs:{filled:"",color:"dark","bg-color":"secondary",label:"Marca",options:e.options,"use-input":"","hide-selected":"","fill-input":"","input-debounce":"0","map-options":""},on:{filter:e.filterMarcas,input:function(t){return e.fetchFromServer()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[n("q-icon",{attrs:{name:"fas fa-grip-lines-vertical"}})]},proxy:!0},{key:"no-option",fn:function(){return[n("q-item",[n("q-item-section",{staticClass:"text-grey"},[e._v("\n              No hay Resultados\n            ")])],1)]},proxy:!0}]),model:{value:e.clientes.fields.marca,callback:function(t){e.$set(e.clientes.fields,"marca",t)},expression:"clientes.fields.marca"}})],1),n("div",{staticClass:"col-md-3"},[n("q-select",{attrs:{filled:"",color:"dark","bg-color":"secondary",label:"Subcategoría",options:e.options3,"use-input":"","hide-selected":"","fill-input":"","input-debounce":"0","map-options":""},on:{filter:e.filterLines,input:function(t){return e.fetchFromServer()}},model:{value:e.clientes.fields.linea,callback:function(t){e.$set(e.clientes.fields,"linea",t)},expression:"clientes.fields.linea"}})],1),n("div",{staticClass:"col-md-3"},[n("q-select",{attrs:{filled:"",color:"dark","bg-color":"secondary",label:"Producto",options:e.options4,"use-input":"","hide-selected":"","fill-input":"","input-debounce":"0","map-options":""},on:{filter:e.filterProduct,input:function(t){return e.fetchFromServer()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[n("q-icon",{attrs:{name:"fas fa-grip-lines-vertical"}})]},proxy:!0},{key:"no-option",fn:function(){return[n("q-item",[n("q-item-section",{staticClass:"text-grey"},[e._v("\n          No hay Resultados\n        ")])],1)]},proxy:!0}]),model:{value:e.clientes.fields.product,callback:function(t){e.$set(e.clientes.fields,"product",t)},expression:"clientes.fields.product"}})],1),n("div",{staticClass:"col-md-6"},[n("q-select",{attrs:{filled:"",color:"dark","bg-color":"secondary",label:"Cliente",options:e.options2,"use-input":"","hide-selected":"","fill-input":"","input-debounce":"0","map-options":""},on:{filter:e.filtrarClientes,input:function(t){return e.fetchFromServer()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[n("q-icon",{attrs:{name:"fas fa-grip-lines-vertical"}})]},proxy:!0},{key:"no-option",fn:function(){return[n("q-item",[n("q-item-section",{staticClass:"text-grey"},[e._v("\n            No hay Resultados\n          ")])],1)]},proxy:!0}]),model:{value:e.clientes.fields.cliente,callback:function(t){e.$set(e.clientes.fields,"cliente",t)},expression:"clientes.fields.cliente"}})],1)]),n("div",{staticClass:"row bg-white",staticStyle:{"margin-top":"20px"}},[n("div",{staticClass:"col q-pa-md"},[n("q-table",{attrs:{flat:"",bordered:"",data:e.data,columns:e.columns,"row-key":"sale_date",pagination:e.pagination,filter:e.filter},on:{"update:pagination":function(t){e.pagination=t},request:e.qTableRequest},scopedSlots:e._u([{key:"top",fn:function(){return[n("div",{staticStyle:{width:"100%"}},[n("q-input",{attrs:{dense:"",debounce:"300",placeholder:"Buscar"},on:{input:function(t){e.filter=t.toUpperCase()}},scopedSlots:e._u([{key:"append",fn:function(){return[n("q-icon",{attrs:{name:"search"}})]},proxy:!0}]),model:{value:e.filter,callback:function(t){e.filter=t},expression:"filter"}})],1)]},proxy:!0},{key:"body",fn:function(t){return[n("q-tr",{attrs:{props:t}},[n("q-td",{key:"factura",staticStyle:{"text-align":"center"},attrs:{props:t}},[n("label",{staticClass:"text-primary",staticStyle:{"text-decoration":"underline",cursor:"pointer"},on:{click:function(n){return e.openActions(t.row.factura)}}},[e._v(e._s(t.row.factura))])]),n("q-td",{key:"sale_date",attrs:{props:t}},[e._v(e._s(t.row.sale_date))]),n("q-td",{key:"sucursal",attrs:{props:t}},[e._v(e._s(t.row.sucursal))]),n("q-td",{key:"line",attrs:{props:t}},[e._v(e._s(t.row.line))]),n("q-td",{key:"cliente",attrs:{props:t}},[e._v(e._s(t.row.cliente))]),n("q-td",{key:"producto",attrs:{props:t}},[e._v(e._s(t.row.producto))]),n("q-td",{key:"qty",attrs:{props:t}},[e._v(e._s(t.row.qty))]),n("q-td",{key:"qty_price",attrs:{props:t}},[e._v(e._s(t.row.qty_price))]),n("q-td",{key:"qty_iva",attrs:{props:t}},[e._v(e._s(t.row.qty_iva))]),n("q-td",{key:"total",attrs:{props:t}},[e._v(e._s(t.row.total))])],1)]}}])})],1)])])},p=[],h=(n("4de4"),n("d3b7"),n("159b"),n("99af"),n("e260"),n("3ca3"),n("ddb0"),n("2b3d"),n("9861"),n("aabb")),m={name:"clientes",clientes:{fields:{DateOf:{},DateUntil:{},sucursal:{},marca:{},categoria:{},Linea:{},cliente:{},product:{}}},beforeRouteEnter:function(e,t,n){n((function(e){var t=e.$store.getters["users/user"];1===t||3===t||7===t||2===t||20===t||4===t||27===t||17===t||22===t||28===t||29===t||17===t?n():n("/")}))},data:function(){return{clientes:{fields:{DateOf:null,DateUntil:null,sucursal:null,marca:null,categoria:null,linea:null,cliente:null,product:null}},branchesList:[],categoryOptions:[],lineOptions:[],markOptions:[],familyOptions:[],unitOptions:[],claveProdOptions:[],productOptions:[],options:this.markOptions,options2:this.customerOptions,options3:this.lineOptions,options4:this.productOptions,customerOptions:[],filteredCustomerOptions:[],pagination:{sortBy:"id",descending:!0,page:1,rowsNumber:0,rowsPerPage:25},columns:[{name:"factura",align:"center",label:"REMISION",field:"factura",sortable:!0},{name:"sale_date",align:"center",label:"VENTA",field:"sale_date",sortable:!0},{name:"sucursal",align:"center",label:"ESTACIÓN",field:"sucursal",sortable:!0},{name:"line",align:"left",label:"SUBCATEGORÍA",field:"line",sortable:!0},{name:"cliente",align:"left",label:"CLIENTE",field:"cliente",sortable:!0},{name:"producto",align:"left",label:"PRODUCTO",field:"producto",sortable:!0},{name:"qty",align:"right",label:"CANTIDAD",field:"qty",sortable:!0},{name:"qty_price",align:"right",label:"IMPORTE",field:"qty_price",sortable:!0},{name:"qty_iva",align:"right",label:"IVA",field:"qty_iva",sortable:!0},{name:"total",align:"right",label:"TOTAL",field:"total",sortable:!0}],data:[],filter:""}},computed:{},created:function(){this.loadExpenses(),this.getBranchesList(),this.getMarks(),this.getCategories(),this.getLines(),this.getClients(),this.getAllProducts();var e=this.$store.getters["users/branch"],t=this.$store.getters["users/rol"];1!==t&&(9===e&&(this.clientes.fields.sucursal={value:e,label:"EMPRESA"}),12===e&&(this.clientes.fields.sucursal={value:e,label:"LOPEZ DE LARA TINAJERO GUILLERMO"}),13===e&&(this.clientes.fields.sucursal={value:e,label:"EMPRESA SA DE CV"}),14===e&&(this.clientes.fields.sucursal={value:e,label:"REBASA RODAMIENTOS Y MANGUERAS - RODAMIENTOS"})),this.fetchFromServer()},mounted:function(){},methods:{fetchFromServer:function(){this.qTableRequest({pagination:this.pagination,filter:this.filter})},qTableRequest:function(e){var t=this;return r()(a()().mark((function n(){var i;return a()().wrap((function(n){while(1)switch(n.prev=n.next){case 0:t.pagination=e.pagination,t.filter=e.filter,i=[],i.DateOf=t.clientes.fields.DateOf,i.DateUntil=t.clientes.fields.DateUntil,i.sucursal=t.clientes.fields.sucursal,i.marca=t.clientes.fields.marca,i.product=t.clientes.fields.product,i.linea=t.clientes.fields.linea,i.cliente=t.clientes.fields.cliente,i.pagination=t.pagination,i.filter=t.filter,h["a"].post("/reports/pagbyclients",i).then((function(e){var n=e.data;t.data=n.info,t.pagination.rowsNumber=n.infoCount})).catch((function(e){return e}));case 13:case"end":return n.stop()}}),n)})))()},loadExpenses:function(){return r()(a()().mark((function e(){return a()().wrap((function(e){while(1)switch(e.prev=e.next){case 0:case"end":return e.stop()}}),e)})))()},loadExpensesFilter:function(){return r()(a()().mark((function e(){return a()().wrap((function(e){while(1)switch(e.prev=e.next){case 0:case"end":return e.stop()}}),e)})))()},filtrarClientes:function(e,t,n){var i=this;t((function(){var t=e.toLowerCase();i.options2=i.customerOptions.filter((function(e){return e.label.toLowerCase().indexOf(t)>-1}))}))},filterMarcas:function(e,t,n){var i=this;t((function(){var t=e.toLowerCase();i.options=i.markOptions.filter((function(e){return e.label.toLowerCase().indexOf(t)>-1}))}))},filterProduct:function(e,t,n){var i=this,l=[];this.productOptions.forEach((function(e){l.push({label:e.label,value:e.id})})),t((function(){var t=e.toLowerCase();i.options4=l.filter((function(e){return e.label.toLowerCase().indexOf(t)>-1}))}))},filterLines:function(e,t,n){var i=this;t((function(){var t=e.toLowerCase();i.options3=i.lineOptions.filter((function(e){return e.label.toLowerCase().indexOf(t)>-1}))}))},getCategories:function(){var e=this;h["a"].get("/categories/options").then((function(t){var n=t.data;e.categoryOptions=n.options,e.categoryOptions.push({label:"TODAS",value:"TODAS"})}))},getLines:function(){var e=this;h["a"].get("/lines/options").then((function(t){var n=t.data;e.lineOptions=n.options,e.lineOptions.push({label:"TODAS",value:"TODAS"})}))},getMarks:function(){var e=this;h["a"].get("/marks/options").then((function(t){var n=t.data;e.markOptions=n.options,e.markOptions.push({label:"TODAS",value:"TODAS"})}))},getBranchesList:function(){var e=this;h["a"].get("branch-offices/options").then((function(t){e.branchesList=t.data.options,e.branchesList.push({label:"TODAS",value:"TODAS"})}))},getClients:function(){var e=this;h["a"].get("/customers/options").then((function(t){var n=t.data;e.customerOptions=n.options,e.customerOptions.push({label:"TODOS",value:"TODOS"})}))},getAllProducts:function(){var e=this;h["a"].get("/products/category2").then((function(t){var n=t.data;e.productOptions=n.products,e.productOptions.push({label:"TODOS",value:"TODOS"})}))},openActions:function(e){this.$router.push("/storage-exits/".concat(e))},generateCSV:function(){var e=this,t=[];null!==this.clientes.fields.DateOf?t.DateOf=this.$formatDate(this.clientes.fields.DateOf):t.DateOf=null,this.clientes.fields.DateUntil?t.DateUntil=this.$formatDate(this.clientes.fields.DateUntil):t.DateUntil=null,null!=this.clientes.fields.sucursal?t.sucursal=this.clientes.fields.sucursal.value:t.sucursal=null,null!=this.clientes.fields.narca?t.marca=this.clientes.fields.marca.value:t.marca=null,null!=this.clientes.fields.product?t.product=this.clientes.fields.product.value:t.product=null,null!=this.clientes.fields.linea?t.linea=this.clientes.fields.linea.value:t.linea=null,null!=this.clientes.fields.cliente?t.cliente=this.clientes.fields.cliente.value:t.cliente=null;var n="https://api_alpez.wasp.mx/"+"reports/getCsvpagbyclients/".concat(t.DateOf,"/").concat(t.DateUntil,"/").concat(t.sucursal,"/").concat(t.marca,"/").concat(t.product,"/").concat(t.linea,"/").concat(t.cliente);this.$q.loading.show(),h["a"].fileDownload(n).then((function(t){var n=t.data,i=window.URL.createObjectURL(new Blob([n],{type:"application/csv"})),l=document.createElement("a");l.href=i,l.setAttribute("download","ReporteClientes.csv"),document.body.appendChild(l),e.$q.loading.hide(),l.click()}))},generatePDF:function(){var e=this,t=[];null!==this.clientes.fields.DateOf?t.DateOf=this.$formatDate(this.clientes.fields.DateOf):t.DateOf=null,this.clientes.fields.DateUntil?t.DateUntil=this.$formatDate(this.clientes.fields.DateUntil):t.DateUntil=null,null!=this.clientes.fields.sucursal?t.sucursal=this.clientes.fields.sucursal.value:t.sucursal=null,null!=this.clientes.fields.narca?t.marca=this.clientes.fields.marca.value:t.marca=null,null!=this.clientes.fields.product?t.product=this.clientes.fields.product.value:t.product=null,null!=this.clientes.fields.linea?t.linea=this.clientes.fields.linea.value:t.linea=null,null!=this.clientes.fields.cliente?t.cliente=this.clientes.fields.cliente.value:t.cliente=null;var n="https://api_alpez.wasp.mx/"+"reports/getPdfpagbyclients/".concat(t.DateOf,"/").concat(t.DateUntil,"/").concat(t.sucursal,"/").concat(t.marca,"/").concat(t.product,"/").concat(t.linea,"/").concat(t.cliente);this.$q.loading.show(),h["a"].fileDownload(n).then((function(t){var n=t.data,i=window.URL.createObjectURL(new Blob([n],{type:"application/pdf"})),l=document.createElement("a");l.href=i,l.setAttribute("download","ReporteClientes.pdf"),document.body.appendChild(l),e.$q.loading.hide(),l.click()}))}}},b=m,v=n("2877"),g=n("9989"),y=n("9c40"),O=n("05c0"),q=n("ddd8"),D=n("7cbe"),w=n("52ee"),C=n("0016"),k=n("66e5"),x=n("4074"),S=n("eaac"),_=n("27f9"),A=n("bd08"),T=n("db86"),E=n("eebe"),R=n.n(E),L=Object(v["a"])(b,f,p,!1,null,null,null),U=L.exports;R()(L,"components",{QPage:g["a"],QBtn:y["a"],QTooltip:O["a"],QSelect:q["a"],QPopupProxy:D["a"],QDate:w["a"],QIcon:C["a"],QItem:k["a"],QItemSection:x["a"],QTable:S["a"],QInput:_["a"],QTr:A["a"],QTd:T["a"]});var $=function(){var e=this,t=e.$createElement,n=e._self._c||t;return n("q-page",{attrs:{padding:""}},[n("div",{staticClass:"row q-col-gutter-xs"},[n("div",{staticClass:"col-xs-12 pull-right"},[n("div",[n("q-btn",{staticStyle:{"margin-left":"10px"},attrs:{color:"green",icon:"fas fa-file-excel"},on:{click:function(t){return e.generateCSV()}}},[n("q-tooltip",[e._v("GENERAR CSV")])],1),n("q-btn",{staticStyle:{"margin-left":"10px"},attrs:{color:"red",icon:"fas fa-file-pdf"},on:{click:function(t){return e.generatePDF()}}},[n("q-tooltip",[e._v("GENERAR PDF")])],1)],1)])]),n("div",{staticClass:"row q-col-gutter-xs",staticStyle:{"margin-top":"20px"}},[n("div",{staticClass:"col-md-3"},[n("q-select",{attrs:{color:"white","bg-color":"secondary",filled:"",mask:"date",label:"Fecha de inicio"},model:{value:e.clientes.fields.DateOf,callback:function(t){e.$set(e.clientes.fields,"DateOf",t)},expression:"clientes.fields.DateOf"}},[n("q-popup-proxy",{ref:"date1",attrs:{"transition-show":"scale","transition-hide":"scale"}},[n("div",{staticClass:"col-sm-12"},[n("q-date",{attrs:{"today-btn":""},on:{input:function(t){return e.fetchFromServer()}},model:{value:e.clientes.fields.DateOf,callback:function(t){e.$set(e.clientes.fields,"DateOf",t)},expression:"clientes.fields.DateOf"}})],1)])],1)],1),n("div",{staticClass:"col-md-3"},[n("q-select",{attrs:{color:"white","bg-color":"secondary",filled:"",mask:"date",label:"Fecha de fin"},model:{value:e.clientes.fields.DateUntil,callback:function(t){e.$set(e.clientes.fields,"DateUntil",t)},expression:"clientes.fields.DateUntil"}},[n("q-popup-proxy",{ref:"date2",attrs:{"transition-show":"scale","transition-hide":"scale"}},[n("div",{staticClass:"col-sm-12"},[n("q-date",{attrs:{"today-btn":""},on:{input:function(t){return e.fetchFromServer()}},model:{value:e.clientes.fields.DateUntil,callback:function(t){e.$set(e.clientes.fields,"DateUntil",t)},expression:"clientes.fields.DateUntil"}})],1)])],1)],1),n("div",{staticClass:"col-md-3"},[n("q-select",{attrs:{color:"white","bg-color":"secondary",filled:"","map-options":"",label:"Estación",options:e.branchesList},on:{input:function(t){return e.fetchFromServer()}},model:{value:e.clientes.fields.sucursal,callback:function(t){e.$set(e.clientes.fields,"sucursal",t)},expression:"clientes.fields.sucursal"}})],1),n("div",{staticClass:"col-md-3"},[n("q-select",{attrs:{filled:"",color:"dark","bg-color":"secondary",label:"Marca",options:e.options,"use-input":"","hide-selected":"","fill-input":"","input-debounce":"0","map-options":""},on:{filter:e.filterMarcas,input:function(t){return e.fetchFromServer()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[n("q-icon",{attrs:{name:"fas fa-grip-lines-vertical"}})]},proxy:!0},{key:"no-option",fn:function(){return[n("q-item",[n("q-item-section",{staticClass:"text-grey"},[e._v("\n              No hay Resultados\n            ")])],1)]},proxy:!0}]),model:{value:e.clientes.fields.marca,callback:function(t){e.$set(e.clientes.fields,"marca",t)},expression:"clientes.fields.marca"}})],1),n("div",{staticClass:"col-md-3"},[n("q-select",{attrs:{filled:"",color:"dark","bg-color":"secondary",label:"Subcategoría",options:e.options3,"use-input":"","hide-selected":"","fill-input":"","input-debounce":"0","map-options":""},on:{filter:e.filterLines,input:function(t){return e.fetchFromServer()}},model:{value:e.clientes.fields.linea,callback:function(t){e.$set(e.clientes.fields,"linea",t)},expression:"clientes.fields.linea"}})],1),n("div",{staticClass:"col-md-3"},[n("q-select",{attrs:{filled:"",color:"dark","bg-color":"secondary",label:"Producto",options:e.options4,"use-input":"","hide-selected":"","fill-input":"","input-debounce":"0","map-options":""},on:{filter:e.filterProduct,input:function(t){return e.fetchFromServer()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[n("q-icon",{attrs:{name:"fas fa-grip-lines-vertical"}})]},proxy:!0},{key:"no-option",fn:function(){return[n("q-item",[n("q-item-section",{staticClass:"text-grey"},[e._v("\n          No hay Resultados\n        ")])],1)]},proxy:!0}]),model:{value:e.clientes.fields.product,callback:function(t){e.$set(e.clientes.fields,"product",t)},expression:"clientes.fields.product"}})],1),n("div",{staticClass:"col-md-6"},[n("q-select",{attrs:{filled:"",color:"dark","bg-color":"secondary",label:"Cliente",options:e.optionsClients,"use-input":"","hide-selected":"","fill-input":"","input-debounce":"0","map-options":""},on:{filter:e.filtrarClientes,input:function(t){return e.fetchFromServer()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[n("q-icon",{attrs:{name:"fas fa-grip-lines-vertical"}})]},proxy:!0},{key:"no-option",fn:function(){return[n("q-item",[n("q-item-section",{staticClass:"text-grey"},[e._v("\n            No hay Resultados\n          ")])],1)]},proxy:!0}]),model:{value:e.clientes.fields.cliente,callback:function(t){e.$set(e.clientes.fields,"cliente",t)},expression:"clientes.fields.cliente"}})],1),n("div",{staticClass:"col-md-6"},[n("q-select",{attrs:{filled:"",color:"dark","bg-color":"secondary",label:"Vendedor",options:e.options2,"use-input":"","hide-selected":"","fill-input":"","input-debounce":"0","map-options":""},on:{filter:e.filtrarSeller,input:function(t){return e.fetchFromServer()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[n("q-icon",{attrs:{name:"fas fa-grip-lines-vertical"}})]},proxy:!0},{key:"no-option",fn:function(){return[n("q-item",[n("q-item-section",{staticClass:"text-grey"},[e._v("\n            No hay Resultados\n          ")])],1)]},proxy:!0}]),model:{value:e.clientes.fields.seller,callback:function(t){e.$set(e.clientes.fields,"seller",t)},expression:"clientes.fields.seller"}})],1)]),n("div",{staticClass:"row bg-white",staticStyle:{"margin-top":"20px"}},[n("div",{staticClass:"col q-pa-md"},[n("q-table",{attrs:{flat:"",bordered:"",data:e.data,columns:e.columns,"row-key":"sale_date",pagination:e.pagination,filter:e.filter},on:{"update:pagination":function(t){e.pagination=t},request:e.qTableRequest},scopedSlots:e._u([{key:"top",fn:function(){return[n("div",{staticStyle:{width:"100%"}},[n("q-input",{attrs:{dense:"",debounce:"300",placeholder:"Buscar"},on:{input:function(t){e.filter=t.toUpperCase()}},scopedSlots:e._u([{key:"append",fn:function(){return[n("q-icon",{attrs:{name:"search"}})]},proxy:!0}]),model:{value:e.filter,callback:function(t){e.filter=t},expression:"filter"}})],1)]},proxy:!0},{key:"body",fn:function(t){return[n("q-tr",{attrs:{props:t}},[n("q-td",{key:"factura",staticStyle:{"text-align":"center"},attrs:{props:t}},[n("label",{staticClass:"text-primary",staticStyle:{"text-decoration":"underline",cursor:"pointer"},on:{click:function(n){return e.openActions(t.row.factura)}}},[e._v(e._s(t.row.factura))])]),n("q-td",{key:"sale_date",attrs:{props:t}},[e._v(e._s(t.row.sale_date))]),n("q-td",{key:"sucursal",attrs:{props:t}},[e._v(e._s(t.row.sucursal))]),n("q-td",{key:"line",attrs:{props:t}},[e._v(e._s(t.row.line))]),n("q-td",{key:"seller",attrs:{props:t}},[e._v(e._s(t.row.vendedor))]),n("q-td",{key:"cliente",attrs:{props:t}},[e._v(e._s(t.row.cliente))]),n("q-td",{key:"producto",attrs:{props:t}},[e._v(e._s(t.row.producto))]),n("q-td",{key:"qty",attrs:{props:t}},[e._v(e._s(t.row.qty))]),n("q-td",{key:"qty_price",attrs:{props:t}},[e._v(e._s(t.row.qty_price))]),n("q-td",{key:"qty_iva",attrs:{props:t}},[e._v(e._s(t.row.qty_iva))]),n("q-td",{key:"total",attrs:{props:t}},[e._v(e._s(t.row.total))])],1)]}}])})],1)])])},P=[],F={name:"clientes",clientes:{fields:{DateOf:{},DateUntil:{},sucursal:{},marca:{},categoria:{},Linea:{},cliente:{},product:{}}},beforeRouteEnter:function(e,t,n){n((function(e){var t=e.$store.getters["users/user"];1===t||3===t||7===t||2===t||20===t||4===t||27===t||17===t||22===t||28===t||29===t||17===t?n():n("/")}))},data:function(){return{clientes:{fields:{DateOf:null,DateUntil:null,sucursal:null,marca:null,categoria:null,linea:null,seller:null,product:null,cliente:null}},optionsClients:[],optionsClientsFilter:[],branchesList:[],categoryOptions:[],lineOptions:[],markOptions:[],familyOptions:[],unitOptions:[],claveProdOptions:[],productOptions:[],options:this.markOptions,options2:this.customerOptions,options3:this.lineOptions,options4:this.productOptions,customerOptions:[],filteredCustomerOptions:[],pagination:{sortBy:"id",descending:!0,page:1,rowsNumber:0,rowsPerPage:25},columns:[{name:"factura",align:"center",label:"REMISION",field:"factura",sortable:!0},{name:"sale_date",align:"center",label:"VENTA",field:"sale_date",sortable:!0},{name:"sucursal",align:"center",label:"ESTACIÓN",field:"sucursal",sortable:!0},{name:"line",align:"left",label:"SUBCATEGORÍA",field:"line",sortable:!0},{name:"seller",align:"left",label:"VENDEDOR",field:"seller",sortable:!0},{name:"cliente",align:"left",label:"CLIENTE",field:"cliente",sortable:!0},{name:"producto",align:"left",label:"PRODUCTO",field:"producto",sortable:!0},{name:"qty",align:"right",label:"CANTIDAD",field:"qty",sortable:!0},{name:"qty_price",align:"right",label:"IMPORTE",field:"qty_price",sortable:!0},{name:"qty_iva",align:"right",label:"IVA",field:"qty_iva",sortable:!0},{name:"total",align:"right",label:"TOTAL",field:"total",sortable:!0}],data:[],filter:""}},computed:{},created:function(){this.loadExpenses(),this.getBranchesList(),this.getMarks(),this.getCategories(),this.getLines(),this.getClients(),this.getOptionsClients(),this.getAllProducts();var e=this.$store.getters["users/branch"],t=this.$store.getters["users/rol"];1!==t&&(9===e&&(this.clientes.fields.sucursal={value:e,label:"EMPRESA"}),12===e&&(this.clientes.fields.sucursal={value:e,label:"LOPEZ DE LARA TINAJERO GUILLERMO"}),13===e&&(this.clientes.fields.sucursal={value:e,label:"EMPRESA SA DE CV"}),14===e&&(this.clientes.fields.sucursal={value:e,label:"REBASA RODAMIENTOS Y MANGUERAS - RODAMIENTOS"})),this.fetchFromServer()},mounted:function(){},methods:{fetchFromServer:function(){this.qTableRequest({pagination:this.pagination,filter:this.filter})},qTableRequest:function(e){var t=this;return r()(a()().mark((function n(){var i;return a()().wrap((function(n){while(1)switch(n.prev=n.next){case 0:t.pagination=e.pagination,t.filter=e.filter,i=[],i.DateOf=t.clientes.fields.DateOf,i.DateUntil=t.clientes.fields.DateUntil,i.sucursal=t.clientes.fields.sucursal,i.marca=t.clientes.fields.marca,i.product=t.clientes.fields.product,i.linea=t.clientes.fields.linea,i.seller=t.clientes.fields.seller,i.cliente=t.clientes.fields.cliente,i.pagination=t.pagination,i.filter=t.filter,h["a"].post("/reports/pagbySellers",i).then((function(e){var n=e.data;t.data=n.info,t.pagination.rowsNumber=n.infoCount})).catch((function(e){return e}));case 14:case"end":return n.stop()}}),n)})))()},loadExpenses:function(){return r()(a()().mark((function e(){return a()().wrap((function(e){while(1)switch(e.prev=e.next){case 0:case"end":return e.stop()}}),e)})))()},loadExpensesFilter:function(){return r()(a()().mark((function e(){return a()().wrap((function(e){while(1)switch(e.prev=e.next){case 0:case"end":return e.stop()}}),e)})))()},getOptionsClients:function(){var e=this;return r()(a()().mark((function t(){return a()().wrap((function(t){while(1)switch(t.prev=t.next){case 0:return t.next=2,h["a"].get("/customers/options").then((function(t){var n=t.data;e.optionsClientsFilter=n.options,e.optionsClientsFilter.push({label:"TODOS",value:"TODOS"})}));case 2:case"end":return t.stop()}}),t)})))()},filtrarClientes:function(e,t,n){var i=this;t((function(){var t=e.toLowerCase();i.optionsClients=i.optionsClientsFilter.filter((function(e){return e.label.toLowerCase().indexOf(t)>-1}))}))},filtrarSeller:function(e,t,n){var i=this;t((function(){var t=e.toLowerCase();i.options2=i.customerOptions.filter((function(e){return e.label.toLowerCase().indexOf(t)>-1}))}))},filterMarcas:function(e,t,n){var i=this;t((function(){var t=e.toLowerCase();i.options=i.markOptions.filter((function(e){return e.label.toLowerCase().indexOf(t)>-1}))}))},filterProduct:function(e,t,n){var i=this,l=[];this.productOptions.forEach((function(e){l.push({label:e.label,value:e.id})})),t((function(){var t=e.toLowerCase();i.options4=l.filter((function(e){return e.label.toLowerCase().indexOf(t)>-1}))}))},filterLines:function(e,t,n){var i=this;t((function(){var t=e.toLowerCase();i.options3=i.lineOptions.filter((function(e){return e.label.toLowerCase().indexOf(t)>-1}))}))},getCategories:function(){var e=this;h["a"].get("/categories/options").then((function(t){var n=t.data;e.categoryOptions=n.options,e.categoryOptions.push({label:"TODAS",value:"TODAS"})}))},getLines:function(){var e=this;h["a"].get("/lines/options").then((function(t){var n=t.data;e.lineOptions=n.options,e.lineOptions.push({label:"TODAS",value:"TODAS"})}))},getMarks:function(){var e=this;h["a"].get("/marks/options").then((function(t){var n=t.data;e.markOptions=n.options,e.markOptions.push({label:"TODAS",value:"TODAS"})}))},getBranchesList:function(){var e=this;h["a"].get("branch-offices/options").then((function(t){e.branchesList=t.data.options,e.branchesList.push({label:"TODAS",value:"TODAS"})}))},getClients:function(){var e=this;h["a"].get("/users/sellers").then((function(t){var n=t.data;e.customerOptions=n.sellers,e.customerOptions.push({label:"TODOS",value:"TODOS"})}))},getAllProducts:function(){var e=this;h["a"].get("/products/category2").then((function(t){var n=t.data;e.productOptions=n.products,e.productOptions.push({label:"TODOS",value:"TODOS"})}))},openActions:function(e){this.$router.push("/storage-exits/".concat(e))},generateCSV:function(){var e=this,t=[];null!==this.clientes.fields.DateOf?t.DateOf=this.$formatDate(this.clientes.fields.DateOf):t.DateOf=null,this.clientes.fields.DateUntil?t.DateUntil=this.$formatDate(this.clientes.fields.DateUntil):t.DateUntil=null,null!=this.clientes.fields.sucursal?t.sucursal=this.clientes.fields.sucursal.value:t.sucursal=null,null!=this.clientes.fields.narca?t.marca=this.clientes.fields.marca.value:t.marca=null,null!=this.clientes.fields.product?t.product=this.clientes.fields.product.value:t.product=null,null!=this.clientes.fields.linea?t.linea=this.clientes.fields.linea.value:t.linea=null,null!=this.clientes.fields.seller?t.seller=this.clientes.fields.seller.value:t.seller=null,null!=this.clientes.fields.cliente?t.cliente=this.clientes.fields.cliente.value:t.cliente=null;var n="https://api_alpez.wasp.mx/"+"reports/getCsvpagbySeller/".concat(t.DateOf,"/").concat(t.DateUntil,"/").concat(t.sucursal,"/").concat(t.marca,"/").concat(t.product,"/").concat(t.linea,"/").concat(t.seller,"/").concat(t.cliente);this.$q.loading.show(),h["a"].fileDownload(n).then((function(t){var n=t.data,i=window.URL.createObjectURL(new Blob([n],{type:"application/csv"})),l=document.createElement("a");l.href=i,l.setAttribute("download","ReporteVendedor.csv"),document.body.appendChild(l),e.$q.loading.hide(),l.click()}))},generatePDF:function(){var e=this,t=[];null!==this.clientes.fields.DateOf?t.DateOf=this.$formatDate(this.clientes.fields.DateOf):t.DateOf=null,this.clientes.fields.DateUntil?t.DateUntil=this.$formatDate(this.clientes.fields.DateUntil):t.DateUntil=null,null!=this.clientes.fields.sucursal?t.sucursal=this.clientes.fields.sucursal.value:t.sucursal=null,null!=this.clientes.fields.narca?t.marca=this.clientes.fields.marca.value:t.marca=null,null!=this.clientes.fields.product?t.product=this.clientes.fields.product.value:t.product=null,null!=this.clientes.fields.linea?t.linea=this.clientes.fields.linea.value:t.linea=null,null!=this.clientes.fields.seller?t.seller=this.clientes.fields.seller.value:t.seller=null,null!=this.clientes.fields.cliente?t.cliente=this.clientes.fields.cliente.value:t.cliente=null;var n="https://api_alpez.wasp.mx/"+"reports/getPdfpagbySeller/".concat(t.DateOf,"/").concat(t.DateUntil,"/").concat(t.sucursal,"/").concat(t.marca,"/").concat(t.product,"/").concat(t.linea,"/").concat(t.seller,"/").concat(t.cliente);this.$q.loading.show(),h["a"].fileDownload(n).then((function(t){var n=t.data,i=window.URL.createObjectURL(new Blob([n],{type:"application/pdf"})),l=document.createElement("a");l.href=i,l.setAttribute("download","ReporteVendedor.pdf"),document.body.appendChild(l),e.$q.loading.hide(),l.click()}))}}},N=F,I=Object(v["a"])(N,$,P,!1,null,null,null),Q=I.exports;R()(I,"components",{QPage:g["a"],QBtn:y["a"],QTooltip:O["a"],QSelect:q["a"],QPopupProxy:D["a"],QDate:w["a"],QIcon:C["a"],QItem:k["a"],QItemSection:x["a"],QTable:S["a"],QInput:_["a"],QTr:A["a"],QTd:T["a"]}),d["a"].use(u.a),d["a"].component("apexchart",u.a);var M={name:"Dashboard",components:{cliente:U,Vendedor:Q},data:function(){return{currencyFormatter:new Intl.NumberFormat("en-US",{style:"currency",currency:"USD"}),startDate:null,currentTab:"sales",ocAmount:0,productsAmount:0,entryAmount:0,purchasesInArribo:0,weeklyArribos:0,inventaryCostAmount:0,inventaryCostAmountAVG:0,loadingDaily:!1,loadingMonthly:!0,pendientes:0,autorizados:0,enviados:0,nuevos:0,solicitados:0,parciales:0,entregados:0,actualmonth:0}},computed:{},beforeRouteEnter:function(e,t,n){n((function(e){var t=e.$store.getters["users/rol"];console.log(t),1===t||3===t||7===t||2===t||20===t||4===t||27===t||17===t||22===t||28===t||29===t||17===t?n():n("/")}))},created:function(){},mounted:function(){},methods:{changeModel:function(e){},loadCommercial:function(){return r()(a()().mark((function e(){return a()().wrap((function(e){while(1)switch(e.prev=e.next){case 0:case"end":return e.stop()}}),e)})))()},loadExpenses:function(){return r()(a()().mark((function e(){return a()().wrap((function(e){while(1)switch(e.prev=e.next){case 0:case"end":return e.stop()}}),e)})))()}}},B=M,V=(n("c703"),n("ead5")),G=n("079e"),j=n("429b"),z=n("7460"),J=n("adad"),Y=n("823b"),Z=Object(v["a"])(B,i,l,!1,null,"236e0dae",null);t["default"]=Z.exports;R()(Z,"components",{QPage:g["a"],QBreadcrumbs:V["a"],QBreadcrumbsEl:G["a"],QTabs:j["a"],QTab:z["a"],QTabPanels:J["a"],QTabPanel:Y["a"]})},a6f5:function(e,t,n){},c703:function(e,t,n){"use strict";n("a6f5")}}]);
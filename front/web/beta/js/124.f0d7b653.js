(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[124],{cfcf:function(e,t,a){"use strict";a.r(t);a("4de4"),a("d3b7");var r=function(){var e=this,t=e._self._c;return t("q-page",{staticClass:"bg-grey-3"},[t("div",{staticClass:"q-pa-sm panel-header"},[t("div",{staticClass:"row"},[t("div",{staticClass:"col-sm-6"},[t("div",{staticClass:"q-pa-md q-gutter-sm"},[t("q-breadcrumbs",{staticStyle:{"font-size":"20px"},attrs:{align:"left"}},[t("q-breadcrumbs-el",{attrs:{label:"",icon:"home",to:"/"}}),t("q-breadcrumbs-el",{attrs:{label:"Compras de proveedor"}})],1)],1)]),t("div",{staticClass:"col-xs-12 col-md-6 pull-right"},[t("div",{staticClass:"q-pa-sm q-gutter-sm"},[t("q-btn",{staticStyle:{"margin-left":"10px"},attrs:{color:"green",icon:"fas fa-file-excel"},on:{click:function(t){return e.generateCSV()}}},[t("q-tooltip",[e._v("GENERAR CSV")])],1),t("q-btn",{staticStyle:{"margin-left":"10px"},attrs:{color:"red",icon:"fas fa-file-pdf"},on:{click:function(t){return e.generatePDF()}}},[t("q-tooltip",[e._v("GENERAR PDF")])],1)],1)])])]),t("div",{staticClass:"q-pa-md bg-grey-3"},[t("div",{staticClass:"row bg-white border-panel"},[t("div",{staticClass:"col q-pa-md"},[t("div",{staticClass:"row bg-white q-pb-md q-col-gutter-xs q-col-gutter-md"},[t("div",{staticClass:"col-sm-2"},[t("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",mask:"dd/mm/Y",label:"Desde"},model:{value:e.saleDatev1,callback:function(t){e.saleDatev1=t},expression:"saleDatev1"}},[t("q-popup-proxy",{ref:"date",attrs:{"transition-show":"scale","transition-hide":"scale"}},[t("div",{staticClass:"col-sm-12"},[t("q-date",{attrs:{"today-btn":""},on:{input:function(t){return e.fetchFromServer()}},model:{value:e.saleDatev1,callback:function(t){e.saleDatev1=t},expression:"saleDatev1"}})],1)])],1)],1),t("div",{staticClass:"col-md-2"},[t("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",mask:"date",label:"Hasta"},model:{value:e.saleDatev2,callback:function(t){e.saleDatev2=t},expression:"saleDatev2"}},[t("q-popup-proxy",{ref:"date",attrs:{"transition-show":"scale","transition-hide":"scale"}},[t("div",{staticClass:"col-sm-12"},[t("q-date",{attrs:{"today-btn":""},on:{input:function(t){return e.fetchFromServer()}},model:{value:e.saleDatev2,callback:function(t){e.saleDatev2=t},expression:"saleDatev2"}})],1)])],1)],1),t("div",{staticClass:"col-md-3"},[t("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",options:e.filtrarOfficeBranch,"use-input":"","hide-selected":"","fill-input":"","input-debounce":"0",label:"Sucursal","emit-value":"","map-options":""},on:{filter:e.filteredOfficeBranch,input:function(t){return e.fetchFromServer()}},model:{value:e.officeBranch,callback:function(t){e.officeBranch=t},expression:"officeBranch"}})],1),t("div",{staticClass:"col-md-3"},[t("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",options:e.filteredSupplierOptions,"use-input":"","hide-selected":"","fill-input":"","input-debounce":"0",label:"Proveedor","emit-value":"","map-options":""},on:{filter:e.filtrarSupplier,input:function(t){return e.fetchFromServer()}},model:{value:e.supplier,callback:function(t){e.supplier=t},expression:"supplier"}})],1)]),t("div",{staticClass:"row q-col-gutter-xs"},[t("div",{staticClass:"col-xs-12 col-sm-12"},[t("q-table",{attrs:{flat:"",bordered:"",data:e.datashoppingsuppliers,columns:e.columns,"row-key":"serial",pagination:e.pagination,filter:e.filter},on:{"update:pagination":function(t){e.pagination=t}},scopedSlots:e._u([{key:"top",fn:function(){return[t("div",{staticStyle:{width:"100%"}},[t("q-input",{attrs:{dense:"",debounce:"300",placeholder:"Buscar"},on:{input:function(t){e.filter=t.toUpperCase()}},scopedSlots:e._u([{key:"append",fn:function(){return[t("q-icon",{attrs:{name:"search"}})]},proxy:!0}]),model:{value:e.filter,callback:function(t){e.filter=t},expression:"filter"}})],1)]},proxy:!0},{key:"body",fn:function(a){return[t("q-tr",{attrs:{props:a}},[t("q-td",{key:"supplier",staticStyle:{"text-align":"left"},attrs:{props:a}},[e._v(e._s(a.row.supplier))]),t("q-td",{key:"total",staticStyle:{"text-align":"right"},attrs:{props:a}},[e._v(e._s(a.row.total))])],1)]}}])})],1)])])])])])},i=[],n=a("7ec2"),s=a.n(n),l=a("c973"),o=a.n(l),c=(a("99af"),a("3c65"),a("aabb")),p={name:"CrmIndex",data:function(){return{saleDatev1:null,saleDatev2:null,supplier:"TODOS",filteredSupplierOptions:[],supplierOptions:[],datashoppingsuppliers:[],columns:[{name:"supplier",align:"center",label:"PROVEEDOR",field:"supplier",sortable:!1},{name:"total",align:"center",label:"TOTAL",field:"total",sortable:!1}],filter:"",pagination:{page:1,rowsPerPage:25},filtrarOfficeBranch:[],filteredOfficeBranchOptions:[],officeBranch:"TODOS"}},mounted:function(){this.getSuppliers(),this.getBranchOfficesToReportShopping(),this.fetchFromServer()},methods:{generateCSV:function(){var e=null!==this.saleDatev1?this.$formatDate(this.saleDatev1):null,t=null!==this.saleDatev2?this.$formatDate(this.saleDatev2):null,a=this.supplier,r=this.officeBranch,i="http://api.alpez.beta.wasp.mx/"+"purchase-order-details/getReportShoppingToCSVShoppingSupplier/".concat(a,"/").concat(e,"/").concat(t,"/").concat(r);window.open(i,"_blank")},generatePDF:function(){var e=null!==this.saleDatev1?this.$formatDate(this.saleDatev1):null,t=null!==this.saleDatev2?this.$formatDate(this.saleDatev2):null,a=this.supplier,r=this.officeBranch,i="http://api.alpez.beta.wasp.mx/"+"purchase-order-details/shoppingOfSuppliersPDF/".concat(a,"/").concat(e,"/").concat(t,"/").concat(r);window.open(i,"_blank")},getSuppliers:function(){var e=this;return o()(s()().mark((function t(){return s()().wrap((function(t){while(1)switch(t.prev=t.next){case 0:return t.next=2,c["a"].get("/suppliers/getSuppliersToReportSales").then((function(t){var a=t.data;a.result&&(e.supplierOptions=a.suppliers,e.supplierOptions.unshift({label:"TODOS",value:"TODOS"}))}));case 2:case"end":return t.stop()}}),t)})))()},getBranchOfficesToReportShopping:function(){var e=this;return o()(s()().mark((function t(){return s()().wrap((function(t){while(1)switch(t.prev=t.next){case 0:return t.next=2,c["a"].get("/branch-offices/getBranchOfficesToReportShopping").then((function(t){var a=t.data;a.result&&(e.filteredOfficeBranchOptions=a.branch,e.filteredOfficeBranchOptions.unshift({label:"TODOS",value:"TODOS"}))}));case 2:case"end":return t.stop()}}),t)})))()},filteredOfficeBranch:function(e,t,a){var r=this;t((function(){var t=e.toLowerCase();r.filtrarOfficeBranch=r.filteredOfficeBranchOptions.filter((function(e){return e.label.toLowerCase().indexOf(t)>-1}))}))},filtrarSupplier:function(e,t,a){var r=this;t((function(){var t=e.toLowerCase();r.filteredSupplierOptions=r.supplierOptions.filter((function(e){return e.label.toLowerCase().indexOf(t)>-1}))}))},fetchFromServer:function(){var e=this;return o()(s()().mark((function t(){return s()().wrap((function(t){while(1)switch(t.prev=t.next){case 0:e.qTableRequest({pagination:e.pagination,filter:e.filter});case 1:case"end":return t.stop()}}),t)})))()},qTableRequest:function(e){var t=this;return o()(s()().mark((function a(){var r;return s()().wrap((function(a){while(1)switch(a.prev=a.next){case 0:return t.$q.loading.show(),r=[],r.dataini=t.saleDatev1,r.saleDatev2=t.saleDatev2,r.pagination=e.pagination,r.filter=e.filter,r.supplier=t.supplier,r.branch=t.officeBranch,a.next=10,c["a"].post("/purchase-order-details/shoppingOfSuppliers",r).then((function(e){var a=e.data;a.result&&(t.datashoppingsuppliers=a.shopping)}));case 10:t.$q.loading.hide();case 11:case"end":return a.stop()}}),a)})))()}}},u=p,f=a("2877"),d=a("9989"),h=a("ead5"),v=a("079e"),b=a("9c40"),m=a("05c0"),g=a("ddd8"),O=a("7cbe"),S=a("52ee"),w=a("eaac"),D=a("27f9"),q=a("0016"),x=a("bd08"),C=a("db86"),k=a("eebe"),y=a.n(k),B=Object(f["a"])(u,r,i,!1,null,null,null);t["default"]=B.exports;y()(B,"components",{QPage:d["a"],QBreadcrumbs:h["a"],QBreadcrumbsEl:v["a"],QBtn:b["a"],QTooltip:m["a"],QSelect:g["a"],QPopupProxy:O["a"],QDate:S["a"],QTable:w["a"],QInput:D["a"],QIcon:q["a"],QTr:x["a"],QTd:C["a"]})}}]);
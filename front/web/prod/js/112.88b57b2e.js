(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[112],{ea40:function(t,e,a){"use strict";a.r(e);var s=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("q-page",{staticClass:"bg-grey-3"},[a("div",{staticClass:"q-pa-sm panel-header"},[a("div",{staticClass:"row"},[a("div",{staticClass:"col-sm-8"},[a("div",{staticClass:"q-pa-md q-gutter-sm"},[a("q-breadcrumbs",{staticStyle:{"font-size":"20px"},attrs:{align:"left"}},[a("q-breadcrumbs-el",{attrs:{label:"",icon:"home",to:"/"}}),a("q-breadcrumbs-el",{attrs:{label:"Ordenes de compra"}})],1)],1)]),1===t.roleId||22===t.roleId||20===t.roleId||28===t.roleId?a("div",{staticClass:"col-xs-6 col-md-4 pull-right"},[a("div",{staticClass:"q-pa-sm q-gutter-sm"},[a("q-btn",{staticClass:"bg-primary",staticStyle:{color:"white"},attrs:{icon:"add",label:"Nuevo"},nativeOn:{click:function(e){return t.$router.push("/purchase-orders/new")}}})],1)]):t._e()])]),a("div",{staticClass:"q-pa-md bg-grey-3"},[a("div",{staticClass:"row bg-white border-panel"},[a("div",{staticClass:"col q-pa-md"},[a("div",{staticClass:"row bg-white q-pa-md q-col-gutter-xs q-col-gutter-md"},[a("div",{staticClass:"col-sm-2"},[a("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",mask:"date",label:"Desde"},model:{value:t.saleDatev1,callback:function(e){t.saleDatev1=e},expression:"saleDatev1"}},[a("q-popup-proxy",{ref:"date",attrs:{"transition-show":"scale","transition-hide":"scale"}},[a("div",{staticClass:"col-sm-12"},[a("q-date",{attrs:{"today-btn":""},on:{input:function(e){return t.filterGrid()}},model:{value:t.saleDatev1,callback:function(e){t.saleDatev1=e},expression:"saleDatev1"}})],1)])],1)],1),a("div",{staticClass:"col-md-2"},[a("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",mask:"date",label:"Hasta"},model:{value:t.saleDatev2,callback:function(e){t.saleDatev2=e},expression:"saleDatev2"}},[a("q-popup-proxy",{ref:"date",attrs:{"transition-show":"scale","transition-hide":"scale"}},[a("div",{staticClass:"col-sm-12"},[a("q-date",{attrs:{"today-btn":""},on:{input:function(e){return t.filterGrid()}},model:{value:t.saleDatev2,callback:function(e){t.saleDatev2=e},expression:"saleDatev2"}})],1)])],1)],1),a("div",{staticClass:"col-md-3"},[a("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",options:t.optionsSupp,"use-input":"","emit-value":"","map-options":"",label:"Proveedor"},on:{filter:t.filterSuppliers,input:function(e){return t.filterGrid()}},model:{value:t.suppliers,callback:function(e){t.suppliers=e},expression:"suppliers"}})],1),a("div",{staticClass:"col-sm-2"},[a("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",options:[{label:"TODOS",value:"TODOS"},{label:"NUEVO",value:"NUEVO"},{label:"COTIZADO",value:"COTIZADO"},{label:"PEDIDO",value:"PEDIDO"},{label:"RECIBIDO",value:"RECIBIDO"}],"emit-value":"","map-options":"",label:"Estatus"},on:{input:function(e){return t.filterGrid()}},model:{value:t.status,callback:function(e){t.status=e},expression:"status"}})],1)]),a("div",{staticClass:"row bg-white"},[a("div",{staticClass:"col q-pa-md"},[a("q-table",{attrs:{flat:"",bordered:"",data:t.data,columns:t.columns,"row-key":"serial",pagination:t.pagination},on:{"update:pagination":function(e){t.pagination=e}},scopedSlots:t._u([{key:"body",fn:function(e){return[a("q-tr",{attrs:{props:e}},[a("q-td",{key:"serial",staticStyle:{"text-align":"center",width:"10%"},attrs:{props:e}},[t._v(t._s(e.row.serial))]),a("q-td",{key:"supplier",staticStyle:{"text-align":"left",width:"35%"},attrs:{props:e}},[t._v(t._s(e.row.supplier))]),a("q-td",{key:"order_date",staticStyle:{width:"15%"},attrs:{props:e}},[t._v(t._s(e.row.order_date))]),a("q-td",{key:"requested_date",staticStyle:{width:"15%"},attrs:{props:e}},[t._v(t._s(e.row.requested_date))]),a("q-td",{key:"status",staticStyle:{width:"10%","text-align":"center"},attrs:{props:e}},[a("q-chip",{attrs:{square:"",dense:"",color:"NUEVO"==e.row.status?"blue":"COTIZADO"==e.row.status?"warning":"PEDIDO"==e.row.status?"orange":"EMBARCADO"==e.row.status?"purple-6":"ARRIBO"==e.row.status?"accent":"RECEPCION"==e.row.status?"light-green":"RECIBIDO"==e.row.status?"green":"PARCIAL"==e.row.status?"red-4":"red-6","text-color":"white"}},[t._v("\n                  "+t._s(e.row.status)+"\n                ")])],1),"Cerrado"!=e.row.status?a("q-td",{key:"actions",staticStyle:{width:"10%","text-align":"left"},attrs:{props:e}},[a("q-btn",{attrs:{color:"primary",icon:"fas fa-edit",flat:"",size:"10px"},nativeOn:{click:function(a){return t.editSelectedRow(e.row.id)}}},[a("q-tooltip",{attrs:{"content-class":"bg-primary"}},[t._v("Editar")])],1),1!==t.roleId&&5!==t.roleId&&10!==t.roleId&&19!==t.roleId&&20!==t.roleId||"NUEVO"!=e.row.status?t._e():a("q-btn",{attrs:{color:"negative",icon:"fas fa-trash-alt",flat:"",size:"10px"},nativeOn:{click:function(a){return t.deleteSelectedRow(e.row.id)}}},[a("q-tooltip",{attrs:{"content-class":"bg-red"}},[t._v("Eliminar")])],1)],1):a("q-td",{key:"actions",staticStyle:{width:"10%"},attrs:{props:e}},[a("q-btn",{attrs:{color:"primary",icon:"remove_red_eye",flat:"",size:"10px"},nativeOn:{click:function(a){return t.editSelectedRow(e.row.id)}}},[a("q-tooltip",{attrs:{"content-class":"bg-primary"}},[t._v("Ver detalles")])],1)],1)],1)]}}])})],1)])])])])])},r=[],l=(a("4de4"),a("d3b7"),a("aabb")),o={name:"IndexPurchaseOrders",data:function(){return{optionsSupp:[],optionsSuppliers:[],filterSupp:[],suppliers:{value:0,label:"TODOS"},status:{label:"TODOS",value:"TODOS"},saleDatev1:null,saleDatev2:null,pagination:{sortBy:"serial",descending:!0,rowsPerPage:25},columns:[{name:"serial",align:"center",label:"FOLIO",field:"serial",style:"width: 10%",sortable:!0},{name:"supplier",align:"center",label:"PROVEEDOR",field:"supplier",style:"width: 35%",sortable:!0},{name:"order_date",align:"center",label:"FECHA DE PEDIDO",field:"order_date",style:"width: 15%",sortable:!0},{name:"requested_date",align:"center",label:"FECHA DE ARRIBO",field:"requested_date",style:"width: 15%",sortable:!0},{name:"status",align:"center",label:"ESTATUS",field:"status",style:"width: 10%",sortable:!0},{name:"actions",align:"center",label:"ACCIONES",field:"actions",style:"width: 10%",sortable:!1}],data:[]}},computed:{roleId:function(){var t=this.$store.getters["users/rol"];return parseInt(t)}},beforeRouteEnter:function(t,e,a){a((function(t){var e=t.$store.getters["users/rol"];console.log(e),1===e||3===e||7===e||2===e||20===e||4===e||27===e||20===e||22===e|28===e?a():a("/")}))},mounted:function(){this.fetchFromServer(),this.getSuppliersOrders()},methods:{filterSuppliers:function(t,e,a){var s=this;e((function(){var e=t.toLowerCase();s.optionsSupp=s.optionsSuppliers.filter((function(t){return t.label.toLowerCase().indexOf(e)>-1}))}))},getSuppliersOrders:function(){var t=this;l["a"].get("/suppliers/getSuppliersOrders").then((function(e){var a=e.data;t.optionsSuppliers=a.suppliers,t.optionsSuppliers.unshift({value:0,label:"TODOS"})}))},filterGrid:function(){var t=this;this.data=[];var e=[];e.suppliers=this.suppliers,e.saleDatev1=this.saleDatev1,e.saleDatev2=this.$formatDate(this.saleDatev2),e.status=this.status,l["a"].post("/purchase-orders/getGrid",e).then((function(e){var a=e.data;a.result&&(t.data=a.orders)}))},fetchFromServer:function(){var t=this;console.log(this.$store.getters["users/roles"]),this.$q.loading.show(),l["a"].get("/purchase-orders/all").then((function(e){var a=e.data;console.log(a),a.result?t.data=a.orders:t.$q.notify({message:a.message.content,position:"top",color:"warning",icon:"close"}),t.$q.loading.hide()}))},editSelectedRow:function(t){this.$router.push("/purchase-orders/".concat(t))},deleteSelectedRow:function(t){var e=this;this.$q.dialog({title:"Confirmación",message:"¿Desea eliminar esta Orden de Compra?",persistent:!0,ok:"Eliminar",cancel:"Cancelar"}).onOk((function(){l["a"].delete("/purchase-orders/".concat(t)).then((function(t){var a=t.data;e.$q.notify({message:a.message.content,position:"top",color:a.result?"positive":"warning",icon:a.result?"thumb_up":"close"}),a.result&&e.fetchFromServer()}))})).onCancel((function(){}))}}},i=o,n=a("2877"),c=a("9989"),d=a("ead5"),u=a("079e"),p=a("9c40"),v=a("ddd8"),f=a("7cbe"),b=a("52ee"),m=a("eaac"),h=a("bd08"),g=a("db86"),w=a("b047"),O=a("05c0"),q=a("eebe"),D=a.n(q),y=Object(n["a"])(i,s,r,!1,null,null,null);e["default"]=y.exports;D()(y,"components",{QPage:c["a"],QBreadcrumbs:d["a"],QBreadcrumbsEl:u["a"],QBtn:p["a"],QSelect:v["a"],QPopupProxy:f["a"],QDate:b["a"],QTable:m["a"],QTr:h["a"],QTd:g["a"],QChip:w["a"],QTooltip:O["a"]})}}]);
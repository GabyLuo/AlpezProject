(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[119],{a1a0:function(t,e,a){"use strict";a.r(e);var s=function(){var t=this,e=t._self._c;return e("q-page",{staticClass:"bg-grey-3"},[e("div",{staticClass:"q-pa-sm panel-header"},[e("div",{staticClass:"row"},[e("div",{staticClass:"col-sm-8"},[e("div",{staticClass:"q-pa-md q-gutter-sm"},[e("q-breadcrumbs",{staticStyle:{"font-size":"20px"},attrs:{align:"left"}},[e("q-breadcrumbs-el",{attrs:{label:"",icon:"home",to:"/"}}),e("q-breadcrumbs-el",{attrs:{label:"Recepciones"}})],1)],1)])])]),e("div",{staticClass:"q-pa-md bg-grey-3"},[e("div",{staticClass:"row bg-white border-panel"},[e("div",{staticClass:"col q-pa-md"},[e("q-table",{attrs:{flat:"",bordered:"",data:t.data,columns:t.columns,"row-key":"serial",pagination:t.pagination},on:{"update:pagination":function(e){t.pagination=e}},scopedSlots:t._u([{key:"body",fn:function(a){return[e("q-tr",{attrs:{props:a}},[e("q-td",{key:"serial",staticStyle:{"text-align":"center",width:"10%"},attrs:{props:a}},[t._v(t._s(a.row.order_id))]),e("q-td",{key:"oc",staticStyle:{"text-align":"center",width:"10%"},attrs:{props:a}},[t._v(t._s(a.row.serial))]),e("q-td",{key:"supplier",staticStyle:{"text-align":"left",width:"25%"},attrs:{props:a}},[t._v(t._s(a.row.supplier))]),e("q-td",{key:"branch_office",staticStyle:{"text-align":"left",width:"25%"},attrs:{props:a}},[t._v(t._s(a.row.branch_office))]),e("q-td",{key:"storage",staticStyle:{"text-align":"left",width:"25%"},attrs:{props:a}},[t._v(t._s(a.row.storage))]),e("q-td",{key:"status",staticStyle:{width:"10%"},attrs:{props:a}},[e("q-chip",{attrs:{square:"",dense:"",color:"PARCIAL"==a.row.status?"pink":"PEDIDO"==a.row.status?"orange":"secondary","text-color":"white"}},[t._v("\n                  "+t._s(a.row.status)+"\n                ")])],1),e("q-td",{key:"actions",staticStyle:{width:"5%"},attrs:{props:a}},[e("q-btn",{attrs:{color:"primary",icon:a.row.hayrestante||null===a.row.shipment_id?"add":"edit",flat:"",size:"10px"},nativeOn:{click:function(e){a.row.hayrestante||null===a.row.shipment_id?t.newShipment(a.row.order_id):t.editShipment(a.row.shipment_id)}}},[a.row.hayrestante||null===a.row.shipment_id?e("q-tooltip",{attrs:{"content-class":"bg-primary"}},[t._v("Nueva Recepción")]):e("q-tooltip",{attrs:{"content-class":"bg-primary"}},[t._v("Editar Recepción")])],1)],1)],1)]}}])})],1)])])])},r=[],i=(a("a9e3"),a("14d9"),a("aabb")),n={name:"IndexRawMaterialShipments",data:function(){return{pagination:{sortBy:"serial",descending:!0,rowsPerPage:25},columns:[{name:"serial",align:"center",label:"FOLIO",field:"serial",style:"width: 10%",sortable:!0},{name:"oc",align:"center",label:"OC",field:"oc",style:"width: 10%",sortable:!0},{name:"supplier",align:"center",label:"PROVEEDOR",field:"supplier",style:"width: 25%",sortable:!0},{name:"branch_office",align:"center",label:"ESTACIÓN",field:"branch_office",style:"width: 25%",sortable:!0},{name:"storage",align:"center",label:"ALMACÉN",field:"storage",style:"width: 25%",sortable:!0},{name:"status",align:"center",label:"ESTATUS",field:"status",style:"width: 10%",sortable:!0},{name:"actions",align:"center",label:"ACCIONES",field:"actions",style:"width: 5%",sortable:!1}],order_id:null,data:[],edit:!0}},beforeRouteEnter:function(t,e,a){a((function(t){var e=t.$store.getters["users/rol"];console.log(e),1===e||3===e||7===e||2===e||20===e||4===e||27===e||22===e||26===e?a():a("/")}))},mounted:function(){this.fetchFromServer()},methods:{fetchFromServer:function(){var t=this;this.$q.loading.show(),i["a"].get("/shipments/arribeOcs").then((function(e){var a=e.data;console.log(a.shipments),t.data=a.shipments,t.$q.loading.hide()}))},newShipment:function(t){var e=Number(t);this.$router.push("/shipments/purcharse-order/".concat(e,"/",0))},editShipment:function(t){var e=Number(t);this.$router.push("/shipments/".concat(e,"/",0))}}},o=n,l=a("2877"),c=a("9989"),d=a("ead5"),p=a("079e"),u=a("9c40"),h=a("eaac"),b=a("bd08"),m=a("db86"),w=a("b047"),f=a("05c0"),g=a("eebe"),y=a.n(g),_=Object(l["a"])(o,s,r,!1,null,null,null);e["default"]=_.exports;y()(_,"components",{QPage:c["a"],QBreadcrumbs:d["a"],QBreadcrumbsEl:p["a"],QBtn:u["a"],QTable:h["a"],QTr:b["a"],QTd:m["a"],QChip:w["a"],QTooltip:f["a"]})}}]);
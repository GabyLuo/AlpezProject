(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[29],{"2ac2":function(t,e,s){"use strict";s.r(e);var a=function(){var t=this,e=t._self._c;return e("q-page",[e("div",{staticClass:"q-pa-sm panel-header"},[e("div",{staticClass:"row"},[e("div",{staticClass:"col-sm-8"},[e("span",{staticClass:"q-ml-md grey-8 fs28 page-title"},[t._v("Recepciones PET")])]),e("div",{staticClass:"col-sm-4 pull-right"},[e("div",{staticClass:"q-pa-md q-gutter-sm"},[e("q-breadcrumbs",{attrs:{align:"right"}},[e("q-breadcrumbs-el",{attrs:{label:"",icon:"home",to:"/"}}),e("q-breadcrumbs-el",{attrs:{label:"Recepciones PET"}})],1)],1)])])]),e("div",{staticClass:"q-pa-md bg-grey-3"},[e("div",{staticClass:"row bg-white border-panel"},[e("div",{staticClass:"col q-pa-md"},[e("q-table",{attrs:{flat:"",bordered:"",data:t.data,columns:t.columns,"row-key":"serial",pagination:t.pagination},on:{"update:pagination":function(e){t.pagination=e}},scopedSlots:t._u([{key:"body",fn:function(s){return[e("q-tr",{attrs:{props:s}},[e("q-td",{key:"purchase_order_serial",staticStyle:{"text-align":"right",width:"10%"},attrs:{props:s}},[t._v(t._s(s.row.purchase_order_serial))]),e("q-td",{key:"serial",staticStyle:{"text-align":"right",width:"10%"},attrs:{props:s}},[t._v(t._s(s.row.serial))]),e("q-td",{key:"receive_date",staticStyle:{width:"10%"},attrs:{props:s}},[t._v(t._s(s.row.receive_date))]),e("q-td",{key:"total_weight",staticStyle:{"text-align":"right",width:"8%"},attrs:{props:s}},[t._v(t._s("".concat(t.formatter.format(s.row.total_weight)," KG.")))]),e("q-td",{key:"real_weight",staticStyle:{"text-align":"right",width:"8%"},attrs:{props:s}},[t._v(t._s("".concat(t.formatter.format(s.row.real_weight)," KG.")))]),e("q-td",{key:"supplier",staticStyle:{"text-align":"left",width:"39%"},attrs:{props:s}},[t._v(t._s(s.row.supplier?s.row.supplier:0))]),e("q-td",{key:"status",staticStyle:{width:"10%"},attrs:{props:s}},[e("q-chip",{attrs:{square:"",dense:"",color:"NUEVO"==s.row.status?"secondary":"RECIBIDO"==s.row.status?"orange":"ANALIZADO"==s.row.status?"positive":"RECHAZADO"==s.row.status?"negative":"blue","text-color":"white"}},[t._v("\n                  "+t._s(s.row.status)+"\n                ")])],1),e("q-td",{key:"actions",staticStyle:{width:"5%"},attrs:{props:s}},[e("q-btn",{attrs:{color:"primary",icon:"fas fa-edit",flat:"",size:"10px"},nativeOn:{click:function(e){return t.editShipment(s.row)}}},[e("q-tooltip",{attrs:{"content-class":"bg-primary"}},[t._v("Editar")])],1)],1)],1)]}}])})],1)])])])},r=[],i=(s("caad"),s("2532"),s("14d9"),s("aabb")),l={name:"IndexAnalyzedShipments",data:function(){return{formatter:new Intl.NumberFormat("en-US"),pagination:{rowsPerPage:50},columns:[{name:"purchase_order_serial",align:"center",label:"Folio OC",field:"purchase_order_serial",style:"width: 10%",sortable:!0},{name:"serial",align:"center",label:"Folio",field:"serial",style:"width: 10%",sortable:!0},{name:"receive_date",align:"center",label:"Fecha de recepción",field:"receive_date",style:"width: 10%",sortable:!0},{name:"total_weight",align:"center",label:"Peso báscula proveedor",field:"total_weight",style:"width: 8%",sortable:!0},{name:"real_weight",align:"center",label:"Peso real",field:"real_weight",style:"width: 8%",sortable:!0},{name:"supplier",align:"center",label:"Proveedor",field:"supplier",style:"width: 39%",sortable:!0},{name:"status",align:"center",label:"Estatus",field:"status",style:"width: 10%",sortable:!0},{name:"actions",align:"center",label:"Acciones",field:"actions",style:"width: 5%",sortable:!1}],data:[]}},beforeCreate:function(){this.$store.getters["users/roles"].includes(1)||this.$store.getters["users/roles"].includes(3)||this.$store.getters["users/roles"].includes(7)||this.$store.getters["users/roles"].includes(2)||this.$store.getters["users/roles"].includes(3)||this.$store.getters["users/roles"].includes(7)||this.$router.push("/")},mounted:function(){this.fetchFromServer()},methods:{fetchFromServer:function(){var t=this;this.$q.loading.show(),i["a"].get("/shipments/analyzed").then((function(e){var s=e.data;t.data=s.shipments,t.$q.loading.hide()}))},editShipment:function(t){this.$router.push("/shipments/".concat(t.id))}}},o=l,n=s("2877"),c=s("9989"),d=s("ead5"),p=s("079e"),u=s("eaac"),h=s("bd08"),g=s("db86"),w=s("b047"),b=s("9c40"),m=s("05c0"),_=s("eebe"),f=s.n(_),y=Object(n["a"])(o,a,r,!1,null,null,null);e["default"]=y.exports;f()(y,"components",{QPage:c["a"],QBreadcrumbs:d["a"],QBreadcrumbsEl:p["a"],QTable:u["a"],QTr:h["a"],QTd:g["a"],QChip:w["a"],QBtn:b["a"],QTooltip:m["a"]})}}]);
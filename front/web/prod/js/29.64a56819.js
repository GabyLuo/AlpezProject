(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[29],{"2ac2":function(t,e,s){"use strict";s.r(e);var a=function(){var t=this,e=t.$createElement,s=t._self._c||e;return s("q-page",[s("div",{staticClass:"q-pa-sm panel-header"},[s("div",{staticClass:"row"},[s("div",{staticClass:"col-sm-8"},[s("span",{staticClass:"q-ml-md grey-8 fs28 page-title"},[t._v("Recepciones PET")])]),s("div",{staticClass:"col-sm-4 pull-right"},[s("div",{staticClass:"q-pa-md q-gutter-sm"},[s("q-breadcrumbs",{attrs:{align:"right"}},[s("q-breadcrumbs-el",{attrs:{label:"",icon:"home",to:"/"}}),s("q-breadcrumbs-el",{attrs:{label:"Recepciones PET"}})],1)],1)])])]),s("div",{staticClass:"q-pa-md bg-grey-3"},[s("div",{staticClass:"row bg-white border-panel"},[s("div",{staticClass:"col q-pa-md"},[s("q-table",{attrs:{flat:"",bordered:"",data:t.data,columns:t.columns,"row-key":"serial",pagination:t.pagination},on:{"update:pagination":function(e){t.pagination=e}},scopedSlots:t._u([{key:"body",fn:function(e){return[s("q-tr",{attrs:{props:e}},[s("q-td",{key:"purchase_order_serial",staticStyle:{"text-align":"right",width:"10%"},attrs:{props:e}},[t._v(t._s(e.row.purchase_order_serial))]),s("q-td",{key:"serial",staticStyle:{"text-align":"right",width:"10%"},attrs:{props:e}},[t._v(t._s(e.row.serial))]),s("q-td",{key:"receive_date",staticStyle:{width:"10%"},attrs:{props:e}},[t._v(t._s(e.row.receive_date))]),s("q-td",{key:"total_weight",staticStyle:{"text-align":"right",width:"8%"},attrs:{props:e}},[t._v(t._s(t.formatter.format(e.row.total_weight)+" KG."))]),s("q-td",{key:"real_weight",staticStyle:{"text-align":"right",width:"8%"},attrs:{props:e}},[t._v(t._s(t.formatter.format(e.row.real_weight)+" KG."))]),s("q-td",{key:"supplier",staticStyle:{"text-align":"left",width:"39%"},attrs:{props:e}},[t._v(t._s(e.row.supplier?e.row.supplier:0))]),s("q-td",{key:"status",staticStyle:{width:"10%"},attrs:{props:e}},[s("q-chip",{attrs:{square:"",dense:"",color:"NUEVO"==e.row.status?"secondary":"RECIBIDO"==e.row.status?"orange":"ANALIZADO"==e.row.status?"positive":"RECHAZADO"==e.row.status?"negative":"blue","text-color":"white"}},[t._v("\n                  "+t._s(e.row.status)+"\n                ")])],1),s("q-td",{key:"actions",staticStyle:{width:"5%"},attrs:{props:e}},[s("q-btn",{attrs:{color:"primary",icon:"fas fa-edit",flat:"",size:"10px"},nativeOn:{click:function(s){return t.editShipment(e.row)}}},[s("q-tooltip",{attrs:{"content-class":"bg-primary"}},[t._v("Editar")])],1)],1)],1)]}}])})],1)])])])},r=[],i=(s("caad"),s("2532"),s("aabb")),l={name:"IndexAnalyzedShipments",data:function(){return{formatter:new Intl.NumberFormat("en-US"),pagination:{rowsPerPage:50},columns:[{name:"purchase_order_serial",align:"center",label:"Folio OC",field:"purchase_order_serial",style:"width: 10%",sortable:!0},{name:"serial",align:"center",label:"Folio",field:"serial",style:"width: 10%",sortable:!0},{name:"receive_date",align:"center",label:"Fecha de recepción",field:"receive_date",style:"width: 10%",sortable:!0},{name:"total_weight",align:"center",label:"Peso báscula proveedor",field:"total_weight",style:"width: 8%",sortable:!0},{name:"real_weight",align:"center",label:"Peso real",field:"real_weight",style:"width: 8%",sortable:!0},{name:"supplier",align:"center",label:"Proveedor",field:"supplier",style:"width: 39%",sortable:!0},{name:"status",align:"center",label:"Estatus",field:"status",style:"width: 10%",sortable:!0},{name:"actions",align:"center",label:"Acciones",field:"actions",style:"width: 5%",sortable:!1}],data:[]}},beforeCreate:function(){this.$store.getters["users/roles"].includes(1)||this.$store.getters["users/roles"].includes(3)||this.$store.getters["users/roles"].includes(7)||this.$store.getters["users/roles"].includes(2)||this.$store.getters["users/roles"].includes(3)||this.$store.getters["users/roles"].includes(7)||this.$router.push("/")},mounted:function(){this.fetchFromServer()},methods:{fetchFromServer:function(){var t=this;this.$q.loading.show(),i["a"].get("/shipments/analyzed").then((function(e){var s=e.data;t.data=s.shipments,t.$q.loading.hide()}))},editShipment:function(t){this.$router.push("/shipments/".concat(t.id))}}},o=l,n=s("2877"),c=s("9989"),d=s("ead5"),p=s("079e"),u=s("eaac"),h=s("bd08"),g=s("db86"),w=s("b047"),b=s("9c40"),m=s("05c0"),_=s("eebe"),f=s.n(_),y=Object(n["a"])(o,a,r,!1,null,null,null);e["default"]=y.exports;f()(y,"components",{QPage:c["a"],QBreadcrumbs:d["a"],QBreadcrumbsEl:p["a"],QTable:u["a"],QTr:h["a"],QTd:g["a"],QChip:w["a"],QBtn:b["a"],QTooltip:m["a"]})}}]);
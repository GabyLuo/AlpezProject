(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[39],{8338:function(t,e,a){"use strict";a.r(e);var s=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("q-page",[a("div",{staticClass:"q-pa-sm panel-header"},[a("div",{staticClass:"row"},[a("div",{staticClass:"col-sm-8"},[a("div",{staticClass:"q-pa-md q-gutter-sm"},[a("q-breadcrumbs",{staticStyle:{"font-size":"20px"},attrs:{align:"left"}},[a("q-breadcrumbs-el",{attrs:{label:"",icon:"home",to:"/"}}),a("q-breadcrumbs-el",{attrs:{label:"Traspasos Sucursales"}})],1)],1)])])]),a("div",{staticClass:"q-pa-md bg-grey-3"},[a("div",{staticClass:"row bg-white border-panel"},[a("div",{staticClass:"col q-pa-md"},[a("div",{staticClass:"row q-mb-sm"},[a("div",{staticClass:"col-xs-12 pull-right"},[a("q-btn",{attrs:{color:"primary",icon:"add",label:"Nuevo"},nativeOn:{click:function(e){return t.$router.push("/branch-transfers/new")}}})],1)]),a("q-table",{attrs:{flat:"",bordered:"",data:t.data,columns:t.columns,"row-key":"id",pagination:t.pagination},on:{"update:pagination":function(e){t.pagination=e}},scopedSlots:t._u([{key:"body",fn:function(e){return[a("q-tr",{attrs:{props:e}},[a("q-td",{key:"id",staticStyle:{"text-align":"right",width:"5%"},attrs:{props:e}},[t._v(t._s(e.row.id))]),a("q-td",{key:"origin_storage_name",staticStyle:{"text-align":"left",width:"30%"},attrs:{props:e}},[t._v(t._s(e.row.origin_storage_name))]),a("q-td",{key:"destination_storage_name",staticStyle:{"text-align":"left",width:"30%"},attrs:{props:e}},[t._v(t._s(e.row.destination_storage_name))]),a("q-td",{key:"date",staticStyle:{width:"10%"},attrs:{props:e}},[t._v(t._s(e.row.date))]),a("q-td",{key:"status",staticStyle:{width:"10%"},attrs:{props:e}},[a("q-chip",{attrs:{square:"",dense:"",color:0==e.row.status?"primary":1==e.row.status?"positive":"accent","text-color":"white"}},[t._v("\n                  "+t._s(0==Number(e.row.status)?"NO EJECUTADO":"EJECUTADO")+"\n                ")])],1),a("q-td",{key:"actions",staticStyle:{width:"5%"},attrs:{props:e}},[1==e.row.status?a("q-btn",{attrs:{color:"primary",icon:"fas fa-file-pdf",flat:"",size:"10px"},nativeOn:{click:function(a){return t.openBranchTransferPdf(e.row.id)}}},[a("q-tooltip",{attrs:{"content-class":"bg-primary"}},[t._v("Ver reporte")])],1):t._e(),a("q-btn",{attrs:{color:"primary",icon:"fas fa-edit",flat:"",size:"10px"},nativeOn:{click:function(a){return t.editSelectedRow(e.row.id)}}},[a("q-tooltip",{attrs:{"content-class":"bg-primary"}},[t._v("Editar")])],1)],1)],1)]}}])})],1)])])])},r=[],n=(a("a9e3"),a("caad"),a("2532"),a("aabb")),i={name:"IndeBranchTransfers",data:function(){return{pagination:{sortBy:"id",descending:!0,rowsPerPage:50},columns:[{name:"id",align:"center",label:"No. Traspaso",field:"id",style:"width: 5%",sortable:!0,sort:function(t,e){return Number(t,10)-Number(e,10)}},{name:"origin_storage_name",align:"center",label:"Almacén de origen",field:"origin_storage_name",style:"width: 30%",sortable:!0},{name:"destination_storage_name",align:"center",label:"Almacén de destino",field:"destination_storage_name",style:"width: 30%",sortable:!0},{name:"date",align:"center",label:"Fecha",field:"date",style:"width: 10%",sortable:!0},{name:"status",align:"center",label:"Estatus",field:"status",style:"width: 10%",sortable:!0},{name:"actions",align:"center",label:"Acciones",field:"actions",style:"width: 5%",sortable:!1}],data:[]}},beforeCreate:function(){this.$store.getters["users/roles"].includes(1)||this.$store.getters["users/roles"].includes(3)||this.$store.getters["users/roles"].includes(7)||this.$store.getters["users/roles"].includes(2)||this.$store.getters["users/roles"].includes(10)||this.$router.push("/")},mounted:function(){this.fetchFromServer()},methods:{fetchFromServer:function(){var t=this;this.$q.loading.show(),n["a"].get("/branch-transfers").then((function(e){var a=e.data;t.data=a.branchTransfers,t.$q.loading.hide()}))},openBranchTransferPdf:function(t){var e="https://api_alpez.wasp.mx/"+"branch-transfers/pdf/".concat(t);window.open(e,"_blank")},editSelectedRow:function(t){this.$router.push("/branch-transfers/".concat(t))}}},o=i,l=a("2877"),c=a("9989"),d=a("ead5"),u=a("079e"),p=a("9c40"),b=a("eaac"),f=a("bd08"),h=a("db86"),m=a("b047"),g=a("05c0"),w=a("eebe"),_=a.n(w),y=Object(l["a"])(o,s,r,!1,null,null,null);e["default"]=y.exports;_()(y,"components",{QPage:c["a"],QBreadcrumbs:d["a"],QBreadcrumbsEl:u["a"],QBtn:p["a"],QTable:b["a"],QTr:f["a"],QTd:h["a"],QChip:m["a"],QTooltip:g["a"]})}}]);
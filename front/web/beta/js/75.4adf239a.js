(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[75],{a1c4:function(t,e,a){"use strict";a.r(e);a("14d9");var s=function(){var t=this,e=t._self._c;return e("q-page",[e("div",{staticClass:"q-pa-sm panel-header"},[e("div",{staticClass:"row"},[e("div",{staticClass:"col-sm-8"},[e("span",{staticClass:"q-ml-md grey-8 fs28 page-title"},[t._v("Laminados")])]),e("div",{staticClass:"col-sm-4 pull-right"},[e("div",{staticClass:"q-pa-md q-gutter-sm"},[e("q-breadcrumbs",{attrs:{align:"right"}},[e("q-breadcrumbs-el",{attrs:{label:"",icon:"home",to:"/"}}),e("q-breadcrumbs-el",{attrs:{label:"Laminado"}})],1)],1)])])]),e("div",{staticClass:"q-pa-md bg-grey-3"},[e("div",{staticClass:"row bg-white border-panel"},[e("div",{staticClass:"col q-pa-md"},[e("div",{staticClass:"row q-mb-sm"},[e("div",{staticClass:"col-xs-12 col-md-2 offset-md-10 pull-right"},[e("q-btn",{attrs:{color:"primary",icon:"add",label:"Nuevo"},nativeOn:{click:function(e){return t.$router.push("/laminates/new")}}})],1)]),e("q-table",{attrs:{flat:"",bordered:"",data:t.data,columns:t.columns,"row-key":"id",pagination:t.pagination},on:{"update:pagination":function(e){t.pagination=e}},scopedSlots:t._u([{key:"body",fn:function(a){return[e("q-tr",{attrs:{props:a}},[e("q-td",{key:"id",staticStyle:{"text-align":"right",width:"10%"},attrs:{props:a}},[t._v(t._s(a.row.id))]),e("q-td",{key:"scheduled_date",staticStyle:{width:"10%"},attrs:{props:a}},[t._v(t._s(a.row.scheduled_date))]),e("q-td",{key:"product",staticStyle:{"text-align":"left",width:"15%"},attrs:{props:a}},[t._v(t._s(a.row.product))]),e("q-td",{key:"branch_office",staticStyle:{"text-align":"left",width:"15%"},attrs:{props:a}},[t._v(t._s(a.row.branch_office))]),e("q-td",{key:"storage",staticStyle:{"text-align":"left",width:"10%"},attrs:{props:a}},[t._v(t._s(a.row.storage))]),e("q-td",{key:"operator",staticStyle:{"text-align":"left",width:"10%"},attrs:{props:a}},[t._v(t._s(a.row.operator))]),e("q-td",{key:"weight",staticStyle:{"text-align":"right",width:"10%"},attrs:{props:a}},[t._v(t._s(a.row.weight?t.formatPrice(a.row.weight)+"KG":null)+" ")]),e("q-td",{key:"status",staticStyle:{width:"10%"},attrs:{props:a}},[e("q-chip",{attrs:{square:"",dense:"",color:"NUEVO"==a.row.status?"primary":"PRODUCIENDO"==a.row.status?"secondary":"TERMINADO"==a.row.status?"positive":"accent","text-color":"white"}},[t._v("\n                  "+t._s(a.row.status)+"\n                ")])],1),e("q-td",{key:"actions",staticStyle:{width:"10%"},attrs:{props:a}},[e("q-btn",{attrs:{color:"primary",icon:"fas fa-edit",flat:"",size:"10px"},nativeOn:{click:function(e){return t.editSelectedRow(a.row.id)}}},[e("q-tooltip",{attrs:{"content-class":"bg-primary"}},[t._v("Editar")])],1)],1)],1)]}}])})],1)])])])},r=[],i=(a("caad"),a("2532"),a("ac1f"),a("5319"),a("b680"),a("d3b7"),a("25f0"),a("aabb")),o={name:"IndexLaminates",data:function(){return{pagination:{sortBy:"code",descending:!1,rowsPerPage:50},columns:[{name:"id",align:"center",label:"No. Laminado",field:"id",style:"width: 10%",sortable:!0},{name:"scheduled_date",align:"center",label:"Fecha programada",field:"scheduled_date",style:"width: 10%",sortable:!0},{name:"product",align:"center",label:"Producto",field:"product",style:"width: 15%",sortable:!0},{name:"branch_office",align:"center",label:"Sucursal",field:"branch_office",style:"width: 15%",sortable:!0},{name:"storage",align:"center",label:"Almacén",field:"storage",style:"width: 10%",sortable:!0},{name:"operator",align:"center",label:"Operador",field:"operator",style:"width: 10%",sortable:!0},{name:"weight",align:"center",label:"Peso ejecutado",field:"weight",style:"width: 10%",sortable:!0},{name:"status",align:"center",label:"Estatus",field:"status",style:"width: 10%",sortable:!0},{name:"actions",align:"center",label:"Acciones",field:"actions",style:"width: 10%",sortable:!1}],data:[]}},beforeCreate:function(){this.$store.getters["users/roles"].includes(1)||this.$store.getters["users/roles"].includes(3)||this.$store.getters["users/roles"].includes(7)||this.$store.getters["users/roles"].includes(10)||this.$store.getters["users/roles"].includes(13)||this.$router.push("/")},mounted:function(){this.fetchFromServer()},methods:{formatPrice:function(t){var e=(t/1).toFixed(1).replace(".",",");return e.toString().replace(/\B(?=(\d{3})+(?!\d))/g,".")},fetchFromServer:function(){var t=this;this.$q.loading.show(),i["a"].get("/laminates").then((function(e){var a=e.data;console.log(a),t.data=a.laminates,t.$q.loading.hide()}))},editSelectedRow:function(t){this.$router.push("/laminates/".concat(t))}}},l=o,n=a("2877"),d=a("9989"),c=a("ead5"),u=a("079e"),p=a("9c40"),h=a("eaac"),g=a("bd08"),b=a("db86"),m=a("b047"),w=a("05c0"),f=a("eebe"),y=a.n(f),v=Object(n["a"])(l,s,r,!1,null,null,null);e["default"]=v.exports;y()(v,"components",{QPage:d["a"],QBreadcrumbs:c["a"],QBreadcrumbsEl:u["a"],QBtn:p["a"],QTable:h["a"],QTr:g["a"],QTd:b["a"],QChip:m["a"],QTooltip:w["a"]})}}]);
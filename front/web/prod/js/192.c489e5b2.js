(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[192],{9048:function(t,e,a){"use strict";a.r(e);var n=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("q-page",{staticClass:"bg-grey-3"},[a("div",{staticClass:"q-pa-sm panel-header"},[a("div",{staticClass:"row"},[a("div",{staticClass:"col-sm-8"},[a("div",{staticClass:"q-pa-md q-gutter-sm"},[a("q-breadcrumbs",{staticStyle:{"font-size":"20px"},attrs:{align:"left"}},[a("q-breadcrumbs-el",{attrs:{label:"",icon:"home",to:"/"}}),a("q-breadcrumbs-el",{attrs:{label:"Unidades"}})],1)],1)]),a("div",{staticClass:"col-xs-6 col-md-4 pull-right"},[a("div",{staticClass:"q-pa-sm q-gutter-sm"},[a("q-btn",{staticClass:"bg-primary",staticStyle:{color:"white"},attrs:{icon:"add",label:"Nuevo"},nativeOn:{click:function(e){return t.$router.push("/units/new")}}})],1)])])]),a("div",{staticClass:"q-pa-md bg-grey-3"},[a("div",{staticClass:"row bg-white border-panel"},[a("div",{staticClass:"col q-pa-md"},[a("q-table",{attrs:{flat:"",bordered:"",data:t.data,columns:t.columns,"row-key":"code",pagination:t.pagination,filter:t.filter},on:{"update:pagination":function(e){t.pagination=e}},scopedSlots:t._u([{key:"top",fn:function(){return[a("div",{staticStyle:{width:"100%"}},[a("q-input",{attrs:{dense:"",debounce:"300",placeholder:"Buscar"},on:{input:function(e){t.filter=e.toUpperCase()}},scopedSlots:t._u([{key:"append",fn:function(){return[a("q-icon",{attrs:{name:"search"}})]},proxy:!0}]),model:{value:t.filter,callback:function(e){t.filter=e},expression:"filter"}})],1)]},proxy:!0},{key:"body",fn:function(e){return[a("q-tr",{attrs:{props:e}},[a("q-td",{key:"code",staticStyle:{width:"20%","text-align":"center"},attrs:{props:e}},[t._v(t._s(e.row.code))]),a("q-td",{key:"name",staticStyle:{width:"50%","text-align":"left"},attrs:{props:e}},[t._v(t._s(e.row.name))]),a("q-td",{key:"actions",staticStyle:{width:"10%"},attrs:{props:e}},[a("q-btn",{attrs:{color:"primary",icon:"fas fa-edit",flat:"",size:"10px"},nativeOn:{click:function(a){return t.editSelectedRow(e.row.id)}}},[a("q-tooltip",{attrs:{"content-class":"bg-primary"}},[t._v("Editar")])],1),a("q-btn",{attrs:{color:"red",icon:"fas fa-trash-alt",flat:"",size:"10px"},nativeOn:{click:function(a){return t.deleteSelectedRow(e.row.id)}}},[a("q-tooltip",{attrs:{"content-class":"bg-red"}},[t._v("Eliminar")])],1)],1)],1)]}}])})],1)])])])},i=[],s=a("aabb"),o={name:"IndexUnits",data:function(){return{pagination:{sortBy:"code",descending:!1,rowsPerPage:25},columns:[{name:"code",align:"center",label:"CÓDIGO",field:"code",style:"width: 20%",sortable:!0},{name:"name",align:"center",label:"NOMBRE",field:"name",style:"width: 50%",sortable:!0},{name:"actions",align:"center",label:"ACCIONES",field:"actions",style:"width: 10%",sortable:!1}],data:[],filter:""}},beforeRouteEnter:function(t,e,a){a((function(t){var e=t.$store.getters["users/rol"];console.log(e),1===e||3===e||7===e||2===e||20===e||4===e||27===e?a():a("/")}))},mounted:function(){this.fetchFromServer()},methods:{fetchFromServer:function(){var t=this;this.$q.loading.show(),s["a"].get("/units").then((function(e){var a=e.data;t.data=a.units,t.$q.loading.hide()}))},editSelectedRow:function(t){this.$router.push("/units/".concat(t))},deleteSelectedRow:function(t){var e=this;this.$q.dialog({title:"Confirmación",message:"¿Desea eliminar esta Unidad?",persistent:!0,ok:{label:"Aceptar",color:"green"},cancel:{label:"Cancelar",color:"red"}}).onOk((function(){s["a"].delete("/units/".concat(t)).then((function(t){var a=t.data;e.$q.notify({message:a.message.content,position:"top",color:a.result?"positive":"warning",icon:a.result?"thumb_up":"close"}),a.result&&e.fetchFromServer()}))})).onCancel((function(){}))}}},r=o,l=a("2877"),c=a("9989"),d=a("ead5"),u=a("079e"),p=a("9c40"),f=a("eaac"),b=a("27f9"),m=a("0016"),g=a("bd08"),h=a("db86"),v=a("05c0"),w=a("eebe"),q=a.n(w),y=Object(l["a"])(r,n,i,!1,null,null,null);e["default"]=y.exports;q()(y,"components",{QPage:c["a"],QBreadcrumbs:d["a"],QBreadcrumbsEl:u["a"],QBtn:p["a"],QTable:f["a"],QInput:b["a"],QIcon:m["a"],QTr:g["a"],QTd:h["a"],QTooltip:v["a"]})}}]);
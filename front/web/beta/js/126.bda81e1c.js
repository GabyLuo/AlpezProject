(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[126],{"6dce":function(t,e,a){"use strict";a.r(e);a("14d9"),a("4de4"),a("d3b7"),a("b0c0");var n=function(){var t=this,e=t._self._c;return e("q-page",{staticClass:"bg-grey-3"},[e("div",{staticClass:"q-pa-sm panel-header"},[e("div",{staticClass:"row"},[e("div",{staticClass:"col-sm-8"},[e("div",{staticClass:"q-pa-md q-gutter-sm"},[e("q-breadcrumbs",{staticStyle:{"font-size":"20px"},attrs:{align:"left"}},[e("q-breadcrumbs-el",{attrs:{label:"",icon:"home",to:"/"}}),e("q-breadcrumbs-el",{attrs:{label:"Dirección"}})],1)],1)]),e("div",{staticClass:"col-xs-6 col-md-4 pull-right"},[e("div",{staticClass:"q-pa-sm q-gutter-sm"},[e("q-btn",{staticClass:"bg-primary",staticStyle:{color:"white"},attrs:{icon:"add",label:"Nuevo"},nativeOn:{click:function(e){return t.$router.push("/repositories/new")}}})],1)])])]),e("div",{staticClass:"q-pa-md bg-grey-3"},[e("div",{staticClass:"row bg-white border-panel"},[e("div",{staticClass:"col q-pa-md"},[e("q-table",{attrs:{flat:"",bordered:"",data:t.data,columns:t.columns,"row-key":"code",pagination:t.pagination,filter:t.filter},on:{"update:pagination":function(e){t.pagination=e}},scopedSlots:t._u([{key:"top",fn:function(){return[e("div",{staticStyle:{width:"100%"}},[e("q-input",{attrs:{dense:"",debounce:"300",placeholder:"Buscar"},on:{input:function(e){t.filter=e.toUpperCase()}},scopedSlots:t._u([{key:"append",fn:function(){return[e("q-icon",{attrs:{name:"search"}})]},proxy:!0}]),model:{value:t.filter,callback:function(e){t.filter=e},expression:"filter"}})],1)]},proxy:!0},{key:"body",fn:function(a){return[e("q-tr",{attrs:{props:a}},[e("q-td",{key:"name",staticStyle:{"text-align":"left"},attrs:{props:a}},[t._v(t._s(a.row.name))]),e("q-td",{key:"icon",staticStyle:{"text-align":"left"},attrs:{props:a}},[t._v(t._s(a.row.icon))]),e("q-td",{key:"sequence",staticStyle:{"text-align":"right"},attrs:{props:a}},[t._v(t._s(a.row.sequence))]),e("q-td",{key:"actions",staticClass:"pull-left",staticStyle:{width:"18%"},attrs:{props:a}},[e("q-btn",{attrs:{color:"primary",icon:"fas fa-edit",flat:"",size:"10px"},nativeOn:{click:function(e){return t.editSelectedRow(a.row.id)}}},[e("q-tooltip",{attrs:{"content-class":"bg-primary"}},[t._v("Editar")])],1),e("q-btn",{attrs:{color:"red",icon:"fas fa-trash-alt",flat:"",size:"10px"},nativeOn:{click:function(e){return t.deleteSelectedRow(a.row.id)}}},[e("q-tooltip",{attrs:{"content-class":"bg-red"}},[t._v("Eliminar")])],1)],1)],1)]}}])})],1)])])])},i=[],s=(a("caad"),a("2532"),a("aabb")),r={name:"Index",data:function(){return{pagination:{sortBy:"name",descending:!1,rowsPerPage:25},columns:[{name:"name",align:"center",label:"NOMBRE",field:"name",sortable:!0},{name:"icon",align:"center",label:"ICONO",field:"icon",sortable:!0},{name:"sequence",align:"center",label:"ORDEN",field:"sequence",sortable:!0},{name:"actions",align:"center",label:"ACCIONES",field:"actions",style:"width: 18%",sortable:!1}],data:[],filter:""}},beforeCreate:function(){this.$store.getters["users/roles"].includes(1)||this.$router.push("/")},mounted:function(){this.fetchFromServer()},methods:{fetchFromServer:function(){var t=this;this.$q.loading.show(),s["a"].get("/repositories").then((function(e){var a=e.data;t.data=a.info,t.$q.loading.hide()}))},editSelectedRow:function(t){this.$router.push("/repositories/".concat(t))},deleteSelectedRow:function(t){var e=this;this.$q.dialog({title:"Confirmación",message:"¿Desea eliminar esta dirección?",persistent:!0,ok:{label:"Aceptar",color:"green"},cancel:{label:"Cancelar",color:"red"}}).onOk((function(){s["a"].delete("/repositories/".concat(t)).then((function(t){var a=t.data;e.$q.notify({message:a.message.content,position:"top",color:a.result?"positive":"warning",icon:a.result?"thumb_up":"close"}),a.result&&e.fetchFromServer()}))})).onCancel((function(){}))}}},o=r,l=a("2877"),c=a("9989"),d=a("ead5"),u=a("079e"),p=a("9c40"),f=a("eaac"),b=a("27f9"),m=a("0016"),g=a("bd08"),h=a("db86"),q=a("05c0"),v=a("eebe"),w=a.n(v),y=Object(l["a"])(o,n,i,!1,null,null,null);e["default"]=y.exports;w()(y,"components",{QPage:c["a"],QBreadcrumbs:d["a"],QBreadcrumbsEl:u["a"],QBtn:p["a"],QTable:f["a"],QInput:b["a"],QIcon:m["a"],QTr:g["a"],QTd:h["a"],QTooltip:q["a"]})}}]);
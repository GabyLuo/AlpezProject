(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[54],{c448:function(e,t,a){"use strict";a.r(t);a("14d9"),a("4de4"),a("d3b7"),a("b0c0");var n=function(){var e=this,t=e._self._c;return t("q-page",{staticClass:"bg-grey-3"},[t("div",{staticClass:"q-pa-sm panel-header"},[t("div",{staticClass:"row"},[t("div",{staticClass:"col-sm-8"},[t("div",{staticClass:"q-pa-md q-gutter-sm"},[t("q-breadcrumbs",{staticStyle:{"font-size":"20px"},attrs:{align:"left"}},[t("q-breadcrumbs-el",{attrs:{label:"",icon:"home",to:"/"}}),t("q-breadcrumbs-el",{attrs:{label:"Tipo de Moneda"}})],1)],1)]),t("div",{staticClass:"col-xs-6 col-md-4 pull-right"},[t("div",{staticClass:"q-pa-sm q-gutter-sm"},[t("q-btn",{attrs:{color:"primary",icon:"add",label:"Nuevo"},nativeOn:{click:function(t){return e.$router.push("/currencies/new")}}})],1)])])]),t("div",{staticClass:"q-pa-md bg-grey-3"},[t("div",{staticClass:"row bg-white border-panel"},[t("div",{staticClass:"col q-pa-md"},[t("q-table",{attrs:{flat:"",bordered:"",data:e.data,columns:e.columns,"row-key":"code",pagination:e.pagination,filter:e.filter},on:{"update:pagination":function(t){e.pagination=t}},scopedSlots:e._u([{key:"top",fn:function(){return[t("div",{staticStyle:{width:"100%"}},[t("q-input",{attrs:{dense:"",debounce:"300",placeholder:"Buscar"},on:{input:function(t){e.filter=t.toUpperCase()}},scopedSlots:e._u([{key:"append",fn:function(){return[t("q-icon",{attrs:{name:"search"}})]},proxy:!0}]),model:{value:e.filter,callback:function(t){e.filter=t},expression:"filter"}})],1)]},proxy:!0},{key:"body",fn:function(a){return[t("q-tr",{attrs:{props:a}},[t("q-td",{key:"code",staticStyle:{"text-align":"center"},attrs:{props:a}},[e._v(e._s(a.row.code))]),t("q-td",{key:"name",staticStyle:{"text-align":"left"},attrs:{props:a}},[e._v(e._s(a.row.name))]),t("q-td",{key:"actions",attrs:{props:a}},[t("q-btn",{attrs:{color:"primary",icon:"fas fa-edit",flat:"",size:"10px"},nativeOn:{click:function(t){return e.editSelectedRow(a.row.id)}}},[t("q-tooltip",{attrs:{"content-class":"bg-primary"}},[e._v("Editar")])],1),t("q-btn",{attrs:{color:"red",icon:"fas fa-trash-alt",flat:"",size:"10px"},nativeOn:{click:function(t){return e.deleteSelectedRow(a.row.id)}}},[t("q-tooltip",{attrs:{"content-class":"bg-red"}},[e._v("Eliminar")])],1)],1)],1)]}}])})],1)])])])},r=[],o=a("aabb"),i={name:"IndexCategories",data:function(){return{pagination:{sortBy:"code",descending:!1,rowsPerPage:25},columns:[{name:"code",align:"center",label:"CODIGO",field:"code",sortable:!0},{name:"name",align:"center",label:"NOMBRE",field:"name",sortable:!0},{name:"actions",align:"center",label:"ACCIONES",field:"actions",style:"width: 10%",sortable:!1}],data:[],filter:""}},beforeRouteEnter:function(e,t,a){a((function(e){var t=e.$store.getters["users/rol"];console.log(t),1===t||3===t||7===t||2===t||20===t||4===t||27===t?a():a("/")}))},mounted:function(){this.fetchFromServer()},methods:{fetchFromServer:function(){var e=this;this.$q.loading.show(),o["a"].get("/currencies").then((function(t){var a=t.data;e.data=a.currencies,e.$q.loading.hide()}))},editSelectedRow:function(e){this.$router.push("/currencies/".concat(e))},deleteSelectedRow:function(e){var t=this;this.$q.dialog({title:"Confirmación",message:"¿Desea eliminar este Tipo de Moneda?",persistent:!0,ok:{label:"Aceptar",color:"green"},cancel:{label:"Cancelar",color:"red"}}).onOk((function(){o["a"].delete("/currencies/".concat(e)).then((function(e){var a=e.data;t.$q.notify({message:a.message.content,position:"top",color:a.result?"positive":"warning",icon:a.result?"thumb_up":"close"}),a.result&&t.fetchFromServer()}))})).onCancel((function(){}))}}},s=i,c=a("2877"),l=a("9989"),d=a("ead5"),u=a("079e"),p=a("9c40"),f=a("eaac"),b=a("27f9"),m=a("0016"),g=a("bd08"),v=a("db86"),h=a("05c0"),q=a("eebe"),w=a.n(q),y=Object(c["a"])(s,n,r,!1,null,null,null);t["default"]=y.exports;w()(y,"components",{QPage:l["a"],QBreadcrumbs:d["a"],QBreadcrumbsEl:u["a"],QBtn:p["a"],QTable:f["a"],QInput:b["a"],QIcon:m["a"],QTr:g["a"],QTd:v["a"],QTooltip:h["a"]})}}]);
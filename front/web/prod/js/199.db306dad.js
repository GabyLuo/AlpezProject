(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[199],{"34af":function(e,t,a){"use strict";a.r(t);var l=function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("q-page",{staticClass:"bg-grey-3"},[a("div",{staticClass:"q-pa-sm panel-header"},[a("div",{staticClass:"row"},[a("div",{staticClass:"col-sm-8"},[a("div",{staticClass:"q-pa-md q-gutter-sm"},[a("q-breadcrumbs",{staticStyle:{"font-size":"20px"},attrs:{align:"left"}},[a("q-breadcrumbs-el",{attrs:{label:"",icon:"home",to:"/"}}),a("q-breadcrumbs-el",{attrs:{label:"Vehículos"}})],1)],1)]),a("div",{staticClass:"col-xs-6 col-md-4 pull-right"},[a("div",{staticClass:"q-pa-sm q-gutter-sm"},[a("q-btn",{attrs:{color:"primary",icon:"add",label:"Nuevo"},nativeOn:{click:function(t){return e.$router.push("/vehicle/new")}}})],1)])])]),a("div",{staticClass:"q-pa-md bg-grey-3"},[a("div",{staticClass:"row bg-white border-panel"},[a("div",{staticClass:"col q-pa-md"},[a("q-table",{attrs:{flat:"",bordered:"",data:e.data,columns:e.columns,"row-key":"code",pagination:e.pagination,filter:e.filter},on:{"update:pagination":function(t){e.pagination=t}},scopedSlots:e._u([{key:"top",fn:function(){return[a("div",{staticStyle:{width:"100%"}},[a("q-input",{attrs:{dense:"",debounce:"300",placeholder:"Buscar"},on:{input:function(t){e.filter=t.toUpperCase()}},scopedSlots:e._u([{key:"append",fn:function(){return[a("q-icon",{attrs:{name:"search"}})]},proxy:!0}]),model:{value:e.filter,callback:function(t){e.filter=t},expression:"filter"}})],1)]},proxy:!0},{key:"body",fn:function(t){return[a("q-tr",{attrs:{props:t}},[a("q-td",{key:"economic_number",staticStyle:{"text-align":"centar"},attrs:{props:t}},[e._v(e._s(t.row.economic_number))]),a("q-td",{key:"type_id",staticStyle:{"text-align":"left"},attrs:{props:t}},[e._v(e._s(t.row.type_id))]),a("q-td",{key:"vehicle_brand",staticStyle:{"text-align":"left"},attrs:{props:t}},[e._v(e._s(t.row.vehicle_brand))]),a("q-td",{key:"vehicle_model",staticStyle:{"text-align":"left"},attrs:{props:t}},[e._v(e._s(t.row.vehicle_model))]),a("q-td",{key:"year",staticStyle:{"text-align":"right"},attrs:{props:t}},[e._v(e._s(t.row.year))]),a("q-td",{key:"license_plate",staticStyle:{"text-align":"center"},attrs:{props:t}},[e._v(e._s(t.row.license_plate))]),a("q-td",{key:"actions",staticClass:"pull-left",staticStyle:{width:"18%"},attrs:{props:t}},[a("q-btn",{attrs:{color:"primary",icon:"fas fa-edit",flat:"",size:"10px"},nativeOn:{click:function(a){return e.editSelectedRow(t.row.id)}}},[a("q-tooltip",{attrs:{"content-class":"bg-primary"}},[e._v("Editar")])],1),a("q-btn",{attrs:{color:"red",icon:"fas fa-trash-alt",flat:"",size:"10px"},nativeOn:{click:function(a){return e.deleteSelectedRow(t.row.id)}}},[a("q-tooltip",{attrs:{"content-class":"bg-red"}},[e._v("Eliminar")])],1)],1)],1)]}}])})],1)])])])},n=[],s=(a("caad"),a("2532"),a("aabb")),i={name:"IndexVehicle",data:function(){return{pagination:{sortBy:"name",descending:!1,rowsPerPage:25},columns:[{name:"economic_number",align:"center",label:"NÚMERO DE UNIDAD",field:"economic_number",sortable:!0},{name:"type_id",align:"center",label:"TIPO DE VEHÍCULO",field:"type_id",sortable:!0},{name:"vehicle_brand",align:"center",label:"MARCA DE VEHÍCULO",field:"vehicle_brand",sortable:!0},{name:"vehicle_model",align:"center",label:"MODELO DE VEHÍCULO",field:"vehicle_model",sortable:!0},{name:"year",align:"center",label:"AÑO",field:"year",sortable:!0},{name:"license_plate",align:"center",label:"PLACAS",field:"license_plate",sortable:!0},{name:"actions",align:"center",label:"ACCIONES",field:"actions",style:"width: 18%",sortable:!1}],data:[],filter:""}},beforeCreate:function(){this.$store.getters["users/roles"].includes(1)||this.$store.getters["users/roles"].includes(3)||this.$store.getters["users/roles"].includes(7)||this.$store.getters["users/roles"].includes(8)||this.$router.push("/")},mounted:function(){this.fetchFromServer()},methods:{fetchFromServer:function(){var e=this;this.$q.loading.show(),s["a"].get("/vehicle").then((function(t){var a=t.data;e.data=a.vehicles,e.$q.loading.hide()}))},editSelectedRow:function(e){this.$router.push("/vehicle/".concat(e))},deleteSelectedRow:function(e){var t=this;this.$q.dialog({title:"Confirmación",message:"¿Desea eliminar este Tipo de vehículo?",persistent:!0,ok:{label:"Aceptar",color:"green"},cancel:{label:"Cancelar",color:"red"}}).onOk((function(){s["a"].delete("/vehicle/".concat(e)).then((function(e){var a=e.data;t.$q.notify({message:a.message.content,position:"top",color:a.result?"positive":"warning",icon:a.result?"thumb_up":"close"}),a.result&&t.fetchFromServer()}))})).onCancel((function(){}))}}},r=i,o=a("2877"),c=a("9989"),d=a("ead5"),u=a("079e"),p=a("9c40"),f=a("eaac"),b=a("27f9"),m=a("0016"),h=a("bd08"),g=a("db86"),v=a("05c0"),y=a("eebe"),_=a.n(y),q=Object(o["a"])(r,l,n,!1,null,null,null);t["default"]=q.exports;_()(q,"components",{QPage:c["a"],QBreadcrumbs:d["a"],QBreadcrumbsEl:u["a"],QBtn:p["a"],QTable:f["a"],QInput:b["a"],QIcon:m["a"],QTr:h["a"],QTd:g["a"],QTooltip:v["a"]})}}]);
(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[86],{"80be":function(t,e,a){"use strict";a.r(e);a("14d9"),a("4de4"),a("d3b7");var o=function(){var t=this,e=t._self._c;return e("q-page",{staticClass:"bg-grey-3"},[e("div",{staticClass:"q-pa-sm panel-header"},[e("div",{staticClass:"row"},[e("div",{staticClass:"col-sm-4"},[e("div",{staticClass:"q-pa-md q-gutter-sm"},[e("q-breadcrumbs",{staticStyle:{"font-size":"20px"}},[e("q-breadcrumbs-el",{attrs:{label:"",icon:"home",to:"/"}}),e("q-breadcrumbs-el",{attrs:{label:"Movimientos"}})],1)],1)]),e("div",{staticClass:"col-sm-8 pull-right"},[e("div",{staticClass:"q-pa-sm q-gutter-sm"},[e("q-btn",{attrs:{color:"positive",icon:"add",label:"Entrada"},nativeOn:{click:function(e){return t.$router.push("/movements/new/".concat(1))}}}),e("q-btn",{attrs:{color:"negative",icon:"remove",label:"Salida"},nativeOn:{click:function(e){return t.$router.push("/movements/new/".concat(2))}}}),e("q-btn",{attrs:{color:"warning",icon:"compare_arrows",label:"Traspaso"},nativeOn:{click:function(e){return t.$router.push("/movements/newTransfer/".concat(4))}}}),e("q-btn",{attrs:{color:"light-blue",icon:"restore",label:"Inventario Físico"},nativeOn:{click:function(e){return t.$router.push("/movements/new/".concat(3))}}})],1)])])]),e("div",{staticClass:"q-pa-md bg-grey-3"},[e("div",{staticClass:"row bg-white border-panel"},[e("div",{staticClass:"col-md-4 col-xs-4 q-pl-md q-pt-md"},[e("q-select",{attrs:{label:"Tipo de Movimiento",color:"dark",filled:"","bg-color":"secondary",options:[{label:"TODOS",value:0},{label:"ENTRADA",value:1},{label:"SALIDA",value:2},{label:"INVENTARIO FÍSICO",value:3},{label:"TRASPASO (ENTRADA)",value:4},{label:"TRASPASO (SALIDA)",value:5},{label:"MERMA",value:6}],"map-options":""},scopedSlots:t._u([{key:"prepend",fn:function(){return[e("q-icon",{attrs:{name:"fas fa-cubes"}})]},proxy:!0}]),model:{value:t.filterType,callback:function(e){t.filterType=e},expression:"filterType"}})],1),e("div",{staticClass:"col-md-4 col-xs-4 q-pl-md q-pt-md"},[e("q-select",{attrs:{label:"Almacén",color:"dark",filled:"","bg-color":"secondary",options:t.storageOptions,"map-options":""},scopedSlots:t._u([{key:"prepend",fn:function(){return[e("q-icon",{attrs:{name:"fas fa-cubes"}})]},proxy:!0}]),model:{value:t.storage,callback:function(e){t.storage=e},expression:"storage"}})],1),e("div",{staticClass:"col-md-12 col-xs-12 q-pa-md"},[e("div",{staticClass:"row q-mb-sm"}),e("q-table",{attrs:{flat:"",bordered:"",data:t.filterTM,"row-key":"folio",pagination:t.pagination,columns:t.columns,filter:t.filter},on:{"update:pagination":function(e){t.pagination=e}},scopedSlots:t._u([{key:"top",fn:function(){return[e("div",{staticStyle:{width:"100%"}},[e("q-input",{attrs:{dense:"",debounce:"300",placeholder:"Buscar"},scopedSlots:t._u([{key:"append",fn:function(){return[e("q-icon",{attrs:{name:"search"}})]},proxy:!0}]),model:{value:t.filter,callback:function(e){t.filter=e},expression:"filter"}})],1)]},proxy:!0},{key:"body",fn:function(a){return[e("q-tr",{attrs:{props:a}},[e("q-td",{key:"folio",staticStyle:{"text-align":"center"},attrs:{props:a}},[t._v(t._s(a.row.folio))]),e("q-td",{key:"created",attrs:{props:a}},[t._v(t._s(a.row.date))]),e("q-td",{key:"type_id",attrs:{bg:"",props:a}},[e("q-chip",{attrs:{"text-color":"white",square:"",dense:"",color:a.row.color}},[t._v("\n                            "+t._s(a.row.type_id)+"\n                          ")])],1),e("q-td",{key:"branch_name",attrs:{props:a}},[t._v(t._s(a.row.branch_name))]),e("q-td",{key:"storage_name",attrs:{props:a}},[t._v(t._s(a.row.storage_name))]),e("q-td",{key:"status",attrs:{props:a}},[e("q-chip",{attrs:{dense:"",icon:"add",color:"NUEVO"==a.row.status?"blue":"EJECUTADO"===a.row.status?"positive":"CANCELADO"===a.row.status?"negative":t.negative,"text-color":"white"}},[t._v("\n                            "+t._s(a.row.status)+"\n                          ")])],1),e("q-td",{key:"actions",attrs:{props:a}},[e("q-btn",{attrs:{color:"primary",flat:"",icon:"fas fa-edit",size:"10px"},nativeOn:{click:function(e){return t.editSelectedRow(a.row.id)}}},[e("q-tooltip",{attrs:{"content-class":"bg-primary"}},[t._v("Editar")])],1),e("q-btn",{attrs:{color:"negative",flat:"",icon:"fas fa-trash-alt",size:"10px"},nativeOn:{click:function(e){return t.deleteSelectedRow(a.row.id)}}},[e("q-tooltip",{attrs:{"content-class":"bg-red"}},[t._v("Eliminar")])],1),"EJECUTADO"==a.row.status&&t.haspermissionv1?e("q-btn",{attrs:{color:"negative",icon:"fas fa-ban",flat:"",size:"10px"},nativeOn:{click:function(e){return t.cancelSelectedRow(a.row.id)}}},[e("q-tooltip",{attrs:{"content-class":"bg-red"}},[t._v("Cancelar")])],1):t._e()],1)],1)]}}])})],1)])])])},n=[],i=(a("caad"),a("2532"),a("a15b"),a("aabb")),s={name:"IndexMovements",data:function(){return{storage:{label:"TODOS",value:0},storageOptions:[],filter:"",pagination:{sortBy:"code",descending:!1,rowsPerPage:25},columns:[{name:"folio",align:"center",label:"FOLIO",field:"folio",style:"width: 10%",sortable:!0},{name:"created",align:"center",label:"FECHA",field:"created",style:"width: 15%",sortable:!0},{name:"type_id",align:"center",label:"MOVIMIENTOS",field:"type_id",style:"width: 10%",sortable:!0},{name:"branch_name",align:"center",label:"ESTACIÓN",field:"branch_name",style:"width: 10%",sortable:!0},{name:"storage_name",align:"center",label:"ALMACÉN",field:"storage_name",style:"width: 10%",sortable:!0},{name:"status",align:"center",label:"ESTATUS",field:"status",style:"width: 10%",sortable:!0},{name:"actions",align:"center",label:"ACCIONES",field:"actions",style:"width: 10%",sortable:!1}],filterType:{label:"TODOS",value:0},data:[],dataTemp:[]}},mounted:function(){this.fetchFromServer()},computed:{filterTM:function(){var t=this,e=this.data;return console.log(e),null!==this.storage&&"TODOS"!==this.storage.label&&null!==this.filterType&&"TODOS"!==this.filterType.label?e=this.data.filter((function(e){return e.storage_id===t.storage.value&&e.type_id===t.filterType.label})):null!==this.filterType&&"TODOS"!==this.filterType.label?e=this.data.filter((function(e){return e.type_id===t.filterType.label})):null!==this.storage&&"TODOS"!==this.storage.label&&(e=this.data.filter((function(e){return e.storage_id===t.storage.value}))),e},haspermissionv1:function(){return this.$store.getters["users/roles"].includes(1)}},beforeRouteEnter:function(t,e,a){a((function(t){var e=t.$store.getters["users/rol"];console.log(e),1===e||3===e||7===e||2===e||20===e||4===e||27===e||22===e||26===e?a():a("/")}))},methods:{invertDate:function(t){if(null!==t)var e=t.split("/").reverse().join("-");return e},deleteSelectedRow:function(t){var e=this;this.$q.dialog({title:"Confirmación",message:"¿Desea eliminar este Movimiento?",persistent:!0,ok:{label:"Aceptar",color:"positive"},cancel:{label:"Cancelar",color:"negative"}}).onOk((function(){e.$q.loading.show(),i["a"].delete("/movements/".concat(t)).then((function(t){var a=t.data;e.$q.notify({message:a.message.content,position:"top",color:a.result?"positive":"warning",icon:a.result?"thumb_up":"close"}),a.result?(e.$q.loading.hide(),e.fetchFromServer()):e.$q.loading.hide()}))})).onCancel((function(){}))},editSelectedRow:function(t){this.$router.push("/movements/".concat(t))},fetchFromServer:function(){var t=this;this.$q.loading.show(),i["a"].get("/movements/").then((function(e){var a=e.data;t.data=a.movements;for(var o=0;o<t.data.length;o++)1===t.data[o].type_id?(t.data[o].type_id="ENTRADA",t.data[o].color="positive"):2===t.data[o].type_id?(t.data[o].type_id="SALIDA",t.data[o].color="negative"):3===t.data[o].type_id?(t.data[o].type_id="INVENTARIO FÍSICO",t.data[o].color="light-blue"):4===t.data[o].type_id?(t.data[o].type_id="TRASPASO (ENTRADA)",t.data[o].color="positive"):5===t.data[o].type_id?(t.data[o].type_id="TRASPASO (SALIDA)",t.data[o].color="negative"):6===t.data[o].type_id&&(t.data[o].type_id="MERMA",t.data[o].color="purple")})),i["a"].get("/storages/options").then((function(e){var a=e.data;t.storageOptions=a.options,t.storageOptions.push({label:"TODOS",value:0})})),this.$q.loading.hide()},cancelSelectedRow:function(t){var e=this;this.$q.dialog({title:"Confirmación",message:"¿Desea cancelar este Movimiento?",persistent:!0,ok:{label:"Aceptar",color:"positive"},cancel:{label:"Cancelar",color:"negative"}}).onOk((function(){e.$q.loading.show(),i["a"].put("/movements/cancel/".concat(t)).then((function(t){var a=t.data;e.$q.notify({message:a.message.content,position:"top",color:a.result?"positive":"warning"}),a.result?(e.$q.loading.hide(),e.fetchFromServer()):e.$q.loading.hide()}))})).onCancel((function(){}))}}},r=s,l=a("2877"),c=a("9989"),d=a("ead5"),p=a("079e"),u=a("9c40"),f=a("ddd8"),m=a("0016"),b=a("eaac"),v=a("27f9"),g=a("bd08"),h=a("db86"),y=a("b047"),q=a("05c0"),w=a("eebe"),_=a.n(w),O=Object(l["a"])(r,o,n,!1,null,null,null);e["default"]=O.exports;_()(O,"components",{QPage:c["a"],QBreadcrumbs:d["a"],QBreadcrumbsEl:p["a"],QBtn:u["a"],QSelect:f["a"],QIcon:m["a"],QTable:b["a"],QInput:v["a"],QTr:g["a"],QTd:h["a"],QChip:y["a"],QTooltip:q["a"]})}}]);
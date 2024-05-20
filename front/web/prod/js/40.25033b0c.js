(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[40],{"505a":function(e,r,a){"use strict";a.r(r);var i=function(){var e=this,r=e.$createElement,a=e._self._c||r;return a("q-page",[a("div",{staticClass:"q-pa-sm panel-header"},[a("div",{staticClass:"row"},[a("div",{staticClass:"col-sm-8"},[a("div",{staticClass:"q-pa-md q-gutter-sm"},[a("q-breadcrumbs",{staticStyle:{"font-size":"20px"},attrs:{align:"left"}},[a("q-breadcrumbs-el",{attrs:{label:"",icon:"home",to:"/"}}),a("q-breadcrumbs-el",{attrs:{label:"Traspasos Sucursales",to:"/branch-transfers"}}),a("q-breadcrumbs-el",{attrs:{label:"Nuevo Traspaso Sucursales"}})],1)],1)])])]),a("div",{staticClass:"q-pa-md bg-grey-3"},[a("div",{staticClass:"row bg-white border-panel"},[a("div",{staticClass:"col q-pa-md"},[a("div",{staticClass:"row q-mb-sm"}),a("div",{staticClass:"row q-col-gutter-xs",staticStyle:{"margin-bottom":"20px"}},[a("div",{staticClass:"col-sm-12 col-md-2"},[a("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",label:"Estatus",disable:""},scopedSlots:e._u([{key:"prepend",fn:function(){return[a("q-icon",{attrs:{name:"battery_full"}})]},proxy:!0}]),model:{value:e.branchTrasfer.fields.statusStr,callback:function(r){e.$set(e.branchTrasfer.fields,"statusStr",r)},expression:"branchTrasfer.fields.statusStr"}})],1)]),a("div",{staticClass:"row q-col-gutter-xs"},[a("div",{staticClass:"col-sm-12 col-md-6"},[a("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",options:e.branchOfficeOptions,label:"Sucursal origen","emit-value":"","map-options":"",rules:e.originBranchOfficeRules},scopedSlots:e._u([{key:"prepend",fn:function(){return[a("q-icon",{attrs:{name:"fas fa-store-alt"}})]},proxy:!0}]),model:{value:e.branchTrasfer.fields.originBranchOffice,callback:function(r){e.$set(e.branchTrasfer.fields,"originBranchOffice",r)},expression:"branchTrasfer.fields.originBranchOffice"}})],1),a("div",{staticClass:"col-sm-12 col-md-4"},[a("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",options:e.filteredOriginStorageOptions,label:"Almacén origen",rules:e.originStorageRules},scopedSlots:e._u([{key:"prepend",fn:function(){return[a("q-icon",{attrs:{name:"fas fa-warehouse"}})]},proxy:!0}]),model:{value:e.branchTrasfer.fields.originStorage,callback:function(r){e.$set(e.branchTrasfer.fields,"originStorage",r)},expression:"branchTrasfer.fields.originStorage"}})],1),a("div",{staticClass:"col-xs-12 col-sm-2 text-center"},[a("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",readonly:"",label:"Folio"},scopedSlots:e._u([{key:"prepend",fn:function(){return[a("q-icon",{attrs:{name:"style"}})]},proxy:!0}]),model:{value:e.branchTrasfer.fields.folio_1,callback:function(r){e.$set(e.branchTrasfer.fields,"folio_1",r)},expression:"branchTrasfer.fields.folio_1"}})],1),a("div",{staticClass:"col-sm-12 col-md-6"},[a("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",options:e.branchOfficeOptions2,label:"Sucursal destino",rules:e.destinationBranchOfficeRules},scopedSlots:e._u([{key:"prepend",fn:function(){return[a("q-icon",{attrs:{name:"fas fa-store-alt"}})]},proxy:!0}]),model:{value:e.branchTrasfer.fields.destinationBranchOffice,callback:function(r){e.$set(e.branchTrasfer.fields,"destinationBranchOffice",r)},expression:"branchTrasfer.fields.destinationBranchOffice"}})],1),a("div",{staticClass:"col-sm-12 col-md-4"},[a("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",options:e.filteredDestinationStorage,label:"Almacén destino",rules:e.destinationStorageRules},scopedSlots:e._u([{key:"prepend",fn:function(){return[a("q-icon",{attrs:{name:"fas fa-warehouse"}})]},proxy:!0}]),model:{value:e.branchTrasfer.fields.destinationStorage,callback:function(r){e.$set(e.branchTrasfer.fields,"destinationStorage",r)},expression:"branchTrasfer.fields.destinationStorage"}})],1),a("div",{staticClass:"col-xs-12 col-sm-2 text-center"},[a("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",readonly:"",label:"Folio"},scopedSlots:e._u([{key:"prepend",fn:function(){return[a("q-icon",{attrs:{name:"style"}})]},proxy:!0}]),model:{value:e.branchTrasfer.fields.folio_1,callback:function(r){e.$set(e.branchTrasfer.fields,"folio_1",r)},expression:"branchTrasfer.fields.folio_1"}})],1),a("div",{staticClass:"col-sm-12 col-md-12 pull-right"},[a("q-btn",{attrs:{color:"positive",icon:"save",label:"Guardar"},on:{click:function(r){return e.createBranchOfficeTransfer()}}})],1)])])])])])},s=[],t=a("ded3"),n=a.n(t),o=(a("4de4"),a("d3b7"),a("a9e3"),a("159b"),a("aabb")),c=a("b5ae"),l=c.required,f={name:"NewBranchTransfer",validations:{branchTrasfer:{fields:{originBranchOffice:{required:l},originStorage:{required:l},destinationBranchOffice:{required:l},destinationStorage:{required:l},folio_1:{required:l},folio_2:{required:l}}}},data:function(){return{branchTrasfer:{fields:{id:null,originBranchOffice:null,originStorage:null,destinationBranchOffice:null,destinationStorage:null,status:null,statusStr:"NUEVO",transactionId:null,folio_1:null,folio_2:null}},branchOfficeOptions:[],branchOfficeOptions2:[],storageOptions:[],storageOptions2:[]}},computed:{originBranchOfficeRules:function(e){var r=this;return[function(e){return r.$v.branchTrasfer.fields.originBranchOffice.required||"El campo Sucursal origen es requerido."}]},originStorageRules:function(e){var r=this;return[function(e){return r.$v.branchTrasfer.fields.originStorage.required||"El campo Almacén origen es requerido."}]},destinationBranchOfficeRules:function(e){var r=this;return[function(e){return r.$v.branchTrasfer.fields.destinationBranchOffice.required||"El campo Sucursal destino es requerido."}]},destinationStorageRules:function(e){var r=this;return[function(e){return r.$v.branchTrasfer.fields.destinationStorage.required||"El campo Almacén destino es requerido."}]},filteredOriginStorageOptions:function(){var e=this;return null==this.branchTrasfer.fields.originBranchOffice||isNaN(this.branchTrasfer.fields.originBranchOffice)?[]:this.storageOptions.filter((function(r){return Number(r.branchOffice)===Number(e.branchTrasfer.fields.originBranchOffice)}))},filteredDestinationStorage:function(){var e=this;return console.log(this.branchTrasfer.fields.originStorage),null==this.branchTrasfer.fields.destinationBranchOffice||isNaN(this.branchTrasfer.fields.destinationBranchOffice.value)?[]:this.storageOptions2.filter((function(r){return Number(r.branchOffice)===Number(e.branchTrasfer.fields.destinationBranchOffice.value)&&Number(e.branchTrasfer.fields.originStorage.value)!==Number(r.value)}))}},created:function(){this.fetchFromServer()},methods:{fetchFromServer:function(){var e=this;this.$q.loading.show(),o["a"].get("/branch-offices/options").then((function(r){var a=r.data;e.branchOfficeOptions=a.options,e.branchOfficeOptions2=a.options,o["a"].get("/storages/options").then((function(r){var a=r.data;e.storageOptions=a.options,e.storageOptions2=a.options,e.$q.loading.hide()}))}))},createBranchOfficeTransfer:function(){var e=this;if(this.$v.branchTrasfer.fields.$reset(),this.$v.branchTrasfer.fields.$touch(),this.$v.branchTrasfer.fields.$error)return this.$q.dialog({title:"Error",message:"Por favor, verifique las validaciones.",persistent:!0}),!1;this.$q.loading.show();var r=[];r.originStorage=Number(n()({},this.branchTrasfer.fields).originStorage.value),r.destinationStorage=Number(n()({},this.branchTrasfer.fields).destinationStorage.value),o["a"].post("/branch-transfers",r).then((function(r){var a=r.data;e.$q.notify({message:a.message.content,position:"top",color:a.result?"positive":"warning"}),a.result?(e.$q.loading.hide(),e.$router.push("/branch-transfers/".concat(a.branchTransfer.id))):e.$q.loading.hide()}))},prueba:function(){var e=this;this.branchTrasfer.fields.originStorage=null;var r=this.branchTrasfer.fields.originBranchOffice;this.branchOfficeOptions2=[],this.branchOfficeOptions.forEach((function(a){r!==a.value&&e.branchOfficeOptions2.push({label:a.label,value:a.value})}))},test:function(){var e=this,r=this.branchTrasfer.fields.originStorage.value;this.storageOptions2=[],this.storageOptions.forEach((function(a){r!==a.value&&e.storageOptions2.push({label:a.label,value:a.value})}))}}},d=f,u=a("2877"),h=a("9989"),b=a("ead5"),p=a("079e"),g=a("27f9"),m=a("0016"),v=a("ddd8"),O=a("9c40"),q=a("eebe"),S=a.n(q),T=Object(u["a"])(d,i,s,!1,null,null,null);r["default"]=T.exports;S()(T,"components",{QPage:h["a"],QBreadcrumbs:b["a"],QBreadcrumbsEl:p["a"],QInput:g["a"],QIcon:m["a"],QSelect:v["a"],QBtn:O["a"]})}}]);
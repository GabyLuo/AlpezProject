(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[57],{"367d":function(e,t,a){"use strict";a.r(t);var o=function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("q-page",{staticClass:"bg-grey-3"},[a("div",{staticClass:"q-pa-sm panel-header"},[a("div",{staticClass:"row"},[a("div",{staticClass:"col-sm-6"},[a("div",{staticClass:"q-pa-md q-gutter-sm"},[a("q-breadcrumbs",{staticStyle:{"font-size":"20px"},attrs:{align:"left"}},[a("q-breadcrumbs-el",{attrs:{label:"",icon:"home",to:"/"}}),a("q-breadcrumbs-el",{attrs:{label:"Clientes"}})],1)],1)]),a("div",{staticClass:"col-xs-12 col-md-6  pull-right"},[a("div",{staticClass:"q-pa-sm q-gutter-sm"},[20!==e.roleId&&4!==e.roleId&&22!==e.roleId&&28!==e.roleId&&29!==e.roleId&&27!==e.roleId?a("q-btn",{attrs:{color:"positive",icon:"fas fa-file-csv",label:"Descargar CSV"},on:{click:function(t){return e.generateCsv()}}}):e._e(),20!==e.roleId&&4!==e.roleId&&22!==e.roleId&&28!==e.roleId&&29!==e.roleId&&27!==e.roleId?a("q-btn",{attrs:{color:"positive",icon:"cloud_upload",label:"CARGA MASIVA CLIENTES"},on:{click:function(t){return e.openUploadFileModal()}}}):e._e(),20!==e.roleId&&29!==e.roleId&&27!==e.roleId?a("q-btn",{staticClass:"bg-primary",staticStyle:{color:"white"},attrs:{icon:"add",label:"Nuevo"},nativeOn:{click:function(t){return e.$router.push("/customers/new")}}}):e._e()],1)])])]),a("div",{staticClass:"q-pa-md bg-grey-3"},[a("div",{staticClass:"row bg-white border-panel"},[a("div",{staticClass:"col q-pa-md"},[a("q-table",{attrs:{flat:"",bordered:"",data:e.filteredCustomers,columns:27!==e.roleId?e.columns:e.columnsMostrador,"row-key":"serial",pagination:e.pagination,filter:e.filter},on:{"update:pagination":function(t){e.pagination=t},request:e.qTableRequest},scopedSlots:e._u([{key:"top",fn:function(){return[a("div",{staticStyle:{width:"100%"}},[a("q-input",{attrs:{dense:"",debounce:"300",placeholder:"Buscar"},on:{input:function(t){e.filter=t.toUpperCase()}},scopedSlots:e._u([{key:"append",fn:function(){return[a("q-icon",{attrs:{name:"search"}})]},proxy:!0}]),model:{value:e.filter,callback:function(t){e.filter=t},expression:"filter"}})],1)]},proxy:!0},{key:"body",fn:function(t){return[a("q-tr",{style:"No"===t.row.yesornotdatafs?"background: red; color: white;":"background: white;",attrs:{props:t}},[a("q-td",{key:"serial",staticClass:"text-primary cursor-pointer",staticStyle:{"text-align":"center","text-decoration":"underline"},attrs:{props:t,flat:""},nativeOn:{click:function(a){return e.editSelectedRow(t.row.id)}}},[e._v(e._s(t.row.serial))]),a("q-td",{key:"name",staticStyle:{"text-align":"left"},attrs:{props:t}},[e._v(e._s(t.row.name))]),a("q-td",{key:"email",staticStyle:{"text-align":"left"},attrs:{props:t}},[e._v(e._s(t.row.email))]),a("q-td",{key:"contact_phone",staticStyle:{"text-align":"center"},attrs:{props:t}},[e._v(e._s(t.row.contact_phone))]),a("q-td",{key:"price_list",staticStyle:{"text-align":"center"},attrs:{props:t}},[e._v(e._s(t.row.price_list))]),a("q-td",{key:"country",staticStyle:{"text-align":"left"},attrs:{props:t}},[e._v(e._s(t.row.country))]),a("q-td",{key:"active",attrs:{props:t}},[a("q-chip",{attrs:{square:"",dense:"",color:t.row.active?"positive":"negative","text-color":"white"}},[e._v("\n                  "+e._s(t.row.active?"ACTIVO":"INACTIVO")+"\n                ")])],1),29!==e.roleId&&27!==e.roleId?a("q-td",{key:"actions",attrs:{props:t}},[a("q-btn",{attrs:{color:"primary",icon:"photo",flat:"",size:"10px"},nativeOn:{click:function(a){return e.editPhoto(t.row.id)}}},[a("q-tooltip",{attrs:{"content-class":"bg-primary"}},[e._v("Fotografía")])],1),a("q-btn",{attrs:{color:"primary",icon:"fas fa-edit",flat:"",size:"10px"},nativeOn:{click:function(a){return e.editSelectedRow(t.row.id)}}},[a("q-tooltip",{attrs:{"content-class":"bg-primary"}},[e._v("Editar")])],1),a("q-btn",{attrs:{color:"red",icon:"fas fa-trash-alt",flat:"",size:"10px"},nativeOn:{click:function(a){return e.deleteSelectedRow(t.row.id)}}},[a("q-tooltip",{attrs:{"content-class":"bg-red"}},[e._v("Eliminar")])],1)],1):e._e()],1)]}}])})],1)])]),a("q-dialog",{attrs:{persistent:""},model:{value:e.photoModal,callback:function(t){e.photoModal=t},expression:"photoModal"}},[a("q-card",[a("q-card-section",{staticClass:"row"},[a("div",{staticClass:"col-xs-12 col-sm-10 text-h6"},[e._v("Foto: "+e._s(e.photoCode))]),a("q-btn",{directives:[{name:"close-popup",rawName:"v-close-popup"}],staticClass:"col-xs-12 col-sm-2 pull-right",attrs:{icon:"close",flat:"",round:"",dense:""}})],1),a("q-card-section",[a("q-uploader",{ref:"photoRef",attrs:{accept:".jpg, image/*",method:"POST","hide-upload-btn":"",factory:e.factoryFn}})],1),a("q-card-section",[a("q-img",{attrs:{src:e.photoUrl,"spinner-color":"white"}})],1),a("q-card-actions",{staticClass:"text-primary",attrs:{align:"right"}},[a("q-btn",{attrs:{flat:"",label:"Subir foto"},on:{click:function(t){return e.uploadPhoto()}}})],1)],1)],1),a("q-dialog",{attrs:{persistent:""},model:{value:e.documentFileModal,callback:function(t){e.documentFileModal=t},expression:"documentFileModal"}},[a("q-card",[a("q-card-section",{staticClass:"row"},[a("div",{staticClass:"col-xs-12 col-sm-10 text-h6"},[e._v("Archivo: "+e._s(e.documentName))]),a("q-btn",{directives:[{name:"close-popup",rawName:"v-close-popup"}],staticClass:"col-xs-12 col-sm-2 pull-right",attrs:{icon:"close",flat:"",round:"",dense:""}})],1),a("q-card-section",[a("q-uploader",{ref:"fileDocumentRef",attrs:{url:e.fileDocumentUrl,headers:[{name:"Authorization",value:e.token}],method:"POST","hide-upload-btn":""},on:{uploaded:e.afterUploadDocumentFile}})],1),a("q-card-actions",{staticClass:"text-secondary",attrs:{align:"right"}},[a("q-btn",{attrs:{flat:"",label:"Subir archivo"},on:{click:function(t){return e.uploadDocumentFile()}}})],1)],1)],1)],1)},r=[],n=a("7ec2"),i=a.n(n),l=a("c973"),s=a.n(l),c=(a("4de4"),a("d3b7"),a("a9e3"),a("aabb")),d={name:"IndexCustomers",data:function(){return{documentName:null,documentFileModal:!1,pagination:{sortBy:"serial",descending:!1,page:1,rowsNumber:0,rowsPerPage:25},columns:[{name:"serial",align:"center",label:"CÓDIGO",field:"serial",sortable:!0},{name:"name",align:"center",label:"CLIENTE",field:"name",sortable:!0},{name:"email",align:"center",label:"EMAIL",field:"email",sortable:!0},{name:"contact_phone",align:"center",label:"TELÉFONO",field:"contact_phone",sortable:!0},{name:"price_list",align:"center",label:"PRECIO DE LISTA",field:"price_list",sortable:!0},{name:"country",align:"center",label:"PAÍS",field:"country",sortable:!0},{name:"active",align:"center",label:"ESTATUS",field:"active",sortable:!0},{name:"actions",align:"center",label:"ACCIONES",field:"actions",sortable:!1}],columnsMostrador:[{name:"serial",align:"center",label:"CÓDIGO",field:"serial",sortable:!0},{name:"name",align:"center",label:"CLIENTE",field:"name",sortable:!0},{name:"rfc",align:"center",label:"RFC",field:"rfc",sortable:!0},{name:"email",align:"center",label:"EMAIL",field:"email",sortable:!0},{name:"contact_phone",align:"center",label:"TELÉFONO",field:"contact_phone",sortable:!0},{name:"price_list",align:"center",label:"PRECIO DE LISTA",field:"price_list",sortable:!0},{name:"country",align:"center",label:"PAÍS",field:"country",sortable:!0},{name:"active",align:"center",label:"ESTATUS",field:"active",sortable:!0}],data:[],filter:"",branchOffice:{value:null,label:"TODOS"},branchOfficeOptions:[],serverUrl:"https://api_alpez.wasp.mx/",customerId:null,photoModal:!1,photoUrl:null,photoCode:null}},computed:{roleId:function(){var e=this.$store.getters["users/rol"];return parseInt(e)},token:function(){var e="Bearer "+localStorage.getItem("JWT");return e},filteredCustomers:function(){var e=this,t=this.data.filter((function(e){return!0===e.active||!1===e.active}));return null!=this.branchOffice&&null!=this.branchOffice.value?t.filter((function(t){return Number(t.branch_id)===Number(e.branchOffice.value)})):t}},beforeRouteEnter:function(e,t,a){a((function(e){var t=e.$store.getters["users/rol"];console.log(t),1===t||3===t||7===t||2===t||20===t||4===t||27===t||17===t||22===t||28===t||29===t?a():a("/")}))},mounted:function(){this.fetchFromServer(),this.getBranchOffices()},methods:{afterUploadDocumentFile:function(e){console.log(e);var t=JSON.parse(e.xhr.response);this.$q.notify({message:t.message.content,position:"top",color:t.result?"positive":"warning"}),t.result&&(this.fetchFromServer(),this.documentFileModal=!1)},openUploadFileModal:function(){this.documentFileModal=!0},generateCsv:function(){var e=null;this.branchOffice&&(e=this.branchOffice.value);var t="https://api_alpez.wasp.mx/"+"customers/csv/".concat(e);window.open(t,"_blank")},fetchFromServer:function(){this.$q.loading.show(),this.qTableRequest({pagination:this.pagination,filter:this.filter})},qTableRequest:function(e){var t=this;return s()(i()().mark((function a(){var o;return i()().wrap((function(a){while(1)switch(a.prev=a.next){case 0:return t.pagination=e.pagination,t.filter=e.filter,o=[],o.branch=t.branchOffice.value,o.pagination=t.pagination,o.filter=t.filter,a.next=8,c["a"].post("/customers/pag",o).then((function(e){var a=e.data;t.$q.loading.hide(),t.data=a.customers,t.pagination.rowsNumber=a.customersCount})).catch((function(e){return e}));case 8:case"end":return a.stop()}}),a)})))()},getBranchOffices:function(){var e=this;c["a"].get("branch-offices/options").then((function(t){var a=t.data;e.branchOfficeOptions=a.options,e.branchOfficeOptions.unshift({value:null,label:"TODOS"})}))},factoryFn:function(e){var t=this;this.$q.loading.show();var a=new FormData;a.append("fileReg",e[0]),a.append("id",this.customerId),c["a"].file("/customers/photo/",a).then((function(e){var a=e.data;t.fetchFromServer(),t.$showNotify(a.message.content,a.result?"positive":"negative"),t.photoModal=!1,t.$q.loading.hide()}))},editPhoto:function(e){var t=this.data.filter((function(t){return Number(t.id)===Number(e)}))[0];this.photoUrl=null!=t.photo?this.serverUrl+"assets/images/customers/"+t.photo:null,this.customerId=t.id,this.photoCode=t.serial,this.photoModal=!0},uploadPhoto:function(){this.$refs.photoRef.upload()},editSelectedRow:function(e){27!==this.roleId&&this.$router.push("/customers/".concat(e))},deleteSelectedRow:function(e){var t=this;this.$q.dialog({title:"Confirmación",message:"¿Desea eliminar este Cliente?",persistent:!0,ok:{label:"Aceptar",color:"green"},cancel:{label:"Cancelar",color:"red"}}).onOk((function(){c["a"].delete("/customers/".concat(e)).then((function(e){var a=e.data;t.$q.notify({message:a.message.content,position:"top",icon:a.result?"thumb_up":"close",color:a.result?"positive":"warning"}),a.result&&t.fetchFromServer()}))})).onCancel((function(){}))},uploadDocumentFile:function(){this.$refs.fileDocumentRef.upload()},fileDocumentUrl:function(){return"".concat("https://api_alpez.wasp.mx/","customers/file")}}},u=d,p=a("2877"),f=a("9989"),m=a("ead5"),h=a("079e"),b=a("9c40"),g=a("eaac"),v=a("27f9"),q=a("0016"),w=a("bd08"),C=a("db86"),y=a("b047"),_=a("05c0"),I=a("24e8"),S=a("f09f"),O=a("a370"),x=a("ee89"),k=a("068f"),F=a("4b7e"),T=a("7f67"),E=a("eebe"),N=a.n(E),A=Object(p["a"])(u,o,r,!1,null,null,null);t["default"]=A.exports;N()(A,"components",{QPage:f["a"],QBreadcrumbs:m["a"],QBreadcrumbsEl:h["a"],QBtn:b["a"],QTable:g["a"],QInput:v["a"],QIcon:q["a"],QTr:w["a"],QTd:C["a"],QChip:y["a"],QTooltip:_["a"],QDialog:I["a"],QCard:S["a"],QCardSection:O["a"],QUploader:x["a"],QImg:k["a"],QCardActions:F["a"]}),N()(A,"directives",{ClosePopup:T["a"]})}}]);
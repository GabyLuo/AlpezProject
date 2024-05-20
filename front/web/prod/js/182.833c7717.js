(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[182],{5569:function(e,t,s){"use strict";s.r(t);var i=function(){var e=this,t=e.$createElement,s=e._self._c||t;return s("q-page",{staticClass:"bg-grey-3"},[s("div",{staticClass:"q-pa-sm panel-header"},[s("div",{staticClass:"row"},[s("div",{staticClass:"col-sm-9"},[s("div",{staticClass:"q-pa-md q-gutter-sm"},[s("q-breadcrumbs",{staticStyle:{"font-size":"20px"},attrs:{align:"left"}},[s("q-breadcrumbs-el",{attrs:{label:"",icon:"home",to:"/"}}),s("q-breadcrumbs-el",{attrs:{label:"Proveedores",to:"/suppliers"}}),s("q-breadcrumbs-el",{attrs:{label:"Editar"},domProps:{textContent:e._s(e.supplier.fields.name)}})],1)],1)])])]),s("div",{staticClass:"q-pa-md bg-grey-3"},[s("div",{staticClass:"row bg-white border-panel"},[s("div",{staticClass:"col q-pa-md"},[s("div",{staticClass:"row q-col-gutter-xs"},[s("div",{staticClass:"col-xs-12 col-sm-3"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.supplier.fields.serial.$error,label:"Código",rules:e.serialRules},on:{input:function(t){e.supplier.fields.serial=t.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"code"}})]},proxy:!0}]),model:{value:e.supplier.fields.serial,callback:function(t){e.$set(e.supplier.fields,"serial",t)},expression:"supplier.fields.serial"}})],1),s("div",{staticClass:"col-xs-12 col-sm-3 text-center"},[s("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",options:[{label:"ACTIVO",value:!0},{label:"INACTIVO",value:!1}],label:"Estatus"},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:e.supplier.fields.active.value?"battery_full":"battery_alert"}})]},proxy:!0}]),model:{value:e.supplier.fields.active,callback:function(t){e.$set(e.supplier.fields,"active",t)},expression:"supplier.fields.active"}})],1),s("div",{staticClass:"col-xs-12 col-sm-3"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.supplier.fields.name.$error,label:"Razón social",rules:e.nameRules},on:{input:function(t){e.supplier.fields.name=t.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"fas fa-signature"}})]},proxy:!0}]),model:{value:e.supplier.fields.name,callback:function(t){e.$set(e.supplier.fields,"name",t)},expression:"supplier.fields.name"}})],1),s("div",{staticClass:"col-xs-12 col-sm-3"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.supplier.fields.tradename.$error,label:"Nombre comercial",rules:e.tradenameRules},on:{input:function(t){e.supplier.fields.tradename=t.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"business"}})]},proxy:!0}]),model:{value:e.supplier.fields.tradename,callback:function(t){e.$set(e.supplier.fields,"tradename",t)},expression:"supplier.fields.tradename"}})],1),s("div",{staticClass:"col-xs-12 col-sm-3"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.supplier.fields.contact_name.$error,label:"Nombre contacto",rules:e.contactNameRules},on:{input:function(t){e.supplier.fields.contact_name=t.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"fas fa-id-card"}})]},proxy:!0}]),model:{value:e.supplier.fields.contact_name,callback:function(t){e.$set(e.supplier.fields,"contact_name",t)},expression:"supplier.fields.contact_name"}})],1),s("div",{staticClass:"col-xs-12 col-sm-3"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.supplier.fields.contact_phone.$error,label:"Oficina",rules:e.contactPhoneRules},on:{input:function(t){e.supplier.fields.contact_phone=t.replace(/[^0-9]/g,"").substr(0,20)}},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"contact_phone"}})]},proxy:!0}]),model:{value:e.supplier.fields.contact_phone,callback:function(t){e.$set(e.supplier.fields,"contact_phone",t)},expression:"supplier.fields.contact_phone"}})],1),s("div",{staticClass:"col-xs-12 col-sm-3"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.supplier.fields.contact_phone_two.$error,label:"Celular",rules:e.contactPhoneRulesTwo},on:{input:function(t){e.supplier.fields.contact_phone_two=t.replace(/[^0-9]/g,"").substr(0,20)}},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"contact_phone"}})]},proxy:!0}]),model:{value:e.supplier.fields.contact_phone_two,callback:function(t){e.$set(e.supplier.fields,"contact_phone_two",t)},expression:"supplier.fields.contact_phone_two"}})],1),s("div",{staticClass:"col-xs-12 col-sm-3"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.supplier.fields.street.$error,label:"Calle",rules:e.streetRules},on:{input:function(t){e.supplier.fields.street=t.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"fas fa-road"}})]},proxy:!0}]),model:{value:e.supplier.fields.street,callback:function(t){e.$set(e.supplier.fields,"street",t)},expression:"supplier.fields.street"}})],1),s("div",{staticClass:"col-xs-12 col-sm-3"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.supplier.fields.outdoor_number.$error,label:"Número ext.",rules:e.outdoorNumberRules},on:{input:function(t){e.supplier.fields.outdoor_number=t.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"fas fa-hashtag"}})]},proxy:!0}]),model:{value:e.supplier.fields.outdoor_number,callback:function(t){e.$set(e.supplier.fields,"outdoor_number",t)},expression:"supplier.fields.outdoor_number"}})],1),s("div",{staticClass:"col-xs-12 col-sm-3"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.supplier.fields.indoor_number.$error,label:"Número int.",rules:e.indoorNumberRules},on:{input:function(t){e.supplier.fields.indoor_number=t.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"fas fa-hashtag"}})]},proxy:!0}]),model:{value:e.supplier.fields.indoor_number,callback:function(t){e.$set(e.supplier.fields,"indoor_number",t)},expression:"supplier.fields.indoor_number"}})],1),s("div",{staticClass:"col-xs-12 col-sm-3"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.supplier.fields.suburb.$error,label:"Colonia",rules:e.suburbRules},on:{input:function(t){e.supplier.fields.suburb=t.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"fas fa-city"}})]},proxy:!0}]),model:{value:e.supplier.fields.suburb,callback:function(t){e.$set(e.supplier.fields,"suburb",t)},expression:"supplier.fields.suburb"}})],1),s("div",{staticClass:"col-xs-12 col-sm-3"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.supplier.fields.municipality.$error,label:"Municipio",rules:e.municipalityRules},on:{input:function(t){e.supplier.fields.municipality=t.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"fas fa-city"}})]},proxy:!0}]),model:{value:e.supplier.fields.municipality,callback:function(t){e.$set(e.supplier.fields,"municipality",t)},expression:"supplier.fields.municipality"}})],1),s("div",{staticClass:"col-xs-12 col-sm-3"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.supplier.fields.state.$error,label:"Estado",rules:e.stateRules},on:{input:function(t){e.supplier.fields.state=t.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"map"}})]},proxy:!0}]),model:{value:e.supplier.fields.state,callback:function(t){e.$set(e.supplier.fields,"state",t)},expression:"supplier.fields.state"}})],1),s("div",{staticClass:"col-xs-12 col-sm-3"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.supplier.fields.country.$error,label:"Pais",rules:e.countryRules},on:{input:function(t){e.supplier.fields.country=t.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"map"}})]},proxy:!0}]),model:{value:e.supplier.fields.country,callback:function(t){e.$set(e.supplier.fields,"country",t)},expression:"supplier.fields.country"}})],1),s("div",{staticClass:"col-xs-12 col-sm-3"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.supplier.fields.zip_code.$error,label:"CP",rules:e.zipCodeRules},on:{input:function(t){e.supplier.fields.zip_code=t.replace(/[^0-9]/g,"").substr(0,5)}},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"fas fa-mail-bulk"}})]},proxy:!0}]),model:{value:e.supplier.fields.zip_code,callback:function(t){e.$set(e.supplier.fields,"zip_code",t)},expression:"supplier.fields.zip_code"}})],1),s("div",{staticClass:"col-xs-12 col-sm-3"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.supplier.fields.rfc.$error,label:"RFC",rules:e.rfcRules},on:{input:function(t){e.supplier.fields.rfc=t.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"insert_drive_file"}})]},proxy:!0}]),model:{value:e.supplier.fields.rfc,callback:function(t){e.$set(e.supplier.fields,"rfc",t)},expression:"supplier.fields.rfc"}})],1),s("div",{staticClass:"col-xs-12 col-sm-2"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",label:"Días de crédito"},on:{input:function(t){e.supplier.fields.credit_days=t.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"insert_drive_file"}})]},proxy:!0}]),model:{value:e.supplier.fields.credit_days,callback:function(t){e.$set(e.supplier.fields,"credit_days",t)},expression:"supplier.fields.credit_days"}})],1),s("div",{staticClass:"col-xs-12 col-sm-2"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.supplier.fields.currency.$error,label:"Moneda",rules:e.currencyRules},on:{input:function(t){e.supplier.fields.currency=t.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"attach_money"}})]},proxy:!0}]),model:{value:e.supplier.fields.currency,callback:function(t){e.$set(e.supplier.fields,"currency",t)},expression:"supplier.fields.currency"}})],1),s("div",{staticClass:"col-xs-12 col-sm-4"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.supplier.fields.payment_method.$error,label:"Forma de pago",rules:e.paymentMethodRules},on:{input:function(t){e.supplier.fields.payment_method=t.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"payment"}})]},proxy:!0}]),model:{value:e.supplier.fields.payment_method,callback:function(t){e.$set(e.supplier.fields,"payment_method",t)},expression:"supplier.fields.payment_method"}})],1),s("div",{staticClass:"col-xs-4"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.supplier.fields.email.$error,label:"Dirección de correo electrónico",rules:e.emailRules},on:{input:function(t){e.supplier.fields.email=t.toLowerCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"email"}})]},proxy:!0}]),model:{value:e.supplier.fields.email,callback:function(t){e.$set(e.supplier.fields,"email",t)},expression:"supplier.fields.email"}})],1),s("div",{staticClass:"col-xs-4"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.supplier.fields.email2.$error,label:"Dirección de correo electrónico 2",rules:e.emailRules},on:{input:function(t){e.supplier.fields.email2=t.toLowerCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"email"}})]},proxy:!0}]),model:{value:e.supplier.fields.email2,callback:function(t){e.$set(e.supplier.fields,"email2",t)},expression:"supplier.fields.email2"}})],1)]),20!==e.roleId?s("div",{staticClass:"row q-mb-sm q-mt-md"},[s("div",{staticClass:"col-xs-12 col-sm-2 offset-sm-10 pull-right"},[s("q-btn",{attrs:{color:"positive",icon:"save",label:"Actualizar"},on:{click:function(t){return e.editSupplier()}}})],1)]):e._e()])]),s("br"),s("div",{staticClass:"bg-white border-panel"},[s("q-tabs",{staticClass:"text-grey",attrs:{dense:"","active-color":"primary","indicator-color":"primary",align:"justify","narrow-indicator":""},model:{value:e.currentTab,callback:function(t){e.currentTab=t},expression:"currentTab"}},[s("q-tab",{attrs:{name:"contacts",label:"Contactos"}})],1),s("q-tab-panels",{attrs:{animated:""},model:{value:e.currentTab,callback:function(t){e.currentTab=t},expression:"currentTab"}},[s("q-tab-panel",{attrs:{name:"contacts"}},[s("div",{staticStyle:{"font-weight":"normal"}},[s("div",{staticClass:"row q-col-gutter-xs q-pa-xs"},[s("div",{staticClass:"col-xs-12 col-sm-3"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.contacts.fields.name.$error,label:"Nombre",rules:e.contactsNameRules},on:{input:function(t){e.contacts.fields.name=t.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"fas fa-signature"}})]},proxy:!0}]),model:{value:e.contacts.fields.name,callback:function(t){e.$set(e.contacts.fields,"name",t)},expression:"contacts.fields.name"}})],1),s("div",{staticClass:"col-xs-12 col-sm-3"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.contacts.fields.tel.$error,label:"Teléfono",rules:e.contactsTelRules},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"fas fa-signature"}})]},proxy:!0}]),model:{value:e.contacts.fields.tel,callback:function(t){e.$set(e.contacts.fields,"tel",t)},expression:"contacts.fields.tel"}})],1),s("div",{staticClass:"col-xs-12 col-sm-3"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.contacts.fields.phone.$error,label:"Celular",rules:e.contactsPhoneRules},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"fas fa-signature"}})]},proxy:!0}]),model:{value:e.contacts.fields.phone,callback:function(t){e.$set(e.contacts.fields,"phone",t)},expression:"contacts.fields.phone"}})],1),s("div",{staticClass:"col-xs-12 col-sm-3"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.contacts.fields.email.$error,label:"Correo electrónico",rules:e.contactsEmailRules},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"fas fa-signature"}})]},proxy:!0}]),model:{value:e.contacts.fields.email,callback:function(t){e.$set(e.contacts.fields,"email",t)},expression:"contacts.fields.email"}})],1),20!==e.roleId?s("div",{staticClass:"col-xs-12 pull-right"},[s("q-btn",{directives:[{name:"show",rawName:"v-show",value:null==e.contacts.fields.id,expression:"contacts.fields.id == null"}],staticStyle:{height:"100%","margin-right":"5px"},attrs:{color:"primary",icon:"add",label:"Agregar"},on:{click:function(t){return e.addContact()}}}),s("q-btn",{directives:[{name:"show",rawName:"v-show",value:null!=e.contacts.fields.id,expression:"contacts.fields.id != null"}],staticStyle:{height:"100%","margin-right":"5px"},attrs:{color:"positive",icon:"save",label:"Guardar"},on:{click:function(t){return e.updateContact()}}}),s("q-btn",{directives:[{name:"show",rawName:"v-show",value:null!=e.contacts.fields.id,expression:"contacts.fields.id != null"}],staticStyle:{height:"100%","margin-right":"5px"},attrs:{color:"negative",icon:"cancel",label:"Cancelar"},on:{click:function(t){return e.cancelEditContact()}}})],1):e._e()]),s("div",{staticClass:"q-col-gutter-xs",staticStyle:{padding:"2%"}},[s("q-table",{attrs:{flat:"",bordered:"",data:e.contactsList,columns:e.contactsColumns,"row-key":"name",pagination:e.pagination},on:{"update:pagination":function(t){e.pagination=t}},scopedSlots:e._u([{key:"body",fn:function(t){return[s("q-tr",{attrs:{props:t}},[s("q-td",{key:"name",staticStyle:{"text-align":"left",width:"20%"},attrs:{props:t}},[e._v(e._s(t.row.name))]),s("q-td",{key:"tel",staticStyle:{"text-align":"left",width:"20%"},attrs:{props:t}},[e._v(e._s(t.row.tel))]),s("q-td",{key:"phone",staticStyle:{"text-align":"left",width:"10%"},attrs:{props:t}},[e._v(e._s(t.row.phone))]),s("q-td",{key:"email",staticStyle:{"text-align":"left",width:"10%"},attrs:{props:t}},[e._v(e._s(t.row.email))]),20!==e.roleId?s("q-td",{key:"actions",staticStyle:{width:"10%"},attrs:{props:t}},[s("q-btn",{attrs:{color:"primary",icon:"fas fa-edit",flat:"",size:"10px"},nativeOn:{click:function(s){return e.editSelectedRowContacts(t.row)}}},[s("q-tooltip",{attrs:{"content-class":"bg-primary"}},[e._v("Editar")])],1),s("q-btn",{attrs:{color:"negative",icon:"fas fa-trash-alt",flat:"",size:"10px"},nativeOn:{click:function(s){return e.deleteSelectedRowContact(t.row.id)}}},[s("q-tooltip",{attrs:{"content-class":"bg-red"}},[e._v("Eliminar")])],1)],1):e._e()],1)]}}])})],1)])]),s("q-tab-panel",{attrs:{name:"dossiers"}},[s("div",{staticStyle:{"font-weight":"normal"}},[s("div",{staticClass:"row q-col-gutter-xs q-pa-xs"},[s("div",{staticClass:"col-xs-12 col-sm-6"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.dossiers.fields.name.$error,label:"Nombre",rules:e.dossiersNameRules},on:{input:function(t){e.dossiers.fields.name=t.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"fas fa-signature"}})]},proxy:!0}]),model:{value:e.dossiers.fields.name,callback:function(t){e.$set(e.dossiers.fields,"name",t)},expression:"dossiers.fields.name"}})],1),s("div",{staticClass:"col-xs-12 col-sm-6"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",label:"Descripción del documento"},on:{input:function(t){e.dossiers.fields.description=t.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"fas fa-signature"}})]},proxy:!0}]),model:{value:e.dossiers.fields.description,callback:function(t){e.$set(e.dossiers.fields,"description",t)},expression:"dossiers.fields.description"}})],1),s("div",{staticClass:"col-xs-12 pull-right"},[s("q-btn",{directives:[{name:"show",rawName:"v-show",value:null==e.dossiers.fields.id,expression:"dossiers.fields.id == null"}],staticStyle:{height:"100%","margin-right":"5px"},attrs:{color:"primary",icon:"add",label:"Agregar"},on:{click:function(t){return e.addDossier()}}}),s("q-btn",{directives:[{name:"show",rawName:"v-show",value:null!=e.dossiers.fields.id,expression:"dossiers.fields.id != null"}],staticStyle:{height:"100%","margin-right":"5px"},attrs:{color:"positive",icon:"save",label:"Guardar"},on:{click:function(t){return e.updateDossier()}}}),s("q-btn",{directives:[{name:"show",rawName:"v-show",value:null!=e.dossiers.fields.id,expression:"dossiers.fields.id != null"}],staticStyle:{height:"100%","margin-right":"5px"},attrs:{color:"negative",icon:"cancel",label:"Cancelar"},on:{click:function(t){return e.cancelEditDossier()}}})],1)]),s("div",{staticClass:"q-col-gutter-xs",staticStyle:{padding:"2%"}},[s("q-table",{attrs:{flat:"",bordered:"",data:e.dossiersList,columns:e.dossiersColumns,"row-key":"name",pagination:e.pagination},on:{"update:pagination":function(t){e.pagination=t}},scopedSlots:e._u([{key:"body",fn:function(t){return[s("q-tr",{attrs:{props:t}},[s("q-td",{key:"name",staticStyle:{"text-align":"left",width:"20%"},attrs:{props:t}},[e._v(e._s(t.row.name))]),s("q-td",{key:"description",staticStyle:{"text-align":"left",width:"20%"},attrs:{props:t}},[e._v(e._s(t.row.description))]),s("q-td",{key:"actions",staticStyle:{width:"10%"},attrs:{props:t}},[s("q-btn",{attrs:{color:"primary",icon:"fas fa-edit",flat:"",size:"10px"},nativeOn:{click:function(s){return e.editSelectedRowDossier(t.row)}}},[s("q-tooltip",{attrs:{"content-class":"bg-primary"}},[e._v("Editar")])],1),s("q-btn",{attrs:{color:"negative",icon:"fas fa-trash-alt",flat:"",size:"10px"},nativeOn:{click:function(s){return e.deleteSelectedRowDossier(t.row.id)}}},[s("q-tooltip",{attrs:{"content-class":"bg-red"}},[e._v("Eliminar")])],1),s("q-btn",{attrs:{color:"negative",icon:"fas fa-file-pdf",flat:"",size:"10px"},nativeOn:{click:function(s){return e.openDossierFile(t.row.id)}}},[s("q-tooltip",{attrs:{"content-class":"bg-red"}},[e._v("Agregar Documento")])],1)],1)],1)]}}])})],1)])])],1),s("q-dialog",{attrs:{persistent:""},model:{value:e.openDossierFileModal,callback:function(t){e.openDossierFileModal=t},expression:"openDossierFileModal"}},[s("q-card",{staticStyle:{"min-width":"500px"}},[s("q-card-section",{staticClass:"bg-primary"},[s("div",{staticClass:"text-h6 text-white text-center"},[e._v("Seleccionar Archivo")])]),s("q-card-section",[s("div",{staticClass:"col-xs-6 q-pa-xs vertical-middle",attrs:{align:"center"}},[s("q-uploader",{ref:"fileDocumentCitationRef",attrs:{method:"POST",label:"Elegir archivo a subir","max-files":"1",flat:"","auto-upload":"","no-thumbnails":"",headers:[{name:"Authorization",value:"Bearer "+this.JWT}]},on:{uploaded:e.afterUploadDocumentFileCitation}})],1)]),s("q-card-actions",{staticClass:"text-primary",attrs:{align:"center"}},[s("q-btn",{directives:[{name:"close-popup",rawName:"v-close-popup"}],attrs:{color:"red",label:"Cancelar"}}),s("q-btn",{attrs:{color:"positive",label:"Generar"},on:{click:function(t){return e.request()}}})],1)],1)],1)],1)])])},r=[],o=s("ded3"),n=s.n(o),l=(s("b0c0"),s("99af"),s("a4d3"),s("e01a"),s("aabb")),a=s("b5ae"),c=a.required,u=a.maxLength,d=a.minLength,p=a.integer,f=a.minValue,m=a.email,h={name:"EditSupplier",validations:{supplier:{fields:{serial:{required:c,integer:p,maxLength:u(10)},name:{required:c,maxLength:u(100)},tradename:{required:c,maxLength:u(100)},contact_name:{maxLength:u(100)},contact_phone:{minLength:d(10),maxLength:u(20),integer:p,minValue:f(1)},contact_phone_two:{minLength:d(10),maxLength:u(20),integer:p,minValue:f(1)},street:{required:c,maxLength:u(100)},outdoor_number:{required:c,maxLength:u(10)},indoor_number:{maxLength:u(10)},suburb:{required:c,maxLength:u(100)},municipality:{required:c,maxLength:u(100)},state:{required:c,maxLength:u(100)},zip_code:{required:c,maxLength:u(5),integer:p,minValue:f(1)},rfc:{required:c,maxLength:u(20)},payment_method:{required:c,maxLength:u(100)},currency:{required:c,maxLength:u(20)},email:{required:c,email:m},email2:{email:m},country:{required:c},credit_days:{required:c}}},contacts:{fields:{name:{required:c},phone:{minLength:d(10),maxLength:u(20),integer:p,minValue:f(1)},email:{required:c,email:m},tel:{minLength:d(10),maxLength:u(20),integer:p,minValue:f(1)}}},dossiers:{fields:{name:{required:c}}}},data:function(){return{currentTab:"contacts",supplier:{fields:{id:null,serial:null,name:null,tradename:null,contact_name:null,contact_phone:null,contact_phone_two:null,street:null,outdoor_number:null,indoor_number:null,suburb:null,municipality:null,state:null,zip_code:null,rfc:null,term:null,payment_method:null,currency:null,active:!1,email:null,email2:null,country:null,branch_id:7,credit_days:null}},contacts:{fields:{id:null,name:null,phone:null,email:null,tel:null}},dossiers:{fields:{id:null,name:null,description:null}},openDossierFileModal:!1,contactsColumns:[{name:"name",align:"center",label:"Nombre".toUpperCase(),field:"name",style:"width: 20%",sortable:!0},{name:"tel",align:"center",label:"Teléfono".toUpperCase(),field:"tel",style:"width: 20%",sortable:!0},{name:"phone",align:"center",label:"Celular".toUpperCase(),field:"phone",style:"width: 20%",sortable:!0},{name:"email",align:"center",label:"Correo Electrónico".toUpperCase(),field:"email",style:"width: 20%",sortable:!0},{name:"actions",align:"center",label:"Acciones".toUpperCase(),field:"actions",style:"width: 20%",sortable:!1}],dossiersColumns:[{name:"name",align:"center",label:"Nombre".toUpperCase(),field:"name",style:"width: 20%",sortable:!0},{name:"description",align:"center",label:"Descripción del documento".toUpperCase(),field:"description",style:"width: 20%",sortable:!0},{name:"actions",align:"center",label:"Acciones".toUpperCase(),field:"actions",style:"width: 20%",sortable:!1}],pagination:{sortBy:"code",descending:!1,rowsPerPage:25},branchesList:[],contactsList:[],dossiersList:[]}},computed:{roleId:function(){var e=this.$store.getters["users/rol"];return parseInt(e)},contactsNameRules:function(e){var t=this;return[function(e){return t.$v.contacts.fields.name.required||"El campo Nombre es requerido."}]},JWT:function(){return localStorage.getItem("JWT")},fileDocumentUrlCitation:function(){return"".concat("https://api_alpez.wasp.mx/","shopping-carts/file3/").concat(this.$route.params.id)},dossiersNameRules:function(e){var t=this;return[function(e){return t.$v.dossiers.fields.name.required||"El campo Nombre de es requerido."}]},contactsPhoneRules:function(e){var t=this;return[function(e){return t.$v.contacts.fields.phone.integer||"El campo Celular debe ser numérico."},function(e){return t.$v.contacts.fields.phone.minValue||"El campo Celular debe ser positivo."},function(e){return t.$v.contacts.fields.phone.minLength||"El campo Celular debe tener al menos 10 dígitos."},function(e){return t.$v.contacts.fields.phone.maxLength||"El campo Celular no debe exceder los 20 dígitos."}]},contactsEmailRules:function(e){var t=this;return[function(e){return t.$v.contacts.fields.email.required||"El campo Correo electrónico es requerido."},function(e){return t.$v.contacts.fields.email.email||"El campo Correo electrónico debe contener una dirección de correo electrónico válida."}]},contactsTelRules:function(e){var t=this;return[function(e){return t.$v.contacts.fields.tel.integer||"El campo Teléfono debe ser numérico."},function(e){return t.$v.contacts.fields.tel.minValue||"El campo Teléfono debe ser positivo."},function(e){return t.$v.contacts.fields.tel.minLength||"El campo Teléfono debe tener al menos 10 dígitos."},function(e){return t.$v.contacts.fields.tel.maxLength||"El campo Teléfono no debe exceder los 20 dígitos."}]},serialRules:function(e){var t=this;return[function(e){return t.$v.supplier.fields.serial.required||"El campo Código es requerido."},function(e){return t.$v.supplier.fields.serial.maxLength||"El campo Código no debe exceder los 10 dígitos."},function(e){return t.$v.supplier.fields.serial.integer||"El campo Código debe ser numérico."}]},nameRules:function(e){var t=this;return[function(e){return t.$v.supplier.fields.name.required||"El campo Nombre es requerido."},function(e){return t.$v.supplier.fields.name.maxLength||"El campo Nombre no debe exceder los 100 dígitos."}]},tradenameRules:function(e){var t=this;return[function(e){return t.$v.supplier.fields.tradename.required||"El campo Nombre comercial es requerido."},function(e){return t.$v.supplier.fields.tradename.maxLength||"El campo Nombre comercial no debe exceder los 100 dígitos."}]},contactNameRules:function(e){var t=this;return[function(e){return t.$v.supplier.fields.contact_name.maxLength||"El campo Nombre Contacto no debe exceder los 100 dígitos."}]},contactPhoneRules:function(e){var t=this;return[function(e){return t.$v.supplier.fields.contact_phone.integer||"El campo Teléfono debe ser numérico."},function(e){return t.$v.supplier.fields.contact_phone.minValue||"El campo Teléfono debe ser positivo."},function(e){return t.$v.supplier.fields.contact_phone.minLength||"El campo Teléfono debe tener al menos 10 dígitos."},function(e){return t.$v.supplier.fields.contact_phone.maxLength||"El campo Teléfono no debe exceder los 20 dígitos."}]},contactPhoneRulesTwo:function(e){var t=this;return[function(e){return t.$v.supplier.fields.contact_phone.integer||"El campo Teléfono 2 debe ser numérico."},function(e){return t.$v.supplier.fields.contact_phone.minValue||"El campo Teléfono 2 debe ser positivo."},function(e){return t.$v.supplier.fields.contact_phone.minLength||"El campo Teléfono 2 debe tener al menos 10 dígitos."},function(e){return t.$v.supplier.fields.contact_phone.maxLength||"El campo Teléfono 2 no debe exceder los 20 dígitos."}]},streetRules:function(e){var t=this;return[function(e){return t.$v.supplier.fields.street.required||"El campo Calle es requerido."},function(e){return t.$v.supplier.fields.street.maxLength||"El campo Calle no debe exceder los 100 dígitos."}]},outdoorNumberRules:function(e){var t=this;return[function(e){return t.$v.supplier.fields.outdoor_number.required||"El campo Número ext. es requerido."},function(e){return t.$v.supplier.fields.outdoor_number.maxLength||"El campo Número ext. no debe exceder los 10 dígitos."}]},indoorNumberRules:function(e){var t=this;return[function(e){return t.$v.supplier.fields.indoor_number.maxLength||"El campo Número int. no debe exceder los 10 dígitos."}]},suburbRules:function(e){var t=this;return[function(e){return t.$v.supplier.fields.suburb.required||"El campo Colonia es requerido."},function(e){return t.$v.supplier.fields.suburb.maxLength||"El campo Colonia no debe exceder los 100 dígitos."}]},municipalityRules:function(e){var t=this;return[function(e){return t.$v.supplier.fields.municipality.required||"El campo Municipio es requerido."},function(e){return t.$v.supplier.fields.municipality.maxLength||"El campo Municipio no debe exceder los 100 dígitos."}]},stateRules:function(e){var t=this;return[function(e){return t.$v.supplier.fields.state.required||"El campo Estado es requerido."},function(e){return t.$v.supplier.fields.state.maxLength||"El campo Estado no debe exceder los 100 dígitos."}]},countryRules:function(e){var t=this;return[function(e){return t.$v.supplier.fields.state.required||"El campo Pais es requerido."}]},zipCodeRules:function(e){var t=this;return[function(e){return t.$v.supplier.fields.zip_code.required||"El campo CP es requerido."},function(e){return t.$v.supplier.fields.zip_code.integer||"El campo CP debe ser numérico."},function(e){return t.$v.supplier.fields.zip_code.minValue||"El campo CP debe ser positivo."},function(e){return t.$v.supplier.fields.zip_code.maxLength||"El campo CP no debe exceder los 5 dígitos."}]},rfcRules:function(e){var t=this;return[function(e){return t.$v.supplier.fields.rfc.required||"El campo RFC es requerido."},function(e){return t.$v.supplier.fields.rfc.maxLength||"El campo RFC no debe exceder los 20 dígitos."}]},paymentMethodRules:function(e){var t=this;return[function(e){return t.$v.supplier.fields.payment_method.required||"El campo Forma de pago es requerido."},function(e){return t.$v.supplier.fields.payment_method.maxLength||"El campo Forma de pago no debe exceder los 100 dígitos."}]},currencyRules:function(e){var t=this;return[function(e){return t.$v.supplier.fields.currency.required||"El campo Moneda es requerido."},function(e){return t.$v.supplier.fields.currency.maxLength||"El campo Moneda no debe exceder los 20 dígitos."}]},emailRules:function(e){var t=this;return[function(e){return t.$v.supplier.fields.email.required||"El campo Dirección de correo electrónico es requerido."},function(e){return t.$v.supplier.fields.email.email||"El campo Dirección de correo electrónico debe contener una dirección de correo electrónico válida."}]}},mounted:function(){this.getBranchesList()},created:function(){var e=this;this.$q.loading.show();var t=this.$route.params.id;l["a"].get("/suppliers/".concat(t)).then((function(t){var s=t.data;s.supplier?(e.supplier.fields=s.supplier,s.supplier.active?e.supplier.fields.active={label:"ACTIVO",value:!0}:e.supplier.fields.active={label:"INACTIVO",value:!1},l["a"].get("/supplier-contacts/supplier/".concat(e.$route.params.id)).then((function(t){var s=t.data;e.contactsList=s.contacts,console.log(s.contacts),e.$q.loading.hide()})),l["a"].get("/supplier-dossiers/all/".concat(e.$route.params.id)).then((function(t){var s=t.data;e.dossiersList=s.dossiers,console.log(s.dossiers),e.$q.loading.hide()}))):e.$router.push("/suppliers")}))},beforeRouteEnter:function(e,t,s){s((function(e){var t=e.$store.getters["users/rol"];console.log(t),1===t||3===t||7===t||2===t||20===t||4===t||27===t||22===t||17===t||28===t?s():s("/")}))},methods:{clearContactsInputs:function(){this.contacts.fields.name=null,this.contacts.fields.phone=null,this.contacts.fields.tel=null,this.contacts.fields.email=null},afterUploadDocumentFileCitation:function(e){var t=JSON.parse(e.xhr.response);this.$q.notify({message:t.message.content,position:"top",color:t.result?"positive":"warning"})},clearDossierInputs:function(){this.dossiers.fields.name=null,this.dossiers.fields.description=null},openDossierFile:function(e){this.openDossierFileModal=!0},addContact:function(){var e=this;if(this.$v.contacts.fields.$reset(),this.$v.contacts.fields.$touch(),this.$v.contacts.fields.$error)return this.$q.dialog({title:"Error",message:"Por favor, verifique las validaciones.",persistent:!0}),!1;this.$q.loading.show();var t=n()({},this.contacts.fields);t.supplier_id=this.$route.params.id,console.log(t),l["a"].post("/supplier-contacts",t).then((function(t){var s=t.data;e.$q.notify({message:s.message.content,position:"top",color:s.result?"positive":"warning"}),s.result?(e.clearContactsInputs(),l["a"].get("/supplier-contacts/supplier/".concat(e.$route.params.id)).then((function(t){var s=t.data;e.contactsList=s.contacts,e.$q.loading.hide()}))):e.$q.loading.hide()}))},addDossier:function(){var e=this;if(this.$v.dossiers.fields.$reset(),this.$v.dossiers.fields.$touch(),this.$v.dossiers.fields.$error)return this.$q.dialog({title:"Error",message:"Por favor, verifique las validaciones.",persistent:!0}),!1;this.$q.loading.show();var t=n()({},this.dossiers.fields);t.supplier_id=this.$route.params.id,console.log(t),l["a"].post("/supplier-dossiers",t).then((function(t){var s=t.data;e.$q.notify({message:s.message.content,position:"top",color:s.result?"positive":"warning"}),s.result?(e.clearDossierInputs(),l["a"].get("/supplier-dossiers/all/".concat(e.$route.params.id)).then((function(t){var s=t.data;e.dossiersList=s.dossiers,e.$q.loading.hide()}))):e.$q.loading.hide()}))},updateContact:function(){var e=this;if(this.$v.contacts.fields.$reset(),this.$v.contacts.fields.$touch(),this.$v.contacts.fields.$error)return this.$q.dialog({title:"Error",message:"Por favor, verifique las validaciones.",persistent:!0}),!1;this.$q.loading.show();var t=n()({},this.contacts.fields);t.supplier_id=this.$route.params.id,l["a"].put("/supplier-contacts/".concat(t.id),t).then((function(t){var s=t.data;e.$q.notify({message:s.message.content,position:"top",color:s.result?"positive":"warning"}),s.result?(e.clearContactsInputs(),l["a"].get("/supplier-contacts/supplier/".concat(e.$route.params.id)).then((function(t){var s=t.data;e.contactsList=s.contacts,e.$q.loading.hide()}))):e.$q.loading.hide()}))},updateDossier:function(){var e=this;if(this.$v.dossiers.fields.$reset(),this.$v.dossiers.fields.$touch(),this.$v.dossiers.fields.$error)return this.$q.dialog({title:"Error",message:"Por favor, verifique las validaciones.",persistent:!0}),!1;this.$q.loading.show();var t=n()({},this.dossiers.fields);t.supplier_id=this.$route.params.id,l["a"].put("/supplier-dossiers/".concat(t.id),t).then((function(t){var s=t.data;e.$q.notify({message:s.message.content,position:"top",color:s.result?"positive":"warning"}),s.result?(e.clearDossierInputs(),l["a"].get("/supplier-dossiers/all/".concat(e.$route.params.id)).then((function(t){var s=t.data;e.dossiersList=s.dossiers,e.$q.loading.hide()}))):e.$q.loading.hide()}))},editSelectedRowContacts:function(e){this.contacts.fields.id=e.id,this.contacts.fields.name=e.name,this.contacts.fields.phone=e.phone,this.contacts.fields.tel=e.tel,this.contacts.fields.email=e.email},cancelEditContact:function(){this.contacts.fields.id=null,this.contacts.fields.name=null,this.contacts.fields.phone=null,this.contacts.fields.tel=null,this.contacts.fields.email=null},editSelectedRowDossier:function(e){this.dossiers.fields.id=e.id,this.dossiers.fields.name=e.name,this.dossiers.fields.description=e.description},cancelEditDossier:function(){this.dossiers.fields.id=null,this.dossiers.fields.name=null,this.dossiers.fields.description=null},deleteSelectedRowContact:function(e){var t=this;this.$q.loading.show(),l["a"].delete("/supplier-contacts/".concat(e)).then((function(e){var s=e.data;t.$q.notify({message:s.message.content,position:"top",color:s.result?"positive":"warning"}),s.result?l["a"].get("/supplier-contacts/supplier/".concat(t.$route.params.id)).then((function(e){var s=e.data;t.contactsList=s.contacts,t.$q.loading.hide()})):t.$q.loading.hide()}))},deleteSelectedRowDossier:function(e){var t=this;this.$q.loading.show(),l["a"].delete("/supplier-dossiers/".concat(e)).then((function(e){var s=e.data;t.$q.notify({message:s.message.content,position:"top",color:s.result?"positive":"warning"}),s.result?l["a"].get("/supplier-dossiers/all/".concat(t.$route.params.id)).then((function(e){var s=e.data;t.dossiersList=s.dossiers,t.$q.loading.hide()})):t.$q.loading.hide()}))},getBranchesList:function(){var e=this;this.$q.loading.show(),l["a"].get("/branch-offices/options").then((function(t){e.$q.loading.hide(),e.branchesList=t.data.options}))},updateSupplierFields:function(){this.$v.supplier.fields.$reset(),this.$v.supplier.fields.$touch()},editSupplier:function(){var e=this;if(this.$v.supplier.fields.$reset(),this.$v.supplier.fields.$touch(),this.$v.supplier.fields.$error)return this.$q.dialog({title:"Error",message:"Por favor, verifique las validaciones.",persistent:!0}),!1;this.$q.loading.show();var t=n()({},this.supplier.fields);t.active=t.active.value,l["a"].put("/suppliers/".concat(t.id),t).then((function(t){var s=t.data;e.$q.notify({message:s.message.content,position:"top",color:s.result?"positive":"warning"}),s.result?(e.$q.loading.hide(),e.$router.push("/suppliers")):e.$q.loading.hide()}))}}},v=h,g=s("2877"),b=s("9989"),$=s("ead5"),q=s("079e"),y=s("27f9"),x=s("0016"),_=s("ddd8"),C=s("9c40"),k=s("429b"),w=s("7460"),E=s("adad"),S=s("823b"),L=s("eaac"),R=s("bd08"),T=s("db86"),N=s("05c0"),D=s("24e8"),U=s("f09f"),P=s("a370"),z=s("ee89"),I=s("4b7e"),Q=s("7f67"),F=s("eebe"),V=s.n(F),A=Object(g["a"])(v,i,r,!1,null,null,null);t["default"]=A.exports;V()(A,"components",{QPage:b["a"],QBreadcrumbs:$["a"],QBreadcrumbsEl:q["a"],QInput:y["a"],QIcon:x["a"],QSelect:_["a"],QBtn:C["a"],QTabs:k["a"],QTab:w["a"],QTabPanels:E["a"],QTabPanel:S["a"],QTable:L["a"],QTr:R["a"],QTd:T["a"],QTooltip:N["a"],QDialog:D["a"],QCard:U["a"],QCardSection:P["a"],QUploader:z["a"],QCardActions:I["a"]}),V()(A,"directives",{ClosePopup:Q["a"]})}}]);
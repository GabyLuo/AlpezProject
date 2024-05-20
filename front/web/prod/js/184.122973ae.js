(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[184],{f1bc:function(e,r,i){"use strict";i.r(r);var s=function(){var e=this,r=e.$createElement,i=e._self._c||r;return i("q-page",{staticClass:"bg-grey-3"},[i("div",{staticClass:"q-pa-sm panel-header"},[i("div",{staticClass:"row"},[i("div",{staticClass:"col-sm-9"},[i("div",{staticClass:"q-pa-md q-gutter-sm"},[i("q-breadcrumbs",{staticStyle:{"font-size":"20px"},attrs:{align:"left"}},[i("q-breadcrumbs-el",{attrs:{label:"",icon:"home",to:"/"}}),i("q-breadcrumbs-el",{attrs:{label:"Proveedores",to:"/suppliers"}}),i("q-breadcrumbs-el",{attrs:{label:"Nuevo Proveedor"}})],1)],1)])])]),i("div",{staticClass:"q-pa-md bg-grey-3"},[i("div",{staticClass:"row bg-white border-panel"},[i("div",{staticClass:"col q-pa-md"},[i("div",{staticClass:"row q-mb-sm"}),i("div",{staticClass:"row q-col-gutter-xs"},[i("div",{staticClass:"col-xs-12 col-sm-3"},[i("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.supplier.fields.serial.$error,label:"Código",rules:e.serialRules},on:{input:function(r){e.supplier.fields.serial=r.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[i("q-icon",{attrs:{name:"code"}})]},proxy:!0}]),model:{value:e.supplier.fields.serial,callback:function(r){e.$set(e.supplier.fields,"serial",r)},expression:"supplier.fields.serial"}})],1),i("div",{staticClass:"col-xs-12 col-sm-3 text-center"},[i("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",options:[{label:"ACTIVO",value:!0},{label:"INACTIVO",value:!1}],label:"Estatus"},scopedSlots:e._u([{key:"prepend",fn:function(){return[i("q-icon",{attrs:{name:e.supplier.fields.active.value?"battery_full":"battery_alert"}})]},proxy:!0}]),model:{value:e.supplier.fields.active,callback:function(r){e.$set(e.supplier.fields,"active",r)},expression:"supplier.fields.active"}})],1),i("div",{staticClass:"col-xs-12 col-sm-3"},[i("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.supplier.fields.name.$error,label:"Razón social",rules:e.nameRules},on:{input:function(r){e.supplier.fields.name=r.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[i("q-icon",{attrs:{name:"fas fa-signature"}})]},proxy:!0}]),model:{value:e.supplier.fields.name,callback:function(r){e.$set(e.supplier.fields,"name",r)},expression:"supplier.fields.name"}})],1),i("div",{staticClass:"col-xs-12 col-sm-3"},[i("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.supplier.fields.tradename.$error,label:"Nombre comercial",rules:e.tradenameRules},on:{input:function(r){e.supplier.fields.tradename=r.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[i("q-icon",{attrs:{name:"business"}})]},proxy:!0}]),model:{value:e.supplier.fields.tradename,callback:function(r){e.$set(e.supplier.fields,"tradename",r)},expression:"supplier.fields.tradename"}})],1),i("div",{staticClass:"col-xs-12 col-sm-3"},[i("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.supplier.fields.contact_name.$error,label:"Nombre contacto",rules:e.contactNameRules},on:{input:function(r){e.supplier.fields.contact_name=r.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[i("q-icon",{attrs:{name:"fas fa-id-card"}})]},proxy:!0}]),model:{value:e.supplier.fields.contact_name,callback:function(r){e.$set(e.supplier.fields,"contact_name",r)},expression:"supplier.fields.contact_name"}})],1),i("div",{staticClass:"col-xs-12 col-sm-3"},[i("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.supplier.fields.contact_phone.$error,label:"Oficina",rules:e.contactPhoneRules},on:{input:function(r){e.supplier.fields.contact_phone=r.replace(/[^0-9]/g,"").substr(0,20)}},scopedSlots:e._u([{key:"prepend",fn:function(){return[i("q-icon",{attrs:{name:"contact_phone"}})]},proxy:!0}]),model:{value:e.supplier.fields.contact_phone,callback:function(r){e.$set(e.supplier.fields,"contact_phone",r)},expression:"supplier.fields.contact_phone"}})],1),i("div",{staticClass:"col-xs-12 col-sm-3"},[i("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.supplier.fields.contact_phone_two.$error,label:"Celular",rules:e.contactPhoneRulesTwo},on:{input:function(r){e.supplier.fields.contact_phone_two=r.replace(/[^0-9]/g,"").substr(0,20)}},scopedSlots:e._u([{key:"prepend",fn:function(){return[i("q-icon",{attrs:{name:"contact_phone"}})]},proxy:!0}]),model:{value:e.supplier.fields.contact_phone_two,callback:function(r){e.$set(e.supplier.fields,"contact_phone_two",r)},expression:"supplier.fields.contact_phone_two"}})],1),i("div",{staticClass:"col-xs-12 col-sm-3"},[i("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.supplier.fields.street.$error,label:"Calle",rules:e.streetRules},on:{input:function(r){e.supplier.fields.street=r.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[i("q-icon",{attrs:{name:"fas fa-road"}})]},proxy:!0}]),model:{value:e.supplier.fields.street,callback:function(r){e.$set(e.supplier.fields,"street",r)},expression:"supplier.fields.street"}})],1),i("div",{staticClass:"col-xs-12 col-sm-3"},[i("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.supplier.fields.outdoor_number.$error,label:"Número ext.",rules:e.outdoorNumberRules},on:{input:function(r){e.supplier.fields.outdoor_number=r.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[i("q-icon",{attrs:{name:"fas fa-hashtag"}})]},proxy:!0}]),model:{value:e.supplier.fields.outdoor_number,callback:function(r){e.$set(e.supplier.fields,"outdoor_number",r)},expression:"supplier.fields.outdoor_number"}})],1),i("div",{staticClass:"col-xs-12 col-sm-3"},[i("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.supplier.fields.indoor_number.$error,label:"Número int.",rules:e.indoorNumberRules},on:{input:function(r){e.supplier.fields.indoor_number=r.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[i("q-icon",{attrs:{name:"fas fa-hashtag"}})]},proxy:!0}]),model:{value:e.supplier.fields.indoor_number,callback:function(r){e.$set(e.supplier.fields,"indoor_number",r)},expression:"supplier.fields.indoor_number"}})],1),i("div",{staticClass:"col-xs-12 col-sm-3"},[i("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.supplier.fields.suburb.$error,label:"Colonia",rules:e.suburbRules},on:{input:function(r){e.supplier.fields.suburb=r.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[i("q-icon",{attrs:{name:"fas fa-city"}})]},proxy:!0}]),model:{value:e.supplier.fields.suburb,callback:function(r){e.$set(e.supplier.fields,"suburb",r)},expression:"supplier.fields.suburb"}})],1),i("div",{staticClass:"col-xs-12 col-sm-3"},[i("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.supplier.fields.municipality.$error,label:"Municipio",rules:e.municipalityRules},on:{input:function(r){e.supplier.fields.municipality=r.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[i("q-icon",{attrs:{name:"fas fa-city"}})]},proxy:!0}]),model:{value:e.supplier.fields.municipality,callback:function(r){e.$set(e.supplier.fields,"municipality",r)},expression:"supplier.fields.municipality"}})],1),i("div",{staticClass:"col-xs-12 col-sm-3"},[i("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.supplier.fields.state.$error,label:"Estado",rules:e.stateRules},on:{input:function(r){e.supplier.fields.state=r.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[i("q-icon",{attrs:{name:"map"}})]},proxy:!0}]),model:{value:e.supplier.fields.state,callback:function(r){e.$set(e.supplier.fields,"state",r)},expression:"supplier.fields.state"}})],1),i("div",{staticClass:"col-xs-12 col-sm-3"},[i("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.supplier.fields.country.$error,label:"Pais",rules:e.countryRules},on:{input:function(r){e.supplier.fields.country=r.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[i("q-icon",{attrs:{name:"map"}})]},proxy:!0}]),model:{value:e.supplier.fields.country,callback:function(r){e.$set(e.supplier.fields,"country",r)},expression:"supplier.fields.country"}})],1),i("div",{staticClass:"col-xs-12 col-sm-3"},[i("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.supplier.fields.zip_code.$error,label:"CP",rules:e.zipCodeRules},on:{input:function(r){e.supplier.fields.zip_code=r.replace(/[^0-9]/g,"").substr(0,5)}},scopedSlots:e._u([{key:"prepend",fn:function(){return[i("q-icon",{attrs:{name:"fas fa-mail-bulk"}})]},proxy:!0}]),model:{value:e.supplier.fields.zip_code,callback:function(r){e.$set(e.supplier.fields,"zip_code",r)},expression:"supplier.fields.zip_code"}})],1),i("div",{staticClass:"col-xs-12 col-sm-3"},[i("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.supplier.fields.rfc.$error,label:"RFC",rules:e.rfcRules},on:{input:function(r){e.supplier.fields.rfc=r.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[i("q-icon",{attrs:{name:"insert_drive_file"}})]},proxy:!0}]),model:{value:e.supplier.fields.rfc,callback:function(r){e.$set(e.supplier.fields,"rfc",r)},expression:"supplier.fields.rfc"}})],1),i("div",{staticClass:"col-xs-12 col-sm-2"},[i("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",label:"Días de crédito",rules:e.creditDaysRules,error:e.$v.supplier.fields.credit_days.$error},scopedSlots:e._u([{key:"prepend",fn:function(){return[i("q-icon",{attrs:{name:"insert_drive_file"}})]},proxy:!0}]),model:{value:e.supplier.fields.credit_days,callback:function(r){e.$set(e.supplier.fields,"credit_days",r)},expression:"supplier.fields.credit_days"}})],1),i("div",{staticClass:"col-xs-12 col-sm-2"},[i("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.supplier.fields.currency.$error,label:"Moneda",rules:e.currencyRules},on:{input:function(r){e.supplier.fields.currency=r.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[i("q-icon",{attrs:{name:"attach_money"}})]},proxy:!0}]),model:{value:e.supplier.fields.currency,callback:function(r){e.$set(e.supplier.fields,"currency",r)},expression:"supplier.fields.currency"}})],1),i("div",{staticClass:"col-xs-12 col-sm-4"},[i("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.supplier.fields.payment_method.$error,label:"Forma de pago",rules:e.paymentMethodRules},on:{input:function(r){e.supplier.fields.payment_method=r.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[i("q-icon",{attrs:{name:"payment"}})]},proxy:!0}]),model:{value:e.supplier.fields.payment_method,callback:function(r){e.$set(e.supplier.fields,"payment_method",r)},expression:"supplier.fields.payment_method"}})],1),i("div",{staticClass:"col-xs-4"},[i("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.supplier.fields.email.$error,label:"Dirección de correo electrónico",rules:e.emailRules},on:{input:function(r){e.supplier.fields.email=r.toLowerCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[i("q-icon",{attrs:{name:"email"}})]},proxy:!0}]),model:{value:e.supplier.fields.email,callback:function(r){e.$set(e.supplier.fields,"email",r)},expression:"supplier.fields.email"}})],1),i("div",{staticClass:"col-xs-4"},[i("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.supplier.fields.email2.$error,label:"Dirección de correo electrónico 2",rules:e.emailRules},on:{input:function(r){e.supplier.fields.email2=r.toLowerCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[i("q-icon",{attrs:{name:"email"}})]},proxy:!0}]),model:{value:e.supplier.fields.email2,callback:function(r){e.$set(e.supplier.fields,"email2",r)},expression:"supplier.fields.email2"}})],1)]),i("div",{staticClass:"row q-mb-sm q-mt-md"},[i("div",{staticClass:"col-xs-12 col-sm-2 offset-sm-10 pull-right"},[i("q-btn",{attrs:{color:"positive",icon:"save",label:"Guardar"},on:{click:function(r){return e.createSupplier()}}})],1)])])])])])},l=[],o=i("ded3"),t=i.n(o),n=(i("b0c0"),i("aabb")),u=i("b5ae"),a=u.required,c=u.maxLength,p=u.minLength,d=u.integer,f=u.minValue,m=u.email,b={name:"NewSupplier",validations:{supplier:{fields:{serial:{required:a,integer:d,maxLength:c(10)},name:{required:a,maxLength:c(100)},tradename:{required:a,maxLength:c(100)},contact_name:{maxLength:c(100)},contact_phone:{minLength:p(10),maxLength:c(20),integer:d,minValue:f(1)},contact_phone_two:{minLength:p(10),maxLength:c(20),integer:d,minValue:f(1)},street:{required:a,maxLength:c(100)},outdoor_number:{required:a,maxLength:c(10)},indoor_number:{maxLength:c(10)},suburb:{required:a,maxLength:c(100)},municipality:{required:a,maxLength:c(100)},state:{required:a,maxLength:c(100)},zip_code:{required:a,maxLength:c(5),integer:d,minValue:f(1)},rfc:{required:a,maxLength:c(20)},payment_method:{required:a,maxLength:c(100)},currency:{required:a,maxLength:c(20)},email:{required:a,email:m},email2:{email:m},country:{required:a},credit_days:{required:a,integer:d}}}},data:function(){return{supplier:{fields:{serial:null,name:null,tradename:null,contact_name:null,contact_phone:null,contact_phone_two:null,street:null,outdoor_number:null,indoor_number:null,suburb:null,municipality:null,state:null,zip_code:null,rfc:null,term:null,payment_method:null,currency:null,country:null,branch_id:6,credit_days:0,active:{label:"ACTIVO",value:!0},email2:null}},branchesList:[]}},created:function(){var e=this;n["a"].get("/branch-offices/options").then((function(r){e.branchesList=r.data.options}))},computed:{serialRules:function(e){var r=this;return[function(e){return r.$v.supplier.fields.serial.required||"El campo Código es requerido."},function(e){return r.$v.supplier.fields.serial.maxLength||"El campo Código no debe exceder los 10 dígitos."},function(e){return r.$v.supplier.fields.serial.integer||"El campo Código debe ser numérico."}]},nameRules:function(e){var r=this;return[function(e){return r.$v.supplier.fields.name.required||"El campo Nombre es requerido."},function(e){return r.$v.supplier.fields.name.maxLength||"El campo Nombre no debe exceder los 100 dígitos."}]},tradenameRules:function(e){var r=this;return[function(e){return r.$v.supplier.fields.tradename.required||"El campo Nombre comercial es requerido."},function(e){return r.$v.supplier.fields.tradename.maxLength||"El campo Nombre comercial no debe exceder los 100 dígitos."}]},contactNameRules:function(e){var r=this;return[function(e){return r.$v.supplier.fields.contact_name.maxLength||"El campo Nombre Contacto no debe exceder los 100 dígitos."}]},contactPhoneRules:function(e){var r=this;return[function(e){return r.$v.supplier.fields.contact_phone.integer||"El campo Teléfono debe ser numérico."},function(e){return r.$v.supplier.fields.contact_phone.minValue||"El campo Teléfono debe ser positivo."},function(e){return r.$v.supplier.fields.contact_phone.minLength||"El campo Teléfono debe tener al menos 10 dígitos."},function(e){return r.$v.supplier.fields.contact_phone.maxLength||"El campo Teléfono no debe exceder los 20 dígitos."}]},countryRules:function(e){var r=this;return[function(e){return r.$v.supplier.fields.state.required||"El campo Pais es requerido."}]},contactPhoneRulesTwo:function(e){var r=this;return[function(e){return r.$v.supplier.fields.contact_phone.integer||"El campo Teléfono 2 debe ser numérico."},function(e){return r.$v.supplier.fields.contact_phone.minValue||"El campo Teléfono 2 debe ser positivo."},function(e){return r.$v.supplier.fields.contact_phone.minLength||"El campo Teléfono 2 debe tener al menos 10 dígitos."},function(e){return r.$v.supplier.fields.contact_phone.maxLength||"El campo Teléfono 2 no debe exceder los 20 dígitos."}]},streetRules:function(e){var r=this;return[function(e){return r.$v.supplier.fields.street.required||"El campo Calle es requerido."},function(e){return r.$v.supplier.fields.street.maxLength||"El campo Calle no debe exceder los 100 dígitos."}]},outdoorNumberRules:function(e){var r=this;return[function(e){return r.$v.supplier.fields.outdoor_number.required||"El campo Número ext. es requerido."},function(e){return r.$v.supplier.fields.outdoor_number.maxLength||"El campo Número ext. no debe exceder los 10 dígitos."}]},indoorNumberRules:function(e){var r=this;return[function(e){return r.$v.supplier.fields.indoor_number.maxLength||"El campo Número int. no debe exceder los 10 dígitos."}]},suburbRules:function(e){var r=this;return[function(e){return r.$v.supplier.fields.suburb.required||"El campo Colonia es requerido."},function(e){return r.$v.supplier.fields.suburb.maxLength||"El campo Colonia no debe exceder los 100 dígitos."}]},municipalityRules:function(e){var r=this;return[function(e){return r.$v.supplier.fields.municipality.required||"El campo Municipio es requerido."},function(e){return r.$v.supplier.fields.municipality.maxLength||"El campo Municipio no debe exceder los 100 dígitos."}]},stateRules:function(e){var r=this;return[function(e){return r.$v.supplier.fields.state.required||"El campo Estado es requerido."},function(e){return r.$v.supplier.fields.state.maxLength||"El campo Estado no debe exceder los 100 dígitos."}]},zipCodeRules:function(e){var r=this;return[function(e){return r.$v.supplier.fields.zip_code.required||"El campo CP es requerido."},function(e){return r.$v.supplier.fields.zip_code.integer||"El campo CP debe ser numérico."},function(e){return r.$v.supplier.fields.zip_code.minValue||"El campo CP debe ser positivo."},function(e){return r.$v.supplier.fields.zip_code.maxLength||"El campo CP no debe exceder los 5 dígitos."}]},creditDaysRules:function(e){var r=this;return[function(e){return r.$v.supplier.fields.credit_days.required||"El campo Días de crédito es requerido."},function(e){return r.$v.supplier.fields.credit_days.integer||"El campo Días de crédito debe ser numérico."}]},rfcRules:function(e){var r=this;return[function(e){return r.$v.supplier.fields.rfc.required||"El campo RFC es requerido."},function(e){return r.$v.supplier.fields.rfc.maxLength||"El campo RFC no debe exceder los 20 dígitos."}]},paymentMethodRules:function(e){var r=this;return[function(e){return r.$v.supplier.fields.payment_method.required||"El campo Forma de pago es requerido."},function(e){return r.$v.supplier.fields.payment_method.maxLength||"El campo Forma de pago no debe exceder los 100 dígitos."}]},currencyRules:function(e){var r=this;return[function(e){return r.$v.supplier.fields.currency.required||"El campo Moneda es requerido."},function(e){return r.$v.supplier.fields.currency.maxLength||"El campo Moneda no debe exceder los 20 dígitos."}]},emailRules:function(e){var r=this;return[function(e){return r.$v.supplier.fields.email.required||"El campo Dirección de correo electrónico es requerido."},function(e){return r.$v.supplier.fields.email.email||"El campo Dirección de correo electrónico debe contener una dirección de correo electrónico válida."}]}},beforeRouteEnter:function(e,r,i){i((function(e){var r=e.$store.getters["users/rol"];console.log(r),1===r||3===r||7===r||2===r||20===r||4===r||27===r||22===r||17===r||28===r?i():i("/")}))},methods:{createSupplier:function(){var e=this;if(this.$v.supplier.fields.$reset(),this.$v.supplier.fields.$touch(),this.$v.supplier.fields.$error)return this.$q.dialog({title:"Error",message:"Por favor, verifique las validaciones.",persistent:!0}),!1;this.$q.loading.show();var r=t()({},this.supplier.fields);r.active=r.active.value,console.log(r),n["a"].post("/suppliers",r).then((function(r){var i=r.data;e.$q.notify({message:i.message.content,position:"top",color:i.result?"positive":"warning"}),i.result?(e.$q.loading.hide(),e.$router.push("/suppliers")):e.$q.loading.hide()}))}}},v=b,h=i("2877"),y=i("9989"),g=i("ead5"),x=i("079e"),$=i("27f9"),_=i("0016"),q=i("ddd8"),C=i("9c40"),k=i("eebe"),E=i.n(k),R=Object(h["a"])(v,s,l,!1,null,null,null);r["default"]=R.exports;E()(R,"components",{QPage:y["a"],QBreadcrumbs:g["a"],QBreadcrumbsEl:x["a"],QInput:$["a"],QIcon:_["a"],QSelect:q["a"],QBtn:C["a"]})}}]);
(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[191],{e4e3:function(e,t,i){"use strict";i.r(t);i("b0c0");var s=function(){var e=this,t=e._self._c;return t("q-page",{staticClass:"bg-grey-3"},[t("div",{staticClass:"q-pa-sm panel-header"},[t("div",{staticClass:"row"},[t("div",{staticClass:"col-sm-9"},[t("div",{staticClass:"q-pa-md q-gutter-sm"},[t("q-breadcrumbs",{staticStyle:{"font-size":"20px"},attrs:{align:"left"}},[t("q-breadcrumbs-el",{attrs:{label:"",icon:"home",to:"/"}}),t("q-breadcrumbs-el",{attrs:{label:"Unidades",to:"/units"}}),t("q-breadcrumbs-el",{attrs:{label:"Editar"},domProps:{textContent:e._s(e.unit.fields.name)}})],1)],1)])])]),t("div",{staticClass:"q-pa-md bg-grey-3"},[t("div",{staticClass:"row bg-white border-panel"},[t("div",{staticClass:"col q-pa-md"},[t("div",{staticClass:"row q-mb-sm",staticStyle:{visibility:"hidden"}},[t("div",{staticClass:"col-sm-1 offset-11 pull-right"},[t("q-btn",{attrs:{color:"primary",label:"Editar"}})],1)]),t("div",{staticClass:"row q-col-gutter-xs"},[t("div",{staticClass:"col-xs-12 col-sm-4"},[t("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.unit.fields.code.$error,label:"Código",rules:e.codeRules},on:{input:function(t){e.unit.fields.code=t.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[t("q-icon",{attrs:{name:"fingerprint"}})]},proxy:!0}]),model:{value:e.unit.fields.code,callback:function(t){e.$set(e.unit.fields,"code",t)},expression:"unit.fields.code"}})],1),t("div",{staticClass:"col-xs-12 col-sm-4"},[t("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.unit.fields.name.$error,label:"Nombre",rules:e.nameRules},on:{input:function(t){e.unit.fields.name=t.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[t("q-icon",{attrs:{name:"fas fa-signature"}})]},proxy:!0}]),model:{value:e.unit.fields.name,callback:function(t){e.$set(e.unit.fields,"name",t)},expression:"unit.fields.name"}})],1)]),t("div",{staticClass:"row q-mb-sm q-mt-md"},[t("div",{staticClass:"col-xs-12 col-sm-2 offset-sm-10 pull-right"},[t("q-btn",{attrs:{color:"positive",icon:"save",label:"Actualizar"},on:{click:function(t){return e.editUnit()}}})],1)])])])])])},n=[],r=i("ded3"),a=i.n(r),o=(i("14d9"),i("aabb")),l=i("b5ae"),d=l.required,u=l.maxLength,c={name:"EditUnit",validations:{unit:{fields:{name:{required:d,maxLength:u(20)},code:{required:d,maxLength:u(5)}}}},data:function(){return{unit:{fields:{id:null,name:null,code:null}}}},computed:{nameRules:function(e){var t=this;return[function(e){return t.$v.unit.fields.name.required||"El campo Nombre es requerido."},function(e){return t.$v.unit.fields.name.maxLength||"El campo Nombre no debe exceder los 20 dígitos."}]},codeRules:function(e){var t=this;return[function(e){return t.$v.unit.fields.code.required||"El campo Código es requerido."},function(e){return t.$v.unit.fields.code.maxLength||"El campo Código no debe exceder los 5 dígitos."}]}},beforeRouteEnter:function(e,t,i){i((function(e){var t=e.$store.getters["users/rol"];console.log(t),1===t||3===t||7===t||2===t||20===t||4===t||27===t?i():i("/")}))},created:function(){this.fetchFromServer()},methods:{fetchFromServer:function(){var e=this;this.$q.loading.show();var t=this.$route.params.id;o["a"].get("/units/".concat(t)).then((function(t){var i=t.data;i.unit?(e.unit.fields=i.unit,e.$q.loading.hide()):e.$router.push("/units")}))},updateUnitFields:function(){this.$v.unit.fields.$reset(),this.$v.unit.fields.$touch()},editUnit:function(){var e=this;if(this.$v.unit.fields.$reset(),this.$v.unit.fields.$touch(),this.$v.unit.fields.$error)return this.$q.dialog({title:"Error",message:"Por favor, verifique las validaciones.",persistent:!0}),!1;this.$q.loading.show();var t=a()({},this.unit.fields);o["a"].put("/units/".concat(t.id),t).then((function(t){var i=t.data;e.$q.notify({message:i.message.content,position:"top",color:i.result?"positive":"warning"}),i.result?(e.$q.loading.hide(),e.$router.push("/units")):e.$q.loading.hide()}))}}},f=c,m=i("2877"),p=i("9989"),v=i("ead5"),b=i("079e"),h=i("9c40"),g=i("27f9"),q=i("0016"),$=i("eebe"),C=i.n($),x=Object(m["a"])(f,s,n,!1,null,null,null);t["default"]=x.exports;C()(x,"components",{QPage:p["a"],QBreadcrumbs:v["a"],QBreadcrumbsEl:b["a"],QBtn:h["a"],QInput:g["a"],QIcon:q["a"]})}}]);
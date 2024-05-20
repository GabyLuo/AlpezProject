(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[193],{"412b":function(e,t,s){"use strict";s.r(t);s("b0c0");var i=function(){var e=this,t=e._self._c;return t("q-page",{staticClass:"bg-grey-3"},[t("div",{staticClass:"q-pa-sm panel-header"},[t("div",{staticClass:"row"},[t("div",{staticClass:"col-sm-9"},[t("div",{staticClass:"q-pa-md q-gutter-sm"},[t("q-breadcrumbs",{staticStyle:{"font-size":"20px"},attrs:{align:"left"}},[t("q-breadcrumbs-el",{attrs:{label:"",icon:"home",to:"/"}}),t("q-breadcrumbs-el",{attrs:{label:"Unidades",to:"/units"}}),t("q-breadcrumbs-el",{attrs:{label:"Nueva Unidad"}})],1)],1)])])]),t("div",{staticClass:"q-pa-md bg-grey-3"},[t("div",{staticClass:"row bg-white border-panel"},[t("div",{staticClass:"col q-pa-md"},[t("div",{staticClass:"row q-mb-sm"}),t("div",{staticClass:"row q-col-gutter-xs"},[t("div",{staticClass:"col-xs-12 col-sm-4"},[t("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.unit.fields.code.$error,label:"Código",rules:e.codeRules},on:{input:function(t){e.unit.fields.code=t.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[t("q-icon",{attrs:{name:"fingerprint"}})]},proxy:!0}]),model:{value:e.unit.fields.code,callback:function(t){e.$set(e.unit.fields,"code",t)},expression:"unit.fields.code"}})],1),t("div",{staticClass:"col-xs-12 col-sm-4"},[t("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.unit.fields.name.$error,label:"Nombre",rules:e.nameRules},on:{input:function(t){e.unit.fields.name=t.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[t("q-icon",{attrs:{name:"fas fa-signature"}})]},proxy:!0}]),model:{value:e.unit.fields.name,callback:function(t){e.$set(e.unit.fields,"name",t)},expression:"unit.fields.name"}})],1)]),t("div",{staticClass:"row q-mb-sm q-mt-md"},[t("div",{staticClass:"col-xs-12 col-sm-2 offset-sm-10 pull-right"},[t("q-btn",{attrs:{color:"positive",icon:"save",label:"Guardar"},on:{click:function(t){return e.createUnit()}}})],1)])])])])])},n=[],a=s("ded3"),r=s.n(a),o=(s("14d9"),s("aabb")),l=s("b5ae"),d=l.required,u=l.maxLength,c={name:"NewUnit",validations:{unit:{fields:{name:{required:d,maxLength:u(20)},code:{required:d,maxLength:u(5)}}}},data:function(){return{unit:{fields:{name:null,code:null}}}},computed:{nameRules:function(e){var t=this;return[function(e){return t.$v.unit.fields.name.required||"El campo Nombre es requerido."},function(e){return t.$v.unit.fields.name.maxLength||"El campo Nombre no debe exceder los 20 dígitos."}]},codeRules:function(e){var t=this;return[function(e){return t.$v.unit.fields.code.required||"El campo Código es requerido."},function(e){return t.$v.unit.fields.code.maxLength||"El campo Código no debe exceder los 5 dígitos."}]}},methods:{createUnit:function(){var e=this;if(this.$v.unit.fields.$reset(),this.$v.unit.fields.$touch(),this.$v.unit.fields.$error)return this.$q.dialog({title:"Error",message:"Por favor, verifique las validaciones.",persistent:!0}),!1;this.$q.loading.show();var t=r()({},this.unit.fields);o["a"].post("/units",t).then((function(t){var s=t.data;e.$q.notify({message:s.message.content,position:"top",color:s.result?"positive":"warning"}),s.result?(e.$q.loading.hide(),e.$router.push("/units")):e.$q.loading.hide()}))}}},m=c,f=s("2877"),p=s("9989"),b=s("ead5"),v=s("079e"),g=s("27f9"),q=s("0016"),h=s("9c40"),$=s("eebe"),C=s.n($),x=Object(f["a"])(m,i,n,!1,null,null,null);t["default"]=x.exports;C()(x,"components",{QPage:p["a"],QBreadcrumbs:b["a"],QBreadcrumbsEl:v["a"],QInput:g["a"],QIcon:q["a"],QBtn:h["a"]})}}]);
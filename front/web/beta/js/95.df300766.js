(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[95],{"52da":function(e,r,t){"use strict";t.r(r);t("b0c0");var s=function(){var e=this,r=e._self._c;return r("q-page",[r("div",{staticClass:"q-pa-sm panel-header"},[r("div",{staticClass:"row"},[r("div",{staticClass:"col-sm-9"},[r("span",{staticClass:"q-ml-md grey-8 fs28 page-title"},[e._v("Nuevo Operador")])]),r("div",{staticClass:"col-sm-3"},[r("div",{staticClass:"q-pa-md q-gutter-sm"},[r("q-breadcrumbs",{attrs:{align:"right"}},[r("q-breadcrumbs-el",{attrs:{label:"",icon:"home",to:"/"}}),r("q-breadcrumbs-el",{attrs:{label:"Operadores",to:"/operators"}}),r("q-breadcrumbs-el",{attrs:{label:"Nuevo"}})],1)],1)])])]),r("div",{staticClass:"q-pa-md bg-grey-3"},[r("div",{staticClass:"row bg-white border-panel"},[r("div",{staticClass:"col q-pa-md"},[r("div",{staticClass:"row q-mb-sm"}),r("div",{staticClass:"row q-col-gutter-xs"},[r("div",{staticClass:"col-xs-12 col-sm-6"},[r("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.operator.fields.name.$error,label:"Nombre",rules:e.nameRules},on:{input:function(r){e.operator.fields.name=r.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[r("q-icon",{attrs:{name:"fas fa-signature"}})]},proxy:!0}]),model:{value:e.operator.fields.name,callback:function(r){e.$set(e.operator.fields,"name",r)},expression:"operator.fields.name"}})],1),r("div",{staticClass:"col-xs-12 col-sm-6 pull-right"},[r("q-btn",{staticStyle:{height:"70%"},attrs:{color:"positive",icon:"save",label:"Guardar"},on:{click:function(r){return e.createOperator()}}})],1)])])])])])},a=[],o=t("ded3"),i=t.n(o),l=(t("caad"),t("2532"),t("14d9"),t("aabb")),n=t("b5ae"),d=n.required,c=n.maxLength,u={name:"NewOperator",validations:{operator:{fields:{name:{required:d,maxLength:c(100)}}}},data:function(){return{operator:{fields:{name:null}}}},computed:{nameRules:function(e){var r=this;return[function(e){return r.$v.operator.fields.name.required||"El campo Nombre es requerido."},function(e){return r.$v.operator.fields.name.maxLength||"El campo Nombre no debe exceder los 100 dígitos."}]}},beforeCreate:function(){this.$store.getters["users/roles"].includes(1)||this.$store.getters["users/roles"].includes(3)||this.$store.getters["users/roles"].includes(7)||this.$store.getters["users/roles"].includes(10)||this.$router.push("/")},methods:{createOperator:function(){var e=this;if(this.$v.operator.fields.$reset(),this.$v.operator.fields.$touch(),this.$v.operator.fields.$error)return this.$q.dialog({title:"Error",message:"Por favor, verifique las validaciones.",persistent:!0}),!1;this.$q.loading.show();var r=[];r.name=i()({},this.operator.fields).name,l["a"].post("/operators",r).then((function(r){var t=r.data;e.$q.notify({message:t.message.content,position:"top",color:t.result?"positive":"warning"}),t.result?(e.$q.loading.hide(),e.$router.push("/operators")):e.$q.loading.hide()}))}}},p=u,m=t("2877"),f=t("9989"),b=t("ead5"),v=t("079e"),h=t("27f9"),g=t("0016"),q=t("9c40"),$=t("eebe"),C=t.n($),w=Object(m["a"])(p,s,a,!1,null,null,null);r["default"]=w.exports;C()(w,"components",{QPage:f["a"],QBreadcrumbs:b["a"],QBreadcrumbsEl:v["a"],QInput:h["a"],QIcon:g["a"],QBtn:q["a"]})}}]);
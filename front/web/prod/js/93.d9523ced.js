(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[93],{"2e84":function(e,t,r){"use strict";r.r(t);var s=function(){var e=this,t=e.$createElement,r=e._self._c||t;return r("q-page",[r("div",{staticClass:"q-pa-sm panel-header"},[r("div",{staticClass:"row"},[r("div",{staticClass:"col-sm-9"},[r("span",{staticClass:"q-ml-md grey-8 fs28 page-title"},[e._v("Editar Operador "+e._s(e.$route.params.id))])]),r("div",{staticClass:"col-sm-3"},[r("div",{staticClass:"q-pa-md q-gutter-sm"},[r("q-breadcrumbs",{attrs:{align:"right"}},[r("q-breadcrumbs-el",{attrs:{label:"",icon:"home",to:"/"}}),r("q-breadcrumbs-el",{attrs:{label:"Operadores",to:"/operators"}}),r("q-breadcrumbs-el",{attrs:{label:"Editar"}})],1)],1)])])]),r("div",{staticClass:"q-pa-md bg-grey-3"},[r("div",{staticClass:"row bg-white border-panel"},[r("div",{staticClass:"col q-pa-md"},[r("div",{staticClass:"row q-mb-sm",staticStyle:{visibility:"hidden"}},[r("div",{staticClass:"col-sm-1 offset-11 pull-right"},[r("q-btn",{attrs:{color:"primary",label:"Editar"}})],1)]),r("div",{staticClass:"row q-col-gutter-xs"},[r("div",{staticClass:"col-xs-12 col-sm-6"},[r("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.operator.fields.name.$error,label:"Nombre",rules:e.nameRules},on:{input:function(t){e.operator.fields.name=t.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[r("q-icon",{attrs:{name:"fas fa-signature"}})]},proxy:!0}]),model:{value:e.operator.fields.name,callback:function(t){e.$set(e.operator.fields,"name",t)},expression:"operator.fields.name"}})],1),r("div",{staticClass:"col-xs-12 col-sm-6 pull-right"},[r("q-btn",{staticStyle:{height:"70%"},attrs:{color:"positive",icon:"save",label:"Guardar"},on:{click:function(t){return e.updateOperator()}}})],1)])])])])])},a=[],o=r("ded3"),i=r.n(o),l=(r("b0c0"),r("caad"),r("2532"),r("aabb")),n=r("b5ae"),d=n.required,c=n.maxLength,u={name:"EditOperator",validations:{operator:{fields:{name:{required:d,maxLength:c(100)}}}},data:function(){return{operator:{fields:{name:null}}}},computed:{nameRules:function(e){var t=this;return[function(e){return t.$v.operator.fields.name.required||"El campo Nombre es requerido."},function(e){return t.$v.operator.fields.name.maxLength||"El campo Nombre no debe exceder los 100 dígitos."}]}},beforeCreate:function(){this.$store.getters["users/roles"].includes(1)||this.$store.getters["users/roles"].includes(3)||this.$store.getters["users/roles"].includes(7)||this.$store.getters["users/roles"].includes(10)||this.$router.push("/")},created:function(){this.fetchFromServer()},methods:{fetchFromServer:function(){var e=this;this.$q.loading.show(),l["a"].get("/operators/".concat(this.$route.params.id)).then((function(t){var r=t.data;r.operator?(e.operator.fields=r.operator,e.$q.loading.hide()):e.$router.push("/operators")}))},updateOperator:function(){var e=this;if(this.$v.operator.fields.$reset(),this.$v.operator.fields.$touch(),this.$v.operator.fields.$error)return this.$q.dialog({title:"Error",message:"Por favor, verifique las validaciones.",persistent:!0}),!1;this.$q.loading.show();var t=i()({},this.operator.fields);l["a"].put("/operators/".concat(this.$route.params.id),t).then((function(t){var r=t.data;e.$q.notify({message:r.message.content,position:"top",color:r.result?"positive":"warning"}),r.result?(e.$q.loading.hide(),e.$router.push("/operators")):e.$q.loading.hide()}))}}},p=u,m=r("2877"),f=r("9989"),h=r("ead5"),v=r("079e"),b=r("9c40"),g=r("27f9"),q=r("0016"),$=r("eebe"),C=r.n($),w=Object(m["a"])(p,s,a,!1,null,null,null);t["default"]=w.exports;C()(w,"components",{QPage:f["a"],QBreadcrumbs:h["a"],QBreadcrumbsEl:v["a"],QBtn:b["a"],QInput:g["a"],QIcon:q["a"]})}}]);
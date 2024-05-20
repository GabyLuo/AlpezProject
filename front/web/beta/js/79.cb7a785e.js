(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[79],{"955b":function(e,t,s){"use strict";s.r(t);s("b0c0");var i=function(){var e=this,t=e._self._c;return t("q-page",{staticClass:"bg-grey-3"},[t("div",{staticClass:"q-pa-sm panel-header"},[t("div",{staticClass:"row"},[t("div",{staticClass:"col-sm-9"},[t("div",{staticClass:"q-pa-md q-gutter-sm"},[t("q-breadcrumbs",{staticStyle:{"font-size":"20px"},attrs:{align:"left"}},[t("q-breadcrumbs-el",{attrs:{label:"",icon:"home",to:"/"}}),t("q-breadcrumbs-el",{attrs:{label:"Subcategorías",to:"/lines"}}),t("q-breadcrumbs-el",{attrs:{label:"Nueva Subcategoría"}})],1)],1)])])]),t("div",{staticClass:"q-pa-md bg-grey-3"},[t("div",{staticClass:"row bg-white border-panel"},[t("div",{staticClass:"col q-pa-md"},[t("div",{staticClass:"row q-mb-sm"}),t("div",{staticClass:"row q-col-gutter-xs"},[t("div",{staticClass:"col-xs-12 col-sm-4"},[t("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",options:e.categoryOptions,label:"Categorías",rules:e.categoryRules},scopedSlots:e._u([{key:"prepend",fn:function(){return[t("q-icon",{attrs:{name:"category"}})]},proxy:!0}]),model:{value:e.line.fields.category,callback:function(t){e.$set(e.line.fields,"category",t)},expression:"line.fields.category"}})],1),t("div",{staticClass:"col-xs-12 col-sm-4"},[t("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.line.fields.code.$error,label:"Código",rules:e.codeRules},on:{input:function(t){e.line.fields.code=t.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[t("q-icon",{attrs:{name:"fingerprint"}})]},proxy:!0}]),model:{value:e.line.fields.code,callback:function(t){e.$set(e.line.fields,"code",t)},expression:"line.fields.code"}})],1),t("div",{staticClass:"col-xs-12 col-sm-4"},[t("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.line.fields.name.$error,label:"Nombre",rules:e.nameRules},on:{input:function(t){e.line.fields.name=t.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[t("q-icon",{attrs:{name:"fas fa-signature"}})]},proxy:!0}]),model:{value:e.line.fields.name,callback:function(t){e.$set(e.line.fields,"name",t)},expression:"line.fields.name"}})],1)]),t("div",{staticClass:"row q-mb-sm q-mt-md"},[t("div",{staticClass:"col-xs-12 col-sm-2 offset-sm-10 pull-right"},[t("q-btn",{attrs:{color:"positive",icon:"save",label:"Guardar"},on:{click:function(t){return e.createLine()}}})],1)])])])])])},o=[],n=s("ded3"),r=s.n(n),a=(s("14d9"),s("aabb")),l=s("b5ae"),c=l.required,d=l.maxLength,u={name:"NewLine",validations:{line:{fields:{name:{required:c,maxLength:d(50)},code:{required:c,maxLength:d(5)},category:{required:c}}}},data:function(){return{line:{fields:{name:null,code:null,category:null}},categoryOptions:[]}},computed:{nameRules:function(e){var t=this;return[function(e){return t.$v.line.fields.name.required||"El campo Nombre es requerido."},function(e){return t.$v.line.fields.name.maxLength||"El campo Nombre no debe exceder los 50 dígitos."}]},codeRules:function(e){var t=this;return[function(e){return t.$v.line.fields.code.required||"El campo Código es requerido."},function(e){return t.$v.line.fields.code.maxLength||"El campo Código no debe exceder los 5 dígitos."}]},categoryRules:function(e){var t=this;return[function(e){return t.$v.line.fields.category.required||"El campo Categoría es requerido."}]}},beforeRouteEnter:function(e,t,s){s((function(e){var t=e.$store.getters["users/rol"];console.log(t),1===t||3===t||7===t||2===t||20===t||4===t||27===t||22===t?s():s("/")}))},created:function(){this.fetchFromServer()},mounted:function(){},methods:{fetchFromServer:function(){var e=this;this.$q.loading.show(),a["a"].get("/categories/options").then((function(t){var s=t.data;e.categoryOptions=s.options,e.$q.loading.hide()}))},createLine:function(){var e=this;if(this.$v.line.fields.$reset(),this.$v.line.fields.$touch(),this.$v.line.fields.$error)return this.$q.dialog({title:"Error",message:"Por favor, verifique las validaciones.",persistent:!0}),!1;this.$q.loading.show();var t=[];t.code=r()({},this.line.fields).code,t.name=r()({},this.line.fields).name,t.category_id=r()({},this.line.fields).category.value,a["a"].post("/lines",t).then((function(t){var s=t.data;e.$q.notify({message:s.message.content,position:"top",color:s.result?"positive":"warning"}),s.result?(e.$q.loading.hide(),e.$router.push("/lines")):e.$q.loading.hide()}))}}},f=u,m=s("2877"),p=s("9989"),g=s("ead5"),v=s("079e"),b=s("ddd8"),q=s("0016"),h=s("27f9"),y=s("9c40"),$=s("eebe"),C=s.n($),x=Object(m["a"])(f,i,o,!1,null,null,null);t["default"]=x.exports;C()(x,"components",{QPage:p["a"],QBreadcrumbs:g["a"],QBreadcrumbsEl:v["a"],QSelect:b["a"],QIcon:q["a"],QInput:h["a"],QBtn:y["a"]})}}]);
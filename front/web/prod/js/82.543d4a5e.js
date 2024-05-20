(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[82],{"0b74":function(e,r,t){"use strict";t.r(r);var a=function(){var e=this,r=e.$createElement,t=e._self._c||r;return t("q-page",{staticClass:"bg-grey-3"},[t("div",{staticClass:"q-pa-sm panel-header"},[t("div",{staticClass:"row"},[t("div",{staticClass:"col-sm-9",staticStyle:{"font-size":"20px"}},[t("div",{staticClass:"q-pa-md q-gutter-sm"},[t("q-breadcrumbs",{attrs:{align:"left"}},[t("q-breadcrumbs-el",{attrs:{label:"",icon:"home",to:"/"}}),t("q-breadcrumbs-el",{attrs:{label:"Marcas",to:"/marks"}}),t("q-breadcrumbs-el",{attrs:{label:"Editar Marca"},domProps:{textContent:e._s(e.mark.fields.name)}})],1)],1)]),t("div",{staticClass:"col-sm-3"})])]),t("div",{staticClass:"q-pa-md bg-grey-3"},[t("div",{staticClass:"row bg-white border-panel"},[t("div",{staticClass:"col q-pa-md"},[t("div",{staticClass:"row q-mb-sm",staticStyle:{visibility:"hidden"}},[t("div",{staticClass:"col-sm-1 offset-11 pull-right"},[t("q-btn",{attrs:{color:"primary",label:"Editar"}})],1)]),t("div",{staticClass:"row q-col-gutter-xs"},[t("div",{staticClass:"col-xs-12 col-sm-4"},[t("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.mark.fields.code.$error,label:"Código",rules:e.codeRules},on:{input:function(r){e.mark.fields.code=r.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[t("q-icon",{attrs:{name:"fingerprint"}})]},proxy:!0}]),model:{value:e.mark.fields.code,callback:function(r){e.$set(e.mark.fields,"code",r)},expression:"mark.fields.code"}})],1),t("div",{staticClass:"col-xs-12 col-sm-4"},[t("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.mark.fields.name.$error,label:"Nombre",rules:e.nameRules},on:{input:function(r){e.mark.fields.name=r.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[t("q-icon",{attrs:{name:"fas fa-signature"}})]},proxy:!0}]),model:{value:e.mark.fields.name,callback:function(r){e.$set(e.mark.fields,"name",r)},expression:"mark.fields.name"}})],1)]),t("div",{staticClass:"row q-mb-sm q-mt-md"},[t("div",{staticClass:"col-xs-12 col-sm-2 offset-sm-10 pull-right"},[t("q-btn",{attrs:{color:"positive",icon:"save",label:"Actualizar"},on:{click:function(r){return e.editMark()}}})],1)])])])])])},s=[],i=t("ded3"),o=t.n(i),n=(t("b0c0"),t("aabb")),l=t("b5ae"),d=l.required,c=l.maxLength,u={name:"EditLine",validations:{mark:{fields:{name:{required:d,maxLength:c(50)},code:{required:d,maxLength:c(5)}}}},data:function(){return{mark:{fields:{id:null,name:null,code:null}},categoryOptions:[]}},computed:{nameRules:function(e){var r=this;return[function(e){return r.$v.mark.fields.name.required||"El campo Nombre es requerido."},function(e){return r.$v.mark.fields.name.maxLength||"El campo Nombre no debe exceder los 50 dígitos."}]},codeRules:function(e){var r=this;return[function(e){return r.$v.mark.fields.code.required||"El campo Código es requerido."},function(e){return r.$v.mark.fields.code.maxLength||"El campo Código no debe exceder los 5 dígitos."}]},categoryRules:function(e){var r=this;return[function(e){return r.$v.mark.fields.category.required||"El campo Categoría es requerido."}]}},beforeRouteEnter:function(e,r,t){t((function(e){var r=e.$store.getters["users/rol"];console.log(r),1===r||3===r||7===r||2===r||20===r||4===r||27===r?t():t("/")}))},created:function(){this.fetchFromServer()},methods:{fetchFromServer:function(){var e=this;this.$q.loading.show();var r=this.$route.params.id;n["a"].get("/marks/".concat(r)).then((function(r){var t=r.data;t.mark?(console.log(t.mark),e.mark.fields=t.mark):(e.$q.loading.hide(),e.$router.push("/marks")),e.$q.loading.hide()}))},updateLineFields:function(){this.$v.mark.fields.$reset(),this.$v.mark.fields.$touch()},editMark:function(){var e=this;if(this.$v.mark.fields.$reset(),this.$v.mark.fields.$touch(),this.$v.mark.fields.$error)return this.$q.dialog({title:"Error",message:"Por favor, verifique las validaciones.",persistent:!0}),!1;this.$q.loading.show();var r=o()({},this.mark.fields);n["a"].put("/marks/".concat(r.id),r).then((function(r){var t=r.data;e.$q.notify({message:t.message.content,position:"top",color:t.result?"positive":"warning"}),t.result?(e.$q.loading.hide(),e.$router.push("/marks")):e.$q.loading.hide()}))}}},m=u,f=t("2877"),p=t("9989"),v=t("ead5"),b=t("079e"),g=t("9c40"),h=t("27f9"),k=t("0016"),q=t("eebe"),$=t.n(q),C=Object(f["a"])(m,a,s,!1,null,null,null);r["default"]=C.exports;$()(C,"components",{QPage:p["a"],QBreadcrumbs:v["a"],QBreadcrumbsEl:b["a"],QBtn:g["a"],QInput:h["a"],QIcon:k["a"]})}}]);
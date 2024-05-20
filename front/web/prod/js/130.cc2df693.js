(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[130],{c68d:function(e,a,t){"use strict";t.r(a);var s=function(){var e=this,a=e.$createElement,t=e._self._c||a;return t("q-page",{staticClass:"bg-grey-3"},[t("div",{staticClass:"q-pa-sm panel-header"},[t("div",{staticClass:"row"},[t("div",{staticClass:"col-sm-9",staticStyle:{"font-size":"20px"}},[t("div",{staticClass:"q-pa-md q-gutter-sm"},[t("q-breadcrumbs",{attrs:{align:"left"}},[t("q-breadcrumbs-el",{attrs:{label:"",icon:"home",to:"/"}}),t("q-breadcrumbs-el",{attrs:{label:"Areas",to:"/areas"}}),t("q-breadcrumbs-el",{attrs:{label:"Editar Area"},domProps:{textContent:e._s(e.area.fields.name)}})],1)],1)]),t("div",{staticClass:"col-sm-3"})])]),t("div",{staticClass:"q-pa-md bg-grey-3"},[t("div",{staticClass:"row bg-white border-panel"},[t("div",{staticClass:"col q-pa-md"},[t("div",{staticClass:"row q-mb-sm",staticStyle:{visibility:"hidden"}},[t("div",{staticClass:"col-sm-1 offset-11 pull-right"},[t("q-btn",{attrs:{color:"primary",label:"Editar"}})],1)]),t("div",{staticClass:"row q-col-gutter-xs"},[t("div",{staticClass:"col-xs-12 col-sm-4"},[t("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"","emit-value":"","map-options":"",options:e.departmentOptions,label:"Departamentos",rules:e.departmentRules},on:{input:e.updateAreaFields},scopedSlots:e._u([{key:"prepend",fn:function(){return[t("q-icon",{attrs:{name:"fas fa-building"}})]},proxy:!0}]),model:{value:e.area.fields.department_id,callback:function(a){e.$set(e.area.fields,"department_id",a)},expression:"area.fields.department_id"}})],1),t("div",{staticClass:"col-xs-12 col-sm-8"},[t("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.area.fields.name.$error,label:"Nombre",rules:e.nameRules},on:{input:function(a){e.area.fields.name=a.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[t("q-icon",{attrs:{name:"fas fa-signature"}})]},proxy:!0}]),model:{value:e.area.fields.name,callback:function(a){e.$set(e.area.fields,"name",a)},expression:"area.fields.name"}})],1)]),t("div",{staticClass:"row q-mb-sm q-mt-md"},[t("div",{staticClass:"col-xs-12 col-sm-2 offset-sm-10 pull-right"},[t("q-btn",{attrs:{color:"positive",icon:"save",label:"Actualizar"},on:{click:function(a){return e.editArea()}}})],1)])])])])])},r=[],i=t("ded3"),n=t.n(i),o=(t("b0c0"),t("caad"),t("2532"),t("aabb")),l=t("b5ae"),d=l.required,c=l.maxLength,u={name:"EditArea",validations:{area:{fields:{name:{required:d,maxLength:c(20)},department:{required:d}}}},data:function(){return{area:{fields:{id:null,name:null,department:null}},departmentOptions:[]}},computed:{nameRules:function(e){var a=this;return[function(e){return a.$v.area.fields.name.required||"El campo Nombre es requerido."},function(e){return a.$v.area.fields.name.maxLength||"El campo Nombre no debe exceder los 20 dígitos."}]},departmentRules:function(e){var a=this;return[function(e){return a.$v.area.fields.department.required||"El campo Departamento es requerido."}]}},beforeCreate:function(){this.$store.getters["users/roles"].includes(1)||this.$store.getters["users/roles"].includes(3)||this.$store.getters["users/roles"].includes(7)||this.$store.getters["users/roles"].includes(4)||this.$router.push("/")},created:function(){this.getDepartmentsList(),this.fetchFromServer()},methods:{getDepartmentsList:function(){var e=this;o["a"].get("/departments/options").then((function(a){var t=a.data;e.departmentOptions=t.options,e.$q.loading.hide()}))},fetchFromServer:function(){var e=this;this.$q.loading.show();var a=this.$route.params.id;o["a"].get("/areas/".concat(a)).then((function(a){var t=a.data;t.area?(console.log(t.area),e.area.fields=t.area,e.area.fields.department=t.area.department_id):(e.$q.loading.hide(),e.$router.push("/areas")),e.$q.loading.hide(),console.log(e.area.fields)}))},updateAreaFields:function(){this.$v.area.fields.$reset(),this.$v.area.fields.$touch()},editArea:function(){var e=this;if(this.$v.area.fields.$reset(),this.$v.area.fields.$touch(),this.$v.area.fields.$error)return this.$q.dialog({title:"Error",message:"Por favor, verifique las validaciones.",persistent:!0}),!1;this.$q.loading.show();var a=n()({},this.area.fields);o["a"].put("/areas/".concat(a.id),a).then((function(a){var t=a.data;e.$q.notify({message:t.message.content,position:"top",color:t.result?"positive":"warning"}),t.result?(e.$q.loading.hide(),e.$router.push("/areas")):e.$q.loading.hide()}))}}},m=u,p=t("2877"),f=t("9989"),h=t("ead5"),v=t("079e"),b=t("9c40"),g=t("ddd8"),q=t("0016"),$=t("27f9"),C=t("eebe"),w=t.n(C),x=Object(p["a"])(m,s,r,!1,null,null,null);a["default"]=x.exports;w()(x,"components",{QPage:f["a"],QBreadcrumbs:h["a"],QBreadcrumbsEl:v["a"],QBtn:b["a"],QSelect:g["a"],QIcon:q["a"],QInput:$["a"]})}}]);
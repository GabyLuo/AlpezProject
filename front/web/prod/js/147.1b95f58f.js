(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[147],{ab87:function(e,t,s){"use strict";s.r(t);var i=function(){var e=this,t=e.$createElement,s=e._self._c||t;return s("q-page",{staticClass:"bg-grey-3"},[s("div",{staticClass:"q-pa-sm panel-header"},[s("div",{staticClass:"row"},[s("div",{staticClass:"col-sm-9"},[s("div",{staticClass:"q-pa-md q-gutter-sm"},[s("q-breadcrumbs",{staticStyle:{"font-size":"20px"},attrs:{align:"left"}},[s("q-breadcrumbs-el",{attrs:{label:"",icon:"home",to:"/"}}),s("q-breadcrumbs-el",{attrs:{label:"Puestos",to:"/positions"}}),s("q-breadcrumbs-el",{attrs:{label:"Nuevo Puesto"}})],1)],1)])])]),s("div",{staticClass:"q-pa-md bg-grey-3"},[s("div",{staticClass:"row bg-white border-panel"},[s("div",{staticClass:"col q-pa-md"},[s("div",{staticClass:"row q-col-gutter-xs"},[s("div",{staticClass:"col-xs-12 col-sm-6"},[s("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",options:e.departmentOptions,label:"Departamentos",rules:e.departmentRules},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"fas fa-building"}})]},proxy:!0}]),model:{value:e.position.fields.department,callback:function(t){e.$set(e.position.fields,"department",t)},expression:"position.fields.department"}})],1),s("div",{staticClass:"col-xs-12 col-sm-6"},[s("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",options:e.filteredAreaOptions,label:"Areas",rules:e.areaRules},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"list"}})]},proxy:!0}]),model:{value:e.position.fields.area,callback:function(t){e.$set(e.position.fields,"area",t)},expression:"position.fields.area"}})],1),s("div",{staticClass:"col-xs-12 col-sm-12"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.position.fields.name.$error,label:"Nombre",rules:e.nameRules},on:{input:function(t){e.position.fields.name=t.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"fas fa-signature"}})]},proxy:!0}]),model:{value:e.position.fields.name,callback:function(t){e.$set(e.position.fields,"name",t)},expression:"position.fields.name"}})],1)]),s("div",{staticClass:"row q-mb-sm q-mt-md"},[s("div",{staticClass:"col-xs-12 col-sm-2 offset-sm-10 pull-right"},[s("q-btn",{attrs:{color:"positive",icon:"save",label:"Guardar"},on:{click:function(t){return e.createPosition()}}})],1)])])])])])},o=[],a=(s("b0c0"),s("4de4"),s("d3b7"),s("caad"),s("2532"),s("a9e3"),s("aabb")),r=s("b5ae"),n=r.required,l=r.maxLength,d={name:"NewPosition",validations:{position:{fields:{name:{required:n,maxLength:l(100)},department:{required:n},area:{required:n}}}},data:function(){return{position:{fields:{name:null,department:null,area:null}},departmentOptions:[],areaOptions:[]}},computed:{nameRules:function(e){var t=this;return[function(e){return t.$v.position.fields.name.required||"El campo Nombre es requerido."},function(e){return t.$v.position.fields.name.maxLength||"El campo Nombre no debe exceder los 100 dígitos."}]},departmentRules:function(e){var t=this;return[function(e){return t.$v.position.fields.department.required||"El campo Departamento es requerido."}]},areaRules:function(e){var t=this;return[function(e){return t.$v.position.fields.area.required||"El campo Area es requerido."}]},filteredAreaOptions:function(){var e=this;return null!=this.position.fields.department&&null!=this.position.fields.department.value?this.areaOptions.filter((function(t){return t.department===e.position.fields.department.value})):[]}},beforeCreate:function(){this.$store.getters["users/roles"].includes(1)||this.$store.getters["users/roles"].includes(3)||this.$store.getters["users/roles"].includes(7)||this.$store.getters["users/roles"].includes(4)||this.$router.push("/")},mounted:function(){this.fetchFromServer()},methods:{fetchFromServer:function(){this.$q.loading.show(),this.getDepartments(),this.getAreas(),this.$q.loading.hide()},getDepartments:function(){var e=this;a["a"].get("/departments/options").then((function(t){var s=t.data;e.departmentOptions=s.options}))},getAreas:function(){var e=this;a["a"].get("/areas/options").then((function(t){var s=t.data;e.areaOptions=s.options}))},createPosition:function(){var e=this;if(this.$v.position.fields.$reset(),this.$v.position.fields.$touch(),this.$v.position.fields.$error)return this.$q.dialog({title:"Error",message:"Por favor, verifique las validaciones.",persistent:!0}),!1;this.$q.loading.show();var t=[];t.name=this.position.fields.name,t.area_id=Number(this.position.fields.area.value),a["a"].post("/positions",t).then((function(t){var s=t.data;e.$q.notify({message:s.message.content,position:"top",color:s.result?"positive":"warning"}),s.result?(e.$q.loading.hide(),e.$router.push("/positions")):e.$q.loading.hide()}))}}},u=d,c=s("2877"),p=s("9989"),m=s("ead5"),f=s("079e"),h=s("ddd8"),v=s("0016"),b=s("27f9"),g=s("9c40"),q=s("eebe"),$=s.n(q),x=Object(c["a"])(u,i,o,!1,null,null,null);t["default"]=x.exports;$()(x,"components",{QPage:p["a"],QBreadcrumbs:m["a"],QBreadcrumbsEl:f["a"],QSelect:h["a"],QIcon:v["a"],QInput:b["a"],QBtn:g["a"]})}}]);
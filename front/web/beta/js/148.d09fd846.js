(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[148],{"8a70":function(t,s,e){"use strict";e.r(s);e("b0c0");var i=function(){var t=this,s=t._self._c;return s("q-page",{staticClass:"bg-grey-3"},[s("div",{staticClass:"q-pa-sm panel-header"},[s("div",{staticClass:"row"},[s("div",{staticClass:"col-sm-9"},[s("div",{staticClass:"q-pa-md q-gutter-sm"},[s("q-breadcrumbs",{staticStyle:{"font-size":"20px"},attrs:{align:"left"}},[s("q-breadcrumbs-el",{attrs:{label:"",icon:"home",to:"/"}}),s("q-breadcrumbs-el",{attrs:{label:"Turnos",to:"/shifts"}}),s("q-breadcrumbs-el",{attrs:{label:"Editar Turno"},domProps:{textContent:t._s(t.shift.fields.name)}})],1)],1)])])]),s("div",{staticClass:"q-pa-md bg-grey-3"},[s("div",{staticClass:"row bg-white border-panel"},[s("div",{staticClass:"col q-pa-md"},[s("div",{staticClass:"row q-mb-sm",staticStyle:{visibility:"hidden"}},[s("div",{staticClass:"col-sm-1 offset-11 pull-right"},[s("q-btn",{attrs:{color:"primary",label:"Editar"}})],1)]),s("div",{staticClass:"row q-col-gutter-xs"},[s("div",{staticClass:"col-xs-12 col-sm-12"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:t.$v.shift.fields.name.$error,label:"Nombre",rules:t.nameRules},on:{input:function(s){t.shift.fields.name=s.toUpperCase()}},scopedSlots:t._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"fas fa-signature"}})]},proxy:!0}]),model:{value:t.shift.fields.name,callback:function(s){t.$set(t.shift.fields,"name",s)},expression:"shift.fields.name"}})],1)]),s("div",{staticClass:"row q-mb-sm q-mt-md"},[s("div",{staticClass:"col-xs-12 col-sm-2 offset-sm-10 pull-right"},[s("q-btn",{attrs:{color:"positive",icon:"save",label:"Actualizar"},on:{click:function(s){return t.editShift()}}})],1)])])])])])},a=[],r=e("ded3"),o=e.n(r),n=(e("caad"),e("2532"),e("14d9"),e("aabb")),l=e("b5ae"),d=l.required,c=l.maxLength,f={name:"EditShift",validations:{shift:{fields:{name:{required:d,maxLength:c(20)}}}},data:function(){return{shift:{fields:{id:null,name:null}}}},computed:{nameRules:function(t){var s=this;return[function(t){return s.$v.shift.fields.name.required||"El campo Nombre es requerido."},function(t){return s.$v.shift.fields.name.maxLength||"El campo Nombre no debe exceder los 20 dígitos."}]}},beforeCreate:function(){this.$store.getters["users/roles"].includes(1)||this.$store.getters["users/roles"].includes(3)||this.$store.getters["users/roles"].includes(7)||this.$store.getters["users/roles"].includes(4)||this.$router.push("/")},created:function(){this.fetchFromServer()},methods:{fetchFromServer:function(){var t=this;this.$q.loading.show();var s=this.$route.params.id;n["a"].get("/shifts/".concat(s)).then((function(s){var e=s.data;e.shift?(t.shift.fields=e.shift,t.$q.loading.hide()):t.$router.push("/shifts")}))},updateShiftFields:function(){this.$v.shift.fields.$reset(),this.$v.shift.fields.$touch()},editShift:function(){var t=this;if(this.$v.shift.fields.$reset(),this.$v.shift.fields.$touch(),this.$v.shift.fields.$error)return this.$q.dialog({title:"Error",message:"Por favor, verifique las validaciones.",persistent:!0}),!1;this.$q.loading.show();var s=o()({},this.shift.fields);n["a"].put("/shifts/".concat(s.id),s).then((function(s){var e=s.data;t.$q.notify({message:e.message.content,position:"top",color:e.result?"positive":"warning"}),e.result?(t.$q.loading.hide(),t.$router.push("/shifts")):t.$q.loading.hide()}))}}},u=f,h=e("2877"),m=e("9989"),p=e("ead5"),v=e("079e"),b=e("9c40"),g=e("27f9"),q=e("0016"),$=e("eebe"),C=e.n($),w=Object(h["a"])(u,i,a,!1,null,null,null);s["default"]=w.exports;C()(w,"components",{QPage:m["a"],QBreadcrumbs:p["a"],QBreadcrumbsEl:v["a"],QBtn:b["a"],QInput:g["a"],QIcon:q["a"]})}}]);
(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[151],{"0ca4":function(e,t,i){"use strict";i.r(t);var s=function(){var e=this,t=e.$createElement,i=e._self._c||t;return i("q-page",{staticClass:"bg-grey-3"},[i("div",{staticClass:"q-pa-sm panel-header"},[i("div",{staticClass:"row"},[i("div",{staticClass:"col-sm-9"},[i("div",{staticClass:"q-pa-md q-gutter-sm"},[i("q-breadcrumbs",{staticStyle:{"font-size":"20px"},attrs:{align:"left"}},[i("q-breadcrumbs-el",{attrs:{label:"",icon:"home",to:"/"}}),i("q-breadcrumbs-el",{attrs:{label:"Horarios",to:"/timetables"}}),i("q-breadcrumbs-el",{attrs:{label:"Editar"},domProps:{textContent:e._s(e.timetable.fields.name)}})],1)],1)])])]),i("div",{staticClass:"q-pa-md bg-grey-3"},[i("div",{staticClass:"row bg-white border-panel"},[i("div",{staticClass:"col q-pa-md"},[i("div",{staticClass:"row q-col-gutter-xs"},[i("div",{staticClass:"col-xs-12 col-sm-3"},[i("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.timetable.fields.name.$error,label:"Nombre",rules:e.nameRules},on:{input:function(t){e.timetable.fields.name=t.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[i("q-icon",{attrs:{name:"fas fa-signature"}})]},proxy:!0}]),model:{value:e.timetable.fields.name,callback:function(t){e.$set(e.timetable.fields,"name",t)},expression:"timetable.fields.name"}})],1),i("div",{staticClass:"col-xs-12 col-sm-3"},[i("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",options:e.shiftOptions,label:"Turno",rules:e.shifttRules},scopedSlots:e._u([{key:"prepend",fn:function(){return[i("q-icon",{attrs:{name:"brightness_6"}})]},proxy:!0}]),model:{value:e.timetable.fields.shift,callback:function(t){e.$set(e.timetable.fields,"shift",t)},expression:"timetable.fields.shift"}})],1),i("div",{staticClass:"col-xs-12 col-sm-3"},[i("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",label:"Hora Entrada",rules:e.entryRules},scopedSlots:e._u([{key:"append",fn:function(){return[i("q-icon",{staticClass:"cursor-pointer",attrs:{name:"access_time"}},[i("q-popup-proxy",{attrs:{"transition-show":"scale","transition-hide":"scale"}},[i("q-time",{attrs:{color:"secondary","bg-color":"secondary"},model:{value:e.timetable.fields.time_entry,callback:function(t){e.$set(e.timetable.fields,"time_entry",t)},expression:"timetable.fields.time_entry"}},[i("div",{staticClass:"row items-center justify-end"},[i("q-btn",{directives:[{name:"close-popup",rawName:"v-close-popup"}],attrs:{label:"Cerrar",color:"primary",flat:""}})],1)])],1)],1)]},proxy:!0}]),model:{value:e.timetable.fields.time_entry,callback:function(t){e.$set(e.timetable.fields,"time_entry",t)},expression:"timetable.fields.time_entry"}})],1),i("div",{staticClass:"col-xs-12 col-sm-3"},[i("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",label:"Hora Salida",rules:e.departureRules},scopedSlots:e._u([{key:"append",fn:function(){return[i("q-icon",{staticClass:"cursor-pointer",attrs:{name:"access_time"}},[i("q-popup-proxy",{attrs:{"transition-show":"scale","transition-hide":"scale"}},[i("q-time",{attrs:{color:"secondary","bg-color":"secondary"},model:{value:e.timetable.fields.time_departure,callback:function(t){e.$set(e.timetable.fields,"time_departure",t)},expression:"timetable.fields.time_departure"}},[i("div",{staticClass:"row items-center justify-end"},[i("q-btn",{directives:[{name:"close-popup",rawName:"v-close-popup"}],attrs:{label:"Cerrar",color:"primary",flat:""}})],1)])],1)],1)]},proxy:!0}]),model:{value:e.timetable.fields.time_departure,callback:function(t){e.$set(e.timetable.fields,"time_departure",t)},expression:"timetable.fields.time_departure"}})],1)]),i("div",{staticClass:"row q-mb-sm q-mt-md"},[i("div",{staticClass:"col-xs-12 col-sm-2 offset-sm-10 pull-right"},[i("q-btn",{attrs:{color:"positive",icon:"save",label:"Actualizar"},on:{click:function(t){return e.updateTimetable()}}})],1)])])])])])},a=[],r=(i("b0c0"),i("caad"),i("2532"),i("a9e3"),i("aabb")),l=i("b5ae"),o=l.required,n={name:"EditTimetable",validations:{timetable:{fields:{shift:{required:o},time_entry:{required:o},time_departure:{required:o},name:{required:o}}}},data:function(){return{timetable:{fields:{id:null,shift:null,time_entry:null,time_departure:null,name:null}},shiftOptions:[]}},computed:{shifttRules:function(e){var t=this;return[function(e){return t.$v.timetable.fields.shift.required||"El campo Turno es requerido."}]},nameRules:function(e){var t=this;return[function(e){return t.$v.timetable.fields.name.required||"El campo Nombre es requerido."}]},entryRules:function(e){var t=this;return[function(e){return t.$v.timetable.fields.time_entry.required||"El campo Hora Entrada es requerido."}]},departureRules:function(e){var t=this;return[function(e){return t.$v.timetable.fields.time_departure.required||"El campo Hora Salida es requerido."}]}},beforeCreate:function(){this.$store.getters["users/roles"].includes(1)||this.$store.getters["users/roles"].includes(3)||this.$store.getters["users/roles"].includes(7)||this.$store.getters["users/roles"].includes(4)||this.$router.push("/")},mounted:function(){this.fetchFromServer(),this.getShifts()},methods:{fetchFromServer:function(){var e=this;this.$q.loading.show();var t=this.$route.params.id;r["a"].get("/timetables/".concat(t)).then((function(i){var s=i.data;s.timetable?(console.log(s),e.timetable.fields.id=t,e.timetable.fields.shift={value:s.timetable.job_title_id,label:s.timetable.shift_name},e.timetable.fields.time_entry=s.timetable.check_in_time,e.timetable.fields.time_departure=s.timetable.check_out_time,e.timetable.fields.name=s.timetable.name,e.$q.loading.hide(),console.log(e.timetable.fields)):e.$router.push("/timetables")})),this.$q.loading.hide()},getShifts:function(){var e=this;r["a"].get("/shifts/options").then((function(t){var i=t.data;e.shiftOptions=i.options}))},updateTimetable:function(){var e=this;if(this.$v.timetable.fields.$reset(),this.$v.timetable.fields.$touch(),this.$v.timetable.fields.$error)return this.$q.dialog({title:"Error",message:"Por favor, verifique las validaciones.",persistent:!0}),!1;this.$q.loading.show();var t=[];t.id=Number(this.timetable.fields.id),t.shift_id=Number(this.timetable.fields.shift.value),t.time_entry=this.timetable.fields.time_entry,t.time_departure=this.timetable.fields.time_departure,t.name=this.timetable.fields.name,r["a"].put("/timetables/".concat(t.id),t).then((function(t){var i=t.data;e.$q.notify({message:i.message.content,position:"top",color:i.result?"positive":"warning"}),i.result?(e.$q.loading.hide(),e.$router.push("/timetables")):e.$q.loading.hide()}))}}},d=n,c=i("2877"),u=i("9989"),m=i("ead5"),f=i("079e"),b=i("27f9"),p=i("0016"),h=i("ddd8"),v=i("7cbe"),q=i("ca78"),_=i("9c40"),y=i("7f67"),g=i("eebe"),$=i.n(g),C=Object(c["a"])(d,s,a,!1,null,null,null);t["default"]=C.exports;$()(C,"components",{QPage:u["a"],QBreadcrumbs:m["a"],QBreadcrumbsEl:f["a"],QInput:b["a"],QIcon:p["a"],QSelect:h["a"],QPopupProxy:v["a"],QTime:q["a"],QBtn:_["a"]}),$()(C,"directives",{ClosePopup:y["a"]})}}]);
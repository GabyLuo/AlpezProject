(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[63],{d7ce:function(e,t,a){"use strict";a.r(t);var r=function(){var e=this,t=e._self._c;return t("q-page",[t("div",{staticClass:"q-pa-sm panel-header"},[t("div",{staticClass:"row"},[t("div",{staticClass:"col-sm-9"},[t("div",{staticClass:"q-pa-md q-gutter-sm"},[t("q-breadcrumbs",{staticStyle:{"font-size":"20px"},attrs:{align:"left"}},[t("q-breadcrumbs-el",{attrs:{label:"",icon:"home",to:"/"}}),t("q-breadcrumbs-el",{attrs:{label:"Tipo de Cambio",to:"/exchanges"}}),t("q-breadcrumbs-el",{attrs:{label:"Nuevo Tipo de Cambio"}})],1)],1)])])]),t("div",{staticClass:"q-pa-md bg-grey-3"},[t("div",{staticClass:"row bg-white border-panel"},[t("div",{staticClass:"col q-pa-md"},[t("div",{staticClass:"row q-mb-sm"}),t("div",{staticClass:"row q-col-gutter-xs"},[t("div",{staticClass:"col-xs-12 col-sm-3"},[t("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"","emit-value":"","map-options":"",options:e.currencyOptions,label:"Tipo de Moneda",rules:e.currencyRules},scopedSlots:e._u([{key:"prepend",fn:function(){return[t("q-icon",{attrs:{name:"fas fa-dollar-sign"}})]},proxy:!0}]),model:{value:e.exchange.fields.currency,callback:function(t){e.$set(e.exchange.fields,"currency",t)},expression:"exchange.fields.currency"}})],1),t("div",{staticClass:"col-xs-12 col-sm-3 text-center"},[t("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",rules:e.dateRules,mask:"date",label:"Fecha"},scopedSlots:e._u([{key:"prepend",fn:function(){return[t("q-icon",{attrs:{name:"event"}})]},proxy:!0}]),model:{value:e.exchange.fields.date,callback:function(t){e.$set(e.exchange.fields,"date",t)},expression:"exchange.fields.date"}},[t("q-popup-proxy",{ref:"date",attrs:{"transition-show":"scale","transition-hide":"scale"}},[t("div",{staticClass:"col-sm-12"},[t("q-date",{attrs:{color:"secondary",locale:e.myLocale,"text-color":"white",mask:"DD/MM/YYYY","today-btn":""},model:{value:e.exchange.fields.date,callback:function(t){e.$set(e.exchange.fields,"date",t)},expression:"exchange.fields.date"}})],1)])],1)],1),t("div",{staticClass:"col-xs-12 col-sm-6"},[t("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",type:"number",error:e.$v.exchange.fields.current_value.$error,label:"Valor",rules:e.currentValueRules},scopedSlots:e._u([{key:"prepend",fn:function(){return[t("q-icon",{attrs:{name:"fas fa-signature"}})]},proxy:!0}]),model:{value:e.exchange.fields.current_value,callback:function(t){e.$set(e.exchange.fields,"current_value",t)},expression:"exchange.fields.current_value"}})],1)]),t("div",{staticClass:"row q-mb-sm q-mt-md"},[t("div",{staticClass:"col-xs-12 col-sm-2 offset-sm-10 pull-right"},[t("q-btn",{attrs:{color:"positive",icon:"save",label:"Actualizar"},on:{click:function(t){return e.editExchange()}}})],1)])])])])])},s=[],n=a("ded3"),i=a.n(n),c=(a("caad"),a("2532"),a("14d9"),a("a9e3"),a("a15b"),a("aabb")),o=a("b5ae"),l=o.required,u={name:"NewExchange",validations:{exchange:{fields:{current_value:{required:l},date:{required:l},currency:{required:l}}}},data:function(){return{myLocale:{days:"Domingo_Lunes_Martes_Miércoles_Jueves_Viernes_Sábado".split("_"),daysShort:"Dom_Lun_Mar_Mié_Jue_Vie_Sáb".split("_"),months:"Enero_Febrero_Marzo_Abril_Mayo_Junio_Julio_Agosto_Septiembre_Octubre_Noviembre_Diciembre".split("_"),monthsShort:"Ene_Feb_Mar_Abr_May_Jun_Jul_Ago_Sep_Oct_Nov_Dic".split("_"),firstDayOfWeek:1},exchange:{fields:{current_value:null,date:null,currency:null}},currencyOptions:[]}},computed:{dateRules:function(e){var t=this;return[function(e){return t.$v.exchange.fields.date.required||"El campo Fecha es requerido."}]},currentValueRules:function(e){var t=this;return[function(e){return t.$v.exchange.fields.current_value.required||"El campo valor es requerido."}]},currencyRules:function(e){var t=this;return[function(e){return t.$v.exchange.fields.currency.required||"El campo Tipo de Moneda es requerido."}]}},beforeCreate:function(){this.$store.getters["users/roles"].includes(1)||this.$store.getters["users/roles"].includes(3)||this.$store.getters["users/roles"].includes(7)||this.$store.getters["users/roles"].includes(10)||this.$router.push("/")},created:function(){this.getCurrencies(),this.fetchFromServer()},mounted:function(){},methods:{editExchange:function(){var e=this;if(this.$v.exchange.fields.$reset(),this.$v.exchange.fields.$touch(),this.$v.exchange.fields.$error)return this.$q.dialog({title:"Error",message:"Por favor, verifique las validaciones.",persistent:!0}),!1;this.$q.loading.show();var t=[];t.id=Number(this.exchange.fields.id),t.date=this.invertDate(this.exchange.fields.date),t.current_value=Number(i()({},this.exchange.fields).current_value),t.currency_id=i()({},this.exchange.fields).currency.value,c["a"].put("/exchanges/".concat(t.id),t).then((function(t){var a=t.data;console.log(a),e.$q.notify({message:a.message.content,position:"top",color:a.result?"positive":"warning"}),a.result?(e.$q.loading.hide(),e.$router.push("/exchanges")):e.$q.loading.hide()}))},invertDate:function(e){if(null!==e)var t=e.split("/").reverse().join("-");return t},fetchFromServer:function(){var e=this;this.$q.loading.show();var t=this.$route.params.id;c["a"].get("/exchanges/".concat(t)).then((function(t){var a=t.data;a.exchange?(console.log(a.exchange),e.exchange.fields=a.exchange,e.exchange.fields.currency={label:a.exchange.label,value:a.exchange.value}):(e.$q.loading.hide(),e.$router.push("/exchanges")),e.$q.loading.hide(),console.log(e.exchange.fields)}))},getCurrencies:function(){var e=this;c["a"].get("/currencies/options").then((function(t){var a=t.data;e.currencyOptions=a.options,e.$q.loading.hide()}))}}},d=u,h=a("2877"),f=a("9989"),g=a("ead5"),p=a("079e"),v=a("ddd8"),m=a("0016"),x=a("7cbe"),b=a("52ee"),_=a("27f9"),q=a("9c40"),y=a("eebe"),$=a.n(y),C=Object(h["a"])(d,r,s,!1,null,null,null);t["default"]=C.exports;$()(C,"components",{QPage:f["a"],QBreadcrumbs:g["a"],QBreadcrumbsEl:p["a"],QSelect:v["a"],QIcon:m["a"],QPopupProxy:x["a"],QDate:b["a"],QInput:_["a"],QBtn:q["a"]})}}]);
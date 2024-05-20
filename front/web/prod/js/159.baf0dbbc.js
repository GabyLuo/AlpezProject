(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[159],{edc2:function(e,t,s){"use strict";s.r(t);var i=function(){var e=this,t=e.$createElement,s=e._self._c||t;return s("q-page",{staticClass:"bg-grey-3"},[s("div",{staticClass:"q-pa-sm panel-header"},[s("div",{staticClass:"row"},[s("div",{staticClass:"col-sm-12 row"},[s("div",{staticClass:"q-pa-md q-gutter-sm"},[s("q-breadcrumbs",{staticStyle:{"font-size":"20px"},attrs:{align:"right"}},[s("q-breadcrumbs-el",{attrs:{label:"",icon:"home",to:"/"}}),s("q-breadcrumbs-el",{attrs:{label:"1"===this.$route.params.type?"Ordenes de compra":"Recepciones",to:"1"===this.$route.params.type?"/purchase-orders/"+this.$route.params.id:"/raw-material-shipments"}}),s("q-breadcrumbs-el",{attrs:{label:"Nueva recepción"}})],1)],1)])])]),s("div",{staticClass:"q-pa-md bg-grey-3"},[s("div",{staticClass:"row bg-white border-panel"},[s("div",{staticClass:"col q-pa-md"},[s("div",{staticClass:"row q-col-gutter-xs"},[s("div",{staticClass:"col-xs-12 col-sm-3"},[s("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",mask:"date",label:"Fecha de recepción",rules:e.shipmentReceiveDateRules},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"event"}})]},proxy:!0}]),model:{value:e.shipment.fields.receive_date,callback:function(t){e.$set(e.shipment.fields,"receive_date",t)},expression:"shipment.fields.receive_date"}},[s("q-popup-proxy",{ref:"shipmentFieldsReceiveDateRef",attrs:{"transition-show":"scale","transition-hide":"scale"}},[s("div",{staticClass:"col-sm-12"},[s("q-date",{attrs:{mask:"DD/MM/YYYY","today-btn":""},on:{input:function(){return e.$refs.shipmentFieldsReceiveDateRef.hide()}},model:{value:e.shipment.fields.receive_date,callback:function(t){e.$set(e.shipment.fields,"receive_date",t)},expression:"shipment.fields.receive_date"}})],1)])],1)],1),s("div",{staticClass:"col-xs-12 col-sm-3 text-center"},[s("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",mask:"time",label:"Hora de recepción",rules:e.shipmentReceiveTimeRules},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"access_time"}})]},proxy:!0}]),model:{value:e.shipment.fields.receive_time,callback:function(t){e.$set(e.shipment.fields,"receive_time",t)},expression:"shipment.fields.receive_time"}},[s("q-popup-proxy",{ref:"shipmentFieldsReceiveTimeRef",attrs:{"transition-show":"scale","transition-hide":"scale"}},[s("div",{staticClass:"col-sm-12"},[s("q-time",{attrs:{"now-btn":""},on:{input:function(){return e.$refs.shipmentFieldsReceiveTimeRef.hide()}},model:{value:e.shipment.fields.receive_time,callback:function(t){e.$set(e.shipment.fields,"receive_time",t)},expression:"shipment.fields.receive_time"}})],1)])],1)],1),s("div",{staticClass:"col-xs-12 col-sm-3"},[s("q-input",{attrs:{color:"dark","bg-color":"blue",dark:"",filled:"",disable:"",error:e.$v.shipment.fields.status.$error,label:"Estatus",rules:e.shipmentStatusRules},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"fas fa-battery-empty"}})]},proxy:!0}]),model:{value:e.shipment.fields.status,callback:function(t){e.$set(e.shipment.fields,"status",t)},expression:"shipment.fields.status"}})],1),s("div",{staticClass:"col-xs-12 col-sm-2 offset-xs-10 pull-right"},[s("q-btn",{attrs:{color:"positive",icon:"save",label:"Guardar"},on:{click:function(t){return e.createShipment()}}})],1)])])])])])},r=[],a=s("ded3"),n=s.n(a),o=(s("caad"),s("2532"),s("99af"),s("4d90"),s("d3b7"),s("25f0"),s("a15b"),s("ac1f"),s("1276"),s("aabb")),c=s("b5ae"),l=c.required,d=c.maxLength,u={name:"NewShipment",validations:{shipment:{fields:{status:{required:l,maxLength:d(10)},receive_date:{required:l},receive_time:{required:l}}}},beforeCreate:function(){22!==!this.$store.getters["users/rol"]||this.$store.getters["users/roles"].includes(1)||this.$store.getters["users/roles"].includes(3)||this.$store.getters["users/roles"].includes(7)||this.$store.getters["users/roles"].includes(2)||this.$router.push("/")},created:function(){console.log(this.$route.params)},data:function(){return{shipment:{fields:{status:"NUEVO",receive_date:"".concat((new Date).getDate().toString().padStart(2,"0"),"/").concat(((new Date).getMonth()+1).toString().padStart(2,"0"),"/").concat((new Date).getFullYear()),receive_time:"".concat((new Date).getHours().toString().padStart(2,"0"),":").concat((new Date).getMinutes().toString().padStart(2,"0"))}}}},computed:{shipmentReceiveDateRules:function(e){var t=this;return[function(e){return t.$v.shipment.fields.receive_date.required||"El campo Fecha de recepción es requerido."}]},shipmentReceiveTimeRules:function(e){var t=this;return[function(e){return t.$v.shipment.fields.receive_time.required||"El campo Hora de recepción es requerido."}]},shipmentStatusRules:function(e){var t=this;return[function(e){return t.$v.shipment.fields.status.required||"El campo Estatus es requerido."},function(e){return t.$v.shipment.fields.status.maxLength||"El campo Estatus no debe exceder los 10 dígitos."}]}},methods:{backToPurchaseOrder:function(){this.$router.push("/purchase-orders/".concat(this.$route.params.id))},invertDate:function(e){if(null!==e)var t=e.split("/").reverse().join("-");return t},createShipment:function(){var e=this;if(this.$v.shipment.fields.$reset(),this.$v.shipment.fields.$touch(),this.$v.shipment.fields.$error)return this.$q.dialog({title:"Error",message:"Por favor, verifique las validaciones.",persistent:!0}),!1;if(null==this.shipment.fields.receive_date)return this.$q.dialog({title:"Error",message:"Por favor, seleccione la fecha de recepción.",persistent:!0}),!1;this.$q.loading.show();var t=n()({},this.shipment.fields);t.receive_date=this.invertDate(this.shipment.fields.receive_date),t.order_id=this.$route.params.id,o["a"].post("/shipments",t).then((function(t){var s=t.data;e.$q.notify({message:s.message.content,position:"top",color:s.result?"positive":"warning"}),s.result?(e.$q.loading.hide(),e.$router.push("/shipments/".concat(s.shipment.id,"/").concat(e.$route.params.type))):e.$q.loading.hide()}))}}},p=u,m=s("2877"),h=s("9989"),f=s("ead5"),v=s("079e"),b=s("ddd8"),g=s("0016"),$=s("7cbe"),q=s("52ee"),_=s("ca78"),x=s("27f9"),w=s("9c40"),y=s("eebe"),k=s.n(y),R=Object(m["a"])(p,i,r,!1,null,null,null);t["default"]=R.exports;k()(R,"components",{QPage:h["a"],QBreadcrumbs:f["a"],QBreadcrumbsEl:v["a"],QSelect:b["a"],QIcon:g["a"],QPopupProxy:$["a"],QDate:q["a"],QTime:_["a"],QInput:x["a"],QBtn:w["a"]})}}]);
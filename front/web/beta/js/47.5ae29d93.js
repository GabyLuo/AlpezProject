(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[47],{"170a":function(e,n,a){"use strict";a.r(n);a("b0c0");var t=function(){var e=this,n=e._self._c;return n("q-page",{staticClass:"bg-grey-3"},[n("div",{staticClass:"q-pa-sm panel-header"},[n("div",{staticClass:"row"},[n("div",{staticClass:"col-sm-9"},[n("div",{staticClass:"q-pa-md q-gutter-sm"},[n("q-breadcrumbs",{staticStyle:{"font-size":"20px"},attrs:{align:"left"}},[n("q-breadcrumbs-el",{attrs:{label:"",icon:"home",to:"/"}}),n("q-breadcrumbs-el",{attrs:{label:"Canales",to:"/channels"}}),n("q-breadcrumbs-el",{attrs:{label:"Editar Canal"},domProps:{textContent:e._s(e.channel.fields.name)}})],1)],1)])])]),n("div",{staticClass:"q-pa-md bg-grey-3"},[n("div",{staticClass:"row bg-white border-panel"},[n("div",{staticClass:"col q-pa-md"},[n("div",{staticClass:"row q-mb-sm",staticStyle:{visibility:"hidden"}},[n("div",{staticClass:"col-sm-1 offset-11 pull-right"},[n("q-btn",{attrs:{color:"primary",label:"Editar"}})],1)]),n("div",{staticClass:"row q-col-gutter-xs"},[n("div",{staticClass:"col-xs-12 col-sm-6"},[n("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.channel.fields.code.$error,label:"Código",rules:e.codeRules},on:{input:function(n){e.channel.fields.code=n.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[n("q-icon",{attrs:{name:"fingerprint"}})]},proxy:!0}]),model:{value:e.channel.fields.code,callback:function(n){e.$set(e.channel.fields,"code",n)},expression:"channel.fields.code"}})],1),n("div",{staticClass:"col-xs-12 col-sm-6"},[n("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.channel.fields.name.$error,label:"Nombre",rules:e.nameRules},on:{input:function(n){e.channel.fields.name=n.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[n("q-icon",{attrs:{name:"fas fa-signature"}})]},proxy:!0}]),model:{value:e.channel.fields.name,callback:function(n){e.$set(e.channel.fields,"name",n)},expression:"channel.fields.name"}})],1)]),n("div",{staticClass:"row q-mb-sm q-mt-md"},[n("div",{staticClass:"col-xs-12 col-sm-2 offset-sm-10 pull-right"},[n("q-btn",{attrs:{color:"positive",icon:"save",label:"Actualizar"},on:{click:function(n){return e.editChannel()}}})],1)])])])])])},s=[],l=a("ded3"),i=a.n(l),r=(a("14d9"),a("aabb")),o=a("b5ae"),c=o.required,d=o.maxLength,u={name:"EditChannel",validations:{channel:{fields:{name:{required:c,maxLength:d(50)},code:{required:c,maxLength:d(5)}}}},data:function(){return{channel:{fields:{id:null,name:null,code:null}}}},computed:{nameRules:function(e){var n=this;return[function(e){return n.$v.channel.fields.name.required||"El campo Nombre es requerido."},function(e){return n.$v.channel.fields.name.maxLength||"El campo Nombre no debe exceder los 50 dígitos."}]},codeRules:function(e){var n=this;return[function(e){return n.$v.channel.fields.code.required||"El campo Código es requerido."},function(e){return n.$v.channel.fields.code.maxLength||"El campo Código no debe exceder los 5 dígitos."}]}},created:function(){this.fetchFromServer()},methods:{fetchFromServer:function(){var e=this;this.$q.loading.show();var n=this.$route.params.id;r["a"].get("/channels/".concat(n)).then((function(n){var a=n.data;a.channel?(e.channel.fields=a.channel,e.$q.loading.hide()):e.$router.push("/channels")}))},updateChannelFields:function(){this.$v.channel.fields.$reset(),this.$v.channel.fields.$touch()},editChannel:function(){var e=this;if(this.$v.channel.fields.$reset(),this.$v.channel.fields.$touch(),this.$v.channel.fields.$error)return this.$q.dialog({title:"Error",message:"Por favor, verifique las validaciones.",persistent:!0}),!1;this.$q.loading.show();var n=i()({},this.channel.fields);r["a"].put("/channels/".concat(n.id),n).then((function(n){var a=n.data;e.$q.notify({message:a.message.content,position:"top",color:a.result?"positive":"warning"}),a.result?(e.$q.loading.hide(),e.$router.push("/channels")):e.$q.loading.hide()}))}}},h=u,f=a("2877"),m=a("9989"),p=a("ead5"),v=a("079e"),b=a("9c40"),g=a("27f9"),q=a("0016"),$=a("eebe"),C=a.n($),x=Object(f["a"])(h,t,s,!1,null,null,null);n["default"]=x.exports;C()(x,"components",{QPage:m["a"],QBreadcrumbs:p["a"],QBreadcrumbsEl:v["a"],QBtn:b["a"],QInput:g["a"],QIcon:q["a"]})}}]);
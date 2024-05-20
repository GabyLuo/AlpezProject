(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[197],{"135c":function(e,s,r){"use strict";r.r(s);var i=function(){var e=this,s=e._self._c;return s("q-page",{staticClass:"bg-grey-3"},[s("div",{staticClass:"q-pa-sm panel-header"},[s("div",{staticClass:"row"},[s("div",{staticClass:"col-sm-9"},[s("span",{staticClass:"q-ml-md grey-8 fs28 page-title"},[e._v("Perfil "+e._s(e.user.fields.email))])]),s("div",{staticClass:"col-sm-3"},[s("div",{staticClass:"q-pa-md q-gutter-sm"},[s("q-breadcrumbs",{attrs:{align:"right"}},[s("q-breadcrumbs-el",{attrs:{label:"",icon:"home",to:"/"}}),s("q-breadcrumbs-el",{attrs:{label:"Usuarios",to:"/users"}}),s("q-breadcrumbs-el",{attrs:{label:"Editar"}})],1)],1)])])]),s("div",{staticClass:"q-pa-md bg-grey-3"},[s("div",{staticClass:"row bg-white border-panel"},[s("div",{staticClass:"col q-pa-md"},[s("div",{staticClass:"row q-mb-sm",staticStyle:{visibility:"hidden"}},[s("div",{staticClass:"col-sm-1 offset-11 pull-right"},[s("q-btn",{attrs:{color:"primary",label:"Editar"}})],1)]),s("div",{staticClass:"row q-col-gutter-xs"},[s("div",{staticClass:"col-xs-12 col-sm-3"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.user.fields.email.$error,label:"Dirección de correo electrónico",rules:e.emailRules},on:{input:function(s){e.user.fields.email=s.toLowerCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"fas fa-at"}})]},proxy:!0}]),model:{value:e.user.fields.email,callback:function(s){e.$set(e.user.fields,"email",s)},expression:"user.fields.email"}})],1),s("div",{staticClass:"col-xs-12 col-sm-4"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.user.fields.nickname.$error,label:"Nickname",rules:e.nicknameRules},on:{input:function(s){e.user.fields.nickname=s.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"fas fa-signature"}})]},proxy:!0}]),model:{value:e.user.fields.nickname,callback:function(s){e.$set(e.user.fields,"nickname",s)},expression:"user.fields.nickname"}})],1),s("div",{staticClass:"col-xs-12 col-sm-2"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.user.fields.password.$error,label:"Contraseña",type:e.isPwd?"password":"text"},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"fas fa-key"}})]},proxy:!0},{key:"append",fn:function(){return[s("q-icon",{staticClass:"cursor-pointer",attrs:{name:e.isPwd?"visibility_off":"visibility"},on:{click:function(s){e.isPwd=!e.isPwd}}})]},proxy:!0}]),model:{value:e.user.fields.password,callback:function(s){e.$set(e.user.fields,"password",s)},expression:"user.fields.password"}})],1)]),s("div",{staticClass:"row q-mb-sm q-mt-md"},[s("div",{staticClass:"col-xs-12 col-sm-2 offset-sm-10 pull-right"},[s("q-btn",{attrs:{color:"positive",icon:"save",label:"Guardar"},on:{click:function(s){return e.editUser()}}})],1)])])])])])},a=[],t=r("ded3"),o=r.n(t),l=(r("14d9"),r("aabb")),n=r("b5ae"),c=n.required,d=n.maxLength,u=n.email,m={name:"Profile",validations:{user:{fields:{email:{required:c,maxLength:d(100),email:u},nickname:{required:c,maxLength:d(100)},password:{}}}},data:function(){return{user:{fields:{id:null,email:null,nickname:null,password:null}},isPwd:!0}},computed:{emailRules:function(e){var s=this;return[function(e){return s.$v.user.fields.email.required||"El campo Dirección de correo electrónico es requerido."},function(e){return s.$v.user.fields.email.maxLength||"El campo Dirección de correo electrónico no debe exceder los 100 dígitos."},function(e){return s.$v.user.fields.email.email||"El campo Dirección de correo electrónico debe contener una dirección de correo electrónico válida."}]},nicknameRules:function(e){var s=this;return[function(e){return s.$v.user.fields.nickname.required||"El campo Nickname es requerido."},function(e){return s.$v.user.fields.nickname.maxLength||"El campo Nickname no debe exceder los 100 dígitos."}]}},created:function(){this.fetchFromServer()},methods:{fetchFromServer:function(){var e=this;l["a"].get("/users/profile").then((function(s){var r=s.data;r.user?(e.user.fields=r.user,e.$q.loading.hide()):e.$router.push("/")}))},editUser:function(){var e=this;if(this.$v.user.fields.$reset(),this.$v.user.fields.$touch(),this.$v.user.fields.$error)return this.$q.dialog({title:"Error",message:"Por favor, verifique las validaciones.",persistent:!0}),!1;this.$q.loading.show();var s=o()({},this.user.fields);l["a"].put("/users/profile",s).then((function(s){var r=s.data;e.$q.notify({message:r.message.content,position:"top",color:r.result?"positive":"warning"}),r.result?(e.$q.loading.hide(),window.location.reload()):(e.$q.loading.hide(),r.errors&&"Expired token"===r.errors.message&&(localStorage.removeItem("JWT"),window.location.reload()))}))}}},f=m,p=r("2877"),v=r("9989"),b=r("ead5"),g=r("079e"),q=r("9c40"),h=r("27f9"),w=r("0016"),k=r("eebe"),$=r.n(k),x=Object(p["a"])(f,i,a,!1,null,null,null);s["default"]=x.exports;$()(x,"components",{QPage:v["a"],QBreadcrumbs:b["a"],QBreadcrumbsEl:g["a"],QBtn:q["a"],QInput:h["a"],QIcon:w["a"]})}}]);
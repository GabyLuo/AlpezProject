(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[17],{"1ea5":function(e,s,t){},"5e1f":function(e,s,t){"use strict";t("1ea5")},e6f9:function(e,s,t){"use strict";t.r(s);t("b0c0");var a=function(){var e=this,s=e._self._c;return s("q-page",{staticClass:"bg-grey-3"},[s("div",{staticClass:"q-pa-sm panel-header"},[s("div",{staticClass:"row"},[s("div",{staticClass:"col-sm-6"},[s("div",{staticClass:"q-pa-md q-gutter-sm"},[s("q-breadcrumbs",{attrs:{align:"left"}},[s("q-breadcrumbs-el",{staticClass:"fs20",attrs:{label:"",icon:"home",to:"/"}}),s("q-breadcrumbs-el",{staticClass:"fs20",attrs:{label:"Roles",to:"/roles"}}),s("q-breadcrumbs-el",{staticClass:"fs20",attrs:{label:"Nuevo"}})],1)],1)])])]),s("div",{staticClass:"q-pa-md bg-grey-3"},[s("div",{staticClass:"row bg-white border-panel"},[s("div",{staticClass:"col q-pa-md"},[s("div",{staticClass:"row q-col-gutter-xs"},[s("div",{staticClass:"col-xs-12 col-sm-4"},[s("q-input",{staticClass:"uppcase",attrs:{color:"white",borderless:"","bg-color":"secondary",filled:"",dark:"",error:e.$v.rol.fields.name.$error,rules:[function(e){return!!e||"Campo requerido"}],label:"Rol"},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"fas fa-user"}})]},proxy:!0}]),model:{value:e.rol.fields.name,callback:function(s){e.$set(e.rol.fields,"name",s)},expression:"rol.fields.name"}})],1)]),s("div",{staticClass:"row q-mb-sm"},[s("div",{staticClass:"col-xs-12 col-sm-2 offset-sm-10 pull-right"},[s("q-btn",{attrs:{color:"positive",icon:"save",label:"Guardar"},on:{click:function(s){return e.createRol()}}})],1)])])])])])},r=[],o=t("7ec2"),l=t.n(o),i=t("c973"),n=t.n(i),c=(t("14d9"),t("aabb")),d=t("b5ae"),u=d.required,f=d.maxLength,m={name:"NewUser",validations:{rol:{fields:{name:{required:u,maxLength:f(50)}}}},data:function(){return{selectRoles:[],rol:{fields:{name:null}},isPwd:!0}},computed:{},created:function(){this.loadAll()},methods:{loadAll:function(){return n()(l()().mark((function e(){return l()().wrap((function(e){while(1)switch(e.prev=e.next){case 0:case"end":return e.stop()}}),e)})))()},createRol:function(){var e=this;if(this.$v.rol.fields.$reset(),this.$v.rol.fields.$touch(),this.$v.rol.fields.$error)return this.$q.notify({message:"Por favor revise los campos.",color:"red",position:"top"}),!1;this.showLoading();var s=this.rol.fields;c["a"].post("/roles",s).then((function(s){var t=s.data;t.result&&e.$router.push("/roles"),e.$q.notify({message:t.message.content,color:t.result?"positive":"red",position:"top"}),e.beforeDestroy()}))},showLoading:function(){this.$q.loading.show({spinnerColor:"primary",spinnerSize:140,backgroundColor:"white",message:"Cargando..",messageColor:"black"})},beforeDestroy:function(){this.$q.loading.hide()}}},p=m,b=(t("5e1f"),t("2877")),h=t("9989"),v=t("ead5"),g=t("079e"),q=t("27f9"),w=t("0016"),C=t("9c40"),$=t("eebe"),y=t.n($),k=Object(b["a"])(p,a,r,!1,null,null,null);s["default"]=k.exports;y()(k,"components",{QPage:h["a"],QBreadcrumbs:v["a"],QBreadcrumbsEl:g["a"],QInput:q["a"],QIcon:w["a"],QBtn:C["a"]})}}]);
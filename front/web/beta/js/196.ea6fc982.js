(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[196],{"2a2f":function(e,s,r){"use strict";r.r(s);var t=function(){var e=this,s=e._self._c;return s("q-page",{staticClass:"bg-grey-3"},[s("div",{staticClass:"q-pa-sm panel-header"},[s("div",{staticClass:"row"},[s("div",{staticClass:"col-sm-9"},[s("div",{staticClass:"q-pa-md q-gutter-sm"},[s("q-breadcrumbs",{staticStyle:{"font-size":"20px"},attrs:{align:"left"}},[s("q-breadcrumbs-el",{attrs:{label:"",icon:"home",to:"/"}}),s("q-breadcrumbs-el",{attrs:{label:"Usuarios",to:"/users"}}),s("q-breadcrumbs-el",{attrs:{label:"Nuevo Usuario"}})],1)],1)])])]),s("div",{staticClass:"q-pa-md bg-grey-3"},[s("div",{staticClass:"row bg-white border-panel"},[s("div",{staticClass:"col q-pa-md"},[s("div",{staticClass:"row q-col-gutter-xs"},[26!==e.user.fields.role?s("div",{staticClass:"col-xs-12 col-sm-3 text-center"},[s("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",options:e.branchesList,label:"Estaciones",error:e.$v.user.fields.branch_id.$error,rules:e.branchOfficeRules,"emit-value":"","map-options":""},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"business"}})]},proxy:!0}],null,!1,3736652810),model:{value:e.user.fields.branch_id,callback:function(s){e.$set(e.user.fields,"branch_id",s)},expression:"user.fields.branch_id"}})],1):s("div",{staticClass:"col-xs-12 col-sm-3 text-center"},[s("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",options:e.clusterOptions,label:"Cluster","emit-value":"","map-options":""},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"business"}})]},proxy:!0}]),model:{value:e.user.fields.cluster_id,callback:function(s){e.$set(e.user.fields,"cluster_id",s)},expression:"user.fields.cluster_id"}})],1),s("div",{staticClass:"col-xs-12 col-sm-3"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.user.fields.email.$error,label:"Dirección de correo electrónico",rules:e.emailRules},on:{input:function(s){e.user.fields.email=s.toLowerCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"fas fa-at"}})]},proxy:!0}]),model:{value:e.user.fields.email,callback:function(s){e.$set(e.user.fields,"email",s)},expression:"user.fields.email"}})],1),s("div",{staticClass:"col-xs-12 col-sm-3"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.user.fields.nickname.$error,label:"Nombre Completo",rules:e.nicknameRules},on:{input:function(s){e.user.fields.nickname=s.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"fas fa-signature"}})]},proxy:!0}]),model:{value:e.user.fields.nickname,callback:function(s){e.$set(e.user.fields,"nickname",s)},expression:"user.fields.nickname"}})],1),s("div",{staticClass:"col-xs-12 col-sm-3"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.user.fields.password.$error,label:"Contraseña",rules:e.passwordRules,type:e.isPwd?"password":"text"},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"fas fa-key"}})]},proxy:!0},{key:"append",fn:function(){return[s("q-icon",{staticClass:"cursor-pointer",attrs:{name:e.isPwd?"visibility_off":"visibility"},on:{click:function(s){e.isPwd=!e.isPwd}}})]},proxy:!0}]),model:{value:e.user.fields.password,callback:function(s){e.$set(e.user.fields,"password",s)},expression:"user.fields.password"}})],1),s("div",{staticClass:"col-xs-12 col-sm-3"},[s("q-select",{staticClass:"uppcase",attrs:{color:"dark",borderless:"","bg-color":"secondary",filled:"",label:"Rol",options:e.selectRoles,error:e.$v.user.fields.role.$error,"emit-value":"","map-options":""},model:{value:e.user.fields.role,callback:function(s){e.$set(e.user.fields,"role",s)},expression:"user.fields.role"}})],1)]),s("div",{staticClass:"row q-mb-sm q-mt-md"},[s("div",{staticClass:"col-xs-12 col-sm-2 offset-sm-10 pull-right"},[s("q-btn",{attrs:{color:"positive",icon:"save",label:"Guardar"},on:{click:function(s){return e.createUser()}}})],1)])])])])])},i=[],o=r("ded3"),l=r.n(o),n=(r("14d9"),r("d3b7"),r("159b"),r("b0c0"),r("aabb")),a=r("b5ae"),c=a.required,u=a.maxLength,d=a.email,f={name:"NewUser",validations:{user:{fields:{email:{required:c,maxLength:u(100),email:d},nickname:{required:c,maxLength:u(100)},password:{required:c},branch_id:{required:c},role:{required:c}}}},data:function(){return{user:{fields:{email:null,nickname:null,password:null,branch_id:null,role:null,cluster_id:null}},isPwd:!0,branchesList:[],selectRoles:[],clusterOptions:[]}},beforeRouteEnter:function(e,s,r){r((function(e){var s=e.$store.getters["users/rol"];console.log(s),1===s||3===s||7===s?r():r("/")}))},created:function(){this.getRoles(),this.getBranchesList(),this.getClusterList()},computed:{emailRules:function(e){var s=this;return[function(e){return s.$v.user.fields.email.required||"El campo Dirección de correo electrónico es requerido."},function(e){return s.$v.user.fields.email.maxLength||"El campo Dirección de correo electrónico no debe exceder los 100 dígitos."},function(e){return s.$v.user.fields.email.email||"El campo Dirección de correo electrónico debe contener una dirección de correo electrónico válida."}]},nicknameRules:function(e){var s=this;return[function(e){return s.$v.user.fields.nickname.required||"El campo Nickname es requerido."},function(e){return s.$v.user.fields.nickname.maxLength||"El campo Nickname no debe exceder los 100 dígitos."}]},passwordRules:function(e){var s=this;return[function(e){return s.$v.user.fields.password.required||"El campo Contraseña es requerido."}]},branchOfficeRules:function(e){var s=this;return[function(e){return s.$v.user.fields.branch_id.required||"El campo Cluster es requerido."}]}},methods:{createUser:function(){var e=this;if(26===this.user.fields.role&&(this.user.fields.branch_id=0),this.$v.user.fields.$reset(),this.$v.user.fields.$touch(),this.$v.user.fields.$error)return this.$q.dialog({title:"Error",message:"Por favor, verifique las validaciones.",persistent:!0}),!1;this.$q.loading.show();var s=l()({},this.user.fields);n["a"].post("/users",s).then((function(s){var r=s.data;e.$q.notify({message:r.message.content,position:"top",color:r.result?"positive":"warning"}),r.result?(e.$q.loading.hide(),e.$router.push("/users/".concat(r.userId))):e.$q.loading.hide()}))},getBranchesList:function(){var e=this;n["a"].get("branch-offices/options").then((function(s){e.branchesList=s.data.options}))},getClusterList:function(){var e=this;n["a"].get("supercluster/getOptions").then((function(s){e.clusterOptions=s.data.options}))},getRoles:function(){var e=this;this.selectRoles=[],n["a"].get("/roles").then((function(s){var r=s.data;r.roles.length>0&&r.roles.forEach((function(s){e.selectRoles.push({label:s.name,value:s.id})}))})).catch((function(e){return e}))}}},p=f,m=r("2877"),b=r("9989"),v=r("ead5"),h=r("079e"),g=r("ddd8"),q=r("0016"),k=r("27f9"),$=r("9c40"),x=r("eebe"),w=r.n(x),C=Object(m["a"])(p,t,i,!1,null,null,null);s["default"]=C.exports;w()(C,"components",{QPage:b["a"],QBreadcrumbs:v["a"],QBreadcrumbsEl:h["a"],QSelect:g["a"],QIcon:q["a"],QInput:k["a"],QBtn:$["a"]})}}]);
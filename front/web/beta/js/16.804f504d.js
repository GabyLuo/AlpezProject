(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[16],{a850:function(s,e,t){"use strict";t("cf3a")},b54b:function(s,e,t){"use strict";t.r(e);t("b0c0");var a=function(){var s=this,e=s._self._c;return e("q-page",{staticClass:"bg-grey-3"},[e("div",{staticClass:"q-pa-sm panel-header"},[e("div",{staticClass:"row"},[e("div",{staticClass:"col-sm-6"},[e("div",{staticClass:"q-pa-md q-gutter-sm"},[e("q-breadcrumbs",{attrs:{align:"left"}},[e("q-breadcrumbs-el",{staticClass:"fs20",attrs:{label:"",icon:"home",to:"/"}}),e("q-breadcrumbs-el",{staticClass:"fs20",attrs:{label:"Roles",to:"/roles"}}),e("q-breadcrumbs-el",{staticClass:"fs20",attrs:{label:"Editar"}})],1)],1)])])]),e("div",{staticClass:"q-pa-md bg-grey-3"},[e("div",{staticClass:"row bg-white border-panel"},[e("div",{staticClass:"col q-pa-md"},[e("div",{staticClass:"row q-col-gutter-xs"},[e("div",{staticClass:"col-xs-12 col-sm-4"},[e("q-input",{staticClass:"uppcase",attrs:{color:"white","bg-color":"secondary",filled:"",dark:"",error:s.$v.rol.fields.name.$error,label:"ROL"},scopedSlots:s._u([{key:"prepend",fn:function(){return[e("q-icon",{attrs:{name:"fas fa-user"}})]},proxy:!0}]),model:{value:s.rol.fields.name,callback:function(e){s.$set(s.rol.fields,"name",e)},expression:"rol.fields.name"}})],1)]),e("div",{staticClass:"row q-mb-sm"},[e("div",{staticClass:"col-xs-12 col-sm-2 offset-sm-10 pull-right"},[e("q-btn",{attrs:{color:"positive",icon:"save",label:"Guardar"},on:{click:function(e){return s.editRol()}}})],1)])])])])])},r=[],o=t("ded3"),i=t.n(o),l=t("7ec2"),n=t.n(l),c=t("c973"),d=t.n(c),u=(t("14d9"),t("aabb")),f=t("b5ae"),m=f.required,p=f.maxLength,h={name:"EditUser",validations:{rol:{fields:{name:{required:m,maxLength:p(50)}}}},data:function(){return{rol:{fields:{id:null,name:null}}}},computed:{},created:function(){this.fetchFromServer()},methods:{loadAll:function(){return d()(n()().mark((function s(){return n()().wrap((function(s){while(1)switch(s.prev=s.next){case 0:case"end":return s.stop()}}),s)})))()},fetchFromServer:function(){var s=this;this.$q.loading.show();var e=this.$route.params.id;u["a"].get("/roles/".concat(e)).then((function(e){var t=e.data;t.roles?(s.rol.fields.id=t.roles.id,s.rol.fields.name=t.roles.name,s.$q.loading.hide()):s.$router.push("/roles")}))},editRol:function(){var s=this;if(this.$v.rol.fields.$reset(),this.$v.rol.fields.$touch(),this.$v.rol.fields.$error)return this.$q.notify({message:"Por favor revise los campos.",color:"red",position:"top"}),!1;this.showLoading();var e=i()({},this.rol.fields);u["a"].put("/roles/".concat(e.id),e).then((function(e){var t=e.data;t.result&&t.result&&s.$router.push("/roles"),s.$q.notify({message:t.message.content,color:t.result?"positive":"red",position:"top"}),s.beforeDestroy()}))},showLoading:function(){this.$q.loading.show({spinnerColor:"primary",spinnerSize:140,backgroundColor:"white",message:"Cargando..",messageColor:"black"})},beforeDestroy:function(){this.$q.loading.hide()}}},b=h,v=(t("a850"),t("2877")),g=t("9989"),q=t("ead5"),w=t("079e"),C=t("27f9"),$=t("0016"),y=t("9c40"),k=t("eebe"),x=t.n(k),Q=Object(v["a"])(b,a,r,!1,null,null,null);e["default"]=Q.exports;x()(Q,"components",{QPage:g["a"],QBreadcrumbs:q["a"],QBreadcrumbsEl:w["a"],QInput:C["a"],QIcon:$["a"],QBtn:y["a"]})},cf3a:function(s,e,t){}}]);
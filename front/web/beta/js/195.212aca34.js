(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[195],{b816:function(t,e,a){"use strict";a.r(e);a("14d9"),a("4de4"),a("d3b7");var s=function(){var t=this,e=t._self._c;return e("q-page",{staticClass:"bg-grey-3"},[e("div",{staticClass:"q-pa-sm panel-header"},[e("div",{staticClass:"row"},[e("div",{staticClass:"col-sm-8"},[e("div",{staticClass:"q-pa-md q-gutter-sm"},[e("q-breadcrumbs",{staticStyle:{"font-size":"20px"},attrs:{align:"left"}},[e("q-breadcrumbs-el",{attrs:{label:"",icon:"home",to:"/"}}),e("q-breadcrumbs-el",{attrs:{label:"Usuarios"}})],1)],1)]),e("div",{staticClass:"col-xs-6 col-md-4 pull-right"},[e("div",{staticClass:"q-pa-sm q-gutter-sm"},[e("q-btn",{staticClass:"bg-primary",staticStyle:{color:"white"},attrs:{icon:"add",label:"Nuevo"},nativeOn:{click:function(e){return t.$router.push("/users/new")}}})],1)])])]),e("div",{staticClass:"q-pa-md bg-grey-3"},[e("div",{staticClass:"row bg-white border-panel"},[e("div",{staticClass:"col q-pa-md"},[e("q-table",{attrs:{flat:"",bordered:"",data:t.data,columns:t.columns,"row-key":"email",pagination:t.pagination,filter:t.filter},on:{"update:pagination":function(e){t.pagination=e}},scopedSlots:t._u([{key:"top",fn:function(){return[e("div",{staticStyle:{width:"100%"}},[e("q-input",{attrs:{dense:"",debounce:"300",placeholder:"Buscar"},on:{input:function(e){t.filter=e.toUpperCase()}},scopedSlots:t._u([{key:"append",fn:function(){return[e("q-icon",{attrs:{name:"search"}})]},proxy:!0}]),model:{value:t.filter,callback:function(e){t.filter=e},expression:"filter"}})],1)]},proxy:!0},{key:"body",fn:function(a){return[e("q-tr",{attrs:{props:a}},[e("q-td",{key:"email",staticStyle:{"text-align":"left",width:"20%"},attrs:{props:a}},[t._v(t._s(a.row.email))]),e("q-td",{key:"nickname",staticStyle:{"text-align":"left",width:"30%"},attrs:{props:a}},[t._v(t._s(a.row.nickname))]),e("q-td",{key:"roles",staticStyle:{"text-align":"left",width:"40%"},attrs:{props:a}},[t._v(t._s(a.row.roles))]),e("q-td",{key:"sucursal",staticStyle:{"text-align":"left",width:"30%"},attrs:{props:a}},[t._v(t._s(a.row.sucursal))]),e("q-td",{key:"actions",staticStyle:{width:"10%"},attrs:{props:a}},[e("q-btn",{attrs:{color:"primary",icon:"fas fa-edit",flat:"",size:"10px"},nativeOn:{click:function(e){return t.editSelectedRow(a.row.id)}}},[e("q-tooltip",{attrs:{"content-class":"bg-primary"}},[t._v("Editar")])],1)],1)],1)]}}])})],1)])])])},r=[],i=(a("caad"),a("2532"),a("aabb")),n={name:"IndexUsers",data:function(){return{pagination:{sortBy:"code",descending:!1,rowsPerPage:25},columns:[{name:"email",align:"center",label:"EMAIL",field:"email",style:"width: 20%",sortable:!0},{name:"nickname",align:"center",label:"NOMBRE",field:"nickname",style:"width: 30%",sortable:!0},{name:"roles",align:"center",label:"ROLES",field:"roles",style:"width: 20%",sortable:!0},{name:"sucursal",align:"center",label:"ESTACIÓN",field:"sucursal",style:"width: 20%",sortable:!0},{name:"actions",align:"center",label:"ACCIONES",field:"actions",style:"width: 10%",sortable:!1}],data:[],filter:""}},beforeRouteEnter:function(t,e,a){a((function(t){var e=t.$store.getters["users/rol"];console.log(e),1===e||3===e||7===e?a():a("/")}))},mounted:function(){(this.$store.getters["users/roles"].includes(1)||this.$store.getters["users/roles"].includes(3)||this.$store.getters["users/roles"].includes(7))&&this.fetchFromServer()},methods:{fetchFromServer:function(){var t=this;this.$q.loading.show(),i["a"].get("/users").then((function(e){var a=e.data;t.data=a.users,t.$q.loading.hide()}))},editSelectedRow:function(t){this.$router.push("/users/".concat(t))}}},l=n,o=a("2877"),c=a("9989"),d=a("ead5"),u=a("079e"),p=a("9c40"),f=a("eaac"),b=a("27f9"),m=a("0016"),g=a("bd08"),h=a("db86"),w=a("05c0"),y=a("eebe"),v=a.n(y),q=Object(o["a"])(l,s,r,!1,null,null,null);e["default"]=q.exports;v()(q,"components",{QPage:c["a"],QBreadcrumbs:d["a"],QBreadcrumbsEl:u["a"],QBtn:p["a"],QTable:f["a"],QInput:b["a"],QIcon:m["a"],QTr:g["a"],QTd:h["a"],QTooltip:w["a"]})}}]);
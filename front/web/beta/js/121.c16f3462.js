(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[121],{"28f3":function(t,e,a){"use strict";a.r(e);a("4de4"),a("d3b7"),a("b680"),a("a9e3");var s=function(){var t=this,e=t._self._c;return e("q-page",[e("div",{staticClass:"q-pa-md panel-header"},[e("div",{staticClass:"row"},[e("div",{staticClass:"col-sm-8"},[e("div",{staticClass:"q-pa-md q-gutter-sm"},[e("q-breadcrumbs",{staticStyle:{"font-size":"20px"},attrs:{align:"left"}},[e("q-breadcrumbs-el",{attrs:{label:"",icon:"home",to:"/"}}),e("q-breadcrumbs-el",{attrs:{label:"Incentivos producción."}})],1)],1)]),e("div",{staticClass:"col-sm-4 pull-right",staticStyle:{"margin-top":"10px"}},[e("q-btn",{staticStyle:{"margin-right":"10px"},attrs:{color:"primary",icon:"fas fa-file-pdf"},on:{click:function(e){return t.getPdf()}}}),e("q-btn",{staticStyle:{"margin-right":"10px"},attrs:{color:"positive",icon:"fas fa-file-excel"},on:{click:function(e){return t.getCsv()}}})],1)])]),e("div",{staticClass:"q-pa-md bg-grey-3"},[e("div",{staticClass:"row bg-white"},[e("div",{staticClass:"col-md-3 q-pa-md"},[e("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"","use-input":"","hide-selected":"","fill-input":"","input-debounce":"0",label:"Empleado",options:t.selectEmployees,"emit-value":"","map-options":""},model:{value:t.employee,callback:function(e){t.employee=e},expression:"employee"}})],1),e("div",{staticClass:"col-md-3 q-pa-md"},[e("q-select",{attrs:{color:"white","bg-color":"secondary",filled:"",dark:"",mask:"date",label:"Fecha inicio"},scopedSlots:t._u([{key:"prepend",fn:function(){return[e("q-icon",{attrs:{name:"event"}})]},proxy:!0}]),model:{value:t.date_start,callback:function(e){t.date_start=e},expression:"date_start"}},[e("q-popup-proxy",{ref:"date_ref",attrs:{"transition-show":"scale","transition-hide":"scale"}},[e("div",{staticClass:"col-sm-12"},[e("q-date",{attrs:{color:"secondary","text-color":"white",mask:"DD/MM/YYYY","today-btn":""},on:{input:function(){return t.$refs.date_ref.hide()}},model:{value:t.date_start,callback:function(e){t.date_start=e},expression:"date_start"}})],1)])],1)],1),e("div",{staticClass:"col-md-3 q-pa-md"},[e("q-select",{attrs:{color:"white","bg-color":"secondary",filled:"",dark:"",mask:"date",label:"Fecha fin"},scopedSlots:t._u([{key:"prepend",fn:function(){return[e("q-icon",{attrs:{name:"event"}})]},proxy:!0}]),model:{value:t.date_end,callback:function(e){t.date_end=e},expression:"date_end"}},[e("q-popup-proxy",{ref:"date_ref",attrs:{"transition-show":"scale","transition-hide":"scale"}},[e("div",{staticClass:"col-sm-12"},[e("q-date",{attrs:{color:"secondary","text-color":"white",mask:"DD/MM/YYYY","today-btn":""},on:{input:function(){return t.$refs.date_ref.hide()}},model:{value:t.date_end,callback:function(e){t.date_end=e},expression:"date_end"}})],1)])],1)],1),e("div",{staticClass:"col-md-3 q-pa-md pull-right"},[e("q-btn",{staticClass:"full-height",staticStyle:{"margin-left":"6px"},attrs:{icon:"fas fa-eraser",color:"secondary"},on:{click:function(e){return t.cleanFilters()}}},[e("q-tooltip",{attrs:{"content-class":"bg-primary"}},[t._v("Limpiar filtros")])],1),e("q-btn",{staticClass:"full-height",staticStyle:{"margin-left":"3px"},attrs:{icon:"fas fa-search",color:"secondary"},on:{click:function(e){return t.generatFilter()}}},[e("q-tooltip",{attrs:{"content-class":"bg-primary"}},[t._v("Buscar")])],1)],1)]),e("div",{staticClass:"row bg-white"},[e("div",{staticClass:"col q-pa-md"},[e("q-table",{attrs:{flat:"",bordered:"",data:t.data,columns:t.columns,"row-key":"code",pagination:t.pagination,filter:t.filter},on:{"update:pagination":function(e){t.pagination=e}},scopedSlots:t._u([{key:"top",fn:function(){return[e("div",{staticStyle:{width:"100%"}},[e("q-input",{attrs:{dense:"",debounce:"300",placeholder:"Buscar"},on:{input:function(e){t.filter=e.toUpperCase()}},scopedSlots:t._u([{key:"append",fn:function(){return[e("q-icon",{attrs:{name:"search"}})]},proxy:!0}]),model:{value:t.filter,callback:function(e){t.filter=e},expression:"filter"}})],1)]},proxy:!0},{key:"body",fn:function(a){return[e("q-tr",{attrs:{props:a}},[e("q-td",{key:"position",staticClass:"cursor-pointer",staticStyle:{"text-align":"left"},attrs:{flat:"",props:a}},[t._v(t._s(a.row.position))]),e("q-td",{key:"employee",staticClass:"cursor-pointer",staticStyle:{"text-align":"left"},attrs:{flat:"",props:a}},[t._v(t._s(a.row.employee))]),e("q-td",{key:"product",staticStyle:{"text-align":"left"},attrs:{props:a}},[t._v(t._s(a.row.product))]),e("q-td",{key:"equipment",staticStyle:{"text-align":"left"},attrs:{props:a}},[t._v(t._s())]),e("q-td",{key:"date",staticStyle:{"text-align":"left"},attrs:{props:a}},[t._v(t._s(a.row.date))]),e("q-td",{key:"parts",staticStyle:{"text-align":"left"},attrs:{props:a}},[t._v(t._s(Number(a.row.qty).toFixed(2)))]),e("q-td",{key:"parts_time",staticStyle:{"text-align":"left"},attrs:{props:a}},[t._v(t._s(Number(a.row.parts_time).toFixed(2)))]),e("q-td",{key:"min_job",staticStyle:{"text-align":"left"},attrs:{props:a}},[t._v(t._s(Number(a.row.min_job).toFixed(2)))]),e("q-td",{key:"factor",staticStyle:{"text-align":"left"},attrs:{props:a}},[t._v(t._s(a.row.factor))]),e("q-td",{key:"incentive",staticStyle:{"text-align":"left"},attrs:{props:a}},[t._v(t._s(Number(a.row.qty)*Number(a.row.factor)||""))]),e("q-td",{key:"efficiency",staticStyle:{"text-align":"left"},attrs:{props:a}},[t._v(t._s(Number(a.row.efficiency).toFixed(2)+"%"))])],1)]}}])})],1)])])])},r=[],n=a("7ec2"),i=a.n(n),o=a("c973"),l=a.n(o),c=(a("caad"),a("2532"),a("99af"),a("aabb")),d={name:"IndexStorageExits",data:function(){return{pagination:{sortBy:"code",descending:!0,page:1,rowsNumber:0,rowsPerPage:25},filter:"",columns:[{name:"position",align:"center",label:"PUESTO",field:"position",sortable:!0},{name:"employee",align:"center",label:"EMPLEADO",field:"employee",sortable:!0},{name:"product",align:"center",label:"PRODUCTO",field:"product",sortable:!0},{name:"equipment",align:"center",label:"EQUIPO",field:"equipment",sortable:!0},{name:"date",align:"center",label:"FECHA",field:"date",sortable:!0},{name:"parts",align:"center",label:"PIEZAS",field:"parts",sortable:!0},{name:"parts_time",align:"center",label:"PZS X MIN",field:"parts_time",sortable:!0},{name:"min_job",align:"center",label:"MIN.TRAB",field:"min_job",sortable:!0},{name:"factor",align:"center",label:"FACTOR",field:"factor",sortable:!0},{name:"incentive",align:"center",label:"INCENTIVO",field:"incentive",sortable:!0},{name:"efficiency",align:"center",label:"EFICIENCIA",field:"efficiency",sortable:!0}],data:[],employee:null,date_start:null,date_end:null,selectEmployees:[]}},computed:{roleId:function(){var t=this.$store.getters["users/rol"];return parseInt(t)},haspermissionv1:function(){var t=!1;return(this.$store.getters["users/roles"].includes(1)||this.$store.getters["users/roles"].includes(3)||this.$store.getters["users/roles"].includes(7)||this.$store.getters["users/roles"].includes(4))&&(t=!0),t}},beforeRouteEnter:function(t,e,a){a((function(t){var e=t.$store.getters["users/rol"];console.log(e),1===e||2===e||12===e||17===e?a():a("/")}))},mounted:function(){this.$q.loading.show(),this.fetchFromServer(),this.getEmployees(),this.$q.loading.hide()},methods:{fetchFromServer:function(){var t=this;this.$q.loading.show(),c["a"].get("/incentives-production").then((function(e){var a=e.data;t.data=a.employees,t.$q.loading.hide()}))},getEmployees:function(){var t=this;c["a"].get("/employees/options").then((function(e){var a=e.data;t.selectEmployees=a.options}))},cleanFilters:function(){var t=this;return l()(i()().mark((function e(){return i()().wrap((function(e){while(1)switch(e.prev=e.next){case 0:return t.$q.loading.show(),t.employee=null,t.date_start=null,t.date_end=null,e.next=6,t.fetchFromServer();case 6:t.$q.loading.hide();case 7:case"end":return e.stop()}}),e)})))()},generatFilter:function(){var t=this;this.$q.loading.show();var e=this.date_start,a=this.date_end;this.date_start&&(e=this.date_start.substr(6,4)+"-"+this.date_start.substr(3,2)+"-"+this.date_start.substr(0,2)),this.date_end&&(a=this.date_end.substr(6,4)+"-"+this.date_end.substr(3,2)+"-"+this.date_end.substr(0,2)),c["a"].get("/incentives-production/".concat(this.employee,"/").concat(e,"/").concat(a)).then((function(e){var a=e.data;t.data=a.employees,t.$q.loading.hide()}))},getCsv:function(){var t=this.date_start,e=this.date_end;this.date_start&&(t=this.date_start.substr(6,4)+"-"+this.date_start.substr(3,2)+"-"+this.date_start.substr(0,2)),this.date_end&&(e=this.date_end.substr(6,4)+"-"+this.date_end.substr(3,2)+"-"+this.date_end.substr(0,2));var a="http://api.alpez.beta.wasp.mx/"+"incentives-production/csv/".concat(this.employee,"/").concat(t,"/").concat(e);window.open(a,"_blank")},getPdf:function(){var t=this.date_start,e=this.date_end;this.date_start&&(t=this.date_start.substr(6,4)+"-"+this.date_start.substr(3,2)+"-"+this.date_start.substr(0,2)),this.date_end&&(e=this.date_end.substr(6,4)+"-"+this.date_end.substr(3,2)+"-"+this.date_end.substr(0,2));var a="http://api.alpez.beta.wasp.mx/"+"incentives-production/pdf/".concat(this.employee,"/").concat(t,"/").concat(e);window.open(a,"_blank")}}},p=d,u=a("2877"),f=a("9989"),m=a("ead5"),b=a("079e"),h=a("9c40"),_=a("ddd8"),y=a("0016"),g=a("7cbe"),v=a("52ee"),q=a("05c0"),w=a("eaac"),x=a("27f9"),k=a("bd08"),S=a("db86"),C=a("eebe"),E=a.n(C),F=Object(u["a"])(p,s,r,!1,null,null,null);e["default"]=F.exports;E()(F,"components",{QPage:f["a"],QBreadcrumbs:m["a"],QBreadcrumbsEl:b["a"],QBtn:h["a"],QSelect:_["a"],QIcon:y["a"],QPopupProxy:g["a"],QDate:v["a"],QTooltip:q["a"],QTable:w["a"],QInput:x["a"],QTr:k["a"],QTd:S["a"]})}}]);
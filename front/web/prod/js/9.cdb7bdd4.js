(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[9],{4566:function(t,a,e){"use strict";e.r(a);var n=function(){var t=this,a=t.$createElement,e=t._self._c||a;return e("q-page",{staticClass:"bg-grey-3"},[e("div",{staticClass:"q-pa-sm panel-header"},[e("div",{staticClass:"row"},[e("div",{staticClass:"col-sm-8"},[e("div",{staticClass:"q-pa-md q-gutter-sm"},[e("q-breadcrumbs",{staticStyle:{"font-size":"20px"},attrs:{align:"left"}},[e("q-breadcrumbs-el",{attrs:{label:"",icon:"home",to:"/"}}),e("q-breadcrumbs-el",{attrs:{label:"Forecast cuentas por pagar"}})],1)],1)]),e("div",{staticClass:"col-sm-4"},[e("div",{staticClass:"q-pa-md q-gutter-sm"},[e("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",options:t.optionsBranches,"fill-input":"","input-debounce":"0",label:"Estación","emit-value":"","map-options":""},on:{input:function(a){return t.fetchFromServer()}},model:{value:t.branche,callback:function(a){t.branche=a},expression:"branche"}})],1)])])]),e("ForecastComponent",{attrs:{branche:t.branche}})],1)},o=[],i=function(){var t=this,a=t.$createElement,e=t._self._c||a;return e("div",{staticClass:"q-pa-md bg-grey-3"},[e("div",{staticClass:"row"},[e("div",{staticClass:"col-xs-6 col-sm-6 col-md-3 col-lg-2",attrs:{align:"left"}},[e("q-btn",{attrs:{color:"primary",label:t.bmonth,icon:"navigate_before"},on:{click:t.calendarPrev}})],1),e("div",{staticClass:"col-md-6 col-lg-8",attrs:{align:"center"}},[e("q-btn",{attrs:{color:"primary",label:t.amonth}})],1),e("div",{staticClass:"col-xs-6 col-sm-6 col-md-3 col-lg-2",attrs:{align:"right"}},[e("q-btn",{attrs:{color:"primary",label:t.nmonth,"icon-right":"navigate_next"},on:{click:t.calendarNext}})],1)]),e("div",{staticClass:"row",staticStyle:{"margin-top":"10px"}},[e("q-calendar",{directives:[{name:"touch-swipe",rawName:"v-touch-swipe.mouse.left.right",value:t.handleSwipe,expression:"handleSwipe",modifiers:{mouse:!0,left:!0,right:!0}}],ref:"calendar",staticClass:"col-xs-12 col-sm-12 col-md-12 col-lg-12",staticStyle:{overflow:"hidden"},attrs:{view:"month",locale:"es","enable-outside-days":"","day-height":110,animated:"","transition-prev":"slide-right","transition-next":"slide-left"},scopedSlots:t._u([{key:"day",fn:function(a){var n=a.timestamp;return[t._l(t.getEvents(n.date),(function(a,n){return[e("q-badge",{key:n,staticClass:"badge",class:a.bgcolor,staticStyle:{"text-align":"right"},on:{click:function(e){return t.showModal(a.date,a.details)}}},[e("label",{staticClass:"ellipsis",staticStyle:{cursor:"pointer"}},[t._v("$ "+t._s(t.formatPrice(a.title)))]),e("q-tooltip",{attrs:{"transition-show":"scale","transition-hide":"scale","content-class":a.bgcolor,"content-style":"font-size: 14px"}},[t._v("PLAZO")])],1)]}))]}}]),model:{value:t.date,callback:function(a){t.date=a},expression:"date"}})],1),e("q-dialog",{model:{value:t.modal,callback:function(a){t.modal=a},expression:"modal"}},[e("q-card",{staticStyle:{"min-width":"70%","max-height":"90vh"}},[e("div",[e("div",{staticClass:"bg-primary"},[e("div",{staticClass:"row"},[e("div",{staticClass:"col-sm-11 text-h6",staticStyle:{color:"white","padding-top":"5px","padding-bottom":"5px"}},[t._v("   DETALLES DEUDAS POR PAGAR")]),e("div",{staticClass:"col-sm-1 pull-right",staticStyle:{color:"white","padding-top":"5px","padding-bottom":"5px"}},[e("q-btn",{attrs:{color:"white",flat:"",round:"",dense:"",icon:"close"},nativeOn:{click:function(a){return t.closeModal()}}})],1)])])]),e("div",{staticClass:"row q-pa-md q-col-gutter-md"},[e("div",{staticClass:"col-xs-12 col-sm-6 col-md-4 col-lg-3"},[e("q-input",{attrs:{color:"white","bg-color":"primary",filled:"",dark:"",disable:"",label:"FECHA"},scopedSlots:t._u([{key:"prepend",fn:function(){return[e("q-icon",{attrs:{name:"calendar_today"}})]},proxy:!0}]),model:{value:t.dateModal,callback:function(a){t.dateModal=a},expression:"dateModal"}})],1),e("div",{staticClass:"col-xs-12 col-sm-6 col-md-4 col-lg-3"},[e("q-input",{attrs:{color:"white","bg-color":"primary",filled:"",dark:"",disable:"",label:"PLAZO"},scopedSlots:t._u([{key:"prepend",fn:function(){return[e("q-icon",{attrs:{name:"date_range"}})]},proxy:!0}]),model:{value:t.term,callback:function(a){t.term=a},expression:"term"}})],1),e("div",{staticClass:"col-xs-12 col-sm-6 col-md-4 col-lg-4"},[e("q-input",{attrs:{color:"white","bg-color":"primary",filled:"",disable:"",dark:"",label:"ACUMULADO"},scopedSlots:t._u([{key:"prepend",fn:function(){return[e("q-icon",{attrs:{name:"attach_money"}})]},proxy:!0}]),model:{value:t.total,callback:function(a){t.total=a},expression:"total"}})],1)]),e("q-tabs",{staticClass:"text-grey",attrs:{"inline-label":"",dense:"","active-color":"primary","indicator-color":"primary",align:"justify","narrow-indicator":""},on:{input:t.changeModel},model:{value:t.currentTab,callback:function(a){t.currentTab=a},expression:"currentTab"}},[e("q-tab",{attrs:{name:"client",icon:"person",label:"POR PROVEEDOR"}}),e("q-tab",{attrs:{name:"rem",icon:"outbox",label:"POR ORDEN"}})],1),e("q-tab-panels",{attrs:{animated:""},model:{value:t.currentTab,callback:function(a){t.currentTab=a},expression:"currentTab"}},[e("q-tab-panel",{attrs:{name:"client"}},[e("div",{staticClass:"row q-pa-md"},[e("div",{staticClass:"col-xs-12 col-sm-12 col-md-12 col-lg-12"},[e("q-table",{staticStyle:{"max-height":"80vh"},attrs:{color:"primary",data:t.info,columns:t.infoColumnsClient,"row-key":"id",pagination:t.pagination},on:{"update:pagination":function(a){t.pagination=a}},scopedSlots:t._u([{key:"body",fn:function(a){return[e("q-tr",{attrs:{props:a}},[e("q-td",{key:"customer_name",staticClass:"pull-left",staticStyle:{"text-left":"center",width:"15%"},attrs:{props:a}},[e("label",[t._v(" "+t._s(a.row.name_sup))])]),e("q-td",{key:"contador",staticStyle:{"text-align":"center",width:"15%"},attrs:{props:a}},[e("label",[t._v(" "+t._s(a.row.qtyorders))])]),e("q-td",{key:"total",staticStyle:{"text-align":"right",width:"15%"},attrs:{props:a}},[e("label",[t._v("$ "+t._s(t.formatPrice(a.row.total)))])]),e("q-td",{key:"paid",staticStyle:{"text-align":"right",width:"15%"},attrs:{props:a}},[e("label",[t._v("$ "+t._s(t.formatPrice(a.row.abonado)))])]),e("q-td",{key:"remaining",staticStyle:{"text-align":"right",width:"15%"},attrs:{props:a}},[e("q-badge",{staticClass:"bg-red-14",attrs:{transparent:""}},[e("label",{staticStyle:{"font-size":"13px","font-weight":"bold"}},[t._v("$ "+t._s(t.formatPrice(a.row.remaining)))])])],1)],1)]}}])})],1)])]),e("q-tab-panel",{attrs:{name:"rem"}},[e("div",{staticClass:"row q-pa-md"},[e("div",{staticClass:"col-xs-12 col-sm-12 col-md-12 col-lg-12"},[e("q-table",{staticStyle:{"max-height":"60vh",width:"100%"},attrs:{color:"primary",data:t.info,columns:t.infoColumnsRem,"row-key":"id",pagination:t.pagination},on:{"update:pagination":function(a){t.pagination=a}},scopedSlots:t._u([{key:"body",fn:function(a){return[e("q-tr",{attrs:{props:a}},[e("q-td",{key:"rem",staticStyle:{"text-align":"center",width:"10%"},attrs:{props:a}},[e("label",[t._v(" "+t._s(a.row.serial))])]),e("q-td",{key:"customer_name",staticClass:"pull-left",staticStyle:{"text-align":"left",width:"25%"},attrs:{props:a}},[e("label",[t._v(" "+t._s(a.row.name_suppliers))])]),e("q-td",{key:"total",staticStyle:{"text-align":"right",width:"15%"},attrs:{props:a}},[e("label",[t._v("$ "+t._s(t.formatPrice(a.row.totalamount)))])]),e("q-td",{key:"paid",staticStyle:{"text-align":"right",width:"15%"},attrs:{props:a}},[e("label",[t._v("$ "+t._s(t.formatPrice(a.row.abonado)))])]),e("q-td",{key:"remaining",staticStyle:{"text-align":"right",width:"15%"},attrs:{props:a}},[e("q-badge",{staticClass:"bg-red-14",attrs:{transparent:""}},[e("label",{staticStyle:{"font-size":"13px","font-weight":"bold"}},[t._v("$ "+t._s(t.formatPrice(a.row.restante)))])])],1)],1)]}}])})],1)])])],1),e("q-card-actions",{attrs:{align:"right"}},[e("br")])],1)],1)],1)},s=[],r=e("4082"),l=e.n(r),c=e("ded3"),d=e.n(c),m=(e("ac1f"),e("5319"),e("b680"),e("d3b7"),e("25f0"),e("2f62")),h=e("fe7a"),b=e("aabb"),p=e("bd4c"),u=["evt"],f=Date.now(),g=p["b"].formatDate(f,"DD/MM/YYYY"),v=p["b"].formatDate(f,"MMMM"),y=p["b"].addToDate(f,{month:1}),w=p["b"].formatDate(y,"MMMM"),q=p["b"].subtractFromDate(f,{month:1}),x=p["b"].formatDate(q,"MMMM"),C={components:{QCalendar:h["a"]},name:"Forecast",props:{branche:null},created:function(){},data:function(){return{branchId:null,date:"",amonth:"",bmonth:"",nmonth:"",month:f,term:null,dateModal:g,total:null,events:[],modal:!1,data:[],info:[],infoColumnsClient:[{name:"customer_name",align:"center",label:"PROVEEDOR",field:"customer_name",sortable:!0},{name:"contador",align:"center",label:"CANT. ORDENES",field:"contador",sortable:!0},{name:"total",align:"center",label:"TOTAL",field:"total",sortable:!0},{name:"paid",align:"center",label:"ABONADO",field:"paid",sortable:!0},{name:"remaining",align:"center",label:"RESTANTE",field:"remaining",sortable:!0}],infoColumnsRem:[{name:"rem",align:"center",label:"ORDEN",field:"rem",sortable:!0},{name:"customer_name",align:"center",label:"PROVEEDOR",field:"customer_name",sortable:!0},{name:"total",align:"center",label:"TOTAL",field:"total",sortable:!0},{name:"paid",align:"center",label:"ABONADO",field:"paid",sortable:!0},{name:"remaining",align:"center",label:"RESTANTE",field:"remaining",sortable:!0}],infoPagination:{sortBy:"customer_name",descending:!1,rowsPerPage:25},pagination:{sortBy:"id",descending:!1,rowsPerPage:25},currentTab:"client"}},computed:d()({},Object(m["c"])({})),validations:{},watch:{branche:function(t){this.loadAll(),console.log(t)}},mounted:function(){this.loadAll()},methods:d()(d()({formatPrice:function(t){var a=(t/1).toFixed(2).replace(",",".");return a.toString().replace(/\B(?=(\d{3})+(?!\d))/g,",")}},Object(m["b"])({getDataCalendar:"debts/forecast/dataC"})),{},{loadAll:function(){var t=this;this.$q.loading.show(),this.getDataCalendar({branch_id:this.branche}).then((function(a){var e=a.data;t.events=e.data,t.$q.loading.hide()})),this.amonth=v,this.bmonth=x,this.nmonth=w},getEvents:function(t){for(var a=[],e=0;e<this.events.length;++e){var n=!1;this.events[e].date===t&&(n||(this.events[e].side=void 0,a.push(this.events[e])))}return a},showModal:function(t,a){this.modal=!0,this.getDataClient(t,a)},getDataClient:function(t,a){var e=this;this.$q.loading.show(),this.term=a,this.date=t;var n=[];n.date=t,n.type=a,n.branch_id=this.branche,b["a"].post("/forecast-debts/getDebtsToPay",n).then((function(t){var a=t.data;e.info=a.data,e.total=a.sm,e.$q.loading.hide()}))},getDataRem:function(t,a){var e=this;this.$q.loading.show(),this.term=a,this.date=t;var n=[];n.date=t,n.type=a,n.branch_id=this.branche,b["a"].post("/forecast-debts/getDebtsToPayForSuppliers",n).then((function(t){var a=t.data;e.info=a.data,console.log(a.data),e.total=a.sm,e.$q.loading.hide()}))},closeModal:function(){this.modal=!1,this.currentTab="client"},changeModel:function(){this.info=[],"client"===this.currentTab?this.getDataClient(this.date,this.term):"rem"===this.currentTab&&(console.log(this.date),console.log(this.term),this.getDataRem(this.date,this.term))},calendarNext:function(){this.$refs.calendar.next(),this.month=p["b"].addToDate(this.month,{month:1}),this.amonth=p["b"].formatDate(this.month,"MMMM");var t=p["b"].addToDate(this.month,{month:1});this.nmonth=p["b"].formatDate(t,"MMMM");var a=p["b"].subtractFromDate(this.month,{month:1});this.bmonth=p["b"].formatDate(a,"MMMM")},calendarPrev:function(){this.$refs.calendar.prev(),this.month=p["b"].subtractFromDate(this.month,{month:1}),this.amonth=p["b"].formatDate(this.month,"MMMM");var t=p["b"].subtractFromDate(this.month,{month:1});this.bmonth=p["b"].formatDate(t,"MMMM");var a=p["b"].addToDate(this.month,{month:1});this.nmonth=p["b"].formatDate(a,"MMMM")},handleSwipe:function(t){var a=t.evt,e=l()(t,u);e.duration>=30&&("right"===e.direction?this.calendarPrev():"left"===e.direction&&this.calendarNext()),!1!==a.cancelable&&a.preventDefault(),a.stopPropagation()}})},_=C,M=(e("c32c"),e("e1f6"),e("2877")),D=e("9c40"),S=e("58a81"),k=e("05c0"),T=e("24e8"),P=e("f09f"),O=e("27f9"),E=e("0016"),A=e("429b"),$=e("7460"),Q=e("adad"),R=e("823b"),B=e("eaac"),F=e("bd08"),N=e("db86"),L=e("4b7e"),I=e("12c5"),j=e("eebe"),z=e.n(j),Y=Object(M["a"])(_,i,s,!1,null,"1a09efa8",null),U=Y.exports;z()(Y,"components",{QBtn:D["a"],QBadge:S["a"],QTooltip:k["a"],QDialog:T["a"],QCard:P["a"],QInput:O["a"],QIcon:E["a"],QTabs:A["a"],QTab:$["a"],QTabPanels:Q["a"],QTabPanel:R["a"],QTable:B["a"],QTr:F["a"],QTd:N["a"],QCardActions:L["a"]}),z()(Y,"directives",{TouchSwipe:I["a"]});var V={components:{ForecastComponent:U},name:"IndexTrainings",data:function(){return{optionsBranches:[],branche:0,pagination:{sortBy:"id",descending:!1,rowsPerPage:25},filter:""}},mounted:function(){this.$q.loading.show(),this.getBranchOptions(),this.fetchFromServer(),this.$q.loading.hide()},computed:{roleId:function(){var t=this.$store.getters["users/rol"];return parseInt(t)}},beforeRouteEnter:function(t,a,e){e((function(t){var a=t.$store.getters["users/rol"];1===a||3===a||7===a||2===a||20===a||4===a||27===a||17===a||22===a||28===a?e():e("/")}))},methods:{getBranchOptions:function(){var t=this;b["a"].get("/branch-offices/getBranchsOffices").then((function(a){var e=a.data;e.result&&(t.optionsBranches=e.branchs,t.optionsBranches.unshift({label:"TODOS",value:0}))}))},fetchFromServer:function(){}}},J=V,Z=e("9989"),G=e("ead5"),H=e("079e"),K=e("ddd8"),W=Object(M["a"])(J,n,o,!1,null,null,null);a["default"]=W.exports;z()(W,"components",{QPage:Z["a"],QBreadcrumbs:G["a"],QBreadcrumbsEl:H["a"],QSelect:K["a"]})},b159:function(t,a,e){},c32c:function(t,a,e){"use strict";e("b159")}}]);
(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[10],{3067:function(t,e,a){"use strict";a("ace6")},ace6:function(t,e,a){},ecc4:function(t,e,a){"use strict";a.r(e);a("4de4"),a("d3b7");var n=function(){var t=this,e=t._self._c;return e("q-page",{staticClass:"bg-grey-3"},[e("div",{staticClass:"q-pa-sm panel-header"},[e("div",{staticClass:"row"},[e("div",{staticClass:"col-sm-6"},[e("div",{staticClass:"q-pa-md q-gutter-sm"},[e("q-breadcrumbs",{staticStyle:{"font-size":"20px"},attrs:{align:"left"}},[e("q-breadcrumbs-el",{attrs:{label:"",icon:"home",to:"/"}}),e("q-breadcrumbs-el",{attrs:{label:"Antigüedad de saldos"}})],1)],1)]),e("div",{staticClass:"col-xs-12 col-md-6 pull-right"},[e("div",{staticClass:"col-xs-12 col-md-4 offset-md-10"},[e("div",[e("q-btn",{staticStyle:{"margin-left":"10px"},attrs:{color:"green",icon:"fas fa-file-excel"},on:{click:function(e){return t.generateCSV()}}},[e("q-tooltip",[t._v("GENERAR CSV")])],1),e("q-btn",{staticStyle:{"margin-left":"10px"},attrs:{color:"red",icon:"fas fa-file-pdf"},on:{click:function(e){return t.generatePDF()}}},[e("q-tooltip",[t._v("GENERAR PDF")])],1)],1)])])])]),e("div",{staticClass:"q-pa-md bg-grey-3"},[e("div",{staticClass:"row bg-white border-panel"},[e("div",{staticClass:"col q-pa-md"},[e("div",{staticClass:"row q-col-gutter-xs"},[e("div",{staticClass:"col-md-4 q-pb-md"},[e("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",options:t.filteredCustomerOptions,"use-input":"","hide-selected":"","fill-input":"","input-debounce":"0",label:"Cliente","emit-value":"","map-options":""},on:{filter:t.filtrarClientes,input:function(e){return t.fetchFromServer()}},model:{value:t.customer,callback:function(e){t.customer=e},expression:"customer"}})],1),e("div",{staticClass:"col-sm-4"},[e("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",options:t.branchesOptions,"use-input":"",label:"Estación","emit-value":"","map-options":""},on:{input:function(e){return t.fetchFromServer()}},model:{value:t.branches,callback:function(e){t.branches=e},expression:"branches"}})],1),e("div",{staticClass:"col-xs-12 col-sm-12"},[e("q-table",{attrs:{flat:"",bordered:"",data:t.dataoldbalance,columns:t.columns,"row-key":"serial",pagination:t.pagination,filter:t.filter},on:{"update:pagination":function(e){t.pagination=e},request:t.qTableRequest},scopedSlots:t._u([{key:"top",fn:function(){return[e("div",{staticStyle:{width:"100%"}},[e("q-input",{attrs:{dense:"",debounce:"300",placeholder:"Buscar"},on:{input:function(e){t.filter=e.toUpperCase()}},scopedSlots:t._u([{key:"append",fn:function(){return[e("q-icon",{attrs:{name:"search"}})]},proxy:!0}]),model:{value:t.filter,callback:function(e){t.filter=e},expression:"filter"}})],1)]},proxy:!0},{key:"body",fn:function(a){return[e("q-tr",{attrs:{props:a}},[!1!==a.row.customer?e("q-td",{key:"customer",staticStyle:{"text-align":"left"},attrs:{props:a}},[t._v(t._s(a.row.customer))]):e("q-td",{key:"customer",staticStyle:{"text-align":"right"},attrs:{props:a}},[t._v("TOTAL: ")]),(a.row.customer,e("q-td",{key:"totalbalance",staticStyle:{"text-align":"right"},attrs:{props:a}},[t._v(t._s(a.row.currentBalance))])),(a.row.customer,e("q-td",{key:"thirty",staticStyle:{"text-align":"right"},attrs:{props:a}},[t._v(t._s(a.row.thirty))])),(a.row.customer,e("q-td",{key:"sixty",staticStyle:{"text-align":"right"},attrs:{props:a}},[t._v(t._s(a.row.sixty))])),(a.row.customer,e("q-td",{key:"ninety",staticStyle:{"text-align":"right"},attrs:{props:a}},[t._v(t._s(a.row.ninety))])),(a.row.customer,e("q-td",{key:"overninety",staticStyle:{"text-align":"right"},attrs:{props:a}},[t._v(t._s(a.row.overninety))])),(a.row.customer,e("q-td",{key:"pastduebalance",staticStyle:{"text-align":"right"},attrs:{props:a}},[t._v(t._s(a.row.pastduebalance))])),(a.row.customer,e("q-td",{key:"total",staticStyle:{"text-align":"right"},attrs:{props:a}},[t._v(t._s(a.row.sumAll))]))],1)]}}])})],1)])])])])])},s=[],r=a("7ec2"),i=a.n(r),l=a("c973"),o=a.n(l),c=(a("3c65"),a("99af"),a("14d9"),a("aabb")),d={name:"CrmIndex",data:function(){return{branchesOptions:[],branches:"TODOS",filteredCustomerOptions:[],customerOptions:[],customer:"TODOS",dataoldbalance:[],columns:[{name:"customer",align:"center",label:"CLIENTE",field:"customer",sortable:!1},{name:"totalbalance",align:"center",label:"CORRIENTE",field:"totalbalance",sortable:!1,headerClasses:"bg-green"},{name:"thirty",align:"center",label:"0 - 30",field:"thirty",sortable:!1,headerClasses:"bg-amber",style:"width: 10%"},{name:"sixty",align:"center",label:"31 - 60",field:"sixty",sortable:!1,headerClasses:"bg-orange",style:"width: 10%"},{name:"ninety",align:"center",label:"61 - 90",field:"ninety",sortable:!1,headerClasses:"bg-red",style:"width: 10%"},{name:"overninety",align:"center",label:"90+",field:"overninety",sortable:!1,headerClasses:"bg-purple",style:"width: 10%"},{name:"pastduebalance",align:"center",label:"VENCIDO",field:"pastduebalance",sortable:!1,headerClasses:"bg-brown"},{name:"total",align:"center",label:"TOTAL",field:"total",sortable:!1,headerClasses:"bg-dark"}],filter:"",pagination:{sortBy:"serial",descending:!1,page:1,rowsNumber:0,rowsPerPage:25}}},mounted:function(){this.fetchFromServer(),this.getClients(),this.getBranchsOffices()},methods:{getBranchsOffices:function(){var t=this;c["a"].get("/branch-offices/getBranchsOffices").then((function(e){var a=e.data;t.branchesOptions=a.branchs,t.branchesOptions.unshift({label:"TODOS",value:"TODOS"})}))},generateCSV:function(){var t=this.customer,e=[2,3],a=this.branches,n=null===this.filter||""===this.filter?"TODOS":this.filter,s="http://api.alpez.beta.wasp.mx/"+"oldbalance/getCSV/".concat(t,"/").concat(e,"/").concat(a,"/").concat(n);window.open(s,"_blank")},generatePDF:function(){var t=this.customer,e=[2,3],a=this.branches,n=null===this.filter||""===this.filter?"TODOS":this.filter,s="http://api.alpez.beta.wasp.mx/"+"oldbalance/getPdf/".concat(t,"/").concat(e,"/").concat(a,"/").concat(n);window.open(s,"_blank")},getClients:function(){var t=this;c["a"].get("/oldbalance/getClients").then((function(e){var a=e.data;t.customerOptions=a.options,t.customerOptions.push({label:"TODOS",value:"TODOS"})}))},filtrarClientes:function(t,e,a){var n=this;e((function(){var e=t.toLowerCase();n.filteredCustomerOptions=n.customerOptions.filter((function(t){return t.label.toLowerCase().indexOf(e)>-1}))}))},fetchFromServer:function(){var t=this;return o()(i()().mark((function e(){return i()().wrap((function(e){while(1)switch(e.prev=e.next){case 0:t.qTableRequest({pagination:t.pagination,filter:t.filter});case 1:case"end":return e.stop()}}),e)})))()},qTableRequest:function(t){var e=this;return o()(i()().mark((function a(){var n;return i()().wrap((function(a){while(1)switch(a.prev=a.next){case 0:return e.$q.loading.show(),e.filter=t.filter,e.pagination=t.pagination,e.data=[],n=[],n.customer=e.customer,n.status=[2,3],n.saleDatev1=null,n.saleDatev2=null,n.type=0,n.pagination=e.pagination,n.filter=e.filter,n.branches=e.branches,a.next=15,c["a"].post("/oldbalance/getOldbalance",n).then((function(t){var a=t.data;console.log(a),e.dataoldbalance=a.oldbalance,e.pagination.rowsNumber=a.oldbalanceCount})).catch((function(t){return t}));case 15:e.$q.loading.hide();case 16:case"end":return a.stop()}}),a)})))()},editSelectedRow:function(t){var e=t;this.$router.push("/oldbalance/".concat(e))},deleteSelectedRow:function(t){var e=this;this.$q.dialog({title:"Confirmación",message:"¿Desea eliminar este oldbalance?",persistent:!0,ok:{label:"Aceptar",color:"green"},cancel:{label:"Cancelar",color:"red"}}).onOk(o()(i()().mark((function a(){return i()().wrap((function(a){while(1)switch(a.prev=a.next){case 0:return e.$q.loading.show(),a.next=3,c["a"].delete("/oldbalance/".concat(t)).then((function(t){var a=t.data;e.$q.notify({message:a.message.content,position:"top",color:a.result?"positive":"warning"}),a.result&&(e.fetchFromServer(),e.$q.loading.hide())}));case 3:case"end":return a.stop()}}),a)})))).onCancel((function(){})),this.$q.loading.hide()}}},u=d,p=(a("3067"),a("2877")),f=a("9989"),b=a("ead5"),h=a("079e"),g=a("9c40"),m=a("05c0"),v=a("ddd8"),w=a("0016"),y=a("eaac"),C=a("27f9"),q=a("bd08"),O=a("db86"),x=a("eebe"),S=a.n(x),k=Object(p["a"])(u,n,s,!1,null,null,null);e["default"]=k.exports;S()(k,"components",{QPage:f["a"],QBreadcrumbs:b["a"],QBreadcrumbsEl:h["a"],QBtn:g["a"],QTooltip:m["a"],QSelect:v["a"],QIcon:w["a"],QTable:y["a"],QInput:C["a"],QTr:q["a"],QTd:O["a"]})}}]);
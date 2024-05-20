(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[52],{"910a":function(t,e,a){"use strict";a.r(e);a("4de4"),a("d3b7"),a("b0c0");var s=function(){var t=this,e=t._self._c;return e("q-page",{staticClass:"bg-grey-3"},[e("div",{staticClass:"q-pa-sm panel-header"},[e("div",{staticClass:"row"},[e("div",{staticClass:"col-sm-6"},[e("div",{staticClass:"q-pa-md q-gutter-sm"},[e("q-breadcrumbs",{staticStyle:{"font-size":"20px"},attrs:{align:"left"}},[e("q-breadcrumbs-el",{attrs:{label:"",icon:"home",to:"/"}}),e("q-breadcrumbs-el",{attrs:{label:"Corte de caja remisión"}})],1)],1)]),e("div",{staticClass:"col-xs-12 col-md-6 q-pr-sm pull-right"},[e("q-btn",{attrs:{color:"positive",icon:"fas fa-file-excel"},on:{click:function(e){return t.generateCsv()}}},[e("q-tooltip",{attrs:{"content-class":"bg-primary"}},[t._v("Descargar CSV")])],1),e("q-btn",{staticStyle:{"margin-left":"10px"},attrs:{color:"red",icon:"fas fa-file-pdf"},on:{click:function(e){return t.closeSales()}}},[e("q-tooltip",[t._v("CORTE DE CAJA")])],1)],1)])]),e("div",{staticClass:"q-pa-md bg-grey-3"},[e("div",{staticClass:"row bg-white border-panel"},[e("div",{staticClass:"col q-pa-md"},[e("div",{staticClass:"row q-col-gutter-xs"},[e("div",{staticClass:"col-md-3"},[e("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",mask:"YYYY-DD-MM",label:"Buscar por fecha"},model:{value:t.saleDatev1,callback:function(e){t.saleDatev1=e},expression:"saleDatev1"}},[e("q-popup-proxy",{ref:"date",attrs:{"transition-show":"scale","transition-hide":"scale"}},[e("div",{staticClass:"col-sm-12"},[e("q-date",{attrs:{mask:"DD/MM/YYYY","today-btn":""},on:{input:function(e){return t.filterGrid()}},model:{value:t.saleDatev1,callback:function(e){t.saleDatev1=e},expression:"saleDatev1"}})],1)])],1)],1),e("div",{staticClass:"col-xs-12 col-sm-12"},[e("q-table",{attrs:{flat:"",bordered:"",data:t.closeSale,columns:t.columns,"row-key":"serial",pagination:t.pagination,filter:t.filter},on:{"update:pagination":function(e){t.pagination=e}},scopedSlots:t._u([{key:"body",fn:function(a){return[e("q-tr",{attrs:{props:a}},[e("q-td",{key:"name",staticStyle:{"text-align":"left"},attrs:{props:a}},[t._v(t._s(a.row.name))]),""!==a.row.name?e("q-td",{key:"id",staticStyle:{"text-align":"center"},attrs:{props:a}},[t._v(t._s(a.row.id))]):e("q-td",{key:"id",staticStyle:{"text-align":"center"},attrs:{props:a}},[t._v(t._s(a.row.total))]),(a.row.name,e("q-td",{key:"subtotal",staticStyle:{"text-align":"right"},attrs:{props:a}},[t._v("$ "+t._s(t.$formatNumberPrice(a.row.subtotal)))])),(a.row.name,e("q-td",{key:"iva",staticStyle:{"text-align":"right"},attrs:{props:a}},[t._v("$ "+t._s(t.$formatNumberPrice(a.row.iva)))])),""!==a.row.name?e("q-td",{key:"total",staticStyle:{"text-align":"right"},attrs:{props:a}},[t._v("$ "+t._s(t.$formatNumberPrice(a.row.total)))]):e("q-td",{key:"total",staticStyle:{"text-align":"right"},attrs:{props:a}},[t._v("$ "+t._s(t.$formatNumberPrice(a.row.neto)))]),""!==a.row.name?e("q-td",{key:"remission",staticStyle:{"text-align":"right"},attrs:{props:a}},[t._v("$ "+t._s(t.$formatNumberPrice(a.row.remission)))]):e("q-td",{key:"remission",staticStyle:{"text-align":"right"},attrs:{props:a}},[t._v("$ "+t._s(t.$formatNumberPrice(a.row.countremission)))]),""!==a.row.name?e("q-td",{key:"credit",staticStyle:{"text-align":"right"},attrs:{props:a}},[t._v("$ "+t._s("CREDITO"===a.row.payment_method?t.$formatNumberPrice(a.row.total):t.$formatNumberPrice(0)))]):e("q-td",{key:"credit",staticStyle:{"text-align":"right"},attrs:{props:a}},[t._v("$ "+t._s(t.$formatNumberPrice(a.row.credit)))]),""!==a.row.name?e("q-td",{key:"counted",staticStyle:{"text-align":"right"},attrs:{props:a}},[t._v("$ "+t._s("CONTADO"===a.row.payment_method?t.$formatNumberPrice(a.row.total):t.$formatNumberPrice(0)))]):e("q-td",{key:"counted",staticStyle:{"text-align":"right"},attrs:{props:a}},[t._v("$ "+t._s(t.$formatNumberPrice(a.row.counted)))])],1)]}}])})],1)])])])])])},o=[],l=a("7ec2"),r=a.n(l),i=a("c973"),n=a.n(i),c=(a("99af"),a("e260"),a("3ca3"),a("ddb0"),a("2b3d"),a("9861"),a("14d9"),a("aabb")),d={name:"CrmIndex",data:function(){return{saleDatev1:null,closeSale:[],columns:[{name:"name",align:"center",label:"NOMBRE DEL CLIENTE",field:"name",sortable:!1},{name:"id",align:"center",label:"NO. REMISIÓN",field:"id",sortable:!1},{name:"subtotal",align:"center",label:"SUBTOTAL",field:"subtotal",sortable:!1},{name:"iva",align:"center",label:"IVA",field:"iva",sortable:!1},{name:"total",align:"center",label:"NETO",field:"total",sortable:!1},{name:"remission",align:"center",label:"TICKET",field:"remission",sortable:!1},{name:"credit",align:"center",label:"CRÉDITO",field:"credit",sortable:!1},{name:"counted",align:"center",label:"CONTADO",field:"counted",sortable:!1}],filter:"",pagination:{page:1,rowsPerPage:25}}},computed:{getDate:function(){var t=new Date,e=t.getDate(),a=t.getMonth()+1,s=t.getFullYear();return e<10&&(e="0"+e),a<10&&(a="0"+a),t=s+"-"+a+"-"+e,t}},created:function(){this.fetchFromServer()},methods:{filterGrid:function(){var t=this;console.log(this.saleDatev1);var e=this.$formatDate(this.saleDatev1);this.$q.loading.show(),c["a"].get("/close-sale/getCloseSalesRemission/".concat(e)).then((function(e){var a=e.data;a.result&&(t.closeSale=a.closeSale,console.log(t.closeSale))})),this.$q.loading.hide()},generateCsv:function(){var t=this,e="si",a="http://api.alpez.beta.wasp.mx/"+"close-sale/getCsvCloseSales/".concat(null===this.saleDatev1?this.getDate:this.$formatDate(this.saleDatev1),"/").concat(e);this.$q.loading.show(),c["a"].fileDownload(a).then((function(e){var a=e.data,s=window.URL.createObjectURL(new Blob([a],{type:"application/csv"})),o=document.createElement("a");o.href=s,o.setAttribute("download","Cierre"+(null===t.saleDatev1?t.getDate:t.$formatDate(t.saleDatev1))+".csv"),document.body.appendChild(o),t.$q.loading.hide(),o.click()}))},closeSales:function(){var t=this,e="si",a="http://api.alpez.beta.wasp.mx/"+"close-sale/closeSales/".concat(null===this.saleDatev1?this.getDate:this.$formatDate(this.saleDatev1),"/").concat(e);this.$q.loading.show(),c["a"].fileDownload(a).then((function(e){var a=e.data,s=window.URL.createObjectURL(new Blob([a],{type:"application/pdf"})),o=document.createElement("a");o.href=s,o.setAttribute("download","Cierre"+(null===t.saleDatev1?t.getDate:t.$formatDate(t.saleDatev1))+".pdf"),document.body.appendChild(o),t.$q.loading.hide(),o.click()}))},fetchFromServer:function(){var t=this;console.log(this.getDate),this.$q.loading.show(),c["a"].get("/close-sale/getCloseSalesRemission/".concat(this.getDate)).then((function(e){var a=e.data;a.result&&(t.closeSale=a.closeSale,console.log(t.closeSale))})),this.$q.loading.hide()},editSelectedRow:function(t){var e=t;this.$router.push("/corte de caja/".concat(e))},deleteSelectedRow:function(t){var e=this;this.$q.dialog({title:"Confirmación",message:"¿Desea eliminar este Corte de caja?",persistent:!0,ok:{label:"Aceptar",color:"green"},cancel:{label:"Cancelar",color:"red"}}).onOk(n()(r()().mark((function a(){return r()().wrap((function(a){while(1)switch(a.prev=a.next){case 0:return e.$q.loading.show(),a.next=3,c["a"].delete("/corte de caja/".concat(t)).then((function(t){var a=t.data;e.$q.notify({message:a.message.content,position:"top",color:a.result?"positive":"warning"}),a.result&&(e.fetchFromServer(),e.$q.loading.hide())}));case 3:case"end":return a.stop()}}),a)})))).onCancel((function(){})),this.$q.loading.hide()}}},m=d,p=a("2877"),u=a("9989"),g=a("ead5"),f=a("079e"),b=a("9c40"),h=a("05c0"),v=a("ddd8"),w=a("7cbe"),q=a("52ee"),D=a("eaac"),y=a("27f9"),$=a("0016"),S=a("bd08"),C=a("db86"),_=a("eebe"),k=a.n(_),x=Object(p["a"])(m,s,o,!1,null,null,null);e["default"]=x.exports;k()(x,"components",{QPage:u["a"],QBreadcrumbs:g["a"],QBreadcrumbsEl:f["a"],QBtn:b["a"],QTooltip:h["a"],QSelect:v["a"],QPopupProxy:w["a"],QDate:q["a"],QTable:D["a"],QInput:y["a"],QIcon:$["a"],QTr:S["a"],QTd:C["a"]})}}]);
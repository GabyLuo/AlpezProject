(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[15],{"1ea1":function(e,t,a){},8652:function(e,t,a){"use strict";a.r(t);a("4de4"),a("d3b7");var i=function(){var e=this,t=e._self._c;return t("q-page",[t("div",{staticClass:"q-pa-md panel-header"},[t("div",{staticClass:"row"},[t("div",{staticClass:"col-sm-8"},[t("div",{staticClass:"q-pa-md q-gutter-sm"},[t("q-breadcrumbs",{staticStyle:{"font-size":"20px"},attrs:{align:"left"}},[t("q-breadcrumbs-el",{attrs:{label:"",icon:"home",to:"/"}}),t("q-breadcrumbs-el",{attrs:{label:"Cobranza"}})],1)],1)]),t("div",{staticClass:"col-sm-4 pull-right"},[t("div",{staticClass:"col-xs-12 col-md-4 offset-md-10 pull-right"},[t("div",[t("q-btn",{attrs:{color:"purple",icon:"mail"},nativeOn:{click:function(t){return e.sendMail()}}},[t("q-tooltip",[e._v("ENVIAR CORREO")])],1),t("q-dialog",{attrs:{persistent:""},model:{value:e.promptEmailPagos,callback:function(t){e.promptEmailPagos=t},expression:"promptEmailPagos"}},[t("q-card",{staticStyle:{"min-width":"350px"}},[t("q-card-section",[t("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",options:e.filteredCustomerOptionsv2,"use-input":"","hide-selected":"","fill-input":"","input-debounce":"0",label:"Cliente","emit-value":"","map-options":""},on:{filter:e.filtrarClientesv2,input:function(t){return e.filterMail()}},model:{value:e.emailReport.fields.customer_select,callback:function(t){e.$set(e.emailReport.fields,"customer_select",t)},expression:"emailReport.fields.customer_select"}})],1),t("q-card-section",{staticClass:"q-pt-none"},[t("q-input",{attrs:{color:"white","bg-color":"primary",filled:"",dark:"",error:e.$v.emailReport.fields.email_cliente.$error,label:"Email",rules:e.emailRule},on:{keyup:function(t){if(!t.type.indexOf("key")&&e._k(t.keyCode,"enter",13,t.key,"Enter"))return null;e.promptEmail=!1}},model:{value:e.emailReport.fields.email_cliente,callback:function(t){e.$set(e.emailReport.fields,"email_cliente",t)},expression:"emailReport.fields.email_cliente"}})],1),t("q-card-actions",{staticClass:"text-primary",attrs:{align:"right"}},[t("q-btn",{attrs:{flat:"",label:"Cancelar"},on:{click:function(t){return e.cleanDialog()}}}),t("q-btn",{attrs:{flat:"",label:"Enviar",loading:e.loadingSendingMailBtn},on:{click:function(t){return e.sendEmailsReport()}}})],1)],1)],1),e.haspermissionv1?t("q-btn",{staticStyle:{"margin-left":"10px"},attrs:{color:"green",icon:"fas fa-file-excel"},on:{click:function(t){return e.generateCSV()}}},[t("q-tooltip",[e._v("GENERAR CSV")])],1):e._e(),e.haspermissionv1?t("q-btn",{staticStyle:{"margin-left":"10px"},attrs:{color:"red",icon:"fas fa-file-pdf"},on:{click:function(t){return e.generatePDF()}}},[t("q-tooltip",[e._v("GENERAR PDF")])],1):e._e()],1)])])])]),t("div",{staticClass:"q-pa-md bg-grey-3"},[t("div",{staticClass:"row bg-white q-col-gutter-md"},[t("div",{staticClass:"col-md-4"}),t("div",{staticClass:"col-md-2"},[t("q-select",{attrs:{color:"white","bg-color":"primary",filled:"",dark:"",mask:"date",label:"Desde"},model:{value:e.saleDatev1,callback:function(t){e.saleDatev1=t},expression:"saleDatev1"}},[t("q-popup-proxy",{ref:"date",attrs:{"transition-show":"scale","transition-hide":"scale"}},[t("div",{staticClass:"col-sm-12"},[t("q-date",{attrs:{"today-btn":""},on:{input:function(t){return e.filterGrid()}},model:{value:e.saleDatev1,callback:function(t){e.saleDatev1=t},expression:"saleDatev1"}})],1)])],1)],1),t("div",{staticClass:"col-md-2"},[t("q-select",{attrs:{color:"white","bg-color":"primary",filled:"",dark:"",mask:"date",label:"Hasta"},model:{value:e.saleDatev2,callback:function(t){e.saleDatev2=t},expression:"saleDatev2"}},[t("q-popup-proxy",{ref:"date",attrs:{"transition-show":"scale","transition-hide":"scale"}},[t("div",{staticClass:"col-sm-12"},[t("q-date",{attrs:{"today-btn":""},on:{input:function(t){return e.filterGrid()}},model:{value:e.saleDatev2,callback:function(t){e.saleDatev2=t},expression:"saleDatev2"}})],1)])],1)],1),t("div",{staticClass:"col-md-2"},[t("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",options:[{label:"PENDIENTE",value:0},{label:"ABONADO",value:1},{label:"PAGADO",value:2}],"emit-value":"","map-options":"",multiple:"",label:"Estatus"},on:{input:function(t){return e.filterGrid()}},scopedSlots:e._u([{key:"option",fn:function(a){var i=a.itemProps,s=a.itemEvents,l=a.opt,n=a.selected,o=a.toggleOption;return[t("q-item",e._g(e._b({},"q-item",i,!1),s),[t("q-item-section",[t("q-item-label",{domProps:{innerHTML:e._s(l.label)}})],1),t("q-item-section",{attrs:{side:""}},[t("q-toggle",{attrs:{value:n},on:{input:function(e){return o(l)}}})],1)],1)]}}]),model:{value:e.status,callback:function(t){e.status=t},expression:"status"}})],1),t("div",{staticClass:"col-md-2"},[t("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",options:e.filteredCustomerOptions,"use-input":"","hide-selected":"","fill-input":"","input-debounce":"0",label:"Cliente","emit-value":"","map-options":""},on:{filter:e.filtrarClientes,input:function(t){return e.filterGrid()}},model:{value:e.customer,callback:function(t){e.customer=t},expression:"customer"}})],1)]),t("div",{staticClass:"row bg-white"},[t("div",{staticClass:"col q-pa-md"},[t("q-table",{attrs:{flat:"",bordered:"",data:e.data,columns:e.columns,"row-key":"sale_date",pagination:e.pagination,filter:e.filter},on:{"update:pagination":function(t){e.pagination=t},request:e.qTableRequest},scopedSlots:e._u([{key:"top",fn:function(){return[t("div",{staticStyle:{width:"100%"}},[t("q-input",{attrs:{dense:"",debounce:"300",placeholder:"Buscar"},scopedSlots:e._u([{key:"append",fn:function(){return[t("q-icon",{attrs:{name:"search"}})]},proxy:!0}]),model:{value:e.filter,callback:function(t){e.filter=t},expression:"filter"}})],1)]},proxy:!0},{key:"body",fn:function(a){return[t("q-tr",{attrs:{props:a}},[t("q-td",{key:"id",staticStyle:{"text-align":"right"},attrs:{props:a}},[t("label",{staticStyle:{"text-decoration":"underline black",cursor:"pointer"},on:{click:function(t){return e.openActions(a.row.id)}}},[e._v(e._s(a.row.id))])]),t("q-td",{key:"status_payment",attrs:{props:a}},[t("q-chip",{attrs:{square:"",dense:"",color:e.colorPayment[a.row.status_payment],"text-color":"white"}},[e._v(e._s(e.statusPayment[a.row.status_payment]))])],1),t("q-td",{key:"sale_date",attrs:{props:a}},[e._v(e._s(a.row.sale_date))]),t("q-td",{key:"shopping_cart_id",staticStyle:{"text-align":"right"},attrs:{props:a}},[t("label",{staticStyle:{"text-decoration":"underline black",cursor:"pointer"},on:{click:function(t){return e.openActionsv2(a.row.shopping_cart_id)}}},[e._v(e._s(a.row.shopping_cart_id))])]),t("q-td",{key:"customer",staticStyle:{"text-align":"left"},attrs:{props:a}},[e._v(e._s(a.row.customer))]),t("q-td",{key:"total",staticStyle:{"text-align":"right"},attrs:{props:a}},[e._v(e._s("".concat(e.currencyFormatter.format(a.row.cantidad_total))))]),t("q-td",{key:"abonado",staticStyle:{"text-align":"right"},attrs:{props:a}},[e._v(e._s("".concat(e.currencyFormatter.format(a.row.abonado))))]),t("q-td",{key:"restante",staticStyle:{"text-align":"right"},attrs:{props:a}},[e._v(e._s("".concat(e.currencyFormatter.format(a.row.cantidad_restante))))])],1)]}}])})],1)])])])},s=[],l=a("ded3"),n=a.n(l),o=a("7ec2"),r=a.n(o),c=a("c973"),u=a.n(c),d=(a("99af"),a("159b"),a("a9e3"),a("caad"),a("2532"),a("14d9"),a("b680"),a("ac1f"),a("5319"),a("aabb")),p=a("b5ae"),m=p.required,v=p.email,f={name:"IndexStorageExits",validations:{paymentMethod:{required:m},paymentDate:{required:m},qty:{required:m},emailReport:{fields:{email_cliente:{required:m,email:v},customer_select:{required:m}}}},data:function(){return{currencyFormatter:new Intl.NumberFormat("en-US",{style:"currency",currency:"USD"}),formatter:new Intl.NumberFormat("en-US"),role:null,promptEmailPagos:!1,tableWithInvoice:!1,tableWithoutInvoice:!0,interval:null,customer:"TODOS",disableBtnAddPayment:!0,disableTblPayments:!0,qty:null,paymentDate:null,paymentMethod:null,status:[],emailReport:{fields:{email_cliente:"",customer_select:null}},loadingSendingMailBtn:!1,reference:null,total_invoice:null,id_invoice:null,paymentsModal:!1,saleDatev1:null,saleDatev2:null,customerOptions:[],filteredCustomerOptions:[],filteredCustomerOptionsv2:[],formaPagoOptions:[],pagination:{sortBy:"id",descending:!0,page:1,rowsNumber:0,rowsPerPage:50},columns:[{name:"id",align:"center",label:"# Remision",field:"id",sortable:!0},{name:"status_payment",align:"center",label:"Estatus de Pago",field:"status_payment",sortable:!0},{name:"sale_date",align:"center",label:"Fecha de venta",field:"sale_date",sortable:!0},{name:"shopping_cart_id",align:"center",label:"# Pedido",field:"shopping_cart_id",sortable:!0},{name:"customer",align:"center",label:"Cliente",field:"customer",sortable:!0},{name:"total",align:"center",label:"Monto Total",field:"total",sortable:!0},{name:"abonado",align:"center",label:"Abonado",field:"abonado",sortable:!0},{name:"restante",align:"center",label:"Restante",field:"restante",sortable:!0}],data:[],filter:"",statusPayment:["PENDIENTE DE PAGO","ABONADO","PAGADO"],colorPayment:["blue-6","warning","green-6"]}},computed:{roleId:function(){var e=this.$store.getters["users/rol"];return parseInt(e)},fiberSaleDocumentFileUrl:function(){return"".concat("http://api.alpez.beta.wasp.mx/","invoices/").concat(this.fiberSaleDocumentFile.fields.fiberSaleId,"/document-file")},totalAmounfromPayments:function(){var e=0;return this.dataPayments.forEach((function(t){e+=Number(t.amount)})),e},haspermissionv1:function(){var e=!1;return(this.$store.getters["users/roles"].includes(1)||this.$store.getters["users/roles"].includes(3)||this.$store.getters["users/roles"].includes(7)||this.$store.getters["users/roles"].includes(12)||this.$store.getters["users/roles"].includes(17))&&(e=!0),e},emailRule:function(e){var t=this;return[function(e){return t.$v.emailReport.fields.email_cliente.email||"El campo debe contener un correo válido."}]}},beforeRouteEnter:function(e,t,a){a((function(e){var t=e.$store.getters["users/rol"];console.log(t),1===t||2===t||12===t||17===t?a():a("/")}))},mounted:function(){this.fetchFromServer(),this.getClients()},methods:{fetchFromServer:function(){this.$q.loading.show(),this.qTableRequest({pagination:this.pagination,filter:this.filter})},qTableRequest:function(e){var t=this;return u()(r()().mark((function a(){var i;return r()().wrap((function(a){while(1)switch(a.prev=a.next){case 0:return t.pagination=e.pagination,t.filter=e.filter,t.data=[],i=[],i.customer=t.customer,i.status=t.status,i.saleDatev1=t.saleDatev1,i.saleDatev2=t.saleDatev2,i.type=1,i.pagination=t.pagination,i.filter=t.filter,a.next=13,d["a"].post("/invoices/pag_payments",i).then((function(e){var a=e.data;t.$q.loading.hide(),t.data=a.invoices,t.pagination.rowsNumber=a.invoicesCount})).catch((function(e){return e}));case 13:case"end":return a.stop()}}),a)})))()},getClients:function(){var e=this;d["a"].get("/customers/options").then((function(t){var a=t.data;e.customerOptions=a.options,e.customerOptionsv2=a.options,e.customerOptions.push({label:"TODOS",value:"TODOS"})}))},getPayments:function(e){var t=this,a=[];a.id=this.id_invoice,d["a"].get("/invoices/formaPagoOptions").then((function(e){var i=e.data;t.formaPagoOptions=i.options,d["a"].post("/invoices/dataFromInvoice",a).then((function(e){var a=e.data;a.result&&(t.total_invoice=parseFloat(a.total_invoice),t.dataPayments=a.payments,t.dataPayments.length>0?d["a"].get("/invoices/keepCheckingPayments/"+t.id_invoice).then((function(e){var a=e.data;a.result?null===t.interval&&(t.interval=setInterval((function(){t.revisarPagos(t.id_invoice)}),1e4)):(clearInterval(t.interval),t.interval=null),t.disableTblPayments=!0;var i=parseFloat(t.totalAmounfromPayments),s=parseFloat(t.total_invoice.toFixed(2));s>i&&(t.disableBtnAddPayment=!1,t.fetchFromServer())})):t.disableTblPayments=!1)}))}))},filterGrid:function(){var e=this;this.data=[];var t=[];t.customer=this.customer,t.status=this.status,t.saleDatev1=this.saleDatev1,t.saleDatev2=this.saleDatev2,t.type=1,t.pagination=this.pagination,t.filter=this.filter,d["a"].post("/invoices/getGridPayments",t).then((function(t){var a=t.data;a.result&&(e.$q.loading.hide(),e.data=a.invoices,e.pagination.rowsNumber=a.invoicesCount)}))},filtrarClientes:function(e,t,a){var i=this;t((function(){var t=e.toLowerCase();i.filteredCustomerOptions=i.customerOptions.filter((function(e){return e.label.toLowerCase().indexOf(t)>-1}))}))},filtrarClientesv2:function(e,t,a){var i=this;t((function(){var t=e.toLowerCase();i.filteredCustomerOptionsv2=i.customerOptionsv2.filter((function(e){return e.label.toLowerCase().indexOf(t)>-1}))}))},generatePDF:function(){var e=[],t=[];if(t=0===this.status.length?[99]:this.status,e.customer=this.customer,e.status=t,e.type=1,this.saleDatev1){e.saleDatev1=this.saleDatev1;while(e.saleDatev1.includes("/"))e.saleDatev1=e.saleDatev1.replace("/","-")}else e.saleDatev1=null;if(this.saleDatev2){e.saleDatev2=this.saleDatev2;while(e.saleDatev2.includes("/"))e.saleDatev2=e.saleDatev2.replace("/","-")}else e.saleDatev2=null;var a="http://api.alpez.beta.wasp.mx/"+"/invoices/getPdfFromPaymentsDetails/".concat(e.type,"/").concat(e.customer,"/").concat(e.status,"/").concat(e.saleDatev1,"/").concat(e.saleDatev2);window.open(a,"_blank")},generateCSV:function(){var e=[];if(e.customer=this.customer,e.status=this.status,this.saleDatev1){e.saleDatev1=this.saleDatev1;while(e.saleDatev1.includes("/"))e.saleDatev1=e.saleDatev1.replace("/","-")}else e.saleDatev1=null;if(this.saleDatev2){e.saleDatev2=this.saleDatev2;while(e.saleDatev2.includes("/"))e.saleDatev2=e.saleDatev2.replace("/","-")}else e.saleDatev2=null;var t="http://api.alpez.beta.wasp.mx/"+"/invoices/getCSVFromPaymentsDetails/".concat(e.customer,"/").concat(e.status,"/").concat(e.saleDatev1,"/").concat(e.saleDatev2);window.open(t,"_blank")},sendMail:function(){this.promptEmailPagos=!0},filterMail:function(){var e=this,t=[];t.id=this.emailReport.fields.customer_select,d["a"].post("/customers/getDataClient",t).then((function(t){var a=t.data;console.log(a),e.emailReport.fields.email_cliente=a.data[0].email}))},cleanDialog:function(){this.emailReport.fields.email_cliente="",this.emailReport.fields.customer_select=null,this.promptEmailPagos=!1},sendEmailsReport:function(){var e=this;if(this.$v.emailReport.fields.$reset(),this.$v.emailReport.fields.$reset(),this.$v.emailReport.fields.$touch(),this.$v.emailReport.fields.$error)return!1;this.loadingSendingMailBtn=!0;var t=[];if(t.customer=this.emailReport.fields.customer_select,t.email=n()({},this.emailReport.fields).email_cliente,this.saleDatev1){t.saleDatev1=this.saleDatev1;while(t.saleDatev1.includes("/"))t.saleDatev1=t.saleDatev1.replace("/","-")}else t.saleDatev1=null;if(this.saleDatev2){t.saleDatev2=this.saleDatev2;while(t.saleDatev2.includes("/"))t.saleDatev2=t.saleDatev2.replace("/","-")}else t.saleDatev2=null;t.status=this.status,d["a"].post("/invoices/sendEmailsReport",t).then((function(t){var a=t.data;e.$q.notify({message:a.message.content,position:"top",color:a.result?"positive":"warning"}),a.result?(e.promptEmailPagos=!1,e.emailReport.fields.email_cliente="",e.emailReport.fields.customer_select=null,e.loadingSendingMailBtn=!1):e.loadingSendingMailBtn=!1}))}}},h=f,g=(a("9316"),a("2877")),b=a("9989"),D=a("ead5"),y=a("079e"),_=a("9c40"),q=a("05c0"),w=a("24e8"),C=a("f09f"),k=a("a370"),P=a("ddd8"),x=a("27f9"),S=a("4b7e"),R=a("7cbe"),O=a("52ee"),E=a("66e5"),$=a("4074"),F=a("0170"),Q=a("9564"),A=a("eaac"),I=a("0016"),T=a("bd08"),N=a("db86"),B=a("b047"),M=a("eebe"),G=a.n(M),L=Object(g["a"])(h,i,s,!1,null,null,null);t["default"]=L.exports;G()(L,"components",{QPage:b["a"],QBreadcrumbs:D["a"],QBreadcrumbsEl:y["a"],QBtn:_["a"],QTooltip:q["a"],QDialog:w["a"],QCard:C["a"],QCardSection:k["a"],QSelect:P["a"],QInput:x["a"],QCardActions:S["a"],QPopupProxy:R["a"],QDate:O["a"],QItem:E["a"],QItemSection:$["a"],QItemLabel:F["a"],QToggle:Q["a"],QTable:A["a"],QIcon:I["a"],QTr:T["a"],QTd:N["a"],QChip:B["a"]})},9316:function(e,t,a){"use strict";a("1ea1")}}]);
(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[12],{"1eb8":function(e,t,a){"use strict";a("2460")},2460:function(e,t,a){},c69e:function(e,t,a){"use strict";a.r(t);var s=function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("q-page",{staticClass:"bg-grey-3"},[a("div",{staticClass:"q-pa-sm panel-header"},[a("div",{staticClass:"row q-col-gutter-xs q-col-gutter-md"},[a("div",{staticClass:"col-sm-8"},[a("div",{staticClass:"q-pa-md q-gutter-sm",staticStyle:{"font-size":"24px"}},[a("q-breadcrumbs",{staticStyle:{"font-size":"24px"},attrs:{align:"left","active-color":"blue-8"}},[a("q-breadcrumbs-el",{attrs:{label:"",icon:"home",to:"/"}}),a("q-breadcrumbs-el",{attrs:{label:"Auxiliar Contable"}})],1)],1)]),a("div",{staticClass:"col-xs-12 col-md-4 pull-right"},[a("div",{staticClass:"q-pa-md q-gutter-sm"},[a("q-dialog",{attrs:{persistent:""},model:{value:e.promptEmailPagos,callback:function(t){e.promptEmailPagos=t},expression:"promptEmailPagos"}},[a("q-card",{staticStyle:{"min-width":"350px"}},[a("q-card-section",{staticClass:"bg-primary"},[a("div",{staticClass:"row"},[a("div",{staticClass:"col-sm-11 text-h6",staticStyle:{color:"white"}},[e._v("Enviar E-mail")]),a("div",{staticClass:"col-sm-1 pull-right"},[a("q-btn",{directives:[{name:"close-popup",rawName:"v-close-popup"}],attrs:{color:"white",flat:"",round:"",dense:"",icon:"close"}})],1)])]),a("q-card-section",{staticClass:"row items-center"},[a("div",{staticClass:"row"},[a("div",{staticClass:"col-xs-12 col-sm-12 col-md-12 col-lg-12"},[a("q-select",{attrs:{color:"white","bg-color":"primary",filled:"",dark:"",options:e.filteredCustomerOptionsv2,"use-input":"","hide-selected":"","fill-input":"",error:e.$v.emailReport.fields.customer_select.$error,"input-debounce":"0",label:"Cliente","emit-value":"","map-options":""},on:{filter:e.filtrarClientesv2,input:function(t){return e.filterMail()}},model:{value:e.emailReport.fields.customer_select,callback:function(t){e.$set(e.emailReport.fields,"customer_select",t)},expression:"emailReport.fields.customer_select"}})],1),a("div",{staticClass:"col-xs-12 col-sm-12 col-md-12 col-lg-12",staticStyle:{"padding-top":"15px"}},[a("q-input",{attrs:{color:"white","bg-color":"primary",filled:"",dark:"",error:e.$v.emailReport.fields.email_cliente.$error,label:"E-mail",rules:e.emailRule},on:{keyup:function(t){if(!t.type.indexOf("key")&&e._k(t.keyCode,"enter",13,t.key,"Enter"))return null;e.promptEmail=!1}},model:{value:e.emailReport.fields.email_cliente,callback:function(t){e.$set(e.emailReport.fields,"email_cliente",t)},expression:"emailReport.fields.email_cliente"}})],1)])]),a("q-card-actions",{attrs:{align:"right"}},[a("q-btn",{directives:[{name:"close-popup",rawName:"v-close-popup"}],attrs:{label:"Cancelar",color:"negative"},on:{click:function(t){return e.cleanDialog()}}}),a("q-btn",{attrs:{label:"Enviar",color:"positive",loading:e.loadingSendingMailBtn},on:{click:function(t){return e.sendEmailsReport()}}})],1)],1)],1),a("q-btn",{staticStyle:{"margin-left":"10px"},attrs:{color:"green",icon:"fas fa-file-excel"},on:{click:function(t){return e.generateCSV()}}},[a("q-tooltip",[e._v("GENERAR CSV")])],1),a("q-btn",{staticStyle:{"margin-left":"10px"},attrs:{color:"red",icon:"fas fa-file-pdf"},on:{click:function(t){return e.generatePDF()}}},[a("q-tooltip",[e._v("GENERAR PDF")])],1)],1)])])]),a("div",{staticClass:"q-pa-md bg-grey-3"},[a("div",{staticClass:"row bg-white"},[a("div",{staticClass:"col q-pa-md"},[a("div",{staticClass:"row q-col-gutter-xs"},[a("div",{staticClass:"col-md-2 col-xs-12 col-sm-2"},[a("q-select",{attrs:{color:"white","bg-color":"secondary",filled:"",dark:"",mask:"date",label:"Desde"},model:{value:e.saleDatev1,callback:function(t){e.saleDatev1=t},expression:"saleDatev1"}},[a("q-popup-proxy",{ref:"date",attrs:{"transition-show":"scale","transition-hide":"scale"}},[a("div",{staticClass:"col-sm-12"},[a("q-date",{attrs:{mask:"DD/MM/YYYY","today-btn":""},on:{input:function(t){return e.filterGrid()}},model:{value:e.saleDatev1,callback:function(t){e.saleDatev1=t},expression:"saleDatev1"}})],1)])],1)],1),a("div",{staticClass:"col-md-2"},[a("q-select",{attrs:{color:"white","bg-color":"secondary",filled:"",dark:"",mask:"date",label:"Hasta"},model:{value:e.saleDatev2,callback:function(t){e.saleDatev2=t},expression:"saleDatev2"}},[a("q-popup-proxy",{ref:"date",attrs:{"transition-show":"scale","transition-hide":"scale"}},[a("div",{staticClass:"col-sm-12"},[a("q-date",{attrs:{mask:"DD/MM/YYYY","today-btn":""},on:{input:function(t){return e.filterGrid()}},model:{value:e.saleDatev2,callback:function(t){e.saleDatev2=t},expression:"saleDatev2"}})],1)])],1)],1),a("div",{staticClass:"col-xs-12 col-sm-3 col-md-3"},[a("q-select",{attrs:{color:"white","bg-color":"secondary",filled:"",dark:"",options:e.filteredCustomerOptions,"use-input":"","hide-selected":"","fill-input":"","input-debounce":"0",label:"Cliente","emit-value":"","map-options":""},on:{filter:e.filtrarClientes,input:function(t){return e.filterGrid()}},model:{value:e.customer,callback:function(t){e.customer=t},expression:"customer"}})],1),a("div",{staticClass:"col-xs-12 col-sm-3 text-center"},[a("q-select",{attrs:{color:"white","bg-color":"secondary",filled:"",dark:"","emit-value":"","map-options":"",options:e.branchesList,rules:e.branchOfficeRules,error:e.$v.branch_id.$error,label:"Estación"},on:{input:function(t){return e.filterGrid()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[a("q-icon",{attrs:{name:"business"}})]},proxy:!0}]),model:{value:e.branch_id,callback:function(t){e.branch_id=t},expression:"branch_id"}})],1),a("div",{staticClass:"col-md-2"},[a("q-select",{attrs:{color:"white","bg-color":"secondary",filled:"",dark:"",options:[{label:"PAGADO",value:1},{label:"PENDIENTE",value:2},{label:"ABONADO",value:3}],"emit-value":"","map-options":"",multiple:"",label:"Estatus Pago"},on:{input:function(t){return e.filterGrid()}},scopedSlots:e._u([{key:"option",fn:function(t){var s=t.itemProps,i=t.itemEvents,l=t.opt,n=t.selected,r=t.toggleOption;return[a("q-item",e._g(e._b({},"q-item",s,!1),i),[a("q-item-section",[a("q-item-label",{domProps:{innerHTML:e._s(l.label)}})],1),a("q-item-section",{attrs:{side:""}},[a("q-toggle",{attrs:{color:"green",value:n},on:{input:function(e){return r(l)}}})],1)],1)]}}]),model:{value:e.status,callback:function(t){e.status=t},expression:"status"}})],1)])])]),a("div",{staticClass:"row bg-white"},[a("div",{staticClass:"col q-pa-md"},[a("div",{attrs:{id:"sticky-table-scroll"}},[a("q-table",{staticStyle:{"margin-top":"10px"},attrs:{id:"sticky-table-newstyle",flat:"",bordered:"",data:e.data,columns:e.columns,"row-key":"sale_date",pagination:e.pagination,filter:e.filter},on:{"update:pagination":function(t){e.pagination=t},request:e.qTableRequest},scopedSlots:e._u([{key:"top",fn:function(){return[a("div",{staticStyle:{width:"100%"}},[a("q-input",{attrs:{dense:"",debounce:"300",placeholder:"Buscar"},scopedSlots:e._u([{key:"append",fn:function(){return[a("q-icon",{attrs:{name:"search"}})]},proxy:!0}]),model:{value:e.filter,callback:function(t){e.filter=t},expression:"filter"}})],1)]},proxy:!0},{key:"body",fn:function(t){return[a("q-tr",{attrs:{props:t}},[a("q-td",{key:"id",staticStyle:{"text-align":"center"},attrs:{props:t}},[a("label",{staticClass:"text-blue-8",staticStyle:{"text-decoration":"underline",cursor:"pointer"},on:{click:function(a){return e.openActions(t.row.id)}}},[e._v(e._s(t.row.id))])]),a("q-td",{key:"sale_date",attrs:{props:t}},[e._v(e._s(t.row.sale_date))]),a("q-td",{key:"status_invoice",staticStyle:{"text-align":"center"},attrs:{props:t}},[a("q-chip",{attrs:{square:"",dense:"",color:"REMISIONADO"==t.row.status?"primary":"ENVIADO"==t.row.status?"warning":"ENTREGADO"==t.row.status||"PAGADO"==t.row.status?"positive":"FACTURADO"==t.row.status?"purple":"CANCELADO"==t.row.status?"negative":"black","text-color":"white"}},[e._v("\n                    "+e._s(t.row.status)+"\n                  ")])],1),a("q-td",{key:"status_payment",attrs:{props:t}},[a("q-chip",{staticStyle:{"font-size":"13px","padding-top":"3px"},attrs:{square:"",dense:"",color:e.colorPayment[t.row.status_payment],"text-color":"white"}},[e._v(e._s(e.statusPayment[t.row.status_payment]))])],1),a("q-td",{key:"no_factura",staticStyle:{"text-align":"left"},attrs:{props:t}},[e._v(e._s(t.row.factura))]),a("q-td",{key:"shopping_cart_id",staticStyle:{"text-align":"center"},attrs:{props:t}},[a("label",{staticClass:"text-blue-8",staticStyle:{"text-decoration":"underline",cursor:"pointer"},on:{click:function(a){return e.openActionsv2(t.row.shopping_cart_id)}}},[e._v(e._s(t.row.shopping_cart_id))])]),a("q-td",{key:"fecha_factura",staticStyle:{"text-align":"center"},attrs:{props:t}},[e._v(e._s(t.row.expired_date))]),a("q-td",{key:"customer",staticStyle:{"text-align":"left"},attrs:{props:t}},[e._v(e._s(t.row.customer))]),a("q-td",{key:"total",staticStyle:{"text-align":"right"},attrs:{props:t}},[e._v(e._s(""+e.currencyFormatter.format(t.row.cantidad_total)))]),a("q-td",{key:"abonado",staticStyle:{"text-align":"right"},attrs:{props:t}},[e._v(e._s(""+e.currencyFormatter.format(t.row.abonado)))]),a("q-td",{key:"restante",staticStyle:{"text-align":"right"},attrs:{props:t}},[e._v(e._s(""+e.currencyFormatter.format(t.row.cantidad_restante)))])],1)]}}])})],1)])])])])},i=[],l=a("ded3"),n=a.n(l),r=a("7ec2"),o=a.n(r),c=a("c973"),u=a.n(c),d=(a("99af"),a("d3b7"),a("159b"),a("a9e3"),a("caad"),a("2532"),a("4de4"),a("b680"),a("ac1f"),a("5319"),a("aabb")),p=a("b5ae"),m=p.required,f=p.email,v={name:"IndexStorageExits",validations:{paymentMethod:{required:m},paymentDate:{required:m},qty:{required:m},emailReport:{fields:{email_cliente:{required:m,email:f},customer_select:{required:m}}},branch_id:{}},data:function(){return{currencyFormatter:new Intl.NumberFormat("en-US",{style:"currency",currency:"USD"}),formatter:new Intl.NumberFormat("en-US"),role:null,promptEmailPagos:!1,tableWithInvoice:!1,tableWithoutInvoice:!0,interval:null,customer:"TODOS",disableBtnAddPayment:!0,disableTblPayments:!0,qty:null,branch_id:0,paymentDate:null,paymentMethod:null,status:[],emailReport:{fields:{email_cliente:"",customer_select:null}},loadingSendingMailBtn:!1,reference:null,total_invoice:null,id_invoice:null,paymentsModal:!1,saleDatev1:null,saleDatev2:null,customerOptions:[],filteredCustomerOptions:[],filteredCustomerOptionsv2:[],formaPagoOptions:[],branchesList:[],pagination:{sortBy:"id",descending:!0,page:1,rowsNumber:0,rowsPerPage:25},columns:[{name:"id",align:"center",label:"# REMISIÓN",field:"id",sortable:!0},{name:"sale_date",align:"center",label:"FECHA DE VENTA",field:"sale_date",sortable:!0},{name:"status_invoice",align:"center",label:"STATUS",field:"status_invoice",sortable:!0},{name:"status_payment",align:"center",label:"ESTATUS DE PAGO",field:"status_payment",sortable:!0},{name:"shopping_cart_id",align:"center",label:"No. FACTURA",field:"shopping_cart_id",sortable:!0},{name:"no_factura",align:"center",label:"No. PEDIDO",field:"no_factura",sortable:!0},{name:"fecha_factura",align:"center",label:"FECHA FACTURA",field:"fecha_factura",sortable:!0},{name:"customer",align:"center",label:"CLIENTE",field:"customer",sortable:!0},{name:"total",align:"center",label:"MONTO TOTAL",field:"total",sortable:!0},{name:"abonado",align:"center",label:"ABONADO",field:"abonado",sortable:!0},{name:"restante",align:"center",label:"RESTANTE",field:"restante",sortable:!0}],data:[],filter:"",statusPayment:["PENDIENTE DE PAGO","ABONADO","PAGADO","VENCIDO","VENCIDO ABONADO"],colorPayment:["blue-6","warning","green-6","red-14","red-14"]}},computed:{roleId:function(){var e=this.$store.getters["users/rol"];return parseInt(e)},fiberSaleDocumentFileUrl:function(){return"".concat("https://api_alpez.wasp.mx/","invoices/document-file/").concat(this.fiberSaleDocumentFile.fields.fiberSaleId)},totalAmounfromPayments:function(){var e=0;return this.dataPayments.forEach((function(t){e+=Number(t.amount)})),e},haspermissionv1:function(){var e=!1;return(this.$store.getters["users/roles"].includes(1)||this.$store.getters["users/roles"].includes(12)||this.$store.getters["users/roles"].includes(17))&&(e=!0),e},emailRule:function(e){var t=this;return[function(e){return t.$v.emailReport.fields.email_cliente.email||"El campo debe contener un correo válido."}]},branchOfficeRules:function(e){var t=this;return[function(e){return t.$v.branch_id||"El campo Sucursal es requerido."}]}},beforeRouteEnter:function(e,t,a){a((function(e){var t=e.$store.getters["users/rol"];console.log(t),1===t||2===t||12===t||17===t||28===t||4===t||22===t||25===t||20===t||29===t?a():a("/")}))},mounted:function(){this.fetchFromServer(),this.getClients(),this.getBranchesList()},methods:{openActions:function(e){this.$router.push("/storage-exits/".concat(e))},openActionsv2:function(e){this.$router.push("/shopping-carts/orders/".concat(e))},fetchFromServer:function(){this.$q.loading.show(),this.qTableRequest({pagination:this.pagination,filter:this.filter})},qTableRequest:function(e){var t=this;return u()(o()().mark((function a(){var s;return o()().wrap((function(a){while(1)switch(a.prev=a.next){case 0:return t.pagination=e.pagination,t.filter=e.filter,t.data=[],s=[],s.customer=t.customer,s.status=t.status,s.saleDatev1=t.saleDatev1,s.saleDatev2=t.saleDatev2,s.branchid=t.branch_id,s.invoices="",s.type=1,s.pagination=t.pagination,s.filter=t.filter,a.next=15,d["a"].post("/invoices/pag_payments",s).then((function(e){var a=e.data;t.$q.loading.hide(),t.data=a.invoices,console.log(a),t.pagination.rowsNumber=a.invoicesCount})).catch((function(e){return e}));case 15:case"end":return a.stop()}}),a)})))()},getClients:function(){var e=this;d["a"].get("/customers/options").then((function(t){var a=t.data;e.customerOptions=a.options,e.customerOptionsv2=a.options,e.customerOptions.push({label:"TODOS",value:"TODOS"})}))},getPayments:function(e){var t=this,a=[];a.id=this.id_invoice,d["a"].get("/invoices/formaPagoOptions").then((function(e){var s=e.data;t.formaPagoOptions=s.options,d["a"].post("/invoices/dataFromInvoice",a).then((function(e){var a=e.data;a.result&&(t.total_invoice=parseFloat(a.total_invoice),t.dataPayments=a.payments,t.dataPayments.length>0?d["a"].get("/invoices/keepCheckingPayments/"+t.id_invoice).then((function(e){var a=e.data;a.result?null===t.interval&&(t.interval=setInterval((function(){t.revisarPagos(t.id_invoice)}),1e4)):(clearInterval(t.interval),t.interval=null),t.disableTblPayments=!0;var s=parseFloat(t.totalAmounfromPayments),i=parseFloat(t.total_invoice.toFixed(2));i>s&&(t.disableBtnAddPayment=!1,t.fetchFromServer())})):t.disableTblPayments=!1)}))}))},filterGrid:function(){var e=this;this.data=[];var t=[];t.customer=this.customer,t.status=this.status,t.saleDatev1=this.saleDatev1,t.saleDatev2=this.saleDatev2,t.invoices="",t.type=1,t.pagination=this.pagination,t.filter=this.filter,t.branch=this.branch_id,d["a"].post("/invoices/getGridPayments",t).then((function(t){var a=t.data;a.result&&(e.$q.loading.hide(),e.data=a.invoices,e.pagination.rowsNumber=a.invoicesCount)})),"TODOS"!==this.customer&&(this.emailReport.fields.customer_select=this.customer,this.filterMail())},filtrarClientes:function(e,t,a){var s=this;t((function(){var t=e.toLowerCase();s.filteredCustomerOptions=s.customerOptions.filter((function(e){return e.label.toLowerCase().indexOf(t)>-1}))}))},filtrarClientesv2:function(e,t,a){var s=this;t((function(){var t=e.toLowerCase();s.filteredCustomerOptionsv2=s.customerOptionsv2.filter((function(e){return e.label.toLowerCase().indexOf(t)>-1}))}))},generatePDF:function(){var e=[],t=[];if(t=0===this.status.length?[99]:this.status,e.customer=this.customer,e.status=t,e.type=1,this.saleDatev1){e.saleDatev1=this.saleDatev1;while(e.saleDatev1.includes("/"))e.saleDatev1=e.saleDatev1.replace("/","-")}else e.saleDatev1=null;if(this.saleDatev2){e.saleDatev2=this.saleDatev2;while(e.saleDatev2.includes("/"))e.saleDatev2=e.saleDatev2.replace("/","-")}else e.saleDatev2=null;if("null"===this.branch_id?e.branchid="TODAS":e.branchid=this.branch_id,0===this.branch_id)return this.$q.notify({message:"Elige una sucursal",position:"top",color:"warning"}),!1;var a="https://api_alpez.wasp.mx/"+"invoices/getPdfFromPaymentsDetails/".concat(e.type,"/").concat(e.customer,"/").concat(e.status,"/").concat(e.saleDatev1,"/").concat(e.saleDatev2,"/").concat(e.branchid);window.open(a,"_blank")},generateCSV:function(){var e=[],t=[];if(t=0===this.status.length?[99]:this.status,e.customer=this.customer,e.status=t,this.saleDatev1){e.saleDatev1=this.saleDatev1;while(e.saleDatev1.includes("/"))e.saleDatev1=e.saleDatev1.replace("/","-")}else e.saleDatev1=null;if(this.saleDatev2){e.saleDatev2=this.saleDatev2;while(e.saleDatev2.includes("/"))e.saleDatev2=e.saleDatev2.replace("/","-")}else e.saleDatev2=null;if("null"===this.branch_id?e.branchid="TODAS":e.branchid=this.branch_id,0===this.branch_id)return this.$q.notify({message:"Elige una sucursal",position:"top",color:"warning"}),!1;var a="https://api_alpez.wasp.mx/"+"invoices/getCSVFromPaymentsDetails/".concat(e.customer,"/").concat(e.status,"/").concat(e.saleDatev1,"/").concat(e.saleDatev2,"/").concat(e.branchid);window.open(a,"_blank")},sendMail:function(){this.promptEmailPagos=!0},filterMail:function(){var e=this,t=[];t.id=this.emailReport.fields.customer_select,t.branch=this.branch_id,d["a"].post("/customers/getDataClient",t).then((function(t){var a=t.data;e.emailReport.fields.email_cliente=a.data[0][f]}))},cleanDialog:function(){this.emailReport.fields.email_cliente="",this.emailReport.fields.customer_select=null,this.promptEmailPagos=!1},getBranchesList:function(){var e=this;d["a"].get("branch-offices/options").then((function(t){e.branchesList=t.data.options,e.branchesList.push({label:"TODAS",value:0})}))},sendEmailsReport:function(){var e=this;if(this.$v.emailReport.fields.$reset(),this.$v.emailReport.fields.$reset(),this.$v.emailReport.fields.$touch(),this.$v.emailReport.fields.$error)return!1;this.loadingSendingMailBtn=!0;var t=[];if(t.customer=this.emailReport.fields.customer_select,t.email=n()({},this.emailReport.fields).email_cliente,this.saleDatev1){t.saleDatev1=this.saleDatev1;while(t.saleDatev1.includes("/"))t.saleDatev1=t.saleDatev1.replace("/","-")}else t.saleDatev1=null;if(this.saleDatev2){t.saleDatev2=this.saleDatev2;while(t.saleDatev2.includes("/"))t.saleDatev2=t.saleDatev2.replace("/","-")}else t.saleDatev2=null;var a=[];a=0===this.status.length?[99]:this.status,t.status=a,t.type=2,d["a"].post("/invoices/sendEmailsReport",t).then((function(t){var a=t.data;e.$q.notify({message:a.message.content,position:"top",color:a.result?"positive":"warning"}),a.result?(e.promptEmailPagos=!1,e.emailReport.fields.email_cliente="",e.emailReport.fields.customer_select=null,e.loadingSendingMailBtn=!1):e.loadingSendingMailBtn=!1}))}}},h=v,b=(a("1eb8"),a("2877")),g=a("9989"),_=a("ead5"),D=a("079e"),y=a("9c40"),q=a("05c0"),w=a("24e8"),C=a("f09f"),x=a("a370"),S=a("ddd8"),k=a("27f9"),E=a("4b7e"),O=a("7cbe"),P=a("52ee"),A=a("0016"),R=a("66e5"),T=a("4074"),$=a("0170"),N=a("9564"),I=a("eaac"),F=a("bd08"),Q=a("db86"),M=a("b047"),B=a("7f67"),G=a("eebe"),L=a.n(G),U=Object(b["a"])(h,s,i,!1,null,null,null);t["default"]=U.exports;L()(U,"components",{QPage:g["a"],QBreadcrumbs:_["a"],QBreadcrumbsEl:D["a"],QBtn:y["a"],QTooltip:q["a"],QDialog:w["a"],QCard:C["a"],QCardSection:x["a"],QSelect:S["a"],QInput:k["a"],QCardActions:E["a"],QPopupProxy:O["a"],QDate:P["a"],QIcon:A["a"],QItem:R["a"],QItemSection:T["a"],QItemLabel:$["a"],QToggle:N["a"],QTable:I["a"],QTr:F["a"],QTd:Q["a"],QChip:M["a"]}),L()(U,"directives",{ClosePopup:B["a"]})}}]);
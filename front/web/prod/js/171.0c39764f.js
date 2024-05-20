(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[171],{"9a58":function(t,e,a){"use strict";a.r(e);var l=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("q-page",{staticClass:"bg-grey-3"},[a("div",{staticClass:"q-pa-sm panel-header"},[a("div",{staticClass:"row"},[a("div",{staticClass:"col-sm-8"},[a("div",{staticClass:"q-pa-md q-gutter-sm"},[a("q-breadcrumbs",{staticStyle:{"font-size":"20px"},attrs:{align:"left"}},[a("q-breadcrumbs-el",{attrs:{label:"",icon:"home",to:"/"}}),a("q-breadcrumbs-el",{attrs:{label:"Existencias"}})],1)],1)]),a("div",{staticClass:"col-sm-4 pull-right "},[a("div",{staticClass:"q-pa-sm q-gutter-sm"},[a("q-btn",{attrs:{color:"positive",icon:"fas fa-file-csv"},on:{click:function(e){return t.generateCsv()}}},[a("q-tooltip",{attrs:{"content-class":"bg-purple-6"}},[t._v("Generar Reporte CSV")])],1),a("q-btn",{attrs:{color:"negative",icon:"fas fa-file-pdf"},on:{click:function(e){return t.generatePDF()}}},[a("q-tooltip",{attrs:{"content-class":"bg-purple-6"}},[t._v("Generar Reporte")])],1)],1)])])]),a("div",{staticClass:"q-pa-md bg-grey-3"},[a("div",{staticClass:"row bg-white border-panel"},[a("div",{staticClass:"col q-pa-md"},[a("div",{staticClass:"row q-mb-sm"},[a("div",{staticClass:"col-xs-12 col-md-3",staticStyle:{padding:"3px"}},[a("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",options:t.branchOfficeOptions,label:"Estación"},on:{input:function(){t.storage={value:null,label:"TODOS"}}},scopedSlots:t._u([{key:"prepend",fn:function(){return[a("q-icon",{attrs:{name:"fas fa-store-alt"}})]},proxy:!0}]),model:{value:t.branchOffice,callback:function(e){t.branchOffice=e},expression:"branchOffice"}})],1),a("div",{staticClass:"col-xs-12 col-md-3",staticStyle:{padding:"3px"}},[a("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",options:t.filteredStorageOptions,label:"Almacén"},scopedSlots:t._u([{key:"prepend",fn:function(){return[a("q-icon",{attrs:{name:"fas fa-warehouse"}})]},proxy:!0}]),model:{value:t.storage,callback:function(e){t.storage=e},expression:"storage"}})],1),a("div",{staticClass:"col-xs-12 col-md-3",staticStyle:{padding:"3px"}},[a("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",options:t.categoryOptions,label:"Categorías"},on:{input:function(){t.lines={value:null,label:"TODOS"}}},scopedSlots:t._u([{key:"prepend",fn:function(){return[a("q-icon",{attrs:{name:"fas fa-cubes"}})]},proxy:!0}]),model:{value:t.category,callback:function(e){t.category=e},expression:"category"}})],1),a("div",{staticClass:"col-xs-12 col-md-3",staticStyle:{padding:"3px"}},[a("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",options:t.filteredLineOptions,label:"Subcategoría"},scopedSlots:t._u([{key:"prepend",fn:function(){return[a("q-icon",{attrs:{name:"fas fa-grip-lines-vertical"}})]},proxy:!0}]),model:{value:t.line,callback:function(e){t.line=e},expression:"line"}})],1),a("div",{staticClass:"col-xs-12 col-md-5",staticStyle:{padding:"3px"}},[a("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"","use-input":"","hide-selected":"","fill-input":"","input-debounce":"0",options:t.filteredProductOptionsOut,label:"Producto"},on:{filter:t.filterProducts},scopedSlots:t._u([{key:"prepend",fn:function(){return[a("q-icon",{attrs:{name:"emoji_objects"}})]},proxy:!0}]),model:{value:t.product,callback:function(e){t.product=e},expression:"product"}})],1),a("div",{staticClass:"col-xs-12 col-md-3",staticStyle:{padding:"3px"}},[a("q-select",{attrs:{filled:"",color:"dark","bg-color":"secondary",label:"Marcas",options:t.options,"use-input":"","hide-selected":"","fill-input":""},on:{filter:t.filterMarcas},scopedSlots:t._u([{key:"prepend",fn:function(){return[a("q-icon",{attrs:{name:"fas fa-grip-lines-vertical"}})]},proxy:!0}]),model:{value:t.mark,callback:function(e){t.mark=e},expression:"mark"}})],1),a("div",{staticClass:"col-xs-12 col-md-12 pull-right",staticStyle:{padding:"3px"}},[a("q-btn",{staticStyle:{height:"96%"},attrs:{color:"primary",icon:"fas fa-search"},on:{click:function(e){return t.fetchFromServer()}}})],1),a("div",{staticClass:"col-xs-12 col-md-12 pull-right",staticStyle:{padding:"3px","padding-top":"10px"}},[a("label",{staticStyle:{"font-size":"18px","font-style":"italic",color:"#A9A9A9"}},[t._v("Nota: Los productos marcados en rojo, son productos inactivos.")])])]),a("br"),a("q-table",{attrs:{flat:"",bordered:"",data:t.data,columns:t.columns,"row-key":"serial",pagination:t.pagination},on:{"update:pagination":function(e){t.pagination=e},request:t.qTableRequest},scopedSlots:t._u([{key:"body",fn:function(e){return[a("q-tr",{attrs:{props:e}},[a("q-td",{key:"category",staticStyle:{"text-align":"left",width:"20%"},attrs:{props:e}},[t._v(t._s(e.row.category_name))]),a("q-td",{key:"line",staticStyle:{"text-align":"left",width:"20%"},attrs:{props:e}},[t._v(t._s(e.row.line_name))]),a("q-td",{key:"code",staticStyle:{"text-align":"center width: 10%"},attrs:{props:e}},[t._v(t._s(e.row.category_code+"-"+e.row.line_code+"-"+e.row.product_code))]),a("q-td",{key:"product",staticStyle:{"text-align":"left",width:"40%"},attrs:{props:e}},[t._v(t._s(e.row.product_name))]),a("q-td",{key:"almacen",staticStyle:{"text-align":"left",width:"40%"},attrs:{props:e}},[t._v(t._s(e.row.almacen))]),a("q-td",{key:"price",staticStyle:{"text-align":"right",width:"40%"},attrs:{props:e}},[t._v(t._s(e.row.price))]),3===e.row.category_id||6===e.row.category_id?a("q-td",{key:"stock",staticStyle:{"text-align":"right",width:"10%"},attrs:{props:e}},[a("label",{staticStyle:{"text-decoration":"underline",cursor:"pointer"},on:{click:function(a){return t.openModal(e.row.product_id,e.row.category_id)}}},[t._v(t._s(t.formatPrice(e.row.stock))+" ")])]):a("q-td",{key:"stock",staticStyle:{"text-align":"right",width:"10%"},attrs:{props:e}},[a("label",[t._v(t._s(t.formatPrice(e.row.stock))+" ")])])],1)]}}])})],1)])]),a("q-dialog",{model:{value:t.modalProducts,callback:function(e){t.modalProducts=e},expression:"modalProducts"}},[a("q-card",{staticStyle:{"min-width":"1100px"}},[a("div",[a("div",{staticClass:"bg-primary"},[a("div",{staticClass:"row"},[a("div",{staticClass:"col-sm-11 text-h6",staticStyle:{color:"white"}},[t._v("  Datos del Producto")]),a("div",{staticClass:"col-sm-1 pull-right"},[a("q-btn",{attrs:{color:"white",flat:"",round:"",dense:"",icon:"close"},nativeOn:{click:function(e){return t.closeModal()}}})],1)])])]),a("div",{staticClass:"row",staticStyle:{"padding-top":"10px"}},[a("div",{staticClass:"col-md-3",staticStyle:{"margin-left":"35px"}},[a("q-input",{attrs:{color:"white","bg-color":"primary",filled:"",dark:"",disable:"true",label:"Categoria"},model:{value:t.productInfo.fields.category,callback:function(e){t.$set(t.productInfo.fields,"category",e)},expression:"productInfo.fields.category"}})],1),a("div",{staticClass:"col-md-1"}),a("div",{staticClass:"col-md-3"},[a("q-input",{attrs:{color:"white","bg-color":"primary",filled:"",dark:"",disable:"true",label:"Linea"},model:{value:t.productInfo.fields.line,callback:function(e){t.$set(t.productInfo.fields,"line",e)},expression:"productInfo.fields.line"}})],1),a("div",{staticClass:"col-md-1"}),a("div",{staticClass:"col-md-3"},[a("q-input",{attrs:{color:"white","bg-color":"primary",filled:"",dark:"",disable:"true",label:"Producto"},model:{value:t.productInfo.fields.product,callback:function(e){t.$set(t.productInfo.fields,"product",e)},expression:"productInfo.fields.product"}})],1)]),a("div",{staticClass:"row",staticStyle:{"padding-top":"10px"}},[a("q-separator",{staticStyle:{"margin-top":"10px"},attrs:{inset:""}}),a("div",{staticClass:"col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12",staticStyle:{"margin-top":"10px"}},[a("div",{staticClass:"row q-col-gutter-md"},[a("div",{staticClass:"col-md-2",staticStyle:{"text-align":"center"}},["PET PROCESADO"===t.productInfo.fields.category||"PET BOTELLA"===t.productInfo.fields.category?a("label",{staticStyle:{"font-size":"14px","font-weight":"bold"}},[t._v("JUMBO")]):t._e(),"FIBRAS"===t.productInfo.fields.category?a("label",{staticStyle:{"font-size":"14px","font-weight":"bold"}},[t._v("PACA")]):t._e()]),a("div",{staticClass:"col-md-2",staticStyle:{"text-align":"center"}},[a("label",{staticStyle:{"font-size":"14px","font-weight":"bold"}},[t._v("PESO")])]),a("div",{staticClass:"col-md-3",staticStyle:{"text-align":"center"}},[a("label",{staticStyle:{"font-size":"14px","font-weight":"bold"}},[t._v("SUCURSAL")])]),a("div",{staticClass:"col-md-4",staticStyle:{"text-align":"center"}},[a("label",{staticStyle:{"font-size":"14px","font-weight":"bold"}},[t._v("ALMACÉN")])]),a("div",{staticClass:"col-md-1",staticStyle:{"text-align":"center"}},[a("label",{staticStyle:{"font-size":"14px","font-weight":"bold"}})])]),a("div",{staticStyle:{"overflow-y":"auto",height:"350px"}},t._l(t.productData,(function(e){return a("div",{key:e.id,staticClass:"col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12"},[a("div",{staticClass:"row q-col-gutter-md"},[a("div",{staticClass:"col-md-2",staticStyle:{"text-align":"center"}},[a("label",[t._v(t._s(e.id))])]),a("div",{staticClass:"col-md-2",staticStyle:{"text-align":"center"}},[a("label",[t._v(t._s(t.formatter.format(e.qty)+" KG."))])]),a("div",{staticClass:"col-md-3",staticStyle:{"text-align":"center"}},[a("label",[t._v(t._s(e.office))])]),a("div",{staticClass:"col-md-4",staticStyle:{"text-align":"center"}},[a("label",[t._v(t._s(e.sucursal))])]),a("div",{staticClass:"col-md-1",staticStyle:{"text-align":"center"}})])])})),0)]),a("q-separator",{staticStyle:{"margin-top":"10px"},attrs:{inset:""}}),a("div",{staticClass:"col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12",staticStyle:{"margin-top":"10px"}},[a("div",{staticClass:"row"},[a("div",{staticClass:"col-md-7"}),a("div",{staticClass:"col-md-4 pull-right"},[a("label",{staticStyle:{"font-size":"20px"}},[t._v("Peso: "+t._s(t.formatter.format(t.totalQtys)+" KG"))])]),a("div",{staticClass:"col-md-1"})])])],1),a("q-card-actions",{attrs:{align:"right"}},[a("br")])],1)],1)],1)},o=[],i=a("7ec2"),s=a.n(i),n=a("c973"),r=a.n(n),c=(a("a9e3"),a("4de4"),a("d3b7"),a("159b"),a("ac1f"),a("5319"),a("b680"),a("25f0"),a("caad"),a("2532"),a("99af"),a("aabb")),d={name:"StorageInventory",data:function(){return{formatter:new Intl.NumberFormat("en-US"),branchOffice:{value:null,label:"TODAS"},storage:{value:null,label:"TODOS"},category:{value:null,label:"TODAS"},line:{value:null,label:"TODAS"},product:{value:null,label:"TODOS"},date:null,branchOfficeOptions:[],storageOptions:[],categoryOptions:[],lineOptions:[],productOptions:[],options:this.markOptions,markOptions:[],productInfo:{fields:{category:null,line:null,product:null}},mark:"null",productData:[],modalProducts:!1,pagination:{sortBy:"code",descending:!0,page:1,rowsNumber:0,rowsPerPage:25},columns:[{name:"category",align:"center",label:"CATEGORÍAS",field:"category",style:"width: 20%",sortable:!0},{name:"line",align:"center",label:"SUBCATEGORÍA",field:"line",style:"width: 20%",sortable:!0},{name:"code",align:"center",label:"CÓDIGO",field:"code",style:"width: 10%",sortable:!0},{name:"product",align:"center",label:"PRODUCTO",field:"product",style:"width: 40%",sortable:!0},{name:"almacen",align:"center",label:"ALMACEN",field:"almacen",style:"width: 40%",sortable:!0},{name:"price",align:"center",label:"ÚLTIMO PRECIO",field:"price",style:"width: 40%",sortable:!0},{name:"stock",align:"center",label:"EXISTENCIA",field:"stock",style:"width: 10%",sortable:!0,sort:function(t,e){return Number(t,10)-Number(e,10)}}],data:[],fil:{flagOption:!1},filteredProductOptionsOut:[],filter:""}},computed:{filteredStorageOptions:function(){var t=this,e=null!=this.branchOffice&&null!=this.branchOffice.value?this.storageOptions.filter((function(e){return Number(e.branchOffice)===Number(t.branchOffice.value)})):this.storageOptions;return e.unshift({value:null,label:"TODOS"}),e},filteredLineOptions:function(){var t=this,e=null!=this.category&&null!=this.category.value?this.lineOptions.filter((function(e){return Number(e.category)===Number(t.category.value)})):this.lineOptions;return e.unshift({value:null,label:"TODOS"}),e},filteredProductOptions:function(){var t=this,e=null!=this.line&&null!=this.line.value?this.productOptions.filter((function(e){return Number(e.line)===Number(t.line.value)})):this.productOptions;return e.unshift({value:null,label:"TODOS"}),e},totalQtys:function(){var t=0;return this.productData.forEach((function(e){t+=Number(e.qty)})),t}},beforeRouteEnter:function(t,e,a){a((function(t){var e=t.$store.getters["users/rol"];console.log(e),1===e||3===e||7===e||2===e||20===e||4===e||27===e||22===e||26===e?a():a("/")}))},created:function(){var t=this;this.$q.loading.show(),this.$q.loading.hide(),this.getMarks(),c["a"].get("/branch-offices/options").then((function(e){var a=e.data;t.branchOfficeOptions=a.options,t.branchOfficeOptions.unshift({value:null,label:"TODOS"}),c["a"].get("/storages/options").then((function(e){var a=e.data;t.storageOptions=a.options,c["a"].get("/categories/options").then((function(e){var a=e.data;t.categoryOptions=a.options,t.categoryOptions.unshift({value:null,label:"TODAS"}),c["a"].get("/lines/options").then((function(e){var a=e.data;t.lineOptions=a.options,c["a"].get("/products/options").then((function(e){var a=e.data;t.productOptions=a.options}))}))}))}))}))},methods:{getMarks:function(){var t=this;this.mark={label:"TODOS",value:null},c["a"].get("/marks/options").then((function(e){var a=e.data;t.markOptions=a.options,t.markOptions.unshift({value:null,label:"TODOS"})}))},exportPDF2:function(){c["a"].get("mail/generatePDF")},filterMarcas:function(t,e,a){var l=this;e((function(){var e=t.toLowerCase();l.options=l.markOptions.filter((function(t){return t.label.toLowerCase().indexOf(e)>-1}))}))},filterProducts:function(t,e,a){var l=this;e((function(){var e=t.toLowerCase();l.filteredProductOptionsOut=l.filteredProductOptions.filter((function(t){return t.label.toLowerCase().indexOf(e)>-1}))}))},formatPrice:function(t){var e=(t/1).toFixed(3).replace(",",".");return e.toString().replace(/\B(?=(\d{3})+(?!\d))/g,",")},fetchFromServer:function(){this.$q.loading.show(),this.qTableRequest({pagination:this.pagination,filter:this.filter})},qTableRequest:function(t){var e=this;return r()(s()().mark((function a(){var l,o,i,n,r;return s()().wrap((function(a){while(1)switch(a.prev=a.next){case 0:return e.$q.loading.show(),e.pagination=t.pagination,e.filter=t.filter,e.products=[],l=[],o=null,i=null,n=null,r=null,e.branchOffice&&(o=e.branchOffice.value),e.storage&&(i=e.storage.value),e.product&&(n=e.product.value),e.mark&&(r=e.mark.value),l.branchOfficeId=o,l.storageId=i,l.categoryId=e.category.value,l.lineId=e.line.value,l.status=!0,l.pagination=e.pagination,l.filter=e.filter,l.product=n,l.mark=r,a.next=24,c["a"].post("/movements/storageInventoryByMark",l).then((function(t){var a=t.data;console.log(a),e.$q.loading.hide(),e.data=a.stock.data,e.pagination.rowsNumber=a.stock.rowCounts})).catch((function(t){return t}));case 24:case"end":return a.stop()}}),a)})))()},searchStock:function(){var t=this;this.$q.loading.show();var e=null,a=null,l=null,o=null,i=null,s=null,n=null;if(this.branchOffice&&(e=this.branchOffice.value),this.storage&&(a=this.storage.value),this.category&&(l=this.category.value),this.line&&(o=this.line.value),this.product&&(i=this.product.value),this.date){s=this.date;while(s.includes("/"))s=s.replace("/","-")}n=this.mark.value,c["a"].get("/movements/storageInventoryv2/".concat(e,"/").concat(a,"/").concat(l,"/").concat(o,"/").concat(i,"/").concat(s,"/").concat(n)).then((function(e){var a=e.data;a.result&&(t.data=a.stock),t.$q.loading.hide()}))},openModal:function(t,e){var a=this,l=[];l.product=t,l.category=e,l.branchoffice=this.branchOffice.value,l.storage=this.storage.value,c["a"].post("/movements/getDataProducts",l).then((function(t){var e=t.data;e.result&&(a.productInfo.fields.category=e.product[0].category,a.productInfo.fields.line=e.product[0].line,a.productInfo.fields.product=e.product[0].product,a.productData=e.productData,a.modalProducts=!0)}))},closeModal:function(){this.modalProducts=!1},generatePDF:function(){var t=this.$store.getters["users/id"],e=null,a=null,l=null,o=null,i=null,s=null,n=null;if(this.branchOffice&&(e=this.branchOffice.value),this.storage&&(a=this.storage.value),this.category&&(l=this.category.value),this.line&&(o=this.line.value),this.product&&(i=this.product.value),this.date){s=this.date;while(s.includes("/"))s=s.replace("/","-")}this.mark&&(n=this.mark.value);var r="https://api_alpez.wasp.mx/"+"movements/inventorybymark/pdf/".concat(e,"/").concat(a,"/").concat(l,"/").concat(o,"/").concat(i,"/").concat(t,"/").concat(n);window.open(r,"_blank")},generateCsv:function(){var t=this.$store.getters["users/id"],e=null,a=null,l=null,o=null,i=null,s=null,n=null;if(this.branchOffice&&(e=this.branchOffice.value),this.storage&&(a=this.storage.value),this.category&&(l=this.category.value),this.line&&(o=this.line.value),this.product&&(i=this.product.value),this.date){s=this.date;while(s.includes("/"))s=s.replace("/","-")}this.mark&&(n=this.mark.value);var r="https://api_alpez.wasp.mx/"+"movements/inventorybymark/csv/".concat(e,"/").concat(a,"/").concat(l,"/").concat(o,"/").concat(i,"/").concat(t,"/").concat(n);window.open(r,"_blank")}}},u=d,p=a("2877"),f=a("9989"),g=a("ead5"),h=a("079e"),v=a("9c40"),b=a("05c0"),m=a("ddd8"),y=a("0016"),O=a("eaac"),S=a("bd08"),x=a("db86"),w=a("24e8"),k=a("f09f"),C=a("27f9"),q=a("eb85"),_=a("4b7e"),P=a("eebe"),I=a.n(P),D=Object(p["a"])(u,l,o,!1,null,null,null);e["default"]=D.exports;I()(D,"components",{QPage:f["a"],QBreadcrumbs:g["a"],QBreadcrumbsEl:h["a"],QBtn:v["a"],QTooltip:b["a"],QSelect:m["a"],QIcon:y["a"],QTable:O["a"],QTr:S["a"],QTd:x["a"],QDialog:w["a"],QCard:k["a"],QInput:C["a"],QSeparator:q["a"],QCardActions:_["a"]})}}]);
(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[172],{b221:function(t,e,a){"use strict";a.r(e);var l=function(){var t=this,e=t._self._c;return e("q-page",{staticClass:"bg-grey-3"},[e("div",{staticClass:"q-pa-sm panel-header"},[e("div",{staticClass:"row"},[e("div",{staticClass:"col-sm-8"},[e("div",{staticClass:"q-pa-md q-gutter-sm"},[e("q-breadcrumbs",{staticStyle:{"font-size":"20px"},attrs:{align:"left"}},[e("q-breadcrumbs-el",{attrs:{label:"",icon:"home",to:"/"}}),e("q-breadcrumbs-el",{attrs:{label:"Stock Minimo"}})],1)],1)]),e("div",{staticClass:"col-sm-4 pull-right"},[e("div",{staticClass:"q-pa-sm q-gutter-sm"},[e("q-btn",{attrs:{color:"positive",icon:"fas fa-file-csv"},on:{click:function(e){return t.generateCsv()}}},[e("q-tooltip",{attrs:{"content-class":"bg-purple-6"}},[t._v("Generar Reporte CSV")])],1),e("q-btn",{attrs:{color:"negative",icon:"fas fa-file-pdf"},on:{click:function(e){return t.generatePDF()}}},[e("q-tooltip",{attrs:{"content-class":"bg-purple-6"}},[t._v("Generar Reporte")])],1)],1)])])]),e("div",{staticClass:"q-pa-md bg-grey-3"},[e("div",{staticClass:"row bg-white border-panel"},[e("div",{staticClass:"col q-pa-md"},[e("div",{staticClass:"row q-mb-sm"},[e("div",{staticClass:"col-xs-12 col-md-3",staticStyle:{padding:"3px"}},[e("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",options:t.branchOfficeOptions,label:"Estación"},on:{input:function(){t.storage={value:null,label:"TODOS"}}},scopedSlots:t._u([{key:"prepend",fn:function(){return[e("q-icon",{attrs:{name:"fas fa-store-alt"}})]},proxy:!0}]),model:{value:t.branchOffice,callback:function(e){t.branchOffice=e},expression:"branchOffice"}})],1),e("div",{staticClass:"col-xs-12 col-md-3",staticStyle:{padding:"3px"}},[e("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",options:t.filteredStorageOptions,label:"Almacén"},scopedSlots:t._u([{key:"prepend",fn:function(){return[e("q-icon",{attrs:{name:"fas fa-warehouse"}})]},proxy:!0}]),model:{value:t.storage,callback:function(e){t.storage=e},expression:"storage"}})],1),e("div",{staticClass:"col-xs-12 col-md-3",staticStyle:{padding:"3px"}},[e("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",options:t.categoryOptions,label:"Categorías"},on:{input:function(){t.lines={value:null,label:"TODOS"}}},scopedSlots:t._u([{key:"prepend",fn:function(){return[e("q-icon",{attrs:{name:"fas fa-cubes"}})]},proxy:!0}]),model:{value:t.category,callback:function(e){t.category=e},expression:"category"}})],1),e("div",{staticClass:"col-xs-12 col-md-3",staticStyle:{padding:"3px"}},[e("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",options:t.filteredLineOptions,label:" Subcategoría"},scopedSlots:t._u([{key:"prepend",fn:function(){return[e("q-icon",{attrs:{name:"fas fa-grip-lines-vertical"}})]},proxy:!0}]),model:{value:t.line,callback:function(e){t.line=e},expression:"line"}})],1),e("div",{staticClass:"col-xs-12 col-md-5",staticStyle:{padding:"3px"}},[e("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"","use-input":"","hide-selected":"","fill-input":"","input-debounce":"0",options:t.filteredProductOptionsOut,label:"Producto"},on:{filter:t.filterProducts},scopedSlots:t._u([{key:"prepend",fn:function(){return[e("q-icon",{attrs:{name:"emoji_objects"}})]},proxy:!0}]),model:{value:t.product,callback:function(e){t.product=e},expression:"product"}})],1),e("div",{staticClass:"col-xs-12 col-md-3",staticStyle:{padding:"3px"}},[e("q-select",{attrs:{filled:"",color:"dark","bg-color":"secondary",label:"Marcas",options:t.optionsmarks,"use-input":"","hide-selected":"","fill-input":""},on:{filter:t.filterMarcas,input:function(e){return t.fetchFromServer()}},scopedSlots:t._u([{key:"prepend",fn:function(){return[e("q-icon",{attrs:{name:"fas fa-grip-lines-vertical"}})]},proxy:!0}]),model:{value:t.mark,callback:function(e){t.mark=e},expression:"mark"}})],1),e("div",{staticClass:"col-xs-12 col-md-12 pull-right",staticStyle:{padding:"3px"}},[e("q-btn",{staticStyle:{height:"96%"},attrs:{color:"primary",icon:"fas fa-search"},on:{click:function(e){return t.fetchFromServer()}}})],1)]),e("br"),e("q-table",{attrs:{flat:"",bordered:"",data:t.data,columns:t.columns,"row-key":"serial",pagination:t.pagination},on:{"update:pagination":function(e){t.pagination=e}},scopedSlots:t._u([{key:"body",fn:function(a){return[e("q-tr",{attrs:{props:a}},[e("q-td",{key:"category",staticStyle:{"text-align":"left",width:"20%"},attrs:{props:a}},[t._v(t._s(a.row.category))]),e("q-td",{key:"line",staticStyle:{"text-align":"left",width:"20%"},attrs:{props:a}},[t._v(t._s(a.row.line))]),e("q-td",{key:"code",staticStyle:{"text-align":"center width: 10%"},attrs:{props:a}},[t._v(t._s(a.row.category_code+"-"+a.row.line_code+"-"+a.row.product_code))]),e("q-td",{key:"branch_office_name",staticStyle:{"text-align":"left",width:"20%"},attrs:{props:a}},[t._v(t._s(a.row.branch_office_name))]),e("q-td",{key:"storage_name",staticStyle:{"text-align":"left",width:"20%"},attrs:{props:a}},[t._v(t._s(a.row.storage_name))]),e("q-td",{key:"marca",staticStyle:{"text-align":"left",width:"20%"},attrs:{props:a}},[t._v(t._s(a.row.marca))]),e("q-td",{key:"product",staticStyle:{"text-align":"left",width:"40%"},attrs:{props:a}},[t._v(t._s(a.row.product_name))]),e("q-td",{key:"minimal_stock",staticStyle:{"text-align":"right",width:"10%"},attrs:{props:a}},[e("label",[t._v(t._s(t.formatPrice(a.row.minimal_stock))+" ")])]),a.row.minimal_stock<=a.row.stock?e("q-td",{key:"stock",staticStyle:{"text-align":"right",width:"10%","background-color":"#21BA45",color:"white"},attrs:{props:a}},[e("label",[t._v(t._s(t.formatPrice(a.row.stock))+" ")])]):e("q-td",{key:"stock",staticStyle:{"text-align":"right",width:"10%","background-color":"#C10015",color:"white"},attrs:{props:a}},[e("label",[t._v(t._s(t.formatPrice(a.row.stock))+" ")])])],1)]}}])})],1)])]),e("q-dialog",{model:{value:t.modalProducts,callback:function(e){t.modalProducts=e},expression:"modalProducts"}},[e("q-card",{staticStyle:{"min-width":"1100px"}},[e("div",[e("div",{staticClass:"bg-primary"},[e("div",{staticClass:"row"},[e("div",{staticClass:"col-sm-11 text-h6",staticStyle:{color:"white"}},[t._v("  Datos del Producto")]),e("div",{staticClass:"col-sm-1 pull-right"},[e("q-btn",{attrs:{color:"white",flat:"",round:"",dense:"",icon:"close"},nativeOn:{click:function(e){return t.closeModal()}}})],1)])])]),e("div",{staticClass:"row",staticStyle:{"padding-top":"10px"}},[e("div",{staticClass:"col-md-3",staticStyle:{"margin-left":"35px"}},[e("q-input",{attrs:{color:"white","bg-color":"primary",filled:"",dark:"",disable:"true",label:"Categoria"},model:{value:t.productInfo.fields.category,callback:function(e){t.$set(t.productInfo.fields,"category",e)},expression:"productInfo.fields.category"}})],1),e("div",{staticClass:"col-md-1"}),e("div",{staticClass:"col-md-3"},[e("q-input",{attrs:{color:"white","bg-color":"primary",filled:"",dark:"",disable:"true",label:"Linea"},model:{value:t.productInfo.fields.line,callback:function(e){t.$set(t.productInfo.fields,"line",e)},expression:"productInfo.fields.line"}})],1),e("div",{staticClass:"col-md-1"}),e("div",{staticClass:"col-md-3"},[e("q-input",{attrs:{color:"white","bg-color":"primary",filled:"",dark:"",disable:"true",label:"Producto"},model:{value:t.productInfo.fields.product,callback:function(e){t.$set(t.productInfo.fields,"product",e)},expression:"productInfo.fields.product"}})],1)]),e("div",{staticClass:"row",staticStyle:{"padding-top":"10px"}},[e("q-separator",{staticStyle:{"margin-top":"10px"},attrs:{inset:""}}),e("div",{staticClass:"col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12",staticStyle:{"margin-top":"10px"}},[e("div",{staticClass:"row q-col-gutter-md"},[e("div",{staticClass:"col-md-2",staticStyle:{"text-align":"center"}},["PET PROCESADO"===t.productInfo.fields.category||"PET BOTELLA"===t.productInfo.fields.category?e("label",{staticStyle:{"font-size":"14px","font-weight":"bold"}},[t._v("JUMBO")]):t._e(),"FIBRAS"===t.productInfo.fields.category?e("label",{staticStyle:{"font-size":"14px","font-weight":"bold"}},[t._v("PACA")]):t._e()]),e("div",{staticClass:"col-md-2",staticStyle:{"text-align":"center"}},[e("label",{staticStyle:{"font-size":"14px","font-weight":"bold"}},[t._v("PESO")])]),e("div",{staticClass:"col-md-3",staticStyle:{"text-align":"center"}},[e("label",{staticStyle:{"font-size":"14px","font-weight":"bold"}},[t._v("SUCURSAL")])]),e("div",{staticClass:"col-md-4",staticStyle:{"text-align":"center"}},[e("label",{staticStyle:{"font-size":"14px","font-weight":"bold"}},[t._v("ALMACÉN")])]),e("div",{staticClass:"col-md-1",staticStyle:{"text-align":"center"}},[e("label",{staticStyle:{"font-size":"14px","font-weight":"bold"}})])]),e("div",{staticStyle:{"overflow-y":"auto",height:"350px"}},t._l(t.productData,(function(a){return e("div",{key:a.id,staticClass:"col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12"},[e("div",{staticClass:"row q-col-gutter-md"},[e("div",{staticClass:"col-md-2",staticStyle:{"text-align":"center"}},[e("label",[t._v(t._s(a.id))])]),e("div",{staticClass:"col-md-2",staticStyle:{"text-align":"center"}},[e("label",[t._v(t._s("".concat(t.formatter.format(a.qty)," KG.")))])]),e("div",{staticClass:"col-md-3",staticStyle:{"text-align":"center"}},[e("label",[t._v(t._s(a.office))])]),e("div",{staticClass:"col-md-4",staticStyle:{"text-align":"center"}},[e("label",[t._v(t._s(a.sucursal))])]),e("div",{staticClass:"col-md-1",staticStyle:{"text-align":"center"}})])])})),0)]),e("q-separator",{staticStyle:{"margin-top":"10px"},attrs:{inset:""}}),e("div",{staticClass:"col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12",staticStyle:{"margin-top":"10px"}},[e("div",{staticClass:"row"},[e("div",{staticClass:"col-md-7"}),e("div",{staticClass:"col-md-4 pull-right"},[e("label",{staticStyle:{"font-size":"20px"}},[t._v("Peso: "+t._s("".concat(t.formatter.format(t.totalQtys)," KG")))])]),e("div",{staticClass:"col-md-1"})])])],1),e("q-card-actions",{attrs:{align:"right"}},[e("br")])],1)],1)],1)},o=[],i=a("7ec2"),s=a.n(i),n=a("c973"),r=a.n(n),c=(a("a9e3"),a("4de4"),a("d3b7"),a("3c65"),a("159b"),a("ac1f"),a("5319"),a("b680"),a("25f0"),a("caad"),a("2532"),a("99af"),a("aabb")),d={name:"StorageInventory",data:function(){return{formatter:new Intl.NumberFormat("en-US"),branchOffice:{value:null,label:"TODAS"},storage:{value:null,label:"TODOS"},category:{value:null,label:"TODAS"},line:{value:null,label:"TODAS"},product:{value:null,label:"TODOS"},optionsmarks:this.markOptions,markOptions:[],date:null,filter:"",branchOfficeOptions:[],storageOptions:[],categoryOptions:[],lineOptions:[],productOptions:[],mark:null,productInfo:{fields:{category:null,line:null,product:null}},productData:[],modalProducts:!1,pagination:{sortBy:"code",descending:!0,page:1,rowsNumber:0,rowsPerPage:25},columns:[{name:"category",align:"center",label:"CATEGORÍAS",field:"category",style:"width: 20%",sortable:!0},{name:"line",align:"center",label:"SUBCATEGORÍA",field:"line",style:"width: 20%",sortable:!0},{name:"code",align:"center",label:"CÓDIGO",field:"code",style:"width: 10%",sortable:!0},{name:"branch_office_name",align:"center",label:"ESTACIÓN",field:"branch_office_name",style:"width: 10%",sortable:!0},{name:"storage_name",align:"center",label:"ALMACÉN",field:"storage_name",style:"width: 10%",sortable:!0},{name:"marca",align:"center",label:"MARCA",field:"marca",style:"width: 10%",sortable:!0},{name:"product",align:"center",label:"PRODUCTO",field:"product",style:"width: 40%",sortable:!0},{name:"minimal_stock",align:"center",label:"STOCK MINIMO",field:"minimal_stock",style:"width: 10%",sortable:!0,sort:function(t,e){return Number(t,10)-Number(e,10)}},{name:"stock",align:"center",label:"EXISTENCIA",field:"stock",style:"width: 10%",sortable:!0,sort:function(t,e){return Number(t,10)-Number(e,10)}}],data:[],fil:{flagOption:!1},filteredProductOptionsOut:[]}},computed:{filteredStorageOptions:function(){var t=this,e=null!=this.branchOffice&&null!=this.branchOffice.value?this.storageOptions.filter((function(e){return Number(e.branchOffice)===Number(t.branchOffice.value)})):this.storageOptions;return e.unshift({value:null,label:"TODOS"}),e},filteredLineOptions:function(){var t=this,e=null!=this.category&&null!=this.category.value?this.lineOptions.filter((function(e){return Number(e.category)===Number(t.category.value)})):this.lineOptions;return e},filteredProductOptions:function(){var t=this,e=null!=this.line&&null!=this.line.value?this.productOptions.filter((function(e){return Number(e.line)===Number(t.line.value)})):this.productOptions;return e.unshift({value:null,label:"TODOS"}),e},totalQtys:function(){var t=0;return this.productData.forEach((function(e){t+=Number(e.qty)})),t}},beforeRouteEnter:function(t,e,a){a((function(t){var e=t.$store.getters["users/rol"];console.log(e),1===e||3===e||7===e||2===e||20===e||4===e||27===e||22===e||26===e?a():a("/")}))},created:function(){var t=this;this.$q.loading.show(),this.$q.loading.hide(),this.fetchFromServer(),c["a"].get("/branch-offices/options").then((function(e){var a=e.data;t.branchOfficeOptions=a.options,t.branchOfficeOptions.unshift({value:null,label:"TODOS"}),c["a"].get("/storages/options").then((function(e){var a=e.data;t.storageOptions=a.options,c["a"].get("/categories/options").then((function(e){var a=e.data;t.categoryOptions=a.options,t.categoryOptions.unshift({value:null,label:"TODAS"}),c["a"].get("/lines/options").then((function(e){var a=e.data;t.lineOptions=a.options,c["a"].get("/products/options").then((function(e){var a=e.data;t.productOptions=a.options}))}))}))}))}))},mounted:function(){this.getMarks()},methods:{filterMarcas:function(t,e,a){var l=this;e((function(){var e=t.toLowerCase();l.optionsmarks=l.markOptions.filter((function(t){return t.label.toLowerCase().indexOf(e)>-1}))}))},filterProducts:function(t,e,a){var l=this;e((function(){var e=t.toLowerCase();l.filteredProductOptionsOut=l.filteredProductOptions.filter((function(t){return t.label.toLowerCase().indexOf(e)>-1}))}))},formatPrice:function(t){var e=(t/1).toFixed(3).replace(",",".");return e.toString().replace(/\B(?=(\d{3})+(?!\d))/g,",")},fetchFromServer:function(){var t=this.$store.getters["users/branch"],e=this.$store.getters["users/rol"];9===t&&1!==e&&(this.branchOffice={value:9,label:"MATRIZ"}),12===t&&1!==e&&(this.branchOffice={value:12,label:"LOPEZ DE LARA TINAJERO GUILLERMO"}),this.$q.loading.show(),this.qTableRequest({pagination:this.pagination,filter:this.filter})},qTableRequest:function(t){var e=this;return r()(s()().mark((function a(){var l,o,i,n,r;return s()().wrap((function(a){while(1)switch(a.prev=a.next){case 0:return e.pagination=t.pagination,e.filter=t.filter,e.products=[],l=[],o=null,i=null,n=null,r=null,e.branchOffice&&(o=e.branchOffice.value),e.storage&&(i=e.storage.value),e.product&&(n=e.product.value),e.mark&&(r=e.mark.value),l.branchOfficeId=o,l.storageId=i,l.categoryId=e.category.value,l.lineId=e.line.value,l.status=!0,l.pagination=e.pagination,l.filter=e.filter,l.product=n,l.mark=r,a.next=23,c["a"].post("/movements/storageInventoryMinimal",l).then((function(t){var a=t.data;console.log(a),e.$q.loading.hide(),e.data=a.stock.data,e.pagination.rowsNumber=a.stock.rowCounts})).catch((function(t){return t}));case 23:case"end":return a.stop()}}),a)})))()},searchStock:function(){var t=this;this.$q.loading.show();var e=null,a=null,l=null,o=null,i=null,s=null;if(this.branchOffice&&(e=this.branchOffice.value),this.storage&&(a=this.storage.value),this.category&&(l=this.category.value),this.line&&(o=this.line.value),this.product&&(i=this.product.value),this.date){s=this.date;while(s.includes("/"))s=s.replace("/","-")}c["a"].get("/movements/storageInventoryMinimal/".concat(e,"/").concat(a,"/").concat(l,"/").concat(o,"/").concat(i,"/").concat(s)).then((function(e){var a=e.data;a.result&&(t.data=a.stock),t.$q.loading.hide()}))},openModal:function(t,e){var a=this,l=[];l.product=t,l.category=e,l.branchoffice=this.branchOffice.value,l.storage=this.storage.value,c["a"].post("/movements/getDataProducts",l).then((function(t){var e=t.data;e.result&&(a.productInfo.fields.category=e.product[0].category,a.productInfo.fields.line=e.product[0].line,a.productInfo.fields.product=e.product[0].product,a.productData=e.productData,a.modalProducts=!0)}))},closeModal:function(){this.modalProducts=!1},getMarks:function(){var t=this;this.mark={label:"TODOS",value:null},c["a"].get("/marks/options").then((function(e){var a=e.data;t.markOptions=a.options,t.markOptions.unshift({value:null,label:"TODOS"})}))},generatePDF:function(){var t=this.$store.getters["users/id"],e=null,a=null,l=null,o=null,i=null,s=null,n=null;if(this.branchOffice&&(e=this.branchOffice.value),this.storage&&(a=this.storage.value),this.category&&(l=this.category.value),this.line&&(o=this.line.value),this.product&&(i=this.product.value),this.date){s=this.date;while(s.includes("/"))s=s.replace("/","-")}this.mark&&(n=this.mark.value);var r="http://api.alpez.beta.wasp.mx/"+"movements/inventory-minimal-stock/pdf/".concat(e,"/").concat(a,"/").concat(l,"/").concat(o,"/").concat(i,"/").concat(t,"/").concat(n);window.open(r,"_blank")},generateCsv:function(){var t=this.$store.getters["users/id"],e=null,a=null,l=null,o=null,i=null,s=null,n=null;if(this.branchOffice&&(e=this.branchOffice.value),this.storage&&(a=this.storage.value),this.category&&(l=this.category.value),this.line&&(o=this.line.value),this.product&&(i=this.product.value),this.date){s=this.date;while(s.includes("/"))s=s.replace("/","-")}this.mark&&(n=this.mark.value);var r="http://api.alpez.beta.wasp.mx/"+"movements/inventory-minimal-stock/csv/".concat(e,"/").concat(a,"/").concat(l,"/").concat(o,"/").concat(i,"/").concat(t,"/").concat(n);window.open(r,"_blank")}}},u=d,f=a("2877"),p=a("9989"),g=a("ead5"),h=a("079e"),m=a("9c40"),b=a("05c0"),v=a("ddd8"),y=a("0016"),O=a("eaac"),k=a("bd08"),S=a("db86"),w=a("24e8"),x=a("f09f"),C=a("27f9"),_=a("eb85"),q=a("4b7e"),I=a("eebe"),P=a.n(I),T=Object(f["a"])(u,l,o,!1,null,null,null);e["default"]=T.exports;P()(T,"components",{QPage:p["a"],QBreadcrumbs:g["a"],QBreadcrumbsEl:h["a"],QBtn:m["a"],QTooltip:b["a"],QSelect:v["a"],QIcon:y["a"],QTable:O["a"],QTr:k["a"],QTd:S["a"],QDialog:w["a"],QCard:x["a"],QInput:C["a"],QSeparator:_["a"],QCardActions:q["a"]})}}]);
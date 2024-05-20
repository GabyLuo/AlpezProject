(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[5],{"45a8":function(e,t,l){"use strict";l.r(t);var a=function(){var e=this,t=this,l=t.$createElement,a=t._self._c||l;return a("q-page",[a("div",{staticClass:"q-pa-sm panel-header"},[a("div",{staticClass:"row"},[a("div",{staticClass:"col-sm-8"},[a("div",{staticClass:"q-pa-md q-gutter-sm"},[a("q-breadcrumbs",{staticStyle:{"font-size":"20px"},attrs:{align:"left"}},[a("q-breadcrumbs-el",{attrs:{label:"",icon:"home",to:"/"}}),a("q-breadcrumbs-el",{attrs:{label:"Tracking de Sacos"}})],1)],1)])])]),a("div",{staticClass:"q-pa-md bg-grey-3"},[a("div",{staticClass:"row bg-white border-panel"},[a("div",{staticClass:"col q-pa-md"},[a("div",{staticClass:"row q-mb-sm"},[a("div",{staticClass:"col-xs-12 col-md-3",staticStyle:{padding:"3px"}},[a("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",options:t.categoryOptions,label:"Categoría"},on:{input:function(){t.line={value:null,label:"Seleccione la línea"},t.product={value:null,label:"Seleccione el producto"},t.bag={value:null,label:"Seleccione el saco"}}},scopedSlots:t._u([{key:"prepend",fn:function(){return[a("q-icon",{attrs:{name:"fas fa-cubes"}})]},proxy:!0}]),model:{value:t.category,callback:function(e){t.category=e},expression:"category"}})],1),a("div",{staticClass:"col-xs-12 col-md-3",staticStyle:{padding:"3px"}},[a("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",options:t.filteredLineOptions,label:"Línea"},on:{input:function(){t.product={value:null,label:"Seleccione el producto"},t.bag={value:null,label:"Seleccione el saco"}}},scopedSlots:t._u([{key:"prepend",fn:function(){return[a("q-icon",{attrs:{name:"fas fa-grip-lines-vertical"}})]},proxy:!0}]),model:{value:t.line,callback:function(e){t.line=e},expression:"line"}})],1),a("div",{staticClass:"col-xs-12 col-md-3",staticStyle:{padding:"3px"}},[a("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",options:t.filteredProductOptions,label:"Producto"},on:{input:function(){e.bag={value:null,label:"Seleccione el saco"}}},scopedSlots:t._u([{key:"prepend",fn:function(){return[a("q-icon",{attrs:{name:"emoji_objects"}})]},proxy:!0}]),model:{value:t.product,callback:function(e){t.product=e},expression:"product"}})],1),a("div",{staticClass:"col-xs-12 col-md-3 text-center",staticStyle:{padding:"3px"}},[a("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",options:t.filteredBagOptions,label:"Saco"},scopedSlots:t._u([{key:"prepend",fn:function(){return[a("q-icon",{attrs:{name:"fas fa-shopping-bag"}})]},proxy:!0}]),model:{value:t.bag,callback:function(e){t.bag=e},expression:"bag"}})],1),a("div",{staticClass:"col-xs-12 col-md-3 text-center",staticStyle:{padding:"3px"}},[a("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",label:"Número de Saco",min:0,type:"number"},on:{keyup:function(e){return t.updateFieldsv2(e,"cantidad")}},scopedSlots:t._u([{key:"prepend",fn:function(){return[a("q-icon",{attrs:{name:"fas fa-shopping-bag"}})]},proxy:!0}]),model:{value:t.bagNumber,callback:function(e){t.bagNumber=e},expression:"bagNumber"}})],1),a("div",{staticClass:"col-xs-12 col-md-3 offset-md-6 text-center pull-right",staticStyle:{padding:"3px"}},[a("q-btn",{staticStyle:{margin:"3px",height:"95%"},attrs:{color:"primary",icon:"fas fa-search",label:"Buscar"},on:{click:function(e){return t.generateTracking()}}})],1)]),a("br"),a("q-table",{attrs:{flat:"",bordered:"",data:t.data,columns:t.columns,"row-key":"serial",pagination:t.pagination},on:{"update:pagination":function(e){t.pagination=e}},scopedSlots:t._u([{key:"body",fn:function(e){return[a("q-tr",{attrs:{props:e}},[a("q-td",{key:"date",staticStyle:{width:"10%"},attrs:{props:e}},[t._v(t._s(e.row.date))]),a("q-td",{key:"bag",staticStyle:{"text-align":"right",width:"10%"},attrs:{props:e}},[t._v(t._s(e.row.bag_id))]),a("q-td",{key:"product",staticStyle:{"text-align":"left",width:"20%"},attrs:{props:e}},[t._v(t._s(e.row.category_code+"-"+e.row.line_code+"-"+e.row.product_name))]),a("q-td",{key:"branchOffice",staticStyle:{"text-align":"left",width:"20%"},attrs:{props:e}},[t._v(t._s(e.row.branch_office_name))]),a("q-td",{key:"storage",staticStyle:{"text-align":"left",width:"20%"},attrs:{props:e}},[t._v(t._s(e.row.storage_name))]),a("q-td",{key:"movement_type",staticStyle:{width:"10%"},attrs:{props:e}},[a("q-chip",{attrs:{square:"",dense:"",color:1==e.row.movement_type?"green":2==e.row.movement_type?"orange":"blue","text-color":"white"}},[t._v("\n                  "+t._s(1==e.row.movement_type?"Entrada":2==e.row.movement_type?"Salida":"Otro")+"\n                ")])],1),a("q-td",{key:"qty",staticStyle:{"text-align":"right",width:"10%"},attrs:{props:e}},[t._v(t._s(t.formatPrice(e.row.qty))+" KG")])],1)]}}])})],1)])])])},n=[],o=(l("a9e3"),l("4de4"),l("d3b7"),l("caad"),l("2532"),l("ac1f"),l("5319"),l("b680"),l("25f0"),l("159b"),l("aabb")),i={name:"Tracking",data:function(){return{formatter:new Intl.NumberFormat("en-US"),category:{value:null,label:"Seleccione la categoría"},line:{value:null,label:"Seleccione la línea"},product:{value:null,label:"Seleccione el producto"},bag:{value:null,label:"Seleccione el saco"},bagNumber:null,bagOptions:[],productOptions:[],lineOptions:[],categoryOptions:[],pagination:{rowsPerPage:50},columns:[{name:"date",align:"center",label:"Fecha",field:"date",style:"width: 10%",sortable:!0},{name:"bag",align:"center",label:"Número de saco",field:"bag",style:"width: 10%",sortable:!0,sort:function(e,t){return Number(e,10)-Number(t,10)}},{name:"product",align:"center",label:"Producto",field:"product",style:"width: 20%",sortable:!0},{name:"branchOffice",align:"center",label:"Sucursal",field:"branchOffice",style:"width: 20%",sortable:!0},{name:"storage",align:"center",label:"Almacén",field:"storage",style:"width: 20%",sortable:!0},{name:"movement_type",align:"center",label:"Tipo de movimiento",field:"movement_type",style:"width: 10%",sortable:!0},{name:"qty",align:"center",label:"Cantidad",field:"qty",style:"width: 10%",sortable:!0,sort:function(e,t){return Number(e,10)-Number(t,10)}}],data:[]}},computed:{filteredLineOptions:function(){var e=this,t=null!=this.category&&null!=this.category.value?this.lineOptions.filter((function(t){return Number(t.category)===Number(e.category.value)})):[];return t.unshift({value:null,label:"Seleccione la línea"}),t},filteredProductOptions:function(){var e=this,t=null!=this.line&&null!=this.line.value?this.productOptions.filter((function(t){return Number(t.line)===Number(e.line.value)})):[];return t.unshift({value:null,label:"Seleccione el producto"}),t},filteredBagOptions:function(){var e=this,t=null!=this.product&&null!=this.product.value?this.bagOptions.filter((function(t){return Number(t.product_id)===Number(e.product.value)})):[];return t.unshift({value:null,label:"Seleccione el saco"}),t}},beforeCreate:function(){this.$store.getters["users/roles"].includes(1)||this.$store.getters["users/roles"].includes(3)||this.$store.getters["users/roles"].includes(7)||this.$store.getters["users/roles"].includes(2)||this.$store.getters["users/roles"].includes(3)||this.$store.getters["users/roles"].includes(7)||this.$router.push("/")},created:function(){this.fetchFromServer()},methods:{formatPrice:function(e){var t=(e/1).toFixed(1).replace(".",",");return t.toString().replace(/\B(?=(\d{3})+(?!\d))/g,".")},fetchFromServer:function(){var e=this;this.$q.loading.show(),o["a"].get("/shipment-details/bags/all").then((function(t){var l=t.data;e.bagOptions=l.bags;var a=[];e.bagOptions.forEach((function(e){a.includes(Number(e.product_id))||a.push(Number(e.product_id))})),o["a"].get("/categories/options").then((function(t){var l=t.data;e.categoryOptions=l.options,e.categoryOptions.unshift({value:null,label:"Seleccione la categoría"}),o["a"].get("/lines/options").then((function(t){var l=t.data;e.lineOptions=l.options,e.lineOptions.unshift({value:null,label:"Seleccione la línea"}),o["a"].get("/products/options").then((function(t){var l=t.data,n=[];l.options.forEach((function(e){a.includes(Number(e.value))&&n.push(e)})),e.productOptions=n,e.$q.loading.hide()}))}))}))}))},updateFields:function(){var e=this;if(isNaN(this.bagNumber))this.category={value:null,label:"Seleccione la categoría"},this.line={value:null,label:"Seleccione la línea"},this.product={value:null,label:"Seleccione el producto"},this.bag={value:null,label:"Seleccione el saco"};else{var t=this.bagOptions.filter((function(t){return Number(t.value)===Number(e.bagNumber)}));t.length>0&&null!=t[0]?(this.bag=t[0],this.product=this.productOptions.filter((function(t){return Number(t.value)===Number(e.bag.product_id)}))[0],this.line=this.lineOptions.filter((function(t){return Number(t.value)===Number(e.product.line)}))[0],this.category=this.categoryOptions.filter((function(t){return Number(t.value)===Number(e.line.category)}))[0]):(this.bag={value:null,label:"Seleccione el saco"},this.product={value:null,label:"Seleccione el producto"},this.line={value:null,label:"Seleccione la línea"},this.category={value:null,label:"Seleccione la categoría"})}},updateFieldsv2:function(e,t){var l=this;switch(t){case"cantidad":if(this.bagNumber=this.bagNumber.replace(/[^0-9.]/g,""),this.bagNumber>0){var a=this.bagOptions.filter((function(e){return Number(e.value)===Number(l.bagNumber)}));a.length>0&&null!=a[0]&&(this.bag=a[0],this.product=this.productOptions.filter((function(e){return Number(e.value)===Number(l.bag.product_id)}))[0],this.line=this.lineOptions.filter((function(e){return Number(e.value)===Number(l.product.line)}))[0],this.category=this.categoryOptions.filter((function(e){return Number(e.value)===Number(l.line.category)}))[0])}else this.bag={value:null,label:"Seleccione el saco"},this.product={value:null,label:"Seleccione el producto"},this.line={value:null,label:"Seleccione la línea"},this.category={value:null,label:"Seleccione la categoría"};this.$v.bagNumber.$touch();break;default:break}},generateTracking:function(){var e=this;if(null==this.bag||null==this.bag.value)return this.$q.notify({message:"Por favor, seleccione el saco",position:"top",color:"warning"}),!1;this.$q.loading.show(),o["a"].get("/movements/kardex/null/null/null/null/null/".concat(this.bag.value,"/null")).then((function(t){var l=t.data;l.result&&(e.data=l.kardex,e.$q.loading.hide())}))}}},r=i,s=(l("54c0"),l("2877")),c=l("9989"),u=l("ead5"),d=l("079e"),p=l("ddd8"),b=l("0016"),g=l("27f9"),f=l("9c40"),h=l("eaac"),m=l("bd08"),v=l("db86"),y=l("b047"),S=l("eebe"),N=l.n(S),w=Object(s["a"])(r,a,n,!1,null,null,null);t["default"]=w.exports;N()(w,"components",{QPage:c["a"],QBreadcrumbs:u["a"],QBreadcrumbsEl:d["a"],QSelect:p["a"],QIcon:b["a"],QInput:g["a"],QBtn:f["a"],QTable:h["a"],QTr:m["a"],QTd:v["a"],QChip:y["a"]})},"54c0":function(e,t,l){"use strict";l("e8db")},e8db:function(e,t,l){}}]);
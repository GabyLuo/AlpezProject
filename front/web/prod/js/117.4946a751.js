(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[117],{aac4:function(e,t,i){"use strict";i.r(t);var s=function(){var e=this,t=e.$createElement,i=e._self._c||t;return i("q-page",[i("div",{staticClass:"q-pa-sm panel-header"},[i("div",{staticClass:"row"},[i("div",{staticClass:"col-sm-9"},[i("div",{staticClass:"q-pa-md q-gutter-sm"},[i("q-breadcrumbs",{staticStyle:{"font-size":"20px"},attrs:{align:"left"}},[i("q-breadcrumbs-el",{attrs:{label:"",icon:"home",to:"/"}}),i("q-breadcrumbs-el",{attrs:{label:"Destinos",to:"/ranges"}}),i("q-breadcrumbs-el",{attrs:{label:"Nuevo Destino"}})],1)],1)])])]),i("div",{staticClass:"q-pa-md bg-grey-3"},[i("div",{staticClass:"row bg-white border-panel"},[i("div",{staticClass:"col q-pa-md"},[i("div",{staticClass:"row q-mb-sm"}),i("div",{staticClass:"row q-col-gutter-xs"},[i("div",{staticClass:"col-xs-12 col-sm-4"},[i("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.range.fields.name.$error,label:"Nombre",rules:e.nameRules},on:{input:function(t){e.range.fields.name=t.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[i("q-icon",{attrs:{name:"fas fa-signature"}})]},proxy:!0}]),model:{value:e.range.fields.name,callback:function(t){e.$set(e.range.fields,"name",t)},expression:"range.fields.name"}})],1),i("div",{staticClass:"col-xs-12 col-sm-3"},[i("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.range.fields.state.$error,label:"Estado",rules:e.stateRules,options:e.stateOptionsFilter,"use-input":"","hide-selected":"","fill-input":"","input-debounce":"0",hint:"Basic autocomplete","emit-value":"","map-options":""},on:{filter:e.filterState},scopedSlots:e._u([{key:"prepend",fn:function(){return[i("q-icon",{attrs:{name:"fmd_good"}})]},proxy:!0},{key:"no-option",fn:function(){return[i("q-item",[i("q-item-section",{staticClass:"text-grey"},[e._v("\n                    No hay Resultados\n                  ")])],1)]},proxy:!0}]),model:{value:e.range.fields.state,callback:function(t){e.$set(e.range.fields,"state",t)},expression:"range.fields.state"}})],1),i("div",{staticClass:"col-xs-12 col-sm-3"},[i("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.range.fields.municipality.$error,label:"Municipio",rules:e.municipalityRules,options:e.municipalityOptionsFilter,"use-input":"","hide-selected":"","fill-input":"","input-debounce":"0",hint:"Basic autocomplete","emit-value":"","map-options":""},on:{filter:e.filterMunicipality},scopedSlots:e._u([{key:"prepend",fn:function(){return[i("q-icon",{attrs:{name:"fmd_good"}})]},proxy:!0},{key:"no-option",fn:function(){return[i("q-item",[i("q-item-section",{staticClass:"text-grey"},[e._v("\n                    No hay Resultados\n                  ")])],1)]},proxy:!0}]),model:{value:e.range.fields.municipality,callback:function(t){e.$set(e.range.fields,"municipality",t)},expression:"range.fields.municipality"}})],1),i("div",{staticClass:"col-xs-12 col-sm-2"},[i("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.range.fields.distance.$error,label:"Distancia",rules:e.distanceRules},on:{input:function(t){e.range.fields.distance=t.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[i("q-icon",{attrs:{name:"social_distance"}})]},proxy:!0}]),model:{value:e.range.fields.distance,callback:function(t){e.$set(e.range.fields,"distance",t)},expression:"range.fields.distance"}})],1)]),i("div",{staticClass:"row q-mb-sm q-mt-md"},[i("div",{staticClass:"col-xs-12 col-sm-2 offset-sm-10 pull-right"},[i("q-btn",{attrs:{color:"positive",icon:"save",label:"Guardar"},on:{click:function(t){return e.createRange()}}})],1)])])])])])},n=[],a=i("ded3"),r=i.n(a),o=(i("b0c0"),i("4de4"),i("d3b7"),i("a9e3"),i("caad"),i("2532"),i("aabb")),l=i("b5ae"),c=l.required,u=l.decimal,d={name:"NewRange",validations:{range:{fields:{name:{required:c},distance:{required:c,decimal:u},state:{required:c},municipality:{required:c}}}},data:function(){return{range:{fields:{name:null,distance:null,state:null,municipality:null}},stateOptions:[],stateOptionsFilter:[],municipalityOptions:[],municipalityOptionsFilter:[]}},mounted:function(){this.getState(),this.getMunicipality()},computed:{nameRules:function(e){var t=this;return[function(e){return t.$v.range.fields.name.required||"El campo Nombre es requerido."}]},distanceRules:function(e){var t=this;return[function(e){return t.$v.range.fields.distance.required||"El campo Distancia es requerido."},function(e){return t.$v.range.fields.distance.decimal||"El campo Distancia es numerico."}]},stateRules:function(e){var t=this;return[function(e){return t.$v.range.fields.state.required||"El campo Estado es requerido."}]},municipalityRules:function(e){var t=this;return[function(e){return t.$v.range.fields.municipality.required||"El campo Minicipio es requerido."}]},filteredMunicipalityOptions:function(){var e=this;return null!=this.range.fields.state?this.municipalityOptions.filter((function(t){return Number(t.state_id)===Number(e.range.fields.state)})):this.municipalityOptions}},beforeCreate:function(){this.$store.getters["users/roles"].includes(1)||this.$store.getters["users/roles"].includes(3)||this.$store.getters["users/roles"].includes(7)||this.$store.getters["users/roles"].includes(8)||this.$router.push("/")},methods:{createRange:function(){var e=this;if(this.$v.range.fields.$reset(),this.$v.range.fields.$touch(),this.$v.range.fields.$error)return this.$q.dialog({title:"Error",message:"Por favor, verifique las validaciones.",persistent:!0}),!1;this.$q.loading.show();var t=r()({},this.range.fields);o["a"].post("/ranges",t).then((function(t){var i=t.data;e.$q.notify({message:i.message.content,position:"top",color:i.result?"positive":"warning"}),i.result?(e.$q.loading.hide(),e.$router.push("/ranges")):e.$q.loading.hide()}))},getState:function(){var e=this;o["a"].get("ranges/getStateOptions").then((function(t){e.stateOptions=t.data.options}))},filterState:function(e,t,i){var s=this;t((function(){var t=e.toLowerCase();s.stateOptionsFilter=s.stateOptions.filter((function(e){return e.label.toLowerCase().indexOf(t)>-1}))}))},getMunicipality:function(){var e=this;o["a"].get("ranges/getMunicipalityOptions").then((function(t){e.municipalityOptions=t.data.options}))},filterMunicipality:function(e,t,i){var s=this;t((function(){var t=e.toLowerCase();s.municipalityOptionsFilter=s.filteredMunicipalityOptions.filter((function(e){return e.label.toLowerCase().indexOf(t)>-1}))}))}}},p=d,f=i("2877"),m=i("9989"),g=i("ead5"),v=i("079e"),b=i("27f9"),h=i("0016"),q=i("ddd8"),y=i("66e5"),$=i("4074"),C=i("9c40"),x=i("eebe"),O=i.n(x),w=Object(f["a"])(p,s,n,!1,null,null,null);t["default"]=w.exports;O()(w,"components",{QPage:m["a"],QBreadcrumbs:g["a"],QBreadcrumbsEl:v["a"],QInput:b["a"],QIcon:h["a"],QSelect:q["a"],QItem:y["a"],QItemSection:$["a"],QBtn:C["a"]})}}]);
(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[200],{"9edc":function(e,i,l){"use strict";l.r(i);var r=function(){var e=this,i=e.$createElement,l=e._self._c||i;return l("q-page",{staticClass:"bg-grey-3"},[l("div",{staticClass:"q-pa-sm panel-header"},[l("div",{staticClass:"row"},[l("div",{staticClass:"col-sm-9"},[l("div",{staticClass:"q-pa-md q-gutter-sm"},[l("q-breadcrumbs",{staticStyle:{"font-size":"20px"},attrs:{align:"left"}},[l("q-breadcrumbs-el",{attrs:{label:"",icon:"home",to:"/"}}),l("q-breadcrumbs-el",{attrs:{label:"Vehículos",to:"/vehicle"}}),l("q-breadcrumbs-el",{attrs:{label:"Nuevo Vehículo"}})],1)],1)])])]),l("div",{staticClass:"q-pa-md bg-grey-3"},[l("div",{staticClass:"row bg-white border-panel"},[l("div",{staticClass:"col q-pa-md"},[l("div",{staticClass:"row q-mb-sm"}),l("div",{staticClass:"row q-col-gutter-xs"},[l("div",{staticClass:"col-xs-12 col-sm-2"},[l("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.vehicle.fields.economic_number.$error,label:"Número de unidad",rules:e.economic_numberRules},scopedSlots:e._u([{key:"prepend",fn:function(){return[l("q-icon",{attrs:{name:"tag"}})]},proxy:!0}]),model:{value:e.vehicle.fields.economic_number,callback:function(i){e.$set(e.vehicle.fields,"economic_number",i)},expression:"vehicle.fields.economic_number"}})],1),l("div",{staticClass:"col-xs-12 col-sm-3"},[l("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.vehicle.fields.type_id.$error,options:e.vehicleTypeFilter,label:"Tipo de vehículo",rules:e.type_idRules,"use-input":"","emit-value":"","map-options":""},on:{filter:e.filterVehicle},scopedSlots:e._u([{key:"prepend",fn:function(){return[l("q-icon",{attrs:{name:"format_list_numbered"}})]},proxy:!0}]),model:{value:e.vehicle.fields.type_id,callback:function(i){e.$set(e.vehicle.fields,"type_id",i)},expression:"vehicle.fields.type_id"}})],1),l("div",{staticClass:"col-xs-12 col-sm-2"},[l("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.vehicle.fields.vehicle_brand.$error,label:"Marca de vehículo",rules:e.vehicle_brandRules},on:{input:function(i){e.vehicle.fields.vehicle_brand=i.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[l("q-icon",{attrs:{name:"book"}})]},proxy:!0}]),model:{value:e.vehicle.fields.vehicle_brand,callback:function(i){e.$set(e.vehicle.fields,"vehicle_brand",i)},expression:"vehicle.fields.vehicle_brand"}})],1),l("div",{staticClass:"col-xs-12 col-sm-3"},[l("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.vehicle.fields.vehicle_model.$error,label:"Modelo de vehículo",rules:e.vehicle_modelRules},on:{input:function(i){e.vehicle.fields.vehicle_model=i.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[l("q-icon",{attrs:{name:"format_list_bulleted"}})]},proxy:!0}]),model:{value:e.vehicle.fields.vehicle_model,callback:function(i){e.$set(e.vehicle.fields,"vehicle_model",i)},expression:"vehicle.fields.vehicle_model"}})],1),l("div",{staticClass:"col-xs-12 col-sm-2"},[l("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.vehicle.fields.year.$error,label:"Año",rules:e.yearRules},scopedSlots:e._u([{key:"prepend",fn:function(){return[l("q-icon",{attrs:{name:"event"}})]},proxy:!0}]),model:{value:e.vehicle.fields.year,callback:function(i){e.$set(e.vehicle.fields,"year",i)},expression:"vehicle.fields.year"}})],1),l("div",{staticClass:"col-xs-12 col-sm-3"},[l("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.vehicle.fields.license_plate.$error,label:"Placas",rules:e.license_plateRules},on:{input:function(i){e.vehicle.fields.license_plate=i.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[l("q-icon",{attrs:{name:"credit_score"}})]},proxy:!0}]),model:{value:e.vehicle.fields.license_plate,callback:function(i){e.$set(e.vehicle.fields,"license_plate",i)},expression:"vehicle.fields.license_plate"}})],1),l("div",{staticClass:"col-xs-12 col-sm-3"},[l("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.vehicle.fields.perm_sct.$error,rules:e.perm_sctRules,label:"Permiso SCT"},on:{input:function(i){e.vehicle.fields.perm_sct=i.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[l("q-icon",{attrs:{name:"insert_drive_file"}})]},proxy:!0}]),model:{value:e.vehicle.fields.perm_sct,callback:function(i){e.$set(e.vehicle.fields,"perm_sct",i)},expression:"vehicle.fields.perm_sct"}})],1),l("div",{staticClass:"col-xs-12 col-sm-3"},[l("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.vehicle.fields.perm_num_sct.$error,rules:e.perm_num_sctRules,label:"Número de permiso SCT"},on:{input:function(i){e.vehicle.fields.perm_num_sct=i.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[l("q-icon",{attrs:{name:"format_list_bulleted"}})]},proxy:!0}]),model:{value:e.vehicle.fields.perm_num_sct,callback:function(i){e.$set(e.vehicle.fields,"perm_num_sct",i)},expression:"vehicle.fields.perm_num_sct"}})],1),l("div",{staticClass:"col-xs-12 col-sm-3"},[l("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.vehicle.fields.vehicle_config.$error,options:e.vehicleConfigFilter,label:"Configuracion vehicular",rules:e.vehicle_configRules,"use-input":"","emit-value":"","map-options":""},on:{filter:e.filterConfig},scopedSlots:e._u([{key:"prepend",fn:function(){return[l("q-icon",{attrs:{name:"format_list_numbered"}})]},proxy:!0}]),model:{value:e.vehicle.fields.vehicle_config,callback:function(i){e.$set(e.vehicle.fields,"vehicle_config",i)},expression:"vehicle.fields.vehicle_config"}})],1),l("div",{staticClass:"col-xs-12 col-sm-3"},[l("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.vehicle.fields.insurance_civil_resp.$error,rules:e.insurance_civil_respRules,label:"Aseguradora Resp. Civil"},on:{input:function(i){e.vehicle.fields.insurance_civil_resp=i.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[l("q-icon",{attrs:{name:"insert_drive_file"}})]},proxy:!0}]),model:{value:e.vehicle.fields.insurance_civil_resp,callback:function(i){e.$set(e.vehicle.fields,"insurance_civil_resp",i)},expression:"vehicle.fields.insurance_civil_resp"}})],1),l("div",{staticClass:"col-xs-12 col-sm-3"},[l("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.vehicle.fields.resp_civil_policy.$error,rules:e.resp_civil_policyRules,label:"Poliza Aseg. Resp. Civil"},on:{input:function(i){e.vehicle.fields.resp_civil_policy=i.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[l("q-icon",{attrs:{name:"format_list_bulleted"}})]},proxy:!0}]),model:{value:e.vehicle.fields.resp_civil_policy,callback:function(i){e.$set(e.vehicle.fields,"resp_civil_policy",i)},expression:"vehicle.fields.resp_civil_policy"}})],1),l("div",{staticClass:"col-xs-12 col-sm-3"},[l("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",label:"Aseguradora Med. Ambiente"},on:{input:function(i){e.vehicle.fields.ambience_insurance=i.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[l("q-icon",{attrs:{name:"insert_drive_file"}})]},proxy:!0}]),model:{value:e.vehicle.fields.ambience_insurance,callback:function(i){e.$set(e.vehicle.fields,"ambience_insurance",i)},expression:"vehicle.fields.ambience_insurance"}})],1),l("div",{staticClass:"col-xs-12 col-sm-3"},[l("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",label:"Poliza Aseg. Med. Ambiente"},on:{input:function(i){e.vehicle.fields.ambience_insurance_policy=i.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[l("q-icon",{attrs:{name:"format_list_bulleted"}})]},proxy:!0}]),model:{value:e.vehicle.fields.ambience_insurance_policy,callback:function(i){e.$set(e.vehicle.fields,"ambience_insurance_policy",i)},expression:"vehicle.fields.ambience_insurance_policy"}})],1)]),l("div",{staticClass:"row q-mb-lg"}),l("div",{staticClass:"row q-col-gutter-xs"},[l("div",{staticClass:"col-xs-1 col-sm-1"},[l("div",{staticClass:"q-gutter-sm"},[l("q-checkbox",{attrs:{"indeterminate-value":"maybe",label:"Remolque"},model:{value:e.vehicle.fields.has_towing,callback:function(i){e.$set(e.vehicle.fields,"has_towing",i)},expression:"vehicle.fields.has_towing"}})],1)]),e.vehicle.fields.has_towing?l("div",{staticClass:"col-xs-12 col-sm-3 col-md-3 col-lg-3"},[l("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.vehicle.fields.towing_type_id.$error,rules:e.towing_type_idRules,options:e.towingTypeFilter,label:"SubTipo remolque","use-input":"","emit-value":"","map-options":""},on:{filter:e.filterTowing},scopedSlots:e._u([{key:"prepend",fn:function(){return[l("q-icon",{attrs:{name:"format_list_numbered"}})]},proxy:!0}],null,!1,4080341839),model:{value:e.vehicle.fields.towing_type_id,callback:function(i){e.$set(e.vehicle.fields,"towing_type_id",i)},expression:"vehicle.fields.towing_type_id"}})],1):e._e(),e.vehicle.fields.has_towing?l("div",{staticClass:"col-xs-12 col-sm-2"},[l("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.vehicle.fields.towing_plate.$error,rules:e.towing_plateRules,label:"Placas remolque"},on:{input:function(i){e.vehicle.fields.towing_plate=i.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[l("q-icon",{attrs:{name:"credit_score"}})]},proxy:!0}],null,!1,1141551574),model:{value:e.vehicle.fields.towing_plate,callback:function(i){e.$set(e.vehicle.fields,"towing_plate",i)},expression:"vehicle.fields.towing_plate"}})],1):e._e()]),l("div",{staticClass:"row q-mb-sm q-mt-md"},[l("div",{staticClass:"col-xs-12 col-sm-2 offset-sm-10 pull-right"},[l("q-btn",{attrs:{color:"positive",icon:"save",label:"Guardar"},on:{click:function(i){return e.createVehicle()}}})],1)])])])])])},s=[],o=l("ded3"),t=l.n(o),n=(l("caad"),l("2532"),l("4de4"),l("d3b7"),l("aabb")),c=l("b5ae"),a=c.required,u=c.requiredIf,d=c.numeric,p={name:"NewVehicle",validations:{vehicle:{fields:{economic_number:{required:a,numeric:d},type_id:{required:a},vehicle_brand:{required:a},vehicle_model:{required:a},year:{required:a,numeric:d},license_plate:{required:a},perm_sct:{required:a},perm_num_sct:{required:a},vehicle_config:{required:a},insurance_civil_resp:{required:a},resp_civil_policy:{required:a},towing_type_id:{required:u((function(){return this.vehicle.fields.has_towing}))},towing_plate:{required:u((function(){return this.vehicle.fields.has_towing}))}}}},data:function(){return{vehicle:{fields:{economic_number:null,type_id:null,vehicle_brand:null,vehicle_model:null,year:null,license_plate:null,perm_sct:null,perm_num_sct:null,vehicle_config:null,insurance_civil_resp:null,resp_civil_policy:null,ambience_insurance:null,ambience_insurance_policy:null,has_towing:null,towing_type_id:null,towing_plate:null}},vehicleType:[],towingType:[],vehicleTypeFilter:[],towingTypeFilter:[],vehicleConfig:[],vehicleConfigFilter:[]}},mounted:function(){this.getVehicleType(),this.getTowingType(),this.getConfig()},computed:{economic_numberRules:function(e){var i=this;return[function(e){return i.$v.vehicle.fields.economic_number.required||"El campo Número de unidad es requerido."},function(e){return i.$v.vehicle.fields.economic_number.numeric||"El campo Número de unidad es numerico."}]},type_idRules:function(e){var i=this;return[function(e){return i.$v.vehicle.fields.type_id.required||"El campo Tipo de vehiculo es requerido."}]},vehicle_brandRules:function(e){var i=this;return[function(e){return i.$v.vehicle.fields.vehicle_brand.required||"El campo Marca de vehículo es requerido."}]},vehicle_modelRules:function(e){var i=this;return[function(e){return i.$v.vehicle.fields.vehicle_model.required||"El campo Modelo de vehículo es requerido."}]},yearRules:function(e){var i=this;return[function(e){return i.$v.vehicle.fields.year.required||"El campo Año es requerido."},function(e){return i.$v.vehicle.fields.year.numeric||"El campo Año es numerico."}]},vinRules:function(e){var i=this;return[function(e){return i.$v.vehicle.fields.vin.required||"El campo VIN es requerido."}]},license_plateRules:function(e){var i=this;return[function(e){return i.$v.vehicle.fields.license_plate.required||"El campo Placas es requerido."}]},perm_sctRules:function(e){var i=this;return[function(e){return i.$v.vehicle.fields.perm_sct.required||"El campo Permiso SCT es requerido."}]},perm_num_sctRules:function(e){var i=this;return[function(e){return i.$v.vehicle.fields.perm_num_sct.required||"El campo Número de permiso SCT es requerido."}]},vehicle_configRules:function(e){var i=this;return[function(e){return i.$v.vehicle.fields.vehicle_config.required||"El campo Configuracion vehicular es requerido."}]},insurance_civil_respRules:function(e){var i=this;return[function(e){return i.$v.vehicle.fields.insurance_civil_resp.required||"El campo Aseguradora Resp. Civil es requerido."}]},resp_civil_policyRules:function(e){var i=this;return[function(e){return i.$v.vehicle.fields.resp_civil_policy.required||"El campo Poliza Aseg. Resp. Civil es requerido."}]},towing_type_idRules:function(e){var i=this;return[function(e){return i.$v.vehicle.fields.towing_type_id.required||"El campo SubTipo remolque es requerido."}]},towing_plateRules:function(e){var i=this;return[function(e){return i.$v.vehicle.fields.towing_plate.required||"El campo Placas remolque es requerido."}]}},beforeCreate:function(){this.$store.getters["users/roles"].includes(1)||this.$store.getters["users/roles"].includes(3)||this.$store.getters["users/roles"].includes(7)||this.$store.getters["users/roles"].includes(8)||this.$router.push("/")},methods:{createVehicle:function(){var e=this;if(this.$v.vehicle.fields.$reset(),this.$v.vehicle.fields.$touch(),this.$v.vehicle.fields.$error)return this.$q.dialog({title:"Error",message:"Por favor, verifique las validaciones.",persistent:!0}),!1;this.$q.loading.show();var i=t()({},this.vehicle.fields);n["a"].post("/vehicle",i).then((function(i){var l=i.data;e.$q.notify({message:l.message.content,position:"top",color:l.result?"positive":"warning"}),l.result?(e.$q.loading.hide(),e.$router.push("/vehicle")):e.$q.loading.hide()}))},getVehicleType:function(){var e=this;n["a"].get("vehicleType/options").then((function(i){e.vehicleType=i.data.options,e.towingTypeFilter=i.data.options}))},getTowingType:function(){var e=this;n["a"].get("vehicleType/optionstowing").then((function(i){e.towingType=i.data.options}))},getConfig:function(){var e=this;n["a"].get("vehicleType/optionsconfig").then((function(i){e.vehicleConfig=i.data.options}))},filterVehicle:function(e,i,l){var r=this;i((function(){var i=e.toLowerCase();r.vehicleTypeFilter=r.vehicleType.filter((function(e){return e.label.toLowerCase().indexOf(i)>-1}))}))},filterTowing:function(e,i,l){var r=this;i((function(){var i=e.toLowerCase();r.towingTypeFilter=r.towingType.filter((function(e){return e.label.toLowerCase().indexOf(i)>-1}))}))},filterConfig:function(e,i,l){var r=this;i((function(){var i=e.toLowerCase();r.vehicleConfigFilter=r.vehicleConfig.filter((function(e){return e.label.toLowerCase().indexOf(i)>-1}))}))}}},v=p,f=l("2877"),h=l("9989"),_=l("ead5"),m=l("079e"),b=l("27f9"),g=l("0016"),y=l("ddd8"),q=l("8f8e"),$=l("9c40"),C=l("eebe"),w=l.n(C),x=Object(f["a"])(v,r,s,!1,null,null,null);i["default"]=x.exports;w()(x,"components",{QPage:h["a"],QBreadcrumbs:_["a"],QBreadcrumbsEl:m["a"],QInput:b["a"],QIcon:g["a"],QSelect:y["a"],QCheckbox:q["a"],QBtn:$["a"]})}}]);
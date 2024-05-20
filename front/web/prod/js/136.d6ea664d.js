(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([[136],{"267c":function(e,t,s){"use strict";s.r(t);var o=function(){var e=this,t=e.$createElement,s=e._self._c||t;return s("q-page",{staticClass:"bg-grey-3"},[s("div",{staticClass:"q-pa-sm panel-header"},[s("div",{staticClass:"row"},[s("div",{staticClass:"col-sm-9"},[s("div",{staticClass:"q-pa-md q-gutter-sm"},[s("q-breadcrumbs",{staticStyle:{"font-size":"20px"},attrs:{align:"left"}},[s("q-breadcrumbs-el",{attrs:{label:"",icon:"home",to:"/"}}),s("q-breadcrumbs-el",{attrs:{label:"Empleados",to:"/employees"}}),s("q-breadcrumbs-el",{attrs:{label:"Editar Empleado"},domProps:{textContent:e._s(e.employee.fields.name)}})],1)],1)])])]),s("div",{staticClass:"q-pa-xs"},[s("div",{staticClass:"q-gutter-y-md",staticStyle:{width:"100%"}},[s("q-card",[s("q-tabs",{staticClass:"text-grey",attrs:{dense:"","active-color":"primary","indicator-color":"primary",align:"justify","narrow-indicator":""},model:{value:e.tab,callback:function(t){e.tab=t},expression:"tab"}},[s("q-tab",{attrs:{name:"expediente",icon:"rule_folder",label:"EXPEDIENTE"}}),s("q-tab",{attrs:{name:"vacations",icon:"date_range",label:"VACACIONES"}})],1),s("q-tab-panels",{attrs:{animated:""},model:{value:e.tab,callback:function(t){e.tab=t},expression:"tab"}},[s("q-tab-panel",{attrs:{name:"expediente"}},[s("div",{staticClass:"row bg-white border-panel"},[s("div",{staticClass:"col q-pa-md"},[s("div",{staticClass:"row",staticStyle:{"padding-bottom":"15px"}},[e._v("\n                    Información laboral\n                  ")]),s("div",{staticClass:"row q-col-gutter-xs"},[s("div",{staticClass:"col-xs-12 col-sm-3"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.employee.fields.code.$error,label:"Codigo",rules:e.codeRules},on:{input:function(t){e.employee.fields.code=t.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"tag"}})]},proxy:!0}]),model:{value:e.employee.fields.code,callback:function(t){e.$set(e.employee.fields,"code",t)},expression:"employee.fields.code"}})],1),s("div",{staticClass:"col-xs-12 col-sm-3"},[s("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",options:e.departmentOptions,label:"Departamentos",rules:e.departmentRules,error:e.$v.employee.fields.department.$error},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"fas fa-building"}})]},proxy:!0}]),model:{value:e.employee.fields.department,callback:function(t){e.$set(e.employee.fields,"department",t)},expression:"employee.fields.department"}})],1),s("div",{staticClass:"col-xs-12 col-sm-3"},[s("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",options:e.filteredAreaOptions,label:"Areas",rules:e.areaRules,error:e.$v.employee.fields.area.$error},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"list"}})]},proxy:!0}]),model:{value:e.employee.fields.area,callback:function(t){e.$set(e.employee.fields,"area",t)},expression:"employee.fields.area"}})],1),s("div",{staticClass:"col-xs-12 col-sm-3"},[s("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",options:e.filteredPositionOptions,label:"Puesto",rules:e.positionRules,error:e.$v.employee.fields.position.$error},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"fas fa-clipboard-list"}})]},proxy:!0}]),model:{value:e.employee.fields.position,callback:function(t){e.$set(e.employee.fields,"position",t)},expression:"employee.fields.position"}})],1),s("div",{staticClass:"col-xs-12 col-sm-3"},[s("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",options:e.shiftOptions,label:"Turno",rules:e.shiftRules,error:e.$v.employee.fields.shift.$error},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"restore"}})]},proxy:!0}]),model:{value:e.employee.fields.shift,callback:function(t){e.$set(e.employee.fields,"shift",t)},expression:"employee.fields.shift"}})],1),s("div",{staticClass:"col-xs-12 col-sm-3"},[s("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",options:e.filteredTimetablesOptions,label:"Horario"},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"restore"}})]},proxy:!0}]),model:{value:e.employee.fields.timetable,callback:function(t){e.$set(e.employee.fields,"timetable",t)},expression:"employee.fields.timetable"}})],1),s("div",{staticClass:"col-xs-12 col-sm-6"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",label:"Ubicación"},on:{input:function(t){e.employee.fields.location=t.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"radar"}})]},proxy:!0}]),model:{value:e.employee.fields.location,callback:function(t){e.$set(e.employee.fields,"location",t)},expression:"employee.fields.location"}})],1),s("div",{staticClass:"col-xs-12 col-sm-3"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",label:"Forma de pago"},on:{input:function(t){e.employee.fields.payment_method=t.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"attach_money"}})]},proxy:!0}]),model:{value:e.employee.fields.payment_method,callback:function(t){e.$set(e.employee.fields,"payment_method",t)},expression:"employee.fields.payment_method"}})],1),s("div",{staticClass:"col-xs-12 col-sm-3 text-center"},[s("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",mask:"date",label:"Fecha de ingreso"},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"event"}})]},proxy:!0}]),model:{value:e.employee.fields.date_entry,callback:function(t){e.$set(e.employee.fields,"date_entry",t)},expression:"employee.fields.date_entry"}},[s("q-popup-proxy",{ref:"date_ref",attrs:{"transition-show":"scale","transition-hide":"scale"}},[s("div",{staticClass:"col-sm-12"},[s("q-date",{attrs:{color:"secondary","text-color":"white",mask:"DD/MM/YYYY","today-btn":""},on:{input:function(){return e.$refs.date_ref.hide()}},model:{value:e.employee.fields.date_entry,callback:function(t){e.$set(e.employee.fields,"date_entry",t)},expression:"employee.fields.date_entry"}})],1)])],1)],1),s("div",{staticClass:"col-xs-12 col-sm-3 text-center"},[s("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",mask:"date",label:"Fecha de baja"},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"event"}})]},proxy:!0}]),model:{value:e.employee.fields.out_date,callback:function(t){e.$set(e.employee.fields,"out_date",t)},expression:"employee.fields.out_date"}},[s("q-popup-proxy",{ref:"date_ref",attrs:{"transition-show":"scale","transition-hide":"scale"}},[s("div",{staticClass:"col-sm-12"},[s("q-date",{attrs:{color:"secondary","text-color":"white",mask:"DD/MM/YYYY","today-btn":""},on:{input:function(){return e.$refs.date_ref.hide()}},model:{value:e.employee.fields.out_date,callback:function(t){e.$set(e.employee.fields,"out_date",t)},expression:"employee.fields.out_date"}})],1)])],1)],1),s("div",{staticClass:"col-xs-12 col-sm-3"},[s("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.employee.fields.status.$error,options:e.statusOptions,label:"Status",rules:e.statusRules},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"inventory"}})]},proxy:!0}]),model:{value:e.employee.fields.status,callback:function(t){e.$set(e.employee.fields,"status",t)},expression:"employee.fields.status"}})],1)]),s("div",{staticClass:"row",staticStyle:{"padding-bottom":"15px","padding-top":"15px"}},[e._v("\n                    Información general\n                  ")]),s("div",{staticClass:"row q-col-gutter-xs"},[s("div",{staticClass:"col-xs-12 col-sm-4"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.employee.fields.name.$error,label:"Nombre",rules:e.nameRules},on:{input:function(t){e.employee.fields.name=t.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"fas fa-signature"}})]},proxy:!0}]),model:{value:e.employee.fields.name,callback:function(t){e.$set(e.employee.fields,"name",t)},expression:"employee.fields.name"}})],1),s("div",{staticClass:"col-xs-12 col-sm-4"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",error:e.$v.employee.fields.paternal.$error,label:"Apellido paterno",rules:e.paternalRules},on:{input:function(t){e.employee.fields.paternal=t.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"fas fa-signature"}})]},proxy:!0}]),model:{value:e.employee.fields.paternal,callback:function(t){e.$set(e.employee.fields,"paternal",t)},expression:"employee.fields.paternal"}})],1),s("div",{staticClass:"col-xs-12 col-sm-4"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",label:"Apellido materno"},on:{input:function(t){e.employee.fields.mathers=t.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"fas fa-signature"}})]},proxy:!0}]),model:{value:e.employee.fields.mathers,callback:function(t){e.$set(e.employee.fields,"mathers",t)},expression:"employee.fields.mathers"}})],1),s("div",{staticClass:"col-xs-12 col-sm-4 text-center"},[s("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",mask:"date",label:"Fecha de nacimiento"},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"event"}})]},proxy:!0}]),model:{value:e.employee.fields.birth_date,callback:function(t){e.$set(e.employee.fields,"birth_date",t)},expression:"employee.fields.birth_date"}},[s("q-popup-proxy",{ref:"date_ref",attrs:{"transition-show":"scale","transition-hide":"scale"}},[s("div",{staticClass:"col-sm-12"},[s("q-date",{attrs:{color:"secondary","text-color":"white",mask:"DD/MM/YYYY","today-btn":""},model:{value:e.employee.fields.birth_date,callback:function(t){e.$set(e.employee.fields,"birth_date",t)},expression:"employee.fields.birth_date"}})],1)])],1)],1),s("div",{staticClass:"col-xs-12 col-sm-4"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",label:"CURP",error:e.$v.employee.fields.curp.$error,rules:e.curpRules},on:{input:function(t){e.employee.fields.curp=t.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"person_search"}})]},proxy:!0}]),model:{value:e.employee.fields.curp,callback:function(t){e.$set(e.employee.fields,"curp",t)},expression:"employee.fields.curp"}})],1),s("div",{staticClass:"col-xs-12 col-sm-4"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",rules:e.rfcRules,label:"RFC",error:e.$v.employee.fields.rfc.$error},on:{input:function(t){e.employee.fields.rfc=t.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"hourglass_top"}})]},proxy:!0}]),model:{value:e.employee.fields.rfc,callback:function(t){e.$set(e.employee.fields,"rfc",t)},expression:"employee.fields.rfc"}})],1),s("div",{staticClass:"col-xs-12 col-sm-4"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",rules:e.social_securityRules,error:e.$v.employee.fields.social_security.$error,label:"Numero de seguro social"},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"health_and_safety"}})]},proxy:!0}]),model:{value:e.employee.fields.social_security,callback:function(t){e.$set(e.employee.fields,"social_security",t)},expression:"employee.fields.social_security"}})],1),s("div",{staticClass:"col-xs-12 col-sm-1"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",rules:e.ladaRules,error:e.$v.employee.fields.lada.$error,label:"Lada"},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"call"}})]},proxy:!0}]),model:{value:e.employee.fields.lada,callback:function(t){e.$set(e.employee.fields,"lada",t)},expression:"employee.fields.lada"}})],1),s("div",{staticClass:"col-xs-12 col-sm-3"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",rules:e.phoneRules,error:e.$v.employee.fields.phone.$error,label:"Telefono"},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"call"}})]},proxy:!0}]),model:{value:e.employee.fields.phone,callback:function(t){e.$set(e.employee.fields,"phone",t)},expression:"employee.fields.phone"}})],1),s("div",{staticClass:"col-xs-12 col-sm-4"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",label:"Tipo de sangre",rules:e.blood_typeRules,error:e.$v.employee.fields.blood_type.$error},on:{input:function(t){e.employee.fields.blood_type=t.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"bloodtype"}})]},proxy:!0}]),model:{value:e.employee.fields.blood_type,callback:function(t){e.$set(e.employee.fields,"blood_type",t)},expression:"employee.fields.blood_type"}})],1)]),s("div",{staticClass:"row",staticStyle:{"padding-bottom":"15px","padding-top":"15px"}},[e._v("\n                    Estudios\n                  ")]),s("div",{staticClass:"row q-col-gutter-xs"},[s("div",{staticClass:"col-xs-12 col-sm-6"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",label:"Escolaridad"},on:{input:function(t){e.employee.fields.studies=t.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"school"}})]},proxy:!0}]),model:{value:e.employee.fields.studies,callback:function(t){e.$set(e.employee.fields,"studies",t)},expression:"employee.fields.studies"}})],1),s("div",{staticClass:"col-xs-12 col-sm-6"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",label:"Especialidad"},on:{input:function(t){e.employee.fields.specialty=t.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"history_edu"}})]},proxy:!0}]),model:{value:e.employee.fields.specialty,callback:function(t){e.$set(e.employee.fields,"specialty",t)},expression:"employee.fields.specialty"}})],1),s("div",{staticClass:"col-xs-12 col-sm-12"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",label:"Otros conocimientos"},on:{input:function(t){e.employee.fields.expertise=t.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"note_alt"}})]},proxy:!0}]),model:{value:e.employee.fields.expertise,callback:function(t){e.$set(e.employee.fields,"expertise",t)},expression:"employee.fields.expertise"}})],1)]),s("div",{staticClass:"row",staticStyle:{"padding-bottom":"15px","padding-top":"15px"}},[e._v("\n                    Domicilio\n                  ")]),s("div",{staticClass:"row q-col-gutter-xs"},[s("div",{staticClass:"col-xs-12 col-sm-8"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",label:"Calle"},on:{input:function(t){e.employee.fields.street=t.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"room"}})]},proxy:!0}]),model:{value:e.employee.fields.street,callback:function(t){e.$set(e.employee.fields,"street",t)},expression:"employee.fields.street"}})],1),s("div",{staticClass:"col-xs-12 col-sm-4"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",label:"Colonia"},on:{input:function(t){e.employee.fields.colony=t.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"business"}})]},proxy:!0}]),model:{value:e.employee.fields.colony,callback:function(t){e.$set(e.employee.fields,"colony",t)},expression:"employee.fields.colony"}})],1),s("div",{staticClass:"col-xs-12 col-sm-3"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",label:"Municipio"},on:{input:function(t){e.employee.fields.municipality=t.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"map"}})]},proxy:!0}]),model:{value:e.employee.fields.municipality,callback:function(t){e.$set(e.employee.fields,"municipality",t)},expression:"employee.fields.municipality"}})],1),s("div",{staticClass:"col-xs-12 col-sm-3"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",label:"Código postal"},on:{input:function(t){e.employee.fields.zip_code=t.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"language"}})]},proxy:!0}]),model:{value:e.employee.fields.zip_code,callback:function(t){e.$set(e.employee.fields,"zip_code",t)},expression:"employee.fields.zip_code"}})],1),s("div",{staticClass:"col-xs-12 col-sm-3"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",label:"Estado de nacimiento"},on:{input:function(t){e.employee.fields.birth_state=t.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"flag"}})]},proxy:!0}]),model:{value:e.employee.fields.birth_state,callback:function(t){e.$set(e.employee.fields,"birth_state",t)},expression:"employee.fields.birth_state"}})],1),s("div",{staticClass:"col-xs-12 col-sm-3"},[s("q-input",{attrs:{color:"dark","bg-color":"secondary",filled:"",label:"Ciudad de nacimiento"},on:{input:function(t){e.employee.fields.birth_city=t.toUpperCase()}},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"location_city"}})]},proxy:!0}]),model:{value:e.employee.fields.birth_city,callback:function(t){e.$set(e.employee.fields,"birth_city",t)},expression:"employee.fields.birth_city"}})],1)]),s("div",{staticClass:"row q-mb-sm q-mt-md"},[s("div",{staticClass:"col-xs-12 col-sm-2 offset-sm-10 pull-right"},[s("q-btn",{attrs:{color:"positive",icon:"save",label:"Guardar"},on:{click:function(t){return e.updateEmployee()}}})],1)])])])]),s("q-tab-panel",{attrs:{name:"vacations"}},[s("div",{staticClass:"row border-panel",staticStyle:{"margin-bottom":"20px"}},[s("div",{staticClass:"col-xs-12 col-sm-9"},[s("p",{staticStyle:{"margin-left":"8px","margin-top":"20px","font-size":"18px"}},[e._v("Dias de vacaciones restantes: "+e._s(e.restVacations)+" dias.")])]),s("div",{staticClass:"col-xs-12 col-sm-3 pull-right text-center"},[s("q-btn",{staticStyle:{margin:"15px"},attrs:{color:"positive",icon:"add",label:"SOLICITAR"},on:{click:function(t){e.request=!0}}})],1)]),s("div",{staticClass:"row bg-white border-panel"},[s("div",{staticClass:"col-xs-12 col-sm-5",staticStyle:{padding:"20px"}},[s("q-table",{attrs:{title:"VACACIONES ACREDITADAS",flat:"",bordered:"",data:e.data,columns:e.columns,"row-key":"code",pagination:e.pagination,filter:e.filter},on:{"update:pagination":function(t){e.pagination=t}},scopedSlots:e._u([{key:"body",fn:function(t){return[s("q-tr",{attrs:{props:t}},[s("q-td",{key:"years",style:t.row.year===e.employee.fields.derecho+""?"text-align: center; background-color: #21ba45;":"text-align: center;",attrs:{props:t}},[e._v(e._s(t.row.year))]),s("q-td",{key:"days",style:t.row.year===e.employee.fields.derecho+""?"text-align: center; background-color: #21ba45;":"text-align: center;",attrs:{props:t}},[e._v(e._s(t.row.day))])],1)]}}])})],1),s("div",{staticClass:"col-xs-12 col-sm-7",staticStyle:{padding:"20px"}},[s("q-table",{attrs:{title:"DIAS DE VACACIONES TOMADOS",flat:"",bordered:"",data:e.dataVacations,columns:e.columnsVacations,"row-key":"code",pagination:e.pagination,filter:e.filter},on:{"update:pagination":function(t){e.pagination=t}},scopedSlots:e._u([{key:"body",fn:function(t){return[s("q-tr",{attrs:{props:t}},[s("q-td",{key:"date",staticStyle:{"text-align":"center"},attrs:{props:t}},[e._v(e._s(t.row.date))]),s("q-td",{key:"vacation_year",staticStyle:{"text-align":"center"},attrs:{props:t}},[e._v(e._s(e.getPeriodo(t.rowIndex)))])],1)]}}])})],1)])])],1)],1)],1)]),s("q-dialog",{attrs:{persistent:""},model:{value:e.request,callback:function(t){e.request=t},expression:"request"}},[s("q-card",{staticStyle:{"min-width":"750px"}},[s("q-card-section",[s("div",{staticClass:"text-h6"},[e._v("Solicitar periodo vacacional")])]),s("q-card-section",{staticClass:"q-pt-none"},[s("div",{staticClass:"row q-col-gutter-xs"},[s("div",{staticClass:"col-xs-12 col-sm-6 text-center"},[s("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",rules:e.sinceRules,error:e.$v.requestModal.fields.since.$error,mask:"date",label:"Desde"},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"event"}})]},proxy:!0}]),model:{value:e.requestModal.fields.since,callback:function(t){e.$set(e.requestModal.fields,"since",t)},expression:"requestModal.fields.since"}},[s("q-popup-proxy",{ref:"date_ref",attrs:{"transition-show":"scale","transition-hide":"scale"}},[s("div",{staticClass:"col-sm-12"},[s("q-date",{attrs:{color:"secondary","text-color":"white",mask:"DD/MM/YYYY","today-btn":""},on:{input:function(){return e.$refs.date_ref.hide()}},model:{value:e.requestModal.fields.since,callback:function(t){e.$set(e.requestModal.fields,"since",t)},expression:"requestModal.fields.since"}})],1)])],1)],1),s("div",{staticClass:"col-xs-12 col-sm-6 text-center"},[s("q-select",{attrs:{color:"dark","bg-color":"secondary",filled:"",rules:e.untilRules,error:e.$v.requestModal.fields.until.$error,mask:"date",label:"Hasta"},scopedSlots:e._u([{key:"prepend",fn:function(){return[s("q-icon",{attrs:{name:"event"}})]},proxy:!0}]),model:{value:e.requestModal.fields.until,callback:function(t){e.$set(e.requestModal.fields,"until",t)},expression:"requestModal.fields.until"}},[s("q-popup-proxy",{ref:"date_ref",attrs:{"transition-show":"scale","transition-hide":"scale"}},[s("div",{staticClass:"col-sm-12"},[s("q-date",{attrs:{color:"secondary","text-color":"white",mask:"DD/MM/YYYY","today-btn":""},on:{input:function(){return e.$refs.date_ref.hide()}},model:{value:e.requestModal.fields.until,callback:function(t){e.$set(e.requestModal.fields,"until",t)},expression:"requestModal.fields.until"}})],1)])],1)],1)])]),s("q-card-actions",{staticClass:"text-primary",attrs:{align:"right"}},[s("div",{staticClass:"col-xs-12 col-sm-4 text-center pull-right"},[s("q-btn",{directives:[{name:"close-popup",rawName:"v-close-popup"}],staticStyle:{"margin-right":"10px"},attrs:{color:"negative",label:"Cancel"}}),s("q-btn",{directives:[{name:"close-popup",rawName:"v-close-popup"}],attrs:{color:"positive",label:"Solicitar"},on:{click:function(t){return e.creatRequest()}}})],1)])],1)],1)],1)},l=[],i=s("ded3"),a=s.n(i),n=(s("b0c0"),s("4de4"),s("d3b7"),s("caad"),s("2532"),s("fb6a"),s("a9e3"),s("25eb"),s("aabb")),r=s("b5ae"),c=r.required,d=r.maxLength,p=r.minLength,u={name:"NewEmployee",validations:{employee:{fields:{code:{required:c,maxLength:d(6)},department:{required:c},area:{required:c},position:{required:c},shift:{required:c},status:{required:c},name:{required:c},paternal:{required:c},curp:{maxLength:d(20),minLength:p(18)},rfc:{maxLength:d(15),minLength:p(12)},social_security:{maxLength:d(14),minLength:p(11)},phone:{maxLength:d(9),minLength:p(7)},blood_type:{maxLength:d(3),minLength:p(2)},lada:{maxLength:d(3),minLength:p(2)}}},incidencias:{fields:{assistance_date:{required:c},assistance_type:{required:c}}},requestModal:{fields:{since:{required:c},until:{required:c}}}},data:function(){return{tab:"expediente",request:!1,employee:{fields:{code:null,department:null,area:null,position:null,shift:null,location:null,payment_method:null,date_entry:null,out_date:null,status:null,name:null,paternal:null,mathers:null,birth_date:null,curp:null,rfc:null,social_security:null,phone:0,blood_type:null,studies:null,specialty:null,expertise:null,street:null,colony:null,municipality:null,zip_code:null,birth_state:null,birth_city:null,lada:null,timetable:null,derecho:null}},incidencias:{fields:{employee_id:this.$route.params.id,assistance_type:null,assistance_date:null}},requestModal:{fields:{employee_id:this.$route.params.id,since:null,until:null}},pagination:{sortBy:"code",descending:!1,rowsPerPage:25},columns:[{name:"years",align:"center",label:"AÑOS",field:"years",sortable:!1},{name:"days",align:"center",label:"DIAS",field:"days",sortable:!1}],columnsIncidencias:[{name:"type",align:"center",label:"TIPO INCIDENCIA",field:"type",sortable:!1},{name:"date",align:"center",label:"FECHA",field:"date",sortable:!1}],columnsVacations:[{name:"date",align:"center",label:"FECHA",field:"date",sortable:!1},{name:"vacation_year",align:"center",label:"AÑO DE VACACIONES",field:"vacation_year",sortable:!1}],data:[],captura:[],filter:"",departmentOptions:[],areaOptions:[],positionOptions:[],shiftOptions:[],timetablesOptions:[],incidenciasOptions:[],statusOptions:[{label:"ACTIVO",value:"ACTIVO"},{label:"INACTIVO",value:"INACTIVO"}],dataVacations:[],vacationDays:null,restVacations:null}},computed:{codeRules:function(e){var t=this;return[function(e){return t.$v.employee.fields.code.required||"El campo Codigo es requerido."},function(e){return t.$v.employee.fields.code.maxLength||"El campo Codigo no debe exceder los 6 dígitos."}]},departmentRules:function(e){var t=this;return[function(e){return t.$v.employee.fields.department.required||"El campo Departamento es requerido."}]},areaRules:function(e){var t=this;return[function(e){return t.$v.employee.fields.area.required||"El campo Area es requerido."}]},positionRules:function(e){var t=this;return[function(e){return t.$v.employee.fields.position.required||"El campo Puesto es requerido."}]},shiftRules:function(e){var t=this;return[function(e){return t.$v.employee.fields.shift.required||"El campo Turno es requerido."}]},statusRules:function(e){var t=this;return[function(e){return t.$v.employee.fields.status.required||"El campo Status es requerido."}]},nameRules:function(e){var t=this;return[function(e){return t.$v.employee.fields.name.required||"El campo Nombre es requerido."}]},paternalRules:function(e){var t=this;return[function(e){return t.$v.employee.fields.paternal.required||"El campo Apellido paterno es requerido."}]},curpRules:function(e){var t=this;return[function(e){return t.$v.employee.fields.curp.maxLength||"El campo CURP no debe exceder los 20 dígitos."},function(e){return t.$v.employee.fields.curp.minLength||"El campo CURP no debe ser menor a 18 dígitos."}]},rfcRules:function(e){var t=this;return[function(e){return t.$v.employee.fields.rfc.maxLength||"El campo RFC no debe exceder los 15 dígitos."},function(e){return t.$v.employee.fields.rfc.minLength||"El campo RFC no debe ser menor a los 12 dígitos."}]},social_securityRules:function(e){var t=this;return[function(e){return t.$v.employee.fields.social_security.maxLength||"El campo Numero de seguro no debe exceder los 14 dígitos."},function(e){return t.$v.employee.fields.social_security.minLength||"El campo Numero de seguro no debe ser menor a 11 dígitos."}]},ladaRules:function(e){var t=this;return[function(e){return t.$v.employee.fields.lada.maxLength||"El campo Lada no debe exceder los 3 dígitos."},function(e){return t.$v.employee.fields.lada.minLength||"El campo Lada no debe ser menor a los 2 dígitos."}]},phoneRules:function(e){var t=this;return[function(e){return t.$v.employee.fields.phone.maxLength||"El campo Telefono no debe exceder los 9 dígitos."},function(e){return t.$v.employee.fields.phone.minLength||"El campo Telefono no debe ser menor a los 7 dígitos."}]},blood_typeRules:function(e){var t=this;return[function(e){return t.$v.employee.fields.blood_type.maxLength||"El campo Tipo de sangre no debe exceder los 3 dígitos."},function(e){return t.$v.employee.fields.blood_type.minLength||"El campo Tipo de sangre  debe ser mayor a 2 dígitos."}]},filteredAreaOptions:function(){var e=this;return null!=this.employee.fields.department&&null!=this.employee.fields.department.value?this.areaOptions.filter((function(t){return t.department===e.employee.fields.department.value})):[]},filteredPositionOptions:function(){var e=this;return null!=this.employee.fields.area&&null!=this.employee.fields.area.value?this.positionOptions.filter((function(t){return t.area===e.employee.fields.area.value})):[]},filteredTimetablesOptions:function(){var e=this;return null!=this.employee.fields.shift&&null!=this.employee.fields.shift.value?this.timetablesOptions.filter((function(t){return t.shift===e.employee.fields.shift.value})):[]},incidenciaDateRules:function(e){var t=this;return[function(e){return t.$v.incidencias.fields.assistance_date.required||"El campo Fecha es requerido."}]},incidenciaTypeRules:function(e){var t=this;return[function(e){return t.$v.incidencias.fields.assistance_type.required||"El campo Tipo de incidencia es requerido."}]},sinceRules:function(e){var t=this;return[function(e){return t.$v.requestModal.fields.since.required||"El campo Desde es requerido."}]},untilRules:function(e){var t=this;return[function(e){return t.$v.requestModal.fields.until.required||"El campo Hasta es requerido."}]}},beforeCreate:function(){this.$store.getters["users/roles"].includes(1)||this.$store.getters["users/roles"].includes(3)||this.$store.getters["users/roles"].includes(7)||this.$store.getters["users/roles"].includes(4)||this.$store.getters["users/roles"].includes(6)||this.$router.push("/")},mounted:function(){this.$q.loading.show(),this.fetchFromServer(),this.getVacations(),this.getDepartments(),this.getAreas(),this.getPositions(),this.getShift(),this.getTimetables(),this.getIncidencia(),this.getIncidencias(),this.$q.loading.hide()},methods:{fetchFromServer:function(){var e=this;this.$q.loading.show();var t=this.$route.params.id;n["a"].get("/employees/".concat(t)).then((function(t){var s=t.data;e.employee.fields.code=s.employee.code,e.employee.fields.department={value:s.employee.department_id,label:s.employee.department},e.employee.fields.area={value:s.employee.area_id,label:s.employee.area},e.employee.fields.position={value:s.employee.position_id,label:s.employee.position},e.employee.fields.shift={value:s.employee.shift_id,label:s.employee.shift},e.employee.fields.timetable={value:s.employee.timetable_id,label:s.employee.timetable},e.employee.fields.location=s.employee.location,e.employee.fields.payment_method=s.employee.payment_method,e.employee.fields.date_entry=s.employee.date_entry,e.employee.fields.out_date=s.employee.out_date,e.employee.fields.status={value:s.employee.status,label:s.employee.status},e.employee.fields.name=s.employee.name,e.employee.fields.paternal=s.employee.paternal,e.employee.fields.mathers=s.employee.mathers,e.employee.fields.birth_date=s.employee.birth_date,e.employee.fields.curp=s.employee.curp,e.employee.fields.rfc=s.employee.rfc,e.employee.fields.blood_type=s.employee.blood_type,e.employee.fields.phone=s.employee.phone,e.employee.fields.social_security=s.employee.social_security,e.employee.fields.studies=s.employee.studies,e.employee.fields.specialty=s.employee.specialty,e.employee.fields.expertise=s.employee.expertise,e.employee.fields.street=s.employee.street,e.employee.fields.colony=s.employee.colony,e.employee.fields.municipality=s.employee.municipality,e.employee.fields.zip_code=s.employee.zip_code,e.employee.fields.birth_state=s.employee.birth_state,e.employee.fields.birth_city=s.employee.birth_city,e.employee.fields.lada=s.employee.lada})),this.$q.loading.hide()},getVacations:function(){var e=this,t=this.$route.params.id,s=new Date;this.date1=new Date(s),n["a"].get("/employees/".concat(t)).then((function(t){var s=t.data;if(null!==s.employee.date_entry){var o=s.employee.date_entry.slice(0,2),l=s.employee.date_entry.slice(3,6),i=s.employee.date_entry.slice(6,10);e.date2=new Date(l+o+"/"+i)}n["a"].get("/vacations").then((function(t){var s=t.data;e.data=s.vacations;for(var o=0;o<e.data.length;o++)e.data[o].year<=e.employee.fields.derecho&&(e.vacationDays+=Number(e.data[o].day));e.$q.loading.hide(),e.getTakeVacations()})),null!==s.employee.date_entry&&(e.employee.fields.derecho=e.date1.getFullYear()-e.date2.getFullYear())}))},getTakeVacations:function(){var e=this,t=this.$route.params.id;n["a"].get("/capture-incidencias/vacations/".concat(t)).then((function(t){var s=t.data;e.dataVacations=s.fechas,e.restVacations=Number(e.vacationDays)-Number(s.dias[0].dias)}))},getIncidencias:function(){var e=this,t=this.$route.params.id,s=new Date;this.date1=new Date(s),n["a"].get("/capture-incidencias/".concat(t)).then((function(t){var s=t.data;e.captura=s.capturaIncidencias}))},getDepartments:function(){var e=this;n["a"].get("/departments/options").then((function(t){var s=t.data;e.departmentOptions=s.options}))},getAreas:function(){var e=this;n["a"].get("/areas/options").then((function(t){var s=t.data;e.areaOptions=s.options}))},getPositions:function(){var e=this;n["a"].get("/positions/options").then((function(t){var s=t.data;e.positionOptions=s.options}))},getShift:function(){var e=this;n["a"].get("/shifts/options").then((function(t){var s=t.data;e.shiftOptions=s.options}))},getTimetables:function(){var e=this;n["a"].get("/timetables/options").then((function(t){var s=t.data;e.timetablesOptions=s.options}))},getIncidencia:function(){var e=this;n["a"].get("/incidencias/options").then((function(t){var s=t.data;e.incidenciasOptions=s.options}))},updateEmployee:function(){var e=this;if(this.$v.employee.fields.$reset(),this.$v.employee.fields.$touch(),this.$v.employee.fields.$error)return this.$q.dialog({title:"Error",message:"Por favor, verifique las validaciones.",persistent:!0}),console.log(this.employee.fields),!1;this.$q.loading.show();var t=this.$route.params.id,s=a()({},this.employee.fields);n["a"].put("/employees/".concat(t),s).then((function(t){var s=t.data;e.$q.notify({message:s.message.content,position:"top",color:s.result?"positive":"warning"}),s.result,e.$q.loading.hide()}))},captureIncidence:function(){var e=this;if(this.$v.incidencias.fields.$reset(),this.$v.incidencias.fields.$touch(),this.$v.incidencias.fields.$error)return this.$q.dialog({title:"Error",message:"Por favor, verifique las validaciones.",persistent:!0}),console.log(this.incidencias.fields),!1;if(0===this.restVacations&&4===this.incidencias.fields.assistance_type.value)this.$q.notify({message:"No cuentas con dias de vacaciones restantes",position:"top",color:"warning"});else{this.$q.loading.show();var t=a()({},this.incidencias.fields);n["a"].post("/capture-incidencias",t).then((function(t){var s=t.data;e.$q.notify({message:s.message.content,position:"top",color:s.result?"positive":"warning"}),e.getIncidencias(),s.result?(e.incidencias.fields.assistance_type="",e.incidencias.fields.assistance_date="",e.getTakeVacations(),e.$q.loading.hide()):e.$q.loading.hide()}))}},creatRequest:function(){var e=this;if(this.$v.requestModal.fields.$reset(),this.$v.requestModal.fields.$touch(),this.$v.requestModal.fields.$error)return this.$q.dialog({title:"Error",message:"Por favor, verifique las validaciones.",persistent:!0}),console.log(this.requestModals.fields),!1;var t=this.requestModal.fields.since,s=this.requestModal.fields.until,o=new Date(t.substr(3,2)+"/"+t.substr(0,2)+"/"+t.substr(6,10)),l=new Date(s.substr(3,2)+"-"+s.substr(0,2)+"/"+s.substr(6,10)),i=l.getTime()-o.getTime(),r=i/864e5+1;if(r>this.restVacations)this.$q.notify({message:"Tus vacaciones acreditadas son insuficientes",position:"top",color:"warning"});else{this.$q.loading.show();var c=a()({},this.requestModal.fields);c.since=t.substr(6,10)+"-"+t.substr(3,2)+"-"+t.substr(0,2),c.until=s.substr(6,10)+"-"+s.substr(3,2)+"-"+s.substr(0,2),n["a"].post("/vacations/request",c).then((function(t){var s=t.data;e.$q.notify({message:s.message.content,position:"top",color:s.result?"positive":"warning"})})),this.$q.loading.hide()}},getPeriodo:function(e){var t="se paso de vacaciones, este no entra en ningun periodo y queda por defect",s=0;return this.data.some((function(o,l){if(s+=Number.parseInt(o.day),e+1<=s)return t="AÑO - ".concat(o.year),!0})),t}}},m=u,f=s("2877"),y=s("9989"),b=s("ead5"),v=s("079e"),h=s("f09f"),g=s("429b"),q=s("7460"),x=s("adad"),_=s("823b"),$=s("27f9"),k=s("0016"),C=s("ddd8"),S=s("7cbe"),w=s("52ee"),E=s("9c40"),R=s("eaac"),O=s("bd08"),D=s("db86"),M=s("05c0"),T=s("24e8"),A=s("a370"),I=s("4b7e"),L=s("7f67"),P=s("eebe"),V=s.n(P),N=Object(f["a"])(m,o,l,!1,null,null,null);t["default"]=N.exports;V()(N,"components",{QPage:y["a"],QBreadcrumbs:b["a"],QBreadcrumbsEl:v["a"],QCard:h["a"],QTabs:g["a"],QTab:q["a"],QTabPanels:x["a"],QTabPanel:_["a"],QInput:$["a"],QIcon:k["a"],QSelect:C["a"],QPopupProxy:S["a"],QDate:w["a"],QBtn:E["a"],QTable:R["a"],QTr:O["a"],QTd:D["a"],QTooltip:M["a"],QDialog:T["a"],QCardSection:A["a"],QCardActions:I["a"]}),V()(N,"directives",{ClosePopup:L["a"]})}}]);
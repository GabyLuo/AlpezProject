<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-9">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Empleados" to="/employees" />
              <q-breadcrumbs-el label="Editar Empleado" v-text= "employee.fields.name"/>
            </q-breadcrumbs>
          </div>
        </div>
      </div>
    </div>

    <div class="q-pa-xs">
      <div class="q-gutter-y-md" style="width: 100%">
        <q-card>
          <q-tabs
            v-model="tab"
            dense
            class="text-grey"
            active-color="primary"
            indicator-color="primary"
            align="justify"
            narrow-indicator
          >
            <q-tab name="expediente" icon="rule_folder" label="EXPEDIENTE" />
            <q-tab name="vacations" icon="date_range" label="VACACIONES" />
            <!-- <q-tab name="insidences" icon="how_to_reg" label="INCIDENCIAS" /> -->
          </q-tabs>
          <q-tab-panels v-model="tab" animated class="">
            <q-tab-panel name="expediente">
                <div class="row bg-white border-panel">
                  <div class="col q-pa-md">
                    <div class="row" style="padding-bottom: 15px;">
                      Información laboral
                    </div>
                    <div class="row q-col-gutter-xs">
                      <div class="col-xs-12 col-sm-3">
                        <q-input
                          color="dark"
                          bg-color="secondary"
                          filled
                          v-model="employee.fields.code"
                          :error="$v.employee.fields.code.$error"
                          label="Codigo"
                          :rules="codeRules"
                          @input="v => { employee.fields.code = v.toUpperCase() }"
                        >
                          <template v-slot:prepend>
                            <q-icon name="tag" />
                          </template>
                        </q-input>
                      </div>
                      <div class="col-xs-12 col-sm-3">
                        <q-select
                          color="dark"
                          bg-color="secondary"
                          filled
                          v-model="employee.fields.department"
                          :options="departmentOptions"
                          label="Departamentos"
                          :rules="departmentRules"
                          :error="$v.employee.fields.department.$error"
                        >
                          <template v-slot:prepend>
                            <q-icon name="fas fa-building" />
                          </template>
                        </q-select>
                      </div>
                      <div class="col-xs-12 col-sm-3">
                        <q-select
                          color="dark"
                          bg-color="secondary"
                          filled
                          v-model="employee.fields.area"
                          :options="filteredAreaOptions"
                          label="Areas"
                          :rules="areaRules"
                          :error="$v.employee.fields.area.$error"
                        >
                          <template v-slot:prepend>
                            <q-icon name="list" />
                          </template>
                        </q-select>
                      </div>
                      <div class="col-xs-12 col-sm-3">
                        <q-select
                          color="dark"
                          bg-color="secondary"
                          filled
                          v-model="employee.fields.position"
                          :options="filteredPositionOptions"
                          label="Puesto"
                          :rules="positionRules"
                          :error="$v.employee.fields.position.$error"
                        >
                          <template v-slot:prepend>
                            <q-icon name="fas fa-clipboard-list" />
                          </template>
                        </q-select>
                      </div>
                      <div class="col-xs-12 col-sm-3">
                        <q-select
                          color="dark"
                          bg-color="secondary"
                          filled
                          v-model="employee.fields.shift"
                          :options="shiftOptions"
                          label="Turno"
                          :rules="shiftRules"
                          :error="$v.employee.fields.shift.$error"
                        >
                          <template v-slot:prepend>
                            <q-icon name="restore" />
                          </template>
                        </q-select>
                      </div>
                      <div class="col-xs-12 col-sm-3">
                        <q-select
                          color="dark"
                          bg-color="secondary"
                          filled
                          v-model="employee.fields.timetable"
                          :options="filteredTimetablesOptions"
                          label="Horario"
                        >
                          <template v-slot:prepend>
                            <q-icon name="restore" />
                          </template>
                        </q-select>
                      </div>
                      <div class="col-xs-12 col-sm-6">
                        <q-input
                          color="dark"
                          bg-color="secondary"
                          filled
                          v-model="employee.fields.location"
                          label="Ubicación"
                          @input="v => { employee.fields.location = v.toUpperCase() }"
                        >
                          <template v-slot:prepend>
                            <q-icon name="radar" />
                          </template>
                        </q-input>
                      </div>
                      <div class="col-xs-12 col-sm-3">
                        <q-input
                          color="dark"
                          bg-color="secondary"
                          filled
                          v-model="employee.fields.payment_method"
                          label="Forma de pago"
                          @input="v => { employee.fields.payment_method = v.toUpperCase() }"
                        >
                          <template v-slot:prepend>
                            <q-icon name="attach_money" />
                          </template>
                        </q-input>
                      </div>
                      <div class="col-xs-12 col-sm-3 text-center">
                          <q-select color="dark"
                          bg-color="secondary"
                          filled
                          v-model="employee.fields.date_entry"
                          mask="date"
                          label="Fecha de ingreso">
                          <template v-slot:prepend>
                              <q-icon name="event"></q-icon>
                          </template>
                          <q-popup-proxy
                          ref="date_ref"
                          transition-show="scale"
                          transition-hide="scale">
                              <div class="col-sm-12">
                                  <q-date
                                  color="secondary"
                                  text-color="white"
                                  mask="DD/MM/YYYY"
                                  v-model="employee.fields.date_entry"
                                  @input="() => $refs.date_ref.hide()"
                                  today-btn>
                                  </q-date>
                              </div>
                          </q-popup-proxy>
                          </q-select>
                      </div>
                      <div class="col-xs-12 col-sm-3 text-center">
                          <q-select color="dark"
                          bg-color="secondary"
                          filled
                          v-model="employee.fields.out_date"
                          mask="date"
                          label="Fecha de baja">
                          <template v-slot:prepend>
                              <q-icon name="event"></q-icon>
                          </template>
                          <q-popup-proxy
                          ref="date_ref"
                          transition-show="scale"
                          transition-hide="scale">
                              <div class="col-sm-12">
                                  <q-date
                                  color="secondary"
                                  text-color="white"
                                  mask="DD/MM/YYYY"
                                  v-model="employee.fields.out_date"
                                  @input="() => $refs.date_ref.hide()"
                                  today-btn>
                                  </q-date>
                              </div>
                          </q-popup-proxy>
                          </q-select>
                      </div>
                      <div class="col-xs-12 col-sm-3">
                        <q-select
                          color="dark"
                          bg-color="secondary"
                          filled
                          v-model="employee.fields.status"
                          :error="$v.employee.fields.status.$error"
                          :options="statusOptions"
                          label="Status"
                          :rules="statusRules"
                        >
                          <template v-slot:prepend>
                            <q-icon name="inventory" />
                          </template>
                        </q-select>
                      </div>
                    </div>
                    <div class="row" style="padding-bottom: 15px; padding-top: 15px">
                      Información general
                    </div>
                    <div class="row q-col-gutter-xs">
                      <div class="col-xs-12 col-sm-4">
                        <q-input
                          color="dark"
                          bg-color="secondary"
                          filled
                          v-model="employee.fields.name"
                          :error="$v.employee.fields.name.$error"
                          label="Nombre"
                          :rules="nameRules"
                          @input="v => { employee.fields.name = v.toUpperCase() }"
                        >
                          <template v-slot:prepend>
                            <q-icon name="fas fa-signature" />
                          </template>
                        </q-input>
                      </div>
                      <div class="col-xs-12 col-sm-4">
                        <q-input
                          color="dark"
                          bg-color="secondary"
                          filled
                          v-model="employee.fields.paternal"
                          :error="$v.employee.fields.paternal.$error"
                          label="Apellido paterno"
                          :rules="paternalRules"
                          @input="v => { employee.fields.paternal = v.toUpperCase() }"
                        >
                          <template v-slot:prepend>
                            <q-icon name="fas fa-signature" />
                          </template>
                        </q-input>
                      </div>
                      <div class="col-xs-12 col-sm-4">
                        <q-input
                          color="dark"
                          bg-color="secondary"
                          filled
                          v-model="employee.fields.mathers"
                          label="Apellido materno"
                          @input="v => { employee.fields.mathers = v.toUpperCase() }"
                        >
                          <template v-slot:prepend>
                            <q-icon name="fas fa-signature" />
                          </template>
                        </q-input>
                      </div>
                      <div class="col-xs-12 col-sm-4 text-center">
                          <q-select color="dark"
                          bg-color="secondary"
                          filled
                          v-model="employee.fields.birth_date"
                          mask="date"
                          label="Fecha de nacimiento">
                          <template v-slot:prepend>
                              <q-icon name="event"></q-icon>
                          </template>
                          <q-popup-proxy
                          ref="date_ref"
                          transition-show="scale"
                          transition-hide="scale">
                              <div class="col-sm-12">
                                  <q-date
                                  color="secondary"
                                  text-color="white"
                                  mask="DD/MM/YYYY"
                                  v-model="employee.fields.birth_date"
                                  today-btn>
                                  </q-date>
                              </div>
                          </q-popup-proxy>
                          </q-select>
                      </div>
                      <div class="col-xs-12 col-sm-4">
                        <q-input
                          color="dark"
                          bg-color="secondary"
                          filled
                          v-model="employee.fields.curp"
                          label="CURP"
                          :error="$v.employee.fields.curp.$error"
                          :rules="curpRules"
                          @input="v => { employee.fields.curp = v.toUpperCase() }"
                        >
                          <template v-slot:prepend>
                            <q-icon name="person_search" />
                          </template>
                        </q-input>
                      </div>
                      <div class="col-xs-12 col-sm-4">
                        <q-input
                          color="dark"
                          bg-color="secondary"
                          filled
                          :rules="rfcRules"
                          v-model="employee.fields.rfc"
                          label="RFC"
                          :error="$v.employee.fields.rfc.$error"
                          @input="v => { employee.fields.rfc = v.toUpperCase() }"
                        >
                          <template v-slot:prepend>
                            <q-icon name="hourglass_top" />
                          </template>
                        </q-input>
                      </div>
                      <div class="col-xs-12 col-sm-4">
                        <q-input
                          color="dark"
                          bg-color="secondary"
                          filled
                          :rules="social_securityRules"
                          v-model="employee.fields.social_security"
                          :error="$v.employee.fields.social_security.$error"
                          label="Numero de seguro social"
                        >
                          <template v-slot:prepend>
                            <q-icon name="health_and_safety" />
                          </template>
                        </q-input>
                      </div>
                      <div class="col-xs-12 col-sm-1">
                        <q-input
                          color="dark"
                          bg-color="secondary"
                          filled
                          :rules="ladaRules"
                          :error="$v.employee.fields.lada.$error"
                          v-model="employee.fields.lada"
                          label="Lada"
                        >
                          <template v-slot:prepend>
                            <q-icon name="call" />
                          </template>
                        </q-input>
                      </div>
                      <div class="col-xs-12 col-sm-3">
                        <q-input
                          color="dark"
                          bg-color="secondary"
                          filled
                          :rules="phoneRules"
                          :error="$v.employee.fields.phone.$error"
                          v-model="employee.fields.phone"
                          label="Telefono"
                        >
                          <template v-slot:prepend>
                            <q-icon name="call" />
                          </template>
                        </q-input>
                      </div>
                      <div class="col-xs-12 col-sm-4">
                        <q-input
                          color="dark"
                          bg-color="secondary"
                          filled
                          v-model="employee.fields.blood_type"
                          label="Tipo de sangre"
                          :rules="blood_typeRules"
                          :error="$v.employee.fields.blood_type.$error"
                          @input="v => { employee.fields.blood_type = v.toUpperCase() }"
                        >
                          <template v-slot:prepend>
                            <q-icon name="bloodtype" />
                          </template>
                        </q-input>
                      </div>
                    </div>
                    <div class="row" style="padding-bottom: 15px; padding-top: 15px">
                      Estudios
                    </div>
                    <div class="row q-col-gutter-xs">
                      <div class="col-xs-12 col-sm-6">
                        <q-input
                          color="dark"
                          bg-color="secondary"
                          filled
                          v-model="employee.fields.studies"
                          label="Escolaridad"
                          @input="v => { employee.fields.studies = v.toUpperCase() }"
                        >
                          <template v-slot:prepend>
                            <q-icon name="school" />
                          </template>
                        </q-input>
                      </div>
                      <div class="col-xs-12 col-sm-6">
                        <q-input
                          color="dark"
                          bg-color="secondary"
                          filled
                          v-model="employee.fields.specialty"
                          label="Especialidad"
                          @input="v => { employee.fields.specialty = v.toUpperCase() }"
                        >
                          <template v-slot:prepend>
                            <q-icon name="history_edu" />
                          </template>
                        </q-input>
                      </div>
                      <div class="col-xs-12 col-sm-12">
                        <q-input
                          color="dark"
                          bg-color="secondary"
                          filled
                          v-model="employee.fields.expertise"
                          label="Otros conocimientos"
                          @input="v => { employee.fields.expertise = v.toUpperCase() }"
                        >
                          <template v-slot:prepend>
                            <q-icon name="note_alt" />
                          </template>
                        </q-input>
                      </div>
                    </div>
                    <div class="row" style="padding-bottom: 15px; padding-top: 15px">
                      Domicilio
                    </div>
                    <div class="row q-col-gutter-xs">
                      <div class="col-xs-12 col-sm-8">
                        <q-input
                          color="dark"
                          bg-color="secondary"
                          filled
                          v-model="employee.fields.street"
                          label="Calle"
                          @input="v => { employee.fields.street = v.toUpperCase() }"
                        >
                          <template v-slot:prepend>
                            <q-icon name="room" />
                          </template>
                        </q-input>
                      </div>
                      <div class="col-xs-12 col-sm-4">
                        <q-input
                          color="dark"
                          bg-color="secondary"
                          filled
                          v-model="employee.fields.colony"
                          label="Colonia"
                          @input="v => { employee.fields.colony = v.toUpperCase() }"
                        >
                          <template v-slot:prepend>
                            <q-icon name="business" />
                          </template>
                        </q-input>
                      </div>
                      <div class="col-xs-12 col-sm-3">
                        <q-input
                          color="dark"
                          bg-color="secondary"
                          filled
                          v-model="employee.fields.municipality"
                          label="Municipio"
                          @input="v => { employee.fields.municipality = v.toUpperCase() }"
                        >
                          <template v-slot:prepend>
                            <q-icon name="map" />
                          </template>
                        </q-input>
                      </div>
                      <div class="col-xs-12 col-sm-3">
                        <q-input
                          color="dark"
                          bg-color="secondary"
                          filled
                          v-model="employee.fields.zip_code"
                          label="Código postal"
                          @input="v => { employee.fields.zip_code = v.toUpperCase() }"
                        >
                          <template v-slot:prepend>
                            <q-icon name="language" />
                          </template>
                        </q-input>
                      </div>
                      <div class="col-xs-12 col-sm-3">
                        <q-input
                          color="dark"
                          bg-color="secondary"
                          filled
                          v-model="employee.fields.birth_state"
                          label="Estado de nacimiento"
                          @input="v => { employee.fields.birth_state = v.toUpperCase() }"
                        >
                          <template v-slot:prepend>
                            <q-icon name="flag" />
                          </template>
                        </q-input>
                      </div>
                      <div class="col-xs-12 col-sm-3">
                        <q-input
                          color="dark"
                          bg-color="secondary"
                          filled
                          v-model="employee.fields.birth_city"
                          label="Ciudad de nacimiento"
                          @input="v => { employee.fields.birth_city = v.toUpperCase() }"
                        >
                          <template v-slot:prepend>
                            <q-icon name="location_city" />
                          </template>
                        </q-input>
                      </div>
                    </div>
                    <div class="row q-mb-sm q-mt-md">
                      <div class="col-xs-12 col-sm-2 offset-sm-10 pull-right">
                        <q-btn color="positive" icon="save" label="Guardar" @click="updateEmployee()" />
                      </div>
                    </div>
                  </div>
              </div>
            </q-tab-panel>
            <q-tab-panel name="vacations">
              <div class="row border-panel" style="margin-bottom: 20px">
                <div class="col-xs-12 col-sm-9">
                  <p style="margin-left: 8px; margin-top: 20px; font-size: 18px">Dias de vacaciones restantes: {{ restVacations }} dias.</p>
                </div>
                <div class="col-xs-12 col-sm-3 pull-right text-center">
                  <q-btn style="margin: 15px;" color="positive" icon="add" label="SOLICITAR"  @click="request = true" />
                </div>
              </div>
              <div class="row bg-white border-panel">
                <div class="col-xs-12 col-sm-5" style="padding: 20px">
                  <q-table
                    title="VACACIONES ACREDITADAS"
                    flat
                    bordered
                    :data="data"
                    :columns="columns"
                    row-key="code"
                    :pagination.sync="pagination"
                    :filter="filter"
                  >
                    <template v-slot:body="props">
                      <q-tr :props="props">
                        <q-td key="years" :style="props.row.year === employee.fields.derecho+'' ? 'text-align: center; background-color: #21ba45;' : 'text-align: center;'" :props="props">{{ props.row.year }}</q-td>
                        <q-td key="days" :style="props.row.year === employee.fields.derecho+'' ? 'text-align: center; background-color: #21ba45;' : 'text-align: center;'" :props="props">{{ props.row.day }}</q-td>
                      </q-tr>
                    </template>
                  </q-table>
                </div>
                <div class="col-xs-12 col-sm-7" style="padding: 20px">
                  <q-table
                    title="DIAS DE VACACIONES TOMADOS"
                    flat
                    bordered
                    :data="dataVacations"
                    :columns="columnsVacations"
                    row-key="code"
                    :pagination.sync="pagination"
                    :filter="filter"
                  >
                    <template v-slot:body="props">
                      <q-tr :props="props">
                        <q-td key="date" style="text-align: center;" :props="props">{{ props.row.date }}</q-td>
                        <q-td key="vacation_year" style="text-align: center" :props="props">{{ getPeriodo(props.rowIndex) }}</q-td>
                      </q-tr>
                    </template>
                  </q-table>
                </div>
              </div>
            </q-tab-panel>
            <!-- <q-tab-panel name="insidences">
            <div class="row bg-white border-panel">
              <div class="col q-pa-md">
                <div class="row q-col-gutter-xs">
                  <div class="col-xs-12 col-sm-4">
                    <q-select
                      color="white"
                      bg-color="secondary"
                      filled
                      dark
                      v-model="incidencias.fields.assistance_type"
                      :options="incidenciasOptions"
                      label="Tipo de incidencia"
                      :rules="incidenciaTypeRules"
                      :error="$v.incidencias.fields.assistance_type.$error"
                    >
                      <template v-slot:prepend>
                        <q-icon name="fas fa-building" />
                      </template>
                    </q-select>
                  </div>
                  <div class="col-xs-12 col-sm-4 text-center">
                      <q-select color="white"
                      bg-color="secondary"
                      filled
                      dark
                      v-model="incidencias.fields.assistance_date"
                      :rules="incidenciaDateRules"
                      :error="$v.incidencias.fields.assistance_date.$error"
                      mask="date"
                      label="Fecha">
                      <template v-slot:prepend>
                          <q-icon name="event"></q-icon>
                      </template>
                      <q-popup-proxy
                      ref="date_ref"
                      transition-show="scale"
                      transition-hide="scale">
                          <div class="col-sm-12">
                              <q-date
                              color="secondary"
                              text-color="white"
                              mask="DD/MM/YYYY"
                              v-model="incidencias.fields.assistance_date"
                              @input="() => $refs.date_ref.hide()"
                              today-btn>
                              </q-date>
                          </div>
                      </q-popup-proxy>
                      </q-select>
                  </div>
                    <div class="col-xs-12 col-sm-4 text-center pull-right">
                      <q-btn color="positive" icon="save" label="Capturar" @click="captureIncidence()" />
                    </div>
                </div>
              </div>
            </div>
              <div style="margin-top: 10px" class="row bg-white border-panel">
                <div class="col q-pa-md">
                  <q-table
                    flat
                    bordered
                    :data="captura"
                    :columns="columnsIncidencias"
                    row-key="code"
                    :pagination.sync="pagination"
                    :filter="filter"
                  >
                    <template v-slot:body="props">
                      <q-tr :props="props">
                        <q-td key="type" style="text-align: left;" :props="props">{{ props.row.nombre }}</q-td>
                        <q-td key="date" style="text-align: center;" :props="props">{{ props.row.fecha }}</q-td>
                        <q-td key="hour" style="text-align: center;" :props="props">{{ props.row.fecha.slice(10, 19) }}</q-td>
                        <q-td key="actions" style="text-align: left;" :props="props">
                          <q-btn color="primary" icon="fas fa-edit" flat @click.native="editSelectedRow(props.row.id)" size="10px">
                            <q-tooltip content-class="bg-primary">Editar</q-tooltip>
                          </q-btn>
                          <q-btn color="red" icon="fas fa-trash-alt" flat @click.native="deleteSelectedRow(props.row.id)" size="10px">
                            <q-tooltip content-class="bg-red">Eliminar</q-tooltip>
                          </q-btn>
                        </q-td>
                      </q-tr>
                    </template>
                  </q-table>
                </div>
              </div>
            </q-tab-panel> -->
          </q-tab-panels>
        </q-card>
      </div>
    </div>
    <q-dialog v-model="request" persistent>
      <q-card style="min-width: 750px">
        <q-card-section>
          <div class="text-h6">Solicitar periodo vacacional</div>
        </q-card-section>

        <q-card-section class="q-pt-none">
              <div class="row q-col-gutter-xs">
                <div class="col-xs-12 col-sm-6 text-center">
                    <q-select color="dark"
                    bg-color="secondary"
                    filled
                    v-model="requestModal.fields.since"
                    :rules="sinceRules"
                    :error="$v.requestModal.fields.since.$error"
                    mask="date"
                    label="Desde">
                    <template v-slot:prepend>
                        <q-icon name="event"></q-icon>
                    </template>
                    <q-popup-proxy
                    ref="date_ref"
                    transition-show="scale"
                    transition-hide="scale">
                        <div class="col-sm-12">
                            <q-date
                            color="secondary"
                            text-color="white"
                            mask="DD/MM/YYYY"
                            v-model="requestModal.fields.since"
                            @input="() => $refs.date_ref.hide()"
                            today-btn>
                            </q-date>
                        </div>
                    </q-popup-proxy>
                    </q-select>
                </div>
                <div class="col-xs-12 col-sm-6 text-center">
                    <q-select color="dark"
                    bg-color="secondary"
                    filled
                    v-model="requestModal.fields.until"
                    :rules="untilRules"
                    :error="$v.requestModal.fields.until.$error"
                    mask="date"
                    label="Hasta">
                    <template v-slot:prepend>
                        <q-icon name="event"></q-icon>
                    </template>
                    <q-popup-proxy
                    ref="date_ref"
                    transition-show="scale"
                    transition-hide="scale">
                        <div class="col-sm-12">
                            <q-date
                            color="secondary"
                            text-color="white"
                            mask="DD/MM/YYYY"
                            v-model="requestModal.fields.until"
                            @input="() => $refs.date_ref.hide()"
                            today-btn>
                            </q-date>
                        </div>
                    </q-popup-proxy>
                    </q-select>
                </div>
              </div>
        </q-card-section>

        <q-card-actions align="right" class="text-primary">
          <div class="col-xs-12 col-sm-4 text-center pull-right">
            <q-btn color="negative" label="Cancel" style="margin-right: 10px;" v-close-popup />
            <q-btn color="positive" label="Solicitar" @click="creatRequest()" v-close-popup />
          </div>
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import api from '../../commons/api.js'
const { required, maxLength, minLength } = require('vuelidate/lib/validators')

export default {
  name: 'NewEmployee',
  validations: {
    employee: {
      fields: {
        code: { required, maxLength: maxLength(6) },
        department: { required },
        area: { required },
        position: { required },
        shift: { required },
        // payment_method: { required },
        // date_entry: { required },
        status: { required },
        name: { required },
        paternal: { required },
        curp: { maxLength: maxLength(20), minLength: minLength(18) },
        rfc: { maxLength: maxLength(15), minLength: minLength(12) },
        social_security: { maxLength: maxLength(14), minLength: minLength(11) },
        phone: { maxLength: maxLength(9), minLength: minLength(7) },
        blood_type: { maxLength: maxLength(3), minLength: minLength(2) },
        lada: { maxLength: maxLength(3), minLength: minLength(2) }
      }
    },
    incidencias: {
      fields: {
        assistance_date: { required },
        assistance_type: { required }
      }
    },
    requestModal: {
      fields: {
        since: { required },
        until: { required }
      }
    }
  },
  data () {
    return {
      tab: 'expediente',
      request: false,
      employee: {
        fields: {
          code: null,
          department: null,
          area: null,
          position: null,
          shift: null,
          location: null,
          payment_method: null,
          date_entry: null,
          out_date: null,
          status: null,
          name: null,
          paternal: null,
          mathers: null,
          birth_date: null,
          curp: null,
          rfc: null,
          social_security: null,
          phone: 0,
          blood_type: null,
          studies: null,
          specialty: null,
          expertise: null,
          street: null,
          colony: null,
          municipality: null,
          zip_code: null,
          birth_state: null,
          birth_city: null,
          lada: null,
          timetable: null,
          derecho: null
        }
      },
      incidencias: {
        fields: {
          employee_id: this.$route.params.id,
          assistance_type: null,
          assistance_date: null
        }
      },
      requestModal: {
        fields: {
          employee_id: this.$route.params.id,
          since: null,
          until: null
        }
      },
      pagination: {
        sortBy: 'code',
        descending: false,
        rowsPerPage: 25
      },
      columns: [
        { name: 'years', align: 'center', label: 'AÑOS', field: 'years', sortable: false },
        { name: 'days', align: 'center', label: 'DIAS', field: 'days', sortable: false }
      ],
      columnsIncidencias: [
        { name: 'type', align: 'center', label: 'TIPO INCIDENCIA', field: 'type', sortable: false },
        { name: 'date', align: 'center', label: 'FECHA', field: 'date', sortable: false }
        // { name: 'hour', align: 'center', label: 'HORA', field: 'hour', sortable: true },
      ],
      columnsVacations: [
        { name: 'date', align: 'center', label: 'FECHA', field: 'date', sortable: false },
        { name: 'vacation_year', align: 'center', label: 'AÑO DE VACACIONES', field: 'vacation_year', sortable: false }
      ],
      data: [],
      captura: [],
      filter: '',
      departmentOptions: [],
      areaOptions: [],
      positionOptions: [],
      shiftOptions: [],
      timetablesOptions: [],
      incidenciasOptions: [],
      statusOptions: [
        {
          label: 'ACTIVO',
          value: 'ACTIVO'
        },
        {
          label: 'INACTIVO',
          value: 'INACTIVO'
        }
      ],
      dataVacations: [],
      vacationDays: null,
      restVacations: null
    }
  },
  computed: {
    codeRules (val) {
      return [
        val => (this.$v.employee.fields.code.required) || 'El campo Codigo es requerido.',
        val => (this.$v.employee.fields.code.maxLength) || 'El campo Codigo no debe exceder los 6 dígitos.'
      ]
    },
    departmentRules (val) {
      return [
        val => this.$v.employee.fields.department.required || 'El campo Departamento es requerido.'
      ]
    },
    areaRules (val) {
      return [
        val => this.$v.employee.fields.area.required || 'El campo Area es requerido.'
      ]
    },
    positionRules (val) {
      return [
        val => this.$v.employee.fields.position.required || 'El campo Puesto es requerido.'
      ]
    },
    shiftRules (val) {
      return [
        val => this.$v.employee.fields.shift.required || 'El campo Turno es requerido.'
      ]
    },
    /* payment_methodRules (val) {
      return [
        val => this.$v.employee.fields.payment_method.required || 'El campo Forma de pago es requerido.'
      ]
    },
    date_entryRules (val) {
      return [
        val => this.$v.employee.fields.date_entry.required || 'El campo Fecha de ingreso es requerido.'
      ]
    }, */
    statusRules (val) {
      return [
        val => this.$v.employee.fields.status.required || 'El campo Status es requerido.'
      ]
    },
    nameRules (val) {
      return [
        val => this.$v.employee.fields.name.required || 'El campo Nombre es requerido.'
      ]
    },
    paternalRules (val) {
      return [
        val => this.$v.employee.fields.paternal.required || 'El campo Apellido paterno es requerido.'
      ]
    },
    curpRules (val) {
      return [
        val => this.$v.employee.fields.curp.maxLength || 'El campo CURP no debe exceder los 20 dígitos.',
        val => this.$v.employee.fields.curp.minLength || 'El campo CURP no debe ser menor a 18 dígitos.'
      ]
    },
    rfcRules (val) {
      return [
        val => this.$v.employee.fields.rfc.maxLength || 'El campo RFC no debe exceder los 15 dígitos.',
        val => this.$v.employee.fields.rfc.minLength || 'El campo RFC no debe ser menor a los 12 dígitos.'
      ]
    },
    social_securityRules (val) {
      return [
        val => this.$v.employee.fields.social_security.maxLength || 'El campo Numero de seguro no debe exceder los 14 dígitos.',
        val => this.$v.employee.fields.social_security.minLength || 'El campo Numero de seguro no debe ser menor a 11 dígitos.'
        // val => this.$v.employee.fields.social_security.numeric || 'El campo Numero de seguro debe ser numerico.'
      ]
    },
    ladaRules (val) {
      return [
        val => this.$v.employee.fields.lada.maxLength || 'El campo Lada no debe exceder los 3 dígitos.',
        val => this.$v.employee.fields.lada.minLength || 'El campo Lada no debe ser menor a los 2 dígitos.'
        // val => this.$v.employee.fields.lada.numeric || 'El campo Lada debe ser numerico.'
      ]
    },
    phoneRules (val) {
      return [
        val => this.$v.employee.fields.phone.maxLength || 'El campo Telefono no debe exceder los 9 dígitos.',
        val => this.$v.employee.fields.phone.minLength || 'El campo Telefono no debe ser menor a los 7 dígitos.'
        // val => this.$v.employee.fields.phone.numeric || 'El campo Telefono debe ser numerico.'
      ]
    },
    blood_typeRules (val) {
      return [
        val => this.$v.employee.fields.blood_type.maxLength || 'El campo Tipo de sangre no debe exceder los 3 dígitos.',
        val => this.$v.employee.fields.blood_type.minLength || 'El campo Tipo de sangre  debe ser mayor a 2 dígitos.'
      ]
    },
    filteredAreaOptions () {
      if (this.employee.fields.department != null && this.employee.fields.department.value != null) {
        return this.areaOptions.filter(area => area.department === this.employee.fields.department.value)
      }
      return []
    },
    filteredPositionOptions () {
      if (this.employee.fields.area != null && this.employee.fields.area.value != null) {
        return this.positionOptions.filter(position => position.area === this.employee.fields.area.value)
      }
      return []
    },
    filteredTimetablesOptions () {
      if (this.employee.fields.shift != null && this.employee.fields.shift.value != null) {
        return this.timetablesOptions.filter(timetables => timetables.shift === this.employee.fields.shift.value)
      }
      return []
    },
    incidenciaDateRules (val) {
      return [
        val => this.$v.incidencias.fields.assistance_date.required || 'El campo Fecha es requerido.'
      ]
    },
    incidenciaTypeRules (val) {
      return [
        val => this.$v.incidencias.fields.assistance_type.required || 'El campo Tipo de incidencia es requerido.'
      ]
    },
    sinceRules (val) {
      return [
        val => this.$v.requestModal.fields.since.required || 'El campo Desde es requerido.'
      ]
    },
    untilRules (val) {
      return [
        val => this.$v.requestModal.fields.until.required || 'El campo Hasta es requerido.'
      ]
    }
  },
  beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(4) && !this.$store.getters['users/roles'].includes(6)) {
      this.$router.push('/')
    }
  },
  mounted () {
    this.$q.loading.show()
    this.fetchFromServer()
    this.getVacations()
    this.getDepartments()
    this.getAreas()
    this.getPositions()
    this.getShift()
    this.getTimetables()
    this.getIncidencia()
    this.getIncidencias()
    this.$q.loading.hide()
  },
  methods: {
    fetchFromServer () {
      this.$q.loading.show()
      const id = this.$route.params.id
      api.get(`/employees/${id}`).then(({ data }) => {
        /* if (!data.employee) {
          this.$router.push('/employees')
        } else { */
        this.employee.fields.code = data.employee.code
        this.employee.fields.department = { value: data.employee.department_id, label: data.employee.department }
        this.employee.fields.area = { value: data.employee.area_id, label: data.employee.area }
        this.employee.fields.position = { value: data.employee.position_id, label: data.employee.position }
        this.employee.fields.shift = { value: data.employee.shift_id, label: data.employee.shift }
        this.employee.fields.timetable = { value: data.employee.timetable_id, label: data.employee.timetable }
        this.employee.fields.location = data.employee.location
        this.employee.fields.payment_method = data.employee.payment_method
        this.employee.fields.date_entry = data.employee.date_entry
        this.employee.fields.out_date = data.employee.out_date
        this.employee.fields.status = { value: data.employee.status, label: data.employee.status }
        this.employee.fields.name = data.employee.name
        this.employee.fields.paternal = data.employee.paternal
        this.employee.fields.mathers = data.employee.mathers
        this.employee.fields.birth_date = data.employee.birth_date
        this.employee.fields.curp = data.employee.curp
        this.employee.fields.rfc = data.employee.rfc
        this.employee.fields.blood_type = data.employee.blood_type
        this.employee.fields.phone = data.employee.phone
        this.employee.fields.social_security = data.employee.social_security
        this.employee.fields.studies = data.employee.studies
        this.employee.fields.specialty = data.employee.specialty
        this.employee.fields.expertise = data.employee.expertise
        this.employee.fields.street = data.employee.street
        this.employee.fields.colony = data.employee.colony
        this.employee.fields.municipality = data.employee.municipality
        this.employee.fields.zip_code = data.employee.zip_code
        this.employee.fields.birth_state = data.employee.birth_state
        this.employee.fields.birth_city = data.employee.birth_city
        this.employee.fields.lada = data.employee.lada
        // }
      })
      this.$q.loading.hide()
    },
    getVacations () {
      const id = this.$route.params.id
      const date = new Date()
      this.date1 = new Date(date)
      api.get(`/employees/${id}`).then(({ data }) => {
        if (data.employee.date_entry !== null) {
          const dia = data.employee.date_entry.slice(0, 2)
          const mes = data.employee.date_entry.slice(3, 6)
          const año = data.employee.date_entry.slice(6, 10)
          this.date2 = new Date(mes + dia + '/' + año)
        }
        api.get('/vacations').then(({ data }) => {
          this.data = data.vacations
          for (let i = 0; i < this.data.length; i++) {
            if (this.data[i].year <= this.employee.fields.derecho) {
              this.vacationDays += Number(this.data[i].day)
            }
          }
          this.$q.loading.hide()
          this.getTakeVacations()
        })
        if (data.employee.date_entry !== null) {
          this.employee.fields.derecho = this.date1.getFullYear() - this.date2.getFullYear()
        }
      })
    },
    getTakeVacations () {
      const id = this.$route.params.id
      api.get(`/capture-incidencias/vacations/${id}`).then(({ data }) => {
        this.dataVacations = data.fechas
        this.restVacations = Number(this.vacationDays) - Number(data.dias[0].dias)
      })
    },
    getIncidencias () {
      const id = this.$route.params.id
      const date = new Date()
      this.date1 = new Date(date)
      api.get(`/capture-incidencias/${id}`).then(({ data }) => {
        this.captura = data.capturaIncidencias
      })
    },
    getDepartments () {
      api.get('/departments/options').then(({ data }) => {
        this.departmentOptions = data.options
      })
    },
    getAreas () {
      api.get('/areas/options').then(({ data }) => {
        this.areaOptions = data.options
      })
    },
    getPositions () {
      api.get('/positions/options').then(({ data }) => {
        this.positionOptions = data.options
      })
    },
    getShift () {
      api.get('/shifts/options').then(({ data }) => {
        this.shiftOptions = data.options
      })
    },
    getTimetables () {
      api.get('/timetables/options').then(({ data }) => {
        this.timetablesOptions = data.options
      })
    },
    getIncidencia () {
      api.get('/incidencias/options').then(({ data }) => {
        this.incidenciasOptions = data.options
      })
    },
    updateEmployee () {
      this.$v.employee.fields.$reset()
      this.$v.employee.fields.$touch()
      if (this.$v.employee.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        console.log(this.employee.fields)
        return false
      }
      this.$q.loading.show()
      const id = this.$route.params.id
      const params = { ...this.employee.fields }
      api.put(`/employees/${id}`, params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          // this.$router.push('/employees')
        } else {
          this.$q.loading.hide()
        }
      })
    },
    captureIncidence () {
      this.$v.incidencias.fields.$reset()
      this.$v.incidencias.fields.$touch()
      if (this.$v.incidencias.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        console.log(this.incidencias.fields)
        return false
      }
      if (this.restVacations === 0 && this.incidencias.fields.assistance_type.value === 4) {
        this.$q.notify({
          message: 'No cuentas con dias de vacaciones restantes',
          position: 'top',
          color: 'warning'
        })
      } else {
        this.$q.loading.show()
        const params = { ...this.incidencias.fields }
        api.post('/capture-incidencias', params).then(({ data }) => {
          this.$q.notify({
            message: data.message.content,
            position: 'top',
            color: (data.result ? 'positive' : 'warning')
          })
          this.getIncidencias()
          if (data.result) {
            this.incidencias.fields.assistance_type = ''
            this.incidencias.fields.assistance_date = ''
            this.getTakeVacations()
            this.$q.loading.hide()
          } else {
            this.$q.loading.hide()
          }
        })
      }
    },
    creatRequest () {
      this.$v.requestModal.fields.$reset()
      this.$v.requestModal.fields.$touch()
      if (this.$v.requestModal.fields.$error) {
        this.$q.dialog({
          title: 'Error',
          message: 'Por favor, verifique las validaciones.',
          persistent: true
        })
        console.log(this.requestModals.fields)
        return false
      }
      const dateSince = this.requestModal.fields.since
      const dateUntil = this.requestModal.fields.until
      const dateOne = new Date(dateSince.substr(3, 2) + '/' + dateSince.substr(0, 2) + '/' + dateSince.substr(6, 10))
      const dateTwo = new Date(dateUntil.substr(3, 2) + '-' + dateUntil.substr(0, 2) + '/' + dateUntil.substr(6, 10))
      const difTime = dateTwo.getTime() - dateOne.getTime()
      const difDay = (difTime / (1000 * 60 * 60 * 24)) + 1
      if (difDay > this.restVacations) {
        this.$q.notify({
          message: 'Tus vacaciones acreditadas son insuficientes',
          position: 'top',
          color: 'warning'
        })
      } else {
        this.$q.loading.show()
        const params = { ...this.requestModal.fields }
        params.since = dateSince.substr(6, 10) + '-' + dateSince.substr(3, 2) + '-' + dateSince.substr(0, 2)
        params.until = dateUntil.substr(6, 10) + '-' + dateUntil.substr(3, 2) + '-' + dateUntil.substr(0, 2)
        api.post('/vacations/request', params).then(({ data }) => {
          this.$q.notify({
            message: data.message.content,
            position: 'top',
            color: (data.result ? 'positive' : 'warning')
          })
        })
        this.$q.loading.hide()
      }
    },
    getPeriodo (indice) {
      var periodo = 'se paso de vacaciones, este no entra en ningun periodo y queda por defect'
      var days = 0
      this.data.some((element, index) => {
        days += Number.parseInt(element.day)
        if (indice + 1 <= days) {
          periodo = `AÑO - ${element.year}`
          return true
        }
      })
      return periodo
    }
  }
}
</script>

<style>
</style>

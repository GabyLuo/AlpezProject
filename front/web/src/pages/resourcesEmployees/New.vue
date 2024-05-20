<template>
  <q-page class="bg-grey-3">
    <div class="q-pa-sm panel-header">
      <div class="row">
        <div class="col-sm-9">
          <div class="q-pa-md q-gutter-sm">
            <q-breadcrumbs align="left" style="font-size: 20px">
              <q-breadcrumbs-el label="" icon="home" to="/" />
              <q-breadcrumbs-el label="Empleados" to="/departments" />
              <q-breadcrumbs-el label="Nuevo Empleado" />
            </q-breadcrumbs>
          </div>
        </div>
      </div>
    </div>

    <div class="q-pa-md bg-grey-3">
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
                :options="statusOptions"
                :error="$v.employee.fields.status.$error"
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
                        @input="() => $birth_date.hide()"
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
                :rules="curpRules"
                :error="$v.employee.fields.curp.$error"
                v-model="employee.fields.curp"
                label="CURP"
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
                :error="$v.employee.fields.rfc.$error"
                v-model="employee.fields.rfc"
                label="RFC"
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
                :error="$v.employee.fields.social_security.$error"
                v-model="employee.fields.social_security"
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
              <q-btn color="positive" icon="save" label="Guardar" @click="createEmployees()" />
            </div>
          </div>
        </div>
      </div>
    </div>
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
    }
  },
  data () {
    return {
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
          phone: null,
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
          timetable: null
        }
      },
      departmentOptions: [],
      areaOptions: [],
      positionOptions: [],
      shiftOptions: [],
      timetablesOptions: [],
      statusOptions: [
        {
          label: 'ACTIVO',
          value: 'ACTIVO'
        },
        {
          label: 'INACTIVO',
          value: 'INACTIVO'
        }
      ]
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
    }
  },
  beforeCreate () {
    if (!this.$store.getters['users/roles'].includes(1) && !this.$store.getters['users/roles'].includes(3) && !this.$store.getters['users/roles'].includes(7) && !this.$store.getters['users/roles'].includes(4) && !this.$store.getters['users/roles'].includes(6)) {
      this.$router.push('/')
    }
  },
  mounted () {
    this.getDepartments()
    this.getAreas()
    this.getPositions()
    this.getShift()
    this.getTimetables()
  },
  methods: {
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
    createEmployees () {
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
      const params = { ...this.employee.fields }
      api.post('/employees', params).then(({ data }) => {
        this.$q.notify({
          message: data.message.content,
          position: 'top',
          color: (data.result ? 'positive' : 'warning')
        })
        if (data.result) {
          this.$q.loading.hide()
          this.$router.push('/employees')
        } else {
          this.$q.loading.hide()
        }
      })
    }
  }
}
</script>

<style>
</style>

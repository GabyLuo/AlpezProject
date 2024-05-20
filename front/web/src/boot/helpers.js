// import roles from '../commons/roles.js'
// import privileges from '../commons/privileges.js'
import { Dialog, Notify } from 'quasar'
// import store from '../store'

export default ({ app, Vue }) => {
  // Lista con todos los roles
  // Vue.prototype.$roles = roles

  // Lista con todos los privilegios
  // Vue.prototype.$privileges = privileges

  // Función global para mostrar mensajes
  Vue.prototype.$showMessage = (title, message, preventClose = false) => {
    return Dialog.create({ title: title, message: message, preventClose: preventClose })
  }

  // Función global para mostrar un diálogo de confirmación
  Vue.prototype.$showConfirm = (message, preventClose = false) => {
    return Dialog.create({
      title: 'Confirmar',
      message: message,
      ok: 'Aceptar',
      cancel: 'Cancelar',
      preventClose: preventClose
    })
  }

  // Función global para mostrar alertas
  Vue.prototype.$showNotify = (message, color) => {
    return Notify.create({
      message: message,
      position: 'top-right',
      color: color,
      // progress: true,
      timeout: 1500
    })
  }

  // Función global para comprobar los roles indicados con los roles del usuario
  // Vue.prototype.$hasRoles = (...rolesIds) => {
  //   for (var i = 0; i < rolesIds.length; i++) {
  //     if (store.getters['user/rolesIds'].includes(rolesIds[i])) {
  //       return true
  //     }
  //   }
  //   if (store.getters['user/rolesIds'].includes(roles.SUPER_ADMIN)) {
  //     return true
  //   }
  //   return false
  // }

  // Función global para comprobar el rol indicado con los roles del usuario
  // Vue.prototype.$checkRol = (rolId) => {
  //   return store.getters['user/rolesIds'].includes(rolId)
  // }

  // Función global para comprobar los privilegios indicados con los privilegios del usuario
  // Vue.prototype.$hasPrivileges = (...privilegesIds) => {
  //   for (var i = 0; i < privilegesIds.length; i++) {
  //     if (store.getters['user/privilegesIds'].includes(privilegesIds[i])) {
  //       return true
  //     }
  //   }
  //   if (store.getters['user/rolesIds'].includes(roles.SUPER_ADMIN)) {
  //     return true
  //   }
  //   return false
  // }

  // Función para darle formato de número a cualquier cifra eg: 1000 => 1,000.00
  Vue.prototype.$formatNumber = (number, len = 2) => parseFloat((100 * number) / 100).toFixed(len).replace(/(\d)(?=(\d{3})+\.)/g, '$1,')

  // Acomodar cabeceras de las tablas
  Vue.prototype.$cssHelper = () => {
    setTimeout(() => {
      let thead = document.querySelector('#maintable thead tr')
      let tbody = document.querySelector('#maintable tbody tr')
      if (thead !== null && tbody !== null) {
        thead = thead.querySelectorAll('th')
        tbody = tbody.querySelectorAll('td')
        for (let i = 0; i < tbody.length; i++) {
          thead[i].style.width = tbody[i].offsetWidth + 'px'
        }
      }
    }, 250)
  }

  // Evita que se escriban letras, solo números
  Vue.prototype.$lockIntegers = (evt) => {
    if (!evt) {
      evt = window.event
    }
    const charCode = (evt.which) ? evt.which : evt.keyCode
    if ((charCode > 31 && (charCode < 48 || charCode > 57))) {
      evt.preventDefault()
    } else {
      return true
    }
  }

  // Evita que se escriban letras, solo números y puntos decimales
  Vue.prototype.$lockDecimals = (evt) => {
    if (!evt) {
      evt = window.event
    }
    const charCode = (evt.which) ? evt.which : evt.keyCode
    if ((charCode > 31 && (charCode < 48 || charCode > 57)) && charCode !== 46) {
      evt.preventDefault()
    } else {
      return true
    }
  }

  // Agrega a (n) una cantidad (width) de (z). e.g. (1, 4): 0001
  Vue.prototype.$padZeros = (n, width, z = '0') => {
    // z = z || '0'
    n = n + ''
    return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n
  }

  // Devuelve un objecto con el mensaje para confirmar un dialog
  Vue.prototype.$objConfirm = (msg) => {
    return {
      title: 'Confirmar',
      message: msg,
      ok: 'Aceptar',
      cancel: 'Cancelar'
    }
  }

  Vue.prototype.$formatDatetime = (date) => {
    console.log('estoy recibiendo ')
    console.log(date)
    const fecha = date.split(' ')
    if (fecha[0] !== null) {
      var info = fecha[0].split('-').reverse().join('/')
    }
    return info + ' ' + fecha[1]
  }
  Vue.prototype.$formatDate = (date) => {
    var info = null
    if (date !== null) {
      info = date.split('/').reverse().join('-')
    }
    return info
  }
  Vue.prototype.$formatCost = (val) => {
    const cost = Number(val)
    if (cost >= 1000 && cost <= 999900) {
      return (Number.parseFloat(cost) / 1000).toFixed(0).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,') + 'K'
    } else if (cost > 999900) {
      return (Number.parseFloat(cost) / 1000000).toFixed(0).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,') + 'M'
    } else {
      return Number.parseFloat(cost).toFixed(0)
    }
  }

  Vue.prototype.$formatNumberThree = (value) => {
    const val = (value / 1).toFixed(3).replace(',', '.')
    return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')
  }

  Vue.prototype.$formatNumberPrice = (value) => {
    const val = (value / 1).toFixed(2).replace(',', '.')
    return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')
  }

  Vue.prototype.$selectIcon = (ext) => {
    let icon = 'fas fa-file'
    if (ext === 'pdf' || ext === 'PDF') {
      icon = 'fas fa-file-pdf'
    } else if (ext === 'png' || ext === 'PNG' || ext === 'jpg' || ext === 'jpeg') {
      icon = 'fas fa-file-image'
    } else if (ext === 'xls' || ext === 'csv') {
      icon = 'fas fa-file-excel'
    }
    return icon
  }
  Vue.prototype.$selectIconColor = (ext) => {
    let color = 'positive'
    if (ext === 'pdf' || ext === 'PDF') {
      color = 'negative'
    } else if (ext === 'png' || ext === 'PNG' || ext === 'jpg' || ext === 'jpeg') {
      color = 'primary'
    } else if (ext === 'xls' || ext === 'csv') {
      color = 'positive'
    }
    return color
  }
}

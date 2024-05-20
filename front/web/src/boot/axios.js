import axios from 'axios'

export default async ({ Vue }) => {
  axios.defaults.baseURL = process.env.API

  axios.interceptors.response.use(response => {
    const isValid = response.data instanceof Object || response.data instanceof Array
    if (!isValid) {
      console.info(response.request.responseURL, response)
    }
    return response
  }, error => {
    console.group()
    if (error.response) {
      // The request was made and the server responded with a status code
      // that falls out of the range of 2xx
      console.error('Data: ', error.response.data)
      console.error('Status: ', error.response.status)
      console.error('Headers: ', error.response.headers)
    } else if (error.request) {
      // The request was made but no response was received
      // `error.request` is an instance of XMLHttpRequest in the browser and an instance of
      // http.ClientRequest in node.js
      console.error('Request: ', error.request)
    } else {
      // Something happened in setting up the request that triggered an Error
      console.error('Message: ', error.message)
    }
    console.error('Config: ', error.config)
    console.groupEnd()
    return Promise.reject(error)
  })

  Vue.prototype.$axios = axios
}

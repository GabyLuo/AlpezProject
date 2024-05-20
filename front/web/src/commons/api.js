import axios from 'axios'
import qs from 'qs'

var CancelToken = axios.CancelToken
var source = CancelToken.source()

const api = {
  request: (config) => axios.request(qs.stringify(config)),
  get: (url, config) => axios.get(url, qs.stringify(config)),
  post: (url, config) => axios.post(url, qs.stringify(config), { cancelToken: source.token }),
  put: (url, config) => axios.put(url, qs.stringify(config)),
  delete: (url, config) => axios.delete(url, qs.stringify(config)),
  file: (url, request) => axios.post(url, request, {
    headers: {
      'Content-Type': 'multipart/form-data'
    }
  }),
  fileDownload: (url, config) => axios.get(url, {
    responseType: 'arraybuffer',
    headers: {
      'Content-Type': 'application/json'
    }
  }),
  cancel: () => {
    source.cancel('Operation canceled by the user.')
    source = CancelToken.source()
  }

}

export default api

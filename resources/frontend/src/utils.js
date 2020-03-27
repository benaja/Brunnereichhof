import axios from './axios'

function downloadFile(url) {
  return axios
    .get(url, { responseType: 'arraybuffer' })
    .then(response => {
      var newBlob = new Blob([response.data], { type: 'application/pdf' })

      // IE doesn't allow using a blob object directly as link href
      // instead it is necessary to use msSaveOrOpenBlob
      if (window.navigator && window.navigator.msSaveOrOpenBlob) {
        window.navigator.msSaveOrOpenBlob(newBlob)
        return true
      }

      // // For other browsers:
      // // Create a link pointing to the ObjectURL containing the blob.
      const data = window.URL.createObjectURL(newBlob)
      var link = document.createElement('a')
      link.href = data
      link.download = response.headers.pragma // axios only supports less headers so I took this one because it works with this
      link.click()
      setTimeout(function() {
        // For Firefox it is necessary to delay revoking the ObjectURL
        window.URL.revokeObjectURL(data)
      }, 100)
      return true
    })
    .catch(error => {
      console.log(error)
      return false
    })
}

const rules = {
  required: v => !!v || 'Dieses Feld muss vorhanden sein',
  nullableEmail: v =>
    !v ||
    /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/.test(
      v
    ) ||
    'Email nicht korrekt'
}

export { downloadFile, rules }

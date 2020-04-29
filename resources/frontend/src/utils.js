import axios from './axios'

function downloadFile(url, params) {
  return axios.get(url, { params, responseType: 'arraybuffer' }).then((response) => {
    const newBlob = new Blob([response.data], { type: 'application/pdf' })

    // IE doesn't allow using a blob object directly as link href
    // instead it is necessary to use msSaveOrOpenBlob
    if (window.navigator && window.navigator.msSaveOrOpenBlob) {
      window.navigator.msSaveOrOpenBlob(newBlob)
      return true
    }

    // // For other browsers:
    // // Create a link pointing to the ObjectURL containing the blob.
    const data = window.URL.createObjectURL(newBlob)
    const link = document.createElement('a')
    link.href = data
    // axios only supports less headers so I took this one because it works with this
    link.download = response.headers.pragma
    link.click()
    setTimeout(() => {
      // For Firefox it is necessary to delay revoking the ObjectURL
      window.URL.revokeObjectURL(data)
    }, 100)
    return true
  })
}

const rules = {
  required: (v) => !!v || 'Dieses Feld muss vorhanden sein',
  nullableEmail: (v) => !v
    || /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/.test(
      v,
    )
    || 'Email nicht korrekt',
  integer: (v) => Number.isInteger(v) || 'Muss eine ganzzahlige Zahl sein'
}

export { downloadFile, rules }

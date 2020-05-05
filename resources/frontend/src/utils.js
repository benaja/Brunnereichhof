import fileDownload from 'js-file-download'
import Vue from 'vue'
import axios from './axios'
import { COLORS } from './constants'

function downloadFile(url, params) {
  return new Promise((resolve, reject) => {
    axios.get(url, { params, responseType: 'arraybuffer' }).then(response => {
      fileDownload(response.data, response.headers.pragma)
      resolve()
    }).catch(error => {
      try {
        const responseData = JSON.parse(Buffer.from(error.response.data).toString('utf8'))
        reject(responseData)
      } catch {
        reject(error)
      }
    })
  })
}

const emailRegex = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/

const rules = {
  required: v => !!v || 'Dieses Feld muss vorhanden sein',
  nullableEmail: v => !v
    || emailRegex.test(v)
    || 'Email nicht korrekt',
  email: v => emailRegex.test(v) || 'Email nicht korrekt',
  integer: v => Number.isInteger(v) || 'Muss eine ganzzahlige Zahl sein'
}

function confirmAction(text = 'Willst du diesen Eintrag wirklich löschen?', confirmButtonText = 'Ja, löschen') {
  return new Promise(resolve => {
    Vue.swal({
      title: 'Bis du dir sicher?',
      text,
      type: 'warning',
      showCancelButton: true,
      confirmButtonText,
      cancelButtonText: 'Nein, abbrechen',
      confirmButtonColor: COLORS.PRIMARY
    }).then(result => {
      resolve(result.value)
    })
  })
}

export { downloadFile, rules, confirmAction }

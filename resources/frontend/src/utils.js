import fileDownload from 'js-file-download'
import Vue from 'vue'
import i18n from '@/plugins/i18n'
import axios from './axios'
import { COLORS } from './constants'

function downloadFile(url, params) {
  return new Promise((resolve, reject) => {
    axios
      .get(url, { params, responseType: 'arraybuffer' })
      .then(response => {
        fileDownload(response.data, response.headers.pragma)
        resolve()
      })
      .catch(error => {
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
  nullableEmail: v => !v || emailRegex.test(v) || 'Email nicht korrekt',
  email: v => emailRegex.test(v) || 'Email nicht korrekt',
  integer: v => Number.isInteger(v) || 'Muss eine ganzzahlige Zahl sein'
}

function confirmAction(text = 'Willst du diesen Eintrag wirklich löschen?', confirmButtonText = 'Ja, löschen') {
  let options
  if (typeof text === 'object') {
    options = text
  } else {
    options = {
      title: 'Bis du dir sicher?',
      text,
      confirmButtonText,
      cancelButtonText: 'Nein, abbrechen',
      showCancelButton: true,
      icon: 'warning'
    }
  }

  return new Promise(resolve => {
    Vue.swal({
      confirmButtonColor: COLORS.PRIMARY,
      ...options
    }).then(result => {
      if (typeof text === 'object') {
        resolve(result)
      } else {
        resolve(result.value)
      }
    })
  })
}

const employeeFunctions = [
  {
    value: null,
    text: i18n.tc('Standartmitarbeiter'),
    rank: 0
  },
  {
    value: 'driver',
    text: i18n.tc('Fahrer'),
    rank: 1
  },
  {
    value: 'group-leader',
    text: i18n.tc('Gruppenführer'),
    rank: 2
  },
  {
    value: 'representation',
    text: i18n.tc('Stellvertretung'),
    rank: 3
  }
]

const contractTypes = [
  {
    value: 'work_contract',
    text: 'Werksvertrag'
  },
  {
    value: 'staff_grant',
    text: 'Personalverleih'
  }
]

const foodTypes = [
  {
    value: 1,
    text: 'Eichhof'
  },
  {
    value: 2,
    text: 'Kunde'
  },
  {
    value: 3,
    text: 'Keine Angabe'
  }
]

const QuarterType = {
  EmployerConfirmation: 0,
  CreditToEichhof: 1,
  FamilyAllowancesPaid: 2
}

const UserType = {
  Customer: 1,
  Worker: 2,
  SuperAdmin: 3,
  Employee: 4
}

export {
  downloadFile, rules, confirmAction, employeeFunctions, contractTypes, foodTypes, QuarterType, UserType
}

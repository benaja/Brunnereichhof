<template>
  <time-type-form :open="value && $store.getters.openPopups" @save="save" @cancel="cancel" :saveButtonActive="allValid" class="no-select">
    <p class="font-weight-bold headline">{{ formatedDate }}</p>
    <v-select label="Leistungsart" v-model="worktype" :items="worktypes" item-text="name_de" item-value="id"></v-select>
    <v-select
      label="Erfassungsart"
      v-model="edittype"
      item-text="name"
      item-value="id"
      :items="edittypes"
      no-data-text="Wähle zuerst eine Leistungsart"
    ></v-select>
    <div v-if="edittype !== 'manually'">
      <v-dialog v-model="timeDialogFrom" width="290px">
        <template v-slot:activator="{ on }">
          <v-text-field v-on="on" v-model="from" readonly label="Zeit von" :error-messages="rules.from()" disabled></v-text-field>
        </template>
        <v-time-picker v-if="timeDialogFrom" v-model="from" format="24hr" disabled>
          <v-spacer></v-spacer>
          <v-btn text color="primary" @click="timeDialogFrom = false">OK</v-btn>
        </v-time-picker>
      </v-dialog>
      <v-dialog v-model="timeDialogTo" width="290px">
        <template v-slot:activator="{ on }">
          <v-text-field v-on="on" v-model="to" readonly label="Zeit bis" disabled></v-text-field>
        </template>
        <v-time-picker v-if="timeDialogTo" v-model="to" format="24hr" disabled>
          <v-spacer></v-spacer>
          <v-btn text color="primary" @click="timeDialogTo = false">OK</v-btn>
        </v-time-picker>
      </v-dialog>
    </div>
    <div v-else>
      <v-text-field type="number" v-model="hours" label="Stunden"></v-text-field>
    </div>
    <v-checkbox v-model="breakfast" label="Frühstück auf dem Eichhof"></v-checkbox>
    <v-checkbox v-model="lunch" label="Mitagessen auf dem Eichhof" class="ma-0"></v-checkbox>
    <v-checkbox v-model="dinner" label="Abendessen auf dem Eichhof" class="ma-0"></v-checkbox>
    <v-textarea auto-grow v-model="comment" rows="1" label="Bemerkung" class="comment-textarea"></v-textarea>
    <p class="text-center delete-button" v-if="timerecord.id">
      <v-btn color="red" text icon @click="deleteTimerecord">
        <v-icon>delete</v-icon>
      </v-btn>
    </p>
  </time-type-form>
</template>

<script>
import TimeTypeForm from '@/components/Time/TimeTypeForm'

export default {
  name: 'TimePopup',
  components: {
    TimeTypeForm
  },
  props: {
    date: String,
    value: Boolean,
    settings: Object,
    timerecord: Object,
    urlWorkerParam: String
  },
  data() {
    return {
      worktypes: [],
      worktype: localStorage.worktype ? localStorage.worktype : 'productiveHours',
      edittype: localStorage.edittype ? localStorage.edittype : 'full-day-short',
      from: '07:00',
      to: '17:00',
      hours: null,
      breakfast: false,
      lunch: false,
      dinner: false,
      comment: null,
      timeDialogFrom: false,
      timeDialogTo: false,
      rules: {
        from: () => {
          let from = this.getDate(this.from)
          let to = this.getDate(this.to)

          if (from < to || (from <= to && this.worktype === 'holidays')) {
            return []
          } else {
            return 'Zeit muss vor der Endzeit sein'
          }
        }
      }
    }
  },
  mounted() {
    if (this.edittype === 'manually') {
      this.hours = localStorage.hours
    }
    this.applyLocalStorageSettings()
    this.applyTimeSettings()
    this.axios.get('/worktypes').then(response => {
      this.worktypes = response.data
    })
  },
  methods: {
    save() {
      if (this.allValid) {
        let requestBody = {
          from: this.from,
          to: this.to,
          breakfast: this.breakfast,
          lunch: this.lunch,
          dinner: this.dinner,
          comment: this.comment,
          worktype: this.worktype
        }
        if (this.timerecord.id) {
          this.axios
            .patch('time/' + this.timerecord.id + this.urlWorkerParam, requestBody)
            .then(response => {
              this.$emit('input', false)
              this.$emit('add', response.data)
            })
            .catch(error => {
              if (error.includes('Die Zeit überschneidet sich mit einem anderen Eintrag.')) {
                this.$swal('Kollision mit einem anderen Eintrag', 'Die Zeit überschneidet sich mit einem bereits existierenden Eintrag.', 'error')
              } else if (!error.status(403)) {
                this.$swal('Fehler beim Speichern', 'Zeit konnte aus einem umbekannten Grund nicht gespeichert werden.', 'error')
              }
            })
        } else {
          requestBody.date = this.date
          this.axios
            .post('/time' + this.urlWorkerParam, requestBody)
            .then(response => {
              localStorage.edittype = this.edittype
              localStorage.worktype = this.worktype
              localStorage.breakfast = this.breakfast
              localStorage.lunch = this.lunch
              localStorage.dinner = this.dinner
              if (this.hours) {
                localStorage.hours = this.hours
              }
              this.$emit('input', false)
              this.$emit('add', response.data)
            })
            .catch(error => {
              if (error.includes('Die Zeit überschneidet sich mit einem anderen Eintrag.')) {
                this.$swal('Kollision mit einem anderen Eintrag', 'Die Zeit überschneidet sich mit einem bereits existierenden Eintrag.', 'error')
              } else if (!error.status(403)) {
                this.$swal('Fehler beim Speichern', 'Zeit konnte aus einem umbekannten Grund nicht gespeichert werden.', 'error')
              }
            })
        }
      }
    },
    deleteTimerecord() {
      this.axios
        .delete('time/' + this.timerecord.id + this.urlWorkerParam)
        .then(response => {
          this.$emit('input', false)
          this.$emit('add', response.data)
        })
        .catch(error => {
          if (!error.status(403)) {
            this.$swal('Fehler', 'Element konnte nicht gelöscht werden', 'error')
          }
        })
    },
    cancel() {
      this.$emit('input', false)
    },
    getDate(timeString) {
      if (!timeString) return new Date()
      let date = new Date()
      let timeSplits = timeString.split(':')
      date.setHours(timeSplits[0])
      date.setMinutes(timeSplits[1])
      return date
    },
    applyTimeSettings() {
      if (this.edittype === 'full-day-short') {
        this.from = this.settings.fullDayShortStart
        this.to = this.settings.fullDayShortEnd
      } else if (this.edittype === 'full-day-long') {
        this.from = this.settings.fullDayLongStart
        this.to = this.settings.fullDayLongEnd
      }
    },
    applyLocalStorageSettings() {
      this.breakfast = localStorage.breakfast ? JSON.parse(localStorage.breakfast) : false
      this.lunch = localStorage.lunch ? JSON.parse(localStorage.lunch) : false
      this.dinner = localStorage.dinner ? JSON.parse(localStorage.dinner) : false
      this.worktype = localStorage.worktype ? localStorage.worktype : 'productiveHours'
      this.edittype = localStorage.edittype ? localStorage.edittype : 'full-day-short'
    },
    setEditTypeByTime() {
      const worktype = this.worktypes.find(w => w.id === this.worktype)
      const hours = this.calculateHoursFromTime()
      const inputType = worktype.work_input_types.find(w => w.hours === hours)
      if (inputType) {
        this.edittype = inputType.id
      } else {
        // to prevent bugs, because worktype changes and hours gets changed in watch function
        this.edittype = 'manually'
        setTimeout(() => {
          this.hours = hours
        }, 10)
      }
    },
    hoursToTime() {
      if (this.edittype === 'manually') {
        this.from = '08:00'
        let minutes = (this.hours - Math.floor(this.hours)) * 60
        this.to = Math.floor(8 + Number(this.hours)) + ':' + minutes.toFixed(0)
      } else {
        this.from = '08:00'
        let worktype = this.worktypes.find(w => w.id === this.worktype)
        let edittype = worktype.work_input_types.find(w => w.id === this.edittype)
        let minutes = (edittype.hours - Math.floor(edittype.hours)) * 60
        this.to = Math.floor(8 + Number(edittype.hours)) + ':' + minutes.toFixed(0)
      }
    },
    calculateHoursFromTime() {
      let from = this.getDate(this.from)
      let to = this.getDate(this.to)
      let timeDiff = Math.abs(from.getTime() - to.getTime())
      return timeDiff / 1000 / 60 / 60
    }
  },
  computed: {
    allValid() {
      if (this.rules.from().length !== 0) return false
      // if (this.rules.break().length !== 0) return false
      return true
    },
    formatedDate() {
      let date = new Date(this.date)
      let day = date.getDay() === 0 ? 6 : date.getDay()
      day--
      return `${this.$store.getters.dayShortNames[day]}, ${date.getDate()}.${date.getMonth() + 1}.${date.getFullYear()}`
    },
    edittypes() {
      let worktype = this.worktypes.find(w => w.id === this.worktype)
      if (!worktype) return []
      if (worktype.manually) {
        return [
          ...worktype.work_input_types,
          {
            id: 'manually',
            name: 'Manuell'
          }
        ]
      }
      return worktype.work_input_types
    }
  },
  watch: {
    time() {
      this.from = this.time
    },
    timerecord() {
      if (this.timerecord.id) {
        if (this.timerecord.from.length === 8) {
          this.from = this.timerecord.from.slice(0, -3)
          this.to = this.timerecord.to.slice(0, -3)
        }
        this.breakfast = this.timerecord.breakfast
        this.lunch = this.timerecord.lunch
        this.dinner = this.timerecord.dinner
        this.comment = this.timerecord.comment
        this.worktype = this.timerecord.worktype.id
        this.setEditTypeByTime()
      } else {
        this.applyTimeSettings()
        this.applyLocalStorageSettings()
      }
    },
    edittype() {
      this.hoursToTime()
    },
    hours() {
      if (this.edittype === 'manually') {
        this.hoursToTime()
      }
    },
    settings() {
      this.applyTimeSettings()
    },
    worktype() {
      this.setEditTypeByTime()
    }
  }
}
</script>

<style lang="scss" scoped>
.no-select {
  user-select: none;
}

.comment-textarea {
  margin-bottom: 50px;
}

.delete-button {
  margin-bottom: 70px;
}
</style>

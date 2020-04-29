<template>
  <v-form ref="form">
    <v-select
      v-model="form.worktype"
      label="Leistungsart"
      :items="worktypes"
      item-text="name_de"
      item-value="id"
      class="pa-0 ma-0"
      :rules="[rules.required]"
      @change="setEditTypeByTime"
    ></v-select>
    <v-select
      v-model="form.edittype"
      label="Erfassungsart"
      item-text="name"
      item-value="id"
      :items="edittypes"
      no-data-text="Wähle zuerst eine Leistungsart"
      :rules="[rules.required]"
      @change="applyTime"
    ></v-select>
    <div v-if="form.edittype !== 'manually'">
      <v-dialog
        v-model="timeDialogFrom"
        width="290px"
      >
        <template v-slot:activator="{ on }">
          <v-text-field
            v-model="form.from"
            readonly
            label="Zeit von"
            :error-messages="rules.from()"
            disabled
            v-on="on"
          ></v-text-field>
        </template>
        <v-time-picker
          v-if="timeDialogFrom"
          v-model="form.from"
          format="24hr"
          disabled
        >
          <v-spacer></v-spacer>
          <v-btn
            text
            color="primary"
            @click="timeDialogFrom = false"
          >
            OK
          </v-btn>
        </v-time-picker>
      </v-dialog>
      <v-dialog
        v-model="timeDialogTo"
        width="290px"
      >
        <template v-slot:activator="{ on }">
          <v-text-field
            v-model="form.to"
            readonly
            label="Zeit bis"
            disabled
            v-on="on"
          ></v-text-field>
        </template>
        <v-time-picker
          v-if="timeDialogTo"
          v-model="form.to"
          format="24hr"
          disabled
        >
          <v-spacer></v-spacer>
          <v-btn
            text
            color="primary"
            @click="timeDialogTo = false"
          >
            OK
          </v-btn>
        </v-time-picker>
      </v-dialog>
    </div>
    <div v-else>
      <v-text-field
        v-model="form.hours"
        type="number"
        :rules="[rules.required, rules.greaterThanZero]"
        label="Stunden"
        @change="applyTime"
      ></v-text-field>
    </div>
    <v-checkbox
      v-model="form.breakfast"
      label="Frühstück auf dem Eichhof"
      class="ma-0 pa-0"
    ></v-checkbox>
    <v-checkbox
      v-model="form.lunch"
      label="Mitagessen auf dem Eichhof"
      class="ma-0 pa-0"
    ></v-checkbox>
    <v-checkbox
      v-model="form.dinner"
      label="Abendessen auf dem Eichhof"
      class="ma-0 pa-0"
    ></v-checkbox>
    <v-textarea
      v-model="form.comment"
      auto-grow
      rows="1"
      label="Bemerkung"
      class="comment-textarea"
    ></v-textarea>
  </v-form>
</template>

<script>
import { rules } from '@/utils'

const defaultForm = {
  worktype: null,
  edittype: null,
  from: '',
  to: '',
  hours: null,
  breakfast: null,
  lunch: null,
  dinner: null,
  comment: null
}

export default {
  props: {
    timerecord: {
      type: Object,
      default: null
    },
    startTime: {
      type: Number,
      default: null
    },
    date: {
      type: String,
      default: null
    },
    urlWorkerParam: {
      type: String,
      default: ''
    }
  },
  data() {
    return {
      form: {
        ...defaultForm
      },
      worktypes: [],
      timeDialogFrom: false,
      timeDialogTo: false,
      rules: {
        ...rules,
        greaterThanZero: (v) => v > 0 || 'Zahl muss positiv sein',
        from: () => {
          const from = this.getDate(this.from)
          const to = this.getDate(this.to)

          if (from < to || (from <= to && this.worktype === 'holidays')) {
            return []
          }
          return 'Zeit muss vor der Endzeit sein'
        }
      }
    }
  },
  computed: {
    edittypes() {
      const worktype = this.worktypes.find((w) => w.id === this.form.worktype)
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
    timerecord() {
      this.editTimerecrod()
    },
    startTime() {
      this.applyTime()
    }
  },
  mounted() {
    this.axios.get('/worktypes').then((response) => {
      this.worktypes = response.data
      if (this.timerecord) {
        this.setEditTypeByTime()
      } else {
        const timerecordSettings = JSON.parse(localStorage.timerecordSettings)
        if (timerecordSettings.edittype === 'manually') {
          this.form.hours = timerecordSettings.hours
        }
        this.form.worktype = timerecordSettings.worktype
        this.form.edittype = timerecordSettings.edittype
        this.form.breakfast = timerecordSettings.breakfast
        this.form.lunch = timerecordSettings.lunch
        this.form.dinner = timerecordSettings.dinner
        this.applyTime()
      }
    })

    this.applyTime()
    this.editTimerecrod()
  },
  methods: {
    getDate(timeString) {
      if (!timeString) return new Date()
      const date = new Date()
      const timeSplits = timeString.split(':')
      date.setHours(timeSplits[0])
      date.setMinutes(timeSplits[1])
      return date
    },
    applyTime() {
      let { startTime } = this
      if (this.form.from) {
        const startDate = this.$moment(this.form.from, 'HH:mm')
        startTime = startDate.hour() + startDate.minutes() / 60
      }
      this.setTimeFrom(startTime)
      if (this.form.edittype === 'manually') {
        const endTime = startTime + Number(this.form.hours)
        const hours = Math.floor(endTime)
        const minutes = (endTime - Math.floor(endTime)) * 60
        this.form.to = `${this.getTimeString(hours)}:${this.getTimeString(minutes.toFixed(0))}`
      } else if (this.form.edittype) {
        const worktype = this.worktypes.find((w) => w.id === this.form.worktype)
        const edittype = worktype.work_input_types.find((w) => w.id === this.form.edittype)
        const endTime = startTime + Number(edittype.hours)
        const minutes = (endTime - Math.floor(endTime)) * 60
        this.form.to = `${this.getTimeString(Math.floor(endTime))}:${this.getTimeString(minutes.toFixed(0))}`
      }
      const toHour = this.getHoursFromTime(this.form.to)
      if (toHour > 24) {
        const hoursBetweenTime = this.calculateHoursBetweenTime()
        this.setTimeFrom(24 - hoursBetweenTime)
        this.form.to = '24:00'
      }
    },
    setTimeFrom(startTime) {
      const hours = Math.floor(startTime)
      const minutes = ((startTime - Math.floor(startTime)) * 60).toFixed(0)
      this.form.from = `${this.getTimeString(hours)}:${this.getTimeString(minutes)}`
    },
    getTimeString(time) {
      return time < 10 ? `0${time}` : time
    },
    getHoursFromTime(timeString) {
      const splitTimeString = timeString.split(':')
      const hours = Number(splitTimeString[0])
      const minutes = Number(splitTimeString[1])
      return hours + Math.round((minutes / 60) * 100) / 100
    },
    save() {
      if (this.$refs.form.validate()) {
        if (this.form.id) {
          this.axios
            .patch(`time/${this.form.id}${this.urlWorkerParam}`, this.form)
            .then((response) => {
              this.$emit('updated', response.data)
            })
            .catch((error) => {
              if (error.includes('Die Zeit überschneidet sich mit einem anderen Eintrag.')) {
                this.$swal('Kollision mit einem anderen Eintrag', 'Die Zeit überschneidet sich mit einem bereits existierenden Eintrag.', 'error')
              } else if (!error.status(403)) {
                this.$swal('Fehler beim Speichern', 'Zeit konnte aus einem umbekannten Grund nicht gespeichert werden.', 'error')
              }
            })
        } else {
          this.axios
            .post(`/time${this.urlWorkerParam}`, {
              ...this.form,
              date: this.date
            })
            .then((response) => {
              localStorage.timerecordSettings = JSON.stringify(this.form)
              this.$emit('updated', response.data)
            })
            .catch((error) => {
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
      this.$swal({
        title: 'Wirklich Löschen?',
        text: 'Willst du diesen Eintrag wirklich löschen?',
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ja, löschen!',
        cancelButtonText: 'Nein, abbrechen'
      }).then((result) => {
        if (result.value) {
          this.axios
            .delete(`time/${this.timerecord.id}${this.urlWorkerParam}`)
            .then((response) => {
              this.$emit('updated', response.data)
            })
            .catch((error) => {
              if (!error.status(403)) {
                this.$swal('Fehler', 'Element konnte nicht gelöscht werden', 'error')
              }
            })
        }
      })
    },
    editTimerecrod() {
      if (this.timerecord) {
        this.form = {
          id: this.timerecord.id,
          from: this.timerecord.from.length === 8 ? this.timerecord.from.slice(0, -3)
            : this.timerecord.from,
          to: this.timerecord.to.length === 8 ? this.timerecord.to.slice(0, -3)
            : this.timerecord.to,
          worktype: this.timerecord.worktype_id,
          breakfast: this.timerecord.breakfast,
          lunch: this.timerecord.lunch,
          dinner: this.timerecord.dinner,
          comment: this.timerecord.comment,
          edittype: null,
          hours: null
        }
        this.form.hours = this.calculateHoursBetweenTime()
        // this.setEditTypeByTime()
      } else {
        this.$refs.form.reset()
      }
    },
    setEditTypeByTime() {
      const worktype = this.worktypes.find((w) => w.id === this.form.worktype)
      if (worktype) {
        const hours = this.calculateHoursBetweenTime()
        const inputType = worktype.work_input_types.find((w) => w.hours === hours)
        if (inputType) {
          this.form.edittype = inputType.id
        } else {
          // to prevent bugs, because worktype changes and hours gets changed in watch function
          this.form.edittype = 'manually'
          setTimeout(() => {
            this.form.hours = hours
          }, 10)
        }
      }
    },
    calculateHoursBetweenTime() {
      const from = this.getDate(this.form.from)
      const to = this.getDate(this.form.to)
      const timeDiff = Math.abs(from.getTime() - to.getTime())
      return Math.round((timeDiff / 1000 / 60 / 60) * 100) / 100
    }
  }
}
</script>

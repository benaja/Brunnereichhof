<template>
  <v-card>
    <p class="text-right pa-2">
      <v-btn text icon small @click="generatePdf">
        <v-icon>picture_as_pdf</v-icon>
      </v-btn>
      <v-btn
        text
        icon
        small
        @click="editMode = !editMode"
        v-if="$auth.user().hasPermission(['superadmin'], ['roomdispositioner_write'])"
      >
        <v-icon>edit</v-icon>
      </v-btn>
      <v-btn
        text
        icon
        small
        @click="deleteReservation"
        v-if="$auth.user().hasPermission(['superadmin'], ['roomdispositioner_write'])"
      >
        <v-icon>delete</v-icon>
      </v-btn>
      <v-btn text icon small @click="$emit('close')">
        <v-icon>close</v-icon>
      </v-btn>
    </p>
    <v-row v-if="value.bed_room_pivot && !editMode" class="px-3">
      <v-col cols="4" class="py-0">
        <p class="font-weight-bold subheading">Von</p>
      </v-col>
      <v-col cols="8" class="py-0">
        <p>{{moment(value.entry).format('DD.MM.YYYY')}}</p>
      </v-col>
      <v-col cols="4" class="py-0">
        <p class="font-weight-bold subheading">Bis</p>
      </v-col>
      <v-col cols="8" class="py-0">
        <p>{{moment(value.exit).format('DD.MM.YYYY')}}</p>
      </v-col>
      <v-col cols="4" class="py-0">
        <p class="font-weight-bold subheading">Raum</p>
      </v-col>
      <v-col cols="8" class="py-0">
        <p>{{value.bed_room_pivot.room.name}}</p>
      </v-col>
      <v-col cols="4" class="py-0">
        <p class="font-weight-bold subheading">Bett</p>
      </v-col>
      <v-col cols="8" class="py-0">
        <p>{{value.bed_room_pivot.bed.name}}</p>
      </v-col>
      <v-col cols="4" class="py-0">
        <p class="font-weight-bold subheading">Mittarbeiter</p>
      </v-col>
      <v-col cols="8" class="py-0">
        <p>{{value.employee.lastname}} {{value.employee.firstname}}</p>
      </v-col>
    </v-row>
    <v-form ref="form" lazy-validation v-else-if="editMode" class="px-3">
      <date-picker v-model="value.entry" label="Von" :rules="rules.required" color="blue"></date-picker>
      <date-picker v-model="value.exit" label="Bis" :rules="rules.after" color="blue"></date-picker>
      <v-select
        :items="rooms"
        label="Raum"
        v-model="value.bed_room_pivot.room_id"
        item-value="id"
        item-text="name"
        :rules="rules.required"
        color="bluje"
      ></v-select>
      <v-select
        :items="beds"
        label="Bett"
        v-model="value.bed_room_pivot.id"
        item-value="pivot.id"
        item-text="name"
        :rules="rules.required"
        no-data-text="Kein freies Bett vorhanden."
        color="blue"
      ></v-select>
      <select-employee v-model="value.employee_id" :rules="rules.required"></select-employee>
    </v-form>
    <v-card-actions v-if="editMode">
      <v-spacer></v-spacer>
      <v-btn text @click="editMode = false">Abbrechen</v-btn>
      <v-btn color="blue" text @click="save">Speichern</v-btn>
    </v-card-actions>
  </v-card>
</template>

<script>
import moment from 'moment'
import DatePicker from '@/components/general/DatePicker'
import SelectEmployee from '@/components/Roomdispositioner/SelectEmployee'
import { downloadFile } from '@/utils'

export default {
  name: 'ReservationDetailss',
  components: {
    DatePicker,
    SelectEmployee
  },
  props: {
    value: {
      type: Object,
      default: () => {
        return {
          employee_id: null
        }
      }
    },
    selectedDay: Object
  },
  data() {
    return {
      employees: [],
      rooms: [],
      beds: [],
      editMode: false,
      rules: {
        required: [v => !!v || 'Dieses Feld muss vorhanden sein'],
        after: [v => new Date(this.value.entry) <= new Date(this.value.exit) || 'Das Datum muss nach dem Startdatum sein.']
      },
      loadingBeds: false,
      originalRoomId: null
    }
  },
  methods: {
    moment(date) {
      return moment(date)
    },
    save() {
      if (this.$refs.form.validate()) {
        this.axios
          .put('/reservations/' + this.value.id, this.reservationBody)
          .then(response => {
            this.updateExistingReservation(response)
          })
          .catch(error => {
            if (error.includes('Employee is already in an other bed at this time')) {
              this.$swal({
                title: 'Achtung!',
                text: 'Dieser Mitarbeiter ist zur angegeben Zeit in einem anderen Bett. Wollen sie ihn umbuchen?',
                type: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ja, umbuchen!',
                cancelButtonText: 'Nein, abbrechen'
              }).then(result => {
                if (result.value) {
                  this.axios
                    .put('/reservations/' + this.value.id, {
                      ...this.reservationBody,
                      force: true
                    })
                    .then(response => {
                      this.$emit('updateAll', response.data)
                    })
                    .catch(() => {
                      this.$swal('Fehler', 'Es ist ein unbekannter Fehler aufgetreten.', 'error')
                    })
                }
              })
            } else if (error.includes('Bed is already booked at this time')) {
              this.$swal(
                'Bett ist bereit voll',
                'Dieses Bett ist zureit voll. Bitte wählen Sie ein anderes Bett oder machen sie dies zuerst frei.',
                'error'
              )
            } else if (!error.status(403)) {
              this.$swal('Fehler', 'Es ist ein unbekannter Fehler aufgetreten. Bitte versuchen Sie es später erneut.', 'error')
            }
          })
      }
    },
    deleteReservation() {
      this.$swal({
        title: 'Achtung!',
        text: 'Wollen sie diese Reservation wirklich löschen?',
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ja, löschen!',
        cancelButtonText: 'Nein, abbrechen'
      }).then(result => {
        if (result.value) {
          this.axios.delete('/reservations/' + this.value.id).then(response => {
            this.$emit('delete', this.value)
          })
        }
      })
    },
    getBeds() {
      if (this.editMode && !this.loadingBeds) {
        this.axios.get(`/rooms/${this.value.bed_room_pivot.room_id}/beds?entry=${this.value.entry}&exit=${this.value.exit}`).then(response => {
          if (!response.data.find(b => b.pivot.id === this.value.bed_room_pivot.id) && this.value.bed_room_pivot.room_id === this.originalRoomId) {
            response.data.push({ ...this.value.bed_room_pivot.bed, pivot: this.value.bed_room_pivot })
          }
          for (let bed of response.data) {
            bed.bed_room_pivot = bed.pivot
          }
          this.beds = response.data
        })
      }
    },
    generatePdf() {
      downloadFile(`pdf/reservation/employee/${this.value.employee_id}?date=${this.selectedDay.format('YYYY-MM-DD')}`)
    },
    updateExistingReservation(response) {
      this.editMode = false
      this.value.id = response.data.id
      this.value.bed_room_pivot = response.data.bed_room_pivot
      this.value.employee = response.data.employee
      this.value.bed_room_pivot.bed.pivot = {
        id: this.value.bed_room_pivot.id
      }
      this.originalRoomId = this.value.bed_room_pivot.room_id
      this.$emit('update')
    }
  },
  computed: {
    reservationBody() {
      return {
        entry: this.value.entry,
        exit: this.value.exit,
        room: this.value.bed_room_pivot.room_id,
        bed: this.value.bed_room_pivot.id,
        employee: this.value.employee_id
      }
    }
  },
  watch: {
    value() {
      this.editMode = false
      this.rooms = [this.value.bed_room_pivot.room]
      this.value.employee.name = `${this.value.employee.lastname} ${this.value.employee.firstname}`
      this.value.bed_room_pivot.bed.pivot = {
        id: this.value.bed_room_pivot.id
      }
      this.beds = [this.value.bed_room_pivot.bed]
      this.originalRoomId = this.value.bed_room_pivot.room_id
      if (this.employees.length === 0) {
        this.employees = [this.value.employee]
      }
    },
    editMode() {
      if (this.editMode) {
        if (!this.originalRoomId) this.originalRoomId = this.value.bed_room_pivot.room_id
        this.axios.get('/rooms').then(response => {
          this.rooms = response.data
          if (!this.rooms.find(r => r.id === this.originalRoomId)) {
            this.rooms.push(this.value.bed_room_pivot.room)
          }
          this.getBeds()
        })
      }
    },
    'value.bed_room_pivot.room_id'() {
      this.getBeds()
    },
    'value.entry'() {
      this.getBeds()
    },
    'value.exit'() {
      this.getBeds()
    }
  }
}
</script>

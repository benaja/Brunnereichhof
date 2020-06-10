<template>
  <v-card width="400">
    <p class="text-right pa-2">
      <v-btn
        text
        icon
        small
        :loading="loadingPdf"
        @click="generatePdf"
      >
        <v-icon>picture_as_pdf</v-icon>
      </v-btn>
      <v-btn
        v-if="$auth.user().hasPermission(['superadmin'], ['roomdispositioner_write'])"
        text
        icon
        small
        @click="editMode = !editMode"
      >
        <v-icon>edit</v-icon>
      </v-btn>
      <v-btn
        v-if="$auth.user().hasPermission(['superadmin'], ['roomdispositioner_write'])"
        text
        icon
        small
        :loading="isDeleting"
        @click="deleteReservation"
      >
        <v-icon>delete</v-icon>
      </v-btn>
      <v-btn
        text
        icon
        small
        @click="$emit('close')"
      >
        <v-icon>close</v-icon>
      </v-btn>
    </p>
    <v-row
      v-if="value.bed_room_pivot && !editMode"
      class="px-3"
    >
      <v-col
        cols="4"
        class="py-0"
      >
        <p class="font-weight-bold subheading">
          Von
        </p>
      </v-col>
      <v-col
        cols="8"
        class="py-0"
      >
        <p>{{ $moment(value.entry).format('DD.MM.YYYY') }}</p>
      </v-col>
      <v-col
        cols="4"
        class="py-0"
      >
        <p class="font-weight-bold subheading">
          Bis
        </p>
      </v-col>
      <v-col
        cols="8"
        class="py-0"
      >
        <p>{{ $moment(value.exit).format('DD.MM.YYYY') }}</p>
      </v-col>
      <v-col
        cols="4"
        class="py-0"
      >
        <p class="font-weight-bold subheading">
          Raum
        </p>
      </v-col>
      <v-col
        cols="8"
        class="py-0"
      >
        <p>{{ value.bed_room_pivot.room.name }}</p>
      </v-col>
      <v-col
        cols="4"
        class="py-0"
      >
        <p class="font-weight-bold subheading">
          Bett
        </p>
      </v-col>
      <v-col
        cols="8"
        class="py-0"
      >
        <p>{{ value.bed_room_pivot.bed.name }}</p>
      </v-col>
      <v-col
        cols="4"
        class="py-0"
      >
        <p class="font-weight-bold subheading">
          Mittarbeiter
        </p>
      </v-col>
      <v-col
        cols="8"
        class="py-0"
      >
        <p>{{ value.employee.lastname }} {{ value.employee.firstname }}</p>
      </v-col>
    </v-row>
    <reservation-form
      v-else-if="editMode"
      ref="form"
      v-model="value"
      :original-room-id="originalRoomId"
    ></reservation-form>
    <v-card-actions v-if="editMode">
      <v-btn
        text
        @click="cancel"
      >
        Abbrechen
      </v-btn>
      <v-spacer></v-spacer>
      <v-btn
        color="blue"
        class="white--text"
        depressed
        :loading="isSaving"
        @click="save"
      >
        Speichern
      </v-btn>
    </v-card-actions>
  </v-card>
</template>

<script>
import { downloadFile, confirmAction } from '@/utils'
import ReservationForm from '@/components/forms/ReservationForm'

export default {
  components: {
    ReservationForm
  },
  props: {
    value: {
      type: Object,
      default: () => ({
        employee_id: null
      })
    },
    selectedDay: {
      type: Object,
      default: null
    },
    original: {
      type: Object,
      default: null
    }
  },
  data() {
    return {
      editMode: false,
      originalRoomId: null,
      isDeleting: false,
      loadingPdf: false,
      isSaving: false
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
    },
    editMode() {
      if (this.editMode && !this.originalRoomId) {
        this.originalRoomId = this.value.bed_room_pivot.room_id
      }
    }
  },
  methods: {
    save() {
      if (this.$refs.form.validate()) {
        this.isSaving = true
        this.axios
          .put(`/reservations/${this.value.id}`, this.reservationBody)
          .then(response => {
            this.updateExistingReservation(response)
          })
          .catch(error => {
            if (error.includes('Employee is already in an other bed at this time')) {
              confirmAction('Dieser Mitarbeiter ist zur angegeben Zeit in einem anderen Bett. Wollen sie ihn umbuchen?', 'Ja, umbuchen!')
                .then(result => {
                  if (result) {
                    this.isSaving = true
                    this.axios
                      .put(`/reservations/${this.value.id}`, {
                        ...this.reservationBody,
                        force: true
                      })
                      .then(response => {
                        this.$emit('updateAll', response.data)
                        this.editMode = false
                      })
                      .catch(() => {
                        this.$swal('Fehler', 'Es ist ein unbekannter Fehler aufgetreten.', 'error')
                      }).finally(() => {
                        this.isSaving = false
                      })
                  }
                })
            } else if (error.includes('Bed is already booked at this time')) {
              this.$swal(
                'Bett ist bereit voll',
                'Dieses Bett ist zureit voll. Bitte wählen Sie ein anderes Bett oder machen sie dies zuerst frei.',
                'error',
              )
            } else if (!error.status(403)) {
              this.$swal('Fehler', 'Es ist ein unbekannter Fehler aufgetreten. Bitte versuchen Sie es später erneut.', 'error')
            }
          }).finally(() => {
            this.isSaving = false
          })
      }
    },
    deleteReservation() {
      confirmAction('Wollen sie diese Reservation wirklich löschen?').then(value => {
        if (value) {
          this.isDeleting = true
          this.axios.delete(`/reservations/${this.value.id}`).then(() => {
            this.$emit('delete', this.original)
          }).finally(() => {
            this.isDeleting = false
          })
        }
      })
    },
    generatePdf() {
      this.loadingPdf = true
      downloadFile(`pdf/reservation/employee/${this.value.employee_id}?date=${this.selectedDay.format('YYYY-MM-DD')}`)
        .catch(() => {
          this.$store.dispatch('error', 'Pdf konnte nicht erstellt werden')
        }).finally(() => {
          this.loadingPdf = false
        })
    },
    updateExistingReservation(response) {
      this.editMode = false
      this.$emit('input', {
        ...response.data,
        bed_room_pivot: {
          ...response.data.bed_room_pivot,
          bed: {
            ...response.data.bed_room_pivot.bed,
            pivot: {
              id: this.value.bed_room_pivot.id
            }
          }
        }
      })
      this.originalRoomId = this.value.bed_room_pivot.room_id
      this.$nextTick(() => {
        this.$emit('update', this._.cloneDeep(this.value))
      })
    },
    cancel() {
      this.editMode = false
      this.$emit('input', this._.cloneDeep(this.original))
    }
  }
}
</script>

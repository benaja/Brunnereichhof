<template>
  <card-layout
    class="reservation-model"
    color="blue"
    title="Raum buchen"
    :saving="isLoading"
    @save="save"
    @cancel="$emit('input', false)"
  >
    <reservation-form
      v-if="value"
      ref="form"
      v-model="reservation"
    ></reservation-form>
  </card-layout>
</template>

<script>
import CardLayout from '@/components/general/CardLayout'
import ReservationForm from '@/components/forms/ReservationForm'
import { confirmAction } from '@/utils'

export default {
  components: {
    CardLayout,
    ReservationForm
  },
  props: {
    value: {
      type: Boolean,
      default: false
    },
    reservation: {
      type: Object,
      default: null
    },
    entry: {
      type: String,
      default: null
    }
  },
  data() {
    return {
      isLoading: false
    }
  },
  computed: {
    reservationPayload() {
      return {
        entry: this.reservation.entry,
        exit: this.reservation.exit,
        room: this.reservation.bed_room_pivot.room_id,
        bed: this.reservation.bed_room_pivot.id,
        employee: this.reservation.employee_id
      }
    }
  },
  watch: {
    value() {
      if (!this.value) this.resetReservation()
    }
  },
  methods: {
    save() {
      if (this.$refs.form.validate()) {
        this.isLoading = true
        this.axios
          .post('/reservations', this.reservationPayload)
          .then(response => {
            this.$emit('add', response.data)
            this.resetReservation()
          })
          .catch(error => {
            if (error.includes('Employee is already in an other bed at this time')) {
              confirmAction(
                'Dieser Mitarbeiter ist zur angegeben Zeit in einem anderen Bett. Wollen sie ihn umbuchen?',
                'Ja, umbuchen!'
              ).then(value => {
                if (value) {
                  this.isLoading = true
                  this.axios
                    .post('/reservations', {
                      ...this.reservationPayload,
                      force: true
                    })
                    .then(response => {
                      this.$emit('updateAll', response.data)
                      this.resetReservation()
                    })
                    .catch(() => {
                      this.$swal('Fehler', 'Es ist ein unbekannter Fehler aufgetreten.', 'error')
                    }).finally(() => {
                      this.isLoading = false
                    })
                }
              })
            } else {
              this.$swal('Fehler', 'Bett konnte nicht gebucht werden. Bitte versuchen Sie es später erneut.', 'error')
            }
          }).finally(() => {
            this.isLoading = false
          })
      }
    },
    resetReservation() {
      this.$emit('input', false)
      this.reservation.exit = null
      this.reservation.bed_room_pivot = {}
      this.reservation.employee_id = null
      this.$refs.form.reset()
    },
    getBeds() {
      if (this.reservation.room) {
        this.axios.get(`/rooms/${this.reservation.room}/beds?entry=${this.reservation.entry}&exit=${this.reservation.exit}`).then(response => {
          this.beds = response.data
        })
      }
    }
  }
}
</script>

<style lang="scss" scoped>
</style>

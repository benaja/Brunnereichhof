<template>
  <card-layout
    class="reservation-model"
    color="blue"
    @save="save"
    @cancel="$emit('input', false)"
  >
    <v-form
      ref="form"
      lazy-validation
    >
      <h2>Raum buchen</h2>
      <date-picker
        v-model="reservation.entry"
        label="Von"
        :rules="rules.required"
        color="blue"
      ></date-picker>
      <date-picker
        v-model="reservation.exit"
        label="Bis"
        :rules="rules.after"
        color="blue"
      ></date-picker>
      <v-select
        v-model="reservation.room"
        :items="rooms"
        label="Raum"
        item-value="id"
        item-text="name"
        :rules="rules.required"
        color="blue"
      ></v-select>
      <v-select
        v-model="reservation.bed"
        :items="beds"
        label="Bett"
        item-value="pivot.id"
        item-text="name"
        :disabled="!reservation.room"
        :rules="rules.required"
        no-data-text="Kein freies Bett vorhanden."
        color="blue"
      ></v-select>
      <select-employee
        v-model="reservation.employee"
        :rules="rules.required"
      ></select-employee>
    </v-form>
  </card-layout>
</template>

<script>
import CardLayout from '@/components/general/CardLayout'
import DatePicker from '@/components/general/DatePicker'
import SelectEmployee from '@/components/Roomdispositioner/SelectEmployee'
import { rules } from '@/utils'

export default {
  name: 'CreateReservation',
  components: {
    CardLayout,
    DatePicker,
    SelectEmployee
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
      rooms: [],
      employees: [],
      beds: [],
      isLoadingEmployees: false,
      employeesLoaded: false,
      searchStringEmployee: null,
      rules: {
        ...rules,
        after: [() => new Date(this.reservation.entry) <= new Date(this.reservation.exit) || 'Das Datum muss nach dem Startdatum sein.']
      }
    }
  },
  watch: {
    searchStringEmployee() {
      if (this.employeesLoaded) return
      if (this.isLoadingEmployees) return
      this.isLoadingEmployees = true

      this.$store.dispatch('employeesWithGuests').then((employees) => {
        this.employees = employees
        this.isLoadingEmployees = false
        this.employeesLoaded = true
      })
    },
    'reservation.room': function() {
      this.getBeds()
    },
    'reservation.entry': function() {
      if (this.reservation.room) {
        this.getBeds()
      }
    },
    'reservation.exit': function() {
      if (this.reservation.room) {
        this.getBeds()
      }
    },
    value() {
      // if (!this.value) {
      // console.log(this.reservation.entry)
      // let temp = this.reservation.entry
      // this.reservation.entry = ''
      // this.$refs.form.reset()
      // this.$nextTick(() => {
      //   this.reservation.entry = temp
      // })
      // }
      // console.log('test')
      // if (this.rooms.length === 0) {
      //   this.axios.get('/rooms').then(response => {
      //     this.rooms = response.data
      //   })
      // }
    }
  },
  mounted() {
    this.axios.get('/rooms').then((response) => {
      this.rooms = response.data
    })
  },
  methods: {
    save() {
      if (this.$refs.form.validate()) {
        this.axios
          .post('/reservations', this.reservation)
          .then((response) => {
            this.$emit('add', response.data)
            this.resetReservation()
          })
          .catch((error) => {
            if (error.includes('Employee is already in an other bed at this time')) {
              this.$swal({
                title: 'Achtung!',
                text: 'Dieser Mitarbeiter ist zur angegeben Zeit in einem anderen Bett. Wollen sie ihn umbuchen?',
                type: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ja, umbuchen!',
                cancelButtonText: 'Nein, abbrechen'
              }).then((result) => {
                if (result.value) {
                  this.axios
                    .post('/reservations', {
                      ...this.reservation,
                      force: true
                    })
                    .then((response) => {
                      this.$emit('updateAll', response.data)
                      this.resetReservation()
                    })
                    .catch(() => {
                      this.$swal('Fehler', 'Es ist ein unbekannter Fehler aufgetreten.', 'error')
                    })
                }
              })
            } else {
              this.$swal('Fehler', 'Bett konnte nicht gebucht werden. Bitte versuchen Sie es später erneut.', 'error')
            }
          })
      }
    },
    resetReservation() {
      this.$emit('input', false)
      this.reservation.exit = null
      this.reservation.room = null
      this.reservation.bed = null
      this.reservation.employee = null
      this.$refs.form.reset()
    },
    getBeds() {
      if (this.reservation.room) {
        this.axios.get(`/rooms/${this.reservation.room}/beds?entry=${this.reservation.entry}&exit=${this.reservation.exit}`).then((response) => {
          this.beds = response.data
        })
      }
    }
  }
}
</script>

<style lang="scss" scoped>
</style>

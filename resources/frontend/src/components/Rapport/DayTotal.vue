<template>
  <div>
    <v-dialog
      v-model="isOpen"
      max-width="600px"
    >
      <template v-slot:activator="{ on }">
        <v-btn
          color="primary"
          outlined
          class="my-2"
          v-on="on"
        >
          <v-icon>today</v-icon>
          <span class="ml-3">Tagestotal</span>
        </v-btn>
      </template>
      <v-card max-width="600px">
        <v-card-title>
          <h3>Tagestotal</h3>
        </v-card-title>
        <v-divider></v-divider>
        <v-card-text>
          <v-menu
            ref="dateMenu"
            v-model="datePickerMenu"
            :close-on-content-click="false"
            :nudge-right="40"
            :return-value.sync="date"
            transition="scale-transition"
            offset-y
            min-width="290px"
          >
            <template v-slot:activator="{ on }">
              <v-text-field
                v-model="date"
                label="Datum auswählen"
                prepend-icon="event"
                readonly
                v-on="on"
              ></v-text-field>
            </template>
            <v-date-picker
              v-model="date"
              no-title
              scrollable
              first-day-of-week="1"
              locale="ch-de"
            >
              <v-spacer></v-spacer>
              <v-btn
                text
                color="primary"
                @click="datePickerMenu = false"
              >
                Abbrechen
              </v-btn>
              <v-btn
                text
                color="primary"
                @click="$refs.dateMenu.save(date)"
              >
                OK
              </v-btn>
            </v-date-picker>
          </v-menu>
          <v-divider></v-divider>
          <div
            v-for="dayTotal of dayTotals"
            :key="dayTotal.employee.id"
          >
            <p class="my-2">
              <span
                class="font-weight-bold"
              >{{ dayTotal.employee.lastname }} {{ dayTotal.employee.firstname }}</span>
              <span class="float-right">{{ dayTotal.hours }}</span>
            </p>
            <v-divider></v-divider>
          </div>
        </v-card-text>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
export default {
  name: 'DayTotal',
  props: {
    value: Boolean
  },
  data() {
    return {
      isOpen: false,
      datePickerMenu: false,
      date: new Date().toISOString().substr(0, 10),
      dayTotals: []
    }
  },
  watch: {
    value() {
      if (this.value) {
        this.getDayTotal()
      }
      this.isOpen = this.value
    },
    isOpen() {
      this.$emit('input', this.isOpen)
    },
    datePickerMenu() {
      if (!this.datePickerMenu) {
        this.getDayTotal()
      }
    }
  },
  methods: {
    getDayTotal() {
      this.axios
        .get(`rapport/daytotal/${this.date}`)
        .then(response => {
          this.dayTotals = response.data
        })
        .catch(() => {
          this.$swal('Fehler', 'Tagestotal konnte nicht generiert werden.', 'error')
        })
    }
  }
}
</script>

<style lang="scss" scoped>
</style>

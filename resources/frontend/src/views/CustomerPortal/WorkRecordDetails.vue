<template>
  <v-container>
    <div class="d-flex flex-wrap">
      <h1 class="display-1">
        Arbeiten im {{ (new Date()).getFullYear() }}
      </h1>
      <v-btn
        v-if="!editMode && $store.getters.isEditTime"
        color="primary"
        depressed
        class="ml-auto mt-3"
        @click="toggleEditMode"
      >
        <v-icon class="mr-2">
          edit
        </v-icon>Bearbeiten
      </v-btn>
    </div>
    <h2
      class="mb-4 headline pre-wrap-text"
    >
      {{ $store.getters.isEditTime ? $store.getters.settings.hourrecordValid :
        $store.getters.settings.hourrecordInvalid }}
    </h2>
    <v-form ref="form">
      <week
        v-for="(week, index) of weeks"
        :key="index"
        :week="week"
        :cultures="cultures"
        :year="$moment().format('YYYY')"
        :edit="editMode"
        @input="w => (week = w)"
        @remove="removeWeek(index)"
      ></week>
    </v-form>
    <v-row class="px-3">
      <v-col
        cols="12"
        class="pa-2"
      >
        <v-alert
          :value="!allValid && trySave"
          type="error"
        >
          Überprüffen sie ob überall eine Kultur/Arbeit ausgewählt wurde.
        </v-alert>
      </v-col>
    </v-row>
    <div
      v-if="editMode || $store.getters.isEditTime"
      class="d-flex flex-wrap"
    >
      <v-btn
        v-if="editMode"
        class="mb-4"
        to="/kundenportal/erfassen"
        color="primary"
        depressed
      >
        <v-icon class="mr-2">
          keyboard_arrow_left
        </v-icon>Zur Wochenauswahl
      </v-btn>
      <v-btn
        v-if="editMode"
        color="primary"
        class="mb-4 mr-2 ml-sm-auto ml-0"
        outlined
        @click="addHourrecordDialog = true"
      >
        <v-icon class="mr-2">
          add
        </v-icon>Woche/Kultur hinzufügen
      </v-btn>
      <v-btn
        v-if="editMode"
        class="mb-4"
        color="primary"
        depressed
        @click="saveAll"
      >
        <v-icon class="mr-2">
          check
        </v-icon>Fertig
      </v-btn>
      <v-btn
        v-else-if="weeksLength > 6"
        color="primary"
        depressed
        class="mb-4 ml-auto"
        @click="toggleEditMode"
      >
        <v-icon class="mr-2">
          edit
        </v-icon>Bearbeiten
      </v-btn>
    </div>
    <add-hourrecord
      v-model="addHourrecordDialog"
      title="Woche/Kultur hinzufügen"
      :cultures="cultures"
      :customer="$auth.user().customer"
      :year="$moment().format('YYYY')"
      @add="addHourrecord"
    ></add-hourrecord>
  </v-container>
</template>

<script>
import Week from '@/components/CustomerPortal/Week'
import AddHourrecord from '@/components/Hourrecords/AddHourrecord'

export default {
  name: 'WorkRecordDetails',
  components: {
    Week,
    AddHourrecord
  },
  data() {
    return {
      weeks: this.$store.getters.hourRecords,
      cultures: [],
      trySave: false,
      addHourrecordDialog: false,
      editMode: false
    }
  },
  computed: {
    allValid() {
      if (this.weeks.length === 0) return false
      if (this.weeks.find((week) => week.find((culture) => !culture.culture))) return false
      return true
    },
    isEditModeEnabled() {
      return !(!this.$store.getters.isEditTime || localStorage.getItem('finishedHourrecordYear') === this.$moment().format('YYYY'))
    },
    weeksLength() {
      return Object.keys(this.weeks).length
    },
    weeksFlat() {
      return Object.keys(this.weeks).flatMap((week) => this.weeks[week])
    }
  },
  watch: {
    weeks: {
      handler() {
        this.$store.commit('hourRecords', this.weeks)
      },
      deep: true
    }
  },
  mounted() {
    this.editMode = this.isEditModeEnabled
    this.axios.get('/culture').then((response) => {
      this.cultures = response.data
    })
    if (this.weeks.length === 0) {
      this.$store.commit('isLoading', true)
      this.axios.get('/hourrecord').then((response) => {
        this.weeks = response.data
        this.$store.commit('isLoading', false)
      })
    }
    if (!this.$store.getters.settings.hourrecordStartDate) {
      this.axios.get('/settings/hourrecords').then((response) => {
        this.$store.commit('settings', response.data)
      })
    }
  },
  methods: {
    removeWeek(weekNumber) {
      delete this.weeks[weekNumber]
    },
    addHourrecord(hourrecord) {
      if (this.weeks[hourrecord.week]) {
        this.weeks[hourrecord.week].push(hourrecord)
      } else if (!Object.keys(this.weeks).length) {
        this.weeks = {
          [hourrecord.week]: [hourrecord]
        }
      } else {
        this.$set(this.weeks, hourrecord.week, [hourrecord])
      }
    },
    saveAll() {
      if (this.$refs.form.validate()) {
        this.axios
          .patch('/hourrecord', this.weeksFlat)
          .then(() => {
            this.toggleEditMode()
            this.$swal('Danke', 'Alle Ihre vorgesehenen Arbeiten wurden erfolgreich gespeichert. Vielen Dank für Ihre Zeit.', 'success')
          })
          .catch(() => {
            this.$swal(
              'Fehler beim Speichern',
              `Leider ist beim Speichern ein unbekannter Fehler aufgetreten.
                Bitte versuchen Sie es später erneut oder melden Sie sich bei uns.`,
              'error',
            )
          })
      } else {
        this.$swal('Nicht alles ausgefüllt', 'Bitte füllen Sie alle fehlenden Felder aus.')
      }
    },
    toggleEditMode() {
      this.editMode = !this.editMode
      if (this.editMode) {
        localStorage.setItem('finishedHourrecordYear', null)
      } else {
        localStorage.setItem('finishedHourrecordYear', this.$moment().format('YYYY'))
      }
    }
  }
}
</script>

<style lang="scss" scoped>
.next-button {
  float: right;
}

.pre-wrap-text {
  white-space: pre-wrap;
  text-overflow: inherit;
}

@media only screen and (max-width: 600px) {
  .next-button {
    float: none;
    margin-left: 0;
  }
}
</style>

<template>
  <fragment>
    <navigation-bar
      :title="`Arbeiten im ${(new Date()).getFullYear()}`"
      :loading="$store.getters.isLoading.settings || isLoading"
    >
      <v-btn
        v-if="!editMode && $store.getters.isEditTime"
        color="primary"
        depressed
        class="ml-auto"
        @click="toggleEditMode"
      >
        <v-icon class="mr-2">
          edit
        </v-icon>Bearbeiten
      </v-btn>
    </navigation-bar>
    <v-container>
      <div class="d-flex flex-wrap">
      </div>
      <h2
        class="mb-4 headline pre-wrap-text"
        v-html="$store.getters.isEditTime ?
          $store.getters.hourrecordSettings.hourrecordValid :
          $store.getters.hourrecordSettings.hourrecordInvalid"
      >
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
          :loading="isSaving"
          @click="saveAll"
        >
          <v-icon class="mr-2">
            check
          </v-icon>Fertig
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
  </fragment>
</template>

<script>
import Week from '@/components/CustomerPortal/Week'
import AddHourrecord from '@/components/Hourrecords/AddHourrecord'
import { mapGetters } from 'vuex'

export default {
  components: {
    Week,
    AddHourrecord
  },
  data() {
    return {
      weeks: this.$store.getters.hourRecords,
      trySave: false,
      addHourrecordDialog: false,
      editMode: false,
      isLoading: false,
      isSaving: false
    }
  },
  computed: {
    ...mapGetters(['cultures']),
    allValid() {
      if (!Object.keys(this.weeks).length) return false
      if (Object.keys(this.weeks)
        .find(key => this.weeks[key].find(culture => !culture.culture))) return false
      return true
    },
    isEditModeEnabled() {
      return !(!this.$store.getters.isEditTime || localStorage.getItem('finishedHourrecordYear') === this.$moment().format('YYYY'))
    },
    weeksLength() {
      return Object.keys(this.weeks).length
    },
    weeksFlat() {
      return Object.keys(this.weeks).flatMap(week => this.weeks[week])
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
    this.$store.dispatch('fetchCultures')
    if (this.weeks.length === 0) {
      this.isLoading = true
      this.axios.get('/hourrecords').then(response => {
        this.weeks = response.data
      }).catch(() => {
        this.$store.dispatch('error', 'Fehler beim Abrufen der Daten')
      }).finally(() => {
        this.isLoading = false
      })
    }
    if (!this.$store.getters.hourrecordSettings.hourrecordStartDate) {
      this.$store.dispatch('fetchHourrecordSettings')
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
        this.isSaving = true
        this.axios
          .patch('/hourrecords', this.weeksFlat)
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
          }).finally(() => {
            this.isSaving = false
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

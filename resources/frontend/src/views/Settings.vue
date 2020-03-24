<template>
  <v-container>
    <h1>Einstellungen</h1>
    <h2>Zeiterfassung</h2>
    <v-row wrap>
      <v-col cols="12">
        <edit-work-types />
      </v-col>
      <v-col cols="12" class="mt-4">
        <h2>Stundenangaben der Kunden</h2>
      </v-col>
      <v-col cols="12" sm6 class="px-2">
        <v-menu
          :close-on-content-click="false"
          v-model="menu1"
          offset-y
          transition="scale-transition"
          min-width="290px"
          :nudge-right="40"
        >
          <template v-slot:activator="{ on }">
            <v-text-field
              v-model="hourrecordStartDateFormated"
              label="Sartdatum"
              prepend-icon="event"
              :readonly="!isUserAllowedToEdit"
              v-on="on"
            />
          </template>
          <v-date-picker
            v-model="settings.hourrecordStartDate"
            @input="menu1 = false; update('hourrecordStartDate')"
            locale="ch-de"
            first-day-of-week="1"
            :readonly="!isUserAllowedToEdit"
          ></v-date-picker>
        </v-menu>
      </v-col>
      <v-col cols="12" sm6 class="px-2">
        <v-menu
          :close-on-content-click="false"
          v-model="menu2"
          offset-y
          transition="scale-transition"
          min-width="290px"
          :nudge-right="40"
        >
          <template v-slot:activator="{ on }">
            <v-text-field
              v-model="hourrecordEndDateFormated"
              label="Enddatum"
              prepend-icon="event"
              :readonly="!isUserAllowedToEdit"
              v-on="on"
            />
          </template>
          <v-date-picker
            v-model="settings.hourrecordEndDate"
            @input="menu2 = false; update('hourrecordEndDate')"
            locale="ch-de"
            first-day-of-week="1"
            :readonly="!isUserAllowedToEdit"
          ></v-date-picker>
        </v-menu>
      </v-col>
      <v-col cols="12" class="mt-4">
        <h2>Kundenportal</h2>
        <p>
          Für einen Wert, welcher sich ändern kann, muss folgende Schreibweise verwendet werden: {name}
          <br />Welche Werte verwendet werden können, ist immer in der Klammer angegeben. Achte auch auf Gross- und Kleinschreibung.
        </p>
      </v-col>
      <v-col cols="12">
        <v-text-field
          label="Willkommenstext {name}"
          v-model="settings.welcomeText"
          @change="update('welcomeText')"
          :readonly="!isUserAllowedToEdit"
        ></v-text-field>
        <v-textarea
          label="Untertitel"
          v-model="settings.subtitle"
          @change="update('subtitle')"
          auto-grow
          rows="1"
          :readonly="!isUserAllowedToEdit"
        ></v-textarea>
        <v-textarea
          label="Arbeiten-Karte Titel"
          v-model="settings.hourrecordTitle"
          @change="update('hourrecordTitle')"
          auto-grow
          rows="1"
          :readonly="!isUserAllowedToEdit"
        ></v-textarea>
        <v-textarea
          label="Arbeiten-Karte text {datum}"
          v-model="settings.hourrecordValid"
          @change="update('hourrecordValid')"
          auto-grow
          rows="1"
          :readonly="!isUserAllowedToEdit"
        ></v-textarea>
        <v-textarea
          label="Arbeiten-Karte Zeit abgeloffen text"
          v-model="settings.hourrecordInvalid"
          @change="update('hourrecordInvalid')"
          auto-grow
          rows="1"
          :readonly="!isUserAllowedToEdit"
        ></v-textarea>
        <v-textarea
          label="Umfrage-Karte Titel"
          v-model="settings.surveyTitle"
          @change="update('surveyTitle')"
          auto-grow
          rows="1"
          :readonly="!isUserAllowedToEdit"
        ></v-textarea>
        <v-textarea
          label="Umfrage-Karte Text"
          v-model="settings.surveyText"
          @change="update('surveyText')"
          auto-grow
          rows="1"
          :readonly="!isUserAllowedToEdit"
        ></v-textarea>
        <v-textarea
          label="Wochenrapport-Karte Titel"
          v-model="settings.weekRapportTitle"
          @change="update('weekRapportTitle')"
          auto-grow
          rows="1"
          :readonly="!isUserAllowedToEdit"
        ></v-textarea>
        <v-textarea
          label="Wochenrapport-Karte Text"
          v-model="settings.weekRapportText"
          @change="update('weekRapportText')"
          auto-grow
          rows="1"
          :readonly="!isUserAllowedToEdit"
        ></v-textarea>
      </v-col>
      <v-col cols="12" class="mt-4">
        <h2>Sonstige Einstellungen</h2>
      </v-col>
      <v-col cols="12">
        <v-switch
          v-model="settings.rapportFoodTypeEnabled"
          label="Verpflegung bei Wochenrapport"
          @change="update('rapportFoodTypeEnabled')"
          :readonly="!isUserAllowedToEdit"
        ></v-switch>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import EditWorkTypes from '@/components/Settings/EditWorkTypes'

export default {
  name: 'Settings',
  components: {
    EditWorkTypes
  },
  data() {
    return {
      settings: {},
      menu1: false,
      menu2: false,
      isUserAllowedToEdit: false
    }
  },
  mounted() {
    this.$store.commit('isLoading', true)
    this.axios.get(process.env.VUE_APP_API_URL + 'settings').then(response => {
      this.settings = response.data
      this.$store.commit('isLoading', false)
    })
    this.isUserAllowedToEdit = this.$auth.user().hasPermission(['superadmin'], ['settings_write'])
  },
  methods: {
    update(key) {
      this.axios
        .patch(process.env.VUE_APP_API_URL + 'settings', {
          [key]: this.settings[key]
        })
        .catch(() => {
          this.$swal('Fehler', 'Einstellungen konnten nicht gespeichert werden', 'error')
        })
    }
  },
  computed: {
    hourrecordStartDateFormated() {
      return new Date(this.settings.hourrecordStartDate).toLocaleDateString()
    },
    hourrecordEndDateFormated() {
      return new Date(this.settings.hourrecordEndDate).toLocaleDateString()
    }
  }
}
</script>

<style lang="scss" scoped>
</style>

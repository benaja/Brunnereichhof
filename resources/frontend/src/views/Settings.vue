<template>
  <fragment>
    <navigation-bar title="Einstellungen"></navigation-bar>
    <v-container>
      <h2>Zeiterfassung</h2>
      <v-row wrap>
        <v-col cols="12">
          <edit-work-types />
        </v-col>
        <v-col
          cols="12"
          class="mt-4"
        >
          <h2>Stundenangaben der Kunden</h2>
        </v-col>
        <v-col
          cols="12"
          sm6
          class="px-2"
        >
          <v-menu
            v-model="menu1"
            :close-on-content-click="false"
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
              locale="ch-de"
              first-day-of-week="1"
              :readonly="!isUserAllowedToEdit"
              @input="menu1 = false; update('hourrecordStartDate')"
            ></v-date-picker>
          </v-menu>
        </v-col>
        <v-col
          cols="12"
          sm6
          class="px-2"
        >
          <v-menu
            v-model="menu2"
            :close-on-content-click="false"
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
              locale="ch-de"
              first-day-of-week="1"
              :readonly="!isUserAllowedToEdit"
              @input="menu2 = false; update('hourrecordEndDate')"
            ></v-date-picker>
          </v-menu>
        </v-col>
        <v-col
          cols="12"
          class="mt-4"
        >
          <h2>Kundenportal</h2>
          <p>
            Für einen Wert, welcher sich ändern kann, muss folgende
            Schreibweise verwendet werden: {name}
            <br />Welche Werte verwendet werden können, ist immer in der Klammer angegeben.
            Achte auch auf Gross- und Kleinschreibung.
          </p>
        </v-col>
        <v-col cols="12">
          <v-text-field
            v-model="settings.welcomeText"
            label="Willkommenstext {name}"
            :readonly="!isUserAllowedToEdit"
            @change="update('welcomeText')"
          ></v-text-field>
          <v-textarea
            v-model="settings.subtitle"
            label="Untertitel"
            auto-grow
            rows="1"
            :readonly="!isUserAllowedToEdit"
            @change="update('subtitle')"
          ></v-textarea>
          <v-textarea
            v-model="settings.hourrecordTitle"
            label="Arbeiten-Karte Titel"
            auto-grow
            rows="1"
            :readonly="!isUserAllowedToEdit"
            @change="update('hourrecordTitle')"
          ></v-textarea>
          <v-textarea
            v-model="settings.hourrecordValid"
            label="Arbeiten-Karte text {datum}"
            auto-grow
            rows="1"
            :readonly="!isUserAllowedToEdit"
            @change="update('hourrecordValid')"
          ></v-textarea>
          <v-textarea
            v-model="settings.hourrecordInvalid"
            label="Arbeiten-Karte Zeit abgeloffen text"
            auto-grow
            rows="1"
            :readonly="!isUserAllowedToEdit"
            @change="update('hourrecordInvalid')"
          ></v-textarea>
          <v-textarea
            v-model="settings.surveyTitle"
            label="Umfrage-Karte Titel"
            auto-grow
            rows="1"
            :readonly="!isUserAllowedToEdit"
            @change="update('surveyTitle')"
          ></v-textarea>
          <v-textarea
            v-model="settings.surveyText"
            label="Umfrage-Karte Text"
            auto-grow
            rows="1"
            :readonly="!isUserAllowedToEdit"
            @change="update('surveyText')"
          ></v-textarea>
          <v-textarea
            v-model="settings.weekRapportTitle"
            label="Wochenrapport-Karte Titel"
            auto-grow
            rows="1"
            :readonly="!isUserAllowedToEdit"
            @change="update('weekRapportTitle')"
          ></v-textarea>
          <v-textarea
            v-model="settings.weekRapportText"
            label="Wochenrapport-Karte Text"
            auto-grow
            rows="1"
            :readonly="!isUserAllowedToEdit"
            @change="update('weekRapportText')"
          ></v-textarea>
        </v-col>
        <v-col
          cols="12"
          class="mt-4"
        >
          <h2>Sonstige Einstellungen</h2>
        </v-col>
        <v-col cols="12">
          <v-switch
            v-model="settings.rapportFoodTypeEnabled"
            label="Verpflegung bei Wochenrapport"
            :readonly="!isUserAllowedToEdit"
            @change="update('rapportFoodTypeEnabled')"
          ></v-switch>
        </v-col>
      </v-row>
    </v-container>
  </fragment>
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
  computed: {
    hourrecordStartDateFormated() {
      return new Date(this.settings.hourrecordStartDate).toLocaleDateString()
    },
    hourrecordEndDateFormated() {
      return new Date(this.settings.hourrecordEndDate).toLocaleDateString()
    }
  },
  mounted() {
    this.$store.commit('loading', { settings: true })
    this.axios.get('settings').then(response => {
      this.settings = response.data
      this.$store.commit('loading', { settings: false })
    })
    this.isUserAllowedToEdit = this.$auth.user().hasPermission(['superadmin'], ['settings_write'])
  },
  methods: {
    update(key) {
      this.axios
        .patch('settings', {
          [key]: this.settings[key]
        })
        .catch(() => {
          this.$swal('Fehler', 'Einstellungen konnten nicht gespeichert werden', 'error')
        })
    }
  }
}
</script>

<style lang="scss" scoped>
</style>

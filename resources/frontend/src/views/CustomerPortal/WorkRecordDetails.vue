<template>
  <v-container>
    <h1 class="display-1">Arbeiten im {{(new Date()).getFullYear()}}</h1>
    <h2
      class="mb-4 headline"
      v-if="$store.getters.isEditTime"
    >Geben Sie für die ausgewählten Wochen an, wieviele Studen Sie in den jeweiligen Wochen geplant haben.</h2>
    <h2
      class="mb-4 headline"
      v-else
    >Hier können sie ihre vorgesehenen Arbeiten anschauen. Falls sie Änderungen bezüglich ihren Arbeiten haben kontaktieren sie bitte info@brunnereichhof.ch.</h2>
    <week
      v-for="(week, index) of weeks"
      :week="week"
      :cultures="cultures"
      :key="index"
      @input="w => week = w"
    ></week>
    <v-row class="px-3">
      <v-col cols="12" class="pa-2">
        <v-alert
          :value="!allValid && trySave"
          type="error"
        >Überprüffen sie ob überall eine Kultur/Arbeit ausgewählt wurde.</v-alert>
      </v-col>
      <v-col cols="6" v-if="$store.getters.isEditTime">
        <v-btn class="mb-4" to="/kundenportal/erfassen" color="primary">Zur Wochenauswahl</v-btn>
      </v-col>
      <v-col cols="6" v-if="$store.getters.isEditTime">
        <p class="text-right">
          <v-btn
            class="mb-4"
            color="primary"
            @click="$swal('Danke', 'Alle ihre vorgesehenen Arbeiten wurden erfolgreich gespeichert. Vielen Dank für ihre Zeit.', 'success')"
          >Fertig</v-btn>
        </p>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import Week from '@/components/CustomerPortal/Week'
export default {
  name: 'WorkRecordDetails',
  components: {
    Week
  },
  data() {
    return {
      weeks: this.$store.getters.hourRecords,
      cultures: [],
      trySave: false
    }
  },
  mounted() {
    this.axios.get(process.env.VUE_APP_API_URL + 'culture').then(response => {
      this.cultures = response.data
    })
    if (this.weeks.length === 0) {
      this.$store.commit('isLoading', true)
      this.axios.get(process.env.VUE_APP_API_URL + 'hourrecord').then(response => {
        this.weeks = response.data
        this.$store.commit('isLoading', false)
      })
    }
    if (!this.$store.getters.settings.hourrecordStartDate) {
      this.axios.get(process.env.VUE_APP_API_URL + 'settings/hourrecords').then(response => {
        this.$store.commit('settings', response.data)
      })
    }
  },
  methods: {
    save() {
      this.trySave = true
      if (this.allValid) {
        let weeks = this.selectedWeeks.flatMap(w => {
          for (let culture of w.cultures) {
            culture.week = w.week
            if (culture.culture.id) {
              culture.culture = culture.culture.name
            }
          }
          return w.cultures
        })
        this.axios
          .post(process.env.VUE_APP_API_URL + 'hourrecord', {
            weeks
          })
          .then(() => {
            this.$swal('')
          })
          .catch(() => {
            this.$swal('Fehler', 'Es ist ein unbekannter Feheler aufgetreten. Bitter versuchen Sie es später erneut.')
          })
      }
    }
  },
  computed: {
    allValid() {
      if (this.weeks.length === 0) return false
      for (let week in this.weeks) {
        for (let culture of week) {
          // if (!culture.hours) return false
          if (!culture.culture) return false
        }
      }
      return true
    }
  },
  watch: {
    weeks: {
      handler() {
        this.$store.commit('hourRecords', this.weeks)
      },
      deep: true
    }
  }
}
</script>

<style lang="scss" scoped>
.next-button {
  float: right;
}

@media only screen and (max-width: 600px) {
  .next-button {
    float: none;
    margin-left: 0;
  }
}
</style>

<template>
  <fragment>
    <navigation-bar
      :title="`Arbeiten im ${new Date().getFullYear()}`"
      :loading="$store.getters.isLoading.settings || isLoading"
    ></navigation-bar>
    <v-container>
      <h2 class="mb-4 headline">
        Wählen sie alle Kallenderwochen aus, in denen Sie Arbeiten vorgesehen haben.
      </h2>
      <v-row>
        <v-col
          v-for="week of activeWeeks"
          :key="week.week"
          cols="12"
          sm6
          md="4"
          lg="3"
        >
          <v-checkbox
            v-model="week.isSelected"
            :disabled="!week.active"
            class="mt-0"
          >
            <div slot="label">
              <p class="my-0">
                <span class="font-weight-bold">KW {{ week.week }}</span>
                ({{ week.monday.format('DD.MM.YYYY') }} - {{ week.sunday.format('DD.MM.YYYY') }})
              </p>
            </div>
          </v-checkbox>
        </v-col>
      </v-row>
      <v-btn
        class="mb-4 next-button"
        :disabled="selectedWeeks.length === 0"
        :loading="isSaving"
        color="primary"
        depressed
        @click="save"
      >
        Weiter
      </v-btn>
    </v-container>
  </fragment>
</template>

<script>
import { mapGetters } from 'vuex'

export default {
  data() {
    return {
      date: this.$moment().startOf('year'),
      weeks: this.$store.getters.recordWeeks,
      hourRecords: this.$store.getters.hourRecords,
      isLoading: false,
      isSaving: false
    }
  },
  computed: {
    ...mapGetters(['settings']),
    activeWeeks() {
      return this.weeks.filter(w => w.active)
    },
    selectedWeeks() {
      return this.weeks.filter(w => w.isSelected)
    }
  },
  watch: {
    weeks: {
      handler() {
        this.$store.commit('recordWeeks', this.weeks)
      },
      deep: true
    }
  },
  mounted() {
    this.$store.dispatch('fetchHourrecordSettings').then(() => {
      if (!this.$store.getters.isEditTime) {
        this.$router.push('/kundenportal/erfassen/details')
      }
    })
    if (this.weeks.length === 0) {
      let monday = this.date.clone().startOf('week')
      for (let i = 1; i <= 52; i++) {
        const sunday = monday.clone().endOf('week')
        const week = {
          monday,
          sunday,
          week: monday.week(),
          isSelected: false,
          active: monday.isAfter(this.$moment())
        }
        this.weeks.push(week)
        monday = monday.clone()
        monday.add(7, 'days')
      }
    }
    if (this.hourRecords.length === 0) {
      this.isLoading = true
      this.axios
        .get('hourrecords')
        .then(response => {
          this.hourRecords = response.data
          for (const key in this.hourRecords) {
            this.weeks[key - 1].isSelected = true
          }
        })
        .catch(() => {
          this.$store.dispatch('error', 'Fehler beim Abrufen der Daten')
        })
        .finally(() => {
          this.isLoading = false
        })
    } else {
      for (const key in this.hourRecords) {
        this.weeks[key - 1].isSelected = true
      }
    }
  },
  methods: {
    getMonday(date) {
      const day = date.getDay()
      const diff = date.getDate() - day + (day === 0 ? -6 : 1)
      return new Date(date.setDate(diff))
    },
    save() {
      this.isSaving = true
      this.axios
        .post('hourrecords', {
          weeks: this.selectedWeeks
        })
        .then(response => {
          this.$store.commit('hourRecords', response.data)
          this.$router.push('/kundenportal/erfassen/details')
        })
        .catch(error => {
          if (error.includes('the edit duration is over')) {
            this.$swal('Fehler', 'Die Bearbeitungszeit für dieses Jahr ist vorbei.', 'error')
          } else {
            this.$swal('Fehler', 'Es ist ein unbekannter Fehler aufgetreten. Versuchen Sie es bitte später erneut.', 'error')
          }
        })
        .finally(() => {
          this.isSaving = false
        })
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

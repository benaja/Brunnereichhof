<template>
  <v-container>
    <h1 class="display-1">Arbeiten im {{(new Date()).getFullYear()}}</h1>
    <h2
      class="mb-4 headline"
    >Wählen sie alle Kallenderwochen aus, in denen Sie Arbeiten vorgesehen haben.</h2>
    <v-row>
      <v-col cols="12" sm6 md="4" lg="3" v-for="week of activeWeeks" :key="week.week">
        <v-checkbox v-model="week.isSelected" :disabled="!week.active" class="mt-0">
          <div slot="label">
            <p class="my-0">
              <span class="font-weight-bold">KW {{week.week}}</span>
              ({{week.monday.toLocaleDateString()}} - {{week.sunday.toLocaleDateString()}})
            </p>
          </div>
        </v-checkbox>
      </v-col>
    </v-row>
    <v-btn
      class="mb-4 next-button"
      @click="save"
      :disabled="selectedWeeks.length === 0"
      color="primary"
    >Weiter</v-btn>
  </v-container>
</template>

<script>
export default {
  name: 'WorkRecord',
  data() {
    return {
      date: new Date(new Date().getFullYear(), 0, 1),
      weeks: this.$store.getters.recordWeeks,
      hourRecords: this.$store.getters.hourRecords
    }
  },
  mounted() {
    if (this.weeks.length === 0) {
      let monday = this.getMonday(this.date)
      for (let i = 1; i <= 52; i++) {
        let sunday = new Date(monday)
        sunday.setDate(sunday.getDate() + 6)
        let week = {
          monday: monday,
          sunday: sunday,
          week: i,
          isSelected: false,
          active: monday > new Date()
        }
        this.weeks.push(week)
        monday = new Date(monday)
        monday.setDate(monday.getDate() + 7)
      }
    }
    if (this.hourRecords.length === 0) {
      this.$store.commit('isLoading', true)
      this.axios.get('hourrecord').then(response => {
        this.hourRecords = response.data
        for (let key in this.hourRecords) {
          this.weeks[key - 1].isSelected = true
        }
        this.$store.commit('isLoading', false)
      })
    } else {
      for (let key in this.hourRecords) {
        this.weeks[key - 1].isSelected = true
      }
    }
  },
  methods: {
    getMonday(date) {
      let day = date.getDay()
      let diff = date.getDate() - day + (day === 0 ? -6 : 1)
      return new Date(date.setDate(diff))
    },
    save() {
      this.$store.commit('isLoading', true)
      this.axios
        .post('hourrecord', {
          weeks: this.selectedWeeks
        })
        .then(response => {
          this.$store.commit('isLoading', false)
          this.$store.commit('hourRecords', response.data)
          this.$router.push('/kundenportal/erfassen/details')
        })
        .catch(error => {
          if (error.includes('the edit duration is over')) {
            this.$store.commit('isLoading', false)
            this.$swal('Fehler', 'Die Bearbeitungszeit für dieses Jahr ist vorbei.', 'error')
          } else {
            this.$store.commit('isLoading', false)
            this.$swal('Fehler', 'Es ist ein unbekannter Fehler aufgetreten. Versuchen Sie es bitte später erneut.', 'error')
          }
        })
    }
  },
  computed: {
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

<template>
  <v-container>
    <h1 class="display-1">
      Arbeiten im {{ (new Date()).getFullYear() }}
    </h1>
    <h2
      class="mb-4 headline"
    >
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
              ({{ week.monday.toLocaleDateString() }} - {{ week.sunday.toLocaleDateString() }})
            </p>
          </div>
        </v-checkbox>
      </v-col>
    </v-row>
    <v-btn
      class="mb-4 next-button"
      :disabled="selectedWeeks.length === 0"
      color="primary"
      @click="save"
    >
      Weiter
    </v-btn>
  </v-container>
</template>

<script>
import { mapGetters } from 'vuex'

export default {
  name: 'WorkRecord',
  data() {
    return {
      date: new Date(new Date().getFullYear(), 0, 1),
      weeks: this.$store.getters.recordWeeks,
      hourRecords: this.$store.getters.hourRecords
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
    this.$store.commit('isLoading', true)
    this.axios
      .get('/settings/hourrecords')
      .then(response => {
        this.$store.commit('settings', response.data)
        if (!this.$store.getters.isEditTime) {
          this.$router.push('/kundenportal/erfassen/details')
        }
      })
      .finally(() => {
        this.$store.commit('isLoading', false)
      })
    if (this.weeks.length === 0) {
      let monday = this.getMonday(this.date)
      for (let i = 1; i <= 52; i++) {
        const sunday = new Date(monday)
        sunday.setDate(sunday.getDate() + 6)
        const week = {
          monday,
          sunday,
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
        for (const key in this.hourRecords) {
          this.weeks[key - 1].isSelected = true
        }
        this.$store.commit('isLoading', false)
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

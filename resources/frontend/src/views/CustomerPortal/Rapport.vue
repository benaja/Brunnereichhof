<template>
  <div>
    <h1 class="text-center mt-4 mb-8 display-1">Wochenrapport für Woche {{formatedWeek}}</h1>
    <v-row class="mx-1">
      <v-col cols="12" sm="6">
        <p class="text-left mb-0 text-sm-center">Totale Stunden: {{rapport.hours}}</p>
      </v-col>
      <v-col cols="12" sm="6">
        <p class="text-left text-sm-center">Abgeschlossen: {{rapport.isFinished ? 'Ja' : 'Nein'}}</p>
      </v-col>
    </v-row>
    <div class="grid ma-3" :style="gridLayout">
      <p
        v-if="!$vuetify.breakpoint.smAndDown"
        class="font-weight-bold"
        :style="{gridArea: 'weekdays-label'}"
      >Wochentag</p>
      <day-title grid-area="weekday-0" text="Montag" />
      <day-title grid-area="weekday-1" text="Dienstag" />
      <day-title grid-area="weekday-2" text="Mittwoch" />
      <day-title grid-area="weekday-3" text="Donnerstag" />
      <day-title grid-area="weekday-4" text="Freitag" />
      <day-title grid-area="weekday-5" text="Samstag" />
      <p
        v-if="!$vuetify.breakpoint.smAndDown"
        class="font-weight-bold"
        :style="{gridArea: 'comments-label'}"
      >Kommentar</p>
      <comment grid-area="comment-0" :text="rapport.comment_mo" />
      <comment grid-area="comment-1" :text="rapport.comment_tu" />
      <comment grid-area="comment-2" :text="rapport.comment_we" />
      <comment grid-area="comment-3" :text="rapport.comment_th" />
      <comment grid-area="comment-4" :text="rapport.comment_fr" />
      <comment grid-area="comment-5" :text="rapport.comment_sa" />
      <template v-for="(rapportdetailsByEmployee) of rapport.rapportdetails">
        <p
          v-if="!$vuetify.breakpoint.smAndDown"
          class="font-weight-bold"
          :style="{gridArea: `employee-${rapportdetailsByEmployee[0].employee.id}-label`}"
          :key="`employee-label${rapportdetailsByEmployee[0].employee.id}`"
        >{{rapportdetailsByEmployee[0].employee.lastname}} {{rapportdetailsByEmployee[0].employee.firstname}}</p>
        <template v-for="(rapportdetail, index) of rapportdetailsByEmployee">
          <div
            :key="`employee-${rapportdetailsByEmployee[0].employee.id}-${index}`"
            :style="{gridArea: `employee-${rapportdetailsByEmployee[0].employee.id}-${index}`}"
            class="mb-4"
          >
            <p
              v-if="$vuetify.breakpoint.smAndDown"
              class="font-weight-bold mt-4"
            >{{rapportdetailsByEmployee[0].employee.lastname}} {{rapportdetailsByEmployee[0].employee.firstname}}</p>
            <p class="overline mb-0">Stunden</p>
            <p>{{rapportdetail.hours || 0}}</p>
            <p v-if="settings.rapportFoodTypeEnabled" class="overline mb-0">Verpflegung</p>
            <p
              v-if="settings.rapportFoodTypeEnabled"
            >{{foodTypes.find(f => f.value === rapportdetail.foodtype_id).text}}</p>
            <p class="overline mb-0">Kultur</p>
            <p>{{rapportdetail.project.name}}</p>
          </div>
        </template>
      </template>
      <p
        v-if="!$vuetify.breakpoint.smAndDown"
        class="font-weight-bold"
        :style="{gridArea: 'total-label'}"
      >Totale Stunden</p>
      <p
        v-for="index of 6"
        :key="`total-${index - 1}`"
        class="font-weight-bold mb-8"
        :style="{gridArea: `total-${index - 1}`}"
      >
        <span v-if="$vuetify.breakpoint.smAndDown">Totale Stunden:</span>
        {{getTotalHours(index - 1)}}
      </p>
    </div>
  </div>
</template>

<script>
import Comment from '@/components/CustomerPortal/Rapport/Comment'
import DayTitle from '@/components/CustomerPortal/Rapport/DayTitle'

export default {
  components: {
    Comment,
    DayTitle
  },
  props: {
    id: {
      type: String,
      default: null
    }
  },
  data() {
    return {
      rapport: {
        rapportdetails: []
      },
      foodTypes: [
        {
          value: 1,
          text: 'Eichhof'
        },
        {
          value: 2,
          text: 'Kunde'
        },
        {
          value: 3,
          text: 'Keine Angabe'
        }
      ],
      settings: {}
    }
  },
  computed: {
    formatedWeek() {
      let date = this.$moment(this.rapport.startdate, 'YYYY-MM-DD', 'de-ch')
      return `${date.format('W (DD.MM.YYYY')} - ${date
        .clone()
        .add(6, 'days')
        .format('DD.MM.YYYY')})`
    },
    gridLayout() {
      if (this.$vuetify.breakpoint.smAndDown) {
        let gridTemplateColumns = '100%'
        let gridTemplateRows = ''
        let gridTemplateAreas = ''
        for (let i = 0; i < 6; i++) {
          gridTemplateAreas += `"weekday-${i}" "comment-${i}"`
          gridTemplateRows += 'auto auto'
          for (let rapportdetailsByEmployee of this.rapport.rapportdetails) {
            gridTemplateAreas += ` "employee-${rapportdetailsByEmployee[0].employee.id}-${i}"`
            gridTemplateRows += ' auto'
          }
          gridTemplateAreas += ` "total-${i}"`
          gridTemplateRows += ' auto '
        }
        return {
          gridTemplateColumns,
          gridTemplateRows,
          gridTemplateAreas
        }
      } else {
        let gridTemplateRows = 'auto auto'
        let gridTemplateAreas = `"weekdays-label weekday-0 weekday-1 weekday-2 weekday-3 weekday-4 weekday-5"
        "comments-label comment-0 comment-1 comment-2 comment-3 comment-4 comment-5"`
        for (let rapportdetailsByEmployee of this.rapport.rapportdetails) {
          gridTemplateRows += ' auto'
          gridTemplateAreas += ` "employee-${rapportdetailsByEmployee[0].employee.id}-label`
          for (let i = 0; i < 6; i++) {
            gridTemplateAreas += ` employee-${rapportdetailsByEmployee[0].employee.id}-${i}`
          }
          gridTemplateAreas += '"'
        }
        gridTemplateRows += ' auto'
        gridTemplateAreas += ' "total-label total-0 total-1 total-2 total-3 total-4 total-5"'
        return {
          gridTemplateRows,
          gridTemplateAreas
        }
      }
    }
  },
  mounted() {
    this.axios.get(`/rapport/${this.id}`).then(response => {
      this.rapport = response.data
    })
    this.axios.get('/settings').then(response => {
      this.settings = response.data
    })
  },
  methods: {
    getTotalHours(day) {
      let totalHours = 0
      for (let rapportdetailsByEmployee of this.rapport.rapportdetails) {
        totalHours += rapportdetailsByEmployee[day] ? rapportdetailsByEmployee[day].hours : 0
      }
      return totalHours
    }
  }
}
</script>

<style lang="scss" scoped>
.grid {
  display: grid;
  grid-template-columns: 22% 13% 13% 13% 13% 13% 13%;
  grid-template-rows: auto;
}
</style>

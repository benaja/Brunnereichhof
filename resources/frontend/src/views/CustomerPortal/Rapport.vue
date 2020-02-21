<template>
  <div>
    <h1 class="text-center mt-4 mb-8">Wochenrapport für Woche {{formatedWeek}}</h1>
    <div class="grid ma-3" :style="gridLayout">
      <p
        v-if="!$vuetify.breakpoint.smAndDown"
        class="font-weight-bold"
        :style="{gridArea: 'weekdays-label'}"
      >Wochentag</p>
      <p
        class="font-weight-bold"
        :style="{gridArea: 'weekday-0'}"
        :class="{'title': $vuetify.breakpoint.smAndDown }"
      >Montag</p>
      <p
        class="font-weight-bold"
        :style="{gridArea: 'weekday-1'}"
        :class="{'title': $vuetify.breakpoint.smAndDown }"
      >Dienstag</p>
      <p
        class="font-weight-bold"
        :style="{gridArea: 'weekday-2'}"
        :class="{'title': $vuetify.breakpoint.smAndDown }"
      >Mittwoch</p>
      <p
        class="font-weight-bold"
        :style="{gridArea: 'weekday-3'}"
        :class="{'title': $vuetify.breakpoint.smAndDown }"
      >Donnerstag</p>
      <p
        class="font-weight-bold"
        :style="{gridArea: 'weekday-4'}"
        :class="{'title': $vuetify.breakpoint.smAndDown }"
      >Freitag</p>
      <p
        class="font-weight-bold"
        :style="{gridArea: 'weekday-5'}"
        :class="{'title': $vuetify.breakpoint.smAndDown }"
      >Samstag</p>
      <p
        v-if="!$vuetify.breakpoint.smAndDown"
        class="font-weight-bold"
        :style="{gridArea: 'comments-label'}"
      >Kommentar</p>
      <div :style="{gridArea: 'comment-0'}">
        <p v-if="$vuetify.breakpoint.smAndDown" class="overline mb-0">Kommentar</p>
        <p class="mb-8">{{rapport.comment_mo}}</p>
      </div>
      <p :style="{gridArea: 'comment-1'}" class="mb-8">{{rapport.comment_tu}}</p>
      <p :style="{gridArea: 'comment-2'}" class="mb-8">{{rapport.comment_we}}</p>
      <p :style="{gridArea: 'comment-3'}" class="mb-8">{{rapport.comment_th}}</p>
      <p :style="{gridArea: 'comment-4'}" class="mb-8">{{rapport.comment_fr}}</p>
      <p :style="{gridArea: 'comment-5'}" class="mb-8">{{rapport.comment_sa}}</p>
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
          >
            <p
              v-if="$vuetify.breakpoint.smAndDown"
              class="font-weight-bold mt-4"
            >{{rapportdetailsByEmployee[0].employee.lastname}} {{rapportdetailsByEmployee[0].employee.firstname}}</p>
            <p class="overline mb-0">Stunden</p>
            <p>{{rapportdetail.hours || 0}}</p>
            <p class="overline mb-0">Verpflegung</p>
            <p>{{foodTypes.find(f => f.value === rapportdetail.foodtype_id).text}}</p>
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
        <span v-if="$vuetify.breakpoint.smAndDown">Totale Stunden</span>
        {{getTotalHours(index - 1)}}
      </p>
    </div>
  </div>
</template>

<script>
export default {
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
      ]
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

        console.log({
          gridTemplateColumns,
          gridTemplateRows,
          gridTemplateAreas
        })
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
          gridTemplateRows += ' 200px'
          gridTemplateAreas += ` "employee-${rapportdetailsByEmployee[0].employee.id}-label`
          for (let i = 0; i < 6; i++) {
            gridTemplateAreas += ` employee-${rapportdetailsByEmployee[0].employee.id}-${i}`
          }
          gridTemplateAreas += '"'
        }
        gridTemplateRows += ' auto'
        gridTemplateAreas += ' "total-label total-0 total-1 total-2 total-3 total-4 total-5"'
        console.log({
          gridTemplateRows,
          gridTemplateAreas
        })
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

<template>
  <div class="week">
    <v-row class="mt-4">
      <v-col
        cols="12"
        md="3"
        xl="2"
        class="py-0"
      >
        <p class="text-center text-md-left">
          <span class="font-weight-bold">KW {{ week[0].week }}</span>
          ({{ getMondayOfWeek(week[0].week, week[0].year).format('DD.MM.YYYY') }} -
          {{ getSundayOfWeek(week[0].week, week[0].year).format('DD.MM.YYYY') }})
        </p>
      </v-col>
      <v-col
        cols="12"
        md="9"
        xl="12"
        class="py-0"
      >
        <hourrecord-element
          v-for="(culture, index) of week"
          :key="index"
          v-model="week[index]"
          :edit-mode="edit"
          :cultures="cultures"
          :admin-mode="adminMode"
          @remove="removeHourrecord(culture)"
        ></hourrecord-element>
      </v-col>
    </v-row>

    <p
      v-if="edit"
      class="text-right mb-0"
    >
      <v-btn
        :loading="isAddCultureLoading"
        outlined
        color="primary"
        @click="addCulture(week)"
      >
        <v-icon>add</v-icon>Kultur/Arbeit hinzufügen
      </v-btn>
    </p>
  </div>
</template>

<script>
import HourrecordElement from '@/components/Hourrecords/HourrecordElement'
import moment from 'moment'

export default {
  name: 'Week',
  components: {
    HourrecordElement
  },
  props: {
    week: {
      type: Array,
      default: null
    },
    cultures: {
      type: Array,
      default: null
    },
    customer: {
      type: Object,
      default: null
    },
    year: {
      type: String,
      default: moment().format('YYYY')
    },
    adminMode: {
      type: Boolean,
      default: false
    },
    edit: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      isAddCultureLoading: false
    }
  },
  methods: {
    getMondayOfWeek(week, year) {
      return this.$moment()
        .year(year)
        .week(week)
        .startOf('week')
    },
    getSundayOfWeek(week, year) {
      return this.$moment()
        .year(year)
        .week(week)
        .endOf('week')
    },
    addCulture(week) {
      this.isAddCultureLoading = true
      this.axios
        .post(`hourrecords/${this.year}/${week[0].week}`, {
          customerId: this.customer ? this.customer.id : null
        })
        .then(response => {
          week.push(response.data)
        })
        .catch(() => {
          this.$swal('Fehler', 'Es konnte aus einem unbekannten Grund keine Kultur hinzugefügt werden. Bitte versuchen Sie es später erneut.')
        })
        .finally(() => {
          this.isAddCultureLoading = false
        })
    },
    removeHourrecord(hourrecord) {
      this.week.splice(this.week.indexOf(hourrecord), 1)
      if (!this.week.length) {
        this.$emit('remove')
      }
    }
  }
}
</script>

<style lang="scss" scoped>
.week {
  background-color: white;
  border-radius: 10px;
  box-shadow: 0 0 20px rgb(226, 226, 226);
  padding: 20px;
  margin: 20px 0;
}
</style>

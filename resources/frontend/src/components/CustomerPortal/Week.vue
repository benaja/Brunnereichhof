<template>
  <div>
    <v-row v-if="!culture.isLoading" v-for="(culture, index) of week" :key="index" class="mt-4">
      <v-col cols="12" md="3" xl="2" class="px-1 py-0">
        <p class="mt-2 text-center text-md-left" v-if="index === 0">
          <span class="font-weight-bold">KW {{culture.week}}</span>
          ({{getMondayOfWeek(culture.week, culture.year).toLocaleDateString()}} - {{getSundayOfWeek(culture.week, culture.year).toLocaleDateString()}})
        </p>
      </v-col>
      <v-col cols="12" md="2" lg="1" class="px-1 py-0">
        <v-text-field
          label="Stunden*"
          class="pa-0 ma-0"
          type="number"
          v-model="culture.hours"
          @change="update(culture)"
          :disabled="!$store.getters.isEditTime"
        ></v-text-field>
      </v-col>
      <v-col cols="12" :md="$store.getters.isEditTime || adminMode ? 3 : 4" class="px-1 py-0">
        <v-combobox
          :items="cultures"
          v-model="culture.culture"
          class="pa-0 ma-0"
          item-text="name"
          item-value="id"
          label="Kultur/Arbeit*"
          @input="update(culture)"
          autocomplete="off"
          :disabled="!$store.getters.isEditTime"
        ></v-combobox>
      </v-col>
      <v-col cols="12" md="3" lg="4" xl="5" class="px-1 py-0">
        <v-textarea
          label="Bemerkung"
          class="pa-0 ma-0"
          auto-grow
          rows="1"
          v-model="culture.comment"
          @change="update(culture)"
          :disabled="!$store.getters.isEditTime"
        ></v-textarea>
      </v-col>
      <v-col cols="12" md="1" v-if="$store.getters.isEditTime || adminMode" class="pa-0">
        <v-btn text icon color="red" class="my-0" @click="deleteCulture(week, culture)">
          <v-icon>delete</v-icon>
        </v-btn>
      </v-col>
    </v-row>
    <div v-else>
      <spinner></spinner>
    </div>
    <p class="text-center mb-4" v-if="$store.getters.isEditTime">
      <v-btn text color="primary" @click="addCulture(week)">Kultur/Arbeit hinzufügen</v-btn>
    </p>
    <v-divider></v-divider>
  </div>
</template>

<script>
import Spinner from '@/components/general/Spinner'
import moment from 'moment'

export default {
  name: 'Week',
  components: {
    Spinner
  },
  props: {
    week: Array,
    cultures: Array,
    customer: Object,
    year: {
      type: String,
      default: moment().format('YYYY')
    },
    adminMode: {
      type: Boolean,
      default: false
    }
  },
  methods: {
    getMondayOfWeek(week, year) {
      let day = (week - 1) * 7

      return new Date(year, 0, day)
    },
    getSundayOfWeek(week, year) {
      let monday = this.getMondayOfWeek(week, year)
      monday.setDate(monday.getDate() + 6)
      return monday
    },
    addCulture(week) {
      let isLoading = {
        isLoading: true
      }
      week.push(isLoading)
      this.axios
        .post(`hourrecord/${this.year}/${week[0].week}`, {
          customerId: this.customer ? this.customer.id : null
        })
        .then(response => {
          week.splice(week.indexOf(isLoading), 1)
          week.push(response.data)
        })
        .catch(() => {
          this.$swal('Fehler', 'Es konnte aus einem unbekannten Grund keine Kultur hinzugefügt werden. Bitte versuchen Sie es später erneut.')
        })
    },
    deleteCulture(week, culture) {
      week.splice(week.indexOf(culture), 1)
      week = [...week]
      this.axios
        .delete(process.env.VUE_APP_API_URL + 'hourrecord/' + culture.id)
        .then(() => {})
        .catch(() => {
          this.$swal('Fehler', 'Kultur konnte nicht gelöscht werden. Bitter versuchen Sie es später erneut.', 'error')
        })
    },
    update(culture) {
      this.axios
        .put(process.env.VUE_APP_API_URL + 'hourrecord/' + culture.id, culture)
        .then(response => {
          culture = response.data
        })
        .catch(() => {
          this.$swal('Fehler', 'Ein unbekannter Fehler ist aufgetreten. Versuchen Sie es bitte später erneut.', 'error')
        })
    }
  }
}
</script>

<style lang="scss" scoped>
</style>

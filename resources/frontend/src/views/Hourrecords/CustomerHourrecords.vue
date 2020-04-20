<template>
  <v-container>
    <v-row>
      <v-flex>
        <h1>Stundenangaben {{ customer.lastname }} {{ customer.firstname }} {{ $route.query.year }}</h1>
      </v-flex>
      <v-flex shrink align-self="end" class="py-5 d-flex">
        <date-picker :value="year.toString()" type="year" dense outlined @input="updateYear"></date-picker>
        <div v-if="$auth.user().hasPermission(['superadmin'], ['hourrecord_write'])" class="ml-2">
          <v-btn v-if="edit" color="primary" outlined @click="addHourrecordDialog = true"> <v-icon class="mr-2">add</v-icon>Hinzufügen </v-btn>
          <v-btn v-else color="primary" depressed @click="edit = !edit"> <v-icon class="mr-2">edit</v-icon>Bearbeiten </v-btn>
        </div>
      </v-flex>
    </v-row>
    <week
      v-for="(week, index) of hourrecords"
      :week="week"
      :cultures="cultures"
      :key="index"
      :customer="customer"
      :year="$route.query.year"
      admin-mode
      @input="w => (week = w)"
    ></week>
    <v-row>
      <v-flex align-self="end">
        <v-btn v-if="edit" color="primary" class="float-right" depressed @click="edit = !edit"><v-icon class="mr-2">check</v-icon>Fertig</v-btn>
      </v-flex>
    </v-row>
    <v-row> </v-row>
    <add-hourrecord
      v-model="addHourrecordDialog"
      :cultures="cultures"
      :customer="customer"
      :year="$route.query.year"
      @add="addHourrecord"
    ></add-hourrecord>
  </v-container>
</template>

<script>
import Week from '@/components/CustomerPortal/Week'
import AddHourrecord from '@/components/Hourrecords/AddHourrecord'
import DatePicker from '@/components/general/DatePicker'

export default {
  components: {
    Week,
    AddHourrecord,
    DatePicker
  },
  props: {
    id: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      customer: {},
      hourrecords: [],
      cultures: [],
      addHourrecordDialog: false,
      edit: false,
      year: this.$moment().format('YYYY')
    }
  },
  mounted() {
    this.$store.commit('isLoading', true)
    Promise.all([
      this.axios.get(`/customer/${this.id}`).then(response => {
        this.customer = response.data
      }),
      this.getHourrecords(),
      this.axios.get('/culture').then(response => {
        this.cultures = response.data
      })
    ])
      .catch(() => {
        this.$swal('Fehler', 'Es ist ein unbekannter Feheler aufgetreten', 'error')
      })
      .finally(() => {
        this.$store.commit('isLoading', false)
      })
  },
  methods: {
    addHourrecord(hourrecord) {
      if (this.hourrecords[hourrecord.week]) {
        this.hourrecords[hourrecord.week].push(hourrecord)
      } else if (!this.hourrecords.lenght) {
        this.hourrecords = {
          [hourrecord.week]: [hourrecord]
        }
      } else {
        this.$set(this.hourrecords, hourrecord.week, [hourrecord])
      }
    },
    toggleEdit() {
      this.$router.replace({ query: { ...this.$route.query, edit: this.edit ? 0 : 1 } })
    },
    getHourrecords() {
      return this.axios.get(`/customer/${this.id}/hourrecords?year=${this.year}`).then(response => {
        this.hourrecords = response.data
      })
    },
    updateYear(value) {
      this.year = value
      this.getHourrecords()
    }
  },
  url: {
    edit: {
      param: 'edit',
      noHistory: true
    },
    year: {
      param: 'year',
      noHistory: true
    }
  }
}
</script>

<style lang="scss" scoped>
.edit-button {
  position: fixed;
  bottom: 20px;
  right: 20px;
}
</style>

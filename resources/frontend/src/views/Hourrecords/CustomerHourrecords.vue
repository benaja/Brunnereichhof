<template>
  <v-container>
    <h1>Stundenangaben {{customer.lastname}} {{customer.firstname}} {{$route.query.year}}</h1>
    <week
      v-for="(week, index) of hourrecords"
      :week="week"
      :cultures="cultures"
      :key="index"
      :customer="customer"
      :year="$route.query.year"
      admin-mode
      @input="w => week = w"
    ></week>
    <add-hourrecord
      v-model="addHourrecordDialog"
      :cultures="cultures"
      :customer="customer"
      :year="$route.query.year"
      @add="addHourrecord"
    ></add-hourrecord>
    <v-btn
      fab
      color="primary"
      class="edit-button"
      @click="addHourrecordDialog = true"
      v-if="$auth.user().hasPermission(['superadmin'], ['hourrecord_write'])"
    >
      <v-icon>add</v-icon>
    </v-btn>
  </v-container>
</template>

<script>
import Week from '@/components/CustomerPortal/Week'
import AddHourrecord from '@/components/Hourrecords/AddHourrecord'

export default {
  components: {
    Week,
    AddHourrecord
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
      addHourrecordDialog: false
    }
  },
  mounted() {
    this.$store.commit('isLoading', true)
    Promise.all([
      this.axios.get(`/customer/${this.id}`).then(response => {
        this.customer = response.data
      }),
      this.axios.get(`/customer/${this.id}/hourrecords?year=${this.$route.query.year}`).then(response => {
        this.hourrecords = response.data
      }),
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

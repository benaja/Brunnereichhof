<template>
  <v-container>
    <h1 class="text-center mb-4">
      KW {{$route.params.week}}
      ({{monday.toLocaleDateString()}} -
      {{sunday.toLocaleDateString()}})
      / {{totalHours}} Stunden
    </h1>
    <div v-if="customers.length > 0">
      <v-row
        v-for="(customer, index) of customers.filter(c => !c.isDeleted && c.hourrecords.length > 0)"
        :key="index"
        class="mt-3"
      >
        <v-col cols="12" sm="4" md="3" v-if="customer.hourrecords.length > 0">
          <h3 class="mb-3">{{customer.lastname}} {{customer.firstname}}</h3>
        </v-col>
        <v-col cols="12" sm="8" md="9">
          <hourrecord-element
            v-for="hourrecord of customer.hourrecords"
            :key="hourrecord.id"
            :value="hourrecord"
            :editMode="editMode"
            :cultures="cultures"
            @input="h => hourrecord = h"
            @remove="removeHourrecord(customer, hourrecord)"
          ></hourrecord-element>
        </v-col>
        <v-col cols="12">
          <v-divider></v-divider>
        </v-col>
      </v-row>
      <p class="text-center" v-if="editMode">
        <v-btn text color="primary" @click="addHourrecord = true">Kultur/Kunde hinzufügen</v-btn>
      </p>
    </div>
    <v-btn
      fab
      color="primary"
      class="edit-button"
      @click="editMode = !editMode"
      v-if="$auth.user().hasPermission(['superadmin'], ['hourrecord_write'])"
    >
      <v-icon v-if="editMode">check</v-icon>
      <v-icon v-else>edit</v-icon>
    </v-btn>
    <add-hourrecrod v-model="addHourrecord" :cultures="cultures" @add="h => applyHourrecord(h)"></add-hourrecrod>
  </v-container>
</template>

<script>
import HourrecordElement from '@/components/Hourrecords/HourrecordElement'
import AddHourrecrod from '@/components/Hourrecords/AddHourrecord'

export default {
  name: 'HourrecordDetails',
  components: {
    HourrecordElement,
    AddHourrecrod
  },
  props: ['edit'],
  data() {
    return {
      customers: [],
      editMode: false,
      cultures: [],
      addHourrecord: false
    }
  },
  mounted() {
    this.editMode = this.edit
    this.addHourrecord = this.editMode
    this.$store.commit('isLoading', true)
    this.axios.get(`hourrecord/${this.$route.params.year}/${this.$route.params.week}`).then(response => {
      this.customers = response.data
      this.$store.commit('isLoading', false)
    })
  },
  methods: {
    removeHourrecord(customer, hourrecord) {
      if (customer.hourrecords.length === 1) {
        this.customers.splice(this.customers.indexOf(customer), 1)
      } else {
        customer.hourrecords.splice(customer.hourrecords.indexOf(hourrecord), 1)
      }
    },
    applyHourrecord(hourrecord) {
      let customer = this.customers.find(c => c.id === hourrecord.customer_id)
      if (customer) {
        customer.hourrecords.push(hourrecord)
      } else {
        hourrecord.customer.hourrecords = [hourrecord]
        this.customers.push(hourrecord.customer)
      }
    }
  },
  computed: {
    monday() {
      let day = (this.$route.params.week - 1) * 7
      return new Date(this.$route.params.year, 0, day)
    },
    sunday() {
      let monday = new Date(this.monday)
      monday.setDate(monday.getDate() + 6)
      return monday
    },
    totalHours() {
      let hours = 0
      for (let customer of this.customers) {
        for (let hourrecord of customer.hourrecords) {
          hours += Number(hourrecord.hours)
        }
      }
      return hours
    }
  },
  watch: {
    editMode() {
      if (this.editMode && this.cultures.length === 0) {
        this.$store.dispatch('cultures').then(cultures => {
          this.cultures = cultures
        })
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

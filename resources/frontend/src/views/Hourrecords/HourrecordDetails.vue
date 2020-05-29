<template>
  <fragment>
    <navigation-bar
      :title="documentTitle"
      :loading="isLoading"
    ></navigation-bar>
    <v-container>
      <div class="d-flex justify-sm-end flex-wrap justify-start">
        <v-btn
          depressed
          color="primary"
          class="mt-2 mr-2"
          :loading="loadingPdf"
          outlined
          @click="generatePdf"
        >
          <v-icon class="mr-2">
            picture_as_pdf
          </v-icon>Pdf generieren
        </v-btn>
        <template v-if="$auth.user().hasPermission(['superadmin'], ['hourrecord_write'])">
          <v-btn
            v-if="editMode"
            outlined
            color="primary"
            class="mt-2 mr-2"
            @click="addHourrecord = true"
          >
            <v-icon class="mr-2">
              add
            </v-icon>Kultur/Kunde hinzufügen
          </v-btn>
          <v-btn
            v-else
            depressed
            color="primary"
            class="mt-2"
            @click="editMode = true"
          >
            <v-icon class="mr-2">
              edit
            </v-icon>Bearbeiten
          </v-btn>
          <v-btn
            v-if="editMode"
            depressed
            color="primary"
            class="mt-2"
            @click="editMode = false"
          >
            <v-icon class="mr-2">
              check
            </v-icon>Fertig
          </v-btn>
        </template>
      </div>
      <div v-if="customers.length > 0">
        <v-row
          v-for="(customer, index) of customersFiltered"
          :key="index"
          class="mt-3"
        >
          <v-col
            v-if="customer.hourrecords.length > 0"
            cols="12"
            sm="4"
            md="3"
          >
            <h3 class="mb-3">
              {{ customer.lastname }} {{ customer.firstname }}
            </h3>
          </v-col>
          <v-col
            cols="12"
            sm="8"
            md="9"
          >
            <hourrecord-element
              v-for="hourrecord of customer.hourrecords"
              :key="hourrecord.id"
              :value="hourrecord"
              :edit-mode="editMode"
              :cultures="cultures"
              admin-mode
              @input="h => hourrecord = h"
              @remove="removeHourrecord(customer, hourrecord)"
            ></hourrecord-element>
          </v-col>
          <v-col cols="12">
            <v-divider></v-divider>
          </v-col>
        </v-row>
      </div>
      <add-hourrecord
        v-model="addHourrecord"
        :cultures="cultures"
        :year="$route.params.year"
        :week="$route.params.week"
        @add="h => applyHourrecord(h)"
      ></add-hourrecord>
    </v-container>
  </fragment>
</template>

<script>
import HourrecordElement from '@/components/Hourrecords/HourrecordElement'
import AddHourrecord from '@/components/Hourrecords/AddHourrecord'
import { downloadFile } from '@/utils'
import { mapGetters } from 'vuex'

export default {
  components: {
    HourrecordElement,
    AddHourrecord
  },
  props: {
    edit: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      customers: [],
      editMode: false,
      addHourrecord: false,
      isLoading: false,
      loadingPdf: false
    }
  },
  computed: {
    ...mapGetters(['cultures']),
    documentTitle() {
      return `KW ${this.$route.params.week}
        (${this.monday.format('DD.MM.YYYY')} -
        ${this.sunday.format('DD.MM.YYYY')})
        / ${this.totalHours} Stunden`
    },
    monday() {
      return this.$moment(this.$route.params.year, 'YYYY')
        .week(this.$route.params.week)
        .startOf('week')
    },
    sunday() {
      return this.$moment(this.$route.params.year, 'YYYY')
        .week(this.$route.params.week)
        .endOf('week')
    },
    totalHours() {
      let hours = 0
      for (const customer of this.customers) {
        for (const hourrecord of customer.hourrecords) {
          hours += Number(hourrecord.hours)
        }
      }
      return hours
    },
    customersFiltered() {
      return this.customers
        .filter(c => !c.isDeleted && c.hourrecords.length > 0)
        .sort((a, b) => {
          if (a.lastname.toLowerCase() < b.lastname.toLowerCase()) {
            return -1
          }
          if (a.lastname.toLowerCase() > b.lastname.toLowerCase()) {
            return 1
          }
          return 0
        })
    }
  },
  watch: {
    editMode() {
      if (this.editMode && this.cultures.length === 0) {
        this.$store.dispatch('fetchCultures')
      }
    }
  },
  mounted() {
    this.editMode = this.edit
    this.addHourrecord = this.editMode
    this.isLoading = true
    this.axios.get(`hourrecords/${this.$route.params.year}/${this.$route.params.week}`).then(response => {
      this.customers = response.data
    }).finally(() => {
      this.isLoading = false
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
      const customer = this.customers.find(c => c.id === hourrecord.customer_id)
      if (customer) {
        customer.hourrecords.push(hourrecord)
      } else {
        hourrecord.customer.hourrecords = [hourrecord]
        this.customers.push(hourrecord.customer)
      }
    },
    generatePdf() {
      this.loadingPdf = true
      downloadFile(`/pdf/hourrecords?date=${this.monday.format('YYYY-MM-DD')}`).catch(() => {
        this.$store.dispatch('error', 'PDF konnte nicht erstellt werden')
      }).finally(() => {
        this.loadingPdf = false
      })
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

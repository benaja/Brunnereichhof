<template>
  <fragment>
    <navigation-bar
      :title="`Stundenangaben ${ customer.lastname } ${ customer.firstname } ${ yearNumber }`"
      :loading="isLoading"
    />
    <v-container>
      <div class="d-flex justify-end flex-wrap">
        <div class="date-picker-container">
          <date-picker
            :value="year.toString()"
            :full-width="$vuetify.breakpoint.xsOnly"
            type="year"
            dense
            outlined
            @input="updateYear"
          ></date-picker>
        </div>
        <template v-if="edit">
          <v-btn
            :width="$vuetify.breakpoint.xsOnly ? '50%' : ''"
            color="primary"
            outlined
            @click="addHourrecordDialog = true"
          >
            <v-icon class="mr-2">
              add
            </v-icon>Hinzufügen
          </v-btn>
          <v-btn
            :width="$vuetify.breakpoint.xsOnly ? 'calc(50% - 12px)' : ''"
            color="primary"
            class="ml-2"
            depressed
            @click="edit = !edit"
          >
            <v-icon class="mr-2">
              check
            </v-icon>Fertig
          </v-btn>
        </template>
        <v-btn
          v-else-if="$auth.user().hasPermission(['superadmin'], ['hourrecord_write'])"
          color="primary"
          depressed
          @click="edit = !edit"
        >
          <v-icon class="mr-2">
            edit
          </v-icon>Bearbeiten
        </v-btn>
      </div>
      <week
        v-for="(week, index) of hourrecords"
        :key="index"
        :week="week"
        :cultures="cultures"
        :customer="customer"
        :year="$route.query.year"
        :edit="edit"
        admin-mode
        @input="w => (week = w)"
        @remove="removeWeek(index)"
      ></week>
      <p v-if="noEntries">
        Keine Einträge zur ausgewählten Zeit vorhanden
      </p>
      <add-hourrecord
        v-model="addHourrecordDialog"
        :cultures="cultures"
        :customer="customer"
        :year="$route.query.year"
        @add="addHourrecord"
      ></add-hourrecord>
    </v-container>
  </fragment>
</template>

<script>
import Week from '@/components/CustomerPortal/Week'
import AddHourrecord from '@/components/Hourrecords/AddHourrecord'
import DatePicker from '@/components/general/DatePicker'
import { mapGetters } from 'vuex'

export default {
  components: {
    Week,
    AddHourrecord,
    DatePicker
  },
  props: {
    id: {
      type: [String, Number],
      required: true
    }
  },
  data() {
    return {
      customer: {},
      hourrecords: [],
      addHourrecordDialog: false,
      edit: false,
      year: this.$moment().format('YYYY'),
      isLoading: false
    }
  },
  computed: {
    ...mapGetters(['cultures']),
    yearNumber() {
      return typeof this.year === 'number' ? this.year : this.$moment(this.year).format('YYYY')
    },
    noEntries() {
      return !Object.keys(this.hourrecords).length && !this.isLoading
    }
  },
  mounted() {
    this.isLoading = true
    Promise.all([
      this.axios.get(`/customers/${this.id}`).then(response => {
        this.customer = response.data
      }),
      this.getHourrecords(),
      this.$store.dispatch('fetchCultures')
    ])
      .catch(() => {
        this.$swal('Fehler', 'Es ist ein unbekannter Feheler aufgetreten', 'error')
      })
      .finally(() => {
        this.isLoading = false
      })
  },
  methods: {
    addHourrecord(hourrecord) {
      if (this.hourrecords[hourrecord.week]) {
        this.hourrecords[hourrecord.week].push(hourrecord)
      } else if (!Object.keys(this.hourrecords).length) {
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
      return this.axios.get(`/customers/${this.id}/hourrecords?year=${this.year}`).then(response => {
        this.hourrecords = response.data
      })
    },
    updateYear(value) {
      this.year = value
      this.isLoading = true
      this.getHourrecords().finally(() => {
        this.isLoading = false
      })
    },
    removeWeek(weekNumber) {
      delete this.hourrecords[weekNumber]
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
    },
    addHourrecordDialog: {
      param: 'hourrecordDialog',
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

.date-picker-container {
  padding-right: 8px;
}

@media (max-width: 600px) {
  .date-picker-container {
    width: 100%;
    padding-right: 0;
  }
}
</style>

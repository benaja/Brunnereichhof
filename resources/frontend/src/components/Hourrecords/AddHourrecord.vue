<template>
  <v-row justify="center">
    <v-dialog
      :value="value"
      width="1000px"
      max-width="100%"
      @input="v => $emit('input', v)"
    >
      <v-card
        width="1000px"
        max-width="100%"
      >
        <v-card-title>
          <h3>{{ title }}</h3>
        </v-card-title>
        <v-divider></v-divider>
        <v-card-text>
          <v-form ref="form">
            <v-autocomplete
              v-if="!customer"
              v-model="hourrecord.customerId"
              :items="customers"
              label="Kunde suchen"
              append-outer-icon="search"
              item-value="id"
              item-text="name"
            ></v-autocomplete>
            <date-picker
              v-if="!week"
              v-model="hourrecord.date"
              :min="$moment().year(year).startOf('year').format('YYYY-MM-DD')"
              :max="$moment().year(year).endOf('year').format('YYYY-MM-DD')"
              label="Datum"
            ></date-picker>
            <v-row>
              <v-col
                cols="12"
                md="2"
              >
                <v-text-field
                  v-model="hourrecord.hours"
                  label="Stunden"
                  type="number"
                  class="pa-1 ma-0"
                ></v-text-field>
              </v-col>
              <v-col
                cols="12"
                md="4"
              >
                <v-combobox
                  v-model="hourrecord.culture"
                  :items="cultures"
                  class="pa-1 ma-0"
                  item-text="name"
                  item-value="id"
                  label="Kultur/Arbeit"
                  autocomplete="off"
                />
              </v-col>
              <v-col
                cols="12"
                md="6"
              >
                <v-textarea
                  v-model="hourrecord.comment"
                  label="Kommentar"
                  class="pa-1 ma-0"
                  rows="1"
                  auto-grow
                />
              </v-col>
            </v-row>
            <v-row
              justify="center"
              class="mt-4"
            >
              <v-btn
                color="primary"
                depressed
                :loading="isSaving"
                :disabled="!allValid()"
                @click="save"
              >
                Speichern
              </v-btn>
            </v-row>
          </v-form>
        </v-card-text>
      </v-card>
    </v-dialog>
  </v-row>
</template>

<script>
import DatePicker from '@/components/general/DatePicker'
import { mapGetters } from 'vuex'

export default {
  components: {
    DatePicker
  },
  props: {
    value: Boolean,
    customer: {
      type: Object,
      default: null
    },
    year: {
      type: [String, Number],
      required: true
    },
    week: {
      type: String,
      default: null
    },
    title: {
      type: String,
      default: 'Kultur hinzufügen'
    }
  },
  data() {
    return {
      hourrecord: {},
      isSaving: false
    }
  },
  computed: {
    ...mapGetters(['customers', 'cultures'])
  },
  watch: {
    value() {
      this.getData()
    }
  },
  mounted() {
    this.getData()
  },
  methods: {
    save() {
      if (this.customer) {
        this.hourrecord.customerId = this.customer.id
      }
      let { week } = this
      if (!this.week) {
        week = this.$moment(this.hourrecord.date).week()
      }
      this.isSaving = true
      this.axios
        .post(`hourrecords/${this.year}/${week}`, this.hourrecord)
        .then(response => {
          this.$emit('add', response.data)
          this.$emit('input', false)
          this.$refs.form.reset()
        })
        .catch(() => {
          this.$swal('Fehler', 'Kultur konnte nicht erfasst werden', 'error')
        }).finally(() => {
          this.isSaving = false
        })
    },
    allValid() {
      if (!this.hourrecord.hours) return false
      if (!this.hourrecord.culture) return false
      if (!this.hourrecord.customerId && !this.customer) return false
      if (!this.hourrecord.date && !this.week) return false
      return true
    },
    getData() {
      if (this.value && !this.cultures.length) {
        this.$store.dispatch('fetchCultures')
      }
      if (this.value && !this.customers.length && !this.customer) {
        this.$store.dispatch('fetchCustomers')
      }
    }
  }
}
</script>

<template>
  <v-row justify="center">
    <v-dialog :value="value" @input="v => $emit('input', v)" width="1000px" max-width="100%">
      <v-card width="1000px" max-width="100%">
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
            <date-picker v-if="!week" v-model="hourrecord.date" label="Datum"></date-picker>
            <v-row>
              <v-col cols="12" md="2">
                <v-text-field
                  label="Stunden"
                  type="number"
                  v-model="hourrecord.hours"
                  class="pa-1 ma-0"
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="4">
                <v-combobox
                  :items="cultures"
                  v-model="hourrecord.culture"
                  class="pa-1 ma-0"
                  item-text="name"
                  item-value="id"
                  label="Kultur/Arbeit"
                  autocomplete="off"
                />
              </v-col>
              <v-col cols="12" md="6">
                <v-textarea
                  v-model="hourrecord.comment"
                  label="Kommentar"
                  class="pa-1 ma-0"
                  rows="1"
                  auto-grow
                />
              </v-col>
            </v-row>
            <v-row justify="center" class="mt-4">
              <v-btn color="primary" depressed @click="save" :disabled="!allValid()">Speichern</v-btn>
            </v-row>
          </v-form>
        </v-card-text>
      </v-card>
    </v-dialog>
  </v-row>
</template>

<script>
import DatePicker from '@/components/general/DatePicker'

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
      type: String,
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
      customers: [],
      cultures: [],
      hourrecord: {}
    }
  },
  methods: {
    save() {
      if (this.customer) {
        this.hourrecord.customerId = this.customer.id
      }
      let week = this.week
      if (!this.week) {
        week = this.$moment(this.hourrecord.date).week()
      }
      this.axios
        .post(`hourrecord/${this.year}/${week}`, this.hourrecord)
        .then(response => {
          this.$emit('add', response.data)
          this.$emit('input', false)
          this.$refs.form.reset()
        })
        .catch(() => {
          this.$swal('Fehler', 'Kultur konnte nicht erfasst werden', 'error')
        })
    },
    allValid() {
      if (!this.hourrecord.hours) return false
      if (!this.hourrecord.culture) return false
      if (!this.hourrecord.customerId && !this.customer) return false
      if (!this.hourrecord.date && !this.week) return false
      return true
    }
  },
  watch: {
    value() {
      if (this.value && !this.cultures.length) {
        this.$store.dispatch('cultures').then(cultures => {
          this.cultures = cultures
        })
      }
      if (this.value && !this.customers.length && !this.customer) {
        this.$store.dispatch('customers').then(customers => {
          this.customers = customers
        })
      }
    }
  }
}
</script>

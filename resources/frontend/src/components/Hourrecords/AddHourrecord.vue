<template>
  <v-row justify="center">
    <v-dialog :value="value" @input="v => $emit('input', v)" width="1000px" max-width="100%">
      <v-card width="1000px" max-width="100%">
        <v-card-title>
          <h3>Kultur Hinzufügen</h3>
        </v-card-title>
        <v-divider></v-divider>
        <v-card-text>
          <v-form ref="form">
            <v-autocomplete
              label="Kunde suchen"
              append-outer-icon="search"
              v-model="hourrecord.customer"
              :items="customers"
              item-value="id"
              item-text="name"
            ></v-autocomplete>
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
              <v-btn color="primary" @click="save" :disabled="!allValid()">Speichern</v-btn>
            </v-row>
          </v-form>
        </v-card-text>
      </v-card>
    </v-dialog>
  </v-row>
</template>

<script>
export default {
  name: 'EditEmployees',
  components: {},
  props: {
    value: Boolean
  },
  data() {
    return {
      customers: [],
      cultures: [],
      hourrecord: {
        customer: null
      }
    }
  },
  methods: {
    save() {
      this.axios
        .post('hourrecord/week/' + this.$route.params.week, this.hourrecord)
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
      if (!this.hourrecord.customer) return false
      return true
    }
  },
  watch: {
    value() {
      if (this.value && this.customers.length === 0) {
        this.$store.dispatch('customers').then(customers => {
          this.customers = customers
        })
        this.$store.dispatch('cultures').then(cultures => {
          this.cultures = cultures
        })
      }
    }
  }
}
</script>

<style lang="scss" scoped>
</style>

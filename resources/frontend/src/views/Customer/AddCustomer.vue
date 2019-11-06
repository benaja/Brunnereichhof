<template>
  <div>
    <h1 class="text-center my-4">Kunde erstellen</h1>
    <v-form>
      <v-container>
        <v-row>
          <v-col cols="12" md="6">
            <v-text-field label="Vorname*" v-model="customer.firstname" :rules="nameRules"></v-text-field>
          </v-col>
          <v-col cols="12" md="6">
            <v-text-field label="Nachname*" v-model="customer.lastname" :rules="nameRules"></v-text-field>
          </v-col>
          <v-col cols="12" md="6">
            <v-text-field label="Strasse + Nr*" v-model="customer.street" :rules="nameRules"></v-text-field>
          </v-col>
          <v-col cols="12" md="6">
            <v-text-field label="Zusatz" v-model="customer.addition"></v-text-field>
          </v-col>
          <v-col cols="12" md="6">
            <v-text-field label="Ort*" v-model="customer.place" :rules="nameRules"></v-text-field>
          </v-col>
          <v-col cols="12" md="6">
            <v-text-field type="number" label="PLZ*" v-model="customer.plz" :rules="nameRules"></v-text-field>
          </v-col>
          <v-col cols="12" md="6">
            <v-text-field label="Mobile" v-model="customer.mobile"></v-text-field>
          </v-col>
          <v-col cols="12" md="6">
            <v-text-field label="Festnetzt" v-model="customer.phone"></v-text-field>
          </v-col>
          <v-col cols="12" md="6">
            <v-text-field label="Email" type="email" v-model="customer.email" :rules="emailRules"></v-text-field>
          </v-col>
          <v-col cols="12" md="6">
            <v-text-field label="Kundennummer" v-model="customer.customer_number"></v-text-field>
          </v-col>
          <v-col cols="12" md="6">
            <v-checkbox label="Benötigt Einzahlungsschein" v-model="customer.needs_payment_order"></v-checkbox>
          </v-col>
          <v-col cols="12" md="6">
            <v-checkbox label="Verpflegung" v-model="customer.hasCatering"></v-checkbox>
          </v-col>
          <v-col cols="12" md="6" v-if="customer.hasCatering">
            <v-text-field label="Ausstattung der Küche" v-model="customer.kitchen_infrastructure"></v-text-field>
          </v-col>
          <v-col cols="12" md="6" v-if="customer.hasCatering">
            <v-text-field
              label="Max Anzahl Verpflegung"
              type="number"
              v-model="customer.max_catering"
            ></v-text-field>
          </v-col>
          <v-col cols="12" v-if="customer.hasCatering">
            <v-text-field label="Bemerkung zur Verpflegung" v-model="customer.comment_catering"></v-text-field>
          </v-col>
          <v-col cols="12">
            <v-text-field label="Fahrerinfo" v-model="customer.driver_info"></v-text-field>
          </v-col>
          <v-col cols="12">
            <v-text-field label="Google-Maps Link" v-model="customer.maps"></v-text-field>
          </v-col>
          <v-col cols="12">
            <v-text-field label="Allgemeine Bemerkungen" v-model="customer.comment"></v-text-field>
          </v-col>
          <v-col cols="12" class="text-center">
            <v-btn @click="save" :disabled="!allValid" color="primary">
              Speichern
              <v-icon right>send</v-icon>
            </v-btn>
          </v-col>
        </v-row>
      </v-container>
    </v-form>
  </div>
</template>

<script>
export default {
  name: 'AddCustomer',
  data() {
    return {
      customer: {
        // Empty string so that email regex correctyl works
        email: ''
      },
      apiUrl: process.env.VUE_APP_API_URL + 'customer',
      nameRules: [v => !!v || 'Dieses Feld muss vorhanden sein'],
      emailRules: [
        // A Valid email or emtpy string
        v => /^(?:[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+.[a-zA-Z]{2,6})?$/.test(v) || 'Email nicht korrekt'
      ]
    }
  },
  methods: {
    save() {
      this.axios
        .post(this.apiUrl, this.customer)
        .then(() => {
          this.$router.push('/customer')
        })
        .catch(error => {
          if (error.includes('validation.unique', 'email')) {
            this.$swal('Email existiert bereits', 'Diese Email wurde bereits verwendet', 'error')
          } else if (error.includes('validation.unique', 'customer_number')) {
            this.$swal('Kundennummber existiert bereits', 'Diese Kundennummer wurde bereits verwendet', 'error')
          } else {
            this.$swal('Fehler beim Speichern', 'Es ist ein unbekannter Fehler aufgetreten', 'error')
          }
        })
    }
  },
  computed: {
    allValid() {
      if (!this.customer.firstname) return false
      if (!this.customer.lastname) return false
      if (!this.customer.street) return false
      if (!this.customer.plz) return false
      if (!this.customer.place) return false
      return true
    }
  }
}
</script>

<style lang="scss" scoped>
.container {
  background-color: white;
  border-radius: 5px;
}

.save_button {
  text-align: center;
  margin-bottom: 30px;
}
</style>

<template>
  <div>
    <h1 class="text-center my-4">
      Kunde erstellen
    </h1>
    <v-form ref="form">
      <v-container>
        <v-row>
          <v-col
            cols="12"
            md="6"
          >
            <v-text-field
              v-model="customer.firstname"
              label="Vorname*"
              :rules="nameRules"
            ></v-text-field>
          </v-col>
          <v-col
            cols="12"
            md="6"
          >
            <v-text-field
              v-model="customer.lastname"
              label="Nachname*"
              :rules="nameRules"
            ></v-text-field>
          </v-col>
          <v-col cols="12">
            <h3>Adresse</h3>
            <Address v-model="customer.address"></Address>
            <v-checkbox
              v-model="customer.differingBillingAddress"
              label="Abweichende Rechnungsadresse"
            ></v-checkbox>
          </v-col>
          <v-col
            v-if="customer.differingBillingAddress"
            cols="12"
          >
            <h3>Rechnungsadresse</h3>
            <Address v-model="customer.billingAddress"></Address>
          </v-col>
          <v-col
            cols="12"
            md="6"
          >
            <v-text-field
              v-model="customer.mobile"
              label="Mobile"
            ></v-text-field>
          </v-col>
          <v-col
            cols="12"
            md="6"
          >
            <v-text-field
              v-model="customer.phone"
              label="Festnetzt"
            ></v-text-field>
          </v-col>
          <v-col
            cols="12"
            md="6"
          >
            <v-text-field
              v-model="customer.email"
              label="Email"
              type="email"
              :rules="emailRules"
            ></v-text-field>
          </v-col>
          <v-col
            cols="12"
            md="6"
          >
            <v-text-field
              v-model="customer.customer_number"
              label="Kundennummer"
            ></v-text-field>
          </v-col>
          <v-col
            cols="12"
            md="6"
          >
            <v-checkbox
              v-model="customer.needs_payment_order"
              label="Benötigt Einzahlungsschein"
            ></v-checkbox>
          </v-col>
          <v-col
            cols="12"
            md="6"
          >
            <v-checkbox
              v-model="customer.hasCatering"
              label="Verpflegung"
            ></v-checkbox>
          </v-col>
          <v-col
            v-if="customer.hasCatering"
            cols="12"
            md="6"
          >
            <v-text-field
              v-model="customer.kitchen_infrastructure"
              label="Ausstattung der Küche"
            ></v-text-field>
          </v-col>
          <v-col
            v-if="customer.hasCatering"
            cols="12"
            md="6"
          >
            <v-text-field
              v-model="customer.max_catering"
              label="Max Anzahl Verpflegung"
              type="number"
            ></v-text-field>
          </v-col>
          <v-col
            v-if="customer.hasCatering"
            cols="12"
          >
            <v-text-field
              v-model="customer.comment_catering"
              label="Bemerkung zur Verpflegung"
            ></v-text-field>
          </v-col>
          <v-col cols="12">
            <v-text-field
              v-model="customer.driver_info"
              label="Fahrerinfo"
            ></v-text-field>
          </v-col>
          <v-col cols="12">
            <v-text-field
              v-model="customer.maps"
              label="Google-Maps Link"
            ></v-text-field>
          </v-col>
          <v-col cols="12">
            <v-text-field
              v-model="customer.comment"
              label="Allgemeine Bemerkungen"
            ></v-text-field>
          </v-col>
          <v-col
            cols="12"
            class="text-center"
          >
            <v-btn
              color="primary"
              @click="save"
            >
              Speichern
              <v-icon right>
                send
              </v-icon>
            </v-btn>
          </v-col>
        </v-row>
      </v-container>
    </v-form>
  </div>
</template>

<script>
import Address from '@/components/customer/Address'
import { rules } from '@/utils'

export default {
  name: 'AddCustomer',
  components: {
    Address
  },
  data() {
    return {
      customer: {
        // Empty string so that email regex correctyl works
        email: '',
        address: {},
        billingAddress: {},
        differingBillingAddress: false
      },
      nameRules: [rules.required],
      emailRules: [
        // A Valid email or emtpy string
        (v) => /^(?:[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+.[a-zA-Z]{2,6})?$/.test(v) || 'Email nicht korrekt'
      ]
    }
  },
  methods: {
    save() {
      if (this.$refs.form.validate()) {
        this.axios
          .post('customer', this.customer)
          .then(() => {
            this.$router.push('/customer')
          })
          .catch((error) => {
            if (error.includes('validation.unique', 'email')) {
              this.$swal('Email existiert bereits', 'Diese Email wurde bereits verwendet', 'error')
            } else if (error.includes('validation.unique', 'customer_number')) {
              this.$swal('Kundennummber existiert bereits', 'Diese Kundennummer wurde bereits verwendet', 'error')
            } else {
              this.$swal('Fehler beim Speichern', 'Es ist ein unbekannter Fehler aufgetreten', 'error')
            }
          })
      }
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

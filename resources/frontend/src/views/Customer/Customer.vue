<template>
  <v-container>
    <v-row class="single-customer">
      <v-col
        cols="12"
        md="6"
        class="py-0"
      >
        <input-field
          v-model="customer.firstname"
          label="Vorname"
          :readonly="!isUserAllowedToEdit"
          @input="changed"
        ></input-field>
      </v-col>
      <v-col
        cols="12"
        md="6"
        class="py-0"
      >
        <input-field
          v-model="customer.lastname"
          label="Nachname"
          :readonly="!isUserAllowedToEdit"
          @input="changed"
        ></input-field>
      </v-col>
      <v-col
        cols="12"
        class="py-0"
      >
        <v-divider class="mb-8"></v-divider>
        <h3 class="headline">
          Adresse
        </h3>
        <edit-address
          v-model="customer.address"
          :readonly="!isUserAllowedToEdit"
          @change="changed"
        ></edit-address>
        <v-divider class="mb-8"></v-divider>
        <v-checkbox
          v-model="customer.differingBillingAddress"
          label="Abweichende Rechnungsadresse"
          @change="changed"
        ></v-checkbox>
      </v-col>
      <v-col
        v-if="customer.differingBillingAddress"
        cols="12"
        class="py-0"
      >
        <h3 class="headline">
          Rechnungsadresse
        </h3>
        <edit-address
          v-model="customer.billing_address"
          :readonly="!isUserAllowedToEdit"
          @change="changed"
        ></edit-address>
        <v-divider class="mb-8"></v-divider>
      </v-col>
      <v-col
        cols="12"
        md="6"
        class="py-0"
      >
        <input-field
          v-model="customer.mobile"
          label="Mobile"
          :readonly="!isUserAllowedToEdit"
          @input="changed"
        ></input-field>
      </v-col>
      <v-col
        cols="12"
        md="6"
        class="py-0"
      >
        <input-field
          v-model="customer.phone"
          label="Festnetz"
          :readonly="!isUserAllowedToEdit"
          @input="changed"
        ></input-field>
      </v-col>
      <v-col
        cols="12"
        md="6"
        class="py-0"
      >
        <input-field
          v-model="customer.email"
          label="Email"
          :readonly="!isUserAllowedToEdit"
          @change="changed('email')"
        ></input-field>
      </v-col>
      <v-col
        cols="12"
        md="6"
        class="py-0"
      >
        <input-field
          v-model="customer.customer_number"
          label="Kundennummer"
          :readonly="!isUserAllowedToEdit"
          @input="changed"
        ></input-field>
      </v-col>
      <v-col
        cols="12"
        md="6"
        class="py-0"
      >
        <v-row>
          <v-checkbox
            v-model="customer.needs_payment_order"
            label="Benötigt Einzahlungsschein"
            color="primary"
            :readonly="!isUserAllowedToEdit"
            @change="changed"
          ></v-checkbox>
        </v-row>
      </v-col>
      <v-col
        cols="12"
        md="6"
        class="py-0"
      >
        <v-row>
          <v-checkbox
            v-model="customer.hasCatering"
            label="Verpflegung"
            color="primary"
            :readonly="!isUserAllowedToEdit"
            @change="changed"
          ></v-checkbox>
        </v-row>
      </v-col>
      <v-col
        cols="12"
        md="6"
        class="py-0"
      >
        <input-field
          v-model="customer.kitchen_infrastructure"
          label="Ausstattung der Küche"
          :readonly="!isUserAllowedToEdit"
          @input="changed"
        ></input-field>
      </v-col>
      <v-col
        cols="12"
        md="6"
        class="py-0"
      >
        <input-field
          v-model="customer.max_catering"
          label="Max Anzahl Verpflegung"
          type="number"
          :readonly="!isUserAllowedToEdit"
          @input="changed"
        ></input-field>
      </v-col>
      <v-col
        cols="12"
        class="py-0"
      >
        <input-field
          v-model="customer.comment_catering"
          label="Bemerkung zur Verpflegung"
          :readonly="!isUserAllowedToEdit"
          long
          @input="changed"
        ></input-field>
      </v-col>
      <v-col
        cols="12"
        class="py-0"
      >
        <input-field
          v-model="customer.driver_info"
          label="Fahrerinfo"
          :readonly="!isUserAllowedToEdit"
          long
          @input="changed"
        ></input-field>
      </v-col>
      <v-col
        cols="12"
        class="py-0"
      >
        <input-field
          v-model="customer.maps"
          label="Google-Maps Link"
          :readonly="!isUserAllowedToEdit"
          long
          @input="changed"
        ></input-field>
      </v-col>
      <v-col
        cols="12"
        class="py-0"
      >
        <input-field
          v-model="customer.comment"
          label="Allgemeine Bemerkung"
          :readonly="!isUserAllowedToEdit"
          long
          @input="changed"
        ></input-field>
      </v-col>
      <v-col
        cols="12"
        md="2"
        class="py-0"
      >
        <p class="description font-weight-bold subheading mb-0">
          Projekte
        </p>
      </v-col>
      <v-col
        cols="12"
        md="10"
        class="py-0"
      >
        <projects
          :customer-id="$route.params.id"
          :readonly="!isUserAllowedToEdit"
        ></projects>
      </v-col>
      <v-col
        cols="12"
        md="2"
        class="py-0"
      >
        <p class="description font-weight-bold subheading mb-0">
          Passwort
        </p>
      </v-col>
      <v-col
        cols="12"
        md="10"
        class="py-0"
      >
        <v-btn
          v-if="!customer.secret && $auth.user().hasPermission(['superadmin'], ['customer_write'])"
          color="primary"
          class="float-right"
          @click="resetPassword"
        >
          Passwort zurücksetzen
        </v-btn>
        <p
          class="mt-3 reset-password-text"
        >
          {{ customer.secret ? customer.secret : 'Passwort wurde von Kunde geändert' }}
        </p>
      </v-col>
      <v-col
        v-if="$auth.user().hasPermission(['superadmin'], ['customer_write'])"
        cols="12"
        class="py-0"
      >
        <v-btn
          color="red white--text"
          @click="deleteCustomer"
        >
          Löschen
        </v-btn>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import Projects from '@/components/customer/Projects'
import InputField from '@/components/general/InputField'
import EditAddress from '@/components/customer/EditAddress'
import _ from 'lodash'

export default {
  name: 'Home',
  components: {
    Projects,
    InputField,
    EditAddress
  },
  data() {
    return {
      customer: {
        address: {},
        billing_address: {}
      },
      original: {},
      apiUrl: `customers/${this.$route.params.id}`,
      isUserAllowedToEdit: false
    }
  },
  mounted() {
    this.axios.get(this.apiUrl).then(response => {
      if (!response.data.billing_address) response.data.billing_address = {}
      this.customer = response.data
      this.original = this._.cloneDeep(this.customer)
    })
    this.isUserAllowedToEdit = this.$auth.user().hasPermission(['superadmin'], ['customer_write'])
  },
  methods: {
    changed: _.debounce(function(changedElement = null) {
      if (changedElement === 'email') {
        setTimeout(() => {
          this.$swal({
            title: 'Wollen sie die Email wirklich ändern?',
            text: `Bist du dir sicher, dass die Email: "${this.customer.email}" wirklich stimmt?`,
            confirmButtonText: 'Ja',
            cancelButtonText: 'Nein',
            showCancelButton: true
          }).then(async result => {
            if (result.value) {
              this.update()
            }
          })
        }, 10)
      } else {
        this.update()
      }
    }, 400),
    update() {
      this.axios.put(this.apiUrl, this.customer).catch(error => {
        if (error.response.data.errors && error.response.data.errors.email.includes('validation.email')) {
          this.$swal('Email nicht korrekt', 'Bitte schaue, dass die email ein korrektes Format hat.', 'error')
        } else if (error.response.data.errors && error.response.data.errors.email.includes('validation.unique')) {
          this.$swal('Email bereits vorhanden', 'Diese Email wird bereits von einem anderen Benutzer verwendet.', 'error')
        } else {
          this.$swal('Fehler', 'Kunde konnte aus einem unbekannten Grund nicht gespeichert werden.', 'error')
        }
      })
    },
    deleteCustomer() {
      this.axios.delete(this.apiUrl).then(() => {
        this.$store.dispatch('resetCustomers')
        this.$router.push('/customer')
      })
    },
    resetPassword() {
      this.axios
        .patch(`${this.apiUrl}/resetpassword`)
        .then(response => {
          this.customer.secret = response.data
        })
        .catch(() => {
          this.$swal('Fehler', 'Passwort konnte nicht zurückgesetzt werden', 'error')
        })
    }
  }
}
</script>

<style lang="scss" scoped>
.single-customer {
  background-color: white;
  margin-top: 30px;
  border-radius: 5px;
  padding: 20px;
  box-shadow: 0 0 10px lightgray;
}

input {
  border-bottom: none;
}

.description {
  margin-top: 12px;
}

.delete_button {
  text-align: center;
  margin-top: 40px;
}

@media only screen and (max-width: 959px) {
  .container {
    padding: 0;
  }

  .single-customer {
    margin-top: 0;
    background-color: transparent;
    box-shadow: none;
  }

  .reset-password-text {
    width: 100%;
  }
}
</style>

<template>
  <v-container>
    <v-row class="single-customer">
      <v-col cols="12" md="6" class="py-0">
        <v-row>
          <v-col cols="12" md="4" class="py-0">
            <p class="description font-weight-bold subheading mb-0">Vorname</p>
          </v-col>
          <v-col cols="12" md="8" class="py-0">
            <edit-field
              v-model="customer.firstname"
              @change="changed"
              :readonly="!isUserAllowedToEdit"
            ></edit-field>
          </v-col>
        </v-row>
      </v-col>
      <v-col cols="12" md="6" class="py-0">
        <v-row>
          <v-col cols="12" md="4" class="py-0">
            <p class="description font-weight-bold subheading mb-0">Nachname</p>
          </v-col>
          <v-col cols="12" md="8" class="py-0">
            <edit-field
              v-model="customer.lastname"
              @change="changed"
              :readonly="!isUserAllowedToEdit"
            ></edit-field>
          </v-col>
        </v-row>
      </v-col>
      <v-col cols="12" md="6" class="py-0">
        <v-row>
          <v-col cols="12" md="4" class="py-0">
            <p class="description font-weight-bold subheading mb-0">Strasse + Nr</p>
          </v-col>
          <v-col cols="12" md="8" class="py-0">
            <edit-field
              v-model="customer.street"
              @change="changed"
              :readonly="!isUserAllowedToEdit"
            ></edit-field>
          </v-col>
        </v-row>
      </v-col>
      <v-col cols="12" md="6" class="py-0">
        <v-row>
          <v-col cols="12" md="4" class="py-0">
            <p class="description font-weight-bold subheading mb-0">Ort</p>
          </v-col>
          <v-col cols="12" md="8" class="py-0">
            <edit-field v-model="customer.place" @change="changed" :readonly="!isUserAllowedToEdit"></edit-field>
          </v-col>
        </v-row>
      </v-col>
      <v-col cols="12" md="6" class="py-0">
        <v-row>
          <v-col cols="12" md="4" class="py-0">
            <p class="description font-weight-bold subheading mb-0">PLZ</p>
          </v-col>
          <v-col cols="12" md="8" class="py-0">
            <edit-field v-model="customer.plz" @change="changed" :readonly="!isUserAllowedToEdit"></edit-field>
          </v-col>
        </v-row>
      </v-col>
      <v-col cols="12" md="6" class="py-0">
        <v-row>
          <v-col cols="12" md="4" class="py-0">
            <p class="description font-weight-bold subheading mb-0">Zusatz</p>
          </v-col>
          <v-col cols="12" md="8" class="py-0">
            <edit-field
              v-model="customer.addition"
              @change="changed"
              :readonly="!isUserAllowedToEdit"
            ></edit-field>
          </v-col>
        </v-row>
      </v-col>
      <v-col cols="12" md="6" class="py-0">
        <v-row>
          <v-col cols="12" md="4" class="py-0">
            <p class="description font-weight-bold subheading mb-0">Mobile</p>
          </v-col>
          <v-col cols="12" md="8" class="py-0">
            <edit-field
              v-model="customer.mobile"
              @change="changed"
              :readonly="!isUserAllowedToEdit"
            ></edit-field>
          </v-col>
        </v-row>
      </v-col>
      <v-col cols="12" md="6" class="py-0">
        <v-row>
          <v-col cols="12" md="4" class="py-0">
            <p class="description font-weight-bold subheading mb-0">Festnetz</p>
          </v-col>
          <v-col cols="12" md="8" class="py-0">
            <edit-field v-model="customer.phone" @change="changed" :readonly="!isUserAllowedToEdit"></edit-field>
          </v-col>
        </v-row>
      </v-col>
      <v-col cols="12" md="6" class="py-0">
        <v-row>
          <v-col cols="12" md="4" class="py-0">
            <p class="description font-weight-bold subheading mb-0">Email</p>
          </v-col>
          <v-col cols="12" md="8" class="py-0">
            <edit-field
              v-model="customer.email"
              @change="changed('email')"
              :readonly="!isUserAllowedToEdit"
            ></edit-field>
          </v-col>
        </v-row>
      </v-col>
      <v-col cols="12" md="6" class="py-0">
        <v-row>
          <v-col cols="12" md="4" class="py-0">
            <p class="description font-weight-bold subheading mb-0">Kundennummer</p>
          </v-col>
          <v-col cols="12" md="8">
            <edit-field
              v-model="customer.customer_number"
              @change="changed"
              :readonly="!isUserAllowedToEdit"
            ></edit-field>
          </v-col>
        </v-row>
      </v-col>
      <v-col cols="12" md="6" class="py-0">
        <v-row>
          <v-checkbox
            label="Benötigt Einzahlungsschein"
            v-model="customer.needs_payment_order"
            @change="changed"
            color="primary"
            :readonly="!isUserAllowedToEdit"
          ></v-checkbox>
        </v-row>
      </v-col>
      <v-col cols="12" md="6" class="py-0">
        <v-row>
          <v-checkbox
            label="Verpflegung"
            v-model="customer.hasCatering"
            @change="changed"
            color="primary"
            :readonly="!isUserAllowedToEdit"
          ></v-checkbox>
        </v-row>
      </v-col>
      <v-col cols="12" md="6" class="py-0">
        <v-row>
          <v-col cols="12" md="4" class="py-0">
            <p class="description font-weight-bold subheading mb-0">Ausstattung der Küche</p>
          </v-col>
          <v-col cols="12" md="8" class="py-0">
            <edit-field
              v-model="customer.kitchen_infrastructure"
              @change="changed"
              :readonly="!isUserAllowedToEdit"
            ></edit-field>
          </v-col>
        </v-row>
      </v-col>
      <v-col cols="12" md="6" class="py-0">
        <v-row>
          <v-col cols="12" md="4" class="py-0">
            <p class="description font-weight-bold subheading mb-0">Max Anzahl Verpflegung</p>
          </v-col>
          <v-col cols="12" md="8" class="py-0">
            <edit-field
              v-model="customer.max_catering"
              type="number"
              @change="changed"
              :readonly="!isUserAllowedToEdit"
            ></edit-field>
          </v-col>
        </v-row>
      </v-col>
      <v-col cols="12" class="py-0">
        <v-row>
          <v-col cols="12" md="2" class="py-0">
            <p class="description font-weight-bold subheading mb-0">Bemerkung zur Verpflegung</p>
          </v-col>
          <v-col cols="12" md="10" class="py-0">
            <edit-field
              v-model="customer.comment_catering"
              @change="changed"
              :readonly="!isUserAllowedToEdit"
            ></edit-field>
          </v-col>
        </v-row>
      </v-col>
      <v-col cols="12" class="py-0">
        <v-row>
          <v-col cols="12" md="2">
            <p class="description font-weight-bold subheading mb-0">Fahrerinfo</p>
          </v-col>
          <v-col cols="12" md="10" class="py-0">
            <edit-field
              v-model="customer.driver_info"
              @change="changed"
              :readonly="!isUserAllowedToEdit"
            ></edit-field>
          </v-col>
        </v-row>
      </v-col>
      <v-col cols="12" class="py-0">
        <v-row>
          <v-col cols="12" md="2" class="py-0">
            <p class="description font-weight-bold subheading mb-0">Google-Maps Link</p>
          </v-col>
          <v-col cols="12" md="10" class="py-0">
            <edit-field v-model="customer.maps" @change="changed" :readonly="!isUserAllowedToEdit"></edit-field>
          </v-col>
        </v-row>
      </v-col>
      <v-col cols="12" class="py-0">
        <v-row>
          <v-col cols="12" md="2" class="py-0">
            <p class="description font-weight-bold subheading mb-0">Allgemeine Bemerkung</p>
          </v-col>
          <v-col cols="12" md="10" class="py-0">
            <edit-field
              v-model="customer.comment"
              @change="changed"
              :readonly="!isUserAllowedToEdit"
            ></edit-field>
          </v-col>
        </v-row>
      </v-col>
      <v-col cols="12" class="py-0">
        <v-row>
          <v-col cols="12" md="2" class="py-0">
            <p class="description font-weight-bold subheading mb-0">Projekte</p>
          </v-col>
          <v-col cols="12" md="10" class="py-0">
            <projects :customerId="$route.params.id" :readonly="!isUserAllowedToEdit"></projects>
          </v-col>
        </v-row>
      </v-col>
      <v-col cols="12" md="2" class="py-0">
        <p class="description font-weight-bold subheading mb-0">Passwort</p>
      </v-col>
      <v-col cols="12" md="10" class="py-0">
        <v-btn
          color="primary"
          class="float-right"
          @click="resetPassword"
          v-if="!customer.secret && $auth.user().hasPermission(['superadmin'], ['customer_write'])"
        >Passwort zurücksetzen</v-btn>
        <p
          class="mt-3 reset-password-text"
        >{{customer.secret ? customer.secret : 'Passwort wurde von Kunde geändert'}}</p>
      </v-col>
      <v-col
        cols="12"
        class="py-0"
        v-if="$auth.user().hasPermission(['superadmin'], ['customer_write'])"
      >
        <v-btn color="red white--text" @click="deleteCustomer">Löschen</v-btn>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import Projects from '@/components/customer/Projects'

export default {
  name: 'home',
  components: {
    Projects
  },
  data() {
    return {
      customer: {},
      apiUrl: process.env.VUE_APP_API_URL + 'customer/' + this.$route.params.id,
      isUserAllowedToEdit: false
    }
  },
  mounted() {
    this.$store.commit('isLoading', true)
    this.axios.get(this.apiUrl).then(response => {
      this.customer = response.data
      this.$store.commit('isLoading', false)
    })
    this.isUserAllowedToEdit = this.$auth.user().hasPermission(['superadmin'], ['customer_write'])
  },
  methods: {
    changed(changedElement = null) {
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
    },
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
        .patch(this.apiUrl + '/resetpassword')
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

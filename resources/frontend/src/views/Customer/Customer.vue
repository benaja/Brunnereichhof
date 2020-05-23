<template>
  <fragment>
    <navigation-bar
      title="Kunde bearbeiten"
      :loading="isLoading"
    >
      <template v-if="hasPedingChanges">
        <v-btn
          color="primary"
          class="ml-auto"
          depressed
          @click="saveChanges"
        >
          Fertig
        </v-btn>
      </template>
    </navigation-bar>
    <v-container>
      <customer-form
        v-model="customer"
        :original="original"
        @change="changed"
      ></customer-form>
      <v-row>
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
            v-if="!customer.secret
              && $auth.user().hasPermission(['superadmin'], ['customer_write'])"
            color="primary"
            class="float-right"
            text
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
            depressed
            @click="deleteCustomer"
          >
            Löschen
          </v-btn>
        </v-col>
      </v-row>
    </v-container>
  </fragment>
</template>

<script>
import _ from 'lodash'
import CustomerForm from '@/components/forms/CustomerForm'

export default {
  components: {
    CustomerForm
  },
  data() {
    return {
      customer: {
        address: {},
        billing_address: {}
      },
      original: {
        address: {},
        billing_address: {}
      },
      apiUrl: `customers/${this.$route.params.id}`,
      isUserAllowedToEdit: false,
      loadingReset: false,
      isLoading: false
    }
  },
  computed: {
    hasPedingChanges() {
      return !this._.isEqual(this.customer, this.original)
    }
  },
  mounted() {
    this.isLoading = true
    this.axios.get(this.apiUrl).then(response => {
      if (!response.data.billing_address) response.data.billing_address = {}
      this.customer = response.data
      this.original = this._.cloneDeep(this.customer)
    }).finally(() => {
      this.isLoading = false
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
      this.$store.commit('isSaving', true)
      this.updateCustomer(this.customer).then(() => {
        this.$store.commit('isSaving', false)
      })
    },
    updateCustomer(customer) {
      return new Promise(resove => {
        this.axios.put(this.apiUrl, customer).catch(error => {
          if (error.response.data.errors && error.response.data.errors.email.includes('validation.email')) {
            this.$swal('Email nicht korrekt', 'Bitte schaue, dass die email ein korrektes Format hat.', 'error')
          } else if (error.response.data.errors && error.response.data.errors.email.includes('validation.unique')) {
            this.$swal('Email bereits vorhanden', 'Diese Email wird bereits von einem anderen Benutzer verwendet.', 'error')
          } else {
            this.$swal('Fehler', 'Kunde konnte aus einem unbekannten Grund nicht gespeichert werden.', 'error')
          }
        }).finally(() => {
          resove()
        })
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
    },
    saveChanges() {
      this.original = this._.cloneDeep(this.customer)
    },
    resetCustomer() {
      this.loadingReset = true
      this.updateCustomer(this.original).then(() => {
        this.customer = this._.cloneDeep(this.original)
        this.loadingReset = false
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

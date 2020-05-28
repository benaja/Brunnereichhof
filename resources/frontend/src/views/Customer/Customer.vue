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
          :loading="isSaving"
          @click="saveChanges"
        >
          Fertig
        </v-btn>
      </template>
    </navigation-bar>
    <v-container>
      <customer-form
        ref="form"
        v-model="customer"
        :original="original"
        @change="changed"
        @submit="saveChanges"
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
            :loading="isDeleting"
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
import { confirmAction } from '@/utils'

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
      isLoading: false,
      isSaving: false,
      isDeleting: false
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
      if (!this.$refs.form.validate()) return
      if (changedElement === 'email' && this.customer.email !== this.original.email) {
        setTimeout(() => {
          this.$swal({
            title: 'Wollen sie die Email wirklich ändern?',
            text: `Bist du dir sicher, dass die Email: "${this.customer.email}" wirklich stimmt?`,
            confirmButtonText: 'Ja',
            cancelButtonText: 'Nein',
            showCancelButton: true,
            allowOutsideClick: false
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
      if (this.$refs.form.validate()) {
        this.$store.commit('isSaving', true)
        this.updateCustomer(this.customer).then(() => {
          this.$store.commit('isSaving', false)
        })
      }
    },
    updateCustomer(customer) {
      return new Promise(resolve => {
        this.axios.put(this.apiUrl, customer).catch(error => {
          if (error.includes('validation.email')) {
            this.$swal('Email nicht korrekt', 'Bitte schaue, dass die email ein korrektes Format hat.', 'error')
          } else if (error.includes('validation.unique')) {
            this.$swal('Email bereits vorhanden', 'Diese Email wird bereits von einem anderen Benutzer verwendet.', 'error')
          } else {
            this.$swal('Fehler', 'Kunde konnte aus einem unbekannten Grund nicht gespeichert werden.', 'error')
          }
          resolve(false)
        }).then(() => {
          resolve(true)
        })
      })
    },
    deleteCustomer() {
      confirmAction().then(result => {
        if (result) {
          this.isDeleting = true
          this.axios.delete(this.apiUrl).then(() => {
            this.$router.push('/customer')
          }).catch(() => {
            this.$swal('Fehler', 'Kunde konnte nicht gelöscht werden', 'error')
          }).finally(() => {
            this.isDeleting = false
          })
        }
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
      if (this.$refs.form.validate()) {
        this.isSaving = true
        this.updateCustomer(this.customer).then(result => {
          this.isSaving = false
          if (result) {
            this.original = this._.cloneDeep(this.customer)
          }
        })
      } else {
        this.$store.dispatch('error', 'Prüffe deine Eingaben.')
      }
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

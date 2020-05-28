<template>
  <fragment>
    <navigation-bar title="Kunde erstellen"></navigation-bar>
    <v-container>
      <customer-form
        ref="form"
        v-model="customer"
        @submit="save"
      ></customer-form>
      <v-btn
        color="primary"
        :loading="isLoading"
        depressed
        @click="save"
      >
        Speichern
        <v-icon right>
          send
        </v-icon>
      </v-btn>
    </v-container>
  </fragment>
</template>

<script>
import CustomerForm from '@/components/forms/CustomerForm'

export default {
  components: {
    CustomerForm
  },
  data() {
    return {
      customer: {
        // Empty string so that email regex correctyl works
        email: '',
        address: {},
        billing_address: {},
        differingBillingAddress: false,
        projects: []
      },
      isLoading: false
    }
  },
  methods: {
    save() {
      if (this.$refs.form.validate()) {
        this.isLoading = true
        this.axios
          .post('customers', this.customer)
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
          }).finally(() => {
            this.isLoading = false
          })
      }
    }
  }
}
</script>

<template>
  <form-container>
    <template>
      <h1 class="display-1 mb-8">Passwort zurücksetzten</h1>
      <v-form
        onsubmit="return false;"
        ref="form"
        v-if="!emailSend"
        v-model="valid"
        @submit="resetPassword"
      >
        <v-text-field
          outlined
          label="Email"
          v-model="email"
          :rules="[rules.required]"
          :error-messages="errorMessage"
        ></v-text-field>
        <v-btn
          class="reset-button"
          color="primary"
          @click="resetPassword"
          :loading="isLoading"
        >Zurücksetzten</v-btn>
      </v-form>
      <div v-else>
        <p class="primary--text">Email erfolgreich gesendet.</p>
        <p>Folgen Sie den Anweisungen auf der erhaltenen Email.</p>
      </div>
    </template>
  </form-container>
</template>

<script>
import FormContainer from '@/components/general/FormContainer'

export default {
  components: {
    FormContainer
  },
  data() {
    return {
      email: '',
      rules: {
        required: v => !!v || 'Dieses Feld muss vorhanden sein'
      },
      emailSend: false,
      valid: false,
      isLoading: false,
      errorMessage: null
    }
  },
  mounted() {
    this.email = this.$route.query.email
  },
  methods: {
    resetPassword() {
      if (this.$refs.form.validate()) {
        this.isLoading = true
        this.axios
          .post('/auth/reset-password', { email: this.email })
          .then(() => {
            this.emailSend = true
            this.isLoading = false
          })
          .catch(error => {
            this.isLoading = false
            if (error.includes('Email does not exist')) {
              this.errorMessage = 'Email existiert nicht'
            } else {
              this.$swal('Unbekannter Fehler', 'Bitte versuche es später erneut.', 'error')
            }
          })
      }
    }
  }
}
</script>

<style lang="scss" scoped>
.reset-button {
  width: 100%;
}
</style>

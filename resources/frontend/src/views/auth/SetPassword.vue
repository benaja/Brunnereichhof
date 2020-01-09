<template>
  <form-container>
    <h1 class="display-1 mb-8">Passwort setzen</h1>
    <v-form onSubmit="return false;" ref="form" @submit="setPassword">
      <v-text-field
        v-model="password"
        outlined
        label="Neues Passwort"
        type="password"
        :rules="[rules.required, rules.password]"
        class="mb-4"
        validate-on-blur
      ></v-text-field>
      <v-text-field
        v-model="repeatPassword"
        outlined
        label="Passwort wiederholen"
        class="mb-4"
        type="password"
        :rules="[rules.repeat]"
        validate-on-blur
        :error-messages="errorMessage"
        @blur="errorMessage = null"
      ></v-text-field>
      <v-btn
        type="submit"
        color="primary"
        class="set-password-button"
        :loading="isLoading"
      >Speichern</v-btn>
    </v-form>
  </form-container>
</template>

<script>
import FormContainer from '@/components/general/FormContainer'

export default {
  name: 'SetPassword',
  components: {
    FormContainer
  },
  props: {
    token: {
      type: String,
      required: true
    },
    userId: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      password: '',
      repeatPassword: '',
      emailRegex: /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/,
      rules: {
        required: value => !!value || 'Dieses Feld muss vorhanden sein',
        password: value => value.length >= 6 || 'Passwort muss mindestens 6 Zeichen haben.',
        email: value => this.emailRegex.test(value) || 'Keine gültige Email',
        repeat: value => value === this.password || 'Passwörter stimmen nicht überein',
        matchOldPassword: value => this.matchOldPassword || 'Passwort nicht korrekt'
      },
      isLoading: false,
      errorMessage: null
    }
  },
  methods: {
    setPassword() {
      if (this.$refs.form.validate()) {
        this.isLoading = true
        this.axios
          .post('auth/set-password', { password: this.password, token: this.token, userId: this.userId })
          .then(response => {
            this.isLoading = false
            this.$auth.login({
              params: {
                email: response.data.email,
                password: this.password
              },
              error: function() {
                this.$swal('Unbekannter Fehler', 'Es ist ein unbekannter Fehler aufgetreten. Bitte versuche es später erneut.', 'error')
              },
              rememberMe: true,
              redirect: '/',
              fetchUser: true
            })
          })
          .catch(error => {
            this.isLoading = false
            if (error.includes('Token is invalid')) {
              this.errorMessage = 'Link ist ungültig. Versuche erneut das Passwort zurückzusetzten.'
            } else {
              this.$swal('Unbekannter Fehler', 'Es ist ein unbekannter Fehler aufgetreten. Bitte versuche es später erneut.', 'error')
            }
          })
      }
    }
  }
}
</script>

<style lang="scss" scoped>
.set-password-button {
  width: 100%;
}
</style>

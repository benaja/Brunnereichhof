<template>
  <form-container>
    <h1 class="display-1 mb-8">
      Passwort setzen
    </h1>
    <v-form
      ref="form"
      @keyup.native.enter="setPassword"
    >
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
        color="primary"
        class="set-password-button"
        depressed
        :loading="isLoading"
        @click="setPassword"
      >
        Speichern
      </v-btn>
    </v-form>
  </form-container>
</template>

<script>
import FormContainer from '@/components/general/FormContainer'
import { rules } from '@/utils'

export default {
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
      rules: {
        ...rules,
        password: value => value.length >= 6 || 'Passwort muss mindestens 6 Zeichen haben.',
        repeat: value => value === this.password || 'Passwörter stimmen nicht überein',
        matchOldPassword: () => this.matchOldPassword || 'Passwort nicht korrekt'
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
              error() {
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

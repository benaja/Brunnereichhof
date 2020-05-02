<template>
  <div class="container">
    <h1 class="text-center">
      Passwort ändern
    </h1>
    <div class="elevation-1 pa-4">
      <v-text-field
        ref="passwordOld"
        v-model="passwordOld"
        type="password"
        required
        label="Altes Passwort"
        :rules="[rules.required, rules.matchOldPassword]"
        :error="!matchOldPassword"
        :error-messages="matchNotOldPasswordText"
        @change="matchNotOldPasswordText = []"
      ></v-text-field>
      <v-text-field
        v-model="passwordNew"
        type="password"
        label="Neues Passwort"
        :rules="[rules.required, rules.password]"
        validate-on-blur
        hint="Mindestens 6 Zeichen"
      ></v-text-field>
      <v-text-field
        v-model="passwordRepeated"
        type="password"
        label="Passwort wiederholen"
        :rules="[rules.required, rules.repeat]"
        validate-on-blur
        @keyup.13="save"
      ></v-text-field>
      <v-text-field
        v-if="!$auth.user().email"
        v-model="email"
        label="Email"
        :rules="[rules.required, rules.email]"
        validate-on-blur
        :error-messages="emailUsedText"
        @keyup.13="save"
        @change="emailUsedText = []"
      />
      <p class="text-center">
        <v-btn
          color="primary"
          :disabled="!allValid"
          @click="save"
        >
          Speichern
        </v-btn>
      </p>
    </div>
  </div>
</template>

<script>
import { rules } from '@/utils'

export default {
  name: 'EditProfile',
  data() {
    return {
      passwordOld: '',
      passwordNew: '',
      passwordRepeated: '',
      email: '',
      emailRegex: /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/,
      rules: {
        ...rules,
        password: () => this.passwordNew.length >= 6 || 'Passwort muss mindestens 6 Zeichen haben.',
        repeat: value => value === this.passwordNew || 'Passwörter stimmen nicht überein',
        matchOldPassword: () => this.matchOldPassword || 'Passwort nicht korrekt'
      },
      matchOldPassword: true,
      matchNotOldPasswordText: [],
      emailUsedText: []
    }
  },
  computed: {
    allValid() {
      if (!this.passwordOld) return false
      if (this.passwordNew.length < 6) return false
      if (!this.$auth.user().email && !this.emailRegex.test(this.email)) return false
      if (this.passwordNew !== this.passwordRepeated) return false
      return true
    }
  },
  mounted() {
    if (!this.$auth.user().email) {
      this.$swal(
        'Bitte ändern Sie ihr Passwort',
        'Sie haben noch das Standard-Passwort. Bitte persolalisieren Sie ihr Passwort und geben Sie eine Email ein, um fortfahren zu können',
        'info',
      )
    } else if (this.$auth.user().isPasswordChanged !== 1) {
      this.$swal(
        'Bitte ändern Sie ihr Passwort',
        'Sie haben noch das Standard-Passwort. Bitte persolalisieren Sie ihr Passwort und geben Sie eine Email ein, um fortfahren zu können.',
        'info',
      )
    }
  },
  methods: {
    save() {
      if (this.allValid) {
        this.axios
          .post('password/change', {
            passwordOld: this.passwordOld,
            password: this.passwordNew,
            password_confirmation: this.passwordRepeated,
            email: this.email
          })
          .then(() => {
            const user = this.$auth.user()
            user.isPasswordChanged = 1
            this.$auth.user(user)
            this.$router.push('/')
          })
          .catch(error => {
            if (error.includes('password invalid')) {
              this.matchOldPassword = false
              this.$refs.passwordOld.focus()
              this.matchNotOldPasswordText = 'Passwort nicht korrekt'
            } else if (error.includes('validation.unique', 'email')) {
              this.emailUsedText = 'Email ist bereits vorhanden'
            } else {
              this.$swal('Fehler', 'Passwort konnte aus einem unbekannten Grund nicht geändert werden', 'error')
            }
          })
      }
    }
  }
}
</script>

<style lang="scss" scoped>
.container {
  max-width: 800px;
}
</style>

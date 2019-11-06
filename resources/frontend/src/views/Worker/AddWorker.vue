<template>
  <div>
    <h1 class="text-center my-4">Hofmitarbeiter erstellen</h1>
    <v-form>
      <v-container>
        <v-row>
          <v-col cols="12" md="6">
            <v-text-field label="Vorname*" v-model="worker.firstname" :rules="nameRules"></v-text-field>
          </v-col>
          <v-col cols="12" md="6">
            <v-text-field label="Nachname*" v-model="worker.lastname" :rules="nameRules"></v-text-field>
          </v-col>
          <v-col cols="12">
            <v-text-field
              type="email"
              label="Email*"
              v-model="worker.email"
              :rules="emailRules"
              validate-on-blur
            ></v-text-field>
          </v-col>
          <v-col cols="12">
            <p>Nach dem Erstellen des Hofmitarbeiters erhält er automatisch eine E-Mail mit seinem Passwort.</p>
          </v-col>
          <v-col cols="12" class="text-center">
            <v-btn @click="save" :disabled="!allValid" color="primary">
              Speichern
              <v-icon right>send</v-icon>
            </v-btn>
          </v-col>
        </v-row>
      </v-container>
    </v-form>
  </div>
</template>

<script>
export default {
  name: 'AddWorker',
  data() {
    return {
      worker: {},
      apiUrl: process.env.VUE_APP_API_URL + 'worker',
      emailRegex: /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/,
      nameRules: [v => !!v || 'Name muss vorhanden sein'],
      emailRules: [
        // A Valid email or emtpy string
        v => this.emailRegex.test(v) || 'Email nicht korrekt'
      ]
    }
  },
  methods: {
    save() {
      this.axios
        .post(this.apiUrl, this.worker)
        .then(response => {
          this.$router.push('/worker')
        })
        .catch(error => {
          if (error.response.data.errors.email && error.response.data.errors.email.includes('validation.unique')) {
            this.$swal('Email existiert bereits', 'Diese Email wurde bereits verwendet', 'error')
          } else {
            this.$swal('Fehler beim Speichern', 'Es ist ein unbekannter Fehler aufgetreten', 'error')
          }
        })
    }
  },
  computed: {
    allValid() {
      if (!this.worker.firstname) return false
      if (!this.worker.lastname) return false
      if (!this.worker.email) return false
      if (!this.emailRegex.test(String(this.worker.email).toLowerCase())) return false
      return true
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

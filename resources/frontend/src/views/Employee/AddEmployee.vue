<template>
  <div>
    <h1 class="text-center my-4">Mitarbeiter erstellen</h1>
    <v-form>
      <v-container>
        <v-row>
          <v-col cols="12" md="6">
            <v-text-field label="Vorname*" v-model="employee.firstname" :rules="nameRules"></v-text-field>
          </v-col>
          <v-col cols="12" md="6">
            <v-text-field label="Nachname*" v-model="employee.lastname" :rules="nameRules"></v-text-field>
          </v-col>
          <v-col cols="12" md="6">
            <v-text-field label="Rufname" v-model="employee.callname"></v-text-field>
          </v-col>
          <v-col cols="12" md="6">
            <v-text-field label="Nationalität" v-model="employee.nationality"></v-text-field>
          </v-col>
          <v-col cols="12" md="6">
            <v-checkbox label="Intern" v-model="employee.isIntern"></v-checkbox>
          </v-col>
          <v-col cols="12" md="6">
            <v-checkbox label="Fahrer" v-model="employee.isDriver"></v-checkbox>
          </v-col>
          <v-col cols="12" md="6">
            <v-checkbox label="Deutschkenntnisse" v-model="employee.german_knowledge"></v-checkbox>
          </v-col>
          <v-col cols="12" md="6">
            <v-checkbox label="Englischkenntnisse" v-model="employee.english_knowledge"></v-checkbox>
          </v-col>
          <v-col cols="12" md="6">
            <v-select :items="genders" label="Geschlecht" v-model="employee.sex"></v-select>
          </v-col>
          <v-col cols="12">
            <v-text-field label="Kommentar" v-model="employee.comment"></v-text-field>
          </v-col>
          <v-col cols="12">
            <v-text-field label="Erfahrung" v-model="employee.experience"></v-text-field>
          </v-col>
          <v-col cols="12">
            <v-text-field label="Allergie" v-model="employee.allergy"></v-text-field>
          </v-col>
          <v-col cols="12">
            <v-btn color="primary left" @click="$refs.profileImage.click()">Profilebild</v-btn>
            <p class="mt-3">{{filename}}</p>
            <input
              class="hidden"
              ref="profileImage"
              accept="image/*"
              type="file"
              @change="profileImageChanged"
            />
          </v-col>
          <v-col cols="12">
            <v-switch v-model="employee.isGuest" label="Gast"></v-switch>
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
  name: 'AddCustomer',
  data() {
    return {
      employee: {
        sex: 'man',
        isGuest: !!this.$route.query.guest
      },
      apiUrl: process.env.VUE_APP_API_URL + 'employee',
      nameRules: [v => !!v || 'Name muss vorhanden sein'],
      genders: [
        {
          value: 'man',
          text: 'Männlich'
        },
        {
          value: 'woman',
          text: 'Weiblich'
        }
      ],
      filename: ''
    }
  },
  methods: {
    save() {
      this.axios
        .post(this.apiUrl, this.employee)
        .then(response => {
          if (this.$refs.profileImage.files.length === 1) {
            let data = new FormData()
            data.append('profileimage', this.$refs.profileImage.files[0])
            this.axios
              .post(this.apiUrl + '/' + response.data + '/editimage', data)
              .then(response => {
                this.redirect()
              })
              .catch(() => {
                this.$swal('Fehler beim Speichern', 'Das Bild konnte nicht hochgeladen werden', 'error')
              })
          } else {
            this.redirect()
          }
        })
        .catch(() => {
          this.$swal('Fehler beim Speichern', 'Es ist ein unbekannter Fehler aufgetreten', 'error')
        })
    },
    profileImageChanged() {
      if (this.$refs.profileImage.files.length === 1) {
        let file = this.$refs.profileImage.files[0]
        this.filename = file.name
      } else {
        this.filename = ''
      }
    },
    redirect() {
      if (this.$route.query.guest) {
        this.$router.push('/guests')
      } else {
        this.$router.push('/employee')
      }
    }
  },
  computed: {
    allValid() {
      if (!this.employee.firstname) return false
      if (!this.employee.lastname) return false
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

.hidden {
  display: none;
}
</style>

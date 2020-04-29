<template>
  <div>
    <h1 class="text-center my-4">
      Mitarbeiter erstellen
    </h1>
    <v-form
      ref="form"
      onsubmit="return false;"
      @submit="save"
    >
      <v-container>
        <v-row>
          <v-col
            cols="12"
            md="6"
          >
            <v-text-field
              v-model="employee.firstname"
              label="Vorname*"
              :rules="[rules.required]"
            ></v-text-field>
          </v-col>
          <v-col
            cols="12"
            md="6"
          >
            <v-text-field
              v-model="employee.lastname"
              label="Nachname*"
              :rules="[rules.required]"
            ></v-text-field>
          </v-col>
          <v-col
            cols="12"
            md="6"
          >
            <v-text-field
              v-model="employee.callname"
              label="Rufname"
            ></v-text-field>
          </v-col>
          <v-col
            cols="12"
            md="6"
          >
            <v-text-field
              v-model="employee.nationality"
              label="Nationalität"
            ></v-text-field>
          </v-col>
          <v-col
            cols="12"
            md="12"
          >
            <v-text-field
              v-model="employee.email"
              label="Email"
              :rules="[rules.nullableEmail]"
            ></v-text-field>
          </v-col>
          <v-col
            cols="12"
            md="6"
          >
            <v-checkbox
              v-model="employee.isLoginActive"
              label="Login aktiviert"
              color="primary"
            ></v-checkbox>
          </v-col>
          <v-col
            cols="12"
            md="6"
          >
            <select-role v-model="employee.role_id"></select-role>
          </v-col>
          <v-col
            cols="12"
            md="6"
          >
            <date-picker
              v-model="employee.entryDate"
              label="Arbeitseintrittsjahr"
              type="year"
            ></date-picker>
          </v-col>
          <v-col
            cols="12"
            md="6"
          >
            <v-checkbox
              v-model="employee.isIntern"
              label="Intern"
            ></v-checkbox>
          </v-col>
          <v-col
            cols="12"
            md="6"
          >
            <v-checkbox
              v-model="employee.drivingLicence"
              label="Führerschein"
            ></v-checkbox>
          </v-col>
          <v-col
            cols="12"
            md="6"
          >
            <v-checkbox
              v-model="employee.isDriver"
              label="Fahrer"
            ></v-checkbox>
          </v-col>
          <v-col
            cols="12"
            md="6"
          >
            <v-checkbox
              v-model="employee.german_knowledge"
              label="Deutschkenntnisse"
            ></v-checkbox>
          </v-col>
          <v-col
            cols="12"
            md="6"
          >
            <v-checkbox
              v-model="employee.english_knowledge"
              label="Englischkenntnisse"
            ></v-checkbox>
          </v-col>
          <v-col
            cols="12"
            md="6"
          >
            <v-select
              v-model="employee.sex"
              :items="genders"
              label="Geschlecht"
            ></v-select>
          </v-col>
          <v-col cols="12">
            <v-text-field
              v-model="employee.comment"
              label="Kommentar"
            ></v-text-field>
          </v-col>
          <v-col cols="12">
            <v-text-field
              v-model="employee.experience"
              label="Erfahrung"
            ></v-text-field>
          </v-col>
          <v-col cols="12">
            <v-text-field
              v-model="employee.allergy"
              label="Allergie"
            ></v-text-field>
          </v-col>
          <v-col cols="12">
            <v-btn
              color="primary left"
              @click="$refs.profileImage.click()"
            >
              Profilebild
            </v-btn>
            <p class="mt-3">
              {{ filename }}
            </p>
            <input
              ref="profileImage"
              class="hidden"
              accept="image/*"
              type="file"
              @change="profileImageChanged"
            />
          </v-col>
          <v-col cols="12">
            <v-switch
              v-model="employee.isGuest"
              label="Gast"
            ></v-switch>
          </v-col>
          <v-col
            cols="12"
            class="text-center"
          >
            <v-btn
              type="submit"
              color="primary"
            >
              Speichern
              <v-icon right>
                send
              </v-icon>
            </v-btn>
          </v-col>
        </v-row>
      </v-container>
    </v-form>
  </div>
</template>

<script>
import { rules } from '@/utils'
import SelectRole from '@/components/Authorization/SelectRole'
import DatePicker from '@/components/general/DatePicker'

export default {
  name: 'AddCustomer',
  components: {
    SelectRole,
    DatePicker
  },
  data() {
    return {
      employee: {
        sex: 'man',
        isGuest: !!this.$route.query.guest
      },
      apiUrl: `${process.env.VUE_APP_API_URL}employee`,
      rules,
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
  computed: {
    allValid() {
      if (!this.employee.firstname) return false
      if (!this.employee.lastname) return false
      return true
    }
  },
  methods: {
    save() {
      if (this.$refs.form.validate()) {
        this.axios
          .post(this.apiUrl, this.employee)
          .then((response) => {
            if (this.$refs.profileImage.files.length === 1) {
              const data = new FormData()
              data.append('profileimage', this.$refs.profileImage.files[0])
              this.axios
                .post(`${this.apiUrl}/${response.data}/editimage`, data)
                .then(() => {
                  this.redirect()
                })
                .catch(() => {
                  this.$swal('Fehler beim Speichern', 'Das Bild konnte nicht hochgeladen werden', 'error')
                })
            } else {
              this.redirect()
            }
          })
          .catch((error) => {
            if (error.includes('validation.unique')) {
              this.$swal('Email existiert bereits', 'Es existiert bereits ein anderer User mit der selben Email', 'error')
            } else {
              this.$swal('Fehler beim Speichern', 'Es ist ein unbekannter Fehler aufgetreten', 'error')
            }
          })
      } else {
        this.$store.dispatch('alert', { text: 'Bitte fülle alle Felder korrekt aus', type: 'error' })
      }
    },
    profileImageChanged() {
      if (this.$refs.profileImage.files.length === 1) {
        const file = this.$refs.profileImage.files[0]
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

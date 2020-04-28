<template>
  <v-container>
    <v-form ref="form">
      <v-row>
        <v-col cols="12" md="6" class="my-0">
          <v-row>
            <v-col cols="12" sm="4" class="py-0">
              <p class="font-weight-bold subheading description mb-0">Vorname</p>
            </v-col>
            <v-col cols="12" sm="8" class="py-0">
              <edit-field
                v-model="employee.firstname"
                @change="changed"
                :readonly="!isUserAllowedToEdit"
              ></edit-field>
            </v-col>
            <v-col cols="12" sm="4" class="py-0">
              <p class="font-weight-bold subheading description mb-0">Nachname</p>
            </v-col>
            <v-col cols="12" sm="8" class="py-0">
              <edit-field
                v-model="employee.lastname"
                @change="changed"
                :readonly="!isUserAllowedToEdit"
              ></edit-field>
            </v-col>
            <v-col cols="12" sm="4" class="py-0">
              <p class="font-weight-bold subheading description mb-0">Rufname</p>
            </v-col>
            <v-col cols="12" sm="8" class="py-0">
              <edit-field
                v-model="employee.callname"
                @change="changed"
                :readonly="!isUserAllowedToEdit"
              ></edit-field>
            </v-col>
            <v-col cols="12" sm="4" class="py-0">
              <p class="font-weight-bold subheading description mb-0">Nationalität</p>
            </v-col>
            <v-col cols="12" sm="8" class="py-0">
              <edit-field
                v-model="employee.nationality"
                @change="changed"
                :readonly="!isUserAllowedToEdit"
              ></edit-field>
            </v-col>
          </v-row>
        </v-col>
        <v-col cols="12" md="6">
          <div v-if="employee.profileimage">
            <img :src="backendUrl + 'profileimages/'+ employee.profileimage" class="profileimage" />
            <p
              class="image-buttons"
              v-if="$auth.user().hasPermission(['superadmin'], ['employee_write'])"
            >
              <v-btn @click="$refs.profileImage.click()" color="primary">
                Bild ändern
                <v-icon right>edit</v-icon>
              </v-btn>
              <v-btn @click="deleteImage" color="primary" class="ml-2">
                Bild entfernen
                <v-icon right>delete</v-icon>
              </v-btn>
            </p>
          </div>
          <div v-if="!employee.profileimage">
            <div class="new-image">
              <v-btn
                color="primary"
                @click="$refs.profileImage.click()"
                v-if="$auth.user().hasPermission(['superadmin'], ['employee_write'])"
              >Bild hinzufügen</v-btn>
            </div>
          </div>
          <input
            type="file"
            ref="profileImage"
            @change="uploadImage"
            class="hidden"
            accept="image/*"
          />
        </v-col>
        <v-col cols="12">
          <input-field
            v-model="employee.email"
            label="Email"
            @change="changed"
            :readonly="!isUserAllowedToEdit"
            long
            :rules="[rules.nullableEmail]"
          ></input-field>
        </v-col>
        <v-col cols="12" md="6">
          <v-checkbox
            label="Login aktiviert"
            v-model="employee.isLoginActive"
            @change="changed"
            color="primary"
            :readonly="!isUserAllowedToEdit"
          ></v-checkbox>
        </v-col>
        <v-col cols="12" md="6">
          <select-role
            v-model="employee.user.role_id"
            @change="changed"
            :readonly="!isUserAllowedToEdit"
          ></select-role>
        </v-col>
        <v-col cols="12" md="6">
          <input-field label="Arbeitseintrittsjahr">
            <date-picker v-model="employee.entryDate" @input="changed" type="year"></date-picker>
          </input-field>
        </v-col>

        <v-col cols="12" md="6">
          <v-checkbox
            label="Intern"
            v-model="employee.isIntern"
            @change="changed"
            color="primary"
            :readonly="!isUserAllowedToEdit"
          ></v-checkbox>
        </v-col>
        <v-col cols="12" md="6">
          <v-checkbox
            label="Führerschein"
            v-model="employee.drivingLicence"
            @change="changed"
            color="primary"
            :readonly="!isUserAllowedToEdit"
          ></v-checkbox>
        </v-col>
        <v-col cols="12" md="6">
          <v-checkbox
            label="Fahrer"
            v-model="employee.isDriver"
            @change="changed"
            color="primary"
            :readonly="!isUserAllowedToEdit"
          ></v-checkbox>
        </v-col>
        <v-col cols="12" md="6">
          <v-checkbox
            label="Deutschkenntnisse"
            v-model="employee.german_knowledge"
            @change="changed"
            color="primary"
            :readonly="!isUserAllowedToEdit"
          ></v-checkbox>
        </v-col>
        <v-col cols="12" md="6">
          <v-checkbox
            label="Englischkenntnisse"
            v-model="employee.english_knowledge"
            @change="changed"
            color="primary"
            :readonly="!isUserAllowedToEdit"
          ></v-checkbox>
        </v-col>
        <v-col cols="12" md="6">
          <v-select
            :items="genders"
            label="Geschlecht"
            v-model="employee.sex"
            @change="changed"
            :readonly="!isUserAllowedToEdit"
          ></v-select>
        </v-col>
        <v-col cols="12">
          <v-row>
            <v-col cols="12" sm="4" md="2">
              <p class="font-weight-bold subheading description mb-0">Kommentar</p>
            </v-col>
            <v-col cols="12" sm="8" md="10">
              <edit-field
                v-model="employee.comment"
                @change="changed"
                :readonly="!isUserAllowedToEdit"
              ></edit-field>
            </v-col>
          </v-row>
        </v-col>
        <v-col cols="12" sm="4" md="2">
          <p class="font-weight-bold subheading description mb-0">Erfahrung</p>
        </v-col>
        <v-col cols="12" sm="8" md="10">
          <edit-field
            v-model="employee.experience"
            @change="changed"
            :readonly="!isUserAllowedToEdit"
          ></edit-field>
        </v-col>
        <v-col cols="12" sm="4" md="2">
          <p class="font-weight-bold subheading description mb-0">Allergie</p>
        </v-col>
        <v-col cols="12" sm="8" md="10">
          <edit-field v-model="employee.allergy" @change="changed" :readonly="!isUserAllowedToEdit"></edit-field>
        </v-col>
        <v-col cols="12">
          <v-checkbox
            label="Aktiv"
            v-model="employee.isActive"
            @change="changed"
            color="primary"
            :readonly="!isUserAllowedToEdit"
          ></v-checkbox>
        </v-col>
        <v-col cols="12">
          <v-checkbox
            label="Gast"
            v-model="employee.isGuest"
            @change="changed"
            color="primary"
            :readonly="!$auth.user().hasPermission(['superadmin'], ['employee_write'])"
          ></v-checkbox>
        </v-col>
        <v-col
          cols="12"
          class="text-center"
          v-if="$auth.user().hasPermission(['superadmin'], ['employee_write'])"
        >
          <v-btn color="red white--text my-4" @click="deleteEmployee">
            Löschen
            <v-icon right>delete</v-icon>
          </v-btn>
        </v-col>
      </v-row>
    </v-form>
  </v-container>
</template>

<script>
import { rules } from '@/utils'
import InputField from '@/components/general/InputField'
import SelectRole from '@/components/Authorization/SelectRole'
import DatePicker from '@/components/general/DatePicker'

export default {
  components: {
    InputField,
    SelectRole,
    DatePicker
  },
  data() {
    return {
      employee: {
        user: {}
      },
      apiUrl: process.env.VUE_APP_API_URL + 'employee/' + this.$route.params.id,
      backendUrl: process.env.VUE_APP_URL,
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
      isUserAllowedToEdit: false,
      rules
    }
  },
  mounted() {
    this.$store.commit('isLoading', true)
    this.axios.get(this.apiUrl).then(response => {
      this.employee = response.data
      this.$store.commit('isLoading', false)
      if (this.employee.isGuest) {
        this.isUserAllowedToEdit = this.$auth.user().hasPermission(['superadmin'], ['employee_write', 'roomdispositioner_write'])
      }
    })
    this.isUserAllowedToEdit = this.$auth.user().hasPermission(['superadmin'], ['employee_write'])
  },
  methods: {
    changed() {
      if (this.$refs.form.validate()) {
        this.axios.put(this.apiUrl, this.employee).catch(error => {
          if (error.includes('Email already exist')) {
            this.$store.dispatch('alert', { text: 'Email existiert bereits', type: 'error', duration: 6 })
          } else {
            this.$store.dispatch('alert', { text: 'Fehler beim Speichern', type: 'error' })
          }
        })
      }
    },
    deleteEmployee() {
      this.axios.delete(this.apiUrl).then(() => {
        this.$store.dispatch('resetEmployees')
        if (this.employee.isGuest) this.$router.push('/guests')
        else this.$router.push('/employee')
      })
    },
    uploadImage() {
      if (this.$refs.profileImage.files.length === 1) {
        let data = new FormData()
        data.append('profileimage', this.$refs.profileImage.files[0])
        this.axios
          .post(this.apiUrl + '/editimage', data)
          .then(response => {
            this.employee.profileimage = response.data
          })
          .catch(() => {
            this.$swal('Fehler beim hochladen', '', 'error')
          })
      }
    },
    deleteImage() {
      this.axios
        .delete(this.apiUrl + '/editimage')
        .then(() => {
          this.employee.profileimage = null
        })
        .catch(() => {
          this.$swal('Fehler', '', 'error')
        })
    }
  }
}
</script>

<style lang="scss" scoped>
.single-employee {
  background-color: white;
  margin-top: 50px;
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
}

.new-image {
  text-align: center;
  margin-top: 100px;
}

.profileimage {
  max-width: 100%;
  max-height: 240px;
  position: relative;
  display: block;
  margin: 0 auto 10px auto;
}

.image-buttons {
  text-align: center;

  a {
    margin-left: 10px;
    margin-right: 10px;
  }
}

.hidden {
  display: none;
}

@media only screen and (max-width: 600px) {
  .container {
    padding: 0;
  }

  .single-employee {
    margin-top: 0;
    background-color: transparent;
    box-shadow: none;
  }
}
</style>

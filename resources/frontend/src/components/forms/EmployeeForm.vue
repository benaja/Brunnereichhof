<template>
  <v-form
    ref="form"
    @keyup.native.enter="$emit('submit')"
  >
    <v-row no-gutters>
      <v-col
        cols="12"
        md="6"
        class="my-0"
      >
        <text-field
          v-model="value.firstname"
          label="Vorname*"
          :original="original.firstname"
          :rules="[rules.required]"
          :readonly="readonly"
          @change="$emit('change', 'firstname')"
        ></text-field>
        <text-field
          v-model="value.lastname"
          label="Nachname*"
          :rules="[rules.required]"
          :original="original.lastname"
          :readonly="readonly"
          @change="$emit('change', 'lastname')"
        ></text-field>
        <text-field
          v-model="value.callname"
          label="Rufname"
          :readonly="readonly"
          :original="original.callname"
          @change="$emit('change', 'callname')"
        ></text-field>
        <text-field
          v-model="value.nationality"
          label="Nationalität"
          :readonly="readonly"
          :original="original.nationality"
          @change="$emit('change', 'nationality')"
        ></text-field>
      </v-col>
      <v-col
        cols="12"
        md="6"
      >
        <select-images
          v-model="value.profileimage"
          :upload-on-change="saveOnChange"
          :upload-url="`/employees/${$route.params.id}/profileimage`"
          :disabled="readonly"
          single-file
          :delete="deleteImage"
        ></select-images>
      </v-col>
      <v-col
        cols="12"
        md="6"
      >
        <text-field
          v-model="value.email"
          :original="original.email"
          :rules="[rules.nullableEmail]"
          label="Email"
          :readonly="readonly"
          @change="$emit('change', 'email')"
        ></text-field>
      </v-col>

      <v-col
        cols="12"
        md="6"
      >
        <date-field
          v-model="value.entryDate"
          :original="original.entryDate"
          label="Arbeitseintrittsjahr"
          type="year"
          @input="$emit('change', 'entryDate')"
        ></date-field>
      </v-col>

      <v-col
        cols="12"
        md="6"
      >
        <v-checkbox
          v-model="value.isIntern"
          label="Intern"
          color="primary"
          :readonly="readonly"
          @change="$emit('change', 'isIntern')"
        ></v-checkbox>
      </v-col>
      <v-col
        cols="12"
        md="6"
      >
        <v-checkbox
          v-model="value.drivingLicence"
          label="Führerschein"
          color="primary"
          :readonly="readonly"
          @change="$emit('change', 'drivingLicence')"
        ></v-checkbox>
      </v-col>
      <v-col
        cols="12"
        md="6"
      >
        <v-checkbox
          v-model="value.isDriver"
          label="Fahrer"
          color="primary"
          :readonly="readonly"
          @change="$emit('change', 'isDriver')"
        ></v-checkbox>
      </v-col>
      <v-col
        cols="12"
        md="6"
      >
        <select-field
          v-model="value.languages"
          :items="languages"
          :original="original.languages"
          item-value="id"
          item-text="name"
          label="Sprachkenntnisse"
          multiple
          @change="$emit('change', 'languages')"
        ></select-field>
      </v-col>
      <v-col
        cols="12"
        md="6"
      >
        <v-checkbox
          v-model="value.resource_planner_white_listed"
          label="Mehrfache Zuteilung bei Einsatzplaner"
          color="primary"
          :readonly="readonly"
          @change="$emit('change', 'resource_planner_white_listed')"
        ></v-checkbox>
      </v-col>
      <v-col
        cols="12"
        md="6"
      >
        <select-field
          v-model="value.function"
          :original="original.function"
          :items="employeeFunctions"
          label="Funktion"
          :readonly="readonly"
          @change="$emit('change', 'function')"
        ></select-field>
      </v-col>
      <v-col
        cols="12"
        md="6"
      >
        <select-field
          v-model="value.sex"
          :original="original.sex"
          :items="genders"
          label="Geschlecht"
          :readonly="readonly"
          @change="$emit('change', 'sex')"
        ></select-field>
      </v-col>
      <v-col cols="12">
        <text-field
          v-model="value.comment"
          :original="original.comment"
          label="Kommentar"
          :readonly="readonly"
          @change="$emit('change', 'comment')"
        ></text-field>
      </v-col>
      <v-col
        cols="12"
      >
        <text-field
          v-model="value.experience"
          :original="original.experience"
          label="Erfahrung"
          :readonly="readonly"
          @change="$emit('change', 'experience')"
        ></text-field>
      </v-col>
      <v-col
        cols="12"
      >
        <text-field
          v-model="value.allergy"
          :original="original.allergy"
          label="Allergie"
          :readonly="readonly"
          @change="$emit('change', 'allergy')"
        ></text-field>
      </v-col>
      <v-col
        cols="12"
        md="6"
      >
        <v-checkbox
          v-model="value.isLoginActive"
          label="Login aktiviert"
          color="primary"
          :readonly="readonly"
          @change="$emit('change', 'isLoginActive')"
        ></v-checkbox>
      </v-col>
      <v-col
        cols="12"
        md="6"
      >
        <select-role
          v-model="value.user.role_id"
          :original="original.user.role_id"
          :readonly="readonly"
          @change="$emit('change', 'user.role_id')"
        ></select-role>
      </v-col>
      <v-col
        cols="12"
        md="6"
      >
        <v-checkbox
          v-model="value.isActive"
          label="Aktiv"
          color="primary"
          :readonly="readonly"
          @change="$emit('change', 'isActive')"
        ></v-checkbox>
      </v-col>
      <v-col
        cols="12"
        md="6"
      >
        <v-checkbox
          v-model="value.isGuest"
          label="Gast"
          color="primary"
          :readonly="readonly"
          @change="$emit('change', 'isGuest')"
        ></v-checkbox>
      </v-col>
    </v-row>
  </v-form>
</template>

<script>
import { TextField, SelectField, DateField } from '@/components/FormComponents'
import SelectRole from '@/components/Authorization/SelectRole'
import { rules, employeeFunctions } from '@/utils'
import SelectImages from '@/components/Roomdispositioner/Room/SelectImages'

export default {
  components: {
    TextField,
    SelectRole,
    SelectField,
    DateField,
    SelectImages
  },
  props: {
    value: {
      type: Object,
      default: () => ({})
    },
    original: {
      type: Object,
      default: () => ({
        user: {}
      })
    },
    readonly: {
      type: Boolean,
      default: false
    },
    loadingImage: {
      type: Boolean,
      default: false
    },
    loadingDeleteImage: {
      type: Boolean,
      default: false
    },
    saveOnChange: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      rules,
      employeeFunctions,
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
      backendUrl: process.env.VUE_APP_URL,
      languages: []
    }
  },

  async mounted() {
    const { data } = await this.axios.$get('languages')
    this.languages = data

    console.log(this.original.languages)
  },

  methods: {
    validate() {
      return this.$refs.form.validate()
    },
    deleteImage() {
      return this.axios.delete(`employees/${this.value.id}/profileimage`)
    }
  }
}
</script>

<style lang="scss" scoped>
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
</style>

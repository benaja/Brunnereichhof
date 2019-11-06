<template>
  <v-menu :close-on-content-click="false" :nudge-width="300" offset-x v-model="isOpen">
    <template v-slot:activator="{ on }">
      <v-btn text v-on="on" color="primary">Neue Rolle Erstellen</v-btn>
    </template>
    <v-card>
      <v-card-title class="headline">Rolle erstellen</v-card-title>
      <v-card-text>
        <v-form ref="form">
          <edit-field label="Name" v-model="role.name" :rules="[rules.required]"></edit-field>
          <div class="scroll-container">
            <v-checkbox
              v-for="authorizationRule of authorizationRules"
              v-model="role.authorizationRules"
              :value="authorizationRule.id"
              :label="authorizationRule.name_de"
              :key="authorizationRule.id"
              class="ma-0"
              height="10"
            ></v-checkbox>
          </div>
        </v-form>
      </v-card-text>
      <v-card-actions>
        <v-spacer />
        <v-btn color="primary" @click="saveRole">Speicher</v-btn>
      </v-card-actions>
    </v-card>
  </v-menu>
</template>

<script>
export default {
  name: 'CreateRole',
  props: {
    value: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      authorizationRules: [],
      role: {
        name: null,
        authorizationRules: []
      },
      name: null,
      rules: {
        required: v => !!v || 'Dieses Feld muss vorhanden sein',
        minLenghtOne: v => v.length > 0 || 'Wähle mindestens ein Elment aus'
      }
    }
  },
  computed: {
    isOpen: {
      get: function() {
        return this.value
      },
      set: function(value) {
        this.$emit('input', value)
      }
    }
  },
  mounted() {
    this.$store
      .dispatch('authorizationRules')
      .then(authorizationRules => {
        this.authorizationRules = authorizationRules
      })
      .catch(() => {
        this.$swal('Fehler', 'Berechtigungen konnten nich abgerufen werden', 'error')
      })
  },
  methods: {
    saveRole() {
      if (this.$refs.form.validate()) {
        this.axios
          .post('roles', this.role)
          .then(response => {
            this.$emit('addRole', response.data)
            this.isOpen = false
            this.$refs.form.reset()
          })
          .catch(() => {
            this.$swal('Fehler', 'Es ist ein unerwarteter Fehler beim Speichern aufgetreten', 'error')
          })
      }
    }
  }
}
</script>

<style lang="scss" scoped>
.scroll-container {
  max-height: 400px;
  overflow-y: scroll;
}
</style>

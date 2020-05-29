<template>
  <fragment>
    <navigation-bar
      title="Rolle bearbeiten"
      :loading="isLoading || $store.getters.isLoading.authorizationRules"
    ></navigation-bar>
    <v-container v-if="!isLoading && !$store.getters.isLoading.authorizationRules">
      <edit-field
        v-model="role.name"
        label="name"
        @input="updateRole"
      ></edit-field>
      <v-checkbox
        v-for="authorizationRule of authorizationRules"
        :key="authorizationRule.id"
        v-model="role.selectedAuthorizationRules"
        :value="authorizationRule.id"
        :label="authorizationRule.name_de"
        class="ma-0"
        height="10"
        @change="updateRole"
      ></v-checkbox>
      <v-btn
        color="red"
        class="white--text"
        depressed
        @click="deleteRole"
      >
        Rolle Löschen
      </v-btn>
    </v-container>
  </fragment>
</template>

<script>
import { confirmAction } from '@/utils'
import { mapGetters } from 'vuex'
import { debounce } from 'lodash'

export default {
  data() {
    return {
      role: {},
      isLoading: false
    }
  },
  computed: {
    ...mapGetters(['authorizationRules'])
  },
  mounted() {
    this.$store.dispatch('fetchAuthorizationRules').then(() => {
      this.isLoading = true
      this.axios
        .get(`roles/${this.$route.params.id}`)
        .then(response => {
          response.data.selectedAuthorizationRules = response.data.authorization_rules
            .map(r => r.id)

          this.role = response.data
        })
        .catch(() => {
          this.$swal('Fehler', 'Rolle konnte nicht abgerufen werden', 'error')
        }).finally(() => {
          this.isLoading = false
        })
    })
  },
  methods: {
    updateRole: debounce(function() {
      this.$store.commit('isSaving', true)
      this.$store.dispatch('updateRole', this.role).catch(() => {
        this.$swal('Fehler', 'Die Änderungen konnten nicht gespeichert werden', 'error')
      }).finally(() => {
        this.$store.commit('isSaving', false)
      })
    }, 400),
    deleteRole() {
      confirmAction().then(result => {
        if (result) {
          this.$store
            .dispatch('deleteRole', this.role.id)
            .then(() => this.$router.push('/roles'))
            .catch(error => {
              if (error.includes('Role still has one or more users')) {
                this.$swal('Rolle besitzt noch Benutzer', 'Stelle sicher, dass keine Benutzer diese Rolle besitzt, damit du sie löschen kannst.', 'error')
              } else {
                this.$swal('Fehler', 'Die Rolle konnte nicht gelöscht werden', 'error')
              }
            })
        }
      })
    }
  }
}
</script>

<template>
  <fragment>
    <navigation-bar
      title="Rollen"
      :loading="$store.getters.isLoading.roles"
    ></navigation-bar>
    <v-container>
      <v-list class="pa-0">
        <template v-for="role of roles">
          <v-list-item
            :key="role.id"
            :to="`/roles/${role.id}`"
          >
            <v-list-item-content>{{ role.name }}</v-list-item-content>
          </v-list-item>

          <v-divider :key="'divider-' + role.id"></v-divider>
        </template>
      </v-list>
      <CreateRole v-model="isCreateRolePopupOpen">
        <v-btn
          slot="activator"
          fab
          color="primary"
          class="add-button"
        >
          <v-icon>add</v-icon>
        </v-btn>
      </CreateRole>
    </v-container>
  </fragment>
</template>

<script>
import CreateRole from '@/components/Authorization/CreateRole'
import { mapGetters } from 'vuex'

export default {
  components: {
    CreateRole
  },
  data() {
    return {
      isCreateRolePopupOpen: false
    }
  },
  computed: {
    ...mapGetters(['roles'])
  },
  mounted() {
    this.$store
      .dispatch('fetchRoles')
      .catch(() => {
        this.$swal('Fehler', 'Rollen konnten nicht emfangen werden', 'error')
      })
  }
}
</script>

<style lang="scss" scoped>
.width-100 {
  width: 100%;
}

.add-button {
  position: fixed;
  bottom: 10px;
  right: 10px;
}
</style>

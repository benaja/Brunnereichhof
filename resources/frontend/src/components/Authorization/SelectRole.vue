<template>
  <div>
    <Select
      v-model="role"
      :items="roles"
      label="Rolle"
      item-text="name"
      item-value="id"
      :readonly="readonly"
      @change="$emit('change')"
    ></Select>
    <CreateRole
      v-if="$auth.user().hasPermission(['superadmin'], ['role_write'])"
      v-model="isCreateRolePopupOpen"
      @addRole="addRole"
    >
      <v-btn
        slot="activator"
        text
        color="primary"
      >
        Neue Rolle erstellen
      </v-btn>
    </CreateRole>
  </div>
</template>

<script>
import Select from '@/components/general/Select'
import CreateRole from '@/components/Authorization/CreateRole'
import { mapGetters } from 'vuex'

export default {
  name: 'SelectRole',
  components: {
    Select,
    CreateRole
  },
  props: {
    value: {
      type: Number,
      default: null
    },
    readonly: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      isCreateRolePopupOpen: false,
      isDetailPopupOpen: false
    }
  },
  computed: {
    ...mapGetters(['roles']),
    role: {
      get() {
        return this.value
      },
      set(value) {
        this.$emit('input', value)
      }
    }
  },
  mounted() {
    this.$store.dispatch('fetchRoles')
  },
  methods: {
    addRole(role) {
      this.roles.push(role)
      this.role = role.id
      this.$emit('change')
    }
  }
}
</script>

<style lang="scss" scoped>
.info-button {
  margin-top: 30px;
}
</style>

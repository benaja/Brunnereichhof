<template>
  <div>
    <select-field
      v-model="role"
      :items="roles"
      :original="original"
      label="Rolle"
      item-text="name"
      item-value="id"
      :readonly="readonly"
      @change="$emit('change')"
    ></select-field>
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
import { SelectField } from '@/components/FormComponents'
import CreateRole from '@/components/Authorization/CreateRole'
import { mapGetters } from 'vuex'

export default {
  name: 'SelectRole',
  components: {
    SelectField,
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
    },
    original: {
      type: Number,
      default: null
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

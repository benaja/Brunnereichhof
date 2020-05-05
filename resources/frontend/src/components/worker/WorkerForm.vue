<template>
  <v-form
    ref="form"
    @keyup.native.enter="$emit('submit')"
  >
    <v-row>
      <v-col
        cols="12"
        sm="6"
      >
        <text-field
          v-model="value.firstname"
          :original="original.firstname"
          :readonly="readonly"
          label="Vorname*"
          :rules="[rules.required]"
          @change="$emit('change','firstname')"
        ></text-field>
      </v-col>
      <v-col
        cols="12"
        sm="6"
      >
        <text-field
          v-model="value.lastname"
          :original="original.lastname"
          :readonly="readonly"
          label="Nachname*"
          :rules="[rules.required]"
          @change="$emit('change', 'lastname')"
        ></text-field>
      </v-col>
      <v-col
        cols="12"
        sm="6"
      >
        <text-field
          v-model="value.email"
          :original="original.email"
          :readonly="readonly"
          label="Email*"
          :rules="[rules.required, rules.email]"
          @change="$emit('change', 'email')"
        ></text-field>
      </v-col>
      <v-col
        cols="12"
        sm="6"
      >
        <select-role
          v-model="value.role_id"
          :original="original.role_id"
          :readonly="readonly"
          :rules="[rules.required]"
          @change="$emit('change', 'role_id')"
        ></select-role>
      </v-col>
      <v-col cols="12">
        <v-switch
          v-model="value.isActive"
          :readonly="readonly"
          label="Aktiv"
          @change="$emit('change', 'isActive')"
        ></v-switch>
      </v-col>
    </v-row>
  </v-form>
</template>

<script>
import SelectRole from '@/components/Authorization/SelectRole'
import { TextField } from '@/components/FormComponents'
import { rules } from '@/utils'

export default {
  components: {
    SelectRole,
    TextField
  },
  props: {
    value: {
      type: Object,
      required: true
    },
    original: {
      type: Object,
      default: () => ({})
    },
    readonly: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      rules
    }
  },
  methods: {
    validate() {
      return this.$refs.form.validate()
    }
  }
}
</script>

<template>
  <v-form
    ref="form"
    @keyup.native.enter="$emit('submit')"
  >
    <v-row
      v-if="value"
      :no-gutters="fullWidth"
    >
      <v-col
        cols="12"
        :md="fullWidth ? 12 : 6"
      >
        <text-field
          v-model="value.name"
          label="Name*"
          color="blue"
          :original="original.name"
          :rules="[rules.required]"
          :readonly="readonly"
          @change="$emit('change', 'name')"
        ></text-field>
      </v-col>
      <v-col
        cols="12"
        :md="fullWidth ? 12 : 6"
      >
        <text-field
          v-model="value.width"
          label="Breite*"
          color="blue"
          :original="original.width"
          :rules="[rules.required]"
          :readonly="readonly"
          @change="$emit('change', 'width')"
        ></text-field>
      </v-col>
      <v-col
        cols="12"
        :md="fullWidth ? 12 : 6"
      >
        <text-field
          v-model="value.places"
          label="Plätze*"
          color="blue"
          :original="original.places"
          :rules="[rules.required]"
          :readonly="readonly"
          type="number"
          @change="$emit('change', 'places')"
        ></text-field>
      </v-col>
      <v-col
        cols="12"
      >
        <text-field
          v-model="value.comment"
          label="Kommentar"
          color="blue"
          :original="original.comment"
          :readonly="readonly"
          @change="$emit('change', 'comment')"
        ></text-field>
      </v-col>
      <v-col
        v-if="!readonly"
        cols="12"
      >
        <select-inventar
          v-model="value.inventars"
          :bed="value"
        ></select-inventar>
      </v-col>
    </v-row>
  </v-form>
</template>

<script>
import { TextField } from '@/components/FormComponents'
import { rules } from '@/utils'
import SelectInventar from '@/components/Roomdispositioner/Inventar/SelectInventar'

export default {
  components: {
    TextField,
    SelectInventar
  },
  props: {
    value: {
      type: Object,
      default: null
    },
    original: {
      type: Object,
      default: () => ({})
    },
    readonly: {
      type: Boolean,
      default: false
    },
    fullWidth: {
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
    },
    reset() {
      return this.$refs.form.reset()
    }
  }
}
</script>

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
          v-model="value.number"
          label="Nummer*"
          color="blue"
          type="number"
          :original="original.number"
          :rules="[rules.required]"
          :readonly="readonly"
          @change="$emit('change', 'number')"
        ></text-field>
      </v-col>
      <v-col
        cols="12"
        :md="fullWidth ? 12 : 6"
      >
        <text-field
          v-model="value.location"
          label="Standort*"
          color="blue"
          :original="original.location"
          :rules="[rules.required]"
          :readonly="readonly"
          @change="$emit('change', 'location')"
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
      <v-col cols="12">
        <v-checkbox
          v-model="value.isActive"
          label="Aktiv"
          color="blue"
          :false-value="0"
          :true-value="1"
          :readonly="readonly"
          @change="$emit('change', 'isActive')"
        ></v-checkbox>
      </v-col>
      <v-col
        v-if="!readonly"
        cols="12"
      >
        <select-bed
          v-model="value.beds"
          :disabled="readonly"
        ></select-bed>
      </v-col>
    </v-row>
  </v-form>
</template>

<script>
import { TextField } from '@/components/FormComponents'
import { rules } from '@/utils'
import SelectBed from '@/components/Roomdispositioner/Bed/SelectBed'

export default {
  components: {
    TextField,
    SelectBed
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

<template>
  <v-form
    ref="form"
    @keyup.native.enter="$emit('submit')"
  >
    <v-row>
      <v-col
        cols="12"
        md="6"
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
        md="6"
      >
        <text-field
          v-model="value.price"
          label="Preis in CHF*"
          color="blue"
          type="number"
          :rules="[rules.required]"
          :original="original.price"
          :readonly="readonly"
          @change="$emit('change', 'price')"
        ></text-field>
      </v-col>
    </v-row>
  </v-form>
</template>

<script>
import { TextField } from '@/components/FormComponents'
import { rules } from '@/utils'

export default {
  components: {
    TextField
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

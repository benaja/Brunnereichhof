<template>
  <v-form
    ref="form"
    @keyup.native.enter="$emit('submit')"
  >
    <text-field
      v-model="value.name"
      label="Name*"
      :original="original.name"
      :rules="[rules.required]"
      :readonly="readonly"
      @change="$emit('change', 'name')"
    ></text-field>
    <v-checkbox
      v-model="value.is_positive"
      label="Positiver betrag"
      :false-value="0"
      :true-value="1"
      :readonly="readonly"
      @change="$emit('change', 'is_positive')"
    ></v-checkbox>
  </v-form>
</template>

<script>
import { rules } from '@/utils'
import { TextField } from '@/components/FormComponents'


export default {
  components: {
    TextField
  },
  props: {
    value: {
      type: Object,
      default: () => ({})
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

<style>

</style>

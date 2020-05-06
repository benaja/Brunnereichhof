<template>
  <base-input
    :restore-message="computedRestoreMessage"
    :label="label"
    @restore="restoreOriginal"
  >
    <date-picker
      v-bind="$attrs"
      :value="value"
      @input="input"
    ></date-picker>
  </base-input>
</template>

<script>
import DatePicker from '@/components/general/DatePicker'
import BaseInput from './_BaseInput'
import InputMixin from './_InputMixin'

export default {
  components: {
    DatePicker,
    BaseInput
  },
  mixins: [InputMixin],
  props: {
    value: {
      type: [String, Number],
      default: null
    },
    label: {
      type: String,
      default: null
    }
  }
}
</script>

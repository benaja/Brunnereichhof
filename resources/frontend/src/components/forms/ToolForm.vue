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
          :label="`${$t('Name')}*`"
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
          v-model="value.amount"
          :label="`${$t('Menge')}*`"
          type="number"
          :rules="[rules.required]"
          :original="original.amount"
          :readonly="readonly"
          @change="$emit('change', 'amount')"
        ></text-field>
      </v-col>
      <v-col
        cols="12"
        md="6"
      >
        <select-images
          v-model="value.image"
          :image-url="value.image_url"
          single-file
        ></select-images>
      </v-col>
    </v-row>
  </v-form>
</template>

<script>
import { TextField } from '@/components/FormComponents'
import { rules } from '@/utils'
import SelectImages from '@/components/Roomdispositioner/Room/SelectImages'

export default {
  components: {
    TextField,
    SelectImages
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
      this.value.image = null
      return this.$refs.form.reset()
    }
  }
}
</script>

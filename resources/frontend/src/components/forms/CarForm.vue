<template>
  <v-form
    v-if="value"
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
          v-model="value.seats"
          :label="`${$t('Sitzplätze')}*`"
          type="number"
          :rules="[rules.required]"
          :original="original.seats"
          :readonly="readonly"
          @change="$emit('change', 'seats')"
        ></text-field>
      </v-col>
      <v-col
        cols="12"
        md="6"
      >
        <text-field
          v-model="value.number"
          :label="`${$t('BE-Nummer')}`"
          :original="original.number"
          :readonly="readonly"
          @change="$emit('change', 'number')"
        ></text-field>
      </v-col>
      <v-col
        cols="12"
        md="6"
      >
        <select-field
          v-model="value.fuel"
          :label="`${$t('Benzin')}*`"
          :original="original.fuel"
          :rules="[rules.required]"
          :readonly="readonly"
          :items="fuelTypes"
          @change="$emit('change', 'fuel')"
        ></select-field>
      </v-col>
      <v-col cols="12">
        <text-area
          v-model="value.comment"
          :label="`${$t('Kommentar')}`"
          :original="original.comment"
          :readonly="readonly"
          @change="$emit('change', 'comment')"
        ></text-area>
      </v-col>
      <v-col cols="12">
        <text-area
          v-model="value.important_comment"
          :label="`${$t('Wichtige Bemerkung')}`"
          :original="original.important_comment"
          :readonly="readonly"
          @change="$emit('change', 'important_comment')"
        ></text-area>
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
import { TextField, TextArea, SelectField } from '@/components/FormComponents'
import { rules } from '@/utils'
import SelectImages from '@/components/Roomdispositioner/Room/SelectImages'

export default {
  components: {
    TextField,
    TextArea,
    SelectField,
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
      rules,
      fuelTypes: [
        {
          value: 'gas',
          text: this.$t('Benzin')
        },
        {
          value: 'diesel',
          text: this.$t('Diesel')
        }
      ]
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

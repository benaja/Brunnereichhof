<template>
  <!-- <v-form
    ref="form"
    @keyup.native.enter="$emit('submit')"
  > -->
  <tr v-if="loaded">
    <td>{{ value.name }}</td>
    <td>
      <select-field
        v-model="value.transaction.positive_transaction_type_id"
        label="Hinzufügen"
        :items="transactionTypes.filter(t => t.is_positive)"
        item-text="name"
        item-value="id"
        @input="value.transaction.negative_transaction_type_id = null"
      ></select-field>
    </td>
    <td>
      <select-field
        v-model="value.transaction.negative_transaction_type_id"
        label="Entfernen"
        :items="transactionTypes.filter(t => !t.is_positive)"
        item-text="name"
        item-value="id"
        @input="value.transaction.positive_transaction_type_id = null"
      ></select-field>
    </td>
    <td>
      <text-field
        v-model="value.transaction.amount"
        label="Menge in CHF"
        type="number"
      ></text-field>
    </td>
    <td>
      <text-field
        v-model="value.transaction.comment"
        label="Kommentar"
      ></text-field>
    </td>
  </tr>
  <!-- </v-form> -->
</template>

<script>
import { rules } from '@/utils'
import { SelectField, TextField } from '@/components/FormComponents'
import { mapGetters } from 'vuex'

export default {
  components: {
    TextField,
    SelectField
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
      rules,
      transaction: {},
      loaded: false
    }
  },
  computed: {
    ...mapGetters(['transactionTypes'])
  },
  mounted() {
    this.value.transaction = this.value.transaction || {}
    this.loaded = true
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

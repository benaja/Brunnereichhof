<template>
  <tr>
    <td>{{ value.name }}</td>
    <td>
      <div class="mt-3">
        <date-picker
          v-model="value.transaction.date"
          outlined
          no-icon
          label="Datum"
          dense
        ></date-picker>
      </div>
    </td>
    <td>
      <v-select
        v-model="value.transaction.positive_transaction_type_id"
        class="mt-3"
        label="Hinzufügen"
        :items="transactionTypes.filter(t => t.is_positive)"
        item-text="name"
        item-value="id"
        outlined
        dense
        @input="value.transaction.negative_transaction_type_id = null"
      ></v-select>
    </td>
    <td>
      <v-select
        v-model="value.transaction.negative_transaction_type_id"
        class="mt-3"
        label="Entfernen"
        :items="transactionTypes.filter(t => !t.is_positive)"
        item-text="name"
        item-value="id"
        outlined
        dense
        @input="value.transaction.positive_transaction_type_id = null"
      ></v-select>
    </td>
    <td>
      <v-text-field
        v-model="value.transaction.amount"
        class="mt-3"
        label="Menge in CHF"
        type="number"
        dense
        outlined
      ></v-text-field>
    </td>
    <td>
      <v-text-field
        v-model="value.transaction.comment"
        class="mt-3"
        label="Kommentar"
        dense
        outlined
      ></v-text-field>
    </td>
  </tr>
</template>

<script>
import { rules } from '@/utils'
import DatePicker from '@/components/general/DatePicker'
import { mapGetters } from 'vuex'

export default {
  components: {
    DatePicker
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
  watch: {
    'value.transaction': {
      deep: true,
      handler: transaction => {
        transaction.isValid = (transaction.positive_transaction_type_id
          || transaction.negative_transaction_type_id)
          && transaction.date && transaction.amount
      }
    }
  },
  methods: {
    validate() {
      return this.$refs.form.validate()
    },
    reset() {
      return this.$refs.form.reset()
    },
    updateDate(date) {
      console.log(date)
    }
  }
}
</script>

<style>

</style>

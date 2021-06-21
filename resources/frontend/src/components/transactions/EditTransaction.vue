<template>
  <card-layout
    title="Vorschuss bearbeiten"
    :saving="saving"
    @save="saveTransaction"
    @cancel="$emit('cancel')"
  >
    <v-form
      ref="form"
      @keyup.native.enter="$emit('submit')"
    >
      <v-row>
        <v-col
          cols="12"
        >
          <v-autocomplete
            v-model="transaction.user_id"
            label="Mitarbeiter"
            :items="employeesAndWorkers"
            item-value="id"
            item-text="name"
            no-data-text="keine Daten"
            autocomplete="off"
            :rules="[rules.required]"
          ></v-autocomplete>
        </v-col>
        <v-col
          cols="12"
          md="6"
        >
          <date-picker
            v-model="transaction.date"
            :rules="[rules.required]"
            label="Datum"
          ></date-picker>
        </v-col>
        <v-col
          cols="12"
          md="3"
        >
          <v-switch
            v-model="isPositive"
            label="Positiver Betrag"
          ></v-switch>
        </v-col>
        <v-col
          cols="12"
          md="3"
        >
          <v-switch
            v-model="transaction.entered"
            label="Verbucht"
          ></v-switch>
        </v-col>
        <v-col
          cols="12"
          md="6"
        >
          <v-select
            v-model="transaction.transaction_type_id"
            label="Vorschuss Typ"
            :items="transactionTypes.filter(t => t.is_positive == isPositive)"
            item-text="name"
            item-value="id"
            :rules="[rules.required]"
          ></v-select>
        </v-col>
        <v-col
          cols="12"
          md="6"
        >
          <v-text-field
            v-model="transaction.amount"
            label="Menge in CHF"
            type="number"
            :rules="[rules.required]"
          ></v-text-field>
        </v-col>
      </v-row>
      <v-text-field
        v-model="transaction.comment"
        label="Kommentar"
      ></v-text-field>
    </v-form>
  </card-layout>
</template>

<script>
import { mapGetters } from 'vuex'
import { rules } from '@/utils'
import DatePicker from '@/components/general/DatePicker'
import CardLayout from '@/components/general/CardLayout'


export default {
  components: {
    DatePicker,
    CardLayout
  },
  props: {
    value: {
      type: Object,
      default: () => ({})
    }
  },
  data() {
    return {
      rules,
      transaction: {},
      isPositive: true,
      saving: false
    }
  },
  computed: {
    ...mapGetters(['employeesAndWorkers', 'transactionTypes'])
  },
  watch: {
    value() {
      this.setTransaction()
    }
  },
  mounted() {
    this.setTransaction()
    this.$store.dispatch('fetchUsers')
    this.$store.dispatch('fetchTransactionTypes')
  },
  methods: {
    setTransaction() {
      if (this.value) {
        this.transaction = {
          ...this.value,
          amount: Math.abs(this.value.amount)
        }
        this.isPositive = this.value.type.is_positive
      } else {
        this.transaction = {}
      }
    },
    saveTransaction() {
      if (this.$refs.form.validate()) {
        this.saving = true
        const transactionType = this.transactionTypes
          .find(t => t.id === this.transaction.transaction_type_id)

        this.axios.patch(`transactions/${this.value.id}`, {
          ...this.transaction,
          amount: transactionType.is_positive
            ? Math.abs(this.transaction.amount) : -Math.abs(this.transaction.amount)
        }).then(() => {
          this.$store.dispatch('alert', { text: 'Vorschuss erfolgreich aktualisiert' })
          this.$emit('update')
        }).catch(() => {
          this.$stroe.dispatch('error', 'Fehler beim Speichern')
        }).finally(() => {
          this.saving = false
        })
      }
    }
  }
}
</script>

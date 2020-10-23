<template>
  <v-form
    ref="form"
    @keyup.native.enter="save"
  >
    <v-row>
      <v-col
        cols="12"
        md="6"
      >
        <date-picker
          v-model="value.date"
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
          v-model="value.entered"
          label="Verbucht"
        ></v-switch>
      </v-col>
      <v-col
        cols="12"
        md="6"
      >
        <v-select
          v-model="value.transaction_type_id"
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
          v-model="value.amount"
          label="Menge in CHF"
          type="number"
          :rules="[rules.required]"
        ></v-text-field>
      </v-col>
    </v-row>
    <v-text-field
      v-model="value.comment"
      label="Kommentar"
    ></v-text-field>
    <div class="text-center">
      <v-btn
        depressed
        color="primary"
        :loading="saving"
        @click="save"
      >
        Speichern
      </v-btn>
    </div>
  </v-form>
</template>

<script>
import { mapGetters } from 'vuex'
import { rules } from '@/utils'
import DatePicker from '@/components/general/DatePicker'


export default {
  components: {
    DatePicker
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
      isPositive: true,
      saving: false
    }
  },
  computed: {
    ...mapGetters(['transactionTypes'])
  },
  methods: {
    save() {
      if (this.$refs.form.validate()) {
        this.saving = true
        this.axios.post('transactions', {
          ...this.value,
          amount: this.isPositive ? Math.abs(this.value.amount) : -Math.abs(this.value.amount)
        }).then(() => {
          this.$store.dispatch('alert', { text: 'Vorschuss erfolgreich erstellt' })
          this.$emit('created')
          this.value.date = this.$moment().format('YYYY-MM-DD')
          this.isPositive = true
          this.value.transaction_type_id = null
          this.value.amount = null
          this.value.comment = null
          this.value.entered = false
          this.$refs.form.resetValidation()
        }).catch(() => {
          this.$store.dispatch('error', 'Vorschuss konnte nicht erstellt werden')
        }).finally(() => {
          this.saving = false
        })
      }
    }
  }
}
</script>

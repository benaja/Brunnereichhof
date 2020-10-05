<template>
  <fragment>
    <navigation-bar
      title="Stapelverarbeitung"
      :loading="isLoading.employees"
    >
      <v-btn
        class="ml-auto"
        color="primary"
        depressed
        @click="verifyModel = true"
      >
        Speichern
      </v-btn>
    </navigation-bar>
    <v-container>
      <search-bar
        ref="searchBar"
        v-model="employeesFiltered"
        name="employees"
        label="Mitarbeiter suchen"
        disable-deleted
        :items="mapedEmployees"
      >
      </search-bar>
      <v-data-table
        :items="employeesFiltered"
        :headers="headers"
      >
        <template v-slot:body="{items}">
          <transaction-form
            v-for="item of items"
            :key="item.id"
            :value="item"
          ></transaction-form>
        </template>
      </v-data-table>
    </v-container>
    <v-dialog
      v-model="verifyModel"
      width="1100"
    >
      <card-layout
        title="Eingaben überprüfen"
        :saving="saving"
        :valid="employeesWithValidTransactions().length > 0"
        @save="saveTransactions"
        @cancel="verifyModel = false"
      >
        <v-data-table
          :headers="headers"
          :items="employeesWithValidTransactions()"
        >
          <template v-slot:item="{item}">
            <tr>
              <td>{{ item.name }}</td>
              <td>{{ $moment(item.transaction.date).format('DD.MM.YYYY') }}</td>
              <td>
                {{ getTransactionName(item.transaction.positive_transaction_type_id) }}
              </td>
              <td>
                {{ getTransactionName(item.transaction.negative_transaction_type_id) }}
              </td>
              <td>{{ item.transaction.amount }}</td>
              <td>{{ item.transaction.comment }}</td>
            </tr>
          </template>
        </v-data-table>
      </card-layout>
    </v-dialog>
  </fragment>
</template>

<script>
import CardLayout from '@/components/general/CardLayout'
import TransactionForm from '@/components/transactions/TransactionForm'
import SearchBar from '@/components/general/SearchBar'
import { mapGetters } from 'vuex'

export default {
  components: {
    TransactionForm,
    SearchBar,
    CardLayout
  },
  data() {
    return {
      verifyModel: false,
      saving: false,
      headers: [
        {
          text: 'Mitarbeiter',
          value: 'name'
        },
        {
          text: 'Datum',
          value: 'transaction.date'
        },
        {
          text: 'Hinzufügen',
          value: 'transaction.positive_transaction_type_id'
        },
        {
          text: 'Entfernen',
          value: 'transaction.negative_transaction_type_id'
        },
        {
          text: 'Menge',
          value: 'transaction.amount'
        },
        {
          text: 'Kommentar',
          value: 'transaction.comment'
        }
      ],
      employeesFiltered: []
    }
  },
  computed: {
    ...mapGetters(['activeEmployees', 'isLoading', 'transactionTypes']),
    mapedEmployees() {
      return this.activeEmployees.map(e => ({
        ...e,
        transaction: e.transaction || this.emptyTransaction()
      }))
    }
  },
  watch: {
    employeesWithValidTransactions() {
      console.log(this.employeesWithValidTransactions)
    }
  },
  async mounted() {
    await this.$store.dispatch('fetchEmployees')
    await this.$store.dispatch('fetchTransactionTypes')
  },
  methods: {
    emptyTransaction() {
      return {
        isValid: false,
        date: this.$moment().format('YYYY-MM-DD'),
        positive_transaction_type_id: null,
        negative_transaction_type_id: null,
        amount: null,
        comment: null
      }
    },
    employeesWithValidTransactions() {
      return this.mapedEmployees.filter(e => e.transaction.isValid)
    },
    getTransactionName(transactionTypeId) {
      const transaction = this.transactionTypes.find(t => t.id === transactionTypeId)
      if (transaction) return transaction.name
      return null
    },
    saveTransactions() {
      this.saving = true
      const transactions = this.employeesWithValidTransactions().map(e => ({
        employee_id: e.id,
        date: e.transaction.date,
        comment: e.transaction.comment,
        transaction_type_id: e.transaction.positive_transaction_type_id
          || e.transaction.negative_transaction_type_id,
        amount: e.transaction.positive_transaction_type_id
          ? Math.abs(e.transaction.amount) : -Math.abs(e.transaction.amount)
      }))
      this.axios.post('transactions/bulk', {
        transactions
      }).then(() => {
        this.$store.dispatch('alert', { text: 'Einträge erfolgreich erstellt' })
        this.verifyModel = false
        this.mapedEmployees.forEach(e => { e.transaction = this.emptyTransaction() })
      }).catch(() => {
        this.$store.dispatch('error', 'Es ist ein unbekannter Fehler Aufgetreten')
      }).finally(() => {
        this.saving = false
      })
    }
  }
}
</script>

<style>

</style>

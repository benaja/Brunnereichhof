<template>
  <fragment>
    <navigation-bar
      title="Vorschüsse Anzeigen"
      :loading="isLoading.transactions"
    >
    </navigation-bar>
    <v-container>
      <transactions-table
        :transactions="transactions"
        :meta="transactionsMeta"
        with-employee
        @pagination="paginate"
      ></transactions-table>
      <!-- <v-data-table
        :items="transactions"
        :headers="headers"
        :items-per-page="15"
        :server-items-length="transactionsMeta.total || transactions.length"
        :footer-props=" {itemsPerPageOptions: [15, 30, -1]}"
        @pagination="paginate"
      >
        <template v-slot:item="{item}">
          <tr>
            <td>{{ item.employee.lastname }} {{ item.employee.firstname }}</td>
            <td>{{ $moment(item.date).format('DD.MM.YYYY') }}</td>
            <td>{{ item.type.name }}</td>
            <td>{{ item.amount }}</td>
            <td>{{ item.comment }}</td>
            <td class="d-flex justify-end">
              <v-btn
                icon
                @click="editTransaction = item"
              >
                <v-icon>edit</v-icon>
              </v-btn>
              <v-btn
                icon
                @click="deleteTransaction(item)"
              >
                <v-icon>delete</v-icon>
              </v-btn>
            </td>
          </tr>
        </template>
      </v-data-table> -->
    </v-container>
    <!-- <v-dialog
      :value="!!editTransaction"
      width="900"
      @input="editTransaction = null"
    >
      <edit-transaction
        v-model="editTransaction"
        @update="updateTransactions"
        @cancel="editTransaction = null"
      ></edit-transaction>
    </v-dialog> -->
  </fragment>
</template>

<script>
import { mapGetters } from 'vuex'
import { confirmAction } from '@/utils'
import TransactionsTable from '@/components/transactions/TransactionsTable'

export default {
  components: {
    TransactionsTable
  },
  data() {
    return {
      editTransaction: null,
      paginations: {},
      headers: [
        {
          text: 'Mitarbeiter',
          value: 'employee.lastname'
        },
        {
          text: 'Datum',
          value: 'date'
        },
        {
          text: 'Vorschuss Typ',
          value: 'type.name'
        },
        {
          text: 'Menge in CHF',
          value: 'amount'
        },
        {
          text: 'Kommentar',
          value: 'comment'
        },
        {
          text: 'Aktionen'
        }
      ]
    }
  },
  computed: {
    ...mapGetters(['transactions', 'isLoading', 'transactionsMeta'])
  },
  methods: {
    updateTransactions() {
      this.editTransaction = null
      this.$store.dispatch('fetchTransactions', this.paginations)
    },
    paginate(paginations) {
      this.paginations = paginations
      this.$store.dispatch('fetchTransactions', paginations)
    },
    deleteTransaction(transaction) {
      confirmAction().then(value => {
        if (value) {
          this.axios.delete(`transactions/${transaction.id}`).then(() => {
            this.$store.dispatch('alert', { text: 'Vorschuss wurde erfolgreich gelöscht' })
            this.$store.dispatch('fetchTransactions', this.paginations)
          }).catch(() => {
            this.$store.dispatch('error', 'Vorschuss konnte nicht gelöscht werden')
          })
        }
      })
    }
  }
}
</script>

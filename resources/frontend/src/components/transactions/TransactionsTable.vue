<template>
  <fragment>
    <v-data-table
      :items="transactions"
      :headers="headers"
      :items-per-page="15"
      :server-items-length="meta.total || transactions.length"
      :footer-props=" {itemsPerPageOptions: [15, 30, -1]}"
      :loading="loading"
      @pagination="paginate"
      @update:options="sortBy"
    >
      <template v-slot:item="{item}">
        <tr>
          <td v-if="withEmployee">
            <router-link
              :to="`/user/${item.user.id}`"
              class="employee-link"
            >
              {{ item.user.name }}
            </router-link>
          </td>
          <td>{{ $moment(item.date).format('DD.MM.YYYY') }}</td>
          <td>{{ item.type.name }}</td>
          <td>{{ item.amount }}</td>
          <td>{{ item.entered ? 'Ja' : 'Nein' }}</td>
          <td>{{ item.comment }}</td>
          <td v-if="$auth.user().hasPermission(['superadmin'], ['transaction_write'])">
            <div class="d-flex justify-end">
              <v-btn
                v-if="!item.entered"
                icon
                @click="setEntered(item)"
              >
                <v-icon>check</v-icon>
              </v-btn>
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
            </div>
          </td>
        </tr>
      </template>
    </v-data-table>
    <v-dialog
      :value="!!editTransaction"
      width="900"
      @input="editTransaction = null"
    >
      <edit-transaction
        v-model="editTransaction"
        @update="updateTransactions"
        @cancel="editTransaction = null"
      ></edit-transaction>
    </v-dialog>
  </fragment>
</template>

<script>
import { confirmAction, UserType } from '@/utils'
import EditTransaction from '@/components/transactions/EditTransaction'

export default {
  components: {
    EditTransaction
  },
  props: {
    transactions: {
      type: Array,
      default: () => []
    },
    meta: {
      type: Object,
      default: () => ({})
    },
    withEmployee: {
      type: Boolean,
      default: false
    },
    loading: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      editTransaction: null,
      pagination: {},
      sortOptions: {},
      UserType
    }
  },
  computed: {
    headers() {
      const headers = [
        {
          text: 'Datum',
          value: 'date'
        },
        {
          text: 'Vorschuss Typ',
          value: 'type.name',
          width: 200
        },
        {
          text: 'Menge in CHF',
          value: 'amount',
          width: 130
        },
        {
          text: 'Verbucht',
          value: 'entered',
          width: 100
        },
        {
          text: 'Kommentar',
          value: 'comment'
        }
      ]
      if (this.withEmployee) {
        headers.unshift({
          text: 'Mitarbeiter',
          value: 'employee.lastname'
        })
      }
      if (this.$auth.user().hasPermission(['superadmin'], ['transaction_write'])) {
        headers.push({
          text: 'Aktionen',
          width: 100
        })
      }
      return headers
    }
  },
  methods: {
    updateTransactions() {
      this.editTransaction = null
      this.$emit('pagination', this.pagination)
      this.$emit('update')
    },
    paginate(paginations) {
      this.pagination = paginations
      this.$emit('pagination', paginations)
    },
    deleteTransaction(transaction) {
      confirmAction().then(value => {
        if (value) {
          this.axios.delete(`transactions/${transaction.id}`).then(() => {
            this.$store.dispatch('alert', { text: 'Vorschuss wurde erfolgreich gelöscht' })
            this.$emit('pagination', this.pagination)
            this.$emit('delete')
          }).catch(() => {
            this.$store.dispatch('error', 'Vorschuss konnte nicht gelöscht werden')
          })
        }
      })
    },
    sortBy(options) {
      this.sortOptions = options
      this.$emit('sortBy', {
        ...options,
        sortBy: options.sortBy[0],
        sortDesc: options.sortDesc[0]
      })
    },
    setEntered(transaction) {
      let text
      if (transaction.user) {
        text = `Willst du die Transaktion von "${transaction.user.name}",
          Typ: "${transaction.type.name}", Menge: ${transaction.amount} CHF, Datum ${this.$moment(transaction.date).format('DD.MM.YYYY')}
          wirklich auf verbucht setzten?`
      } else {
        text = `Willst du die Transaktion mit dem Typ: "${transaction.type.name}", Menge: ${transaction.amount} CHF,
          Datum ${this.$moment(transaction.date).format('DD.MM.YYYY')} wirklich auf verbucht setzten?`
      }

      confirmAction(text, 'Ja').then(value => {
        if (value) {
          this.axios.patch(`transactions/${transaction.id}`, {
            entered: true
          }).then(() => {
            if (transaction.user) {
              this.$emit('update')
            } else {
              this.paginate(this.pagination)
            }
          })
        }
      })
    }
  }
}
</script>

<style lang="scss" scoped>
.employee-link {
  text-decoration: none;
  color: black;
}
</style>

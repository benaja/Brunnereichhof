<template>
  <fragment>
    <navigation-bar
      title="Vorschuss Typen"
      :loading="isLoading.transactionTypes"
    ></navigation-bar>
    <v-container>
      <v-data-table
        :headers="headers"
        :items="transactionTypes"
      >
        <template v-slot:item="{item}">
          <tr>
            <td>{{ item.name }}</td>
            <td>{{ item.is_positive ? 'Ja' : 'Nein' }}</td>
            <td>
              <v-btn
                icon
                @click="editTransactionType = item"
              >
                <v-icon>edit</v-icon>
              </v-btn>
            </td>
          </tr>
        </template>
      </v-data-table>
      <v-dialog
        v-model="addDialog"
        width="700"
      >
        <template v-slot:activator="{ on }">
          <v-btn
            v-if="$auth.user().hasPermission(['superadmin'], ['transaction_write'])"
            fixed
            bottom
            right
            fab
            color="primary"
            v-on="on"
          >
            <v-icon color="white">
              add
            </v-icon>
          </v-btn>
        </template>
        <add-transaction-type
          v-model="addDialog"
          @add="$store.dispatch('fetchTransactionTypes')"
        />
      </v-dialog>
    </v-container>
    <v-dialog
      :value="!!editTransactionType"
      width="700"
      @input="editTransactionType = null"
    >
      <edit-transaction-type
        :value="editTransactionType"
        @update="$store.dispatch('fetchTransactionTypes')"
        @input="editTransactionType = null"
      ></edit-transaction-type>
    </v-dialog>
  </fragment>
</template>

<script>
import { mapGetters } from 'vuex'
import AddTransactionType from '@/components/transactions/AddTransactionType'
import EditTransactionType from '@/components/transactions/EditTransactionType'

export default {
  components: {
    AddTransactionType,
    EditTransactionType
  },
  data() {
    return {
      addDialog: false,
      headers: [
        {
          text: 'Name',
          value: 'name'
        },
        {
          text: 'Positiv',
          value: 'is_positive'
        },
        {
          text: '',
          width: 0.1
        }
      ],
      editTransactionType: null
    }
  },
  computed: {
    ...mapGetters(['transactionTypes', 'isLoading'])
  },
  mounted() {
    this.$store.dispatch('fetchTransactionTypes')
  }
}
</script>

<style>

</style>

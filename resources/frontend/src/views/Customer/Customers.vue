<template>
  <fragment>
    <navigation-bar title="Kunden"></navigation-bar>
    <v-container>
      <search-bar
        ref="searchBar"
        v-model="customersFiltered"
        :items="allCustomers"
        name="customers"
        label="Kunden suchen"
        @showDeleted="s => (showDeleted = s)"
      ></search-bar>
      <progress-linear :loading="$store.getters.isLoading.customers"></progress-linear>
      <v-data-table
        :headers="headers"
        :items="customersFiltered"
        single-expand
        :footer-props="tableFooterProps"
      >
        <template v-slot:item="{item, expand, isExpanded }">
          <tr
            :class="{ 'white--text grey darken-4': item.is_blacklisted }"
            @click="expand(!isExpanded)"
          >
            <td>
              {{ item.customer_number }}
            </td>
            <td>
              {{ item.lastname }}
            </td>
            <td>
              {{ item.firstname }}
            </td>
            <td>
              {{ item.address.street }}, {{ item.address.plz }} {{ item.address.place }}
            </td>
            <td>
              <v-btn
                v-if="showDeleted && $auth.user().hasPermission(['superadmin'], ['customer_write'])"
                max-width="200"
                color="primary"
                text
                @click="e => restoreCustomer(item)"
              >
                Wiederherstellen
              </v-btn>
              <v-btn
                v-else-if="!showDeleted"
                icon
                :color="item.is_blacklisted ? 'white' : 'grey darken-2'"
                :to="'/customer/' + item.id"
              >
                <v-icon>edit</v-icon>
              </v-btn>
            </td>
          </tr>
        </template>
        <template v-slot:expanded-item="{ item }">
          <customer-expanded-table-item :source="item"></customer-expanded-table-item>
        </template>
      </v-data-table>
      <v-btn
        v-if="$auth.user().hasPermission(['superadmin'], ['customer_write'])"
        to="/customer/add"
        fixed
        bottom
        right
        fab
        color="primary"
      >
        <v-icon>add</v-icon>
      </v-btn>
    </v-container>
  </fragment>
</template>

<script>
import SearchBar from '@/components/general/SearchBar'
import { mapGetters } from 'vuex'
import CustomerExpandedTableItem from '@/components/customer/CustomerExpandedTableItem'


export default {
  components: {
    SearchBar,
    CustomerExpandedTableItem
  },
  data() {
    return {
      showDeleted: false,
      customersFiltered: [],
      headers: [
        {
          text: 'Kundennummer',
          value: 'customer_number',
          width: 140,
          sort: (a, b) => {
            if (a === null) return 1
            if (b === null) return -1
            return a - b
          }
        },
        {
          text: 'Nachname',
          value: 'lastname'
        },
        {
          text: 'Vorname',
          value: 'firstname'
        },
        {
          text: 'Adresse',
          value: 'address.place'
        },
        {
          text: 'Details',
          width: 90,
          sortable: false
        }
      ],
      tableFooterProps: {
        itemsPerPageOptions: [20, 50, 100, -1],
        itemsPerPageAllText: 'Alle',
        itemsPerPageText: 'Einträge pro Seite'
      }
    }
  },
  computed: {
    ...mapGetters(['allCustomers'])
  },
  mounted() {
    this.$store.dispatch('fetchCustomers')
  },
  methods: {
    restoreCustomer(customer) {
      this.$refs.searchBar.restoreItem(customer)
    }
  }
}
</script>

<style lang="scss" scoped>
#addbutton {
  position: fixed;
  bottom: 50px;
  right: 50px;
}

.scroller {
  height: 70vh;
  overflow-y: auto;
}

.switch {
  margin-top: 1em;
}

.virtual-list {
  width: 100%;
}
</style>

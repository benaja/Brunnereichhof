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
      <v-expansion-panels>
        <virtual-list
          :data-key="'firstname'"
          :data-sources="customersFiltered"
          :data-component="CustomerExpansionPanel"
          :keeps="20"
          page-mode
          :extra-props="{ restoreCustomer, showDeleted }"
          class="virtual-list"
        />
      </v-expansion-panels>
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
import VirtualList from 'vue-virtual-scroll-list'
import CustomerExpansionPanel from '@/components/customer/CustomerExpansionPanel'


export default {
  components: {
    SearchBar,
    VirtualList
  },
  data() {
    return {
      showDeleted: false,
      customersFiltered: [],
      CustomerExpansionPanel
    }
  },
  computed: {
    ...mapGetters(['allCustomers'])
  },
  mounted() {
    this.$store.dispatch('fetchCustomers')
  },
  methods: {
    restoreCustomer(event, customer) {
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

<template>
  <v-dialog
    v-model="dialogOpen"
    width="500px"
  >
    <template v-slot:activator="{ on }">
      <div class="text-right">
        <v-btn
          color="primary"
          depressed
          class="mt-2 "
          v-on="on"
        >
          {{ $t('Kunde Hinzufügen') }}
        </v-btn>
      </div>
    </template>
    <v-card class="pa-6">
      <h3>
        {{ $t('Kunde Hinzufügen') }}
      </h3>
      <v-autocomplete
        v-if="$auth.user().hasPermission(['superadmin'], ['rapport_write'])"
        v-model="selectedCustomer"
        :label="$t('Kunde Hinzufügen')"
        append-outer-icon="search"
        :items="availableCustomers"
        item-value="id"
        item-text="name"
        @input="addCustomer"
      ></v-autocomplete>
      <div class="d-flex justify-end">
        <v-btn
          color="primary"
          depressed
          @click="dialogOpen = false"
        >
          {{ $t('Fertig') }}
        </v-btn>
      </div>
    </v-card>
  </v-dialog>
</template>

<script>
import { mapGetters } from 'vuex'


export default {
  props: {
    selectedCustomers: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      selectedCustomer: null,
      dialogOpen: false
    }
  },
  computed: {
    ...mapGetters(['customers']),
    availableCustomers() {
      return this.customers
        .filter(customer => !this.selectedCustomers.find(c => c.id === customer.id))
    }
  },
  methods: {
    addCustomer() {
      this.$emit('input', this.selectedCustomer)
      this.$nextTick(() => {
        this.selectedCustomer = null
      })
    }
  }
}
</script>

<style>

</style>

<template>
  <v-autocomplete
    v-if="$auth.user().hasPermission(['superadmin'], ['rapport_write'])"
    v-model="selectedCustomer"
    :label="$t('Kunde Hinzufügen')"
    append-outer-icon="search"
    :items="customers"
    item-value="id"
    item-text="name"
    @input="addCustomer"
  ></v-autocomplete>
</template>

<script>
import { mapGetters } from 'vuex'


export default {
  data() {
    return {
      selectedCustomer: null
    }
  },
  computed: {
    ...mapGetters(['customers'])
  },
  methods: {
    addCustomer() {
      this.$emit('input', this.selectedCustomer)
      this.selectedCustomer = null
    }
  }
}
</script>

<style>

</style>

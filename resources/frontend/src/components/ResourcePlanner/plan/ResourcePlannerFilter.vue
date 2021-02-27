<template>
  <div class="d-flex">
    <v-text-field
      v-model="searchString"
      :label="$t('Suchen')"
      prepend-icon="search"
    ></v-text-field>
    <v-checkbox
      v-model="showUsed"
      :label="$t('Verwendete anzeigen')"
    ></v-checkbox>
  </div>
</template>

<script>
export default {
  props: {
    items: {
      type: Array,
      default: () => []
    },
    itemSearchText: {
      type: Function,
      default: item => item.name
    },
    isItemUsed: {
      type: Function,
      default: () => false
    }
  },
  data() {
    return {
      searchString: null,
      showUsed: false
    }
  },
  computed: {
    filteredItems() {
      return this.items.filter(item => {
        if (this.searchString
          && !this.itemSearchText(item).toLowerCase().includes(this.searchString.toLowerCase())) {
          return false
        }

        if (this.showUsed !== this.isItemUsed(item)) {
          return false
        }

        return true
      })
    }
  },
  watch: {
    filteredItems() {
      this.$emit('input', this.filteredItems)
    }
  },
  mounted() {
    this.$emit('input', this.filteredItems)
  }
}
</script>

<style>

</style>

<template>
  <v-combobox
    v-if="inputField.type === EVALUATION_INPUT_TYPES.COMBOBOX"
    v-model="inputField.value"
    :items="items"
    :loading="isLoading"
    :search-input.sync="searchString"
    no-data-text="keine Daten"
    :label="inputField.label"
    prepend-icon="search"
    autocomplete="off"
    :item-text="inputField.itemText || 'name'"
    :item-value="inputField.itemValue || 'id'"
    @focus="searchString = ''"
  ></v-combobox>
</template>

<script>
import { EVALUATION_INPUT_TYPES } from '@/constants'

export default {
  props: {
    inputField: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      searchString: null,
      isLoading: false,
      isLoaded: false,
      items: []
    }
  },
  computed: {
    EVALUATION_INPUT_TYPES() {
      return EVALUATION_INPUT_TYPES
    }
  },
  watch: {
    searchString() {
      if (this.isLoaded) return
      if (this.isLoading) return
      this.isLoading = true

      this.$store.dispatch(this.inputField.dispatch).then((items) => {
        this.items = [...items]
        if (this.inputField.selectAll) {
          this.items.unshift({
            id: 'all',
            name: 'Alle'
          })
        }
        this.isLoading = false
        this.isLoaded = true
      })
    }
  }
}
</script>

<style>
</style>

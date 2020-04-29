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
      EVALUATION_INPUT_TYPES
    }
  },
  computed: {
    items() {
      const items = [...this.$store.getters[this.inputField.dispatch]]
      console.log(items)
      if (this.inputField.selectAll) {
        items.unshift({
          id: 'all',
          name: 'Alle'
        })
      }
      return items
    },
    dispatchName() {
      const firstLetter = this.inputField.dispatch.slice(0, 1).toUpperCase()
      const restOfName = this.inputField.dispatch.slice(1, this.inputField.dispatch.length)
      return `${firstLetter}${restOfName}`
    }
  },
  watch: {
    searchString() {
      if (this.isLoaded) return
      if (this.isLoading) return
      this.isLoading = true

      this.$store.dispatch(`fetch${this.dispatchName}`).then(() => {
        this.isLoading = false
        this.isLoaded = true
      })
    }
  }
}
</script>

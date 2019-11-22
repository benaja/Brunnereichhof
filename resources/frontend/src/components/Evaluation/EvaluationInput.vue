<template>
  <v-combobox
    v-if="inputField.type === EVALUATION_INPUT_TYPES.COMBOBOX"
    v-model="inputField.value"
    :items="items"
    :loading="isLoading"
    :search-input.sync="sarchString"
    no-data-text="keine Daten"
    @focus="sarchString = ''"
    :label="inputfield.label"
    prepend-icon="search"
    :item-text="inputField.itemText || 'name'"
    :item-value="inputField.itemValue || 'id'"
  ></v-combobox>
</template>

<script>
export default {
  props: {
    inputField: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      sarchString: null,
      isLoading: false,
      isLoaded: false,
      items: []
    }
  },
  watch: {
    searchStringEmployee() {
      if (this.isLoaded) return
      if (this.isLoading) return
      this.isLoading = true

      this.$store.dispatch(this.inputField.dispatch).then(items => {
        this.items = items
        if (this.inputField.selectAll) {
          this.items.unshift({
            id: 0,
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

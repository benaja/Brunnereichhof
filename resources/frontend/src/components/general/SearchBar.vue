<template>
  <div>
    <v-row class="filter-controlls">
      <v-col cols="12" :md="customFilterFunction ? 7 : 9" :lg="customFilterFunction ? 9 : 10">
        <v-text-field v-model="searchString" :label="label" :color="color" prepend-icon="search"></v-text-field>
      </v-col>
      <v-col cols="6" md="2" lg="1" v-if="customFilterFunction">
        <slot name="custom-filter"></slot>
      </v-col>
      <v-col :cols="customFilterFunction ? 6 : 12" md="3" lg="2">
        <v-checkbox v-model="showDeleted" :color="color" label="Gelöschte anzeigen"></v-checkbox>
      </v-col>
    </v-row>
  </div>
</template>

<script>
export default {
  name: 'SearchBar',
  props: {
    value: Array,
    customFilterFunction: Function,
    customFilterIndex: String,
    name: String,
    label: String,
    items: {
      type: Array,
      default: () => []
    },
    color: {
      type: String,
      default: 'primary'
    }
  },
  data() {
    return {
      searchString: '',
      showDeleted: false
    }
  },
  computed: {
    filteredItems() {
      if (this.items.length === 0) return []
      return this.items.filter(item => {
        if (!!this.showDeleted !== !!item.deleted_at) return false
        if (this.customFilterFunction && !this.customFilterFunction(item) && !this.showDeleted) return false
        if (!this.searchString) return true
        let fullName = item.name || ''
        if (item[this.customFilterIndex]) fullName += ` ${item[this.customFilterIndex]}`
        return fullName.toLowerCase().includes(this.searchString.toLowerCase())
      })
    }
  },
  mounted() {
    this.$emit('input', this.filteredItems)
  },
  methods: {
    restoreItem(item) {
      this.axios
        .patch(`/${this.name}/${item.id}`, { deleted_at: '-' })
        .then(() => {
          this.showDeleted = false
          this.$store.dispatch(`fetch${this.name.slice(0, 1).toUpperCase()}${this.name.slice(1, this.name.length - 1)}s`)
          this.$store.dispatch('alert', { text: 'Erfolgreich wiederhergestellt' })
        })
        .catch(() => {
          this.$store.dispatch('error', 'Fehler beim Wiederherstellen')
        })
    }
  },
  watch: {
    showDeleted() {
      this.$emit('showDeleted', this.showDeleted)
    },
    filteredItems(value) {
      this.$emit('input', value)
    }
  },
  url: {
    searchString: {
      param: 'searchString',
      noHistory: true
    },
    showDeleted: {
      param: 'showDeleted',
      noHistory: true
    }
  }
}
</script>

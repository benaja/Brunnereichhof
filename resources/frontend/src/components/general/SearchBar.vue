<template>
  <div>
    <v-row class="filter-controlls">
      <v-col
        cols="12"
        :md="mdColWidth"
        :lg="lgColWidth"
      >
        <v-text-field
          v-model="searchString"
          :label="label"
          :color="color"
          :hint="value.length + ' Resultat' + (value.length !== 1 ? 'e' : '')"
          persistent-hint
          prepend-icon="search"
        ></v-text-field>
      </v-col>
      <v-col
        v-if="customFilterFunction"
        cols="6"
        md="3"
        lg="2"
      >
        <slot name="custom-filter"></slot>
      </v-col>
      <v-col
        v-if="!disableDeleted"
        :cols="customFilterFunction ? 6 : 12"
        md="3"
        lg="2"
      >
        <v-checkbox
          v-model="showDeleted"
          :color="color"
          label="Gelöschte anzeigen"
        ></v-checkbox>
      </v-col>
    </v-row>
  </div>
</template>

<script>
export default {
  name: 'SearchBar',
  props: {
    value: {
      type: Array,
      default: null
    },
    customFilterFunction: {
      type: Function,
      default: null
    },
    customFilterIndex: {
      type: String,
      default: null
    },
    name: {
      type: String,
      default: null
    },
    label: {
      type: String,
      default: null
    },
    items: {
      type: Array,
      default: () => []
    },
    color: {
      type: String,
      default: 'primary'
    },
    disableDeleted: {
      type: Boolean,
      default: false
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
        if (this.customFilterFunction
          && !this.customFilterFunction(item)
          && !this.showDeleted) return false
        if (!this.searchString) return true

        if (item.customer_number && item.customer_number.includes(this.searchString)) return true
        let fullName = item.name || ''
        if (item[this.customFilterIndex]) fullName += ` ${item[this.customFilterIndex]}`
        return fullName.toLowerCase().includes(`${this.searchString}`.toLowerCase())
      })
    },

    mdColWidth() {
      let initial = 12
      if (this.customFilterFunction) initial -= 3
      if (!this.disableDeleted) initial -= 3
      return initial
    },

    lgColWidth() {
      let initial = 12
      if (this.customFilterFunction) initial -= 2
      if (!this.disableDeleted) initial -= 2
      return initial
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

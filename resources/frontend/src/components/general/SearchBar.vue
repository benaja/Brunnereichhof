<template>
  <div>
    <v-row class="filter-controlls">
      <v-col cols="12" :md="customFilterFunction ? 7 : 9" :lg="customFilterFunction ? 9 : 10">
        <v-text-field :label="label" prepend-icon="search" v-model="searchString"></v-text-field>
      </v-col>
      <v-col cols="6" md="2" lg="1" v-if="customFilterFunction">
        <slot name="custom-filter"></slot>
      </v-col>
      <v-col :cols="customFilterFunction ? 6 : 12" md="3" lg="2">
        <v-checkbox label="Gelöschte anzeigen" v-model="showDeleted"></v-checkbox>
      </v-col>
    </v-row>
    <alert v-model="showSuccessMessage" text="Erfolgreich wiederhergestellt"></alert>
  </div>
</template>

<script>
import Alert from '@/components/general/Alert'

export default {
  name: 'SearchBar',
  components: {
    Alert
  },
  props: {
    value: Array,
    customFilterFunction: Function,
    customFilterIndex: String,
    name: String,
    label: String
  },
  data() {
    return {
      searchString: '',
      showDeleted: false,
      items: [],
      showSuccessMessage: false
    }
  },
  mounted() {
    this.getItems()
  },
  computed: {
    filteredItems() {
      if (this.items.length === 0) return []
      return this.items.filter(item => {
        if (this.showDeleted && item.deleted_at === null) return false
        if (this.customFilterFunction && !this.customFilterFunction(item) && !this.showDeleted) return false
        if (!this.searchString) return true
        let fullName = item.name
        if (item[this.customFilterIndex]) fullName += ` ${item[this.customFilterIndex]}`
        return fullName.toLowerCase().includes(this.searchString.toLowerCase())
      })
    }
  },
  methods: {
    getItems(deleted = false, update = true) {
      this.$store.commit('isLoading', true)
      this.$store.dispatch(this.name, { update, deleted }).then(items => {
        this.items = items
        this.$store.commit('isLoading', false)
      })
    },
    restoreItem(item) {
      this.axios.patch(`/${this.name.slice(0, this.name.length - 1)}/${item.id}`, { deleted_at: '-' }).then(() => {
        this.showDeleted = false
        this.$store.dispatch(`reset${this.name.slice(0, 1).toUpperCase()}${this.name.slice(1, this.name.length)}`)
        this.getItems()
        this.showSuccessMessage = true
      })
    }
  },
  watch: {
    showDeleted() {
      this.$emit('showDeleted', this.showDeleted)
      this.getItems(this.showDeleted, false)
    },
    filteredItems(value) {
      this.$emit('input', value)
    }
  }
}
</script>

<style>
</style>

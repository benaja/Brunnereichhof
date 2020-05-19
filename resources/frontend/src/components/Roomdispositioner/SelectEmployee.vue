<template>
  <v-autocomplete
    v-model="employee"
    label="Mitarbeiter suchen"
    :items="selectableEmployees"
    item-value="id"
    item-text="name"
    :loading="isLoading"
    :search-input.sync="searchString"
    no-data-text="keine Daten"
    autocomplete="off"
    :rules="rules"
    color="blue"
    item-color="blue"
    @focus="searchString = ''"
  >
    <template v-slot:item="data">
      <p class="autocomplete-item-text">
        {{ data.item.name }}
        <v-chip
          v-if="data.item.isGuest"
          color="blue"
          text-color="white"
          class="float-right"
          small
        >
          Gast
        </v-chip>
      </p>
    </template>
  </v-autocomplete>
</template>

<script>
import { mapGetters } from 'vuex'

export default {
  props: {
    value: {
      type: Number,
      default: null
    },
    rules: {
      type: Array,
      default: () => []
    },
    selectAll: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      searchString: null,
      isLoading: false,
      loaded: false
    }
  },
  computed: {
    ...mapGetters(['employeesWithGuests', 'allEmployeesWithGuests']),
    selectableEmployees() {
      const employees = [...this.employeesWithGuests]
      if (this.selectAll) {
        employees.unshift({
          id: 0,
          name: 'Alle'
        })
      }
      if (this.value && !this.employeesWithGuests.find(e => e.id === this.value)) {
        const employee = this.allEmployeesWithGuests.find(e => e.id === this.value)
        employees.push(employee)
      }
      return employees
    },
    employee: {
      get() {
        return this.value
      },
      set(value) {
        this.$emit('input', value)
      }
    }
  },
  watch: {
    searchString() {
      if (this.loaded) return
      if (this.isLoading) return
      this.isLoading = true


      this.$store.dispatch('fetchEmployees').then(() => {
        this.isLoading = false
        this.loaded = true
      })
    }
  },
  mounted() {
    if (this.value) this.searchString = ''
  },
  methods: {
    getDeletedEmployees() {
      this.$store.dispatch('employeesWithGuests', { deleted: true })
    }
  }
}
</script>

<style lang="scss" scoped>
.autocomplete-item-text {
  margin: 10px 0;
  width: 100%;
  vertical-align: middle;
  line-height: 2em;
}

.float-right {
  position: relative;
  float: right;
}
</style>

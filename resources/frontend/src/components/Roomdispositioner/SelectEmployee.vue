<template>
  <v-autocomplete
    v-model="employee"
    label="Mitarbeiter suchen"
    :items="employees"
    item-value="id"
    item-text="name"
    :loading="isLoading"
    :search-input.sync="searchString"
    no-data-text="keine Daten"
    autocomplete="off"
    :rules="rules"
    color="blue"
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
export default {
  name: 'SelectEmployee',
  props: {
    value: {
      type: Number,
      required: true
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
      employees: [],
      loaded: false
    }
  },
  computed: {
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

      this.$store.dispatch('employeesWithGuests').then((employees) => {
        this.employees = [...employees]
        this.isLoading = false
        this.loaded = true
        if (this.value && !this.employees.find((e) => e.id === this.value)) {
          this.$store.dispatch('employeesWithGuests', { deleted: true }).then((employeesWithGuests) => {
            const employee = employeesWithGuests.find((e) => e.id === this.value)
            this.employees.push(employee)
          })
        }
        if (this.selectAll) {
          this.employees.unshift({
            id: 0,
            name: 'Alle'
          })
        }
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

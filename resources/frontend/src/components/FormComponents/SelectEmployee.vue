<template>
  <v-autocomplete
    ref="autocomplete"
    v-model="employee"
    label="Mitarbeiter"
    :items="employees"
    item-value="id"
    item-text="name"
    no-data-text="keine Daten"
    autocomplete="off"
    return-object
    clearable
    :search-input.sync="searchString"
    @focus="searchString = ''"
  >
    <template
      v-if="withGuests"
      v-slot:item="{ item }"
    >
      <p class="autocomplete-item-text">
        {{ item.name }}
        <v-chip
          v-if="item.isGuest"
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
      type: Object,
      default: null
    },
    toggleActive: {
      type: Boolean,
      default: false
    },
    withInactive: {
      type: Boolean,
      default: false
    },
    withGuests: {
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
    ...mapGetters({ employees: 'allEmployeesWithGuests' }),
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


      this.$store.dispatch('loadEmployees', { withInactive: this.withInactive, withGuests: this.withGuests }).then(() => {
        this.isLoading = false
        this.loaded = true


        const menu = document.getElementsByClassName('menuable__content__active')[0]
        menu.addEventListener('scroll', () => {
          if (menu.scrollHeight - menu.scrollTop - menu.clientHeight < 20) {
            this.loadMoreEmployees()
          }
        })
      })
    }
  },
  mounted() {
    if (this.value) this.searchString = ''
  },
  methods: {
    loadMoreEmployees() {
      if (this.isLoading) return
      this.isLoading = true
      document.activeElement.blur()

      this.$store.dispatch('loadMoreEmployees', { withInactive: this.withInactive, withGuests: this.withGuests })
        .then(() => {
          this.isLoading = false
        })
    }
  }
}
</script>

<style>

</style>

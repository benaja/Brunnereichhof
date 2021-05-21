<template>
  <div>
    <p class="mb-0 font-weight-bold">
      {{ $t('Kinder') }}
    </p>
    <Child
      v-for="child of value"
      :key="child.id"
      :child="child"
      :readonly="readonly"
      @remove="removeChild"
    >
    </Child>
    <v-btn
      v-if="!readonly"
      color="primary"
      class="mt-3"
      text
      @click="addChild"
    >
      {{ $t('Kind hinzufügen') }} <v-icon class="ml-1">
        add
      </v-icon>
    </v-btn>
  </div>
</template>

<script>
import Child from './Child'

export default {
  components: {
    Child
  },
  props: {
    value: {
      type: Array,
      default: () => []
    },
    familyAllowance: {
      type: Object,
      required: true
    },
    readonly: {
      type: Boolean,
      default: false
    }
  },
  methods: {
    addChild() {
      this.axios.$post('children', {
        family_allowance_id: this.familyAllowance.id
      }).then(data => {
        data.files = []
        this.value.push(data)
        this.$emit('input', [...this.value])
      }).catch(() => {
        this.$store.dispatch('error', this.$t('Es ist ein unerwarteter Fehler aufgetreten'))
      })
    },
    removeChild(child) {
      const index = this.value.indexOf(child)
      this.value.splice(index, 1)
      this.$emit('input', [...this.value])
    }
  }
}
</script>

<style lang="scss" scoped>

</style>

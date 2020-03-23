<template>
  <v-menu
    :close-on-content-click="false"
    :nudge-width="300"
    v-model="open"
    obsolute
    :position-x="positionX"
    :position-y="positionY"
    z-index="3"
  >
    <v-card>
      <v-card-actions></v-card-actions>
      <h1>Add Element</h1>
    </v-card>
  </v-menu>
</template>

<script>
export default {
  props: {
    value: {
      type: Boolean,
      default: false
    },
    positionX: {
      type: Number,
      default: 0
    },
    positionY: {
      type: Number,
      default: 0
    }
  },
  computed: {
    open: {
      get() {
        return this.value
      },
      set(value) {
        this.$emit(value)
      }
    }
  }
}
</script>

<style>
</style>

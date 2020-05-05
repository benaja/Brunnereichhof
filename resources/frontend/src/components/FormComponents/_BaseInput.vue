<template>
  <div class="px-1 custom-input">
    <p
      v-if="label"
      class="grey--text text--darken-3 ma-0"
    >
      {{ label }}
    </p>
    <div class="d-flex">
      <slot></slot>
      <v-tooltip
        v-if="restoreMessage"
        :max-width="240"
        left
      >
        <template v-slot:activator="{ on }">
          <v-btn
            class="ml-1 mr-0 mt-3"
            color="grey"
            text
            icon
            v-on="on"
            @click="$emit('restore')"
          >
            <v-icon>restore</v-icon>
          </v-btn>
        </template>
        <span v-html="restoreMessage"></span>
      </v-tooltip>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    restoreMessage: {
      type: String,
      default: null
    },
    label: {
      type: String,
      default: null
    }
  }
}
</script>

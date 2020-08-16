<template>
  <v-card
    v-if="card"
  >
    <h3 class="pa-4 title">
      {{ title }}
      <span class="float-right">
        <slot name="title-action"></slot>
      </span>
    </h3>
    <div class="card-content">
      <slot></slot>
    </div>
    <v-card-actions>
      <v-btn
        text
        @click="$emit('cancel')"
      >
        Abbrechen
      </v-btn>
      <v-spacer></v-spacer>
      <v-btn
        :color="color"
        :loading="saving"
        depressed
        class="white--text"
        @click="$emit('save')"
      >
        Speichern
      </v-btn>
    </v-card-actions>
  </v-card>
  <div v-else>
    <slot></slot>
    <v-btn
      :color="color"
      :loading="saving"
      @click="$emit('save')"
    >
      Speichern
    </v-btn>
  </div>
</template>

<script>
export default {
  props: {
    card: {
      type: Boolean,
      default: true
    },
    color: {
      type: String,
      default: 'primary'
    },
    saving: {
      type: Boolean,
      default: false
    },
    title: {
      type: String,
      default: null
    }
  }
}
</script>

<style lang="scss" scoped>
.card-content {
  max-height: calc(100vh - 300px);
  overflow-y: auto;
  overflow-x: hidden;
  padding: 16px
}
</style>

<template>
  <v-card
    v-if="card"
    class="pa-3"
  >
    <slot></slot>
    <v-card-actions>
      <v-spacer></v-spacer>
      <v-btn
        text
        @click="$emit('cancel')"
      >
        Abbrechen
      </v-btn>
      <v-btn
        :color="color"
        text
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
    }
  }
}
</script>

<style>
</style>

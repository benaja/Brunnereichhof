<template>
  <v-row justify="center">
    <v-dialog v-model="isOpen" max-width="600px">
      <v-card max-width="600px">
        <v-card-title>
          <h3>Auswertung</h3>
        </v-card-title>
        <v-divider></v-divider>
        <v-card-text></v-card-text>
      </v-card>
    </v-dialog>
  </v-row>
</template>

<script>
export default {
  name: 'RoomEvaluation',
  data() {
    return {
      isOpen: true
    }
  }
}
</script>

<style lang="scss" scoped>
</style>

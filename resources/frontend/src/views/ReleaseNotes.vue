<template>
  <fragment>
    <navigation-bar title="Release Notes"></navigation-bar>
    <v-container>
      <v-select
        v-model="value"
        :items="releases"
        item-value="version"
        item-text="version"
        chips
        label="Version"
        outlined
        class="mt-4"
      ></v-select>
      <p v-if="selectedRelease.date">
        Datum: {{ $moment(selectedRelease.date, 'DD.MM.YYYY').format('DD.MM.YYYY') }}
      </p>
      <h2 v-if="selectedRelease.changes && selectedRelease.changes.length">
        Neuerungen
      </h2>
      <p
        v-for="(change, index) of selectedRelease.changes"
        :key="index"
      >
        {{ change }}
      </p>
      <h2 v-if="selectedRelease.bugfixes && selectedRelease.bugfixes.length">
        Bug fixes
      </h2>
      <p
        v-for="(change, index) of selectedRelease.bugfixes"
        :key="`bf-${index}`"
      >
        {{ change }}
      </p>
    </v-container>
  </fragment>
</template>

<script>
import releases from '@/release-notes'

export default {
  name: 'ReleaseNotes',
  data() {
    return {
      releases,
      value: releases[0].version
    }
  },
  computed: {
    selectedRelease() {
      return this.releases.find(r => r.version === this.value)
    }
  }
}
</script>

<style>
</style>

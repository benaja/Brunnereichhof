<template>
  <v-card
    v-if="open"
    class="release-notes-card"
    elevation="7"
  >
    <p class="headline">
      Neue Version
      <span class="green--text">{{ release.version }}</span>
      <v-btn
        text
        icon
        small
        class="float-right"
        @click="closeReleaseNotes"
      >
        <v-icon>close</v-icon>
      </v-btn>
    </p>
    <template v-if="release.changes && release.changes.length">
      <p class="title">
        Änderungen
      </p>
      <p
        v-for="(change, index) of release.changes"
        :key="index"
      >
        {{ change }}
      </p>
    </template>
    <v-btn
      class="float-right"
      text
      color="primary"
      to="/release-notes"
    >
      Mehr infos
    </v-btn>
  </v-card>
</template>

<script>
import releases from '@/release-notes'

export default {
  name: 'ReleaseNotes',
  data() {
    return {
      open: true,
      release: releases[0]
    }
  },
  mounted() {
    const releaseNotesCard = JSON.parse(localStorage.getItem('releaseNotesCard'))
    if (releaseNotesCard
      && releaseNotesCard.closed
      && releaseNotesCard.version === this.release.version) {
      this.open = false
    }
  },
  methods: {
    closeReleaseNotes() {
      this.open = false
      const releaseNotesCard = {
        closed: true,
        version: this.release.version
      }
      localStorage.setItem('releaseNotesCard', JSON.stringify(releaseNotesCard))
    }
  }
}
</script>

<style lang="scss" scoped>
.release-notes-card {
  position: absolute;
  bottom: 12px;
  right: 12px;
  padding: 12px;
  width: 400px;
  max-width: calc(100% - 24px);
}

@media only screen and (max-width: 600px) {
  .release-notes-card {
    top: 64px;
    position: fixed;
    overflow-x: scroll;
  }
}
</style>

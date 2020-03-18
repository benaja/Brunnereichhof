<template>
  <div id="app">
    <v-app>
      <div v-if="$auth.ready()">
        <loading-page v-if="$store.getters.isLoading"></loading-page>
        <NavigationBar></NavigationBar>
        <router-view />
      </div>
      <alert v-model="alert.visible" :text="alert.text"></alert>
    </v-app>
  </div>
</template>

<script>
import NavigationBar from '@/components/NavigationBar'
import LoadingPage from '@/components/general/LoadingPage'
import Alert from '@/components/general/Alert'
import { mapGetters } from 'vuex'

export default {
  name: 'app',
  components: {
    NavigationBar,
    LoadingPage,
    Alert
  },
  computed: {
    ...mapGetters(['alert'])
  }
}
</script>

<style lang="scss">
body {
  background-color: rgb(238, 237, 237);
}

* {
  font-family: Roboto, sans-serif;
}

.no-select {
  user-select: none;
}
</style>

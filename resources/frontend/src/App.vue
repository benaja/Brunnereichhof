<template>
  <div id="app">
    <v-app>
      <div v-if="$auth.ready()">
        <loading-page v-if="$store.getters.isLoading"></loading-page>
        <NavigationBar></NavigationBar>
        <router-view />
      </div>
      <div class="alerts" v-if="$store">
        <v-alert class="ma-3" v-for="alert of alerts" :key="alert.key" :type="alert.type || 'success'">{{ alert.text }}</v-alert>
      </div>
    </v-app>
  </div>
</template>

<script>
import NavigationBar from '@/components/NavigationBar'
import LoadingPage from '@/components/general/LoadingPage'
import { mapGetters } from 'vuex'

export default {
  name: 'app',
  components: {
    NavigationBar,
    LoadingPage
  },
  computed: {
    ...mapGetters(['alerts'])
  },
  mounted() {
    this.setCssWindowHeight()
    window.addEventListener('resize', this.setCssWindowHeight)
  },
  methods: {
    setCssWindowHeight() {
      const vh = window.innerHeight * 0.01
      document.documentElement.style.setProperty('--vh', `${vh}px`)
    }
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

.alerts {
  position: fixed;
  bottom: 0;
  right: 0;
  z-index: 1000;
}
</style>

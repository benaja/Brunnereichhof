<template>
  <div class="nav-bar">
    <v-toolbar absolute>
      <v-btn
        v-if="$auth.check()
          && $auth.user().hasPermission(
            ['superadmin'],
            ['customer_read',
             'employee_read',
             'employee_preview_read',
             'worker_read',
             'rapport_read',
             'roomdispositioner_read',
             'timerecord_stats',
             'hourrecord_read',
             'evaluation_employee',
             'evaluation_customer'])"
        icon
        class="d-md-none"
        @click="$emit('openNavigation')"
      >
        <v-icon>dehaze</v-icon>
      </v-btn>
      <v-spacer></v-spacer>
      <v-toolbar-title>
        <router-link
          tag="div"
          to="/"
          class="logo"
        >
          <img src="@/assets/images/logo.png" />
        </router-link>
      </v-toolbar-title>
    </v-toolbar>
  </div>
</template>

<script>
export default {
  name: 'NavigationBar',
  data() {
    return {
      showSaveSateRouteNames: ['Rapport']
    }
  },
  mounted() {
    let isMobile = window.innerWidth < 960
    this.$store.commit('isMobile', isMobile)
    window.addEventListener('resize', () => {
      isMobile = window.innerWidth < 960
      this.$store.commit('isMobile', isMobile)
    })
  }
}
</script>

<style lang="scss" scoped>
.nav-bar {
  height: 64px;

  > header {
    position: fixed;
    width: 100%;
    z-index: 5;
  }
}

.logo {
  cursor: pointer;

  img {
    margin-top: 5px;
    height: 56px;
  }
}
</style>

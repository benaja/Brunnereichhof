<template>
  <div class="nav-bar">
    <div
      class="toolbar"
    >
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
      <v-container>
        <div class="d-flex align-center nav-content-container">
          <div class="nav-title">
            <h1 class="display-1 ma-0">
              {{ title }}
            </h1>
            <p
              v-if="$store.getters.isSaving"
              class="my-0 mr-3 grey--text font-italic save-text"
            >
              {{ $vuetify.breakpoint.xsOnly ? 'speichern...' : 'Wird gespeichert...' }}
            </p>
          </div>
          <slot></slot>
        </div>
      </v-container>
      <!-- <v-spacer></v-spacer> -->
      <!-- <div
        v-if="$store.getters.isSaving"
        class="is-saving"
      >
        <p class="my-0 mr-3 grey--text font-italic">
          {{ $vuetify.breakpoint.xsOnly ? 'speichern...' : 'Wird gespeichert...' }}
        </p>
      </div> -->
      <!-- <v-toolbar-title>
        <router-link
          tag="div"
          to="/"
          class="logo"
        >
          <img src="@/assets/images/logo.png" />
        </router-link>
      </v-toolbar-title> -->
    </div>
  </div>
</template>

<script>
export default {
  props: {
    title: {
      type: String,
      default: null
    }
  },
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
  height: 80px;
  z-index: 5;
}

.toolbar {
  box-shadow: 10px 0 15px rgba(0, 0, 0, 0.2);
  height: 80px;
  position: fixed;
  width: 100%;
  background-color: white;
  z-index: 5;
  left: 0;
  top: 0;
  padding-left: 256px;
}

.nav-content-container {
  height: 52px;
}

.nav-title {
  position: relative;
}

.save-text {
  position: absolute;
  bottom: -18px;
  left: 0
}

.is-saving {
  height: 100%;
  display: flex;

  p {
    text-decoration: underline;
    align-self: center;
  }
}

@media only screen and (max-width: 600px) {
  .toolbar {
    height: 56px;
  }

  .nav-bar {
    height: 56px;
  }

  .logo img {
    height: 40px;

  }
}
</style>

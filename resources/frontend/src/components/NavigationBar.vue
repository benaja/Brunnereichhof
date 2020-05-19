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
      <v-container :fluid="fullWidth">
        <div class="d-flex align-center nav-content-container">
          <div class="nav-title">
            <h1 class="display-1 ma-0">
              {{ title }}
            </h1>
            <p
              v-if="$store.getters.isSaving || $store.getters.saved"
              class="my-0 mr-3 grey--text font-italic save-text"
            >
              <template v-if="$store.getters.isSaving">
                {{ $vuetify.breakpoint.xsOnly ? 'speichern...' : 'Wird gespeichert...' }}
              </template>
              <template v-else>
                Gespeichert
              </template>
            </p>
          </div>
          <slot></slot>
        </div>
      </v-container>
      <progress-linear
        :loading="loading"
        :color="color"
      />
    </div>
  </div>
</template>

<script>
export default {
  props: {
    title: {
      type: String,
      default: null
    },
    loading: {
      type: Boolean,
      default: false
    },
    color: {
      type: String,
      default: 'primary'
    },
    fullWidth: {
      type: Boolean,
      default: false
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
  left: 0;
  min-width: 150px;
}

.is-saving {
  height: 100%;
  display: flex;

  p {
    text-decoration: underline;
    align-self: center;
  }
}

.loading-bar {
  position: fixed;
  width: 100%;
  top: 76px
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

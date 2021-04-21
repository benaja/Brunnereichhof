<template>
  <fragment>
    <div class="nav-bar">
      <div
        class="toolbar"
        :class="{'without-nav': withoutNav}"
      >
        <v-container
          :fluid="fullWidth"
          class="pa-1 pa-sm-3"
        >
          <div class="d-flex align-center nav-content-container">
            <v-btn
              v-if="$auth.check()
                && $auth.user().hasPermission(
                  ['superadmin', 'customer'],
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
              @click="$store.commit('navigationBarModel', true)"
            >
              <v-icon>dehaze</v-icon>
            </v-btn>
            <div class="nav-title">
              <h1
                class="display-1 ma-0 d-md-block"
                :class="{'d-none': mobileTitleBelow || $vuetify.breakpoint.smAndDown}"
              >
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
            <!-- <div class="ml-auto"> -->
            <slot></slot>
            <!-- </div> -->
          </div>
        </v-container>
        <progress-linear
          :loading="loading"
          :color="color"
        />
      </div>
    </div>
    <h1
      v-if="mobileTitleBelow"
      class="display-1 mx-3 mt-5 d-md-none"
    >
      {{ title }}
    </h1>
  </fragment>
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
    },
    mobileTitleBelow: {
      type: Boolean,
      default: false
    },
    withoutNav: {
      type: Boolean,
      default: false
    },
    showTitleOnMobile: {
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

  &.without-nav {
    padding-left: 0;
  }
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

.profile {
  height: 50px;
  width: 50px;
  color: white;
  border-radius: 25px;
  text-align: center;
  line-height: 50px;
  span {
    display: inline-block;
    vertical-align: middle;
    line-height: normal;
  }
}

@media only screen and (max-width: 960px) {
  .toolbar {
    padding-left: 0px;
  }
}

@media only screen and (max-width: 600px) {
  .toolbar {
    height: 56px;
  }

  .nav-content-container {
    max-height: 45px;
  }

  .nav-bar {
    height: 56px;
  }

  .logo img {
    height: 40px;

  }
}
</style>

<template>
  <div id="app">
    <v-app>
      <template v-if="$auth.ready()">
        <v-navigation-drawer
          v-if="$auth.check()"
          v-model="navDrawerModel"
          :permanent="$vuetify.breakpoint.mdAndUp"
          app
          class="nav-drawer white"
        >
          <router-link
            tag="a"
            to="/"
          >
            <img
              class="logo"
              src="@/assets/images/logo.png"
            />
          </router-link>
          <v-list
            dense
            class="pt-0"
          >
            <v-list-item link>
              <v-list-item-content>
                <v-list-item-title
                  class="title"
                >
                  {{ $auth.user().firstname }} {{ $auth.user().lastname }}
                </v-list-item-title>
                <v-list-item-subtitle>{{ $auth.user().email }}</v-list-item-subtitle>
              </v-list-item-content>
            </v-list-item>
          </v-list>

          <v-divider></v-divider>

          <v-list
            nav
            dense
          >
            <template v-for="(navItem, index) of navItems">
              <v-list-item
                v-if="navItem.show && !navItem.items"
                :key="index"
                link
                :to="navItem.to"
                color="primary"
              >
                <v-list-item-icon>
                  <v-icon>{{ navItem.icon }}</v-icon>
                </v-list-item-icon>
                <v-list-item-title>{{ navItem.text }}</v-list-item-title>
              </v-list-item>
              <v-list-group
                v-else-if="navItem.show"
                :key="index"
              >
                <template v-slot:activator>
                  <v-list-item
                    link
                    :to="navItem.to"
                    :class="`ma-0 ${navItem.to ? '' : 'no-link'}`"
                  >
                    <v-list-item-icon>
                      <v-icon :class="navItem.to">
                        {{ navItem.icon }}
                      </v-icon>
                    </v-list-item-icon>
                    <v-list-item-title>{{ navItem.text }}</v-list-item-title>
                  </v-list-item>
                </template>
                <v-list-item
                  v-for="(navSubItem, i) of navItem.items.filter(item => item.show)"
                  :key="`${index}-${i}`"
                  link
                  :to="navSubItem.to"
                >
                  <v-list-item-icon>
                    <v-icon>{{ navSubItem.icon }}</v-icon>
                  </v-list-item-icon>
                  <v-list-item-title>{{ navSubItem.text }}</v-list-item-title>
                </v-list-item>
              </v-list-group>
            </template>
          </v-list>
          <template v-slot:append>
            <div class="pa-2">
              <v-btn
                block
                depressed
                dark
                class="px-2"
                @click="$auth.logout(); $router.push('/login')"
              >
                <v-icon class="mr-0">
                  exit_to_app
                </v-icon>
                <span
                  class="ml-2"
                >Logout</span>
              </v-btn>
            </div>
          </template>
        </v-navigation-drawer>
        <div class="router-view">
          <router-view />
        </div>
        <div
          v-if="$store"
          class="alerts"
        >
          <v-alert
            v-for="alert of alerts"
            :key="alert.key"
            class="ma-3"
            :type="alert.type || 'success'"
          >
            {{ alert.text }}
          </v-alert>
        </div>
      </template>
    </v-app>
  </div>
</template>

<script>
import { mapGetters } from 'vuex'

export default {
  computed: {
    ...mapGetters(['alerts', 'navigationBarModel']),
    navDrawerModel: {
      get() {
        return this.navigationBarModel
      },
      set(value) {
        this.$store.commit('navigationBarModel', value)
      }
    },
    navItems() {
      return [
        {
          to: '/kundenportal/home',
          text: 'Startseite',
          icon: 'home',
          show: this.hasPermission(['customer'])
        },
        {
          to: '/kundenportal/erfassen',
          text: 'Arbeiten erfassen',
          icon: 'edit',
          show: this.hasPermission(['customer'])
        },
        {
          to: '/kundenportal/wochenrapport',
          text: 'Wochenrapporte',
          icon: 'event_note',
          show: this.hasPermission(['customer'])
        },
        {
          to: '/dashboard',
          text: 'Dashboard',
          icon: 'dashboard',
          show: this.hasPermission(['superadmin'])
        },
        {
          to: '/time',
          text: 'Zeiterfassung',
          icon: 'access_time',
          show: this.hasPermission([], ['timerecord_read_write'])
        },
        {
          to: '/roomdispositioner',
          text: 'Roomdispositioner',
          icon: 'house',
          show: this.hasPermission(['superadmin'], ['roomdispositioner_read']),
          items: [
            {
              to: '/roomdispositioner/evaluation',
              text: 'Auswertung',
              icon: 'show_chart',
              show: true
            },
            {
              to: '/roomdispositioner/room-overview',
              text: 'Raumübersicht',
              icon: 'line_style',
              show: true
            },
            {
              to: '/rooms',
              text: 'Räume',
              icon: 'apartment',
              show: true
            },
            {
              to: '/beds',
              text: 'Betten',
              icon: 'single_bed',
              show: true
            },
            {
              to: '/inventars',
              text: 'Inventar',
              icon: 'category',
              show: true
            }
          ]
        },
        {
          text: 'Benutzerverwaltung',
          icon: 'mdi-account-multiple',
          fixArrow: true,
          show: this.hasPermission(
            ['superadmin'],
            ['customer_read', 'employee_read', 'employee_preview_read', 'worker_read', 'roomdispositioner_read'],
          ),
          items: [
            {
              to: '/customer',
              text: 'Kunden',
              icon: 'supervisor_account',
              show: this.hasPermission(['superadmin'], ['customer_read'])
            },
            {
              to: '/employee',
              text: 'Mitarbeiter',
              icon: 'account_circle',
              show: this.hasPermission(['superadmin'], ['employee_read', 'employee_preview_read'])
            },
            {
              to: '/guests',
              text: 'Gäste',
              icon: 'account_box',
              show: this.hasPermission(['superadmin'], ['roomdispositioner_read'])
            },
            {
              to: '/worker',
              text: 'Hofmitarbeiter',
              icon: 'perm_identity',
              show: this.hasPermission(['superadmin'], ['worker_read'])
            }
          ]
        },
        {
          to: '/rapport',
          text: 'Wochenrapport',
          icon: 'event_note',
          show: this.hasPermission(['superadmin'], ['rapport_read'])
        },
        {
          to: '/evaluation',
          text: 'Auswertung',
          icon: 'show_chart',
          show: this.hasPermission(['superadmin'], ['timerecord_stats', 'evaluation_employee', 'evaluation_customer'])
        },
        {
          to: '/hourrecords',
          text: 'Stundenangaben',
          icon: 'insert_chart',
          show: this.hasPermission(['superadmin'], ['hourrecord_read'])
        },
        {
          to: '/roles',
          text: 'Rollen',
          icon: 'fingerprint',
          show: this.hasPermission(['superadmin'], ['role_write'])
        },
        {
          to: '/settings',
          text: 'Einstellungen',
          icon: 'settings',
          show: this.hasPermission(['superadmin'], ['settings_read'])
        },
        {
          to: '/release-notes',
          text: 'Release Notes',
          icon: 'fiber_new',
          show: this.hasPermission(['superadmin'])
        }
      ]
    }
  },
  mounted() {
    this.setCssWindowHeight()
    window.addEventListener('resize', this.setCssWindowHeight)
  },
  methods: {
    setCssWindowHeight() {
      const vh = window.innerHeight * 0.01
      document.documentElement.style.setProperty('--vh', `${vh}px`)
    },
    hasPermission(urserTypes, rules) {
      return this.$auth.check() && this.$auth.user().hasPermission(urserTypes, rules)
    }
  }
}
</script>

<style lang="scss">
h1 {
  margin: 12px 0;
}

body {
  background-color: rgb(238, 237, 237);
}

#app .v-application--wrap {
  min-height: calc(var(--vh, 1vh) * 100);
}

* {
  font-family: Roboto, sans-serif;
}

.router-view {
  margin-left: 256px;
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

.v-navigation-drawer .v-list--nav {
  .v-list-group__header {
    padding-left: 0;

    .no-link + * .v-icon {
      margin-right: 4px;
    }
  }
}

.logo {
  width: 80%;
  margin: 10px 5%;
}

.v-navigation-drawer--absolute {
  z-index: 10 !important;
}

@media only screen and (max-width: 960px) {
  .router-view {
    margin-left: 0;
  }
}
</style>

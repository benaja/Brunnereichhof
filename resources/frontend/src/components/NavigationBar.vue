<template>
  <div class="nav-bar">
    <v-toolbar absolute>
      <v-menu
        transition="slide-x-transition"
        v-if="$route.path !== '/' && $route.path !== '/login' && $auth.check()"
      >
        <template v-slot:activator="{ on }">
          <v-btn
            v-on="on"
            icon
            class="d-lg-none"
            v-if="$auth.check() && $auth.user().hasPermission(['superadmin',], ['customer_read', 'employee_read', 'employee_preview_read', 'worker_read', 'rapport_read', 'roomdispositioner_read', 'timerecord_stats', 'hourrecord_read', 'evaluation_employee', 'evaluation_customer'])"
          >
            <v-icon>dehaze</v-icon>
          </v-btn>
        </template>
        <v-list>
          <v-list-item
            to="/time"
            v-if="$auth.check() && $auth.user().hasPermission([], ['timerecord_read_write'])"
          >Zeiterfassung</v-list-item>
          <template v-if="$auth.user().hasPermission(['superadmin'], ['roomdispositioner_read'])">
            <v-list-item to="/roomdispositioner">Roomdispositioner</v-list-item>
            <v-list-item to="/roomdispositioner/evaluation">
              <v-list-item>Auswertung</v-list-item>
            </v-list-item>
            <v-list-item to="/rooms">
              <v-list-item>Räume</v-list-item>
            </v-list-item>
            <v-list-item to="/beds">
              <v-list-item>Betten</v-list-item>
            </v-list-item>
            <v-list-item to="/inventars">
              <v-list-item>Inventar</v-list-item>
            </v-list-item>
          </template>
          <v-list-item
            v-if="$auth.user().hasPermission(['superadmin'], ['customer_read', 'employee_read', 'employee_preview_read', 'worker_read', 'roomdispositioner_read'])"
          >Benutzerverwaltung</v-list-item>
          <v-list-item
            to="/customer"
            v-if="$auth.user().hasPermission(['superadmin'], ['customer_read'])"
          >
            <v-list-item>Kunden</v-list-item>
          </v-list-item>
          <v-list-item
            to="/employee"
            v-if="$auth.user().hasPermission(['superadmin'], ['employee_read', 'employee_preview_read'])"
          >
            <v-list-item>Mitarbeiter</v-list-item>
          </v-list-item>
          <v-list-item
            to="/guests"
            v-if="$auth.user().hasPermission(['superadmin'], ['roomdispositioner_read'])"
          >
            <v-list-item>Gäste</v-list-item>
          </v-list-item>
          <v-list-item
            to="/worker"
            v-if="$auth.user().hasPermission(['superadmin'], ['worker_read'])"
          >
            <v-list-item>Hofmitarbeiter</v-list-item>
          </v-list-item>
          <v-list-item
            to="/rapport"
            v-if="$auth.user().hasPermission(['superadmin'], ['rapport_read'])"
          >Wochenrapport</v-list-item>
          <v-list-item
            to="/evaluation"
            v-if="$auth.user().hasPermission(['superadmin'], ['timerecord_stats', 'evaluation_employee', 'evaluation_customer'])"
          >Auswertung</v-list-item>
          <v-list-item
            to="/hourrecords"
            v-if="$auth.user().hasPermission(['superadmin'], ['hourrecord_read'])"
          >Stundenangaben</v-list-item>
        </v-list>
      </v-menu>

      <v-toolbar-title>
        <router-link tag="div" to="/" class="logo">
          <img src="@/assets/images/logo.png" />
        </router-link>
      </v-toolbar-title>
      <v-spacer></v-spacer>
      <v-toolbar-items
        class="d-none d-lg-block"
        v-if="$auth.check() && $auth.user().hasPermission(['superadmin',], ['customer_read', 'employee_read', 'employee_preview_read', 'worker_read', 'rapport_read', 'roomdispositioner_read', 'timerecord_stats', 'hourrecord_read', 'evaluation_employee', 'evaluation_customer'])"
      >
        <p
          class="saving my-4"
          v-if="showSaveSateRouteNames.includes($route.name)"
        >{{$store.getters.saveState.isSaving ? 'speichern...' : 'gespeichert'}}</p>
        <v-btn
          to="/time"
          text
          v-if="$auth.user().hasPermission([], ['timerecord_read_write'])"
        >Zeiterfassung</v-btn>
        <v-menu
          button
          offset-y
          open-on-hover
          v-if="$auth.user().hasPermission(['superadmin'], ['roomdispositioner_read'])"
        >
          <template v-slot:activator="{ on }">
            <v-btn text v-on="on" to="/Roomdispositioner" offset-y>Roomdispositioner</v-btn>
          </template>
          <v-list class="pa-0">
            <v-list-item
              to="/roomdispositioner/evaluation"
              class="black--text"
              active-class="blue--text"
            >Auswertung</v-list-item>
            <v-list-item to="/rooms" class="black--text" active-class="blue--text">Räume</v-list-item>
            <v-list-item to="/beds" class="black--text" active-class="blue--text">Betten</v-list-item>
            <v-list-item to="/inventars" class="black--text" active-class="blue--text">Inventar</v-list-item>
          </v-list>
        </v-menu>
        <v-menu
          buttom
          offset-y
          open-on-hover
          v-if="$auth.user().hasPermission(['superadmin'], ['customer_read', 'employee_read', 'employee_preview_read', 'worker_read', 'roomdispositioner_read'])"
        >
          <template v-slot:activator="{ on }">
            <v-btn text v-on="on">Benutzerverwaltung</v-btn>
          </template>
          <v-list class="pa-0">
            <v-list-item
              to="/customer"
              v-if="$auth.user().hasPermission(['superadmin'], ['customer_read'])"
            >Kunden</v-list-item>
            <v-list-item
              to="/employee"
              v-if="$auth.user().hasPermission(['superadmin'], ['employee_read', 'employee_preview_read'])"
            >Mitarbeiter</v-list-item>
            <v-list-item
              to="/guests"
              v-if="$auth.user().hasPermission(['superadmin'], ['roomdispositioner_read'])"
            >Gäste</v-list-item>
            <v-list-item
              to="/worker"
              v-if="$auth.user().hasPermission(['superadmin'], ['worker_read'])"
            >Hofmitarbeiter</v-list-item>
          </v-list>
        </v-menu>
        <v-btn
          to="/rapport"
          text
          v-if="$auth.user().hasPermission(['superadmin'], ['rapport_read'])"
        >Wochenrapport</v-btn>
        <v-btn
          to="/evaluation"
          text
          v-if="$auth.user().hasPermission(['superadmin'], ['timerecord_stats', 'evaluation_employee', 'evaluation_customer'])"
        >Auswertung</v-btn>
        <v-btn
          to="/hourrecords"
          text
          v-if="$auth.user().hasPermission(['superadmin'], ['hourrecord_read'])"
        >Stundenangaben</v-btn>
      </v-toolbar-items>
      <v-menu buttom left v-if="$auth.check()">
        <template v-slot:activator="{ on }">
          <v-btn slot="activator" icon v-on="on">
            <v-icon>account_circle</v-icon>
          </v-btn>
        </template>

        <v-list>
          <v-list-item>{{$auth.user().firstname}} {{$auth.user().lastname}}</v-list-item>
          <v-list-item to="/profile/edit">Passwort ändern</v-list-item>
          <v-list-item
            to="/settings"
            v-if="$auth.user().hasPermission(['superadmin'], ['settings_read'])"
          >Einstellungen</v-list-item>
          <v-list-item
            to="/roles"
            v-if="$auth.user().hasPermission(['superadmin'], ['role_write'])"
          >Rollen</v-list-item>
          <v-list-item
            to="/release-notes"
            v-if="$auth.user().hasPermission(['superadmin'])"
          >Release Notes</v-list-item>
          <v-list-item @click="$auth.logout(); $router.push('/login')">Abmelden</v-list-item>
        </v-list>
      </v-menu>
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
      let isMobile = window.innerWidth < 960
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

.nav {
  width: 580px;
  height: 100%;
  margin-left: auto;
  margin-right: auto;
}

.logo {
  width: 140px;
  cursor: pointer;

  img {
    position: absolute;
    bottom: 10%;
    height: 80%;
  }
}

.login {
  i {
    color: black;
    float: right;
    font-size: 40px;
  }
}

#menu-button {
  display: none;
}

.nav {
  > * {
    float: left;
  }
  > a {
    width: 25%;
  }
  a {
    height: 100%;
    color: black;
    display: block;
    float: left;
    text-align: center;

    &:hover {
      background-color: white;
    }
  }
}

.saving {
  line-height: 2em;
  vertical-align: middle;
  display: inline;
}
</style>

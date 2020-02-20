import Vue from 'vue'
import Router from 'vue-router'
import Home from './views/Home.vue'

Vue.use(Router)

export default new Router({
  mode: 'history',
  base: process.env.BASE_URL,
  routes: [
    {
      path: '/',
      name: 'home',
      component: Home,
      meta: {
        auth: true
      }
    },
    {
      path: '/login',
      name: 'Login',
      component: () => import('./views/auth/Login.vue')
    },
    {
      path: '/reset-password',
      name: 'ResetPassword',
      component: () => import('./views/auth/ResetPassword.vue')
    },
    {
      path: '/set-password',
      props: route => ({ token: route.query.token, userId: route.query.userId }),
      name: 'SetPassword',
      component: () => import('./views/auth/SetPassword')
    },
    {
      path: '/customer',
      name: 'Customers',
      component: () => import('./views/Customer/Customers'),
      meta: {
        auth: true
      }
    },
    {
      path: '/customer/add',
      name: 'AddCustomer',
      component: () => import('./views/Customer/AddCustomer'),
      meta: {
        auth: true
      }
    },
    {
      path: '/customer/:id',
      name: 'Customer',
      component: () => import('./views/Customer/Customer'),
      meta: {
        auth: true
      }
    },
    {
      path: '/employee',
      name: 'Employees',
      component: () => import('./views/Employee/Employees'),
      meta: {
        auth: true
      }
    },
    {
      path: '/guests',
      name: 'Guests',
      component: () => import('./views/Employee/Guests'),
      meta: {
        auth: true
      }
    },
    {
      path: '/employee/add',
      name: 'AddEmployee',
      component: () => import('./views/Employee/AddEmployee'),
      meta: {
        auth: true
      }
    },
    {
      path: '/employee/:id',
      alias: '/guest/:id',
      name: 'Employee',
      component: () => import('./views/Employee/Employee'),
      meta: {
        auth: true
      }
    },
    {
      path: '/worker',
      name: 'Workers',
      component: () => import('./views/Worker/Workers'),
      meta: {
        auth: true
      }
    },
    {
      path: '/worker/add',
      name: 'AddWorker',
      component: () => import('./views/Worker/AddWorker'),
      meta: {
        auth: true
      }
    },
    {
      path: '/worker/:id',
      name: 'Worker',
      component: () => import('./views/Worker/Worker'),
      meta: {
        auth: true
      }
    },
    {
      path: '/rapport',
      name: 'RapportOverview',
      component: () => import('./views/Rapport/RapportOverview'),
      meta: {
        auth: true
      }
    },
    {
      path: '/rapport/week/:week',
      name: 'RapportWeek',
      component: () => import('./views/Rapport/RapportWeek'),
      meta: {
        auth: true
      }
    },
    {
      path: '/rapport/:id',
      name: 'Rapport',
      component: () => import('./views/Rapport/Rapport'),
      meta: {
        auth: true
      }
    },
    {
      path: '/evaluation',
      name: 'Evaluation',
      component: () => import('./views/Evaluation/Evaluation'),
      meta: {
        auth: true
      }
    },
    {
      path: '/time',
      name: 'Time',
      component: () => import('./views/Time'),
      meta: {
        auth: true
      }
    },
    {
      path: '/profile/edit',
      name: 'EditProfile',
      component: () => import('./views/Profile/EditProfile'),
      meta: {
        auth: true
      }
    },
    {
      path: '/dashboard',
      name: 'Dashboard',
      component: () => import('./views/Dashboard'),
      meta: {
        auth: true
      }
    },
    {
      path: '/hourrecords',
      name: 'Hourrecords',
      component: () => import('./views/Hourrecords/Hourrecords'),
      meta: {
        auth: true
      }
    },
    {
      path: '/hourrecords/:year/:week',
      name: 'HourrecordDetails',
      component: () => import('./views/Hourrecords/HourrecordDetails'),
      props: route => ({ edit: route.query.edit === 'true' }),
      meta: {
        auth: true
      }
    },
    {
      path: '/settings',
      name: 'Settings',
      component: () => import('./views/Settings'),
      meta: {
        auth: true
      }
    },
    {
      path: '/kundenportal',
      name: 'CustomerPortal',
      component: () => import('./views/CustomerPortal/CustomerPortal'),
      meta: {
        auth: true
      }
    },
    {
      path: '/kundenportal/erfassen',
      name: 'WorkRecord',
      component: () => import('./views/CustomerPortal/WorkRecord'),
      meta: {
        auth: true
      }
    },
    {
      path: '/kundenportal/erfassen/details',
      name: 'WorkRecordDetails',
      component: () => import('./views/CustomerPortal/WorkRecordDetails'),
      meta: {
        auth: true
      }
    },
    {
      path: '/kundenportal/wochenrapport',
      name: 'CustomerRapportOverview',
      component: () => import('./views/CustomerPortal/WeekRapports'),
      meta: {
        auth: true
      }
    },
    {
      path: '/kundenportal/wochenrapport/:id',
      name: 'CustomerRapport',
      component: () => import('./views/CustomerPortal/Rapport'),
      meta: {
        auth: true
      },
      props: true
    },
    {
      path: '/roomdispositioner',
      name: 'Roomdispositioner',
      component: () => import('./views/Roomdispositioner/Dashboard'),
      meta: {
        auth: true
      }
    },
    {
      path: '/roomdispositioner/evaluation',
      name: 'RoomdispositionerEvaluation',
      component: () => import('./views/Roomdispositioner/RoomdispositionerEvaluation'),
      meta: {
        auth: true
      }
    },
    {
      path: '/rooms',
      name: 'Rooms',
      component: () => import('./views/Roomdispositioner/Room/Rooms'),
      meta: {
        auth: true
      }
    },
    {
      path: '/rooms/add',
      name: 'AddRoom',
      component: () => import('./views/Roomdispositioner/Room/AddRoom'),
      meta: {
        auth: true
      }
    },
    {
      path: '/rooms/:id',
      name: 'ShowRoom',
      component: () => import('./views/Roomdispositioner/Room/ShowRoom'),
      meta: {
        auth: true
      }
    },
    {
      path: '/inventars',
      name: 'Inventars',
      component: () => import('./views/Roomdispositioner/Inventar/Inventars'),
      meta: {
        auth: true
      }
    },
    {
      path: '/inventars/:id',
      name: 'ShowInventar',
      component: () => import('./views/Roomdispositioner/Inventar/ShowInventar'),
      meta: {
        auth: true
      }
    },
    {
      path: '/beds',
      name: 'Beds',
      component: () => import('./views/Roomdispositioner/Bed/Beds'),
      meta: {
        auth: true
      }
    },
    {
      path: '/beds/:id',
      name: 'ShowBed',
      component: () => import('./views/Roomdispositioner/Bed/ShowBed'),
      meta: {
        auth: true
      }
    },
    {
      path: '/roles',
      name: 'Roles',
      component: () => import('./views/Authorization/Roles'),
      meta: {
        auth: true
      }
    },
    {
      path: '/roles/:id',
      name: 'ShowRole',
      component: () => import('./views/Authorization/ShowRole'),
      meta: {
        auth: true
      }
    },
    {
      path: '/release-notes',
      name: 'ReleaseNotes',
      component: () => import('./views/ReleaseNotes'),
      meta: {
        auth: true
      }
    }
  ],
  scrollBehavior() {
    return { x: 0, y: 0 }
  }
})

import Vue from 'vue'
import vueAuthAxios from '@websanova/vue-auth/drivers/http/axios.1.x'
import vueAuthRouter from '@websanova/vue-auth/drivers/router/vue-router.2.x'
import auth from '@websanova/vue-auth'
import axios from 'axios'

export default {
  register() {
    Vue.use(auth, {
      auth: {
        request (req, token) {
          this.http.setHeaders.call(this, req, {
            Authorization: `Bearer ${token}`
          })
        },
        response (res) {
          return res.data.access_token
        }
      },
      http: vueAuthAxios,
      router: vueAuthRouter,
      parseUserData(body) {
        console.log(body)
        const user = body.data
        if (user) {
          user.hasPermission = function(types, roles = []) {
            if (!Array.isArray(types)) types = [types]
            if (types.includes(this.type.name)) return true

            return this.role && !!this.role.authorization_rules.find(r => roles.includes(r.name))
          }
        }
        return {
          hasPermission: () => {}
        }
      },
      refreshData: { enabled: false, interval: 0 }
      // fetchData: {
      //   enabled: false
      // }
    })

    // axios.get('auth/user').then(response => {
    //   console.log(response)
    // })
  }
}

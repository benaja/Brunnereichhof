import Vue from 'vue'
import vueAuthAxios from '@websanova/vue-auth/drivers/http/axios.1.x'
import vueAuthRouter from '@websanova/vue-auth/drivers/router/vue-router.2.x'
import auth from '@websanova/vue-auth'

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
        const user = body.data
        if (user) {
          user.hasPermission = function(types, roles = []) {
            if (!Array.isArray(types)) types = [types]
            if (types.includes(this.type.name)) return true

            return this.role && !!this.role.authorization_rules.find(r => roles.includes(r.name))
          }
        }
        return user
      },
      refreshData: { enabled: false, interval: 0 }
    })
  }
}

<template>
  <div></div>
</template>

<script>
import { UserType } from '@/utils'


export default {
  mounted() {
    this.axios.$get(`/users/${this.$route.params.id}`).then(user => {
      if (user.type_id === UserType.Employee) {
        this.$router.replace(`/employee/${user.employee.id}`)
      } else if (user.type_id === UserType.Customer) {
        this.$router.replace(`/customer/${user.customer.id}`)
      } else {
        this.$router.replace(`/worker/${user.id}`)
      }
    })
  }
}
</script>

<template>
  <div class="home"></div>
</template>

<script>
export default {
  name: 'Home',
  components: {},
  mounted() {
    if (this.$auth.user().isPasswordChanged !== 1) this.$router.push('/profile/edit')
    else if (this.$auth.user().hasPermission(['superadmin'])) this.$router.push('/dashboard')
    else if (this.$auth.user().type_id === 1) this.$router.push('/kundenportal/home')
    else if (this.$auth.user().hasPermission([], ['timerecord_read_write'])) this.$router.push('/time')
    else if (this.$auth.user().hasPermission([], ['rapport_read'])) this.$router.push('/rapport')
    else if (this.$auth.user().hasPermission([], ['employee_preview_read', 'employee_read'])) this.$router.push('/employee')
    else if (this.$auth.user().hasPermission([], ['roomdispositioner_read'])) this.$router.push('/roomdispositioner')
    else if (this.$auth.user().hasPermission([], ['hourrecord_read'])) this.$router.push('/hourrecords')
    else if (this.$auth.user().hasPermission([], ['worker_read'])) this.$router.push('/worker')
    else if (this.$auth.user().hasPermission([], ['customer_read'])) this.$router.push('/customer')
    else if (this.$auth.user().hasPermission([], ['timerecord_stats', 'evaluation_customer', 'evaluation_employee'])) {
      this.$router.push('/evaluation')
    }
  }
}
</script>

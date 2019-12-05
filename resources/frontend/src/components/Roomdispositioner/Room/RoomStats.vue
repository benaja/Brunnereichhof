<template>
  <div></div>
</template>

<script>
export default {
  mounted() {
    this.axios.get(`/rooms/${this.$route.params.id}/reservations/month/2019-12-01`).then(response => {
      console.log(response.data)
    })
  }
}
</script>

<style>
</style>

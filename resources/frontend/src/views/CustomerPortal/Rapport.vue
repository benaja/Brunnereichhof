<template>
  <div>
    <h1 class="text-center my-4">Wochenrapport {{formatedWeek}}</h1>
  </div>
</template>

<script>
export default {
  props: {
    id: {
      type: String,
      default: null
    }
  },
  data() {
    return {
      rapport: {}
    }
  },
  computed: {
    formatedWeek() {
      let date = this.$moment(this.rapport.startdate, 'YYYY-MM-DD', 'de-ch')
      return `${date.format('W (DD.MM.YYYY')} - ${date
        .clone()
        .add(6, 'days')
        .format('DD.MM.YYYY')})`
    }
  },
  mounted() {
    this.axios.get(`/rapport/${this.id}`).then(response => {
      this.rapport = response.data
    })
  }
}
</script>

<style>
</style>

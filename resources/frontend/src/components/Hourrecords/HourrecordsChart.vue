
<script>
import { Bar, mixins } from 'vue-chartjs'

export default {
  name: 'HourrecordsChart',
  extends: Bar,
  mixins: [mixins.reactiveProp],
  props: {
    labels: Array,
    chartData: Object
  },
  mounted() {
    this.renderChart(this.chartData, {
      height: '500px',
      style: {
        height: '500px'
      }
    })
  }
}
</script>

<style lang="scss" scoped>
</style>

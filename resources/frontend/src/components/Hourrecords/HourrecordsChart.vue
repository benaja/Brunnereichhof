
<script>
import { Bar, mixins } from 'vue-chartjs'

const { reactiveProp } = mixins
export default {
  name: 'HourrecordsChart',
  extends: Bar,
  mixins: [reactiveProp],
  mounted() {
    return this.renderChart(this.chartData, {
      height: '500px',
      style: {
        height: '500px'
      }
    })
  }
}
</script>

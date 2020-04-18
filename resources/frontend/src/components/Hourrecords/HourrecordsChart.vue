
<script>
import { Bar, mixins } from 'vue-chartjs'

const { reactiveProp } = mixins
export default {
  name: 'HourrecordsChart',
  extends: Bar,
  mixins: [reactiveProp],
  props: {
    options: {
      type: Object,
      default: () => ({})
    }
  },
  mounted() {
    return this.renderChart(this.chartData, this.options)
  }
}
</script>


<script>
import { Line, mixins } from 'vue-chartjs'

export default {
  name: 'Chart',
  extends: Line,
  mixins: [mixins.reactiveProp],
  props: {
    chartData: Object,
    options: {
      type: Object
    }
  },
  mounted() {
    console.log(this.options)
    this.renderChart(this.chartData, this.options)
  }
}
</script>

<style lang="scss" scoped>
</style>

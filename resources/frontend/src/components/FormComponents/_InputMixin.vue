<script>
import _ from 'lodash'

export default {
  props: {
    original: {
      type: [Object, String, Number],
      default: undefined
    },
    rules: {
      type: Array,
      default: () => []
    }
  },
  computed: {
    canRestore() {
      if (!this.original || this.disabled || this.readonly) return false
      if (typeof this.original === 'object') return !this._.isEqual(this.original, this.$attrs.value)
      if (this.type === 'number') return Number(this.value) !== Number(this.original)
      if (this.$attrs.type === 'year' || this.$attrs.type === 'month' || this.$attrs.type === 'date') {
        return !this.$moment(this.value).isSame(this.original, 'day')
      }
      return this.original !== this.value
    },
    computedRestoreMessage() {
      return this.canRestore ? `Ursprünglicher Wert wiederherstellen <br /><br /> Ursprünglicher Wert: <i>${this.original}</i>` : null
    }
  },
  methods: {
    restoreOriginal() {
      this.$emit('input', this.original)
      this.$emit('change')
    },
    input(value) {
      this.$emit('input', value)
      this.changeDebounce(value)
    },
    changeDebounce: _.debounce(function(value) {
      this.$emit('change', value)
    }, 500)
  }
}
</script>

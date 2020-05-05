<script>
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
      console.log(this.original)
      if (this.original === undefined || this.disabled || this.readonly) return false
      if (typeof this.original === 'object') return !this._.isEqual(this.original, this.$attrs.value)
      if (this.type === 'number') return Number(this.value) !== Number(this.original)
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
    }
  }
}
</script>

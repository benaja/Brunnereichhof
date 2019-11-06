<template>
  <span class="loading-dots">
    <span>.</span>
    <span>.</span>
    <span>.</span>
  </span>
</template>

<script>
export default {
  name: 'LoadingDots'
}
</script>

<style lang="scss" scoped>
@keyframes blink {
  0% {
    opacity: 0.2;
  }
  20% {
    opacity: 1;
  }
  100% {
    opacity: 0.2;
  }
}

.loading-dots {
  margin-left: 0.4em;
}

.loading-dots span {
  animation-name: blink;
  animation-duration: 1.4s;
  animation-iteration-count: infinite;
  animation-fill-mode: both;
}

.loading-dots span:nth-child(2) {
  animation-delay: 0.2s;
}

.loading-dots span:nth-child(3) {
  animation-delay: 0.4s;
}
</style>

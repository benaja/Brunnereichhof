<template>
  <div class="loading-page">
    <div class="center">
      <spinner></spinner>
    </div>
  </div>
</template>

<script>
import Spinner from '@/components/general/Spinner'

export default {
  name: 'LoadingPage',
  components: {
    Spinner
  }
}
</script>

<style lang="scss" scoped>
  .loading-page{
    width: 100vw;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    background-color: rgba(255, 255, 255, 0.5);
    z-index: 1000;
  }

  .center{
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 100%;
  }
</style>

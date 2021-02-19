<template>
  <button
    class="drag-item-container"
  >
    <slot>
      {{ name }}
      <div
        v-if="image"
        class="profile-image"
        :style="{ backgroundImage: `url(${image})`}"
      >
      </div>
    </slot>
  </button>
</template>

<script>
export default {
  props: {
    name: {
      type: String,
      default: null
    },
    image: {
      type: String,
      default: null
    }
  }
}
</script>

<style lang="scss" scoped>
.drag-item-container {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 4px 8px;
  width: 100%;
  background-color: white;
  border-radius: 2px;
  margin: 10px 0;
  box-shadow: 0 2px 2px 2px rgba(0, 0, 0, 0.06);
}

.profile-image {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  background-size: cover;
  background-position: center;
}
</style>

<template>
  <div class="mb-1">
    <v-list-item
      class="drag-item"
    >
      <div
        class="drag-item-container"
        :class="[color]"
      >
        <slot>
          <slot name="name">
            {{ name }}
          </slot>
          <div
            v-if="image"
            class="profile-image"
            :style="{ backgroundImage: `url(${image})`}"
          >
          </div>
        </slot>
      </div>
    </v-list-item>
  </div>
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
    },
    color: {
      type: String,
      default: null
    }
  }
}
</script>

<style lang="scss" scoped>
.drag-item {
  cursor: pointer;
}

.drag-item-container {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 4px 2px 2px 2px;
  width: 100%;
}

.profile-image {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  background-size: cover;
  background-position: center;
}
</style>

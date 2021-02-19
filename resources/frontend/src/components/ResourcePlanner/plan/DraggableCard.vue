<template>
  <div>
    <v-list-item class="drag-item">
      <div class="drag-item-container">
        <slot>
          {{ name }}
          <div
            v-if="image"
            class="profile-image"
            :style="{ backgroundImage: `url(${image})`}"
          >
          </div>
        </slot>
      </div>
    </v-list-item>
    <v-divider></v-divider>
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
    }
  }
}
</script>

<style lang="scss" scoped>
.drag-item {
  background-color: white;
  cursor: pointer;
}

.drag-item-container {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 4px 2px 2px 2px;
  width: 100%;
  background-color: white;
}

.profile-image {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  background-size: cover;
  background-position: center;
}
</style>

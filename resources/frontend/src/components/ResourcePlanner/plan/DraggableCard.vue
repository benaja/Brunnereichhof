<template>
  <div class="mb-1">
    <v-list-item
      class="drag-item"
    >
      <div
        class="drag-item-container"
        :class="[color, {flex: !noFlex}]"
      >
        <slot>
          <slot name="name">
            {{ name }}
          </slot>
          <avatar-image
            :avatar="avatar"
            :image="image"
          ></avatar-image>
        </slot>
      </div>
    </v-list-item>
  </div>
</template>

<script>
import AvatarImage from './AvatarImage'

export default {
  components: {
    AvatarImage
  },
  props: {
    name: {
      type: String,
      default: null
    },
    avatar: {
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
    },
    noFlex: {
      type: Boolean,
      default: false
    }
  }
}
</script>

<style lang="scss" scoped>
.drag-item {
  cursor: pointer;
}

.drag-item-container {
  padding: 4px 2px 2px 2px;
  width: 100%;

  &.flex {
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
}

.profile-image {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  background-size: cover;
  background-position: center;
}
</style>

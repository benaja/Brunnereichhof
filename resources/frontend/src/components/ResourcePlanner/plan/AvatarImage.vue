<template>
  <v-dialog width="500">
    <template v-slot:activator="{on}">
      <div
        v-if="avatar"
        class="avatar-image"
        :style="{ backgroundImage: `url(${avatar})`}"
        v-on="on"
      >
      </div>
    </template>
    <img :src="image">
  </v-dialog>
</template>

<script>
export default {
  props: {
    avatar: {
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
.avatar-image {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background-size: cover;
  background-position: center;
  flex-shrink: 0;
}

img {
  max-width: 100%;
  max-height: 70vh;
}
</style>

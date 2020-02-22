<template>
  <v-col cols="12" md="4" class="text-center mb-5">
    <img :src="icon" class="card-icon" />
    <h3 class="headline mt-4 font-weight-bold mb-2">{{title}}</h3>
    <p>{{text}}</p>
    <v-btn text color="primary" :to="buttonLink" @click="$emit('clickLink')">{{buttonText}}</v-btn>
  </v-col>
</template>

<script>
export default {
  props: {
    icon: {
      type: String,
      default: null
    },
    title: {
      type: String,
      default: null
    },
    text: {
      type: String,
      default: null
    },
    buttonText: {
      type: String,
      default: null
    },
    buttonLink: {
      type: String,
      default: null
    }
  }
}
</script>

<style lang="scss" scoped>
.card-icon {
  display: block;
  width: 30%;
  margin: 0 auto;
  max-width: 80px;
}
</style>

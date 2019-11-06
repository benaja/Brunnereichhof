<template>
  <transition name="move">
    <div :class="['time-type-form', {desktop: !$store.getters.isMobile}]" v-if="open">
      <div class="min-height-px">
        <div class="min-height-100">
          <div class="pa-3 content">
            <slot></slot>
          </div>
          <div class="footer elevation-9 pt-1">
            <v-row wrap>
              <v-col cols="6">
                <p class="text-center mb-0">
                  <v-btn depressed medium @click="$emit('cancel')">Abbrechen</v-btn>
                </p>
              </v-col>
              <v-col cols="6">
                <p class="text-center mb-0">
                  <v-btn
                    color="primary"
                    depressed
                    medium
                    width="100px"
                    @click="$emit('save')"
                    :disabled="!saveButtonActive"
                  >Speichern</v-btn>
                </p>
              </v-col>
            </v-row>
          </div>
        </div>
      </div>
    </div>
  </transition>
</template>

<script>
export default {
  name: 'TypeTypeForm',
  props: {
    open: Boolean,
    saveButtonActive: Boolean
  }
}
</script>

<style lang="scss" scoped>
.time-type-form {
  position: absolute;
  min-height: 100vh;
  width: 100vw;
  background-color: white;
  top: 0;
  left: 0;
  z-index: 5;

  &.desktop {
    position: absolute;
    width: 100%;
    overflow: hidden;
    min-height: 100%;

    .footer {
      width: 100%;
      position: absolute;
    }
  }
}

.footer {
  height: 65px;
  width: 100vw;
  background-color: white;
  position: absolute;
  bottom: 0;
  left: 0;
}

.move-enter-active,
.move-leave-active {
  transition: translateY(0);
  transition-duration: 0.3s;

  &.desktop {
    transition: translateX(0);
  }
}
.move-enter, .move-leave-to /* .fade-leave-active below version 2.1.8 */ {
  transform: translateY(130vh);
  &.desktop {
    transform: translateX(-400px);
  }
}

@media only screen and (max-width: 960px) {
  .min-height-px {
    position: relative;
    min-height: 540px;

    > .min-height-100 {
      min-height: 100vh;
    }
  }
}
</style>

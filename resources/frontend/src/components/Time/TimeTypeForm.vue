<template>
  <transition name="move">
    <div class="time-type-form" v-if="open">
      <div class="min-height-px">
        <div class="min-height-100">
          <div class="header py-3">
            <v-btn icon small class="ml-3 mt-1" @click="$emit('cancel')">
              <v-icon>close</v-icon>
            </v-btn>
            <v-btn icon small class="ml-3 mt-1" @click="$emit('deleteTimerecord')" v-if="edit">
              <v-icon>delete</v-icon>
            </v-btn>
            <v-btn
              color="primary"
              depressed
              medium
              class="float-right mr-3"
              @click="$emit('save')"
            >{{ edit ? 'Aktualisieren' : 'Speichern' }}</v-btn>
          </div>
          <v-divider></v-divider>
          <div class="pa-3 content">
            <div class="py-2"></div>
            <slot></slot>
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
    edit: Boolean
  }
}
</script>

<style lang="scss" scoped>
.time-type-form {
  position: fixed;
  min-height: 100vh;
  width: 100vw;
  background-color: white;
  top: 0;
  left: 0;
  z-index: 5;
}

.header {
  height: 60px;
}

.header-placeholder {
  height: 60px;
}

.content {
  max-height: calc(100vh - 60px);
  overflow-y: auto;
}

.move-enter-active,
.move-leave-active {
  transition: translateY(0);
  transition-duration: 0.3s;
}
.move-enter, .move-leave-to /* .fade-leave-active below version 2.1.8 */ {
  transform: translateY(130vh);
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

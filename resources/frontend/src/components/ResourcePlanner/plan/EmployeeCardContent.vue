<template>
  <fragment>
    <div>
      <p class="mb-0">
        <span class="font-weight-bold">
          {{ employee.lastname }} {{ employee.firstname }}
        </span>
        <span
          v-if="employee.callname"
          class="font-italic"
        >{{ employee.callname }}</span>
        <span v-if="employee.function">
          - {{ employeeFunction.text }}
        </span>
      </p>
      <p class="mb-0 font-italic">
        <span v-if="employee.languages.length">Sprachkenntnisse: </span>
        <span
          v-for="(language, index) of employee.languages"
          :key="language.id"
        >{{ index !== 0 ? ', ' : '' }}{{ language.name }}</span>
      </p>
    </div>
    <avatar-image
      :avatar="employee.small_profile_image"
      :image="employee.profileimage_url"
    ></avatar-image>
  </fragment>
</template>

<script>
import { employeeFunctions } from '@/utils'
import AvatarImage from './AvatarImage'

export default {
  components: {
    AvatarImage
  },

  props: {
    employee: {
      type: Object,
      required: true
    }
  },

  computed: {
    employeeFunction() {
      return employeeFunctions.find(f => f.value === this.employee.function)
    }
  }
}
</script>

<style lang="scss" scoped>
</style>

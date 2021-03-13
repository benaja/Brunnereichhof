<template>
  <fragment>
    <div>
      <p class="mb-0">
        <span class="font-weight-bold">
          {{ employee.lastname }} {{ employee.firstname }}
        </span>
        <span v-if="employee.function">{{ employeeFunction.text }}</span>
      </p>
      <p class="mb-0 font-italic">
        <span v-if="employee.languages.length">Sprachkenntnisse: </span>
        <span
          v-for="(language, index) of employee.languages"
          :key="language.id"
        >{{ index !== 0 ? ', ' : '' }}{{ language.name }}</span>
      </p>
    </div>
    <div
      v-if="employee.small_profile_image"
      class="profile-image"
      :style="{ backgroundImage: `url(${employee.small_profile_image})`}"
    >
    </div>
  </fragment>
</template>

<script>
import { employeeFunctions } from '@/utils'

export default {
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
.profile-image {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  background-size: cover;
  background-position: center;
  flex-shrink: 0;
}
</style>

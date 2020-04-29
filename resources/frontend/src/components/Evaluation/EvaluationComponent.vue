<template>
  <v-expansion-panels>
    <v-expansion-panel
      v-for="(evaluationGroup, index) of evaluationGroups.filter(e => e.show)"
      :key="index"
    >
      <v-expansion-panel-header>
        <p class="header-text mb-1">
          <v-icon class="account-icon mr-2">
            {{ evaluationGroup.icon }}
          </v-icon>
          {{ evaluationGroup.name }}
        </p>
      </v-expansion-panel-header>
      <v-expansion-panel-content>
        <div>
          <evaluation-element
            v-for="(evaluation, i) of evaluationGroup.evaluations"
            :key="`e-${i}`"
            :evaluation="evaluation"
          ></evaluation-element>
        </div>
      </v-expansion-panel-content>
    </v-expansion-panel>
  </v-expansion-panels>
</template>

<script>
import EvaluationElement from '@/components/Evaluation/EvaluationElement'

export default {
  components: {
    EvaluationElement
  },
  props: {
    evaluationGroups: {
      type: Array,
      required: true
    }
  }
}
</script>

<style>
</style>

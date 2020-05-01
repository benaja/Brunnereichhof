<template>
  <div>
    <v-tabs v-model="tab">
      <v-tab
        v-for="(evaluationGroup, index) of evaluationGroups.filter(e => e.show)"
        :key="index"
      >
        {{ evaluationGroup.name }}
      </v-tab>
    </v-tabs>
    <v-tabs-items v-model="tab">
      <v-tab-item
        v-for="(evaluationGroup, index) of evaluationGroups.filter(e => e.show)"
        :key="index"
      >
        <evaluation-element
          v-for="(evaluation, i) of evaluationGroup.evaluations"
          :key="`e-${i}`"
          :evaluation="evaluation"
        ></evaluation-element>
      </v-tab-item>
    </v-tabs-items>
  </div>
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
  },
  data() {
    return {
      tab: 0
    }
  }
}
</script>

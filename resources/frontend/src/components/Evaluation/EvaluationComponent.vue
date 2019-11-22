<template>
  <v-expansion-panels>
    <v-expansion-panel v-for="(evaluation, index) of evaluations" :key="index">
      <v-expansion-panel-header>
        <p class="header-text mb-1">
          <v-icon class="account-icon mr-2">supervisor_account</v-icon>Kunde
        </p>
      </v-expansion-panel-header>
      <evaluation-element :evaluation="evaluation"></evaluation-element>
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
    evaluations: {
      type: Array,
      required: true
    }
  }
}
</script>

<style>
</style>

import Vue from 'vue'
import VueI18n from 'vue-i18n'
import de from './de'


Vue.use(VueI18n)


console.log(de)

const i18n = new VueI18n({
  locale: 'de',
  messages: {
    de
  }
})


export default i18n

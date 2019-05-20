import Vue from 'vue'
import './plugins/vuetify'
import App from './App.vue'
import router from "./router"
import 'typeface-roboto/index.css';
import VuetifyConfirm from 'vuetify-confirm'
import VueMoment from "vue-moment";
import Print from 'vue-print-nb'

Vue.use(Print);
Vue.use(VuetifyConfirm);
Vue.use(VueMoment);
Vue.config.productionTip = false;

new Vue({
  el: "#app",
  router,
  render: h => h(App),
});

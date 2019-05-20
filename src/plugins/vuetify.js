import Vue from 'vue'
import Vuetify from 'vuetify/lib'
import 'vuetify/src/stylus/app.styl'
import 'material-design-icons-iconfont/dist/material-design-icons.css'

Vue.use(Vuetify, {
  iconfont: 'md',
  theme: {
    primary: "#1E88E5",
    secondary: "#1976D2",
    accent: "#3949AB",
    error: "#F44336",
    success: "#7CB342"
  }
})
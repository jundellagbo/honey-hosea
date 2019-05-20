<template>
      <custom-layout>
          <v-layout justify-center align-center text-center>
            <v-flex style="text-align: center; margin-top: 30px;">
                <v-img :src="require('../img/logo.jpg')" style="width: 300px; margin: 0 auto;"></v-img>
                <h2 style="margin-top: 15px; margin-bottom: 15px;" class="display-2 primary--text">HONEY HOSEA ACADEMY INC.</h2>
                <p style="font-size: 20px;">Enrollment and Billing System</p>
            </v-flex>
          </v-layout>
      </custom-layout>
</template>
<script>
import Layout from "./../components/Layout.vue"
export default {
    components: {
        'custom-layout': Layout
    }
}
</script>


<template>
    <div>
        <v-snackbar
        v-model="alert.open"
        left="left"
        :right="null"
        :color="alert.type"
        bottom="bottom"
        :timeout="3000"
        :key="alert.key"
        >
        {{ alert.text }}
        <v-btn
            flat
            @click="close_"
            icon
        >
            <v-icon>cancel</v-icon>
        </v-btn>
        </v-snackbar>
    </div>
</template>

<script>
export default {
    props: {
      alert: { type: Object }
    },
    methods: {
        close_() {
            this.$emit("close")
        }   
    }
}
</script>

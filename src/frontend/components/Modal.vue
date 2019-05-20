<template>
    <v-dialog v-model="dialog.open" persistent :max-width="dialog.width" :scrollable="true">
      <v-card>
        <v-card-title class="headline">{{ dialog.title }}</v-card-title>
        <v-card-text>
            <slot />
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn v-for="(button, index) in dialog.buttons" :key="index" flat @click="button.click" :color="button.color">{{ button.text }}</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
</template>

<script>
export default {
    props: {
        dialog: Object
    }
}
</script>


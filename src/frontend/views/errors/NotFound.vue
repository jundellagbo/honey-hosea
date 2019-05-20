<template>
    <v-layout align-center justify-center style="height: calc(100vh - 200px)">
        <v-flex text-xs-center>
        <img :src="require('../../img/kitten-large.png')" style="width: 400px;" />
        <h2 style="margin-bottom: 15px;" class="display-2 primary--text">404 NOT FOUND</h2>
        <p>The page that you are looking for was not found.</p>
        <v-btn color="primary" flat to="/">
            Get me out of here
        </v-btn>
        </v-flex>
    </v-layout>
</template>
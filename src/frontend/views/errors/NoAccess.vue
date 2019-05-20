<template>
    <v-layout align-center justify-center style="height: calc(100vh - 200px)">
        <v-flex text-xs-center>
        <img :src="require('../../img/kitten-large.png')" style="width: 400px;" />
        <h2 style="margin-bottom: 15px;" class="display-2 primary--text">Whoops, You have no access on this page.</h2>
        <p>You're trying to enter on this page, this is forbidden</p>
        </v-flex>
    </v-layout>
</template>
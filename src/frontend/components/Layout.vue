<template>
  <v-app id="inspire">
    <!-- Navigation Menu -->
    <v-navigation-drawer
      v-model="drawer"
      :clipped="$vuetify.breakpoint.lgAndUp"
      fixed
      app
    >
      <v-list dense>
        <v-list>
          <v-list-tile avatar>
            <v-list-tile-avatar>
              <img src="./../img/logo.jpg" alt="">
            </v-list-tile-avatar>
            <v-list-tile-title>HONEY HOSEA ACADEMY INC.</v-list-tile-title>
          </v-list-tile>
        </v-list>
        <div v-if="isShow([0])" style="padding-left: 15px; padding-right: 15px; margin-bottom: 15px;">
          <v-btn @click="users = true" color="primary" block>
            User List
          </v-btn>
        </div>
        <v-list-tile to="/">
          <v-list-tile-action>
            <v-icon>home</v-icon>
          </v-list-tile-action>
          <v-list-tile-content>
            <v-list-tile-title>WELCOME</v-list-tile-title>
          </v-list-tile-content>
        </v-list-tile>

        <!-- Registrar -->
        <div v-if="isShow([0, 1])">
        <v-subheader class="mt-3 grey--text text--darken-1">REGISTRAR</v-subheader>
        <v-list-tile to="/registrar/students">
          <v-list-tile-action>
            <v-icon>assignment_ind</v-icon>
          </v-list-tile-action>
          <v-list-tile-content>
            <v-list-tile-title>STUDENTS</v-list-tile-title>
          </v-list-tile-content>
        </v-list-tile>

        <v-list-tile to="/registrar/classes">
          <v-list-tile-action>
            <v-icon>assessment</v-icon>
          </v-list-tile-action>
          <v-list-tile-content>
            <v-list-tile-title>CLASSES</v-list-tile-title>
          </v-list-tile-content>
        </v-list-tile>

        <v-list-group
          prepend-icon="list"
          value="true"
        >

        <template v-slot:activator>
          <v-list-tile>
            <v-list-tile-title>MORE SETTINGS</v-list-tile-title>
          </v-list-tile>
        </template>

        <v-list-tile to="/registrar/requirements">
          <v-list-tile-action>
            <v-icon>restore_page</v-icon>
          </v-list-tile-action>
          <v-list-tile-content>
            <v-list-tile-title>REQUIREMENTS</v-list-tile-title>
          </v-list-tile-content>
        </v-list-tile>

        <v-list-tile to="/registrar/teachers">
          <v-list-tile-action>
            <v-icon>supervised_user_circle</v-icon>
          </v-list-tile-action>
          <v-list-tile-content>
            <v-list-tile-title>TEACHERS</v-list-tile-title>
          </v-list-tile-content>
        </v-list-tile>

        <v-list-tile to="/registrar/subjects">
          <v-list-tile-action>
            <v-icon>note</v-icon>
          </v-list-tile-action>
          <v-list-tile-content>
            <v-list-tile-title>SUBJECTS</v-list-tile-title>
          </v-list-tile-content>
        </v-list-tile>

        </v-list-group>
        </div>

        <!-- Close Registrar -->

        <!-- Accountant -->
        <div v-if="isShow([0, 2])">
        <v-subheader class="mt-3 grey--text text--darken-1">ACCOUNTANT</v-subheader>
        <v-list-tile to="/accountant/accounts">
          <v-list-tile-action>
            <v-icon>account_balance_wallet</v-icon>
          </v-list-tile-action>
          <v-list-tile-content>
            <v-list-tile-title>ACCOUNTS</v-list-tile-title>
          </v-list-tile-content>
        </v-list-tile>

        <v-list-tile to="/accountant/payments">
          <v-list-tile-action>
            <v-icon>settings_applications</v-icon>
          </v-list-tile-action>
          <v-list-tile-content>
            <v-list-tile-title>PAYMENTS</v-list-tile-title>
          </v-list-tile-content>
        </v-list-tile>
        </div>
        <!-- close accountant -->

        <v-subheader class="mt-3 grey--text text--darken-1">MY ACCOUNT</v-subheader>

        <v-list-tile @click="usersSettings = true">
          <v-list-tile-action>
            <v-icon>account_box</v-icon>
          </v-list-tile-action>
          <v-list-tile-content>
            <v-list-tile-title>USER SETTINGS</v-list-tile-title>
          </v-list-tile-content>
        </v-list-tile>

        <v-list-tile @click="logout">
          <v-list-tile-action>
            <v-icon>power_settings_new</v-icon>
          </v-list-tile-action>
          <v-list-tile-content>
            <v-list-tile-title>LOGOUT</v-list-tile-title>
          </v-list-tile-content>
        </v-list-tile>
      </v-list>

    </v-navigation-drawer>
    <!-- Close Navigation Menu -->

    <!-- Header Title -->
    <v-toolbar color="primary" dark app :clipped-left="$vuetify.breakpoint.lgAndUp">
      <v-toolbar-side-icon @click.stop="drawer = !drawer"></v-toolbar-side-icon>
      <v-toolbar-title>{{ title }}</v-toolbar-title>
    </v-toolbar>
    <!-- Close Header Title -->
    
    <!-- Content -->
    <v-content>
      <div class="_contentContainer">
        <slot v-if="!noAccess"></slot>
        <no-access v-else />
      </div>
    </v-content>
    <!-- Close Content -->

    <modal v-if="isShow([0])" :dialog="{
        title: 'Users Lists',
        open: users,
        width: 850
    }">
      <users @close="users = false" :fetch="users" @alert="openAlert" />
    </modal>

    <modal :dialog="{
        title: 'User Settings',
        open: usersSettings,
        width: 550
    }">
      <user-settings @alert="openAlert" @close="usersSettings = false" />
    </modal>

    <message :alert="alert" @close="alert.open = false" />

  </v-app>
</template>

<script>
  import { user } from "./../../app";
  import NoAccess from "./../views/errors/NoAccess.vue";
  import Modal from "./Modal.vue";
  import Message from "./Message.vue";
  import Users from "./Layout.Users.vue"
  import UserSettings from "./Layout.UserSettings.vue"
  export default {
    components: {
      'no-access': NoAccess,
      'modal': Modal,
      'message': Message,
      'users': Users,
      'user-settings': UserSettings
    },
    data() {
      return {
        drawer: null,
        title: "Enrollment and Billing System",
        noAccess: false,
        users: false,
        usersSettings: false,
        alert: {
            open: false,
            text: "",
            type: "",
            key: 0
        },
      }
    },
    mounted() {
      if( this.$router.history.current.name ) {
        this.title = this.$router.history.current.name
      }
      if( this.$router.history.current.meta.middleware && (this.$router.history.current.meta.middleware.indexOf( parseInt(user.role) ) != -1) ) {
        this.noAccess = false;
      } else {
        this.noAccess = true;
      }
    },
    methods: {
      async logout() {
        let res = await this.$confirm('Are you sure you want to logout?', { title: "Logout Confirmation", buttonTrueText: "Confirm", buttonFalseText: "Cancel", persistent: true, icon: "power_settings_new", color: "info" });
        if( res ) {
          localStorage.removeItem("userToken");
          window.location.href = "/login";
        }
      },
      isShow( middleware ) {
        if( middleware && (middleware.indexOf( parseInt(user.role) ) != -1) ) {
          return true;
        } 
        return false;
      },
      openAlert( param ) {
        this.alert = {
          open: true,
          text: param.message,
          type: param.type,
          key: Date.now()
        }
      }
    },
  }
</script>

<style>
._contentContainer { padding: 30px; }
</style>
<template>
    <v-layout align-center justify-center style="padding: 20px;">
        <v-flex md4>
            <v-card style="padding: 15px;">
                <v-layout>
                    <v-flex md4>
                        <v-img :src="require('../../img/logo.jpg')"></v-img>
                    </v-flex>
                    <v-flex md8>
                        <div style="padding: 20px;">
                        <v-form ref="loginform">

                            <h2 style="margin-bottom: 15px;">Login</h2>

                            <v-text-field
                                v-model="inputs.username"
                                :rules="rule.username"
                                label="Username"
                                solo
                                class="padder"
                            ></v-text-field>

                            <v-text-field
                                v-model="inputs.password"
                                :rules="rule.password"
                                label="Password"
                                solo
                                class="padder"
                                type="password"
                            ></v-text-field>

                            <v-btn :loading="loadingSubmit" color="primary" block @click="login">
                                Login
                            </v-btn>

                        </v-form>
                        </div>
                    </v-flex>
                </v-layout>
            </v-card>
        </v-flex>
        <message :alert="alert" @close="alert.open = false" />
    </v-layout>
</template>
<script>
import { api, postHeader } from "./../../../app.js";
import Message from "./../../components/Message.vue"
export default {
    components: {
        'message': Message
    },
    data() { return { 
        alert: {
            open: false,
            text: "",
            type: "",
            key: 0
        },
        inputs: {
            username: "",
            password: ""
        },
        rule: {
            username: [
                v => !!v || 'Username is required'
            ],
            password: [
                v => !!v || 'Password is required'
            ]
        },
        loadingSubmit: false
    } },
    methods: {
        openMessage( params ) {
            this.alert = {
                open: true,
                text: params.message,
                type: params.type,
                key: Date.now()
            }
        },
        login() {
            if( !this.$refs.loginform.validate() ) {
                this.openMessage({ message: "Please check the error fields", type: "error" });
            } else {
                this.submitLogin();
            }
        },
        submitLogin() {
            const e = this;
            e.loadingSubmit = true;
            api.post("/auth/login", e.inputs, postHeader)
            .then((response) => {
                if( response.data.response.not_found ) {
                    e.openMessage({ message: "Invalid Login", type: "error" });
                } else {
                    localStorage.setItem("userToken", JSON.stringify(response.data.response));
                    window.location.href = "/";
                }
            })
            .catch(( error ) => {
                e.openMessage({ message: "Login Error [" + error + "].", type: "error" })
            })
            .then(() => e.loadingSubmit = false);
        }
    },
}
</script>


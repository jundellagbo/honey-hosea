<template>
    <div>
        <v-card style="padding: 20px;">
            <v-form ref="detailsform">
                <v-layout>      
                <v-flex md6>
                <v-text-field
                    v-model="inputs.fname"
                    :rules="rules.fname"
                    label="Firstname"
                    outline
                    class="padder"
                ></v-text-field>

                <v-text-field
                    v-model="inputs.mname"
                    :rules="rules.mname"
                    label="Middlename"
                    outline
                    class="padder"
                ></v-text-field>

                <v-text-field
                    v-model="inputs.lname"
                    :rules="rules.lname"
                    label="Lastname"
                    outline
                    class="padder"
                ></v-text-field>
                </v-flex>
                <v-flex md6>
                <v-text-field
                    v-model="inputs.username"
                    :rules="rules.username"
                    label="Username"
                    outline
                    class="padder"
                ></v-text-field>

                <v-text-field
                    v-model="inputs.password"
                    label="Password"
                    :rules="rules.password"
                    outline
                    :type="show1 ? 'text' : 'password'"
                    :append-icon="show1 ? 'visibility' : 'visibility_off'"
                    @click:append="show1 = !show1"
                    class="padder"
                ></v-text-field>

                <v-text-field
                    v-model="inputs.cpassword"
                    label="Confirm Password"
                    :rules="rules.cpassword"
                    outline
                    :type="show2 ? 'text' : 'password'"
                    :append-icon="show2 ? 'visibility' : 'visibility_off'"
                    @click:append="show2 = !show2"
                    :error-messages="isMatch"
                    class="padder"
                ></v-text-field>
                </v-flex>
                </v-layout>

                <p class="padder">
                <strong>Note:</strong> password value is the same to current password if empty.
                </p>

                <v-btn style="margin-bottom: 15px;" small flat color="primary" @click="autoGen">
                    Auto Generate Password
                </v-btn>

                <br>

                <v-btn :loading="loadingDetails" color="primary" @click="saveDetails">
                    Save
                </v-btn>

            </v-form>
            </v-card>
        <v-btn flat style="margin-top: 15px;" color="primary" @click="closeSettings">
            Close
        </v-btn>
    </div>
</template>

<script>
import { api, user, postHeader } from "./../../app"

export default {
    data() { return {
        inputs: Object.assign({password: "", cpassword: "" }, user),
        rules: {
            fname: [ v => !!v || 'Firstname is required' ],
            mname: [ v => !!v || 'Middlename is required' ],
            lname: [ v => !!v || 'Lastname is required' ],
            username: [ v => !!v || 'Username is required' ]
        },
        show1: false,
        show2: false,
        loadingDetails: false
    } },
    computed: {
        isMatch() {
            return ( this.inputs.password != this.inputs.cpassword ) ? 'Password and Confirm password not match.' : '';
        }
    },
    methods: {
        closeSettings() {
            this.$emit("close")
            this.inputs = user;
        },
        saveDetails() {
            if( !this.$refs.detailsform.validate() ) {
                this.$emit('alert', { message: 'Please check the error fields', type: 'error' });
            } else {
                const e = this;
                e.loadingDetails = true;
                api.post("/user/settings/", e.inputs, postHeader)
                .then(() => {
                    const output = Object.assign({}, e.inputs);
                    delete output.password;
                    delete output.cpassword;
                    localStorage.setItem("userToken", JSON.stringify( output ));
                    setTimeout( function() {
                        location.reload();
                    }, 3000);
                    e.$emit('alert', { message: 'Settings has been saved, reloading the page to save cookies.', type: 'success' });
                })
                .catch(( error ) => e.$emit('alert', { message: 'Error when submitting the form [' + error + '].', type: 'error'}))
                .then(() => e.loadingDetails = false)
            }
        },
        autoGen() {
            let randomstring = Math.random().toString(36).slice(-8);
            this.inputs.password = randomstring;
            this.inputs.cpassword = randomstring;
        }
    },
}
</script>

<style scoped>
.padder { padding: 0 5px; }
</style>
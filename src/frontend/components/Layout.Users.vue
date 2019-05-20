<template>
    <div>
        <v-stepper v-model="step" style="width: 100%;" light>
            <v-stepper-items>

                <v-stepper-content step="1">
                    <v-card>
                        <v-card-title>
                            <v-btn color="primary" @click="step = 2">
                                NEW USER
                            </v-btn>
                            <v-spacer></v-spacer>
                            <div>
                                <v-text-field
                                    v-model="search"
                                    append-icon="search"
                                    label="Search"
                                    solo
                                ></v-text-field>
                            </div>
                        </v-card-title>
                        
                        <v-data-table
                        :headers="th"
                        :items="userlists"
                        :search="search"
                        :no-results-text="`Your search for (${search}) user found no results.`"
                        :no-data-text="loadingUsers ? `Loading...` : `There is no user available.`"
                        :loading="loadingUsers"
                        hide-actions
                        >
                        <template v-slot:items="props">
                            <tr>
                                <td class="text-xs-left">
                                    {{ props.item.fullname }}
                                </td>
                                <td class="text-xs-left">
                                    {{ props.item.username }}
                                </td>
                                <td class="text-xs-left">
                                    {{ props.item.roleObj.text }}
                                </td>
                                <td class="text-xs-left">
                                    <span v-if="props.item.status" class="active_">Active</span>
                                    <span v-else class="inactive_">Deactivated</span>
                                </td>

                                <td class="text-xs-right">

                                    <v-tooltip bottom>
                                        <template v-slot:activator="{ on }">
                                            <v-btn icon v-on="on" color="primary" small dark @click="getEdit( Object.assign({}, props.item) )">
                                            <v-icon size="14">edit</v-icon>
                                            </v-btn>
                                        </template>
                                        <span>Edit Requirement</span>
                                    </v-tooltip>

                                </td>
                            </tr>
                        </template>
                        </v-data-table>
                        
                    </v-card>
                </v-stepper-content>

                <v-stepper-content step="2">
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

                            <p class="padder" v-if="inputs.id">
                            <strong>Note:</strong> password value is the same to current password if empty.
                            </p>

                            <v-btn style="margin-bottom: 15px;" small flat color="primary" @click="autoGen">
                                Auto Generate Password
                            </v-btn>

                            <br>

                            <v-select
                            v-model="inputs.role"
                            :items="roles"
                            :rules="rules.role"
                            label="User Role"
                            persistent-hint
                            item-text="text"
                            class="padder"
                            outline
                            return-object
                            single-line
                            ></v-select>

                            <v-checkbox
                            v-model="inputs.status"
                            :label="`${inputs.status ? 'Active' : 'InActive'}`"
                            color="primary"
                            :true-value="1"
                            :false-value="0"
                            style="margin-top: 0;"
                            ></v-checkbox>

                            <v-btn :loading="loadingDetails" color="primary" @click="saveDetails">
                                Save
                            </v-btn>

                            <v-btn flat color="primary" @click="goBack">
                                Back
                            </v-btn>

                        </v-form>
                    </v-card>
                </v-stepper-content>

            </v-stepper-items>
        </v-stepper>
        <v-btn flat style="margin-top: 15px;" color="primary" @click="closeUsers">
            Close
        </v-btn>
    </div>
</template>

<script>
import { api, user, postHeader } from "./../../app"

const roles = [
    { id: 0, text: "Administrator" },
    { id: 1, text: "Registrar" },
    { id: 2, text: "Accountant" }
];

const inputs = {
    id: 0,
    fname: "",
    mname: "",
    lname: "",
    username: "",
    password: "",
    cpassword: "",
    role: 0,
    status: 1
};

const resetInputs = Object.assign({}, inputs);

export default {
    data() { return {
        roles,
        inputs,
        show1: false,
        show2: false,
        loadingDetails: false,
        rules: {
            fname: [ v => !!v || 'Firstname is required' ],
            mname: [ v => !!v || 'Middlename is required' ],
            lname: [ v => !!v || 'Lastname is required' ],
            username: [ v => !!v || 'Username is required' ],
            role: [ v => !!v || 'Please select user role' ]
        },
        step: 1,
        users: [],
        loadingUsers: false,
        search: "",
        th: [
            { text: "NAMES", sortable: false, value: "names", align: "left" },
            { text: "USERNAME", sortable: false, value: "username", align: "left" },
            { text: "ROLE", sortable: false, value: "username", align: "left" },
            { text: "STATUS", sortable: false, value: "role", align: "left" },
            { text: "OPTIONS", sortable: false, value: "options", align: "right" }
        ]
    } },
    computed: {
        isMatch() {
            return ( this.inputs.password != this.inputs.cpassword ) ? 'Password and Confirm password not match.' : ''; 
        },
        userlists() {
            const e = this;
            return this.users.map( function( row, index ) {
                row.key = index;
                row.fullname = row.fname + ' ' + row.mname + ' ' + row.lname,
                row.roleObj = e.roles[ row.role ];
                row.status = parseInt( row.status );
                return row;
            })
        }
    },
    props: ['fetch'],
    watch: {
        fetch( newVal ) {
            if( newVal ) {
                this.fetchUsers();
            }
        }
    },
    methods: {
        fetchUsers() {
            const e = this;
            e.loadingUsers = true;
            api.get("/users/" + user.id)
            .then(( response ) => {
                e.users = response.data.users;
            })
            .catch(( error ) => e.openAlert({ message: 'Error when fetching users [' + error + '].', type: 'error'}))
            .then(() => e.loadingUsers = false)
        },
        resetForm() {
            this.$refs.detailsform.resetValidation();
            this.inputs = Object.assign({}, resetInputs);
        },
        closeUsers() {
            this.$emit("close")
            this.resetForm();
            this.step = 1;
        },
        saveDetails() {
            if( !this.$refs.detailsform.validate() ) {
                this.openAlert({ message: 'Please check the error fields', type: 'error' });
            } else {
                if( this.inputs.id && (this.inputs.password == '' || this.inputs.cpassword == '') ) {
                    this.openAlert({ message: 'Please enter your password', type: 'error' });
                } else {
                    this.saveToApi();
                }
            }
        },
        saveToApi() {
            const e = this;
            e.loadingDetails = true;
            const inputs = Object.assign({}, e.inputs)
            inputs.status = inputs.status ? inputs.status : 0;
            inputs.roleId = inputs.role.id;
            api.post("/user/settings/", inputs, postHeader)
            .then(( response ) => {
                if( response.data.response.existing_user || typeof(response.data.response.existing_user) != 'undefined' ) {
                    if( inputs.id ) {
                        // update
                        e.openAlert({ message: 'There is no changes.', type: 'warning' })
                    } else {
                        // insert
                        e.openAlert({ message: 'Failed to add user, we did not recognize this account.', type: 'warning' })
                    }
                } else {
                    inputs.role = inputs.role.id;
                    delete inputs.password;
                    delete inputs.cpassword;
                    if( inputs.id ) {
                        // update
                        e.$set( e.users, inputs.key, inputs );
                        e.openAlert({ message: 'User changes has been saved.', type: 'success' })
                    } else {
                        // insert
                        inputs.id = response.data.response.id;
                        e.users.push(inputs);
                        e.openAlert({ message: 'New user has been saved.', type: 'success' })
                    }
                    e.step = 1;
                    e.resetForm();
                }
            })
            .catch(( error ) => e.openAlert({ message: 'Error when submitting the form [' + error + '].', type: 'error'}))
            .then(() => e.loadingDetails = false)
        },
        autoGen() {
            let randomstring = Math.random().toString(36).slice(-8);
            this.inputs.password = randomstring;
            this.inputs.cpassword = randomstring;
        },
        openAlert( param ) {
            this.$emit('alert', { message: param.message, type: param.type } )
        },
        goBack() {
            this.step = 1;
            this.resetForm();
        },
        getEdit( params ) {
            //this.resetForm();
            this.inputs = params;
            this.inputs.role = params.roleObj;
            this.step = 2;
        }
    },
}
</script>

<style scoped>
.padder { padding: 0 5px; }
.active_ { color: #7CB342; }
.inactive_ { color: red; }
</style>
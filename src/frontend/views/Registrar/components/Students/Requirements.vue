<template>
    <div> 
        <v-stepper v-model="step" style="width: 100%;" light>
            <v-stepper-items>
                <v-stepper-content step="1">
                    <v-flex>
                        <v-card>
                            <v-card-title class="headline font-weight-bold" style="font-size: 20px!important; padding: 0 16px;">
                            <p>
                                {{ sid.fullname }} - Requirements
                            </p>
                            </v-card-title>
                            <v-card-title>
                                <v-btn color="primary" @click="newReq">
                                    NEW REQUIREMENT
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
                            :items="reqlist"
                            :search="search"
                            :no-results-text="`Your search for (${search}) requirement found no results.`"
                            :no-data-text="loadRequirement ? `Loading...` : `There is no requirement available.`"
                            :loading="loadRequirement"
                            hide-actions
                            >
                            <template v-slot:items="props">
                                <tr>
                                    <td class="text-xs-left">
                                        {{ props.item.requirement }}
                                    </td>
                                    <td class="text-xs-left">
                                        <span v-if="props.item.status" class="cleared_">CLEARED</span>
                                        <span v-else class="pending_">PENDING</span>
                                    </td>

                                    <td class="text-xs-right">

                                        <v-tooltip bottom>
                                            <template v-slot:activator="{ on }">
                                                <v-btn icon v-on="on" color="primary" small dark @click="editReq(props.index, props.item)">
                                                <v-icon size="14">edit</v-icon>
                                                </v-btn>
                                            </template>
                                            <span>Edit Requirement</span>
                                        </v-tooltip>

                                        <v-tooltip bottom>
                                            <template v-slot:activator="{ on }">
                                                <v-btn icon v-on="on" color="error" small @click="confirmDelete(props.index, props.item.id)">
                                                <v-icon size="14">delete</v-icon>
                                                </v-btn>
                                            </template>
                                            <span>Remove</span>
                                        </v-tooltip>
                                    </td>
                                </tr>
                            </template>
                            </v-data-table>
                        </v-card>
                    </v-flex>
                </v-stepper-content>
                <v-stepper-content step="2">

                    <div class="padder">
                        <h5 style="margin-bottom: 15px;" class="headline">New Requirement</h5>
                        <v-form ref="requirementform">
                            <v-layout wrap>
                                <v-flex
                                md12
                                >
                                <v-text-field
                                    v-model="inputs.requirement"
                                    :rules="rule.requirement"
                                    label="Requiment"
                                    solo
                                ></v-text-field>
                                </v-flex>

                                <v-flex
                                md12
                                >
                                <v-checkbox
                                v-model="inputs.status"
                                :label="`${inputs.status ? 'Cleared' : 'Pending'}`"
                                color="primary"
                                :true-value="1"
                                :false-value="0"
                                ></v-checkbox>
                                </v-flex>

                                <v-btn
                                @click="submitForm"
                                :loading="loadingbtn"
                                color="primary"
                                >Submit</v-btn>

                                <v-btn
                                v-if="inputs.id != 0"
                                @click="cancelEdit"
                                color="primary"
                                flat
                                >Cancel</v-btn>

                                <v-btn 
                                flat
                                @click="step = 1"
                                color="primary"
                                >Back</v-btn>

                            </v-layout>
                        </v-form>
                    </div>

                </v-stepper-content>
            </v-stepper-items>
        </v-stepper>

        <v-btn 
        flat
        @click="closeModal"
        color="primary"
        style="margin-top: 15px; float: right;"
        >Close</v-btn>


    </div>
</template>

<script>
import { inputs, resetInputs, validations } from "./js/Requirements"
import { api, postHeader } from "./../../../../../app";
export default {
    data() {
        return {
            studentid: {},
            step: 1,
            requirements: [],
            th: [
                { text: "NAMES", sortable: false, value: "requirement", align: "left" },
                { text: "STATUS", sortable: false, value: "status", align: "left" },
                { text: "OPTIONS", sortable: false, value: "options", align: "right" }
            ],
            loadRequirement: false,
            search: "",
            inputs,
            rule: validations,
            resetInputs,
            loadingbtn: false,
            editIndex: 0
        }
    },
    props: ['sid'],
    watch: {
        sid( newVal, oldVal ) {
            if( (newVal.id != oldVal.id) && newVal.open ) {
                this.studentid = newVal
                this.fetchRequirement();
            }
        }
    },
    computed: {
        reqlist() {
            return this.requirements ? this.requirements.map( function( req ) {
                req.id = parseInt( req.id );
                req.status = parseInt(req.status);
                return req;
            }) : null;
        }
    },
    methods: {
        newReq() {
            this.resetForm();
            this.step = 2;
        },
        resetStep() {
            const e = this;
            setTimeout( function() { e.step = 1 }, 500)
        },
        closeModal() {
            this.resetStep();
            this.$refs.requirementform.resetValidation();
            this.$refs.requirementform.reset();
            this.inputs = this.resetInputs;
            this.$emit("closeModal");
        },
        async confirmDelete( index, id ) {
            let res = await this.$confirm('Are you sure you want to remove this requirement?', { title: "Confirm Delete", buttonTrueText: "Remove", buttonFalseText: "Cancel", persistent: true, icon: "warning", color: "warning" });
            if( res ) {
                this.deleteRequirement( index, id );
            }
        },
        deleteRequirement( index, id ) {
            const e = this;
            api.get("http://localhost/app/api/registrar/student/requirement/" + id)
            .then(() => {
                e.requirements.splice( index, 1 )
                e.$emit('alert', { message: "Requirement has been removed.", type: "success" })
            })
            .catch(( error ) => {
                e.$emit('alert', { message: "Error when removing requirement [" + error + "].", type: "error" })
            })
        },
        fetchRequirement() {
            const e = this;
            e.loadRequirement = true;
            e.requirements = [];
            api.get("/registrar/student/" + e.studentid.id + "/requirement")
            .then(( response ) => {
                e.requirements = response.data.requirements;
            })
            .catch(( error ) => {
                e.$emit('alert', { message: "Error when fetching student's requirements [" + error + "]", type: "error" })
            })
            .then(() => e.loadRequirement = false)
        },
        submitForm() {
            if( !this.$refs.requirementform.validate() ) {
                this.$emit('alert', { message: "Please check the error fields", type: "error" })
            } else {
                this.storedata();
            }
        },
        storedata() {
            const e = this;
            e.loadingbtn = true;
            let inputs = Object.assign({}, this.inputs);
            inputs.status = typeof(inputs.status) == 'undefined' ? 0 : inputs.status;
            inputs.studentsid = this.studentid.id;
            api.post("/registrar/student/requirement", inputs, postHeader)
            .then(( response ) => {
                // insert here
                if( !inputs.id ) {
                    inputs.id = parseInt(response.data.response.id);
                    e.requirements.push(inputs);
                    e.$emit('alert', { message: "New requirement has been added.", type: "success" });
                    e.step = 1;
                } else {
                    // edit here
                    this.$set( this.requirements, this.editIndex, inputs )
                    e.$emit('alert', { message: "Requirement has been updated.", type: "success" });
                }
                e.resetForm();
            })
            .catch(( error ) => {
                this.$emit('alert', { message: "Error when submitting requirements form, please try again. [" + error + "]", type: "error" })
            })
            .then(() => e.loadingbtn = false);
        },
        resetForm() {
            this.step = 1;
            this.$refs.requirementform.resetValidation();
            this.$refs.requirementform.reset();
            this.inputs = this.resetInputs;
        },
        editReq( index, item ) {
            this.step = 2;
            this.editIndex = index;
            const copy = Object.assign({}, item);
            this.inputs = copy;
        },
        cancelEdit() {
            this.editIndex = 0;
            this.resetForm();
        }
    }
}
</script>

<style scoped>
.cleared_ {
    color: #3949AB;
}
.pending_ {
    color: #7CB342;
}
.padder { padding: 0 10px; }
</style>

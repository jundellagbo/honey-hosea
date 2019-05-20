<template>
    <div>
        <v-stepper v-model="step" style="width: 100%;" light>
            <v-stepper-items>

                <v-stepper-content step="1">
                    
                    <v-btn v-if="!sid.isAccountant" color="primary" @click="step = 2">
                        Enroll Student
                    </v-btn>

                    <v-btn color="primary" flat @click="fetchEnrollment">
                        <v-icon size="22">refresh</v-icon>&nbsp;&nbsp;Refresh
                    </v-btn>

                    <v-data-table
                    :headers="th"
                    :items="getEnrollment"
                    :no-data-text="loadingEnrollment ? `Loading...` : `There is no enrollment record available.`"
                    :loading="loadingEnrollment"
                    hide-actions
                    >
                        <template v-slot:items="props">
                            <tr>
                                <td class="text-xs-left">
                                    {{ props.item.classname }}
                                </td>
                                <td class="text-xs-left">
                                    {{ props.item.schoolyear }}
                                </td>
                                <td class="text-xs-left">
                                    {{ props.item.dateenrolled | moment("MMM. D, YYYY") }}
                                </td>
                                <td class="text-xs-left">
                                    <span :class="props.item.status == 0 ? 'pending' : 'enrolled'">
                                        {{ props.item.status == 0 ? "Pending" : "Enrolled" }}
                                    </span>
                                </td>
                                <td class="text-xs-left" v-if="sid.isAccountant">
                                    <span v-if="props.item.balance != 0">
                                        ₱ {{ props.item.balance }}
                                    </span>
                                    <span class="enrolled" v-else>
                                        PAID
                                    </span>
                                </td>
                                <td class="text-xs-right" v-if="sid.isAccountant">
                                    <v-btn flat small :to="`/accountant/statement/${props.item.id}`" color="info">
                                        Show Account
                                    </v-btn>
                                </td>
                            </tr>
                        </template>
                    </v-data-table>
                </v-stepper-content>

                <v-stepper-content step="2">
                    <v-form ref="enrollmentform">
                        <v-layout>

                        <v-flex
                        md6
                        >
                        <v-select
                        v-model="inputs.classid"
                        :items="classes"
                        :rules="rule.classid"
                        item-value="id"
                        label="Select Class"
                        persistent-hint
                        item-text="classname"
                        class="padder"
                        solo
                        return-object
                        single-line
                        ></v-select>
                        </v-flex>

                        <v-flex
                        md6
                        >
                        <v-select
                        v-model="inputs.schoolyear"
                        :items="getSchoolYear"
                        :rules="rule.schoolyear"
                        item-value="id"
                        label="School Year"
                        persistent-hint
                        class="padder"
                        solo
                        single-line
                        ></v-select>
                        </v-flex>

                        <v-btn :loading="loadingSubmit" color="primary" @click="submitEnroll">
                            Enroll Student
                        </v-btn>

                        <v-btn color="primary" flat @click="goBack">
                            Back
                        </v-btn>

                        </v-layout>
                    </v-form>
                </v-stepper-content>

            </v-stepper-items>
        </v-stepper>
        <v-btn style="margin-top: 15px;" flat color="primary" @click="closeEnrollment">
            Close
        </v-btn>
    </div>
</template>

<script>
const inputs = {
    studentsid: 0,
    schoolyear: "",
    classid: 0
}
const resetInputs = Object.assign({}, inputs)
import { api, postHeader } from "./../../../../../app";
export default {
    data() { return {
        inputs,
        enrollments: [],
        classes: [],
        loadingEnrollment: false,
        loadingSubmit: false,
        step: 1,
        th: [
            { text: "Grade Level", sortable: false, value: "classname", align: "left" },
            { text: "School Year", sortable: false, value: "section", align: "left" },
            { text: "Date Enrolled", sortable: false, value: "section", align: "left" },
            { text: "Status", sortable: false, value: "status", align: "left" }
        ],
        rule: {
            classid: [
                v => !!v || 'Class is required'
            ],
            schoolyear: [
                v => !!v || 'School year is required'
            ]
        }
    } },
    props: ['sid'],
    watch: {
        sid( newVal, oldVal ) {
            if( newVal.open && ( newVal.student.id != oldVal.student.id ) ) {
                // fetch here
                this.fetchEnrollment();
            }

            if( newVal.isAccountant && this.th.length < 5 ) {
                this.th.push({ text: "Balance", sortable: false, value: "balance", align: "left" });
                this.th.push({ text: "", sortable: false, value: "options", align: "right" });
            }
        }
    },
    computed: {
        getEnrollment() {
            const e = this;
            return e.enrollments.map( function( row ) {
                row.classname = e.classes[e.getIndexClass( row.classid )].classname;
                row.status = isNaN( parseInt( row.status ) ) || parseInt( row.status ) == 0 ? 0 : parseInt( row.status );
                return row;
            });
        },
        getSchoolYear() {
            return [
                    parseInt(new Date().getFullYear() - 1) + "-" + parseInt(new Date().getFullYear()), 
                    parseInt(new Date().getFullYear()) + "-" + parseInt(new Date().getFullYear() + 1)
                ];
        }
    },
    methods: {
        fetchEnrollment() {
            const e = this;
            e.loadingEnrollment = true;
            api.get("/registrar/enrollment/" + e.sid.student.id )
            .then((response) => {
                e.enrollments = response.data.enrollments;
                e.classes = response.data.classes;
            })
            .catch(( error ) => e.$emit("alert", { message: "Error when fetching the data [" + error + "].", type: "error" }))
            .then(() => e.loadingEnrollment = false);
        },
        closeEnrollment() {
            this.step = 1;
            this.$emit("close");
            this.resetForm();
        },
        getIndexClass( id ) {
            return this.classes.findIndex(m => m.id === id);
        },
        submitEnroll() {
            const e = this;
            if( !e.$refs.enrollmentform.validate() ) {
                e.$emit("alert", { message: "Please check the error fields", type: "error" })
            } else {
                const data = Object.assign({}, e.inputs)
                data.studentsid = this.sid.student.id;
                data.class = JSON.stringify(data.classid);
                data.classid = data.classid.id;
                e.status = 0;
                e.enrollStudent( data );
            }
        },
        enrollStudent( data ) {
            const e = this;
            e.loadingSubmit = true;
            api.post("/registrar/enrollment/", data, postHeader)
            .then(( response ) => {
                data.dateenrolled = response.data.response.datenow;
                e.enrollments.push( data );
                e.step = 1;
                e.resetForm();
            })
            .catch(( error ) => e.$emit("alert", { message: "Error when submitting the form [" + error + "].", type: "error" }))
            .then(() => e.loadingSubmit = false);
        },
        resetForm() {
            this.$refs.enrollmentform.resetValidation();
            this.$refs.enrollmentform.reset();
            this.inputs = resetInputs;
        },
        goBack() {
            this.step = "1";
            this.resetForm();
        }
    },
}
</script>

<style scoped lang="scss">
    .enrolled { color: green; }
    .pending { color: #3F51B5; }
    .padder { padding: 0 10px; }
</style>
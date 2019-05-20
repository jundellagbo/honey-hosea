<template>
    <div>
    <v-stepper v-model="step" style="width: 100%;" light>
        <v-stepper-items>
            <v-stepper-content step="records">

                <v-btn color="primary" @click="step = 'form'">
                    NEW RECORD
                </v-btn>

                <v-data-table
                :headers="th"
                :items="recordsList"
                :no-data-text="loadingRecords ? `Loading...` : `There is no record available.`"
                :loading="loadingRecords"
                hide-actions
                >
                    <template v-slot:items="props">
                        <tr>
                            <td class="text-xs-left">
                                {{ props.item.recordings.classname.classname }}
                            </td>
                            <td class="text-xs-left">
                                {{ props.item.recordings.section.sectionname }}
                            </td>

                            <td class="text-xs-right">

                                <v-tooltip bottom>
                                    <template v-slot:activator="{ on }">
                                        <v-btn icon v-on="on" color="primary" small dark @click="viewRecord(props.item)">
                                        <v-icon size="14">assignment</v-icon>
                                        </v-btn>
                                    </template>
                                    <span>View Record</span>
                                </v-tooltip>

                                <v-tooltip bottom>
                                    <template v-slot:activator="{ on }">
                                        <v-btn icon v-on="on" color="primary" small dark @click="editRecord(props.item)">
                                        <v-icon size="14">edit</v-icon>
                                        </v-btn>
                                    </template>
                                    <span>Edit Record</span>
                                </v-tooltip>

                                <v-tooltip bottom>
                                    <template v-slot:activator="{ on }">
                                        <v-btn icon v-on="on" color="error" small @click="removeRecord(props.item.id, props.item.index)">
                                        <v-icon size="14">delete</v-icon>
                                        </v-btn>
                                    </template>
                                    <span>Remove</span>
                                </v-tooltip>
                            </td>
                        </tr>
                    </template>
                </v-data-table>
            </v-stepper-content>

            <v-stepper-content step="show_records" v-if="showRecords">
                <p>Navigation: <a @click="step = 'records'; resetForm();">Records</a> / Show</p>
                <p>Grade Level: <strong>{{ showRecords.classname.classname }}</strong></p>
                <p>Section: <strong>{{ showRecords.section.sectionname }}</strong></p>
                <p>Average Grade: <strong>{{ averageGrade }} <span :class="averageGrade >= 75 ? 'passed' : 'failed'">({{ averageGrade >= 75 ? "passed" : "failed" }})</span></strong></p>
                <v-layout>
                    <v-flex md4 v-for="(record, index) in showRecords.records" :key="index" style="padding: 5px;">
                        <v-card style="padding: 15px;">
                            <p>Subject: <strong>{{ record.subject.metaname}}</strong></p>
                            <p>Teacher: <strong>{{ record.teacher.metaname}}</strong></p>
                            <p>
                                Grade: {{ record.grade }}&nbsp;
                                <span :class="record.grade >= 75 ? 'passed' : 'failed'">
                                    ({{ record.grade >= 75 ? 'passed' : 'failed' }})
                                </span>
                            </p>
                        </v-card>
                    </v-flex>
                </v-layout>
            </v-stepper-content>

            <v-stepper-content step="form">
                <v-card style="padding: 15px;">

                    <v-form ref="recordsform">
                        <v-layout v-if="isLoading">
                        <v-flex
                        md12
                        style="text-align: center; width: 100%;"
                        >
                        <v-progress-circular
                        indeterminate
                        color="primary"
                        style="margin-bottom: 15px;"
                        ></v-progress-circular>
                        <p>Loading Form</p>
                        </v-flex>
                        </v-layout>

                        <v-layout wrap justify-space-between v-else>
                        <v-flex md12 class="padder">
                        <p>Navigation: <a @click="step = 'records';  resetForm();">Records</a> / Form</p>
                        <p>Please fill out the form.</p>
                        <p><router-link to="classes" target="_blank">Click here</router-link> to add class and section.</p>
                        <p><router-link to="teachers" target="_blank">Click here</router-link> to add teacher ( Note: active or inactive teacher will always show in the teacher selection field ).</p>
                        <p><router-link to="subjects" target="_blank">Click here</router-link> to add subjects.</p>
                        </v-flex>

                        <v-flex
                        md6
                        >
                        <v-select
                        v-model="inputs.classname"
                        :items="selects.class"
                        :rules="rule.classname"
                        label="Select Class"
                        persistent-hint
                        item-text="classname"
                        item-value="classname"
                        class="padder"
                        solo
                        single-line
                        return-object
                        @change="selectClass"
                        ></v-select>
                        </v-flex>

                        <v-flex
                        md6
                        >
                        <v-select
                        v-model="inputs.section"
                        :items="sections"
                        :rules="rule.section"
                        item-value="section"
                        label="Select Section"
                        persistent-hint
                        item-text="sectionname"
                        class="padder"
                        solo
                        return-object
                        single-line
                        ></v-select>
                        </v-flex>


                        <v-layout v-for="(record, index) in getRecords" :key="index">
                        <v-flex
                        md4
                        >
                        <v-select
                        v-model="record.teacher"
                        :items="selects.teachers"
                        :rules="record.rule.teacher"
                        label="Select Teacher"
                        persistent-hint
                        item-text="metaname"
                        class="padder"
                        solo
                        return-object
                        single-line
                        ></v-select>
                        </v-flex>

                        <v-flex
                        md4
                        >
                        <v-select
                        v-model="record.subject"
                        :items="selects.subjects"
                        :rules="record.rule.subject"
                        label="Select Subject"
                        persistent-hint
                        item-text="metaname"
                        class="padder"
                        solo
                        return-object
                        single-line
                        ></v-select>
                        </v-flex>

                        <v-flex
                        md4
                        >
                        <v-text-field
                        v-model="record.grade"
                        :rules="record.rule.grade"
                        label="Enter grade"
                        class="padder"
                        solo
                        type="number"
                        single-line
                        ></v-text-field>
                        </v-flex>

                        <v-btn v-if="index > 0" fab small @click="inputs.records.splice(index, 1)" color="error">
                            <v-icon>remove</v-icon>
                        </v-btn>

                        </v-layout>

                        
                        <v-layout>
                        <v-btn :loading="loadingSubmit" @click="save" color="primary">
                        Save
                        </v-btn>

                        <v-btn @click="resetForm(); step = 'records'" v-if="inputs.id" flat color="primary">
                        Cancel
                        </v-btn>

                        <v-btn @click="addField" color="primary" flat style="float: right;">
                        Add Field
                        </v-btn>
                        </v-layout>

                        </v-layout>
                        
                    </v-form>
                </v-card>
            </v-stepper-content>
        </v-stepper-items>
    </v-stepper>
    <v-btn style="margin-top: 15px;" flat color="primary" @click="closeRecord">
    Close
    </v-btn>
    </div>
</template>

<script>
import { api, postHeader } from "./../../../../../app";
const record = {
    subject: "",
    teacher: "",
    grade: ""
};
const inputs =  {
    id: 0,
    classname: "",
    section: "",
    index: 0,
    records: [Object.assign({}, record)]
};
const resetInput = Object.assign({}, inputs);
const rule = {
    classname: [
        v => !!v || 'Class is required'
    ],
    section: [
        v => !!v || 'Section is required'
    ]
}
export default {
    data() { return {
        inputs,
        resetInput,
        rule,
        isLoading: false,
        selects: {},
        sections: [],
        loadingSubmit: false,
        records: [],
        loadingRecords: false,
        step: "records",
        th: [
            { text: "Grade Level", sortable: false, value: "classname", align: "left" },
            { text: "Section Name", sortable: false, value: "section", align: "left" },
            { text: "OPTIONS", sortable: false, value: "options", align: "right" }
        ],
        showRecords: null
    } },
    props: ['sid'],
    watch: {
        sid( newVal, oldVal ) {
            if( newVal.open && ( newVal.student.id != oldVal.student.id ) ) {
                // fetch here
                this.fetchRecords( newVal.student.id );
            }
        }
    },
    computed: {
        getRecords() {
            return this.inputs.records.map(function(row, index) {
                row.rule = {
                    subject: [
                        v => !!v || 'Subject is required'
                    ],
                    teacher: [
                        v => !!v || 'Teacher is required'
                    ],
                    grade: [
                        v => !!v || 'Grade is required',
                        v => v >= 60 || 'Grade must be 60 or up',
                    ]
                };
                row.index = index;
                return row;
            });
        },
        recordsList() {
            return this.records.map(function(row, index) {
                row.index = index;
                row.id = parseInt(row.id);
                row.recordings = JSON.parse( row.records );
                return row;
            });
        },
        averageGrade() {
            let total = 0;
            let average = 0;
            this.showRecords.records.map( function(row) {
                average++;
                total += parseInt(row.grade);
            });
            return parseInt(total/average);
        }
    },
    mounted() {
        this.fetchItems();
    },
    methods: {
        fetchItems() {
            const e = this;
            e.isLoading = true;
            api.get("/registrar/records/autocomplete")
            .then(( response ) => e.selects = response.data.items)
            .catch(( error ) => e.$emit("alert", { message: "Error when fetching the form [" + error + "].", type: "error" }))
            .then(() => e.isLoading = false)
        },
        fetchRecords( studentsid ) {
            const e = this;
            e.records = [];
            e.loadingRecords = true;
            api.get("/registrar/record/" + studentsid)
            .then(( response ) => {
                e.records = response.data.records;
            })
            .catch(() => e.$emit("alert", { message: "", type: ""}))
            .then(() => e.loadingRecords = false);
        },
        selectClass( val ) {
            this.sections = val.sections;
        },
        resetForm() {
            this.$refs.recordsform.resetValidation();
            this.$refs.recordsform.reset();
            this.inputs = resetInput;
        },
        closeRecord() {
            this.$emit("close");
            this.step = "records";
            this.resetForm();
        },
        addField() {
            this.inputs.records.push(Object.assign({}, record));
        },
        save() {
            if( !this.$refs.recordsform.validate() ) {
                this.$emit("alert", { message: "Please check the error fields", type: "error" })
            } else {
                const inputData = Object.assign({}, this.inputs)
                delete inputData.index;
                let recordsData = inputData.records.map( function( row ) {
                    return row;
                });
                inputData.records = recordsData;
                let dataInput = {
                    id: inputData.id,
                    studentsid: this.sid.student.id,
                    records: JSON.stringify( inputData )
                }
                this.submitToApi( dataInput );
            }
        },
        submitToApi( inputsData ) {
            const e = this;
            let inputs = Object.assign({}, inputsData);
            e.loadingSubmit = true;
            api.post("/registrar/records", inputs, postHeader)
            .then(( response ) => {
                if( inputs.id != 0 ) {
                    // edit here
                    e.$set( e.records, e.inputs.index, inputs );
                    e.$emit("alert", { message: "Record has been updated!", type: "success" })
                } else {
                    // insert here
                    inputs.id = parseInt( response.data.response.id );
                    e.records.push(inputs);
                    e.$emit("alert", { message: "New Records has been saved", type: "success" })
                }
                e.loadingSubmit = false;
                e.resetForm();
                e.step = "records";
            })
            .catch(( error ) => e.$emit("alert", { message: "Error when submitting the form [" + error + "].", type: "error" }))
            .then(() => e.loadingSubmit = false)
        },
        editRecord( item ) {
            const getItem = Object.assign({}, item);
            getItem.records = JSON.parse( getItem.records ).records;
            let indexing = this.selects.class.findIndex(m => m.classname === getItem.recordings.classname.classname);
            this.sections = this.selects.class[indexing].sections;
            getItem.classname = getItem.recordings.classname;
            getItem.section = getItem.recordings.section;
            this.inputs = getItem;
            this.step = "form";
        },
        viewRecord( item ) {
            this.showRecords = item.recordings;
            this.step = "show_records"
        },
        async removeRecord(id, index) {
            let res = await this.$confirm('Are you sure you want to remove this record?<p><strong>Note: </strong> This cannot be undone.</p>', { title: "Confirm Delete", buttonTrueText: "Remove", buttonFalseText: "Cancel", persistent: true, icon: "warning", color: "warning" });
            if( res ) {
                // removing;
                this.confirmRemove( id, index );
            }
        },
        confirmRemove( id, index ) {
            const e = this;
            api.get("/registrar/record/remove/" + id)
            .then(() => {
                e.records.splice( index, 1 );
                e.$emit("alert", { message: "Record has been removed", type: "success" })
            })
            .catch(( error ) => e.$emit("alert", { message: "Error when removing remord [" + error + "].", type: "error" }));
        }
    },
}
</script>

<style lang="scss" scoped>
.passed { color: green; }
.failed { color: red; }
.padder { padding: 0 10px; }
</style>

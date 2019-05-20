<template>
    <custom-layout>
        <v-flex>
            <v-card>
                <v-card-title>
                    <v-btn color="primary" @click="studentsform = true">
                        NEW STUDENTS
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
                :items="studentslist"
                :search="search"
                :no-results-text="`Your search for (${search}) student found no results.`"
                :no-data-text="loadStudents ? `Loading...` : `There is no students available.`"
                :loading="loadStudents"
                hide-actions
                >
                <template v-slot:items="props">
                    <tr>
                        <td class="text-xs-left">
                            {{ props.item.fullname }}
                        </td>
                        <td class="text-xs-left">
                            {{ props.item.studentsid }}
                        </td>
                        <td class="text-xs-left">{{ props.item.sex }}</td>
                        <td class="text-xs-left">{{ props.item.dateofbirth | moment("MMMM D, YYYY") }}</td>
                        <td class="text-xs-left">
                            <div v-if="props.item.status != 0">
                                <span class="active__">ACTIVE</span>
                            </div>
                            <div v-else>
                                <span class="not_active__">INACTIVE</span>
                            </div>
                        </td>
                        <td class="text-xs-right">

                            <v-tooltip bottom>
                                <template v-slot:activator="{ on }">
                                    <v-btn icon v-on="on" color="primary" small dark @click="student = Object.assign({ open: true }, props.item)">
                                    <v-icon size="14">person</v-icon>
                                    </v-btn>
                                </template>
                                <span>Student Information</span>
                            </v-tooltip>

                            <v-tooltip bottom>
                                <template v-slot:activator="{ on }">
                                    <v-btn icon v-on="on" color="primary" small dark @click="editStudent(props.item.index)">
                                    <v-icon size="14">edit</v-icon>
                                    </v-btn>
                                </template>
                                <span>Edit Student</span>
                            </v-tooltip>

                            <v-tooltip bottom>
                                <template v-slot:activator="{ on }">
                                    <v-btn icon v-on="on" color="info" small @click="openRequirements( { id: props.item.id, studentsid: props.item.studentsid, fullname: props.item.fullname } )">
                                    <v-icon size="14">filter_none</v-icon>
                                    </v-btn>
                                </template>
                                <span>Requirements</span>
                            </v-tooltip>

                            <v-tooltip bottom>
                                <template v-slot:activator="{ on }">
                                    <v-btn icon v-on="on" color="info" small dark @click="records_ = Object.assign({ open: true, student: props.item })">
                                    <v-icon size="14">library_books</v-icon>
                                    </v-btn>
                                </template>
                                <span>Records</span>
                            </v-tooltip>

                            <v-tooltip bottom>
                                <template v-slot:activator="{ on }">
                                    <v-btn icon v-on="on" color="warning" small dark @click="enrollment = Object.assign({ open: true, student: props.item, isAccountant: false })">
                                    <v-icon size="14">description</v-icon>
                                    </v-btn>
                                </template>
                                <span>Enrollments</span>
                            </v-tooltip>
                            
                        </td>
                    </tr>
                </template>
                </v-data-table>
            </v-card>

            <message :alert="alert" @close="alert.open = false" />
            
            <modal :dialog="{
                title: 'STUDENTS FORM',
                open: studentsform,
                width: 800
            }">
                <students-form 
                @closeStudentsForm="closeStudentsForm"
                @alert="_alert"
                @store="storeStudents"
                :editInputs="editInputs"
                />
            </modal>

            <modal :dialog="{
                title: '',
                open: requirements.open,
                width: 800
            }">
                <requirements 
                :sid="requirements"
                @closeModal="closeRequirements"
                @alert="_alert"
                />
            </modal>

            <modal :dialog="{
                title: student.studentsid,
                open: student.open,
                width: 500
            }">
                <p><strong>Name:</strong> {{ student.fname }} {{ student.mname }} {{ student.lname }} {{ student.extension }}</p>
                <p><strong>Age:</strong> {{ student.age }}</p>
                <p><strong>Birthdate:</strong> {{ student.dateofbirth | moment("MMMM D, YYYY") }}</p>
                <p><strong>Gender:</strong> {{ student.sex }}</p>
                <h3 style="margin-bottom: 10px;">Other Information:</h3>
                <p><strong>Street:</strong> {{ student.street }}</p>
                <p><strong>Barangay:</strong> {{ student.barangay }}</p>
                <p><strong>City:</strong> {{ student.city }}</p>
                <p><strong>Zip:</strong> {{ student.zip }}</p>
                <p><strong>Tel #:</strong> {{ student.tel }}</p>
                <p><strong>Cel #:</strong> {{ student.cel }}</p>
                <h3 style="margin-bottom: 10px;">Guardian Information:</h3>
                <p><strong>Guardian:</strong> {{ student.gname }} {{ student.gmname }} {{ student.glname }}</p>
                <v-btn flat color="primary" @click="student.open = false">
                    Close
                </v-btn>
            </modal>

            <modal :dialog="{
                title: records_.student.fullname + ' - Records',
                open: records_.open,
                width: 900
            }">
                <records :sid="records_" @alert="_alert" @close="records_.open = false" />
            </modal>

            <modal :dialog="{
                title: 'Enrollment Records',
                open: enrollment.open,
                width: 900
            }">
                <enrollment :sid="enrollment" @alert="_alert" @close="enrollment.open = false" />
            </modal>

        </v-flex>
    </custom-layout>
</template>


<script>
import { api } from "./../../../app"
import Layout from "./../../components/Layout.vue"
import Message from "./../../components/Message.vue"
import Modal from "./../../components/Modal.vue"
import StudentsForm from "./components/Students/StudentsForm.vue"
import Requirements from "./components/Students/Requirements.vue"
import Records from "./components/Students/Records.vue"
import Enrollment from "./components/Students/Enrollment.vue"
export default {
    components: {
        'custom-layout': Layout,
        'message': Message,
        'modal': Modal,
        'students-form': StudentsForm,
        'requirements': Requirements,
        'records': Records,
        'enrollment': Enrollment
    },
    data() {
        return {
            enrollment: {
                open: false,
                student: {}
            },
            records_: {
                open: false,
                student: {}
            },
            studentsform: false,
            alert: {
                open: false,
                text: "",
                type: "",
                key: 0
            },
            search: "",
            th: [
                { text: "NAMES", sortable: true, value: "fullname", align: "left" },
                { text: "STUDENTS ID", sortable: true, value: "studentsid", align: "left" },
                { text: "GENDER", sortable: false, value: "gender", align: "left" },
                { text: "BIRTHDATE", sortable: false, value: "birthdate", align: "left" },
                { text: "STATUS", sortable: false, value: "status", align: "left" },
                { text: "OPTIONS", sortable: false, value: "options", align: "right" }
            ],
            students: [],
            loadStudents: true,
            editIndex: 0,
            editInputs: null,
            requirements: {
                open: false,
                id: 0,
                studentid: 0,
                fullname: ""
            },
            confirm: {
                open: false,
                text: "",
                key: 0
            },
            student: {}
        }
    },
    computed: {
        studentslist() {
            const e = this;
            return e.students.map( function( student, index ) {
                student.fullname = student.lname + ", " + student.fname + " " + student.mname.charAt(0) + ". " + student.extension;
                student.index = index;
                student.status = parseInt(student.status);
                student.id = parseInt(student.id);
                student.studentsid = parseInt(student.studentsid);
                student.age = e.calculateAge(student.dateofbirth);
                return student;
            });
        }
    },
    methods: {
        calculateAge( dateString ) {
            var today = new Date();
            var birthDate = new Date(dateString);
            var age = today.getFullYear() - birthDate.getFullYear();
            var m = today.getMonth() - birthDate.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) 
            {
                age--;
            }
            return age;
        },
        openAlert( text, type ) {
            this.alert = {
                key: Date.now(),
                text,
                type,
                open: true
            }
        },
        _alert( param ) {
            this.openAlert( param.message, param.type );
        },
        fetchStudents() {
            const e = this;
            api.get("/registrar/students")
            .then(( response ) => {
                e.students = response.data.students;
            })
            .catch(( error ) => {
                e.openAlert( "Error when fetching students data [" + error + "].", "error" );
            })
            .then(() => e.loadStudents = false)
        },
        storeStudents( response ) {
            if( response.type == "insert" ) {
                this.students.push( response.output );
                this.openAlert('Success! student has been added.', 'success');
            } else {
                this.$set(this.students, this.editIndex, response.output)
                this.openAlert('Success! student has been updated.', 'success');
            }
        },
        editStudent( index ) {
            this.studentsform = true;
            const copy = Object.assign({}, this.students[index])
            this.editInputs = copy
            this.editIndex = index;
        },
        closeStudentsForm() {
            this.studentsform = false;
            this.clearEdits();
        },
        clearEdits() {
            this.editInputs = null;
            this.editIndex = 0;
        },
        openRequirements( data ) {
            this.requirements = {
                open: true,
                id: data.id,
                studentid: data.studentsid,
                fullname: data.fullname
            }
        },
        closeRequirements() {
            this.requirements.open = false;
        }
    },
    mounted() {
        this.fetchStudents();
    }
}
</script>

<style scoped>
.active__ {
    color: #3F51B5;
    font-weight: 500;
}
.not_active__ {
    color: #ff5252;
    font-weight: 500;
}
</style>

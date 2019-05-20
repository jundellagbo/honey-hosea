<template>
    <custom-layout>
        <v-flex>
            <v-card>
                <v-card-title>
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
                                    <v-btn icon v-on="on" color="warning" small dark @click="enrollment = Object.assign({ open: true, student: props.item, isAccountant: true })">
                                    <v-icon size="14">description</v-icon>
                                    </v-btn>
                                </template>
                                <span>Statement of Account</span>
                            </v-tooltip>
                            
                        </td>
                    </tr>
                </template>
                </v-data-table>
            </v-card>

            <message :alert="alert" @close="alert.open = false" />

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
                title: 'Statement of Account',
                open: enrollment.open,
                width: 900
            }">
                <enrollment :sid="enrollment" @close="enrollment.open = false" />
            </modal>

        </v-flex>
    </custom-layout>
</template>


<script>
import { api } from "./../../../app"
import Layout from "./../../components/Layout.vue"
import Message from "./../../components/Message.vue"
import Modal from "./../../components/Modal.vue"
import Enrollment from "./../Registrar/components/Students/Enrollment.vue"
export default {
    components: {
        'custom-layout': Layout,
        'message': Message,
        'modal': Modal,
        'enrollment': Enrollment
    },
    data() {
        return {
            enrollment: {
                open: false,
                student: {}
            },
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
        _alert( param ) {
            this.openAlert( param.message, param.type );
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
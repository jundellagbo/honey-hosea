<template>
    <custom-layout>

        <v-layout row wrap>
            <v-flex md3 class="padder">
                <v-card class="cards">
                    <v-form ref="classes">
                        <v-flex>
                            <v-text-field
                                v-model="input.classname"
                                :rules="rule"
                                label="Enter Class"
                                solo
                            ></v-text-field>
                            <v-btn 
                            color="primary"
                            block
                            @click="save"
                            :loading="loadingSubmit"
                            >{{ input.id ? "Save Class" : "Add Class" }}</v-btn>

                            <v-btn 
                            flat
                            block
                            v-if="input.id"
                            @click="cancel"
                            >Cancel</v-btn>

                        </v-flex>
                    </v-form>
                </v-card>

                <v-checkbox
                v-model="showDelete"
                label="Enable Delete"
                color="error"
                :true-value="1"
                :false-value="0"
                ></v-checkbox>

            </v-flex>

            <v-flex md12 />

            <v-progress-circular
            :size="50"
            color="primary"
            indeterminate
            style="margin: 50px 20px 20px 20px"
            v-if="loadClasses"
            ></v-progress-circular>

            <p v-if="!loadClasses && !classes.length" class="padder">There is no class available.</p>

            <v-flex md3 class="padder" v-for="class_ in classList" :key="class_.id">
                <v-card class="cards" hover>
                    <v-card-text class="headline font-weight-bold text-center">
                        {{ class_.classname }}
                    </v-card-text>

                    <v-card-actions>

                    <v-spacer></v-spacer>

                    <v-tooltip bottom>
                        <template v-slot:activator="{ on }">
                            <v-btn icon flat v-on="on" color="primary" class="btn" @click="openSubject(class_)">
                                <v-icon>ballot</v-icon>
                            </v-btn>
                        </template>
                        <span>Sections</span>
                    </v-tooltip>

                    <v-tooltip bottom>
                        <template v-slot:activator="{ on }">
                            <v-btn icon flat v-on="on" color="info" class="btn" @click="editClass(class_)">
                                <v-icon>edit</v-icon>
                            </v-btn>
                        </template>
                        <span>Edit</span>
                    </v-tooltip>

                    <v-tooltip bottom v-if="showDelete">
                        <template v-slot:activator="{ on }">
                            <v-btn icon flat v-on="on" color="error" class="btn" @click="confirmDelete(class_.id, class_.index)">
                                <v-icon>delete</v-icon>
                            </v-btn>
                        </template>
                        <span>Delete</span>
                    </v-tooltip>

                    </v-card-actions>
                </v-card>
            </v-flex>
        </v-layout>

        
        <message :alert="alert" @close="alert.open = false" />

        <modal :dialog="{
            title: '',
            open: subjectModal.open,
            width: 900
        }">
            <subjects-component 
            @close="closeSubject" 
            :selectedClass="subjectModal" 
            @alert="_alert"/>
        </modal>

    </custom-layout>
</template>
<script>
import Layout from "./../../components/Layout.vue"
import Message from "./../../components/Message.vue"
import Modal from "./../../components/Modal.vue"
import Subjects from "./components/Class/Sections.vue"
import { api, postHeader } from "./../../../app"
export default {
    components: {
        'custom-layout': Layout,
        'message': Message,
        'modal': Modal,
        'subjects-component': Subjects
    },
    data() {
        return {
            subjectModal: {
                open: false,
                class: { id: 0, classname: "", index: 0 }
            },
            input: {
                id: 0,
                classname: "",
                index: 0
            },
            rule: [
                v => !!v || 'Class name is required'
            ],
            loadingSubmit: false,
            loadClasses: false,
            classes: [],
            search: "",
            alert: {
                open: false,
                text: "",
                type: "",
                key: 0
            },
            showDelete: false
        }
    },
    computed: {
        classList() {
            return this.classes.map( function( row, index ) {
                row.index = index;
                row.id = parseInt( row.id );
                return row;
            })
        }
    },
    mounted() {
        this.fetchClasses();
    },
    methods: {
        openAlert( text, type ) {
            this.alert = {
                text,
                type,
                key: Date.now(),
                open: true
            }
        },
        _alert( param ) {
            this.openAlert( param.message, param.type );
        },
        fetchClasses() {
            const e = this;
            e.loadClasses = true;
            api.get("/registrar/classes")
            .then(( response ) => {
                e.classes = response.data.classes;
            })
            .catch(() => {
                e.openAlert("Error when fetching classes", "error");
            })
            .then(() => e.loadClasses = false)
        },
        submitClass() {
            const e = this;
            e.loadingSubmit = true;
            const inputs = Object.assign({}, e.input)
            api.post("/registrar/class", inputs, postHeader)
            .then(( response ) => {
                let id = parseInt(response.data.response.id);
                if( inputs.id ) {
                    // edit here
                    this.$set( e.classes, inputs.index, inputs );
                    e.openAlert("Class has been saved!", "success");
                } else {
                    // insert here
                    if( id == -1 ) {
                        e.openAlert("You're entering existing classname", "error");
                    } else {
                        inputs.id = id
                        e.classes.push( inputs );
                        e.openAlert("New Class has been added!", "success");
                    }
                }
                e.resetForm();
            })
            .catch(( error ) => {
                e.openAlert("Error when submitting the form [" + error + "].", "error")
            })
            .then(() => e.loadingSubmit = false)
        },
        save() {
            if(!this.$refs.classes.validate()) {
                this.openAlert("Please check the error fields", "error");
            } else {
                const inputs = Object.assign({}, this.input);
                let exists = this.classes.filter(m => m.classname.toLowerCase() === inputs.classname.toLowerCase()).length
                if( exists ) {
                    this.openAlert("You're entering existing classname", "error");
                } else {
                    this.submitClass();
                }
            }
        }, 
        resetForm() {
            this.$refs.classes.reset();
            this.$refs.classes.resetValidation();
            this.input = {
                id: 0,
                classname: "",
                index: 0
            }
        },
        cancel() {
            this.resetForm();
            this.alert.open = false;
        },
        async confirmDelete( id, index ) {
            this.cancel();
            let res = await this.$confirm('Are you sure you want to remove this class?<br> <strong>Note:</strong> All schedules inside this class will be removed', { title: "Confirm Delete", buttonTrueText: "Remove", buttonFalseText: "Cancel", persistent: true, icon: "warning", color: "warning" });
            if( res ) {
                this.completeRemove( id, index );
            }
        },
        completeRemove(id, index) {
            const e = this;
            api.get("/registrar/class/" + id)
            .then(() => {
                e.classes.splice(index, 1)
                e.openAlert("Class has been successfully removed.", "success");
            })
            .catch(() => {
                e.openAlert("Error when removing class", "error");
            })
        },
        editClass( params ) {
            const copy = Object.assign({}, params);
            this.input = copy;
            this.openAlert("Class named [" + copy.classname + "] is ready to edit.", "info");
        },
        openSubject( class_ ) {
            this.subjectModal = {
                open: true,
                class: class_
            }
        },
        closeSubject() {
            this.subjectModal.open = false
        }
    }
}
</script>
<style scoped>
    .padder {
        padding: 15px;
    }
    .cards {
        padding: 15px;
    }
    .btn {
        margin: 0 5px;
    }
</style>
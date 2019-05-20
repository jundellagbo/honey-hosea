<template>
    <div>
        <h3 style="margin: 20px;">{{ selectedClass.class.classname }} Sections</h3>

        <v-layout>
            <v-flex md6>
                <v-card style="margin: 15px; padding: 15px;">
                <v-form ref="sectionform">
                    <v-flex>
                        <v-text-field
                            v-model="input.sectionname"
                            :rules="rule.sectionname"
                            label="Enter Section"
                            solo
                        ></v-text-field>

                        <v-text-field
                            v-model="input.average"
                            :rules="rule.average"
                            label="Average"
                            type="number"
                            solo
                        ></v-text-field>

                        <v-btn 
                        color="primary"
                        block
                        @click="save"
                        :loading="loadingSubmit"
                        >{{ input.id ? "Save Section" : "Add Section" }}</v-btn>
                        <v-btn 
                        flat
                        block
                        v-if="input.id"
                        @click="cancel"
                        >Cancel</v-btn>
                    </v-flex>
                </v-form>
                </v-card>
            </v-flex>

            <v-flex md6>

                <v-flex md12 justify-center style="text-align: center;">
                <v-progress-circular
                :size="50"
                color="primary"
                indeterminate
                style="margin: 50px 20px 20px 20px"
                v-if="loadingsections"
                ></v-progress-circular>
                </v-flex>

                <p style="margin-top: 15px; text-align: center;" v-if="!loadingsections && !sectionlists.length">There is no section available.</p>
                <v-list style="margin: 15px;">
                    <v-list-tile v-for="section in sectionlists" :key="section.id" class="border" light>
                        <v-list-tile-content>
                            <v-list-tile-title>
                                {{ section.sectionname }}
                            </v-list-tile-title>
                            <v-list-tile-sub-title>
                                Average: {{ section.average }}
                            </v-list-tile-sub-title>
                        </v-list-tile-content>
                        <v-list-tile-action>
                            <v-menu bottom left>
                            <template v-slot:activator="{ on }">
                                <v-btn icon v-on="on">
                                <v-icon>settings</v-icon>
                                </v-btn>
                            </template>
                            <v-list>
                                <v-list-tile @click="edit(section)">
                                    <v-list-tile-title>Edit</v-list-tile-title>
                                </v-list-tile>
                                <v-list-tile @click="remove(section)">
                                    <v-list-tile-title>Delete</v-list-tile-title>
                                </v-list-tile>
                                <v-list-tile @click="function() { return false; }">
                                    <v-list-tile-title>Cancel</v-list-tile-title>
                                </v-list-tile>
                            </v-list>
                            </v-menu>
                        </v-list-tile-action>
                    </v-list-tile>
                </v-list>
            </v-flex>
        </v-layout>

        <v-btn style="margin-top: 15px;" @click="closeSection" flat color="primary">Close</v-btn>
    </div>  
</template>

<script>
import { api, postHeader } from "./../../../../../app";
import { inputs, resetInputs, validations, scheduleProps } from "./js/Sections";
export default {
    data() {
        return {
            input: inputs,
            rule: validations,
            loadingSubmit: false,
            sections: [],
            loadingsections: false,
            subjectProps: scheduleProps,
            openSubject: true
        }
    },
    props: ['selectedClass'],
    watch: {
        selectedClass( newVal, oldVal ) {
            if( ( newVal.open ) && ( newVal.class.id != oldVal.class.id ) ) {
                this.fetchSections();
            }
        }
    },
    computed: {
        subjInput() {
            let inputs = this.input;
            inputs.classid = this.selectedClass.class.id;
            return inputs;
        },
        sectionlists() {
            return this.sections.map( function( row, index ) {
                row.index = index;
                row.id = parseInt( row.id );
                return row;
            });
        }
    },
    methods: {
        openAlert( message, type ) {
            this.$emit("alert", { message, type });
        },
        fetchSections() {
            const e = this;
            e.loadingsections = true;
            api.get("/registrar/class/" + e.selectedClass.class.id + "/sections")
            .then(( response ) => {
                e.sections = response.data.sections;
            })
            .catch(( error ) => {
                e.openAlert("Error when fetching sections [" + error + "]", "error");
            })
            .then(() => e.loadingsections = false)
        },
        closeSection() {
            const e = this;
            e.clearForm();
            e.openSubject = false;
            e.$emit("close")
        },
        save() {
            if( !this.$refs.sectionform.validate() ) {
                this.$emit("alert", { message: "Please check the error fields", type: "error" })
            } else {
                // submitted
                const inputs = Object.assign({}, this.subjInput);
                let exists = this.sectionlists.filter(m => m.sectionname.toLowerCase() === inputs.sectionname.toLowerCase()).length;
                if( exists ) {
                    this.openAlert("You're entering an existing section name", "error");
                } else {
                    // submit to api
                    this.storeSection();
                }
            }
        },
        storeSection() {
            const e = this;
            const inputs = Object.assign({}, e.subjInput);
            api.post("/registrar/class/section", inputs, postHeader )
            .then(( response ) => {
                if( inputs.id ) {
                    // edit here
                    e.$set(e.sections, inputs.index, inputs );
                    e.openAlert("Section has been saved!.", "success");
                } else {
                    // insert here
                    inputs.id = parseInt(response.data.response.id);
                    if( inputs.id == -1 ) {
                        e.openAlert("You're entering existing section name.", "error");
                    } else {
                        e.sections.push( inputs );
                        e.openAlert("New Section has been added on this class.", "success");
                    }
                }
                e.clearForm();
            })
            .catch(( error ) => {
                e.openAlert("Error when submitting the form [" + error + "].", "error")
            });
        },
        clearForm() {
            this.$refs.sectionform.reset();
            this.$refs.sectionform.resetValidation();
            this.input = resetInputs;
        },
        cancel() {
            this.clearForm();
        },
        edit( section ) {
            const inputs = Object.assign({}, section);
            this.input = inputs;
            this.openAlert("Section named [" + inputs.sectionname + "] is ready for edit.", "info")
        },
        async remove( section ) {
            this.cancel();
            let res = await this.$confirm('Are you sure you want to remove this section?', { title: "Confirm Delete", buttonTrueText: "Remove", buttonFalseText: "Cancel", persistent: true, icon: "warning", color: "warning" });
            if( res ) {
                this.deleteSection( section );
            }
        },
        deleteSection( section ) {
            const e = this;
            e.loadingSubmit = true;
            const inputs = Object.assign({}, section);
            api.get("/registrar/class/section/" + inputs.id)
            .then(() => {
                e.sections.splice( inputs.index, 1 );
                e.openAlert("Section has been removed.", "success");
            })
            .catch((error) => {
                e.openAlert("Error when removing section [" + error + "].", "error");
            })
            .then(() => e.loadingSubmit = false)
        }
    },
}
</script>


<style scoped>
.nopadding { padding: 0; }
.border { border-width: 0 1px 1px 1px; border-color: #eaeaea; border-style: solid; padding: 10px; }
.border:first-child { border-top: 1px solid #eaeaea; }
</style>
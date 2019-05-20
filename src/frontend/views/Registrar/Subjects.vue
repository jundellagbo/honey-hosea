<template>
      <custom-layout>
          <template>
                <v-layout>
                    <v-flex md4 class="padder">
                        <v-card style="padding: 15px;">
                            <v-form ref="subjectform">
                                <v-text-field
                                    v-model="inputs.metaname"
                                    :rules="rules"
                                    label="Enter subject"
                                    solo
                                ></v-text-field>

                                <v-btn color="primary" @click="submit" :loading="loadingSubmit">
                                    Save
                                </v-btn>

                                <v-btn
                                v-if="inputs.id != 0"
                                @click="resetForm"
                                color="primary"
                                flat
                                >Cancel</v-btn>
                            </v-form>
                        </v-card>
                    </v-flex>

                    <v-flex md8 class="padder">
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
                            :items="slist"
                            :search="search"
                            :no-results-text="`Your search for (${search}) subject found no results.`"
                            :no-data-text="loadSubject ? `Loading...` : `There is no subject available.`"
                            :loading="loadSubject"
                            hide-actions
                            >
                            <template v-slot:items="props">
                                <tr>
                                    <td class="text-xs-left">
                                        {{ props.item.metaname }}
                                    </td>

                                    <td class="text-xs-right">

                                        <v-tooltip bottom>
                                            <template v-slot:activator="{ on }">
                                                <v-btn icon v-on="on" color="primary" small dark @click="setEdit( props.item )">
                                                <v-icon size="14">edit</v-icon>
                                                </v-btn>
                                            </template>
                                            <span>Edit Subject</span>
                                        </v-tooltip>

                                        <v-tooltip bottom>
                                            <template v-slot:activator="{ on }">
                                                <v-btn icon v-on="on" color="error" small @click="remove(props.item.id, props.item.index)">
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
                </v-layout>
          </template>
          <message :alert="alert" @close="alert.open = false" />
      </custom-layout>
</template>
<script>
import Layout from "./../../components/Layout.vue"
import Message from "./../../components/Message.vue"
import { api, postHeader } from "./../../../app"
const inputs = {
    id: 0,
    metaname: "",
    metakey: 0,
    metatype: "subject",
    index: 0
};
const resetInputs = Object.assign({}, inputs)
export default {
    components: {
        'custom-layout': Layout,
        'message': Message
    },
    data() { return {
        alert: {
            open: false,
            text: "",
            type: "",
            key: 0
        },
        inputs,
        rules: [
            v => !!v || 'Subject is required'
        ],
        search: "",
        th: [
            { text: "SUBJECTS", sortable: false, value: "metaname", align: "left" },
            { text: "OPTIONS", sortable: false, value: "options", align: "right" }
        ],
        subject: [],
        loadSubject: false,
        loadingSubmit: false
    } },
    computed: {
        slist() {
            return this.subject.map( function( row, key ) {
                row.id = parseInt(row.id);
                row.index = key;
                row.metakey = parseInt( row.metakey);
                return row;
            })
        }
    },
    mounted() {
        this.fetchSubject();
    },
    methods: {
        fetchSubject() {
            const e = this;
            e.loadSubject = true;
            api.get("/registrar/meta/subject")
            .then(( response ) => {
                e.subject = response.data.metas;
            })
            .catch(() => {
                e.openAlert( "Error when fetching subject", "error" );
            })
            .then(() => e.loadSubject = false)
        },
        openAlert( text, type ) {
            this.alert = {
                open: true,
                text,
                type,
                key: Date.now()
            }
        },
        submitToApi( inputs ) {
            const e = this;
            e.loadingSubmit = true;
            inputs.metakey = inputs.metakey ? 1 : 0;
            api.post("/registrar/meta", inputs, postHeader)
            .then(( response ) => {
                if( inputs.id == 0 ) {
                    // insert
                    inputs.id = parseInt(response.data.response.id);
                    e.subject.push( inputs );
                    e.openAlert("New Subject has been added.", "success");
                } else {
                    // update
                    e.$set(e.subject, inputs.index, inputs);
                    e.openAlert("Subject has been saved!", "success");
                }
                e.resetForm();
            })
            .catch(( error ) => {
                e.openAlert("Error when submitting the form [" + error + "].", "error");
            })
            .then(() => e.loadingSubmit = false)
        },
        submit() {
            if( !this.$refs.subjectform.validate() ) {
                this.openAlert("Please check the error fields.", "error");
            } else {
                // submit
                const inputs = Object.assign({}, this.inputs);
                this.submitToApi( inputs );
            }
        },
        resetForm() {
            this.$refs.subjectform.resetValidation();
            this.$refs.subjectform.reset();
            this.inputs = resetInputs;
        },
        setEdit( data ) {
            this.inputs = Object.assign({}, data); 
            this.openAlert("Subject [" + data.metaname + "] is ready to edit.", "info");
        },
        async remove( id, index ) {
            this.resetForm();
            let res = await this.$confirm('Are you sure you want to remove?', { title: "Confirm Delete", buttonTrueText: "Remove", buttonFalseText: "Cancel", persistent: true, icon: "warning", color: "warning" });
            if( res ) {
                // removing;
                this.confirmRemove( id, index );
            }
        },
        confirmRemove( id, index ) {
            const e = this;
            api.get("/registrar/meta/remove/" + id)
            .then(() => {
                e.subject.splice( index, 1 )
                e.openAlert("Subject has been removed!", "success");
            })
            .catch(() => {
                e.openAlert("Error when removing.", "error");
            });
        }
    },
}
</script>

<style scoped>
.padder { padding: 15px; }
</style>
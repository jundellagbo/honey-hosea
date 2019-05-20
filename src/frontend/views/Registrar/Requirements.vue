<template>
      <custom-layout>
          <template>
                <p style="padding: 0 15px;"><strong>Note:</strong> These requirements will be applied for all new students.</p>
                <v-layout>
                    <v-flex md4 class="padder">
                        <v-card style="padding: 15px;">
                            <v-form ref="requirementform">
                                <v-text-field
                                    v-model="inputs.metaname"
                                    :rules="rules"
                                    label="Requiment"
                                    solo
                                ></v-text-field>

                                <v-checkbox
                                v-model="inputs.metakey"
                                :label="`${inputs.metakey ? 'Cleared' : 'Pending'}`"
                                color="primary"
                                :true-value="1"
                                :false-value="0"
                                ></v-checkbox>

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
                            :items="reqlist"
                            :search="search"
                            :no-results-text="`Your search for (${search}) requirement found no results.`"
                            :no-data-text="loadMetas ? `Loading...` : `There is no requirement available.`"
                            :loading="loadMetas"
                            hide-actions
                            >
                            <template v-slot:items="props">
                                <tr>
                                    <td class="text-xs-left">
                                        {{ props.item.metaname }}
                                    </td>
                                    <td class="text-xs-left">
                                        <span v-if="props.item.metakey" class="cleared_">CLEARED</span>
                                        <span v-else class="pending_">PENDING</span>
                                    </td>

                                    <td class="text-xs-right">

                                        <v-tooltip bottom>
                                            <template v-slot:activator="{ on }">
                                                <v-btn icon v-on="on" color="primary" small dark @click="setEdit( props.item )">
                                                <v-icon size="14">edit</v-icon>
                                                </v-btn>
                                            </template>
                                            <span>Edit Requirement</span>
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
    metatype: "requirement",
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
            v => !!v || 'Requirement is required'
        ],
        search: "",
        th: [
            { text: "REQUIREMENTS", sortable: false, value: "metaname", align: "left" },
            { text: "STATUS", sortable: false, value: "metakey", align: "left" },
            { text: "OPTIONS", sortable: false, value: "options", align: "right" }
        ],
        metas: [],
        loadMetas: false,
        loadingSubmit: false
    } },
    computed: {
        reqlist() {
            return this.metas.map( function( row, key ) {
                row.id = parseInt(row.id);
                row.index = key;
                row.metakey = parseInt( row.metakey);
                return row;
            })
        }
    },
    mounted() {
        this.fetchMetas();
    },
    methods: {
        fetchMetas() {
            const e = this;
            e.loadMetas = true;
            api.get("/registrar/meta/requirement")
            .then(( response ) => {
                e.metas = response.data.metas;
            })
            .catch(( error ) => {
                e.openAlert( "Error when fetching requirement [" + error + "].", "error" );
            })
            .then(() => e.loadMetas = false)
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
                    e.metas.push( inputs );
                    e.openAlert("New Reuirement has been added.", "success");
                } else {
                    // update
                    e.$set(e.metas, inputs.index, inputs);
                    e.openAlert("Requirement has been saved!", "success");
                }
                e.resetForm();
            })
            .catch(( error ) => {
                e.openAlert("Error when submitting the form [" + error + "].", "error");
            })
            .then(() => e.loadingSubmit = false)
        },
        submit() {
            if( !this.$refs.requirementform.validate() ) {
                this.openAlert("Please check the error fields.", "error");
            } else {
                // submit
                const inputs = Object.assign({}, this.inputs);
                this.submitToApi( inputs );
            }
        },
        resetForm() {
            this.$refs.requirementform.resetValidation();
            this.$refs.requirementform.reset();
            this.inputs = resetInputs;
        },
        setEdit( data ) {
            this.inputs = Object.assign({}, data); 
            this.openAlert("Requirement [" + data.metaname + "] is ready to edit.", "info");
        },
        async remove( id, index ) {
            this.resetForm();
            let res = await this.$confirm('Are you sure you want to remove this requirement?', { title: "Confirm Delete", buttonTrueText: "Remove", buttonFalseText: "Cancel", persistent: true, icon: "warning", color: "warning" });
            if( res ) {
                // removing;
                this.confirmRemove( id, index );
            }
        },
        confirmRemove( id, index ) {
            const e = this;
            api.get("/registrar/meta/remove/" + id)
            .then(() => {
                e.metas.splice( index, 1 )
                e.openAlert("Requirement has been removed!", "success");
            })
            .catch(() => {
                e.openAlert("Error when removing requirement", "error");
            });
        }
    },
}
</script>

<style scoped>
.padder { padding: 15px; }
.cleared_ {
    color: #3949AB;
}
.pending_ {
    color: #7CB342;
}
</style>
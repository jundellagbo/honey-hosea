<template>
    <custom-layout>

        <v-layout row wrap justify-center align-center v-if="loadPayments">
            <v-progress-circular
            :size="50"
            color="primary"
            indeterminate
            style="margin: 50px 20px 20px 20px"
            ></v-progress-circular>
        </v-layout>
        
        <v-layout v-else>
            <v-flex md4 class="padder">
                <v-card>
                    <v-card-title>TUITION FEE</v-card-title>
                    <v-card-text>
                        <v-form ref="tuitionform">
                            <v-layout>
                                <v-flex md12>
                                    <v-text-field
                                    v-model="tuition.price"
                                    :rules="rules.tprice"
                                    label="Tuition Fee"
                                    solo
                                    type="number"
                                    single-line
                                    ></v-text-field>

                                    <v-text-field
                                    v-model="tuition.discount"
                                    label="Discount"
                                    solo
                                    type="number"
                                    append-icon="%"
                                    single-line
                                    ></v-text-field>

                                    <p>
                                        <strong>Note:</strong> If you set the discount, tuition fee must have a discount when they pay the full payment.
                                    </p>

                                    <v-btn :loading="loadingTuitionSubmit" color="primary" @click="saveTuition">
                                        Save Tuition
                                    </v-btn>

                                </v-flex>
                            </v-layout>
                        </v-form>
                    </v-card-text>
                </v-card>
            </v-flex>
            <v-flex md4 class="padder">
                <v-card>
                    <v-card-title>MISCELLANEOUS FEE</v-card-title>
                    <v-card-text>
                        <v-form ref="miscform">
                            <v-layout>
                                <v-flex md12>
                                    <v-text-field
                                    v-model="misc.price"
                                    :rules="rules.mprice"
                                    label="Miscellaneous Fee"
                                    solo
                                    type="number"
                                    single-line
                                    ></v-text-field>

                                    <v-text-field
                                    v-model="misc.discount"
                                    label="Discount"
                                    solo
                                    type="number"
                                    append-icon="%"
                                    single-line
                                    ></v-text-field>
                                    
                                    <v-dialog
                                        v-if="misc.discount > 0"
                                        ref="dialog"
                                        v-model="datepicker"
                                        :return-value.sync="date"
                                        persistent
                                        lazy
                                        full-width
                                        width="290px"
                                    >
                                        <template v-slot:activator="{ on }">
                                        <v-text-field
                                            v-model="misc.availability"
                                            :rules="rules.miscavailability"
                                            label="Discount End Date"
                                            readonly
                                            v-on="on"
                                            solo
                                        ></v-text-field>
                                        </template>
                                        <v-date-picker v-model="misc.availability" scrollable>
                                        <v-spacer></v-spacer>
                                        <v-btn flat color="primary" @click="$refs.dialog.save(misc.availability)">CLOSE</v-btn>
                                        </v-date-picker>
                                    </v-dialog>

                                    <p>
                                        <strong>Note:</strong> If you set the discount, miscellaneous fee must have a discount before the time of enrollment.
                                    </p>

                                    <v-btn :loading="loadingMiscSubmit" color="primary" @click="saveMisc">
                                        Save Miscellaneous
                                    </v-btn>

                                </v-flex>
                            </v-layout>
                        </v-form>
                    </v-card-text>
                </v-card>
            </v-flex>
            <v-flex md4 class="padder">
                <v-card>
                    <v-card-title>BOOKS PRICING</v-card-title>
                    <v-card-text>
                        <v-data-table
                        :headers="th"
                        :items="bookslists"
                        :no-data-text="`There is no books available.`"
                        hide-actions
                        >
                        <template v-slot:items="props">
                            <tr>
                                <td class="text-xs-left">
                                    {{ props.item.classname }}
                                </td>
                                <td class="text-xs-left">
                                    ₱ {{ props.item.bookprice }}
                                </td>
                                <td class="text-xs-right">

                                    <v-tooltip bottom>
                                        <template v-slot:activator="{ on }">
                                            <v-btn icon v-on="on" color="primary" @click="book = { open: true, data: Object.assign({}, props.item) }" small dark>
                                            <v-icon size="14">edit</v-icon>
                                            </v-btn>
                                        </template>
                                        <span>Change pricing</span>
                                    </v-tooltip>
                                    
                                </td>
                            </tr>
                        </template>
                        </v-data-table>
                    </v-card-text>
                </v-card>
            </v-flex>
        </v-layout>

        <message :alert="alert" @close="alert.open = false" />

        <modal :dialog="{
            title: book.data.classname + ' - Books Pricing',
            open: book.open,
            width: 400
        }">
            <v-form ref="bookpricingform">
                <v-text-field
                v-model="book.data.bookprice"
                :rules="rules.bookprice"
                label="Book Price"
                solo
                type="number"
                single-line
                ></v-text-field>

                <v-btn :loading="loadingBookPrice" @click="saveBookPrice" color="primary">
                    Save
                </v-btn>

                <v-btn flat @click="book.open = false" color="primary">
                    Cancel
                </v-btn>

            </v-form>
        </modal>
    </custom-layout>
</template>

<script>
import { api, postHeader } from "./../../../app"
import Layout from "./../../components/Layout.vue"
import Message from "./../../components/Message.vue"
import Modal from "./../../components/Modal.vue"
const inputs = {
    price: '',
    discount: '',
    availability: '',
    other: ''
};
const tuition = Object.assign({ id: 1 }, inputs);
const misc = Object.assign({ id: 2 }, inputs);
export default {
    components: {
        'custom-layout': Layout,
        'message': Message,
        'modal': Modal
    },
    data() { return {
        datepicker: false,
        date: new Date().toISOString().substr(0, 10),
        tuition,
        misc,
        rules: {
            tprice: [
                v => v > 0 || 'Tuition fee must not negative or zero'
            ],
            mprice: [
                v => v > 0 || 'Miscellaneous fee must not negative or zero'
            ],
            miscavailability: [
                v => !!v || 'Due Date is required'
            ],
            bookprice: [
                v => v > 0 || 'Book price must not negative or zero'
            ]
        },
        th: [
            { text: "BOOKS", sortable: false, align: "left" },
            { text: "PRICE", sortable: false, align: "left" },
            { text: "OPTIONS", sortable: false, align: "right" }
        ],
        loadPayments: false,
        alert: {
            open: false,
            text: "",
            type: "",
            key: 0
        },
        books: [],
        loadingTuitionSubmit: false,
        loadingMiscSubmit: false,
        book: {
            open: false,
            data: {}
        },
        loadingBookPrice: false
    } },
    mounted() {
        this.fetchPayments();
    },
    computed: {
        bookslists() {
            return this.books.map( function( row, index ) {
                row.index = index;
                return row;
            })
        }
    },
    methods: {
        fetchPayments() {
            const e = this;
            e.loadPayments = true;
            api.get("/registrar/pricing")
            .then(( response ) => {
                e.misc = response.data.misc[0];
                e.tuition = response.data.tuition[0];
                e.books = response.data.books;
            })
            .catch(( error ) => e.openAlert({ message: 'Error when loading payments [' + error + '].', type: 'error' }))
            .then(() => e.loadPayments = false)
        },
        openAlert( param ) {
            this.alert = {
                key: Date.now(),
                text: param.message,
                type: param.type,
                open: true
            }
        },
        savePayments( inputs, callback ) {
            const e = this;
            api.post("/registrar/pricing/store", inputs, postHeader)
            .then(() => {
                e.openAlert({ message: 'Payments Setup has been saved.', type: 'success' })
            })
            .catch(( error ) => e.openAlert({ message: 'Error when submitting the form [' + error + '].', type: 'error' }))
            .then(() => callback())
        },
        saveTuition() {
            if( this.$refs.tuitionform.validate() ) {
                const e = this;
                const inputs = Object.assign({}, e.tuition);
                e.loadingTuitionSubmit = true;
                e.savePayments( inputs, function() {
                    e.loadingTuitionSubmit = false;
                });
            } else {
                this.openAlert({ message: 'Please check the error fields.', type: 'error' })
            }
        },
        saveMisc() {
            if( this.$refs.miscform.validate() ) {
                const e = this;
                const inputs = Object.assign({}, e.misc);
                e.loadingMiscSubmit = true;
                e.savePayments( inputs, function() {
                    e.loadingMiscSubmit = false;
                });
            } else {
                this.openAlert({ message: 'Please check the error fields.', type: 'error' })
            }
        },
        saveBookPrice() {
            if( this.$refs.bookpricingform.validate() ) {
                const e = this;
                const inputs = Object.assign({}, e.book.data);
                e.loadingBookPrice = true;
                api.post("/registrar/book/pricing", inputs, postHeader)
                .then(() => {
                    e.$set( e.books, inputs.index, inputs);
                    e.openAlert({ message: 'Book pricing has been saved.', type: 'success' });
                    e.book.open = false;
                })
                .catch(( error ) => e.openAlert({ message: 'Error when submitting the form [' + error + '].', type: 'error' }))
                .then(() => e.loadingBookPrice = false)
            } else {
                this.openAlert({ message: 'Please check the error fields.', type: 'error' })
            }
        }
    },
}
</script>

<style scoped>
    .padder {
        padding: 15px;
    }
</style>
<template>
    <custom-layout>
        <div v-if="notfound">
            <notfound />
        </div>
        <div v-else>
            <v-breadcrumbs :items="breadcrumbs" divider=">"></v-breadcrumbs>
            <v-layout row wrap justify-center align-center v-if="loadStatement">
                <v-progress-circular
                :size="50"
                color="primary"
                indeterminate
                style="margin: 50px 20px 20px 20px"
                ></v-progress-circular>
            </v-layout>
            <v-layout v-else>
                <v-flex md3 class="padder">
                    <v-card class="padder">
                        <h2>Students Information</h2>
                        <p><strong>Name:</strong> {{ studentStatement.fname }} {{ studentStatement.mname }} {{ studentStatement.lname }} {{ studentStatement.extension }}</p>
                        <p><strong>Age:</strong> {{ studentStatement.age }}</p>
                        <p><strong>Birthdate:</strong> {{ studentStatement.dateofbirth | moment("MMMM D, YYYY") }}</p>
                        <p><strong>Gender:</strong> {{ studentStatement.sex }}</p>
                        <h3 style="margin-bottom: 10px;">Other Information:</h3>
                        <p><strong>Street:</strong> {{ studentStatement.street }}</p>
                        <p><strong>Barangay:</strong> {{ studentStatement.barangay }}</p>
                        <p><strong>City:</strong> {{ studentStatement.city }}</p>
                        <p><strong>Zip:</strong> {{ studentStatement.zip }}</p>
                        <p><strong>Tel #:</strong> {{ studentStatement.tel }}</p>
                        <p><strong>Cel #:</strong> {{ studentStatement.cel }}</p>
                        <h3 style="margin-bottom: 10px;">Guardian Information:</h3>
                        <p><strong>Guardian:</strong> {{ studentStatement.gname }} {{ studentStatement.gmname }} {{ studentStatement.glname }}</p>
                        <v-btn :loading="loadingEnroll" outline block v-if="studentStatement.enrollment_status == 0" @click="markEnrolled" color="primary">
                            Mark as Enrolled
                        </v-btn>
                        <v-btn outline block v-else color="success">
                            Enrolled
                        </v-btn>
                    </v-card>
                </v-flex>

                <v-flex class="padder">
                    <v-card class="padder">
                        <v-card-title>
                        <h2>Payments | <span class="paid" v-if="paymentsTotal == 0">PAID</span><span v-else>Total Balance: ₱ {{ paymentsTotal }}</span></h2>
                        <v-spacer></v-spacer>
                        <v-btn small color="primary" @click="pay.open = true">Pay</v-btn>
                        <v-btn small color="warning" @click="newpayment.open = true">New Payment</v-btn>
                        </v-card-title>
                        <v-data-table
                        :headers="th"
                        :items="paymentslists"
                        no-data-text="There is no payments available."
                        hide-actions
                        >
                            <template v-slot:items="props">
                                <tr>
                                    <td class="text-xs-left">
                                        {{ props.item.payment }}
                                    </td>
                                    <td class="text-xs-right">
                                        <span v-if="props.item.balance != 0">
                                            <div style="display: inline;" v-if="props.item.payment == 'TUITION FEE'">
                                                <v-btn icon @click="paymentinfo = { open: true, payment: Object.assign({}, props.item) }" flat color="info">
                                                    <v-icon>info</v-icon>
                                                </v-btn>
                                            </div>
                                            <div style="display: inline;" v-else>
                                                <v-btn icon v-if="props.item.discount != 0" @click="paymentinfo = { open: true, payment: Object.assign({}, props.item) }" flat color="info">
                                                    <v-icon>info</v-icon>
                                                </v-btn>
                                            </div>
                                            ₱ {{ props.item.balance }}
                                        </span>
                                        <span class="paid" v-else>Paid</span>
                                    </td>
                                </tr>
                            </template>
                        </v-data-table>

                    </v-card>


                    <v-card class="padder" style="margin-top: 20px;">
                        <v-card-title>
                        <h2>Transactions</h2>
                        <v-spacer />
                        <v-text-field
                            v-model="searchtransac"
                            append-icon="search"
                            label="Search Transaction"
                            solo
                            style="width: 20px;"
                        ></v-text-field>
                        </v-card-title>

                        <v-data-table
                        :headers="thtransac"
                        :items="transactions"
                        :search="searchtransac"
                        :no-results-text="`Your search for (${searchtransac}) transaction found no results.`"
                        :no-data-text="`There is no transaction available.`"
                        >
                            <template v-slot:items="props">
                                <tr>
                                    <td class="text-xs-left">
                                        {{ props.item.transac_code }}
                                    </td>
                                    <td class="text-xs-left">
                                        {{ props.item.date_transac | moment("MMMM D, YYYY | hh:mm a") }}
                                    </td>
                                    <td class="text-xs-left">
                                        {{ props.item.payer }}
                                    </td>
                                    <td class="text-xs-left">
                                        {{ props.item.payor }}
                                    </td>
                                    <td class="text-xs-right">
                                        <v-btn @click="showTransac = { open: true, transac: Object.assign({}, props.item) }" color="primary" small>
                                            View Transaction
                                        </v-btn>
                                    </td>
                                </tr>
                            </template>
                        </v-data-table>

                    </v-card>

                </v-flex>
            </v-layout>
        </div>
        <message :alert="alert" @close="alert.open = false" />

        <modal :dialog="{
            title: paymentinfo.payment.payment,
            open: paymentinfo.open,
            width: 400
        }">
            <div v-if="paymentinfo.payment.payment == 'TUITION FEE'">
            <!-- Tuition Fee -->
            <p>Quarterly: ₱ {{ paymentinfo.payment.value }} per quarter ( ₱ {{ (paymentinfo.payment.value * 4) }} ) total ammount.</p>
            <p v-if="paymentinfo.payment.discount != 0">Full Payment: ₱ {{ calcPrice( ( paymentinfo.payment.value * 4 ), paymentinfo.payment.discount ) }} ( deducted {{ paymentinfo.payment.discount }}% discount )</p>
            <p>Current Balance: ₱ {{ paymentinfo.payment.balance }}</p>
            <p style="color: orange;" v-if="paymentinfo.payment.balance != (paymentinfo.payment.value * 4)">Discount is not available, you already pay for quarterly.</p>
            </div>
            <div v-else>
            <!-- Misc Fee -->
            <p>Payment: ₱ {{ paymentinfo.payment.value }}</p>
            <div v-if="paymentinfo.payment.discount != 0">
            <p >Discounted: ₱ {{ calcPrice( paymentinfo.payment.value, paymentinfo.payment.discount ) }}</p>
            <p>deducted {{ paymentinfo.payment.discount }}% discount if pays on or before {{ paymentinfo.payment.availability | moment("MMMM D, YYYY") }}</p>
            <p>Current Balance: ₱ {{ paymentinfo.payment.balance }}</p>
            </div>
            </div>
            <v-btn style="margin-top: 15px;" flat color="primary" @click="paymentinfo.open = false">
                Close
            </v-btn>
        </modal>

        <modal :dialog="{width: 800, open: pay.open, title: 'Pay for Account'}">
            <v-form ref="payform">
                
                <v-layout v-for="(row, index) in paymentInputs" :key="index">

                    <v-select
                    v-model="row.payment"
                    :items="paymentsDropdown"
                    :rules="row.rule.payment"
                    item-value="payment"
                    label="Select Payment"
                    persistent-hint
                    item-text="payment"
                    class="inputpadding"
                    solo
                    return-object
                    single-line
                    ></v-select>

                    <v-text-field
                    v-model="row.ammount"
                    :rules="row.rule.ammount"
                    label="Ammount"
                    solo
                    type="number"
                    class="inputpadding"
                    single-line
                    :hint="row.hint"
                    persistent-hint                   
                    ></v-text-field>

                    <v-btn v-if="index > 0" fab small @click="pay.payInputs.splice(index, 1)" color="error">
                        <v-icon>remove</v-icon>
                    </v-btn>

                </v-layout>

                <v-layout>
                    <v-text-field
                    v-model="pay.receiver"
                    :rules="reciverValidate"
                    label="Payer"
                    solo
                    class="inputpadding"
                    single-line
                    ></v-text-field>
                </v-layout>

                <v-btn :loading="payloading" style="margin-top: 15px;" color="primary" @click="savePayment">
                    Save Payment
                </v-btn>

                <v-btn style="margin-top: 15px;" flat color="primary" @click="addPaymentField">
                    Add to Pay
                </v-btn>

                <v-btn style="margin-top: 15px;" flat color="primary" @click="closeForm">
                    Cancel
                </v-btn>

            </v-form>

        </modal>

        <modal :dialog="{width: 800, open: showTransac.open, title: 'Transactions' }">

            <div id="printMe" style="font-family: arial;">
            <h3 class="primary--text" style="margin-bottom: 15px;">HONEY HOSEA ACADEMY INC.</h3>
            <h3 style="margin-bottom: 15px;">TRANSACTION #: {{ showTransac.transac.transac_code }}</h3>
            <p>Date Transac: {{ showTransac.transac.date_transac | moment("MMMM D, YYYY | hh:mm a")  }}</p>
            <v-divider></v-divider>
            <v-list>
                <v-list-tile style="margin: 10px 0;" @click="disableClick" v-for="(row, index) in transactionView.transacObj" :key="index">
                    <v-list-tile-content>
                         <v-list-tile-title>{{ row.payment }}</v-list-tile-title>
                          <v-list-tile-sub-title>
                            ₱ {{ row.finalAmmount }}
                            <strong v-if="row.payment == 'TUITION FEE'">
                            <span v-if="row.discount != 0 && (row.ammount == (row.value * 4))">( discounted {{row.discount}}% for full payment )</span>
                            </strong>
                            <strong v-if="row.payment == 'MISCELLANEOUS FEE'">
                            <span v-if="row.discount != 0 && (row.ammount == row.value) && (beforeEnrollment(payments[row.paymentIndex].availability) >= 0)">( discounted {{row.discount}}%, full payment on or before {{ payments[row.paymentIndex].availability | moment("MMMM D, YYYY") }} )</span>
                            </strong>
                           </v-list-tile-sub-title>
                    </v-list-tile-content>
                    <v-list-tile-action>
                        <v-icon>check</v-icon>
                    </v-list-tile-action>
                </v-list-tile>
            </v-list>
            <v-divider></v-divider>
            <h3 style="margin-top: 15px; margin-bottom: 15px;">Total: ₱ {{ showTransac.transac.total }}</h3>
            <p>Payer: {{ showTransac.transac.payer }}</p>
            <p>Accountant: {{ showTransac.transac.payor }}</p>
            </div>

            <v-btn style="margin-top: 15px;" flat color="primary" @click="showTransac.open = false">
                Close
            </v-btn>

            <v-btn icon color="primary" flat v-print="'#printMe'">
                <v-icon>print</v-icon>
            </v-btn>
        </modal>


        <modal :dialog="{ width: 400, open: newpayment.open, title: 'New Payment' }">
            <v-form ref="newpaymentform">
                <v-text-field
                v-model="newpayment.payment.payment"
                :rules="newpayment.rules.payment"
                label="Payment"
                solo
                class="inputpadding"
                single-line
                ></v-text-field>

                <v-text-field
                v-model="newpayment.payment.value"
                :rules="newpayment.rules.ammount"
                label="Ammount"
                solo
                class="inputpadding"
                single-line
                type="number"
                ></v-text-field>

                <v-btn :loading="newpayment.loading" style="margin-top: 15px;" color="primary" @click="saveNewPayment">
                    Save
                </v-btn>

                <v-btn style="margin-top: 15px;" flat color="primary" @click="resetnewPayment">
                    Close
                </v-btn>
            </v-form>
        </modal>

    </custom-layout>
</template>

<script>
import Vue from "vue";
import { api, user, postHeader } from "./../../../app"
import Layout from "./../../components/Layout.vue"
import Message from "./../../components/Message.vue"
import Modal from "./../../components/Modal.vue"
import NotFound from "./../errors/NotFound.vue"
export default {
    components: {
        'custom-layout': Layout,
        'message': Message,
        'modal': Modal,
        'notfound': NotFound
    },
    data() { return { 
        newpayment: {
            open: false,
            payment: {},
            rules: {
                payment: [
                    v => !!v || 'Payment is required',
                ],
                ammount: [
                    v => !!v || 'Ammount is required',
                    v => v > 0 || 'Please check the ammount'
                ]
            },
            loading: false
        },
        notfound: false,
        enrollmentId: this.$router.history.current.params.id,
        loadStatement: false,
        alert: {
            open: false,
            text: "",
            type: "",
            key: 0
        },
        related: [],
        payments: [],
        statement: {},
        th: [
            { text: "PAYMENTS", sortable: false, align: "left" },
            { text: "BALANCE", sortable: false, align: "right" },
        ],
        thtransac: [
            { text: "TRANSACTION #", sortable: true, value: "transac_code", align: "left" },
            { text: "TRANSAC DATE", sortable: true, value: "date_transac", align: "left" },
            { text: "PAYER", sortable: false, align: "left" },
            { text: "PAYOR", sortable: false, align: "left" },
            { text: "", sortable: false, align: "left" }
        ],
        searchtransac: "",
        transactions: [],
        paymentinfo: {
            open: false,
            payment: {}
        },
        loadingEnroll: false,
        breadcrumbs: [
            {
                text: 'Accounts',
                disabled: false,
                to: '/accountant/accounts'
            },
            {
                text: 'Statement of Account',
                disabled: true
            }
        ],
        pay: {
            open: false,
            payInputs: [
                { payment: null, ammount: "" }
            ],
            receiver: ""
        },
        reciverValidate: [
            v => !!v || 'Receiver is required',
        ],
        payloading: false,
        showTransac: {
            open: false,
            transac: {}
        }
    } },
    computed: {
        paymentsTotal() {
            let total = 0;
            this.payments.map( function( row ) {
                total += parseFloat( row.balance );
            });
            return isNaN( total ) || total == 0 ? 0 : total;
        },
        transactionView() {
            const transaction = this.showTransac.transac;
            if( this.showTransac.open ) {
                transaction.transacObj = JSON.parse( transaction.transactions );
                let total = 0;
                transaction.transacObj.map(row => {
                    total += parseFloat(row.finalAmmount)
                });
                transaction.total = total
            }
            return transaction;
        },
        studentStatement() {
            let student = this.statement;
            student.age = this.calculateAge(student.dateofbirth);
            return student;
        },
        paymentslists() {
            return this.payments.map( function(row,index) {
                row.index = index;
                return row;
            });
        },
        transacPayments() {
            const e = this;
            let transacs = [];
            e.pay.payInputs.map( function( row ) {
                transacs.push({
                    paymentIndex: row.payment.index,
                    ammount: row.ammount,
                    discount: row.payment.discount,
                    payment: row.payment.payment,
                    balance: e.payments[ row.payment.index ].balance - row.ammount,
                    id: e.payments[ row.payment.index ].id,
                    finalAmmount: row.finalAmmount,
                    value: row.payment.value
                });
            });
            return transacs;
        },
        getUser() {
            return user.fname + " " + user.mname + " " + user.lname;
        },
        paymentInputs() {
            const e = this;
            return e.pay.payInputs.map( function( row, index ) {
                row.index = index;
                row.rule = {
                    payment: [
                        v => !!v || 'Payment is required',
                        v => e.isExists(v) || 'Payment already selected'
                    ],
                    ammount: [
                        v => !!v || 'Ammount is required',
                        v => v > 0 || 'Please check your ammount.',
                        row.payment != null || 'Please select payment.',
                        v => ((row.payment != null) && (parseInt(row.payment.balance) >= parseInt(v))) || 'Your ammount exceed to your balance.'
                    ]
                };
                
                row.finalAmmount = row.ammount;
                row.hint = "";
                if( row.payment != null && row.payment.payment == "TUITION FEE" ) {
                    if( row.ammount == ( row.payment.value * 4 ) && row.payment.discount != 0 ) {
                        row.finalAmmount = e.calcPrice( row.ammount, row.payment.discount );
                        row.hint = "Fully paid ammount will be discounted by " + row.payment.discount + "% - ( ₱ " + row.finalAmmount + ")";
                    }
                }

                if( row.payment != null && row.payment.payment == "MISCELLANEOUS FEE" ) {
                    if( ( row.ammount == row.payment.value ) && row.payment.discount != 0 ) {
                        let discount_interval = e.beforeEnrollment( row.payment.availability );
                        if( discount_interval >= 0 ) {
                            row.finalAmmount = e.calcPrice( row.ammount, row.payment.discount );
                            row.hint = "Paying full ammount on or before " + Vue.moment(row.payment.availability).format("MMMM D, YYYY") + " will be discounted by " +  row.payment.discount + "% - ( ₱ - " + row.finalAmmount + ")";
                        }
                    }
                }

                return row;
            })
        },
        paymentsDropdown() {
            const e = this;
            let newData = [];
            e.payments.map( function(row) {
                if( row.balance != 0 ) {
                    newData.push( row );
                }
            });
            return newData;
        }
    },
    mounted() {
        this.fetchStatement();
    },
    methods: {
        beforeEnrollment( date ) {
            return Vue.moment(date).diff( Vue.moment(new Date()), 'days');
        },
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
        fetchStatement() {
            const e = this;
            e.loadStatement = true;
            api.get("/registrar/statement/" + e.enrollmentId)
            .then(( response ) => {
                if( response.data.not_found || typeof( response.data.not_found ) != 'undefined' ) {
                    e.notfound = true;
                } else {
                    e.notfound = false;
                    e.payments = response.data.payments;
                    e.related = response.data.related;
                    e.statement = response.data.statement;
                    e.transactions = response.data.transactions;
                }
            })
            .catch(( error ) => e.openAlert({ message: 'Error when retrieve statement of account [' + error + '].', type: 'error'}))
            .then(() => e.loadStatement = false)
        },
        openAlert( param ) {
            this.alert = {
                key: Date.now(),
                text: param.message,
                type: param.type,
                open: true
            }
        },
        calcPrice( price, discount ) {
            return price - ( price * ( discount / 100 ) );
        },
        async markEnrolled() {
            let res = await this.$confirm('Are you sure you want to enroll this student?', { title: "Enrollment Confirm", buttonTrueText: "Confirm", buttonFalseText: "Cancel", persistent: true, icon: "info", color: "info" });
            if( res ) {
                const e = this;
                e.loadingEnroll = true;
                api.get("/registrar/enrolled/" + e.enrollmentId)
                .then(() => {
                    e.statement.enrollment_status = 1;
                    e.openAlert({ message: 'Success! Marked as Enrolled.', type: 'success'});
                })
                .catch(( error ) => e.openAlert({ message: 'Error when setting as enrolled [' + error + '].', type: 'error'}))
                .then(() => e.loadingEnroll = false)
            }
        },
        addPaymentField() {
            if( this.pay.payInputs.length < this.paymentsDropdown.length ) {
                this.pay.payInputs.push({ payment: null, ammount: "" });
            } else {
                this.openAlert({ message: 'There are ' + this.paymentsDropdown.length + ' available payments only.', type: 'error'})
            }
        },
        isExists( val ) {
            if( val != null ) {
                return this.paymentInputs.filter(m => (m.payment != null) && (parseInt(m.payment.id) === parseInt(val.id)) ).length > 1 ? false : true;
            }
        },
        resetForm() {
            this.$refs.payform.reset();
            this.$refs.payform.resetValidation();
            this.pay = {
                open: false,
                payInputs: [
                    { payment: null, ammount: "" }
                ],
                receiver: ""
            };
        },
        closeForm() {
            this.resetForm();
        },
        savePayment() {
            if( !this.$refs.payform.validate() ) {
                this.openAlert({ message: 'Please check the error fields.', type: 'error'})
            } else {
                this.submitApiPayment();
            }
        },
        submitApiPayment() {
            const e = this;
            const transacPayments = e.transacPayments.slice(0);
            const data = Object.assign({ eid: e.enrollmentId, transactions: JSON.stringify( transacPayments ), payer: e.pay.receiver, payor: e.getUser })
            e.payloading = true;
            api.post("/registrar/transac", data, postHeader)
            .then(( response ) => {
                transacPayments.map(function(row) {
                    const updatedRow = Object.assign({}, e.payments[row.paymentIndex]);
                    updatedRow.balance = row.balance;
                    e.$set(e.payments, row.paymentIndex, updatedRow);
                });
                e.transactions.push( response.data.response );
                e.closeForm();
                e.openAlert({ message: 'Payments has been saved.', type: 'success'});
                e.showTransac = Object.assign({ open: true, transac: response.data.response });
            })
            .catch(( error ) => e.openAlert({ message: 'Payment submission failed [' + error + '].', type: 'error'}))
            .then(() => e.payloading = false)
        },
        disableClick() { return false; },
        resetnewPayment() {
            this.$refs.newpaymentform.reset();
            this.$refs.newpaymentform.resetValidation();
            this.newpayment.open = false;
            this.newpayment.payment = {};
        },
        saveNewPayment() {
            const e = this;
            if( !e.$refs.newpaymentform.validate() ) {
                e.openAlert({ message: 'Please check the error fields.', type: 'error'})
            } else {
                const input = Object.assign({}, e.newpayment.payment)
                e.newpayment.loading = true;
                input.eid = e.enrollmentId;
                api.post("/registrar/newpayment", input, postHeader)
                .then(( response ) => {
                    e.payments.push( response.data.response );
                    e.openAlert({ message: 'New Payment has been added.', type: 'success'});
                    e.resetnewPayment();
                })
                .catch(( error ) => e.openAlert({ message: 'New Payment submission failed [' + error + '].', type: 'error'}))
                .then(() => e.newpayment.loading = false)
            }
        }
    },
}
</script>

<style scoped>
.padder {
    padding: 15px;
}
.inputpadding {
    padding: 0 5px;
}
.paid { color: green; }
</style>
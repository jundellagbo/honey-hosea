<template>
    <v-form ref="studentsform">
    <p>Please enter the fields below.</p>
      <v-layout>

          <v-stepper v-model="step" style="width: 100%;" light>
              <v-stepper-header>
                <v-stepper-step :complete="step > 1" step="1">Students Details</v-stepper-step>
                <v-divider></v-divider>
                <v-stepper-step :complete="step > 2" step="2">Additional Information</v-stepper-step>
                <v-divider></v-divider>
                <v-stepper-step :complete="step > 3" step="3">Guardian Information</v-stepper-step>
            </v-stepper-header>

            <v-stepper-items>
                <v-stepper-content step="1">

                    <v-layout wrap justify-space-between>
                        
                        <v-flex
                        md6
                        xs12
                        >
                        <v-text-field
                            v-model="inputs.fname"
                            :rules="rule.fname"
                            label="Firstname"
                            solo
                            class="padder"
                        ></v-text-field>
                        </v-flex>
                        
                        <v-flex
                        md6
                        xs12
                        >
                        <v-text-field
                            v-model="inputs.mname"
                            :rules="rule.mname"
                            label="Middlename"
                            solo
                            class="padder"
                        ></v-text-field>
                        </v-flex>

                        <v-flex
                        md6
                        xs12
                        >
                        <v-text-field
                            v-model="inputs.lname"
                            :rules="rule.lname"
                            label="Lastname"
                            solo
                            class="padder"
                        ></v-text-field>
                        </v-flex>


                        <v-flex
                        md6
                        xs12
                        >
                        <v-text-field
                            v-model="inputs.extension"
                            :rules="rule.extension"
                            label="Extension"
                            solo
                            class="padder"
                        ></v-text-field>
                        </v-flex>

                        <v-flex
                        md6
                        xs12
                        >
                        <v-dialog
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
                                v-model="inputs.dateofbirth"
                                :rules="rule.dateofbirth"
                                label="Birthdate"
                                readonly
                                v-on="on"
                                solo
                                class="padder"
                            ></v-text-field>
                            </template>
                            <v-date-picker v-model="inputs.dateofbirth" scrollable>
                            <v-spacer></v-spacer>
                            <v-btn flat color="primary" @click="$refs.dialog.save(inputs.dateofbirth)">CLOSE</v-btn>
                            </v-date-picker>
                        </v-dialog>
                        </v-flex>

                        <v-flex
                        md6
                        xs12
                        >
                        <v-select
                        :items="genders"
                        label="Gender"
                        v-model="inputs.sex"
                        :rules="rule.sex"
                        solo
                        class="padder"
                        >
                        
                        </v-select>
                        </v-flex>

                        <v-flex
                        md12
                        v-if="inputs.id != 0"
                        >
                        <v-checkbox
                        v-model="inputs.status"
                        :label="`Student is ${inputs.status ? 'active' : 'not active'}`"
                        class="padder"
                        color="primary"
                        :true-value="1"
                        :false-value="0"
                        ></v-checkbox>
                        </v-flex>

                        
                    </v-layout>
                    
                    <v-btn 
                    color="primary"
                    @click="step = 2"
                    >Continue</v-btn>

                </v-stepper-content>
            </v-stepper-items>

            <v-stepper-content step="2">
                
                <v-layout wrap justify-space-between>
                        
                        <v-flex
                        md12
                        >
                        <v-text-field
                            v-model="inputs.street"
                            :rules="rule.street"
                            label="Street Name"
                            solo
                            class="padder"
                        ></v-text-field>
                        </v-flex>

                        <v-flex
                        md4
                        xs12
                        >
                        <v-text-field
                            v-model="inputs.barangay"
                            :rules="rule.barangay"
                            label="Barangay"
                            solo
                            class="padder"
                        ></v-text-field>
                        </v-flex>

                        <v-flex
                        md4
                        xs12
                        >
                        <v-text-field
                            v-model="inputs.city"
                            :rules="rule.city"
                            label="City"
                            solo
                            class="padder"
                        ></v-text-field>
                        </v-flex>

                        <v-flex
                        md4
                        xs12
                        >
                        <v-text-field
                            v-model="inputs.zip"
                            :rules="rule.zip"
                            label="Zip"
                            solo
                            class="padder"
                        ></v-text-field>
                        </v-flex>

                        <v-flex
                        md6
                        xs12
                        >
                        <v-text-field
                            v-model="inputs.tel"
                            :rules="rule.tel"
                            label="Telephone #"
                            solo
                            class="padder"
                        ></v-text-field>
                        </v-flex>

                        <v-flex
                        md6
                        xs12
                        >
                        <v-text-field
                            v-model="inputs.cel"
                            :rules="rule.cel"
                            label="Celphone #"
                            solo
                            class="padder"
                        ></v-text-field>
                        </v-flex>

                </v-layout>

                <v-btn 
                color="primary"
                @click="step = 3"
                >Continue</v-btn>

                <v-btn 
                flat
                @click="step = 1"
                color="primary"
                >Back</v-btn>
                
            </v-stepper-content>

            <v-stepper-content step="3">
                
                <v-layout wrap justify-space-between>
                    <v-flex
                    md12
                    >
                    <v-text-field
                        v-model="inputs.gname"
                        :rules="rule.gname"
                        label="Guardian Name"
                        solo
                        class="padder"
                    ></v-text-field>
                    </v-flex>

                    <v-flex
                    md12
                    >
                    <v-text-field
                        v-model="inputs.gmname"
                        :rules="rule.gmname"
                        label="Guardian Middle Name"
                        solo
                        class="padder"
                    ></v-text-field>
                    </v-flex>

                    <v-flex
                    md12
                    >
                    <v-text-field
                        v-model="inputs.glname"
                        :rules="rule.glname"
                        label="Guardian Lastname Name"
                        solo
                        class="padder"
                    ></v-text-field>
                    </v-flex>
                </v-layout>

                <v-btn 
                color="info"
                @click="submitForm"
                :loading="loading"
                >
                <span v-if="inputs.id == 0">Add Student</span>
                <span v-else>Save Student</span>
                </v-btn>

                <v-btn 
                flat
                @click="step = 2"
                color="primary"
                >Back</v-btn>

            </v-stepper-content>

          </v-stepper>

      </v-layout>

      <v-btn 
        flat
        @click="resetForm"
        color="primary"
        style="margin-top: 15px; float: right;"
        >Close</v-btn>

  </v-form>
</template>
<script>
import { api, postHeader } from "./../../../../../app";
import { inputs, validation, resetInputs } from "./js/StudentsForm"
export default {
    data() {
        return {
            datepicker: false,
            date: new Date().toISOString().substr(0, 10),
            genders: ["Male", "Female"],
            step: 1,
            inputs,
            resetInputs,
            rule: validation,
            loading: false
        }
    },
    props: ['editInputs'],
    watch: {
        editInputs( newVal ) {
            if( newVal ) {
                this.inputs = newVal
            }
        }
    },
    methods: {
        submitToApi() {
            const e = this;
            e.loading = true;
            api.post("/registrar/student", e.inputs, postHeader)
            .then(( response ) => {
                const output = Object.assign({}, e.inputs);
                output.extension = output.extension ? output.extension : "";
                if( output.id == 0 ) {
                    output.id = response.data.response.id;
                    output.studentsid = response.data.response.studentsid;
                    e.$emit('store', { output, type: "insert" })
                } else { 
                    e.$emit('store', { output, type: "update" })
                }
                 e.resetForm()
            })
            .catch(( error ) => {
                e.$emit('alert', { message: "Error when submitting the form, please try again. [" + error + "]", type: "error" });
            })
            .then(() => e.loading = false)
        },
        submitForm() {
            if( !this.$refs.studentsform.validate() ) {
                this.resetStep();
                this.$emit('alert', { message: "Please check the error fields", type: "error" });
            } else {
                this.submitToApi();
            }
        },
        resetForm() {
            this.resetStep();
            this.$refs.studentsform.resetValidation();
            this.$refs.studentsform.reset();
            this.inputs = this.resetInputs;
            this.$emit('closeStudentsForm');
        },
        resetStep() {
            const e = this;
            setTimeout( function() { e.step = 1; }, 500)
        },
        openInfo( param ) {
            this.$emit('alert', param);
        }
    }
}
</script>

<style lang="scss" scoped>
.padder { padding: 0 10px; }
</style>

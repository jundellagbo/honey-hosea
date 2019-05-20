export const inputs = {
    id: 0,
    fname: '',
    mname: '',
    lname: '',
    extension: '',
    dateofbirth: '',
    sex: '',
    street: '',
    barangay: '',
    city: '',
    zip: '',
    tel: '',
    cel: '',
    gname: '',
    gmname: '',
    glname: '',
    status: 1,
    studentsid: 0,
    fullname: '',
};

export const resetInputs = Object.assign({}, inputs);

export const validation = {
    fname: [
        v => !!v || 'Name is required'
    ],
    mname: [
        v => !!v || 'Middlename is required'
    ],
    lname: [
        v => !!v || 'Lastname is required'
    ],
    dateofbirth: [
        v => !!v || 'Birthdate is required'
    ],
    sex: [
        v => !!v || 'Gender is required'
    ],
    street: [
        v => !!v || 'Street name is required'
    ],
    barangay: [
        v => !!v || 'Barangay is required'
    ],
    city: [
        v => !!v || 'City is required'
    ],
    zip: [
        v => !!v || 'Zip Code is required'
    ],
    gname: [
        v => !!v || 'Guardian name is required'
    ],
    gmname: [
        v => !!v || 'Guardian middlename is required'
    ],
    glname: [
        v => !!v || 'Guardian lastname is required'
    ],
    tel: [
        v => !!v || 'Telephone # is required'
    ],
    cel: [
        v => !!v || 'Celphone # is required'
    ]
};
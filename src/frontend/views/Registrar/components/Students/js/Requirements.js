export const inputs = {
    requirement: "",
    status: "",
    id: 0,
    studentsid: 0
}

export const resetInputs = Object.assign({}, inputs)

export const validations = {
    requirement: [
        v => !!v || 'Requirement is required'
    ]
}
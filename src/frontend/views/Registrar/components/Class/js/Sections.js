export const inputs = {
    id: 0,
    sectionname: "",
    index: 0,
    classid: 0,
    average: ""
};

export const resetInputs = Object.assign({}, inputs);

export const scheduleProps = Object.assign({open: false}, inputs);

export const validations = {
    sectionname: [
        v => !!v || 'Section name is required'
    ],
    average: [
        v => !!v || 'Average is required',
        v => v >= 75 || 'Average must be 75 or up, based on passing average.'
    ]
};
import * as yup from 'yup';

let schemaData = yup.object().shape({
    phone: yup.number().required().positive().integer().min(1000000000).max(9999999999999),
})

let schemaDataPln = yup.object().shape({
    pln: yup.number().required().positive().integer().min(10000000).max(9999999999999),
})

export const number_format = (x) => {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

export const spacing4Char = (x) => {
    const joy = x?.match(/.{1,4}/g);
    return joy?.join(' ')
}

const operator = {
    tri: {
        name: 'Tri',
        img: 'tri',
    },
    telkomsel: {
        name: 'Telkomsel',
        img: 'telkomsel',
    },
    im3: {
        name: 'Im3',
        img: 'im3',
    },
    xl: {
        name: 'XL',
        img: 'xl',
    },
    axis: {
        name: 'Axis',
        img: 'axis',
    },
    smartfren: {
        name: 'Smartfren',
        img: 'smartfren',
    },
}

export const checkOperatorByNumber = async (val, setPhoneNumber, setImgOperator, setIsValid) => {
    setPhoneNumber(val)
    const _val = val.substring(0, 4)
    if (
        _val === '0811' ||
        _val === '0812' ||
        _val === '0813' ||
        _val === '0821' ||
        _val === '0822' ||
        _val === '0823' ||
        _val === '0852' ||
        _val === '0853'
    ) {
        setImgOperator(operator?.telkomsel?.img)
    } else if (
        _val === '0814' ||
        _val === '0815' ||
        _val === '0816' ||
        _val === '0855' ||
        _val === '0856' ||
        _val === '0857' ||
        _val === '0858'
    ) {
        setImgOperator(operator?.im3?.img)
    } else if (
        _val === '0895' ||
        _val === '0896' ||
        _val === '0897' ||
        _val === '0898' ||
        _val === '0899'
    ) {
        setImgOperator(operator?.im3?.img)
    } else if (
        _val === '0817' ||
        _val === '0818' ||
        _val === '0819' ||
        _val === '0859' ||
        _val === '0877' ||
        _val === '0878' ||
        _val === '0879'
    ) {
        setImgOperator(operator?.xl?.img)
    } else if (
        _val === '0831' ||
        _val === '0838'
    ) {
        setImgOperator(operator?.axis?.img)
    } else if (
        _val === '0881' ||
        _val === '0882' ||
        _val === '0883' ||
        _val === '0884'
    ) {
        setImgOperator(operator?.smartfren?.img)
    } else {
        setImgOperator('')
    }
    await setIsValid(await schemaData.isValid({phone: val}))
}

export const isValidPLN = async (val, setPlnNumber, setIsValid) => {
    setPlnNumber(val)
    setIsValid(await schemaDataPln.isValid({pln: val}))
}

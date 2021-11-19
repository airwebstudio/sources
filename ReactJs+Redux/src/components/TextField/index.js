import React, { useState } from 'react'
import DatePicker from "react-datepicker"
import "react-datepicker/dist/react-datepicker.css"

const TextField = (props) => {

    const [startDate, setStartDate] = useState('');

    return (
        <>
        {(props.type == 'date') ?  <div  style={{minWidth: '120px'}}><DatePicker selected={startDate} placeholderText={props.label} onChange={(date) => {setStartDate(date); props.onChange(date) }}></DatePicker></div> : <input type={props.type} style={{width: 'auto'}}   placeholder={props.label} onChange={props.onChange} />}
        </>
        );
}

export default TextField
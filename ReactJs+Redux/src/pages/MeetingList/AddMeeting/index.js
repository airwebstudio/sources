import React, { Component } from 'react'
import { connect } from 'react-redux'
import { meeting } from '../../../actions'

import styles from '../../../styles.scss'

class AddMeeting extends Component {

    state = {
        sellerName: '',
        sellerDescription: '',
        sellerPrice: null,
        meetingAvailabilityName: '',
        meetingAvailabilityDescription: '',
        meetingAvailabilityDate: '',
        isPrivate: false,
        emptyFields: '',
        successCreate: '',
        successRemove: '',
        price: '',
        description: '',
        isStateWithData: false,
        emptyName: '',
        emptyFieldsMeeting: '',
        successAddMeeting: '',
        addMeetingName: '',
        addMeetingDescription: '',
        addMeetingDate: '',
        addMeetingType: '1:1',
        addMeetingPaymentType: 'fixed',
        addMeetingPrice: '5',
        addMeetingMinPrice: '',
        addBlockMinutes: '1',

    }

    onAddMeeting = async (e) => {
        e.preventDefault();
        const { addMeetingName, addMeetingDescription, addMeetingDate, addMeetingType,  addMeetingPaymentType, addMeetingPrice, addMeetingMinPrice, addBlockMinutes} = this.state;
        if(addMeetingName.length === 0 || addMeetingDescription.length === 0 || addMeetingDate === null) {
            return this.setState({
                ...this.state,
                emptyFieldsMeeting: 'All fields must be filled!'
            })
        }
        const res = await this.props.createMeetingAction({
            name: addMeetingName,
            description: addMeetingDescription,
            starting_at: addMeetingDate,
            type: addMeetingType,
            payment_type: addMeetingPaymentType,
            price: addMeetingPrice,
            min_price: addMeetingMinPrice,
            block_minutes: addBlockMinutes,
        })


        
        this.setState({
            ...this.state,
            addMeetingName: '',
            addMeetingDescription: '',
            addMeetingDate: '',
            emptyFieldsMeeting: '',
            addMeetingType: '1:1',

            successAddMeeting: res.payload.message + ' Hash: ' + res.payload.meeting.hash
        })

    }

    
    render =  () => {
        
        const { successCreate, successRemove, emptyFieldsMeeting, successAddMeeting } = this.state;

        return (
        <>
        <h2>Add meeting</h2>
        {emptyFieldsMeeting.length > 0 && <p className={styles.danger}>{emptyFieldsMeeting}</p>}
        {successAddMeeting && successAddMeeting.length > 0 && <p className={styles.success}>{successAddMeeting}</p>}
        <form onSubmit={this.onAddMeeting}>
            <input type={"text"} placeholder={"name"} value={this.state.addMeetingName} onChange={(e)=>this.setState({addMeetingName: e.target.value})}/>
            <textarea type={"textarea"} placeholder={"description"} value={this.state.addMeetingDescription} onChange={(e)=>this.setState({addMeetingDescription: e.target.value})}/>
            <input type={"date"} placeholder={"choose a date"} value={this.state.addMeetingDate} onChange={(e)=>this.setState({addMeetingDate: e.target.value})}/>
            <label htmlFor="typeMeeting" className={`d-flex align-center`} style={{marginBottom: '20px'}}>Choose a Type Meeting:
                <select
                    name="typeMeeting"
                    id="typeMeeting"
                    style={{marginLeft: '20px', width: '223px'}}
                    value={this.state.addMeetingType}
                    onChange={(e) => this.setState({addMeetingType: e.target.value})}
                >
                    <option value="1:1">1 to 1</option>
                    <option value="1:m">1 to Many</option>
                </select>
            </label>

            <label htmlFor="paymentTypeMeeting" className={`d-flex align-center`} style={{marginBottom: '20px'}}>Choose a Payment Type:
                <select
                    name="paymentTypeMeeting"
                    id="paymentTypeMeeting"
                    style={{marginLeft: '20px', width: '223px'}}
                    value={this.state.addMeetingPayment}
                    onChange={(e) => this.setState({addMeetingPaymentType: e.target.value})}
                >
                    <option value="fixed">Fixed</option>
                    <option value="per_min">Price per minute</option>
                </select>
            </label>

            <input type={"text"} placeholder={"meeting price"} value={this.state.addMeetingPrice} onChange={(e)=>this.setState({addMeetingPrice: e.target.value})}/>
            
            {(this.state.addMeetingPaymentType == 'per_min') && 
                <>
                <input type={"text"} placeholder={"minimal meeting price"} value={this.state.addMeetingMinPrice} onChange={(e)=>this.setState({addMeetingMinPrice: e.target.value})}/>
                <input type={"text"} placeholder={"block minutes"} value={this.state.addBlockMinutes} onChange={(e)=>this.setState({addBlockMinutes: e.target.value})}/>
                </>
            }

            <button>Submit</button>
        </form>

        </>


    )}

}

const mapDispatchToProps = dispatch => ({
    createMeetingAction: (params) => dispatch(meeting['create'](params)),
})

const mapStateToProps = state => {
    return {
        meeting: state['meeting'],
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(AddMeeting);
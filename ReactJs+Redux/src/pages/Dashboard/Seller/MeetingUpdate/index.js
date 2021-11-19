import React, { Component } from 'react'
import { connect } from 'react-redux'
import { Link, withRouter } from 'react-router-dom'
import { meeting } from '../../../../actions'
import { withAuthUser } from 'react-auth-kit';
import classnames from 'classnames'
import styles from './styles.scss'
import Header from '../../../../components/Header'
import Footer from '../../../../components/Footer'

class MeetingUpdate extends React.Component {

    state = {
        name: '',
        description: '',
        date: '',
        type: '',
        auto_approve_after_payment: null,
        hash: null,
        participants: [],
        isStateWithData: false,
        emptyFields: '',
        successMsg: '',
    }

    componentDidMount(){
        this.setState({
            name: '',
            description: '',
            date: '',
            type: '',
            auto_approve_after_payment: null,
            hash: null,
            isStateWithData: false,
            emptyFields: '',
            successMsg: '',
        })
        let {hash} = this.props;
        if(!hash) return;

        this.props.getMeetingInfoAction(hash)
    }

    componentDidUpdate(prevProps) {
        if(prevProps.meeting.getMeetingInfo !== this.props.meeting.getMeetingInfo) {
            
            this.setState({
                ...this.state,
                name: this.props.meeting.getMeetingInfo.meeting.name,
                description: this.props.meeting.getMeetingInfo.meeting.description,
                type: this.props.meeting.getMeetingInfo.meeting.type,
                hash: this.props.meeting.getMeetingInfo.meeting.hash,
                participants: this.props.meeting.getMeetingInfo.meeting.participants,
                auto_approve_after_payment: this.props.meeting.getMeetingInfo.meeting.auto_approve_after_payment,
                isStateWithData: true,
            })

            if(this.props.meeting.getMeetingInfo.meeting.starting_at !== null && this.props.meeting.getMeetingInfo.meeting.starting_at !== undefined) {
                this.setState({
                    ...this.state,
                    date: this.props.meeting.getMeetingInfo.meeting.starting_at.split(" ")[0],
                })
            }
        }

        if(prevProps.meeting.update !== this.props.meeting.update) {
            this.setState({
                ...this.state,
                successMsg: this.props.meeting.update.message
            })
            this.props.history.push('/dashboard/seller');
        }

        if(prevProps.meeting.removeUserOnMeeting !== this.props.meeting.removeUserOnMeeting) {
            alert(this.props.meeting.removeUserOnMeeting.message)
            
            this.props.getMeetingInfoAction(this.state.hash)
        }
    }


    onSubmit = (e) => {
        e.preventDefault();
        this.props.updateAction({
            hash: this.state.hash,
            name: this.state.name,
            description: this.state.description,
            type: this.state.type,
            starting_at: this.state.date,
            auto_approve_after_payment: this.state.auto_approve_after_payment,
        })

        this.props.getMeetingInfoAction(this.state.hash)
    }

    onDeclineUser = (id) => {
        this.props.removeUserOnMeetingAction(this.state.hash, id);
    }

    render = () => {

        const { name, description, date, type, auto_approve_after_payment, participants, isStateWithData, successMsg } = this.state;
        
        return (
            <>
                <Header />
                
                {participants.length === 0 && <h2>No Listeners</h2>}
                {participants.length > 0 && <h2>Available Listeners</h2>}
                
                {participants && participants.length > 0 &&
                    <ul>
                        {participants.map(user => (
                            <li className={styles.listItem} key={user.id}>
                                <p>ID: {user.user.id}</p>
                                <p>Name: {user.user.name}</p>
                                <p>Email: {user.user.email}</p>
                                <button 
                                    className={styles.danger}
                                    onClick={(e) => {
                                        e.preventDefault();
                                        this.onDeclineUser(user.user_id)
                                    }}
                                    >DECLINE</button>
                            </li>
                        ))}
                    </ul>
                }
                {isStateWithData &&
                    <>
                        <h2>Update Meeting</h2>
                        {successMsg && <p className={styles.success}>{successMsg}</p>}
                        <form 
                            onSubmit={this.onSubmit}
                        >
                            <input
                                label="Name"
                                type={"text"}
                                value={name}
                                onChange={(e)=>this.setState({name: e.target.value})} />
                            <textarea
                                label="Description"
                                value={description}
                                onChange={(e)=>this.setState({description: e.target.value})} />
                            <input
                                label="Date"
                                type={"date"}
                                value={date}
                                onChange={(e)=>this.setState({date: e.target.value})} />
                            <label htmlFor="typeMeeting" className={`d-flex align-center`} style={{marginBottom: '20px'}}>Choose a Type Meeting:
                                <select 
                                    name="typeMeeting" 
                                    id="typeMeeting" 
                                    style={{marginLeft: '20px', width: '223px'}}
                                    value={this.state.type}
                                    onChange={(e) => this.setState({type: e.target.value})}
                                >
                                    <option value="1:1">1 to 1</option>
                                    <option value="1:m">1 to Many</option>
                                    <option value="m:m">Many to Many</option>
                                </select>
                            </label>
                            <label htmlFor="AutoAproveAfterPayment">
                                Auto Aprove
                                <input
                                    id="AutoAproveAfterPayment"
                                    label="Auto Aproval"
                                    type="checkbox"
                                    checked={auto_approve_after_payment}
                                    onChange={(e)=>this.setState({auto_approve_after_payment: e.target.checked})} />
                            </label>
                            <button type="submit">
                                Update
                            </button>
                        </form>
                    </>
                }

                <Footer />
            </>
        )
    }
}

const mapDispatchToProps = dispatch => ({
    getMeetingInfoAction: hash => dispatch(meeting['getMeetingInfo'](hash)),
    updateAction: params => dispatch(meeting['update'](params)),
    removeUserOnMeetingAction: (id, hash) => dispatch(meeting['removeUserOnMeeting'](id, hash)),
})

const mapStateToProps = state => {return {
    meeting: state['meeting'],
}}

export default connect(mapStateToProps, mapDispatchToProps)(withAuthUser(withRouter(MeetingUpdate)))

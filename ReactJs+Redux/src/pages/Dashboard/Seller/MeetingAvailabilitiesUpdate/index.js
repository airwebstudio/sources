import React, { Component } from 'react'
import { connect } from 'react-redux'
import { Link, withRouter } from 'react-router-dom'
import { meeting } from '../../../../actions'
import { withAuthUser } from 'react-auth-kit';
import classnames from 'classnames'
import styles from './styles.scss'
import Header from '../../../../components/Header'
import Footer from '../../../../components/Footer'

class MeetingAvailabilitiesUpdate extends React.Component {

    state = {
        name: '',
        description: '',
        date: null,
        isStateWithData: false,
        emptyFields: '',
        successMsg: '',
    }

    componentDidMount(){
        this.setState({
            name: '',
            description: '',
            date: null,
            isStateWithData: false,
            emptyFields: '',
            successMsg: '',
        })
        let {id} = this.props;
        if(!id) return;

        this.props.infoMeetingAvailabilityAction(id)
        this.props.getAvailabilityParticipantsAction(id)
    }

    componentDidUpdate(prevProps) {
        if(prevProps.meeting.infoMeetingAvailability !== this.props.meeting.infoMeetingAvailability) {
            this.setState({
                ...this.state,
                name: this.props.meeting.infoMeetingAvailability.meeting.name,
                description: this.props.meeting.infoMeetingAvailability.meeting.description,
                date: this.props.meeting.infoMeetingAvailability.meeting.starting_at.split(" ")[0],
                isStateWithData: true,
            })
        }

        if(prevProps.meeting.updateMeetingAvailability !== this.props.meeting.updateMeetingAvailability) {
            this.setState({
                ...this.state,
                successMsg: this.props.meeting.updateMeetingAvailability.message
            })
        }

        if(prevProps.meeting.removeAvailabilityParticipant !== this.props.meeting.removeAvailabilityParticipant) {
            alert(this.props.meeting.removeAvailabilityParticipant.message)
            this.props.getAvailabilityParticipantsAction(this.props.id)
        }

        if(prevProps.meeting.aproveAvailabilityParticipant !== this.props.meeting.aproveAvailabilityParticipant) {
            alert(this.props.meeting.aproveAvailabilityParticipant.message)
            this.props.getAvailabilityParticipantsAction(this.props.id)
        }
    }


    onSubmit = (id) => {
        this.props.updateMeetingAvailabilityAction(id, {
            name: this.state.name,
            description: this.state.description,
            starting_at: this.state.date,
        })

        this.props.infoMeetingAvailabilityAction(id)
    }

    onAproveUser = (id) => {
        this.props.aproveAvailabilityParticipantAction(id)
    }

    onDeclineUser = (id) => {
        this.props.removeAvailabilityParticipantAction(id)
    }

    render = () => {
        const { name, description, date, isStateWithData, successMsg } = this.state;
        const { data } = this.props.meeting.getAvailabilityParticipants;
        const { response } = this.props.meeting.getAvailabilityParticipants;
        
        console.log(this.props)
        return (
            <>
                <Header />
                <h2>Available Participants</h2>
                {response && response.data &&
                    <p>{response.data.error}</p>
                }
                {data && data.users &&
                    <ul>
                        {data.users.map(user => (
                            <li className={styles.listItem} key={user.id}>
                                <span>UserID: {user.user_id}</span>
                                <button 
                                    className={styles.success}
                                    onClick={(e) => {
                                        e.preventDefault();
                                        this.onAproveUser(user.id)
                                    }}
                                    >APROVE</button>
                                <button 
                                    className={styles.danger}
                                    onClick={(e) => {
                                        e.preventDefault();
                                        this.onDeclineUser(user.id)
                                    }}
                                    >DECLINE</button>
                            </li>
                        ))}
                    </ul>
                }
                {isStateWithData &&
                    <>
                        <h2>Update Meeting Availabilities</h2>
                        {successMsg && <p className={styles.success}>{successMsg}</p>}
                        <form 
                            onSubmit={(e) => {
                                e.preventDefault();
                                this.onSubmit(this.props.meeting.infoMeetingAvailability.meeting.id);
                            }}
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
    infoMeetingAvailabilityAction: id => dispatch(meeting['infoMeetingAvailability'](id)),
    updateMeetingAvailabilityAction: (id, params) => dispatch(meeting['updateMeetingAvailability'](id, params)),
    getAvailabilityParticipantsAction: id => dispatch(meeting['getAvailabilityParticipants'](id)),
    aproveAvailabilityParticipantAction: id => dispatch(meeting['aproveAvailabilityParticipant'](id)),
    removeAvailabilityParticipantAction: id => dispatch(meeting['removeAvailabilityParticipant'](id)),
})

const mapStateToProps = state => {return {
    meeting: state['meeting'],
}}

export default connect(mapStateToProps, mapDispatchToProps)(withAuthUser(withRouter(MeetingAvailabilitiesUpdate)))

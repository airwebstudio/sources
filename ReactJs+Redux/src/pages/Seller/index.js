import React, { Component } from 'react';
import { connect } from 'react-redux'
import { seller, meeting, proposal, feed } from '../../actions'
import { BrowserRouter as Router, Route, withRouter, Link } from 'react-router-dom';
import Header from '../../components/Header';
import Footer from '../../components/Footer';
import styles from './styles.scss';
import {withAuthUser} from 'react-auth-kit';
import draftToHtml from 'draftjs-to-html';

class Seller extends Component {

    state = {
        name: '',
        description: '',
        starting_at: '',
        message: null,
        emptyFields: '',
    }

    componentDidMount = () => {
        let {id} = this.props;
        if (!id) return;

        this.props.calendarAction(id)
        this.props.infoAction()
        this.props.getSellerAvailabilityAction(id)
        this.props.infoByIdAction(id)
        this.props.getPublicUsersFeedsAction(`?user_id=${id}`)
    }

    onSubmit = (e) => {
        let {id} = this.props;
        e.preventDefault();
        const { name, description, starting_at } = this.state;
        if(name.length === 0 || description.length === 0 || starting_at.length === 0) {
            return this.setState({
                ...this.state,
                emptyFields: 'All fields must be filled!'
            })
        }
        this.props.addNewProposalAction({
            user_id: id,
            name: this.state.name,
            description: this.state.description,
            starting_at: this.state.starting_at,
        })

        this.setState({
            name: '',
            description: '',
            starting_at: '',
            message: 'Successfully sent',
            emptyFields: '',
        })
    }

    requestOnMeetingAvailabilities = (id) => {
        this.props.addUserMeetingAvailabilityAction(id)
    }

    requestOnMeeting = (hash, id) => {
        this.props.addUserOnMeetingAction(hash, id)
    }

    componentDidUpdate = prevProps => {
        const { addUserMeetingAvailability } = this.props.meeting;
        const { addUserOnMeeting } = this.props.meeting;
        if(prevProps.meeting.addUserMeetingAvailability !== addUserMeetingAvailability) {
            if(addUserMeetingAvailability.response && addUserMeetingAvailability.response.data.error.length > 0) {
                alert(addUserMeetingAvailability.response.data.error)
            } else if(addUserMeetingAvailability.data.message && addUserMeetingAvailability.data.message.length > 0) {
                alert(addUserMeetingAvailability.data.message + ' Wait for seller confirm!')
            }
        }

        if(prevProps.meeting.addUserOnMeeting !== addUserOnMeeting) {
            if(addUserOnMeeting.response && addUserOnMeeting.response.data.error.length > 0) {
                alert(addUserOnMeeting.response.data.error)
            } else if(addUserOnMeeting.data.message && addUserOnMeeting.data.message.length > 0) {
                alert(addUserOnMeeting.data.message + ' Wait for seller confirm!')
            }
        }
    }

    render = () => {
        const meetingAvailabilities = (this.props.meeting.getSellerAvailability.meeting_availabilities || []).filter(m => !m.declined_at);
        console.log(this.props)
        return (
            <>
                <Header />
                    <h2>Seller Info</h2>
                    <hr />
                    <h1>Name: {this.props.seller.infoById.name}</h1>
                    <p>Description: {this.props.seller.infoById.description}</p>
                    <p>Price: {this.props.seller.infoById.price}</p>


                    <Link to={`/chat/${this.props.seller.infoById.user_id}`}>I want chat with you</Link>

                    {meetingAvailabilities && meetingAvailabilities.length > 0 && 
                        <>
                            <h2>Calendar</h2>
                            <ul className={styles.meetingWrap}>
                                {meetingAvailabilities.map(meet => (
                                    <li className={styles.listItem} key={meet.id}>
                                        <span>Name: {meet.name}</span>
                                        <span>Description: {meet.description}</span>
                                        <span>Date: {meet.starting_at}</span>

                                        {this.props.authState && (
                                            <button onClick={() => {
                                                this.requestOnMeetingAvailabilities(meet.id)
                                            }}>I want too</button>
                                        )}
                                    </li>

                                ))}
                            </ul>
                        </>}
                    {meetingAvailabilities && meetingAvailabilities.length === 0 && <h2>No Calendar Items</h2>}

                    {this.props.meeting.calendar.meetings && this.props.meeting.calendar.meetings.length > 0 && <h2>List of Meeting items</h2>}
                    {this.props.meeting.calendar.meetings && this.props.meeting.calendar.meetings.length === 0 && <h2>No Meetings. Propose?</h2>}
                    {this.props.meeting.calendar
                            && this.props.meeting.calendar.meetings
                            && this.props.meeting.calendar.meetings.map(meet => (
                        <li className="listMeetings--item" key={meet.id}>
                            Name: {meet.name}<br />
                            Description: {meet.description}<br />
                            Date: {meet.starting_at}<br />
                            User: {meet.user.email}<br />

                            {this.props.authState && (
                                <button onClick={() => {
                                    this.requestOnMeeting(meet.hash, this.props.authState.id)
                                }}>I want too</button>
                            )}
                        </li>
                    ))}
                    
                    <h2>Propose meeting</h2>
                    {this.state.message && (
                        <p className={styles.success}>{this.state.message}</p>
                    )}
                    {this.state.emptyFields && (
                        <p className={styles.danger}>{this.state.emptyFields}</p>
                    )}
                    <form onSubmit={this.onSubmit}>
                        <input type={"text"} placeholder={"name"} value={this.state.name} onChange={(e)=>this.setState({name: e.target.value})}/>
                        <textarea type={"textarea"} placeholder={"description"} value={this.state.description} onChange={(e)=>this.setState({description: e.target.value})}/>
                        <input type={"date"} placeholder={"choose a date"} value={this.state.starting_at} onChange={(e)=>this.setState({starting_at: e.target.value})}/>
                        <button>Submit</button>
                    </form>

                    <h2>News from {this.props.seller.infoById.name}</h2>
                    {this.props.feed.getPublicUsersFeeds.data && this.props.feed.getPublicUsersFeeds.data.length === 0 && <h2>No news...</h2>}
                    {this.props.feed.getPublicUsersFeeds.data && this.props.feed.getPublicUsersFeeds.data.length > 0 && 
                     <>
                        <ul>
                            {this.props.feed.getPublicUsersFeeds.data.map(feed => (
                                <li className={styles.listItem} key={feed.id}>
                                    <h2>{feed.title}</h2>
                                    <div dangerouslySetInnerHTML={{__html: draftToHtml(JSON.parse(feed.description))}}></div>
                                    <p><b>Created:</b> {feed.created_at} by <b>USER:</b><Link to={`/seller/${feed.user_id}`}>{feed.user_id}</Link></p>
                                </li>
                            ))}
                        </ul>
                        {this.props.feed.getPublicUsersFeeds.links && this.props.feed.getPublicUsersFeeds.links.length > 0 && this.props.feed.getPublicUsersFeeds.last_page > 1 &&
                            <TimePalPagination catalogLinks={this.props.feed.getPublicUsersFeeds.links} actionPage={this.props.getPublicUsersFeedsAction} />
                        }
                    </>}
                <Footer />
            </>
        )
    }
}

const mapDispatchToProps = dispatch => ({
    infoAction: () => dispatch(seller.info()),
    infoByIdAction: (id) => dispatch(seller.infoById(id)),
    calendarAction: (id) => dispatch(meeting.calendar(id)),
    addUserOnMeetingAction: (hash, userId) => dispatch(meeting.addUserOnMeeting(hash, userId)),
    getSellerAvailabilityAction: (id) => dispatch(meeting.getSellerAvailability(id)),
    addUserMeetingAvailabilityAction: (id) => dispatch(meeting.addUserMeetingAvailability(id)),
    addNewProposalAction: (params) => dispatch(proposal.addNew(params)),
    getPublicUsersFeedsAction: (params) => dispatch(feed.getPublicUsersFeeds(params)),
})

const mapStateToProps = state => {
    return {
        seller: state['seller'],
        meeting: state['meeting'],
        proposal: state['proposal'],
        feed: state['feed'],
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(withAuthUser(withRouter(Seller)));

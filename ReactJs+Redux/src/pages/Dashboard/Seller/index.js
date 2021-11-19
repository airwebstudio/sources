import React, { Component } from 'react'
import { connect } from 'react-redux'
import { Link, withRouter } from 'react-router-dom'
import { seller, meeting, proposal, buyer, feed } from '../../../actions'
import { withAuthUser, withRefreshToken } from 'react-auth-kit';
import classnames from 'classnames'
import styles from './styles.scss'
import Header from '../../../components/Header'
import Footer from '../../../components/Footer'
import TimePalPagination from '../../../components/Pagination';
import draftToHtml from 'draftjs-to-html';

class Seller extends React.Component {

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

    }

    componentDidMount(){
        this.setState({
            meetingAvailabilityName: '',
            meetingAvailabilityDescription: '',
            meetingAvailabilityDate: '',
            isPrivate: false,
            emptyFields: '',
            successCreate: '',
            successRemove: '',
            price: '',
            description: '',
            emptyFieldsMeeting: '',
            successAddMeeting: '',
            addMeetingName: '',
            addMeetingDescription: '',
            addMeetingDate: '',
        })
        this.props.myOwnMeetsAction()
        this.props.myOwnProposalsAction()
        this.props.getMeetingAvailabilitiesAction()
        this.props.sellerInfoAction()
        this.props.getUserFeedsAction()

    }

    componentDidUpdate(prevProps, prevState) {
        if(prevProps.seller.info !== this.props.seller.info) {
            this.setState({
                ...this.state,
                sellerName: this.props.seller.info.name,
                sellerDescription: this.props.seller.info.description,
                sellerPrice: this.props.seller.info.price,
                isStateWithData: true,
            })
        }

        if(
            this.props.proposal.decline && this.props.proposal.decline.message
            && (!prevProps.proposal.decline || !prevProps.proposal.decline.message)
        ){
            this.props.myOwnProposalsAction()
        }

        if(
            this.props.proposal.approve && this.props.proposal.approve.message
            && (!prevProps.proposal.approve || !prevProps.proposal.approve.message)
        ){ 
            this.props.myOwnMeetsAction()
            this.props.myOwnProposalsAction()
        }

        if(prevProps.seller.create !== this.props.seller.create) {
            this.props.infoAction();
        }

        if(prevProps.seller.update !== this.props.seller.update) {
            this.props.infoAction();
        }

        if(prevProps.buyer.info !== this.props.buyer.info) {
            this.props.refreshToken.updateUserState(this.props.buyer.info.data)

            window.location.reload()
        }

        if(prevProps.meeting.createMeetingAvailability !== this.props.meeting.createMeetingAvailability) {
            this.setState({
                ...this.state,
                successCreate: this.props.meeting.createMeetingAvailability.message,
            })
            this.props.getMeetingAvailabilitiesAction()
        }

        if(prevProps.meeting.removeMeetingAvailability !== this.props.meeting.removeMeetingAvailability) {
            this.setState({
                ...this.state,
                successRemove: this.props.meeting.removeMeetingAvailability.message,
            })
            this.props.getMeetingAvailabilitiesAction()
        }

        if(prevProps.meeting.approveUserOnMeeting !== this.props.meeting.approveUserOnMeeting) {
            alert(this.props.meeting.approveUserOnMeeting.message)
            this.props.myOwnMeetsAction()
        }

        if(prevProps.meeting.removeUserOnMeeting !== this.props.meeting.removeUserOnMeeting) {
            alert(this.props.meeting.removeUserOnMeeting.message)
            this.props.myOwnMeetsAction()
        }

        if(prevProps.feed.removeFeed !== this.props.feed.removeFeed) {
            this.props.getUserFeedsAction()
        }

        if(prevProps.meeting.create !== this.props.meeting.create) {
            this.setState({
                ...this.state,
                successAddMeeting: this.props.meeting.create.message,
            })
            this.props.myOwnMeetsAction()
        }
    }

    privateChange = (e) => {
        this.setState({
            ...this.state,
            isPrivate: e.target.checked
        })
    }

    onSubmitCreate = (e) => {
        e.preventDefault();
        const { price, description } = this.state;
        if(price.length === 0 || description.length === 0) {
            return this.setState({
                ...this.state,
                emptyFields: 'All fields must be filled!'
            })
        }
        this.props.createSeller({
            price: this.state.price,
            description: this.state.description,
            name: this.props.authState.name,
        })
        this.props.getMeetingAvailabilitiesAction()
    }

    onSubmit = (e) => {
        e.preventDefault();
        const { meetingAvailabilityName, meetingAvailabilityDescription, meetingAvailabilityDate } = this.state;
        if(meetingAvailabilityName.length === 0 || meetingAvailabilityDescription.length === 0 || meetingAvailabilityDate === null) {
            return this.setState({
                ...this.state,
                emptyFields: 'All fields must be filled!'
            })
        }
        this.props.createMeetingAvailabilityAction({
            name: this.state.meetingAvailabilityName,
            description: this.state.meetingAvailabilityDescription,
            starting_at: this.state.meetingAvailabilityDate,
        })
        this.setState({
            ...this.state,
            meetingAvailabilityName: '',
            meetingAvailabilityDescription: '',
            meetingAvailabilityDate: '',
            emptyFields: '',
        })
    }

    onSubmitEdit = (e) => {
        e.preventDefault();
        const { sellerName, sellerDescription, sellerPrice } = this.state;

        if(sellerName.length === 0) {
            return this.setState({
                ...this.state,
                emptyName: 'Name is required',
            })
        }

        this.props.sellerUpdateAction({
            name: sellerName,
            description: sellerDescription,
            price: sellerPrice,
        })
    }

    onAddStripeAcc = (e) => {
        e.preventDefault();
        this.props.getStripeAccAction({});
    }

    removeMeet = (id) => {
        this.props.removeMeetingAvailabilityAction(id)
    }

    approveUserOnMeeting = (hash, id) => {
        this.props.approveUserOnMeetingAction(hash, id)
    }

    declineUserOnMeeting = (hash, id) => {
        this.props.removeUserOnMeetingAction(hash, id)
    }

    onRemoveFeed = (id) => {
        this.props.removeFeedAction(id)
    }

    onCopy = (e) => {
        const inputElement = e.target.previousElementSibling;
        inputElement.select()
        const success = document.execCommand('copy')
        if(success){
			inputElement.style.borderColor = 'green';
            e.target.innerText = 'copied!';
		} else {
            inputElement.style.borderColor = 'red';
        }
    }

    onAddMeeting = (e) => {
        e.preventDefault();
        const { addMeetingName, addMeetingDescription, addMeetingDate, addMeetingType,  addMeetingPaymentType, addMeetingPrice, addMeetingMinPrice} = this.state;
        if(addMeetingName.length === 0 || addMeetingDescription.length === 0 || addMeetingDate === null) {
            return this.setState({
                ...this.state,
                emptyFieldsMeeting: 'All fields must be filled!'
            })
        }
        this.props.createMeetingAction({
            name: addMeetingName,
            description: addMeetingDescription,
            starting_at: addMeetingDate,
            type: addMeetingType,
            payment_type: addMeetingPaymentType,
            price: addMeetingPrice,
            min_price: addMeetingMinPrice
        })
        this.setState({
            ...this.state,
            addMeetingName: '',
            addMeetingDescription: '',
            addMeetingDate: '',
            emptyFieldsMeeting: '',
            addMeetingType: '1:1',
        })
    }

    render = () => {
        const { getMeetingAvailabilities } = this.props.meeting;
        const { emptyFields, successCreate, successRemove, emptyFieldsMeeting, successAddMeeting } = this.state;
        const { sellerName, sellerDescription, sellerPrice, isStateWithData, emptyName } = this.state;
        const { getUserFeeds } = this.props.feed;

        console.log(this.props)

        if(!this.props.authState.is_seller){
            return (
                <>
                    <Header />
                    <form onSubmit={this.onSubmitCreate}>
                        <p>Please fill inputs</p>
                        {emptyFields.length > 0 && <p className={styles.danger}>{emptyFields}</p>}
                        <input type={"text"} placeholder={"price"} value={this.state.price}
                            onChange={(e)=>this.setState({price: e.target.value})}/>

                        <input type={"text"} placeholder={"description"} value={this.state.description}
                            onChange={(e)=>this.setState({description: e.target.value})}/>

                        <button>Submit</button>
                    </form>
                    <Footer />
                </>
            )
        }

        return (
            <>
                <Header />

                <h1>{this.props.authState.name} (Seller Profile)</h1>



                {isStateWithData &&
                    <>
                        <h2>Edit Seller</h2>
                        <form
                            className={styles.mainForm}
                            onSubmit={this.onSubmitEdit}
                            >
                            {emptyName.length > 0 && <p className={styles.danger}>{emptyName}</p>}
                            <input
                                label="Name"
                                type={"text"}
                                value={sellerName}
                                onChange={(e)=>this.setState({sellerName: e.target.value})} />
                            <textarea
                                label="Description"
                                value={sellerDescription}
                                onChange={(e)=>this.setState({sellerDescription: e.target.value})} />
                            <input
                                label="Price"
                                type={"number"}
                                value={sellerPrice}
                                onChange={(e)=>this.setState({sellerPrice: e.target.value})} />
                            <label>
                                <input
                                    type="checkbox"
                                    checked={this.state.isPrivate}
                                    className="privateInput"
                                    onChange={this.privateChange}
                                />
                                isPrivate
                            </label>
                            <button type="submit">
                                Update
                            </button>
                        </form>
                    </>
                }

                <h2>Calendar</h2>
                {getMeetingAvailabilities.meeting &&
                    getMeetingAvailabilities.meeting.data.length === 0 &&
                    <h3>No items</h3>}
                {successRemove.length > 0 && <p className={styles.success}>{successRemove}</p>}
                <ul>
                    {getMeetingAvailabilities.meeting && getMeetingAvailabilities.meeting.data.map(meet => (
                        <li className={styles.listItem} key={meet.id}>
                            <Link className={styles.meetItem} to={'/dashboard/seller/availability/' + meet.id}>
                                <div>{meet.name}</div>
                            </Link>
                            <span>{meet.description}</span>
                            <span>{meet.starting_at}</span>
                            {meet.declined_at && (
                                <span className={styles.danger}>Declined</span>
                            )}
                            {!meet.declined_at && (
                                <>
                                    <Link className={styles.meetItem} to={'/dashboard/seller/availability/' + meet.id}>
                                        <div className={styles.update}>Update</div>
                                    </Link>
                                    <button
                                        className={styles.danger}
                                        onClick={() => {
                                            this.removeMeet(meet.id)
                                        }}
                                    >REMOVE</button>
                                </>
                            )}
                            {meet.participants.length > 0 &&
                                <span>You have new participants, please aprove or decline <Link className={styles.meetItem} to={'/dashboard/seller/availability/' + meet.id}>here</Link></span>
                            }
                        </li>

                    ))}
                </ul>
                {getMeetingAvailabilities.meeting &&
                    getMeetingAvailabilities.meeting.data.length > 0 &&
                    <TimePalPagination catalogLinks={getMeetingAvailabilities.meeting.links} actionPage={this.props.getMeetingAvailabilitiesAction} />
                }

                {this.props.meeting.myOwn && this.props.meeting.myOwn.meetings && (
                    <>
                        <h2>List of meetings</h2>
                        {this.props.meeting.myOwn.meetings.length === 0 && <h3>No Meetings</h3>}
                        <ul>
                            {this.props.meeting.myOwn.meetings.map(meet => (
                                <li key={meet.id} >
                                    Name: {meet.name}<br />
                                    Description: {meet.description}<br />
                                    Date: {meet.starting_at}<br />
                                    Type: {meet.type}<br />
                                    User: {meet.user.email}<br />
                                    Quant subscribers: {meet.participants.length}<br />
                                    {meet.propose_participants.length > 0 &&
                                        <>
                                            Want subscribe:
                                            <ul>
                                                {meet.propose_participants.map(propose => (
                                                    <li key={propose.id}>
                                                        <span>User: {propose.user_id}</span>
                                                        <button
                                                            className={styles.success}
                                                            onClick={() => {
                                                                this.approveUserOnMeeting(meet.hash, propose.user_id)
                                                            }}
                                                        >APPROVE</button>
                                                        <button
                                                            className={styles.danger}
                                                            onClick={() => {
                                                                this.declineUserOnMeeting(meet.hash, propose.user_id)
                                                            }}
                                                        >DECLINE</button>
                                                    </li>
                                                ))}
                                            </ul>
                                        </>
                                    }
                                    <a href={process.env.REACT_APP_VIDEO_HOST + '/' + meet.hash} target="_blank">Go to video chat</a><br />
                                    <input type="text" readOnly="readonly" value={process.env.REACT_APP_VIDEO_HOST + '/' + meet.hash} />
                                    <button onClick={this.onCopy}>copy to clipboard</button><br />
                                    <a href={'/chat/history/' + meet.hash} target="_blank">View chat history</a><br />
                                    <a href={'/dashboard/seller/meeting/' + meet.hash}>Update Meeting</a>

                                </li>
                            ))}
                        </ul>
                    </>
                )}

                {this.props.proposal.myOwn.meetings && (
                    <>
                        <h2>Meetings proposals</h2>
                        {this.props.proposal.myOwn.meetings.length === 0 && <h3>No Proposals</h3>}
                        <ul>
                            {this.props.proposal.myOwn.meetings.map(meet => (
                                <li key={meet.id}>
                                    Name: {meet.name}<br />
                                    Description: {meet.description}<br />
                                    Date: {meet.starting_at}<br />
                                    User: {meet.user.email}<br />
                                    {!meet.declined_at && (
                                        <>
                                            <button onClick={() => this.props.approveProposalAction(meet.id)}>APPROVE</button>
                                            <button onClick={() => this.props.declineProposalAction(meet.id)}>DECLINE</button>
                                        </>
                                    )}

                                    {meet.declined_at && (
                                        <span className={styles.danger}>Declined</span>
                                    )}


                                </li>
                            ))}
                        </ul>
                    </>
                )}

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
                    <input type={"text"} placeholder={"minimal meeting price"} value={this.state.addMeetingMinPrice} onChange={(e)=>this.setState({addMeetingMinPrice: e.target.value})}/>


                    <button>Submit</button>
                </form>

                <h2>Create calendar item</h2>
                {emptyFields.length > 0 && <p className={styles.danger}>{emptyFields}</p>}
                {successCreate.length > 0 && <p className={styles.success}>{successCreate}</p>}
                <form onSubmit={this.onSubmit}>
                    <input type={"text"} placeholder={"name"} value={this.state.meetingAvailabilityName} onChange={(e)=>this.setState({meetingAvailabilityName: e.target.value})}/>
                    <textarea type={"textarea"} placeholder={"description"} value={this.state.meetingAvailabilityDescription} onChange={(e)=>this.setState({meetingAvailabilityDescription: e.target.value})}/>
                    <input type={"date"} placeholder={"choose a date"} value={this.state.meetingAvailabilityDate} onChange={(e)=>this.setState({meetingAvailabilityDate: e.target.value})}/>
                    <button>Submit</button>
                </form>

                <h2>Last news</h2>
                {getUserFeeds && getUserFeeds.data && getUserFeeds.data.length === 0 &&
                    <Link to={'/dashboard/seller/feeds/create'}>Сreate your first news</Link>}
                {getUserFeeds && getUserFeeds.data && getUserFeeds.data.length > 0 &&
                    <>
                        <ul>
                        {getUserFeeds.data.map(feed => (
                            <li key={feed.id}>
                                <h3>{feed.title}</h3>
                                <div dangerouslySetInnerHTML={{__html: draftToHtml(JSON.parse(feed.description))}}></div>
                                <p><b>Created:</b> {feed.created_at}</p>
                                <Link to={`/dashboard/seller/feeds/edit/${feed.id}`}>Edit news</Link>
                                <button onClick={() => {
                                    this.onRemoveFeed(feed.id)
                                }}>Remove news</button>
                            </li>
                        ))}
                        </ul>
                        {getUserFeeds.links && getUserFeeds.links.length > 0 && getUserFeeds.last_page > 1 &&
                            <TimePalPagination catalogLinks={getUserFeeds.links} actionPage={this.props.getUserFeedsAction} />
                        }
                        <Link to={'/dashboard/seller/feeds/create'}>Сreate your news</Link>
                    </>
                }



                <Footer />
            </>
        )
    }
}

const mapDispatchToProps = dispatch => ({
    infoAction: () => dispatch(buyer['info']()),
    createSeller: (params) => dispatch(seller['create'](params)),
    settings: (params) => dispatch(seller['settings'](params)),
    sellerInfoAction: () => dispatch(seller['info']()),
    sellerUpdateAction: (params) => dispatch(seller['update'](params)),
    myOwnMeetsAction: (params) => dispatch(meeting['myOwn'](params)),
    createMeetingAction: (params) => dispatch(meeting['create'](params)),
    getMeetingAvailabilitiesAction: (page) => dispatch(meeting['getMeetingAvailabilities'](page)),
    createMeetingAvailabilityAction: (params) => dispatch(meeting['createMeetingAvailability'](params)),
    removeMeetingAvailabilityAction: (id) => dispatch(meeting['removeMeetingAvailability'](id)),
    myOwnProposalsAction: (params) => dispatch(proposal['myOwn'](params)),
    approveProposalAction: (id) => dispatch(proposal['approve'](id)),
    declineProposalAction: (id) => dispatch(proposal['decline'](id)),
    approveUserOnMeetingAction: (hash, userId) => dispatch(meeting.approveUserOnMeeting(hash, userId)),
    removeUserOnMeetingAction: (hash, userId) => dispatch(meeting.removeUserOnMeeting(hash, userId)),
    getUserFeedsAction: () => dispatch(feed.getUserFeeds()),
    removeFeedAction: (id) => dispatch(feed.removeFeed(id)),
})

const mapStateToProps = state => {return {
    buyer: state['buyer'],
    seller: state['seller'],
    meeting: state['meeting'],
    proposal: state['proposal'],
    feed: state['feed'],
}}

export default connect(mapStateToProps, mapDispatchToProps)(withAuthUser(withRouter(withRefreshToken(Seller))))

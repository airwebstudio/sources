import React, { Component } from 'react'
import axios from 'axios'
import { connect } from 'react-redux'
import { Link, withRouter } from 'react-router-dom'
import { buyer, meeting, proposal } from '../../../actions'
import classnames from 'classnames'
import styles from './styles.scss'
import { withIsAuthenticated, withAuthUser, withRefreshToken } from 'react-auth-kit';
import Header from '../../../components/Header'
import Footer from '../../../components/Footer'
import TimePalAvatarUpload from '../../../components/TimePalAvatarUpload'

class Buyer extends React.Component {

    state = {
        userName: '',
        isStateWithData: false,
        emptyFields: '',
        successUpdatedMsg: '',
        description: '',
    }

    componentDidMount(){
        this.props.myRequestsMeetsAction()
        this.props.myRequestsProposalsAction()
        this.props.infoAction()
    }

    componentDidUpdate(prevProps) {
        if(prevProps.buyer.avatarUpload !== this.props.buyer.avatarUpload) {
            this.props.infoAction();

        }
        if(prevProps.buyer.info !== this.props.buyer.info) {
            this.props.refreshToken.updateUserState(this.props.buyer.info.data)
        }

        if(
            this.props.proposal.decline && this.props.proposal.decline.message
            && (!prevProps.proposal.decline || !prevProps.proposal.decline.message)
        ){
            this.props.myRequestsProposalsAction()
        }

        if(prevProps.buyer.info !== this.props.buyer.info) {
            this.setState({
                userName: this.props.buyer.info.data.name,
                description: this.props.buyer.info.data.description,
                isStateWithData: true,
            })
        }

        if(prevProps.buyer.update !== this.props.buyer.update) {
            this.setState({
                ...this.state,
                successUpdatedMsg: this.props.buyer.update.message
            })
            this.props.infoAction()
        }

        if(prevProps.buyer !== this.props.buyer) {
            console.log(this.props)
        }
    }

    onSubmitEdit = (e) => {
        e.preventDefault();
        const { userName, description } = this.state;
        if(userName.length === 0) {
            return this.setState({
                ...this.state,
                emptyFields: 'All fields must be filled!'
            })
        }
        this.props.updateAction({
            name: userName,
            email: this.props.authState.email,
            description: description
        })
        this.setState({
            ...this.state,
            emptyFields: '',
        })
    }

    render = () => {

        const { userName, isStateWithData, emptyFields, successUpdatedMsg } = this.state;
        console.log(this.props)

        return (
            <>
                <Header />

                <h1>{this.props.authState.name} (Buyer Profile)</h1>

                {isStateWithData &&
                    <>
                        <h2>Edit User</h2>
                        {emptyFields.length > 0 && <p className={styles.danger}>{emptyFields}</p>}
                        {successUpdatedMsg.length > 0 && <p className={styles.success}>{successUpdatedMsg}</p>}
                        <form 
                            className={styles.mainForm}
                            onSubmit={this.onSubmitEdit}
                            >
                            <input
                                label="Name"
                                type={"text"}
                                value={userName}
                                onChange={(e)=>this.setState({userName: e.target.value})} />
                            <label>
                                <textarea
                                    label="Description"
                                    placeholder="Description"
                                    value={this.state.description}
                                    onChange={(e)=>this.setState({description: e.target.value})} />
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
                <h2>Change Your Avatar</h2>
                <TimePalAvatarUpload />

                {this.props.meeting.myRequests && this.props.meeting.myRequests.meets && (
                    <>
                        <p>My guest meets</p>
                        <ul>
                            {this.props.meeting.myRequests.meets.map(meet => (
                                <li>
                                    Name: {meet.name}<br />
                                    Description: {meet.description}<br />
                                    Date: {meet.starting_at}<br />
                                    User: {meet.user.email}<br />
                                    <a href={process.env.REACT_APP_VIDEO_HOST + '/' + meet.hash}>Go to video chat</a>
                                </li>
                            ))}
                        </ul>
                    </>
                )}

                {this.props.proposal.myRequests && this.props.proposal.myRequests.meets && (
                    <>
                        <p>My meets proposals</p>
                        <ul>
                            {this.props.proposal.myRequests.meets.map(meet => (
                                <li key={meet.id}>
                                    Description: {meet.description}<br />
                                    Date: {meet.starting_at}<br />
                                    User: {meet.user.email}<br />

                                    {!meet.declined_at && (
                                        <button onClick={() => this.props.declineProposalAction(meet.id)}>DECLINE</button>
                                    )}

                                    {meet.declined_at && (
                                        <p>Declined</p>
                                    )}
                                </li>
                            ))}
                        </ul>
                    </>
                )}

                <Footer />
            </>
        )
    }
}

const mapDispatchToProps = dispatch => ({
    settings: (params) => dispatch(buyer['settings'](params)),
    infoAction: () => dispatch(buyer['info']()),
    updateAction: (params) => dispatch(buyer['update'](params)),
    myRequestsMeetsAction: (params) => dispatch(meeting['myRequests'](params)),
    myRequestsProposalsAction: (params) => dispatch(proposal['myRequests'](params)),
    declineProposalAction: (params) => dispatch(proposal['decline'](params)),
})

const mapStateToProps = state => {return {
    buyer: state['buyer'],
    meeting: state['meeting'],
    proposal: state['proposal'],
}}
export default connect(mapStateToProps, mapDispatchToProps)(withRefreshToken(
    withRouter(withAuthUser(withIsAuthenticated(withRefreshToken(Buyer))))
))

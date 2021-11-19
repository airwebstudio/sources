import React, { Component } from 'react'
import { connect } from 'react-redux'
import { BrowserRouter as Router, Route, withRouter, Link } from 'react-router-dom';
import { chat } from '../../../actions'
import classnames from 'classnames'
import styles from './styles.scss'
import Header from '../../../components/Header'
import Footer from '../../../components/Footer'
import TimePalAvatar from '../../../components/TimePalAvatar'
import {withIsAuthenticated, withAuthUser} from 'react-auth-kit';

class History extends React.Component {

    state = {

    }

    componentDidMount(){
        this.props.chatAction(this.props.roomId)
    }

    render = () => {
        console.log(this.props)
        return (
            <>
                <Header />
                <p>History</p>
                <h2>Users</h2>
                <ul>
                {this.props.chat.history && this.props.chat.history.users && this.props.chat.history.users.filter(u => u.username).map(user => (
                    <li>{user.username}</li>
                ))}
                </ul>
                <h2>Message</h2>
                <ul>
                {this.props.chat.history && this.props.chat.history.messages && this.props.chat.history.messages.map(message => (
                    <li>{message.username ? message.username : 'System'}: {message.content}</li>
                ))}
                </ul>
                <Footer />
            </>
        )
    }

}

const mapDispatchToProps = dispatch => ({
    chatAction: (roomId) => dispatch(chat.history(roomId)),
})


const mapStateToProps = state => {return {
    chat: state['chat']
}}

export default connect(mapStateToProps, mapDispatchToProps)(withRouter(withAuthUser(withIsAuthenticated(History))))

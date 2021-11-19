import React, { Component } from 'react';
import { connect } from 'react-redux'
import Header from '../../../components/Header';
import Footer from '../../../components/Footer';
import {chat} from "../../../actions";
import {Link} from "react-router-dom";


class History extends Component {

    state = {
    }

    componentDidMount = () => {
        this.props.privateChatsAction()
    }

    render = () => {
        return (
            <>
                <Header />

                <h2>List</h2>
                {this.props.privateChats.map(id => (
                    <Link key={id} to={`/chat/${id}`}>{id}</Link>
                ))}

                <Footer />
            </>
        )
    }
}

const mapDispatchToProps = dispatch => ({
    privateChatsAction: () => dispatch(chat.privateChats()),
})

const mapStateToProps = state => {
    return {
        privateChats: state['chat']['privateChats']
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(History);

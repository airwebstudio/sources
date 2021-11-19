import React, { Component } from 'react';
import { connect } from 'react-redux'
import { stripe } from '../../actions'
import { BrowserRouter as Router, Route, withRouter, Link } from 'react-router-dom';
import Header from '../../components/Header';
import Footer from '../../components/Footer';
import styles from './styles.scss';
import {withAuthUser} from 'react-auth-kit';

class Reauth extends Component {

    state = {

    }

    componentDidMount = () => {
        this.props.getStripeLink()
    }
    componentDidUpdate = () => {
        if (this.props.stripe.stripe_link)
            window.location = this.props.stripe.stripe_link.data.url;
    }


    render = () => {
        return (
            <>
                Wait a moment..
            </>
        )
    }
}

const mapDispatchToProps = dispatch => ({
    getStripeLink: () => dispatch(stripe['stripe_link']()),
})

const mapStateToProps = state => {
    return {
        stripe: state['stripe'],

    }
}

export default connect(mapStateToProps, mapDispatchToProps)(withAuthUser(withRouter(Reauth)));

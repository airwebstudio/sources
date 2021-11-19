import React, { Component } from 'react';
import { connect } from 'react-redux'
import { stripe } from '../../actions'
import { BrowserRouter as Router, Route, withRouter, Link } from 'react-router-dom';
import Header from '../../components/Header';
import Footer from '../../components/Footer';
import styles from './styles.scss';
import {withAuthUser} from 'react-auth-kit';

class Return extends Component {

    state = {
        errors: [],
        disabled_reason: 'loading',
    }

    componentDidMount = () => {
        this.props.getStripeRetrieve()
    }

    componentDidUpdate = (previousProps, previousState) => {
        let errs, disabled_reason, pending_verification;
        if (this.props.stripe.stripe_retrieve) {
            if (errs = this.props.stripe.stripe_retrieve.data.verification.errors) {
                //his.props.errors = errs
                if (previousState.errors !== errs) {
                    this.setState({'errors': errs})
                    console.log(errs)
                }
                
            }

            if (disabled_reason = this.props.stripe.stripe_retrieve.data.verification.disabled_reason) {
                //his.props.errors = errs
                if (previousState.disabled_reason !== disabled_reason) {
                    this.setState({'disabled_reason': disabled_reason})
                    
                }
                
            }


            if ((pending_verification = this.props.stripe.stripe_retrieve.data.verification.pending_verification) && (pending_verification.length)) {
                setTimeout(() => this.props.getStripeRetrieve(), 4000);
            }
            else {
                if (disabled_reason != null) {
                    setTimeout(() => {window.location = '/stripe/reAuth/'}, 2000);
                }
                else {
                    setTimeout(() => {window.location = '/stripe/wallet/'}, 2000);
                }
            }

            
        }
            
    }


    render = () => {
        console.log(this.props)
        return (
           <>
          
           {
            (this.state.disabled_reason !== 'loading') ? ( 
               (this.state.disabled_reason)  ? (
               <>
                    Disabled reason: { this.state.disabled_reason }
                    <Link to='/stripe/reAuth/'>Try again</Link>
                </>
           ) : (<>Done. Back you to the Wallet...</>)) : (<>Wait a moment..</>)

           } 
           
            </>
        )
    }
}

const mapDispatchToProps = dispatch => ({
    getStripeRetrieve: () => dispatch(stripe['stripe_retrieve']()),

})

const mapStateToProps = state => {
    return {
        stripe: state['stripe'],

    }
}

export default connect(mapStateToProps, mapDispatchToProps)(withAuthUser(withRouter(Return)));

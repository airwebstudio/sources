import React, { Component } from 'react'
import { connect } from 'react-redux'
import { Link, withRouter } from 'react-router-dom'
import { auth } from '../../../actions'
import classnames from 'classnames'
import styles from './styles.scss'
import { withSignIn } from 'react-auth-kit'
import Header from "../../../components/Header";
import Footer from "../../../components/Footer";

class ForgotPassword extends React.Component {

    state = {
        email: '',
        emptyFields: false,
        validEmail: true,
        userNotRegistered: false,
        processing: false,
        successRestore: false,
    }

    onSubmitForgotPassword = (e) => {
        e.preventDefault();
        if(this.state.email.length === 0) {
            this.setState({ emptyFields: true });
        } else {
            this.setState({ emptyFields: false });
            const reg = /[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?/;
            if(reg.test(String(this.state.email).toLowerCase())) {
                this.setState({ processing: true })
                this.props.forgotPasswordAction({
                    email: this.state.email,
                })
                this.setState({
                    validEmail: true,
                    errorMsg: '',
                    userNotRegistered: false,
                })
            } else {
                this.setState({
                    validEmail: false,
                })
            }
        }
    }

    componentDidUpdate = (prevProps, prevState) => {
        if(prevProps.forgotPassword.response !== this.props.forgotPassword.response) {
            if(this.props.forgotPassword.response && this.props.forgotPassword.response.data && this.props.forgotPassword.response.data.error) {
                if(this.props.forgotPassword.response.data.error === "Unauthorized") {
                    this.setState({
                        userNotRegistered: true,
                        processing: false,
                    })
                }
            }
        }
    }

    onClickShowPassword = () => {
        this.setState({
            isHiddenPassword: !this.state.isHiddenPassword
        })
    }

    render = () => {
        const { emptyFields, validEmail, userNotRegistered, processing, successRestore } = this.state;

        let errorMsg = emptyFields ? 'This field is required' : '';
        let errorAuth = userNotRegistered ? 'No such email' : '';

        return (
            <>
                <Header />
                <h1>{successRestore ? 'Thank you!' : 'Password recovery'}</h1>
                <p className={styles.passwordRecoveryDescr}>
                    {successRestore ?
                        'We have sent password reset instructions to your email address. Follow the directions to reset password.' :
                        'To reset your password, please enter the email address of your TimePal account.'}
                </p>
                {!successRestore &&
                <form
                    onSubmit={this.onSubmitForgotPassword}
                >
                    <input
                        className={
                            emptyFields === true && this.state.email.length === 0 ||
                            userNotRegistered || !validEmail ? 'danger' : ''
                        }
                        type={"email"}
                        placeholder={"Your email"}
                        value={this.state.email}
                        onChange={(e)=>this.setState({email: e.target.value, userNotRegistered: false})} />
                    <button
                        className={`${styles.signInButton}
                                        ${processing ? 'processing' : ''}`}
                    >
                        <span>{processing ? 'Processing...' : 'Reset password'}</span>
                    </button>
                </form>
                }
                <div className={styles.authLine} />

                <Footer />
            </>
        )
    }
}

const mapDispatchToProps = dispatch => ({
    forgotPasswordAction: (params) => dispatch(auth['forgotPassword'](params))
})

const mapStateToProps = state => {return {forgotPassword: state['auth']['forgotPassword']}}

export default connect(mapStateToProps, mapDispatchToProps)(withSignIn(withRouter(ForgotPassword)))

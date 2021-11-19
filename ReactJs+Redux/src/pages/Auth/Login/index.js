import React, { Component } from 'react'
import { connect } from 'react-redux'
import { Link, withRouter } from 'react-router-dom'
import { Redirect } from 'react-router';

import { auth } from '../../../actions'
import classnames from 'classnames'
import styles from './styles.scss'
import { withSignIn } from 'react-auth-kit'
import {withIsAuthenticated} from 'react-auth-kit';
import Header from '../../../components/Header'
import Footer from '../../../components/Footer';

class Login extends React.Component {

    state = {
        email: '',
        password: '',
        error: false,
        emptyFields: false,
    }

    onSubmit = (e) => {
        e.preventDefault()
        if(this.state.email.length === 0 || this.state.password.length === 0) {
            this.setState({emptyFields: true});
        } else {
            this.props.loginAction({
                email: this.state.email,
                password: this.state.password
            })
        }
    }

    componentDidUpdate = (prevProps, prevState) => {
        if(prevProps.login !== this.props.login) {
            if(this.props.login.response && Object.keys(this.props.login.response).length > 0) {
                if(this.props.login.response.data.error && this.props.login.response.data.error.length > 0) {
                    return this.setState({
                        ...this.state,
                        error: true
                    })
                }
            }
        }

        if(this.props.login.data && this.props.login.data.access_token && !prevState.login){

            if(this.props.signIn({
                token: this.props.login.data.access_token,
                expiresIn: this.props.login.data.expires_in,
                tokenType: "Bearer",
                authState: this.props.login.data.user
            })){
                window.location.href = "/";
            }
            //return {
            //    login: this.props.login,
            //}
        }
    }

    render = () => {
        const {error, emptyFields} = this.state;
        let msg = error ? 'Login or password entered incorrectly': emptyFields ? `All fields must be filled!`: '';
        let classForText = error ? styles.danger: emptyFields ? styles.danger: '';

        if(this.props.isAuth){
            return (
                <Redirect to='/' />
            )
        }

        return (
            <>
                <Header />
                {msg ? <p className={classForText}>{msg}</p> : ''}
                <form onSubmit={this.onSubmit}>
                    <input type={"email"} placeholder={"email"} onChange={(e)=>this.setState({email: e.target.value})}/>
                    <input type={"password"} placeholder={"password"} onChange={(e)=>this.setState({password: e.target.value})}/>
                    <button>Login</button>
                </form>
                <Link to={`/forgot-password`}>Forgot password</Link>

                <Footer />
            </>
        )
    }
}

const mapDispatchToProps = dispatch => ({
    loginAction: (params) => dispatch(auth['login'](params))
})

const mapStateToProps = state => {return {login: state['auth']['login']}}

export default connect(mapStateToProps, mapDispatchToProps)(withIsAuthenticated(withSignIn(withRouter(Login))))

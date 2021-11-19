import React, { Component } from 'react'
import { connect } from 'react-redux'
import { Link, withRouter, Redirect } from 'react-router-dom'
import { auth } from '../../../actions'
import classnames from 'classnames'
import styles from './styles.scss'
import { withSignIn, withIsAuthenticated } from 'react-auth-kit'
import Header from '../../../components/Header'
import Footer from '../../../components/Footer'

class Register extends React.Component {

    state = {
        name: '',
        email: '',
        password: '',
        error: false,
        emptyFields: false,
    }

    onSubmit = (e) => {
        e.preventDefault()
        if(this.state.name.length === 0 || this.state.password.length === 0 || this.state.email.length === 0) {
            this.setState({emptyFields: true});
        } else {
            this.props.registerAction({
                name: this.state.name,
                email: this.state.email,
                password: this.state.password
            })
        }
    }

    componentDidUpdate = (prevProps, prevState) => {
        if(prevProps.register !== this.props.register) {
            if(this.props.register.data && Object.keys(this.props.register.data).length > 0) {
                if(this.props.register.data.message && this.props.register.data.message.length > 0) {
                    if(this.props.register.data.message === "Successfully registration!") {
                        this.props.loginAction({
                            email: this.state.email,
                            password: this.state.password
                        })
                        console.log("login login login");
                        this.setState({
                            ...this.state,
                            name: '',
                            email: '',
                            password: '',
                        })
                    }
                }
            } else if(this.props.register.response.data && Object.keys(this.props.register.response.data).length > 0) {
                if(this.props.register.response.data.message && this.props.register.response.data.message.length > 0) {
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
            return {
                login: this.props.login,
            }
        }
    }

    render = () => {
        const {error, emptyFields} = this.state;
        let msg = error ? `Something went wrong, please try again later...`: emptyFields ? `All fields must be filled!`: '';
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
                        <input type={"name"} placeholder={"name"} onChange={(e)=>this.setState({name: e.target.value})} value={this.state.name}/>
                        <input type={"email"} placeholder={"email"} onChange={(e)=>this.setState({email: e.target.value})} value={this.state.email}/>
                        <input type={"password"} placeholder={"password"} onChange={(e)=>this.setState({password: e.target.value})} value={this.state.password}/>
                        <button>Register</button>
                    </form>
                <Footer />
            </>
        )
    }
}

const mapDispatchToProps = dispatch => ({
    registerAction: (params) => dispatch(auth['register'](params)),
    loginAction: (params) => dispatch(auth['login'](params))
})

const mapStateToProps = state => {
    return {
        register: state['auth']['register'],
        login: state['auth']['login']
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(withIsAuthenticated(withSignIn(withRouter(Register))))
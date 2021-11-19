import React, { Component } from 'react'
import { connect } from 'react-redux'
import { Link, withRouter } from 'react-router-dom'
import { Redirect } from 'react-router';
import { auth } from '../../actions'
import classnames from 'classnames'
import styles from './styles.scss'
import {withIsAuthenticated, withAuthUser, withSignOut} from 'react-auth-kit';
import TimePalAvatar from '../TimePalAvatar';

class Header extends React.Component {


    state = {
    }

    render = () => {

        if(this.props.isAuth){

            const { name } = this.props.authState;

            return (
                <header>
                    <ul>
                        <li>
                            <Link to={{pathname: "/"}}>Home</Link>
                        </li>
                        <li>
                            <Link to={{pathname: "/dashboard"}}>Dashboard</Link>
                        </li>
                        <li>
                            <Link to={{pathname: "/feeds"}}>News</Link>
                        </li>
                        <li>
                            {this.props.authState.name}
                        </li>
                    </ul>
                    <ul>
                        <li className={styles.avatar}>
                            <TimePalAvatar pathAvatar={this.props.authState.avatar_path} userName={name} />
                        </li>
                        <li>
                            <button onClick={() => this.props.signOut()}>Logout</button>
                        </li>
                    </ul>
                </header>
            )
        }

        return (
            <>
                <header>
                    <ul>
                        <li>
                            <Link to={{pathname: "/"}}>Home</Link>
                        </li>
                        <li>
                            <Link to={{pathname: "/feeds"}}>News</Link>
                        </li>
                        <li>
                            <Link to={{pathname: "/login"}}>Login</Link>
                        </li>
                        <li>
                            <Link to={{pathname: "/register"}}>Register</Link>
                        </li>
                    </ul>
                </header>
            </>
        )
    }
}

const mapDispatchToProps = dispatch => ({
})

//const mapStateToProps = state => {return {login: state['auth']['login']}}

export default connect(null, mapDispatchToProps)(withSignOut(withAuthUser(withIsAuthenticated(withRouter(Header)))))

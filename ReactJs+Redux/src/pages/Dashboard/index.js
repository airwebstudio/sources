import React, { Component } from 'react'
import { connect } from 'react-redux'
import { Link, withRouter } from 'react-router-dom'
//import { demo } from '../../../actions'
import classnames from 'classnames'
import styles from './styles.scss'
import Header from '../../components/Header'
import Footer from '../../components/Footer'
import {withIsAuthenticated, withAuthUser} from 'react-auth-kit';

class Dashboard extends React.Component {

    state = {

    }


    render = () => {

        return (
            <>
                <Header />
                    <h1>Hello, {this.props.authState.name} / {this.props.authState.email}</h1>
                    <Link to={`/my-chats`}>My chats</Link>
                    <Link to={`/stripe/wallet`}>My wallet</Link>
                    <Link to={`/meetings`}>My meetings</Link>
                    <h3>Who are YOU:</h3>
                    <ul>
                        <li>
                            <Link to={{pathname: "/dashboard/seller"}}>seller</Link>
                        </li>
                        <li>
                            <Link to={{pathname: "/dashboard/buyer"}}>buyer</Link>
                        </li>
                    </ul>
                <Footer />
            </>
        )        
    }
}

/*
const mapDispatchToProps = dispatch => ({
    fetch: (page, limit) => dispatch(demo['list'](page, limit))
})

const mapStateToProps = state => {return {list: state['demo']['list']}}
*/
export default connect(null, null)(withRouter(withAuthUser(withIsAuthenticated(Dashboard))))

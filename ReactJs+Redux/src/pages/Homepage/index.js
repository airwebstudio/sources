import React, { Component } from 'react'
import { connect } from 'react-redux'
import { Link, withRouter } from 'react-router-dom'
import { seller, meeting } from '../../actions'
import classnames from 'classnames'
import styles from './styles.scss'
import Header from '../../components/Header'
import Footer from '../../components/Footer'
import TimePalAvatar from '../../components/TimePalAvatar'
import {withIsAuthenticated, withAuthUser} from 'react-auth-kit';
import TimePalPagination from '../../components/Pagination'

class Homepage extends React.Component {

    state = {

    }

    componentDidMount(){
        this.props.catalogAction()
        this.props.upcomingEventsAction()
    }

    render = () => {

        const { catalog } = this.props.seller;
        const { meetings } = this.props.meeting;

        console.log(this.props)

        if(this.props.isAuth) {

            return (
                <>
                    <Header />

                    <h1>Hello, {this.props.authState.name}</h1>
                    <div className={styles.wrapper}>
                        <div className={styles.content}>
                            {catalog.data && 
                                <>
                                    <p>List of sellers</p>
                                    <ul>
                                    {catalog.data.map(seller => (
                                        <li className={styles.listItem} key={seller.id}>
                                            <Link className={styles.userItem} to={'seller/' + seller.id}>
                                                <TimePalAvatar pathAvatar={seller.avatar_path} userName={seller.name} />
                                                {seller.email}<br />
                                                {seller.seller.description}<br />
                                                Price: {seller.seller.price}€
                                            </Link>
                                        </li>
                                    ))}
                                    </ul>
                                    {catalog.links && catalog.links.length > 0 &&
                                        <TimePalPagination catalogLinks={catalog.links} actionPage={this.props.catalogAction} />
                                    }
                                </>
                            }
                        </div>
                        {meetings && meetings.length > 0 &&
                            <aside className={styles.uppcomingMeetings}>
                                <div className={styles.uppcomingMeetingsBlock}>
                                    <h3>Upcoming Events</h3>
                                    <ul>
                                        {meetings.map(meet => (
                                        <li className={styles.listItem} key={meet.id}>
                                                {meet.name}<br />
                                                {meet.description}<br />
                                                {meet.starting_at}
                                            <Link className={styles.userItem} to={'/seller/' + meet.user_id}>
                                                User {meet.user_id}
                                            </Link>
                                        </li>
                                        ))}
                                    </ul>
                                </div>
                            </aside>
                        }
                    </div>

                    <Footer />
                </>
            )
        }

        return (
            <>
                <Header />

                <h1>Hello, greetings from SELLTIME HOMEPAGE</h1>
                {catalog.data &&
                    <>
                        <p>List of sellers</p>
                        <ul>
                        {catalog.data.map(seller => (
                            <li className={styles.listItem} key={seller.id}>
                                <Link className={styles.userItem} to={'seller/' + seller.id}>
                                    <TimePalAvatar pathAvatar={seller.avatar_path} userName={seller.name} />
                                    {seller.email}<br />
                                    {seller.seller.description}<br />
                                    Price: {seller.seller.price}€
                                </Link>
                            </li>
                        ))}
                        </ul>

                        {catalog.links && catalog.links.length > 0 &&
                            <TimePalPagination catalogLinks={catalog.links} actionPage={this.props.catalogAction} />
                        }
                    </>
                }

                <Footer />
            </>
        )
    }
}

const mapDispatchToProps = dispatch => ({
    catalogAction: (search) => dispatch(seller.catalog(search)),
    upcomingEventsAction: () => dispatch(meeting.upcomingEvents()),
})

const mapStateToProps = state => {
    return {
        seller: state['seller'],
        meeting: state['meeting']['upcomingEvents'],
    }
    
}

export default connect(mapStateToProps, mapDispatchToProps)(withRouter(withAuthUser(withIsAuthenticated(Homepage))))

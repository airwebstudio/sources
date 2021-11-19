import React, { Component } from 'react';
import { connect } from 'react-redux'
import { feed } from '../../actions'
import { BrowserRouter as Router, Route, withRouter, Link } from 'react-router-dom';
import { withAuthUser } from 'react-auth-kit';
import styles from './styles.scss'
import Header from '../../components/Header';
import Footer from '../../components/Footer';
import TimePalPagination from '../../components/Pagination';
import draftToHtml from 'draftjs-to-html';

class Feeds extends Component {

    state = {
       
    }

    componentDidMount = () => {
        this.props.getPublicUsersFeedsAction()
    }

    componentDidUpdate(prevProps) {
        
    }

    render = () => {

        const { feeds } = this.props;
        
        return (
            <>
                <Header />
                    <h1>Last News</h1>
                    {feeds.data && feeds.data.length === 0 && <h2>No news...</h2>}

                    {feeds.data && feeds.data.length > 0 &&
                        <>
                            <ul>
                            {feeds.data.map(feed => (
                                <li className={styles.listItem} key={feed.id}>
                                    <h2>{feed.title}</h2>
                                    <div dangerouslySetInnerHTML={{__html: draftToHtml(JSON.parse(feed.description))}}></div>
                                    <p><b>Created:</b> {feed.created_at} by <b>USER:</b><Link to={`/seller/${feed.user_id}`}>{feed.user_id}</Link></p>
                                </li>
                            ))}
                            </ul>
                            {feeds.links && feeds.links.length > 0 && feeds.last_page > 1 &&
                                <TimePalPagination catalogLinks={feeds.links} actionPage={this.props.getPublicUsersFeedsAction} />
                            }
                        </>
                    }
                <Footer />
            </>
        )
    }
}

const mapDispatchToProps = dispatch => ({
    getPublicUsersFeedsAction: () => dispatch(feed['getPublicUsersFeeds']())
})

const mapStateToProps = state => {
    return {feeds: state['feed']['getPublicUsersFeeds']}
}

export default connect(mapStateToProps, mapDispatchToProps)(withAuthUser(withRouter(Feeds)))

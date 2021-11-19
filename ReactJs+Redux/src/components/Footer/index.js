import React, { Component } from 'react';
import { BrowserRouter as Router, Route, withRouter, Link } from 'react-router-dom';

class Footer extends Component {
   
    render = () => {
        return (
            <>
                <footer>
                    <ul>
                        <li>
                            <Link to={{pathname: "/page/about"}}>About</Link>
                        </li>
                        <li>
                            <Link to={{pathname: "/page/terms"}}>Terms</Link>
                        </li>
                        <li>
                            <Link to={{pathname: "/page/faq"}}>Faq</Link>
                        </li>
                    </ul>
                </footer>
            </>
        )
    }
}

export default withRouter(Footer);
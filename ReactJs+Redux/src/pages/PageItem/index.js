import React, { Component } from 'react';
import { connect } from 'react-redux'
import { pages } from '../../actions'
import { BrowserRouter as Router, Route, withRouter, Link } from 'react-router-dom';
import Header from '../../components/Header';
import Footer from '../../components/Footer';

class PageItem extends Component {

    state = {
       
    }

    componentDidMount = () => {
        this.updateItem();
    }

    componentDidUpdate(prevProps) {
        if (this.props.url !== prevProps.url) {
            this.updateItem();
        }
    }

    updateItem() {
        let {url} = this.props;
        if (!url) return;

        this.props.viewAction(url)
    }

    render = () => {
        
        return (
            <>
                <Header />
                    <h1>{this.props.view.title}</h1>
                    <h2>{this.props.view.description}</h2>
                    <div dangerouslySetInnerHTML={{__html: this.props.view.content}}></div>
                <Footer />
            </>
        )
    }
}

const mapDispatchToProps = dispatch => ({
    viewAction: (url) => dispatch(pages['view'](url))
})

const mapStateToProps = state => {
    return {view: state['pages']['view']}
}

export default connect(mapStateToProps, mapDispatchToProps)(PageItem);

import React, { Component } from 'react';
import { connect } from 'react-redux'
import { feed } from '../../../actions'
import { BrowserRouter as Router, Route, withRouter, Link } from 'react-router-dom';
import { withAuthUser } from 'react-auth-kit';
import styles from './styles.scss'
import Header from '../../../components/Header';
import Footer from '../../../components/Footer';
import { Editor } from 'react-draft-wysiwyg';
import { EditorState, convertToRaw } from 'draft-js';
import { convertFromRaw } from 'draft-js';
import 'react-draft-wysiwyg/dist/react-draft-wysiwyg.css';

class FeedUpdate extends Component {

    state = {
        editorState: EditorState.createEmpty(),
        title: '',
        description: '',
        isActive: false,
        emptyFields: '',
        successUpdateNews: '',
        isStateWithData: false,
    }

    componentDidMount = () => {
        const { id } = this.props;
        if (!id) return;

        this.props.getFeedAction(id)
    }

    componentDidUpdate(prevProps) {
        if(prevProps.feed.getFeed !== this.props.feed.getFeed) {
            this.setState({
                ...this.state,
                title: this.props.feed.getFeed.data.title,
                isActive: this.props.feed.getFeed.data.is_active,
                editorState: EditorState.createWithContent(convertFromRaw(JSON.parse(this.props.feed.getFeed.data.description))),
                isStateWithData: true,
                description: this.props.feed.getFeed.data.description,
            })
        }

        if(prevProps.feed.updateFeed !== this.props.feed.updateFeed) {
            if(this.props.feed.updateFeed.message === 'Successfully updatet!') {
                this.props.history.push('/dashboard/seller');
            }
        }
    }

    onEditorStateChange = (editorState) => {
        this.setState({
            ...this.state,
            editorState: editorState,
            description: JSON.stringify(convertToRaw(editorState.getCurrentContent()))
        });
    };

    activeChange = (e) => {
        this.setState({
            ...this.state,
            isActive: e.target.checked,
        })
    }

    onSubmit = (e) => {
        e.preventDefault();
        const { title, description } = this.state;
        if(title.length === 0 || description.length === 0) {
            return this.setState({
                ...this.state,
                emptyFields: 'All fields must be filled',
            })
        }

        this.props.updateFeedAction(this.props.id, {
            title: title,
            description: description,
            is_active: this.state.isActive,
        });

        this.setState({
            title: '',
            description: '',
            isActive: false,
            emptyFields: '',
            editorState: EditorState.createEmpty(),
        });
    }

    render = () => {
        const { emptyFields, editorState, isStateWithData } = this.state;
        
        return (
            <>
                <Header />
                    <h1>Edit News</h1>
                    {emptyFields.length > 0 && <p className={styles.danger}>{emptyFields}</p>}
                    {/* {successCreateNews.length > 0 && <p className={styles.success}>{successCreateNews}</p>} */}
                    {isStateWithData &&
                        <form 
                            onSubmit={this.onSubmit}
                            className={styles.mainForm}>
                            <input type={"text"} placeholder={"Title"} value={this.state.title} onChange={(e)=>this.setState({title: e.target.value})}/>
                            <Editor
                                editorState={editorState}
                                wrapperClassName="demo-wrapper"
                                editorClassName={styles.editorArea}
                                onEditorStateChange={this.onEditorStateChange}
                            />
                            <label>
                                <input
                                    type="checkbox"
                                    checked={this.state.isActive}
                                    className="privateInput"
                                    onChange={this.activeChange}
                                />
                                Publish
                            </label>
                            <button>Update News</button>
                        </form>
                    }
                <Footer />
            </>
        )
    }
}

const mapDispatchToProps = dispatch => ({
    getFeedAction: id => dispatch(feed['getFeed'](id)),
    updateFeedAction: (id, params) => dispatch(feed['updateFeed'](id, params))
})

const mapStateToProps = state => {
    return {feed: state['feed']}
}

export default connect(mapStateToProps, mapDispatchToProps)(withAuthUser(withRouter(FeedUpdate)))
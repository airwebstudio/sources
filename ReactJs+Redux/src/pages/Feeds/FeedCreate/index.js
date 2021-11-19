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
import 'react-draft-wysiwyg/dist/react-draft-wysiwyg.css';

class FeedCreate extends Component {

    state = {
        editorState: EditorState.createEmpty(),
        title: '',
        description: '',
        isActive: false,
        emptyFields: '',
        successCreateNews: '',
    }

    componentDidMount = () => {
        console.log(this.props)
    }

    componentDidUpdate(prevProps) {
        if(prevProps.feed !== this.props.feed) {
        
            if(this.props.feed.message === 'User Feed Item successfully created!') {
                this.setState({
                    ...this.state,
                    successCreateNews: this.props.feed.message,
                })
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

        this.props.createFeedAction({
            title: title,
            description: description,
            is_active: this.state.isActive,
        });

        this.setState({
            title: '',
            description: '',
            isActive: false,
            emptyFields: '',
        })
    }

    render = () => {
        const { emptyFields, successCreateNews, editorState } = this.state;

        // console.log(this.props)
        
        return (
            <>
                <Header />
                    <h1>Create News</h1>
                    {emptyFields.length > 0 && <p className={styles.danger}>{emptyFields}</p>}
                    {successCreateNews && successCreateNews.length > 0 && <p className={styles.success}>{successCreateNews}</p>}
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
                        <button>Publish News</button>
                    </form>
                <Footer />
            </>
        )
    }
}

const mapDispatchToProps = dispatch => ({
    createFeedAction: params => dispatch(feed['createFeed'](params))
})

const mapStateToProps = state => {
    return {feed: state['feed']['createFeed']}
}

export default connect(mapStateToProps, mapDispatchToProps)(withAuthUser(withRouter(FeedCreate)))
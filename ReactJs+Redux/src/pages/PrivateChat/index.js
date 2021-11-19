import React, { Component } from 'react';
import { connect } from 'react-redux'
import {chat, pages} from '../../actions'
import Header from '../../components/Header';
import Footer from '../../components/Footer';
import {io} from "socket.io-client";
import Cookie from "js-cookie";
import styles from './styles.scss'
import {withAuthUser, withIsAuthenticated} from "react-auth-kit";

const { privateChatEvent } = require("../../../../shared/constants")

const socketChat = io(`${process.env.REACT_APP_CHAT_SERVER_HOST}/chat`, {autoConnect:false});

class PrivateChat extends Component {

    state = {
        userId: null,
        toUser: {},
        messages: [],
        files: [],
        message: ''
    }

    componentDidMount = () => {
        if(!this.props.authState){
            this.props.setUserInfoAction({isAuth: false})
            return
        }
        
        let userInfo = {
            username: this.props.authState.name,
            userID: this.props.authState.id.toString(),
            toUserID: this.props.match.params.id
        }
        this.setState({userId: this.props.authState.id.toString()})

        socketChat.auth = {...userInfo, ...this.props.authState}
        socketChat.connect()


        socketChat.on(privateChatEvent.companion, (user) => {
            this.setState({toUser: Object.keys(user).length !== 0 ? user : null})
        })

        socketChat.on(privateChatEvent.allMessages, (messages) => {
            this.setState({messages: messages.map(message => {
                const {content, from, file} = message
                const fromSelf = from === this.state.userId
                let res = {
                    content,
                    fromSelf
                }
                if(file){
                    res.file = file
                }
                return res
            })})
        })

        socketChat.on(privateChatEvent.newMessage, (message) => {
            const {content, from, file} = message
            const fromSelf = from === this.state.userId
            this.setState({messages: this.state.messages.concat({
                    content,
                    fromSelf,
                    file
                })})
        })

        socketChat.on(privateChatEvent.getAllFiles, (files) => {
            this.setState({files: files})
        })

        socketChat.on(privateChatEvent.fileUpload, (file) => {
            this.setState({files: [...this.state.files, file] })
        })
    }

    componentWillUnmount = () => {
        socketChat.off(privateChatEvent.companion);
        socketChat.off(privateChatEvent.allMessages);
        socketChat.off(privateChatEvent.newMessage);
        socketChat.close()
    }

    sendMessage = (content) => {
        if (this.state.message) {
            socketChat.emit(privateChatEvent.sendMessage, content);
            this.setState({
                message: ''
            })
        }
    }

    uploadFiles = async (event) => {
        const token = Cookie.get('_auth_t')

        let formData = new FormData();
        formData.append("uploads", event.target.files[0]);


        await this.props.uploadFileAction(formData)
        let file = {...this.props.uploadedFile}
        file.name = event.target.files[0].name;
        file.type = event.target.files[0].type;

        socketChat.emit(privateChatEvent.fileUpload, file, token)
    }

    render = () => {
        if(!this.state.toUser){
            return (
                <>
                    <Header />
                    <h2>User not found</h2>
                    <Footer />
                </>
            )
        }
        return (
            <>
                <Header />
                <h1>PrivateChat with {this.state.toUser.name}</h1>
                <div id={"messages"}>
                    {this.state.messages.map((message, index) => (
                        <div key={index}>
                            {!message.file && (
                                <b style={{marginRight:'10px'}}>
                                    { message.fromSelf ? "(yourself)" : this.state.toUser.name }:
                                </b>
                            )}
                            {message.content}
                            {message.file && (
                                <a href={`${process.env.REACT_APP_STORAGE_HOST}/${message.file.file}`} target={"_blank"}>{message.file.name}</a>
                            )}
                        </div>
                    ))}
                </div>
                <form>
                    <input type="text" name="message" placeholder="Your message..." value={this.state.message} onChange={($event) => this.setState({message: $event.target.value})}  />
                    <button onClick={(e) => {
                        e.stopPropagation();
                        e.preventDefault()
                        this.sendMessage(this.state.message)
                    }}>Select</button>
                </form>

                <input type="file" onChange={this.uploadFiles}/>
                <div>
                    {this.state.files.map(file => (
                        <div key={file.url}>
                            <a href={`${process.env.REACT_APP_STORAGE_HOST}/${file.url}`} target={"_blank"}>{file.name}</a>
                        </div>
                    ))}
                </div>

                <Footer />
            </>
        )
    }
}

const mapDispatchToProps = dispatch => ({
    viewAction: (url) => dispatch(pages['view'](url)),
    uploadFileAction: (formData) => dispatch(chat['uploadFile'](formData))
})

const mapStateToProps = state => {
    return {
        view: state['pages']['view'],
        uploadedFile: state['chat']['uploadedFile']
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(withAuthUser(withIsAuthenticated(PrivateChat)));

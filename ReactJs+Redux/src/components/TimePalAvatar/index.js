import React, { Component } from 'react';
import styles from './styles.scss'

export default class TimePalAvatar extends Component {

    state = {
        path: null,
        name: '',
    }

    componentDidMount = () => {
        this.setState({
            path: this.props.pathAvatar,
            name: this.props.userName,
        });
    }

    componentDidUpdate = (prevProps) => {
        if(prevProps.pathAvatar !== this.props.pathAvatar) {
            this.setState({
                path: this.props.pathAvatar,
                name: this.props.userName,
            });
        }
    }

    render() {

        const {path, name} = this.state;

        return (
            path === null ? 
                <div className={styles.letterName}>{name[0]}</div> : 
                <div className={styles.avatarWrap}>
                    <img src={process.env.REACT_APP_STORAGE_HOST + path} />
                </div>
        );
    }
}
import React, { Component } from 'react'
import { connect } from 'react-redux'
import { buyer } from '../../actions'
import classnames from 'classnames'
import styles from './styles.scss'
import Dropzone from 'react-dropzone'
import axios from 'axios'

class TimePalAvatarUpload extends React.Component {

    state = {
        percentage: 0,
    }

    acceptedFiles = (imagefile) => {
        var formData = new FormData();
        formData.append("uploads", imagefile[0]);
        let props = [];
        const authHeader = window.getCookie('_auth_t')
        props['headers'] = {
            'Content-Type': 'multipart/form-data',
            'Authorization': 'Bearer '+ authHeader
        }

        const options = {
            headers: props['headers'],
            onUploadProgress: (progressEvent) => {
                const { loaded, total } = progressEvent;
                let percent = Math.floor( (loaded * 100) / total );

                if(percent < 100) {
                    this.setState({
                        percentage: percent
                    })
                }
            }
        }

        axios.post(`${process.env.REACT_APP_API_ADMIN_HOST}/api/avatar/upload`, formData, options)
            .then(res => {
                this.setState({ percentage: 100 }, () => {
                    setTimeout(() => {
                        this.setState({ percentage: 0 })
                        this.props.infoAction()
                    }, 1000);
                })
            })
    }

    render = () => {

        const { percentage } = this.state;
        
        return (
            <>
                {percentage > 0 && <p className={styles.progressBar}>
                    <span style={{ width: `${percentage}%` }}>{percentage}%</span>
                </p>}
                <Dropzone onDrop={this.acceptedFiles}>
                    {({getRootProps, getInputProps}) => (
                        <section>
                        <div {...getRootProps()} className={`${styles.inputDrop} ${styles.mainWraper}`}>
                            <input className={styles.inputDrop} id="file" {...getInputProps()} />
                            <label htmlFor="file">Upload your Avatar: </label>
                            <p className={styles.dropzone}>Drag 'n' drop some files here, or click to select files</p>
                        </div>
                        </section>
                    )}
                </Dropzone>
            </>
        )
    }
}

const mapDispatchToProps = dispatch => ({
    infoAction: () => dispatch(buyer['info']()),
})

const mapStateToProps = state => {return {
    buyer: state['buyer'],
}}
export default connect(mapStateToProps, mapDispatchToProps)(TimePalAvatarUpload)

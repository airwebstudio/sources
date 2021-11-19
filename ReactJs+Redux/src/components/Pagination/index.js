import React, { Component } from 'react';
import styles from './styles.css'

export default class TimePalPagination extends Component {

    state = {
        catalogLinks: [],
        actionPage: null
    }

    componentDidMount() {
        this.setState({
            catalogLinks: this.props.catalogLinks,
            actionPage: this.props.actionPage,
        });
    }

    componentDidUpdate(prevProps) {
        if(this.props.catalogLinks !== prevProps.catalogLinks) {
            this.setState({
                catalogLinks: this.props.catalogLinks,
            });
        }
    }

    getPage = page => {
        this.state.actionPage(page)
    }

    render() {

        const { catalogLinks } = this.state;

        return (
            <ul className={styles.pagination}>
                {catalogLinks && catalogLinks.map((link, idx) => (
                    <li key={idx}>
                        <button
                            disabled={link.url===null ? true : false }
                            className={link.active===true ? styles.active : ''}
                            onClick={() => {
                                this.getPage(link.url===null ? 1 : link.url.split('page=')[1])
                            }}
                            dangerouslySetInnerHTML={{__html: link.label}}
                        >
                        </button>
                    </li>
                ))}
            </ul>
        );
    }
}

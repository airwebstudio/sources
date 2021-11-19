import { api } from './api'

const view = payload => ({type: 'PAGE_VIEW', payload: payload})

export const pages = {
    view: url => api({
        url: url,
        method: "GET",
    }, response => view(response.data))
}
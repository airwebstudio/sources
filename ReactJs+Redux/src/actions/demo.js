import { api } from './api'

const add = payload => ({type: 'DEMO_ADD', payload: payload})
const view = payload => ({type: 'DEMO_VIEW', payload: payload})
const list = payload => ({type: 'DEMO_LIST', payload: payload})

export const demo = {
    add: transaction => api({
        url: "/transaction",
        method: "PUT",
        data: transaction,
    }, response => add(response.data)),

    view: id => api({
        url: "/transaction/" + id,
        method: "GET",
    }, response => view(response.data)),

    list: (page, limit) => api({
        url: `/transactions`, ///${page}/${limit}
        method: "GET",
    }, response => list(response.data))
}

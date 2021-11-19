import { privateApi } from './api'

const myOwn = payload => ({type: 'PROPOSALS_MY_OWN', payload: payload})
const myRequests = payload => ({type: 'PROPOSALS_MY_REQUESTS', payload: payload})
const addNew = payload => ({type: 'PROPOSAL_ADD_NEW', payload: payload})
const approve = payload => ({type: 'PROPOSAL_APPROVE', payload: payload})
const decline = payload => ({type: 'PROPOSAL_DECLINE', payload: payload})

export const proposal = {
    myOwn: filter => privateApi({
        url: "/proposal/my/own",
        method: "POST",
        data: filter
    }, response => myOwn(response.data)),

    myRequests: filter => privateApi({
        url: "/proposal/my/requests",
        method: "POST",
        data: filter
    }, response => myRequests(response.data)),

    addNew: (params) => privateApi({
        url: `/proposal`,
        method: "PUT",
        data: params,
    }, response => addNew(response.data)),

    approve: (id) => privateApi({
        url: `/proposal/${id}`,
        method: "POST",
        data: {},
    }, response => approve(response.data)),

    decline: (id) => privateApi({
        url: `/proposal/${id}`,
        method: "DELETE"
    }, response => decline(response.data)),
}

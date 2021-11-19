import { createReducer } from '@reduxjs/toolkit'
import { combineReducers } from '@reduxjs/toolkit'

export const proposal = combineReducers({
    myOwn: createReducer([], { 'PROPOSALS_MY_OWN': (state, action) => action.payload }),
    myRequests: createReducer([], { 'PROPOSALS_MY_REQUESTS': (state, action) => action.payload }),
    addNew: createReducer([], { 'PROPOSAL_ADD_NEW': (state, action) => action.payload }),
    approve: createReducer([], { 'PROPOSAL_APPROVE': (state, action) => action.payload }),
    decline: createReducer([], { 'PROPOSAL_DECLINE': (state, action) => action.payload }),
})

export const proposalPreloadedState = {
    myOwn: {},
    myRequests: {},
    addNew: {},
    approve: {},
    decline: {}
}

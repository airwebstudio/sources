import { createReducer } from '@reduxjs/toolkit'
import { combineReducers } from '@reduxjs/toolkit'

export const stripe = combineReducers({
    balance: createReducer([], { 'STRIPE_BALANCE': (state, action) => action.payload }),
    payment_status: createReducer([], { 'STRIPE_PAYMENT_STATUS': (state, action) => action.payload }),
    payment: createReducer([], { 'STRIPE_PAYMENT': (state, action) => action.payload }),
    history: createReducer([], { 'STRIPE_HISTORY': (state, action) => action.payload }),
    payout: createReducer([], { 'STRIPE_PAYOUT': (state, action) => action.payload }),

    account: createReducer([], { 'STRIPE_ACC': (state, action) => action.payload }),
    stripe_link: createReducer([], { 'STRIPE_LINK': (state, action) => action.payload }),
    stripe_retrieve: createReducer([], { 'STRIPE_RETRIEVE': (state, action) => action.payload }),

})

export const stripePreloadedState = {

}
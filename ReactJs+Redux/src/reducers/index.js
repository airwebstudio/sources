import { combineReducers, createReducer } from '@reduxjs/toolkit'
import { demo } from './demo'
import { paypal } from './paypal'
import { meeting, meetPreloadedState } from './meeting'
import { proposal, proposalPreloadedState } from './proposal'
import { seller, sellerPreloadedState } from './seller'
import { auth, authPreloadedState } from './auth'
import { buyer, buyerPreloadedState } from './buyer'
import { pages, pagesPreloadedState } from './pages'
import { chat, chatPreloadedState } from './chat'
import { feed, feedPreloadedState } from './feed'
import { stripe, stripePreloadedState } from './stripe'

export const root = combineReducers({
    demo: demo,
    paypal: paypal,
    seller: seller,
    buyer: buyer,
    auth: auth,
    pages: pages,
    meeting: meeting,
    proposal: proposal,
    chat: chat,
    feed: feed,
    stripe: stripe,
})

export const preloadedState = {
    demo: Object.assign({}, {
        list: [],
        view: {},
        add: {}
    }),
    paypal: Object.assign({}, {
        success: {},
        cancel: {}
    }),
    seller: Object.assign({}, sellerPreloadedState),
    buyer: Object.assign({}, buyerPreloadedState),
    auth: Object.assign({}, authPreloadedState),
    pages: Object.assign({}, pagesPreloadedState),
    meeting: Object.assign({}, meetPreloadedState),
    proposal: Object.assign({}, proposalPreloadedState),
    chat: Object.assign({}, chatPreloadedState),
    feed: Object.assign({}, feedPreloadedState),
}

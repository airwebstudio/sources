import { createReducer } from '@reduxjs/toolkit'
import { combineReducers } from '@reduxjs/toolkit'

export const feed = combineReducers({
    createFeed: createReducer([], { 'FEED_CREATE': (state, action) => action.payload }),
    getFeed: createReducer([], { 'FEED_GET': (state, action) => action.payload }),
    updateFeed: createReducer([], { 'FEED_UPDATE': (state, action) => action.payload }),
    removeFeed: createReducer([], { 'FEED_REMOVE': (state, action) => action.payload }),
    getUserFeeds: createReducer([], { 'FEED_GET_USER_FEEDS': (state, action) => action.payload }),
    getPublicUsersFeeds: createReducer([], { 'FEED_GET_USERS_FEEDS': (state, action) => action.payload }),
})

export const feedPreloadedState = {
    createFeed: {},
    getFeed: {},
    updateFeed: {},
    removeFeed: {},
    getUserFeeds: {},
    getPublicUsersFeeds: {},
}

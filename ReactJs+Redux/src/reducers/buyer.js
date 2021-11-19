import { createReducer } from '@reduxjs/toolkit'
import { combineReducers } from '@reduxjs/toolkit'

export const buyer = combineReducers({
    settings: createReducer([], { 'BUYER_SETTINGS': (state, action) => action.payload }),
    avatarGet: createReducer([], { 'BUYER_SETTINGS_AVATAR_GET': (state, action) => action.payload }),
    info: createReducer([], { 'BUYER_SETTINGS_INFO': (state, action) => action.payload }),
    update: createReducer([], { 'BUYER_SETTINGS_UPDATE': (state, action) => action.payload }),
})

export const buyerPreloadedState = {
    settings: {},
    avatarGet: {},
    info: {},
    update: {},
}

import { createReducer } from '@reduxjs/toolkit'
import { combineReducers } from '@reduxjs/toolkit'

export const seller = combineReducers({
    catalog: createReducer([], { 'SELLERS_CATALOG': (state, action) => action.payload }),
    create: createReducer([], { 'SELLER_CREATE': (state, action) => action.payload }),
    settings: createReducer([], { 'SELLER_SETTINGS': (state, action) => action.payload }),
    info: createReducer([], { 'SELLER_INFO': (state, action) => action.payload }),
    infoById: createReducer([], { 'SELLER_INFO_BY_ID': (state, action) => action.payload }),
    update: createReducer([], { 'SELLER_UPDATE': (state, action) => action.payload }),
    
})

export const sellerPreloadedState = {
    catalog: [],
    settings: {},
    create: {},
    info: {},
    update: {},
    infoById: {},
}

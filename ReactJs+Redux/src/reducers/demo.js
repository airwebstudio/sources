import { createReducer } from '@reduxjs/toolkit'
import { combineReducers } from '@reduxjs/toolkit'

export const demo = combineReducers({
    add: createReducer([], { 'DEMO_ADD': (state, action) => action.payload }),
    view: createReducer([], { 'DEMO_VIEW': (state, action) => action.payload }),
    list: createReducer([], { 'DEMO_LIST': (state, action) => action.payload })
})

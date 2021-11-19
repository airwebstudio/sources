import { createReducer } from '@reduxjs/toolkit'
import { combineReducers } from '@reduxjs/toolkit'

export const pages = combineReducers({
    view: createReducer([], { 'PAGE_VIEW': (state, action) => action.payload }),
})
export const pagesPreloadedState = {
    view: {},
}
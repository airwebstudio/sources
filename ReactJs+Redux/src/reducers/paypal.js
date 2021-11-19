import { createReducer } from '@reduxjs/toolkit'
import { combineReducers } from '@reduxjs/toolkit'

export const paypal = combineReducers({
    success: createReducer([], { 'PAYPAL_SUCCESS': (state, action) => action.payload }),
    cancel: createReducer([], { 'PAYPAL_CANCEL': (state, action) => action.payload })
})

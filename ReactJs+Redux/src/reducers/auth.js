import { createReducer } from '@reduxjs/toolkit'
import { combineReducers } from '@reduxjs/toolkit'

export const auth = combineReducers({
    login: createReducer([], {'LOGIN': (state, action) => action.payload}),
    register: createReducer([], {'REGISTER': (state, action) => action.payload}),
    forgotPassword: createReducer([], {'FORGOT_PASSWORD': (state, action) => action.payload})
})

export const authPreloadedState = {
    login: {
        response: {},
        data: {},
    },
    register: {
        response: {},
        data: {},
    },
    forgotPassword: {},
}

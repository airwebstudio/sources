import { createReducer } from '@reduxjs/toolkit'
import { combineReducers } from '@reduxjs/toolkit'

export const chat = combineReducers({
    history: createReducer([], { 'CHAT_HISTORY': (state, action) => action.payload }),
    privateChats: createReducer([], { 'PRIVATE_CHATS': (state, action) => action.payload }),
    uploadedFile: createReducer([], { 'UPLOAD_FILE': (state, action) => action.payload }),
})
export const chatPreloadedState = {
    history: {},
    privateChats: [],
    uploadedFile: {}
}

import { createReducer } from '@reduxjs/toolkit'
import { combineReducers } from '@reduxjs/toolkit'

export const meeting = combineReducers({
    create: createReducer([], { 'MEETING_CREATE': (state, action) => action.payload }),
    update: createReducer([], { 'MEETING_UPDATE': (state, action) => action.payload }),
    myOwn: createReducer([], { 'MEETS_MY_OWN': (state, action) => action.payload }),
    myRequests: createReducer([], { 'MEETS_MY_REQUESTS': (state, action) => action.payload }),
    calendar: createReducer([], { 'MEETING_CALENDAR': (state, action) => action.payload }),
    addUserOnMeeting: createReducer([], { 'MEETING_ADD_USER': (state, action) => action.payload }),
    approveUserOnMeeting: createReducer([], { 'MEETING_APPROVE_USER': (state, action) => action.payload }),
    removeUserOnMeeting: createReducer([], { 'MEETING_REMOVE_USER': (state, action) => action.payload }),
    getSellerAvailability: createReducer([], { 'MEETING_SELLER_AVAILABILITY': (state, action) => action.payload }),
    getMeetingAvailabilities: createReducer([], { 'MEETING_AVAILABILITIES': (state, action) => action.payload }),
    infoMeetingAvailability: createReducer([], { 'MEETING_INFO_AVAILABILITY': (state, action) => action.payload }),
    createMeetingAvailability: createReducer([], { 'MEETING_CREATE_AVAILABILITY': (state, action) => action.payload }),
    updateMeetingAvailability: createReducer([], { 'MEETING_UPDATE_AVAILABILITY': (state, action) => action.payload }),
    removeMeetingAvailability: createReducer([], { 'MEETING_REMOVE_AVAILABILITY': (state, action) => action.payload }),
    getAvailabilityParticipants: createReducer([], { 'MEETING_PARTICIPANTS_AVAILABILITY': (state, action) => action.payload }),
    aproveAvailabilityParticipant: createReducer([], { 'MEETING_APROVE_PARTICIPANT_AVAILABILITY': (state, action) => action.payload }),
    removeAvailabilityParticipant: createReducer([], { 'MEETING_REMOVE_PARTICIPANT_AVAILABILITY': (state, action) => action.payload }),
    addUserMeetingAvailability: createReducer([], { 'MEETING_ADD_BUYER_AVAILABILITY': (state, action) => action.payload }),
    upcomingEvents: createReducer([], { 'MEETING_UPCOMING_EVENTS': (state, action) => action.payload }),
    getMeetingInfo: createReducer([], { 'MEETING_INFO': (state, action) => action.payload }),
})

export const meetPreloadedState = {
    create: {},
    myOwn: {},
    myRequests: {},
    calendar: [],
    addUserOnMeeting: {},
    approveUserOnMeeting: {},
    removeUserOnMeeting: {},
    getSellerAvailability: [],
    getMeetingAvailabilities: {},
    infoMeetingAvailability: {},
    createMeetingAvailability: {},
    updateMeetingAvailability: {},
    removeMeetingAvailability: {},
    getAvailabilityParticipants: {
        data: {},
        response: {},
    },
    aproveAvailabilityParticipant: {},
    removeAvailabilityParticipant: {},
    addUserMeetingAvailability: {
        data: {},
        response: {},
    },
    upcomingEvents: {},
    getMeetingInfo: {},
}
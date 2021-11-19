import { privateApi } from './api'

const myOwn = payload => ({type: 'MEETS_MY_OWN', payload: payload})
const myRequests = payload => ({type: 'MEETS_MY_REQUESTS', payload: payload})
const create = payload => ({type: 'MEETING_CREATE', payload: payload})
const update = payload => ({type: 'MEETING_UPDATE', payload: payload})
const getMeetingInfo = payload => ({type: 'MEETING_INFO', payload: payload})
const decline = payload => ({type: 'MEETING_DECLINE', payload: payload})
const calendar = payload => ({type: 'MEETING_CALENDAR', payload: payload})
const upcomingEvents = payload => ({type: 'MEETING_UPCOMING_EVENTS', payload: payload})

const addUserOnMeeting = payload => ({type: 'MEETING_ADD_USER', payload: payload})
const approveUserOnMeeting = payload => ({type: 'MEETING_APPROVE_USER', payload: payload})
const removeUserOnMeeting = payload => ({type: 'MEETING_REMOVE_USER', payload: payload})

const addParticipant = payload => ({type: 'MEETING_ADD_BUYER', payload: payload})
const removeParticipant = payload => ({type: 'MEETING_REMOVE_PARTICIPANT', payload: payload})

const getSellerAvailability = payload => ({type: 'MEETING_SELLER_AVAILABILITY', payload: payload})
const getMeetingAvailabilities = payload => ({type: 'MEETING_AVAILABILITIES', payload: payload})
const infoMeetingAvailability = payload => ({type: 'MEETING_INFO_AVAILABILITY', payload: payload})
const createMeetingAvailability = payload => ({type: 'MEETING_CREATE_AVAILABILITY', payload: payload})
const updateMeetingAvailability = payload => ({type: 'MEETING_UPDATE_AVAILABILITY', payload: payload})
const removeMeetingAvailability = payload => ({type: 'MEETING_REMOVE_AVAILABILITY', payload: payload})

const getAvailabilityParticipants = payload => ({type: 'MEETING_PARTICIPANTS_AVAILABILITY', payload: payload})
const aproveAvailabilityParticipant = payload => ({type: 'MEETING_APROVE_PARTICIPANT_AVAILABILITY', payload: payload})
const removeAvailabilityParticipant = payload => ({type: 'MEETING_REMOVE_PARTICIPANT_AVAILABILITY', payload: payload})
const addUserMeetingAvailability = payload => ({type: 'MEETING_ADD_BUYER_AVAILABILITY', payload: payload})

export const meeting = {
    create: meeting => privateApi({
        url: "/meeting",
        method: "PUT",
        data: meeting
    }, response => create(response.data)),

    join: (meeting_hash) => privateApi({
        url: `/meeting/join`,
        method: "POST",
        data: {meeting_hash: meeting_hash},
    }, response => pay(response)),

    update: meeting => privateApi({
        url: `/meeting/${meeting.hash}`,
        method: "POST",
        data: meeting
    }, response => update(response.data)),

    getMeetingInfo: hash => privateApi({
        url: `/meeting/${hash}`,
        method: "GET",
    }, response => getMeetingInfo(response.data)),

    decline: meeting => privateApi({
        url: `/meeting/${meeting.hash}`,
        method: "DELETE",
    }, response => decline(response.data)),

    myOwn: (page, filters) => privateApi({
        url: "/meeting/my/own?page="+page,
        method: "POST",
        data: {filters:filters}
    }, response => myOwn(response.data)),

    meParticipantOf: (page, filters) => privateApi({
        url: "/meeting/my/participantof?page="+page,
        method: "POST",
        data: {filters:filters}
    }, response => myOwn(response.data)),

    myRequests: filter => privateApi({
        url: "/meeting/my/requests",
        method: "POST",
        data: filter
    }, response => myRequests(response.data)),

    calendar: id => privateApi({
        url: "/meeting/calendar/" + id,
        method: "GET",
    }, response => calendar(response.data)),

    addUserOnMeeting: (hash, userId) => privateApi({
        url: `/meeting/${hash}/${userId}`,
        method: "PUT",
    }, response => addUserOnMeeting(response)),

    approveUserOnMeeting: (hash, userId) => privateApi({
        url: `/meeting/${hash}/${userId}`,
        method: "POST",
    }, response => approveUserOnMeeting(response)),

    removeUserOnMeeting: (hash, userId) => privateApi({
        url: `/meeting/${hash}/${userId}`,
        method: "DELETE",
    }, response => removeUserOnMeeting(response.data)),

    addParticipant: (meeting, buyer) => privateApi({
        url: `/meeting-buyer/${meeting.hash}/${buyer.id}`,
        method: "PUT",
    }, response => addParticipant(response.data)),

    removeParticipant: buyer => privateApi({
        url: `/meeting-buyer/${meeting.hash}/${buyer.id}`,
        method: "DELETE",
    }, response => removeParticipant(response.data)),

    upcomingEvents: () => privateApi({
        url: `/meeting/upcoming_events`,
        method: "GET",
    }, response => upcomingEvents(response.data)),

    // SELLERS
    getSellerAvailability: id => privateApi({
        url: `/seller/${id}/availability`,
        method: "GET",
    }, response => getSellerAvailability(response.data)),

    getMeetingAvailabilities: (page = 1) => privateApi({
        url: `/seller/availability?page=${page}`,
        method: "GET",
    }, response => getMeetingAvailabilities(response.data)),

    infoMeetingAvailability: (id) => privateApi({
        url: `/seller/availability/${id}`,
        method: "GET",
    }, response => infoMeetingAvailability(response.data)),

    createMeetingAvailability: (params) => privateApi({
        url: `/seller/availability`,
        method: "PUT",
        data: params,
    }, response => createMeetingAvailability(response.data)),

    updateMeetingAvailability: (id, params) => privateApi({
        url: `/seller/availability/${id}`,
        method: "POST",
        data: params,
    }, response => updateMeetingAvailability(response.data)),

    removeMeetingAvailability: (id) => privateApi({
        url: `/seller/availability/${id}`,
        method: "DELETE",
    }, response => removeMeetingAvailability(response.data)),

    getAvailabilityParticipants: (id) => privateApi({
        url: `/seller/availability/${id}/participants`,
        method: "GET",
    }, response => getAvailabilityParticipants(response)),

    aproveAvailabilityParticipant: (id) => privateApi({
        url: `/seller/availability/participant/${id}`,
        method: "POST",
    }, response => aproveAvailabilityParticipant(response.data)),

    removeAvailabilityParticipant: (id) => privateApi({
        url: `/seller/availability/participant/${id}`,
        method: "DELETE",
    }, response => removeAvailabilityParticipant(response.data)),

    // Buyer
    addUserMeetingAvailability: (id) => privateApi({
        url: `/buyer/availability/${id}`,
        method: "PUT",
    }, response => addUserMeetingAvailability(response)),
}

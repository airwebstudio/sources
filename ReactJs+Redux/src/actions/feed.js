import { privateApi, api } from './api'

const createFeed = payload => ({type: 'FEED_CREATE', payload: payload})
const getFeed = payload => ({type: 'FEED_GET', payload: payload})
const updateFeed = payload => ({type: 'FEED_UPDATE', payload: payload})
const removeFeed = payload => ({type: 'FEED_REMOVE', payload: payload})
const getUserFeeds = payload => ({type: 'FEED_GET_USER_FEEDS', payload: payload})
const getPublicUsersFeeds = payload => ({type: 'FEED_GET_USERS_FEEDS', payload: payload})

export const feed = {
    createFeed: params => privateApi({
        url: "/user/feed-item",
        method: "PUT",
        data: params
    }, response => createFeed(response.data)),

    getFeed: id => privateApi({
        url: `/user/feed-item/${id}`,
        method: "GET",
    }, response => getFeed(response)),

    updateFeed: (id, params) => privateApi({
        url: `/user/feed-item/${id}`,
        method: "POST",
        data: params
    }, response => updateFeed(response.data)),

    removeFeed: id => privateApi({
        url: `/user/feed-item/${id}`,
        method: "DELETE",
    }, response => removeFeed(response.data)),

    getUserFeeds: (params = '') => privateApi({
        url: `/user/feed-items${params}`,
        method: "GET",
    }, response => getUserFeeds(response.data)),

    getPublicUsersFeeds: (params = '') => api({
        url: `/public/feed-items${params}`,
        method: "GET",
    }, response => getPublicUsersFeeds(response.data)),    
}

import { privateApi } from './api'

const settings = payload => ({type: 'BUYER_SETTINGS', payload: payload})
const avatarGet = payload => ({type: 'BUYER_SETTINGS_AVATAR_GET', payload: payload})
const info = payload => ({type: 'BUYER_SETTINGS_INFO', payload: payload})
const update = payload => ({type: 'BUYER_SETTINGS_UPDATE', payload: payload})

export const buyer = {
    settings: settings => privateApi({
        url: "/buyer/settings",
        method: "POST",
        data: settings
    }, response => settings(response.data)),

    avatarGet: id => privateApi({
        url: `/avatar/${id}`,
        method: "GET",
    }, response => getAvatar(response.data)),

    info: () => privateApi({
        url: "/user/me",
        method: "GET",
    }, response => info(response)),

    update: (params) => privateApi({
        url: "/user",
        method: "POST",
        data: params,
    }, response => update(response.data)),
}

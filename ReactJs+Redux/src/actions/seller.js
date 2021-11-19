import { privateApi, api } from './api'

const catalog = payload => ({type: 'SELLERS_CATALOG', payload: payload})
const settings = payload => ({type: 'SELLER_SETTINGS', payload: payload})
const info = payload => ({type: 'SELLER_INFO', payload: payload})
const infoById = payload => ({type: 'SELLER_INFO_BY_ID', payload: payload});
const create = payload => ({type: 'SELLER_CREATE', payload: payload})
const meetings = payload => ({type: 'SELLER_CALENDAR', payload: payload})
const update = payload => ({type: 'SELLER_UPDATE', payload: payload})



export const seller = {
    catalog: (page = 1) => privateApi({
        url: `/user/seller/find?page=${page}`,
        method: "GET",
        //data: search
    }, response => catalog(response.data)),

    settings: settings => privateApi({
        url: "/seller/settings",
        method: "POST",
        data: settings
    }, response => settings(response.data)),

    info: () => privateApi({
        url: "/user/seller",
        method: "GET",
    }, response => info(response.data)),

    infoById: id => privateApi({
        url: `/user/seller/info/${id}`,
        method: "GET",
    }, response => infoById(response.data)),

    create: params => privateApi({
        url: "/user/seller",
        method: "PUT",
        data: params
    }, response => create(response.data)),

    update: params => privateApi({
        url: "/user/seller",
        method: "POST",
        data: params
    }, response => update(response.data)),

   

}

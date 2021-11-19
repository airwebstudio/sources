import { api } from './api'

const success = payload => ({type: 'PAYPAL_SUCCESS', payload: payload})
const cancel = payload => ({type: 'PAYPAL_CANCEL', payload: payload})

export const paypal = {
    success: id => api({
        url: "/paypal/update/" + id,
        method: "GET"
    }, response => success(response.data)),

    cancel: id => api({
        url: "/paypal/cancel/" + id,
        method: "GET",
    }, response => cancel(response.data))
}

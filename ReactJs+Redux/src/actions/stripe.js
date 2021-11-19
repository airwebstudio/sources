import { privateApi } from './api'

const stripe_payment = payload => ({type: 'STRIPE_PAYMENT', payload: payload})
const stripe_transaction = payload => ({type: 'STRIPE_TRANSACTION', payload: payload})
const stripe_balance = payload => ({type: 'STRIPE_BALANCE', payload: payload})
const stripe_payment_status = payload => ({type: 'STRIPE_PAYMENT_STATUS', payload: payload})
const stripe_history = payload => ({type: 'STRIPE_HISTORY', payload: payload})
const stripe_payout = payload => ({type: 'STRIPE_PAYOUT', payload: payload})

const stripe_acc = payload => ({type: 'STRIPE_ACC', payload: payload})
const stripe_link = payload => ({type: 'STRIPE_LINK', payload: payload})
const stripe_retrieve = payload => ({type: 'STRIPE_RETRIEVE', payload: payload})
const join_meeting = payload => ({type: 'JOIN_MEETING', payload: payload})
const finish_meeting = payload => ({type: 'FINISH_MEETING', payload: payload})

export const stripe = {
    stripe_payment: (card_data, amount) => privateApi({
        url: '/stripe/charge',
        method: "POST",
        data: { card_data: card_data, amount: amount },
    }, response => stripe_payment(response.data)),

    stripe_transaction: (amount, seller) => privateApi({
        url: '/stripe/transaction',
        method: "POST",
        data: {amount: amount, seller: seller},
    }, response => stripe_transaction(response.data)),

    stripe_balance: () => privateApi({
        url: '/stripe/balance',
        method: "POST",
    }, response => stripe_balance(response.data)),

    stripe_payment_status: (qid, type) => privateApi({
        url: '/stripe/payment_status',
        method: "POST",
        data: {qid: qid, type: type},
    }, response => stripe_payment_status(response.data)),

    stripe_history: () => privateApi({
        url: '/stripe/history',
        method: "POST",
    }, response => stripe_history(response.data)),

    stripe_payout: (amount) => privateApi({
        url: '/stripe/payout',
        method: "POST",
        data: {amount: amount}
    }, response => stripe_payout(response.data)),

    stripe_acc: params => privateApi({
        url: "/stripe/get",
        method: "GET",
        data: params
    }, response => stripe_acc(response.data)),

    stripe_link: params => privateApi({
        url: "/stripe/link",
        method: "GET",
        data: params
    }, response => stripe_link(response.data)),

    stripe_retrieve: params => privateApi({
        url: "/stripe/retrieve",
        method: "GET",
        data: params
    }, response => stripe_retrieve(response.data)),


    join_meeting: params => privateApi({
        url: "/stripe/join_meeting",
        method: "POST",
        data: params
    }, response => join_meeting(response.data)),


    finish_meeting: params => privateApi({
        url: "/stripe/finish_meeting",
        method: "POST",
        data: params
    }, response => finish_meeting(response.data)),

}
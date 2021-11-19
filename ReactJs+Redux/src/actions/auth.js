import { api } from './api'

const login = payload => ({type: 'LOGIN', payload: payload})
const login_with_google = payload => ({type: 'LOGIN', payload: payload})
const register = payload => ({type: 'REGISTER', payload: payload})
const forgotPassword = payload => ({type: 'FORGOT_PASSWORD', payload: payload})

export const auth = {
    login: params => api({
        url: "/user/login",
        method: "POST",
        data: params,
    }, response => login(response)),

    login_with_google: params => api({
        url: "/user/login/google",
        method: "POST",
        data: params,
    }, response => login_with_google(response)),

    register: params => api({
        url: "/user",
        method: "PUT",
        data: params,
    }, response => register(response)),

    forgotPassword: params => api({
        url: `/user/forgot-password`,
        method: "POST",
        data: params,
    }, response => forgotPassword(response.data))
}

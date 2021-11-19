import axios from 'axios'
import {Buffer} from 'buffer'

export const api = (props, func, isPrivate=false, isUpload=false) => {
    return dispatch => {
        props['url'] = process.env.REACT_APP_API_PUBLIC_HOST + "/api" + props['url']

        props['headers'] = {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }

        if(isPrivate){
            const authHeader = window.getCookie('_auth_t')
            props['headers']['Authorization'] = 'Bearer '+ authHeader
      }

        if(isUpload){
            const authHeader = window.getCookie('_auth_t')
            props['headers'] = {
                "Content-Type": "multipart/form-data",
                'Authorization': 'Bearer ' + authHeader
            }
        }

        /*
        if(process.env.REACT_APP_API_ADMIN_USER){
            const token = `${process.env.REACT_APP_API_ADMIN_USER}:${process.env.REACT_APP_API_ADMIN_PASS}`;
            const encodedToken = Buffer.from(token).toString('base64');
            props['withCredentials'] = true
            props['crossDomain'] = true
            props['headers']['Authorization'] = 'Basic '+ encodedToken
        }
        */
        return axios(props)
            .then(response => dispatch(func(response)))
            .catch(error => dispatch(func(error)))
    }
}

export const privateApi = (props, func) => {
    return api(props, func, true)
}
export const uploadApi = (props, func) => {
    return api(props, func, false, true)
}

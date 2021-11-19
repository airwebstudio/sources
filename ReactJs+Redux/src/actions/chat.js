import {privateApi, uploadApi} from './api'

const history = payload => ({type: 'CHAT_HISTORY', payload: payload})
const privateChats = payload => ({type: 'PRIVATE_CHATS', payload: payload})
const uploadFile = payload => ({type: 'UPLOAD_FILE', payload: payload})

export const chat = {
    history: roomId => privateApi({
        url: '/chats/room/' + roomId,
        method: "GET",
    }, response => history(response.data)),
    privateChats: () => privateApi({
        url: '/chats/private',
        method: "GET",
    }, response => privateChats(response.data)),
    uploadFile: (formData) => uploadApi({
        url: "/storage/upload",
        method: "POST",
        data: formData,
    }, response => uploadFile(response.data)),
}

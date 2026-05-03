import {signRequest} from '../services/auth'

export const getUser = async () => {
    try {
        return await signRequest('/api/user', 'GET', null)
    } catch (e) {
        return null
    }
}
export const getNearbySightings = async (latitude, longitude) => {
    try {
        return await signRequest(`/api/user/tips?latitude=${latitude}&longitude=${longitude}`, 'GET', null)
    } catch (e) {
        return null
    }
}

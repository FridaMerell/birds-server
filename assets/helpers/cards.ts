import {signRequest} from "../services/auth"

export const getCards = async () => {
    return await signRequest('/api/cards', 'GET', null);
}

export const getCard = async (id) => {
    return await signRequest(`/api/cards/${id}`, 'GET', null);
}

export const createCard = async (data) => {
    return await signRequest('/api/cards', 'POST', data);
}

export const updateCard = async (id, data) => {
    return await signRequest(`/api/cards/${id}`, 'PUT', data);
}

export const deleteCard = async (id) => {
    return await signRequest(`/api/cards/${id}`, 'DELETE', null);
}


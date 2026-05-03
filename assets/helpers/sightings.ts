import {signRequest} from "../services/auth";

export const getSightings = async () => {
	return await signRequest('/open-api/sightings/', 'GET', null);
}

export const getSighting = async (id) => {
	return await signRequest(`/open-api/sightings/${id}`, 'GET', null);
}

export const createSighting = async (data) => {
	return await signRequest('/api/sightings', 'POST', data);
}

export const updateSighting = async (id, data) => {
	return await signRequest(`/api/sightings/${id}`, 'PUT', data);
}

export const deleteSighting = async (id) => {
	return await signRequest(`/api/sightings/${id}`, 'DELETE', null);
}

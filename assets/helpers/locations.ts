import {signRequest} from "../services/auth";
import {Location} from "../redux/slices/locationSlice";

export const getLocationList = (options: object) => {
    return signRequest('/open-api/locations/list', 'GET', null);
}

export const getLocation = (id: number) => {
    return signRequest('/api/locations/' + id, 'GET', null);
}

export const createLocation = (location: object) => {
    return signRequest('/api/locations', 'POST', location);
}

export const updateLocation = (location: Location) => {
    return signRequest('/api/locations/' + location.id, 'PUT', location);
}

export const deleteLocation = (id: object) => {
    return signRequest('/api/locations/' + id, 'DELETE', null);
}


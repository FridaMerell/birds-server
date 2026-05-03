import {signRequest} from '../services/auth';

export const getSpeciesList = (options, page = 1) => {
    return signRequest(`/open-api/species/list/${page}`, 'GET', null);
}

export const getSpecies = (id: string) => {
    return signRequest('/open-api/species/' + id, 'GET', null);
}

export const searchSpecies = (query: string) => {
    return signRequest('/open-api/species/search', 'GET', {search: query});
}
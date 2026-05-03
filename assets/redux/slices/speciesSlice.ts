import {createSlice} from '@reduxjs/toolkit';

const initialState = {
    speciesList: [],
    currentSpecies: null,
    speciesLoading: false,
    speciesError: null,
};

export interface Species {
    id: number;
    ScientificName: string;
    VernacularName: string;
    Genus: Genus
}

export interface Genus {
    id: number;
    ScientificName: string;
    VernacularName: string;
    Family: Family
}

export interface Family {
    id: number;
    ScientificName: string;
    VernacularName: string;
    TaxOrder: Order
}

export interface Order {
    id: number;
    ScientificName: string;
    VernacularName: string;
    Class: Class
}

export interface Class {
    id: number;
    ScientificName: string;
    VernacularName: string;
}

const speciesSlice = createSlice({
    name: 'species',
    initialState: {
        list: Array<Species>(),
        currentSpecies: null,
        speciesLoading: false,

    },
    reducers: {
        setSpeciesList: (state, action) => {
            state.list = action.payload;
        },
        setCurrentSpecies: (state, action) => {
            state.currentSpecies = action.payload;
        },
        setSpeciesLoading: (state, action) => {
            state.speciesLoading = action.payload;
        }

    }

});

export const {setSpeciesList, setCurrentSpecies, setSpeciesLoading} = speciesSlice.actions;

export default speciesSlice.reducer;

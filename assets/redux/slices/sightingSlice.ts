import {createSlice} from "@reduxjs/toolkit";
import {Species} from "./speciesSlice";

export interface Sighting {
    id: number;
    Comment: string;
	Location: string;
	Timestamp: string;
	Species: Species
}

export const sightingsSlice = createSlice({
    name: "sighting",
    initialState: {
        list: Array<Sighting>(),
        currentSighting: null
    },

    reducers: {
        addSighting: (state, action) => {
            state.list.push(action.payload);
        },
        setSightings: (state, action) => {
            state.list = action.payload;
        },
        deleteSighting: (state, action) => {
            state.list = state.list.filter(sighting => sighting.id !== action.payload);
        },
        updateSighting: (state, action) => {
            state.list = state.list.map(sighting => {
                if (sighting.id === action.payload.id) {
                    return action.payload;
                }
                return sighting;
            });
        },
        setCurrentSighting: (state, action) => {
            state.currentSighting = action.payload;
        }
    },

});


export const {addSighting, setSightings, deleteSighting, updateSighting, setCurrentSighting} = sightingsSlice.actions;

export default sightingsSlice.reducer;
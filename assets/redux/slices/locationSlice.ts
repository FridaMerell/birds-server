import {createSlice} from "@reduxjs/toolkit";

export interface Location {
    latitude: number,
    longitude: number,
    radius: number,
    Name: string,
    id: number,
}

export const locationsSlice = createSlice({
    name: "location",
    initialState: {
        locations: Array<Location>(),
    },

    reducers: {
        addLocation: (state, action) => {
            state.locations.push(action.payload);
        },
        setLocations: (state, action) => {
            state.locations = action.payload;
        },
        deleteLocation: (state, action) => {
            state.locations = state.locations.filter(location => location.id !== action.payload);
        },
        updateLocation: (state, action) => {
            state.locations = state.locations.map(location => {
                if (location.id === action.payload.id) {
                    return action.payload;
                }
                return location;
            });
        }
    },

});

export const {addLocation, setLocations, deleteLocation, updateLocation} = locationsSlice.actions;

export default locationsSlice.reducer;
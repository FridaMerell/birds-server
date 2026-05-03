import {createSlice} from "@reduxjs/toolkit";
import {Sighting} from "./sightingSlice";
import {Species} from "./speciesSlice";
import {User} from "./userSlice";

export interface Card {
    id: number,
    name: string,
    users: User[],
    startDate: string,
    endDate: string,
    species: Species[],
    sightings: Sighting[],
}

export const cardsSlice = createSlice({
    name: "card",
    initialState: {
        cards: Array<Card>()
    },

    reducers: {
        addCard: (state, action) => {
            state.cards.push(action.payload);
        },
        setCards: (state, action) => {
            state.cards = action.payload;
        },
        deleteCard: (state, action) => {
            state.cards = state.cards.filter(card => card.id !== action.payload);
        },
        updateCard: (state, action) => {
            state.cards = state.cards.map(card => {
                if (card.id === action.payload.id) {
                    return action.payload;
                }
                return card;
            });
        }
    },

});

export const {addCard, setCards, deleteCard, updateCard} = cardsSlice.actions;

export default cardsSlice.reducer;
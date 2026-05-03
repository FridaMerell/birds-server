import {configureStore} from '@reduxjs/toolkit'
import userReducer from '../slices/userSlice'
import sightingsReducer from '../slices/sightingSlice'
import locationsReducer from '../slices/locationSlice'
import cardsReducer from '../slices/cardsSlice'
import speciesReducer from '../slices/speciesSlice'

export default configureStore({
	reducer: {
		user: userReducer,
		sightings: sightingsReducer,
		locations: locationsReducer,
		cards: cardsReducer,
		species: speciesReducer
	},
})

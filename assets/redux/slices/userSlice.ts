import {createSlice} from '@reduxjs/toolkit';

export interface User {
    username: string,
    password: string,
    roles: string[],
    token: string,
    id: number,
}

const userSlice = createSlice({
    name: 'user',
    initialState: {
        currentUser: null
    },
    reducers:
        {
            setUser: (state, action) => {
                state.currentUser = action.payload;
            },
            updateUsername: (state, action) => {
                state.currentUser.username = action.payload;
            },
            updatePassword: (state, action) => {
                state.currentUser.password = action.payload;
            },
            updateRoles: (state, action) => {
                state.currentUser.roles = action.payload;
            },
            updateToken: (state, action) => {
                state.currentUser.token = action.payload;
            },
            updateId: (state, action) => {
                state.currentUser.id = action.payload;
            },
            clearUser: (state) => {
                state.currentUser = {
                    username: '',
                    password: '',
                    roles: [],
                    token: '',
                    id: 0,
                };
            },
        }
    ,
});

export const {
    setUser,
    updateUsername,
    updatePassword,
    updateRoles,
    updateToken,
    updateId,
    clearUser
} = userSlice.actions;
export default userSlice.reducer;
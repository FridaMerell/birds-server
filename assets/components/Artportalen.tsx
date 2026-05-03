import {useEffect, useRef} from 'react'
import '../style/main.scss'
import {createRoutesFromElements, Route, RouterProvider, Routes} from "react-router";
import Login from "./Routes/Login";
import Home from "./Routes/Home/Home";
import Layout from "./Layout";
import LocationList from "./Routes/Location/List";
import SightingList from "./Routes/Sightings/List" ;
import SingleSighting from "./Routes/Sightings/New/Single";
import BulkSighting from "./Routes/Sightings/New/Bulk";
import SpeciesList from "./Routes/Species/List";
import {useDispatch, useSelector} from "react-redux";
import {setUser} from "../redux/slices/userSlice";
import {getUser} from '../helpers/user'
import Single from './Routes/Sightings/Single'
import SingleSpecies from './Routes/Species/Single'
import {createBrowserRouter} from 'react-router-dom'
import {refreshToken} from '../services/auth'


const Artportalen = () => {
    const dispatch = useDispatch()
    const hasChecked = useRef(false)
    const user = useSelector((state: any) => state.user)

    useEffect(() => {
        if (!user || !user.id)
            getUser().then((response: any) => {
                dispatch(setUser(response))
            }).catch((error: any) => {
                console.log(error)
            })

    }, []);

    const router = createBrowserRouter(createRoutesFromElements(
        <Route
            path="/"
            element={<Layout/>}
        >
            <Route
                path="/login"
                element={<Login/>}
            />
            <Route
                path="/sightings"
            >
                <Route
                    index={true}
                    element={<SightingList/>}
                />
                <Route
                    path="/sightings/:id"
                    element={<Single/>}
                />
                <Route
                    path="/sightings/new"
                    element={<SingleSighting/>}
                />
            </Route>
            <Route
                path="/sightings/bulk"
                element={<BulkSighting/>}
            />
            <Route
                path="/species"
            >
                <Route
                    index={true}
                    element={<SpeciesList/>}
                />
                <Route
                    path=":id"
                    element={<SingleSpecies/>}
                />
            </Route>
            <Route
                path="/locations"
            >
                <Route
                    index={true}
                    element={<LocationList/>}
                />
            </Route>
            <Route
                path="/"
                element={<Home/>}
            />

        </Route>
    ))


    return (
        <RouterProvider router={router}/>
    )

}

export default Artportalen
import {useEffect, useState} from 'react'
import {useDispatch, useSelector} from "react-redux"
import {getLocationList} from "../../../helpers/locations"
import {setLocations} from "../../../redux/slices/locationSlice"

const List = () => {
    const locations = useSelector((state: any) => state.locations.locations)
    const [search, setSearch] = useState<string>('')
    const dispatch = useDispatch()

    useEffect(() => {
        getLocationList({search: search}).then((response: any) => {
            dispatch(setLocations(response))
        })
    }, [])

    return (
        <main>
            <h1>Platser</h1>
            <hr/>
            {locations && locations.length > 0 ? locations.map((location: any) => {
                    return (
                        <div key={location.id}>
                            <h2>{location.Name}</h2>
                        </div>
                    )
                })
                :
                <p>Inga platser hittades</p>
            }
        </main>
    )
}

export default List
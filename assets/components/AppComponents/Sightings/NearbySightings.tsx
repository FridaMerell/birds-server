import {useEffect, useState} from 'react'
import {getNearbySightings} from "../../../helpers/user";

const NearbySightings = () => {

    const [currentPosition, setCurrentPosition] = useState({latitude: 0, longitude: 0})
    const [nearbySightings, setNearbySightings] = useState([])

    useEffect(() => {
        navigator.geolocation.getCurrentPosition((position) => {
            setCurrentPosition({
                latitude: position.coords.latitude,
                longitude: position.coords.longitude
            })
        }, (error) => {
            console.log(error)
        })
    }, [])

    useEffect(() => {
        if (currentPosition.latitude != 0) {
            getNearbySightings(currentPosition.latitude, currentPosition.longitude).then((response: any) => {
                console.log(response)
                setNearbySightings(response)
            }).catch((error: any) => {
                console.log(error)
            })
        }
    }, [currentPosition])

    return (
        <>
            <h1>Nearby Sightings</h1>

		</>
    )
}

export default NearbySightings
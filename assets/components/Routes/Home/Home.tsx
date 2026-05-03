import {useEffect, useState} from 'react'
import {useSelector} from 'react-redux'
import Dash from "./Partials/Dash";
import Guest from "./Partials/Guest";

const Home = () => {

    const user = useSelector((state: any) => state.user.currentUser)

    useEffect(() => {
        console.log(user)


    }, [user])

    return (
        <main>
            {user && user.id ? <Dash/> : <Guest/>}
        </main>
    )
}

export default Home
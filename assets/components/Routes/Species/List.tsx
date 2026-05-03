import {useEffect, useState} from 'react'
import {getSpecies, getSpeciesList} from '../../../helpers/species';
import {useDispatch, useSelector} from 'react-redux'
import {setSpeciesList, Species} from '../../../redux/slices/speciesSlice'
import {Link} from "react-router-dom";

const List = () => {
    const dispatch = useDispatch()
    const species = useSelector((state: any) => state.species.list)
    useEffect(() => {
            getSpeciesList({}, 1).then((response: Array<Species>) => {
                dispatch(setSpeciesList(response))
            })
        }
        , [])


    return (
        <main>
            <h1>
                Arter
            </h1>
            <ul>
                {species && species.map((species: Species, index: number) => {
                    return <li key={species.id}>
                        <ins>{index + 1}</ins>
                        <Link to={`/species/${species.id}`}>
                            <h2>{species.VernacularName}</h2>
                        </Link>
                    </li>
                })}
            </ul>
        </main>
    )
}

export default List
import {Link, useParams} from "react-router-dom";
import {useEffect} from "react";
import {getSpecies} from "../../../helpers/species";
import {setCurrentSpecies, Species} from "../../../redux/slices/speciesSlice";
import {useDispatch, useSelector} from "react-redux";

const Single = () => {
    const {id} = useParams()
    const dispatch = useDispatch()
    const species = useSelector((state: any) => state.species.currentSpecies)

    useEffect(() => {
        getSpecies(id).then((response: Species) => {
            dispatch(setCurrentSpecies(response))
        })
    }, [id])

    return (
        <main>
            <h1>
                {species && species.VernacularName}
            </h1>
            <p>
                <Link to="/species">Tillbaka</Link>
            </p>
        </main>
    )
}

export default Single
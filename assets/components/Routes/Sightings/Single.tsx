import {useEffect, useState} from 'react'
import {getSighting} from '../../../helpers/sightings'
import {useParams} from 'react-router'
import {useDispatch, useSelector} from "react-redux";
import {setCurrentSighting, Sighting} from "../../../redux/slices/sightingSlice";

const Single = () => {
    const {id} = useParams<{ id: string }>()
    const sighting: Sighting | null = useSelector((state: any) => state.sightings.currentSighting)
    const dispatch = useDispatch()
    useEffect(() => {
        getSighting(id).then((res: Sighting) => {
            console.log(res)
            dispatch(setCurrentSighting(res))

        })
    }, [])

    return (
        <main>
            {sighting && <>
                <section>
                  <div className="observation">
                    <h1 className="capitalize underline">
                        {sighting.Species.VernacularName}
                    </h1>
                  <span className="latin">
                        {sighting.Species.ScientificName}
                  </span>
                  </div>
                  <div className="family-tree">
                    <h2 className="capitalize">
                        {sighting.Species.Genus.VernacularName}
                    </h2>
                    <h2 className="capitalize">
                        {sighting.Species.Genus.Family.VernacularName}
                    </h2>
                    <h2 className="capitalize">
                        {sighting.Species.Genus.Family.TaxOrder.VernacularName}
                    </h2>
                    <h2 className="capitalize">
                        {sighting.Species.Genus.Family.TaxOrder.Class.VernacularName}
                    </h2>

                  </div>
                </section>

              </>

            }
        </main>
    )

}

export default Single
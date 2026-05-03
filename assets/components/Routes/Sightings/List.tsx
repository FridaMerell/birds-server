import  {useEffect, useRef, useState} from 'react'
import {getSightings} from "../../../helpers/sightings";
import Filter from "./partial/Filter";
import {Link} from 'react-router-dom'
import {useDispatch, useSelector} from 'react-redux'
import {setSightings, Sighting} from '../../../redux/slices/sightingSlice'

const List = () => {
	const dispatch = useDispatch()
	const sightings = useSelector((state: any) => state.sightings.list)
	const filters = useRef({})
	useEffect(() => {
		getSightings().then((response : Array<Sighting>) => {

			dispatch(setSightings(response))

		})
	}, [])

	return (
		<main>
			<h1>
				Gluttar
			</h1>
			<Filter ref={filters}/>

			<ul>
				{sightings && sightings.map((sighting:Sighting, index:number) => {
                    console.log(sighting)
					return <li key={sighting.id}>
						<ins>{index + 1}</ins>
						<Link to={'/sightings/' + sighting.id}>
							<h2>{sighting.Species.VernacularName}</h2>
						</Link>
					</li>
				})}
			</ul>
		</main>
	)
}

export default List
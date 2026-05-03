import {useState} from "react";
import {Species} from "../../../redux/slices/speciesSlice";
import {searchSpecies} from "../../../helpers/species";

const SearchBird = () => {
    const [search, setSearch] = useState<string>('');
    const [results, setResults] = useState<Array<Species>>([]);
    const searchBird = (e: any) => {

        setSearch(e.target.value);

        // Fetch birds from API
        searchSpecies(e.target.value).then((res: Array<Species>) => {
                setResults(res);
            }
        )
    }
    return (
        <div>
            <h4>Sök efter art</h4>
            <input
                type="text"
                placeholder="Sök fågel"
                onChange={(e) => searchBird(e)}
                value={search}
            />
            <div className="results">
                {results.map((bird: Species) => {
                    return (
                        <div key={bird.id}>
                            <p>{bird.VernacularName}</p>
                        </div>
                    )

                })}
            </div>
        </div>
    )

}

export default SearchBird;

import {useSelector} from "react-redux";
import {useEffect} from "react";
import SearchBird from "../../../AppComponents/Taxonomy/SearchBird";

const Guest = () => {

    const user = useSelector((state: any) => state.user)

    useEffect(() => {

    }, [user])


    return (
        <div>
            <h1>Fåvitsko fåglar</h1>
            <SearchBird/>
        </div>
    );
}

export default Guest
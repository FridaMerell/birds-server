import React from "react";
import Artportalen from "./components/Artportalen";
import store from "./redux/store/artportalen";
import {Provider} from "react-redux";
import {createRoot} from "react-dom/client";

let artportalen = document.getElementById("root")
if (artportalen) {
	let root = createRoot(artportalen)
	root.render(
		<Provider store={store}>
			<Artportalen/>
		</Provider>
	)
}
import {useEffect, useState} from 'react'
import Header from "./Shared/Header";
import {Outlet} from "react-router";
import Footer from "./Shared/Footer";

const Layout = () => {
	return (
		<>
			<Header/>
			<Outlet/>
			<Footer/>
		</>
	)
}

export default Layout
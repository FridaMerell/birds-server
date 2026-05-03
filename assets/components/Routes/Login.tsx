import  {useEffect, useState} from 'react'
import '../../style/shared/form.scss'
import Spinner from "../Shared/partials/Spinner";
import {signIn} from "../../services/auth";
import {useNavigate} from 'react-router'
import {useDispatch} from 'react-redux'
import {setUser} from '../../redux/slices/userSlice'
import {getUser} from '../../helpers/user'

const Login = () => {

	const dispatch = useDispatch()
	const [username, setUsername] = useState('')
	const [password, setPassword] = useState('')
	const [error, setError] = useState('')
	const [loading, setLoading] = useState(false)
	const navigate = useNavigate()
	const handleSubmit = (e) => {
		e.preventDefault()

		setLoading(true)
		const response = signIn(username, password).then((response) => {
			getUser().then((response) => {
				dispatch(setUser(response.data))
				navigate('/')
			})

		}).catch((error) => {
			setError(error.message)
		}).finally(() => {
			setLoading(false)

		})

	}

	return (
		<>
			{
				loading
					?
					<Spinner/>
					:
					<form onSubmit={handleSubmit}>
						{error && <div className="alert alert-danger">{error}</div>}
						<label htmlFor="username">
							Username
						</label>
						<input
							name="username"
							type="
				text"
							value={username}
							onChange={e => setUsername(e.target.value)}
						/>
						<label htmlFor="password">
							Password
						</label>
						<input
							name="password"
							type="password"
							value={password}
							onChange={e => setPassword(e.target.value)}
						/>
						<button type="submit">Logga in</button>
					</form>
			}
</>
	)
}

export default Login
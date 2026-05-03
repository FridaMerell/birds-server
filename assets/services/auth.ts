export const signRequest = async (url: string, method: string, body: object | null) => {
    let config = {
        method: method, headers: {
            Authorization: 'Bearer ' + localStorage.getItem('token'),
        },
        body: undefined

    }
    if (body) {
        if (method === 'GET') {
            let params = new URLSearchParams();
            for (let key in body) {
                params.append(key, body[key])
            }
            url += '?' + params.toString()
        } else {

            config.headers['Content-Type'] = {'Content-Type': 'application/json'}
            config.body = JSON.stringify(body);
        }
    }

    const response = await fetch(url, config)
    if (response.status === 401) {
        return null
    }
    return await response.json();
}


export const signUp = async (email: string, password: string, username = null) => {
    const payload = {
        email: email, password: password, username: username,
    }
    return await signRequest('/api/register', 'POST', payload);
}

export const signIn = async (email: string, password: string) => {
    const payload = {
        email: email, password: password,
    }
    let request = await fetch('/api/login_check', {
        method: 'POST', headers: {
            'Content-Type': 'application/json'
        }, body: JSON.stringify(payload),
    })
    let result = await request.json()

    if (result.token && result.refresh_token) {
        localStorage.setItem('token', result.token)
        localStorage.setItem('refresh-token', result.refresh_token)
    }

    return request.status === 200

}

export const refreshToken = async () => {
    try {

        const response = await fetch('/api/token/refresh', {
            method: 'POST', headers: {
                Authorization: 'Bearer ' + localStorage.getItem('refresh-token'),
            },
        });
        const json = await response.json();
        localStorage.setItem('token', json.token);
    } catch (e) {
        localStorage.removeItem('token');
        localStorage.removeItem('refresh-token');
    }
}

export const hasToken = () => {
    return localStorage.getItem('token') !== null;

}

export const logout = () => {
    localStorage.removeItem('token');
    location.reload()
}

export const getUser = async () => {
    return await signRequest('/api/user', 'GET', null);
}
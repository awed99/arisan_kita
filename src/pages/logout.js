import { useEffect } from 'react';
import Store from 'store';

export default function Logout() {
    useEffect(() => {
        Store.remove('auth-user')
        Store.remove('token')
        Store.remove('chipper')
        window.location.href = ('/auth/login')
    }, [])

    return (<></>)
}
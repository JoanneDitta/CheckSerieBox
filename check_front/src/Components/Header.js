import React, { useState, useEffect } from 'react';
import '../App.css';
// import { Link } from 'react-router-dom';

export function Header() {

  const [isLogged, setIsLogged] = useState(false);
  const [isAdmin, setIsAdmin] = useState(false);

    useEffect(() => {
        const role = localStorage.getItem('userFirstRole');

        // Check login state and if logged set admin if user is ROLE_ADMIN
        if (role === 'ROLE_ADMIN' || role === 'ROLE_DESIGN') {
            setIsLogged(true);
            if (role === 'ROLE_ADMIN') {
                setIsAdmin(true)
            }
        } else {
            setIsLogged(false);
        }

    }, []);

    // Clear local storage when user lougout
    function clear() {
        localStorage.clear();
        console.log('clear storage and logged out');
    }

    return (
        <header className="mb-5 px-5 py-2 bg-black flex justify-between align-center text-white">
            <div>
                <a href="'series_index'">
                    <h1 className="text-3xl">CheckSerieBox</h1>
                </a>
            </div>
            <div className="flex align-center">
                {isLogged && (
                    <div>
                        <a href='serie_user_index' className="mx-3">Profil</a>
                        <a href='serie_user_series_list' className="mx-3">Series</a>
                        <a href='serie_user_watchlist' className="mx-3">Watchlist</a>
                        <a href='serie_user_watching_list' className="mx-3">Watching now</a>
                        <a href='serie_user_dropped_list' className="mx-3">Given up</a>
                        <a href='app_logout' className="btn btn-danger" onClick={clear}>Logout</a>
                    </div>
                ) && isAdmin && (
                    <a href="'admin_index'" className="mx-3">Admin board</a>
                )}
                {!isLogged && (
                    <div className="flex justify-between align-center gap-3">
                        <a href='app_login' className="m-auto">Login</a>
                        <a href='app_register' className="m-auto">Sign in</a>
                    </div>
                )}
            </div>
        </header>
    );
}

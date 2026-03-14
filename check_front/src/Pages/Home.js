import { useEffect, useState } from "react";


import '../App.css';

import { Header } from '../Components/Header.js';

export function Home() {

    const [seriesRandom, setSeriesRandom] = useState([]);
    const [filteredSeries, setFilteredSeries] = useState([]);
    const [currentPage, setCurrentPage] = useState(0);
    const [userSeriesStatus, setUserSeriesStatus] = useState({});
    const itemsPerPage = 24;

    // ----------------------------------------------------------------------------------------------------------------------------------

    const types = ["Série", "Mini-série", "K-Drama", "Drama", "Anime", "Web-série"];
    const countries = ["USA", "UK", "France", "Korea", "Japan", "Spain"];
    const release_dates = ["1999", "2000", "2005","2010", "2015", "2019","2020", "2021", "2022","2023", "2024", "2025", "2026"];
    const platforms = ["Netflix", "Disney+", "AMC", "ABC", "The CW", "Prime Video", "Fox", "Paramount+", "Crunchyroll", "tvN", "HBO"];
    const nb_seasons = ["1", "2", "3", "$1", "5", "6", "7", "8", "9", "10+"];
    const status = ["En production", "En cours", "Finie", "Annulée", "A venir"];

    const filterOptions = { type: types, country: countries, release_date: release_dates, platform: platforms, nb_season: nb_seasons, status: status };


    // ----------------------------------------------------------------------------------------------------------------------------------

     useEffect(() => {
        fetch("http://localhost:3000/api/series")
            .then((response) => response.json())
            .then((data) => {
                setSeriesRandom(data);
                setFilteredSeries(data);
            })
            .catch((error) => console.error(error));
    }, []);

    // ----------------------------------------------------------------------------------------------------------------------------------

    const totalPages = Math.ceil(filteredSeries.length / itemsPerPage);
    const currentItems = filteredSeries.slice(currentPage * itemsPerPage, (currentPage + 1) * itemsPerPage);
    const handleNextPage = () => setCurrentPage((prev) => Math.min(prev + 1, totalPages - 1));
    const handlePrevPage = () => setCurrentPage((prev) => Math.max(prev - 1, 0));

    // ----------------------------------------------------------------------------------------------------------------------------------

    const handleFilterChange = (e) => {
        const formData = new FormData(e.target.form || e.target.closest('form'));
        const filters = Object.fromEntries(formData.entries());

        fetch('/api/series?' + new URLSearchParams(filters).toString())
            .then(res => res.json())
            .then(data => {
                setFilteredSeries(data);
                setCurrentPage(0);
            })
            .catch(err => console.error(err));
    };

    const handleSearch = (e) => {
        const searchValue = e.target.value.toLowerCase().trim();
        const filtered = seriesRandom.filter(s => s.title.toLowerCase().includes(searchValue));
        setFilteredSeries(filtered);
        setCurrentPage(0);
    };

    // ----------------------------------------------------------------------------------------------------------------------------------

    const toggleSerieList = async (serieId, list) => {
        try {
            const res = await fetch('/api/user/serie/toggle', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ serieId, list })
            });
            const data = await res.json();

            setUserSeriesStatus(prev => ({
                ...prev,
                [serieId]: {
                    list: data.list || null,
                    liked: data.liked ?? prev[serieId]?.liked ?? false
                }
            }));
        } catch (err) {
            console.error(err);
        }
    };

    return (
        <div className="bg-gray-900 h-screen text-white">
            <Header />
            <main>

                {/* -------------------- Filtres -------------------- */}
                <div className="mx-5 mb-10">
                    <div className="flex justify-between">
                        <form id="filtersList" className="filters flex justify-between align-center gap-5 m-0">
                            <p className="m-auto text-lg">Filters</p>
                            <div className="flex p-0">
                                {Object.entries(filterOptions).map(([name, options]) =>
                                    <div className="filter-container p-0">
                                        <label className="filter-label">
                                            <select name={name}  onChange={handleFilterChange}>
                                                <option value="" >{name.replace("_", " ").charAt(0).toUpperCase() + name.replace("_", " ").slice(1)}</option>
                                                {options.map((option) => (
                                                    <option value={option.toLowerCase()} >{option.charAt(0).toUpperCase() + option.slice(1)}</option>
                                                ))}
                                            </select>
                                            <svg className="filter-icon" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                                                <path d="M165.6 349.8c-1.4 1.3-3.5 2.2-5.6 2.2s-4.2-.8-5.6-2.2L34.2 236.3c-1.4-1.3-2.2-3.2-2.2-5.2c0-3.9 3.2-7.1 7.1-7.1l241.7 0c3.9 0 7.1 3.2 7.1 7.1c0 2-.8 3.8-2.2 5.2L165.6 349.8zm22 23.3L307.7 259.6c7.8-7.4 12.3-17.7 12.3-28.4c0-21.6-17.5-39.1-39.1-39.1L39.1 192C17.5 192 0 209.5 0 231.1c0 10.8 4.4 21.1 12.3 28.5L132.4 373.1c7.4 7 17.3 10.9 27.6 10.9s20.1-3.9 27.6-10.9z"/>
                                            </svg>
                                        </label>
                                    </div>
                                )}
                            </div>
                        </form>
                        <div className="m-auto">
                            <input
                                type="text"
                                name="searchbar"
                                id="searchbar"
                                className=""
                                placeholder="Search a serie..."
                                onChange={handleSearch}
                            />
                        </div>
                    </div>
                </div>

                {/* -------------------- Carrousel -------------------- */}
                <div class="carousel-container">
                    <div class="carousel-controls mx-auto w-1/2 flex justify-around align-center mb-8">
                        <button class="bg-blue-500 rounded-sm px-2 mr-2" onClick={handlePrevPage} disabled={currentPage === 0}>←</button>
                        <span id="pageIndicator">{currentPage + 1} / {totalPages}</span>
                        <button class="bg-blue-500 rounded-sm px-2 ml-2" onClick={handleNextPage} disabled={currentPage >= totalPages - 1}>→</button>
                    </div>
                    {currentItems.length === 0 && <p className="text-muted text-center">Aucune série trouvée</p>}
                    <ul class="flex flex-wrap gap-4" id="carousel">
                        {currentItems.map((serieRandom) => {
                            const statusObj = userSeriesStatus[serieRandom.id] || { list: null, liked: false };
                            return (
                                <li key={serieRandom.id} class="bg-dark carousel-item">
                                    <article class={`cardPoster ${statusObj.list ? statusObj.list + "-active" : ""}`}
                                    >
                                        <a href={`/serie/${serieRandom.slug}`} class="position-relative poster-card">
                                            <img class="poster" src={serieRandom.poster_url } alt={`poster-${serieRandom.slug}`} />
                                            {/* {% if is_granted('IS_AUTHENTICATED_FULLY') %} */}
                                                <div class="bg-black rounded position-absolute poster-actions">
                                                    <div class="d-flex justify-content-around align-items-center gap-1 px-2 py-1">
                                                        {["watched","watching","watchlist","dropped"].map(l => (
                                                            <button key= {l} class={`serie-action list-${l} ${statusObj.list === l ? 'active' : ''}`} onClick={(e) => { e.preventDefault(); toggleSerieList(serieRandom.id, l); }} >
                                                                {/* <svg classname="w-5 h-5" fill="currentcolor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                                                    <path d="M256 16a240 240 0 1 1 0 480 240 240 0 1 1 0-480zm0 496A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM357.7 197.7c3.1-3.1 3.1-8.2 0-11.3s-8.2-3.1-11.3 0L224 308.7l-58.3-58.3c-3.1-3.1-8.2-3.1-11.3 0s-3.1 8.2 0 11.3l64 64c3.1 3.1 8.2 3.1 11.3 0l128-128z"/>
                                                                </svg> */}
                                                                {l}
                                                            </button>
                                                        ))}
                                                        <button className={`serie-action list-liked ${statusObj.liked ? 'active' : ''}`} onClick={(e) => { e.preventDefault(); toggleSerieList(serieRandom.id, 'like'); }} >
                                                            <svg classname="w-5 h-5" stroke="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                                                <path d="M12 5.881C12.981 4.729 14.484 4 16.05 4C18.822 4 21 6.178 21 8.95C21 12.3492 17.945 15.1195 13.3164 19.3167L13.305 19.327L12 20.515L10.695 19.336L10.6595 19.3037C6.04437 15.1098 3 12.3433 3 8.95C3 6.178 5.178 4 7.95 4C9.516 4 11.019 4.729 12 5.881Z"/>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>
                                        </a>
                                    </article>
                                </li>
                            );
                        })}
                    </ul>
                </div>
            </main>
        </div>
    )
}
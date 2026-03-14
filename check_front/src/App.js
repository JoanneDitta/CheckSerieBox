// import logo from './logo.svg';

import { BrowserRouter, Routes, Route } from "react-router-dom";
import './App.css';

import { Home } from './Pages/Home.js';
import { Serie } from './Pages/Serie';
import { AdminDashboard } from './Pages/AdminDashboard';
import { EditSerie } from './Pages/EditSerie';
import { Profil } from './Pages/Profil';

export default function App() {
  return (
    <BrowserRouter>
          <Routes>
            <Route
              path="/"
              element={<Home />}
            />
            <Route
              path="/serie/{slug}"
              element={<Serie />}
            />
            <Route
              path="/admin"
              element={<AdminDashboard />}
            />
            <Route
              path="/admin/edit/{id}"
              element={<EditSerie />}
            />
             <Route
              path="/{username}"
              element={<Profil />}
            />
          </Routes>
        </BrowserRouter>
  );
}

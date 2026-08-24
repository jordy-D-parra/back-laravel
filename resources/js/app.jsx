import './bootstrap';
import React from 'react';
import { createRoot } from 'react-dom/client';

// Componente simple de prueba
function App() {
    return (
        <div>
            <h1>Sistema de Inventario - Departamento de Informática</h1>
            <p>React cargado correctamente</p>
        </div>
    );
}

// Montar React en el div con id="app" (solo si existe en la vista)
const rootElement = document.getElementById('app');
if (rootElement) {
    createRoot(rootElement).render(<App />);
}

import React from 'react';
import ReactDOM from 'react-dom/client';
import SpaceChooser from './SpaceChooser.jsx';

window.renderSpaceChooser = (id, props) => {
    ReactDOM.createRoot(document.getElementById(id)).render(<SpaceChooser {...props} />);
};

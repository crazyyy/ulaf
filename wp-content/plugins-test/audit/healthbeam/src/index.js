import { render } from '@wordpress/element';
import App from './App';
import './index.css';

const rootElement = document.getElementById('healthbeam-root');

if (rootElement) {
	render(<App />, rootElement);
}

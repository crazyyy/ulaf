import { useState, useEffect } from '@wordpress/element';
import { Spinner } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { fetchFromApi } from '../utils/api';

const PluginCompatibility = () => {
    const [plugins, setPlugins] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        fetchFromApi({ path: '/plugin-compat' })
            .then((response) => {
                setPlugins(response.plugins);
                setLoading(false);
            })
            .catch((err) => {
                setError(err.message);
                setLoading(false);
            });
    }, []);

    if (loading) {
        return <Spinner />;
    }

    if (error) {
        return <div className="tool-error">{error}</div>;
    }

    return (
        <div>
            <p>{__('Check installed plugins for PHP compatibility.', 'healthbeam')}</p>
            <table className="widefat striped">
                <thead>
                    <tr>
                        <th>{__('Plugin', 'healthbeam')}</th>
                        <th>{__('Version', 'healthbeam')}</th>
                        <th>{__('Requires PHP', 'healthbeam')}</th>
                    </tr>
                </thead>
                <tbody>
                    {plugins.map((plugin, index) => (
                        <tr key={index}>
                            <td>{plugin.name}</td>
                            <td>{plugin.version}</td>
                            <td>{plugin.requires_php || '—'}</td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
};

export default PluginCompatibility;

import { useState, useEffect } from '@wordpress/element';
import { Spinner } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { fetchFromApi } from '../utils/api';

const ServerResources = () => {
    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        fetchFromApi({ path: '/server-resources' })
            .then((response) => {
                setData(response);
                setLoading(false);
            })
            .catch((err) => {
                setError(err.message);
                setLoading(false);
            });
    }, []);

    if (loading) return <Spinner />;
    if (error) return <div className="tool-error">{error}</div>;

    return (
        <div>
            <p>{__('Overview of server resources and configuration.', 'healthbeam')}</p>

            {data && (
                <table className="widefat striped">
                    <tbody>
                        <tr>
                            <td><strong>{__('PHP Version', 'healthbeam')}</strong></td>
                            <td>{data.php_version}</td>
                        </tr>
                        <tr>
                            <td><strong>{__('Server Software', 'healthbeam')}</strong></td>
                            <td>{data.server_software}</td>
                        </tr>
                        <tr>
                            <td><strong>{__('Memory Usage', 'healthbeam')}</strong></td>
                            <td>{data.memory_usage}</td>
                        </tr>
                        <tr>
                            <td><strong>{__('Memory Limit', 'healthbeam')}</strong></td>
                            <td>{data.memory_limit}</td>
                        </tr>
                        {data.load_average && (
                            <tr>
                                <td><strong>{__('Load Average', 'healthbeam')}</strong></td>
                                <td>{data.load_average.join(', ')}</td>
                            </tr>
                        )}
                    </tbody>
                </table>
            )}
        </div>
    );
};

export default ServerResources;

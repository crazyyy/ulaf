import { useState, useEffect } from '@wordpress/element';
import { Button, Spinner } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { fetchFromApi } from '../utils/api';

const DatabaseOptimizer = () => {
    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [optimizing, setOptimizing] = useState(false);
    const [message, setMessage] = useState(null);
    const [error, setError] = useState(null);

    const fetchData = () => {
        setLoading(true);
        fetchFromApi({ path: '/database' })
            .then((response) => {
                setData(response);
                setLoading(false);
            })
            .catch((err) => {
                setError(err.message);
                setLoading(false);
            });
    };

    useEffect(() => {
        fetchData();
    }, []);

    const optimize = () => {
        setOptimizing(true);
        setMessage(null);
        fetchFromApi({ path: '/database', method: 'POST' })
            .then((response) => {
                setMessage(response.message);
                setOptimizing(false);
                fetchData();
            })
            .catch((err) => {
                setError(err.message);
                setOptimizing(false);
            });
    };

    if (loading) return <Spinner />;
    if (error) return <div className="tool-error">{error}</div>;

    return (
        <div>
            <p>{__('Optimize your database by removing overhead.', 'healthbeam')}</p>

            {data && (
                <div className="tool-output">
                    <p><strong>{__('Total Overhead:', 'healthbeam')}</strong> {data.total_overhead}</p>
                    {data.tables.length > 0 ? (
                        <ul style={{ margin: '10px 0', paddingLeft: '20px' }}>
                            {data.tables.map((table, index) => (
                                <li key={index}>{table.name}: {table.overhead}</li>
                            ))}
                        </ul>
                    ) : (
                        <p>{__('Database is already optimized.', 'healthbeam')}</p>
                    )}
                </div>
            )}

            <div style={{ marginTop: '20px' }}>
                <Button
                    variant="primary"
                    onClick={optimize}
                    disabled={optimizing || (data && data.total_overhead_bytes === 0)}
                >
                    {optimizing ? __('Optimizing...', 'healthbeam') : __('Optimize Database', 'healthbeam')}
                </Button>
            </div>

            {message && <div className="tool-success" style={{ marginTop: '15px' }}>{message}</div>}
        </div>
    );
};

export default DatabaseOptimizer;

import { useState, useEffect } from '@wordpress/element';
import { Button, Spinner } from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';
import { fetchFromApi } from '../utils/api';

const TransientSummary = () => {
    const [stats, setStats] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [clearing, setClearing] = useState(false);
    const [message, setMessage] = useState(null);

    const fetchStats = () => {
        setLoading(true);
        fetchFromApi({ path: '/transients' })
            .then((response) => {
                setStats(response);
                setLoading(false);
            })
            .catch((err) => {
                setError(err.message);
                setLoading(false);
            });
    };

    useEffect(() => {
        fetchStats();
    }, []);

    const clearTransients = () => {
        setClearing(true);
        setMessage(null);
        fetchFromApi({
            path: '/transients',
            method: 'DELETE',
        })
            .then((response) => {
                setMessage(response.message);
                setClearing(false);
                fetchStats();
            })
            .catch((err) => {
                setError(err.message);
                setClearing(false);
            });
    };

    if (loading) {
        return <Spinner />;
    }

    if (error) {
        return <div className="tool-error">{error}</div>;
    }

    return (
        <div>
            <p>{__('Transients are temporary pieces of data, that is often requested, or gathered from third party sources, and stored in your website database to improve site performance. These pieces of data may over time become large and take up a lot of space, and can then be safely deleted, as their content is not critical to the functionality of your site.', 'healthbeam')}</p>

            {stats && (
                <div className="tool-output">
                    <p>
                        {sprintf(
                            /* translators: 1: Number of transients, 2: Estimated size */
                            __('Your site currently contains a total of %1$s transients, taking up an estimated %2$s of space in your database.', 'healthbeam'),
                            stats.count,
                            stats.size
                        )}
                    </p>
                </div>
            )}

            <div style={{ marginTop: '20px' }}>
                <Button variant="primary" isDestructive onClick={clearTransients} disabled={clearing}>
                    {clearing ? __('Clearing...', 'healthbeam') : __('Clear All Transients', 'healthbeam')}
                </Button>
            </div>

            {message && <div className="tool-success" style={{ marginTop: '15px' }}>{message}</div>}
        </div>
    );
};

export default TransientSummary;

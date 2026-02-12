import { useState, useEffect } from '@wordpress/element';
import { Spinner } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { fetchFromApi } from '../utils/api';

const DebugLogViewer = () => {
    const [log, setLog] = useState('');
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        fetchFromApi({ path: '/debug-log' })
            .then((response) => {
                setLog(response.content);
                setLoading(false);
            })
            .catch((err) => {
                setError(err);
                setLoading(false);
            });
    }, []);

    if (loading) {
        return <Spinner />;
    }

    if (error) {
        if (error.code === 'no_debug_log') {
            return (
                <div className="tool-error">
                    <p>{__('WP_DEBUG_LOG is not enabled.', 'healthbeam')}</p>
                    <p dangerouslySetInnerHTML={{ __html: __('To enable it, add the following to your <code>wp-config.php</code> file:', 'healthbeam') }} />
                    <pre>
                        {`define( 'WP_DEBUG', true );\ndefine( 'WP_DEBUG_LOG', true );`}
                    </pre>
                </div>
            );
        }
        return <div className="tool-error">{error.message}</div>;
    }

    return (
        <div>
            <p>{__('This section shows errors or warnings caused by code on your site.', 'healthbeam')}</p>
            {log ? (
                <pre className="tool-output">{log}</pre>
            ) : (
                <div className="tool-success">{__('Debug log is empty.', 'healthbeam')}</div>
            )}
        </div>
    );
};

export default DebugLogViewer;

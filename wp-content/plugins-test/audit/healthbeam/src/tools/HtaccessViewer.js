import { useState, useEffect } from '@wordpress/element';
import { Spinner } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { fetchFromApi } from '../utils/api';

const HtaccessViewer = () => {
    const [content, setContent] = useState('');
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        fetchFromApi({ path: '/htaccess' })
            .then((response) => {
                setContent(response.content);
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
            <p>{__('View the content of your .htaccess file.', 'healthbeam')}</p>
            <pre className="tool-output">{content}</pre>
        </div>
    );
};

export default HtaccessViewer;

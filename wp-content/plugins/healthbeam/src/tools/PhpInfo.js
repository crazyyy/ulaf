import { useState, useEffect } from '@wordpress/element';
import { Spinner } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { fetchFromApi } from '../utils/api';

const PhpInfo = () => {
    const [content, setContent] = useState('');
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        fetchFromApi({ path: '/phpinfo' })
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
            <p>{__('View extended PHP configuration information.', 'healthbeam')}</p>
            <div className="tool-output">
                <iframe
                    title="PHP Info"
                    style={{ width: '100%', border: 'none', height: '600px', backgroundColor: '#fff' }}
                    srcDoc={content}
                />
            </div>
        </div>
    );
};

export default PhpInfo;

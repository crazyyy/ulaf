import { useState } from '@wordpress/element';
import { Button, Spinner } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { fetchFromApi } from '../utils/api';

const FileIntegrity = () => {
    const [result, setResult] = useState(null);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);

    const runCheck = () => {
        setLoading(true);
        setError(null);
        setResult(null);

        fetchFromApi({ path: '/file-integrity' })
            .then((response) => {
                setResult(response.files);
                setLoading(false);
            })
            .catch((err) => {
                setError(err.message);
                setLoading(false);
            });
    };

    return (
        <div>
            <p>{__('Check core files against WordPress API checksums.', 'healthbeam')}</p>
            <Button variant="primary" onClick={runCheck} disabled={loading}>
                {loading ? __('Checking...', 'healthbeam') : __('Check Files', 'healthbeam')}
            </Button>

            {loading && <Spinner />}

            {error && <div className="tool-error">{error}</div>}

            {result && (
                <div style={{ marginTop: '20px' }}>
                    {result.length === 0 ? (
                        <div className="tool-success">{__('All files passed the check.', 'healthbeam')}</div>
                    ) : (
                        <table className="widefat striped">
                            <thead>
                                <tr>
                                    <th>{__('Status', 'healthbeam')}</th>
                                    <th>{__('File', 'healthbeam')}</th>
                                    <th>{__('Reason', 'healthbeam')}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {result.map((file, index) => (
                                    <tr key={index}>
                                        <td>
                                            {file.status === 'error' ? '❌' : '⚠️'}
                                            <span className="screen-reader-text">{file.status}</span>
                                        </td>
                                        <td>{file.file}</td>
                                        <td dangerouslySetInnerHTML={{ __html: file.reason }} />
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    )}
                </div>
            )}
        </div>
    );
};

export default FileIntegrity;

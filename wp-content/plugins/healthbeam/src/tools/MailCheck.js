import { useState, useEffect } from '@wordpress/element';
import { Button, TextControl, TextareaControl, Spinner } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { fetchFromApi } from '../utils/api';

const MailCheck = () => {
    const [email, setEmail] = useState('');
    const [message, setMessage] = useState('');
    const [loading, setLoading] = useState(false);
    const [result, setResult] = useState(null);
    const [error, setError] = useState(null);

    useEffect(() => {
        if (window.healthBeamSettings?.current_user_email) {
            setEmail(window.healthBeamSettings.current_user_email);
        }
    }, []);

    const sendEmail = () => {
        setLoading(true);
        setError(null);
        setResult(null);

        fetchFromApi({
            path: '/mail-check',
            method: 'POST',
            data: { email, message },
        })
            .then((response) => {
                setResult(response.message);
                setLoading(false);
            })
            .catch((err) => {
                setError(err.message);
                setLoading(false);
            });
    };

    return (
        <div>
            <p>{__('Send a test email to verify your mail configuration.', 'healthbeam')}</p>

            <TextControl
                label={__('Email Address', 'healthbeam')}
                value={email}
                onChange={(value) => setEmail(value)}
            />

            <TextareaControl
                label={__('Additional Message', 'healthbeam')}
                value={message}
                onChange={(value) => setMessage(value)}
            />

            <Button variant="primary" onClick={sendEmail} disabled={loading || !email}>
                {loading ? __('Sending...', 'healthbeam') : __('Send Test Email', 'healthbeam')}
            </Button>

            {loading && <Spinner />}

            {error && <div className="tool-error">{error}</div>}

            {result && <div className="tool-success">{result}</div>}
        </div>
    );
};

export default MailCheck;

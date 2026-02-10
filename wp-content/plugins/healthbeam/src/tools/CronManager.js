import { useState, useEffect } from '@wordpress/element';
import { Button, Spinner } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { fetchFromApi } from '../utils/api';

const CronManager = () => {
    const [jobs, setJobs] = useState([]);
    const [loading, setLoading] = useState(true);
    const [processing, setProcessing] = useState(null);
    const [message, setMessage] = useState(null);
    const [error, setError] = useState(null);

    const fetchJobs = () => {
        setLoading(true);
        fetchFromApi({ path: '/cron' })
            .then((response) => {
                setJobs(response.jobs);
                setLoading(false);
            })
            .catch((err) => {
                setError(err.message);
                setLoading(false);
            });
    };

    useEffect(() => {
        fetchJobs();
    }, []);

    const runJob = (job) => {
        setProcessing(job.hook);
        setMessage(null);
        fetchFromApi({
            path: '/cron',
            method: 'POST',
            data: { hook: job.hook, args: job.args }
        })
            .then((response) => {
                setMessage(response.message);
                setProcessing(null);
            })
            .catch((err) => {
                setError(err.message);
                setProcessing(null);
            });
    };

    const deleteJob = (job) => {
        if (!confirm(__('Are you sure you want to delete this cron job?', 'healthbeam'))) return;

        setProcessing(job.hook);
        setMessage(null);
        fetchFromApi({
            path: '/cron',
            method: 'DELETE',
            data: { hook: job.hook, timestamp: job.timestamp, args: job.args }
        })
            .then((response) => {
                setMessage(response.message);
                setProcessing(null);
                fetchJobs();
            })
            .catch((err) => {
                setError(err.message);
                setProcessing(null);
            });
    };

    if (loading) return <Spinner />;
    if (error) return <div className="tool-error">{error}</div>;

    return (
        <div>
            <p>{__('Manage scheduled cron events.', 'healthbeam')}</p>

            {message && <div className="tool-success">{message}</div>}

            <table className="widefat striped">
                <thead>
                    <tr>
                        <th>{__('Hook', 'healthbeam')}</th>
                        <th>{__('Next Run', 'healthbeam')}</th>
                        <th>{__('Schedule', 'healthbeam')}</th>
                        <th>{__('Actions', 'healthbeam')}</th>
                    </tr>
                </thead>
                <tbody>
                    {jobs.map((job, index) => (
                        <tr key={index}>
                            <td>{job.hook}</td>
                            <td>{job.next_run}</td>
                            <td>{job.schedule}</td>
                            <td>
                                <Button
                                    isSmall
                                    variant="secondary"
                                    onClick={() => runJob(job)}
                                    disabled={processing === job.hook}
                                    style={{ marginRight: '5px' }}
                                >
                                    {__('Run Now', 'healthbeam')}
                                </Button>
                                <Button
                                    isSmall
                                    isDestructive
                                    onClick={() => deleteJob(job)}
                                    disabled={processing === job.hook}
                                >
                                    {__('Delete', 'healthbeam')}
                                </Button>
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
};

export default CronManager;

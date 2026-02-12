import { useState, useEffect } from '@wordpress/element';
import { SelectControl, Spinner } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { fetchFromApi } from '../utils/api';

const CapabilityChecker = () => {
    const [roles, setRoles] = useState({});
    const [selectedRole, setSelectedRole] = useState('');
    const [capabilities, setCapabilities] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        fetchFromApi({ path: '/capabilities' })
            .then((response) => {
                setRoles(response.roles);
                setLoading(false);
                if (Object.keys(response.roles).length > 0) {
                    setSelectedRole(Object.keys(response.roles)[0]);
                }
            })
            .catch((err) => {
                setError(err.message);
                setLoading(false);
            });
    }, []);

    useEffect(() => {
        if (!selectedRole) return;

        setLoading(true);
        fetchFromApi({ path: `/capabilities?role=${selectedRole}` })
            .then((response) => {
                setCapabilities(response.capabilities);
                setLoading(false);
            })
            .catch((err) => {
                setError(err.message);
                setLoading(false);
            });
    }, [selectedRole]);

    if (loading && !capabilities) return <Spinner />;
    if (error) return <div className="tool-error">{error}</div>;

    return (
        <div>
            <p>{__('Check capabilities for user roles.', 'healthbeam')}</p>

            <SelectControl
                label={__('Select Role', 'healthbeam')}
                value={selectedRole}
                options={Object.keys(roles).map(role => ({
                    label: roles[role].name,
                    value: role
                }))}
                onChange={(value) => setSelectedRole(value)}
            />

            {loading ? <Spinner /> : (
                capabilities && (
                    <div className="tool-output" style={{ maxHeight: '300px', overflowY: 'auto' }}>
                        <ul style={{ columns: 2 }}>
                            {Object.keys(capabilities).map((cap) => (
                                <li key={cap} style={{ color: capabilities[cap] ? 'green' : 'red' }}>
                                    {cap}: {capabilities[cap] ? 'Granted' : 'Denied'}
                                </li>
                            ))}
                        </ul>
                    </div>
                )
            )}
        </div>
    );
};

export default CapabilityChecker;

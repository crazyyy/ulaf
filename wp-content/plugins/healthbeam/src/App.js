import { useState } from '@wordpress/element';
import { Button, Card, CardBody, CardHeader } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

// Import tools
import DebugLogViewer from './tools/DebugLogViewer';
import FileIntegrity from './tools/FileIntegrity';
import MailCheck from './tools/MailCheck';
import PluginCompatibility from './tools/PluginCompatibility';
import HtaccessViewer from './tools/HtaccessViewer';
import PhpInfo from './tools/PhpInfo';
import RobotsTxtViewer from './tools/RobotsTxtViewer';
import TransientSummary from './tools/TransientSummary';
import DatabaseOptimizer from './tools/DatabaseOptimizer';
import CronManager from './tools/CronManager';
import CapabilityChecker from './tools/CapabilityChecker';
import ServerResources from './tools/ServerResources';

const App = () => {
    const [activeTool, setActiveTool] = useState(null);

    const tools = [
        { id: 'debug-log', title: __('Debug Log', 'healthbeam'), component: DebugLogViewer, icon: 'text' },
        { id: 'file-integrity', title: __('File Integrity', 'healthbeam'), component: FileIntegrity, icon: 'shield' },
        { id: 'mail-check', title: __('Mail Check', 'healthbeam'), component: MailCheck, icon: 'email' },
        { id: 'plugin-compat', title: __('Plugin Compatibility', 'healthbeam'), component: PluginCompatibility, icon: 'plugins' },
        { id: 'htaccess', title: __('.htaccess Viewer', 'healthbeam'), component: HtaccessViewer, icon: 'admin-network' },
        { id: 'phpinfo', title: __('PHP Info', 'healthbeam'), component: PhpInfo, icon: 'info' },
        { id: 'robotstxt', title: __('robots.txt Viewer', 'healthbeam'), component: RobotsTxtViewer, icon: 'search' },
        { id: 'transients', title: __('Transient Summary', 'healthbeam'), component: TransientSummary, icon: 'database' },
        { id: 'database', title: __('Database Optimizer', 'healthbeam'), component: DatabaseOptimizer, icon: 'performance' },
        { id: 'cron', title: __('Cron Manager', 'healthbeam'), component: CronManager, icon: 'clock' },
        { id: 'capabilities', title: __('Capability Checker', 'healthbeam'), component: CapabilityChecker, icon: 'groups' },
        { id: 'server', title: __('Server Resources', 'healthbeam'), component: ServerResources, icon: 'desktop' },
    ];

    return (
        <div className="site-health-tools-wrapper">
            <div className="site-health-tools-react-app">
                <div className="tools-header">
                    <h1>{__('Advanced Site Health Tool', 'healthbeam')}</h1>
                    <p>{__('Advanced tools to monitor and debug your WordPress site.', 'healthbeam')}</p>
                </div>

                <div className="tools-grid">
                    {tools.map((tool) => (
                        <Card key={tool.id} className={`tool-card ${activeTool === tool.id ? 'active' : ''}`}>
                            <CardHeader>
                                <h2>{tool.title}</h2>
                            </CardHeader>
                            <CardBody>
                                {activeTool === tool.id ? (
                                    <div className="tool-content">
                                        <tool.component />
                                        <Button variant="secondary" onClick={() => setActiveTool(null)} className="close-tool-button">
                                            {__('Close Tool', 'healthbeam')}
                                        </Button>
                                    </div>
                                ) : (
                                    <Button variant="primary" onClick={() => setActiveTool(tool.id)}>
                                        {__('Open Tool', 'healthbeam')}
                                    </Button>
                                )}
                            </CardBody>
                        </Card>
                    ))}
                </div>
            </div>
        </div>
    );
};

export default App;

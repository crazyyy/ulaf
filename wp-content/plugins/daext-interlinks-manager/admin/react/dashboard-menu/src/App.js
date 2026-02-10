import Table from './components/Table';
import {downloadFileFromString} from '../../utils/utils';
import RefreshIcon from '../../../assets/img/icons/refresh-cw-01.svg';
import LoadingScreen from "../../shared-components/LoadingScreen";

const useState = wp.element.useState;
const useEffect = wp.element.useEffect;

const {__} = wp.i18n;

let internalLinksDataLastUpdate = window.DAEXTINMA_PARAMETERS.internal_links_data_last_update;
let internalLinksDataUpdateFrequency = window.DAEXTINMA_PARAMETERS.internal_links_data_update_frequency;
let currentTime = window.DAEXTINMA_PARAMETERS.current_time;

const App = () => {

    const [formData, setFormData] = useState(
        {
            optimizationStatus: 0,
            searchString: '',
            searchStringChanged: false,
            sortingColumn: 'post_date',
            sortingOrder: 'desc'
        }
    );

    const [dataAreLoading, setDataAreLoading] = useState(true);

    const [dataUpdateRequired, setDataUpdateRequired] = useState(false);

    const [tableData, setTableData] = useState([]);
    const [statistics, setStatistics] = useState({
        allPosts: 0,
        averageMil: 0
    });

    useEffect(() => {

        let automaticUpdate = false;

        if (internalLinksDataLastUpdate === ''){

            /**
             * If the internal links data last update is empty, it means that the data has never been updated. In this
             * case, enable the automatic update.
             */

            automaticUpdate = true;

        }else{

            /**
             * If the internal links data last update is not empty, verify if the data needs to be updated based on the
             * update frequency set in the plugin settings.
             */

            // Convert the MySQL date string into a JavaScript Date object.
            let date = new Date(currentTime);

            switch (internalLinksDataUpdateFrequency) {

                case 'hourly':

                    // Remove one hour from date.
                    date.setHours(date.getHours() - 1);

                    if (new Date(internalLinksDataLastUpdate) < date) {
                        automaticUpdate = true;
                    }

                    break;
                case 'daily':

                    // remove one day from date.
                    date.setDate(date.getDate() - 1);

                    if (new Date(internalLinksDataLastUpdate) < date) {
                        automaticUpdate = true;
                    }

                    break;
                case 'weekly':

                    // Remove one week from date.
                    date.setDate(date.getDate() - 7);

                    if (new Date(internalLinksDataLastUpdate) < date) {
                        automaticUpdate = true;
                    }

                    break;
                case 'monthly':

                    // Remove one month from date.
                    date.setMonth(date.getMonth() - 1);

                    if (new Date(internalLinksDataLastUpdate) < date) {
                        automaticUpdate = true;
                    }

                    break;

            }

        }

        /**
         * If an automatic data update is required, and it's not already set the dataUpdateRequired state to true, set it
         * to true and return. By changing the dataUpdateRequired state to true, useEffect will be triggered again and
         * this time with the dataUpdateRequired state set to true, and the data will be updated.
         */
        if(automaticUpdate && !dataUpdateRequired){

            internalLinksDataLastUpdate = currentTime;
            setDataUpdateRequired(true);
            return;

        }
 
        setDataAreLoading(true);

        /**
         * Initialize the chart data with the data received from the REST API
         * endpoint provided by the plugin.
         */
        wp.apiFetch({
            path: '/interlinks-manager-pro/v1/statistics',
            method: 'POST',
            data: {
                optimization_status: formData.optimizationStatus,
                search_string: formData.searchString,
                sorting_column: formData.sortingColumn,
                sorting_order: formData.sortingOrder,
                data_update_required: dataUpdateRequired
            }
        }).then(data => {

                // Set the table data with setTableData().
                setTableData(data.table);

                // Set the statistics.
                setStatistics({
                    allPosts: data.statistics.all_posts,
                    averageMil: data.statistics.average_mil
                });

                if (dataUpdateRequired) {

                    // Set the dataUpdateRequired state to false.
                    setDataUpdateRequired(false);

                    // Set the form data to the initial state.
                    setFormData({
                        optimizationStatus: 0,
                        searchString: '',
                        searchStringChanged: false,
                        sortingColumn: 'post_date',
                        sortingOrder: 'desc'
                    });

                }

                setDataAreLoading(false);

            },
        );

    }, [
        formData.optimizationStatus,
        formData.searchStringChanged,
        formData.sortingColumn,
        formData.sortingOrder,
        dataUpdateRequired
    ]);

    /**
     * Function to handle key press events.
     *
     * @param event
     */
    function handleKeyUp(event) {

        // Check if Enter key is pressed (key code 13).
        if (event.key === 'Enter') {
            event.preventDefault(); // Prevent form submission.
            document.getElementById('daextinma-search-button').click(); // Simulate click on search button.
        }

    }

    /**
     * Handle sorting changes.
     * @param e
     */
    function handleSortingChanges(e) {

        /**
         * Check if the sorting column is the same as the previous one.
         * If it is, change the sorting order.
         * If it is not, change the sorting column and set the sorting order to 'asc'.
         */
        let sortingOrder = formData.sortingOrder;
        if (formData.sortingColumn === e.target.value) {
            sortingOrder = formData.sortingOrder === 'asc' ? 'desc' : 'asc';
        }

        setFormData({
            ...formData,
            sortingColumn: e.target.value,
            sortingOrder: sortingOrder
        })

    }

    /**
     * Used to toggle the dataUpdateRequired value.
     * @param e
     */
    function handleDataUpdateRequired(e) {
        setDataUpdateRequired(prevDataUpdateRequired => {
            return !prevDataUpdateRequired;
        });
    }

    /**
     * Download the file with the CSV data.
     */
    function downloadExportFile() {

        wp.apiFetch({
            path: '/interlinks-manager-pro/v1/dashboard-menu-export-csv',
            method: 'POST'
        }).then(response => {

                downloadFileFromString(response.csv_content, 'dashboard');

            },
        );

    }

    return (

        <>

            <React.StrictMode>

                {
                    !dataAreLoading ?

                        <div className="daextinma-admin-body">

                            <div className={'daextinma-react-table'}>

                                <div className={'daextinma-react-table-header'}>
                                    <div className={'statistics'}>
                                        <div className={'statistic-label'}>{__('All posts', 'daext-interlinks-manager')}:</div>
                                        <div className={'statistic-value'}>{statistics.allPosts}</div>
                                        <div className={'statistic-label'}>{__('Average Internal Links', 'daext-interlinks-manager')}:</div>
                                        <div className={'statistic-value'}>{statistics.averageMil}</div>
                                    </div>
                                    <div className={'tools-actions'}>
                                        <button
                                            onClick={(event) => handleDataUpdateRequired(event)}
                                        ><img src={RefreshIcon} className={'button-icon'}></img>
                                            {__('Update metrics', 'daext-interlinks-manager')}
                                        </button>
                                        <button onClick={() => {
                                            downloadExportFile()
                                        }}
                                                {...(tableData.length === 0 ? {disabled: 'disabled'} : {})}
                                        >
                                            {__('Export', 'daext-interlinks-manager')}
                                        </button>
                                    </div>
                                </div>

                                <div className={'daextinma-react-table__daextinma-filters'}>

                                    <div className={'daextinma-pills'}>
                                        <button className={'daextinma-pill'}
                                                data-checked={formData.optimizationStatus === 0 ? 'true' : 'false'}
                                                onClick={() => setFormData({...formData, optimizationStatus: 0})}
                                        >All
                                        </button>
                                        <button className={'daextinma-pill'}
                                                data-checked={formData.optimizationStatus === 1 ? 'true' : 'false'}
                                                onClick={() => setFormData({...formData, optimizationStatus: 1})}
                                        >Not Optimized
                                        </button>
                                        <button className={'daextinma-pill'}
                                                data-checked={formData.optimizationStatus === 2 ? 'true' : 'false'}
                                                onClick={() => setFormData({...formData, optimizationStatus: 2})}
                                        >Optimized
                                        </button>
                                    </div>
                                    <div className={'daextinma-search-container'}>
                                        <input
                                            onKeyUp={handleKeyUp}
                                            type={'text'} placeholder={__('Filter by title', 'daext-interlinks-manager')}
                                            value={formData.searchString}
                                            onChange={(event) => setFormData({
                                                ...formData,
                                                searchString: event.target.value
                                            })}
                                        />
                                        <input id={'daextinma-search-button'} className={'daextinma-btn daextinma-btn-secondary'}
                                               type={'submit'} value={__('Search', 'daext-interlinks-manager')}
                                               onClick={() => setFormData({
                                                   ...formData,
                                                   searchStringChanged: formData.searchStringChanged ? false : true
                                               })}
                                        />
                                    </div>

                                </div>

                                <Table
                                    data={tableData}
                                    handleSortingChanges={handleSortingChanges}
                                    formData={formData}
                                />

                            </div>

                        </div>

                        :
                        <LoadingScreen
                            loadingDataMessage={__('Loading data...', 'daext-interlinks-manager')}
                            generatingDataMessage={__('Data is being generated. For large sites, this process may take several minutes. Please wait...', 'daext-interlinks-manager')}
                            dataUpdateRequired={dataUpdateRequired}/>
                }

            </React.StrictMode>

        </>

    );

};
export default App;
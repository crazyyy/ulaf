import Table from './components/Table';
import TableUrlDetails from './components/TableUrlDetails';
import {downloadFileFromString} from '../../utils/utils';
import RefreshIcon from '../../../assets/img/icons/refresh-cw-01.svg';
import LoadingScreen from "../../shared-components/LoadingScreen";

const useState = wp.element.useState;
const useEffect = wp.element.useEffect;

const {__} = wp.i18n;

let juiceDataLastUpdate = window.DAEXTINMA_PARAMETERS.juice_data_last_update;
let juiceDataUpdateFrequency = window.DAEXTINMA_PARAMETERS.juice_data_update_frequency;
let currentTime = window.DAEXTINMA_PARAMETERS.current_time;

const App = () => {

    const [formData, setFormData] = useState(
        {
            urlDetailsView: false,
            urlDetailsViewId: 0,
            urlDetailsViewUrl: '',
            searchString: '',
            searchStringChanged: false,
            sortingColumn: 'juice',
            sortingOrder: 'desc'
        }
    );

    const [dataAreLoading, setDataAreLoading] = useState(true);

    const [dataUpdateRequired, setDataUpdateRequired] = useState(false);

    const [tableData, setTableData] = useState([]);
    const [statistics, setStatistics] = useState({
        allUrls: 0,
        averageIil: 0,
        averageJuice: 0
    });

    useEffect(() => {

        if (formData.urlDetailsView) {
            return;
        }

        let automaticUpdate = false;

        if (juiceDataLastUpdate === ''){

            /**
             * If the juice data last update is empty, it means that the data has never been updated. In this case,
             * enable the automatic update.
             */

            automaticUpdate = true;

        }else{

            /**
             * If the juice data last update is not empty, verify if the data needs to be updated based on the
             * update frequency set in the plugin settings.
             */

            // Convert the MySQL date string into a JavaScript Date object.
            let date = new Date(currentTime);

            switch (juiceDataUpdateFrequency) {

                case 'hourly':

                    // Remove one hour from date.
                    date.setHours(date.getHours() - 1);

                    if (new Date(juiceDataLastUpdate) < date) {
                        automaticUpdate = true;
                    }

                    break;
                case 'daily':

                    // remove one day from date.
                    date.setDate(date.getDate() - 1);

                    if (new Date(juiceDataLastUpdate) < date) {
                        automaticUpdate = true;
                    }

                    break;
                case 'weekly':

                    // Remove one week from date.
                    date.setDate(date.getDate() - 7);

                    if (new Date(juiceDataLastUpdate) < date) {
                        automaticUpdate = true;
                    }

                    break;
                case 'monthly':

                    // Remove one month from date.
                    date.setMonth(date.getMonth() - 1);

                    if (new Date(juiceDataLastUpdate) < date) {
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

            juiceDataLastUpdate = currentTime;
            setDataUpdateRequired(true);
            return;

        }

        setDataAreLoading(true);

        /**
         * Initialize the chart data with the data received from the REST API
         * endpoint provided by the plugin.
         */
        wp.apiFetch({
            path: '/interlinks-manager-pro/v1/juice',
            method: 'POST',
            data: {
                search_string: formData.searchString,
                sorting_column: formData.sortingColumn,
                sorting_order: formData.sortingOrder,
                data_update_required: automaticUpdate ? true : dataUpdateRequired
            }
        }).then(data => {

                // Set the table data with setTableData().
                setTableData(data.table);

                // Set the statistics.
                setStatistics({
                    allPosts: data.statistics.all_urls,
                    averageMil: data.statistics.average_iil,
                    averageAil: data.statistics.average_juice
                });

                if (dataUpdateRequired) {

                    // Set the dataUpdateRequired state to false.
                    setDataUpdateRequired(false);

                    // Set the form data to the initial state.
                    setFormData({
                        urlDetailsView: false,
                        urlDetailsViewId: 0,
                        urlDetailsViewUrl: '',
                        searchString: '',
                        searchStringChanged: false,
                        sortingColumn: 'juice_relative',
                        sortingOrder: 'desc'
                    });

                }

                setDataAreLoading(false);

            },
        );

    }, [
        formData.searchStringChanged,
        formData.sortingColumn,
        formData.sortingOrder,
        formData.urlDetailsView,
        dataUpdateRequired
    ]);

    useEffect(() => {

        if (!formData.urlDetailsView) {
            return;
        }

        /**
         * Initialize the chart data with the data received from the REST API
         * endpoint provided by the plugin.
         */
        wp.apiFetch({
            path: '/interlinks-manager-pro/v1/juice-url',
            method: 'POST',
            data: {
                id: formData.urlDetailsViewId,
            }
        }).then(data => {

                // Set the table data with setTableData().
                setTableData(data);

            },
        );

    }, [
        formData.urlDetailsView
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

    // Used by the Navigation component.
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

    function urlDetailsViewHandler(id, url) {

        setFormData({
            ...formData,
            urlDetailsView: true,
            urlDetailsViewId: id,
            urlDetailsViewUrl: url
        });

    }

    // Used to toggle the dataUpdateRequired value.
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
            path: '/interlinks-manager-pro/v1/juice-menu-export-csv',
            method: 'POST'
        }).then(response => {

                downloadFileFromString(response.csv_content, 'juice');

            },
        );

    }

    return (

        <>

            <React.StrictMode>

                {
                    !dataAreLoading ?

                        <>

                            {!formData.urlDetailsView && (
                                <div className="daextinma-admin-body">

                                    <div className={'daextinma-react-table'}>

                                        <div className={'daextinma-react-table-header'}>
                                            <div className={'statistics'}>
                                                <div className={'statistic-label'}>{__('All URLs', 'daext-interlinks-manager')}:</div>
                                                <div className={'statistic-value'}>{statistics.allPosts}</div>
                                                <div className={'statistic-label'}>{__('Average IIL', 'daext-interlinks-manager')}:</div>
                                                <div className={'statistic-value'}>{statistics.averageMil}</div>
                                                <div className={'statistic-label'}>{__('Average Juice', 'daext-interlinks-manager')}:</div>
                                                <div className={'statistic-value'}>{statistics.averageAil}</div>
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

                                        <div className={'daextinma-react-table__daextinma-filters daextinma-react-table__daextinma-filters-juice-menu'}>

                                            <div className={'daextinma-search-container'}>
                                                <input onKeyUp={handleKeyUp} type={'text'}
                                                       placeholder={__('Filter by URL', 'daext-interlinks-manager')}
                                                       value={formData.searchString}
                                                       onChange={(event) => setFormData({
                                                           ...formData,
                                                           searchString: event.target.value
                                                       })}
                                                />
                                                <input id={'daextinma-search-button'}
                                                       className={'daextinma-btn daextinma-btn-secondary'} type={'submit'}
                                                       value={__('Search', 'daext-interlinks-manager')}
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
                                            urlDetailsViewHandler={urlDetailsViewHandler}
                                        />

                                    </div>

                                </div>
                            )}

                            {formData.urlDetailsView && (
                                <>
                                    <div>

                                        <div className="daextinma-admin-body">

                                            <div className={'daextinma-react-table url-details-view'}>


                                                <div className={'daextinma-react-table-header'}>
                                                    <div>{__('Internal Inbound Links for', 'daext-interlinks-manager') + ' ' + formData.urlDetailsViewUrl}</div>
                                                    <a
                                                        className={'daextinma-back-button'}
                                                        onClick={() => setFormData({
                                                            ...formData,
                                                            urlDetailsView: false,
                                                            urlDetailsViewId: 0
                                                        })}
                                                    >{String.fromCharCode(8592)} {__('Back', 'daext-interlinks-manager')}</a>
                                                </div>

                                                <TableUrlDetails
                                                    data={tableData}
                                                    handleSortingChanges={handleSortingChanges}
                                                    formData={formData}
                                                    urlDetailsViewHandler={urlDetailsViewHandler}
                                                />

                                            </div>

                                        </div>


                                    </div>
                                </>
                            )}

                        </>

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